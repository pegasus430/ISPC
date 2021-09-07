<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamEventAttendingVoluntaryW', 'MDAT');

	class TeamEventAttendingVoluntaryW extends BaseTeamEventAttendingVoluntaryW {

		public function get_team_event_attending_voluntary_w($event, $clientid)
		{
			$team_event = Doctrine_Query::create()
				->select("*")
				->from('TeamEventAttendingVoluntaryW')
				->where("event='" . $event . "'")
				->andWhere("client='" . $clientid . "'")
				->andWhere("isdelete='0'");
			$tmarray = $team_event->fetchArray();

			return $tmarray;
		}

	}

?>