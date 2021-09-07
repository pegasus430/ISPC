<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationDosageform', 'SYSDAT');

	class MedicationDosageform extends BaseMedicationDosageform {
	    
	    public function client_medication_dosage_form($client, $allow_extra = false)
	    {
	        if( empty($client) ){
	           return false;    
	        }
	        // ISPC-2612 Ancuta 01.07.2020
	        $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('MedicationDosageform', $client);
	        // --
	        
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('MedicationDosageform')
	        ->where('clientid =  ?', $client)
	        ->andWhere('isdelete = 0');
	        if($client_is_follower){
	            $query->andWhere('connection_id is NOT null');
	            $query->andWhere('master_id is NOT null');
	        }
	        if( ! $allow_extra){ //ISPC-2247 
    	        $query->andWhere('extra = 0');
	        }
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
	    
	}

?>