<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationUnit', 'SYSDAT');

	class MedicationUnit extends BaseMedicationUnit {
	    
	    public function client_medication_unit($client)
	    {
	        // ISPC-2612 Ancuta 01.07.2020
	        $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('MedicationUnit', $client);
	        // --
	        
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('MedicationUnit')
	        ->where('clientid =  '.$client)
	        ->andWhere('isdelete = 0')
	        ->andWhere('extra = 0');
	        if($client_is_follower){
	            $query->andWhere('connection_id is NOT null');
	            $query->andWhere('master_id is NOT null');
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