<?php

	Doctrine_Manager::getInstance()->bindComponent('SocialCodePriceListGrouped', 'SYSDAT');

	class SocialCodePriceListGrouped extends BaseSocialCodePriceListGrouped {

		public function get_group_pricelists($group)
		{
			$q_res = Doctrine_Query::create()->select('*')->from('SocialCodePriceListGrouped')->where('groupid="' . $group . '"')->fetchArray();

			foreach($q_res as $k_res => $v_res)
			{
				$group_pricelists[$v_res['groupid']][] = $v_res['price_list'];
			}

			return $group_pricelists;
		}

		public function get_pricelist_group($pricelist)
		{
			if(is_array($pricelist))
			{
				$pricelists = $pricelist;
			}
			else
			{
				$pricelists = array($pricelist);
			}

			$q_res = Doctrine_Query::create()->select('*')->from('SocialCodePriceListGrouped')->whereIn('price_list', $pricelists)->fetchArray();

			foreach($q_res as $k_res => $v_res)
			{
				$pricelist_arr[$v_res['id']] = $v_res;
			}

			return $pricelist_arr;
		}

		public function remove_pricelist_group($pricelist)
		{
			$q = Doctrine_Query::create()->update('SocialCodePriceListGrouped')->set('isdelete', '1')->where('price_list = "' . $pricelist . '"')->andWhere('isdelete = "0"')->execute();
		}

		public function remove_group_pricelist($group)
		{
			$q = Doctrine_Query::create()->update('SocialCodePriceListGrouped')->set('isdelete', '1')->where('groupid = "' . $group . '"')->andWhere('isdelete = "0"')->execute();
		}

	}

?>