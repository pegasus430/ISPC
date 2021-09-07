<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamEventTypes', 'MDAT');

	class TeamEventTypes extends BaseTeamEventTypes {

		public function get_team_event_types($clientid,$only_voluntary = false)
		{
			$team_event_types = Doctrine_Query::create()
				->select("*")
				->from('TeamEventTypes')
				->andWhere("client=?", $clientid)
				->andWhere("isdelete = 0");
			if($only_voluntary){
				$team_event_types->andWhere("voluntary = 1");
			}
			$tmarray = $team_event_types->fetchArray();

			return $tmarray;
		}
 
	}

?>