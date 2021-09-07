<?php

	Doctrine_Manager::getInstance()->bindComponent('UserVacations', 'SYSDAT');

	class UserVacations extends BaseUserVacations {

		public function get_user_vacations($userid, $date = false)
		{
			if($date)
			{
				$date = date('Y-m-d H:i:s', $date);
				$where = ' AND DATE("' . $date . '") BETWEEN DATE(`start`) and DATE(`end`) ';
			}
			else
			{
				$where = '';
			}

			$vacations = Doctrine_Query::create()
				->select("*")
				->from('UserVacations')
				->where("userid='" . $userid . "' " . $where)
				->orderBy('start, end ASC');

			$vacations_array = $vacations->fetchArray();

			return $vacations_array;
		}

		public function get_current_vacation_replacements($userid)
		{
			$v_repl = new vacationsReplacements();
			$current_vacations = $this->get_user_vacations($userid, time());

			$vacation_ids[] = '9999999999999';
			foreach($current_vacations as $k_vacation => $v_vacation)
			{
				$vacation_ids[] = $v_vacation['id'];
			}

			$vacation_repl = $v_repl->get_vacation_replacements($vacation_ids);
			if($vacation_repl)
			{
				return $vacation_repl;
			}
			else
			{
				return false;
			}
		}

		public function get_users_vacations($start_period, $end_period, $group_users_ids = false)
		{
			if(!$group_users_ids || empty($group_users_ids))
			{
				$group_users_ids[] = '999999999999';
			}

			$n_vacation = Doctrine_Query::create()
				->select('*')
				->from('UserVacations')
				->where('DATE("' . date('Y-m-d', strtotime($start_period)) . '") <= DATE(`end`) AND DATE("' . date('Y-m-d', strtotime($end_period)) . '") >= DATE(`start`)')
				->andWhereIn('userid', $group_users_ids);
			$v_users = $n_vacation->fetchArray();

			foreach($v_users as $k_vu => $v_vu)
			{
				$users_vacations[] = $v_vu;
			}

			return $users_vacations;
		}

		public function get_vacation_users($start_period, $end_period, $group_users_ids = false)
		{
			if(!$group_users_ids || empty($group_users_ids))
			{
				$group_users_ids[] = '999999999999';
			}

			$n_vacation = Doctrine_Query::create()
				->select('*')
				->from('UserVacations')
				->where('DATE("' . date('Y-m-d', strtotime($start_period)) . '") < DATE(`end`) AND DATE("' . date('Y-m-d', strtotime($end_period)) . '") > DATE(`start`)')
				->andWhereIn('userid', $group_users_ids);

			$v_users = $n_vacation->fetchArray();

			foreach($v_users as $k_vu => $v_vu)
			{
				$vacation_users[] = $v_vu['userid'];
			}

			$vacation_users = array_values(array_unique($vacation_users));

			return $vacation_users;
		}

		public function get_vacation_details($vacation, $userid)
		{
			$vacations = Doctrine_Query::create()
				->select("*")
				->from('UserVacations')
				->where("id='" . $vacation . "'")
				->andWhere('userid="' . $userid . '"')
				->orderBy('start, end ASC');
			$vacations_array = $vacations->fetchArray();

			if($vacations_array)
			{
				return $vacations_array;
			}
			else
			{
				return false;
			}
		}

		public function get_client_vacations($clientid, $start, $end)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$users = new User();
			$client_users = $users->getUserByClientid($clientid, '0', false);

			$client_user_ids[] = '9999999999';
			foreach($client_users as $k_user => $v_user)
			{
				$client_user_ids[] = $v_user['id'];
				$client_user_details[$v_user['id']] = $v_user;
			}

			unset($client_users);
			asort($client_user_ids);
			ksort($client_user_details);
			$client_user_ids = array_values($client_user_ids);

			$vacations_arr = $this->get_users_vacations($start, $end, $client_user_ids);

			foreach($vacations_arr as $k_vacation => $v_vacation)
			{
				$vacations[$k_vacation] = $v_vacation;
				$vacations[$k_vacation]['user_details'] = $client_user_details[$v_vacation['userid']];
			}

			return $vacations;
		}
		
		/**
		 * 
		 * @param unknown $users
		 * @param unknown $ipid
		 * @param unknown $users_ids_associated
		 * @return array|array
		 * TODO-2965 Ancuta changed function - added merge periods, as a replacement can heve multiple periods 
		 * TODO-2965 03.03.2020
		 */
		public function get_all_users_vacations($users,$ipid,$users_ids_associated){
			
			if(empty($users) || empty($ipid)){
				return array();
			}
			
			$vacations = Doctrine_Query::create()
			->select("*")
			->from('UserVacations')
			->whereIn('userid',$users)
			->orderBy('start, end ASC');
			$vacations_array = $vacations->fetchArray();
				
			if(empty($vacations_array)){
				return array();
			}
			foreach($vacations_array as $k=>$urp){
				$user2vacantion_details[$urp['userid']][$urp['id']] =  $urp;
			}
			
			$vr = new VacationsReplacements ();
			$replace_ments = $vr->get_multiple_user_vacation_replacements($users,$ipid);
			
			if(empty($replace_ments)){
				return array();
			}
			
			$patientmaster = new PatientMaster();
			$replace = array();
			foreach($replace_ments as $k=>$data){
				if( ! empty($users_ids_associated[$data['replacement']])){
					$replace[$data['userid']][$data['ipid']][$users_ids_associated[$data['replacement']]][] = $patientmaster->getDaysInBetween($user2vacantion_details[$data['userid']][$data['vacation']]['start'], $user2vacantion_details[$data['userid']][$data['vacation']]['end']);
				} else{
					$replace[$data['userid']][$data['ipid']][$data['replacement']][] = $patientmaster->getDaysInBetween($user2vacantion_details[$data['userid']][$data['vacation']]['start'], $user2vacantion_details[$data['userid']][$data['vacation']]['end']);
				}
			}
			
			// TODO-2965 Ancuta 03.03.2020
			$replace_array = array();
			foreach($replace as $user_id=>$ipid_data){
			    foreach($ipid_data[$ipid] as $rep_uid=>$dates_Arr){
			        foreach ($dates_Arr as $k=>$days){
			            if(!is_Array($replace_array[$user_id][$ipid][$rep_uid])){
			                $replace_array[$user_id][$ipid][$rep_uid] = array();
			            }
			            $replace_array[$user_id][$ipid][$rep_uid] = array_merge($replace_array[$user_id][$ipid][$rep_uid],$days);
			        }
			    }
			}
 
			return $replace_array;
			
			
			
		}
		

	}

?>