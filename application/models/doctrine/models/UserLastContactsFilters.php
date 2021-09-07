<?php

	Doctrine_Manager::getInstance()->bindComponent('UserLastContactsFilters', 'SYSDAT');

	//ISPC-2440 Lore 11.03.2020
	
	class UserLastContactsFilters extends BaseUserLastContactsFilters {

		public function get_user_filter($user_id = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			if(!$user_id){
				$user_id = $logininfo->userid;
				$user_array = array($user_id);				
			} else{
				$user_array = array($user_id);				
			}
			
			$clients = Doctrine_Query::create()
				->select('*')
				->from('UserLastContactsFilters')
				->whereIn('user',$user_array )
				->andWhere('client = '.$logininfo->clientid )
				->andWhere('isdelete = 0 ');
			$filter_arr = $clients->fetchArray();

			if( !empty($filter_arr) ) {
				return $filter_arr;
			} else{
				return false;
			}
			
			
		}
	}
?>