<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamMeetingAssignedUsers', 'MDAT');

	class TeamMeetingAssignedUsers extends BaseTeamMeetingAssignedUsers {

		public function get_team_meeting_assigned_users($meeting, $clientid)
		{
			$team_meeting = Doctrine_Query::create()
				->select("*")
				->from('TeamMeetingAssignedUsers')
				->where("meeting= ? ", $meeting)
				->andWhere("client= ? ", $clientid)
				->andWhere("isdelete='0'")
				->fetchArray();

			return $team_meeting;
		}

	}

?>