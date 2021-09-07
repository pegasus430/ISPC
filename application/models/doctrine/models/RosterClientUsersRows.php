<?php

	Doctrine_Manager::getInstance()->bindComponent('RosterClientUsersRows', 'SYSDAT');

	class RosterClientUsersRows extends BaseRosterClientUsersRows {

		public function get_client_users_rows($clientid, $userid = false)
		{
			$users_rows = Doctrine_Query::create()
				->select('*')
				->from('RosterClientUsersRows')
				->where('isdelete="0"')
//				->andWhere('userid = "'.$userid.'"')
				->andWhere('clientid = "'.$clientid.'"')
				->orderBy('id ASC');
			$users_rows_res = $users_rows->fetchArray();

			if($users_rows_res)
			{
				$users_rows_ammount = array();
				foreach($users_rows_res as $k_res => $v_res)
				{
					$users_rows_ammount[$v_res['rows_user']] = $v_res['amount'];
				}
				
				return $users_rows_ammount;
			}
			else
			{
				return false;
			}
			

		}

	}

?>