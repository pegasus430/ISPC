<?php

	Doctrine_Manager::getInstance()->bindComponent('QuestionnaireC', 'MDAT');

	class QuestionnaireC extends BaseQuestionnaireC {

		public function get_patient_form($ipid)
		{
			$res = Doctrine_Query::create()
				->select('*')
				->from('QuestionnaireC')
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