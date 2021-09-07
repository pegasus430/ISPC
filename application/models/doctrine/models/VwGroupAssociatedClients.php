<?php

	Doctrine_Manager::getInstance()->bindComponent('VwGroupAssociatedClients', 'SYSDAT');

	class VwGroupAssociatedClients extends BaseVwGroupAssociatedClients {
 
		function clients_groups_get($group)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('VwGroupAssociatedClients')
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
 
		function parent_client_groups_get($group)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('VwGroupAssociatedClients')
				->where("status = '0'")
				->andWhere("parent = '1'");

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

		function associated_clients_get($client,$grouped = false)
		{
			$qq = Doctrine_Query::create()
				->select('group_id')
				->from('VwGroupAssociatedClients')
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
				->from('VwGroupAssociatedClients')
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

		function associated_parent_client($client)
		{
			$qq = Doctrine_Query::create()
				->select('group_id')
				->from('VwGroupAssociatedClients')
				->where('client = "' . $client . '"')
				->andWhere('status="0"');
			$res_subquery = $qq->fetchArray();

			
			if($res_subquery){
			    
        		foreach($res_subquery as $k_res => $v_res)
        		{
        			$sub_query_results[] = $v_res['group_id'];
        		}
        
        		$q = Doctrine_Query::create()
        			->select('*')
        			->from('VwGroupAssociatedClients')
        			->whereIn('group_id', $sub_query_results)
        			->andWhere('status="0"')
        			->andWhere('parent="1"');
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
			else
			{
			    return $res;
			}
			
		}
		
		function connected_parent($client){
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $associated_clients_parent = VwGroupAssociatedClients::associated_parent_client($client);
		    
		    if($associated_clients_parent){
    		    foreach($associated_clients_parent as $k=>$assoc_data){
    		        $connected_parent = $assoc_data['client'];
    		    }
    		    
    		    if($connected_parent && $connected_parent != $logininfo->clientid ){
    		        $clientid = $connected_parent;
    		    } else{
    		        $clientid = $logininfo->clientid;
    		    }
    		    
    		    return $clientid;
		    } else {
		        return $logininfo->clientid;
		    }
		}
		
	}

?>