<?php

	Doctrine_Manager::getInstance()->bindComponent('UserPageResults', 'SYSDAT');

	class UserPageResults extends BaseUserPageResults {

		public function get_page_result($user_id = false,$client=false,$page = false,$tabname=false)
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
				->from('UserPageResults')
				->whereIn('user',$user_array )
				->andWhere('client = '.$client)
				->andWhere('isdelete = 0 ')
				->andWhere('page = "'.$page.'" ');
			if($tabname!==false){
				$clients->andWhere(' tab = "'.$tabname.'" ');
			}
			$filter_arr = $clients->fetchArray();

			if( !empty($filter_arr) ) {
				return $filter_arr;
			} else{
				return false;
			}
			
			
		}
	}
?>