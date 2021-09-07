<?php
require_once ("Pms/Form.php");

/**
 * @update Jan 25, 2018: @author claudiu, checked/modified for ISPC-2071
 * vital_signs = Vitalwerte = FormBlockVitalSigns
 *
 * changed: bypass Trigger() on PC
 * fixing: inserts PC every time you post values
 * Changes added by Carmen for ISPC-2470 
 */
class Application_Form_FormBlockVitalSigns extends Pms_Form
{

    public function Update_old_values($ipid = '', $contact_form_id = 0)
    {
        if (! empty($contact_form_id)) {
            $Q = Doctrine_Query::create()->update('FormBlockVitalSigns')
                ->set('isdelete', '1')
                ->where("contact_form_id = ?", $contact_form_id)
                ->andWhere('ipid = ?', $ipid);
            $result = $Q->execute();
            
            return true;
        } else {
            return false;
        }
    }

    
    
    
    public function InsertData($post = array(), $allowed_blocks = array())
    {
        $userid =  $this->logininfo->userid;
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
        

        $vitals_fields =  array(
//             'source', // this is with cf or icon
//             'signs_date', // this is a date
            //other fields are decimal(10,2)
            'blood_pressure_a',
            'blood_pressure_b',
            'puls',
            'respiratory_frequency',
            'temperature',
            'temperature_dd',
            'oxygen_saturation',
            'blood_sugar',
            'weight',
            'height',
            'head_circumference',
            'waist_circumference', 
        );
        
        for($vtrow = 0; $vtrow < $post['vitalsign_colmax']; $vtrow++)
        {
	        $post['signs_date'][$vtrow] = date('Y-m-d H:i:s', strtotime($post['signs_date'][$vtrow] . ' ' . $post['signs_date_h'][$vtrow] . ':' . $post['signs_date_m'][$vtrow] . ':00')); // $post['signs_date'];
        }
        $post['source'] = 'cf';
        
        if ( ! empty($post['old_contact_form_id'])) {
        
            $change_date = $post['contact_form_change_date'];
        
            $vitals = new FormBlockVitalSigns();
            $old_data = $vitals->getPatientFormBlockVitalSigns($post['ipid'], $post['old_contact_form_id'], true);
            
            if ( ! in_array('vital_signs', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                //ISPC-2470 Carmen 28.10.2019
            	if ( ! empty($old_data))
            	{
	                if(count($old_data) == '1') //old entries
	                { 
		                    foreach ($vitals_fields as $field) {
		                        $post[$field] = $old_data[0][$field];
		                    }
		                    $post['signs_date'] =  $old_data[0]['signs_date'];
		                    $post['source']     =  $old_data[0]['source'];		                
	                }
	                else
	                {
	                	foreach($old_data as $kold_row=>$old_data_row)
	                	{
	                		foreach ($vitals_fields as $field) {
	                			$post[$field][$kold_row] = $old_data_row[$field];
	                		}
	                		$post['signs_date'][$kold_row] =  $old_data_row['signs_date'];
	                		$post['source'][$kold_row]     =  $old_data_row['source'];
	                	}
	                }
            	}
            }
            else {
                //we have permissions and cf is being edited
                //write changes in PatientCourse is something was changed
            	//ISPC-2470 Carmen 28.10.2019
            	if ( ! empty($old_data)) {
	            	if(count($old_data) == '1') //old entries
	            	{                
	        
	                    $we_have_data[0] =  false;
	                    
	                    foreach ($vitals_fields as $field) {
	                        if ( Pms_CommonData::str2num($post[$field]) != $old_data[0][$field]) {
	                            //something changed in the values
	                            $save_2_PC[0] = true;
	                            break;
	                        }
	                        
	                        if ( ! empty($post[$field])) { // if all inputs = 0 then we have no data
	                           $we_have_data[0] = true;
	                        }
	                        
	                    }
	                    //check if signs_date was changed
	                    if ( ! $save_2_PC[0] 
	                        && $we_have_data[0]
	                        && $post['signs_date'] != $old_data[0]['signs_date']) 
	                    {
	                        // ust the date was changed
	                        $save_2_PC[0] = true;
	                    }
	                }
		            else 
		            {
		            	foreach($old_data as $kold_row => $old_data_row)
		            	{
		            		$we_have_data[$kold_row] =  false;
		            		 
		            		foreach ($vitals_fields as $field) {
		            			if ( Pms_CommonData::str2num($post[$field][$kold_row]) != $old_data_row[$field]) {
		            				//something changed in the values
		            				$save_2_PC[$kold_row] = true;
		            				break;
		            			}
		            			 
		            			if ( ! empty($post[$field][$kold_row])) { // if all inputs = 0 then we have no data
		            				$we_have_data[$kold_row] = true;
		            			}
		            			 
		            		}
		            		//check if signs_date was changed
		            		if ( ! $save_2_PC[$kold_row]
		            				&& $we_have_data[$kold_row]
		            				&& $post['signs_date'][$kold_row] != $old_data_row['signs_date'])
		            		{
		            			// ust the date was changed
		            			$save_2_PC[$kold_row] = true;
		            		}
		            	}
		            }
	            }
	            else {
	            	//nothing was edited last time, or this block was added after the form was created
	            	for($newrow = 0; $newrow < $post['vitalsign_colmax']; $newrow++)
	            	{
		            	$save_2_PC[$newrow] = true;
		            	$change_date[$newrow] = '';
	            	}
	            	 
	            }
            }
        }
        	else {
            //new cf, save
            //this is the first time you save this block for this patient
        	for($newrow = 0; $newrow < $post['vitalsign_colmax']; $newrow++)
            {
	           	$save_2_PC[$newrow] = true;
	           	$change_date[$newrow] = '';
            }
        }

           
        
        //set the old block values as isdelete
        $clear_form_entryes = $this->Update_old_values($post['ipid'], $post['old_contact_form_id']);
        $course_str = array();
        //ISPC-2470 Carmen 28.10.2019
        for($verow = 0; $verow < 4; $verow++)
        {
	        $we_have_data =  false;
	        
	        foreach ($vitals_fields as $field) {
	            if ( $field != 'temperature_dd'
	                && ( ! empty($post[$field][$verow]) && Pms_CommonData::str2num($post[$field][$verow]) !== 0)) 
	            {
	                // we have values to save
	                $we_have_data = true;
	            }
	        }

	        
	        //create the patient_course_title
	        if ($we_have_data) {
	
	            $tocourse = array();
	            
	            if (strlen($post['blood_pressure_a'][$verow]) > 0 || strlen($post['blood_pressure_b'][$verow]) > 0) {
	                
	                $tocourse_blood = array();
	                $tocourse_blood['pre_blood_pressure_a'] = "RR: ";
	                
	                if (strlen($post['blood_pressure_a'][$verow]) > 0) {
	                    $tocourse_blood['blood_pressure_a'] = Pms_CommonData::str2num($post['blood_pressure_a'][$verow]);
	                }
	                
	                // between blood presure values
	                if (strlen($post['blood_pressure_a'][$verow]) > 0 && strlen($post['blood_pressure_b'][$verow]) > 0) {
	                    $tocourse_blood['blood_pressure_val_separator'] = " / ";
	                }
	                
	                if (strlen($post['blood_pressure_b'][$verow]) > 0) {
	                    $tocourse_blood['blood_pressure_b'] = Pms_CommonData::str2num($post['blood_pressure_b'][$verow]);
	                }
	                
	                $tocourse_blood['post_blood_pressure_b'] = " mmHg";
	                
	                $tocourse['blood_pressure'] = implode('', $tocourse_blood);
	            }
	            
	            if (strlen($post['puls'][$verow]) > 0) {
	                $tocourse['puls'] = "Puls: " . Pms_CommonData::str2num($post['puls'][$verow]) . " /min.";
	            }
	            
	            if (strlen($post['respiratory_frequency'][$verow]) > 0) {
	                $tocourse['respiratory_frequency'] = "Atemfrequenz: " . Pms_CommonData::str2num($post['respiratory_frequency'][$verow]) . " /min";
	            }
	            
	            if ($post['temperature_dd'][$verow] == '1') {
	                $temperature_dd = "im Ohr";
	            } elseif ($post['temperature_dd'][$verow] == '2') {
	                $temperature_dd = "oral";
	            } elseif ($post['temperature_dd'][$verow] == '3') {
	                $temperature_dd = "rektal";
	            }
	            //TODO-3513 Lore 12.10.2020
	              elseif ($post['temperature_dd'][$verow] == '4') {
	                $temperature_dd = "in der Blase";
	            } elseif ($post['temperature_dd'][$verow] == '5') {
	                $temperature_dd = "axillar";
	            } elseif ($post['temperature_dd'][$verow] == '6') {
	                $temperature_dd = "an der Stirn";
	            }
	            //.
	            
	            if (strlen($post['temperature'][$verow]) > 0) {
	                $tocourse['temperature'] = "Temperatur: " . Pms_CommonData::str2num($post['temperature'][$verow]) . " °C " . $temperature_dd;
	            }
	        
	            if (strlen($post['oxygen_saturation'][$verow]) > 0) {
	                $tocourse['oxygen_saturation'] = "Sauerstoffsättigung: " . Pms_CommonData::str2num($post['oxygen_saturation'][$verow]) . " %";
	            }
	        
	            if (strlen($post['blood_sugar'][$verow]) > 0) {
	                $tocourse['blood_sugar'] = " BZ: " . Pms_CommonData::str2num($post['blood_sugar'][$verow]) . " mg/dl";
	            }
	        
	            if (strlen($post['weight'][$verow]) > 0) {
	                $tocourse['weight'] = "Gewicht: " . Pms_CommonData::str2num($post['weight'][$verow]) . " Kg";
	            }
	        
	            if (strlen($post['height'][$verow]) > 0) {
	                $tocourse['height'] = "Größe : " . Pms_CommonData::str2num($post['height'][$verow]) . " cm";
	            }
	        
	            if (strlen($post['head_circumference'][$verow]) > 0) {
	                $tocourse['head_circumference'] = "Kopfumfang: " . Pms_CommonData::str2num($post['head_circumference'][$verow]) . " cm";
	            }
	        
	            if (strlen($post['waist_circumference'][$verow]) > 0) {
	                $tocourse['waist_circumference'] = "Bauchumfang: " . Pms_CommonData::str2num($post['waist_circumference'][$verow]) . " cm";
	            }
	        
	            if (! empty($tocourse)) {
	                $course_str[$verow]  = " Vitalwerte: Datum: " . date("d.m.Y H:i",strtotime($post['signs_date'][$verow])) . " " . implode(', ', $tocourse);
	            }
	            
	        }
	        
	        
	        
	        if ($we_have_data) {
	            
	            $vitals = new FormBlockVitalSigns();
	            $vitals->ipid = $post['ipid'];
	            $vitals->contact_form_id = $post['contact_form_id'];
	            $vitals->signs_date = $post['signs_date'][$verow];
	          
	            foreach ($vitals_fields as $field) {
	                $vitals->$field = Pms_CommonData::str2num($post[$field][$verow]);
	            }
	            
	            $vitals->temperature_dd = $post['temperature_dd'][$verow];
	            
	            
	            if ($save_2_PC[$verow] && in_array('vital_signs', $allowed_blocks)) {
	                if ( ! empty($course_str[$verow])
	                    && $pc_listener = $vitals->getListener()->get('PostInsertWriteToPatientCourse'))
	                {          
	                    $modules = new Modules();
	                    $b_vitalsigns_module = $modules->checkModulePrivileges("117", $clientid);
	            
	                    // if client has module 117 the course_type is 'B' 
	                    $vitalsigns_shortcut =  ($b_vitalsigns_module) ? 'B' : FormBlockVitalSigns::PATIENT_COURSE_TYPE;
	                    $change_date = "";//removed from pc; ISPC-2071
	                    
	                    $pc_listener->setOption('disabled', false);
	                    $pc_listener->setOption('course_title', $course_str[$verow] . $change_date);
	                    $pc_listener->setOption('course_type', $vitalsigns_shortcut);
	                    
	                    $pc_listener->setOption('done_date', $done_date);
	                    $pc_listener->setOption('user_id', $userid);
	                
	                    
	                }
	        
	            }
	            
	            
	            $vitals->save();
	//             dd($vitals->toArray());
	            
	        } else {
	            //we have no data
	             if ($save_2_PC[$verow] && in_array('vital_signs', $allowed_blocks)) {
	                 //this means you erased all the values inputs from this block
	                 //you unchecked all the options
	                 //must remove from PC this option
	                 //manualy remove and set $save_2_PC false
	                 $save_2_PC[$verow] =  false;
	                 
	                 if ( ! empty($post['old_contact_form_id'])) {
	                     $pc_entity = new PatientCourse();
	                     $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockVitalSigns');
	                 }
	             }
	        }
        }
        
    }
    
    
    
    
    
    
    
    
    public function InsertData_OLD($post, $allowed_block)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $modules = new Modules();
        $b_vitalsigns_module = $modules->checkModulePrivileges("117", $clientid);
        
        if ($b_vitalsigns_module) {
            $vitalsigns_shortcut = "B";
        } else {
            $vitalsigns_shortcut = "K";
        }
        
        if ($modules->checkModulePrivileges("139", $clientid)) {
            $head_circumference = true;
        } else {
            $head_circumference = false;
        }
        
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
        
        $vitals = new FormBlockVitalSigns();
        $vitals->ipid = $ipid;
        $vitals->contact_form_id = $post['contact_form_id'];
        $vitals->signs_date = date('Y-m-d H:i:s', strtotime($post['signs_date'] . ' ' . $post['signs_date_h'] . ':' . $post['signs_date_m'] . ':00')); // $post['signs_date'];
        $vitals->blood_pressure_a = Pms_CommonData::str2num($post['blood_pressure_a']);
        $vitals->blood_pressure_b = Pms_CommonData::str2num($post['blood_pressure_b']);
        $vitals->puls = Pms_CommonData::str2num($post['puls']);
        $vitals->respiratory_frequency = Pms_CommonData::str2num($post['respiratory_frequency']);
        $vitals->temperature = Pms_CommonData::str2num($post['temperature']);
        $vitals->temperature_dd = $post['temperature_dd'];
        $vitals->oxygen_saturation = Pms_CommonData::str2num($post['oxygen_saturation']);
        $vitals->blood_sugar = Pms_CommonData::str2num($post['blood_sugar']);
        $vitals->weight = Pms_CommonData::str2num($post['weight']);
        $vitals->height = Pms_CommonData::str2num($post['height']);
        $vitals->head_circumference = Pms_CommonData::str2num($post['head_circumference']);
        $vitals->waist_circumference = Pms_CommonData::str2num($post['waist_circumference']);
        $vitals->save();
        
        
       
        $signs_date = date('d.m.Y H:i', strtotime($post['signs_date'] . ' ' . $post['signs_date_h'] . ':' . $post['signs_date_m'] . ':00'));
        
        $tocourse_blood['pre_blood_pressure_a'] = " RR: ";
        
        if ($post['blood_pressure_a'] > '0') {
            $tocourse_blood['blood_pressure_a'] = Pms_CommonData::str2num($post['blood_pressure_a']);
        }
        
        // between blood presure values
        if (strlen($post['blood_pressure_a']) > 0 && strlen($post['blood_pressure_b']) > '0') {
            $tocourse_blood['blood_pressure_val_separator'] = " / ";
        }
        
        if ($post['blood_pressure_b'] > '0') {
            $tocourse_blood['blood_pressure_b'] = Pms_CommonData::str2num($post['blood_pressure_b']);
        }
        
        $tocourse_blood['post_blood_pressure_b'] = " mmHg";
        
        // join values only if any value was posted
        if (strlen($post['blood_pressure_a']) > 0 || strlen($post['blood_pressure_b']) > '0') {
            $tocourse['blood_pressure'] = implode('', $tocourse_blood);
        }
        
        if ($post['puls'] > '0') {
            $tocourse['puls'] = " Puls: " . Pms_CommonData::str2num($post['puls']) . " /min.";
        }
        
        if ($post['respiratory_frequency'] > '0') {
            $tocourse['respiratory_frequency'] = " Atemfrequenz: " . Pms_CommonData::str2num($post['respiratory_frequency']) . " /min";
        }
        
/*         if ($post['temperature_dd'] == '1') {
            $temperature_dd = "im Ohr";
        } else 
            if ($post['temperature_dd'] == '2') {
                $temperature_dd = "oral";
            } else 
                if ($post['temperature_dd'] == '3') {
                    $temperature_dd = "rektal";
                } */
        //TODO-3513 Lore 12.10.2020
        if ($post['temperature_dd'] == '1') {
            $temperature_dd = "im Ohr";
        } elseif ($post['temperature_dd'] == '2') {
            $temperature_dd = "oral";
        } elseif ($post['temperature_dd'] == '3') {
            $temperature_dd = "rektal";
        } elseif ($post['temperature_dd'] == '4') {
            $temperature_dd = "in der Blase";
        } elseif ($post['temperature_dd'] == '5') {
            $temperature_dd = "axillar";
        } elseif ($post['temperature_dd'] == '6') {
            $temperature_dd = "an der Stirn";
        }
        //.
            
            
        if ($post['temperature'] > '0') {
            $tocourse['temperature'] = " Temperatur: " . Pms_CommonData::str2num($post['temperature']) . " °C " . $temperature_dd;
        }
        
        if ($post['oxygen_saturation'] > '0') {
            $tocourse['oxygen_saturation'] = " Sauerstoffsättigung: " . Pms_CommonData::str2num($post['oxygen_saturation']) . " %";
        }
        
        if ($post['blood_sugar'] > '0') {
            $tocourse['blood_sugar'] = " BZ: " . Pms_CommonData::str2num($post['blood_sugar']) . " mg/dl";
        }
        
        if ($post['weight'] > '0') {
            $tocourse['weight'] = " Gewicht: " . Pms_CommonData::str2num($post['weight']) . " Kg";
        }
        
        if ($post['height'] > '0') {
            $tocourse['height'] = " Größe : " . Pms_CommonData::str2num($post['height']) . " cm";
        }
        
        if ($post['head_circumference']) {
            if ($post['head_circumference'] > '0') {
                $tocourse['head_circumference'] = " Kopfumfang: " . Pms_CommonData::str2num($post['head_circumference']) . " cm";
            }
        }
        
        if ($post['waist_circumference']) {
            if ($post['waist_circumference'] > '0') {
                $tocourse['waist_circumference'] = " Bauchumfang: " . Pms_CommonData::str2num($post['waist_circumference']) . " cm";
            }
        }
        
        if (! empty($tocourse)) {
            $coursecomment = " Vitalwerte: Datum: " . $signs_date . " " . implode(',', $tocourse);
        }
        
        if (strlen($post['old_contact_form_id']) > 0 && $post['old_contact_form_id'] != "0") {
            $change_date = $post['contact_form_change_date'];
        }
        
        // print_r($coursecomment);exit;
        if (! empty($coursecomment) && strlen($_REQUEST['cid']) >= 0) {
            $cust = new PatientCourse();
            $cust->ipid = $ipid;
            $cust->course_date = date("Y-m-d H:i:s", time());
            $cust->course_type = Pms_CommonData::aesEncrypt($vitalsigns_shortcut);
            $cust->course_title = Pms_CommonData::aesEncrypt($coursecomment . $change_date);
            $cust->isserialized = 1;
            $cust->user_id = $userid;
            $cust->done_date = $done_date;
            $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
            $cust->done_id = $post['contact_form_id'];
            
            // ISPC-2071 - added tabname, this entry must be grouped/sorted
            $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockVitalSigns");
            
            $cust->save();
        }
    }

    public function UpdateData($post, $contact_form_id = 0)
    {
        
        return;
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $contact_form_id = $post['contact_form_id'];
        $old_contact_form_id = $post['old_contact_form_id'];
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
        
        $vitals = new FormBlockVitalSigns();
        $vital_signs = $vitals->getPatientFormBlockVitalSigns($contact_form_id, $ipid);
        
        if ($vital_signs) {
        	//ISPC-2470 Carmen 29.10.2019
        	foreach($vitalsign as $krowv => $vrowv)
        	{
	            $vitals->ipid = $ipid;
	            $vitals->contact_form_id = $contact_form_id;
	            $vitals->signs_date = date('Y-m-d H:i:s', strtotime($post['signs_date'][$krowv] . ' ' . $post['signs_date_h'][$krowv] . ':' . $post['signs_date_m'][$krowv] . ':00')); // $post['signs_date'];
	            $vitals->blood_pressure_a = Pms_CommonData::str2num($post['blood_pressure_a'][$krowv]);
	            $vitals->blood_pressure_b = Pms_CommonData::str2num($post['blood_pressure_b'][$krowv]);
	            $vitals->puls = Pms_CommonData::str2num($post['puls'][$krowv]);
	            $vitals->respiratory_frequency = Pms_CommonData::str2num($post['respiratory_frequency'][$krowv]);
	            $vitals->temperature = Pms_CommonData::str2num($post['temperature'][$krowv]);
	            $vitals->temperature_dd = Pms_CommonData::str2num($post['temperature_dd'][$krowv]);
	            $vitals->oxygen_saturation = Pms_CommonData::str2num($post['oxygen_saturation'][$krowv]);
	            $vitals->blood_sugar = Pms_CommonData::str2num($post['blood_sugar'][$krowv]);
	            $vitals->weight = Pms_CommonData::str2num($post['weight'][$krowv]);
	            $vitals->height = Pms_CommonData::str2num($post['height'][$krowv]);
	            $vitals->head_circumference = Pms_CommonData::str2num($post['head_circumference'][$krowv]);
	            $vitals->waist_circumference = Pms_CommonData::str2num($post['waist_circumference'][$krowv]);
	            $vitals->save();
        	}
        }
        $clear_form_entryes = $this->Update_old_values($ipid, $old_contact_form_id);
        
        $qa = Doctrine_Query::create()->update('PatientCourse')
            ->set('done_date', "'" . $done_date . "'")
            ->where('done_name = AES_ENCRYPT("contact_form", "' . Zend_Registry::get('salt') . '")')
            ->andWhere('done_id = "' . $contact_form_id . '"')
            ->andWhere('ipid LIKE "' . $ipid . '"');
        $qa->execute();
        
//         dd(func_get_args(), $vitals->toArray());
    }

    public function insert_from_icon($post)
    {
        // print_r($post); exit;
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $modules = new Modules();
        $b_vitalsigns_module = $modules->checkModulePrivileges("117", $clientid);
        
        if ($b_vitalsigns_module) {
            $vitalsigns_shortcut = "B";
        } else {
            $vitalsigns_shortcut = "K";
        }
        
        if ($modules->checkModulePrivileges("139", $clientid)) {
            $head_circumference = true;
        } else {
            $head_circumference = false;
        }
        
        $done_date = date('Y-m-d H:i:s', strtotime($post['signs_date'] . ' ' . $post['signs_date_h'] . ':' . $post['signs_date_m'] . ':00'));
        
        //ISPC-2515 Carmen 16.04.2020
        if($post['id'])
        {
        	$vitals = FormBlockVitalSignsTable::getInstance()->find($post['id'], Doctrine_Core::HYDRATE_RECORD);
        }
        else 
        {
        	$vitals = new FormBlockVitalSigns();
        
	        $vitals->ipid = $ipid;
	        $vitals->contact_form_id = "0";
        }
        //$vitals->source = "icon";
        $vitals->source = $post['vs_source'];
        //--
        $vitals->signs_date = date('Y-m-d H:i:s', strtotime($post['signs_date'] . ' ' . $post['signs_date_h'] . ':' . $post['signs_date_m'] . ':00')); // $post['signs_date'];
        $vitals->blood_pressure_a = Pms_CommonData::str2num($post['blood_pressure_a']);
        $vitals->blood_pressure_b = Pms_CommonData::str2num($post['blood_pressure_b']);
        $vitals->puls = Pms_CommonData::str2num($post['puls']);
        $vitals->respiratory_frequency = Pms_CommonData::str2num($post['respiratory_frequency']);
        $vitals->temperature = Pms_CommonData::str2num($post['temperature']);
        $vitals->temperature_dd = $post['temperature_dd'];
        $vitals->oxygen_saturation = Pms_CommonData::str2num($post['oxygen_saturation']);
        $vitals->blood_sugar = Pms_CommonData::str2num($post['blood_sugar']);
        $vitals->weight = Pms_CommonData::str2num($post['weight']);
        $vitals->height = Pms_CommonData::str2num($post['height']);
        $vitals->head_circumference = Pms_CommonData::str2num($post['head_circumference']);
        $vitals->waist_circumference = Pms_CommonData::str2num($post['waist_circumference']);
        $vitals->save();
        
        $vitals_last_id = $vitals->id;
        
        $signs_date = date('d.m.Y H:i', strtotime($post['signs_date'] . ' ' . $post['signs_date_h'] . ':' . $post['signs_date_m'] . ':00'));
        
        $tocourse_blood['pre_blood_pressure_a'] = " RR: ";
        
        if ($post['blood_pressure_a'] > '0') {
            $tocourse_blood['blood_pressure_a'] = Pms_CommonData::str2num($post['blood_pressure_a']);
        }
        
        // between blood presure values
        if (strlen($post['blood_pressure_a']) > 0 && strlen($post['blood_pressure_b']) > '0') {
            $tocourse_blood['blood_pressure_val_separator'] = " / ";
        }
        
        if ($post['blood_pressure_b'] > '0') {
            $tocourse_blood['blood_pressure_b'] = Pms_CommonData::str2num($post['blood_pressure_b']);
        }
        
        $tocourse_blood['post_blood_pressure_b'] = " mmHg";
        
        // join values only if any value was posted
        if (strlen($post['blood_pressure_a']) > 0 || strlen($post['blood_pressure_b']) > '0') {
            $tocourse['blood_pressure'] = implode('', $tocourse_blood);
        }
        
        if ($post['puls'] > '0') {
            $tocourse['puls'] = " Puls: " . Pms_CommonData::str2num($post['puls']) . " /min.";
        }
        
        if ($post['respiratory_frequency'] > '0') {
            $tocourse['respiratory_frequency'] = " Atemfrequenz: " . Pms_CommonData::str2num($post['respiratory_frequency']) . " /min";
        }
        
/*         if ($post['temperature_dd'] == '1') {
            $temperature_dd = "im Ohr";
        } else 
            if ($post['temperature_dd'] == '2') {
                $temperature_dd = "oral";
            } else 
                if ($post['temperature_dd'] == '3') {
                    $temperature_dd = "rektal";
                } */
        //TODO-3513 Lore 12.10.2020
        if ($post['temperature_dd'] == '1') {
            $temperature_dd = "im Ohr";
        } elseif ($post['temperature_dd'] == '2') {
            $temperature_dd = "oral";
        } elseif ($post['temperature_dd'] == '3') {
            $temperature_dd = "rektal";
        } elseif ($post['temperature_dd'] == '4') {
            $temperature_dd = "in der Blase";
        } elseif ($post['temperature_dd'] == '5') {
            $temperature_dd = "axillar";
        } elseif ($post['temperature_dd'] == '6') {
            $temperature_dd = "an der Stirn";
        }
        //.
            
        if ($post['temperature'] > '0') {
            $tocourse['temperature'] = " Temperatur: " . Pms_CommonData::str2num($post['temperature']) . " °C " . $temperature_dd;
        }
        
        if ($post['oxygen_saturation'] > '0') {
            $tocourse['oxygen_saturation'] = " Sauerstoffsättigung: " . Pms_CommonData::str2num($post['oxygen_saturation']) . " %";
        }
        
        if ($post['blood_sugar'] > '0') {
            $tocourse['blood_sugar'] = " BZ: " . Pms_CommonData::str2num($post['blood_sugar']) . " mg/dl";
        }
        
        if ($post['weight'] > '0') {
            $tocourse['weight'] = " Gewicht: " . Pms_CommonData::str2num($post['weight']) . " Kg";
        }
        
        if ($post['height'] > '0') {
            $tocourse['height'] = " Größe : " . Pms_CommonData::str2num($post['height']) . " cm";
        }
        if ($post['head_circumference']) {
            
            if ($post['head_circumference'] > '0') {
                $tocourse['head_circumference'] = " Kopfumfang: " . Pms_CommonData::str2num($post['head_circumference']) . " cm";
            }
        }
        if ($post['waist_circumference']) {
            
            if ($post['waist_circumference'] > '0') {
                $tocourse['waist_circumference'] = " Bauchumfang: " . Pms_CommonData::str2num($post['waist_circumference']) . " cm";
            }
        }
        
        if (! empty($tocourse)) {
            $coursecomment = " Vitalwerte: Datum: " . $signs_date . " " . implode(',', $tocourse);
        }
        // print_r($coursecomment);exit;
        if (! empty($coursecomment)) {
            $cust = new PatientCourse();
            $cust->ipid = $ipid;
            $cust->course_date = date("Y-m-d H:i:s", time());
            $cust->course_type = Pms_CommonData::aesEncrypt($vitalsigns_shortcut);
            $cust->course_title = Pms_CommonData::aesEncrypt($coursecomment);
            $cust->isserialized = 1;
            $cust->user_id = $userid;
            $cust->done_date = $done_date;
            $cust->done_name = Pms_CommonData::aesEncrypt("vital_signs_icons");
            $cust->done_id = $vitals_last_id;
            $cust->save();
        }
    }
}