<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage5Part2', 'MDAT');

	class Anlage5Part2 extends BaseAnlage5Part2 {

		public function get_anlage5part2_details($ipid, $current_period)
		{
			$selector = Doctrine_Query::create()
				->select('*')
				->from('Anlage5Part2')
				->where('ipid = "' . $ipid . '"')
				->andWhere('date BETWEEN "' . $current_period['start'] . '" AND "' . $current_period['end'] . '"');
			$selector_res = $selector->fetchArray();

			if($selector_res)
			{
				return $selector_res;
			}
			else
			{
				return false;
			}
		}

	}

?>