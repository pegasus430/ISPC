<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamCustomEvents', 'SYSDAT');

	class TeamCustomEvents extends BaseTeamCustomEvents {

		public function getTeamCustomEvents($clientid)
		{
			$tmCustomEv = Doctrine_Query::create()
				->select("*")
				->from('TeamCustomEvents')
				->andWhere("clientid='" . $clientid . "'");
			$tmarray = $tmCustomEv->fetchArray();

			return $tmarray;
		}

		public function getTeamCustomEvents_on_day($clientid , $date)
		{
			$tmCustomEv = Doctrine_Query::create()
			->select("eventTitle , startDate, endDate, eventType, allDay, dayplan_inform")
			->from('TeamCustomEvents')
			->andWhere("clientid='" . $clientid . "'")
			
			->andWhere("DATE(`startDate`) <= DATE('". $date ."')")
			->andWhere("DATE(`endDate`) >= DATE('". $date ."')");

			
			$tmarray = $tmCustomEv->fetchArray();
		
			return $tmarray;
		}
		
		
	}

?>