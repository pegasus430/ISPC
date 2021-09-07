<?php

	Doctrine_Manager::getInstance()->bindComponent('TherapyplanItems', 'MDAT');

	class TherapyplanItems extends BaseTherapyplanItems {

		public function get_therapyplan_data($formid)
		{
			$res = Doctrine_Query::create()
				->select('*')
				->from('TherapyplanItems')
				->where('isdelete="0"')
				->andWhere('therapyplan_id LIKE "' . $formid . '"');
			$res_arr = $res->fetchArray();

			if($res_arr)
			{
				return $res_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>