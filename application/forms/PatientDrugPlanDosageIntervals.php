<?php
require_once ("Pms/Form.php");

class Application_Form_PatientDrugPlanDosageIntervals extends Pms_Form
{
    public function validate($post,$medications)
    {
        $Tr = new Zend_View_Helper_Translate();
        
        $error = array();
        $val = new Pms_Validation();


        foreach($medications as $k=>$mtype)
        {
            for($i = 1; $i < count($post['interval'][$mtype]); $i++)
            {
                if(empty($error)){
                    if(!$val->isstring($post['interval'][$mtype][$i]['time']))
                    {
                        $this->error_message[$i]['timeerror'] = $Tr->translate('all intervals inputs are mandatory');
            			$error[] = 1;
                    }
                }
                if(empty($error)){
                    
                    if(strtotime($post['interval'][$mtype][$i]['time']) < strtotime($post['interval'][$mtype][$i - 1]['time']))
                    {
                        $this->error_message[$i]['ordererror'] = $Tr->translate('intervals must be consecutive');
        				$error[] = 2;
                    }
                }
            }
        }

        if(empty($error))
        {
            return true;
        }
        
        return false;
        
    }
    
    public function validate_v2($post)
    {
    	$Tr = new Zend_View_Helper_Translate();
    
    	$error = array();
    	$val = new Pms_Validation();
    
    	
    	foreach($post['interval'] as $mtype=>$value)
    	{
    		if (count($value) < 4) {
    			continue;
    		}

    		$i = 0;
    		
    		$previous_hour = -1;
    		
    		foreach($post['interval'][$mtype] as $one_hour) { 
    			
    			if(! empty($error)){
    				break ;
    			}
    			
    			
    			if(!$val->isstring($one_hour['time']))
    			{

    				$this->error_message[$i]['timeerror'] = $Tr->translate('all intervals inputs are mandatory');
    				$error[] = 1;
    			}
    			
   
                if($previous_hour != -1 && strtotime($one_hour['time']) <= strtotime($previous_hour))
                {
					$this->error_message[$i]['ordererror'] = $Tr->translate('intervals must be consecutive');
					$error[] = 2;
                }
                
                $previous_hour = $one_hour['time'];
                
                $i++;
    		}
    	}
    
        if(empty($error)) {
            return true;
        }
    
        return false;
    
    }

    public function InsertData($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        if(!empty($post['interval']))
        {
            foreach($post['interval'] as $k=>$int_value){
            
                $post_time_interval = $int_value['time'].":00";
            
                if((isset($int_value['interval_id']) &&  !empty($int_value['interval_id']) && $int_value['interval_id'] != "0") && $int_value['custom'] == "0")
                {
                    $cust = Doctrine::getTable('PatientDrugPlanDosageIntervals')->find($int_value['interval_id']);
                    if($cust->time_interval != $post_time_interval )
                    {
                        
                        // get all patient dosages that have this time interval and update them with the new one
                        $loc = Doctrine_Query::create()
                        ->update("PatientDrugPlanDosage")
                        ->set('dosage_time_interval', '"'.$post_time_interval.'"')
                        ->set('change_date', '"'.date("Y-m-d H:i:s", time()).'"')
                        ->set('change_user', $userid)
                        ->where('dosage_time_interval= "'.$cust->time_interval.'" ' )
                        ->andWhere('ipid = "' .$post['ipid'] . '"' );
                        $loc->execute();

                         // update existing interval
                        $cust->time_interval = $post_time_interval;
                        $cust->save();
                    
                    }
                } 
                elseif($int_value['custom'] == "1")
                {
                    
                    $insert_pc = new PatientDrugPlanDosageIntervals();
                    $insert_pc->ipid = $post['ipid'];
                    $insert_pc->time_interval = $post_time_interval;
                    $insert_pc->save();

                    //IF ipid has the dosage set  - then insert in patient drugplans -a new line for each medication with the new time details
                   $existing_dosages = PatientDrugPlanDosage::get_patient_drugplan_dosage($post['ipid']); 
                   
                    if(!empty($existing_dosages)){
                        
                        foreach($existing_dosages as $drugplan_id => $values)
                        {
                            $times[$drugplan_id] = array_keys($existing_dosages[$drugplan_id]);
                            
                            if(!in_array($int_value['time'],$times[$drugplan_id])){
                                $insert_pc = new PatientDrugPlanDosage();
                                $insert_pc->ipid = $post['ipid'];
                                $insert_pc->drugplan_id = $drugplan_id;
                                $insert_pc->dosage = "";
                                $insert_pc->dosage_time_interval = $post_time_interval;
                                $insert_pc->save();
                            }
                            
                            $drugplans[] = $drugplan_id;
                            foreach($values as $time => $dos){
                                $drugplans_dosages[$drugplan_id][] = $dos;
                            }
                        }
                    }                    
                }
            }
            
            // DELETE INTERVALS
            
            if(!empty($post['deleted_intervals_ids']) && $post['deleted_intervals_ids'] !="0")
            {
                $deleted_ids = explode(",",$post['deleted_intervals_ids']);

                foreach($deleted_ids as $did)
                {
                    if($did != "0")
                    {
                        // update existing interval - set is delete
                        $cust = Doctrine::getTable('PatientDrugPlanDosageIntervals')->find($did);
                        if($cust){
                            
                           // get all patient dosages that have this time interval and mark them as deleted
                            $loc = Doctrine_Query::create()
                            ->update("PatientDrugPlanDosage")
                            ->set('isdelete', '1')
                            ->set('change_date', '"'.date("Y-m-d H:i:s, time()").'"')
                            ->set('change_user', $userid)
                            ->where('dosage_time_interval= "'.$cust->time_interval.'" ' )
                            ->andWhere('ipid = "' .$post['ipid'] . '"' );
                            $loc->execute();

                            // set as delete
                            $cust->isdelete = "1";
                            $cust->save();
                        }
                    }
                }
            }
        }
    }

    public function insert_data($post = array())
    {
    	
    	if (empty($post['interval']) || ! is_array($post['interval'])) {
    	    return; //for readability
    	}
    	
    	
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $meds = array();
        $deleted_ids = array();
        
        if ( ! empty($post['interval']))
        {
            // get all patient drugs 
            $m_medication = new PatientDrugPlan();
            $medicarr = $m_medication->getPatientAllDrugs($post['ipid'], true);
            
            foreach($medicarr as  $k=>$med_deta)
            {
                $meds['all'][] = $med_deta['id'];
                
                if($med_deta['isbedarfs'] == "1")
                {
                    $meds['isbedarfs'][] = $med_deta['id'];
                }
                elseif($med_deta['iscrisis'] == "1")
                {
                	$meds['iscrisis'][] = $med_deta['id'];
                }
                elseif($med_deta['isivmed'] == "1")
                {
                    $meds['isivmed'][] = $med_deta['id'];
                }
                elseif($med_deta['treatment_care'] == "1")
                {
                    $meds['treatment_care'][] = $med_deta['id'];
                }
                elseif($med_deta['isnutrition'] == "1")
                {
                    $meds['isnutrition'][] = $med_deta['id'];
                }
                elseif($med_deta['isintubated'] == "1")
                {
                    $meds['isintubated'][] = $med_deta['id'];
                }
                elseif($med_deta['isschmerzpumpe'] == "1")
                {
                    $meds['isschmerzpumpe'][] = $med_deta['id'];
                }
                else
                {
                    $meds['actual'][] = $med_deta['id'];
                }
            }


            
            // DELETED INTERVALS
            //TODO @cla : del must be first,,, or else you cannot delete and add same hour
            if ( ! empty($post['deleted_intervals_ids'])) {
                 
                foreach ($post['deleted_intervals_ids'] as $mtype=>$m_delids) {
            
                    if (empty($m_delids)) {
                        continue;
                    }
                    
                    
                    $now_posted_intervals = array_column($post['interval'][$mtype], 'time');
                    array_walk($now_posted_intervals, function(&$value, $key) { $value .= ':00'; } );
                    
                    $deleted_ids[$mtype] = explode(",", $m_delids);
                    $deleted_ids[$mtype] = array_filter($deleted_ids[$mtype], 'is_numeric');
            
                    foreach($deleted_ids[$mtype] as $did) {
                         
                        if ( ! empty($did)) {
                            // update existing interval - set is delete
                            //this table has no Softdelete listener.
                            $cust = Doctrine::getTable('PatientDrugPlanDosageIntervals')->findOneByIdAndIpidAndIsdelete($did, $post['ipid'] , 0);
                            if($cust){
                            	
                            	// set interval as delete
                            	$cust->isdelete = 1;
                            	$cust->save();
                            	
                            	if (in_array($cust->time_interval, $now_posted_intervals)) {
                            		//this hour:min was deleted, but also added again
                            		continue; //for readability
                            		
                            	} elseif ( ! empty($meds[$mtype])) {
                                	
                                	// get all patient dosages that have this time interval and mark them as deleted
                                	
                                    $loc = Doctrine_Query::create()
                                    ->update("PatientDrugPlanDosage")
                                    ->set('isdelete', 1)
                                    ->set('change_date', 'NOW()')
                                    ->set('change_user', $userid)
                                    ->where('ipid = ? ', $post['ipid'] )
                                    ->andWhereIn('drugplan_id', $meds[$mtype] )
                                    ->andwhere('dosage_time_interval= ?', $cust->time_interval )
                                    ->execute()
                                    ;
                                }
            
                                
                            }
                        }
                    }
                }
            }
            
            //IF ipid has the dosage set  - then insert in patient drugplans -a new line for each medication with the new time details
            $existing_dosages_master = PatientDrugPlanDosage::get_patient_drugplan_dosage($post['ipid']);
            
            
            
            foreach ($post['interval'] as $medication_type=>$mvalues)
            {
                
                if ( ! empty($existing_dosages_master)) {
                    foreach ($existing_dosages_master as $drgid => $time) {
                        if (in_array($drgid,$meds[$medication_type])) {
                            $existing_dosages[$medication_type][$drgid] = $time;
                        }
                    }
                }
                
                foreach ($mvalues as $k=>$int_value)
                {
                    $post_time_interval = $int_value['time'].":00";
                
                    if (isset($int_value['interval_id']) 
                    	&& ! empty($int_value['interval_id']) 
                    	&& $int_value['custom'] == "0")
                    {
                        
                        //this table has no Softdelete listener.
                        $cust = Doctrine::getTable('PatientDrugPlanDosageIntervals')->findOneByIdAndIpidAndIsdelete($int_value['interval_id'], $post['ipid'], 0);
                        
                        if ($cust && $cust->time_interval != $post_time_interval )
                        {
                            // get all patient dosages that have this time interval and update them with the new one
                            if ( ! empty($meds[$medication_type])) {
                                $loc = Doctrine_Query::create()
                                ->update("PatientDrugPlanDosage")
                                ->set('dosage_time_interval', '?', $post_time_interval)
                                ->set('change_date', 'NOW()')
                                ->set('change_user', $userid)
                                ->where('ipid = ?', $post['ipid'] )
                                ->andWhereIn('drugplan_id', $meds[$medication_type] )
                                ->andWhere('dosage_time_interval= ?', $cust->time_interval)
                                ->execute()
                                ;
                            }
    
                             // update existing interval
                            $cust->time_interval = $post_time_interval;
                            $cust->save();
                        
                        }
                    } 
                    elseif ($int_value['custom'] == "1")
                    {
                    	
                    	$cust = Doctrine::getTable('PatientDrugPlanDosageIntervals')->findOneByIpidAndIsdeleteAndMedicationTypeAndTimeInterval($post['ipid'], 0, $medication_type, $post_time_interval);
                    	if ($cust) {
                    		continue;
                    		//do not allow a new insert of a value that allready exists
                    	} 
                    	
                    	//else...
                        $insert_pc = new PatientDrugPlanDosageIntervals();
                        $insert_pc->ipid = $post['ipid'];
                        $insert_pc->medication_type = $medication_type;
                        $insert_pc->time_interval = $post_time_interval;
                        $insert_pc->save();
    
                        
      
                        if ( ! empty($existing_dosages[$medication_type])) {
                            
                        	//dd($existing_dosages[$medication_type], $int_value);
                        	
                            foreach($existing_dosages[$medication_type] as $drugplan_id => $values)
                            {
                                $times[$medication_type][$drugplan_id] = array_keys($existing_dosages[$medication_type][$drugplan_id]);
                                
                                if ( ! in_array($int_value['time'], $times[$medication_type][$drugplan_id])) {
                                    $insert_pc = new PatientDrugPlanDosage();
                                    $insert_pc->ipid = $post['ipid'];
                                    $insert_pc->drugplan_id = $drugplan_id;
                                    $insert_pc->dosage = "";
                                    $insert_pc->dosage_time_interval = $post_time_interval;
                                    $insert_pc->save();
                                }
                                
                                $drugplans[] = $drugplan_id; //what 4 you need this ?
                                
                                foreach ($values as $time => $dos) {
                                    $drugplans_dosages[$drugplan_id][] = $dos; //what 4 you need this ?
                                }
                            }
                        }                    
                    }
                
                }
            }

            
        }
    }
}
?>
