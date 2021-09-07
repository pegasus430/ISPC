<?php

	Doctrine_Manager::getInstance()->bindComponent('BwPerformanceRecordFlatrate', 'MDAT');

	class BwPerformanceRecordFlatrate extends BaseBwPerformanceRecordFlatrate { 
		
		
		public function get_bw_performance_record($ipid, $date, $master_price_list)
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
							if($v_res['pay_days'] == '1' && $v_res['qty']=="1")
							{
								$master_data[$formated_date][$v_res['shortcut']]['pay_days'] = "1";
								$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
								$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
								$master_data[$formated_date][$v_res['shortcut']]['qty'] = "1";
	// 							$master_data['totals'][$v_res['shortcut']] += '1';
							}
							elseif($v_res['pay_days'] == '0' && $v_res['qty']=="1")
							{
// 								$master_data[$formated_date][$v_res['shortcut']]['sub_group_days'][] = $formated_date;
								$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
								$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
								$master_data[$formated_date][$v_res['shortcut']]['qty'] = "1";
							}
						}
						else //not weekprice shortcut
						{
							if($v_res['qty'] > "0"){
								
								$qty = $v_res['qty'];
			
								$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
								$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
								$master_data[$formated_date][$v_res['shortcut']]['qty'] += $qty;
		// 						$master_data['totals'][$v_res['shortcut']] += $qty;
							} else {
								$qty = $v_res['qty'];
			
								$master_data[$formated_date][$v_res['shortcut']]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
								$master_data[$formated_date][$v_res['shortcut']]['shortcut'] = $v_res['shortcut'];
								$master_data[$formated_date][$v_res['shortcut']]['qty'] = "";
		// 						$master_data['totals'][$v_res['shortcut']] += $qty;
								
							}
						}
 
				}
// 				print_r($master_data); exit;
				
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