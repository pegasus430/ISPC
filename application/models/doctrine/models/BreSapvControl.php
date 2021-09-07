<?php

	Doctrine_Manager::getInstance()->bindComponent('BreSapvControl', 'MDAT');

	class BreSapvControl extends BaseBreSapvControl {

		public function get_bre_sapv_controlsheet($ipids, $date, $master_price_list, $active_days, $full_hospital_days, $hospiz_days_arr)
		{
			if(is_array($ipids))
			{
				if(count($ipids) > 0)
				{
					$ipid = $ipids;
				}
				else
				{
					$ipid[] = '9999999999999';
				}
			}
			else
			{
				$ipid = array($ipids);
			}

			$query = Doctrine_Query::create()
				->select('*')
				->from('BreSapvControl')
				->whereIn('ipid', $ipid)
				->andWhere('MONTH(date) = MONTH("' . $date . '")')
				->andWhere('YEAR(date) = YEAR("' . $date . '")');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					if(count($ipid) == '1' && !is_array($ipids))
					{
						$formated_date = date('Y-m-d', strtotime($v_res['date']));
						if(in_array($formated_date, $active_days) && !in_array($formated_date, $full_hospital_days) && !in_array($formated_date, $hospiz_days_arr))
						{
							$master_data[$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['shortcut']][$formated_date]['qty'] = $v_res['qty'];
							$master_data[$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data['totals'][$v_res['shortcut']] += $v_res['qty'];
							ksort($master_data[$v_res['shortcut']]);
						}
						else
						{
							$master_data[$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['shortcut']][$formated_date]['qty'] = '0';
							$master_data[$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data['totals'][$v_res['shortcut']] +='0';
							ksort($master_data[$v_res['shortcut']]);
						}
					}
					else //multiple patients
					{
						$formated_date = date('Y-m-d', strtotime($v_res['date']));
//						print_r($active_days);
//						print_r($full_hospital_days);
//						print_r($hospiz_days_arr);
//						exit;
//						print_r($formated_date.' - '.$v_res['shortcut'].' - (A: ');
//						var_dump(in_array($formated_date, $active_days[$v_res['ipid']]));
//						print_r(' ) - ( H: ');
//						var_dump(!in_array($formated_date, $full_hospital_days[$v_res['ipid']]));
//						print_r(' ) - ( Hz: ');
//						var_dump(!in_array($formated_date, $hospiz_days_arr[$v_res['ipid']]));
//						print_r(' ); \n');
						if(in_array($formated_date, $active_days[$v_res['ipid']]) && !in_array($formated_date, $full_hospital_days[$v_res['ipid']]) && !in_array($formated_date, $hospiz_days_arr[$v_res['ipid']]))
						{
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['qty'] = $v_res['qty'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data[$v_res['ipid']]['totals'][$v_res['shortcut']] += $v_res['qty'];
							ksort($master_data[$v_res['ipid']][$v_res['shortcut']]);
						}
						else
						{
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['qty'] = '0';
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data[$v_res['ipid']]['totals'][$v_res['shortcut']] +='0';
							ksort($master_data[$v_res['ipid']][$v_res['shortcut']]);
						}
					}
				}

				return $master_data;
			}
			else
			{
				return false;
			}
		}

		public function get_bre_sapv_controlsheet_report_period($ipids, $report_period, $master_price_list, $active_days, $hospital_hospiz_days)
		{
			if(count($ipids) > 0)
			{
				$ipid = $ipids;
			}
			else
			{
				$ipid[] = '9999999999999';
			}

			$date_sql = " ";
			foreach($report_period as $k => $date)
			{
				$start_date_time = date('Y-m-d H:i:s', strtotime($date['start']));
				$end_date_time = date('Y-m-d H:i:s', strtotime($date['end']));
				$date_sql .= ' ( DATE(date) >= DATE("' . $start_date_time . '") AND DATE(date) <= DATE("' . $end_date_time . '") )  OR ';
			}

			$query = Doctrine_Query::create();
			$query->select('*');
			$query->from('BreSapvControl');
			$query->whereIn('ipid', $ipid);
			$query->andWhere('' . substr($date_sql, 0, -4) . '');
//			echo $query->getSqlQuery(); exit;

			$q_res = $query->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{ {
						$formated_date = date('Y-m-d', strtotime($v_res['date']));
						if(in_array($formated_date, $active_days[$v_res['ipid']]) && !in_array($formated_date, $hospital_hospiz_days[$v_res['ipid']]))
						{
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['qty'] = $v_res['qty'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data[$v_res['ipid']]['totals'][$v_res['shortcut']] += $v_res['qty'];
							ksort($master_data[$v_res['ipid']][$v_res['shortcut']]);
						}
						else
						{
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['qty'] = '0';
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data[$v_res['ipid']]['totals'][$v_res['shortcut']] +='0';
							ksort($master_data[$v_res['ipid']][$v_res['shortcut']]);
						}
					}
				}

				return $master_data;
			}
			else
			{
				return false;
			}
		}

		public function get_bre_sapv_controlsheetnew($ipids, $date, $master_price_list, $active_days = false, $full_hospital_days = false, $hospiz_days_arr = false, $patients_days = false)
		{
			if(is_array($ipids))
			{
				if(count($ipids) > 0)
				{
					$ipid = $ipids;
				}
				else
				{
					$ipid[] = '9999999999999';
				}
			}
			else
			{
				$ipid = array($ipids);
			}

			$query = Doctrine_Query::create()
				->select('*')
				->from('BreSapvControl')
				->whereIn('ipid', $ipid)
				->andWhere('MONTH(date) = MONTH("' . $date . '")')
				->andWhere('YEAR(date) = YEAR("' . $date . '")');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					if(count($ipid) == '1' && !is_array($ipids))
					{
						$formated_date = date('Y-m-d', strtotime($v_res['date']));
						$formated_date_alt = date('d.m.Y', strtotime($v_res['date']));
						if(in_array($formated_date_alt, $active_days) && !in_array($formated_date_alt, $full_hospital_days) && !in_array($formated_date_alt, $hospiz_days_arr))
						{
							$master_data[$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['shortcut']][$formated_date]['qty'] = $v_res['qty'];
							$master_data[$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data['totals'][$v_res['shortcut']] += $v_res['qty'];
							ksort($master_data[$v_res['shortcut']]);
						}
						else
						{
							$master_data[$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['shortcut']][$formated_date]['qty'] = '0';
							$master_data[$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data['totals'][$v_res['shortcut']] +='0';
							ksort($master_data[$v_res['shortcut']]);
						}
					}
					else //multiple patients
					{
						$formated_date = date('Y-m-d', strtotime($v_res['date']));
						$formated_date_alt = date('d.m.Y', strtotime($v_res['date']));

//						if(in_array($formated_date_alt, $active_days[$v_res['ipid']]) && !in_array($formated_date_alt, $full_hospital_days[$v_res['ipid']]) && !in_array($formated_date_alt, $hospiz_days_arr[$v_res['ipid']]))
						if(in_array($formated_date_alt, $patients_days[$v_res['ipid']]['active_days']) && !in_array($formated_date_alt, $patients_days[$v_res['ipid']]['hospital']['real_days_cs']) && !in_array($formated_date_alt, $patients_days[$v_res['ipid']]['hospiz']['real_days_cs']))
						{
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['qty'] = $v_res['qty'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data[$v_res['ipid']]['totals'][$v_res['shortcut']] += $v_res['qty'];
							ksort($master_data[$v_res['ipid']][$v_res['shortcut']]);
						}
						else
						{
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['qty'] = '0';
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['date'] = $v_res['date'];
							$master_data[$v_res['ipid']]['totals'][$v_res['shortcut']] +='0';
							ksort($master_data[$v_res['ipid']][$v_res['shortcut']]);
						}
					}
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