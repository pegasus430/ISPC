<?php
/**
 * !! IMPORTANT !!
 * 'B_Dat_ID' format =  "{$export_data_this_form['B_Pat_ID']}_{$v_pkvno['id']}_{$v_pkvno['TwinDgpKern']['id']}" ;
 */

/*
 *
 Problem with the fields marked with a red asterisk (ECOG: *, Beschreibung der aktuellen bzw. unmittelbar geplanten Versorgung: * , etc etc) from here patientnew/hospizregisterv3

 I have inconsistency at logic level between ISPC register and the actual statistic from hospiz-palliativ-register.de (the .xsd logic and the graphs calculus for 0 + NULL)

 In ISPC, if one of this fields is NOT checked (is 0 or empty), the dgp_kern is NOT submited to the register(form is considered not filled )

 BUT, the .xsd allows them to be empty(nul) or 0,
 and some statistics from hospiz-palliativ-register.de is calculated even for some zeros

 there are datas in register with this as 0, but are from 2017-02 ... i suspect before the current version...


 BL_Ecog
 B_bewertung
 B_thera_ende

 at least one of this fields MUST be checked, or else, again, the form is not submited from ispc to hospiz-palliativ-register.de

 BA_Hausarzt
 BA_Ambulante_Pflege
 BA_Palliativarzt
 BA_Palliativpflege
 BA_Palliativberatung
 BA_Ehrenamtlicher_Dienst
 BA_KH_Palliativstation
 BA_Hospiz_stationaer
 BA_Krankenhaus_Andere_Station
 BA_Heim
 BA_Palliative_Care_Team
 BA_sonstige_behandlung
 BA_Ambulante_Pflege

 in ispc this is considered a not completed form,

 for hospiz-palliativ-register.de none of this restrictions apply



 this is an unrelated @cla TODO / Reminder:
 BL_Ecog => 2 graphs are flawed on register ???... in they are missing a condition = 0 ??
 fixed on my pc.. must check, explain and commit on dev when i will work on register
 
 
ISPC-2496 Ancuta + TODO-1943 ALex

03.12.2019 
Removed /Commented:
    B_geb_datum
    BA_datum
    BL_datum
       
    BL_Analgetika
    BL_Co_Analgetika
    BL_Anxiolytika
    BL_Sedativa
    BL_Neuroleptika
    BL_Anti_Biotika

    BL_Aufwand
    BL_Kortikosteroide
    
    BL_problem_1 
    B_datum_ende 


Added new fields

    B_Versorgungstage// length in days for the fall which is now transferred; Mandatory; Between 1 - 1000
    B_Jahrderversorgung// year-from  the ADMISSION YEAR  of the export period; Mandatory;  Between 2015 - 2050;
   
    BL_Sekretionshemmend
    BL_Benzodiazepine
    BL_Antidepressiva
    BL_Antipsychotika
    BL_Antiinfektiva
    BL_Antikoagulantien
    BL_Sonstige
 */
Doctrine_Manager::getInstance()->bindComponent('DgpPatientsHistory', 'SYSDAT');

class DgpPatientsHistory extends BaseDgpPatientsHistory 
{
    
    /**
     * @var Zend_Controller_Action_Helper_Abstract
     */
    private $_httpService = null;
    
    /**
     * used for logging
     * @var string
     */
    private $_processingClientid = null;
    
    /**
     * this is a mapping with the codes that could be returned by nat-hospiz
     * please translate the messages
     */
    public $NatHospiz_response_text = array(
        "1000" => "All OK data received and saved.",
        "2000" => "Error during login (access data wrong):",
        "2100" => "Error while logging in (functional area not set):",
        "2200" => "Login failed (specified version not allowed):",
        "2300" => "No SSL encryption (no https connection):",
        "2900" => "Error while logging in (account locked):",
        "3000" => "Error of delivered data (no data received):",
        "3100" => "Error of received data (data does not correspond to the XSD file - no valid XML format):",
        "9999" => "9999",
        "4000" => "Error of received data (mandatory fields, insufficient amount of data):",
        "4100" => "Errors of the received data (Pat_ID and Dat_ID do not match):",
        "4200" => "Errors of the received data (Dat_ID submitted multiple times):",
        "9100" => "Reaching the maximum script runtime:",
        "9200" => "No lock on the center"
    );
    
    


    /**
     * @cla on 10.06.2018 : do not use
     * @deprecated
     */
	    public function patients_submited_status($ipids,$only_not_submited = false,$client_ids = false){
	        /* ISPC-1775,ISPC-1678 */
	        foreach ($ipids as $ipid)
	        {
	            $ipid_str .= '"' . $ipid. '",';
	        }
	        $ipid_str = substr($ipid_str, 0, -1);
	    
	        
	        if($client_ids && ! empty($client_ids)){
	        	
		        foreach ($client_ids as $client)
		        {
		            $client_str .= '"' . $client. '",';
		        }
		        $client_str = substr($client_str, 0, -1);
		        
				$client_q = ' and  client IN ('.$client_str.') ';
	        }
		   else
		   {
				$client_q = '';
		   }
	    
	        // get last_update from patient master
	        $q_pm = Doctrine_Query::create()
	        ->select("ipid,last_update,last_update_user")
	        ->from('PatientMaster')
	        ->whereIn('ipid', $ipids);
	        $patients_last_update= $q_pm->fetchArray();
	    
	        // get last submited status of patients
			/* 
	        $patient_submit_data= Doctrine_Query::create()
	        ->select('*')
	        ->from('DgpPatientsHistory ka')
	        ->where('ka.id =(SELECT kb.id  FROM DgpPatientsHistory kb WHERE ka.ipid = kb.ipid  and  kb.ipid in (' . $ipid_str . ') ORDER BY `upload_date` ASC	LIMIT 1 )')
	        ->groupBy('ipid')
	        ->orderby('id asc');
	        $exported_patiens_array = $patient_submit_data->fetchArray();
	     */
	        
	        $exported_patiens_array = array();
	        $querystr = 'SELECT *
		        FROM dgp_patients_history p
		        INNER JOIN (SELECT id  FROM `dgp_patients_history` WHERE   ipid IN ('.$ipid_str.')  '.$client_q.'  ORDER BY `upload_date` DESC ) AS p2
		        ON p.id = p2.id
		        GROUP BY p.ipid
		        ORDER BY p.id asc';
	        $manager = Doctrine_Manager::getInstance();
	        $manager->setCurrentConnection('SYSDAT');
	        $conn = $manager->getCurrentConnection();
	        $query = $conn->prepare($querystr);
	        $dropexec = $query->execute();
	        $exported_patiens_array = $query->fetchAll();
	        
	        foreach($exported_patiens_array as $k=>$expo_data){
	            $export_details[$expo_data['ipid']] = $expo_data;
	        }
	    
	        foreach($patients_last_update as $k => $pat)
	        {
	            if(!empty($export_details[$pat['ipid']])){
	    
	                if(strtotime($export_details[$pat['ipid']]['upload_date']) >= strtotime($pat['last_update'])){
	                    $status[$pat['ipid']] = "submited";
	                } else{
	                    $status[$pat['ipid']] = "not_submited";
	                    $not_submited[] = $pat['ipid'];
	                }
	            }
	            else
	            {
	                $status[$pat['ipid']] = "not_submited";
	                $not_submited[] = $pat['ipid'];
	            }
	        }

	        if($only_not_submited)
	        {
	            return $not_submited;
	        } else {
	    
	            return $status;
	        }
	    }


	    /**
	     * @cla on 10.06.2018 : do not use
	     * @deprecated
	     */
	    public function submited_patients($patients,$only_not_submited = false){
	    
	        foreach ($patients as $k=>$pat)
	        {
	            $ipid_str .= '"' . $pat['ipid']. '",';
	            $ipid_arr[] = $pat['ipid'];
	            $patients_details[$pat['ipid']] = $pat;
	            $patients_details[$pat['ipid']]['last_update'] = $pat['PatientMaster']['last_update'];
	        }
	        $ipid_str = substr($ipid_str, 0, -1);
	    
	        if(empty($ipid_arr)){
	            $ipid_arr[] = "999999999";
	        }
	    
	    
	        // get last submited status of patients
	        $patient_submit_data= Doctrine_Query::create()
	        ->select('*')
	        ->from('DgpPatientsHistory ka')
	        ->whereIn('ka.ipid',$ipid_arr)
	        //->groupBy('ipid')
	        ->orderby('upload_date asc');
	        $exported_patiens_array = $patient_submit_data->fetchArray();
	    
	        
	        foreach($exported_patiens_array as $k=>$expo_data){
	            $full_export_details[$expo_data['ipid']][strtotime($expo_data['upload_date'])] = $expo_data;
	        } 

	        foreach($full_export_details as $ipid=>$uploads_data){
	            ksort($uploads_data);
	            $new_full_export_details[$ipid] = $uploads_data;
	        }
	        
	        
	        foreach($new_full_export_details as $kipid=>$expo_data){
	            $export_details[$kipid] = end(array_values($expo_data));
	        }
	         
// 	        foreach($exported_patiens_array as $k=>$expo_data){
// 	            $export_details[$expo_data['ipid']] = $expo_data;
// 	        } 
// 	        $status['submited'][] = "99999999"; 
// 	        $status['not_submited'][] = "99999999"; 
	        $status = array();
	        foreach($patients_details as $k => $pat)
	        {
	            if(!empty($export_details[$pat['ipid']])){
	    
	                if(strtotime($export_details[$pat['ipid']]['upload_date']) >= strtotime($pat['last_update'])){
	                    $status['submited'][] = $pat['ipid'];
	                    
	                } else{
	                    $status['not_submited'][] = $pat['ipid'];
	                }
	            }
	            else
	            {
	                $status['not_submited'][] = $pat['ipid'];
	            }
	        }
	        if (empty($status['submited'])){
	        	$status['submited'][] = "99999999";
	        }
	        if (empty($status['not_submited'])){
	        	$status['submited'][] = "99999999";
	        }
 
            return $status;
	    }
	    

	    /**
	     * @cla on 10.06.2018 : do not use
	     * @deprecated
	     */
	    public function dgp_auto_export(){
	    
	        // get data for all clients
	        // if client has the auto submit setting
	        // get all filled patients and not submitted,
	        // submit them all
	    
	        $modules = new Modules();
	        $modules_array = array('124');
	        $dgp_export_clients = $modules->clients2modules($modules_array);
	        $patientmaster = new PatientMaster();

	        $dgp_kern_model = new DgpKern();
	        $partners_array = $dgp_kern_model->get_form_texts();
	         
	        $dgp['texts'] = $partners_array;
	        
	        $writer_dgp = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/dgp_auto.log');
	        $log_dgp = new Zend_Log($writer_dgp);
       
	        $dgp_response_text = array(
	        		"1000" => "All OK data received and saved.",
	        		"2000" => "Error during login (access data wrong):",
	        		"2100" => "Error while logging in (functional area not set):",
	        		"2200" => "Login failed (specified version not allowed):",
	        		"2300" => "No SSL encryption (no https connection):",
	        		"2900" => "Error while logging in (account locked):",
	        		"3000" => "Error of delivered data (no data received):",
	        		"3100" => "Error of received data (data does not correspond to the XSD file - no valid XML format):",
	        		"9999" => "9999",
	        		"4000" => "Error of received data (mandatory fields, insufficient amount of data):",
	        		"4100" => "Errors of the received data (Pat_ID and Dat_ID do not match):",
	        		"4200" => "Errors of the received data (Dat_ID submitted multiple times):",
	        		"9100" => "Reaching the maximum script runtime:",
	        		"9200" => "No lock on the center"
	         );
	        	        
// 	        $dgp_export_clients = array("1");
//	        $dgp_export_clients = array("121");

	        $log_dgp->info('--------------------------------------------------------------------');
	        $log_dgp->info('--------------------------------------------------------------------');
	        $log_dgp->info('ENGAGE');
	        $log_dgp->info('--------------------------------------------------------------------');
	        $log_dgp->info('--------------------------------------------------------------------');
	        
	        
	        $client_id_arr = array();
	        if(!empty($dgp_export_clients))
	        {
	            $file_location = APPLICATION_PATH . '/../public/run/';
	            $lock_filename = 'dgp_export.lockfile';
	            $lock_file = false;
	    
	            //check lock file
	            if(file_exists($file_location . $lock_filename))
	            {
	                //lockfile exists
	                $lock_file = true;
	                $log_dgp->info('RUN DGP:: file exists  ');
	            }
	            else
	            {
	                //no lock file exists, create it
	                $handle = fclose(fopen($file_location . $lock_filename, 'x'));
	                $lock_file = false;
	                $log_dgp->info('RUN DGP:: file was created  ');
	            }
	    
	            //skip dgp_export only if lockfile exists
	            if(!$lock_file)
	            {
	            	$log_dgp->info('RUN DGP:: START  -------------------------------------------------------- ');
// 	                $client_id_arr[] = '9999999999';
// 	                $dgp_export_clients[] = '99999999999';
	                foreach($dgp_export_clients as $client_id)
	                {
		                    $client_id_arr[] = $client_id;
	                }
	               
	                //get all client_details
	                $clist = Doctrine_Query::create()
	                ->select("id,dgp_transfer_date,,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(dgp_user,'" . Zend_Registry::get('salt') . "') as dgp_user,
					AES_DECRYPT(dgp_pass,'" . Zend_Registry::get('salt') . "') as dgp_pass")
	                					->from('Client')
	                					->where('isdelete=0')
	                					->andWhereIn('id ', $client_id_arr )
	                					->andWhere("dgp_user is NOT NULL")
	                					->andWhere("dgp_pass is NOT NULL");
	                $clients_res = $clist->fetchArray();
	                
	                $sadmin_users = array("verenakauth","sadmin","volkerkerkhoff","-");
	                $dgp_clients_details  = array();
	                
					foreach($clients_res as $k=>$cl_data){
						if(strlen($cl_data['dgp_user']) > 0  && !in_array($cl_data['dgp_user'],$sadmin_users )){
		                	$dgp_clients_details[$cl_data['id']]  = $cl_data; 
	                	} else{
		                	$dgp_clients_test[]  = $cl_data; 
	                	}
	                }
	                
	                
	                foreach($client_id_arr as $kc=>$clid){
		                if( ! array_key_exists($clid, $dgp_clients_details))
		                {
		                	unset($client_id_arr[$kc]);
		                }
	                }
	                
	                //for all this clients the ipids
	    
	             /*    $patient = Doctrine_Query::create()
	                ->select('p.ipid,e.clientid')
	                ->from('PatientMaster p')
	                ->leftJoin("p.EpidIpidMapping e")
	                ->where('p.isdelete = 0');
	                $patient->andWhereIn('e.clientid',$client_id_arr);
	                $all_clients_patients = $patient->fetchArray();
	    
	                foreach($all_clients_patients as $k=>$pdata){
	                    $patients_ipids[] = $pdata['ipid'];
	                    $patient2client[$pdata['ipid']] = $pdata['EpidIpidMapping']['clientid'];
	                    
	                }
	                $log_dgp->info('RUN DGP::NR OF ALL Patients '.count($patients_ipids));
	                if(!empty($patients_ipids)){
	                    $completed_patients =  $dgp_kern_model->patients_filled_status($patients_ipids);
		                $log_dgp->info('RUN DGP::NR OF  completed Patients '.count($not_submited_patients));

	                    $not_submited_patients =  $this->patients_submited_status($patients_ipids,true);
	                $log_dgp->info('RUN DGP::NR OF NOT submited Patients '.count($not_submited_patients));
	                    
	                    foreach($patients_ipids as $ipid){
	                        if(in_array($ipid,$completed_patients) && in_array($ipid,$not_submited_patients)){
	                            $export_ipids[] = $ipid;
	                        }
	                    }
	                } */
	                
	                
// 	                if(!empty($export_ipids)){
	                $log_dgp->info('RUN DGP:: Nr Clients '.count($client_id_arr));
	                if(!empty($client_id_arr)){
	                
    	                foreach($client_id_arr as $k=>$clientid){
    	                	$start_dgp = microtime(true);
    	                	
    	                	$log_dgp->info('=================================');
    	                	$log_dgp->info('RUN DGP:: CLIENT  '.$clientid);
    	                	$log_dgp->info('=================================');
    	                	
    	                	$xml_string = "";
    	                	$xml = "";
    	                	$patients_ipids_all = array();
    	                	$all_patients = array();
    	                	$patients_ipids = array();
    	                	$patients_ipids_all = array();
    	                	$all_clients_patients = array();
    	                	$in_dgp = array();
    	                	$client_details = array();
    	                	
    	                	$export_data = array();
    	                	
    	                	$patient = Doctrine_Query::create()
    	                	->select('p.ipid,e.clientid')
    	                	->from('PatientMaster p')
    	                	->leftJoin("p.EpidIpidMapping e")
    	                	->where('p.isdelete = 0')
    	                	->andWhere("p.isstandbydelete = 0");
    	                	$patient->andWhere('e.clientid = ' . $clientid);
    	                	$all_clients_patients = $patient->fetchArray();
    	                	 
    	                	foreach($all_clients_patients as $k=>$pdata){
    	                		$patients_ipids_all[] = $pdata['ipid'];
    	                	}
    	             
    	                	if( ! empty($patients_ipids_all)){
    	                		
    	                		$in_dgp_q = Doctrine_Query::create()
    	                		->select('ipid')
    	                		->from('DgpKern')
    	                		->whereIn('ipid',$patients_ipids_all)
    	                		->groupBy('ipid')
    	                		->orderby('id asc');
    	                		$in_dgp = $in_dgp_q->fetchArray();
    	                		
    	                		if( ! empty($in_dgp)){
    	                			foreach($in_dgp as $k=>$dk){
	    	                			$all_patients[] = $dk['ipid'];
    	                			}
    	                		}
    	                	}
    	                	
    	                	$client_data = new Client();
    	                	$client_details_array = $client_data->getClientDataByid($clientid);
    	                	$client_details = $client_details_array[0];
    	                	
    	                	if($client_details['dgp_transfer_date'] != "0000-00-00 00:00:00" && $client_details['dgp_transfer_date'] != "1970-01-01 01:01:00")
    	                	{
    	                		$log_dgp->info('RUN DGP::dgp_transfer_date '.$client_details['dgp_transfer_date']);
    	                		
    	                		
    	                		$active_patients = array();
    	                		$conditions = array();
    	                		$sqls = 'e.epid, p.ipid, e.ipid,';
    	                		$conditions['periods'][0]['start'] =   date('Y-m-d',strtotime($client_details['dgp_transfer_date']));
    	                		$conditions['periods'][0]['end'] = date('Y-m-d');
    	                		$conditions['client'] = $clientid;
    	                		$conditions['ipids'] = $all_patients;
    	                	
    	                		$patient_days = Pms_CommonData::patients_days($conditions,$sqls);
    	                		$active_patients = array_keys($patient_days);
    	                	
    	                		foreach ($all_patients as $k=> $ipid_val){
    	                			if(!in_array($ipid_val,$active_patients)){
    	                				unset($all_patients[$k]);
    	                			}
    	                		}
    	                		
    	                		$patients_ipids = $all_patients;
    	                		
    	                	} else {
    	                		$patients_ipids = $all_patients;
    	                	}

    	                	$log_dgp->info('RUN DGP::NR OF ALL Patients '.count($patients_ipids));
    	                	
    	                	$export_ipids = array();
    	                	$completed_patients = array();
    	                	$not_submited_patients = array();
    	                	
    	                	if(!empty($patients_ipids)){
    	                		
    	                		$completed_patients =  $dgp_kern_model->patients_filled_status($patients_ipids);
    	                		$log_dgp->info('RUN DGP::NR OF  completed Patients '.count($completed_patients));
    	                		 
    	                		$not_submited_patients =  $this->patients_submited_status($patients_ipids,true,array($clientid));
    	                		$log_dgp->info('RUN DGP::NR OF NOT submited Patients '.count($not_submited_patients));
    	                		 
    	                		foreach($patients_ipids as $ipid){
    	                			if(in_array($ipid,$completed_patients) && in_array($ipid,$not_submited_patients)){
    	                				$export_ipids[] = $ipid;
    	                			}
    	                		}
    	                	}
    	                	$log_dgp->info('RUN DGP::NR OF Patients for export '.count($export_ipids));

    	                	$ipids_arr = array();
    	                	$patienidtarray = array();
    	                	
	    	                if( ! empty($export_ipids)){
		    	                	
	    	                    $userid = "0"; // system upload
	    	                    $ipids_arr = $export_ipids;
	    	                    $patients_ids_arr = array();
	    	                    
	                            $patient = Doctrine_Query::create()
	                            ->select('*, p.ipid, p.admission_date, p.birthd, CONVERT(AES_DECRYPT(sex,"' . Zend_Registry::get('salt') . '") using latin1)  as gensex, e.epid, p.living_will as living_will')
	                            ->from('PatientMaster p')
	                            ->leftJoin("p.EpidIpidMapping e")
	                            ->where("p.isdelete = 0 and p.isstandbydelete=0")
	                            ->andwhereIn('p.ipid', $ipids_arr);
	                            $patient->andWhere('e.ipid = p.ipid');
	                            $patient->andWhere('e.clientid = ' . $clientid);
	                            $patienidtarray = $patient->fetchArray();
	                            //$log_dgp->info('RUN DGP:: - STEP 1  ');
	    
	    	                    if (count($patienidtarray) == 0)
	    	                    {
	    	                        $patienidtarray[0] = "1";
	    	                    }
	    	                    
	    	                    $patientarray[0]['count'] = sizeof($patienidtarray);
	    	                    
	    	                    $ipid_arr[] = '999999999';
	    	                    $ipidz_simple[] = '99999999999';
	    	                    
	    	                    foreach ($patienidtarray as $ipid)
	    	                    {
	    	                        $ipid_arr[] = $ipid['ipid'];
	    	                    
	    	                        $ipidz_simple[] = $ipid['ipid'];
	    	                    }
	    	                    
	    	                    $limit = 3000;
	    	                    $sql = "ipid,e.epid as epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
	    	                    $sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
	    	                    
	    	                    $patient->select($sql);
	    	                    $patient->whereIn('ipid', $ipids_arr);
	    	                    $patient->limit($limit);
	    	                    $patients_ids_arr = $patient->fetchArray();
	    	                    
	    	                    $ipid_str = '"99999999",';
	    	                    foreach ($patients_ids_arr as $ipid)
	    	                    {
	    	                        $ipid_str .= '"' . $ipid['ipid'] . '",';
	    	                    }
	    	                    $ipid_str = substr($ipid_str, 0, -1);
	    	                    
	    	                    
	    	                    //get first and last kvno data
	    	                    $log_dgp->info('RUN DGP:: - STEP 1  -- get first kern data START');
	    	                    
	    	                    $p_kvno_first = array();
						        $patient_k_first = Doctrine_Query::create()
						        ->select('*')
						        ->from('DgpKern ka')
						        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and  kb.form_type = "adm" and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` ASC	LIMIT 1 )')
						        ->groupBy('ka.ipid')
						        ->orderby('id asc');
	    	                    $p_kvno_first = $patient_k_first->fetchArray();
	    	                    $log_dgp->info('RUN DGP:: - STEP 1  -- get first  kern data  END ');
	    	                    
	    	                    
	    	                    $log_dgp->info('RUN DGP:: - STEP 1  -- get last kern data  START');
	    	                    $p_kvno_last = array();
						        $patient_k_last = Doctrine_Query::create()
						        ->select('*')
						        ->from('DgpKern ka')
						        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and  kb.form_type = "dis"  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` DESC	LIMIT 1 )')
						        ->groupBy('ka.ipid')
						        ->orderby('id asc');
	    	                    $p_kvno_last = $patient_k_last->fetchArray();
	    	                    $log_dgp->info('RUN DGP:: - STEP 1  -- get last  first data  END');

	    	                    $patient_kvno_first = array();
	    	                    foreach ($p_kvno_first as $k_first => $v_first)
	    	                    {
	    	                        $patient_kvno_first[$v_first['ipid']] = $v_first;
	    	                    }
	    	                    
	    	                    $patient_kvno_last = array();
	    	                    foreach ($p_kvno_last as $k_last => $v_last)
	    	                    {
	    	                        if ($patient_kvno_first[$v_last['ipid']]['id'] != $v_last['id'])
	    	                        {
	    	                            $patient_kvno_last[$v_last['ipid']] = $v_last;
	    	                        }
	    	                    }
	    	                    
	    	                    $log_dgp->info('RUN DGP:: - STEP 2  ');
	    	                    
	    	                    //get contactperson_master data
	    	                    $contact_data = array();
	    	                    $contact_patient_data = array();
	    	                    $contact_pm = Doctrine_Query::create()
	    	                    ->select('cpm.ipid, SUM(cpm.cnt_hatversorgungsvollmacht) as versorgung_max')
	    	                    ->from('ContactPersonMaster cpm')
	    	                    ->andwhere('cpm.ipid IN (' . $ipid_str . ')')
	    	                    ->groupBy('cpm.ipid')
	    	                    ->orderBy('cnt_hatversorgungsvollmacht ASC');
	    	                    $contact_data = $contact_pm->fetchArray();
	    	                    
	    	                    foreach ($contact_data as $k_contact => $v_contact)
	    	                    {
	    	                        $contact_patient_data[$v_contact['ipid']] = $v_contact;
	    	                    }
	    	                    
	    	                    //get all patients sapvs
	    	                   /*  $sapv = new SapvVerordnung();
	    	                    $sapvarray = $sapv->getPatientsSapvVerordnungDetails($ipid_arr, true);
	    	                    
	    	                    $patient_sapv_forms = Doctrine_Query::create()
	    	                    ->select('*')
	    	                    ->from('DgpSapv ds')
	    	                    ->where('ds.ipid in (' . $ipid_str . ')')
	    	                    ->orderby('ds.id asc');
	    	                    
	    	                    $patient_sapv_forms_filled = $patient_sapv_forms ->fetchArray();
	    	                    
	    	                    foreach($patient_sapv_forms_filled as $k_sapvf=>$v_sapvf)
	    	                    {
	    	                        $filled_sapvs[$v_sapvf['sapv']] = $v_sapvf;
	    	                    } */
	    	                    
	    	                    //get discharge locations
	    	                    $discharge_loc = array();
	    	                    $dl = new DischargeLocation();
	    	                    $discharge_locations = $dl->getDischargeLocation($clientid, 0);
	    	                    foreach ($discharge_locations as $k_disloc_client => $v_disloc)
	    	                    {
									$discharge_loc [$v_disloc['id']] = $v_disloc;
	    	                    }
	    	                    
	    	                    //process patient data
	    	                    $final_patient_data = array();
	    	                    foreach ($patients_ids_arr as $k_pat => $v_pat)
	    	                    {
	    	                    	$tod_date_patient = '';
	    	                    	if(array_key_exists($v_pat['ipid'], $patients_tod_date))
	    	                    	{
	    	                    		$tod_date_patient = $patients_tod_date[$v_pat['ipid']];
	    	                    	}
	    	                    	else
	    	                    	{
	    	                    		$tod_date_patient = date("Y-m-d", time());
	    	                    	}
	    	                    	$final_patient_data[$v_pat['ipid']]['age'] = $patientmaster->GetAge($v_pat['birthd'], $tod_date_patient, true);
	    	                    }
	    	                    
	    	                    // ACP
	    	                    if(!empty($ipids_arr)){
	    	                    	$acp_data_patients = array();
							        $acp = new PatientAcp();
							        $acp_data_patients = $acp->getByIpid($ipids_arr);
							        if( ! empty($acp_data_patients))
							        {
							        	foreach($acp_data_patients as $ipid=>$acp_data)
							        	{
							        		foreach($acp_data as $k=>$block)
							        		{
							        			if($block['division_tab'] == "living_will"){
							        
							        				if($block['active'] == "yes"){
							        					$export_data[$ipid]['BL_Patientenverfuegung'] = '1';
							        				} else{
							        					$export_data[$ipid]['BL_Patientenverfuegung'] = '-1';
							        				}
							        
							        			}
							        			elseif($block['division_tab'] == "healthcare_proxy")
							        			{
							        				if($block['active'] == "yes"){
							        					$export_data[$ipid]['BL_Vorsorgevollmacht'] = '1';
							        				} else{
							        					$export_data[$ipid]['BL_Vorsorgevollmacht'] = '-1';
							        				}
							        
							        			}
							        			elseif($block['division_tab'] == "care_orders")
							        			{
							        				if($block['active'] == "yes"){
							        					$export_data[$ipid]['BL_Betreuungsurkunde'] = '1';
							        				} else{
							        					$export_data[$ipid]['BL_Betreuungsurkunde'] = '-1';
							        				}
							        
							        			}
							        		}
							        	}
							        }
	    	                    }    
	    	                    $log_dgp->info('RUN DGP:: - STEP 3 ');
	    	                    
	    	                    foreach ($patients_ids_arr as $k_pat => $v_pat)
						        {
									$export_data[$v_pat['ipid']]['B_Programm'] = 'ISPC';
									$export_data[$v_pat['ipid']]['B_Pat_ID'] = strtoupper($v_pat['epid']);
						            $export_data[$v_pat['ipid']]['B_Dat_ID'] = strtoupper($v_pat['epid']);
	// 					        $export_data[$v_pat['ipid']]['B_geb_datum'] = date('Y-m', strtotime($v_pat['birthd']));
	 					            $export_data[$v_pat['ipid']]['B_XML_date'] = date('Y-m-d', time());
	 					            $export_data[$v_pat['ipid']]['B_Alter'] = $final_patient_data[$v_pat['ipid']]['age'];
						            $export_data[$v_pat['ipid']]['B_auf_datum'] = date('Y-m-d', strtotime($v_pat['admission_date']));
						            
						            //B_geschlecht
						            // -1 = Keine Angabe;
						            // 1 = 1 - weiblich;
						            // 2 = 2 - männlich
						            if (!empty($v_pat['sex']))
						            {
						                $sex_map = array ('1' => '2', '2' => '1');
						                $gender = $sex_map[$v_pat['sex']];
						            }
						            else
						            {
						                $gender = '-1';
						            }
						    
						            $export_data[$v_pat['ipid']]['B_geschlecht'] = $gender;
						            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
						        }
	    	                    
						        $log_dgp->info('RUN DGP:: - STEP 4  ');
	    	                    //		get various nonkvno saved data
	    	                    //		get Haupt Diagnosis diagnosis
	    	                    $dg = new DiagnosisType();
	    	                    $abb2 = "'HD'";
	    	                    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
	    	                    $comma = ",";
	    	                    $typeid = "'0'";
	    	                    foreach ($ddarr2 as $key => $valdia)
	    	                    {
	    	                    	$typeid .=$comma . "'" . $valdia['id'] . "'";
	    	                    	$comma = ",";
	    	                    }
	    	                    
	    	                    $dianoarray = array();
	    	                    $patdia = new PatientDiagnosis();
	    	                    $dianoarray = $patdia->getFinalData($ipid_str, $typeid, true); //set last param true to accept a list of ipids
	    	                    
	    	                    if (count($dianoarray) > 0)
	    	                    {
	    	                    	foreach ($dianoarray as $key => $valdia)
	    	                    	{
	    	                    		if (!empty($valdia['diagnosis']) && !empty($valdia['icdnumber']))
	    	                    		{
	    	                    			$export_data[$valdia['ipid']]['BA_ICD_haupt'] = $valdia['icdnumber'];
	    	                    			$export_data[$valdia['ipid']]['BL_ICD_haupt'] = $valdia['icdnumber'];
	    	                    		}
	    	                    	}
	    	                    }
	    	                    
	    	                    
	    	                    $wohn_mapping = array ('0' => '-1', '1' => '1', '2' => '3', '4' => '2', '6' => '4','5'=>'5');
	    	                    $log_dgp->info('RUN DGP:: - STEP 5  ');
	    	                    //prepare export first kvno data
	    	                    foreach ($patient_kvno_first as $k_pkvno => $v_pkvno)
	    	                    {
	    	                    
	    	                    	//BA_wohnsituation
	    	                    	// -1=Keine Angabe;
	    	                    	// 1 =1 - allein;
	    	                    	// 2 =2 - Heim;
	    	                    	// 3 =3 - mit Angehörigen;
	    	                    	// 4 =4 - Sonstige
	    	                    
	    	                    	//BA_wohnsituation
	    	                    	//1	 Allein zu Hause lebend
	    	                    	//2	 Im (Pflege-/Alten-)Heim
	    	                    	//3	 mit Angehörigen oder anderen privaten Bezugspersonen im Haushalt
	    	                    	//4	 Sonstige
	    	                    	//5	 stationäres Hospiz
	    	                    	//-1 not applicable
	    	                    	 
	    	                    	 
	    	                    	if (!empty($v_pkvno['wohnsituations']))
	    	                    	{
	    	                    		$wohn = $wohn_mapping[$v_pkvno['wohnsituations']];
	    	                    	}
	    	                    	else
	    	                    	{
	    	                    		$wohn = '-1';
	    	                    	}
	    	                    
	    	                    	$export_data[$v_pkvno['ipid']]['BA_wohnsituation'] = $wohn;
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    
	    	                    	// BA_ecog
	    	                    	// -1=Keine Angabe;
	    	                    	// 0=0 - Normale Aktivitat;
	    	                    	// 1=1 - Gehfahig, leichte Aktivitat möglich;
	    	                    	// 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
	    	                    	// 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
	    	                    	// 4=4 - Pflegebedurftig, permanent bettlagerig
	    	                    	if (!empty($v_pkvno['ecog']))
	    	                    	{
	    	                    		$ecog_value = ($v_pkvno['ecog'] - 1);
	    	                    	}
	    	                    	else
	    	                    	{
	    	                    		$ecog_value = '-1';
	    	                    	}
	    	                    
	    	                    	$export_data[$v_pkvno['ipid']]['BA_ecog'] = $ecog_value;
	    	                    
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    	//BA_Pverfuegung
	    	                    	if ($v_pkvno['pverfuegung'] == "1")
	    	                    	{
	    	                    		$export_data[$v_pkvno['ipid']]['BA_Patientenverfuegung'] = '1';
	    	                    	}
	    	                    	else
	    	                    	{
	    	                    		$export_data[$v_pkvno['ipid']]['BA_Patientenverfuegung'] = '-1';
	    	                    	}
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    	 
	    	                    	//BA_vollmacht
	    	                    	if ($v_pkvno['vollmacht'] == "1")
	    	                    	{
	    	                    		$export_data[$v_pkvno['ipid']]['BA_Vorsorgevollmacht'] = '1';
	    	                    	}
	    	                    	else
	    	                    	{
	    	                    		$export_data[$v_pkvno['ipid']]['BA_Vorsorgevollmacht'] = '-1';
	    	                    	}
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    	//BA_Betreuungsurkunde
	    	                    	if ($v_pkvno['betreuungsurkunde'] == "1")
	    	                    	{
	    	                    		$export_data[$v_pkvno['ipid']]['BA_Betreuungsurkunde'] = '1';
	    	                    	}
	    	                    	else
	    	                    	{
	    	                    		$export_data[$v_pkvno['ipid']]['BA_Betreuungsurkunde'] = '-1';
	    	                    	}
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    	//BA_ACP
	    	                    	if ($v_pkvno['acp'] == "1")
	    	                    	{
	    	                    		$export_data[$v_pkvno['ipid']]['BA_ACP'] = '1';
	    	                    	}
	    	                    	else
	    	                    	{
	    	                    		$export_data[$v_pkvno['ipid']]['BA_ACP'] = '-1';
	    	                    	}
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    
	    	                    
	    	                    
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    
	    	                    	// BA_datum
	    	                    	// Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
	    	                    	if (!empty($v_pkvno['datum_der_erfassung1']) && $v_pkvno['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno['datum_der_erfassung1'] != '0000-00-00 00:00:00')
	    	                    	{
	    	                    		//                 $export_data[$v_pkvno['ipid']]['BA_datum'] = date('Y-m-d', strtotime($v_pkvno['datum_der_erfassung1']));
	    	                    	}
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    
	    	                    	// =============================================== //
	    	                    	//this part is repeating with BA and BL prefix
	    	                    	// =============================================== //
	    	                    
	    	                    	// BA_Hausarzt
	    	                    	// 1 = ausgewählt
	    	                    	// -1 = nicht ausgewählt
	    	                    	if (!empty($v_pkvno['begleitung']))
	    	                    	{
	    	                    		$begleitung = explode(',', $v_pkvno['begleitung']);
	    	                    	}
	    	                    
	    	                    	$export_data[$v_pkvno['ipid']]['BA_KH_Palliativstation'] = (in_array('8', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Hospiz_stationaer'] = (in_array('9', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Heim'] = (in_array('11', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Hausarzt'] = (in_array('2', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Ambulante_Pflege'] = (in_array('3', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Palliativarzt'] = (in_array('4', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Palliativpflege'] = (in_array('5', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Palliativberatung'] = (in_array('14', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Palliative_Care_Team'] = (in_array('12', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_MVZ'] = (in_array('15', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_KH_Palliativdienst'] = (in_array('16', $begleitung)) ? '1' : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_sonstige_behandlung'] = (in_array('17', $begleitung)) ? '1' : '-1';
	    	                    
	    	                    	 
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    
	    	                    	//BA_Schmerzen
	    	                    	// -1=Keine Angabe;
	    	                    	// 0=0 - kein;
	    	                    	// 1=1 - leicht;
	    	                    	// 2=2 - mittel;
	    	                    	// 3=3 - stark
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Schmerzen'] = (!empty($v_pkvno['schmerzen'])) ? ($v_pkvno['schmerzen'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Ubelkeit'] = (!empty($v_pkvno['ubelkeit'])) ? ($v_pkvno['ubelkeit'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Erbrechen'] = (!empty($v_pkvno['erbrechen'])) ? ($v_pkvno['erbrechen'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Luftnot'] = (!empty($v_pkvno['luftnot'])) ? ($v_pkvno['luftnot'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Verstopfung'] = (!empty($v_pkvno['verstopfung'])) ? ($v_pkvno['verstopfung'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Schwache'] = (!empty($v_pkvno['swache'])) ? ($v_pkvno['swache'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Appetitmangel'] = (!empty($v_pkvno['appetitmangel'])) ? ($v_pkvno['appetitmangel'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Mudigkeit'] = (!empty($v_pkvno['mudigkeit'])) ? ($v_pkvno['mudigkeit'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Wunden'] = (!empty($v_pkvno['dekubitus'])) ? ($v_pkvno['dekubitus'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Hilfe_ATL'] = (!empty($v_pkvno['hilfebedarf'])) ? ($v_pkvno['hilfebedarf'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Depressivitat'] = (!empty($v_pkvno['depresiv'])) ? ($v_pkvno['depresiv'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Angst'] = (!empty($v_pkvno['angst'])) ? ($v_pkvno['angst'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Anspannung'] = (!empty($v_pkvno['anspannung'])) ? ($v_pkvno['anspannung'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Verwirrtheit'] = (!empty($v_pkvno['desorientier'])) ? ($v_pkvno['desorientier'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Versorgungsorg'] = (!empty($v_pkvno['versorgung'])) ? ($v_pkvno['versorgung'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_Uberforderung'] = (!empty($v_pkvno['umfelds'])) ? ($v_pkvno['umfelds'] - 1) : '-1';
	    	                    	$export_data[$v_pkvno['ipid']]['BA_sonstige_probleme'] = (!empty($v_pkvno['sonstige_probleme'])) ? ($v_pkvno['sonstige_probleme'] - 1) : '-1';
	    	                    	/*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
	    	                    
	    	                    }
	    	                    $log_dgp->info('RUN DGP:: - STEP 6  ');
	    	                        foreach ($patient_kvno_last as $k_pkvno_last => $v_pkvno_last)
							        {
							    
							            //BL_wohnsituation
							            // -1=Keine Angabe;
							            // 1=1 - allein;
							            // 2=2 - Heim;
							            // 3=3 - mit Angehörigen;
							            // 4=4 - Sonstige
							            
							        	//-1 = Keine Angabe; 
							        	//1 = Allein zu Hause lebend ;
							        	//2 = Im (Pflege-/Senioren-)Heim; 
							        	//3 = mit Angehörigen oder anderen privaten Bezugspersonen im Haushalt; 
							        	//4 = Sonstige; 
							        	//5 = stationäres Hospiz
							            if (!empty($v_pkvno_last['wohnsituations']))
							            {
							                $wohn = $wohn_mapping[$v_pkvno_last['wohnsituations']];
							            }
							            else
							            {
							                $wohn = '-1';
							            }
							            $export_data[$v_pkvno_last['ipid']]['BL_wohnsituation'] = $wohn;
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            
							            // BL_datum
							            // 13. Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
							            if (!empty($v_pkvno_last['datum_der_erfassung1']) && $v_pkvno_last['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno_last['datum_der_erfassung1'] != '0000-00-00 00:00:00')
							            {
							//                 $export_data[$v_pkvno_last['ipid']]['BL_datum'] = date('Y-m-d', strtotime($v_pkvno_last['datum_der_erfassung1']));
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							    
							            
							            //BL_ecog
							            // -1=Keine Angabe;
							            // 0=0 - Normale Aktivitat;
							            // 1=1 - Gehfahig, leichte Aktivitat möglich;
							            // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
							            // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
							            // 4=4 - Pflegebedurftig, permanent bettlagerig
							            if (!empty($v_pkvno_last['ecog']))
							            {
							                $ecog_value = ($v_pkvno_last['ecog'] - 1);
							            }
							            else
							            {
							                $ecog_value = '-1';
							            }
							    
							            $export_data[$v_pkvno_last['ipid']]['BL_ecog'] = $ecog_value;
							            
							
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            //BL_Pverfuegung
							            if ($v_pkvno_last['pverfuegung'] == "1")
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_Patientenverfuegung'] = '1';
							            }
							            else
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_Patientenverfuegung'] = '-1';
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            //BL_vollmacht
							            if ($v_pkvno_last['vollmacht'] == "1")
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_Vorsorgevollmacht'] = '1';
							            }
							            else
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_Vorsorgevollmacht'] = '-1';
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            //BL_Betreuungsurkunde
							            if ($v_pkvno_last['betreuungsurkunde'] == "1")
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_Betreuungsurkunde'] = '1';
							            }
							            else
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_Betreuungsurkunde'] = '-1';
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            //BL_ACP
							            if ($v_pkvno_last['acp'] == "1")
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_ACP'] = '1';
							            }
							            else
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_ACP'] = '-1';
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            			
							            			
							            
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            //BL_Hausarzt
							            // 1 = ausgewählt 
							            // -1 = nicht ausgewählt
							            if (!empty($v_pkvno_last['begleitung']))
							            {
							                $begleitung_last = explode(',', $v_pkvno_last['begleitung']);
							            }
							            $export_data[$v_pkvno_last['ipid']]['BA_KH_Palliativstation'] = (in_array('8', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Hospiz_stationaer'] = (in_array('9', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Heim'] = (in_array('11', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Hausarzt'] = (in_array('2', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Ambulante_Pflege'] = (in_array('3', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Palliativarzt'] = (in_array('4', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Palliativpflege'] = (in_array('5', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Palliativberatung'] = (in_array('14', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_Palliative_Care_Team'] = (in_array('12', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_MVZ'] = (in_array('15', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_KH_Palliativdienst'] = (in_array('16', $begleitung_last)) ? '1' : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BA_sonstige_behandlung'] = (in_array('17', $begleitung_last)) ? '1' : '-1';
							            
							            
							            
							            
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            // BL_Schmerzen
							            // -1=Keine Angabe;
							            // 0=0 - kein;
							            // 1=1 - leicht;
							            // 2=2 - mittel;
							            // 3=3 - stark
							            $export_data[$v_pkvno_last['ipid']]['BL_Schmerzen'] = (!empty($v_pkvno_last['schmerzen'])) ? ($v_pkvno_last['schmerzen'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Ubelkeit'] = (!empty($v_pkvno_last['ubelkeit'])) ? ($v_pkvno_last['ubelkeit'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Erbrechen'] = (!empty($v_pkvno_last['erbrechen'])) ? ($v_pkvno_last['erbrechen'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Luftnot'] = (!empty($v_pkvno_last['luftnot'])) ? ($v_pkvno_last['luftnot'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Verstopfung'] = (!empty($v_pkvno_last['verstopfung'])) ? ($v_pkvno_last['verstopfung'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Schwache'] = (!empty($v_pkvno_last['swache'])) ? ($v_pkvno_last['swache'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Appetitmangel'] = (!empty($v_pkvno_last['appetitmangel'])) ? ($v_pkvno_last['appetitmangel'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Mudigkeit'] = (!empty($v_pkvno_last['mudigkeit'])) ? ($v_pkvno_last['mudigkeit'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Wunden'] = (!empty($v_pkvno_last['dekubitus'])) ? ($v_pkvno_last['dekubitus'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Hilfe_ATL'] = (!empty($v_pkvno_last['hilfebedarf'])) ? ($v_pkvno_last['hilfebedarf'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Depressivitat'] = (!empty($v_pkvno_last['depresiv'])) ? ($v_pkvno_last['depresiv'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Angst'] = (!empty($v_pkvno_last['angst'])) ? ($v_pkvno_last['angst'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Anspannung'] = (!empty($v_pkvno_last['anspannung'])) ? ($v_pkvno_last['anspannung'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Verwirrtheit'] = (!empty($v_pkvno_last['desorientier'])) ? ($v_pkvno_last['desorientier'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Versorgungsorg'] = (!empty($v_pkvno_last['versorgung'])) ? ($v_pkvno_last['versorgung'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_Uberforderung'] = (!empty($v_pkvno_last['umfelds'])) ? ($v_pkvno_last['umfelds'] - 1) : '-1';
							            $export_data[$v_pkvno_last['ipid']]['BL_sonstige_probleme'] = (!empty($v_pkvno_last['sonstige_probleme'])) ? ($v_pkvno_last['sonstige_probleme'] - 1) : '-1';
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            // BL_Opioide_WHO_Stufe_3
							            // 1 = ausgewählt 
							            // -1 = nicht ausgewählt
							            if ($v_pkvno_last['who'] == '1')
							            {
							                $opioids = '1';
							            }
							            else
							            {
							                $opioids = '-1';
							            }
							    
							//             $export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_3'] = $opioids;
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            // BL_Kortikosteroide
							            // 1 = ausgewählt
							            // -1 = nicht ausgewählt
							            if ($v_pkvno_last['steroide'] == '1')
							            {
							                $steroide = '1';
							            }
							            else
							            {
							                $steroide = '-1';
							            }
							    
							//             $export_data[$v_pkvno_last['ipid']]['BL_Kortikosteroide'] = $steroide;
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            //BL_Chemotherapie
							            // -1=Keine Angabe;
							            // 0=0 = nein;
							            // 1=1 = fortgesetzt;
							            // 2=2 = initiiert
							            $export_data[$v_pkvno_last['ipid']]['BL_Chemotherapie'] = ($v_pkvno_last['chemotherapie'] - 1);
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            // BL_Strahlentherapie
							            // -1=Keine Angabe; 
							            // 0=0 = nein;
							            // 1=1 = fortgesetzt;
							            // 2=2 = initiiert
							            $export_data[$v_pkvno_last['ipid']]['BL_Strahlentherapie'] = ($v_pkvno_last['strahlentherapie'] - 1);
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							    
							            // BL_Aufwand
							            // text
							            if (!empty($v_pkvno_last['aufwand_mit']))
							            {
							                $export_data[$v_pkvno_last['ipid']]['BL_Aufwand'] = $v_pkvno_last['aufwand_mit'];
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							    
							            // BL_problem_1 
							            // text
							            if(!empty($v_pkvno_last['problem_besonders']))
							            {
							                $export_data[$v_pkvno_last['ipid']]['BL_problem_1'] = $v_pkvno_last['problem_besonders'];
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							    
							            
							            //BL_problem_2
							            // text
							            if(!empty($v_pkvno_last['problem_ausreichend']))
							            {
							                $export_data[$v_pkvno_last['ipid']]['BL_problem_2'] = $v_pkvno_last['problem_ausreichend'];
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							    
							            //BL_bedarf
							            // text
							            if(!empty($v_pkvno_last['bedarf']))
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_Bedarf'] = $v_pkvno_last['bedarf'];
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            	
							            	
							            //BL_massnahmen
							            // text
							            if(!empty($v_pkvno_last['massnahmen']))
							            {
							            	$export_data[$v_pkvno_last['ipid']]['BL_Massnahmen'] = $v_pkvno_last['massnahmen'];
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            $med_referance_value = array();
							            foreach( $dgp['texts']['medication_references_all'] as $k=>$mr_data){
							
							            	if ($v_pkvno_last[$mr_data['code']] == '1')
							            	{
							            		$med_referance_value[$mr_data['code']]  = '1';
							            	}
							            	else
							            	{
							            		$med_referance_value[$mr_data['code']]= '-1';
							            	}
							            	$export_data[$v_pkvno_last['ipid']][$mr_data['dgp_export']] == $med_referance_value[$mr_data['code']];
							            }
							            
							            
							//             $export_data[$v_pkvno_last['ipid']]['BL_Analgetika'] = (in_array('1', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_2'] = (in_array('2', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_3'] = (in_array('3', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Nicht_Opioide'] = (in_array('4', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Co_Analgetika'] = (in_array('5', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Steroide'] = (in_array('6', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Anxiolytika'] = (in_array('7', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Laxantien'] = (in_array('8', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Sedativa'] = (in_array('9', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Neuroleptika'] = (in_array('10', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Anti_Epileptika'] = (in_array('11', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Anti_Emetika'] = (in_array('12', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Anti_Biotika'] = (in_array('13', $medication_references)) ? '1' : '-1';
							//             $export_data[$v_pkvno_last['ipid']]['BL_Magenschutz'] = (in_array('14', $medication_references)) ? '1' : '-1';
							            
							            
							            
							            //B_datum_ende
							            // jjjj-mm-tt
							            // Entlassung / Anderung der Betreuung / Tod
							            if(strlen($v_pkvno_last['entlasung_date'])){
							                $export_data[$v_pkvno_last['ipid']]['B_datum_ende'] = date('Y-m-d', strtotime($v_pkvno_last['entlasung_date']));
							            }
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							            
							            // B_thera_ende
							            // -1=Keine Angabe;
							            // 1=1 - Verstorben;
							            // 2=2 - Verlegung, Entlassung;
							            // 4=4 - Sonstiges
							            if ($v_pkvno_last['therapieende'] == '0' || empty($v_pkvno_last['therapieende']))
							            {
							                $thera_ende = "-1";
							            }
							            else
							            {
							                $thera_ende_mapping = array('0'=>'-1', '1'=>'1', '2'=>'2', '3'=>'2','4'=>'4'); //thera ende has no "3" value in xsd...weird
							                $thera_ende = $thera_ende_mapping[$v_pkvno_last['therapieende']];
							            }
							    
							            $export_data[$v_pkvno_last['ipid']]['B_thera_ende'] = $thera_ende;
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							
							            
							            //B_sterbeort_0
							            //XSD V 3.0
							            //23. Sterbeort | -1=Keine Angabe; 1=zuhause; 2=Heim; 3=Hospiz; 4=Palliativstation; 5=Krankenhaus; 6=unbekannt; 7=Sonstiges

							            $sterbeort = '-1';//Keine Angabe
							            if (empty($v_pkvno_last['sterbeort_dgp']) || $v_pkvno_last['sterbeort_dgp'] == '0')
							            {
							                $sterbeort = '-1';//Keine Angabe
							            }
							            else
							            {
							            	//SYSTEM Loc type ids       -> XSD values
							                //1 = zu Hause              -> XSD : 1
							                //2 = KH                    -> XSD : 5
							                //3 = Hospiz                -> XSD : 3
							                //4 = Altenheim, Pflegeheim -> XSD : 2
							                //5 = Palliativ             -> XSD : 4
							                //6 = bei Kontaktperson     -> XSD : 6 // unknown in register
							            	//7 = Kurzzeitpflege        -> XSD : 6 // unknown in register 
							            	//8 = betreutes Wohnen      -> XSD : 6 // unknown in register
							            	//other				        -> XSD : 7 // sonstiges
							                
							                $location_type_map = array (
							                		'1' => '1', //zu Hause 
							                		'2' => '5', //Krankenhaus
							                		'3' => '3', //Hospiz
							                		'4' => '2', //Heim 
							                		'5' => '4', //Palliativstation
							                		'6' => '6', //bei Kontaktperson 
							                		'7' => '6', //Kurzzeitpflege   
							                		'8' => '6'  //betreutes Wohnen   
							                );
							    
							                if ($discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type'] != 0 && strlen($location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']]) > 0 )
							                {
							                    $sterbeort = $location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']];
							                }
							                else
							                {
							                    //no location type or other(sonstiges...)
							                    $sterbeort = '7';
							                }
							            }
							    
							            $export_data[$v_pkvno_last['ipid']]['B_sterbeort_0'] = $sterbeort;
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							    
							            
							            //   B_bewertung
							            //  -1=Keine Angabe;
							            //   1=1 - sehr gut;
							            //   2=2 - gut;
							            //   3=3 - mittel;
							            //   4=4 - schlecht;
							            //   5=5 - sehr schlecht
							            $satisfaction_map = array ('0'=>'-1','1' => '5', '2' => '4', '3' => '3', '4' => '2', '5' => '1');
							            
							            if (empty($v_pkvno_last['zufriedenheit_mit']))
							            {
							                $satisfaction = '-1';
							            }
							            else
							            {
							                $satisfaction = $satisfaction_map [$v_pkvno_last['zufriedenheit_mit']];
							            }
							            $export_data[$v_pkvno_last['ipid']]['B_bewertung'] = $satisfaction;
							            /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
							    
							        } //end last kvno foreach
	    	                    
							        $log_dgp->info('RUN DGP:: - STEP 7  ');
	    	                    
	                            // ######################################################################################################################################
	                            // SEND KERNE PART
	                            // ######################################################################################################################################
	                            
	    	                //get KERNE xml
							$xml = "";
							$xml_string = "";
							$history_id = "";
				            $kern_schema = '<alle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="KERN_3.1.xsd"></alle>';
				            $xml = $this->toXml($export_data, $kern_schema, null, 'KERN');
				
				            //echo $xml;
				            if (class_exists('DOMDocument'))
				            {
				                $doc = new DOMDocument();
				                $dom_xml = $doc->loadXML($xml);
				                $source_path = APP_BASE . 'xsd/KERN_3.1.xsd';
				                $validation = $doc->schemaValidate($source_path);
				            }
				            else
				            {
				                $validation = true; //sskip validation if DOMDocument is not installed
				            }
				
				            $validation = true;
				            
				            if ($validation)
				            {
				            	
			            		$xml_string = "";
				            	$xml_type = 'KERN';
				                $xml_string = $this->xmlpp($xml, false);
				                //$xml_string = $xml;
				
				                //send xml via post
				                $response = "";
				                $response = $this->data_send($clientid, $xml_string, $xml_type);
				                $log_dgp->info('RUN DGP:: - STEP 8: data sent  ');
				                
				                $history_id = "";
				                $history_id = $this->history_save($userid, $clientid, $xml_string, $response);
				        
				                if (substr($response, 0, 4) == '1000') //1000 is ok RC
				                {
				                    // save patients - for this upload
				                    if($history_id ){
				                    	$exported_ipids = array();
				                        foreach($export_data as $pipid => $ex_data){
				                            $exported_ipids[]= $pipid ;
				                        }
				                        $this->history_patients_save($history_id,$exported_ipids, $userid, $clientid);
				                         
				                    }
				                    // redir success
				                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=suc&c=".substr($response, 0, 4));
				                    $kern_send_status = "suc"; 
				                    $kern_msg = substr($response, 0, 4);
				                }
				                else if(substr($response, 0, 4) == '2000')
				                {
				                    // redir error auth error
				                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
				                    $kern_send_status = "err";
				                    $kern_msg = substr($response, 0, 4);
				                }
				                else if(substr($response, 0, 4) == '0000')
				                {
				                    // curl error
				                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
				                    $kern_send_status = "err";
				                    $kern_msg = substr($response, 0, 4);
				                }
				                else if(substr($response, 0, 4) == '3100')
				                {
				                    // XML error
				                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
				                    $kern_send_status = "err";
				                    $kern_msg = substr($response, 0, 4);
				                }
				                else
				                {
				                    // redir generic error
				                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err");
				                    $kern_send_status = "err";
				                    $kern_msg = "generic error";
				                }  
				            }
				            else
				            {
				            	
				                // validation error
				                // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=9999");
				                $this->history_save($userid, $clientid, $xml, 'NULL');
				                $kern_send_status = "error";
				                $kern_msg = "9999";
				            }
				            
				            $log_dgp->info('RUN DGP:: Response'.substr($response, 0, 4).'::::  '.$dgp_response_text[substr($response, 0, 4)]);
				            
				            $end_dgp =  microtime(true) - $start_dgp;
				            $log_dgp->info('RUN DGP:: - auto export function  was executed for client '.$clientid.'('.round($end_dgp, 0).' Seconds )');
	                            
	    	                }// end if export ipids
						} // endforeach cleint array
	                }// end  if not empty client array 
	                unlink($file_location . $lock_filename);
	                $log_dgp->info('RUN DGP:: file was removed  ');
	                $log_dgp->info('RUN DGP:: THE  END ---------------------------------------');
	            }
	            else
	            {
	            	//	mail
	            	$message_both = "Dgp  lockfile exists - autosubmit -was NOT  completed";
	            	$this -> errormail($message_both);
	            	$log_dgp->info('RUN DGP:: - auto export function FAILD   lockfile exists  ');
	            }
	        }
	    }
	    
	    /**
	     * @cla on 10.06.2018 : do not use
	     * @deprecated
	     */
	    public function dgp_auto_export_v2(){
	    
	        // get data for all clients
	        // if client has the auto submit setting
	        // get all filled patients and not submitted,
	        // submit them all
	    
	        $modules = new Modules();
	        $modules_array = array('124');
	        $dgp_export_clients = $modules->clients2modules($modules_array);
	    
	        if(!empty($dgp_export_clients))
	        {
	            $file_location = APPLICATION_PATH . '/../public/run/';
	            $lock_filename = 'dgp_export.lockfile';
	            $lock_file = false;
	    
	            //check lock file
	            if(file_exists($file_location . $lock_filename))
	            {
	                //lockfile exists
	                $lock_file = true;
	            }
	            else
	            {
	                //no lock file exists, create it
	                $handle = fclose(fopen($file_location . $lock_filename, 'x'));
	                $lock_file = false;
	            }
	    
	            //skip dgp_export only if lockfile exists
	            if(!$lock_file)
	            {
	                $client_id_arr[] = '9999999999';
	                $dgp_export_clients[] = '99999999999';
	                foreach($dgp_export_clients as $client_id)
	                {
	                    $client_id_str .= '"' . $client_id . '",';
	                    $client_id_arr[] = $client_id;
	                }
	    
	    
	                //for all this clients the ipids
	    
	                $patient = Doctrine_Query::create()
	                ->select('p.ipid,e.clientid')
	                ->from('PatientMaster p')
	                ->leftJoin("p.EpidIpidMapping e")
	                ->where('p.isdelete = 0');
	                $patient->andWhereIn('e.clientid',$client_id_arr);
	                $all_clients_patients = $patient->fetchArray();
	    
	                foreach($all_clients_patients as $k=>$pdata){
	                    $patients_ipids[] = $pdata['ipid'];
	                    $patient2client[$pdata['ipid']] = $pdata['EpidIpidMapping']['clientid'];
	                    
	                }
	    
	                if(!empty($patients_ipids)){
	                    $completed_patients =  DgpKern::patients_filled_status($patients_ipids);
	                    $not_submited_patients =  $this->patients_submited_status($patients_ipids,true);
	    
	                    
	                    foreach($patients_ipids as $ipid){
	                        if(in_array($ipid,$completed_patients) && in_array($ipid,$not_submited_patients)){
	                            $export_ipids[] = $ipid;
	                        }
	                    }
	                }
	                
	                if(!empty($export_ipids)){
	                    
	                    foreach($export_ipids as $pat_ipid){
	                        $client_patients[$patient2client[$pat_ipid]][] = $pat_ipid;
	                    }
	                    
	                
    	                foreach($client_patients as $clientid =>$ipids_arr){
    	                    $userid = "0"; // system upload
    	                    $ipids_arr = $export_ipids;
                            
                            $patient = Doctrine_Query::create()
                            ->select('*, p.ipid, p.admission_date, p.birthd, CONVERT(AES_DECRYPT(sex,"' . Zend_Registry::get('salt') . '") using latin1)  as gensex, e.epid, p.living_will as living_will')
                            ->from('PatientMaster p')
                            ->leftJoin("p.EpidIpidMapping e")
                            ->where("p.isdelete = 0 and p.isstandbydelete=0")
                            ->andwhereIn('p.ipid', $ipids_arr);
                            $patient->andWhere('e.ipid = p.ipid');
                            $patient->andWhere('e.clientid = ' . $clientid);
                            $patienidtarray = $patient->fetchArray();
                            
    
    	                    if (count($patienidtarray) == 0)
    	                    {
    	                        $patienidtarray[0] = "1";
    	                    }
    	                    
    	                    $patientarray[0]['count'] = sizeof($patienidtarray);
    	                    
    	                    $ipid_arr[] = '999999999';
    	                    $ipidz_simple[] = '99999999999';
    	                    
    	                    foreach ($patienidtarray as $ipid)
    	                    {
    	                        $ipid_arr[] = $ipid['ipid'];
    	                    
    	                        $ipidz_simple[] = $ipid['ipid'];
    	                    }
    	                    
    	                    $limit = 3000;
    	                    $sql = "ipid,e.epid as epid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
    	                    $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
    	                    $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
    	                    $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
    	                    $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
    	                    $sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
    	                    $sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
    	                    $sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
    	                    $sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
    	                    $sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
    	                    $sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
    	                    $sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
    	                    
    	                    $patient->select($sql);
    	                    $patient->whereIn('ipid', $ipids_arr);
    	                    $patient->limit($limit);
    	                    $patients_ids_arr = $patient->fetchArray();
    	                    
    	                    $ipid_str = '"99999999",';
    	                    foreach ($patients_ids_arr as $ipid)
    	                    {
    	                        $ipid_str .= '"' . $ipid['ipid'] . '",';
    	                    }
    	                    $ipid_str = substr($ipid_str, 0, -1);
    	                    
    	                    
    	                    //get first and last kvno data
    	                    $patient_k_first = Doctrine_Query::create()
    	                    ->select('*')
    	                    ->from('DgpKern ka')
    	                    ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` ASC	LIMIT 1 )')
    	                    ->groupBy('ka.ipid')
    	                    ->orderby('id asc');
    	                    
    	                    $p_kvno_first = $patient_k_first->fetchArray();
    	                    
    	                    $patient_k_last = Doctrine_Query::create()
    	                    ->select('*')
    	                    ->from('DgpKern ka')
    	                    ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` DESC	LIMIT 1 )')
    	                    ->groupBy('ka.ipid')
    	                    ->orderby('id asc');
    	                    
    	                    $p_kvno_last = $patient_k_last->fetchArray();
    	                    
    	                    foreach ($p_kvno_first as $k_first => $v_first)
    	                    {
    	                        $patient_kvno_first[$v_first['ipid']] = $v_first;
    	                    }
    	                    
    	                    foreach ($p_kvno_last as $k_last => $v_last)
    	                    {
    	                        if ($patient_kvno_first[$v_last['ipid']]['id'] != $v_last['id'])
    	                        {
    	                            $patient_kvno_last[$v_last['ipid']] = $v_last;
    	                        }
    	                    }
    	                    
    	                    //get contactperson_master data
    	                    $contact_pm = Doctrine_Query::create()
    	                    ->select('cpm.ipid, SUM(cpm.cnt_hatversorgungsvollmacht) as versorgung_max')
    	                    ->from('ContactPersonMaster cpm')
    	                    ->andwhere('cpm.ipid IN (' . $ipid_str . ')')
    	                    ->groupBy('cpm.ipid')
    	                    ->orderBy('cnt_hatversorgungsvollmacht ASC');
    	                    
    	                    $contact_data = $contact_pm->fetchArray();
    	                    foreach ($contact_data as $k_contact => $v_contact)
    	                    {
    	                        $contact_patient_data[$v_contact['ipid']] = $v_contact;
    	                    }
    	                    
    	                    //get all patients sapvs
    	                    $sapv = new SapvVerordnung();
    	                    $sapvarray = $sapv->getPatientsSapvVerordnungDetails($ipid_arr, true);
    	                    
    	                    $patient_sapv_forms = Doctrine_Query::create()
    	                    ->select('*')
    	                    ->from('DgpSapv ds')
    	                    ->where('ds.ipid in (' . $ipid_str . ')')
    	                    ->orderby('ds.id asc');
    	                    
    	                    $patient_sapv_forms_filled = $patient_sapv_forms ->fetchArray();
    	                    
    	                    foreach($patient_sapv_forms_filled as $k_sapvf=>$v_sapvf)
    	                    {
    	                        $filled_sapvs[$v_sapvf['sapv']] = $v_sapvf;
    	                    }
    	                    
    	                    //get discharge locations
    	                    $dl = new DischargeLocation();
    	                    $discharge_locations = $dl->getDischargeLocation($clientid, 0);
    	                    foreach ($discharge_locations as $k_disloc_client => $v_disloc)
    	                    {
        	                        $discharge_loc [$v_disloc['id']] = $v_disloc;
    	                        
    	                    }
    	                    
    	                    //process patient data
    	                    foreach ($patients_ids_arr as $k_pat => $v_pat)
    	                    {
    	                        $export_data[$v_pat['ipid']]['B_Programm'] = 'ISPC';
    	                        $export_data[$v_pat['ipid']]['B_Pat_ID'] = strtoupper($v_pat['epid']);
    	                        $export_data[$v_pat['ipid']]['B_Dat_ID'] = strtoupper($v_pat['epid']);
    	                        $export_data[$v_pat['ipid']]['B_geb_datum'] = date('Y-m', strtotime($v_pat['birthd']));
    	                        $export_data[$v_pat['ipid']]['B_auf_datum'] = date('Y-m-d', strtotime($v_pat['admission_date']));
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        
    	                        // B_geschlecht
    	                        // -1=Keine Angabe;
    	                        // 1=1 - weiblich;
    	                        // 2=2 - männlich
    	                        if (!empty($v_pat['sex']))
    	                        {
    	                            $sex_map = array ('1' => '2', '2' => '1');
    	                            $gender = $sex_map[$v_pat['sex']];
    	                        }
    	                        else
    	                        {
    	                            $gender = '-1';
    	                        }
    	                    
    	                        $export_data[$v_pat['ipid']]['B_geschlecht'] = $gender;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                        
    	                        // BA_Patientenverfuegung
    	                        // 1 = ausgewählt
    	                        // -1 = nicht ausgewählt
    	                        if (!empty($v_pat['living_will']))
    	                        {
    	                            if ($v_pat['living_will'] == '0')
    	                            {
    	                                $living_will = "-1";
    	                            }
    	                            else if ($v_pat['living_will'] == '1')
    	                            {
    	                                $living_will = $v_pat['living_will'];
    	                            }
    	                    
    	                            if(!empty($patient_kvno_first[$v_pat['ipid']]))
    	                            {
    	                                $export_data[$v_pat['ipid']]['BA_Patientenverfuegung'] = $living_will;
    	                            }
    	                    
    	                            if(!empty($patient_kvno_last[$v_pat['ipid']]))
    	                            {
    	                                $export_data[$v_pat['ipid']]['BL_Patientenverfuegung'] = $living_will;
    	                            }
    	                        }
    	                        else
    	                        {
    	                            if(!empty($patient_kvno_first[$v_pat['ipid']]))
    	                            {
    	                                $export_data[$v_pat['ipid']]['BA_Patientenverfuegung'] = '-1';
    	                            }
    	                    
    	                            if(!empty($patient_kvno_last[$v_pat['ipid']]))
    	                            {
    	                                $export_data[$v_pat['ipid']]['BL_Patientenverfuegung'] = '-1';
    	                            }
    	                        }
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        //versorgung from contact data master
    	                        // BA_Vorsorgevollmacht
    	                        // 1 = ausgewählt
    	                        // -1 = nicht ausgewählt
    	                        if ($contact_patient_data[$v_pat['ipid']]['versorgung_max'] > 0 && !empty($contact_patient_data[$v_pat['ipid']]))
    	                        {
    	                            $versorgung = "1";
    	                        }
    	                        else
    	                        {
    	                            $versorgung = "-1";
    	                        }
    	                    
    	                        if(!empty($patient_kvno_first[$v_pat['ipid']]))
    	                        {
    	                            $export_data[$v_pat['ipid']]['BA_Vorsorgevollmacht'] = $versorgung;
    	                        }
    	                        if(!empty($patient_kvno_last[$v_pat['ipid']]))
    	                        {
    	                            $export_data[$v_pat['ipid']]['BL_Vorsorgevollmacht'] = $versorgung;
    	                        }
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                        
    	                        //SAPV XML
    	                        if ($sapvarray[$v_pat['ipid']])
    	                        {
    	                            foreach ($sapvarray[$v_pat['ipid']] as $k_sapv => $sapv_data)
    	                            {
    	                    
    	                                //if is filled
    	                                if (!empty($filled_sapvs[$sapv_data['id']]) && array_key_exists($sapv_data['id'], $filled_sapvs))
    	                                {
    	                                    //@to do: here find a way to use keine
    	                                    //
    	                                    //minimal data set required to pass validation
    	                                    $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Programm'] = 'ISPC';
    	                                    $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Pat_ID'] = strtoupper($v_pat['epid']);
    	                                    $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Dat_ID'] = $sapv_data['id'];
    	                                    $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_vo_datum'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
    	                                    $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_beginn'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
    	                                    $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($sapv_data['verordnungbis']));
    	                                    $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Keine'] = '-1';
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

    	                                    //get what kind of vv we have
    	                                    $sapv_vv = explode(',', $sapv_data['verordnet']);
    	                                    
    	                                    //SAPV_Beratung
    	                                    // 1 = ausgewählt
    	                                    // -1 = nicht ausgewählt
    	                                    if (in_array('1', $sapv_vv))
    	                                    {
    	                                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Beratung'] = '1';
    	                                    }
    	                                    else
    	                                    {
    	                                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_Beratung'] = '-1';
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    
    	                                    // SAPV_additiv
    	                                    // 1 = ausgewählt
    	                                    // -1 = nicht ausgewähl
    	                                    if (in_array('3', $sapv_vv))
    	                                    {
    	                                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_additiv'] = '1';
    	                                    }
    	                                    else
    	                                    {
    	                                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_additiv'] = '-1';
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    
    	                                    //SAPV_voll
    	                                    // 1 = ausgewählt
    	                                    // -1 = nicht ausgewählt
    	                                    if (in_array('4', $sapv_vv))
    	                                    {
    	                                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_voll'] = '1';
    	                                    }
    	                                    else
    	                                    {
    	                                        $sapv_export_data[$v_pat['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_voll'] = '-1';
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                                    //sapv xml for each saved sapv form
    	                                    //SAPV_verordnung_durch
    	                                    // - 1=Keine Angabe;
    	                                    // 1 = Hausarzt;
    	                                    // 2 = amb. Facharzt;
    	                                    // 3 = Krankenhausarzt;
    	                                    // 4 = andere
    	                                    $verordnung_map = array ('1' => '1', '2' => '2', '3' => '4', '4' => '-1');
    	                                    if (empty($filled_sapvs[$sapv_data['id']]['verordnung_durch']) || $filled_sapvs[$sapv_data['id']]['verordnung_durch'] == '0')
    	                                    {
    	                                        $vv_durch = "-1";
    	                                    }
    	                                    else
    	                                    {
    	                                        $vv_durch = $verordnung_map[$filled_sapvs[$sapv_data['id']]['verordnung_durch']];
    	                                    }
    	                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_verordnung_durch'] = $vv_durch;
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    

    	                                    // SAPV_verordnung_art
    	                                    // -1=Keine Angabe;
    	                                    // 1 = Erst-VO;
    	                                    // 2 = Folge-VO
    	                                    if (!empty($filled_sapvs[$sapv_data['id']]['art_der_erordnung']))
    	                                    {
    	                                        if ($filled_sapvs[$sapv_data['id']]['art_der_erordnung'] == 'Erstverordnung')
    	                                        {
    	                                            $art_erordung = '1';
    	                                        }
    	                                        elseif ($filled_sapvs[$sapv_data['id']]['art_der_erordnung'] == 'Folgeverordnung')
    	                                        {
    	                                            $art_erordung = '2';
    	                                        }
    	                                    }
    	                                    else
    	                                    {
    	                                        $art_erordung = '-1';
    	                                    }
    	                    
    	                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_verordnung_art'] = $art_erordung;
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/    	                    
    	                    
    	                                    

    	                                    // SAPV_uebernahme_aus
    	                                    //-1 = Keine Angabe;
    	                                    // 1 = eigene Haueslichkeit;
    	                                    // 2 = Pflegeheim;
    	                                    // 3 = stationaeres Hospiz;
    	                                    // 4 = Krankenhausstation;
    	                                    // 5 = Palliativstation
    	                                    $ubernahme_aus_mapping = array ('0' => '-1', '1' => '1', '2'=>'-1', '3' => '2', '4' => '3', '5' => '4', '6' => '5');
    	                    
    	                                    if (!empty($filled_sapvs[$sapv_data['id']]['ubernahme_aus']) && $filled_sapvs[$sapv_data['id']]['ubernahme_aus'] != '0')
    	                                    {
    	                                        $ubernahme_aus = $ubernahme_aus_mapping[$filled_sapvs[$sapv_data['id']]['ubernahme_aus']];
    	                                    }
    	                                    else
    	                                    {
    	                                        $ubernahme_aus = '-1';
    	                                    }
    	                    
    	                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_uebernahme_aus'] = $ubernahme_aus;
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    
    	                                    
    	                                    
    	                                    //SAPV_PC_Team
    	                                    // 1 = ausgewählt
    	                                    // -1 = nicht ausgewählt
    	                                    if (!empty($filled_sapvs[$sapv_data['id']]['pcteam']) && $filled_sapvs[$sapv_data['id']]['pcteam'] != '0')
    	                                    {
    	                                        $pcteam = $filled_sapvs[$sapv_data['id']]['pcteam'];
    	                                    }
    	                                    else
    	                                    {
    	                                        $pcteam = '-1';
    	                                    }
    	                    
    	                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_PC_Team'] = $pcteam;
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    
    	                                    
    	                                    
    	                                    //Ärztlich:
    	                                    // SAPV_Hausarzt
    	                                    // 1 = ausgewählt
    	                                    // -1 = nicht ausgewählt
    	                                    $arztlich = explode(',', $filled_sapvs[$sapv_data['id']]['arztlich']);
    	                    
    	                                    if (count($arztlich) > 0 && !empty($arztlich))
    	                                    {
    	                                        $arztlich_array = array ('1' => 'SAPV_Hausarzt', '2' => 'SAPV_andere_aerztlich', '3' => 'SAPV_Krankenhausarzt', '5' => 'SAPV_amb_Facharzt');
    	                    
    	                                        foreach ($arztlich_array as $k_arztlich => $v_arztlich)
    	                                        {
    	                                            if (in_array($k_arztlich, $arztlich))
    	                                            {
    	                                                //set 1 if is in array from db
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_arztlich] = '1';
    	                                            }
    	                                            else
    	                                            {
    	                    
    	                                                //set -1
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_arztlich] = '-1';
    	                                            }
    	                                        }
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    
    	                                    //Pflegerisch
    	                                    // SAPV_amb_Pflege
    	                                    //  1 = ausgewählt
    	                                    // -1 = nicht ausgewählt    	                                    
    	                                    $pflegerisch = explode(',', $filled_sapvs[$sapv_data['id']]['pflegerisch']);
    	                    
    	                                    if (count($pflegerisch) > 0 && !empty($pflegerisch))
    	                                    {
    	                                        $pflegerisch_array = array ('1' => 'SAPV_amb_Pflege', '2' => 'SAPV_amb_Palliativpflege', '3' => 'SAPV_Pflegeheim', '4' => 'SAPV_stat_Hospiz');
    	                    
    	                                        foreach ($pflegerisch_array as $k_pflegerisch => $v_pflegerisch)
    	                                        {
    	                                            if (in_array($k_pflegerisch, $pflegerisch))
    	                                            {
    	                                                //set 1 if is in array from db
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_pflegerisch] = '1';
    	                                            }
    	                                            else
    	                                            {
    	                    
    	                                                //set -1
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_pflegerisch] = '-1';
    	                                            }
    	                                        }
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    
    	                                    
    	                                    //Ambulanter hospizdienst
    	                                    //SAPV_Palliativberatung
    	                                    // 1 = ausgewählt
    	                                    // -1 = nicht ausgewählt
    	                                    $ambulanter = explode(',', $filled_sapvs[$sapv_data['id']]['ambulanter_hospizdienst']);
    	                    
    	                                    if (count($ambulanter) > 0 && !empty($ambulanter))
    	                                    {
    	                                        $ambulanter_array = array ('1' => 'SAPV_Palliativberatung', '2' => 'SAPV_ehrenamtl_Begleitung');
    	                    
    	                                        foreach ($ambulanter_array as $k_ambulanter => $v_ambulanter)
    	                                        {
    	                                            if (in_array($k_ambulanter, $ambulanter))
    	                                            {
    	                                                //set 1 if is in array from db
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_ambulanter] = '1';
    	                                            }
    	                                            else
    	                                            {
    	                    
    	                                                //set -1
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_ambulanter] = '-1';
    	                                            }
    	                                        }
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

    	                                    
    	                                    //SAPV_Case_Management
    	                                    // 1 = ausgewählt
    	                                    // -1 = nicht ausgewählt
    	                                    $weitere_pro = explode(',', $filled_sapvs[$sapv_data['id']]['weitere_professionen']);
    	                    
    	                                    if (count($weitere_pro) > 0 && !empty($weitere_pro))
    	                                    {
    	                                        $weitere_array = array ('1' => 'SAPV_Case_Management', '2' => 'SAPV_Ernaehrungsberatung', '3' => 'SAPV_Physiotherapie', '4' => 'SAPV_Psychotherapie',
    	                                            '5' => 'SAPV_Seelsorge', '6' => 'SAPV_Sozialarbeit', '7' => 'SAPV_andere_professionen');
    	                    
    	                                        foreach ($weitere_array as $k_weitere => $v_weitere)
    	                                        {
    	                                            if (in_array($k_weitere, $weitere_pro))
    	                                            {
    	                                                //set 1 if is in array from db
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_weitere] = '1';
    	                                            }
    	                                            else
    	                                            {
    	                    
    	                                                //set -1
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_weitere] = '-1';
    	                                            }
    	                                        }
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    
    	                                    //weitere Professionen
    	                                    //SAPV_Case_Management
    	                                    // 1 = ausgewählt
    	                                    // -1 = nicht ausgewählt
    	                                    $professions = explode(',', $filled_sapvs[$sapv_data['id']]['weitere_professionen']);
    	                    
    	                                    if (count($professions) > 0 && !empty($professions))
    	                                    {
    	                                        $professions_array = array ('1' => 'SAPV_Case_Management', '2' => 'SAPV_Ernaehrungsberatung', '3' => 'SAPV_Physiotherapie', '4' => 'SAPV_Psychotherapie',
    	                                            '5' => 'SAPV_Seelsorge', '6' => 'SAPV_Sozialarbeit', '7' => 'SAPV_andere_professionen');
    	                    
    	                                        foreach ($professions_array as $k_professions => $v_professions)
    	                                        {
    	                                            if (in_array($k_professions, $professions))
    	                                            {
    	                                                //set 1 if is in array from db
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_professions] = '1';
    	                                            }
    	                                            else
    	                                            {
    	                                                //set -1
    	                                                $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']][$v_professions] = '-1';
    	                                            }
    	                                        }
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    

    	                                    //SAPV_einw_grund
    	                                    // text
    	                                    if (!empty($filled_sapvs[$sapv_data['id']]['grund_einweisung']))
    	                                    {
    	                                        $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_einw_grund'] = $filled_sapvs[$sapv_data['id']]['grund_einweisung'];
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                                    
    	                                    //SAPV_sapvdatum_ende
    	                                    // (jjjj-mm-tt)
    	                                    if (!empty($filled_sapvs[$sapv_data['id']]['end_date_sapv']))
    	                                    {
    	                                        $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($filled_sapvs[$sapv_data['id']]['end_date_sapv']));
    	                                    } 
    	                                    else 
    	                                    {
    	                                        $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($sapv_data['verordnungbis']));
    	                                    }
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                                    //SAPV_ende_spez_amb_behandlung
    	                                    //-1 =Keine Angabe;
    	                                    // 1 = SAPV nicht erforderlich;
    	                                    // 2 = Einweisung in KH;
    	                                    // 3 = verstorben;
    	                                    // 4 = anderes
    	                                    if (empty($filled_sapvs[$sapv_data['id']]['therapieende']) || $filled_sapvs[$sapv_data['id']]['therapieende'] == '0')
    	                                    {
    	                                        $thera_ende = "-1";
    	                                    }
    	                                    else
    	                                    {
    	                                        $thera_ende_mapping = array('0'=>'-1', '1'=>'3', '2'=>'2', '3'=>'1','4'=>'4'); //thera ende has no "3" value in xsd...weird
    	                                        $thera_ende = $thera_ende_mapping[$filled_sapvs[$sapv_data['id']]['therapieende']];
    	                                    }
    	                    
    	                                    $sapv_export_data[$filled_sapvs[$sapv_data['id']]['ipid'] . $filled_sapvs[$sapv_data['id']]['id']]['SAPV_ende_spez_amb_behandlung'] = $thera_ende;
    	                                    /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                                } //end if filled sapvs not empty
    	                                else
    	                                {
    	                                    //minimal data set required to pass validation
    	                                    $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Programm'] = 'ISPC';
    	                                    $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Pat_ID'] = strtoupper($v_pat['epid']);
    	                                    $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Dat_ID'] = $sapv_data['id'];
    	                                    $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_vo_datum'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
    	                                    $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_sapvdatum_beginn'] = date('Y-m-d', strtotime($sapv_data['verordnungam']));
    	                                    $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_sapvdatum_ende'] = date('Y-m-d', strtotime($sapv_data['verordnungbis']));
    	                                    $sapv_export_data[$v_pat['ipid'] . $sapv_data['id']]['SAPV_Keine'] = '1';
    	                                }
    	                            }
    	                        }
    	                    }
    	                    
    	                    
    	                    //		get various nonkvno saved data
    	                    //		get Haupt Diagnosis diagnosis
    	                    $dg = new DiagnosisType();
    	                    $abb2 = "'HD'";
    	                    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
    	                    $comma = ",";
    	                    $typeid = "'0'";
    	                    foreach ($ddarr2 as $key => $valdia)
    	                    {
    	                        $typeid .=$comma . "'" . $valdia['id'] . "'";
    	                        $comma = ",";
    	                    }
    	                    
    	                    $patdia = new PatientDiagnosis();
    	                    $dianoarray = $patdia->getFinalData($ipid_str, $typeid, true); //set last param true to accept a list of ipids
    	                    
    	                    if (count($dianoarray) > 0)
    	                    {
    	                        foreach ($dianoarray as $key => $valdia)
    	                        {
    	                            if (!empty($valdia['diagnosis']) && !empty($valdia['icdnumber']))
    	                            {
    	                                $export_data[$valdia['ipid']]['BA_ICD_haupt'] = $valdia['icdnumber'];
    	                                $export_data[$valdia['ipid']]['BL_ICD_haupt'] = $valdia['icdnumber'];
    	                            }
    	                        }
    	                    }
    	                    
    	                    
    	                    $wohn_mapping = array ('0' => '-1', '1' => '1', '2' => '3', '4' => '2', '6' => '4');
    	                    
    	                    //prepare export first kvno data
    	                    foreach ($patient_kvno_first as $k_pkvno => $v_pkvno)
    	                    {
    	                    
    	                        //BA_wohnsituation
    	                        // -1=Keine Angabe;
    	                        // 1 =1 - allein;
    	                        // 2 =2 - Heim;
    	                        // 3 =3 - mit Angehörigen;
    	                        // 4 =4 - Sonstige
    	                        if (!empty($v_pkvno['wohnsituations']))
    	                        {
    	                            $wohn = $wohn_mapping[$v_pkvno['wohnsituations']];
    	                        }
    	                        else
    	                        {
    	                            $wohn = '-1';
    	                        }
    	                    
    	                        $export_data[$v_pkvno['ipid']]['BA_wohnsituation'] = $wohn;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        // BA_ecog
    	                        // -1=Keine Angabe;
    	                        // 0=0 - Normale Aktivitat;
    	                        // 1=1 - Gehfahig, leichte Aktivitat möglich;
    	                        // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
    	                        // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
    	                        // 4=4 - Pflegebedurftig, permanent bettlagerig
    	                        if (!empty($v_pkvno['ecog']))
    	                        {
    	                            $ecog_value = ($v_pkvno['ecog'] - 1);
    	                        }
    	                        else
    	                        {
    	                            $ecog_value = '-1';
    	                        }
    	                    
    	                        $export_data[$v_pkvno['ipid']]['BA_ecog'] = $ecog_value;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                        // BA_datum
    	                        // Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
    	                        if (!empty($v_pkvno['datum_der_erfassung1']) && $v_pkvno['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno['datum_der_erfassung1'] != '0000-00-00 00:00:00')
    	                        {
    	                            $export_data[$v_pkvno['ipid']]['BA_datum'] = date('Y-m-d', strtotime($v_pkvno['datum_der_erfassung1']));
    	                        }
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

    	                        // =============================================//
    	                        //this part is repeating with BA and BL prefix
    	                        // =============================================//
    	                    
    	                        // BA_Hausarzt
    	                        // 1 = ausgewählt
    	                        // -1 = nicht ausgewählt
    	                        if (!empty($v_pkvno['begleitung']))
    	                        {
    	                            $begleitung = explode(',', $v_pkvno['begleitung']);
    	                        }
    	                    
    	                        $export_data[$v_pkvno['ipid']]['BA_Hausarzt'] = (in_array('2', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Ambulante_Pflege'] = (in_array('3', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Palliativarzt'] = (in_array('4', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Palliativpflege'] = (in_array('5', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Palliativberatung'] = (in_array('6', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_KH_Palliativstation'] = (in_array('8', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Hospiz_stationaer'] = (in_array('9', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Heim'] = (in_array('11', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Palliative_Care_Team'] = (in_array('12', $begleitung)) ? '1' : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_sonstige_behandlung'] = (in_array('13', $begleitung)) ? '1' : '-1';
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        //BA_Schmerzen
    	                        // -1=Keine Angabe;
    	                        // 0=0 - kein;
    	                        // 1=1 - leicht;
    	                        // 2=2 - mittel;
    	                        // 3=3 - stark
    	                        $export_data[$v_pkvno['ipid']]['BA_Schmerzen'] = (!empty($v_pkvno['schmerzen'])) ? ($v_pkvno['schmerzen'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Ubelkeit'] = (!empty($v_pkvno['ubelkeit'])) ? ($v_pkvno['ubelkeit'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Erbrechen'] = (!empty($v_pkvno['erbrechen'])) ? ($v_pkvno['erbrechen'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Luftnot'] = (!empty($v_pkvno['luftnot'])) ? ($v_pkvno['luftnot'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Verstopfung'] = (!empty($v_pkvno['verstopfung'])) ? ($v_pkvno['verstopfung'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Schwache'] = (!empty($v_pkvno['swache'])) ? ($v_pkvno['swache'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Appetitmangel'] = (!empty($v_pkvno['appetitmangel'])) ? ($v_pkvno['appetitmangel'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Mudigkeit'] = (!empty($v_pkvno['mudigkeit'])) ? ($v_pkvno['mudigkeit'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Wunden'] = (!empty($v_pkvno['dekubitus'])) ? ($v_pkvno['dekubitus'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Hilfe_ATL'] = (!empty($v_pkvno['hilfebedarf'])) ? ($v_pkvno['hilfebedarf'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Depressivitat'] = (!empty($v_pkvno['depresiv'])) ? ($v_pkvno['depresiv'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Angst'] = (!empty($v_pkvno['angst'])) ? ($v_pkvno['angst'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Anspannung'] = (!empty($v_pkvno['anspannung'])) ? ($v_pkvno['anspannung'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Verwirrtheit'] = (!empty($v_pkvno['desorientier'])) ? ($v_pkvno['desorientier'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Versorgungsorg'] = (!empty($v_pkvno['versorgung'])) ? ($v_pkvno['versorgung'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_Uberforderung'] = (!empty($v_pkvno['umfelds'])) ? ($v_pkvno['umfelds'] - 1) : '-1';
    	                        $export_data[$v_pkvno['ipid']]['BA_sonstige_probleme'] = (!empty($v_pkvno['sonstige_probleme'])) ? ($v_pkvno['sonstige_probleme'] - 1) : '-1';
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    }
    	                    
    	                    foreach ($patient_kvno_last as $k_pkvno_last => $v_pkvno_last)
    	                    {
    	                    
    	                        //BL_wohnsituation
    	                        // -1=Keine Angabe;
    	                        // 1=1 - allein;
    	                        // 2=2 - Heim;
    	                        // 3=3 - mit Angehörigen;
    	                        // 4=4 - Sonstige
    	                        if (!empty($v_pkvno_last['wohnsituations']))
    	                        {
    	                            $wohn = $wohn_mapping[$v_pkvno_last['wohnsituations']];
    	                        }
    	                        else
    	                        {
    	                            $wohn = '-1';
    	                        }
    	                    
    	                        $export_data[$v_pkvno_last['ipid']]['BL_wohnsituation'] = $wohn;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/

    	                        
    	                        // BL_datum
    	                        // 13. Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
    	                        if (!empty($v_pkvno_last['datum_der_erfassung1']) && $v_pkvno_last['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno_last['datum_der_erfassung1'] != '0000-00-00 00:00:00')
    	                        {
    	                            $export_data[$v_pkvno_last['ipid']]['BL_datum'] = date('Y-m-d', strtotime($v_pkvno_last['datum_der_erfassung1']));
    	                        }
    	                    
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        //BL_ecog
    	                        // -1=Keine Angabe;
    	                        // 0=0 - Normale Aktivitat;
    	                        // 1=1 - Gehfahig, leichte Aktivitat möglich;
    	                        // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
    	                        // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
    	                        // 4=4 - Pflegebedurftig, permanent bettlagerig
    	                        if (!empty($v_pkvno_last['ecog']))
    	                        {
    	                            $ecog_value = ($v_pkvno_last['ecog'] - 1);
    	                        }
    	                        else
    	                        {
    	                            $ecog_value = '-1';
    	                        }
    	                    
    	                        $export_data[$v_pkvno_last['ipid']]['BL_ecog'] = $ecog_value;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        
    	                        //BL_Hausarzt
    	                        // 1 = ausgewählt
    	                        // -1 = nicht ausgewählt
    	                        if (!empty($v_pkvno_last['begleitung']))
    	                        {
    	                            $begleitung_last = explode(',', $v_pkvno_last['begleitung']);
    	                        }
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Hausarzt'] = (in_array('2', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Ambulante_Pflege'] = (in_array('3', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Palliativarzt'] = (in_array('4', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Palliativpflege'] = (in_array('5', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Palliativberatung'] = (in_array('6', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_KH_Palliativstation'] = (in_array('8', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Hospiz_stationaer'] = (in_array('9', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Heim'] = (in_array('11', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Palliative_Care_Team'] = (in_array('12', $begleitung_last)) ? '1' : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_sonstige_behandlung'] = (in_array('13', $begleitung_last)) ? '1' : '-1';
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        // BL_Schmerzen
    	                        // -1=Keine Angabe;
    	                        // 0=0 - kein;
    	                        // 1=1 - leicht;
    	                        // 2=2 - mittel;
    	                        // 3=3 - stark
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Schmerzen'] = (!empty($v_pkvno_last['schmerzen'])) ? ($v_pkvno_last['schmerzen'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Ubelkeit'] = (!empty($v_pkvno_last['ubelkeit'])) ? ($v_pkvno_last['ubelkeit'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Erbrechen'] = (!empty($v_pkvno_last['erbrechen'])) ? ($v_pkvno_last['erbrechen'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Luftnot'] = (!empty($v_pkvno_last['luftnot'])) ? ($v_pkvno_last['luftnot'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Verstopfung'] = (!empty($v_pkvno_last['verstopfung'])) ? ($v_pkvno_last['verstopfung'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Schwache'] = (!empty($v_pkvno_last['swache'])) ? ($v_pkvno_last['swache'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Appetitmangel'] = (!empty($v_pkvno_last['appetitmangel'])) ? ($v_pkvno_last['appetitmangel'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Mudigkeit'] = (!empty($v_pkvno_last['mudigkeit'])) ? ($v_pkvno_last['mudigkeit'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Wunden'] = (!empty($v_pkvno_last['dekubitus'])) ? ($v_pkvno_last['dekubitus'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Hilfe_ATL'] = (!empty($v_pkvno_last['hilfebedarf'])) ? ($v_pkvno_last['hilfebedarf'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Depressivitat'] = (!empty($v_pkvno_last['depresiv'])) ? ($v_pkvno_last['depresiv'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Angst'] = (!empty($v_pkvno_last['angst'])) ? ($v_pkvno_last['angst'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Anspannung'] = (!empty($v_pkvno_last['anspannung'])) ? ($v_pkvno_last['anspannung'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Verwirrtheit'] = (!empty($v_pkvno_last['desorientier'])) ? ($v_pkvno_last['desorientier'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Versorgungsorg'] = (!empty($v_pkvno_last['versorgung'])) ? ($v_pkvno_last['versorgung'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Uberforderung'] = (!empty($v_pkvno_last['umfelds'])) ? ($v_pkvno_last['umfelds'] - 1) : '-1';
    	                        $export_data[$v_pkvno_last['ipid']]['BL_sonstige_probleme'] = (!empty($v_pkvno_last['sonstige_probleme'])) ? ($v_pkvno_last['sonstige_probleme'] - 1) : '-1';
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        // BL_Opioide_WHO_Stufe_3
    	                        // 1 = ausgewählt
    	                        // -1 = nicht ausgewählt
    	                        if ($v_pkvno_last['who'] == '1')
    	                        {
    	                            $opioids = '1';
    	                        }
    	                        else
    	                        {
    	                            $opioids = '-1';
    	                        }
    	                    
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Opioide_WHO_Stufe_3'] = $opioids;
    	                    
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                        // BL_Kortikosteroide
    	                        // 1 = ausgewählt
    	                        // -1 = nicht ausgewählt
    	                        if ($v_pkvno_last['steroide'] == '1')
    	                        {
    	                            $steroide = '1';
    	                        }
    	                        else
    	                        {
    	                            $steroide = '-1';
    	                        }
    	                    
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Kortikosteroide'] = $steroide;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        

    	                        //BL_Chemotherapie
    	                        // -1=Keine Angabe;
    	                        // 0=0 = nein;
    	                        // 1=1 = fortgesetzt;
    	                        // 2=2 = initiiert
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Chemotherapie'] = ($v_pkvno_last['chemotherapie'] - 1);
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        

    	                        // BL_Strahlentherapie
    	                        // -1=Keine Angabe;
    	                        // 0=0 = nein;
    	                        // 1=1 = fortgesetzt;
    	                        // 2=2 = initiiert
    	                        $export_data[$v_pkvno_last['ipid']]['BL_Strahlentherapie'] = ($v_pkvno_last['strahlentherapie'] - 1);
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                        // BL_Aufwand
    	                        // text
    	                        if (!empty($v_pkvno_last['aufwand_mit']))
    	                        {
    	                            $export_data[$v_pkvno_last['ipid']]['BL_Aufwand'] = $v_pkvno_last['aufwand_mit'];
    	                        }
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                        // BL_problem_1
    	                        // text
    	                        if(!empty($v_pkvno_last['problem_besonders']))
    	                        {
    	                            $export_data[$v_pkvno_last['ipid']]['BL_problem_1'] = $v_pkvno_last['problem_besonders'];
    	                        }
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    

    	                        //BL_problem_2
    	                        // text
    	                        if(!empty($v_pkvno_last['problem_ausreichend']))
    	                        {
    	                            $export_data[$v_pkvno_last['ipid']]['BL_problem_2'] = $v_pkvno_last['problem_ausreichend'];
    	                        }
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    

    	                        //B_datum_ende
    	                        // jjjj-mm-tt
    	                        // Entlassung / Anderung der Betreuung / Tod
    	                        if(strlen($v_pkvno_last['entlasung_date'])){
    	                           $export_data[$v_pkvno_last['ipid']]['B_datum_ende'] = date('Y-m-d', strtotime($v_pkvno_last['entlasung_date']));
    	                        } 
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                        
    	                        
    	                        // B_thera_ende
    	                        // -1=Keine Angabe;
    	                        // 1=1 - Verstorben;
    	                        // 2=2 - Verlegung, Entlassung;
    	                        // 4=4 - Sonstiges    	                        
    	                        if ($v_pkvno_last['therapieende'] == '0' || empty($v_pkvno_last['therapieende']))
    	                        {
    	                            $thera_ende = "-1";
    	                        }
    	                        else
    	                        {
    	                            $thera_ende_mapping = array('0'=>'-1', '1'=>'1', '2'=>'2', '3'=>'2','4'=>'4'); //thera ende has no "3" value in xsd...weird
    	                            $thera_ende = $thera_ende_mapping[$v_pkvno_last['therapieende']];
    	                        }
    	                    
    	                        $export_data[$v_pkvno_last['ipid']]['B_thera_ende'] = $thera_ende;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                    
    	                        
    	                        //B_sterbeort_0
    	                        // -1=Keine Angabe;
    	                        // 1=1 - zuhause;
    	                        // 2=2 - Heim;
    	                        // 3=3 - Hospiz;
    	                        // 4=4 - Palliativstation;
    	                        // 5=5 - Krankenhaus;
    	                        // 6=6 - nicht bekannt
    	                        if (empty($v_pkvno_last['sterbeort_dgp']) || $v_pkvno_last['sterbeort_dgp'] == '0')
    	                        {
    	                            $sterbeort = '-1';
    	                        }
    	                        else
    	                        {
    	                            //system location type id=>xml loc type id
    	                            //SYS
    	                            //1 = zu Hause
    	                            //2 = KH
    	                            //3 = Hospiz
    	                            //4 = Altenheim, Pflegeheim
    	                            //5 = Palliativ
    	                            //XML:
    	                            //-1=Keine Angabe;
    	                            //1 zuhause;
    	                            //2 - Heim; (<-- Altenheim / Pflegeheim)
    	                            //3 - Hospiz;
    	                            //4 - Palliativstation;
    	                            //5 - Krankenhaus
    	                            //6 - nicht bekannt
    	                            $location_type_map = array ('1' => '1', '2' => '5', '3' => '3', '4' => '2', '5' => '4');
    	                    
    	                            if ($discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type'] != 0)
    	                            {
    	                                $sterbeort = $location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']];
    	                            }
    	                            else
    	                            {
    	                                //no location type or other(sonstiges...)
    	                                $sterbeort = '6';
    	                            }
    	                        }
    	                    
    	                        $export_data[$v_pkvno_last['ipid']]['B_sterbeort_0'] = $sterbeort;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        

    	                        //   B_bewertung
    	                        //  -1=Keine Angabe;
    	                        //   1=1 - sehr gut;
    	                        //   2=2 - gut;
    	                        //   3=3 - mittel;
    	                        //   4=4 - schlecht;
    	                        //   5=5 - sehr schlecht
    	                        $satisfaction_map = array ('0'=>'-1','1' => '5', '2' => '4', '3' => '3', '4' => '2', '5' => '1');
    	                        
    	                        if (empty($v_pkvno_last['zufriedenheit_mit']))
    	                        {
    	                            $satisfaction = '-1';
    	                        }
    	                        else
    	                        {
    	                            $satisfaction = $satisfaction_map[$v_pkvno_last['zufriedenheit_mit']];
    	                        }
    	                        $export_data[$v_pkvno_last['ipid']]['B_bewertung'] = $satisfaction;
    	                        /*-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    	                        
    	                    } //end last kvno foreach
    	                    
    	                    
    	                    
                            // ######################################################################################################################################
                            // SEND KERNE PART
                            // ######################################################################################################################################
                            
                            //get KERNE xml
                            $kern_schema = '<alle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="KERN.xsd"></alle>';
                            $xml = $this->toXml($export_data, $kern_schema, null, 'KERN');
                            
                            //echo $xml;
                            if (class_exists('DOMDocument'))
                            {
                                $doc = new DOMDocument();
                                $dom_xml = $doc->loadXML($xml);
                                $source_path = APP_BASE . 'xsd/KERN.xsd';
                                $validation = $doc->schemaValidate($source_path);
                            }
                            else
                            {
                                $validation = true; //sskip validation if DOMDocument is not installed
                            }
                            
                            $validation = true;
                            if ($validation)
                            {
                                $xml_type = 'KERN';
                                $xml_string = $this->xmlpp($xml, false);
                                //$xml_string = $xml;
                            
                                //send xml via post
                                $response = $this->data_send($clientid, $xml_string, $xml_type);
                            
                                $history_id = $this->history_save($userid, $clientid, $xml_string, $response);
                            
                                if (substr($response, 0, 4) == '1000') //1000 is ok RC
                                {
                                    // save patients - for this upload
                                    if($history_id ){
                                        $exported_ipids = array();
                                        foreach($export_data as $pipid => $ex_data){
                                            if(!in_array($pipid,$exported_ipids)){
                                                $exported_ipids[] = $pipid ;
                                            }
                                        }
                                        $this->history_patients_save($history_id,$exported_ipids, $userid, $clientid);
                                         
                                    }
                                    // redir success
                                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=suc&c=".substr($response, 0, 4));
                                    $kern_send_status = "suc";
                                    $kern_msg = substr($response, 0, 4);
                                }
                                else if(substr($response, 0, 4) == '2000')
                                {
                                // redir error auth error
                                // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                                    $kern_send_status = "err";
                                    $kern_msg = substr($response, 0, 4);
                                }
                                else if(substr($response, 0, 4) == '0000')
                                {
                                // curl error
                                // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                                    $kern_send_status = "err";
                                    $kern_msg = substr($response, 0, 4);
                                }
                                else if(substr($response, 0, 4) == '3100')
                                {
                                    // XML error
                                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                                    $kern_send_status = "err";
                                    $kern_msg = substr($response, 0, 4);
                                }
                                else
                                {
                                    // redir generic error
                                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err");
                                    $kern_send_status = "err";
                                    $kern_msg = "generic error";
                                }
                            }
                            else
                            {
                                // validation error
                                // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=9999");
                                $this->history_save($userid, $clientid, $xml, 'NULL');
                                $kern_send_status = "error";
                                $kern_msg = "9999";
                            }
    	                    
    	                    
                            // #############
                            // SEND KERNE PART
                            // #############
                            $sapv_schema = '<alle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="SAPV.xsd"></alle>';
                            $xml_sapv = $this->toXml($sapv_export_data, $sapv_schema, null, 'SAPV');
                    
                            //			echo $xml_sapv;
                            if (class_exists('DOMDocument'))
                            {
                               $doc_sapv = new DOMDocument();
                                $dom_xml = $doc_sapv->loadXML($xml_sapv);
                                $source_path = APP_BASE . 'xsd/SAPV.xsd';
                                $validation_sapv = $doc_sapv->schemaValidate($source_path);
                            }
                            else
                            {
                                $validation_sapv = true;
                            }
                    
                            $validation_sapv = true;
                            if ($validation_sapv)
                            {
                                $xml_type = 'SAPV';
                                $xml_string = $this->xmlpp($xml_sapv, false);
                    
                                //  send xml via post
                                $response = $this->data_send($clientid, $xml_string, $xml_type);
                                $sapv_history_id = $this->history_save($userid, $clientid, $xml_string, $response);
                    
                                if (substr($response, 0, 4) == '1000') //1000 is ok RC
                                {
                                    if($sapv_history_id){
                                        foreach($export_data as $pipid => $ex_data){
                                            $exported_ipids[]= $pipid ;
                                        }
                                        $this->history_patients_save($history_id,$exported_ipids, $userid, $clientid);
                                    }
                                    // redir success
                                    //  $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=suc&c=".substr($response, 0, 4));
                                    $sapv_send_status = "suc";
                                    $sapv_msg = substr($response, 0, 4);
                                }
                                else if(substr($response, 0, 4) == '2000')
                                {
                                    // redir error auth error
                                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                                    $sapv_send_status = "err";
                                        $sapv_msg = substr($response, 0, 4);
                                }
                                else if(substr($response, 0, 4) == '0000')
                                {
                                    // curl error
                                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=".substr($response, 0, 4));
                                    $sapv_send_status = "err";
                                    $sapv_msg = substr($response, 0, 4);
                                }
                                else
                                {
                                    //redir generic error
                                    // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err");
                                    $sapv_send_status = "err";
                                    $sapv_msg = "generic error";
                                }
                            }
                            else
                            {
                                //  validation error
                                $this->history_save($userid, $clientid, $xml, 'NULL');
                                // $this->_redirect(APP_BASE . "dgp/dgppatientlist?flg=err&c=9999");
                                $sapv_send_status = "error";
                                $sapv_msg = "9999";
                            }
                            
    	                }// end foreafch
	                }
	            }
	        }
	    }
	    
	    

	    /**
	     * @cla on 10.06.2018 : do not use
	     * @deprecated
	     */
	    private function toXml($data, $rootNodeName = 'data', $xml = null, $elem_root = 'ELEMENT', $xsd_file = false)
	    {
	        // turn off compatibility mode as simple xml throws a wobbly if you don't.
	        if (ini_get('zend.ze1_compatibility_mode') == 1)
	        {
	            ini_set('zend.ze1_compatibility_mode', 0);
	        }
	    
	        if ($xml == null)
	        {
	            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?>$rootNodeName");
	        }
	    
	        // loop through the data passed in.
	        foreach ($data as $key => $value)
	        {
	            // no numeric keys in our xml please!
	            if (is_numeric($key))
	            {
	                // make string key...
	                $key = "unknownNode" . (string)$key;
	            }
	    
	            // replace anything not alpha numeric
	            //find out if key is ipid
	            $ipid_key = explode('_', $key);
	            if (count($ipid_key) == '1')
	            {
	                $key = $elem_root;
	            }
	    
	            // if there is another array found recrusively call this function
	            if (is_array($value))
	            {
	                $node = $xml->addChild($key);
	                // recrusive call.
	                DgpPatientsHistory::toXml($value, $rootNodeName, $node);
	            }
	            else
	            {
	                // add single node.
	                $value = htmlspecialchars($value);
	                $xml->addChild($key, $value);
	            }
	        }
	        // pass back as string. or simple xml object if you want!
	        return $xml->asXML();
	    }
	    
	    
	    /** Prettifies an XML string into a human-readable and indented work of art
	     *  @param string $xml The XML as a string
	     *  @param boolean $html_output True if the output should be escaped (for use in HTML)
	     */
	    /**
	     * @cla on 10.06.2018 : do not use
	     * @deprecated
	     */
	    function xmlpp($xml, $html_output = false)
	    {
	        $xml_obj = new SimpleXMLElement($xml);
	        $level = 4;
	        $indent = 0; // current indentation level
	        $pretty = array ();
	    
	        // get an array containing each XML element
	        $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));
	    
	        // shift off opening XML tag if present
	        if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0]))
	        {
	            $pretty[] = array_shift($xml);
	        }
	    
	        foreach ($xml as $el)
	        {
	            if (preg_match('/^<([\w])+[^>\/]*>$/U', $el))
	            {
	                // opening tag, increase indent
	                $pretty[] = str_repeat(' ', $indent) . $el;
	                $indent += $level;
	            }
	            else
	            {
	                if (preg_match('/^<\/.+>$/', $el))
	                {
	                    $indent -= $level;  // closing tag, decrease indent
	                }
	                if ($indent < 0)
	                {
	                    $indent += $level;
	                }
	                $pretty[] = str_repeat(' ', $indent) . $el;
	            }
	        }
	        $xml = implode("\n", $pretty);
	        return ($html_output) ? htmlspecialchars($xml) : $xml;
	    }
	    
	    /**
	     * @cla on 10.06.2018 : do not use
	     * @deprecated
	     */
	    function data_send($clientid, $xml_string = false, $xml_type = false)
	    {
	    
	        $client_data = new Client();
	        $client_details = $client_data->getClientDataByid($clientid);
	    
	        $params['BNAME'] = $client_details[0]['dgp_user'];
	        $params['KWORT'] = $client_details[0]['dgp_pass'];
	        $params['VERSION'] = '3.1';
	    
	        if ($xml_string && $xml_type)
	        {
	            $params['DATEN'] = $xml_string;
	    
	            if ($xml_type == 'SAPV')
	            {
	                $url = 'https://daten.hospiz-palliativ-register.de/upload/sapv.php';
	            }
	            else if ($xml_type == 'KERN')
	            {
	                $url = 'https://daten.hospiz-palliativ-register.de/upload/kern.php';
	            }
	    
	            $ch = curl_init($url);
	    
	            curl_setopt($ch, CURLOPT_POST, 1);
	            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	            curl_setopt($ch, CURLOPT_VERBOSE, false);
	    
	    
	            $result = curl_exec($ch);
	            $err = curl_error($ch);
	    
	            if (!$result)
	            {
	                return '0000'.$err;
	            }
	            else
	            {
	                return htmlspecialchars($result);
	            }
	            curl_close($ch);
	    
	        }
	    }
	     
	    
	    /**
	     * @cla on 10.06.2018 : do not use
	     * @deprecated
	     */
	    function history_save($user, $client, $xml, $response)
	    {
	        $history = new DgpHistory();
	        $history->user = $user;
	        $history->client = $client;
	        $history->upload_date = date('Y-m-d H:i:s');
	        $history->xml_string = $xml;
	        $history->response_code = substr($response, 0, 4);
	        $history->response_text = $response;
	        $history->save();
	    
	        return $history->id;
	    }
	    
	    /**
	     * @cla on 10.06.2018 : do not use
	     * @deprecated
	     */
	    function history_patients_save($history_id, $ipids,$user, $client)
	    {
	        
	        if(!empty($ipids)){
	            $ipids = array_unique($ipids);
	            foreach($ipids as  $ipid){
	                $records_array[] = array(
	                    'history_id' => $history_id,
	                    'ipid' => $ipid,
	                    'upload_date' => date('Y-m-d H:i:s'),
	                    'user' => $user,
	                    'client' => $client
	                );
	            }
	        }
	    
	        if(count($records_array) > 0)
	        {
	            //insert many records with one query!!
	            $collection = new Doctrine_Collection('DgpPatientsHistory');
	            $collection->fromArray($records_array);
	            $collection->save();
	        }
	         
	    }    
	    

    /**
     * @cla on 10.06.2018 : do not use
     * @deprecated
     */
    public function errormail($exception)
    {
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$mailmessage .= "Page name :" . $_SERVER['REQUEST_URI'];
    	$mailmessage .= "<br /><div> Browser: " . $_SERVER['HTTP_USER_AGENT'] . "</div><br />";
    	$mailmessage .= "<div>" . $exception . " </div>";
    	$mailmessage .= "<div> Date: " . date("d.m.Y H:i:s", time()) . "</div><br />";
    	$mailmessage .= "<div> Username: " . $logininfo->username . "</div><br />";
    	$mailmessage .= "<div> IP-Address: " . $_SERVER['REMOTE_ADDR'] . "</div><br />";
    
    	$mail = new Zend_Mail();
    	$mail->setBodyHtml($mailmessage)->setFrom(ISPC_SENDER, ISPC_SENDERNAME)->addTo(ISPC_ERRORMAILTO, ISPC_ERRORSENDERNAME)->setSubject('ISPC Error - ' . $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REQUEST_URI'] . ' (' . date("d.m.Y H:i:s") . ')')->send();
    }

    
    
    
    

    public function create_DgpKernArray_from_completed_forms ($kern_forms = array(), $clientid = 0)
    {
        if (empty($kern_forms)) {
            return; // fail-safe
        }
    
        if ( ! Zend_Registry::isRegistered("hospizregister") || ($hospizregister_cfg = Zend_Registry::get("hospizregister")) == false) {
            throw new Exception("missing bootstrap _initHospizregister", 0);
        }
    
        $result_export_data = array(); // result export data.. what? :)
    
        $export_data = array(); // this are the same for multiple forms of the same patient
    
        $ipids_arr = array_unique(array_column($kern_forms, 'ipid'));
    
        $patients_ids_arr = Doctrine_Query::create()->select("ipid,
            e.epid as epid,
            p.birthd,
            admission_date,
            change_date,
            last_update,
            CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,
            CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,
            CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,
            CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,
            CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,
            CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,
            CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,
            CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,
            CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,
            CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,
            CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,
            CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex")
                ->from('PatientMaster p indexBy p.ipid')
                ->leftJoin("p.EpidIpidMapping e")
                ->addSelect("pd.discharge_method, pd.discharge_date, pd.isdelete")
                ->leftJoin("p.PatientDischarge pd ON (p.ipid=pd.ipid AND pd.isdelete = 0)")
                ->addSelect("pr.id, pr.date, pr.date_type")
                ->leftJoin("p.PatientReadmission pr ON (p.ipid=pr.ipid )")
                ->whereIn('p.ipid', $ipids_arr)
                ->andWhere('e.clientid = ? ', $clientid)
                ->andWhere("p.isdelete = 0")
                ->andWhere("p.isstandbydelete=0")
                ->fetchArray();
    
    
        if (empty($patients_ids_arr)) {
            return; // fail-safe
        }
    
    
         
    
    
        //get the dates of admision discharge
        $patient_readmission_ids = array();
        foreach ($kern_forms as $form) {
            array_push($patient_readmission_ids, $form['patient_readmission_ID'], $form['TwinDgpKern']['patient_readmission_ID']);
    
        }
        
        
        // ISPC-2496
        
        // This texts were missing
        $dgp_kern_model = new DgpKern();
        $dgp['texts'] = $dgp_kern_model->get_form_texts();
         
        // Get all treatment days of patients 
        $patients_ipids = array_keys($patients_ids_arr);
        $conditions = array(
            'client' => $clientid,
            'ipids' => $patients_ipids,
            'periods' => array(
                array(
                    'start' => date('2008-01-01'),
                    'end' => date('Y-m-d')
                )
            )
        );
        $patient_days = Pms_CommonData::patients_days($conditions);
        // --
    
    
    
    
        // get discharge locations
        $discharge_loc = array();
    
        $dl = new DischargeLocation();
        $discharge_locations = $dl->getDischargeLocation($clientid, 0);
    
        foreach ($discharge_locations as $k_disloc_client => $v_disloc) {
    
            $discharge_loc[$v_disloc['id']] = $v_disloc;
        }
        
        
        
        $dm_obj = new DischargeMethod();
        $discharge_methods = $dm_obj-> get_client_discharge_method($clientid, true);
        
        // process patient data
        $final_patient_data = array();
        
        foreach ($patients_ids_arr as &$patient) {
        
            //re-index  PatientReadmission.. for easier usage later
            $patient_readmission_indexById = array();
            foreach ($patient['PatientReadmission'] as $patient_readmission) {
                $patient_readmission_indexById[$patient_readmission['id']] =  $patient_readmission;
            }
        
            $patient['PatientReadmission'] = $patient_readmission_indexById;
        
            //calculate and add `age`
            if (empty($patient['birthd']) || $patient['birthd'] == "0000-00-00") {
        
                $patient ['age'] = 0 ; //we don't ask a Lady this questions, or this will be born tomorrow
        
                continue;
            }
        
            $birth_date_patient = new Zend_Date($patient['birthd'], Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
        
            $tod_date_patient = new Zend_Date();
        
            if ( ! empty($patient['PatientDischarge'])) {
        
                $death_date = array_filter($patient['PatientDischarge'], function($val) use ($discharge_methods) {return in_array($val['discharge_method'], $discharge_methods);});
        
                if ( ! empty($death_date[0])) {
        
                    $death_date[0]['discharge_date'];
        
                    $tod_date_patient = new Zend_Date($death_date[0]['discharge_date'], Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY . " " .Zend_Date::HOUR.":".Zend_Date::MINUTE .  ":" . Zend_Date::SECOND );
                }
        
        
            }
        
            $patient ['age'] = $this->_age($birth_date_patient, $tod_date_patient);

            //ISPC-2496 Ancuta::   calculate  treatment days 
            $patient ['overall_treatment_days'] = $patient_days[$patient['ipid']]['treatment_days'];
        
        }
        
        $patientmaster_obj = new PatientMaster();
        
        foreach ($patients_ids_arr as $k_pat => $v_pat) {
        
            //THIS IS NOT so important, but good to distinguish among the servers/clients
            $export_data[$v_pat['ipid']]['B_Programm'] = $hospizregister_cfg['KERN']['programm'];
        
            //THIS IS IMPORTANT
            $export_data[$v_pat['ipid']]['B_Pat_ID'] = strtoupper($v_pat['epid']);
        
        
            //THIS IS IMPORTANT.. will be defined in the form
            //$export_data[$v_pat['ipid']]['B_Dat_ID'] = strtoupper($v_pat['epid']);
        
        
            // ISPC-2496 Ancuta Commented B_geb_datum - 03.12.2019
            //$export_data[$v_pat['ipid']]['B_geb_datum'] = date('Y-m', strtotime($v_pat['birthd']));
            //-- 
            $export_data[$v_pat['ipid']]['B_XML_date'] = date('Y-m-d', time());
            $export_data[$v_pat['ipid']]['B_Alter'] = $v_pat['age'];
        
        
            //THIS IS IMPORTANT.. will be defined in the form
            //$export_data[$v_pat['ipid']]['B_auf_datum'] = date('Y-m-d', strtotime($v_pat['admission_date']));
        
            // B_geschlecht
            // -1 = Keine Angabe;
            // 1 = 1 - weiblich;
            // 2 = 2 - männlich
            if (! empty($v_pat['sex'])) {
                $sex_map = array(
                    '1' => '2',
                    '2' => '1'
                );
                $gender = $sex_map[$v_pat['sex']];
            } else {
                $gender = '-1';
            }
            $export_data[$v_pat['ipid']]['B_geschlecht'] = $gender;
        }
        
        
        // get various nonkvno saved data
        // get Haupt Diagnosis diagnosis
        $abb2 = "'HD'";
        $dg = new DiagnosisType();
        $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
        
        $comma = ",";
        $typeid = "'0'";
        
        foreach ($ddarr2 as $key => $valdia) {
            $typeid .= $comma . "'" . $valdia['id'] . "'";
            $comma = ",";
        }
        
        $dianoarray = array();
        $patdia = new PatientDiagnosis();
        
        
        $ipid_str = "'" . implode('\',\'', $ipids_arr) . "'";//bot bother to fix... leave
        
        $dianoarray = $patdia->getFinalData($ipid_str, $typeid, true); // set last param true to accept a list of ipids
        
        foreach ($dianoarray as $key => $valdia) {
            if (! empty($valdia['diagnosis']) && ! empty($valdia['icdnumber'])) {
        
                $export_data[$valdia['ipid']]['BA_ICD_haupt'] = $valdia['icdnumber'];
                $export_data[$valdia['ipid']]['BL_ICD_haupt'] = $valdia['icdnumber'];
        
            }
        }
        
        $wohn_mapping = array(
            '0' => '-1',
            '1' => '1',
            '2' => '3',
            '4' => '2',
            '6' => '4',
            '5' => '5'
        );
        
        foreach ($kern_forms as $v_pkvno) {
        
            //ISPC-2496 Ancuta
            // Moved at the top 03.12.2019 - so we can calculate B_Versorgungstage
            $v_pkvno_last = $v_pkvno['TwinDgpKern'];
            
            //we add the datas that we fetched ...
            //this datas are NOT by FALLS.. so expect trouble in the graphs .. or chage them all to be from FALLS
            $export_data_this_form = $export_data[$v_pkvno['ipid']];
        
            /*
             * Important !
             * i have chosen this as EPID_admisionID_dischardeID
             * admisionID and dischardeID are id from DgpKern
            */
            $export_data_this_form['B_Dat_ID'] =  "{$export_data_this_form['B_Pat_ID']}_{$v_pkvno['id']}_{$v_pkvno['TwinDgpKern']['id']}" ;
        
            /*
             * Important
             * this is the Fall start =  B_auf_datum
             * and the Fall end = B_datum_ende is down the road
             */
            //ISPC-2496 Ancuta 03.12.2019  Commented as is no longer needed 
            //$export_data_this_form['B_auf_datum'] =   date('Y-m-d', strtotime($patients_ids_arr[$v_pkvno['ipid']]['PatientReadmission'][$v_pkvno['patient_readmission_ID']] ['date'])) ;
            //--
        
        
            //$export_data[$v_pat['ipid']]['B_auf_datum'] = date('Y-m-d', strtotime($v_pat['admission_date']));
        
            //ISPC-2496 Ancuta - New value neede: B_Versorgungstage 
            //B_Versorgungstage	length in days for the fall which is now transferred
            
            $start ="";
            $start = date('Y-m-d', strtotime($patients_ids_arr[$v_pkvno['ipid']]['PatientReadmission'][$v_pkvno['patient_readmission_ID']] ['date'])) ;

            $end = "";
            if ( ! empty($v_pkvno_last['patient_readmission_ID'])
                && ! empty($patients_ids_arr[$v_pkvno_last['ipid']]['PatientReadmission'][$v_pkvno_last['patient_readmission_ID']] ['date']))
            {
                $end = date('Y-m-d', strtotime($patients_ids_arr[$v_pkvno_last['ipid']]['PatientReadmission'][$v_pkvno_last['patient_readmission_ID']] ['date']));
            }

            $current_period_days = array();
            $current_period_days = $patientmaster_obj->getDaysInBetween($start,  $end , false, 'd.m.Y');
            
            $current_period_treatment_days = array(); 
            $current_period_treatment_days = array_intersect($patients_ids_arr[$v_pkvno['ipid']]['overall_treatment_days'], $current_period_days);
            
            $export_data_this_form['B_Versorgungstage'] = count($current_period_treatment_days);
            
            
            //ISPC-2496 Ancuta - New value neede: B_Jahrderversorgung
            $export_data_this_form['B_Jahrderversorgung'] = date('Y', strtotime($patients_ids_arr[$v_pkvno['ipid']]['PatientReadmission'][$v_pkvno['patient_readmission_ID']] ['date'])) ;;
            // ---- 
            
            
            /**
             * the admission part of this form
            */
        
            // BA_wohnsituation
            // -1=Keine Angabe;
            // 1 =1 - allein;
            // 2 =2 - Heim;
            // 3 =3 - mit Angehörigen;
            // 4 =4 - Sonstige
        
            // BA_wohnsituation
            // 1 Allein zu Hause lebend
            // 2 Im (Pflege-/Alten-)Heim
            // 3 mit Angehörigen oder anderen privaten Bezugspersonen im Haushalt
            // 4 Sonstige
            // 5 stationäres Hospiz
            // -1 not applicable
        
            if (! empty($v_pkvno['wohnsituations'])) {
                $wohn = $wohn_mapping[$v_pkvno['wohnsituations']];
            } else {
                $wohn = '-1';
            }
        
            $export_data_this_form['BA_wohnsituation'] = $wohn;
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BA_ecog
            // -1=Keine Angabe;
            // 0=0 - Normale Aktivitat;
            // 1=1 - Gehfahig, leichte Aktivitat möglich;
            // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
            // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
            // 4=4 - Pflegebedurftig, permanent bettlagerig
            if (! empty($v_pkvno['ecog'])) {
                $ecog_value = ($v_pkvno['ecog'] - 1);
            } else {
                $ecog_value = '-1';
            }
        
            $export_data_this_form['BA_ecog'] = $ecog_value;
        
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
            // BA_Pverfuegung
            if ($v_pkvno['pverfuegung'] == "1") {
                $export_data_this_form['BA_Patientenverfuegung'] = '1';
            } else {
                $export_data_this_form['BA_Patientenverfuegung'] = '-1';
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BA_vollmacht
            if ($v_pkvno['vollmacht'] == "1") {
                $export_data_this_form['BA_Vorsorgevollmacht'] = '1';
            } else {
                $export_data_this_form['BA_Vorsorgevollmacht'] = '-1';
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
            // BA_Betreuungsurkunde
            if ($v_pkvno['betreuungsurkunde'] == "1") {
                $export_data_this_form['BA_Betreuungsurkunde'] = '1';
            } else {
                $export_data_this_form['BA_Betreuungsurkunde'] = '-1';
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
            // BA_ACP
        
            if ($v_pkvno['acp'] == "1") {
                $export_data_this_form['BA_ACP'] = '1';
            } else {
                $export_data_this_form['BA_ACP'] = '-1';
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BA_datum
            // Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
            if (! empty($v_pkvno['datum_der_erfassung1']) && $v_pkvno['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno['datum_der_erfassung1'] != '0000-00-00 00:00:00') {
                // $export_data_this_form['BA_datum'] = date('Y-m-d', strtotime($v_pkvno['datum_der_erfassung1']));
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // =============================================== //
            // this part is repeating with BA and BL prefix
            // =============================================== //
        
            // BA_Hausarzt
            // 1 = ausgewählt
            // -1 = nicht ausgewählt
            if (! empty($v_pkvno['begleitung'])) {
                $begleitung = explode(',', $v_pkvno['begleitung']);
            }
        
            $export_data_this_form['BA_KH_Palliativstation'] = (in_array('8', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Hospiz_stationaer'] = (in_array('9', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Heim'] = (in_array('11', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Hausarzt'] = (in_array('2', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Ambulante_Pflege'] = (in_array('3', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Palliativarzt'] = (in_array('4', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Palliativpflege'] = (in_array('5', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Palliativberatung'] = (in_array('14', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_Palliative_Care_Team'] = (in_array('12', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_MVZ'] = (in_array('15', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_KH_Palliativdienst'] = (in_array('16', $begleitung)) ? '1' : '-1';
            $export_data_this_form['BA_sonstige_behandlung'] = (in_array('17', $begleitung)) ? '1' : '-1';
        
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BA_Schmerzen
            // -1=Keine Angabe;
            // 0=0 - kein;
            // 1=1 - leicht;
            // 2=2 - mittel;
            // 3=3 - stark
            $export_data_this_form['BA_Schmerzen'] = (! empty($v_pkvno['schmerzen'])) ? ($v_pkvno['schmerzen'] - 1) : '-1';
            $export_data_this_form['BA_Ubelkeit'] = (! empty($v_pkvno['ubelkeit'])) ? ($v_pkvno['ubelkeit'] - 1) : '-1';
            $export_data_this_form['BA_Erbrechen'] = (! empty($v_pkvno['erbrechen'])) ? ($v_pkvno['erbrechen'] - 1) : '-1';
            $export_data_this_form['BA_Luftnot'] = (! empty($v_pkvno['luftnot'])) ? ($v_pkvno['luftnot'] - 1) : '-1';
            $export_data_this_form['BA_Verstopfung'] = (! empty($v_pkvno['verstopfung'])) ? ($v_pkvno['verstopfung'] - 1) : '-1';
            $export_data_this_form['BA_Schwache'] = (! empty($v_pkvno['swache'])) ? ($v_pkvno['swache'] - 1) : '-1';
            $export_data_this_form['BA_Appetitmangel'] = (! empty($v_pkvno['appetitmangel'])) ? ($v_pkvno['appetitmangel'] - 1) : '-1';
            $export_data_this_form['BA_Mudigkeit'] = (! empty($v_pkvno['mudigkeit'])) ? ($v_pkvno['mudigkeit'] - 1) : '-1';
            $export_data_this_form['BA_Wunden'] = (! empty($v_pkvno['dekubitus'])) ? ($v_pkvno['dekubitus'] - 1) : '-1';
            $export_data_this_form['BA_Hilfe_ATL'] = (! empty($v_pkvno['hilfebedarf'])) ? ($v_pkvno['hilfebedarf'] - 1) : '-1';
            $export_data_this_form['BA_Depressivitat'] = (! empty($v_pkvno['depresiv'])) ? ($v_pkvno['depresiv'] - 1) : '-1';
            $export_data_this_form['BA_Angst'] = (! empty($v_pkvno['angst'])) ? ($v_pkvno['angst'] - 1) : '-1';
            $export_data_this_form['BA_Anspannung'] = (! empty($v_pkvno['anspannung'])) ? ($v_pkvno['anspannung'] - 1) : '-1';
            $export_data_this_form['BA_Verwirrtheit'] = (! empty($v_pkvno['desorientier'])) ? ($v_pkvno['desorientier'] - 1) : '-1';
            $export_data_this_form['BA_Versorgungsorg'] = (! empty($v_pkvno['versorgung'])) ? ($v_pkvno['versorgung'] - 1) : '-1';
            $export_data_this_form['BA_Uberforderung'] = (! empty($v_pkvno['umfelds'])) ? ($v_pkvno['umfelds'] - 1) : '-1';
            $export_data_this_form['BA_sonstige_probleme'] = (! empty($v_pkvno['sonstige_probleme'])) ? ($v_pkvno['sonstige_probleme'] - 1) : '-1';
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            $export_data_this_form['BA_Unruhe'] = ( ! empty($v_pkvno['unruhe'])) ? ($v_pkvno['unruhe'] - 1) : '-1';
        
        
        
            /**
             * the discharge part of this form
             */
        
            // ISPC-2496 Ancuta - moved at the start 
            //$v_pkvno_last = $v_pkvno['TwinDgpKern'];
        
            // BL_wohnsituation
            // -1=Keine Angabe;
            // 1=1 - allein;
            // 2=2 - Heim;
            // 3=3 - mit Angehörigen;
            // 4=4 - Sonstige
        
            // -1 = Keine Angabe;
            // 1 = Allein zu Hause lebend ;
            // 2 = Im (Pflege-/Senioren-)Heim;
            // 3 = mit Angehörigen oder anderen privaten Bezugspersonen im Haushalt;
            // 4 = Sonstige;
            // 5 = stationäres Hospiz
            if (! empty($v_pkvno_last['wohnsituations'])) {
                $wohn = $wohn_mapping[$v_pkvno_last['wohnsituations']];
            } else {
                $wohn = '-1';
            }
            $export_data_this_form['BL_wohnsituation'] = $wohn;
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_datum
            // 13. Datum der Erfassung und Dokumentation|Datumsfeld (jjjj-mm-tt)
            if (! empty($v_pkvno_last['datum_der_erfassung1']) && $v_pkvno_last['datum_der_erfassung1'] != '1970-01-01 00:00:00' && $v_pkvno_last['datum_der_erfassung1'] != '0000-00-00 00:00:00') {
                // $export_data_this_form['BL_datum'] = date('Y-m-d', strtotime($v_pkvno_last['datum_der_erfassung1']));
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_ecog
            // -1=Keine Angabe;
            // 0=0 - Normale Aktivitat;
            // 1=1 - Gehfahig, leichte Aktivitat möglich;
            // 2=2 - Nicht arbeitsfahig, kann > 50% der Wachzeit aufstehen;
            // 3=3 - Begrenzte Selbstversorgung, >50% Wachzeit bettlagerig;
            // 4=4 - Pflegebedurftig, permanent bettlagerig
            if (! empty($v_pkvno_last['ecog'])) {
                $ecog_value = ($v_pkvno_last['ecog'] - 1);
            } else {
                $ecog_value = '-1';
            }
        
            $export_data_this_form['BL_ecog'] = $ecog_value;
        
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
            // BL_Pverfuegung
            if ($v_pkvno_last['pverfuegung'] == "1") {
                $export_data_this_form['BL_Patientenverfuegung'] = '1';
            } else {
                $export_data_this_form['BL_Patientenverfuegung'] = '-1';
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_vollmacht
            if ($v_pkvno_last['vollmacht'] == "1") {
                $export_data_this_form['BL_Vorsorgevollmacht'] = '1';
            } else {
                $export_data_this_form['BL_Vorsorgevollmacht'] = '-1';
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
            // BL_Betreuungsurkunde
            if ($v_pkvno_last['betreuungsurkunde'] == "1") {
                $export_data_this_form['BL_Betreuungsurkunde'] = '1';
            } else {
                $export_data_this_form['BL_Betreuungsurkunde'] = '-1';
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
            // BL_ACP
            if ($v_pkvno_last['acp'] == "1") {
                $export_data_this_form['BL_ACP'] = '1';
            } else {
                $export_data_this_form['BL_ACP'] = '-1';
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_Hausarzt
            // 1 = ausgewählt
            // -1 = nicht ausgewählt
            if (! empty($v_pkvno_last['begleitung'])) {
                $begleitung_last = explode(',', $v_pkvno_last['begleitung']);
            }
            $export_data_this_form['BL_KH_Palliativstation'] = (in_array('8', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Hospiz_stationaer'] = (in_array('9', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Krankenhaus_Andere_Station'] = (in_array('10', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Heim'] = (in_array('11', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Hausarzt'] = (in_array('2', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Ambulante_Pflege'] = (in_array('3', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Palliativarzt'] = (in_array('4', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Palliativpflege'] = (in_array('5', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Palliativberatung'] = (in_array('14', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Ehrenamtlicher_Dienst'] = (in_array('7', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_Palliative_Care_Team'] = (in_array('12', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_MVZ'] = (in_array('15', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_KH_Palliativdienst'] = (in_array('16', $begleitung_last)) ? '1' : '-1';
            $export_data_this_form['BL_sonstige_behandlung'] = (in_array('17', $begleitung_last)) ? '1' : '-1';
        
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_Schmerzen
            // -1=Keine Angabe;
            // 0=0 - kein;
            // 1=1 - leicht;
            // 2=2 - mittel;
            // 3=3 - stark
            $export_data_this_form['BL_Schmerzen'] = (! empty($v_pkvno_last['schmerzen'])) ? ($v_pkvno_last['schmerzen'] - 1) : '-1';
            $export_data_this_form['BL_Ubelkeit'] = (! empty($v_pkvno_last['ubelkeit'])) ? ($v_pkvno_last['ubelkeit'] - 1) : '-1';
            $export_data_this_form['BL_Erbrechen'] = (! empty($v_pkvno_last['erbrechen'])) ? ($v_pkvno_last['erbrechen'] - 1) : '-1';
            $export_data_this_form['BL_Luftnot'] = (! empty($v_pkvno_last['luftnot'])) ? ($v_pkvno_last['luftnot'] - 1) : '-1';
            $export_data_this_form['BL_Verstopfung'] = (! empty($v_pkvno_last['verstopfung'])) ? ($v_pkvno_last['verstopfung'] - 1) : '-1';
            $export_data_this_form['BL_Schwache'] = (! empty($v_pkvno_last['swache'])) ? ($v_pkvno_last['swache'] - 1) : '-1';
            $export_data_this_form['BL_Appetitmangel'] = (! empty($v_pkvno_last['appetitmangel'])) ? ($v_pkvno_last['appetitmangel'] - 1) : '-1';
            $export_data_this_form['BL_Mudigkeit'] = (! empty($v_pkvno_last['mudigkeit'])) ? ($v_pkvno_last['mudigkeit'] - 1) : '-1';
            $export_data_this_form['BL_Wunden'] = (! empty($v_pkvno_last['dekubitus'])) ? ($v_pkvno_last['dekubitus'] - 1) : '-1';
            $export_data_this_form['BL_Hilfe_ATL'] = (! empty($v_pkvno_last['hilfebedarf'])) ? ($v_pkvno_last['hilfebedarf'] - 1) : '-1';
            $export_data_this_form['BL_Depressivitat'] = (! empty($v_pkvno_last['depresiv'])) ? ($v_pkvno_last['depresiv'] - 1) : '-1';
            $export_data_this_form['BL_Angst'] = (! empty($v_pkvno_last['angst'])) ? ($v_pkvno_last['angst'] - 1) : '-1';
            $export_data_this_form['BL_Anspannung'] = (! empty($v_pkvno_last['anspannung'])) ? ($v_pkvno_last['anspannung'] - 1) : '-1';
            $export_data_this_form['BL_Verwirrtheit'] = (! empty($v_pkvno_last['desorientier'])) ? ($v_pkvno_last['desorientier'] - 1) : '-1';
            $export_data_this_form['BL_Versorgungsorg'] = (! empty($v_pkvno_last['versorgung'])) ? ($v_pkvno_last['versorgung'] - 1) : '-1';
            $export_data_this_form['BL_Uberforderung'] = (! empty($v_pkvno_last['umfelds'])) ? ($v_pkvno_last['umfelds'] - 1) : '-1';
            $export_data_this_form['BL_sonstige_probleme'] = (! empty($v_pkvno_last['sonstige_probleme'])) ? ($v_pkvno_last['sonstige_probleme'] - 1) : '-1';
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            $export_data_this_form['BL_Unruhe'] = ( ! empty($v_pkvno_last['unruhe'])) ? ($v_pkvno_last['unruhe'] - 1) : '-1';
        
            // BL_Opioide_WHO_Stufe_3
            // 1 = ausgewählt
            // -1 = nicht ausgewählt
            if ($v_pkvno_last['who'] == '1') {
                $opioids = '1';
            } else {
                $opioids = '-1';
            }
        
            // $export_data_this_form['BL_Opioide_WHO_Stufe_3'] = $opioids;
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_Kortikosteroide
            // 1 = ausgewählt
            // -1 = nicht ausgewählt
            if ($v_pkvno_last['steroide'] == '1') {
                $steroide = '1';
            } else {
                $steroide = '-1';
            }
        
            // $export_data_this_form['BL_Kortikosteroide'] = $steroide;
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_Chemotherapie
            // -1=Keine Angabe;
            // 0=0 = nein;
            // 1=1 = fortgesetzt;
            // 2=2 = initiiert
            $export_data_this_form['BL_Chemotherapie'] = ($v_pkvno_last['chemotherapie'] - 1);
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_Strahlentherapie
            // -1=Keine Angabe;
            // 0=0 = nein;
            // 1=1 = fortgesetzt;
            // 2=2 = initiiert
            $export_data_this_form['BL_Strahlentherapie'] = ($v_pkvno_last['strahlentherapie'] - 1);
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_Aufwand
            // text
            //ISPC-2496 Ancuta 03.12.2019  Commented as is no longer needed
            /*
            if (! empty($v_pkvno_last['aufwand_mit'])) {
                $export_data_this_form['BL_Aufwand'] = $v_pkvno_last['aufwand_mit'];
            }
            */
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_problem_1
            // text
            //ISPC-2496 Ancuta 03.12.2019  Commented as is no longer needed
            /* 
            if (! empty($v_pkvno_last['problem_besonders'])) {
                $export_data_this_form['BL_problem_1'] = $v_pkvno_last['problem_besonders'];
            }
             */
            //-- 
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_problem_2
            // text
            if (! empty($v_pkvno_last['problem_ausreichend'])) {
                $export_data_this_form['BL_problem_2'] = $v_pkvno_last['problem_ausreichend'];
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_bedarf
            // text
            if (! empty($v_pkvno_last['bedarf'])) {
                $export_data_this_form['BL_Bedarf'] = $v_pkvno_last['bedarf'];
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // BL_massnahmen
            // text
            if (! empty($v_pkvno_last['massnahmen'])) {
                $export_data_this_form['BL_Massnahmen'] = $v_pkvno_last['massnahmen'];
            }
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            $med_referance_value = array();
            foreach ($dgp['texts']['medication_references_all'] as $k => $mr_data) {
        
                if($mr_data['code'] && !empty($mr_data['dgp_export'])){
                    
                    if ($v_pkvno_last[$mr_data['code']] == '1') {
                        $med_referance_value[$mr_data['code']] = '1';
                    } else {
                        $med_referance_value[$mr_data['code']] = '-1';
                    }
                    $export_data_this_form[$mr_data['dgp_export']] = $med_referance_value[$mr_data['code']];
                }
            }
        
            // $export_data_this_form['BL_Analgetika'] = (in_array('1', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Opioide_WHO_Stufe_2'] = (in_array('2', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Opioide_WHO_Stufe_3'] = (in_array('3', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Nicht_Opioide'] = (in_array('4', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Co_Analgetika'] = (in_array('5', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Steroide'] = (in_array('6', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Anxiolytika'] = (in_array('7', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Laxantien'] = (in_array('8', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Sedativa'] = (in_array('9', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Neuroleptika'] = (in_array('10', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Anti_Epileptika'] = (in_array('11', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Anti_Emetika'] = (in_array('12', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Anti_Biotika'] = (in_array('13', $medication_references)) ? '1' : '-1';
            // $export_data_this_form['BL_Magenschutz'] = (in_array('14', $medication_references)) ? '1' : '-1';
        
            // B_datum_ende
            // jjjj-mm-tt
            // Entlassung / Anderung der Betreuung / Tod\
            /*
            * this was before falls
            if (strlen($v_pkvno_last['entlasung_date'])) {
            $export_data_this_form['B_datum_ende'] = date('Y-m-d', strtotime($v_pkvno_last['entlasung_date']));
            }
            */
        
            /*
            * Important
            * this is the Fall end
            */
        
            
            //ISPC-2496 Ancuta 03.12.2019  Commented as is no longer needed
            /* 
            if ( ! empty($v_pkvno_last['patient_readmission_ID'])
                && ! empty($patients_ids_arr[$v_pkvno_last['ipid']]['PatientReadmission'][$v_pkvno_last['patient_readmission_ID']] ['date'])) 
            {
                $export_data_this_form['B_datum_ende'] = date('Y-m-d', strtotime($patients_ids_arr[$v_pkvno_last['ipid']]['PatientReadmission'][$v_pkvno_last['patient_readmission_ID']] ['date']));
            }
            */         
            // --
        
        
        
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // B_thera_ende
            // -1=Keine Angabe;
            // 1=1 - Verstorben;
            // 2=2 - Verlegung, Entlassung;
            // 5=5 - Stabilisierung unter erfolgtem Versorgungs- / Behandlungsangebot;//ISPC-2496 Ancuta 03.12.2019 Added new option
            // 6=6 - Änderung der Betreuung;//ISPC-2496 Ancuta 03.12.2019 Added new option
            // 4=4 - Sonstiges
            if ($v_pkvno_last['therapieende'] == '0' || empty($v_pkvno_last['therapieende'])) {
                $thera_ende = "-1";
            } else {
                $thera_ende_mapping = array(
                '0' => '-1',
                '1' => '1',
                '2' => '2',
                '3' => '2',
                    
                '5' => '5',
                '6' => '6',
                    
                '4' => '4'
                ); // thera ende has no "3" value in xsd...weird
                $thera_ende = $thera_ende_mapping[$v_pkvno_last['therapieende']];
            }
        
            $export_data_this_form['B_thera_ende'] = $thera_ende;
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
            // B_sterbeort_0
            // XSD V 3.0
            // 23. Sterbeort | -1=Keine Angabe; 1=zuhause; 2=Heim; 3=Hospiz; 4=Palliativstation; 5=Krankenhaus; 6=unbekannt; 7=Sonstiges
        
            $sterbeort = '-1'; // Keine Angabe
            
            if (empty($v_pkvno_last['sterbeort_dgp']) || $v_pkvno_last['sterbeort_dgp'] == '0') {
                $sterbeort = '-1'; // Keine Angabe
            } else {
                // SYSTEM Loc type ids -> XSD values
                // 1 = zu Hause -> XSD : 1
                // 2 = KH -> XSD : 5
                // 3 = Hospiz -> XSD : 3
                // 4 = Altenheim, Pflegeheim -> XSD : 2
                // 5 = Palliativ -> XSD : 4
                // 6 = bei Kontaktperson -> XSD : 6 // unknown in register
                // 7 = Kurzzeitpflege -> XSD : 6 // unknown in register
                // 8 = betreutes Wohnen -> XSD : 6 // unknown in register
                // other -> XSD : 7 // sonstiges
        
                $location_type_map = array(
                '1' => '1', // zu Hause
                '2' => '5', // Krankenhaus
                    '3' => '3', // Hospiz
                        '4' => '2', // Heim
                        '5' => '4', // Palliativstation
                        '6' => '6', // bei Kontaktperson
                            '7' => '6', // Kurzzeitpflege
                            '8' => '6'
                    ) // betreutes Wohnen
                    ;
        
                if ($discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type'] != 0 
                    && strlen($location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']]) > 0) 
                {
                    $sterbeort = $location_type_map[$discharge_loc[$v_pkvno_last['sterbeort_dgp']]['type']];
                } else {
                // no location type or other(sonstiges...)
                    $sterbeort = '7';
                }
            }
        
            $export_data_this_form['B_sterbeort_0'] = $sterbeort;
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
            
            // B_bewertung
            // -1=Keine Angabe;
            // 1=1 - sehr gut;
            // 2=2 - gut;
            // 3=3 - mittel;
            // 4=4 - schlecht;
            // 5=5 - sehr schlecht
            $satisfaction_map = array(
            '0' => '-1',
            '1' => '5',
            '2' => '4',
            '3' => '3',
            '4' => '2',
            '5' => '1'
            );
        
            if (empty($v_pkvno_last['zufriedenheit_mit'])) {
                $satisfaction = '-1';
            } else {
                $satisfaction = $satisfaction_map[$v_pkvno_last['zufriedenheit_mit']];
            }
            $export_data_this_form['B_bewertung'] = $satisfaction;
            /* ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
        
        
        
            /*
             * @update 14.06.2018 ISPC-2198
             * The form can only be considered completed if and only if the patient is discharged ? yup
             */
            if (empty($v_pkvno_last['patient_readmission_ID'])
                || empty ($v_pkvno['patient_readmission_ID'])
                || empty($patients_ids_arr[$v_pkvno_last['ipid']]['PatientReadmission'][$v_pkvno_last['patient_readmission_ID']] ['date'])
                || empty($patients_ids_arr[$v_pkvno['ipid']]['PatientReadmission'][$v_pkvno['patient_readmission_ID']] ['date']))
                
            {
                //this form IS NOT COMPLETED ! we DO NOT SEND THIS !
                continue;
                
            } else {
        
                $result_export_data[ $v_pkvno['ipid'] ][] = $export_data_this_form;
            }
        
        }
        
        return $result_export_data;
        
    }
        
        
        
        
        
    
    /**
     * @cla on 06.06.2018
     *
     * ! Attention, we manualy exclude forms with falls before 2013-01-01
     *
     * this fn replaces the previous dgp_auto_export()
     *
     * if param $only_ipids IS NULL, this is AUTO-Submit done by cronjob, and must have module 124
     * else this is a Manual export of some ipids, $only_client must be integer
     * 
     */
    public function dgp_auto_export_v3( $only_client = null, $only_ipids = null)
    {
        /**
         * someone made this invention of users that are not allowed to send..
         * good luck for @testers trying to use one of this ones..
         *
         * info: i kept the var name, $sadmin_users = users that will NOT be sent.. so you 'understand' what a sadmin is (nOOt)
         */
        $sadmin_users = array("verenakauth", "sadmin", "volkerkerkhoff", "-");
         
         
        $dgp_clients_details  = array();
         
        if ( ! Zend_Registry::isRegistered("hospizregister") || ($hospizregister_cfg = Zend_Registry::get("hospizregister")) == false) {
            throw new Exception("missing bootstrap _initHospizregister", 0);
        }
         
         
        if (is_null($only_ipids)) {//this is AUTO-Submit done by cronjob
            
            $modules_array = array('124');
            
            $dgp_export_clients = Modules::clients2modules($modules_array);
         
            if ( ! is_null($only_client)) {
        
                if (in_array($only_client, $dgp_export_clients)) {
        
                    $dgp_export_clients =  array($only_client);
        
                } else {
        
                    $this->_log_error("DGP this client does NOT have dgp module.. how did you managed to do this ?");
                    
                    return; //this client does NOT have dgp module.. how did you managed to do this ?
                }
            }
        
            
        } else {//this is a Manual export
            
            $dgp_export_clients =  array($only_client);
        }
    

        if (empty($dgp_export_clients)) {
            
            $this->_log_error("DGP no client has dgp module");
            
            return;// no client has dgp module
        }
    
    
        //get all client_details
        $clients_res = Doctrine_Query::create()
        ->select("
	            id,
	            dgp_transfer_date,
	            AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
				AES_DECRYPT(dgp_user,'" . Zend_Registry::get('salt') . "') as dgp_user,
				AES_DECRYPT(dgp_pass,'" . Zend_Registry::get('salt') . "') as dgp_pass
	            "
        )
        ->from('Client indexBy id')
        ->whereIn('id ', $dgp_export_clients )
        ->andWhere('isdelete = 0')
        ->andWhere("dgp_user IS NOT NULL") // on dev is not nullable... maybe live db is different
        ->andWhere("dgp_user != '' ")
        ->andWhere("dgp_pass IS NOT NULL") // on dev is not nullable... maybe live db is different
        ->andWhere("dgp_pass != '' ")
        ->andWhereNotIn("AES_DECRYPT(dgp_user, '" . Zend_Registry::get('salt') . "')" , $sadmin_users)
        ->fetchArray()
        ;
    
        foreach ($clients_res as $clientid => $client_details)
        {
             
            $start_dgp =  microtime(true);
    
            $this->_log_info("RUN DGP:: - auto export start for client {$clientid} ");
    
            $this->_processingClientid = $clientid;
    
    
            $patients_ipids = DgpKern::findIpidsOfClient($clientid);
    
            if (empty($patients_ipids)) {
                continue; // jump to next client, this has no patients for dgp
            }
    
    
            if ( ! is_null($only_ipids)) {
                //you want just some ipids of a simple client
    
                if (empty($only_ipids) || ! is_array($only_ipids)) {
                    
                    $this->_log_error("DGP fail-safe ... we only accept array of strings");
                    
                    return;//fail-safe ... we only accept array of strings
                }
    
                $patients_ipids = array_intersect($only_ipids, $patients_ipids);
            }
    
    
            if (empty($patients_ipids)) {
                continue; // jump to next client, this has no patients for dgp
            }
    
    
            if ($client_details['dgp_transfer_date'] != "0000-00-00 00:00:00"
                && $client_details['dgp_transfer_date'] != "1970-01-01 01:00:00")
            {
                //this is a client settings
                //we must filter only the patients active in this period
    
                $sqls = 'e.epid, p.ipid, e.ipid,';
    
                $conditions = array(
                    'client' => $clientid,
                    'ipids' => $patients_ipids,
                    'periods' => array(
                        array(
                            'start' => date('Y-m-d', strtotime($client_details['dgp_transfer_date'])),
                            'end' => date('Y-m-d')
                        )
                    )
                );
    
                //TODO replace this Pms_CommonData::patients_days with a straight one
                $patient_days = Pms_CommonData::patients_days($conditions, $sqls);
    
                if (empty($patient_days)) {
                    continue; //jump to next client, this has no patient active in this period
                }
    
                $patients_ipids = array_keys($patient_days); // new filtered ipids
    
            }
    
    
            if (empty($patients_ipids)) {
                continue; // fail-safe
            }
    
            $completed_forms = DgpKern::findFormsCompletedOfIpids($patients_ipids);
    
            if (empty($completed_forms)) {
                continue;//jump to next client, this has no form fully filled
            }
    
            $patients_ipids = array_column($completed_forms, 'ipid');// new filtered ipids, this are not unique, there may be multiple form filled for the same ipid, in different falls
    
    
            $patients_submited_status =  $this->findSubmitedStatusOfIpids($patients_ipids, array($clientid));
    
            $not_submited_patients = array_filter ( $patients_submited_status, function($val) {return $val == 'not_submited' ;});
            //$submited_patients =  array_filter ( $patients_submited_status, function($val) {return $val == 'submited' ;});
    
            if (empty($not_submited_patients)) {
                continue;//jump to next client, this has all forms submited
            }
    
            $patients_ipids = array_keys($not_submited_patients);// new filtered ipids
    
            $completed_forms_to_submit = array_filter($completed_forms, function($val) use ($patients_ipids) {return in_array($val['ipid'], $patients_ipids);});
            //this are not ipid unique, there may be multiple form filled in different falls
            //this must be transfored to XML
    
            if (empty($completed_forms_to_submit)) {
                continue;//fail-safe
            }
    
            $patients_ipids = array_unique(array_column($completed_forms_to_submit, 'ipid'));// new filtered ipids
    
    
            $final_data_as_array = $this->create_DgpKernArray_from_completed_forms($completed_forms_to_submit, $clientid); //this are groupped by ipid
    
             
            if (empty($final_data_as_array)) {
                continue;//fail-safe
            }
             
    
            $formulars_KERN=  array();
    
            foreach ($final_data_as_array as $ipid => $his_formulars) {
                $formulars_KERN = array_merge($formulars_KERN, $his_formulars);
            }
    
    
            /*
             * ! ATTENTION !
             *
             * i've excluded form for Falls before 2013-01-01
             *
             * because i've added a restriction on XSD, and we force-pass it
             * + B_auf_datum pattern + min 2013-01-01
             * + B_datum_ende pattern + min 2013-01-01
             *
             */
            //$formulars_KERN = array_filter($formulars_KERN, function($val) {return $val['B_auf_datum'] >= '2013-01-01' && $val['B_auf_datum'] >= '2013-01-01';});
            $formulars_KERN = array_filter($formulars_KERN, function($val) {return $val['B_XML_date'] >= '2013-01-01' && $val['B_Jahrderversorgung'] >= '2015';});
            $formulars_KERN = array_values($formulars_KERN);
            
           
    
            $xml_string = $this->DgpKernArray_2_DgpKernXML( array('KERN' => $formulars_KERN ));
    
            if (empty($xml_string)) {
                continue;//fail-safe .. something  went wrong converting the array2xml
            }
    
            if ( ! $this->isValid_DgpKernXML($xml_string)) {
                //failed to validate xml...
                //isValid_NatHospiz_Kern_XML creates a log on error
                continue;
            }
    
    
            $post = array(
                "BNAME"=>	$client_details['dgp_user'],
                "KWORT"=>	$client_details['dgp_pass'],
                "VERSION"=>	$hospizregister_cfg['KERN']['version'], //xsd version
                "rc"=>	1, //return code
                "DATEN"=>	$xml_string,
            );
    
            $post_result = $this->httpPost_to_NatHospiz( $post );
    
            $post_result = trim($post_result);
    
    
            $userid = 0 ; // 0 because this is system
    
            $history_id = $this->httpPost_result_save($userid, $clientid, $xml_string, $post_result);
    
    
            // because you are using simple text... we do substr
            // response as soap,rest, XML will be after the TODO: invent time machine
            switch ($code = substr($post_result, 0, 4)) {
    
                case "1000":
    
                    $this->patients_history_save($history_id, $patients_ipids, $userid, $clientid);
    
                    $end_dgp = round( microtime(true) - $start_dgp, 0 );
    
                    $this->_log_info("RUN DGP:: - auto export function  was executed for client {$clientid} ({$end_dgp} seconds ) ,  KERNs sent = " . count($formulars_KERN));
    
                    break;
    
                default:
    
                    $this->_log_error("RUN DGP:: - auto export FAILED for client {$clientid} ({$end_dgp} seconds ) , code {$code} = " . $this->NatHospiz_response_text[$code]);
    
                    break;
    
            }
    
        }
    
        if ( ! is_null($only_client) || ! is_null($only_ipids)) {
            return $code;
        }
         
    }
    
    
    /**
     *
     * @param unknown $ipids
     * @param string $client_ids - this is more a failsafe...?? cause ipid is unique and belongs only to one client
     * @return void|multitype:string
     */
    public static function findSubmitedStatusOfIpids($ipids = array(), $client_ids = null)
    {
        if (empty($ipids)) {
            return;
        }
    
        $result = array();
    
        $client_query = '';
    
        if ( ! empty($client_ids) && is_array($client_ids)) {
    
            $placeholder_clientids = str_repeat ('?, ',  count ($client_ids) - 1) . '?';
    
            $client_query = " AND `client` IN ({$placeholder_clientids}) ";
    
        } else {
    
            $client_ids = array();
        }
    
        $placeholder_ipids = str_repeat ('?, ',  count ($ipids) - 1) . '?';
    
    
        $querystr = "SELECT *,max(upload_date) as  upload_date
    
        FROM dgp_patients_history p
    
        INNER JOIN (
    
        SELECT id
        FROM `dgp_patients_history`
        WHERE
        ipid IN ({$placeholder_ipids})
        {$client_query}
        ORDER BY `upload_date` DESC
         
        ) AS p2
        ON p.id = p2.id
    
        GROUP BY p.ipid
        ORDER BY p.id ASC
        ";
    
        $manager = Doctrine_Manager::getInstance();
        $manager->setCurrentConnection('SYSDAT');
    
        $conn = $manager->getCurrentConnection();
    
        $query = $conn->prepare($querystr);
    
        $query->execute(array_merge($ipids, $client_ids));
    
        $exported_patiens_array = $query->fetchAll(PDO::FETCH_ASSOC);
    
    
        $not_submited = array();
    
        if (empty($exported_patiens_array)) {
    
            //none of them was submited
            foreach ($ipids as $ipid) {
                $result[$ipid] = 'not_submited';
            }
    
        } else {
    
            // get last_update from patient master, and compare with upload_date submited from history
    
            $exported_ipids = array_column($exported_patiens_array, 'ipid');
    
            //indexBy ipid
            foreach ($exported_patiens_array as $expo_data) {
                $exported_patiens_array[$expo_data['ipid']] = $expo_data;
            }
             
    
            $patients_last_update = Doctrine_Query::create()
            ->select("ipid,
                last_update,
                last_update_user")
            ->from('PatientMaster indexBy ipid')
            ->whereIn('ipid', $exported_ipids)
            ->fetchArray()
            ;
    
            foreach ($ipids as $ipid) {
    
                if ( ! isset($exported_patiens_array[$ipid])) {
    
                    $result[$ipid] = 'not_submited';
    
                } elseif (strtotime($exported_patiens_array[$ipid]['upload_date']) < strtotime($patients_last_update[$ipid]['last_update'])) {
    
                    $result[$ipid] = 'not_submited';
    
                } else {
    
                    $result[$ipid] = 'submited';
    
                }
            }
    
        }
    
        return $result;
    }
    
    
    /**
     *
     * @param array $kern_formulars_array
     * @throws Exception
     * @return string
     */
    public function DgpKernArray_2_DgpKernXML($kern_formulars_array = array())
    {
        $xml_string = null;
    
        if ( ! Zend_Registry::isRegistered("hospizregister") || ($hospizregister_cfg = Zend_Registry::get("hospizregister")) == false) {
            throw new Exception("missing bootstrap _initHospizregister", 0);
        }
    
        try {
            /*
            $dom = Pms_Array2XML::createXML('alle', array_merge($kern_formulars_array , array(
                '@attributes' => array(
                    "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
                    "xsi:noNamespaceSchemaLocation" => $hospizregister_cfg['KERN']['xsd_uri'],
                ))));
            */
            $dom = Pms_Array2XML::createXML('alle', $kern_formulars_array);
    
            $xml_string = $dom->saveXML();
    
        } catch (Exception $e) {
    
            $this->_log_error( __METHOD__ . __LINE__ . " : FAILED TO CONVERT ARRAY 2 XML !! " . $e->getMessage());
            //return false;
        }
    
        return $xml_string;
    }
    
    /**
     * validate $xml_string against XSD from hospizregister
     * this includes a _log_error() if failed
     *
     * @param string $xml_string
     * @throws Exception
     * @return boolean
     */
    public function isValid_DgpKernXML($xml_string)
    {
    
        if ( ! Zend_Registry::isRegistered("hospizregister") || ($hospizregister_cfg = Zend_Registry::get("hospizregister")) == false) {
            throw new Exception("missing bootstrap _initHospizregister", 0);
        }
         
        libxml_use_internal_errors(true);
    
        $xmlerrors = array();
    
        
        $schemePath = $hospizregister_cfg['KERN']['xsd_uri'];
        //$schemePath = PUBLIC_PATH . "/xsd/KERN_3.2.xsd";
        $schemePath = PUBLIC_PATH . "/xsd/KERN_4.0.xsd";//ISPC-2496 Ancuta +TODO-1943 Alex
    
        $dom = new DomDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
    
        $result = $dom->loadXML($xml_string);
    
        if ($result === false) {
    
            $xmlerrors[] = "Document is not well formed\n";
    
            $validation = false;
    
        } elseif (@($dom->schemaValidate($schemePath))) {
    
            $validation = true;
             
        } else {
             
            $validation = false;
    
            $xmlerrors[] = "! Document is not valid:\n";
            $errors = libxml_get_errors();
    
            foreach ($errors as $error) {
                if (APPLICATION_ENV == 'development') {
                    $xmlerrors[] = "---\n" . sprintf("file: %s, line: %s, column: %s, level: %s, code: %s\nError: %s",
                        basename($error->file),
                        $error->line,
                        $error->column,
                        $error->level,
                        $error->code,
                        $error->message
                    );
                } else {
                    $xmlerrors[] = "---\n" . sprintf("Error: %s", $error->message);
                }
            }
        }
    
    
        if ( ! empty($xmlerrors) ) {
            // invalid xml
             
            //we log in here.. this log should have been outside $this->_xml_xsd_errors = $xmlerrors ...
            $this->_log_error('FAILED to ' . __METHOD__ . ' for client ' . $this->_processingClientid . " " . print_r($xmlerrors, true));
            //$this->_log_error($xml_string);
            
            return false;
    
        } else {
    
            return true;
    
        }
    
    }
    
    /**
     *
     * cUrl-POST to $hospizregister_cfg['KERN']['url'] ~ hospiz-palliativ-register.de
     *
     * @param array $post
     * @throws Exception
     * @return void|string
     */
    public function httpPost_to_NatHospiz ( $post = array())
    {
        if (empty($post)) {
            return; // fail-safe
        }
    
        if ( ! Zend_Registry::isRegistered("hospizregister") || ($hospizregister_cfg = Zend_Registry::get("hospizregister")) == false) {
            throw new Exception("missing bootstrap _initHospizregister", 0);
        }
    
        $url = $hospizregister_cfg['KERN']['url'];
    
    
        if (is_null($this->_httpService)) {
    
            $adapter = new Zend_Http_Client_Adapter_Curl();
    
            $adapter->setConfig(array(
                'curloptions' => array(
                    CURLOPT_FOLLOWLOCATION  => true,
                    CURLOPT_RETURNTRANSFER  => true,
                     
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                     
                    // 	            CURLOPT_TIMEOUT => 11,
                    CURLINFO_CONNECT_TIME => 10,
                    CURLOPT_CONNECTTIMEOUT => 10,
                )
            ));
             
            $httpConfig = array(
                'timeout'       => 10,// Default = 10
                'useragent'     => 'Zend_Http_Client-ISPC-MISC-NATREG-INSERT',// Default = Zend_Http_Client
            );
    
            $this->_httpService =  new Zend_Http_Client(null, $httpConfig);
            $this->_httpService->setAdapter($adapter);
            $this->_httpService->setUri(Zend_Uri_Http::fromString($url));
        }
    
        $this->_httpService->resetParameters(true);
    
        $this->_httpService->setMethod('POST');
    
        $this->_httpService->setParameterPost($post);
    
        $this->_httpService->request('POST');
    
        $result = $this->_httpService->getLastResponse()->getBody();
    
        return $result;
    
    }
    
    
    /**
     *
     * @param number $user
     * @param number $client
     * @param string $xml_string
     * @param string $response
     * @return number
     */
    public function httpPost_result_save($user = 0, $client = 0, $xml_string = '', $response = '')
    {
        $data = array(
            'user' => $user,
            'client' => $client,
            'upload_date' => date('Y-m-d H:i:s'),
            'xml_string' => $xml_string,
            'response_text' => $response,
            'response_code' => substr($response, 0, 4),
        );
    
        $history = new DgpHistory();
        $history_entity = $history->findOrCreateOneBy('id', null, $data);
    
        if ($history_entity) {
            return $history_entity->id;
        } else {
            return 0; // fail-safe
        }
    }
    
    /**
     *
     * @param number $history_id
     * @param array $ipids
     * @param number $user
     * @param number $client
     * @return NULL, multitype:int>
     */
    public function patients_history_save($history_id = 0, $ipids = array() , $user = 0, $client = 0)
    {
        $result = null;
    
        if ( ! empty($ipids) && is_array($ipids))
        {
    
            $ipids = array_unique($ipids);
    
            $records_array = array();
    
            foreach ($ipids as  $ipid) {
    
                $records_array[] = array(
                    'history_id' => $history_id,
                    'ipid' => $ipid,
                    'upload_date' => date('Y-m-d H:i:s'),
                    'user' => $user,
                    'client' => $client
                );
            }
    
            if ( ! empty($records_array)) {
                $collection = new Doctrine_Collection('DgpPatientsHistory');
                $collection->fromArray($records_array);
                $collection->save();
                $result = $collection->getPrimaryKeys();
            }
        }
    
        return $result;
    
    }
    
    
    /**
     * ! i did not forced/declared the params as Zend_Date ... so i returned 0
     *
     * @param Zend_Date $birthDate
     * @param Zend_Date $date
     * @return number
     */
    private function _age($birthDate, $date)
    {
        if ($birthDate instanceof Zend_Date && $date instanceof Zend_Date) {
    
            $age = $date->get(Zend_Date::YEAR) - $birthDate->get(Zend_Date::YEAR);
    
            $birthYear = clone $birthDate;
    
            $birthYear->set($date, Zend_Date::YEAR);
    
            if (1 == $birthYear->compare($date)) {
                $age = $age -1; // if birth day has not passed yet
            }
    
            return $age;
    
        } else {
    
            return 0;
        }
    }
    
    
    /**
     *
     * @param string $message
     */
    protected static function _log_info($message)
    {
        parent::_log_info($message, 16);
    }
    
    /**
     *
     * @param string $message
     */
    protected static function _log_error($message)
    {
        parent::_log_error($message, 17);
    }
    
}

?>