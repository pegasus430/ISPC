<?php

	Doctrine_Manager::getInstance()->bindComponent('DashboardLabels', 'SYSDAT');

	class DashboardLabels extends BaseDashboardLabels {

		public function getClientLabels()
		{
			$clist = Doctrine_Query::create()
				->select("*")
				->from('DashboardLabels')
				->where('isdelete=0');
			$clientlist = $clist->fetchArray();
			if($clientlist)
			{
				return $clientlist;
			}
			else
			{
				return false;
			}
		}

		public function getLabel($label)
		{
			$label_res = Doctrine_Query::create()
				->select("*")
				->from('DashboardLabels')
				->where('isdelete=0')
				->andWhere('id="' . $label . '"');
			$label_arr = $label_res->fetchArray();

			if($label_arr)
			{
				return $label_arr;
			}
			else
			{
				return false;
			}
		}

		public function getActionsLastLabel()
		{
			$sel_actions = Doctrine_Query::create()
				->select('*')
				->from('DashboardLabels')
				->where('isdelete=0')
				->andWhere('name != ""')
				->andWhere('action != ""');
			$r = $sel_actions->fetchArray();

			foreach($r as $k_label => $v_label)
			{
				$label_arr[$v_label['action']] = $v_label;
			}

			if($label_arr)
			{
				return $label_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>