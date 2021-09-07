<?php

	Doctrine_Manager::getInstance()->bindComponent('HospitalReasons', 'SYSDAT');

	class HospitalReasons extends BaseHospitalReasons {
		
		public function getclienthospreasons($clientid, $order = false)
		{
		    //ISPC-2612 Ancuta 27.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('HospitalReasons',$clientid);
			$hr = Doctrine_Query::create()
			->select("*")
			->from('HospitalReasons')
			->where('clientid = ?', $clientid)
			->andWhere('isdelete = "0"');
			if($client_is_follower){//ISPC-2612 Ancuta 27.06.2020
			    $hr->andWhere('connection_id is NOT null');
			    $hr->andWhere('master_id is NOT null');
			}
			
			if($order)
			{
				$hr->orderBy('reason ASC');
			}
			
			$hrarray = $hr->fetchArray();
			
			if($hrarray)
			{
				return $hrarray;
			}			
		}
	}
	
?>