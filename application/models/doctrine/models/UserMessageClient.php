<?php

	Doctrine_Manager::getInstance()->bindComponent('UserMessageClient', 'SYSDAT');

	class UserMessageClient extends BaseUserMessageClient {

		public function getUserMessageClientData($userid)
		{
			$get_data = Doctrine_Query::create()
				->select('*')
				->from('UserMessageClient')
				->where('userid = "' . $userid . '"');
			$data_res = $get_data->fetchArray();

			if($data_res)
			{
				foreach($data_res as $k_data => $v_data)
				{
					$user_message_client[$v_data['client']] = $v_data;
				}
				return $user_message_client;
			}
			else
			{
				return false;
			}
		}

		public function getMessageSpecialUsers($clientid)
		{
			$get_data = Doctrine_Query::create()
				->select('*')
				->from('UserMessageClient')
				->where('client = "' . $clientid . '"');
			$data_res = $get_data->fetchArray();

			if($data_res)
			{
				
				$s_user_ids = array();
				foreach($data_res as $k_s_user => $v_s_user)
				{
					$client_special_users[$v_s_user['userid']] = $v_s_user;
					$s_user_ids[] = $v_s_user['userid'];
				}
				
				if( empty($s_user_ids)){
					return false;
				}

				$q_users = Doctrine_Query::create()
					->select('*')
					->from('User')
					->whereIn('id', $s_user_ids)
					->andWhere('isactive=0 and isdelete = 0');
				$q_users_res = $q_users->fetchArray();

				foreach($q_users_res as $k_q_users => $v_q_users)
				{
					$client_special_users_array[$v_q_users['id']] = $client_special_users[$v_q_users['id']];
					$client_special_users_array[$v_q_users['id']]['details'] = $v_q_users;
				}

				return $client_special_users_array;
			}
			else
			{
				return false;
			}
		}

	}

?>