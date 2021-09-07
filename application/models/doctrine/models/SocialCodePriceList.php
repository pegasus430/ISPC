<?php

	Doctrine_Manager::getInstance()->bindComponent('SocialCodePriceList', 'SYSDAT');

	class SocialCodePriceList extends BaseSocialCodePriceList {

		public function get_lists($clientid, $list = 0)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceList')
				->where("clientid='" . $clientid . "'")
				->andwhere(' private = "0"')
				->andwhere('isdelete ="0"')
				->orderBy('id ASC');

			$res = $query->fetchArray();

			//added get group for lists
			$list_ids[] = '99999999999';
			foreach($res as $k => $v)
			{
				$list_ids[] = $v['id'];
			}

			$pricelist_grouped = SocialCodePriceListGrouped::get_pricelist_group($list_ids);


			$group_ids[] = '99999999999';
			foreach($pricelist_grouped as $k_pl_gr => $v_pl_gr)
			{
				$group_ids[] = $v_pl_gr['groupid'];
				$pricelist2group[$v_pl_gr['price_list']] = $v_pl_gr['groupid'];
			}

			$group_details = SocialCodePriceListGroups::get_group_details($group_ids);



			foreach($res as $k_res => $v_res)
			{

				$return[$v_res['id']] = $v_res;
				if(strlen($pricelist2group[$v_res['id']]) > '0')
				{
					$return[$v_res['id']]['pricelist_group'] = $pricelist2group[$v_res['id']];
					$return[$v_res['id']]['pricelist_group_details'] = $group_details[$pricelist2group[$v_res['id']]];
				}
				else
				{
					$return[$v_res['id']]['pricelist_group'] = '0';
				}
			}

			return $return;
		}

		public function get_all_price_lists($clientid)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceList')
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
				->from('SocialCodePriceList')
				->where("clientid='" . $clientid . "'")
				->orderBy('create_date DESC')
				->andwhere('isdelete ="0"')
				->limit('1');

			$res = $query->fetchArray();


			return $res;
		}

		public function check_client_list($clientid, $listid)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceList')
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

		/* --------------------  PRIVATE LIST    ---------------------- */

		public function get_private_lists($clientid, $list = 0)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceList')
				->where("clientid='" . $clientid . "'")
				->andwhere('private = "1" ')
				->andwhere('isdelete ="0"')
				->orderBy('id ASC');

			$res = $query->fetchArray();

			//added get group for lists
			$list_ids[] = '99999999999';
			foreach($res as $k => $v)
			{
				$list_ids[] = $v['id'];
			}

			$pricelist_grouped = SocialCodePriceListGrouped::get_pricelist_group($list_ids);


			$group_ids[] = '99999999999';
			foreach($pricelist_grouped as $k_pl_gr => $v_pl_gr)
			{
				$group_ids[] = $v_pl_gr['groupid'];
				$pricelist2group[$v_pl_gr['price_list']] = $v_pl_gr['groupid'];
			}

			$group_details = SocialCodePriceListGroups::get_group_details($group_ids);



			foreach($res as $k_res => $v_res)
			{

				$return[$v_res['id']] = $v_res;
				if(strlen($pricelist2group[$v_res['id']]) > '0')
				{
					$return[$v_res['id']]['pricelist_group'] = $pricelist2group[$v_res['id']];
					$return[$v_res['id']]['pricelist_group_details'] = $group_details[$pricelist2group[$v_res['id']]];
				}
				else
				{
					$return[$v_res['id']]['pricelist_group'] = '0';
				}
			}

			return $return;
		}

		/* --------------------  PERIOD PRICE LIST    ---------------------- */

		public function get_group_period_pricelist($group = false, $client = false, $period = false, $multiple_price_sheet = false)
		{

			if($group && $client)
			{

				$pricelist_grouped = new SocialCodePriceListGrouped();
				$group_price_lists = $pricelist_grouped->get_group_pricelists($group);

				//dummy control
				$group_price_lists[$group][] = '9999999999';
				$query = Doctrine_Query::create()
					->select("*")
					->from('SocialCodePriceList')
					->where("clientid='" . $client . "'")
					->andWhere('DATE("' . $period['start'] . '") <= `end` ')
					->andWhere('DATE("' . $period['end'] . '") >= `start` ')
					->andWhereIn('id', $group_price_lists[$group])
					->andwhere('isdelete ="0"')
					->orderBy('id ASC');

				if($multiple_price_sheet === false)
				{
					$query->limit('1');
				}
				$res = $query->fetchArray();

				if($res)
				{
					if($multiple_price_sheet === false)
					{
						return $res[0]['id']; //correct period pricesheet
					}
					else
					{

						foreach($res as $k_r => $v_r)
						{
							$price_sheets[] = $v_r['id'];
						}

						return $price_sheets;
					}
				}
				else
				{
					return '0';
				}
			}
		}

	}

?>