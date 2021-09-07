<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage5Pretreatment', 'MDAT');

	class Anlage5Pretreatment extends BaseAnlage5Pretreatment {

		public function get_form_pretreatment($ipid, $formid = false)
		{
			$selector = Doctrine_Query::create()
				->select('*')
				->from('Anlage5Pretreatment')
				->where('isdelete="0"')
				->andWhere('ipid = "' . $ipid . '"');
			if($formid)
			{
				$selector->andWhere('formid = "' . $formid . '"');
			}
			$selector_arr = $selector->fetchArray();

			if($selector_arr)
			{
				return $selector_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>