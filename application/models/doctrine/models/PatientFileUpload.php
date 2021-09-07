<?php
	// Maria:: Migration ISPC to CISPC 08.08.2020
	Doctrine_Manager::getInstance()->bindComponent('PatientFileUpload', 'MDAT');

	class PatientFileUpload extends BasePatientFileUpload {

		public $triggerformid = 17;
		public $triggerformname = "frmpatientfileupload";

		//ISPC-2832 Dragos added meta_name and comment decryption
		public function getFileData($ipid)
		{
		    
		    $include_tabnames = '"X",';
		    $include_tabnames .= '"wounddocumentation_incr","wounddocumentation_uploaded_img", "fallprotocolform_save"';
		    $include_tabnames .= ',"'. WlAssessment::PATIENT_FILE_TABNAME . '"';
		    

			$patient = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
				AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
				AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type,
				AES_DECRYPT(meta_name,'" . Zend_Registry::get('salt') . "') as meta_name,
				AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('recordid = "0" OR (recordid != "0" AND tabname in ('.$include_tabnames.')) ')
			->orderBy('create_date DESC');
			$fl = $patient->execute();
			$filearray = $fl->toArray();
			
			return $filearray;
		}
		//Maria:: Migration CISPC to ISPC 22.07.2020
        public function getFilesByTabname($ipid, $tabname)       {

		    $patient = Doctrine_Query::create()
                ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
				AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
				AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
                ->from('PatientFileUpload')
                ->where('ipid=?' , $ipid )
                ->andWhere('tabname=?', $tabname)
                ->andWhere('isdeleted=0')
                ->orderBy('create_date DESC');
            $fl = $patient->execute();
            $filearray = $fl->toArray();

            return $filearray;
        }

		public function getContactFormFileData($ipid = '', $form_ids = array())
		{
			if (empty($ipid) || empty($form_ids)) {
				return array();
			}
			
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhereIn('recordid', $form_ids)
				->andWhere('tabname = "contact_form"');
//				print_r($patient_files->getSqlQuery());
//				print_r($form_ids);
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}

		public function getSgbvFormFileData($ipid, $form_id)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('recordid = "' . $form_id . '"')
				->andWhere('tabname = "sgbv_form"');
//				print_r($patient_files->getSqlQuery());
//				print_r($form_id);
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}

		public function get_entrance_assessment_file_data($ipid)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('tabname = "entrance_assessment" OR tabname = "entrance_assesment_new"  ');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}
		
		public function get_kinder_entrance_assessment_file_data($ipid)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('tabname = "kinder_entrance_assessment" OR tabname = "kinder_entrance_assesment_new"  ');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}
		
		public function get_therapyplan_file_data($ipid)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('tabname = "therapyplan"');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}

		public function get_zapv_assessment_file_data($ipid, $type)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('tabname = "save_zapv_assessment_' . $type . '" OR tabname="zapv_assessment_' . $type . '"');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}

		public function get_zapv_assessment_ii_file_data($ipid, $type)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('tabname = "save_zapv_assessment_ii_' . $type . '" OR tabname="zapv_assessment_ii_' . $type . '"');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}

		public function getBarthelScoreFileData($ipid /*, $form_ids */)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				//->andWhereIn('recordid', $form_ids) // modified from ISPC 1193
				->andWhere('tabname = "barthelscore"')
				->andWhere('isdeleted = "0"');
			if($_REQUEST['dbgfiles'])
			{
				print_r($patient_files->getSqlQuery());
			}
			$filearray = $patient_files->fetchArray();

			if($_REQUEST['dbgfiles'])
			{
				print_r($filearray);
				exit;
			}

			return $filearray;
		}
		
		public function get_wound_documentation_file_data($ipid, $formid = "")
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"');
				if(!empty($formid)){
					$patient_files->andWhere('recordid = "'.$formid.'"');
				}
				$patient_files->andWhere('tabname in ("wounddocumentation_incr","wounddocumentation_uploaded_img")');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}
		
		public function get_pain_questionnaire_file_data($ipid, $formid = false)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"');
				if($formid){
				    $patient_files->andWhere('recordid = "'.$formid.'"');
				}
				$patient_files->andWhere('tabname = "painquestionnaire_save"');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}
		public function get_daystructure_file_data($ipid, $formid)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('recordid = "'.$formid.'"')
				->andWhere('tabname = "daystructure_save"');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}

		public function get_fall_protokol_file_data($ipid)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"')
				->andWhere('tabname = "fallprotocolform_save"');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}
		
		public function get_muster63_file_data($ipid, $formid = false)
		{
			$patient_files = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('PatientFileUpload')
				->where('ipid="' . $ipid . '"');
				if($formid){
					$patient_files->andWhere('recordid = "'.$formid.'"');
				}
				$patient_files->andWhere('tabname = "verordnungtp_save"');
			$filearray = $patient_files->fetchArray();

			return $filearray;
		}
		
		public function get_muster63kinder_file_data($ipid, $formid = false)
		{
			$patient_files = Doctrine_Query::create()
			->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
								->from('PatientFileUpload')
								->where('ipid="' . $ipid . '"');
								if($formid){
									$patient_files->andWhere('recordid = "'.$formid.'"');
								}
								$patient_files->andWhere('tabname = "verordnungtpkinder_save"');
								$filearray = $patient_files->fetchArray();
		
								return $filearray;
		}
		
		public function get_muster1a_file_data($ipid, $formid = false)
		{
			$patient_files = Doctrine_Query::create()
			->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
								->from('PatientFileUpload')
								->where('ipid="' . $ipid . '"');
								if($formid){
									$patient_files->andWhere('recordid = "'.$formid.'"');
								}
								$patient_files->andWhere('tabname = "muster1a1_pdf"');
								$filearray = $patient_files->fetchArray();
		
								return $filearray;
		}
		
		public function get_anlage2_file_data($ipid, $formid)
		{
			$patient_files = Doctrine_Query::create()
			->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('PatientFileUpload')
					->where('ipid="' . $ipid . '"')
					->andWhere('recordid = "'.$formid.'"')
					->andWhere('tabname = "anlage2_save"');
			$filearray = $patient_files->fetchArray();
		
			return $filearray;
		}
		public function get_anlage3_file_data($ipid, $formid)
		{
			$patient_files = Doctrine_Query::create()
			->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('PatientFileUpload')
					->where('ipid="' . $ipid . '"')
					->andWhere('recordid = "'.$formid.'"')
					->andWhere('tabname = "anlage3nordrhein_save"');
			$filearray = $patient_files->fetchArray();
		
			return $filearray;
		}
		public function get_participationpolicy_file_data($ipid)
		{
			$patient_files = Doctrine_Query::create()
			->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('PatientFileUpload')
					->where('ipid="' . $ipid . '"')
					->andWhere('tabname = "participationpolicy_save"');
			$filearray = $patient_files->fetchArray();
		
			return $filearray;
		}

        public function get_downloaded_file_path($ipid, $doc_id)
        {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            
            if ($doc_id > 0) {
                $patient = Doctrine_Query::create()->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
    					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
    					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
                    ->from('PatientFileUpload')
                    ->where('id="' . $doc_id . '"');
                $fl = $patient->execute();
                
                if ($fl) {
                    $flarr = $fl->toArray();
                    
                    $explo = explode("/", $flarr[0]['file_name']);
                    
                    $fdname = $explo[0];
                    $flname = utf8_decode($explo[1]);
                }
                $new_filename = $doc_id.$flname;
//                 $con_id = Pms_FtpFileupload::ftpconnect();
                
//                 if ($con_id) {
//                     $old = false;
//                     if ($_REQUEST['old']) {
//                         $old = true;
//                     }
//                     $upload = Pms_FtpFileupload::filedownload($con_id, 'uploads/' . $fdname . '.zip', 'uploads/' . $fdname . '.zip', $old);
//                     Pms_FtpFileupload::ftpconclose($con_id);
//                 }
                
                
                	
                // check if was uploaded after/in T0 date
                $client_merge_date = strtotime('2013-12-18 13:00:00'); // client merge date
                $req_file_date = strtotime(date('Y-m-d H:i:s', strtotime($flarr[0]['create_date'])));
                $file_password = '';
                
                // if uploaded before client_merge_date use old client(62) password to open it
                if ($req_file_date < $client_merge_date && $clientid == '61') {
                    // check the patient in epid_ipid
                    $patient_epid = Pms_CommonData::getEpidcharsandNum($ipid);
                    
                    if ($patient_epid['char'] == 'DST') // patient belongs to the OLD client(62)
                    {
                        // OLD client upload password
                        $file_password = 'j5qqil01gklqolq';
                    } 
                    else // patient not found in old client => use curent client pass
                    {
                        $file_password = $logininfo->filepass;
                    }
                }
                else // uploaded after client_merge_date use new client password to open it
                {
                    $file_password = $logininfo->filepass;
                }
                
//             $cmd = "unzip -P " . $file_password . " uploads/" . $fdname . ".zip;";
//             exec($cmd);
            
            $old = $_REQUEST['old'] ? true : false;
            if (($path = Pms_CommonData::ftp_download('uploads/' . $fdname . '.zip' , $file_password , $old , null , $flarr[0]['file_name'], "PatientFileUpload", $flarr[0]['id'])) === false){
            	//failed to download file
            }
//             $path = $_SERVER['DOCUMENT_ROOT'] . "/ispc2015/public/uploads/" . $fdname . "/"; // change the path to fit your websites document structure
//             $path = $_SERVER['DOCUMENT_ROOT'] . "/uploads/" . $fdname . "/"; // change the path to fit your websites document structure
            $fullPath = $path . "/" . $flname;
//             die($fullPath);
             //create public/joined_files/ dir
            while(!is_dir(PDFJOIN_PATH))
            {
                mkdir(PDFJOIN_PATH);
                if($i >= 50)
                {
                    exit; //failsafe
                }
                $i++;
            }
            
             //create public/joined_files/$clientid dir
            $pdf_path = PDFJOIN_PATH . '/' . $clientid;
            
            while(!is_dir($pdf_path))
            {
                mkdir($pdf_path);
                if($i >= 50)
                {
                    exit; //failsafe
                }
                $i++;
            }
            
            $source = $fullPath;
            $destination = $pdf_path.'/'.$new_filename;

            if(is_file($source)){
                copy($source,$destination);
                return $destination;
            } else{
                return false;
            }
        }
    }
		
    public function get_muster2b_file_data($ipid, $formid = false)
    {
    	$patient_files = Doctrine_Query::create()
    	->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
    						->from('PatientFileUpload')
    						->where('ipid="' . $ipid . '"');
    						if($formid){
    							$patient_files->andWhere('recordid = "'.$formid.'"');
    						}
    						$patient_files->andWhere('tabname = "muster2b_save"');
    						$filearray = $patient_files->fetchArray();
    
    						return $filearray;
    }	
		
    
    /**
     * be aware, the fn name may be misleading - this is how Doctrine works!
     * this fn will insert new if there is no db-record object in our class...
     * if you called second time, or you fetchOne, it will update!
     * fn was intended for single record, not collection
     * @param array $params
     * @return boolean|number
     * return $this->id | false if you don't have the mandatory_columns in the params
     */
	public function set_new_record($params = array())
	{
			
		if (empty($params) || !is_array($params)) { 
			return false;// something went wrong
		}
		foreach ($params as $k => $v) 
		if (isset($this->{$k})) {
				
			//next columns should be encrypted
			switch ($k) {
				case "title":
				case "file_name":
				case "file_type":
					$v = Pms_CommonData::aesEncrypt($v);
				break;			
			}		
			$this->{$k} = $v;
				
		}
		$this->save();
		return $this->id;
	}
		

	/**
	 * get files that are uploaded from stammdaten grow6 = ACP
	 * they will be ordered by file_date DESC, id DESC
	 * please use with array(ipids)
	 * 
	 * @param array $ipid
	 * @return void|number
	 * ISPC-2671:: Ancuta 14.09.2020  Added  acp_file_emergencyplan  in tabname condition 
	 */
	public static function get_acp_files( $ipid = array() )
	{
		$result = array();
		
		if ( empty($ipid) || ! is_array($ipid)) {
			return $result;// something went wrong
		}
		
		$acp_files = Doctrine_Query::create()
			->select("id, ipid, file_date, tabname, AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title ")
			->from('PatientFileUpload')
			->whereIn('ipid', $ipid)
			->andWhere('isdeleted = 0')
			->andWhereIn('tabname', array('acp_file_living_will', 'acp_file_care_orders', 'acp_file_healthcare_proxy','acp_file_emergencyplan'))
			->orderBy('file_date DESC, id DESC')
			->fetchArray();
		
		if ( ! empty($acp_files))
		foreach ( $acp_files as $row ) {
			$result[$row['ipid']][$row['tabname']][] = $row;
		}
		
		
		return $result;
	}
        
        // ISPC - 2253 - get besd survey files
    public function get_besdsurvey_file_data($ipid)
    {
        $result = array();
        
        if (empty($ipid)) {
            return $result; // something went wrong
        }
         
        $patient_files = Doctrine_Query::create()->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
            ->from('PatientFileUpload')
            ->where('ipid= ?', $ipid)
            ->andWhere('tabname = "BesdSurvey_ispc2253"');
        $result = $patient_files->fetchArray();
        
        return $result;
    }
	
    public function get_emergency_files($ipid)
    {
    	//ISPC - 2129
    	$result = array();
    
    	if (empty($ipid)) {
    		return $result; // something went wrong
    	}
    	 
    	$patient_files = Doctrine_Query::create()->select("pfup.*,AES_DECRYPT(pfup.title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(pfup.file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(pfup.file_type,'" . Zend_Registry::get('salt') . "') as file_type, pfv.*, pft.*")
    						->from('PatientFile2tags pft')
    						->leftJoin('pft.PatientFileUpload pfup')
    						->leftJoin('pfup.PatientFileVersion pfv')
    						->where('pfup.ipid= ?', $ipid)
    						->andWhere('pft.file = pfup.id')
    						->andWhere('pfv.file = pfup.id')
    						->andWhere('pft.tag = "21"');
    						//->orderBy('pfv.active_version DESC');
    						$result = $patient_files->fetchArray();
    //print_r($result); exit;
    						return $result;
    }

    /**
     * @author Ancuta
     * copy of get_acp_files
     * 05.09.2019
     * ISPC-2420
     * @param unknown $ipid
     * @return multitype:|Ambigous <multitype:, Doctrine_Collection>
     */
    public static function get_demstepcare_files( $ipid = array(), $recordid = 0)
    {
        
        $result = array();
    
        if ( empty($ipid) || ! is_array($ipid)) {
            return $result;// something went wrong
        }
    
        $files_q = Doctrine_Query::create()
        ->select("id, ipid, file_date, create_date, tabname, AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title ")
        ->from('PatientFileUpload')
        ->whereIn('ipid', $ipid)
        ->andWhere('isdeleted = 0');
        if(!empty($recordid)){
            $files_q->andWhere('recordid = ?',$recordid);
        }
        $files_q->andWhereIn('tabname', array('patient_demstepcare_page'))
        ->orderBy('file_date DESC, id DESC');
        $files = $files_q->fetchArray();
    
        if ( ! empty($files))
            foreach ( $files as $row ) {
                $result[$row['ipid']][] = $row;
            }
        
        return $result;
    }
    
    /**
     * ISPC-2642 Ancuta 10-11.08.2020
     * @param unknown $ipid
     * @param unknown $file_id
     * @return void|array|Doctrine_Collection
     */
    
    public static function _get_patient_file($ipid,$file_id)
    {
        if (empty($ipid) || empty($file_id)) {
            return;
        }

        $result = array();
        //ISPC-2831 Dragos added meta_name and comment decryption
        $result = Doctrine_Query::create()->select("pfup.*,AES_DECRYPT(pfup.title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(pfup.file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(pfup.file_type,'" . Zend_Registry::get('salt') . "') as file_type,
                        AES_DECRYPT(pfup.meta_name,'" . Zend_Registry::get('salt') . "') as meta_name,
                        AES_DECRYPT(pfup.comment,'" . Zend_Registry::get('salt') . "') as comment, pft.*")
            ->from('PatientFileUpload pfup INDEXBY id')
            ->leftJoin('pfup.PatientFile2tags pft')
            ->where('pfup.ipid= ?', $ipid)
            ->andWhere('pfup.id =?', $file_id)
            ->andWhere('pft.id is NULL or pft.isdelete = 0') 
            ->fetchArray();
            
        return $result;
    }
}

?>
