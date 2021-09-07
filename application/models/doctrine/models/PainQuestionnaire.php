<?php

	Doctrine_Manager::getInstance()->bindComponent('PainQuestionnaire', 'MDAT');

	class PainQuestionnaire extends BasePainQuestionnaire {

		public function get_patient_pain_questionnaire($ipid)
		{
			$sel = Doctrine_Query::create()
				->select('*')
				->from('PainQuestionnaire')
				->where("ipid LIKE '" . $ipid . "'  and isdelete = 0")
				->orderBy('create_date DESC');
			$sel_res = $sel->fetchArray();
			
			return $sel_res;
		}

		public function get_pain_questionnaire($formid)
		{
			$sel = Doctrine_Query::create()
				->select('*')
				->from('PainQuestionnaire')
				->where("id='" . $formid . "'  and isdelete = 0");
			$sel_res = $sel->fetchArray();
			
			return $sel_res;
		}
		
		public function get_last_patientpainquestionnaire($ipid)
		{
			$sel = Doctrine_Query::create()
			->select('*')
			->from('PainQuestionnaire')
			->where("ipid LIKE '" . $ipid . "'  and isdelete = 0")
			->orderBy('create_date DESC')
			->limit('1');
			$sel_res = $sel->fetchArray();
				
			return $sel_res;
		}
	}

?>