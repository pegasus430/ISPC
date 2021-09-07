<?php

	Doctrine_Manager::getInstance()->bindComponent('SocialCodePriceListGroups', 'SYSDAT');

	class SocialCodePriceListGroups extends BaseSocialCodePriceListGroups {

		public function get_all_groups($client)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('SocialCodePriceListGroups')
				->where('client = "' . $client . '"')
				->andWhere('isdelete = "0"');
			$q_res = $q->fetchArray();

			if($q_res)
			{
				return $q_res;
			}
			else
			{
				return false;
			}
		}

		public function get_groups($client, $isdrop = false, $private = false)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('SocialCodePriceListGroups')
				->where('client = "' . $client . '"')
				->andWhere('isdelete = "0"');

			if($private)
			{
				$q->andWhere('private ="1"');
			}
			else
			{
				$q->andWhere('private ="0"');
			}

			$q_res = $q->fetchArray();

			foreach($q_res as $k_gr => $v_gr)
			{
				if($isdrop)
				{
					$groups[$v_gr['id']] = $v_gr['name'];
				}
				else
				{
					$groups[$v_gr['id']] = $v_gr;
				}
			}

			if($q_res && $groups)
			{
				return $groups;
			}
			else
			{
				return false;
			}
		}

		public function get_group_details($group)
		{
			if(is_array($group))
			{
				$group_ids = $group;
			}
			else
			{
				$group_ids = array($group);
			}

			$q = Doctrine_Query::create()
				->select('*')
				->from('SocialCodePriceListGroups')
				->whereIn('id', $group_ids)
				->andWhere('isdelete = "0"');
			$q_res = $q->fetchArray();

			foreach($q_res as $k_res => $v_res)
			{
				if(count($group) == '1')
				{
					$v_res_arr = $v_res;
				}
				else
				{
					$v_res_arr[$v_res['id']] = $v_res;
				}
			}

			if($q_res && $v_res_arr)
			{
				return $v_res_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>