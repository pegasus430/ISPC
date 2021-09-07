<?php

	Doctrine_Manager::getInstance()->bindComponent('UserSessions', 'SYSDAT');

	class UserSessions extends BaseUserSessions {

		public function update_session()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			//update session record
			$usersess = new UserSessions();
			$usersess->findOrCreateOneBy('session', session_id(), array(
			    'user' => $logininfo->userid , 
			    'lastaction' => date('Y-m-d H:i:s')));
		}
		/*
		public function update_session()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		
		    //delete session record
		
		    $sess = Doctrine_Query::create()
		    ->delete('UserSessions')
		    ->where('session="' . session_id() . '"');
		    $sess->execute();
		
		    //update session record
		
		    $usersess = new UserSessions();
		    $usersess->session = session_id();
		    $usersess->user = $logininfo->userid;
		    $usersess->lastaction = date('Y-m-d H:i:s');
		    $usersess->save();
		}
		*/

	}

?>