<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamEventAttendingUsers', 'MDAT');

	class TeamEventAttendingUsers extends BaseTeamEventAttendingUsers {

		public function get_team_event_attending_users($event, $clientid)
		{
			$team_event = Doctrine_Query::create()
				->select("*")
				->from('TeamEventAttendingUsers')
				->where("event='" . $event . "'")
				->andWhere("client='" . $clientid . "'")
				->andWhere("isdelete='0'");
			$tmarray = $team_event->fetchArray();

			return $tmarray;
		}

	}

?>