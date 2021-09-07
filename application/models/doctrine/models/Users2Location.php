<?php

	Doctrine_Manager::getInstance()->bindComponent('Users2Location', 'SYSDAT');

	class Users2Location extends BaseUsers2Location {

		public function get_location_users($locations = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			
			if(is_array($locations))
			{
				$sql_locations_array = $locations;
			}
			else
			{
				$sql_locations_array[] = $locations;
			}
			
			$users2loc = Doctrine_Query::create()
				->select('*')
				->from('Users2Location ul')
				->where('isdelete = 0 ')
				->andwhereIn('location', $sql_locations_array);
			$users2loc_res = $users2loc->fetchArray();

			if(!empty($users2loc_res))
			{
				return $users2loc_res;
			}
			else
			{
				return false;
			}
		}

		
		public function get_user_locations($users = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			
			if(is_array($users))
			{
				$sql_users_array = $users;
			}
			else
			{
				$sql_users_array[] = $users;
			}
			
			$loc2user = Doctrine_Query::create()
				->select('*')
				->from('Users2Location ul')
				->where('isdelete = 0 ');
			if($users){
				$loc2user->andwhereIn('user', $sql_users_array);
			}
				
			$loc2users_res = $loc2user->fetchArray();

			if(!empty($loc2users_res))
			{
				foreach($loc2users_res as $k => $res_data){
					$users_locations[$res_data['user']][] = $res_data['location'];
				}	
				
				return $users_locations;
			}
			else
			{
				return false;
			}
		}

	}

?>