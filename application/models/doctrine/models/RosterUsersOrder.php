<?php

	Doctrine_Manager::getInstance()->bindComponent('RosterUsersOrder', 'SYSDAT');

	class RosterUsersOrder extends BaseRosterUsersOrder {

		public function get_order($clientid, $userid)
		{
			$order = Doctrine_Query::create()
				->select('*')
				->from('RosterUsersOrder')
				->where('isdelete="0"')
//				->andWhere('userid = "'.$userid.'"')
				->andWhere('clientid = "'.$clientid.'"')
				->orderBy('id ASC');
			$order_res = $order->fetchArray();
			
			
			foreach($order_res as $k_res => $v_res)
			{
				$order_result['groups_order'][] = $v_res['group_sort'];
				$order_result['groups_order'] = array_values(array_unique($order_result['groups_order']));
				
				$order_result['users_order'][$v_res['group_sort']][$v_res['sort_order']] = $v_res['user_sort'];
				ksort($order_result['users_order'][$v_res['group_sort']]);
			}

			if($order_result)
			{
				return $order_result;
			}
			else
			{
				return false;
			}
		}

	}

?>