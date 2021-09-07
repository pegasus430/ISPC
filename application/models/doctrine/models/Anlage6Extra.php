<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage6Extra', 'MDAT');

	class Anlage6Extra extends BaseAnlage6Extra {

		public function get_anlage_extra_data($ipid, $period)
		{
			$query = Doctrine_Query::create()
				->select('*')
				->from('Anlage6Extra')
				->whereIn('ipid', $ipid)
				->andWhere('period = "' . $period . '"');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				return $q_res[0];
			}
			else
			{
				return false;
			}
		}
		
		public function get_all_anlage_extra_data($ipid)
		{
			$query = Doctrine_Query::create()
				->select('*')
				->from('Anlage6Extra')
				->whereIn('ipid', $ipid);
			$q_res = $query->fetchArray();

			if($q_res)
			{
				return $q_res;
			}
			else
			{
				return false;
			}
		}

	}

?>