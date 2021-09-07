<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage14', 'MDAT');

	class Anlage14 extends BaseAnlage14 {
		
		


		public function get_period_anlage14_report($ipids, $period = false)
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
				
			if(count($ipid) >'0' && !empty($ipid) && $period)
			{
		
				$date_sql = " ";
				foreach($period as $k => $date)
				{
					$start_date_time = date('Y-m-d H:i:s', strtotime($date['start']));
					$end_date_time = date('Y-m-d H:i:s', strtotime($date['end']));
					$date_sql .= ' ( DATE(date) >= DATE("' . $start_date_time . '") AND DATE(date) <= DATE("' . $end_date_time . '") )  OR ';
				}
		
				$query = Doctrine_Query::create()
				->select('*')
				->from('Anlage14')
				->whereIn('ipid',$ipid);
				$query->andWhere('' . substr($date_sql, 0, -4) . '');
				$q_res = $query->fetchArray();
		
				if($q_res)
				{
					foreach($q_res as $k_res => $v_res)
					{
						$anlage14_data[$v_res['ipid']][date('d.m.Y', strtotime($v_res['date']))] = $v_res;
					}
		
					return $anlage14_data;
				}
				else
				{
					return false;
				}
			}
		}
		
	}

?>