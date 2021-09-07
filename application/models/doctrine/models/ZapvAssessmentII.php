<?php

	Doctrine_Manager::getInstance()->bindComponent('ZapvAssessmentII', 'MDAT');

	class ZapvAssessmentII extends BaseZapvAssessmentII {

		public function get_zapv_assessment($ipid, $assessment_type, $assessment_status = 'active')
		{
			$zapv_q = Doctrine_Query::create()
				->select('*')
				->from('ZapvAssessmentII')
				->where("ipid = '" . $ipid . "' ")
				->andWhere('type = "' . $assessment_type . '" ')
				->andWhere('status = "' . $assessment_status . '" ')
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
				->from('ZapvAssessmentII')
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