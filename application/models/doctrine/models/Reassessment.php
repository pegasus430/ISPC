<?php

	Doctrine_Manager::getInstance()->bindComponent('Reassessment', 'MDAT');

	class Reassessment extends BaseReassessment {

		public function getPatientAssessment($ipid)
		{
			$reassessment = Doctrine_Query::create()
				->select("*")
				->from('Reassessment')
				->where("ipid='" . $ipid . "'");
			$reassessmentarray = $reassessment->fetchArray();

			return $reassessmentarray;
		}

	}

?>