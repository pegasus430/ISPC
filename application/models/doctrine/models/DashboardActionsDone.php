<?php

	Doctrine_Manager::getInstance()->bindComponent('DashboardActionsDone', 'SYSDAT');

	class DashboardActionsDone extends BaseDashboardActionsDone {

		public function getClientDashboardActions($client, $return_only_ids = false,$full_details = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$clist = Doctrine_Query::create()
				->select("*")
				->from('DashboardActionsDone')
				->where('client = "' . $client . '"')
				->andWhere('user = "' . $userid . '"');
			if($full_details)
			{
				$clist->orderBy('create_date ASC');
			}
			$client_excluded_events = $clist->fetchArray();
			if($client_excluded_events)
			{
				foreach($client_excluded_events as $k_excluded => $v_excluded)
				{
					if($return_only_ids)
					{
						$excluded_events[$v_excluded['tabname']][] = $v_excluded['event'];
//						$excluded_events[$v_excluded['tabname']] = array_unique($excluded_events[$v_excluded['tabname']]);
						$excluded_events['excluded_date'][$v_excluded['event']] = $v_excluded['create_date'];
					}
					else
					{
						$excluded_events[$v_excluded['tabname']]['details'][$v_excluded['event']] = $v_excluded;
						$excluded_events[$v_excluded['tabname']]['ids'][] = $v_excluded['event'];
						$excluded_events['user_ids'][] = $v_excluded['create_user'];
						$excluded_events['user_ids'][] = $v_excluded['user'];
//						$excluded_events['user_ids'] = array_values(array_unique($excluded_events['user_ids']));
					}
				}

				foreach($excluded_events as $k_evnt_tabname => $v_evnt)
				{
					$excluded_events[$k_evnt_tabname] = array_values(array_unique($excluded_events[$k_evnt_tabname]));
				}
				
				if($full_details)
				{
					return $client_excluded_events;
				} 
				else
				{
					return $excluded_events;
				}
				
			}
			else
			{
				return false;
			}
		}

	}

?>