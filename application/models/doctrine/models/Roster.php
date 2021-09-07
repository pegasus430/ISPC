<?php

	Doctrine_Manager::getInstance()->bindComponent('Roster', 'SYSDAT');

	class Roster extends BaseRoster {

		public static function dutyrosterweek($clientid, $limit)
		{
			$pagelimit = 3;
			$days = Doctrine_Query::create()
				->select('r.*,u.*')
				->from('Roster r')
				->innerJoin('r.User u')
				->where("r.duty_date between '" . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $limit, date('Y'))) . "' and '" . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $limit + 7, date('Y'))) . "'")
				->andWhere("u.clientid=" . $clientid)
				->andWhere('isdelete = "0"')
				->orderBy('duty_date ASC');
			$daysexec = $days->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			foreach($daysexec as $key => $value)
			{
				$cdate = date("Y-m-d", strtotime($value['duty_date']));
				$uid = $value['user_id'];
				$block[] = $value['User'];

				if($cdate != date("Y-m-d", strtotime($daysexec[$key + 1]['duty_date'])))
				{
					$tempblocks['Users'] = $block;
					$tempblocks['date'] = $cdate;

					$allblocks[] = $tempblocks;
					$block = array();
					$tempblocks = array();
				}
			}

			return $allblocks;
		}
		public function find_shift_data($clientid)
		{
			$droster = Doctrine_Query::create()
			->select('count(*), shift')
			->from('Roster')
			->where("clientid=" . $clientid)
			->andWhere('isdelete = "0"')
			->groupBy('shift');
			$droster_arr = $droster->fetchArray();
	
			return $droster_arr;
		}

		public function getCurrentQPA($clientid)
		{
			$ug = new Usergroup();
			$groupid = $ug->getDoctorGroupid($clientid);

			$days = Doctrine_Query::create()
				->select('r.*,u.*')
				->from('Roster r')
				->innerJoin('r.User u')
				->where("r.duty_date = '" . date("Y-m-d") . "' and u.clientid=" . $clientid)
				->andWhere('isdelete = "0"');

			$daysexec = $days->execute();
			if($daysexec)
			{
				$daysarr = $daysexec->toArray();
			}

			return($daysarr);
		}

		function get_user_overall_details($clientid, $date, $user_id)
		{
			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts($clientid);
			$master_end_date = date('Y-m-d 23:59:59', strtotime($date));

			foreach($client_shifts as $k_c_shift => $v_c_shift)
			{
				$client_shifts_arr[$v_c_shift['id']]['name'] = $v_c_shift['name'];
				$client_shifts_arr[$v_c_shift['id']]['color'] = $v_c_shift['color'];

				// get shift for current day
				$client_shifts_arr[$v_c_shift['id']]['start'] = date('Y-m-d H:i:s', mktime(date('H', strtotime($v_c_shift['start'])), date('i', strtotime($v_c_shift['start'])), 0, date('m', strtotime($date)), date('d', strtotime($date)), date('Y', strtotime($date))));

				if(strtotime($v_c_shift['start']) > strtotime($v_c_shift['end']))
				{
					$client_shifts_arr[$v_c_shift['id']]['end'] = $master_end_date;
				}
				else
				{
					$client_shifts_arr[$v_c_shift['id']]['end'] = date('Y-m-d H:i:s', mktime(date('H', strtotime($v_c_shift['end'])), date('i', strtotime($v_c_shift['end'])), 0, date('m', strtotime($date)), date('d', strtotime($date)), date('Y', strtotime($date))));
				}
			}

			$days = Doctrine_Query::create()
				->select('*')
				->from('Roster')
				->where("DATE(duty_date) =  DATE('" . $date . "')")
				->andWhere('userid = "' . $user_id . '" ')
				->andWhere('clientid = "' . $clientid . '" ')
				->andWhere('isdelete = "0"');
			$daysarr = $days->fetchArray();

			foreach($daysarr as $dutyk => $duty_value)
			{
				if($duty_value ['shift'] != 0)// get client shift
				{
					$user[$duty_value['userid']]['shift_start'] = $client_shifts_arr[$duty_value['shift']]['start'];
					$user[$duty_value['userid']]['shift_end'] = $client_shifts_arr[$duty_value['shift']]['end'];
				}
				elseif($duty_value ['shift'] == 0 && $duty_value ['fullShift'] == 0)
				{
					$user[$duty_value['userid']]['shift_start'] = $duty_value ['shiftStartTime'];
					$user[$duty_value['userid']]['shift_end'] = $duty_value ['shiftEndTime'];
				}
				elseif($duty_value ['shift'] == 0 && $duty_value ['fullShift'] == 1)
				{
					$user[$duty_value['userid']]['shift_start'] = $duty_value['duty_date'] . " 00:00:00";
					$user[$duty_value['userid']]['shift_end'] = $duty_value['duty_date'] . " 23:59:00";
				}

				$user[$duty_value['userid']]['userid'] = $duty_value['userid'];
				$user[$duty_value['userid']]['shift'] = $duty_value['shift'];
				$user[$duty_value['userid']]['fullShift'] = $duty_value['fullShift'];
			}

			if($user)
			{
				return $user;
			}
			else
			{
				return false;
			}
		}

	}

?>