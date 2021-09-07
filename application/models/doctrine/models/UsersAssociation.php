<?php

	Doctrine_Manager::getInstance()->bindComponent('UsersAssociation', 'SYSDAT');

	class UsersAssociation extends BaseUsersAssociation {

		public function get_associated_user($user)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$users = Doctrine_Query::create()
				->select('*')
				->from('UsersAssociation')
				->where('user ="' . $user . '"');
			$users_arr = $users->fetchArray();

			foreach($users_arr as $data)
			{
				$users_array[$data['user']] = $data['associate'];
			}
			return $users_array;
		}

		public function get_associated_user_multiple($userids,$associate = false)
		{
			$users = Doctrine_Query::create()
				->select('*')
				->from('UsersAssociation')
				->whereIn('user', $userids);
				if($associate){
			     $users ->andwhereIn('associate',$associate);
			    }
			$users_arr = $users->fetchArray();

			foreach($users_arr as $data)
			{
				$users_array[$data['user']] = $data['associate'];
			}

			return $users_array;
		}

	}

?>