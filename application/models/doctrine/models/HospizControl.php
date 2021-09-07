<?php

	Doctrine_Manager::getInstance()->bindComponent('HospizControl', 'MDAT');

	class HospizControl extends BaseHospizControl {

		public function get_hospiz_controlsheet($ipids, $start_date)
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
				->from('HospizControl')
				->whereIn('ipid', $ipid)
				->andWhere('MONTH(date) = MONTH("' . $start_date . '")')
				->andWhere('YEAR(date) = YEAR("' . $start_date . '")');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$hospiz_control_data[$v_res['shortcut']][date('d.m.Y', strtotime($v_res['date']))] = $v_res['qty'];
				}

				return $hospiz_control_data;
			}
			else
			{
				return false;
			}
		}
		
		public function get_multiple_hospiz_controlsheet($ipids, $start_date)
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
				->from('HospizControl')
				->whereIn('ipid', $ipid)
				->andWhere('MONTH(date) = MONTH("' . $start_date . '")')
				->andWhere('YEAR(date) = YEAR("' . $start_date . '")');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$hospiz_control_data[$v_res['ipid']][$v_res['shortcut']][date('d.m.Y', strtotime($v_res['date']))] = $v_res['qty'];
				}

				return $hospiz_control_data;
			}
			else
			{
				return false;
			}
		}

	}

?>