<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientSymptoms', 'MDAT');

	class ClientSymptoms extends BaseClientSymptoms {

		public static function get_client_symptoms($clientid,$sym_id = false,$grouped_by_group = false)
		{
		    // ISPC-2612 Ancuta 30.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('ClientSymptoms', $clientid);
		    // --
		    
		    
			$q = Doctrine_Query::create()
				->select('*')
				->from("ClientSymptoms")
				->where("clientid= ?", $clientid )
				->andWhere("isdelete = 0");
				if($client_is_follower){//ISPC-2612 Ancuta 30.06.2020
				    $q->andWhere('connection_id is NOT null');
				    $q->andWhere('master_id is NOT null');
				}
			if($sym_id){
			    $q->andWhere('id=?',$sym_id);
			}
			
			$symptoms = $q->fetchArray();

			if(!empty($symptoms))
			{
			    if($grouped_by_group){
    				foreach($symptoms as $k=>$s_value)
    				{
    					$symptoms_array[$s_value['group_id']][] = $s_value;
    				}
			    } 
			    else
			    {
    				foreach($symptoms as $k=>$s_value)
    				{
    					$symptoms_array[$s_value['id']] = $s_value;
    				}
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