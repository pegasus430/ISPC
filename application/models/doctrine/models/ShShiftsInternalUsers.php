<?php

	Doctrine_Manager::getInstance()->bindComponent('ShShiftsInternalUsers', 'SYSDAT');

	class ShShiftsInternalUsers extends BaseShShiftsInternalUsers {

		public function get_shinternal_users($client = false, $minimal = true)
		{
			if($client)
			{
				$sel = Doctrine_Query::create()
					->select('*')
					->from('ShShiftsInternalUsers')
					->where('isdelete="0"')
					->andWhere('client = "' . $client . '"');
				$sel_res = $sel->fetchArray();

				if($sel_res)
				{
					if($minimal)
					{
						foreach($sel_res as $k_sel => $v_sel)
						{
							$selected_users[] = $v_sel['user'];
						}

						return $selected_users;
					}
					else
					{
						//get print users master details
						$print_users[] = '999999999999999';
						foreach($sel_res as $k_data => $v_data)
						{
							$print_users_ids[] = $v_data['user'];
						}

						$users = new User();
						$print_users_details = $users->getUsersDetails($print_users_ids);

						return $print_users_details;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

	}

?>