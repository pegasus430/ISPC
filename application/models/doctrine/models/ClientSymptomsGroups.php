<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientSymptomsGroups', 'MDAT');

	class ClientSymptomsGroups extends BaseClientSymptomsGroups {

		public function get_client_symptoms_groups($clientid,$group_id = false)
		{
		    // ISPC-2612 Ancuta 30.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('ClientSymptomsGroups', $clientid);
		    // -- 
		    
			$q = Doctrine_Query::create()
				->select('*')
				->from("ClientSymptomsGroups")
				->where("clientid= ?", $clientid )
				->andWhere("isdelete = 0");
				if($client_is_follower){//ISPC-2612 Ancuta 30.06.2020
				    $q->andWhere('connection_id is NOT null');
				    $q->andWhere('master_id is NOT null');
				}
			if($group_id){
			    $q->andWhere('id=?',$group_id);
			}
			
			$symptoms = $q->fetchArray();

			if(!empty($symptoms))
			{
			    
				foreach($symptoms as $k=>$s_value)
				{
					$symptoms_array[$s_value['id']] = $s_value;
			    }

				return $symptoms_array;
			}
			else
			{
				return false;
			}
		}

	}

?>