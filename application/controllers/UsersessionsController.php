<?php

class UsersessionsController extends Zend_Controller_Action {

	public function init() {

	}

	public function checkAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$sess = Doctrine_Query::create()
		->select('*')
		->from('UserSessions')
		->where('session="'.session_id().'" AND user="'.$logininfo->userid.'"');
		$session = $sess->fetchArray();
		$lastaction = strtotime($session[0]['lastaction']);
		$passed = time() - $lastaction;
		$inactive = $logininfo->inactivetime;
		$check['lastaction'] = $lastaction;
		$check['lastaction-h'] = $session[0]['lastaction'];
		$check['passed'] = $passed;
		$check['inactivetime'] = $inactive;

		if($passed >= ($inactive * 60) && !empty($inactive) && $passed > 0) {
			$check['result'] = 'INACTIVE';
		} else {
			$check['result'] = 'ACTIVE';
		}
		
		echo json_encode($check);
		exit;
	}
	
	public function checknewAction() {
		//this shouldn't be accessed directly in browser
		if ( $this->getRequest()->isXmlHttpRequest()) {
		//if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
			//header("HTTP/1.0 404 Not Found"); exit;
			$logininfo = new Zend_Session_Namespace ( 'Login_Info' );

			$passed = time() - ($logininfo->lastactive);
			$inactive = intval($logininfo->inactivetime);

			if(empty($inactive) || $inactive < 30) {
				$inactive = '30';
			}

// 			if($logininfo->userid == 1830 || $logininfo->userid == 293 || $logininfo->userid == 1) {
// 				//$inactive = '1';
// 			}
			
// 			if($logininfo->userid == 338 && $logininfo->clientid == "32") {
// 			    $inactive = 0.5;
// 			}
			
			
			//$inactive = 0.5;

			$check['lastaction'] = $logininfo->lastactive;
			$check['lastaction-h'] = date('Y-m-d H:i:s',$logininfo->lastactive);
			$check['passed'] = $passed;
			$check['inactivetime'] = $inactive;
			$check['client_changed'] = "0"; 
				
				
			if($passed >= ($inactive * 60) && $passed > 0) {
				if($passed - ($inactive * 60) <= 60) { //display warning for 60 seconds
					$check['result'] = 'BEFOREINACTIVE';
				} else {
					$check['result'] = 'INACTIVE';

						

						
					$message .= "\n\n==========================*********START*********============================ \n\n\n";
					$message .= "\n".$check['lastaction-h'].';'.$inactive.';'.$logininfo->userid.';'.$_SERVER['REQUEST_URI'].';'.$_SERVER['HTTP_REFERER']."\n\n\n";
					$message .= "\n\n======================================================================== \n\n";
					$message .= serialize($_SERVER);
					$message .= "\n\n======================================================================== \n\n";
					$message .= serialize($_SESSION);
					$message .= "\n\n======================================================================== \n\n";
					$message .= serialize($_COOKIE);
					$message .= "\n\n\n=======================*********END*********============================ \n\n";

					$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/sessions-2015.log');
					$log = new Zend_Log($writer);
					if($log)
					{
						$log->crit($message);
					}
						
					//force logout here
					/*setcookie("IGUser",false,time() - 60*60,"/",".ispc-login.de", 0, 1);
					setcookie("IGSession",false,time() - 60*60,"/",".ispc-login.de", 0, 1);
					setcookie("IGSessionKey",false,time() - 60*60,"/",".ispc-login.de", 0, 1);
					setcookie("IGAuthValidationServiceUID",false,time() - 60*60,"/",".ispc-login.de", 0, 1);

					$_SESSION = array();
					unset($_SESSION);
					session_destroy();*/
					
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
						
				}
			} else {
				$check['result'] = 'ACTIVE';
			}
			
			//ispc-1901 - verify if User changes client in another tab (2tabs opened with different clients) 
			if ( ! empty($_REQUEST["idcidpd"]) ) {
				
				$oldCID = Pms_Uuid::decrypt($_REQUEST["idcidpd"]);
								
				if ( $oldCID != $logininfo->clientid ) {
				
					$check['client_changed'] = '1'; // user has multiple tabs opened, with different clients 	
					$oldCID_details = Client::getClientDataByid($oldCID);
					if (is_array($oldCID_details) && is_array($oldCID_details[0])) {
						$oldCID_details = $oldCID_details[0];
						$check['client_changed_text'] = sprintf( $this->view->translate('client_changed_text') , $oldCID_details['client_name']);
					}
				}
			}


			echo json_encode($check);
		} else {
			$this->_redirect(APP_BASE.'overview/overview');
		}
		exit;
	}

	public function refreshAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$logininfo->lastactive = time();
		
		echo json_encode(1);
		exit;
	}

}

?>