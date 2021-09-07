<?php

	Doctrine_Manager::getInstance()->bindComponent('BreRecordingReport', 'MDAT');

	class BreRecordingReport extends BaseBreRecordingReport {

		public function getBreRecording($fid, $ipid)
		{
			$bre_recording = Doctrine_Query::create()
				->select("*")
				->from('BreRecordingReport')
				->where("id='" . $fid . "'")
				->andWhere("ipid='" . $ipid . "'")
				->andWhere('isdelete = "0"');
			$bre_recording_array = $bre_recording->fetchArray();

			return $bre_recording_array;
		}

	}

?>