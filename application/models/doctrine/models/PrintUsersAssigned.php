<?php

	Doctrine_Manager::getInstance()->bindComponent('PrintUsersAssigned', 'SYSDAT');

	class PrintUsersAssigned extends BasePrintUsersAssigned {

		public function get_receipt_assigned_users($client = false, $receipts = false)
		{
			if($client > '0')
			{
				if($receipts !== false)
				{
					if(is_array($receipts))
					{
						$receipts_ids = $receipts;
					}
					else
					{
						$receipts_ids = array($receipts);
					}
				}

				$query = Doctrine_Query::create()
					->select('*')
					->from('PrintUsersAssigned')
					->where('isdelete = "0"')
					->andWhere('client = "' . $client . '"');
				if($receipts !== false && $receipts_ids)
				{
					$query->andWhereIn('receipt', $receipts_ids);
				}
				$query_res = $query->fetchArray();

				if($query_res)
				{
					foreach($query_res as $k_q => $v_q)
					{
						$assigned_users[$v_q['receipt']][] = $v_q['user'];
					}

					return $assigned_users;
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

		public function check_assigned_users($client = false, $users = false)
		{

			if($client && $users)
			{
				if(is_array($users))
				{
					$users_ids = $users;
				}
				else
				{
					$users_ids = array($users);
				}

				if(empty($users_ids))
				{
					$users_ids[] = '999999999999';
				}


				$query = Doctrine_Query::create()
					->select('*')
					->from('PrintUsersAssigned')
					->where('isdelete = "0"')
					->andWhere('client = "' . $client . '"')
					->andWhereIn('user', $users_ids);
				$q_res = $query->fetchArray();

				if($q_res)
				{
					foreach($q_res as $k_res => $v_res)
					{
						$assigned_users[] = $v_res['user'];
					}

					//return assigned users ids
					return $assigned_users;
				}
			}
		}

	}

?>