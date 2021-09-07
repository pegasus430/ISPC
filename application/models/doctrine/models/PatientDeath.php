<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDeath', 'MDAT');

	class PatientDeath extends BasePatientDeath {

		public function getPatientDeath($ipid)
		{

			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientDeath')
				->where("ipid='" . $ipid . "' and isdelete='0'");
			$disarr = $loc->fetchArray();
			if($disarr)
			{
				return $disarr;
			}
		}

		public function get_patients_death($ipids)
		{
			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientDeath')
				->whereIn('ipid', $ipids)
				->andWhere('isdelete = "0"');
			$disarr = $loc->fetchArray();
			if($disarr)
			{
				return $disarr;
			}
		}

		public function getPatientsDeathDetails($ipids)
		{
			$ipid_str = '99999999,';
			foreach($ipids as $ipid)
			{
				$ipid_str .= '"' . $ipid['ipid'] . '",';
				$ipidz[$ipid['ipid']] = $ipid;
				$ipidz_simple[] = $ipid['ipid'];
			}

			$discharge = Doctrine_Query::create()
				->select("d.ipid, date_format(d.death_date,'%d.%m.%Y') as death_date, unix_timestamp(d.death_date) as death_date_ut")
				->from('PatientDeath d')
				->whereIn('d.ipid', $ipidz_simple)
				->andWhere('d.isdelete = 0')
				->orderBy('d.death_date');
//			echo $discharge->getSqlQuery();
			$discharge_data = $discharge->fetchArray();

			foreach($discharge_data as $discharge_item)
			{
				$patient_data[$discharge_item['ipid']]['death_date'] = $discharge_item['death_date'];
			}
			return $patient_data;
		}

		public function getDeadPatientsDot($ipids, $qStart, $qEnd)
		{

			if(count($ipids) == 0)
			{
				$ipids[] = '999999999';
			}

			$dead = Doctrine_Query::create()
				->select("*")
				->from('PatientDeath')
				->whereIn('ipid', $ipids)
				->andWhere('death_date BETWEEN "' . date("Y-m-d H:i:s", strtotime($qStart)) . '" and "' . date("Y-m-d H:i:s", strtotime($qEnd)) . '"')
				->andWhere('isdelete = 0')
				->orderBy('death_date');
//			echo $dead->getSqlQuery();
			$dead_data = $dead->fetchArray();

			if(count($dead_data) == 0)
			{
				$deadIpids[] = '999999999';
			}
			foreach($dead_data as $deadPat)
			{
				$deadIpids[] = $deadPat['ipid'];
			}

			return $deadIpids;
		}

	}

?>
