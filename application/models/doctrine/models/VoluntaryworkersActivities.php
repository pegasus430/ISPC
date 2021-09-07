<?php

	Doctrine_Manager::getInstance()->bindComponent('VoluntaryworkersActivities', 'SYSDAT');

	class VoluntaryworkersActivities extends BaseVoluntaryworkersActivities {

		public function get_vw_activities($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('VoluntaryworkersActivities')
				->where("vw_id=? and isdelete=?", array($id, "0"))
				->orderBy('date DESC');
			$droparray = $drop->fetchArray();
			return $droparray;
		}

	}

?>