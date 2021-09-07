<?php

class IndexController extends Zend_Controller_Action
{

	public function init()
	{
		/* Initialize action controller here */
	}

	public function indexAction()
	{

	    $this->_helper->layout->setLayout('layout_blank');
	     
	    
		 
		$acl = Zend_Registry::get('acl');
		if($acl->isLogin()) {
			$this->_redirect(APP_BASE."overview/overview");
		}
		 
		//$cookie_user = $_COOKIE['IGUser'];
		
		
		$cookie_user = $_SESSION['username'];
		
		
		$ldap = false;		
		$live = false;		
		
		
		
		$Tr = new Zend_View_Helper_Translate();
		if($this->getRequest()->isPost())
		{
			$error = 0;
			if(strlen(trim($this->_request->getPost('username')))<1){$this->view->error_message = $this->view->translate('login-error');$error=1;}
			if(strlen(trim($this->_request->getPost('password')))<1){$this->view->error_message = $this->view->translate('login-error');$error=1;}
				
			if($error==0)
			{
				if(!$ldap){
				    
    				/*********************GOETTINGEN COMMENT*************************/
    				$user = Doctrine_Query::create()
    				->select('*')
    				->from('User')
    				->where("username='".$this->_request->getPost('username')."' and password='".md5($this->_request->getPost('password'))."' and isdelete=0 and isactive=0 and duplicated_user = 0");
    				$userexec = $user->execute();
    				/*********************GOETTINGEN COMMENT*************************/
				} 
				else
				{
    				/*********************GOETTINGEN UNCOMMENT*************************/
    				$ldap=new Net_Ldap();
                    if ($ldap->need_ldap_login($this->_request->getPost('username'))){
                        if($ldap->do_ldap_login($this->_request->getPost('username'), $this->_request->getPost('password'))){
                            $user = Doctrine_Query::create()
                                ->select('*')
                                ->from('User')
                                ->where("username=?",$this->_request->getPost('username'))
                                ->andwhere("isdelete=0")
                                ->andwhere("isactive=0");
                            $userexec = $user->execute();
                        }
                    }else {
    					//Login with db-password
                        $user = Doctrine_Query::create()
                            ->select('*')
                            ->from('User')
                            ->where("username=? and password=? and isdelete=0 and isactive=0", array($this->_request->getPost('username') , md5($this->_request->getPost('password')) ));
                        $userexec = $user->execute();
                    }
    				/*********************GOETTINGEN UNCOMMENT*************************/
				}
				
				if($userexec)
				{
					$userarray = $userexec->toArray();

					if(count($userarray)>0)
					{
						
						$has_duplicates = User::get_active_duplicates($userarray[0]['id']);
						$has_connected_clients = User2Client::getuserclients($userarray[0]['id']);
						
						
						if($userarray[0]['clientid']>0)
						{
							$clnt = Doctrine::getTable('Client')->find($userarray[0]['clientid']);
							$clntarray = $clnt->toArray();

							if($clntarray['isactive']==1)
							{
								$this->view->error_message = $this->view->translate('account-supend-error')."<br />";
							}
						}
							
						if($userarray[0]['isactive']==0)
						{
							$setses = Doctrine_Query::create()
							->update('User')
							->set("sessionid","''")
							->where("sessionid='".session_id()."'");
							$setses->execute();

							$setses = Doctrine::getTable('User')->find($userarray[0]['id']);
							$setses->sessionid = session_id();
							$setses->logintime = date("Y-m-d H:i:s");
							$setses->save();
								
							$logininfo= new Zend_Session_Namespace('Login_Info');
							$logininfo->userid = $userarray[0]['id'];
							$logininfo->groupid = $userarray[0]['groupid'];
							$logininfo->clientid = $userarray[0]['clientid'];
							$logininfo->setlater=0;
							$logininfo->parentid = $userarray[0]['parentid'];
							$logininfo->username = $userarray[0]['username'];
							$logininfo->loginclientid = $userarray[0]['clientid'];
							$logininfo->usertype = $userarray[0]['usertype'];
							$logininfo->sca = $userarray[0]['issuperclientadmin']; //set that user is super client admin
							
							//ISPC-2827 Ancuta 26.03.2021
							$logininfo->isEfaUser = $userarray[0]['efa_user'];
							//-- 
							
							if($has_duplicates && $has_connected_clients){
								$logininfo->multiple_clients = "1"; //this is user for regular user connected to multiple clients
							} else{
								$logininfo->multiple_clients = "0";
							}
								
							
							$grp = new Usergroup();
							$grparr = $grp->getUserGroupData($userarray[0]['groupid']);
								
							$logininfo->mastergroupid = $grparr[0]['groupmaster'];
								
							if(strtolower($grparr[0]['groupname']) == 'hospiz'){ //hospiz hack
								$logininfo->hospiz = 1;
							} else {
								$logininfo->hospiz = 0;
							}
							$logininfo->showinfo = "";

							if($userarray[0]['clientid']>0 && $userarray[0]['usertype']!='SA')
							{
								$client = Doctrine_Query::create()
								->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
										AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
										,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone,AES_DECRYPT(fileupoadpass,'".Zend_Registry::get('salt')."') as fileupoadpass")
										->from('Client')
										->where('id='.$userarray[0]['clientid']);
								$clientexec = $client->execute();
								$clientarray = $clientexec->toArray();
								$logininfo= new Zend_Session_Namespace('Login_Info');
								$logininfo->clientname = $clientarray[0]['client_name'];
								$logininfo->filepass = $clientarray[0]['fileupoadpass'];
								
								//ISPC-2827 Ancuta 26.03.2021
								$logininfo->isEfaClient = $clientarray[0]['efa_client'];
								//-- 
								
								if(!empty($clientarray[0]['inactivetime'])) {
									$inactivetime = $clientarray[0]['inactivetime'];
								} else {
									$inactivetime = '100'; //1000 minutes
								}
								
								$logininfo->inactivetime = $inactivetime;

								if($clientarray[0]['maintainance']>0)
								{
									$this->_redirect(APP_BASE."error/undermaintainance");
								}
							} else {
								$logininfo->inactivetime = '100';
							}

							$logininfo->lastactive = time();
							
							if(strlen($_SESSION['logouturl'])>0)
							{
								$this->_redirect($_SESSION['logouturl']);
							}else{
								if($_SERVER['HTTP_REFERER'])
								{
										
									if(Zend_Controller_Front::getInstance()->getRequest()->getActionName()=='index')
									{
									    if (ISPC_WEBSITE_VIEW_VERSION == 'mobile' 
									        && ! $this->getInvokeArg('bootstrap')->getResource('Useragent')->getDevice()->getFeature('is_tablet')) 
									    {
				                            $this->redirect(APP_BASE . "patient/patientoveralllist", array("exit"=>true));
									    } else {
    										$this->_redirect(APP_BASE."overview/overview");									        
									    }
									}else
									{
										$this->_redirect($_SERVER['HTTP_REFERER']);
									}
										
								}else{
									$this->_redirect(APP_BASE."overview/overview");
								}
									
							}
								
						}else{
							$this->view->error_message .= $this->view->translate('inactivemsg');
						}

					}else{
						$this->view->error_message = $this->view->translate('login-error');
					}

				}
			}
		}else{

			if(strlen($cookie_user)>0 && $_SESSION['authenticated'] == '1')
			{
				$user = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where("username='".$cookie_user."' and isdelete=0 and isactive=0");
				$userexec = $user->execute();
					
				if($userexec)
				{
					$userarray = $userexec->toArray();
					if(count($userarray)>0)
					{
						$has_duplicates = User::get_active_duplicates($userarray[0]['id']);
						$has_connected_clients = User2Client::getuserclients($userarray[0]['id']);
						
						if($userarray[0]['clientid']>0)
						{
							$clnt = Doctrine::getTable('Client')->find($userarray[0]['clientid']);
							$clntarray = $clnt->toArray();
								
							if($clntarray['isactive']==1)
							{
								$this->view->error_message = $this->view->error_message = $this->view->translate('account-supend-error');
								$userarray[0]['isactive'] = 1;
							}

							if($userarray[0]['isactive']==0)
							{
								$setses = Doctrine::getTable('User')->find($userarray[0]['id']);
								$setses->sessionid = session_id();
								$setses->logintime = date("Y-m-d H:i:s");
								$setses->save();
									
								$logininfo= new Zend_Session_Namespace('Login_Info');
								$logininfo->userid = $userarray[0]['id'];
								$logininfo->groupid = $userarray[0]['groupid'];
								$logininfo->clientid = $userarray[0]['clientid'];
								$logininfo->setlater=0;
								$logininfo->parentid = $userarray[0]['parentid'];
								$logininfo->username = $userarray[0]['username'];
								$logininfo->loginclientid = $userarray[0]['clientid'];
								$logininfo->usertype = $userarray[0]['usertype'];
								$logininfo->sca = $userarray[0]['issuperclientadmin']; //set that user is super client admin

								//ISPC-2827 Ancuta 26.03.2021
								$logininfo->isEfaUser = $userarray[0]['efa_user'];
								//--
								
								if($has_duplicates && $has_connected_clients){
									$logininfo->multiple_clients = "1"; //this is user for regular user connected to multiple clients
								} else{
									$logininfo->multiple_clients = "0"; 
								}
								$logininfo->showinfo = "";
									
								$grp = new Usergroup();
								$grparr = $grp->getUserGroupData($userarray[0]['groupid']);
									
									
									
								if($userarray[0]['clientid']>0 && $userarray[0]['usertype']!='SA')
								{
									$client = Doctrine_Query::create()
									->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
											AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
											,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone,AES_DECRYPT(fileupoadpass,'".Zend_Registry::get('salt')."') as fileupoadpass")
											->from('Client')
											->where('id='.$userarray[0]['clientid']);
									$clientexec = $client->execute();
									$clientarray = $clientexec->toArray();
									$logininfo= new Zend_Session_Namespace('Login_Info');
									$logininfo->clientname = $clientarray[0]['client_name'];
									$logininfo->filepass = $clientarray[0]['fileupoadpass'];
									
									//ISPC-2827 Ancuta 26.03.2021
									$logininfo->isEfaClient = $clientarray[0]['efa_client'];
									//-- 
									if(!empty($clientarray[0]['inactivetime'])) {
										$inactivetime = $clientarray[0]['inactivetime'];
									} else {
										$inactivetime = '100'; //1000 minutes
									}
								
									$logininfo->inactivetime = $inactivetime;

									if($clientarray[0]['maintainance']>0)
									{
										$this->_redirect(APP_BASE."error/undermaintainance");
									}
										
								} else {
									$logininfo->inactivetime = '100';
								}

								$logininfo->lastactive = time();
									
								if(strlen($_SESSION['logouturl'])>0)
								{
									$this->_redirect($_SESSION['logouturl']);
								}else{
									if($_SERVER['HTTP_REFERER'])
									{
											
										if(Zend_Controller_Front::getInstance()->getRequest()->getActionName()=='index')
										{
										    if (ISPC_WEBSITE_VIEW_VERSION == 'mobile'
										        && ! $this->getInvokeArg('bootstrap')->getResource('Useragent')->getDevice()->getFeature('is_tablet'))
										    {
										        $this->redirect(APP_BASE . "patient/patientoveralllist", array("exit"=>true));
										    } else {
    											$this->_redirect(APP_BASE."overview/overview"); 
										    }
										}else
										{
											$this->_redirect($_SERVER['HTTP_REFERER']);
										}
											
									}else{
										$this->_redirect(APP_BASE."overview/overview");
									}
										
								}
									
							}else{
//  								if(stripos($_SERVER['SERVER_NAME'], 'ispc') !== false) {
 								if( $live ) {
								    $this->_redirect(APP_BASE.'logout/');
								} else {
    								$this->view->error_message .= $this->view->translate('inactivemsg');
								}
							}
						}
					}
					//TODO-3499 Ancuta+Alex 06.10.2020
					else
					{
					    if( $live ) {
					        $this->_redirect(APP_BASE.'logout/');
					    } else {
					        $this->view->error_message .= $this->view->translate('inactivemsg');
					    }
					}
					// -- 
					
				} else {
				    
				    if(!$ldap)
				    {
    					/*********************GOETTINGEN COMMENT*************************/
//     				    if(stripos($_SERVER['SERVER_NAME'], 'ispc') !== false) {
				        if( $live ) {
    				        $this->_redirect(APP_BASE.'logout/');
    				    } else {
        					$this->view->error_message = $this->view->translate('login-error');
    				    }
    				    /*********************GOETTINGEN COMMENT*************************/
				    } 
				    else
				    {
    				    /*********************GOETTINGEN UNCOMMENT*************************/
    				    $this->view->error_message = $this->view->translate('login-error');
    				    
    				    /*********************GOETTINGEN UNCOMMENT*************************/
				    }
				}
				
			} else {
			    
			    if(!$ldap)
			    {
    				/*********************GOETTINGEN COMMENT*************************/
//     				if(stripos($_SERVER['SERVER_NAME'], 'ispc') !== false) {
    				if( $live ) {
    					$this->_redirect(APP_BASE.'logout/');
    				}
    				//$this->_redirect("https://www.ispc-login.de/logout/");
    				/*********************GOETTINGEN COMMENT*************************/
			    }
			}
		}
	}

	public function logoutAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
			
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$setses = Doctrine_Query::create()
		->update('User')
		->set("sessionid", "?" , '')
		->where("sessionid = ? AND id = ?", array(session_id(), $logininfo->userid))
		->execute();

		Zend_Session::namespaceUnset('Login_Info');
		
		Zend_Session::namespaceUnset('Navigation_Menus');
		
		Zend_Session::destroy( true );
		
		$_SESSION = array();
		unset($_SESSION);
		session_destroy();
		
		$past = time() - 3600;
		foreach ( $_COOKIE as $key => $value )
		{
			setcookie( $key, $value, $past, '/' );
		}
		setcookie('CmdCookie',  'logout',  0, '/', ini_get('session.cookie_domain'),  false,  true);
		
		$this->_redirect(APP_BASE);
		//$this->_redirect("https://www.ispc-login.de/");
		
	}


	/**
	 * 12.03.2018
	 * hardCoded, can be removed after serve move has finished
	 * this was not removed... it remains here
	 * it is used if you want to kick-out all nonSA's to a blank-text page 
	 * text is hardcoded in IndexController::logoutmaintenanceAction, logic is Pms_Plugin_Acl
	 * you must set in the .ini => serverMove.redirectUser = 1
	 */
	public function logoutmaintenanceAction()
	{
	 
	    if (Zend_Registry::isRegistered('serverMove') && ($serverMove = Zend_Registry::get('serverMove'))) {
	    
    	    if (empty($serverMove) || ! $serverMove['redirectUser']) {
    	        $this->_redirect(APP_BASE);
    	        return;
    	    }
	    }
	    
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->disableLayout();
	
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    $setses = Doctrine_Query::create()
	    ->update('User')
	    ->set("sessionid", "?" , '')
	    ->where("sessionid = ? AND id = ?", array(session_id(), $logininfo->userid))
	    ->execute();
	
	    Zend_Session::namespaceUnset('Login_Info');
	    Zend_Session::destroy( true );
	
	    $_SESSION = array();
	    unset($_SESSION);
	    session_destroy();
	
	    $past = time() - 3600;
	    foreach ( $_COOKIE as $key => $value )
	    {
	        setcookie( $key, $value, $past, '/' );
	    }
	    setcookie('CmdCookie',  'logout',  0, '/', ini_get('session.cookie_domain'),  false,  true);
	
	   
	    
	    $APP_BASE = APP_BASE;
	    $RES_FILE_PATH = RES_FILE_PATH;
    

$message = <<<EOT
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>ISPC General Server Update</title>
		<base href="{$APP_BASE}" />
    		<!--
            <script> 
                setInterval(function(){  window.location.reload(true); }, 30000);
            </script>
            -->
            <style>
                .maintenance_alert_error {
                    border: 1px solid;
                    font-size: 13px;
		            font-family:Arial, Helvetica, sans-serif;
                    background-repeat: no-repeat;
                    background-position: 10px center;
                    color: #D8000C;
                    background-color: #FFBABA;
                    background-image: url(data:image/png;base64,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);           
                    font-size: 20px; 
                    width: 50%; 
                    height:auto; 
                    padding: 50px;  
                    background-position: 10px 50px; margin:10px auto;
	           }
            </style>
	</head>
	<body style="background:none; padding:50px">
    	<div class="maintenance_alert_error">
            <strong>ACHTUNG: Server Wartung </strong><br/>
            <hr>
            <br/>
            Wir aktualisieren gerade unsere Server um schneller und besser zu werden.
            <br/>
            <br/>
            Bitte loggen Sie sich aus bis die Wartung abgeschlossen wurde.
        </div>
        	
	</body>
</html>
EOT;

	    die($message);
	    
	    return;
	    
	    
	    
	
	}
}

?>