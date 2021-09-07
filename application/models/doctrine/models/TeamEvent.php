<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamEvent', 'MDAT');

	class TeamEvent extends BaseTeamEvent {

		public function get_team_events($clientid, $order = false, $direction = "ASC")
		{
			$team_meeting = Doctrine_Query::create()
				->select("*")
				->from('TeamEvent')
				->andWhere("client='" . $clientid . "'");
			if($order)
			{
				$team_meeting->orderBy('' . $order . ' ' . $direction . '');
			}

			$tmarray = $team_meeting->fetchArray();

			return $tmarray;
		}

		public function get_last_team_event($clientid)
		{
			$team_event = Doctrine_Query::create()
				->select("*")
				->from('TeamEvent')
				->andWhere("client='" . $clientid . "'")
				->orderBy("from_time ASC");
			$tmarray = $team_event->fetchArray();


			if($tmarray)
			{
				$team_results = end($tmarray);

				return $team_results;
			}
			else
			{
				return false;
			}
		}
		

		public function get_event_details($eventid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if($eventid)
			{
				$team_event = Doctrine_Query::create()
					->select("*")
					->from('TeamEvent')
					->andWhere('id = "' . $eventid . '"');
				$tmarray = $team_event->fetchArray();

				if($tmarray)
				{
					//get meeting attending_users
   				    $event_attending_users = TeamEventAttendingUsers::get_team_event_attending_users($eventid, $clientid);
   				    foreach($event_attending_users as $k_mau => $v_mau)
   				    {
   				        $attending_users[] = $v_mau['user'];
   				    }
   				    
   				    $tmarray[0]['attending_users'] = $attending_users;	   				    
	   				    
   					//get meeting attending_volunaty 
	       			$event_attending_vw = TeamEventAttendingVoluntaryW::get_team_event_attending_voluntary_w($eventid, $clientid);
	       			if(!empty($event_attending_vw)){
    					foreach($event_attending_vw as $k_wmau => $v_wmau)
    					{
    						$attending_vw[] = $v_wmau['vw_id'];
    					}
    
    					$tmarray[0]['attending_vw'] = $attending_vw;
	       			}
				

					$event_data = $tmarray[0];
				}

				return $event_data;
			}
			else
			{
				return false;
			}
		}
	}

?>