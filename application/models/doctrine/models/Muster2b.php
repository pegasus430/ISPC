<?php

	Doctrine_Manager::getInstance()->bindComponent('Muster2b', 'MDAT');

	class Muster2b extends BaseMuster2b {

		public function get_muster2b_patient_data($ipid,$exclude_completed = true)
		{
			$selector = Doctrine_Query::create()
				->select('*')
				->from('Muster2b')
				->where('ipid = "' . $ipid . '"');
			
				if($exclude_completed){
					$selector->andWhere('iscompleted = 0 ');
				}
			
			
			$selector_res = $selector->fetchArray();
			
			if($selector_res)
			{
				foreach($selector_res as $k_sel => $v_sel)
				{
					$selector_arr[$k_sel] = $v_sel;
				}

				return $selector_arr[0];
			}
			else
			{
				return false;
			}
		}

	}

?>