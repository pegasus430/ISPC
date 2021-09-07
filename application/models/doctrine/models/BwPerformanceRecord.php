<?php

	Doctrine_Manager::getInstance()->bindComponent('BwPerformanceRecord', 'MDAT');

	class BwPerformanceRecord extends BaseBwPerformanceRecord { 
		
		
		public function get_bw_performance_record($ipid, $date, $master_price_list,$patient_days2locationtypes)
		{
			if(empty($ipid)){
				return false;
			}
			
			if( ! is_array($ipid))
			{
				$ipid = array($ipid);
			}
		
			$query = Doctrine_Query::create()
			->select('*')
			->from('BwPerformanceRecord')
			->whereIn('ipid', $ipid)
			->andWhere('isdelete = 0')
			->andWhere('MONTH(date) = MONTH("' . $date . '")')
			->andWhere('YEAR(date) = YEAR("' . $date . '")');
			$q_res = $query->fetchArray();
		
			
			if($q_res)
			{
				$day_location_type = "";
				foreach($q_res as $k_res => $v_res)
				{
					$formated_date = date('Y-m-d', strtotime($v_res['date']));
					$day_location_type = $patient_days2locationtypes[$v_res['ipid']][date('d.m.Y',strtotime($v_res['date']))];
					
					if($v_res['shortcut'] == "37b1")
					{
						if($v_res['pay_days'] == '1' && $v_res['qty']=="1")
						{
							$master_data[$formated_date][$v_res['shortcut']]['pay_days'] = "1";
							$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
							$master_data[$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
							$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
							$master_data[$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
							$master_data[$formated_date][$v_res['shortcut']]['qty'] = "1";
						}
						elseif($v_res['pay_days'] == '0' && $v_res['qty']=="1")
						{
							$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
							$master_data[$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
							$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
							$master_data[$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
							$master_data[$formated_date][$v_res['shortcut']]['qty'] = "1";
						}
						elseif($v_res['pay_days'] == '0' && $v_res['qty']=="0")
						{
							$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
							$master_data[$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
							$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
							$master_data[$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
							$master_data[$formated_date][$v_res['shortcut']]['qty'] = "0";
						}
					}
					else //not weekprice shortcut
					{
						if($v_res['qty'] > "0"){
							
							$qty = $v_res['qty'];
		
							$master_data[$formated_date][$v_res['shortcut']]['qty'] += $qty;
							$master_data[$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
							$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
							$master_data[$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
							$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
						} else {
							$qty = $v_res['qty'];
		
							$master_data[$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
							$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
							$master_data[$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
							$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
							$master_data[$formated_date][$v_res['shortcut']]['qty'] = "";
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
		
		public function get_multiple_bw_performance_record_in_period($ipid, $period = false, $master_price_list, $patient_days2locationtypes=array())
		{
			if( empty($ipid)){
				return false;
			}
			
			if( ! is_array($ipid))
			{
				$ipid = array($ipid);
			}
			
			foreach($ipid as $k_data => $v_data_ipid)
			{
				$start[$v_data_ipid] = $period[$v_data_ipid][0];
				$end[$v_data_ipid] = end($period[$v_data_ipid]);
				$sql_data[] = "(`ipid` LIKE '" . $v_data_ipid . "' AND (`date` BETWEEN '" . $start[$v_data_ipid]  . "' AND '" . $end[$v_data_ipid] . "') )";
			}
			
			$sql_str = implode(' OR ', $sql_data);
			
			$bw_query = Doctrine_Query::create()
			->select("*")
			->from('BwPerformanceRecord')
			->where('isdelete = 0')
			->orderBy('date ASC');
			if($sql_data)
			{
				$bw_query->andWhere($sql_str);
			}
			$bw_data = $bw_query->fetchArray();
			
			if($bw_data)
			{
				$day_location_type = "";
				foreach($bw_data as $k_res => $v_res)
				{
					$formated_date = date('Y-m-d', strtotime($v_res['date']));
					$day_location_type = $patient_days2locationtypes[$v_res['ipid']][date('d.m.Y',strtotime($v_res['date']))];
					
						if($v_res['shortcut'] == "37b1")
						{
							if($v_res['pay_days'] == '1' && $v_res['qty']=="1")
							{
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['pay_days'] = "1";
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['qty'] = "1";
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['booking_account'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['booking_account'];
							}
							elseif($v_res['pay_days'] == '0' && $v_res['qty']=="1")
							{
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['qty'] = "1";
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['booking_account'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['booking_account'];
							}
						}
						else //not weekprice shortcut
						{
							if($v_res['qty'] > "0"){
								
								$qty = $v_res['qty'];
			
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['qty'] = $qty;
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['booking_account'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['booking_account'];
							} else {
								$qty = $v_res['qty'];
			
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['qty'] = "";
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['location_type'] = $day_location_type;
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['price'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['price_list'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['list'];
								$master_data[$v_res['ipid']][$formated_date][$v_res['shortcut']]['booking_account'] = $master_price_list[$v_res['ipid']][$formated_date][0][$day_location_type][$v_res['shortcut']]['booking_account'];
								
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
		
		
		
		
		public function get_bw_performance_record_II($ipid, $date, $weekprice_shortcuts, $master_price_list)
		{
			if(is_array($ipid))
			{
				if(count($ipid) > 0)
				{
					$ipid = $ipid;
				}
				else
				{
					$ipid[] = '9999999999999';
				}
			}
			else
			{
				$ipid = array($ipid);
			}
		
			$query = Doctrine_Query::create()
			->select('*')
			->from('BwPerformanceRecord')
			->whereIn('ipid', $ipid)
			->andWhere('isdelete = 0')
			->andWhere('MONTH(date) = MONTH("' . $date . '")')
			->andWhere('YEAR(date) = YEAR("' . $date . '")');
			$q_res = $query->fetchArray();
		
// 			print_r($q_res); exit;
			
			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$formated_date = date('Y-m-d', strtotime($v_res['date']));
					if($v_res['shortcut'] == "37b1")
					{
						if($v_res['pay_days'] == '1' && $v_res['pay_days']=="1")
						{
							$master_data[$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['shortcut']][$formated_date]['qty'] += 1;
							$master_data['totals'][$v_res['shortcut']] += '1';
						}
						elseif($v_res['pay_days'] == '0' && $v_res['pay_days']=="1")
						{
							$master_data[$v_res['shortcut']]['sub_group_days'][] = $formated_date;
						}
					}
					else //not weekprice shortcut
					{
						$qty = $v_res['qty'];
	
						$master_data[$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
						$master_data[$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
						$master_data[$v_res['shortcut']][$formated_date]['qty'] += $qty;
						$master_data['totals'][$v_res['shortcut']] += $qty;
					}
				}
				print_r($master_data); exit;
				return $master_data;
			}
			else
			{
				return false;
			}
		}
	}

?>