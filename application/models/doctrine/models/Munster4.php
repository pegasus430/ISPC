<?php

	Doctrine_Manager::getInstance()->bindComponent('Munster4', 'MDAT');

	class Munster4 extends BaseMunster4 {

		public function get_munster4_patient_data($ipid)
		{
			$selector = Doctrine_Query::create()
				->select('*')
				->from('Munster4')
				->where('ipid = "' . $ipid . '"');
			$selector_res = $selector->fetchArray();

			return $selector_res[0];
		}

	}
