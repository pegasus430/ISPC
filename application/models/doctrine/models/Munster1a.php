<?php

	//Doctrine_Manager::getInstance()->bindComponent('Munster', 'MDAT');

	class Munster1a extends BaseMunster1a {

		public function get_munster1a_patient_data($ipid, $exclude_completed = true , $formular_id = false)
		{
			$selector = Doctrine_Query::create()
				->select('formular_id, input_name, input_value, iscompleted, completed_date')
				->from('Munster1a')
				->where('ipid = "' . $ipid . '"');
			
			if($exclude_completed){
				$selector->andWhere('iscompleted = 0 ');
			}
			
			if ($formular_id!==false){
				$selector->andWhere('formular_id = ? ',$formular_id);
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

				foreach($selector_res as $k_sel => $v_sel)
				{
					$formular [ $v_sel['formular_id']] [$v_sel['input_name']] = $v_sel['input_value'];
				
				}
				return $formular;
				return $selector_arr[0];
			}
			else
			{
				return false;
			}
		}
		
		public function get_munster1a_patient_formulars($ipid, $exclude_completed = true)
		{
			$selector = Doctrine_Query::create()
			->select('formular_id ')
			->from('Munster1a')
			->where('ipid = ?' , $ipid )
			->groupBy('formular_id');
				
			if($exclude_completed){
				$selector->andWhere('iscompleted = 0 ');
			}
			$selector_res = $selector->fetchArray();
			
			return $selector_res;
		}

	}

?>