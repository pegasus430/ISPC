<?php

	Doctrine_Manager::getInstance()->bindComponent('PseudoUsers', 'SYSDAT');

	class PseudoUsers extends BasePseudoUsers {

		public function get_pseudo_user_data($client = false, $pseudo_user = false)
		{
			if($client && $pseudo_user)
			{
				$sel_q = Doctrine_Query::create()
					->select("*")
					->from('PseudoUsers')
					->where('client = "' . $client . '"')
					->andWhere('id = "' . $pseudo_user . '"')
					->andWhere('isdelete = "0"');
				$sel_res = $sel_q->fetchArray();

				if($sel_res)
				{
					return $sel_res[0];
				}
				else
				{
					return false;
				}
			}
			else
			{
				//get all pseudousers
				$sel_q = Doctrine_Query::create()
					->select("*")
					->from('PseudoUsers')
					->andWhere('isdelete = "0"');
				$sel_res = $sel_q->fetchArray();

				if($sel_res)
				{
					foreach($sel_res as $k_psu => $v_psu)
					{
						$pseudo_users[$v_psu['client']][$v_psu['id']] = trim(rtrim($v_psu['title'] . ' ' . $v_psu['first_name'] . ' ' . $v_psu['last_name']));
//						$pseudo_users[$v_psu['client']][$v_psu['user']][$v_psu['id']]['id'] = $v_psu['id'];
//						$pseudo_users[$v_psu['client']][$v_psu['user']][$v_psu['id']]['name'] = trim(rtrim($v_psu['title'] . ' ' . $v_psu['last_name'] . ' ' . $v_psu['first_name']));
//						$pseudo_users[$v_psu['client']][$v_psu['user']][$v_psu['id']]['shortname'] = trim(rtrim($v_psu['shortname']));
					}

					return $pseudo_users;
				}
			}
		}
		
		public function get_pseudo_users($client = false, $pseudo_user = false)
		{
			if($client && $pseudo_user)
			{
				$sel_q = Doctrine_Query::create()
					->select("*")
					->from('PseudoUsers')
					->where('client = "' . $client . '"')
					->andWhere('user = "' . $pseudo_user . '"')
					->andWhere('isdelete = "0"');
				$sel_res = $sel_q->fetchArray();

				if($sel_res)
				{
					foreach($sel_res as $k_psu => $v_psu)
					{
						$pseudo_users[$v_psu['id']]['name'] = trim(rtrim($v_psu['title'] . ' ' . $v_psu['first_name'] . ' ' . $v_psu['last_name']));
						$pseudo_users[$v_psu['id']]['shortname'] = trim(rtrim($v_psu['shortname']));
						
						if($v_psu['ishidden'] == "1")
						{
							$pseudo_users[$v_psu['id']]['status'] = "old";
						}
					}

					return $pseudo_users;
					
				}
				else
				{
					return false;
				}
			}
		}

	}

?>