<?php

	Doctrine_Manager::getInstance()->bindComponent('SgbvFormsHistory', 'MDAT');

	class SgbvFormsHistory extends BaseSgbvFormsHistory {

		function getPatientSgbvFormHistory($ipid, $sgbv_form)
		{
			$select = Doctrine_Query::create()
				->select('*')
				->from('SgbvFormsHistory')
				->where('parent="' . $sgbv_form . '"')
				->andWhere('ipid = "' . $ipid . '"')
				->andWhere('isdelete="0"');
			$select_res = $select->fetchArray();

			if($select_res)
			{
				return $select_res;
			}
			else
			{
				return false;
			}
		}

	}

?>