<?php

	require_once("Pms/Form.php");

	class Application_Form_PatientFileUpload extends Pms_Form {

		public function validate($post)
		{

			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();

			
			if (isset($post['filename'])) {
			    if(strlen($post['filename']) < 1)
			    {
			        $this->error_message['filename'] = $Tr->translate("uploadcsvfile");
			        $error = 2;
			    } 
			}
			elseif(strlen($_SESSION['filename']) < 1)
			{
				$this->error_message['filename'] = $Tr->translate("uploadcsvfile");
				$error = 2;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function InsertData($post = array())
		{
			/*
			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::fileupload($con_id, 'uploads/' . $_SESSION['zipname'], 'uploads/' . $_SESSION['zipname']);
				Pms_FtpFileupload::ftpconclose($con_id);
				$cmd = "rm -r uploads/" . $_SESSION['zipname'] . ";";
				exec($cmd);
			}
			*/
			// ISPC-2018
			$pat_files_tags_rights = explode(',', $post['pat_files_tags_rights']);
			
			if(strlen($post['title']) < 1)
			{
				$fl = explode(".", $_SESSION['filetitle']);

				$post['title'] = $fl[0];
			}

			$cust = new PatientFileUpload();
			$cust->title = Pms_CommonData::aesEncrypt($post['title']);
			$cust->ipid = $post['ipid'];
			//ISPC-2831 Dragos 15.03.2021
            $cust->meta_name = !empty($post['meta_name']) ? Pms_CommonData::aesEncrypt($post['meta_name']) : null ;
            $cust->admission_id = $post['admission_id'];
			$cust->comment = !empty($post['comment']) ? Pms_CommonData::aesEncrypt($post['comment']) : null ;
			// -- //
			$cust->file_name = Pms_CommonData::aesEncrypt( (! empty($post['file_name']) ? $post['file_name'] : addslashes($_SESSION['filename']))); //$post['fileinfo']['filename']['name'];
			$cust->file_type = Pms_CommonData::aesEncrypt($post['filetype']);
			// ISPC-2420 05.09.2019 Ancuta // Maria:: Migration ISPC to CISPC 08.08.2020
			// Demstepcare_upload - 10.09.2019 Ancuta
			if(isset($post['tabname'])){
    			$cust->tabname = $post['tabname'];
			}
			if(isset($post['recordid'])){
    			$cust->recordid = $post['recordid'];
			}
			// --
			$cust->save();
			$inserted_file_id = $cust->id;

			//this file is allready zipped
			$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (( ! empty($post['zipname']) ? $post['zipname'] : $_SESSION['zipname']), "uploads" , 
					array(
							"is_zipped" => true,
							"file_name" => (! empty($post['file_name']) ? $post['file_name'] : $_SESSION['filename']),
							"insert_id" => $inserted_file_id,
							"db_table"	=> "PatientFileUpload",
					));

			
			//ISPC-2642 Ancuta 10-11.08.2020 - changed from $_POST to post  
			//$tags_labels = is_array($_POST['tag_name']) ? $_POST['tag_name'] : explode(',', $_POST['tag_name']);
			if(!isset($post['tag_name'])){
			    $post['tag_name'] = $_POST['tag_name'];
			}
			$tags_labels = is_array($post['tag_name']) ? $post['tag_name'] : explode(',', $post['tag_name']);
			$tags_labels = array_map('trim', $tags_labels);
		

			if($inserted_file_id && count($tags_labels) > '0')
			{
				$patient_fl = Doctrine_Query::create()
					->select("*")
					->from('PatientFileTags')
					->where('(client = ? OR client = 0)', $post['clientid'])
					->andWhereIn('tag', $tags_labels)
					->andWhere('isdelete = 0')
					;
				$tags = $patient_fl->fetchArray();

				//existing tags in db
				$existing_tags = array(); //that are not deleted
				foreach($tags as $k_tag => $v_tag) {
				    $existing_tags[$v_tag['id']] = trim($v_tag['tag']); 
				}

				//separate new tags from post'ed tags
				$new_tags = array();
				if (in_array('create', $pat_files_tags_rights)) {
					foreach ($tags_labels as $v_post_tag) {
						if ( ! in_array($v_post_tag, $existing_tags)) {
							$new_tags[] = $v_post_tag;
						}
					}
				}
	
				//insert new tags
				foreach($new_tags as $k_tag => $v_new_tag)
				{
					if(strlen(trim(rtrim($v_new_tag))) > '0')
					{
						$mtag = new PatientFileTags();
						$mtag->client = $post['clientid'];
						$mtag->tag = $v_new_tag;
						$mtag->save();
						$inserted_master_tag = $mtag->id;

						if($inserted_master_tag)
						{
							//append newly created tags to existing db tags
							$existing_tags[$inserted_master_tag] = $mtag->tag;
						}
					}
				}

				//create file2tags array
				//append uploaded by user tag
				if($existing_tags)
				{
					if(!in_array('use', $pat_files_tags_rights))
					{
						$existing_tags = array();
					}
				}
				
				$existing_tags['1'] = array();
				foreach($existing_tags as $k_post_tag_id => $v_tag_name)
				{
						$tags_data[] = array(
							'file' => $inserted_file_id,
							'tag' => $k_post_tag_id
						);
				}

				$collection = new Doctrine_Collection('PatientFile2tags');
				$collection->fromArray($tags_data);
				$collection->save();
				
				//ISPC - 2129
				if (in_array('Notfallplan', $tags_labels))
				{
					$actv = new PatientFileVersion();
					if($post['active_version'] == '1')
					{
						$factv = $actv->get_reset_active_version($post['ipid']);
					}
					
					$actv->file = $inserted_file_id;
					$actv->active_version = $post['active_version'];
					$actv->save();
				}
				
			}


			return $cust;
		}

		public function deleteFile($dids)
		{
			$fluplod = Doctrine::getTable('PatientFileUpload')->find($dids);
			$fluplod->isdeleted = 1;
			$fluplod->save();

			//delete tag if current fileid was the only file left for current file tags

			//1) get file tags
			$file_tags_arr = PatientFile2tags::get_files_tags($dids);
			foreach($file_tags_arr as $v_tags)
			{
				if(empty($file_tags))
				{
					$file_tags = array();
				}

				$file_tags[0] = array_merge($file_tags, $v_tags);
			}
			$file_tags = array_values(array_unique($file_tags[0]));

//			//exclude system tags first check
//			$system_tags = PatientFileTags::get_allclients_tags(true);
//			foreach($file_tags as $kftag => $vftag)
//			{
//				if(in_array($vftag, $system_tags))
//				{
//					unset($file_tags[$kftag]);
//				}
//			}
//			$file_tags = array_values(array_unique($file_tags));

			if(count($file_tags) > '0')
			{
				$current_file = $dids;
				$this->delete_unused_tags($file_tags, $current_file);
			}
		}

		private function delete_unused_tags($tags = false, $current_file = false)
		{
			$logininfo = new Zend_Session_Namespace("Login_Info");
			$clientid = $logininfo->clientid;

			if($tags && $current_file)
			{
				//preserve system tags
				$system_tags = PatientFileTags::get_allclients_tags(true);

				$tags_q = Doctrine_Query::create()
					->select('pfts.*, count(pfts.tag)')
					->from('PatientFile2tags pfts')
					->whereIn('pfts.tag', $tags)
					->andWhereNotIn('pfts.tag', $system_tags) //exclude system tags second check
					->andWhere('pfts.file != "' . $current_file . '"')
					->andWhere('pfts.file NOT IN (SELECT pf.id from PatientFileUpload pf where pf.isdeleted = "1")')
					->groupBy('pfts.tag');
				$tags_res = $tags_q->fetchArray();

				foreach($tags_res as $k_res => $v_res)
				{
					$tags_found[$v_res['tag']] = $v_res['count'];
				}

				$tags_to_be_removed = array();
				foreach($tags as $k_file_tag => $v_file_tag)
				{
					if(!array_key_exists($v_file_tag, $tags_found))
					{
						$tags_to_be_removed[] = $v_file_tag;
					}
				}

				if(count($tags_to_be_removed) > '0')
				{
					//delete master data
					$del_m_tag = Doctrine_Query::create()
						->delete('*')
						->from('PatientFileTags')
						->whereIn('id', $tags_to_be_removed)
						->andWhere('client = "' . $clientid . '"')
						->execute();

					//delete the data related to the deleted tags for deleted files
					$del_tag_files = Doctrine_Query::create()
						->delete('*.pfts')
						->from('PatientFile2tags pfts')
						->whereIn('pfts.tag', $tags_to_be_removed)
						->andWhere('pfts.file IN (SELECT pf.id from PatientFileUpload pf where pf.isdeleted = "1")')
						->execute();
				}
			}
		}

		/**
		 * leave $qquid empty if you want to upload all $files_array
		 * $qquid(a07c1a18-e834-45a8-8623-6422dba0d329) will upload just that file
		 * 
		 * @param array $files_array - structure of this array is in Pms_Controller_Action
		 * array(array(
						"action" => $action,
						"qquuid" => $qquuid,
						"filepath" => $filepath,
						"filename" => $filename,
				));
		 * @param array $qquid_array
		 * array('a07c1a18-e834-45a8-8623-6422dba0d329', '2318792f-b929-4caa-9fe2-5a4665dee443', ...); | null for all
		 * @return boolean
		 */
		public function saveFiles($ipid = 0 , $files_array = array(), $qquid_array = array(), $options = array(
						'file_details' => array(
								'ipid',
								'title_prefix',
								'file_name',
								'file_type',
								'file_date',
								'tabname',
								'recordid'
						)
		)) {
			
			$result = array();
			
			if ( empty($ipid) || empty($files_array) || ! is_array($files_array) ) {
				return $result; // fast return if something is wrong
			}
			
			foreach ($files_array as $onefile) {
				
				if ( ! empty($qquid_array) && !in_array($onefile['qquuid'], $qquid_array)) {
					continue; //don't upload this file 
				}
				$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue ($onefile['filepath'] , "uploads" );
				if ( $ftp_put_queue_result ) {
					
					//ftp OK, save to our table for later download
					$file_name = FtpPutQueue::get_file_name_by_id($ftp_put_queue_result);
					
					//create the displayed file_title and sanitize it
					$file_title = ! empty($options['file_details']['title_prefix']) ? $options['file_details']['title_prefix'] . "-" : "";
					$file_title .= $onefile['filename'];
					$file_title = Pms_CommonData::filter_filename($file_title, true);
					
					$encrypted = Pms_CommonData::aesEncryptMultiple(array(
							'title'		=> $file_title,
							'file_name'	=> $file_name,
							'file_type'	=> strtoupper(pathinfo($onefile['filename'], PATHINFO_EXTENSION)),
					));
					
					$pfu_obj = new PatientFileUpload();
					$pfu_obj->triggerformid = 0; // skip triggers
					$pfu_obj->title = $encrypted['title'];
					$pfu_obj->ipid = $ipid;
					$pfu_obj->file_name = $encrypted['file_name'];
					$pfu_obj->file_type = $encrypted['file_type'];
					$pfu_obj->file_date = isset($options['file_details']['file_date']) ? date('Y-m-d', strtotime($options['file_details']['file_date']))  : null;
					$pfu_obj->tabname = isset($options['file_details']['tabname']) ? $options['file_details']['tabname']  : null;
					$pfu_obj->recordid = isset($options['file_details']['recordid']) ? $options['file_details']['recordid'] : null;
					$pfu_obj->save();
					
					array_push ($result, $pfu_obj->id);
					//var_dump($options['file_details']['tabname']);
					if($options['file_details']['tabname'] == 'acp_file_emergencyplan')
					{
						$flt = new PatientFile2tags();
						$flt->file = $pfu_obj->id;
						$flt->tag = '21';
						$flt->save();
						
						$flact = new PatientFileVersion();
						if($options['file_details']['isactive'] == '1')
						{	
							$ractv = $flact->get_reset_active_version($ipid);
							
							$flact->file = $pfu_obj->id;
							$flact->active_version = '1';
							$flact->save();
						}
						else 
						{
							$flact->file = $pfu_obj->id;
							$flact->active_version = '0';
							$flact->save();
						}
						
					}
					
				}				
				
			}
			
			return $result;

				
		}
 
 
  
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		/**
		 * @author Ancuta
		 * ISPC-2432
		 * copy of fn  BriefController system_file_upload
		 * @param unknown $clientid
		 * @param unknown $ipid
		 * @param boolean $source_path
		 * @param boolean $file_title // Maria:: Migration ISPC to CISPC 08.08.2020
		 * @param boolean $file_for - used to add diferent data to files and survey results
		 * Ancuta: 12.02.2020 added  specific information based on module 215- for ligetis using Pms_CommonData::mePatientIdentification 
		 */
		
		public function system_file_upload($clientid, $ipid, $source_path = false, $file_title = false, $file_for = false)
		{
		    
		    // 
		    
		    if($source_path)
		    {
		        //prepare unique upload folder
		        $tmpstmp = $this->uniqfolder(PDF_PATH);
		        
		        //get upload folder name
		        $tmpstmp_filename = basename($tmpstmp);
		        
		        //get original file name
		        $file_name_real = basename($source_path);
		        $source_path_info = pathinfo($source_path);
		        
		        
		        //construct upload folder, file destination
		        $destination_path = PDF_PATH . "/" . $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
		        $db_filename_destination = $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
		        
// 		        dd(func_get_args(),$source_path,$source_path_info,$destination_path);
		        //do a copy (from place where the pdf is generated to upload folder
		        copy($source_path, $destination_path);
		        
		        //prepare cmd for folder zip
		        // 				$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
		        //execute - zip the folder
		        // 				exec($cmd);
		        
		        $zipname = $tmpstmp . ".zip";
		        $filename = "uploads/" . $tmpstmp . ".zip";
		        
		        /*
		         //connect
		         $con_id = Pms_FtpFileupload::ftpconnect();
		         if($_REQUEST['zzz'])
		         {
		         print_r("Connection ID:");
		         var_dump($con_id);
		         print_r("\n\n");
		         exit;
		         }
		         if($con_id)
		         {
		         //do upload
		         $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
		         //close connection
		         Pms_FtpFileupload::ftpconclose($con_id);
		         }
		         */
		        $Client_obj = new Client();
		        $client_data = $Client_obj->getClientDataByid($clientid);
		        $filepass = $client_data[0]['fileupoadpass'];
		        
		        
		        //$clientarray[0]['fileupoadpass'];
		        
// 		        public static function                     ftp_put_queue($local_file_path ,  $legacy_path = "uploads", $is_zipped = NULL, $foster_file = false , $clientid = NULL, $filepass = NULL)
		        $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue ($destination_path, "uploads" ,null,false,$clientid,$filepass);
		        
		        
/* 		        if($file_title === false)
		        {
		            $file_title = 'Ligetis XCHANGE Datei';
		        }
 */		        
		        $modules = new Modules();
		        if($modules->checkModulePrivileges("215", $clientid))//specific ligetis labels
		        {
		            $mePatient_labels = Pms_CommonData::mePatientIdentification('ligetis');
		        }
		        else
		        {
		            $mePatient_labels = Pms_CommonData::mePatientIdentification('default');
		        }
		        
		        $label_set  =  'file';
		        if(isset($file_for) && $file_for == 'results'){
		            $label_set = 'results';
		        }
		        
		        $file_title = $mePatient_labels[$label_set]['name'];

		        
		        //add pdf to patient files table
		        if(strlen($filename) > 0)
		        {
		            $cust = new PatientFileUpload();
		            $cust->title = Pms_CommonData::aesEncrypt($file_title);
		            $cust->ipid = $ipid;
		            $cust->file_name = Pms_CommonData::aesEncrypt($db_filename_destination);
		            $cust->file_type = Pms_CommonData::aesEncrypt($source_path_info['extension']);
		            $cust->system_generated = "0";
		            $cust->create_user = '-1';
		            $cust->save();
		            
		            $file_id = $cust->id;
		            
		            if($file_id){
		                //Add to verlauf
    		            $custcourse = new PatientCourse();
    		            $custcourse->ipid = $ipid;
    		            $custcourse->course_date = date("Y-m-d H:i:s", time());
    		            $custcourse->course_type = Pms_CommonData::aesEncrypt($mePatient_labels[$label_set]['course_shortcut']);
    		            $comment = $mePatient_labels[$label_set]['course_entry'];
    		            $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
    		            $custcourse->user_id = '-1';
    		            $custcourse->recordid = $file_id;
    		            //$custcourse->tabname = Pms_CommonData::aesEncrypt('mePatient_uploaded_img_from_device');
    		            $custcourse->tabname = Pms_CommonData::aesEncrypt($mePatient_labels[$label_set]['course_tabname']);
    		            $custcourse->save();
    		            
    		            $tag_tabname= $mePatient_labels[$label_set]['tag_tabname'];
    		            $insert_tag = Application_Form_PatientFile2tags::insert_file_tags($file_id,false, $tag_tabname);
    		            
    		            //notify users
    		            $mess = new Messages();
    		            $notify_users = $mess->mePatient_uploadImages_todos($clientid, $ipid);
    		            
		            }
		            
		        }
		    }
		}
		/**
		 * @author Ancuta
		 * ISPC-2432
		 * copy of fn  BriefController uniqfolder
		 * @param unknown $path
		 * @return string
		 */
		private function uniqfolder($path)
		{
		    $i = 0;
		    $dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
		    while(!is_dir($path . '/' . $dir))
		    {
		        $dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
		        mkdir($path . '/' . $dir);
		        if($i >= 50)
		        {
		            exit; //failsafe
		        }
		        $i++;
		    }
		    
		    return $dir;
		}
		
	
		
		
		/**
		 * @author Ancuta
		 * ISPC-2642 10-11.08.2020 
		 * @param array $post
		 * @return void|string
		 */
		public function update_file_info($post = array())
		{
    
		    if(empty($post['ipid']) || empty($post['file_id'])){
		        return;
		    }
		    // first we get the existing data
		    $existing_file_info= PatientFileUpload::_get_patient_file($post['ipid'],$post['file_id']);

		    
		    // file tag rights
		    $pat_files_tags_rights = explode(',', $post['pat_files_tags_rights']);
		    
		    
            $update_title = false;
		    if(trim($existing_file_info[$post['file_id']]['title']) != $post['title']){
		        $update_title = true;
		    }
            
		    
		    if(strlen($post['title']) < 1)
		    {
		        $update_title = false;
		    }
			//ISPC-2831 Dragos 15-23.03.2021

			$fluplod = Doctrine::getTable('PatientFileUpload')->find($post['file_id']);

		    $title = "Datei wurde ".$existing_file_info[$post['file_id']]['title']." aktualisiert";
		    if($update_title ){
		        $title = "Datei wurde umbenannt.'".$existing_file_info[$post['file_id']]['title']."' -> '".$post['title']."'";
		        
    		    // UPDATE TITLE 

    		    $fluplod->title = Pms_CommonData::aesEncrypt($post['title']);
    		    
		    }

			$fluplod->meta_name = !empty(trim($post['meta_name'])) ? Pms_CommonData::aesEncrypt($post['meta_name']) : $fluplod->meta_name;
			$fluplod->comment = !empty(trim($post['comment'])) ? Pms_CommonData::aesEncrypt($post['comment']) : null;
			$fluplod->admission_id = !empty(intval($post['admission_id'])) ? intval($post['admission_id']) : null;
			$fluplod->save();

			// -- //
			
		    // delete existing tags  
// 		    PatientFile2tags
		    $this->clear_file_tags($post['file_id']);
		    
		    
		    $tags_labels = is_array($post['tag_name']) ? $post['tag_name'] : explode(',', $post['tag_name']);
		    
		    $tags_labels = array_map('trim', $tags_labels);
		    $course_tags_post = implode(', ',$tags_labels);
		    $tag_course_title = "";
		    if($post['file_id'])
		    {
		        if(count($tags_labels) < 1){
		            $tag_course_title = "Etikett/en entfernt";
		        } else{
		            
    		        $patient_fl = Doctrine_Query::create()
    		        ->select("*")
    		        ->from('PatientFileTags')
    		        ->where('(client = ? OR client = 0)', $post['clientid'])
    		        ->andWhereIn('tag', $tags_labels)
    		        ->andWhere('isdelete = 0')
    		        ;
    		        $tags = $patient_fl->fetchArray();
    		        
    		        //existing tags in db
    		        $existing_tags = array(); //that are not deleted
    		        foreach($tags as $k_tag => $v_tag) {
    		            $existing_tags[$v_tag['id']] = trim($v_tag['tag']);
    		        }
    		        
    		        //separate new tags from post'ed tags
    		        $new_tags = array();
    		        if (in_array('create', $pat_files_tags_rights)) {
    		            foreach ($tags_labels as $v_post_tag) {
    		                if ( ! in_array($v_post_tag, $existing_tags)) {
    		                    $new_tags[] = $v_post_tag;
    		                }
    		            }
    		        }
    		        
    		        //insert new tags
    		        foreach($new_tags as $k_tag => $v_new_tag)
    		        {
    		            if(strlen(trim(rtrim($v_new_tag))) > '0')
    		            {
    		                $mtag = new PatientFileTags();
    		                $mtag->client = $post['clientid'];
    		                $mtag->tag = $v_new_tag;
    		                $mtag->save();
    		                $inserted_master_tag = $mtag->id;
    		                
    		                if($inserted_master_tag)
    		                {
    		                    //append newly created tags to existing db tags
    		                    $existing_tags[$inserted_master_tag] = $mtag->tag;
    		                }
    		            }
    		        }
    		        
    		        //create file2tags array
    		        //append uploaded by user tag
    		        if($existing_tags)
    		        {
    		            if(!in_array('use', $pat_files_tags_rights))
    		            {
    		                $existing_tags = array();
    		            }
    		        }
    		        
    		        $existing_tags['1'] = array();
    		        foreach($existing_tags as $k_post_tag_id => $v_tag_name)
    		        {
    		            $tags_data[] = array(
    		                'file' => $post['file_id'],
    		                'tag' => $k_post_tag_id
    		            );
    		        }
    		        
    		        $collection = new Doctrine_Collection('PatientFile2tags');
    		        $collection->fromArray($tags_data);
    		        $collection->save();
    		        
    		        //ISPC - 2129
    		        if (in_array('Notfallplan', $tags_labels))
    		        {
    		            $actv = new PatientFileVersion();
    		            if($post['active_version'] == '1')
    		            {
    		                $factv = $actv->get_reset_active_version($post['ipid']);
    		            }
    		            
    		            $actv->file = $post['file_id'];
    		            $actv->active_version = $post['active_version'];
    		            $actv->save();
    		        }
    		        
    		        $tag_course_title = "Etikett/en ".$course_tags_post." wurden aktualisiert.";
    		        
    		        
    		    }
    		        // write to course
//     		        $cust = new PatientCourse();
//     		        $cust->ipid = $post['ipid'];
//     		        $cust->course_date = date("Y-m-d H:i:s",time());
//     		        $cust->course_type=Pms_CommonData::aesEncrypt("K");
//     		        $cust->user_id = $post['userid'];
//     		        $cust->recordid = $post['file_id'];
// //     		        $cust->tabname=Pms_CommonData::aesEncrypt("fileupload");
//     		        $cust->course_title = Pms_CommonData::aesEncrypt($tag_course_title);
//     		        $cust->save();
		    }
		    if( strlen($tag_course_title) > 0 ){
    		    $title = $title ." \n ".$tag_course_title;
		    }
		    // write to course
		    $cust = new PatientCourse();
		    $cust->ipid = $post['ipid'];
		    $cust->course_date = date("Y-m-d H:i:s",time());
		    $cust->course_type=Pms_CommonData::aesEncrypt("K");
		    $cust->user_id = $post['userid'];
		    $cust->recordid = $post['file_id'];
		    $cust->course_title=Pms_CommonData::aesEncrypt($title);
		    $cust->save();
		    
		    
		    
		    return '1';
		}
		
		
		/**
		 * @author Ancuta
		 * ISPC-2642 10-11.08.2020 
		 * @param unknown $file
		 */
		public function clear_file_tags($file)
		{
		    if(empty($file)){
		        return;
		    }
		    $res = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientFile2tags')
		    ->where('file = ?', $file)
		    ->andWhere('isdelete = "0"');
		    $res_array = $res->fetchArray();
		    
		    if($res_array)
		    {
		        foreach($res_array as $k_res => $v_res)
		        {
		            $fluplod = Doctrine::getTable('PatientFile2tags')->find($v_res['id']);
		            $fluplod->isdelete = 1;
		            $fluplod->save();
		        }
		    }
		    
		}
		
		
		
		
		
		
		
	}

?>
