<?php

	Doctrine_Manager::getInstance()->bindComponent('GroupAssociatedClients', 'SYSDAT');

	class GroupAssociatedClients extends BaseGroupAssociatedClients {

		function clients_group_add($clients, $group)
		{
			//add delete here //DONE
			if(!empty($clients) && !empty($group))
			{
				$this->client_groups_delete($group);

				foreach($clients as $k_client => $v_client)
				{
					$a_clients = new GroupAssociatedClients();
					$a_clients->group_id = $group;
					$a_clients->client = $v_client;
					$a_clients->status = "0";
					$a_clients->save();
				}

				return $a_clients->id;
			}
		}

		function clients_groups_get($group)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('GroupAssociatedClients')
				->where("status = '0'");

			if(is_array($group))
			{
				$q->andWhereIn('`group_id`', $group);
			}
			else
			{
				$q->andWhere('`group_id` = "' . $group . '"');
			}

			$q->orderby('id ASC');
			$res = $q->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$results[$v_res['group_id']][] = $v_res['client'];
				}

				return $results;
			}
			else
			{
				return false;
			}
		}

		function client_groups_delete($group)
		{
			$q = Doctrine_Query::create()
				->delete('GroupAssociatedClients')
				->where('group_id ="' . $group . '"');
			$q->execute();
		}

		function clients_group_set_delete($group)
		{
			$cl_group = Doctrine_Query::create()
				->update("GroupAssociatedClients")
				->set('status', "1")
				->where("group_id='" . $group . "'");
			$cl_group->execute();

			return $cl_group;
		}

		function associated_clients_get($client, $grouped = false)
		{
			$qq = Doctrine_Query::create()
				->select('group_id')
				->from('GroupAssociatedClients')
				->where('client = "' . $client . '"')
				->andWhere('status="0"');
			$res_subquery = $qq->fetchArray();

			$sub_query_results[] = '999999999';
			foreach($res_subquery as $k_res => $v_res)
			{
				$sub_query_results[] = $v_res['group_id'];
			}

			$q = Doctrine_Query::create()
				->select('*')
				->from('GroupAssociatedClients')
				->whereIn('group_id', $sub_query_results)
				->andWhere('client != "' . $client . '"')
				->andWhere('status="0"');
			$res = $q->fetchArray();

			if($res)
			{
				if($grouped)
				{
					foreach($res as $k_result => $v_result)
					{
						$results[$v_result['group_id']][$v_result['client']] = $v_result;
					}

					return $results;
				}
				else
				{
					return $res;
				}
			}
		}
	}

?>