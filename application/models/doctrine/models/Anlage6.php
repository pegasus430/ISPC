<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage6', 'MDAT');

	class Anlage6 extends BaseAnlage6 {

		public function get_anlage_shortcut($ipid, $date, $shortcut)
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
				->from('Anlage6')
				->whereIn('ipid', $ipid)
				->andWhere('YEAR(date) = YEAR("' . $date . '")')
				->andWhere('MONTH(date) = MONTH("' . $date . '")')
				->andWhere('shortcut = "' . strtoupper($shortcut) . '"')
				->orderBy('date ASC');

			$q_res = $query->fetchArray();

			if($q_res)
			{
				return $q_res;
			}
			else
			{
				return false;
			}
		}

		public function get_all_anlage_shortcut($ipid, $shortcut)
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
				->from('Anlage6')
				->whereIn('ipid', $ipid)
				->andWhere('shortcut = "' . strtoupper($shortcut) . '"')
				->orderBy('date ASC');

			$q_res = $query->fetchArray();

			if($q_res)
			{
				return $q_res;
			}
			else
			{
				return false;
			}
		}

	}

?>