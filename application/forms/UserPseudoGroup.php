<?php

	require_once("Pms/Form.php");

	class Application_Form_UserPseudoGroup extends Pms_Form {

		public function validate($post) {
			$error = 0;
			$val = new Pms_Validation();
			$Tr = new Zend_View_Helper_Translate();
			if (empty($post['servicesname'])) {
				$this->error_message['servicesname'] = $Tr->translate('entername');
				$error = 1;
			}
			if ($error == 0) {
				return true;
			}
		
			return false;
		}
		
		public function InsertData($post) {
		
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$user = new UserPseudoGroup();
			$user->servicesname = $post['servicesname'];
			$user->phone = $post['phone'];
			$user->fax= $post['fax'];
			$user->mobile= $post['mobile'];
			$user->email= $post['email'];
			$user->makes_visits = $post['makes_visits'];//ispc-1533, ispc-1855
					
			if ($post['clientid'] > 0) {
				$user ->clientid = $post['clientid'];
			} else {
				$user->clientid = $logininfo->clientid;
			}
		
			$user->save();
			return $user->id;
		}
		
		public function UpdateData($post) {
			$user = Doctrine::getTable('UserPseudoGroup')->find($_GET['id']);
			$user->servicesname = $post['servicesname'];
			$user->phone = $post['phone'];
			$user->fax= $post['fax'];
			$user->mobile= $post['mobile'];
			$user->email= $post['email'];
			$user->makes_visits = $post['makes_visits'];//ispc-1533, ispc-1855 
					
			if ($post['clientid'] > 0) {
				$user ->clientid = $post['clientid'];
			} else {
				$user->clientid = $logininfo->clientid;
			}
			$user->save();
		}

	}

?>