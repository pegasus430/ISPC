<?php

	Doctrine_Manager::getInstance()->bindComponent('SettlementServices', 'MDAT');

	class SettlementServices extends BaseSettlementServices {
		 
		public function client_settlement_services($client,$exclude_deleted = true )
		{
			$query = Doctrine_Query::create()
			->select('*')
			->from('SettlementServices')
			->where('clientid =  '.$client);
			if($exclude_deleted){
				$query->andWhere('isdelete = 0');
			}
			//$query->andWhere('extra = 0');
			$q_res = $query->fetchArray();
			 
			if($q_res )
			{
				return $q_res;
			}
			else
			{
				return false;
			}
		}	
	
		public function validate_settlement_services($client, $action_id, $description ){
			
			$fdoc1 = Doctrine_Query::create();
			$fdoc1->select('count(*) AS count');
			$fdoc1->from('SettlementServices');
			$fdoc1->where("clientid = ?", $client);
			$fdoc1->andWhere("action_id = ?  ", $action_id );
			$fdoc1->andWhere("description = ?  ", $description);
			$fdocexec = $fdoc1->execute();
			$fdocarray = $fdocexec->toArray();
			if (! ((int)$fdocarray[0]['count'] > 0)){
				return false;
			}else{
				return true;
			}
			
		}
	
	}