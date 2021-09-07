<?php

	Doctrine_Manager::getInstance()->bindComponent('ZapvAssessmentIISymp', 'MDAT');

	class ZapvAssessmentIISymp extends BaseZapvAssessmentIISymp {

		public function get_zapv_assessment_symp($ipid, $form_id)
		{
			$zapv_q = Doctrine_Query::create()
				->select('*')
				->from('ZapvAssessmentIISymp')
				->where("ipid = ?", $ipid)
				->andWhere('form_id = ?',$form_id)
				->andWhere('isdelete=0');
			$zapv_array = $zapv_q->fetchArray();

			if($zapv_array)
			{
				return $zapv_array;
			}
		}

		public function get_zapv_assessment_by_id($ipid, $form_id)
		{
			$zapv_q = Doctrine_Query::create()
				->select('*')
				->from('ZapvAssessmentIISymp')
				->where("ipid = '" . $ipid . "' ")
				->andWhere('id = "' . $form_id . '" ')
				->andWhere('isdelete=0');
			$zapv_array = $zapv_q->fetchArray();

			if($zapv_array)
			{
				return $zapv_array;
			}
		}

	}

?>