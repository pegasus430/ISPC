<?php

	Doctrine_Manager::getInstance()->bindComponent('NieRecordingReport', 'MDAT');

	class NieRecordingReport extends BaseNieRecordingReport {

		public function getNieRecording($fid, $ipid)
		{
			$nie_recording = Doctrine_Query::create()
				->select("*")
				->from('NieRecordingReport')
				->where("id='" . $fid . "'")
				->andWhere("ipid='" . $ipid . "'")
				->andWhere('isdelete = "0"');

			$nie_recording_array = $nie_recording->fetchArray();

			return $nie_recording_array;
		}

	}

?>