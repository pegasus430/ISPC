<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientCareservices', 'MDAT');

	class PatientCareservices extends BasePatientCareservices { 
	    
	    
        public function get_patient_services($ipid, $date,$user_id=false){

            $days = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCareservices')
            ->where("DATE(date) =  DATE('" . $date . "')")
            ->andWhere("ipid LIKE '" . $ipid."' ")
            ->andWhere("isdelete = 0");
            
            if($user_id && strlen($user_id) > 0)
            {
                $days->andWhere(" create_user = " . $user_id);
            }
            $days->orderBy('date ASC');
            $users_q_array = $days->fetchArray();
            
            if($users_q_array)
            {
                return $users_q_array;
            }
            else
            {
                return false;
            }       
            
        }
	    
	}

?>