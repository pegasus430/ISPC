<?php

	Doctrine_Manager::getInstance()->bindComponent('TeamMeeting', 'MDAT');

	class TeamMeeting extends BaseTeamMeeting {

		public function get_team_meetings($clientid, $order = false, $direction = "ASC")
		{
			$team_meeting = Doctrine_Query::create()
				->select("*")
				->from('TeamMeeting')
				->andWhere("client='" . $clientid . "'");
			if($order)
			{
				$team_meeting->orderBy('' . $order . ' ' . $direction . '');
			}

			$tmarray = $team_meeting->fetchArray();

			return $tmarray;
		}

        /**
         * IM-141, teammeeting for konsil and station
         *
         * @param $clientid
         * @param $case
         * @param bool $order
         * @param string $direction
         * @return array //Maria:: Migration CISPC to ISPC 22.07.2020
         */
		public function get_team_meetings_for_case($clientid, $case, $order = false, $direction = "ASC")
        {
            $team_meeting = Doctrine_Query::create()
                ->select("*")
                ->from('TeamMeeting')
                ->where("casestatus='" . $case . "'")
                ->andWhere("client='" . $clientid . "'");
            if($order)
            {
                $team_meeting->orderBy('' . $order . ' ' . $direction . '');
            }

            $tmarray = $team_meeting->fetchArray();

            return $tmarray;
        }

		public function get_last_team_meetings($clientid, $actual_id = null, $date = false)
		{
			$team_meeting = Doctrine_Query::create()
				->select("*")
				->from('TeamMeeting')
				->andWhere("client='" . $clientid . "'")
				->andWhere('id != "'.$actual_id.'" ');
			if($date){
				$team_meeting->andWhere('from_time < "'.$date.'"');
			}

			$team_meeting->orderBy("from_time ASC");
			$tmarray = $team_meeting->fetchArray();


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

        /**
         * IM-141, teammeeting for konsil and station
         *
         * @param $clientid
         * @param $case
         * @param null $actual_id
         * @param bool $date
         * @return bool|mixed //Maria:: Migration CISPC to ISPC 22.07.2020
         */
		public function get_last_team_meetings_for_case($clientid, $case, $actual_id = null, $date = false)
		{
			$team_meeting = Doctrine_Query::create()
				->select("*")
				->from('TeamMeeting')
                ->where("casestatus='" . $case . "'")
				->andWhere("client='" . $clientid . "'")
				->andWhere('id != "'.$actual_id.'" ');
			if($date){
				$team_meeting->andWhere('from_time < "'.$date.'"');
			}
			
			$team_meeting->orderBy("from_time ASC");
			$tmarray = $team_meeting->fetchArray();


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

		public function get_meeting_details($meetingid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
			
			if($meetingid)
			{
				$team_meeting = Doctrine_Query::create()
					->select("*")
					->from('TeamMeeting')
					->andWhere('id = "' . $meetingid . '"');
				$tmarray = $team_meeting->fetchArray();

				if($tmarray)
				{
					//get meeting details
					$meeting_details = TeamMeetingDetails::get_team_meeting_details($meetingid, $clientid);

					foreach($meeting_details as $k_mdet => $v_mdet)
					{
						$details[$v_mdet['patient']][] = $v_mdet;
					}

					//get meeting assigned_users
					$meeting_assigned_users = TeamMeetingAssignedUsers::get_team_meeting_assigned_users($meetingid, $clientid);

					foreach($meeting_assigned_users as $k_asu => $v_asu)
					{
						$assigned_users[$v_asu['patient']][$v_asu['row']][] = $selectbox_separator_string[$v_asu['user_type']] . $v_asu['user'];
					}

					//get meeting attending_users
					$meeting_attending_users = TeamMeetingAttendingUsers::get_team_meeting_assigned_users($meetingid, $clientid);

					foreach($meeting_attending_users as $k_mau => $v_mau)
					{
						$attending_users[] = $v_mau['user'];
					}

					$tmarray[0]['details'] = $details;
					$tmarray[0]['assigned_users'] = $assigned_users;
					$tmarray[0]['attending_users'] = $attending_users;

					$meeting_data = $tmarray[0];
				}

				return $meeting_data;
			}
			else
			{
				return false;
			}
		}

		public function get_patients_locations($conditions = false)
		{
			if($conditions)
			{
				if(!empty($conditions['ipids']) && !empty($conditions['period']))
				{
					
					$patient_loc = PatientLocation::get_multiple_period_locations($conditions['ipids'], $conditions['period'], false);

					if($patient_loc)
					{
						foreach($patient_loc as $k_loc => $v_loc)
						{
							if(!empty($v_loc['master_details']))
							{
								//last location remains
								$patients_locations[$v_loc['ipid']] = $v_loc['master_details']['location'] . "<br />" . $v_loc['master_details']['street'] . "<br />" . $v_loc['master_details']['zip'] . " " . $v_loc['master_details']['city'];
							}
						}
					}

					if(!empty($patients_locations))
					{
						return $patients_locations;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		
		public function get_client_team_meetings_report_period($client, $report_period)
		{
			$date_sql = " ";
			foreach($report_period as $k => $date)
			{
				$start_date_time = date('Y-m-d H:i:s', strtotime($date['start']));
				$end_date_time = date('Y-m-d H:i:s', strtotime($date['end']));
				$date_sql .= ' ( DATE(date) >= DATE("' . $start_date_time . '") AND DATE(date) <= DATE("' . $end_date_time . '") )  OR ';
			}
		
			$query = Doctrine_Query::create();
			$query->select('*');
			$query->from('TeamMeeting');
			$query->where('' . substr($date_sql, 0, -4) . '');
			$q_res = $query->fetchArray();
		
// 			foreach($q_res as $k_res => $v_res)
// 			{
// 				$meetingids[] =$v_res['id'];
// 			}

// 			//get meeting attending_users
// 			$meeting_attending_users = TeamMeetingAttendingUsers::get_team_multiple_meetings_attending_users($meetingids, $client);
// 			foreach($meeting_attending_users as $k_mau => $v_mau)
// 			{
// 				$meeting_attending_users[$v_mau['meeting']][] = $v_mau['user'];
// 			}
			
			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{ 
					$master_data[$v_res['id']] = $v_res;
				}
		
				return $master_data;
			}
			else
			{
				return false;
			}
		}
		
		
		
		
		
	}

?>