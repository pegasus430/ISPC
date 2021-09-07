<?php

	Doctrine_Manager::getInstance()->bindComponent('FamilyDoctor', 'SYSDAT');

	class FamilyDoctor extends BaseFamilyDoctor {

		public function getFamilyDoc($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('FamilyDoctor')
				->where("id=?",  $id);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getFamilyDocMultiple($ids)
		{
			/*if(strlen($ids) == '0')
			{
				$ids = '"9999999999"';
			}*/
			if(strlen($ids) == '0')
			{
				return;
			}
			
			$drop = Doctrine_Query::create()
				->select('*')
				->from('FamilyDoctor')
				->where('id in (' . $ids . ')');
			$droparray = $drop->fetchArray();

			if($droparray)
			{
				foreach($droparray as $drop_item)
				{
					$fdocarr[$drop_item['id']] = $drop_item;
				}

				return $fdocarr;
			}
		}

		public function getFamilyDoctors($ipid = false, $letter = false, $keyword = false, $arrayids = false,$all_of_client = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			if($ipid != false)
			{
				$patientmaster = new PatientMaster();
				$pat_details = $patientmaster->getMasterData(null, null, null, $ipid);

				$ipid_sql = " AND id = '" . $pat_details['familydoc_id'] . "'";
			}
			else
			{
			    if($all_of_client){
    				$ipid_sql = "";
			    } else{
    				$ipid_sql = " AND indrop=0 AND isdelete =0 AND valid_till = '0000-00-00'";
			    }
			}

			if($keyword != false)
			{
				$keyword_sql = " AND concat(first_name, last_name) like '%" . ($keyword) . "%'";
			}

			if($letter != false)
			{
				$keyword_sql = " AND (first_name like '" . ($letter) . "%'  OR last_name like '" . ($letter) . "%')";
			}

			if($arrayids != false)
			{
				$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
				$ipid_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('FamilyDoctor')
				->where("clientid='" . $clientid . "' AND (first_name != '' or last_name != '') " . $ipid_sql . $keyword_sql . $array_sql)
			//echo $drop->getSqlQuery();
			->orderBy('last_name ASC');
			$droparray = $drop->fetchArray();
			return $droparray;
		}

		public function clone_record($doc_id, $target_client)
		{
			//get curent family doctor
			$family_doc = $this->getFamilyDoc($doc_id);
 
			if($family_doc)
			{
				$fdoc = new FamilyDoctor();
				//IPSC-2614
				$pc_listener = $fdoc->getListener()->get('IntenseConnectionListener');
				$pc_listener->setOption('disabled', true);
				//--
				$fdoc->clientid = $target_client;
				$fdoc->practice = $family_doc[0]['practice'];
				$fdoc->last_name = $family_doc[0]['last_name'];
				$fdoc->first_name = $family_doc[0]['first_name'];
				$fdoc->title = $family_doc[0]['title'];
				$fdoc->salutation = $family_doc[0]['salutation'];
				$fdoc->title_letter = $family_doc[0]['title_letter'];
				$fdoc->salutation_letter = $family_doc[0]['salutation_letter'];
				$fdoc->street1 = $family_doc[0]['street1'];
				$fdoc->street2 = $family_doc[0]['street2'];
				$fdoc->zip = $family_doc[0]['zip'];
				$fdoc->city = $family_doc[0]['city'];
				$fdoc->doctornumber = $family_doc[0]['doctornumber'];
				$fdoc->doctor_bsnr = $family_doc[0]['doctor_bsnr'];
				$fdoc->phone_practice = $family_doc[0]['phone_practice'];
				$fdoc->phone_private = $family_doc[0]['phone_private'];
				$fdoc->phone_cell = $family_doc[0]['phone_cell'];
				$fdoc->fax = $family_doc[0]['fax'];
				$fdoc->email = $family_doc[0]['email'];
				$fdoc->kv_no = $family_doc[0]['kv_no'];
				$fdoc->medical_speciality = $family_doc[0]['medical_speciality'];
				$fdoc->comments = $family_doc[0]['comments'];
				$fdoc->valid_from = $family_doc[0]['valid_from'];
				$fdoc->valid_till = $family_doc[0]['valid_till'];
				$fdoc->indrop = '1';  
				$fdoc->save();

				//IPSC-2614
				$pc_listener->setOption('disabled', false);
				//--
				
				if($fdoc)
				{
					return $fdoc->id;
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

		public function get_family_doctors_multiple($ids, $details = false , $details_all = false)
		{
			if(is_array($ids) && !empty($ids))
			{
				$ids_sql = array_unique($ids);
			}
			else
			{
				$ids_sql = array($ids);
			}

			if( empty($ids_sql)) {
				return array();
			}
			$drop = Doctrine_Query::create()
				->select('*')
				->from('FamilyDoctor')
				->whereIn('id', $ids_sql);
			$droparray = $drop->fetchArray();

 			$fdocarr =  array();
			if($droparray)
			{
				if(!$details && !$details_all)
				{
					foreach($droparray as $drop_item)
					{
						$fdocarr[$drop_item['id']] = $drop_item;
					}
				}
				elseif($details)
				{
					foreach($droparray as $drop_item)
					{
						$fdocarr[$drop_item['id']] .= $drop_item['last_name'];
						if(strlen($drop_item['first_name']) > 0){
							$fdocarr[$drop_item['id']] .= ', ' . $drop_item['first_name'];
						}
						$fdocarr[$drop_item['id']] .= '<br/>' . $drop_item['street1'];
						$fdocarr[$drop_item['id']] .= '<br/>' . $drop_item['zip'];
						$fdocarr[$drop_item['id']] .= ' ' . $drop_item['city'];
					}
				}
				elseif($details_all)
				{
					foreach($droparray as $drop_item)
					{
						$fdocarr[$drop_item['id']] .= $drop_item['last_name'];
						if(strlen($drop_item['first_name']) > 0){
							$fdocarr[$drop_item['id']] .= ', ' . $drop_item['first_name'];
						}
						$fdocarr[$drop_item['id']] .= '<br/>' . $drop_item['street1'];
						$fdocarr[$drop_item['id']] .= '<br/>' . $drop_item['zip'];
						$fdocarr[$drop_item['id']] .= ' ' . $drop_item['city'];
						$fdocarr[$drop_item['id']] .= '<br/>' . $drop_item['phone_practice'];
						$fdocarr[$drop_item['id']] .= '<br/>' . $drop_item['fax'];
						
					}
				}
				return $fdocarr;
			}
		}

		

		public function client_family_doctors($client)
		{
			if($client){
				$drop = Doctrine_Query::create()
				->select('*')
				->from('FamilyDoctor')
				//->where('clientid = '.$client);
				->where("clientid=?",  $client);
				
				$droparray = $drop->fetchArray();
			
				if($droparray)
				{
					foreach($droparray as $drop_item)
					{
						$fdocarr[$drop_item['id']] = $drop_item;
					}
			 
					return $fdocarr;
				}
			}
		}
		

		public static function beautifyName( &$usrarray )
		{ 
		    //mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
		    if ( empty($usrarray) || ! is_array($usrarray)) {
		        return;
		    }
		    
		    if (is_array($usrarray[0])){
		        $usr_array =  $usrarray;
		    } else {
		        $usr_array =  array($usrarray);
		    }	   
		     
		    foreach ( $usr_array as &$k )
		    {
		        if ( ! is_array($k) || isset($k['nice_name'])) {
		            continue; // varaible allready exists, use another name for the variable
		        }
		
		        $k ['nice_name']  = trim($k['title']) != "" ? trim($k['title']) . " " : "";
		        $k ['nice_name']  .= trim($k['last_name']);
		        $k ['nice_name']  .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
		
		    }
		   
		    if ( ! is_array($usrarray[0])){
		        $usr_array =  $usr_array[0];
		    }
		    
		    $usrarray = $usr_array;
		}
		
		
		/*
		 * if $data[__thisIsNotThePatientsFamilyDoctor] then this is not added to the patient_master - TODO-1752
		 * else this will also update the patient
		 */
		public function findOrCreateOneBy($fieldName, $value, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
		{
		    $isNew = false; //familydoc_id must be updateted also
		    
		    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
		        
		
		        if ($fieldName != $this->getTable()->getIdentifier()) {
		            $entity = $this->getTable()->create(array( $fieldName => $value));
		        } else {
		            $entity = $this->getTable()->create();
		        }
		        
		        $data['indrop'] = isset($data['indrop']) ? $data['indrop'] : 1;
		        
		        unset($data[$this->getTable()->getIdentifier()]);
		        
		        $isNew = true; //familydoc_id must be updateted also
		    }
		
// 		    $this->_encryptData($data);
		
		    $entity->fromArray($data); //update
		
		    $entity->save(); //at least one field must be dirty in order to persist

		    if ($entity->id && ! empty($data['ipid']) && ! isset($data['__thisIsNotThePatientsFamilyDoctor']) ) {
		        
		        $ent = new PatientMaster();
		        
		        $dataPatient = array('familydoc_id' => $entity->id);
		        
		        if (isset($data['fdoc_caresalone'])) {
		            $dataPatient['fdoc_caresalone'] = $data['fdoc_caresalone'];
		        }
		        
		        $ent->findOrCreateOneBy('ipid', $data['ipid'], $dataPatient);
		    }
		    
		    return $entity;
		}
		
		
	}

?>