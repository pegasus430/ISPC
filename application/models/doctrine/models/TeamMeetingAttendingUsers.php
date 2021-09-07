<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamMeetingAttendingUsers', 'MDAT');

	class TeamMeetingAttendingUsers extends BaseTeamMeetingAttendingUsers {

		public function get_team_meeting_assigned_users($meeting = 0, $clientid = 0)
		{
			$team_meeting = Doctrine_Query::create()
				->select("*")
				->from('TeamMeetingAttendingUsers')
				->where("meeting= ?", $meeting)
				->andWhere("client= ?" , $clientid)
				->andWhere("isdelete='0'")
				->fetchArray();

			return $team_meeting;
		}
		
		
		public function get_team_multiple_meetings_attending_users($meetings = null, $clientid =null)
		{
			//ISPC - 2274 - Carmen - 08.11.2018
			$tmarray = array();
			if(!$meetings || !$clientid)
			{
				return $tmarray;
			}
			
			if(is_array($meetings))
			{
				$meeting = $meetings;
			}
			else
			{
				$meeting = array($meetings);
			}
			
			if(empty($meeting))
			{
				return $tmarray;
			}
		
			$team_meeting = Doctrine_Query::create()
			->select("*")
			->from('TeamMeetingAttendingUsers')
			->whereIn("meeting", $meeting)
			->andWhere("client=?", $clientid)
			->andWhere("isdelete='0'");
			$tmarray = $team_meeting->fetchArray();
		
			return $tmarray;
		}
		
		

	}

?>