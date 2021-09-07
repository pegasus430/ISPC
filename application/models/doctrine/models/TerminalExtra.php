<?php

	Doctrine_Manager::getInstance()->bindComponent('TerminalExtra', 'IDAT');

	class TerminalExtra extends BaseTerminalExtra {

		public function get_patients_extra_data($ipids)
		{
			$query = Doctrine_Query::create()
				->select('*')
				->from('TerminalExtra')
				->whereIn('ipid', $ipids);
			$q_res = $query->fetchArray();

			foreach($q_res as $k_res => $v_res)
			{
				$results[$v_res['ipid']] = $v_res;
			}

			if($q_res)
			{
				return $results;
			}
			else
			{
				return false;
			}
		}

	}

?>