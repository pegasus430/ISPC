<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationOptions', 'SYSDAT');

	class MedicationOptions extends BaseMedicationOptions {
	    
	    public function client_medication_options($client,$med_type = false,$has_time_schedule  = false)
	    {
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('MedicationOptions')
	        ->where('clientid =  '.$client)
	        ->andWhere('isdelete = 0');
	        if($med_type) {
	           $query->andWhere('medication_type = ?', "'.$med_type.'" );
	        }
	        if($has_time_schedule){
	           $query->andWhere('time_schedule = 1');
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
	    
	    public function client_saved_medication_options($client)
	    {
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('MedicationOptions')
	        ->where('clientid =  ?', $client) //ISPC-2547 Carmen 03.03.2020// Maria:: Migration ISPC to CISPC 08.08.2020
	        ->andWhere('isdelete = 0');
	        $q_res = $query->fetchArray();
	         
	        
	        if($q_res)
	        {
	            foreach($q_res as $k=>$mo)
	            {
	                $options[$mo['medication_type']]['time_schedule'] = $mo['time_schedule']; 
	            }
	        } 
	        else 
	        { //Default
	            $options["actual"]['time_schedule'] = "1";
	            $options["isbedarfs"]['time_schedule'] = "0";
	            $options["iscrisis"]['time_schedule'] = "0";
	            $options["isivmed"]['time_schedule'] = "1";
	            $options["isnutrition"]['time_schedule'] = "0";
	            $options["isschmerzpumpe"]['time_schedule'] = "0";
                $options["ispumpe"]['time_schedule'] = "0";//ISPC-2871,Elena,12.04.2021
	            $options["treatment_care"]['time_schedule'] = "0";
	        }
	         
	        return $options;
	    }
	    
	    
	    
	    
	    
	}

?>