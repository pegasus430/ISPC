<?php

Doctrine_Manager::getInstance()->bindComponent('DgpKern', 'MDAT');

class DgpKern extends BaseDgpKern 
{

	    public function patients_filled_status($ipids =  array())
	    {
	        
	        if (!is_array($ipids) || count($ipids) == 0){
	        	return array();
	        }
	        
	        $old_version = "0";
	        
	        /* ISPC-1775,ISPC-1678, ISPC-1994 */
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	        $userid = $logininfo->userid;
	    
	        $ipid_str = "";
	        foreach ($ipids as $ipid)
	        {
	            $ipid_str .= '"' . $ipid. '",';
	        }
	        if ($ipid_str == "") {
	        	$ipid_str='"99999",';
	        }
	        $ipid_str = substr($ipid_str, 0, -1);
	    
	        if($old_version == "1"){
	        	
		        $patientKvnofirst = Doctrine_Query::create()
		        ->select('*')
		        ->from('DgpKern ka')
		        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and kb.form_type="adm" and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` ASC	LIMIT 1 )')
		        ->groupBy('ipid')
		        ->orderby('id asc');
		        $patientKvnoarrayfirst = $patientKvnofirst->fetchArray();
	        } 
	        else
	        {
		        $patientKvnoarrayfirst = array();
		        
		        $start = microtime(true);
		        $querystr = 'SELECT *
		        FROM patient_dgp_kern p
		        INNER JOIN (SELECT id  FROM `patient_dgp_kern` WHERE form_type = "adm" AND ipid IN ('.$ipid_str.') ORDER BY `create_date` ASC ) AS p2
		        ON p.id = p2.id
		        GROUP BY p.ipid
		        ORDER BY p.id asc';
		        $manager = Doctrine_Manager::getInstance();
		        $manager->setCurrentConnection('MDAT');
		        $conn = $manager->getCurrentConnection();
		        $query = $conn->prepare($querystr);
		        $dropexec = $query->execute();
		        $patientKvnoarrayfirst = $query->fetchAll();
	        }
	        
	        
	        
	        
	        // verify if first form is completed
	        foreach ($patientKvnoarrayfirst as $key => $value)
	        {
	    
	            $dgp['kern']['admission'][$value['ipid']]['ipid'] = $value['ipid'];
	            $dgp['kern']['admission'][$value['ipid']]['kvno_id'] = $value['id'];
	    
	            if ($value['ecog'] == '0' || empty($value['begleitung']) || empty($value['kontaktes']))
	            {
	                $dgp['kern']['admission'][$value['ipid']]['status'] = 'n'; // not completed
	                $dgp_dbg['kern']['not_completed']['admission'][]  = $value['ipid'];
	            }
	            else
	            {
	                $dgp['kern']['admission'][$value['ipid']]['status'] = 'c'; // completed
	                $dgp_dbg['kern']['completed']['admission'][]  = $value['ipid'];
	    
	            }
	        }
	    

	        if($old_version == "1"){
		        $patientKvnolast = Doctrine_Query::create()
		        ->select('*')
		        ->from('DgpKern ka')
		        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and kb.form_type="dis"  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` DESC	LIMIT 1 )')
		        ->groupBy('ipid')
		        ->orderby('id asc');
		        $patientKvnoarraylast = $patientKvnolast->fetchArray();	        
	        } 
	        else
	        {
		        $patientKvnoarraylast = array();
		        
		        $querystr = 'SELECT * 
				FROM patient_dgp_kern p 
				INNER JOIN (SELECT id  FROM `patient_dgp_kern` WHERE form_type = "dis" AND ipid IN ('.$ipid_str.') ORDER BY `create_date` DESC ) AS p2
				ON p.id = p2.id
				GROUP BY p.ipid
				ORDER BY p.id asc;';
		        $manager = Doctrine_Manager::getInstance();
		        $manager->setCurrentConnection('MDAT');
		        $conn = $manager->getCurrentConnection();
		        $query = $conn->prepare($querystr);
		        $dropexec = $query->execute();
		        $patientKvnoarraylast = $query->fetchAll();
	        }
	        
	        foreach ($patientKvnoarraylast as $key_last => $value_last)
	        {
	            $dgp['kern']['discharge'][$value_last['ipid']]['ipid'] = $value_last['ipid'];
	            $dgp['kern']['discharge'][$value_last['ipid']]['kvno_id'] = $value_last['id'];
	    
	            
	            if ($dgp['kern']['discharge'][$value_last['ipid']]['kvno_id'] != $dgp['kern']['admission'][$value_last['ipid']]['kvno_id'])
	            {
	                if (
	                	//empty($value_last['who'])
	                    //|| $value_last['steroide'] == '0'
	                    //||
	                	$value_last['chemotherapie'] == '0'
	                    || $value_last['zufriedenheit_mit'] == '0'
	                    || $value_last['strahlentherapie'] == '0'
						|| $value_last['therapieende'] == '0'
	                	|| empty($value_last['begleitung'])
	                )
	                {
	                    $dgp['kern']['discharge'][$value_last['ipid']]['status'] = 'n'; //not completed
	                    $dgp_dbg['kern']['not_completed']['discharge'][]  = $value_last['ipid'];
	                }
	                else
	                {
	                    $dgp['kern']['discharge'][$value_last['ipid']]['status'] = 'c'; // completed
	                    $dgp_dbg['kern']['completed']['discharge'][]  = $value_last['ipid'];
	                }
	            }
	            else
	            {
	                $dgp['kern']['discharge'][$value_last['ipid']]['status'] = 'n'; // not completed
	                $dgp_dbg['kern']['not_completed']['discharge'][]  = $value_last['ipid'];
	            }
	        }
	    
	    
	   
	        if($_REQUEST['dbg']=="2"){
	            print_r($dgp_dbg);
	        }

	        foreach ($ipids as   $pat_ipid)
	        {
	            if (empty($dgp['kern']['admission'][$pat_ipid]['status']))
	            { // check if any form was filed
	                $patient_data[$pat_ipid]['adm'] = "0"; // non extistent
	                $patient_data[$pat_ipid]['dis'] = "0"; //non extistent
	            }
	            elseif (!empty($dgp['kern']['admission'][$pat_ipid]['status']))
	            {
	                if ($dgp['kern']['admission'][$pat_ipid]['status'] == 'c')
	                {
	                    $patient_data[$pat_ipid]['adm'] = "c"; // completed
	                }
	                else
	                {
	                    $patient_data[$pat_ipid]['adm'] = "n"; //not completed
	                }
	                if ($dgp['kern']['discharge'][$pat_ipid]['kvno_id'] != $dgp['kern']['admission'][$pat_ipid]['kvno_id'])
	                {
	                    if ($dgp['kern']['discharge'][$pat_ipid]['status'] == 'c')
	                    {
	                        $patient_data[$pat_ipid]['dis'] = "c"; // completed
	                    }
	                    else
	                    {
	                        $patient_data[$pat_ipid]['dis'] = "n"; //not completed
	                    }
	                }
	                else
	                {
	                    $patient_data[$pat_ipid]['dis'] = "0"; //non existent
	                }
	            }
	    
	            // return only patients with completed data
	            if( $patient_data[$pat_ipid]['dis'] == "c" && $patient_data[$pat_ipid]['adm'] == "c") {
	                $completed_patient_data[] = $pat_ipid;
	            }
	        }
	        
	        return $completed_patient_data;
	    }
	    public function patients_filled_status_v2($ipids =  array())
	    {
	        
	        if (!is_array($ipids) || count($ipids) == 0){
	        	return array();
	        }
	        
	        $old_version = "0";
	        
	        /* ISPC-1775,ISPC-1678 */
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	        $userid = $logininfo->userid;
	    
	        $ipid_str = "";
	        foreach ($ipids as $ipid)
	        {
	            $ipid_str .= '"' . $ipid. '",';
	        }
	        if ($ipid_str == "") {
	        	$ipid_str='"99999",';
	        }
	        $ipid_str = substr($ipid_str, 0, -1);
	    
	        if($old_version == "1"){
	        	
		        $patientKvnofirst = Doctrine_Query::create()
		        ->select('*')
		        ->from('DgpKern ka')
		        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and kb.form_type="adm" and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` ASC	LIMIT 1 )')
		        ->groupBy('ipid')
		        ->orderby('id asc');
		        $patientKvnoarrayfirst = $patientKvnofirst->fetchArray();
	        } 
	        else
	        {
		        $patientKvnoarrayfirst = array();
		        
		        $start = microtime(true);
		        $querystr = 'SELECT *
		        FROM patient_dgp_kern p
		        INNER JOIN (SELECT id  FROM `patient_dgp_kern` WHERE form_type = "adm" AND ipid IN ('.$ipid_str.') ORDER BY `create_date` ASC ) AS p2
		        ON p.id = p2.id
		        GROUP BY p.ipid
		        ORDER BY p.id asc';
		        $manager = Doctrine_Manager::getInstance();
		        $manager->setCurrentConnection('MDAT');
		        $conn = $manager->getCurrentConnection();
		        $query = $conn->prepare($querystr);
		        $dropexec = $query->execute();
		        $patientKvnoarrayfirst = $query->fetchAll();
	        }
	        
	        
	        
	        
	        // verify if first form is completed
	        foreach ($patientKvnoarrayfirst as $key => $value)
	        {
	    
	            $dgp['kern']['admission'][$value['ipid']]['ipid'] = $value['ipid'];
	            $dgp['kern']['admission'][$value['ipid']]['kvno_id'] = $value['id'];
	    
	            if ($value['ecog'] == '0' || empty($value['begleitung']) || empty($value['kontaktes']))
	            {
	                $dgp['kern']['admission'][$value['ipid']]['status'] = 'n'; // not completed
	                $dgp_dbg['kern']['not_completed']['admission'][]  = $value['ipid'];
	            }
	            else
	            {
	                $dgp['kern']['admission'][$value['ipid']]['status'] = 'c'; // completed
	                $dgp_dbg['kern']['completed']['admission'][]  = $value['ipid'];
	    
	            }
	        }
	    

	        if($old_version == "1"){
		        $patientKvnolast = Doctrine_Query::create()
		        ->select('*')
		        ->from('DgpKern ka')
		        ->where('ka.id =(SELECT kb.id  FROM DgpKern kb WHERE ka.ipid = kb.ipid and kb.form_type="dis"  and  kb.ipid in (' . $ipid_str . ') ORDER BY `create_date` DESC	LIMIT 1 )')
		        ->groupBy('ipid')
		        ->orderby('id asc');
		        $patientKvnoarraylast = $patientKvnolast->fetchArray();	        
	        } 
	        else
	        {
		        $patientKvnoarraylast = array();
		        
		        $querystr = 'SELECT * 
				FROM patient_dgp_kern p 
				INNER JOIN (SELECT id  FROM `patient_dgp_kern` WHERE form_type = "dis" AND ipid IN ('.$ipid_str.') ORDER BY `create_date` DESC ) AS p2
				ON p.id = p2.id
				GROUP BY p.ipid
				ORDER BY p.id asc;';
		        $manager = Doctrine_Manager::getInstance();
		        $manager->setCurrentConnection('MDAT');
		        $conn = $manager->getCurrentConnection();
		        $query = $conn->prepare($querystr);
		        $dropexec = $query->execute();
		        $patientKvnoarraylast = $query->fetchAll();
	        }
	        
	        foreach ($patientKvnoarraylast as $key_last => $value_last)
	        {
	            $dgp['kern']['discharge'][$value_last['ipid']]['ipid'] = $value_last['ipid'];
	            $dgp['kern']['discharge'][$value_last['ipid']]['kvno_id'] = $value_last['id'];
	    
	            
	            if ($dgp['kern']['discharge'][$value_last['ipid']]['kvno_id'] != $dgp['kern']['admission'][$value_last['ipid']]['kvno_id'])
	            {
	                if (empty($value_last['who'])
	                    || $value_last['steroide'] == '0'
	                    || $value_last['chemotherapie'] == '0'
	                    || $value_last['zufriedenheit_mit'] == '0'
	                    || $value_last['strahlentherapie'] == '0'
						|| $value_last['therapieende'] == '0'
	                )
	                {
	                    $dgp['kern']['discharge'][$value_last['ipid']]['status'] = 'n'; //not completed
	                    $dgp_dbg['kern']['not_completed']['discharge'][]  = $value_last['ipid'];
	                }
	                else
	                {
	                    $dgp['kern']['discharge'][$value_last['ipid']]['status'] = 'c'; // completed
	                    $dgp_dbg['kern']['completed']['discharge'][]  = $value_last['ipid'];
	                }
	            }
	            else
	            {
	                $dgp['kern']['discharge'][$value_last['ipid']]['status'] = 'n'; // not completed
	                $dgp_dbg['kern']['not_completed']['discharge'][]  = $value_last['ipid'];
	            }
	        }
	    
	    
	        //		1. preluare sapvs pacient din stamdattem
	        $sapv= Doctrine_Query::create ()
	        ->select("id, ipid")
	        ->from('SapvVerordnung')
	        ->whereIn('ipid', $ipids)
	        ->andwhere('isdelete = 0')
	        ->orderBy('verordnungam ASC');
	        $patient_sapvsarray = $sapv->fetchArray();
	    
	        $patient_sapvs= array();
	    
	        foreach($patient_sapvsarray as  $value ) {
	            $patient_sapvs[$value['ipid']][] =  $value['id'];
	        }
	    
	        //2. preluare filled sapvs
	        $dgpsapv= Doctrine_Query::create()
	        ->select("id, ipid, sapv, verordnung_datum, art_der_erordnung, verordnung_durch, ubernahme_aus")
	        ->from('DgpSapv')
	        ->whereIn('ipid', $ipids);
	        $patient_dgpsapvsarray = $dgpsapv->fetchArray();
	    
	        
	     
	        
	        //		3. check if filled sapv is completed or not
	        foreach($patient_dgpsapvsarray as $k_dgpsapv=>$v_dgpsapv)
	        {
	            if( empty($v_dgpsapv['sapv'])
	                || empty($v_dgpsapv['verordnung_datum'])
	                || empty($v_dgpsapv['art_der_erordnung'])
	                || $v_dgpsapv['verordnung_durch'] == '0'
	                || $v_dgpsapv['ubernahme_aus'] == '0'
	                //|| empty($v_dgpsapv['therapieende']) // this was added only for the last sapv 
	                )
	            {
	                //filled but not completed
	                $filled_sapvs[$v_dgpsapv['ipid']]['not_completed'][$v_dgpsapv['sapv']] = $v_dgpsapv['sapv']; //not completed
	                $dgp['sapv'][$v_dgpsapv['ipid']][$v_dgpsapv['sapv']]['not_completed'] = $v_dgpsapv['sapv']; //not completed
	                $dgp_dbg['sapv']['not_completed'][]  = $v_dgpsapv['ipid'];
	            }
	            else
	            {
	                //filled but completed
	                $filled_sapvs[$v_dgpsapv['ipid']]['completed'][$v_dgpsapv['sapv']] = $v_dgpsapv['sapv']; // completed
	                $dgp['sapv'][$v_dgpsapv['ipid']][$v_dgpsapv['sapv']]['completed'] = $v_dgpsapv['sapv'];// completed
	    
	                $dgp_dbg['sapv']['completed'][]  = $v_dgpsapv['ipid'];
	            }
	        }
// 	        if($_REQUEST['dbg']=="1"){
// 	            var_dump($dgp);
// 	        }
	        if($_REQUEST['dbg']=="2"){
	            print_r($dgp_dbg);
	        }

	        foreach ($ipids as   $pat_ipid)
	        {
	            if (empty($dgp['kern']['admission'][$pat_ipid]['status']))
	            { // check if any form was filed
	                $patient_data[$pat_ipid]['adm'] = "0"; // non extistent
	                $patient_data[$pat_ipid]['dis'] = "0"; //non extistent
	            }
	            elseif (!empty($dgp['kern']['admission'][$pat_ipid]['status']))
	            {
	                if ($dgp['kern']['admission'][$pat_ipid]['status'] == 'c')
	                {
	                    $patient_data[$pat_ipid]['adm'] = "c"; // completed
	                }
	                else
	                {
	                    $patient_data[$pat_ipid]['adm'] = "n"; //not completed
	                }
	                if ($dgp['kern']['discharge'][$pat_ipid]['kvno_id'] != $dgp['kern']['admission'][$pat_ipid]['kvno_id'])
	                {
	                    if ($dgp['kern']['discharge'][$pat_ipid]['status'] == 'c')
	                    {
	                        $patient_data[$pat_ipid]['dis'] = "c"; // completed
	                    }
	                    else
	                    {
	                        $patient_data[$pat_ipid]['dis'] = "n"; //not completed
	                    }
	                }
	                else
	                {
	                    $patient_data[$pat_ipid]['dis'] = "0"; //non existent
	                }
	            }
	             
	            if(!empty($patient_sapvs[$pat_ipid])) {
	                foreach ($patient_sapvs[$pat_ipid] as $value)
	                {
	                    if(array_key_exists($value, $filled_sapvs[$pat_ipid]['completed']))
	                    {
	                        //completed link
	                        $patient_data[$pat_ipid]['sapv_form']['filled_completed'][] = $value;
	                    }
	                    else if(array_key_exists($value, $filled_sapvs[$pat_ipid]['not_completed']))
	                    {
	                        //not completed link
	                        $patient_data[$pat_ipid]['sapv_form']['filled_not_completed'][]= $value ;
	                    }
	                    else
	                    {
	                        //not filled link
	                        $patient_data[$pat_ipid]['sapv_form']['not_filled'][] = $value;
	                    }
	                }
	    
	                if(!empty($patient_data[$pat_ipid]['sapv_form']['not_filled'])){
	                    $patient_data[$pat_ipid]['sapv_form_data'] = "not_done";
	                }elseif(!empty($patient_data[$pat_ipid]['sapv_form']['filled_not_completed'])){
	                    $patient_data[$pat_ipid]['sapv_form_data'] = "not_done";
	                } else{
	                    $patient_data[$pat_ipid]['sapv_form_data'] = "done";
	                }
	            } else{
	                $patient_data[$pat_ipid]['sapv_form_data'] = "done";
	            }
	    
	            // return only patients with completed data
	            if( $patient_data[$pat_ipid]['dis'] == "c" && $patient_data[$pat_ipid]['adm'] == "c" && $patient_data[$pat_ipid]['sapv_form_data'] == "done") {
	                $completed_patient_data[] = $pat_ipid;
	            }
	        }
	        
	        return $completed_patient_data;
	    }
	    
	    

	   public static function get_form_texts($translated = false, $report = false){
	   	$Tr = new Zend_View_Helper_Translate();
	   	if($report ){
		   	$hospizregister_lang = $Tr->translate('hospizregister_report_lang');
	   	} else{
		   	$hospizregister_lang = $Tr->translate('hospizregister_lang');
	   	}
	   	
	   	    $texts['partners'] = array(
				"7" => $hospizregister_lang["partners"]["ehrenamtlicher_dienst"],
	   		    "9" => $hospizregister_lang["partners"]["Hospiz_stationaer"],
	   		    "5" => $hospizregister_lang["partners"]["palliativpflege"],
	   		    "11"=> $hospizregister_lang["partners"]["Heim"],
	   		    "2" => $hospizregister_lang["partners"]["Hausarzt"],
	   		    "4" => $hospizregister_lang["partners"]["Palliativarzt"],
	   		    "12"=> $hospizregister_lang["partners"]["Palliative_Care_Team"],
	   		    "3" => $hospizregister_lang["partners"]["ambulante_pfleg"],
	   		    "14"=> $hospizregister_lang["partners"]["palliativberatung_AHPB"],// new
	   		    "8" => $hospizregister_lang["partners"]["KH_Palliativstation"],
	   		    "10"=> $hospizregister_lang["partners"]["Krankenhaus_Andere_Station"],
	   		    "15"=> $hospizregister_lang["partners"]["MVZ"], // new
	   		    "16"=> $hospizregister_lang["partners"]["KH_Palliativdienst"],// new
	   		    "17"=> $hospizregister_lang["partners"]["sonstiges"],// new
	   		    
	   	    	// ISPC-2144 
	   		    "18"=> $hospizregister_lang["partners"]["Sozialdienst"],// new
	   		    "19"=> $hospizregister_lang["partners"]["Ernährungsteam"],// new
	   		    "20"=> $hospizregister_lang["partners"]["Wundmanagement"],// new
	   		    "21"=> $hospizregister_lang["partners"]["Physiotherapie"],// new
	   		    "22"=> $hospizregister_lang["partners"]["Stomapflege"],// new
	   		    "23"=> $hospizregister_lang["partners"]["Psychotherapeut"],// new
	   	    );
	   	    
	   	    $texts['partners_groupped'] ['ambulant'] = array(
	   		    "3" => $hospizregister_lang["partners"]["ambulante_pfleg"],
				"7" => $hospizregister_lang["partners"]["ehrenamtlicher_dienst"],
	   		    "2" => $hospizregister_lang["partners"]["Hausarzt"],
	   		    "4" => $hospizregister_lang["partners"]["Palliativarzt"],
	   		    "5" => $hospizregister_lang["partners"]["palliativpflege"],
	   		    "12"=> $hospizregister_lang["partners"]["Palliative_Care_Team"],
	   		    "14"=> $hospizregister_lang["partners"]["palliativberatung_AHPB"],// new
	   		    "18"=> $hospizregister_lang["partners"]["Sozialdienst"],// new
	   		    "19"=> $hospizregister_lang["partners"]["Ernährungsteam"],// new
	   		    "20"=> $hospizregister_lang["partners"]["Wundmanagement"],// new
	   		    "21"=> $hospizregister_lang["partners"]["Physiotherapie"],// new
	   		    "22"=> $hospizregister_lang["partners"]["Stomapflege"],// new
	   		    "23"=> $hospizregister_lang["partners"]["Psychotherapeut"],// new
	   		    "24"=> $hospizregister_lang["partners"]["SAPV-Team"],// new
	   		    "17"=> $hospizregister_lang["partners"]["sonstiges"],// new
	   	    );
	   	    
	   	    $texts['partners_groupped_dgp'] ['ambulant'] = array(
	   		    "3" => $hospizregister_lang["partners"]["ambulante_pfleg"],
				"7" => $hospizregister_lang["partners"]["ehrenamtlicher_dienst"],
	   		    "2" => $hospizregister_lang["partners"]["Hausarzt"],
	   		    "4" => $hospizregister_lang["partners"]["Palliativarzt"],
	   		    "5" => $hospizregister_lang["partners"]["palliativpflege"],
	   		    "12"=> $hospizregister_lang["partners"]["Palliative_Care_Team"],
	   		    "14"=> $hospizregister_lang["partners"]["palliativberatung_AHPB"],// new
	   		    "18"=> $hospizregister_lang["partners"]["Sozialdienst"],// new
	   		    "19"=> $hospizregister_lang["partners"]["Ernährungsteam"],// new
	   		    "20"=> $hospizregister_lang["partners"]["Wundmanagement"],// new
	   		    "21"=> $hospizregister_lang["partners"]["Physiotherapie"],// new
	   		    "22"=> $hospizregister_lang["partners"]["Stomapflege"],// new
	   		    "23"=> $hospizregister_lang["partners"]["Psychotherapeut"],// new
	   		    "17"=> $hospizregister_lang["partners"]["sonstiges"],// new
	   	    );
	   	    
	   	    $texts['partners_groupped'] ['stationar'] = array(
	   		    "16"=> $hospizregister_lang["partners"]["KH_Palliativdienst"],// new
	   		    "8" => $hospizregister_lang["partners"]["KH_Palliativstation"],
	   		    "10"=> $hospizregister_lang["partners"]["Krankenhaus_Andere_Station"],
	   		    "15"=> $hospizregister_lang["partners"]["MVZ"], // new
	   		    "11"=> $hospizregister_lang["partners"]["Heim"],
	   		    "9" => $hospizregister_lang["partners"]["Hospiz_stationaer"],
	   	    );
	   	    
	   	    $texts['partners_simple'] = array(
				"7" => "ehrenamtlicher_dienst",
	   		    "9" => "Hospiz_stationaer",
	   		    "5" => "palliativpflege",
	   		    "11"=> "Heim",
	   		    "2" => "Hausarzt",
	   		    "4" => "Palliativarzt",
	   		    "12"=> "Palliative_Care_Team",
	   		    "3" => "ambulante_pfleg",
	   		    "14"=> "palliativberatung_AHPB",// new
	   		    "8" => "KH_Palliativstation",
	   		    "10"=> "Krankenhaus_Andere_Station",
	   		    "15"=> "MVZ", // new
	   		    "16"=> "KH_Palliativdienst",// new
	   		    "17"=> "sonstiges",// new
   	    		// ISPC-2144
   	    		/* 	   		    
	   		    "18"=> "Sozialdienst",
	   		    "19"=> "Ernährungsteam",
	   		    "20"=> "Wundmanagement",
	   		    "21"=> "Physiotherapie",
	   		    "22"=> "Stomapflege",
	   		    "23"=> "Psychotherapeut"
	   	    	 */	
	   	    );
	   	    
	   	   /* $texts['medication_references_a']  = array(
	   	    		"1" => $hospizregister_lang["medication_references"]["analgetika"],
	   	    		"2" => $hospizregister_lang["medication_references"]["who2"],
	   	    		"3" => $hospizregister_lang["medication_references"]["who3"],
	   	    		"4" => $hospizregister_lang["medication_references"]["nicht_opioide"],
	   	    		"5" => $hospizregister_lang["medication_references"]["co_analgetika"]
	   	    );
	   	   
	   	   $texts['medication_references_b']  = array(
	   	    		"6" => $hospizregister_lang["medication_references"]["steroide"],
	   	    		"7" => $hospizregister_lang["medication_references"]["anxiolytika"],
	   	    		"8" => $hospizregister_lang["medication_references"]["laxantien"],
	   	    		"9" => $hospizregister_lang["medication_references"]["sedativa"],
	   	    		"10" => $hospizregister_lang["medication_references"]["neuroleptika"],
	   	    		"11" => $hospizregister_lang["medication_references"]["anti_eleptika"],
	   	    		"12" => $hospizregister_lang["medication_references"]["anti_emetika"],
	   	    		"13" => $hospizregister_lang["medication_references"]["anti_biotika"],
	   	    		"14" => $hospizregister_lang["medication_references"]["magenschutz"]
	   	    ); */

	   	    //ISPC-2496 Ancuta 02.12.2019 - Added values from 15 to 21
	   	    //ISPC-2496 Ancuta 02.12.2019 - Commented values: BL_Analgetika,BL_Co_Analgetika 
	   	   $texts['medication_references_a']  = array(
	   	   			//"1" => array('code'=>'analgetika','label'=>$hospizregister_lang["medication_references"]['analgetika']),
	   	   			"2" => array('code'=>'who2','label'=>$hospizregister_lang["medication_references"]['who2']),
	   	   			"3" => array('code'=>'who','label'=>$hospizregister_lang["medication_references"]['who']),
	   	   			"4" => array('code'=>'nicht_opioide','label'=>$hospizregister_lang["medication_references"]['nicht_opioide']),
	   	   			//"5" => array('code'=>'co_analgetika','label'=>$hospizregister_lang["medication_references"]['co_analgetika']),
                    "15" => array('code'=>'secretioninhibiting_sub','label'=>$hospizregister_lang["medication_references"]['secretioninhibiting_sub']),
                    "16" => array('code'=>'benzodiazepines','label'=>$hospizregister_lang["medication_references"]['benzodiazepines']),
                    "17" => array('code'=>'antidepressants','label'=>$hospizregister_lang["medication_references"]['antidepressants']),
                    "18" => array('code'=>'antipsychotics','label'=>$hospizregister_lang["medication_references"]['antipsychotics']),
                    "19" => array('code'=>'anti_infectives','label'=>$hospizregister_lang["medication_references"]['anti_infectives']),
                    "20" => array('code'=>'anticoagulants','label'=>$hospizregister_lang["medication_references"]['anticoagulants']),
                    "21" => array('code'=>'other_meds','label'=>$hospizregister_lang["medication_references"]['other_meds']),
	   	    );
	   	   
	   	   //ISPC-2496 Ancuta 02.12.2019 - Commented values:BL_Anxiolytika,BL_Sedativa,BL_Neuroleptika,BL_Anti_Biotika
	   	   $texts['medication_references_b']  = array(
	   	   			"6" => array('code'=>'steroide','label'=>$hospizregister_lang["medication_references"]['steroide']),
	   	   			//"7" => array('code'=>'anxiolytika','label'=>$hospizregister_lang["medication_references"]['anxiolytika']),
	   	   			"8" => array('code'=>'laxantien','label'=>$hospizregister_lang["medication_references"]['laxantien']),
	   	   			//"9" => array('code'=>'sedativa','label'=>$hospizregister_lang["medication_references"]['sedativa']),
	   	   			//"10" => array('code'=>'neuroleptika','label'=>$hospizregister_lang["medication_references"]['neuroleptika']),
	   	   			"11" => array('code'=>'anti_eleptika','label'=>$hospizregister_lang["medication_references"]['anti_eleptika']),
	   	   			"12" => array('code'=>'antiemetika','label'=>$hospizregister_lang["medication_references"]['antiemetika']),
	   	   			//"13" => array('code'=>'antibiotika','label'=>$hospizregister_lang["medication_references"]['antibiotika']),
	   	   			"14" => array('code'=>'magenschutz','label'=>$hospizregister_lang["medication_references"]['magenschutz'])
	   	    );
 
           //ISPC-2496 Ancuta 02.12.2019 - Added values from 15 to 21
           //ISPC-2496 Ancuta 02.12.2019 - Commented values: BL_Analgetika,BL_Co_Analgetika,BL_Anxiolytika,BL_Sedativa,BL_Neuroleptika,BL_Anti_Biotika 
	   	   $texts['medication_references_all']  = array(
	   	   			//"1" => array('code'=>'analgetika','label'=>$hospizregister_lang["medication_references"]['analgetika'], 'dgp_export'=>'BL_Analgetika' ),
	   	   			"2" => array('code'=>'who2','label'=>$hospizregister_lang["medication_references"]['who2'], 'dgp_export'=>'BL_Opioide_WHO_Stufe_2'),
	   	   			"3" => array('code'=>'who','label'=>$hospizregister_lang["medication_references"]['who']), 'dgp_export'=>'BL_Opioide_WHO_Stufe_3',
	   	   			"4" => array('code'=>'nicht_opioide','label'=>$hospizregister_lang["medication_references"]['nicht_opioide'], 'dgp_export'=>'BL_Nicht_Opioide'),
	   	   			//"5" => array('code'=>'co_analgetika','label'=>$hospizregister_lang["medication_references"]['co_analgetika'], 'dgp_export'=>'BL_Co_Analgetika'),
	   	   			"6" => array('code'=>'steroide','label'=>$hospizregister_lang["medication_references"]['steroide'], 'dgp_export'=>'BL_Steroide'),
	   	   			//"7" => array('code'=>'anxiolytika','label'=>$hospizregister_lang["medication_references"]['anxiolytika'], 'dgp_export'=>'BL_Anxiolytika'),
	   	   			"8" => array('code'=>'laxantien','label'=>$hospizregister_lang["medication_references"]['laxantien'], 'dgp_export'=>'BL_Laxantien'),
	   	   			//"9" => array('code'=>'sedativa','label'=>$hospizregister_lang["medication_references"]['sedativa'], 'dgp_export'=>'BL_Sedativa'),
	   	   			//"10" => array('code'=>'neuroleptika','label'=>$hospizregister_lang["medication_references"]['neuroleptika'], 'dgp_export'=>'BL_Neuroleptika'),
	   	   			"11" => array('code'=>'anti_eleptika','label'=>$hospizregister_lang["medication_references"]['anti_eleptika'], 'dgp_export'=>'BL_Anti_Epileptika'),
	   	   			"12" => array('code'=>'antiemetika','label'=>$hospizregister_lang["medication_references"]['antiemetika'], 'dgp_export'=>'BL_Anti_Emetika'),
	   	   			//"13" => array('code'=>'antibiotika','label'=>$hospizregister_lang["medication_references"]['antibiotika'], 'dgp_export'=>'BL_Anti_Biotika'),
	   	   			"14" => array('code'=>'magenschutz','label'=>$hospizregister_lang["medication_references"]['magenschutz'], 'dgp_export'=>'BL_Magenschutz'),
	   	   			"15" => array('code'=>'secretioninhibiting_sub','label'=>$hospizregister_lang["medication_references"]['secretioninhibiting_sub'], 'dgp_export'=>'BL_Sekretionshemmend'),
	   	   			"16" => array('code'=>'benzodiazepines','label'=>$hospizregister_lang["medication_references"]['benzodiazepines'], 'dgp_export'=>'BL_Benzodiazepine'),
	   	   			"17" => array('code'=>'antidepressants','label'=>$hospizregister_lang["medication_references"]['antidepressants'], 'dgp_export'=>'BL_Antidepressiva'),
	   	   			"18" => array('code'=>'antipsychotics','label'=>$hospizregister_lang["medication_references"]['antipsychotics'], 'dgp_export'=>'BL_Antipsychotika'),
	   	   			"19" => array('code'=>'anti_infectives','label'=>$hospizregister_lang["medication_references"]['anti_infectives'], 'dgp_export'=>'BL_Antiinfektiva'),
	   	   			"20" => array('code'=>'anticoagulants','label'=>$hospizregister_lang["medication_references"]['anticoagulants'], 'dgp_export'=>'BL_Antikoagulantien'),
	   	   			"21" => array('code'=>'other_meds','label'=>$hospizregister_lang["medication_references"]['other_meds'], 'dgp_export'=>'BL_Sonstige'),
	   	       
	   	    );
	   	   
	   	   $texts['symptoms']  = array(
		   	  "1" => array('code'=>'schmerzen','label'=>$hospizregister_lang["symptoms"]['schmerzen']),
		   	  "2" => array('code'=>'ubelkeit','label'=>$hospizregister_lang["symptoms"]['ubelkeit']),
		   	  "4" => array('code'=>'erbrechen','label'=>$hospizregister_lang["symptoms"]['erbrechen']),
		   	  "5" => array('code'=>'luftnot','label'=>$hospizregister_lang["symptoms"]['luftnot']),
		   	  "6" => array('code'=>'verstopfung','label'=>$hospizregister_lang["symptoms"]['verstopfung']),
		   	  "7" => array('code'=>'swache','label'=>$hospizregister_lang["symptoms"]['swache']),
		   	  "8" => array('code'=>'appetitmangel','label'=>$hospizregister_lang["symptoms"]['appetitmangel']),
		   	  "9" => array('code'=>'mudigkeit','label'=>$hospizregister_lang["symptoms"]['mudigkeit']),
		   	  "10" => array('code'=>'dekubitus','label'=>$hospizregister_lang["symptoms"]['dekubitus']),
		   	  "11" => array('code'=>'hilfebedarf','label'=>$hospizregister_lang["symptoms"]['hilfebedarf']),
		   	  "12" => array('code'=>'depresiv','label'=>$hospizregister_lang["symptoms"]['depresiv']),
		   	  "13" => array('code'=>'angst','label'=>$hospizregister_lang["symptoms"]['angst']),
		   	  "14" => array('code'=>'anspannung','label'=>$hospizregister_lang["symptoms"]['anspannung']),
	   	      "60" => array('code'=>'unruhe','label'=>$hospizregister_lang["symptoms"]['unruhe']),
	   	      "15" => array('code'=>'desorientier','label'=>$hospizregister_lang["symptoms"]['desorientier']),
	   	      "16" => array('code'=>'versorgung','label'=>$hospizregister_lang["symptoms"]['versorgung']),
		   	  "17" => array('code'=>'umfelds','label'=>$hospizregister_lang["symptoms"]['umfelds']),
		   	  "18" => array('code'=>'sonstige_probleme','label'=>$hospizregister_lang["symptoms"]['sonstige_probleme'])
 
	   	    );
	   	   $texts['symptoms_values']  = array(
	   	   		"1" => $hospizregister_lang["symptoms_values"]['no'],
	   	   		"2" => $hospizregister_lang["symptoms_values"]['light'],
	   	   		"3" => $hospizregister_lang["symptoms_values"]['medium'],
	   	   		"4" => $hospizregister_lang["symptoms_values"]['strongly'],
	   	   );
	   	   
	   	   $texts['state']  = array(
	   	   		"1" => "nein",
	   	   		"2" => "fortgesetzt",
	   	   		"3" => "initiiert",
	   	   );
	   	   
	   	   $texts['condition']  = array(
	   	   		"1" => "ja",
	   	   		"2" => "nein",
	   	   );
	   	   
	   	   $texts['condition']  = array(
	   	   		"1" => "ja",
	   	   		"2" => "nein",
	   	   );
	   	   
	   	   $texts['therapieende']  = array(
	   	   		"" => "--",
	   	   		"1" => "Verstorben",
	   	   		"2" => "Verlegung",
	   	   		"3" => "Entlassung",
	   	        "5" => "Stabilisierung unter erfolgtem Versorgungs- / Behandlungsangebot",
	   	   		"6" => "Änderung der Betreuung",
	   	   		"4" => "Sonstiges",
	   	   );
	   	   
	   	   $texts['zufriedenheit_mit']  = array(
	   	   		"" => "--",
	   	   		"1" => "sehr schlecht",
	   	   		"2" => "schlecht",
	   	   		"3" => "mittel",
	   	   		"4" => "gut",
	   	   		"5" => "sehr gut"
	   	   );
 
	   	   $texts['acp']  = array(
	   	   		"1" => "pverfuegung",
	   	   		"2" => "vollmacht",
	   	   		"3" => "betreuungsurkunde",
	   	   		"4" => "acp",
 
	   	   );
 
	   	   $texts['ecog']  = array(//this key-numbers are not the same ones from http://ecog-acrin.org/resources/ecog-performance-status
	   	       '1' => 'Normale Aktivität',
	   	       '2' => 'Gehfähig, leichte Arbeit möglich',
	   	       '3' => 'nicht arbeitsfähig, kann > 50%  der Wachzeit aufstehen',
	   	       '4' => 'begrenzte Selbstversorgung, > 50% Wachzeit bettlägrig',
	   	       '5' => 'Pflegebedürftig, permanent bettlägerig',
	   	   );
	   	   
			return $texts;
	   }
	   
	   
	   
   public function findByIpid( $ipid = '', $hydrationMode = Doctrine_Core::HYDRATE_ARRAY )
   {
       if (empty($ipid) || !is_string($ipid)) {
   
           return;
   
       } else {
           return $this->getTable()->findBy('ipid', $ipid, $hydrationMode);
   
       }
   }
	   

   /**
    * 
    * @param string $ipid
    * @param string $form_type
    * @param array $data
    * @param unknown $hydrationMode
    * @return Doctrine_Record
    * @deprecated
    * @see DgpKern::findOrCreateOneByIpidAndFormTypeAndPatientReadmissionID()
    */
   public function findOrCreateOneByIpidAndFormType($ipid = '', $form_type = 'adm', array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
   {       
       if (  ! $entity = $this->getTable()->findOneByIpidAndFormType($ipid, $form_type)) {
           
           $entity = $this->getTable()->create(array( 'ipid' => $ipid, 'form_type' => $form_type));
       }
       
       unset($data[$this->getTable()->getIdentifier()]);

       $entity->fromArray($data); //update
   
       $entity->save(); //at least one field must be dirty in order to persist
   
       return $entity;
   }
   
   
   
   public function findOrCreateOneByIpidAndFormTypeAndPatientReadmissionID($ipid = '', $form_type = 'adm', $patient_readmission_ID = '0', array $data = array())
   {
       if (  ! $entity = $this->getTable()->findOneByIpidAndFormTypeAndPatientReadmissionId($ipid, $form_type, $patient_readmission_ID)) {
            
           $entity = $this->getTable()->create([
               'ipid'                   => $ipid,
               'form_type'              => $form_type,
               'patient_readmission_ID' => $patient_readmission_ID,
           ]);
       }
        
       unset($data[$this->getTable()->getIdentifier()]);
   
       $entity->fromArray($data); //update
        
       $entity->save(); //at least one field must be dirty in order to persist
             
       return $entity;
   }
	    
	
	
	
	/**
	 * @cla on 06.06.2018
	 * 
	 * this is fn is the new patients_filled_status .. V3 
	 * 
	 * this ISPC Filled logic is not the same as the XSD logic
     * so even thus hospiz-palliativ-register.de (the .xsd file) allows for 0 or NULL to be sent on all this field...
     * ispc dosen't like sendig this as 0's or null ..
     * not a real good ideea because some statistics is(or will be) caculated for ZEROS or NULL
     * 
     * 
     * if is_null($fillFinished), then any started form
     * if $fillFinished == true then the those with adm+dis + the ancuta IF's   === call findFormsCompletedOfIpids
     * if $fillFinished == false then the those with adm+dis + NOT the ancuta IF's === call findFormsNotCompletedOfIpids
     * 
     * @update 14.06.2018
     * The form can only be considered completed if and only if the patient is discharged ? yup
     * + IF patient_readmission_ID .. filled or not filled
     * 
     * if you just want one form/ipid, it is up to you to intersect this with the FALLs you are looking for, via patient_readmission_ID
     * 
     * @param array $ipids
     * @return void|multitype:Ambigous <>
     */
	public static function findFormsOfIpids($ipids =  array() , $fillFinished = null)
	{
	    if (empty($ipids) || ! is_array($ipids)) {
	        return; //fail-safe
	    }
        
        $q = Doctrine_Query::create()
        ->select('dk.*')
        ->from('DgpKern dk')
        ->addSelect('tdk.*')
        ->leftJoin("dk.TwinDgpKern tdk ON (tdk.id = dk.twin_ID AND tdk.twin_ID = dk.id)") //to be sure we double check
        ->whereIn("dk.ipid", $ipids)
        ->andWhere("dk.form_type = 'adm' ")
        ;
        
        if ( ! is_null($fillFinished)) {
            $q->andWhere("tdk.id IS NOT NULL");
        }
        
        $kern_forms =  $q->fetchArray();
        
        if (empty($kern_forms)) {
            return; //you have no forms
        }
        
        $ipis_with_dgp =  array_column($kern_forms, 'ipid');
        
        $filtered_forms = array(); // this is the result
        
        foreach ($kern_forms as $k => $form) {
            
            $part_discharge = $form['TwinDgpKern']; // this is the discharge part.. cause we have  ->andWhere("dk.form_type = 'adm' ")
            unset($form['TwinDgpKern']);
            
            $part_admission = $form;
            
            if ( ! is_null($fillFinished)) {//findFormsCompletedOfIpids OR findFormsNotCompletedOfIpids
                
                if ($part_admission['ecog'] == '0'
                    || empty($part_admission['begleitung'])
                    || empty($part_admission['kontaktes'])
                    || empty($part_admission['patient_readmission_ID']) //if this is empty then.. 
                )
                {
                    
                    if ($fillFinished == false) { //findFormsNotCompletedOfIpids
                        $filtered_forms[] = $kern_forms[$k];
                    }
                    
                    continue;
                    
                }
                
                if ($part_discharge['chemotherapie'] == '0'
                    || $part_discharge['zufriedenheit_mit'] == '0'
                    || $part_discharge['strahlentherapie'] == '0'
                    || $part_discharge['therapieende'] == '0'
                    || empty($part_discharge['begleitung'])
                    || empty($part_discharge['patient_readmission_ID'])
                )
                {
                    if ($fillFinished == false) {//findFormsNotCompletedOfIpids
                        $filtered_forms[] = $kern_forms[$k];
                    }
                    
                    continue;
                }
                
                if ($fillFinished == true) { //findFormsCompletedOfIpids
                    $filtered_forms[] = $kern_forms[$k];
                }
            
            } else {
                
                //all the started forms
                $filtered_forms[] = $kern_forms[$k];
            }
        
        }//endforeach;

        return $filtered_forms;        
	}
	
	
	/**
	 * return findFormsOfIpids with adm+dis + the ancuta IF's
	 * 
	 * @param multiple:string $ipids
	 * @return Ambigous <void, multitype:Ambigous >
	 */
	public static function findFormsCompletedOfIpids($ipids =  array()) {
	    return self::findFormsOfIpids($ipids , $fillStatus  = true );
	}
	
	/**
	 * return findFormsOfIpids with adm+dis + NOT the ancuta IF's
	 * 
	 * @param multiple:string $ipids
	 * @return Ambigous <void, multitype:Ambigous > 
	 */
	public static function findFormsNotCompletedOfIpids($ipids =  array()) {
	    return self::findFormsOfIpids($ipids , $fillStatus = false );
	}
	
	
	
	/**
	 * return ipids that have at least a form started
	 * 
	 * @param number $clientid
	 * @return void|multitype:
	 */
	public static function findIpidsOfClient ($clientid = 0)
	{
	    
	    if (empty($clientid)) {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	    }

	    
	    $all_client_patients = Doctrine_Query::create()
	    ->select('p.id, p.ipid')
	    ->from('PatientMaster p')
	    ->leftJoin("p.EpidIpidMapping e")
	    ->where('e.clientid = ?' , $clientid)
	    ->andWhere('p.isdelete = 0')
	    ->andWhere("p.isstandbydelete = 0")
	    ->fetchArray()
	    ;
	    
	    if (empty($all_client_patients)) {
	        return; //fail-safe
	    }
	    
	    $patients_ipids =  array_column($all_client_patients, 'ipid');
	    
	    $client_patients_in_dgp = Doctrine_Query::create()
	    ->select('ipid')
	    ->from('DgpKern')
	    ->whereIn('ipid', $patients_ipids)
	    ->groupBy('ipid')
	    ->fetchArray()
	    ;
	    
	    if (empty($client_patients_in_dgp)) {
	        return; //fail-safe
	    }
	    
	    $patients_ipids =  array_column($client_patients_in_dgp, 'ipid'); // new filtered ipids
	    
	    return $patients_ipids;
	    
	}
	

}
?>