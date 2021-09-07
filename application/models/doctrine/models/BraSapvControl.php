<?php

	Doctrine_Manager::getInstance()->bindComponent('BraSapvControl', 'MDAT');

	class BraSapvControl extends BaseBraSapvControl {

		public function get_bra_sapv_controlsheet($ipid, $date, $weekprice_shortcuts, $master_price_list)
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
				->from('BraSapvControl')
				->whereIn('ipid', $ipid)
				->andWhere('MONTH(date) = MONTH("' . $date . '")')
				->andWhere('YEAR(date) = YEAR("' . $date . '")');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					if($v_res['value'] == '1')
					{
						$formated_date = date('Y-m-d', strtotime($v_res['date']));
						if(in_array($v_res['shortcut'], $weekprice_shortcuts))
						{
							if($v_res['starter'] == '1')
							{
								$master_data[$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
								$master_data[$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
								$master_data[$v_res['shortcut']][$formated_date]['qty'] += 1;
								$master_data['totals'][$v_res['shortcut']] += '1';
							}
							else
							{
								$master_data[$v_res['shortcut']]['sub_group_days'][] = $formated_date;
							}
						}
						else //not weekprice shortcut
						{
							if($v_res['qty'] == 0)
							{
								$qty = '1';
							}
							else
							{
								$qty = $v_res['qty'];
							}

							$master_data[$v_res['shortcut']][$formated_date]['price'] = $master_price_list[$formated_date][0][$v_res['shortcut']]['price'];
							$master_data[$v_res['shortcut']][$formated_date]['shortcut'] = $v_res['shortcut'];
							$master_data[$v_res['shortcut']][$formated_date]['qty'] += $qty;
							$master_data['totals'][$v_res['shortcut']] += $qty;
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