<?php

	Doctrine_Manager::getInstance()->bindComponent('UserCourseFilters', 'SYSDAT');

	class UserCourseFilters extends BaseUserCourseFilters {

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
				->from('UserCourseFilters')
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