<?php

	class Pms_Plugin_Acl extends Zend_Controller_Plugin_Abstract {

		private $_acl = null;

		public function __construct(Zend_Acl $acl)
		{
			$this->_acl = $acl;
		}

		public function preDispatch(Zend_Controller_Request_Abstract $request)
		{
			$this->view->sticky = "false";
			Zend_Registry::set('acl', $this->_acl);

			if(!file_exists("uploads"))
			{
				mkdir('uploads');
			}
			$view = Zend_Layout::getMvcInstance()->getView();

			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/jquery-1.8.0.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/pms/jquery.table_navigation.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/livesearch/jquery.ajaxQueue.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/menu.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/pms/jqcallserver.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/mask/jquery.maskedinput.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/pms/usersession.js?' . date('Ymdi'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/jquery.megamenu.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/popup/popup.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/jquery.ui.timepicker.js?'.date('Ymd'));
//			if($_REQUEST['ietest']=='1')
//			{
//				print_r($_SERVER['HTTP_USER_AGENT']);
//				print_r("\n");
//
//				print_r(preg_match("/MSIE 8/i", $_SERVER['HTTP_USER_AGENT']));
//				print_r("\n");
//				var_dump(preg_match("/MSIE 8/i", $_SERVER['HTTP_USER_AGENT']));
//				print_r("\n");
//				exit;
//			}
//			if(eregi("MSIE 8", $_SERVER ['HTTP_USER_AGENT']))
			if(preg_match("/MSIE 8/i", $_SERVER['HTTP_USER_AGENT']))
			{
				$view->headLink()->appendStylesheet(RES_FILE_PATH . '/css/ie8.css');
			}
//			else if(eregi("MSIE 7", $_SERVER['HTTP_USER_AGENT']))
			else if(preg_match("/MSIE 7/i", $_SERVER['HTTP_USER_AGENT']))
			{
				$view->headLink()->appendStylesheet(RES_FILE_PATH . '/css/ie7.css');
			}
			
			$view->headLink()->appendStylesheet(RES_FILE_PATH . '/css/jquery.ui.timepicker.css?');
			
			if(Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'addnews' || Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'editnews' || Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'editcontactaddress' || Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'doctorletteradd' || Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'doctorletteredit')
			{
				$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/tinymce3/jscripts/tiny_mce/tiny_mce.js?' . date('Ymd'));
				$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/swampy_browser/sb.js?' . date('Ymd'));
			}

			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/pms/shortcut.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/livesearch/directpatientsearch.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/pms/jquery.alerts_new.js?' . date('Ymd'));
			$view->headScript()->appendFile(RES_FILE_PATH . '/javascript/jgrowl/jquery.jgrowl.js?' . date('Ymd'));
			$view->headScript()->appendFile(OLD_RES_FILE_PATH . "/jslang/jstranslate", 'text/javascript', array('charset' => 'UTF-8'));

			$action = strtolower($request->getActionName());
			$controller = strtolower($request->getControllerName());

			if($this->_acl->isLogin())
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');

				if($logininfo->usertype == "SA" && $logininfo->showinfo != "show")
				{
					$logininfo->showinfo = "hide";
				}
				else
				{
					$logininfo->showinfo = "show";
				}

				if(isset($_REQUEST['showinfo']) && $_REQUEST['showinfo'] == "show")
				{
					$logininfo->showinfo = "show";
				}
				if(isset($_REQUEST['showinfo']) && $_REQUEST['showinfo'] == "hide")
				{
					$logininfo->showinfo = "hide";
				}
				/* if($logininfo->userid>0)
				  {
				  $loguser = Doctrine_Query::create()
				  ->select('*')
				  ->from('User')
				  ->where("sessionid!='".session_id()."' and id=".$logininfo->userid);
				  $logexec = $loguser->execute();
				  $logarray = $logexec->toArray();

				  if(count($logarray)>0)
				  {
				  $request->setControllerName('index');
				  $request->setActionName('index');
				  }
				  } */
				
				
				//WHY IS THIS HERE? COMMENT IT FOR NOW
				
				/*
				$referred = Doctrine::getTable('MenuClient')->findBy('clientid', $logininfo->clientid);

				if($referred)
				{
					$view->msgnotify = "";
					$refarr = $referred->toArray();

					if(count($refarr) < 1 && $logininfo->usertype != "SA")
					{
						$view->msgnotify = '$.jGrowl("You Dont Have Permissions Please Contact to SuperAdmin<br><a href=\'Index/logout\'>Logout</a>", { sticky: true });';
					}
				}*/
				
				
			} else if($controller == 'cron') { //cron controller does not require authentication
				
				
			} else {
				//entrust cut off
//				if($controller == 'index' && $action == 'logout') {
//					//use Entrust logout
//					$this->getResponse ()->setRedirect ( 'http://www.ispc-login.de/IdentityGuardAuth/IdentityGuardLogoff.aspx', 301 ); //redirect & exit
//					return;
//				} else {
//					$request->setControllerName ( 'index' );
//					$request->setActionName ( 'index' );
//				}
				$request->setControllerName('index');
				$request->setActionName('index');
			}

			/* if($logininfo->usertype=='SA')
			  {
			  $isadmin = 1;
			  }
			  else
			  {
			  $isadmin = 0;
			  }

			  $topmenu = Menus::getMenusBySortOrder(0,$isadmin);
			  $grid = new Pms_Grid($topmenu,1,count($topmenu),"topmenugrid.html");
			  $view->topmenus = $grid->renderGrid();

			  $menuarr = Menus::getLeftParentMenus($isadmin);
			  $grid = new Pms_Grid($menuarr,1,count($menuarr),"leftmenugrid.html");
			  $view->leftmenus = $grid->renderGrid(); */

			if($logininfo->usertype == 'SA' || $logininfo->sca == '1'  || $logininfo->multiple_clients == '1') //check if superadmin or super client admin
			{
				//$clientlist = Doctrine::getTable('Client')->findAll();
				/* $clist = Doctrine_Query::create()
				  ->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name")
				  ->from('Client')
				  ->where('isdelete=0');
				  $clistexec = $clist->execute();
				  $clientlist = $clistexec->toArray(); */

				if($logininfo->sca == '1')
				{
					$usersca_clients = Usersa2Client::getusersaclients();
					foreach($usersca_clients as $usca)
					{
						$usersca_clients_str .= '"' . $usca ['client'] . '",';
					}
					$sca_clients = 'c.id in (' . substr($usersca_clients_str, 0, - 1) . ')';
				}
				elseif($logininfo->multiple_clients == '1')
				{
					$users_clients = User2Client::getuserclients();
					foreach($users_clients as $us)
					{
						$users_clients_str .= '"' . $us ['client'] . '",';
					}
					$sca_clients = 'c.id in (' . substr($users_clients_str, 0, - 1) . ')';
				}
				else
				{
					$sca_clients = '1';
				}

				$clist = Doctrine_Query::create()
					->select("*,lower(CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1)) as client_name,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as clientname")
					->from('Client c')
					->where('c.isdelete=0')
					->andWhere($sca_clients)
					->orderBy('h__0 ASC');
				$client_list_arr = $clist->fetchArray();

				$clientarray = array("0" => "Select Client");

				foreach($client_list_arr as $key => $val)
				{
					$clientarray [$val ['id']] = $val ['clientname'];
				}

				$view->adminclientarray = $clientarray;

				$loguser = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where("id=" . $logininfo->userid);
				$logarray = $loguser->fetchArray();

				$view->loguname = $logarray [0] ['user_title'] . " " .$logarray [0] ['last_name'] . ", " . $logarray [0] ['first_name'];
				$view->logutime = date("d.m.Y H:i", time());
			}

			if($logininfo->clientid > 0 && $logininfo->usertype != 'SA' && $logininfo->sca != '1' && $logininfo->multiple_clients != '1' ) // if no SA/SCA then it's "regular" user
			{
				$clientlist = Doctrine::getTable('Client')->find($logininfo->clientid);
				$inarray = $clientlist->toArray();
				$seconds = $inarray ['inactivetime'] * 60000; //minutes to microseconds

				$view->inactive_time = $seconds;
				
				if($logininfo->usertype == 'CA')
				{
					$news = Doctrine_Query::create()->select("*,AES_DECRYPT(news_title,'" . Zend_Registry::get('salt') . "') as news_title,AES_DECRYPT(news_content,'" . Zend_Registry::get('salt') . "') as news_content")->from('News')->where('clientid=' . $logininfo->clientid . ' and issystem=1 and acknowledge=0');

					$newsexec = $news->execute();
					$sysarray = $newsexec->toArray();

					if($logininfo->setlater != 1 && count($sysarray) > 0)
					{
						if($sysarray [0] ['viewcount'] <= 2 && $sysarray [0] ['acknowledge'] != 1)
						{
//						$view->systemnews = "javascript:centerPopup({sr:'news/systemnews?popup=popup',ht:'250px',wt:'350px'});loadPopup();";
//						$view->closebutton = '<a id="popupContactClose" onclick="closepopup()">x</a>';
						}
					}
				}
				else
				{

					$logininfo->setlate = 1;
				}

				$loguser = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where("id=" . $logininfo->userid);
				$logarray= $loguser->fetchArray();
				
				$view->loguname = $logarray [0] ['user_title'] . " " . $logarray [0] ['last_name'] . ", " . $logarray [0] ['first_name'];
				$view->logutime = date("d.m.Y H:i", time());
			}
			elseif( $logininfo->sca == '1' && $logininfo->clientid)
			{
				$clientlist = Doctrine::getTable('Client')->find($logininfo->clientid);
				$inarray = $clientlist->toArray();
				$seconds = $inarray ['inactivetime'] * 60000; //minutes to microseconds

				$view->inactive_time = $seconds;
			}
			elseif( $logininfo->multiple_clients == '1' && $logininfo->clientid)
			{
				$clientlist = Doctrine::getTable('Client')->find($logininfo->clientid);
				$inarray = $clientlist->toArray();
				$seconds = $inarray ['inactivetime'] * 60000; //minutes to microseconds

				$view->inactive_time = $seconds;
			}
			else
			{
				$view->inactive_time = '6000000';
			}
			
			if(empty($inarray['inactivetime'])) {
				$inactivetime = 100;
			} else {
				$inactivetime = $inarray['inactivetime'];
			}

			$logininfo->inactivetime = $inactivetime; 
			
			
			//update session
			if($logininfo->userid > 0 && $controller != 'usersessions' && ($action != 'check' || $action != 'checknew')
			&& ($controller != 'index' && $action != 'logout'))
			{
				$setses = Doctrine::getTable('User')->find($logininfo->userid);
				$setses->logintime = date("Y-m-d H:i:s");
				$setses->save();
				
				//time stored in session
				
				$logininfo->lastactive = time(); 

				//user session
				$usersession = new UserSessions ();
				$usersession->update_session();
			}
		}

		public function postDispatch(Zend_Controller_Request_Abstract $request)
		{
			Zend_Registry::set('acl', $this->_acl);

			$view = Zend_Layout::getMvcInstance()->getView();

			$requestinfo = new Zend_Session_Namespace('Login_Info');
			$requestinfo->requesturl = Zend_Controller_Front::getInstance()->getRequest()->getControllerName() . "/" . Zend_Controller_Front::getInstance()->getRequest()->getActionName() . "?" . $_SERVER ['QUERY_STRING'];

			if($requestinfo->usertype == 'SA')
			{
				$isadmin = 1;
			}
			else
			{
				if($requestinfo->usertype == 'CA' || $requestinfo->usertype == 'SCA')
				{
					$isadmin = 2;
				}
				else
				{
					$isadmin = 0;
				}
			}

			$app_layout = Zend_Layout::getMvcInstance()->getLayout();

			//don`t load menus data in ajax request layouts
			$excluded_layouts = array('layout_basic', 'layout_blank', 'layout_ajax');

			if(!in_array($app_layout, $excluded_layouts))
			{
				$topmenu = Menus::getTopParentMenus(0, $isadmin);
				$grid = new Pms_Grid($topmenu, 1, count($topmenu), "topmenugrid.html");
				$view->topmenus = $grid->renderGrid();

				$submenuarr = Menus::getAllLeftSubMenus();
				$view->allsubmenus = $submenuarr;

				//get waiting share requests for current client
				$waiting_requests = PatientsMarked::received_requests_get($requestinfo->clientid, 'p');
				$view->waiting_share_request = count($waiting_requests);

				$menuarr = Menus::getLeftParentMenus($isadmin);
				$grid = new Pms_Grid($menuarr, 1, count($menuarr), "leftmenugrid.html");
				$view->leftmenus = $grid->renderGrid();
			}
		}

		public static function getClientTabmenuAccess($clid)
		{
			$tablink = Zend_Controller_Front::getInstance()->getRequest()->getControllerName() . "/" . Zend_Controller_Front::getInstance()->getRequest()->getActionName();

			$cldata = Doctrine_Query::create()
				->select('*')
				->from('TabMenus')
				->where("menu_link = '" . $tablink . "'");
			$clarr = $cldata->fetchArray();

			if($clarr)
			{
				$menuid = $clarr[0]['id'];
			}

			if($menuid > 0)
			{
				$menu = Doctrine_Query::create()
					->select('*')
					->from('TabMenuClient')
					->where("menu_id = '" . $menuid . "'")
					->andWhere("clientid = '" . $clid . "'");
				$clientmenu = $menu->fetchArray();
				if($clientmenu)
				{
					if(count($clientmenu) > 0)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
			}
		}

		public function routeShutdown(Zend_Controller_Request_Abstract $request)
		{
			$userprivileges = Links::checkLinkPermission();
			if(!$userprivileges)
			{
				PatientPermissions::LogRightsError(true);
				$this->getResponse()->setRedirect(APP_BASE . 'error/previlege', 301); //redirect & exit
				return;
			}
		}

	}

?>