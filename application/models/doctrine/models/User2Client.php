<?php

	Doctrine_Manager::getInstance()->bindComponent('User2Client', 'SYSDAT');

	class User2Client extends BaseUser2Client {

		public function getuserclients($user_id = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			
			if(!$user_id){
				$user_id = $logininfo->userid;
				$user_details = User::getUsersDetails($logininfo->userid);			
				$user_array = array($user_id,$user_details [$user_id]['parentid']);				
			} else{
				$user_array = array($user_id);				
			}
			
			$clients = Doctrine_Query::create()
				->select('client')
				->from('User2Client s')
				->leftJoin('s.User u')
				->whereIn('user',$user_array )
				->andWhere('s.isdelete = 0 ');
			$clients_arr = $clients->fetchArray();

			if( !empty($clients_arr) ) {
				return $clients_arr;
			} else{
				return false;
			}
			
			
		}
	}
?>