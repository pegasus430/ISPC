<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicePriceList', 'SYSDAT');

	class InternalInvoicePriceList extends BaseInternalInvoicePriceList {

		public function get_lists($clientid, $list = NULL)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoicePriceList')
				->where("clientid='" . $clientid . "'")
				->andwhere('isdelete ="0"')
				->orderBy('id ASC');
			$res = $query->fetchArray();

			foreach($res as $k_res => $v_res)
			{
				$return[$v_res['id']] = $v_res;
			}

			return $return;
		}

		public function get_all_price_lists($clientid)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoicePriceList')
				->where("clientid='" . $clientid . "'")
				->andwhere('isdelete ="0"')
				->orderBy('id ASC');
			$res = $query->fetchArray();

			foreach($res as $k_res => $v_res)
			{
				$return[$v_res['id']] = $v_res;
			}

			return $return;
		}

		public function get_last_list($clientid)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoicePriceList')
				->where("clientid='" . $clientid . "'")
				->orderBy('create_date DESC')
				->andwhere('isdelete ="0"')
				->limit('1');
			$res = $query->fetchArray();


			return $res;
		}

		public function check_client_list($clientid, $listid = 0)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoicePriceList')
				->where("clientid='" . $clientid . "'")
				->andWhere('id="' . $listid . '"')
				->andwhere('isdelete ="0"')
				->orderBy('id ASC');
			$res = $query->fetchArray();

			if($res)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function get_period_pricelist($start, $end)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$ii_specific_products = new InternalInvoicesSpecificProducts();
			$ii_actions_products = new InternalInvoicesActionProducts();
			$ii_specific_visits = new InternalInvoicesSpecificVisits();
			$ii_day_products = new InternalInvoicesDayProducts();
			$pm = new PatientMaster();

			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('InternalInvoicePriceList')
				->where('isdelete = "0"')
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			$period_days_array = $pm->getDaysInBetween($start_date, $end_date);

			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
				
				if(empty($list_days[$v_res_period['id']]))
				{
					$list_days[$v_res_period['id']] = array();
				}
					
				$list_days[$v_res_period['id']] = array_merge($list_days[$v_res_period['id']], $pm->getDaysInBetween(date('Y-m-d', strtotime($v_res_period['start'])), date('Y-m-d', strtotime($v_res_period['end']))));
				$list_days[$v_res_period['id']] = array_values(array_unique($list_days[$v_res_period['id']]));
			}
			
			$master_data['lists_days'] = $list_days;
			
			//get lists specific products
			$ii_sp = $ii_specific_products->get_multiple_list_products($returned['list_ids'], $clientid);
			
			//get lists actions products
			$ii_ap = $ii_actions_products->get_multiple_list_products($returned['list_ids'], $clientid);

			//get lists specific visits
			$ii_s_visits = $ii_specific_visits->get_multiple_list_products($returned['list_ids'], $clientid);

			//get lists day products
			$ii_dp = $ii_day_products->get_multiple_list_products($returned['list_ids'], $clientid);

			foreach($ii_sp as $k_sp => $arr_sp)
			{
				$ii_product_ids['sp'][$k_sp] = array_keys($arr_sp);
			}
			
			foreach($ii_ap as $k_ap => $arr_ap)
			{
				$ii_product_ids['ap'][$k_ap] = array_keys($arr_ap);
			}
			
			foreach($ii_s_visits as $k_sp => $arr_sp)
			{
				$ii_product_ids['sv'][$k_sp] = array_keys($arr_sp);
			}

			foreach($ii_dp as $k_dp => $arr_dp)
			{
				$ii_product_ids['dp'][$k_dp] = array_keys($arr_dp);
			}

			//create master array
			foreach($ii_product_ids['sp'] as $k_list => $product_ids)
			{
				foreach($product_ids as $product)
				{
					$master_data['sp'][$product] = $ii_sp[$k_list][$product];
				}
			}
			
			foreach($ii_product_ids['ap'] as $k_list => $product_ids)
			{
				foreach($product_ids as $product)
				{
					$master_data['ap'][$product] = $ii_ap[$k_list][$product];
				}
			}
			
			foreach($ii_product_ids['sv'] as $k_list => $product_ids)
			{
				foreach($product_ids as $product)
				{
					$master_data['sv'][$product] = $ii_s_visits[$k_list][$product];
				}
			}

			foreach($ii_product_ids['dp'] as $k_list => $product_d_ids)
			{
				foreach($product_d_ids as $product_d)
				{
					$master_data['dp'][$product_d] = $ii_dp[$k_list][$product_d];
				}
			}


			return $master_data;
		}

	}

?>