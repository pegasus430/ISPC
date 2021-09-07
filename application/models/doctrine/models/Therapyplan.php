<?php

	Doctrine_Manager::getInstance()->bindComponent('Therapyplan', 'MDAT');

	class Therapyplan extends BaseTherapyplan {

		public function get_patient_form($ipid)
		{
			$res = Doctrine_Query::create()
				->select('*')
				->from('Therapyplan')
				->where('isdelete="0"')
				->andWhere('ipid LIKE "' . $ipid . '"')
				->limit('1');
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