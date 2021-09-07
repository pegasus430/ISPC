<?php

	Doctrine_Manager::getInstance()->bindComponent('SgbvFormsItems', 'MDAT');

	class SgbvFormsItems extends BaseSgbvFormsItems {

		function getPatientSgbvFormItems($ipid, $sgbv_form)
		{
			if(is_array($sgbv_form))
			{
				$sgbv_form[] = '9999999999999';
			}
			else
			{
				$sgbv_form = array($sgbv_form);
			}

			$select = Doctrine_Query::create()
				->select('*')
				->from('SgbvFormsItems')
				->whereIn('sgbv_form_id', $sgbv_form)
				->andWhere('ipid = "' . $ipid . '"')
				->andWhere('isdelete="0"')
				->orderBy('id ASC');
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
		
		//ISPC-2746 Carmen 03.12.2020
		function getPatientsSgbvFormItems($ipids)
		{
		
			$select = Doctrine_Query::create()
			->select('*')
			->from('SgbvFormsItems')
			//->whereIn('sgbv_form_id', $sgbv_form)
			->whereIn('ipid', $ipids)
			->andWhere('isdelete="0"')
			->orderBy('id ASC');
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
		//--


	}

?>