<?php

	Doctrine_Manager::getInstance()->bindComponent('QuestionnaireB', 'MDAT');

	class QuestionnaireB extends BaseQuestionnaireB {

		public function get_patient_form($ipid)
		{
			$res = Doctrine_Query::create()
				->select('*')
				->from('QuestionnaireB')
				->where('isdelete="0"')
				->andWhere('ipid LIKE "' . $ipid . '"');
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