<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationChangeUsers', 'SYSDAT');

	class MedicationChangeUsers extends BaseMedicationChangeUsers {

		public static function get_medication_change_users($client = false, $minimal = true)
		{
			if($client)
			{
				$sel = Doctrine_Query::create()
					->select('*')
					->from('MedicationChangeUsers')
					->where('isdelete="0"')
					->andWhere('client = ? ' , $client );
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
// 						flowerpower
// 						//get print users master details
// 						$medication_change_users_ids[] = '999999999999999';
// 						foreach($sel_res as $k_data => $v_data)
// 						{
// 							$medication_change_users_ids[] = $v_data['user'];
// 						}

// 						$users = new User();
// 						$medication_change_users_details = $users->getUsersDetails($medication_change_users_ids);
						
// 						return $medication_change_users_details;
						
						//flowerpower reduced to a one-liner
						return User::getUsersDetails( array_column($sel_res, 'user') );
						
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