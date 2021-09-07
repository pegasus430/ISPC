<?php

	Doctrine_Manager::getInstance()->bindComponent('RecordingAssessment', 'MDAT');

	class RecordingAssessment extends BaseRecordingAssessment {

		public function getPatientRecordingAssessment($ipid)
		{
			$Recordingassessment = Doctrine_Query::create()
				->select("*")
				->from('RecordingAssessment')
				->where("ipid='" . $ipid . "'");
			$Recordingassessmentarray = $Recordingassessment->fetchArray();

			return $Recordingassessmentarray;
		}

	}

?>