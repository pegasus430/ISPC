<?php

	Doctrine_Manager::getInstance()->bindComponent('UserVwFilters', 'SYSDAT');

	class UserVwFilters extends BaseUserVwFilters {

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
				->from('UserVwFilters')
				->whereIn('user',$user_array )
				->andWhere('client = '.$logininfo->clientid)
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