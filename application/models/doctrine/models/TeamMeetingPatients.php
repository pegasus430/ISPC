<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamMeetingPatients', 'MDAT');

	class TeamMeetingPatients extends BaseTeamMeetingPatients {

		public function get_team_meeting_patients($meeting, $clientid)
		{
			$team_meeting = Doctrine_Query::create()
				->select("*")
				->from('TeamMeetingPatients')
				->where("meeting='" . $meeting . "'")
				->andWhere("client='" . $clientid . "'")
				->andWhere("isdelete='0'");
			$tmarray = $team_meeting->fetchArray();

			return $tmarray;
		}

	}

?>