<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanDosageIntervals', 'MDAT');

	class PatientDrugPlanDosageIntervals extends BasePatientDrugPlanDosageIntervals {

	public function get_patient_dosage_intervals($ipid,$clientid,$types = false)
	{
	    

    	    $modules = new Modules();
    	    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
    	    if($individual_medication_time_m){
    	        $individual_medication_time = 1;
    	    }else {
    	        $individual_medication_time = 0;
    	    }
	    
	    
	    
	        $types[]="all";
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanDosageIntervals')
	        ->where('ipid =  "'.$ipid.'"')
	        ->andWhere('isdelete = 0');
	        
	        if($types){
    	        $query->andWhereIn('medication_type ',$types);
	        }
	        $query->orderBy('time_interval ASC');
	        $q_res = $query->fetchArray();
	        
	        $client_intervals = MedicationIntervals::client_saved_medication_intervals($clientid,$types);
 
	        
	        if($q_res && !empty($q_res))
	        {
	            foreach($q_res as $k=>$ts)
	            {
	            	//ISPC-2329 Carmen 13.01.2020
	                //$intervals_array['patient'][$ts['medication_type']][$ts['id']] = date("H:i",strtotime($ts['time_interval']));
	            	$intervals_array['patient'][$ts['medication_type']][$ts['id']] = substr($ts['time_interval'], 0, 5);
	            }
	            
	            if($individual_medication_time == "0"){
	                foreach($types as $mtype){
	                    if(!empty($intervals_array['patient'][$mtype]) && in_array($mtype,array("actual","isivmed"))   ) {
	                        $intervals_array['patient'][$mtype] = array_values($intervals_array['patient']['all']);
	                    }
	                }
	            }

	            if($types){
	                foreach($types as $mtype){
	                    if(empty($intervals_array['patient'][$mtype])){
	                        if($mtype == "actual" || $mtype == "isivmed" ){ // if data was saved get saved data  for this blocks 
    	                        $intervals_array['patient'][$mtype] = array_values($intervals_array['patient']['all']); 
	                        } else { // - get details from client
    	                        $intervals_array['patient'][$mtype] = $client_intervals[$mtype]; 
	                        }
   	                        $intervals_array['patient']['new'][] = $mtype;;
	                    }
	                }
	            }
	        }
	        else
	        {// - get details from client
	            $intervals_array['client'] = $client_intervals;
	        }
	        
	        return $intervals_array;
	    }
	    
	}
?>