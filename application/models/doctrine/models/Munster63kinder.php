<?php

	Doctrine_Manager::getInstance()->bindComponent('Munster63kinder', 'MDAT');

	class Munster63kinder extends BaseMunster63kinder {

		public function get_munster_patient_data($ipid,$exclude_completed = true)
		{
			$selector = Doctrine_Query::create()
				->select('*')
				->from('Munster63kinder')
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
					$selector_arr[$k_sel]['vom'] = ($v_sel['vom'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($v_sel['vom'])) : '');
					$selector_arr[$k_sel]['bis'] = ($v_sel['bis'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($v_sel['bis'])) : '');
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