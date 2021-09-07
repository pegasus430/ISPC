<?php

	Doctrine_Manager::getInstance()->bindComponent('User2admission', 'MDAT');

	class User2admission extends BaseUser2admission {

		//used in generation of users invoices
		public function getAdmissions($ipid, $period_start = false, $period_end = false)
		{
			$adm = Doctrine_Query::create()
				->select('*')
				->from('User2admission')
				->where('ipid = "' . $ipid . '"');
			if($period_start && $period_end)
			{
				$adm->andWhere('DATE(date) BETWEEN DATE("' . $period_start . '") AND DATE("' . $period_end . '")');
			}
			$adm_res = $adm->fetchArray();

			if($adm_res)
			{
				return $adm_res;
			}
			else
			{
				return false;
			}
		}

		public function get_multiple_admissions($ipids, $period_start = false, $period_end = false)
		{
			$adm = Doctrine_Query::create()
				->select('*')
				->from('User2admission')
				->whereIn('ipid', $ipids);
			if($period_start && $period_end)
			{
				$adm->andWhere('DATE(date) BETWEEN DATE("' . $period_start . '") AND DATE("' . $period_end . '")');
			}
			$adm_res = $adm->fetchArray();

			if($adm_res)
			{
				return $adm_res;
			}
			else
			{
				return false;
			}
		}

	}

?>