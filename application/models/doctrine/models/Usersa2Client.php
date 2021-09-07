<?php

	Doctrine_Manager::getInstance()->bindComponent('Usersa2Client', 'SYSDAT');

	class Usersa2Client extends BaseUsersa2Client {

		public function checkSA($onlysa = false, $paranoid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($onlysa == true)
			{
				if($logininfo->usertype == "SA")
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				if($logininfo->usertype == "SA")
				{
					return true;
				}
				elseif($logininfo->sca == '1')
				{
					if($paranoid == true)
					{
						$user = Doctrine_Query::create()
							->select('*')
							->from('Usersa2Client s')
							->leftJoin('s.User u')
							->where('s.user = "' . $logininfo->userid . '" AND s.client="' . $logininfo->clientid . '"');
						$user_check = $user->fetchArray();
					}
					else
					{
						return true;
					}
				}
				else
				{
					return false;
				}
			}

			return false;
		}

		public function getusersaclients($user_id = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if(!$user_id){
				$user_array = array($logininfo->userid);
			} 
			else 
			{
				if(!is_array($user_id)){
					$user_array = array($user_id);
				}	
			}
			if(empty($user_array )){
				$user_array[] = "XXXXXX";
			}
			
			
			$clients = Doctrine_Query::create()
				->select('client')
				->from('Usersa2Client s')
				->leftJoin('s.User u')
// 				->where('s.user="' . $user_id . '"');
				->whereIn('s.user',$user_array);
			$clients_arr = $clients->fetchArray();

			return $clients_arr;
		}

		public function getusersaclients_multiple($user_array)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			
			if(!is_array($user_array)){
				$user_array = array($user_array);
			}	
			if(empty($user_array )){
				$user_array[] = "XXXXXX";
			}
			
			$clients = Doctrine_Query::create()
				->select('client,user')
				->from('Usersa2Client s')
				->whereIn('s.user',$user_array);
			$clients_arr = $clients->fetchArray();

			foreach($clients_arr as $k=>$c_details){
				$connected[$c_details['user']][] = $c_details['client']; 
			}
			
			return $connected;
		}

	}

?>