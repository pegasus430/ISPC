<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientsShare', 'SYSDAT');

	class PatientsShare extends BasePatientsShare {

	    
	    public $sharing_files_counter = 0;
	    //ISPC-2614 Ancuta 16.07.2020 added new param
		public function save_shortcuts($sid, $post, $new_ipid = false,$intense_system  =  null)
		{
			if($post['combine'] == '0' && $new_ipid)
			{
				$ipid = $new_ipid;
			}
			else
			{
				$ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($post['patientid']));
			}
			$marked_patients = new PatientsMarked();
			$marked_patient = $marked_patients->share_get($sid);

			$link_update[] = '999999999';
			//insert shortcuts in table
			if(!empty($post['shortcut']))
			{
				foreach($post['shortcut'] as $k_shortcut => $v_shortcut)
				{
					$save_target_shares = new PatientsShare();
					$save_target_shares->source = $marked_patient[0]['ipid'];
					$save_target_shares->target = $ipid;
					$save_target_shares->shortcut = $v_shortcut;
					$save_target_shares->shared = '1';
					$save_target_shares->save();

					$link_update[] = $save_target_shares->id;
					$shortcuts['st'][] = $v_shortcut; //source2target
				}
			}

			if(!empty($post['shortcut_source']))
			{
				foreach($post['shortcut_source'] as $k_sshortcut => $v_sshortcut)
				{
					$save_target_shares = new PatientsShare();
					$save_target_shares->source = $ipid;
					$save_target_shares->target = $marked_patient[0]['ipid'];
					$save_target_shares->shortcut = $v_sshortcut;
					$save_target_shares->shared = '1';
					$save_target_shares->save();

					$link_update[] = $save_target_shares->id;
					$shortcuts['ts'][] = $v_sshortcut; //target2source
				}
			}

			// finnaly ... set status accepted ...
			$save_marked_status = Doctrine::getTable('PatientsMarked')->findOneById($sid);
			$source_ipid = $save_marked_status->ipid;
			$save_marked_status->status = 'a';
			$save_marked_status->save();

			//start copy verlauf data!!!
			$pc = new PatientCourse();
			$cs = new Courseshortcuts();

			foreach($shortcuts as $k_direction => $shortcuts)
			{
				if($k_direction == 'st' && empty($post['source_from_now']))
				{
					//source2target
					$st = $pc->copy_verlauf_records($save_marked_status['ipid'], $ipid, $cs->getShortcutsMultiple($shortcuts));
				}
				else if($k_direction == 'ts' && $post['patientid'] && empty($post['target_from_now']))
				{
					//target2source
					$ts = $pc->copy_verlauf_records($ipid, $save_marked_status['ipid'], $cs->getShortcutsMultiple($shortcuts));
				}
			}
			//end copy verlauf data!!!
			//and ... link patients
			$save_patient_link = new PatientsLinked();
			$save_patient_link->intense_system= $intense_system;//ISPC-2614 Ancuta 16.07.2020
			$save_patient_link->source = $source_ipid;
			$save_patient_link->target = $ipid;
			$save_patient_link->copy_files = $marked_patient[0]['copy_files'];
			if(in_array("3",$marked_patient[0]['copy_options']))
			{
    			$save_patient_link->copy_meds = "1";
			} 
			else 
			{
    			$save_patient_link->copy_meds = "1";
			}
			
			$save_patient_link->save();
			$link_id = $save_patient_link->id;
			if($save_patient_link)
			{
				$q = Doctrine_Query::create()
					->update('PatientsShare')
					->set('link', $link_id)
					->where('link ="0"')
					->andWhereIn('id', $link_update);
				$q->execute();

				if($q)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function delete_shortcuts($link, $source)
		{
			$Q = Doctrine_Query::create()
				->delete('PatientsShare')
				->where('source="' . $source . '"')
				->andWhere('link="' . $link . '"');
			$Q->execute();

			return $Q;
		}

		public function insert_new_shortcuts($source, $target, $link, $shortcuts)
		{
			foreach($shortcuts as $shortcut)
			{
				$ins_sh = new PatientsShare();
				$ins_sh->link = $link;
				$ins_sh->source = $source;
				$ins_sh->target = $target;
				$ins_sh->shortcut = $shortcut;
				$ins_sh->shared = '1';
				$ins_sh->save();

				$count_ins[] = $ins_sh->id;
			}

			if(count($count_ins) == count($shortcuts))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function patient_shortcuts_shared($source)
		{
			$shared = Doctrine_Query::create()
				->select('*')
				->from('PatientsShare')
				->where('source = "' . $source . '"')
				->andWhere('shared="1"');
			$shared_shortcuts = $shared->fetchArray();

			if($shared_shortcuts)
			{
				return $shared_shortcuts;
			}
		}

		public function shortcuts_shared_between($source, $target)
		{
			$shared = Doctrine_Query::create()
				->select('*')
				->from('PatientsShare')
				->where('source = "' . $source . '"')
				->andWhere('target = "' . $target . '"')
				->andWhere('shared="1"');
			$shared_shortcuts = $shared->fetchArray();

			if($shared_shortcuts)
			{
				return $shared_shortcuts;
			}
		}

		public function patient_course_shared($pcid)
		{
			$pc = Doctrine_Query::create()
				->select('*, AES_DECRYPT(course_type,"' . Zend_Registry::get('salt') . '") as course_type,')
				->from('PatientCourse')
				->where('id="' . $pcid . '"')
				->fetchArray();

			if($pc)
			{
				$q = Doctrine_Query::create()
					->select('*, AES_DECRYPT(course_type,"' . Zend_Registry::get('salt') . '") as course_type,')
					->from('PatientCourse')
					->where('course_type="' . Pms_CommonData::aesEncrypt($pc[0]['course_type']) . '"')
					->andWhere('source_ipid = "' . $pc[0]['ipid'] . '"')
					->fetchArray();

				foreach($q as $q_pat => $q_pat_value)
				{
					$patients[$q_pat_value['course_type']][] = $q_pat_value['ipid'];
					$patients[$q_pat_value['course_type']] = array_unique($patients[$q_pat_value['course_type']]);
				}
				return $patients;
			}
		}

		//check if shortcut is shared
		public function check_shortcut($ipid, $shortcut)
		{
			$shared = Doctrine_Query::create()
				->select('*')
				->from('PatientsShare')
				->where('source = "' . $ipid . '" or target = "' . $ipid . '"')
				->andWhere('shortcut = "' . $shortcut . '"')
				->andWhere('shared="1"');

			$shared_shortcuts = $shared->fetchArray();

			if($shared_shortcuts)
			{
				foreach($shared_shortcuts as $shared_shortcut)
				{
					$ssipids[$shared_shortcut['shortcut']][] = $shared_shortcut['target'];
					$ssipids[$shared_shortcut['shortcut']] = array_unique($ssipids[$shared_shortcut['shortcut']]);
				}
				return $ssipids;
			}
			else
			{
				return false;
			}
		}

		
		
		public function sharing_files()
		{
            set_time_limit(0);

		    $linked_patients = Doctrine_Query::create()
		    ->select("*")
            ->from('PatientsLinked')
		    ->fetchArray();
		    
		    
		    if (empty($linked_patients)) {
		        return; //fail-safe, nothing to share
		    }
		    
		    $source_ipids = [];
		    //$source_ipids[] = "9999999";
		    foreach ($linked_patients as $k=>$pl) {
		        if ($pl['copy_files'] == "1") {
    		        $source_ipids[] = $pl['source'];
    		        if (strlen($pl['target']) > 0 ) { 
    			        $linked_array[$pl['source']][] = $pl['target']; 
	    		        $sharing_create_date[$pl['source'].'_'.$pl['target']] = $pl['create_date'];
    		        }
		        }
		    }
		    
		    if (empty($source_ipids)) {
		        return; //fail-safe, nothing to share
		    }
		    
		    $pf_share_array = Doctrine_Query::create()
		    ->select("*")
            ->from('PatientsMarked ')
		    ->where("status = 'a'")
// 		    ->andWhere("copy_files = '1'")
		    // for testing, only allow pms !!!!!!!!
		    //->andWhere("source = '1' or target ='1' ")
		    
		    ->andWhereIn("ipid", $source_ipids)
		    ->fetchArray();

		    
		    if (empty($pf_share_array)) {
		        return; //fail-safe, nothing to share
		    }
		    
		    
		    $patients_sharing_files = [];
		    //$patients_sharing_files[] = "99999999";
		    foreach ($pf_share_array as $lk=>$spf) {
		        $patients_sharing_files[] = $spf['ipid'];		        
		    }

		    
		    foreach ($linked_array as $source_ipid=>$t_ipids)
		    {
		        if (in_array($source_ipid,$patients_sharing_files))
		        {
		            foreach ($t_ipids as $target_ipid)
		            {
		                $this->save_files($source_ipid, $target_ipid); // from inital sourxe to target

		                if (strlen($target_ipid) > 0 ) {
		                  $this->save_files($target_ipid, $source_ipid,$sharing_create_date[$source_ipid.'_'.$target_ipid]); // from target to source - if file was added in target after share date
    		            }
// 		                $patients_to_share_dbg[$source_ipid][] = $target_ipid;
// 		                $patients_to_share_dbgd[$target_ipid][] = $source_ipid;
		            }
		        }
		    }
// 		    print_r($patients_to_share_dbgd); exit;
		}
		
		public function save_files($source_ipid, $target_ipid,$create_date =  false)
		{
		    set_time_limit(0);
		    
		    $ipids = array($source_ipid,$target_ipid);
		    
		    $client_q = Doctrine_Query::create()
		    ->select("ipid,clientid")
		    ->from('EpidIpidMapping')
		    ->whereIn('ipid',$ipids);
		    $client_arr = $client_q->fetchArray();
		    
            foreach($client_arr as $k=>$cl_data)
            {
                if($cl_data['ipid'] == $source_ipid)
                {
                    $source_client = $cl_data['clientid'];
                } 
                elseif($cl_data['ipid'] == $target_ipid)
                {
                    $target_client = $cl_data['clientid'];
                }
            }	    
            $clients_ids =array($source_client,$target_client); 

            $clients_dq = Doctrine_Query::create()
            ->select("id,AES_DECRYPT(fileupoadpass,'" . Zend_Registry::get('salt') . "') as fileupoadpass")
			->from('Client')
            ->whereIn('id', $clients_ids)
            ->andWhere('isdelete = ?', 0);
            $clients_det_arr = $clients_dq->fetchArray();
            
            foreach($clients_det_arr as $k=>$cdetails)
            {
                if($cdetails['id'] == $source_client)
                {
                    $source_client_fpass = $cdetails['fileupoadpass'];
                } 
                else if($cdetails['id'] == $target_client)
                {
                    $target_client_fpass = $cdetails['fileupoadpass'];
                }
            }
                
		    $patient_q = Doctrine_Query::create()
		    ->select("*,create_user,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
				AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
				AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
			->from('PatientFileUpload')
			->where('ipid="' . $source_ipid . '"')
			->andWhere('source_id = "0" ');
			if ($create_date){
    			$patient_q->andWhere(' DATE(create_date) >=  DATE("'.$create_date.'") ');
			}
			$patient_q->orderBy('create_date ASC');
		    $source_files = $patient_q->fetchArray();
		    
		    if (empty($source_files)) {
		        return; //fail-safe, nothing to share
		    }
		    
		    $source_files_ids = [];
		    //$source_files_ids[]= "99999999999";
		    foreach ($source_files as $k=>$f_data)
		    {
		        $source_files_ids[] = $f_data['id'];
		    }
		    
		    // get existing files from target ipid
		    $patient_t_q = Doctrine_Query::create()
		    ->select("*")
			->from('PatientFileUpload')
			->where('ipid="' . $target_ipid . '"')
			->andWhereIn('source_id',$source_files_ids)
			->orderBy('create_date ASC');
		    $target_files = $patient_t_q->fetchArray();
		    
		    
		    $existing_shared_files = [];
		    //$existing_shared_files[] = "9999999999";
		    
            if (!empty($target_files))
            {
                foreach($target_files as $k=>$tfile)
                {
                    $existing_shared_files[] = $tfile['source_id'];
                }
            }
            
            //  print_R($existing_shared_files);
		    // exiting file
		    foreach($source_files as $k=>$f_data)
		    {
                //print_r($f_data['create_user']);
		        if (empty($existing_shared_files) || ! in_array($f_data['id'], $existing_shared_files))
		        {
		            $this->sharing_files_counter++;

		            /*
		             * you insert the file in the target before knowing if it can be downloaded from ftp ... 
		             */
		            
        		    $pf_insert = new PatientFileUpload();
        		    $pf_insert->title = Pms_CommonData::aesEncrypt($f_data['title']);
        		    $pf_insert->file_name = Pms_CommonData::aesEncrypt($f_data['file_name']);
        		    $pf_insert->ipid = $target_ipid;
        		    $pf_insert->source_ipid = $source_ipid;
        		    $pf_insert->source_id = $f_data['id'];
        		    $pf_insert->isdeleted= $f_data['course_date'];
        		    $pf_insert->file_type = Pms_CommonData::aesEncrypt($f_data['file_type']);
        		    $pf_insert->recordid = $f_data['recordid'];
        		    $pf_insert->tabname = $f_data['tabname'];
        		    $pf_insert->system_generated = $f_data['system_generated'];
        		    $pf_insert->isdeleted = $f_data['isdeleted'];
        		    $pf_insert->change_date = $f_data['change_date'];
        		    $pf_insert->change_user = $f_data['change_user'];
        		    $pf_insert->create_date = $f_data['create_date'];
        		    if($f_data['create_user']){
            		    $pf_insert->create_user = $f_data['create_user'];
        		    } 
        		    $pf_insert->save();
        		    
        		    $file_id = $pf_insert->id;
        		    
        		    /* ------------------------------------------------------------*/
        		    /* ----------------- FILES UPLOAD    --------------------------*/
        		    /* ------------------------------------------------------------*/
        		    
        		    
        		    /* -------------- READ / DOWNLOAD-----------------------*/
                    // $fname = Pms_CommonData::aesDecrypt ( $row ['file_name'] );
        		    $fname = $f_data['file_name'];// this is decripted
        		    
        		    // FTP stuff
        		    
        		    $working_dir = PUBLIC_PATH; // change the path to fit your websites document structure
        		    
        		    $explo = explode ( "/",$fname );
        		    
        		    $fdname = $explo [0];
        		    $flname = utf8_decode ( $explo [1] );
        		    
        		    // medication plan pdf issue only
        		    if ($f_data ['title'] == "Medikamentenplan" && strpos ( $fname, '.zip' ) !== false) {
        		        // overwrite folder name so the file can be downloaded
        		        $fdname = str_replace ( ".zip", "", $flname );
        		        	
        		        // overwrite the file name so the file can be served to user
        		        $flname = "medication.pdf";
        		    }
        		    
        		    
//         			$con_id = Pms_FtpFileupload::ftpconnect ();
        		    
        		    
        		    
//         		    if ($con_id) {
//         		        $old = false;
//         		        if ($_REQUEST ['old']) {
//         		            $old = true;
//         		        }
//         		        $upload = Pms_FtpFileupload::filedownload ( $con_id, $working_dir.'/uploads/' . $fdname . '.zip', 'uploads/' . $fdname . '.zip', false, $source_client );
//         		        Pms_FtpFileupload::ftpconclose ( $con_id );
//         		    }
        		    
        		    //$logininfo= new Zend_Session_Namespace('Login_Info');
        		    // $file_password = $logininfo->filepass;
        		    $file_password = $source_client_fpass;
        		    
//         		    $cmd = "unzip -P " . $file_password . " ".$working_dir."/uploads/" . $fdname . ".zip;";
        		    
//         		    exec ( $cmd );
        		    
        		    //echo $cmd.'<br />';
        		    
        		    $path = $working_dir .'/uploads/'. $fdname . "/";
        		    $fullPath = $path . $flname;
        		    
        		    $old = $_REQUEST['old'] ? true : false;
        		    if (($path = Pms_CommonData::ftp_download('uploads/' . $fdname . '.zip' , $file_password , $old , $source_client , $fname, "PatientFileUpload", $f_data['id'])) === false){
        		    	//failed to download file
        		    	continue;
        		    }
        		    
        		    $fullPath = $path . "/" . $flname;
        		    
        		    
        		    /* -------------- WRITE -----------------------*/
        		    
//         		    $dir_name = Pms_CommonData::uniqfolder ( $working_dir.'/uploads/' );
        		    $dir_name = Pms_CommonData::uniqfolder ( PDF_PATH );
        		    
//         		    $dir_path =  $working_dir.'/uploads/'.$dir_name;
        		    $dir_path =  PDF_PATH . '/' . $dir_name;
        		    
        		    $file_path = $dir_path.'/'.$flname;
        		    
        		    copy($fullPath, $file_path);
        		    		    
        		    
        		    //re-make the zip with the new cleint password
        		    $result = Pms_CommonData::ftp_put_queue($file_path , 'uploads', $is_zipped = NULL, $foster_file = false , $ftpclientid = $target_client, $ftpfilepass = $target_client_fpass);
        		    

        		    //echo $fullPath.'<br />';
        		    //echo $file_path.'<br />';
        		    
        		    //$dir_name = $fdname;
        		    //$dir_path = $path;
        		    
        		    //echo $file_path;
        		    
        		    //file_put_contents ( $file_path, $filecontents );
        		    
        		    //$logininfo= new Zend_Session_Namespace('Login_Info');
        		    
//         		    $cmd = "cd ".$working_dir."; zip -9 -r -P ".$target_client_fpass." uploads/".$dir_name.".zip  uploads/".$dir_name ."; rm -r ".$dir_path.";";
        		    
        		    //echo $cmd;
        		    
//         		    exec($cmd);
        		    
        		    //echo $cmd.'<br />';
        		    
//         		    $zipname = $dir_name.".zip";
        		    
        		    
        		    //echo $working_dir.'/uploads/'.$zipname;
        		    
        		    //echo $working_dir.'/uploads/'.$zipname.'<br />';
        		    //echo 'uploads/'.$zipname.'<br />';
        		    
//         		    $con_id = Pms_FtpFileupload::ftpconnect();
//         		    if($con_id)
//         		    {
//         		        $upload = Pms_FtpFileupload::fileupload($con_id, $working_dir.'/uploads/'.$zipname,'uploads/'.$zipname, false, $target_client);
//         		        Pms_FtpFileupload::ftpconclose($con_id);
//         		    }
        		    
        		    
        		    
        		    
//         		    $mod = Doctrine::getTable('PatientFileUpload')->find($file_id);
// 					$mod->file_name = Pms_CommonData::aesEncrypt($dir_name.'/'.$flname);
// 					$mod->save();
        		    $pf_insert->file_name = Pms_CommonData::aesEncrypt($dir_name.'/'.$flname);
        		    $pf_insert->save();
					
					
					
		        }
		    }
		}
		
		
		
		
		
		
		
		public function share_shortcuts_old(){
		    set_time_limit(0);

		    $sharing_date = "2016-06-17 00:00:00";
		    
		    $clients_array = array("187","144","121","167","185", "157","131", "142", "143");

		    $actpatients = Doctrine_Query::create()
		    ->select("p.ipid,p.isdelete")
		    ->from('PatientMaster p');
		    $actpatients->leftJoin("p.EpidIpidMapping e");
		    $actpatients->andWhereIn('e.clientid',$clients_array);
		    $actipidarray = $actpatients->fetchArray();

		    $ipids_str .= '"99999",';
		    foreach($actipidarray as $k=>$cp){
		        if($cp['isdelete'] != '1'){
    		        $ipids[] = $cp['ipid'];
    		        $ipids_str .= '"'.$cp['ipid'].'",';
		        } else {
		            $del_ipids[]= $cp['ipid'];
		        }
		    }
		    $ipids_str = substr($ipids_str,0,-1);
		    if(empty($ipids)){
		        $ipids[]="9999999999";
		    }
		    if(empty($del_ipids)){
		        $del_ipids[]="9999999999";
		    }

		    
		    $patient_q = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientsLinked')
		    ->andWhere('source in ('.$ipids_str.') OR target in ('.$ipids_str.') ');
// 		    ->andWhereIn('source',$ipids)
// 		    ->OrWhereIn('target',$ipids)
            
            if($sharing_date){
                $patient_q->andWhere('create_date >= "'.$sharing_date.'" ');   
            }
		    $linked_patients = $patient_q->fetchArray();
		    
		    $source_ipids[] = "9999999";
		    foreach($linked_patients as $k=>$pl){
	            if(!in_array($pl['source'],$del_ipids)){
    	            $source_ipids[] = $pl['source'];
	            }
	                
	            if(!in_array($pl['target'],$del_ipids)){
	               $linked_array[$pl['source']][] = $pl['target'];
	               $sharing_create_date[$pl['source'].'_'.$pl['target']] = $pl['create_date'];
	            }
		    }
		    
		    $patient_qm = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientsMarked ')
		    ->where("status = 'a'")
		    ->andWhere("shortcuts != ''")
		    ->andWhereIn("ipid",$source_ipids);
		    $pf_share_array = $patient_qm->fetchArray();
		   
		    // check for both source and target - the sharing options
		    $patients_sharing_files[] = "99999999";
		    foreach($pf_share_array as $lk=>$spf){
		        $patients_sharing_files[] = $spf['ipid'];
		    }

		    $course_entry['st'] = 0;
		    $course_entry['ts'] = 0;
		    if(!empty($linked_array)){
		        
    		    foreach($linked_array as $source_ipid=>$t_ipids)
    		    {
    		        if(in_array($source_ipid,$patients_sharing_files))
    		        {
    		            foreach($t_ipids as $target_ipid)
    		            {
    		                if($source_ipid != $target_ipid && !in_array($target_ipid,$del_ipids)){
    		                    
        		                if(($course_entry['st']+ $course_entry['ts']) > 500 ){
//         		                    print_r($course_entry);
        		                    exit;
        		                }
        		                
        		                $course_entry['st'] += $this->share_shortcuts_s2t($source_ipid, $target_ipid, $sharing_create_date[$source_ipid.'_'.$target_ipid]); // from inital sourxe to target
        		                $course_entry['ts'] +=$this->share_shortcuts_t2s($target_ipid, $source_ipid, $sharing_create_date[$source_ipid.'_'.$target_ipid]); // from inital sourxe to target
        		                
    		                }
    		            }
    		        }
    		    }
    		    
//     		    print_r($course_entry);
		    }
		}
		
		
		
		public function share_shortcuts($specific_clients = false){
		    set_time_limit(0);

		    $file_location = APPLICATION_PATH . '/../public/run/';
		    
		    if($specific_clients){
    		    $lock_filename = 'share_pat_cl.lockfile';
		    } else {
    		    $lock_filename = 'share_pat.lockfile';
		    }
		    $lock_file = false;
		    
		    //check lock file
		    if(file_exists($file_location . $lock_filename))
		    {
		        //lockfile exists
		        
		        // get last modified date of file
		        $last_modified = filemtime($file_location . $lock_filename);
		        // get the curent time 
		        $current_time = time();
 	        
		        $file_minutes =  round(($current_time - $last_modified) / 60);
		        
                if($file_minutes > 180) //  If file older then 3 hours  delete file
                {
                    unlink($file_location . $lock_filename);
                    echo "File was deleted - as it was older then 3 hours";
                    exit;
                }
		        else
		        {
                    $lock_file = true;
    		        echo "function is currently running";
                    exit();
		        }
		        // ---------------
		        // --- EXIT -----
		        // ---------------
		    
		    }
		    else
		    {
		        //no lock file exists, create it
		        $handle = fclose(fopen($file_location . $lock_filename, 'x'));
		        $lock_file = false;
		    }
		    
		    //skip sharing only if lockfile exists
		    if(!$lock_file)
		    {
		        
                if($specific_clients){
                    
                    $sp_clients_array = array(); // TODO-1841 Ancuta replaced the array with a query - get all SH, BAY and RP CLients 28.09.2018
                    // TODO-2379 added BW Clients ( Ancuta 01.07.2019)
                    
                    $sp_clients_array = Doctrine_Query::create()
                        ->select("id,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,")
                        ->from('Client INDEXBY id')
                        ->where('isdelete = 0')
                        ->andWhere("(aes_decrypt(client_name,'encrypt') LIKE ? OR aes_decrypt(client_name,'encrypt') LIKE ?  OR aes_decrypt(client_name,'encrypt') LIKE ? OR aes_decrypt(client_name,'encrypt') LIKE ?)",array('SH_%','BAY_%','RP_%','BW_%'))
                        ->fetchArray();
                    
                    if(!empty($sp_clients_array)){
                        $clients_array = array_keys($sp_clients_array); 
                    }
                    else
                    {
        		        $clients_array = array(
                            //sh clients
        		            "187","144","121","167","185", "157","131", "142", "143",
                            //BAY clients
        		            "100","114","120","141","89","88","40","63","87","72","128","139","151","169",
        		            "154","148","172","188","173","182","180","153","145","190","146","181","183",
        		            "156","171","162","147","124","158","58","43","73","125","105","189","194",
        		            "60","71","41","50","191","57","106","186","74","37",
        		            //BW Clients 
        		            "70","93","96","104","119","129","224","271","281","298","302","308"
        		        );
                    }
                } else {
                    $clients_array = array();
                }
                // added BW clients to share  TODO-2379 ISPC: sync bug?
                // $bw_clients = array("70","93","96","104","119","129","224","271","281","298","302","308");
                
    		    $patient_slq = Doctrine_Query::create()
    		    ->select("*")
    		    ->from('PatientsShareLog')
    		    ->orderBy('create_date ASC');
    		    $sh_log_patients = $patient_slq->fetchArray();
    		    
    		    foreach($sh_log_patients as $k=>$log_data){
    		        if(!in_array($log_data['source_ipid'],$change_dates['conncetion'][$log_data['ipid']])){
        		        $log['connections'][$log_data['ipid']][] = $log_data['source_ipid'];
    		        }
    		        $change_dates_target[$log_data['ipid']][] = $log_data['create_date'];
    		        $change_dates_source[$log_data['source_ipid']][] = $log_data['create_date'];
    		        $log_ipids[] = $log_data['ipid'];
    		        $log_ipids[] = $log_data['source_ipid'];
    		    }
    		    $log_ipids = array_values(array_unique($log_ipids));
    		    
    		    foreach($change_dates_target as $log_ipidt=>$log_dates_target){
    		        asort($log_dates_target);
    		        $last_changes_target[$log_ipidt] = end($log_dates_target);
    		    }
    		    
    		    foreach($change_dates_source as $log_ipids=>$log_dates_source){
    		        asort($log_dates_source);
    		        $last_changes_source[$log_ipids] = end($log_dates_source);
    		    }
    		    
    		    $patient_q = Doctrine_Query::create()
    		    ->select("*")
    		    ->from('PatientsLinked');
                if($sharing_date){
                    $patient_q->andWhere('create_date >= "'.$sharing_date.'" ');   
                }
    		    $linked_patients = $patient_q->fetchArray();
    		    
    		    foreach($linked_patients as $kpl=>$vpl ){
    		        if( strlen($vpl['source']) > 0){
        		        $all_ipids[] = $vpl['source'];
    		        }
    		        
    		        if( strlen($vpl['target']) > 0){
    		          $all_ipids[] = $vpl['target'];
    		        }
    		    }
    		    $all_ipids = array_values(array_unique($all_ipids));
                
                if(empty($all_ipids)){
                    $all_ipids[]="99999999";
                }
                
    		    $actpatients = Doctrine_Query::create()
    		    ->select("p.ipid,p.isdelete,p.last_update")
    		    ->from('PatientMaster p')
    		    ->whereIn('ipid',$all_ipids);

    		    if(!empty($clients_array)){
        		    $actpatients->leftJoin("p.EpidIpidMapping e");
        		    $actpatients->andWhereIn('e.clientid',$clients_array);
    		    }
    		    
    		    $actipidarray = $actpatients->fetchArray();

    		    
    		    $ipids_str .= '"99999",';
    		    foreach($actipidarray as $k=>$cp){
    		        if($cp['isdelete'] != '1' ){
                            $run[$cp['ipid']] = false;
                            
    		                if(!isset($last_changes_target[$cp['ipid']]) && !isset($last_changes_source[$cp['ipid']])){
                                $run[$cp['ipid']] = true;
    		                } else {
        		                if((strtotime($cp['last_update']) > strtotime($last_changes_target[$cp['ipid']])) || (strtotime($cp['last_update']) > strtotime($last_changes_source[$cp['ipid']]))){
                                    $run[$cp['ipid']] = true;
                                } 
    		                }
    		                
        		            if($run[$cp['ipid']]){
                		        $ipids[] = $cp['ipid'];
                		        $ipids_str .= '"'.$cp['ipid'].'",';
        		            }
        		            
    		        } else {
    		            $del_ipids[]= $cp['ipid'];
    		        }
    		    }
    		    
    		    $source_ipids[] = "9999999";
    		    foreach($linked_patients as $k=>$pl){
    		        if(in_array($pl['source'],$ipids) || in_array($pl['target'],$ipids)){
    
    		            if(!in_array($pl['source'],$del_ipids)){
            	            $source_ipids[] = $pl['source'];
        	            }
        	                
        	            if(!in_array($pl['target'],$del_ipids) && !in_array($pl['target'],$linked_array[$pl['source']])  ){
        	               $linked_array[$pl['source']][] = $pl['target'];
        	               $sharing_create_date[$pl['source'].'_'.$pl['target']] = $pl['create_date'];
        	            }
    		        }
    		    }
    		    
    		    $source_ipids = array_values(array_unique($source_ipids));
    
    		    $patient_qm = Doctrine_Query::create()
    		    ->select("*")
    		    ->from('PatientsMarked ')
    		    ->where("status = 'a'")
//     		    ->andWhere("shortcuts != ''")// TODO - 1135
    		    ->andWhereIn("ipid",$source_ipids);
    		    $pf_share_array = $patient_qm->fetchArray();
    		   
    		    $patients_sharing_data[] = "99999999";
    		    foreach($pf_share_array as $lk=>$spf){
    		        $patients_sharing_data[] = $spf['ipid'];
    		    }
    
    		    $patients_sharing_data = array_values(array_unique($patients_sharing_data));
    
    
    		    $course_entry['st'] = 0;
    		    $course_entry['ts'] = 0;
    		    /* print_r("\n");
    		    print_r(date("d.m.Y H:i:s",time()));
    		    print_r("\n"); */
    		    
    		    
    		    if(!empty($linked_array)){
    		        
        		    foreach($linked_array as $source_ipid=>$t_ipids)
        		    {
        		        if(in_array($source_ipid,$patients_sharing_data))
        		        {
        		            foreach($t_ipids as $target_ipid)
        		            {
        		                if($source_ipid != $target_ipid && !in_array($target_ipid,$del_ipids)){
        		                    
            		                if(($course_entry['st']+ $course_entry['ts']) > 500 ){
            		                    /* print_r($course_entry);
            		                    print_r("\n");
            		                    print_r(date("d.m.Y H:i:s",time()));
            		                    print_r("\n"); */
            		                    unlink($file_location . $lock_filename);
            		                    exit;
            		                }
            		                
            		                if(strlen($source_ipid) > "0" && strlen($target_ipid) > "0" ){
                		                $course_entry['st'] += $this->share_shortcuts_s2t($source_ipid, $target_ipid, $sharing_create_date[$source_ipid.'_'.$target_ipid]); // from inital sourxe to target
                		                $course_entry['ts'] +=$this->share_shortcuts_t2s($target_ipid, $source_ipid, $sharing_create_date[$source_ipid.'_'.$target_ipid]); // from inital sourxe to target
            		                }
            		                
        		                }
        		            }
        		        }
        		    }
    		    }
    		    
    		    
    		    unlink($file_location . $lock_filename);
		    }
		}
		
		
		public function share_shortcuts_ipids(){
		    set_time_limit(0);
		    
		    $clients_array = array("187","144","121","167","185", "157","131", "142", "143");// sh clients

		    $ipids = array("99999");//LAST SHTBA10049
		    
		    $actpatients = Doctrine_Query::create()
		    ->select("p.ipid,p.isdelete,p.last_update,p.last_update_user")
		    ->from('PatientMaster p')
		    ->whereIn('ipid',$ipids);
		    $actipidarray = $actpatients->fetchArray();
		    if(!empty($actipidarray)){
		        foreach($actipidarray  as $k=>$pl){
		            $update_data[$pl['ipid']]['last_update_user'] = $pl['last_update_user']; 
		            $update_data[$pl['ipid']]['last_update'] = $pl['last_update']; 
		        }
		    }
		 
		    // delete from patient course where id in patient share log 
		    $patient_slq = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientsShareLog')
		    ->whereIn("ipid",$ipids)
		    ->orderBy('create_date ASC');
		    $sh_log_patients = $patient_slq->fetchArray();
		    
		    if(!empty($sh_log_patients)){
		        $j=1;
		        foreach($sh_log_patients as $k=>$sl){
		            //delete from patient course
		            
		            if(strlen($sl['course_id']) > 0 && strlen($sl['ipid']) > 0 && strlen($sl['source_ipid']) > 0 )
		            {
		                $slog_ipids[] = $sl['course_id']; 
    		            $q = Doctrine_Query::create()
    		            ->delete('PatientCourse pc')
    		            ->where("pc.id ='" . $sl['course_id'] . "'")
    		            ->andWhere("pc.ipid ='" . $sl['ipid'] . "'")
    		            ->andWhere("pc.source_ipid ='" . $sl['source_ipid'] . "'");
                        $q->execute();

    		            $q_sl = Doctrine_Query::create()
    		            ->delete('PatientsShareLog')
    		            ->where("id ='" . $sl['id'] . "'")
    		            ->andWhere("course_id ='" . $sl['course_id'] . "'")
    		            ->andWhere("ipid ='" . $sl['ipid'] . "'")
    		            ->andWhere("source_ipid ='" . $sl['source_ipid'] . "'");
                        $q_sl->execute();
		            }
		        }
		    }
		    
		    $del_ipids[] = "999999999999999999";
		    
		    $patient_q = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientsLinked');
            if($sharing_date){
                $patient_q->andWhere('create_date >= "'.$sharing_date.'" ');   
            }
		    $linked_patients = $patient_q->fetchArray();

		  
		    
		    $source_ipids[] = "9999999";
		    foreach($linked_patients as $k=>$pl){
		        if(in_array($pl['source'],$ipids) || in_array($pl['target'],$ipids)){

		            if(!in_array($pl['source'],$del_ipids)){
        	            $source_ipids[] = $pl['source'];
    	            }
    	                
    	            if(!in_array($pl['target'],$del_ipids) && !in_array($pl['target'],$linked_array[$pl['source']])  ){
    	               $linked_array[$pl['source']][] = $pl['target'];
    	               $sharing_create_date[$pl['source'].'_'.$pl['target']] = $pl['create_date'];
    	            }
		        }
		    }
		    
		    $source_ipids = array_values(array_unique($source_ipids));

		    $patient_qm = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientsMarked ')
		    ->where("status = 'a'")
		    ->andWhere("shortcuts != ''")
		    ->andWhereIn("ipid",$source_ipids);
		    $pf_share_array = $patient_qm->fetchArray();
		   
		    $patients_sharing_data[] = "99999999";
		    foreach($pf_share_array as $lk=>$spf){
		        $patients_sharing_data[] = $spf['ipid'];
		    }

		    $patients_sharing_data = array_values(array_unique($patients_sharing_data));

		    $course_entry['st'] = 0;
		    $course_entry['ts'] = 0;
		    /* print_r("\n");
		    print_r(date("d.m.Y H:i:s",time()));
		    print_r("\n"); */
		    if(!empty($linked_array)){
		        
    		    foreach($linked_array as $source_ipid=>$t_ipids)
    		    {
    		        if(in_array($source_ipid,$patients_sharing_data))
    		        {
    		            foreach($t_ipids as $target_ipid)
    		            {
    		                if($source_ipid != $target_ipid && !in_array($target_ipid,$del_ipids)){
    		                    
        		                if(($course_entry['st']+ $course_entry['ts']) > 2000 ){
        		                    print_r($course_entry);
        		                    print_r("\n");
        		                    print_r(date("d.m.Y H:i:s",time()));
        		                    print_r("\n");
        		                    exit;
        		                }
        		                
        		                $course_entry['st'] += $this->share_shortcuts_s2t($source_ipid, $target_ipid, $sharing_create_date[$source_ipid.'_'.$target_ipid]); // from inital sourxe to target
        		                $course_entry['ts'] += $this->share_shortcuts_t2s($target_ipid, $source_ipid, $sharing_create_date[$source_ipid.'_'.$target_ipid]); // from inital sourxe to target
        		                
    		                }
    		            }
    		        }
    		    }
    		    
    		    print_r($course_entry);
    		    print_r("\n");
    		    print_r(date("d.m.Y H:i:s",time()));
    		    print_r("\n");
		    }
		    
		    foreach($ipids as $ipid){
		        if(!empty($update_data[$ipid])){
                    $q_up = Doctrine_Query::create()
                    ->update('PatientMaster')
                    ->set('last_update_user', "'".$update_data[$ipid]['last_update_user']."'")
                    ->set('last_update', "'".$update_data[$ipid]['last_update']."'")
                    ->where("ipid = '".$ipid."'");
                    $q_up->execute();
		        }
		    }
		}
		
		public function share_shortcuts_s2t($source_ipid,$target_ipid, $connection_date){
		    
		    // get sharing options for both source and target 
		    $shared_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientsShare')
		    ->where('shared="1"')
		    ->andWhere('source ="'.$source_ipid.'" and target ="'.$target_ipid.'"  ');
		    $shared_shortcuts_array = $shared_q->fetchArray();
		    
		    
		    foreach($shared_shortcuts_array as $k=>$ss){
   		        $target2source[$ss['source']][] = $ss['shortcut'];
   		        $all_shared_shs[] = $ss['shortcut'];
		    }
		    if(empty($all_shared_shs)){
		        $all_shared_shs[] = "9999999999999";
		    }
		    
		    // get letter shortcuts for ids 
		    $course_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Courseshortcuts')
		    ->where('isdelete=0')
		    ->andWhereIn('shortcut_id',$all_shared_shs);
		    $course_array = $course_q->fetchArray();
		  
		    foreach($course_array as $k=>$c_sh_data){
		        $id2letter[$c_sh_data['shortcut_id']] = $c_sh_data['shortcut']; 
		    }
		    
		    foreach($target2source as $sipid=>$sh_sett){
		        foreach($sh_sett as $k=>$sid){
		           $allowed_sh[$sipid][] = $id2letter[$sid];  
		        }
		    }
		    
		    $sql ="*,";
		    $sql .="AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,";
		    $sql .="AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,";
		    $sql .="AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,";
		    $sql .="AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name,";
		    $sql .="CONCAT(course_date,'_',user_id,'_',AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "'),'_',if( AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') != '' ,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "'),' ')  ) as ident";
		    
		    //get all data that exists in target 
		    $patient_target_source_q = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientCourse')
		    ->where('ipid = "'.$target_ipid.'"')
		    ->andWhere('source_ipid= "'.$source_ipid.'"');
		    $pcourse_target_array = $patient_target_source_q->fetchArray();
		    
		    if(!empty($pcourse_target_array)){
    		    foreach($pcourse_target_array as $k=>$target_data){
    		        $existing_in_target[] = str_replace(" ","",$target_data['ident']);
    		    }
    		    
		    } else {
   		        $existing_in_target[] = "99999999";
		    }

		    //get all data existing in source
		    $pcourse_source_array = array();
		    $patient_course_source_q = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientCourse')
		    ->where('ipid = "'.$source_ipid.'"')
		    ->andwhere('source_ipid = ""')
		    ->andWhere('DATE(done_date) >= DATE("'.$connection_date.'")');
		    $pcourse_source_array = $patient_course_source_q->fetchArray();

		    
		    $file_location = APPLICATION_PATH . '/../public/run/';
		    $stop_filename = 'stop.lockfile';
		    
		    $inserted_in_target = array();
		    foreach($pcourse_source_array as $k => $course)
		    {
		        $course['ident']  = str_replace(" ","",$course['ident']);
                // check if data was already added from source to target
		        if(in_array($course['course_type'],$allowed_sh[$source_ipid]) && !in_array($course['ident'],$existing_in_target) && !in_array($course['ident'],$inserted_in_target))
		        {

		            if($course['recordid'] == '0' || ($course['tabname'] == 'patient_drugplan' && $course['recordid'] >= '0') || $course['tabname'] != 'kvno_assesment')
		            {
		                $share_course_s2t[] = $course;
		            
		                $pc = new PatientCourse();
		                $pc->ipid = $target_ipid;
		                $pc->user_id = $course['user_id'];
		                $pc->course_date = $course['course_date'];
		                $pc->course_type = Pms_CommonData::aesEncrypt($course['course_type']);
		                $pc->tabname = Pms_CommonData::aesEncrypt($course['tabname']);
		                $pc->course_title = Pms_CommonData::aesEncrypt($course['course_title']);
		                $pc->recordid = '0';
		                $pc->recorddata = $course['recorddata'];
		                $pc->ishidden = $course['ishidden'];
		                $pc->wrong = $course['wrong'];
		                $pc->wrongcomment = $course['wrongcomment'];
		                $pc->isstandby = $course['isstandby'];
		                $pc->isserialized = $course['isserialized'];
		                $pc->source_ipid = $source_ipid;
		                $pc->done_date = $course['done_date'];
		                $pc->done_name = $course['done_name'];
		                $pc->done_id = $course['done_id'];
		                $pc->create_date = $course['create_date'];
		                $pc->create_user = $course['create_user'];
		                $pc->save(); 
		                
		                $course_id = $pc->id;
		                if($course_id ){
		                    
		                    $pcl = new PatientsShareLog();
		                    $pcl->course_id = $course_id;
		                    $pcl->ipid = $target_ipid;
		                    $pcl->source_ipid = $source_ipid;
		                    $pcl->source_course_id = $course['id'];
		                    $pcl->create_date = date('Y-m-d H:i:s',time());
		                    $pcl->save();
		                }
		                
		                $inserted_in_target[] = $course['ident'];
		                
		                if(file_exists($file_location . $stop_filename))
		                {
		                    echo "force_stop";
		                    exit;
		                }
		            }
		        }
		    }
		    
		    $result = count($share_course_s2t);
		    
		    return $result;
		    
		}
		
		
		public function share_shortcuts_t2s($target_ipid, $source_ipid, $connection_date){
		    
		    // get sharing options for both source and target 
		    $shared_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientsShare')
		    ->where('shared="1"')
		    ->andWhere('source ="'.$target_ipid.'" and target ="'.$source_ipid.'"  ');
		    $shared_shortcuts_array = $shared_q->fetchArray();
		    
		    foreach($shared_shortcuts_array as $k=>$ss){
   		        $source2target[$ss['source']][] = $ss['shortcut'];
   		        $all_shared_shs[] = $ss['shortcut'];
		    }
		    if(empty($all_shared_shs)){
		        $all_shared_shs[] = "9999999999999";
		    }
		    // get letter shortcuts for ids 
		    $course_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('Courseshortcuts')
		    ->where('isdelete=0')
		    ->andWhereIn('shortcut_id',$all_shared_shs);
		    $course_array = $course_q->fetchArray();
		    
		    foreach($course_array as $k=>$c_sh_data){
		        $id2letter[$c_sh_data['shortcut_id']] = $c_sh_data['shortcut']; 
		    }
		    
		    foreach($source2target as $sipid=>$sh_sett){
		        foreach($sh_sett as $k=>$sid){
		           $allowed_sh[$sipid][] = $id2letter[$sid];  
		        }
		    }

		    $sql ="*,";
		    $sql .="AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,";
		    $sql .="AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,";
		    $sql .="AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,";
		    $sql .="AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name,";
		    $sql .="CONCAT(course_date,'_',user_id,'_',AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "'),'_',if( AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') != '' ,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "'),' ')  ) as t2s_ident";


		    //get all data added in source after the connection was created
		    $patient_course_source_q = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientCourse')
		    ->where('ipid = "'.$source_ipid.'"')
		    ->andWhere('source_ipid = "'.$target_ipid.'"')
		    ->andWhere('DATE(done_date) >= DATE("'.$connection_date.'")');
		    $pcourse_source_array = $patient_course_source_q->fetchArray();
		    
		    if(!empty($pcourse_source_array)) {
    		    foreach($pcourse_source_array as $k=>$sd_data){
    		        $existing_in_source[] = str_replace(" ","",$sd_data['t2s_ident']);
    		    }
		    } else {
    		    $existing_in_source[] = "99999999";
		    }
		    
		    
		    //get all data added in target - and check it it was sent to source
		    $pcourse_target_array = array();
		    $patient_target_source_q = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientCourse')
		    ->where('ipid = "'.$target_ipid.'"')
		    ->andWhere('source_ipid = ""')
		    ->andWhere('DATE(done_date) >= DATE("'.$connection_date.'")');
		    $pcourse_target_array = $patient_target_source_q->fetchArray();
		    

		    $file_location = APPLICATION_PATH . '/../public/run/';
		    $stop_filename = 'stop.lockfile';
		    
		    
		    $inserted_in_source = array();
		    foreach($pcourse_target_array as $k=>$course){
		        
		        $course['t2s_ident'] =  str_replace(" ","",$course['t2s_ident']);
		        if(in_array($course['course_type'],$allowed_sh[$target_ipid]) && !in_array($course['t2s_ident'],$existing_in_source) && !in_array($course['t2s_ident'],$inserted_in_source)){
		            
		              if($course['recordid'] == '0' || ($course['tabname'] == 'patient_drugplan' && $course['recordid'] >= '0') || $course['tabname'] != 'kvno_assesment')
		              {
		                  $share_course_t2s[] = $course;
		                  
		                  $pc = new PatientCourse();
		                  $pc->ipid = $source_ipid;
		                  $pc->user_id = $course['user_id'];
		                  $pc->course_date = $course['course_date'];
		                  $pc->course_type = Pms_CommonData::aesEncrypt($course['course_type']);
		                  $pc->tabname = Pms_CommonData::aesEncrypt($course['tabname']);
		                  $pc->course_title = Pms_CommonData::aesEncrypt($course['course_title']);
		                  $pc->recordid = '0';
		                  $pc->recorddata = $course['recorddata'];
		                  $pc->ishidden = $course['ishidden'];
		                  $pc->wrong = $course['wrong'];
		                  $pc->wrongcomment = $course['wrongcomment'];
		                  $pc->isstandby = $course['isstandby'];
		                  $pc->isserialized = $course['isserialized'];
		                  $pc->source_ipid = $target_ipid;
		                  $pc->done_date = $course['done_date'];
		                  $pc->done_name = $course['done_name'];
		                  $pc->done_id = $course['done_id'];
		                  $pc->create_date = $course['create_date'];
		                  $pc->create_user = $course['create_user'];
		                  $pc->save(); 
		                  
		                  $course_id = $pc->id;
		                  
		                  if($course_id ){
    		                  $pcl = new PatientsShareLog();
    		                  $pcl->course_id = $course_id;
    		                  $pcl->ipid = $source_ipid;
    		                  $pcl->source_ipid = $target_ipid;
    		                  $pcl->source_course_id = $course['id'];
    		                  $pcl->create_date = date('Y-m-d H:i:s',time());
    		                  $pcl->save(); 
		                  }
		                  
		                  $inserted_in_source[] = $course['t2s_ident'];
		                  
		                  if(file_exists($file_location . $stop_filename))
		                  {
		                      echo "force_stop";
		                      exit;
		                  }
		              }
		        }
		    }
		    
            $result = count($share_course_t2s);
            
		    return $result;
		    
		}
		
		public function sharing_drug_plans(){
		    set_time_limit(0);
		    
		    $patient_q = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientsLinked');
		    $linked_patients = $patient_q->fetchArray();
		    
		    $source_ipids[] = "9999999";
		    foreach($linked_patients as $k=>$pl){
		        if($pl['copy_meds'] == "1"){
		            $source_ipids[] = $pl['source'];
		            if(strlen($pl['target']) > 0 ){
		                $linked_array[$pl['source']][] = $pl['target'];
		                $sharing_create_date[$pl['source'].'_'.$pl['target']] = $pl['create_date'];
		            }
		        }
		    }
		    
		    $patient_qm = Doctrine_Query::create()
		    ->select("*")
		    ->from('PatientsMarked ')
		    ->where("status = 'a'")
		    ->andWhereIn("ipid",$source_ipids);
		    $pf_share_array = $patient_qm->fetchArray();

		    
		    $patients_sharing_files[] = "99999999";
		    foreach($pf_share_array as $lk=>$spf){
		        $patients_sharing_files[] = $spf['ipid'];
		    }
		    
		    
		    foreach($linked_array as $source_ipid=>$t_ipids)
		    {
		        if(in_array($source_ipid,$patients_sharing_files))
		        {
		            foreach($t_ipids as $target_ipid)
		            {
		                $this->save_drugplans($source_ipid, $target_ipid); // from inital sourxe to target
		    
// 		                if(strlen($target_ipid) > 0 ){
// 		                    $this->save_drugplans($target_ipid, $source_ipid,$sharing_create_date[$source_ipid.'_'.$target_ipid]); // from target to source - if file was added in target after share date
// 		                }
		            }
		        }
		    }
		    
		}
		
		

		public function save_drugplans($source_ipid, $target_ipid,$create_date =  false)
		{
		    set_time_limit(0);
		    // get alreadi added data in share
		    $ipids = array($source_ipid,$target_ipid);
		    $shared_drugs_array = PatientDrugPlanShare :: get_shared($ipids);

		    $source_pat_meds = PatientDrugPlan::getPatientAllDrugs($source_ipid);
		    foreach($source_pat_meds as $k=>$sda){
// 		        if(!in_array($sda['id'],$shared_drugs_array[$source_ipid])  && $sda['source_ipid'] != $target_ipid){
		        if(!in_array($sda['id'],$shared_drugs_array[$source_ipid])){
		            $insert_source_meds = new PatientDrugPlanShare();
		            $insert_source_meds->ipid = $source_ipid;
		            $insert_source_meds->drugplan_id = $sda['id'];
		            $insert_source_meds->create_date = date("Y-m-d H:i:s",time());
		            $insert_source_meds->save();
		        }
		    }
		    
		    $target_pat_meds = PatientDrugPlan::getPatientAllDrugs($target_ipid);
		    foreach($target_pat_meds as $k=>$tda){
// 		        if(!in_array($tda['id'],$shared_drugs_array[$target_ipid]) && $tda['source_ipid'] != $source_ipid){
		        if(!in_array($tda['id'],$shared_drugs_array[$target_ipid])){
		            $insert_target_meds = new PatientDrugPlanShare();
		            $insert_target_meds->ipid = $target_ipid;
		            $insert_target_meds->drugplan_id = $tda['id'];
		            $insert_target_meds->create_date = date("Y-m-d H:i:s",time());
		            $insert_target_meds->save();
		        }
		    }
		 }
		 
		 
		 
		 /**
		  * ISPC-2832 Ancuta 31.03.2021
		  * @param unknown $ipid
		  */
		 public function get_connection_by_ipid($ipid,$original_direction = false){
		     if(empty($ipid)){
		         return;
		     }
		     
		     // check if the patient where data was inserted is SHARED
		     $rp = Doctrine_Query::create()
		     ->select('*')
		     ->from('PatientsLinked')
		     ->where('source = ?', $ipid)
		     ->orWhere('target = ?',$ipid);
		     $linked_patients = $rp->fetchArray();
		     
		     if(empty($linked_patients)){
		         return;
		     }
		     
		     $source_ipids = array();
		     $all_ipids = array();
		     foreach($linked_patients as $k=>$pl){
		         $source_ipids[] = $pl['source'];
		         
		         $all_ipids[] = $pl['source'];
		         $all_ipids[] = $pl['target'];
		         
		     }
		     if(empty($all_ipids)){
		         return;
		     }
		     
		     $source_ipids = array_values(array_unique($source_ipids));
		     $all_ipids = array_values(array_unique($all_ipids));
		     
		     if(empty($all_ipids)){
		         return;
		     }
		     // get clients of ipids
		     $patient_clietns = Doctrine_Query::create()
		     ->select("ipid,clientid")
		     ->from('EpidIpidMapping')
		     ->whereIn("ipid",$all_ipids)
		     ->fetchArray();
		     
		     $ipid2clientid = array();
		     foreach($patient_clietns as $k => $ep){
		         $ipid2clientid[$ep['ipid']] = $ep['clientid'];
		     }
		     
		     
		     $patient_qm = Doctrine_Query::create()
		     ->select("*")
		     ->from('PatientsMarked ')
		     ->where("status = 'a'")
		     ->andWhereIn("ipid",$source_ipids);
		     $pf_share_array = $patient_qm->fetchArray();
		     
		     $patients_sharing_data = array();
		     foreach($pf_share_array as $lk=>$spf){
		         $patients_sharing_data[] = $spf['ipid'];
		     }
		     
		     $ident=0;
		     $share_direction = array();
		     foreach($linked_patients as $k=>$pl){
		         if(in_array($pl['source'], $patients_sharing_data)){
		             if($original_direction){
		                 
		                 if($pl['source'] == $ipid || $pl['target'] == $ipid){
    		                 $share_direction[$ident]['source'] = $pl['source'];
    		                 $share_direction[$ident]['target'] = $pl['target'];
    		                 $share_direction[$ident]['source_client'] = $ipid2clientid[$pl['source']];
    		                 $share_direction[$ident]['target_client'] = $ipid2clientid[$pl['target']];
    		                 $share_direction[$ident]['current_ipid'] =  'source';
    		                 $share_direction[$ident]['copy_meds'] =  $pl['copy_meds'];
    		                 $ident++;
    		             }  
		             } 
		             else
		             {
    		             if($pl['source'] == $ipid){
    		                 $share_direction[$ident]['source'] = $pl['source'];
    		                 $share_direction[$ident]['target'] = $pl['target'];
    		                 $share_direction[$ident]['source_client'] = $ipid2clientid[$pl['source']];
    		                 $share_direction[$ident]['target_client'] = $ipid2clientid[$pl['target']];
    		                 $share_direction[$ident]['current_ipid'] =  'source';
    		                 //$share_direction[$ident]['intense_connection'] = IntenseConnectionsTable::_find_intense_connectionBetweenClients( $share_direction[$ident]['source_client'],$share_direction[$ident]['target_client'] ,false,true);
    		                 $ident++;
    		             } elseif($pl['target'] == $ipid){
    		                 $share_direction[$ident]['source'] = $pl['target'];
    		                 $share_direction[$ident]['target'] = $pl['source'];
    		                 $share_direction[$ident]['source_client'] = $ipid2clientid[$pl['target']];
    		                 $share_direction[$ident]['target_client'] = $ipid2clientid[$pl['source']];
    		                 $share_direction[$ident]['current_ipid'] =  'target';
    		                 //$share_direction[$ident]['intense_connection'] = IntenseConnectionsTable::_find_intense_connectionBetweenClients( $share_direction[$ident]['source_client'],$share_direction[$ident]['target_client'] ,false,true);
    		                 $ident++;
    		             }
		             }
		         }
		         
		     }
		     
		     if(!empty($share_direction)){
		         return  $share_direction;
		     } else{
		         false;
		     }
		 }
		 
	}

?>