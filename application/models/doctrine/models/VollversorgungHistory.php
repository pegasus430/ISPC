<?php

	Doctrine_Manager::getInstance()->bindComponent('VollversorgungHistory', 'MDAT');

	class VollversorgungHistory extends BaseVollversorgungHistory {

		public function getVollversorgungHistory($ipid, $date_type, $dq_ipids = false, $allow_deleted = false)
		{
			if(is_array($ipid) && sizeof($ipid) > 0)
			{
				foreach($ipid as $ipid_single)
				{
					$ipid_str .= '"' . $ipid_single . '",';
				}
				$ipid_sql = 'ipid IN (' . substr($ipid_str, 0, -1) . ')';
			}
			else
			{
				$ipid_sql = "ipid='" . $ipid . "'";
			}

			$loc = Doctrine_Query::create()
				->select("*")
				->from('VollversorgungHistory')
				->where($ipid_sql);
			if($allow_deleted === false)
			{
				$loc->andWhere('isdelete ="0"');
			}
			$loc->andWhere("date_type='" . $date_type . "'");
			if($dq_ipids)
			{
				$loc->andwherenotin('ipid', $dq_ipids);
			}

			$loc->orderBy("date ASC");
			$locat = $loc->execute();

			if($_REQUEST['dbg'] == 1)
			{
				print_r($loc->getSqlQuery());
			}

			if($locat)
			{
				$disarr = $locat->toArray();

				foreach($disarr as $status)
				{
					$finalArr[$status['ipid']][] = $status;
				}
				return $finalArr;
			}
		}

		public function getVollversorgungHistoryAll($ipid, $deleted = false)
		{
			$loc = Doctrine_Query::create()
				->select("*")
				->from('VollversorgungHistory');
			
			//ISPC-1814
			if (is_array($ipid) && !empty($ipid)){
				$loc->whereIn("ipid" , $ipid);
			}else{
				$loc->where("ipid='" . $ipid . "'");
			}			
				
			if($deleted === false)
			{
				$loc->andWhere('isdelete ="0"');
			}

			$loc->orderBy("id ASC");
			$disarr = $loc->fetchArray();

			if($disarr)
			{
				return $disarr;
			}
		}

		public function delVollversorgungHistory($vvhid)
		{

			$loc = Doctrine_Query::create()
				->select("*")
				->from('VollversorgungHistory')
				->where("id='" . $vvhid . "'")
				->orderBy("id ASC");
			$vvharr = $loc->fetchArray();

			if($vvhid > 0 && !is_array($vvhid))
			{
				//delete or (un)delete the history record
				if($vvharr[0]['isdelete'] == 1)
				{
					$del = 0;
				}
				else
				{
					$del = 1;
				}

				$q = Doctrine_Query::create()
					->update('VollversorgungHistory')
					->set('isdelete', '"' . $del . '"')
					->where('id = "' . $vvhid . '"');
				$deldata = $q->execute();
			}
		}

		public function getVollversorgungDays($ipid)
		{
			$pat_master = new PatientMaster();
			$loc = Doctrine_Query::create()
				->select("*")
				->from('VollversorgungHistory')
				->where("ipid='" . $ipid . "'")
				->andWhere('isdelete ="0"')
				->orderBy('date ASC');
			$vvhistoryarr = $loc->fetchArray();

			if($vvhistoryarr)
			{
				$incr = '0';
				foreach($vvhistoryarr as $khistory => $vhistory)
				{
					if($vhistory['date_type'] == '1')
					{
						$type = 'start';
					}
					else
					{
						$type = 'end';
					}
					$patient_vv_history[$incr][$type] = $vhistory['date'];

					if($vhistory['date_type'] == '1' && !array_key_exists(($khistory + 1), $vvhistoryarr))
					{
						$patient_vv_history[$incr]['end'] = date('Y-m-d H:i:s');
					}
					//increment when reaching end dates(date_type=2)
					if($vhistory['date_type'] == '2')
					{
						$incr++;
					}
				}

				//verify vv history array
				foreach($patient_vv_history as $k_vv => $v_vv)
				{
					if(!empty($v_vv['end']) && empty($v_vv['start']))
					{
						unset($patient_vv_history[$k_vv]);
					}
				}
				$patient_vv_history = array_values($patient_vv_history);

				foreach($patient_vv_history as $k_vpatient => $v_vpatient)
				{
					if(!empty($v_vpatient['start']))
					{
						$patient_days = $pat_master->getAllDaysInBetween($v_vpatient['start'], $v_vpatient['end']);

						if(empty($pat_days_vvperiod))
						{
							$pat_days_vvperiod = array();
						}
						$pat_days_vvperiod = array_merge($pat_days_vvperiod, $patient_days);
					}
					//print_r($v_vpatient);
				}
				//exit;
				$pat_days_vvperiod = array_values(array_unique($pat_days_vvperiod));
				//print_r($pat_days_vvperiod);exit;
				$days_vv = count($pat_days_vvperiod);

				return $days_vv;
			}
		}

		public function get_vollversorgung_period($ipids, $period_limit = '25', $all_patient_details = false)
		{
			$pat_master = new PatientMaster();
//			$ipids = array_values(array_unique($ipids));

			$loc = Doctrine_Query::create()
				->select("*")
				->from('VollversorgungHistory')
				->whereIn('ipid', $ipids)
				->andWhere('isdelete ="0"')
				->orderBy('ipid,id ASC');
			$vvhistoryarr = $loc->fetchArray();

			if($vvhistoryarr)
			{
				foreach($vvhistoryarr as $khistory => $vhistory)
				{
					if($vhistory['date_type'] == '1')
					{
						$type = 'start';
						$patient_vv_history[$vhistory['ipid']][$type][] = $vhistory['date'];
					}
					else if($vhistory['date_type'] == '2')
					{
						$type = 'end';
						if(count($patient_vv_history[$vhistory['ipid']]['start']) > 0 && (count($patient_vv_history[$vhistory['ipid']]['end']) + 1) == count($patient_vv_history[$vhistory['ipid']]['start']))
						{
							$patient_vv_history[$vhistory['ipid']][$type][] = $vhistory['date'];
						}
					}
				}

				$vv_patients = array();
				foreach($patient_vv_history as $k_ipid => $v_vv_periods)
				{
					foreach($v_vv_periods['start'] as $k_vvperiod => $v_vvperiod)
					{
						$period_duration = '';
						$end_vv_period = '';
						if(strlen($patient_vv_history[$k_ipid]['end'][$k_vvperiod]) > 0)
						{
							$end_vv_period = $patient_vv_history[$k_ipid]['end'][$k_vvperiod];
						}
						else
						{
							$end_vv_period = date('Y-m-d H:i:s', time());
						}

						$patients_overall_vv[$k_ipid] += $pat_master->getDaysDiff($v_vvperiod, $end_vv_period);

						if($patients_overall_vv[$k_ipid] >= $period_limit)
						{
							$vv_patients[] = $k_ipid;

//							dump
							if($_REQUEST['list'])
							{
								$patients_dump[$k_ipid] = '"' . $all_patient_details[$k_ipid]['EpidIpidMapping']['epid'] . '", "' . $patients_overall_vv[$k_ipid] . '"';
							}
						}
					}
				}
				$vv_patients = array_values(array_unique($vv_patients));

				if($_REQUEST['list'])
				{
					print_r(implode("\n", $patients_dump));
					exit;
				}

				return $vv_patients;
			}
		}
	}

?>