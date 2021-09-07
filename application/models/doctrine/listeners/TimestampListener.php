<?php

	class TimestampListener extends Doctrine_Record_Listener {

		public function preInsert(Doctrine_Event $event)
		{
			if(array_key_exists('create_date',$event->getInvoker()->getModified())){
				// if someone wants to change create_date explicitly, let him do
				// used in SystemsSync
				// return;
			} else {
				$event->getInvoker()->create_date = date("Y-m-d H:i:s", time());
			}

			$logininfo = new Zend_Session_Namespace('Login_Info');

			//if there is no session, prevent Doctrine constrain error, useful for crons

			if(array_key_exists('create_user',$event->getInvoker()->getModified())) {
			    $create_user = $event->getInvoker()->create_user ;
			} else {
			    
    			if(empty($logininfo->userid)) {
    				$create_user = '0';
    			} else {
    				$create_user = $logininfo->userid;
    			}
			} 
			$event->getInvoker()->create_user = $create_user;
		}

		public function preUpdate(Doctrine_Event $event)
		{
			if(array_key_exists('change_date',$event->getInvoker()->getModified())){
				// if someone wants to change change_date explicitly, let him do
				// used in SystemsSync
				// return;
			} else {
				$event->getInvoker()->change_date = date("Y-m-d H:i:s", time());
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$event->getInvoker()->change_user = $logininfo->userid;
			
		}

		/**
		 * (non-PHPdoc)
		 * @see Doctrine_Record_Listener::preDqlUpdate()
		 * August 2017 @claudiu
		 * rc0.1
		 */
		public function preDqlUpdate(Doctrine_Event $event)
		{

			$params = $event->getParams();
			
			$query = $event->getQuery();
			
			if ( ! $query->contains("change_date")) {
				
				$query->set($params['alias'] . '.' . 'change_date', '?', date("Y-m-d H:i:s", time()));	
			}
			
			
			if ( ! $query->contains("change_user") ) {
				
				$logininfo = new Zend_Session_Namespace('Login_Info');
				
				$query->set( $params['alias'] . '.' . 'change_user', '?', (int)$logininfo->userid);	
			}

		}
		
		
	}

?>