<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationIntervals', 'SYSDAT');

	class MedicationIntervals extends BaseMedicationIntervals {
	    
	    public function client_medication_intervals($client,$type=false,$except_type =false)
	    {
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('MedicationIntervals')
	        ->where('clientid =  '.$client)
	        ->andWhere('isdelete = 0');
	        if($type){
    	        $query->andWhere('medication_type= "'.$type.'" ');
	        }
	        if($except_type){
    	        $query->andWhere('medication_type <>  '.$except_type.'" ');
	        }
	        $query->orderBy('time_interval ASC');
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
	    
	    public function client_saved_medication_intervals($client,$types = false)
	    {
	        
	        $modules = new Modules();
	        $individual_medication_time_m = $modules->checkModulePrivileges("141", $client);
	        if($individual_medication_time_m){
	            $individual_medication_time = 1;
	        }else {
	            $individual_medication_time = 0;
	        }
	         
	        $query = Doctrine_Query::create()
	        ->select('medication_type,time_interval')
	        ->from('MedicationIntervals')
	        ->where('clientid =  '.$client)
	        ->andWhere('isdelete = 0');
	        if($individual_medication_time == "0"){
    	        $query->andWhere('medication_type = "all"');
	        }
	        $query->orderBy('time_interval ASC');
	        $q_res = $query->fetchArray();
	        
            if($types){
    	        if($q_res)
    	        {
   	               $int = 1;
    	                $mint = 1;
    	            foreach($q_res as $k=>$ts)
    	            {
    	            	//ISPC-2329 Carmen 13.01.2020
    	                //$full_intervals_array[$ts['medication_type']][] = date("H:i",strtotime($ts['time_interval']));
    	            	$full_intervals_array[$ts['medication_type']][] = substr($ts['time_interval'], 0, 5);
    	            }
    	            
    	            foreach($types as  $m_type)
    	            {
    	                if(empty($full_intervals_array[$m_type]))
    	                {
    	                    if(!empty($full_intervals_array["all"]))
    	                    {
                                $intervals_array[$m_type]['1'] =  $full_intervals_array['all'][0];
                                $intervals_array[$m_type]['2'] =  $full_intervals_array['all'][1];
                                $intervals_array[$m_type]['3'] =  $full_intervals_array['all'][2];
                                $intervals_array[$m_type]['4'] =  $full_intervals_array['all'][3];
    	                    } 
    	                    else 
    	                    {
            	                $intervals_array[$m_type]['1'] = "08:00";
            	                $intervals_array[$m_type]['2'] = "12:00";
            	                $intervals_array[$m_type]['3'] = "18:00";
            	                $intervals_array[$m_type]['4'] = "22:00";
    	                    }
    	                }
    	                else
    	                {
        	                $intervals_array[$m_type]['1'] =  $full_intervals_array[$m_type][0];
        	                $intervals_array[$m_type]['2'] =  $full_intervals_array[$m_type][1];
        	                $intervals_array[$m_type]['3'] =  $full_intervals_array[$m_type][2];
        	                $intervals_array[$m_type]['4'] =  $full_intervals_array[$m_type][3];
    	                }
    	            }
    	            
    	        }
    	        else
    	        {
    	            foreach($types as  $m_type)
    	            {
        	            $intervals_array[$m_type]['1'] = "08:00";
        	            $intervals_array[$m_type]['2'] = "12:00";
        	            $intervals_array[$m_type]['3'] = "18:00";
          	            $intervals_array[$m_type]['4'] = "22:00";
    	            }
    	        }
            }
	        else
	        {
	                 
    	        if($q_res)
    	        {
    	            $int = 1;
    	            foreach($q_res as $k=>$ts)
    	            {
    	            	//ISPC-2329 Carmen 13.01.2020
    	                //$intervals_array[$int] = date("H:i",strtotime($ts['time_interval']));
    	            	$intervals_array[$int] = substr($ts['time_interval'], 0, 5);
    	                $int++;
    	            }
    	        }
    	        else
    	        {
    	            $intervals_array['1'] = "08:00";
    	            $intervals_array['2'] = "12:00";
    	            $intervals_array['3'] = "18:00";
    	            $intervals_array['4'] = "22:00";
    	        }
	        }
	        
	        return $intervals_array;
	    }
	    
	    
	    
	    
	    
	}

?>