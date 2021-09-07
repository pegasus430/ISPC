<?php

/*
 * @author al3x
 * // Maria:: Migration ISPC to CISPC 08.08.2020
 * class to integrate mePatient/proxypool and process delivered payloads
 * 
 * Ancuta 28.02.2020 - changed fn data_encrypt from private to public
 * 
 */
class Pms_mePatient
{

    protected $_mepatient_encrypt_key;

    public function __construct()
    {
        if (Zend_Registry::isRegistered('mepatient')) {
            $mepatient_cfg = Zend_Registry::get('mepatient');
            $this->_mepatient_encrypt_key = $mepatient_cfg['final']['encrypt_key'];
            $this->_mepatient_push_key = $mepatient_cfg['final']['push_key'];
        } else {
            echo 'No mepatient settings!';
            exit(); // mepatient not configured
        }
    }

    //encrypt data for mePateint, this is never used, adding here for debugging
    
    public function data_encrypt($data)
    {
        $ivlen = openssl_cipher_iv_length($cipher = "aes-256-ctr");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($data, $cipher, $this->_mepatient_encrypt_key, $options = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $this->_mepatient_encrypt_key, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);

        return $ciphertext;
    }
    
    //decrypting data from mePatient

    private function data_decrypt($data)
    {
        $c = base64_decode($data);
        $ivlen = openssl_cipher_iv_length($cipher = "aes-256-ctr");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $decrypted_data = openssl_decrypt($ciphertext_raw, $cipher, $this->_mepatient_encrypt_key, $options = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $this->_mepatient_encrypt_key, $as_binary = true);
        if (hash_equals($hmac, $calcmac)) {
            return $decrypted_data;
        }
    }
    
    
    //validate payload before processing
    private function validate_payload($payload) {
        $decrypted_payload = $this->data_decrypt($payload);
        
        //var_dump($decrypted_payload); exit;
        
        /*
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.str_replace(array("\r","\n", " "), array("","",""), $decrypted_payload); //add fake XML to aid the validation and processing
        */
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.$decrypted_payload; //add fake XML to aid the validation and processing
        
        
        if(Pms_CommonData::isValidXml($xml)) {
            try
            {
                $xml2array = new Pms_XML2Array ();
                $data = $xml2array->createArray ( $xml );
                
                $data = $data['root'];
                
                if(sizeof($data) == 0 || sizeof($data) > 2) { //payload data should have MAXIMUM 2 nodes
                    throw new Exception('validate_payload: payload error: '.$e->getMessage() . ', XML: ' . print_r($xml2, true) );
                    return null;
                } else {
                    if(empty($data['files']) && empty($data['results'])) { 
                        throw new Exception('validate_payload: payload error: '.$e->getMessage() . ', XML: ' . print_r($xml2, true) );
                        return null;
                    } else {
                        return $data;
                    }
                }
            }
            catch (Exception $e)
            {
                
                throw new Exception('validate_payload: parse xml error: '.$e->getMessage() . ', XML: ' . print_r($xml2, true) );
                return null;
            }
        } else {
            throw new Exception('validate_payload: no valid XML found within payload , payload: ' . print_r($xml2, true) );
            return null;
        }
    }
    
    public function process_payload($payload_id, $payload, $deviceid, $ipid) {
        
        $device_data = MePatientDevicesTable::find_patient_deviceByIpidAndId($ipid,$deviceid);

        if(empty($device_data)) {
            return null;
        }
        $data = $this->validate_payload($payload);

        $surveys2patient = array();
        if(!empty($device_data[$deviceid]['MePatientDevicesSurveys'])){
            foreach($device_data[$deviceid]['MePatientDevicesSurveys'] as $k=>$mds){
                $surveys2patient[$mds['ipid']][] = $mds['survey_id'];
            }
        }

        if(!empty($data['files'])) {
            //process images
            
            if(!empty($data['files']['file']['@attributes'])) {
                $real_data['files']['file'][] = $data['files']['file'];
            } else {
                $real_data = $data;
            }
            
            if(!empty($real_data['files']['file'])) {
                $i = 1;
                foreach($real_data['files']['file'] as $file) {
                    $file_binary = str_replace(array("\r","\n", " "), array("","",""), $file['@value']);
                    $file_type = $file['@attributes']['type'];
                    $file_name = $file['@attributes']['filename'];
                    $file_uuid = $file['@attributes']['uuid'];
                    
                    if(!empty($file_uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $file_uuid) != false) //add uuid unique check here!!!!
                    {
                        
                        // Ancuta 12.03.2020:: check if $file_uuid already exsts in DB
                        $uuid_exists = MePatientRequestsProcessedTable::uuid_exists($file_uuid);
                        
                        if(!empty($uuid_exists)) {
                            $files_errors[$file_uuid] = 'Uuid already exists.';
                        } else {
                                
                            if($file_type == 'image') {
                                $file_binary_type = strtolower(trim(substr($file_binary,0, strpos($file_binary, ';'))));
                              
                                if($file_binary_type == 'data:image/jpeg' || $file_binary_type == 'data:image/png' || true) {
                                    
                                    $uuid_path = MEPATIENT_TEMP_IMAGES.'/'.$payload_id;
                                    
                                    if(!is_dir($uuid_path)) {
                                        mkdir($uuid_path);
                                    }
                                    
                                    if(is_dir($uuid_path)) {
                                        try {
                                            
                                            //echo $file_binary;
                                            $test_file = $uuid_path . '/' . md5($file_uuid).'.jpg';
                                            if(stripos($file_binary, 'data:image') !== false) {
                                                $bas64_file = substr($file_binary, strpos($file_binary,',') + 1);
                                            } else {
                                                $bas64_file = $file_binary;
                                            }
                                            
                                            $img = imagecreatefromstring(base64_decode($bas64_file));
                                            imagejpeg($img, $test_file);
                                            $img_data = getimagesize($test_file);
                                            
                                            if($img_data) {
                                                $files_processed[$file_uuid] = $test_file;
                                            } else {
                                                $files_errors[$file_uuid] = 'Invalid image.';
                                            }
                                        } catch (Exception $e) {
    
                                            throw new Exception('validate_payload: cannot write file:' . print_r($uuid_path . '/' . md5($file_uuid), true) );
                                            return null;
                                        }
                                        
                                    } else {
                                        throw new Exception('validate_payload: cannot enter file processing folder: ' . print_r($uuid_path, true) );
                                    }
                                } else {
                                    $files_errors[$file_uuid] = 'Invalid binary file type.';
                                }
                            } else {
                                $files_errors[$file_uuid] = 'File type not supported.';
                            }
                        }
                        
                    } else {
                        throw new Exception('validate_payload: no uuid found/uuid not unique , payload: ' . print_r($file, true) );
                    }
                    $i++;
                }
                
                $return['files']['errors'] = $files_errors;
                $return['files']['paths'] = $files_processed;
             
                return $return;
            }
        
        } elseif(!empty($data['results'])) {
            
            if(!empty($data['results']['result']['@attributes'])) {
                $real_data['results']['result'][] = $data['results']['result'];
            } else {
                $real_data = $data;
            }
            
            // get patient details 
            $generate_result_pdf = 1;
            $patient_data= PatientMaster::get_patients_details_By_Ipids(array($ipid),true);
           
            
            if (! empty($real_data['results']['result'])) {

                $i = 1;
                foreach ($real_data['results']['result'] as $result) {
                    $result_start = $result['@attributes']['start'];
                    $result_end = $result['@attributes']['end'];
                    $result_chain = $result['@attributes']['chain'];
                    $result_uuid = $result['@attributes']['uuid'];
 
                    //get survey details 
                    $survey_data = MePatientSurveysTable::find_survey_Byid($result_chain);//CHANGE
 
                    // AICI se verifica  daca la surveyul trimis are pacientul access
//                     if( empty($surveys2patient[$ipid]) || ! in_array($result_chain,$surveys2patient[$ipid])){
                    
//                         $results_errors[$result_uuid] = 'Survey not allowd for this patient ';
//                     }
//                     else
//                     {
                    
                        if (! empty($result_uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $result_uuid) != false) // add uuid unique check here!!!!
                        {
    
                            $uuid_exists = MePatientRequestsProcessedTable::uuid_exists($result_uuid);
    
                            if (!empty($uuid_exists)) {
                                $results_errors[$result_uuid] = 'Uuid already exists.';
                            } else {
                                
                                
                                // check if patient has access to survey / get chain details
                                //query mepatientrequestsprocessed for ipid and client
                                
                                
                                if (! empty($result['answers']['answer']['@attributes'])) {
                                    $real_result['answers']['answer'][] = $result['answers']['answer'];
                                } else {
                                    $real_result = $result;
                                }
    
                                
                                
                                if(!empty($real_result['answers']['answer'])) {
                                    // AICI scrii in survey_patient2chain  - get ID
                                    // - survey_id  = $result_chain
                                    
                                    // hack for dates 
                                    $start_date_Str = substr($result_start,0,10);
                                    $start_time_Str = substr($result_start,-8);
                                    $start_time_Str = str_replace('-', ':', $start_time_Str);
                                    $start_Date_time = $start_date_Str.' '.$start_time_Str;
                                    
                                    $end_date_Str = substr($result_end,0,10);
                                    $end_time_Str = substr($result_end,-8);
                                    $end_time_Str = str_replace('-', ':', $end_time_Str);
                                    $end_Date_time = $end_date_Str.' '.$end_time_Str;
                                    //--
                                    
                                    
                                    //delete if exists?
                                    
                                    $pateint2chain_rentry = new SurveyPatient2chain();
                                    $pateint2chain_rentry->patient = null;
                                    $pateint2chain_rentry->ipid = $ipid;
                                    $pateint2chain_rentry->survey_id = $result_chain;
                                    $pateint2chain_rentry->start = date('Y-m-d H:i:s',strtotime($start_Date_time ));
                                    $pateint2chain_rentry->end = date('Y-m-d H:i:s',strtotime($end_Date_time));
                                    $pateint2chain_rentry->save();
                                    $patient_chain_id = $pateint2chain_rentry->id;
          
                                   
                               
                                    
                                    
                                    //Save to survey results 
                                    $survey_result = array();
                                    foreach ($real_result['answers']['answer'] as $answer) {
                                        // get survey and question details
                                        $survey_result[] = array(
                                            'survey_took' => $patient_chain_id,
                                            'survey' => $answer['@attributes']['survey'],
                                            'question' => $answer['@attributes']['question'],
                                            'answered' => $answer['answered'],
                                            'answer' => $answer['value']['@cdata'], //???????? < Not sure
                                            'freetext' => $answer['freetext'],
                                            'row' => $answer['@attributes']['row'],
                                            'column' => $answer['@attributes']['column']
                                        );
                                    }
                                    
                                    $conn = Doctrine_Manager::getInstance()->getConnection('IDAT');
                                    foreach ($survey_result as $survey_row){
                                        $values_don = "";
                                        foreach($survey_row as $field => $value){
                                            $values_don .= '"' . $value . '",';
                                        }
                                        $sqlInsert  = "INSERT INTO `survey_results` ( `survey_took`, `survey`, `question`, `answered`, `answer`, `freetext`, `row`, `column`) VALUES (".substr($values_don, 0, -1).")";
                                        $queryInsert = $conn->prepare($sqlInsert);
                                        $queryInsert = $conn->execute($sqlInsert);
                                        $queryInsert->closeCursor();
                                    }
                                    
                                    
                                    //save results scores
                                    
                                    //return file
                                    
                                    if($generate_result_pdf ==1 ){
                                        
                                        define (ABSPATH, APPLICATION_PATH.'/../library/Survey/chain2pdf');
                                        
                                        $patient_details = array(
                                            'first_name' => $patient_data[$ipid]['first_name'],
                                            'last_name' => $patient_data[$ipid]['last_name'],
                                            'survey_name' => $survey_data[$result_chain]['survey_name'],
                                            'dob' =>   $patient_data[$ipid]['birthd'] != "0000-00-00" ? date("d.m.Y",strtotime($patient_data[$ipid]['birthd'])) : "-" 
                                        );
                                        
                                        $chain_id = $patient_chain_id;
                                        
                                        //if outputfolder is set PDF is written there, otherwise it's offered to download
                                        
                                        $path = PDF_PATH;
                                        $dir = Pms_CommonData::uniqfolder(PDF_PATH);
                                        
                                        
                                        //$outputfolder = null;
                                        //$outputfolder = ABSPATH.'/_tmp';
                                        $outputfolder = $path . '/' . $dir;
                                  
                                        
                                        require_once 'Survey/chain2pdf/chain2html.php';
                                        
                                        $results_processed[$result_uuid] = $pdf_disk_path;
                                        
                                    }
                                    
                                    
                                } else {
                                    $results_errors[$result_uuid] = 'Answer node is empty';
                                }
                                
                                
                            }
                        } else {
                            throw new Exception('validate_payload: no uuid found/uuid not unique , payload: ' . print_r($result, true));
                        }
                        $i ++;
//                     }
                }
                
                $return['results']['processed'] = $results_processed;
                $return['results']['errors'] = $results_errors;

                return $return;
            }
        } else {
            return null;
        }
    }
    
    
    public function push_notification($regid, $message = null, $title = null, $notification_id = null) {
        //MEP-151 Ancuta 13.07.2020 :: added additional param $notification_id
        $json_data = array(
            
                        'to' => $regid,
                        'notification' => array(
                            'body' => $message,
                            'click_action' => "FCM_PLUGIN_ACTIVITY" // MEP-151 Ancuta 13.07.2020 (Luc: Necessary for Android )
                        ),
                        //MEP-151 Ancuta 13.07.2020
                        'data' => array(
                            "notificationId" => $notification_id
                        )
                        //--
                        );
        
        $data = json_encode($json_data);
       
        $url = 'https://fcm.googleapis.com/fcm/send';
       
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='. $this->_mepatient_push_key
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        $verbose = curl_getinfo($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
            throw new Exception('FCM Send Error: ' . curl_error($ch). ' | ' . print_r($verbose, true) );
        }
        curl_close($ch);
        
        return $result;
    }
    
    
}

