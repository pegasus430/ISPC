<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage5CurrentProblems', 'MDAT');

	class Anlage5CurrentProblems extends BaseAnlage5CurrentProblems {

		public function get_form_current_problems($ipid, $formid = false)
		{
			$selector = Doctrine_Query::create()
				->select('*')
				->from('Anlage5CurrentProblems')
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