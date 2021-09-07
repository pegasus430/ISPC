<?php

	Doctrine_Manager::getInstance()->bindComponent('OrgSteps', 'SYSDAT');

	class OrgSteps extends BaseOrgSteps {

		public function get_paths_steps($client_paths, $only_shortcuts = false)
		{
// 			$client_paths_ids[] = '999999999';
			$client_paths_ids = array();
			if($client_paths)
			{
				foreach($client_paths as $k_path => $v_path)
				{
					$client_paths_ids[] = $v_path['id'];
				}
			}
			
			if (empty($client_paths_ids)){
				return false;
			}
			
			$res = Doctrine_Query::create()
				->select('*')
				->from('OrgSteps')
				->whereIn('path', $client_paths_ids)
				->andWhere('isdelete = "0"')
				->orderBy('order ASC');
			$res_arr = $res->fetchArray();

			foreach($res_arr as $k_res => $v_res)
			{
				if($only_shortcuts)
				{
					$all_steps['shortcuts'][$v_res['id']] = $v_res['shortcut'];
					$all_steps['shortcuts_details'][$v_res['shortcut']] = $v_res;
				}
				else
				{
					$all_steps[$v_res['id']] = $v_res;
				}
			}

			if($all_steps)
			{
				return $all_steps;
			}
			else
			{
				return false;
			}
		}

		public function get_clients_paths_steps($client_id_str, $only_shortcuts = false)
		{
			$org_query = Doctrine_Query::create()
				->select('*,p.client')
				->from('OrgSteps s')
				->where('isdelete=0');
			$org_query->leftJoin("s.OrgPaths p");
			$org_query->andWhere('p.client  IN (' . substr($client_id_str, 0, -1) . ') ');
			$org_array = $org_query->fetchArray();

			foreach($org_array as $k_res => $v_res)
			{
				if($only_shortcuts)
				{
					$all_steps[$v_res['OrgPaths']['client']]['shortcuts'][$v_res['id']] = $v_res['shortcut'];
					$all_steps[$v_res['OrgPaths']['client']]['shortcuts_details'][$v_res['shortcut']] = $v_res;
				}
				else
				{
					$all_steps[$v_res['OrgPaths']['client']][$v_res['id']] = $v_res;
				}
			}

			if($all_steps)
			{
				return $all_steps;
			}
			else
			{
				return false;
			}
		}

		public function get_paths_steps_multiclient($client_paths, $only_shortcuts = false)
		{
// 			$client_paths_ids[] = '999999999';
			$client_paths_ids = array();
			if($client_paths)
			{
				foreach($client_paths as $k_path => $v_path_arr)
				{
					foreach($v_path_arr as $k_v_path => $v_path)
					{
						$client_paths_ids[] = $v_path['id'];
						$client_paths_details[$v_path['id']] = $v_path;
					}
				}
			}

			if (empty($client_paths_ids)){
				return false;
			}
				
			
			$res = Doctrine_Query::create()
				->select('*')
				->from('OrgSteps')
				->whereIn('path', $client_paths_ids)
				->andWhere('isdelete = "0"')
				->orderBy('order ASC');
			$res_arr = $res->fetchArray();

			foreach($res_arr as $k_res => $v_res)
			{
				if($only_shortcuts)
				{
					$all_steps['shortcuts'] [$client_paths_details[$v_res['path']]['client']] [$v_res['id']] = $v_res['shortcut'];

					$all_steps['shortcuts_details'][$client_paths_details[$v_res['path']]['client']][$v_res['shortcut']] = $v_res;
				}
				else
				{
					$all_steps[$client_paths_details[$v_res['path']]['client']][$v_res['id']] = $v_res;
				}
			}

			if($all_steps)
			{
				return $all_steps;
			}
			else
			{
				return false;
			}
		}

	}

?>