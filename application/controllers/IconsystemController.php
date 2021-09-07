<?php

	class IconsystemController extends Zend_Controller_Action {

		public function init()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			if(!$logininfo->clientid)
			{
				//redir to select client error
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
		}

		public function addiconAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$client_icons = new IconsClient();
			$counted_client_icons = $client_icons->count_client_icons($clientid);
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_Icons();

				$post = $_POST;
				$post['filename'] = $_SESSION['filename'];

				if($upload_form->validate($post))
				{
					$upload_form->insert_icon_data($post);
					$_SESSION['filename'] = '';
					unset($_SESSION['filename']);
					//$this->_redirect(APP_BASE . 'iconsystem/listicons?flg=suc_add');
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retain_values($_POST);
				}
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);
			}
		}

		
		public function addiconvwAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$client_icons = new IconsClient();
			$counted_client_icons = $client_icons->count_client_icons($clientid);
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_Icons();

				$post = $_POST;
				$post['filename'] = $_SESSION['filename'];

				if($upload_form->validate($post))
				{
					$upload_form->insert_icon_data($post,'icons_vw');
					$_SESSION['filename'] = '';
					unset($_SESSION['filename']);
					$this->_redirect(APP_BASE . 'iconsystem/listiconsvw?flg=suc_add');
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retain_values($_POST);
				}
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);
			}
		}
		
		public function addiconmembersAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$client_icons = new IconsClient();
			$counted_client_icons = $client_icons->count_client_icons($clientid);
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_Icons();

				$post = $_POST;
				$post['filename'] = $_SESSION['filename'];

				if($upload_form->validate($post))
				{
					$upload_form->insert_icon_data($post,'icons_member');
					$_SESSION['filename'] = '';
					unset($_SESSION['filename']);
					$this->_redirect(APP_BASE . 'iconsystem/listiconsmembers?flg=suc_add');
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retain_values($_POST);
				}
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);
			}
		}

		public function editiconAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$icon_id = $_REQUEST['id'];

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_Icons();

				$post = $_POST;
				$post['filename'] = $_SESSION['filename'];


				if($upload_form->validate($post))
				{
					$upload_form->update_icon_data($clientid, $post);
					$_SESSION['filename'] = '';
					unset($_SESSION['filename']);
					//$this->_redirect(APP_BASE . 'iconsystem/listicons?flg=suc_ed');
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'iconsystem/listicons?flg=suc&mes='.urlencode($this->view->error_message));
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retain_values($_POST);
				}
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);

				//reset session
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);
			}

			$icons_client = new IconsClient();
			$client_icon = $icons_client->get_client_icon($clientid, $icon_id);
			$this->view->icon_details = $client_icon[0];
		}

		public function editiconvwAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$icon_id = $_REQUEST['id'];

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_Icons();

				$post = $_POST;
				$post['filename'] = $_SESSION['filename'];


				if($upload_form->validate($post))
				{
					$upload_form->update_icon_data($clientid, $post, "icons_vw");
					$_SESSION['filename'] = '';
					unset($_SESSION['filename']);
					$this->_redirect(APP_BASE . 'iconsystem/listiconsvw?flg=suc_ed');
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retain_values($_POST);
				}
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);

				//reset session
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);
			}

			$icons_client = new IconsClient();
			$client_icon = $icons_client->get_client_icon($clientid, $icon_id);
			$this->view->icon_details = $client_icon[0];
		}
		
		public function editiconmembersAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$icon_id = $_REQUEST['id'];

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			
			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_Icons();

				$post = $_POST;
				$post['filename'] = $_SESSION['filename'];


				if($upload_form->validate($post))
				{
					$upload_form->update_icon_data($clientid, $post, "icons_member");
					$_SESSION['filename'] = '';
					unset($_SESSION['filename']);
					$this->_redirect(APP_BASE . 'iconsystem/listiconsmembers?flg=suc_ed');
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retain_values($_POST);
				}
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);

				//reset session
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);
			}

			$icons_client = new IconsClient();
			$client_icon = $icons_client->get_client_icon($clientid, $icon_id);
			$this->view->icon_details = $client_icon[0];
		}

		public function editsysiconAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_Icons();

				$post = $_POST;
				$post['filename'] = $_SESSION['filename'];


				if($upload_form->validatesystemicon($post))
				{
					if($_POST['icon_name'] == 'patient_status_icon') //traffic lights
					{
						$upload_form->update_traffic_icons($clientid, $post);
						$_SESSION['filename'] = '';
						unset($_SESSION['filename']);
						//$this->_redirect(APP_BASE . 'iconsystem/listicons?flg=suc_ed');
					}
					else //normal stufs
					{
						$upload_form->update_system_icon($clientid, $post);
						$_SESSION['filename'] = '';
						unset($_SESSION['filename']);
						//$this->_redirect(APP_BASE . 'iconsystem/listicons?flg=suc_ed');
					}
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'iconsystem/listicons?flg=suc&mes='.urlencode($this->view->error_message));
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retain_values($_POST);
				}
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);

				//reset session
				$_SESSION['filename'] = '';
				unset($_SESSION['filename']);
			}

			
			if ($this->getRequest()->isGet() && isset($_SESSION['filename'])) {
			    unset($_SESSION['filename']); //prevent uploading files saved as session in another action!
			}
			
			
			$icons_id = !empty($_REQUEST['sid']) ? (int)$_REQUEST['sid'] : 0;
			
			$sys_icons = new IconsMaster();
			$system_icons = $sys_icons->get_system_icons($clientid, $icons_id, false, false);
			//ISPC-1896
			
			$system_icon_settings = $sys_icons->get_system_icon_settings();
			
			if (empty($system_icons[ $icons_id ] ['icon_settings']) && !empty($system_icon_settings [$icons_id]) ) {				
				$system_icons[ $icons_id ] ['icon_settings'] = json_encode($system_icon_settings [$icons_id]);
			}			
			$this->view->system_icon_details = $system_icons[ $icons_id ];

			
			$system_icon = $system_icons[ $icons_id ];
			$extra_settins_html = false;
			
			if (!empty($system_icon['icon_settings']) && !is_null($icon_settings = json_decode($system_icon['icon_settings'], true))) {
				
				$extra_settins_html = "<ul class='extra_icon_settings extra_icon_settings_".$icons_id."'>";
				
				if (empty($system_icon['custom']['icon_settings']) || is_null($custom_icon_settings = json_decode($system_icon['custom']['icon_settings'], true))) {
					$custom_icon_settings = array();
				}
				$translations = $this->view->translate("extra_icon_settings");
				
				foreach ($icon_settings as $set) {
				
					$field_name = "icon_settings[".$set['name']."]";
				
					if (isset($custom_icon_settings[$set['name']])) {
						$field_value = $custom_icon_settings[$set['name']];
					}
					else {
						$field_value = $set['default'];
					}
				
					$extra_settins_html .= "<li><label>";
					$extra_settins_html .= $translations[$set['label']];
				
					switch($set['type']) {
						case "text":
							$extra_settins_html .= $this->view->formText($field_name, $field_value, $set['attr']);
							break;			
						case "checkbox":
							//array("disableHidden"=>true)
							$extra_settins_html .= $this->view->formCheckbox($field_name, $field_value , $set['attr'], $set['values']);
							break;
				
						case "radio":
							$extra_settins_html .= $this->view->formRadio($field_name, $field_value, $set['attr'], $set['values'], "&nbsp;");
							break;
				
						case "select":
							$extra_settins_html .= $this->view->formSelect($field_name, $field_value, $set['attr'], $set['values']);
							break;
							
						default :
							$variable = "form". ucfirst(strtolower($set['type']));
							$extra_settins_html .= $this->view->$variable($field_name, $field_value, $set['attr'], $set['values']);		
										
					}

					$extra_settins_html .= "</label></li>";
				
			}
			$extra_settins_html .= "<ul>\n";
		}
		
		$this->view->icon_extra_settins_html = $extra_settins_html;

			//$icons_client = new IconsClient();
			//$client_icon = $icons_client->get_client_icon($clientid, $icon_id);
			//$this->view->icon_details = $client_icon[0];
		}

		public function listiconsoldAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$wlprevileges = new Modules();
			if($wlprevileges->checkModulePrivileges("51", $clientid) || $wlprevileges->checkModulePrivileges("57", $clientid))
			{
				$this->view->wlpermission = true;
			}
			else
			{
				$this->view->wlpermission = false;
			}

			if($wlprevileges->checkModulePrivileges("127", $clientid))
			{
			    $diagno_act_permission = true;
			}
			else
			{
			    $diagno_act_permission= false;
			}
				
			$this->view->diagno_act_permission = $diagno_act_permission;
				
			
			//get system icons
			$sys_icons = new IconsMaster();
			$this->view->system_icons_list = $sys_icons->get_system_icons($clientid, false, false, false);

			//get custom icons
			$icons = new IconsClient();
			$this->view->icons_list = $icons->get_client_icons($clientid);

			if($_REQUEST['op'] == 'del' && $_REQUEST['id'])
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$upload_form = new Application_Form_Icons();
				$upload_form->delete_client_icon($clientid, $_REQUEST['id']);

				echo json_encode(array('status' => 'ok'));
				exit;
			}


			if($logininfo->usertype == 'SA' && !is_dir('icons'))
			{
				$this->view->dir_created = 'no';
			}

			$_SESSION['filename'] = '';
			unset($_SESSION['filename']);
		}
		
		public function listiconsvwAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
 
			//get custom icons
			$icons = new IconsClient();
			$this->view->icons_list = $icons->get_client_icons($clientid,false,"icons_vw");

			if($_REQUEST['op'] == 'del' && $_REQUEST['id'])
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$upload_form = new Application_Form_Icons();
				$upload_form->delete_client_icon($clientid, $_REQUEST['id']);

				echo json_encode(array('status' => 'ok'));
				exit;
			}


			if($logininfo->usertype == 'SA' && !is_dir('icons'))
			{
				$this->view->dir_created = 'no';
			}

			$_SESSION['filename'] = '';
			unset($_SESSION['filename']);
		}
		
		public function listiconsmembersAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			//get custom icons
			$icons = new IconsClient();
			$this->view->icons_list = $icons->get_client_icons($clientid,false,"icons_member");

			if($_REQUEST['op'] == 'del' && $_REQUEST['id'])
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$upload_form = new Application_Form_Icons();
				$upload_form->delete_client_icon($clientid, $_REQUEST['id']);

				echo json_encode(array('status' => 'ok'));
				exit;
			}


			if($logininfo->usertype == 'SA' && !is_dir('icons'))
			{
				$this->view->dir_created = 'no';
			}

			$_SESSION['filename'] = '';
			unset($_SESSION['filename']);
		}

		public function iconimageuploadAction()
		{
			if($_REQUEST['op'] == 'groupsicon' && strlen($_REQUEST['gr']) > 0)
			{
				$_SESSION['file'][$_REQUEST['gr']] = "";
				$_SESSION['filetype'][$_REQUEST['gr']] = "";
				$_SESSION['filetitle'][$_REQUEST['gr']] = "";
			}
			else
			{
				$_SESSION['file'] = '';
				$_SESSION['filetype'] = '';
				$_SESSION['filetitle'] = '';
			}

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$extension = explode(".", $_FILES['qqfile']['name']);

			if($_REQUEST['op'] == 'groupsicon' && strlen($_REQUEST['gr']) > 0)
			{
				$_SESSION['filetype'][$_REQUEST['gr']] = $extension[count($extension) - 1];
				$_SESSION['filetitle'][$_REQUEST['gr']] = $extension[0];
			}
			else
			{
				$_SESSION['filetype'] = $extension[count($extension) - 1];
				$_SESSION['filetitle'] = $extension[0];
			}

			if($_REQUEST['name'] == 'patient_status_icon')
			{
				$timestamp_filename = $_REQUEST['name'] . "_" . $_REQUEST['color'];
			}
			else if($_REQUEST['op'] == 'groupsicon')
			{
				$timestamp_filename = 'client_group_icon';
			}
			else
			{
				$timestamp_filename = time() . "_file";
			}


			$path = 'icons_system';
			if($_REQUEST['op'] == 'pflege')
			{
				$dir = $clientid . '/pflege';

				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else if($_REQUEST['op'] == 'physiotherapist')
			{
				$dir = $clientid . '/physiotherapist';

				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else if($_REQUEST['op'] == 'homecare')
			{
				$dir = $clientid . '/homecare';
			
				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else if($_REQUEST['op'] == 'supplies')
			{
				$dir = $clientid . '/supplies';

				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else if($_REQUEST['op'] == 'voluntaryworkers')
			{
				$dir = $clientid . '/voluntaryworkers';

				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else if($_REQUEST['op'] == 'members')
			{
				$dir = $clientid . '/members';

				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else if($_REQUEST['op'] == 'groupsicon' && strlen($_REQUEST['gr']) > 0)
			{
				$dir = $clientid . '/' . $_REQUEST['gr'];

				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else if($_REQUEST['op'] == 'icons_vw')
			{
				$dir = $clientid . '/icons_vw';

				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else if($_REQUEST['op'] == 'icons_member')
			{
				$dir = $clientid . '/icons_member';

				while(!is_dir($path . '/' . $clientid))
				{
					mkdir($path . '/' . $clientid);
					if($i >= 50)
					{
						exit; //failsafe
					}
					$i++;
				}
			}
			else
			{
				$dir = $clientid;
			}

			while(!is_dir($path . '/' . $dir))
			{
				mkdir($path . '/' . $dir);
				if($i >= 50)
				{
					exit; //failsafe
				}
				$i++;
			}
			$folderpath = $dir;

			$filename = "icons_system/" . $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
			if(count($_SESSION['filename']) == 0)
			{
				$_SESSION['filename'] = array();
			}
			else if(count($_SESSION['filename'][$_REQUEST['gr']]) == 0)
			{
				$_SESSION['filename'][$_REQUEST['gr']] = array();
			}

			if($_REQUEST['op'] == 'groupsicon' && strlen($_REQUEST['gr']) > 0)
			{
				$_SESSION['filename'][$_REQUEST['gr']] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
				$_SESSION['filetype'][$_REQUEST['gr']] = $extension[count($extension) - 1];
			}
			else if($_REQUEST['op'] == 'supplies')
			{
				$_SESSION['supplies_filename'] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
				$_SESSION['filetype'] = $extension[count($extension) - 1];
			}
			else
			{
				$_SESSION['filename'] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
				$_SESSION['filetype'] = $extension[count($extension) - 1];
			}

			$limit = (200 * 1024);
			if(filesize($_FILES['qqfile']['tmp_name']) <= $limit)
			{
				move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename);	
				
				echo json_encode(array(success => true, "file"=>$filename));
			}
			else
			{
				unlink($_FILES['qqfile']['tmp_name']);
				echo json_encode(array("success" => false, "error" => $this->view->translate('icon_file_size_error')));
			}
			exit;
		}

		public function groupsvisitformsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->view->clientid = $clientid;
			$groupid = $logininfo->groupid;
			$userid = $logininfo->userid;
			if(!$clientid)
			{
				$this->_redirect(APP_BASE . 'error/previlege');
				exit;
			}

			$m_group = new GroupMaster();
			$group_master = $m_group->getGroupMaster();
			$this->view->group_master = $group_master;


			$tabmenus_client_details = array();
			$tabmenus_client_details[] = array('menu_title' => $this->view->translate('dont_show'));
			$this->get_client_module_hierarchy($tabmenus_client_details, 0, '');

			$this->view->tabmenus_client_details = $tabmenus_client_details;

			
			
			$ug = new Usergroup();
			$user_master_groups = $ug->get_clients_groups(array($clientid));
			foreach($user_master_groups as $k=>$ugms){
			    $master_groups[] = $ugms['groupmaster'];
			}
			if(empty($master_groups)){
			    $master_groups[] = "999999999";
			}
			
			
			$client_fotm_types = FormTypes::get_form_types($clientid);
			foreach($client_fotm_types as $k =>$ft){
			    $form_types_details[$ft['id']]  = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - ".$ft['name'];
			}
			$this->view->form_types = $form_types_details;
			
			$contact_form_type_permissions = new FormTypePermissions();
			$contact_form_type_perms = $contact_form_type_permissions->get_groups_permissions($clientid, $master_groups);
			
			$this->view->form_type2group = $contact_form_type_perms;
			
			
			if($_REQUEST['op'] == 'del' && strlen($_REQUEST['gr_id']) > '0')
			{
				$gr_visit_form = new GroupsVisitForms();
				$remove_icon = $gr_visit_form->remove_icon($clientid, $_REQUEST['gr_id']);
				$this->_redirect(APP_BASE . 'iconsystem/groupsvisitforms?flg=delsuc');
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$data['clientid'] = $clientid;
				$data['group_visit_form'] = $_POST['group_visit_form']; // group = form_id
				$data['image'] = $_POST['image']; // group = form_id

				$tabmenus_form = new Application_Form_GroupsVisitForms();
				$save_groups_tabmenus = $tabmenus_form->insert_data($data);
				
				$_SESSION['file'] = '';
				$_SESSION['filetype'] = '';
				$_SESSION['filetitle'] = '';
				$_SESSION['filename'] = '';
				
				unset($_SESSION['file']);
				unset($_SESSION['filetype']);
				unset($_SESSION['filetitle']);
				unset($_SESSION['filename']);
				$this->_redirect(APP_BASE . 'iconsystem/groupsvisitforms');
				exit;
			}

			$group_visit_forms = new GroupsVisitForms();
			$get_group_tabnames = $group_visit_forms->get_groups_links($clientid);


			// get icon default image!
			$icon_sys = new IconsMaster();
			$icons_details = $icon_sys->get_system_icons($clientid, '24');

			if($icons_details['24']['custom']['image'])
			{
				$image = $icons_details['24']['custom']['image'];
			}
			else
			{
				$image = $icons_details['24']['image'];
			}

			if($get_group_tabnames)
			{
				foreach($get_group_tabnames as $k_tab => $v_tab)
				{
					$icon_image = '';

					if(!$v_tab['image'])
					{
						$icon_image = $image;
						$groups_tab_names[$v_tab['groupid']]['has_custom_image'] = '0';
					}
					else
					{
						$icon_image = $v_tab['image'];
						$groups_tab_names[$v_tab['groupid']]['has_custom_image'] = '1';
					}
					$groups_tab_names[$v_tab['groupid']]['tabnameid'] = $v_tab['tabmenu'];
					$groups_tab_names[$v_tab['groupid']]['form_type_id'] = $v_tab['form_type'];
					$groups_tab_names[$v_tab['groupid']]['image'] = $icon_image;
				}
			}

			if(count($groups_tab_names) < count($group_master))
			{

				foreach($group_master as $k_m_gr => $v_m_gr)
				{
					if(empty($groups_tab_names[$k_m_gr]))
					{
						$groups_tab_names[$k_m_gr]['has_custom_image'] = '0';
						$groups_tab_names[$k_m_gr]['tabnameid'] = '0';
						$groups_tab_names[$k_m_gr]['form_type_id'] = '0';
						$groups_tab_names[$k_m_gr]['image'] = $image;
					}
				}
			}

			$this->view->groups_tabnames = $groups_tab_names;
			unset($_SESSION['filename']);
		}

		private function get_client_module_hierarchy(&$tabmenus_client_details, $parentid, $space)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			if($clientid)
			{
				$tabmenu_client = Doctrine_Query::create()
					->select('*')
					->from('TabMenuClient')
					->where('clientid = "' . $clientid . '"');
				$tabmenu_client_res = $tabmenu_client->fetchArray();
				$client_tabmenu_ids[] = '999999999';
				foreach($tabmenu_client_res as $k_tabmenu => $v_tabmenu)
				{
					$client_tabmenu_ids[] = $v_tabmenu['menu_id'];
				}

				$folder = Doctrine_Query::create()
					->select('*')
					->from('TabMenus')
					->where('parent_id=' . $parentid)
					->andWhere('isdelete = ?', 0)
					->andWhereIn('id', $client_tabmenu_ids)
					->orderBy("sortorder ASC");

				$folderexec = $folder->execute();
				$folderarray = $folderexec->toArray();
				foreach($folderarray as $key => $val)
				{
					array_push($tabmenus_client_details, array('space' => $space, 'menu_title' => $val['menu_title'], 'menu_link' => $val['menu_link'], 'id' => $val['id']));
					$this->get_client_module_hierarchy($tabmenus_client_details, $val['id'], $space . "&nbsp;&nbsp;&nbsp;");
				}
			}
			return;
		}

		private function retain_values($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		
		

		public function iconimageuploadvwAction()
		{
		    
					
    		// get associated clients of current clientid START 
    		$logininfo = new Zend_Session_Namespace('Login_Info');
    		$connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
    		if($connected_client){
    		    $clientid = $connected_client;
    		} else{
    		    $clientid = $logininfo->clientid;
    		}
    		
		    
		    if($_REQUEST['op'] == 'groupsicon' && strlen($_REQUEST['gr']) > 0)
		    {
		        $_SESSION['file'][$_REQUEST['gr']] = "";
		        $_SESSION['filetype'][$_REQUEST['gr']] = "";
		        $_SESSION['filetitle'][$_REQUEST['gr']] = "";
		    }
		    else
		    {
		        $_SESSION['file'] = '';
		        $_SESSION['filetype'] = '';
		        $_SESSION['filetitle'] = '';
		    }
		
		    $extension = explode(".", $_FILES['qqfile']['name']);
		
		    if($_REQUEST['op'] == 'groupsicon' && strlen($_REQUEST['gr']) > 0)
		    {
		        $_SESSION['filetype'][$_REQUEST['gr']] = $extension[count($extension) - 1];
		        $_SESSION['filetitle'][$_REQUEST['gr']] = $extension[0];
		    }
		    else
		    {
		        $_SESSION['filetype'] = $extension[count($extension) - 1];
		        $_SESSION['filetitle'] = $extension[0];
		    }
		
		    if($_REQUEST['name'] == 'patient_status_icon')
		    {
		        $timestamp_filename = $_REQUEST['name'] . "_" . $_REQUEST['color'];
		    }
		    else if($_REQUEST['op'] == 'groupsicon')
		    {
		        $timestamp_filename = 'client_group_icon';
		    }
		    else
		    {
		        $timestamp_filename = time() . "_file";
		    }
		
		
		    $path = 'icons_system';
		    if($_REQUEST['op'] == 'pflege')
		    {
		        $dir = $clientid . '/pflege';
		
		        while(!is_dir($path . '/' . $clientid))
		        {
		            mkdir($path . '/' . $clientid);
		            if($i >= 50)
		            {
		                exit; //failsafe
		            }
		            $i++;
		        }
		    }
		    else if($_REQUEST['op'] == 'physiotherapist')
		    {
		        $dir = $clientid . '/physiotherapist';
		
		        while(!is_dir($path . '/' . $clientid))
		        {
		            mkdir($path . '/' . $clientid);
		            if($i >= 50)
		            {
		                exit; //failsafe
		            }
		            $i++;
		        }
		    }
		    else if($_REQUEST['op'] == 'homecare')
		    {
		        $dir = $clientid . '/homecare';
		        	
		        while(!is_dir($path . '/' . $clientid))
		        {
		            mkdir($path . '/' . $clientid);
		            if($i >= 50)
		            {
		                exit; //failsafe
		            }
		            $i++;
		        }
		    }
		    else if($_REQUEST['op'] == 'supplies')
		    {
		        $dir = $clientid . '/supplies';
		
		        while(!is_dir($path . '/' . $clientid))
		        {
		            mkdir($path . '/' . $clientid);
		            if($i >= 50)
		            {
		                exit; //failsafe
		            }
		            $i++;
		        }
		    }
		    else if($_REQUEST['op'] == 'voluntaryworkers')
		    {
		        $dir = $clientid . '/voluntaryworkers';
		
		        while(!is_dir($path . '/' . $clientid))
		        {
		            mkdir($path . '/' . $clientid);
		            if($i >= 50)
		            {
		                exit; //failsafe
		            }
		            $i++;
		        }
		    }
		    else if($_REQUEST['op'] == 'groupsicon' && strlen($_REQUEST['gr']) > 0)
		    {
		        $dir = $clientid . '/' . $_REQUEST['gr'];
		
		        while(!is_dir($path . '/' . $clientid))
		        {
		            mkdir($path . '/' . $clientid);
		            if($i >= 50)
		            {
		                exit; //failsafe
		            }
		            $i++;
		        }
		    }
		    else
		    {
		        $dir = $clientid;
		    }
		
		    while(!is_dir($path . '/' . $dir))
		    {
		        mkdir($path . '/' . $dir);
		        if($i >= 50)
		        {
		            exit; //failsafe
		        }
		        $i++;
		    }
		    $folderpath = $dir;
		
		    $filename = "icons_system/" . $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
		    if(count($_SESSION['filename']) == 0)
		    {
		        $_SESSION['filename'] = array();
		    }
		    else if(count($_SESSION['filename'][$_REQUEST['gr']]) == 0)
		    {
		        $_SESSION['filename'][$_REQUEST['gr']] = array();
		    }
		
		    if($_REQUEST['op'] == 'groupsicon' && strlen($_REQUEST['gr']) > 0)
		    {
		        $_SESSION['filename'][$_REQUEST['gr']] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
		        $_SESSION['filetype'][$_REQUEST['gr']] = $extension[count($extension) - 1];
		    }
		    else if($_REQUEST['op'] == 'supplies')
		    {
		        $_SESSION['supplies_filename'] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
		        $_SESSION['filetype'] = $extension[count($extension) - 1];
		    }
		    else
		    {
		        $_SESSION['filename'] = $folderpath . "/" . trim($timestamp_filename) . '.' . $extension[count($extension) - 1];
		        $_SESSION['filetype'] = $extension[count($extension) - 1];
		    }
		
		    $limit = (200 * 1024);
		    if(filesize($_FILES['qqfile']['tmp_name']) <= $limit)
		    {
		        move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename);
		
		        echo json_encode(array(success => true));
		    }
		    else
		    {
		        unlink($_FILES['qqfile']['tmp_name']);
		        echo json_encode(array("success" => false, "error" => $this->view->translate('icon_file_size_error')));
		    }
		    exit;
		}
	
		//get view list icons
		public function listiconsAction(){
			set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			if($this->getRequest()->isPost())
			{
				$upload_form = new Application_Form_Icons();
				$upload_form->delete_client_icon($clientid, $_REQUEST['now_del_id']);
				
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'iconsystem/listicons?flg=suc&mes='.urlencode($this->view->error_message));
				
			}
		}
		
		//get system icons list
		public function getsysiconsAction(){
			//populate the datatables
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			$wlprevileges = new Modules();
			if($wlprevileges->checkModulePrivileges("51", $clientid) || $wlprevileges->checkModulePrivileges("57", $clientid))
			{
				$wlpermission = true;
			}
			else
			{
				$wlpermission = false;
			}
			
			if($wlprevileges->checkModulePrivileges("127", $clientid))
			{
				$diagno_act_permission = true;
			}
			else
			{
				$diagno_act_permission= false;
			}			
			
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			if(!$_REQUEST['length']){
				$_REQUEST['length'] = "25";
			}
			$limit = (int)$_REQUEST['length'];
			$offset = (int)$_REQUEST['start'];
			$search_value = addslashes($_REQUEST['search']['value']);
			
			$columns_array = array(
					"1" => "name",
					"3" => "create_date"
			);
			$columns_search_array = $columns_array;
			
			if(isset($_REQUEST['order'][0]['column']))
			{
				$order_column = $_REQUEST['order'][0]['column'];
				$order_dir = $_REQUEST['order'][0]['dir'];
			}
			else
			{
				$order_column = '';
			}
			
			//get system icons
			$sys_icons = new IconsMaster();
			$fdocarray = $sys_icons->get_system_icons($clientid, false, false, false);
			
			foreach($fdocarray as $fkey=>$fval)
			{
				if(
				(($fval['id'] != '3' && $fval['id'] != '2' &&  $fval['id'] < '11') || (  $fval['id'] > '22'   &&  $fval['id'] != '43') ) || // here are taken all icons for wl and non wl clients
				($fval['id'] == '2' && $wlpermission === true) ||  // for wl clients - show volversorgung icon
				($fval['id'] >= '11' && $fval['id'] <= '22' && $wlpermission === false) || // no wl clients - show all sapv icons
				($fval['id'] != '43' &&( in_array($fval['id'],array('44','45','46','47')) && $diagno_act_permission) )
				)
				{
					if($fval['custom'])
					{
						$fval['create_date'] = $fval['custom']['create_date'];
					}
					else 
					{
						$fval['create_date'] = '0000-00-00';
					}
					$fval['name'] = $this->view->translate($fval['name']);
					$fdoclimit[$fkey] = $fval;
				}
			}
			//print_r($fdoclimit);
			$full_count  = count($fdoclimit);
    		
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
							if($pkey == 'create_date')
							{
								$sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
							}
							
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
			
			if($order_column != '')
			{				
				$sort_col = array();
    			foreach ($fdoclimit as $key=> $row)
    			{
    				if($order_column == '3')
    				{
    					$sort_col[$key] = strtotime($row[$columns_array[$order_column]]);
    				}
    				else
    				{
    					$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
	    				$fdoclimit[$key] = $row;
	    				$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
    				}
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
			
					if (!empty($mdata['custom']))
					{
						if(strlen(trim(rtrim($mdata['custom']['image']))) >'0' && is_file(APPLICATION_PATH . '/../public/icons_system/' . $mdata['custom']['image']))
						{
							$resulted_data[$row_id]['image'] = '<img class="icon_image" id="icon_image_icsys_'.$mdata['id']. '" src="' . APP_BASE . 'icons_system/' . $mdata['custom']['image'] . '" />';
						}
						else 
						{
							$resulted_data[$row_id]['image'] = '-';
						}
						//$resulted_data[$row_id]['name'] = sprintf($link,$this->view->translate($mdata['name']));
						$resulted_data[$row_id]['name'] = sprintf($link, $mdata['name']);
						$resulted_data[$row_id]['color'] = '<div class="icon_color_placeholder" style="background: #' . $mdata['custom']['color'] . '"></div>';
						$resulted_data[$row_id]['create_date'] = sprintf($link, date('d.m.Y', strtotime($mdata['custom']['create_date'])));
						$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'iconsystem/editsysicon?sid='.$mdata['id']. '&cid='.$mdata['custom']['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" type="icsys" rel="'.$mdata['custom']['id'].'" id="delete_icsys_'.$mdata['custom']['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					}
					else 
					{
						if(strlen(trim(rtrim($mdata['image']))) >'0' && is_file(APPLICATION_PATH . '/../public/icons_system/' . $mdata['image']))
						{
							$resulted_data[$row_id]['image'] = '<img class="icon_image" id="icon_image_icsys_'.$mdata['id']. '" src="' . APP_BASE . 'icons_system/' . $mdata['image'] . '" />';
						}
						else 
						{
							$resulted_data[$row_id]['image'] = '-';
						}
						//$resulted_data[$row_id]['name'] = sprintf($link,$this->view->translate($mdata['name']));
						$resulted_data[$row_id]['name'] = sprintf($link, $mdata['name']);
						$resulted_data[$row_id]['color'] = '<div class="icon_color_placeholder" style="background: #' . $mdata['color'] . '"></div>';
						$resulted_data[$row_id]['create_date'] = '-';
						$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'iconsystem/editsysicon?sid='.$mdata['id']. '"><img border="0" src="'.RES_FILE_PATH.'/images/add.png?>" />';
					}
					$row_id++;
				
			}
			
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
			
			$this->_helper->json->sendJson($response);
		}
		
		//get client icons list
		public function getclienticonsAction(){
			//populate the datatables
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			if(!$_REQUEST['length']){
				$_REQUEST['length'] = "25";
			}
			$limit = (int)$_REQUEST['length'];
			$offset = (int)$_REQUEST['start'];
			$search_value = addslashes($_REQUEST['search']['value']);
		
			$columns_array = array(
					"1" => "name",
					"3" => "create_date"
			);
			$columns_search_array = $columns_array;
			
			if(isset($_REQUEST['order'][0]['column']))
			{
				$order_column = $_REQUEST['order'][0]['column'];
				$order_dir = $_REQUEST['order'][0]['dir'];
			}
			else
			{
				$order_column = '';
			}
			
			//get custom icons
			$icons = new IconsClient();
			$fdoclimit = $icons->get_client_icons($clientid);
			$full_count  = count($fdoclimit);
    		
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
						if($pkey == 'create_date')
						{
							$sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
						}
							
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
			
			if($order_column != '')
			{
				$sort_col = array();
				foreach ($fdoclimit as $key=> $row)
				{
					if($order_column == '3')
					{
						$sort_col[$key] = strtotime($row[$columns_array[$order_column]]);
					}
					else
					{
						$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
						$fdoclimit[$key] = $row;
						$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
					}
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
				if(strlen(trim(rtrim($mdata['image']))) >'0' && is_file(APPLICATION_PATH . '/../public/icons_system/' . $mdata['image']))
						{
							$resulted_data[$row_id]['image'] = '<img class="icon_image" id="icon_image_icl_'.$mdata['id']. '" src="' . APP_BASE . 'icons_system/' . $mdata['image'] . '" />';
						}
						else 
						{
							$resulted_data[$row_id]['image'] = '-';
						}
						$resulted_data[$row_id]['name'] = sprintf($link,$mdata['name']);
						$resulted_data[$row_id]['color'] = '<div class="icon_color_placeholder" style="background: #' . $mdata['color'] . '"></div>';
						$resulted_data[$row_id]['create_date'] = sprintf($link, date('d.m.Y', strtotime($mdata['create_date'])));
						$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'iconsystem/editicon?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" type="icl" rel="'.$mdata['id'].'" id="delete_icl_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					$row_id++;
			}
				
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
				
			$this->_helper->json->sendJson($response);
		}
		
		
		
	}
?>