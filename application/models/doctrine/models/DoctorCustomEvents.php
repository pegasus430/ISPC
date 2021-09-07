<?php

	Doctrine_Manager::getInstance()->bindComponent('DoctorCustomEvents', 'SYSDAT');

	class DoctorCustomEvents extends BaseDoctorCustomEvents {

		public function getDoctorCustomEvents($userid, $clientid, $ipid = false)
		{
			$docCustomEv = Doctrine_Query::create()
				->select("*")
				->from('DoctorCustomEvents')
				->Where("clientid='" . $clientid . "'");
			if($ipid !== false)
			{
				$docCustomEv->andWhere("ipid='" . $ipid . "'");
			}

			$docarray = $docCustomEv->fetchArray();

			return $docarray;
		}

		public function get_doc_team_all_custom_events($clientid, $ipids = false)
		{
			$docCustomEv = Doctrine_Query::create()
				->select("*")
				->from('DoctorCustomEvents')
				->Where("clientid='" . $clientid . "'")
				->andWhere('viewForAll = "1"')
				->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'");
			if($ipids !== false)
			{
				$ipids[] = '999999999';
				$docCustomEv->andWhereIn('ipid', $ipids);
			}

			$docarray = $docCustomEv->fetchArray();
			//print_r($docCustomEv->getSqlQuery());
			return $docarray;
		}
		
		public function get_all_doc_events_from_date($clientid, $date = false)
		{
			if ($date === false){
				//today from php
				$date = date("Y-m-d");
			}
			$docCustomEv = Doctrine_Query::create()
			->select("id, userid, eventTitle, startDate, endDate, eventType, allDay, viewForAll, dayplan_inform")
			->from('DoctorCustomEvents')
			->Where("clientid = ? ", $clientid)
			->andWhere("ipid = ? ", 0 )
			->andWhere("userid != ? ", 0 )
			->andwhere("DATE(startDate) = DATE('".$date."')");
			
			$docarray = $docCustomEv->fetchArray();
			
			return $docarray;
		}
		

	}

?>