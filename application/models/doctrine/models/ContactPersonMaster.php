<?php

	Doctrine_Manager::getInstance()->bindComponent('ContactPersonMaster', 'IDAT');

	class ContactPersonMaster extends BaseContactPersonMaster {

		public $triggerformid = 15;
		public $triggerformname = "frmpatientcontact";

		protected $_encypted_columns = array(
		    'cnt_first_name',
		    'cnt_middle_name',
		    'cnt_last_name',
		    'cnt_title',
		    'cnt_street1',
		    'cnt_street2',
		    'cnt_zip',
		    'cnt_city',
		    'cnt_phone',
		    'cnt_email',
		    'cnt_mobile',
		    'cnt_comment',
		    'cnt_nation',
		    'cnt_custody',
		);
		
		
		public function getPatientContact($ipid, $hide_deleted = true)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$sql = "*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name";
			$sql .=",AES_DECRYPT(	cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title";
			$sql .= ",AES_DECRYPT(cnt_salutation,'" . Zend_Registry::get('salt') . "') as cnt_salutation";
			$sql .=",AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone";
			$sql .=",AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody";

			$adminvisible = PatientMaster::getAdminVisibility($ipid);
			if($logininfo->usertype == 'SA' && $adminvisible != 1)
			{
				$sql = "*,'" . $hidemagic . "' as cnt_first_name";
				$sql .=",'" . $hidemagic . "' as cnt_middle_name";
				$sql .=",'" . $hidemagic . "' as cnt_last_name";
				$sql .=",'" . $hidemagic . "' as cnt_salutation";
				$sql .=",'" . $hidemagic . "' as cnt_title";
				$sql .=",'" . $hidemagic . "' as cnt_street1";
				$sql .=",'" . $hidemagic . "' as cnt_street2";
				$sql .=",'" . $hidemagic . "' as cnt_zip";
				$sql .=",'" . $hidemagic . "' as cnt_city";
				$sql .=",'" . $hidemagic . "' as cnt_phone";
				$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone_dec";
				$sql .=",'" . $hidemagic . "' as cnt_mobile";
				$sql .=",'" . $hidemagic . "' as cnt_email";
				$sql .=",'" . $hidemagic . "' as cnt_comment";
				$sql .=",'" . $hidemagic . "' as cnt_nation";
				$sql .=",'" . $hidemagic . "' as cnt_custody";
			}


			$drop = Doctrine_Query::create()
				->select($sql)
				->from('ContactPersonMaster')
				->where("ipid=?", $ipid);
			if($hide_deleted)
			{
				$drop->andWhere('isdelete = 0');
			}
			$drop->orderby('create_date ASC');
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			return $droparray;
		}

		public function getPatientContactById($cid, $addressbook = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$sql = "*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name";
			$sql .=",AES_DECRYPT(	cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone";
			$sql .= ",AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody";

			$adminvisible = PatientMaster::getAdminVisibility($ipid);
			if(($logininfo->usertype == 'SA' && !$adminvisible) && !$addressbook)
			{
				$sql = "*,'" . $hidemagic . "' as cnt_first_name";
				$sql .=",'" . $hidemagic . "' as cnt_middle_name";
				$sql .=",'" . $hidemagic . "' as cnt_last_name";
				$sql .=",'" . $hidemagic . "' as cnt_title";
				$sql .=",'" . $hidemagic . "' as cnt_street1";
				$sql .=",'" . $hidemagic . "' as cnt_street2";
				$sql .=",'" . $hidemagic . "' as cnt_zip";
				$sql .=",'" . $hidemagic . "' as cnt_city";
				$sql .=",'" . $hidemagic . "' as cnt_phone";
				$sql .=",'" . $hidemagic . "' as cnt_mobile";
				$sql .=",'" . $hidemagic . "' as cnt_email";
				$sql .=",'" . $hidemagic . "' as cnt_comment";
				$sql .=",'" . $hidemagic . "' as cnt_nation";
				$sql .=",'" . $hidemagic . "' as cnt_custody";
			}


			$drop = Doctrine_Query::create()
				->select($sql)
				->from('ContactPersonMaster')
				->where("id=?", $cid)
				->andWhere('isdelete = 0');
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			return $droparray;
		}

		public function getVerPatientContact($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name
					,AES_DECRYPT(	cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name
					,AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name
					,AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title
					,AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1
					,AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2
					,AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip
					,AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city
					,AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone
					,AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile
					,AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email	
					,AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment
					,AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody
					,AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation")
				->from('ContactPersonMaster')
				->where("cnt_hatversorgungsvollmacht=1")
				->andWhere("ipid=?", $ipid)
				->andWhere('isdelete = 0');
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			return $droparray;
		}

		public function getPatientLegalguardian($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name
				  ,AES_DECRYPT(	cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name
				   ,AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name
				   ,AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title
				   ,AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1
				   ,AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2
				   ,AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip
				   ,AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city
				   ,AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone
				   ,AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile
				   ,AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email	
				   ,AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment
				   ,AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation")
				->from('ContactPersonMaster')
				->where("cnt_legalguardian=1")
				->andWhere("ipid=?", $ipid)
				->andWhere('isdelete = 0');
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			return $droparray;
		}

		public function get2PatientContact($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$sql = "*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name";
			$sql .=",AES_DECRYPT(	cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone";
			$sql .= ",AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody";

			$adminvisible = PatientMaster::getAdminVisibility($ipid);
			if($logininfo->usertype == 'SA' && $adminvisible != 1)
			{
				$sql = "*,'" . $hidemagic . "' as cnt_first_name";
				$sql .=",'" . $hidemagic . "' as cnt_middle_name";
				$sql .=",'" . $hidemagic . "' as cnt_last_name";
				$sql .=",'" . $hidemagic . "' as cnt_title";
				$sql .=",'" . $hidemagic . "' as cnt_street1";
				$sql .=",'" . $hidemagic . "' as cnt_street2";
				$sql .=",'" . $hidemagic . "' as cnt_zip";
				$sql .=",'" . $hidemagic . "' as cnt_city";
				$sql .=",'" . $hidemagic . "' as cnt_phone";
				$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone_dec";
				$sql .=",'" . $hidemagic . "' as cnt_mobile";
				$sql .=",'" . $hidemagic . "' as cnt_email";
				$sql .=",'" . $hidemagic . "' as cnt_comment";
				$sql .=",'" . $hidemagic . "' as cnt_nation";
				$sql .=",'" . $hidemagic . "' as cnt_custody";
			}

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('ContactPersonMaster')
				->where("ipid=?", $ipid)
				->andWhere('isdelete = 0')
				->limit(2);
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			return $droparray;
		}

		public function getRepPatientContact($ipid, $hide_deleted = true)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$sql = "*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name";
			$sql .=",AES_DECRYPT(	cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone";
			$sql .= ",AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody";

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('ContactPersonMaster')
				->where("ipid=?", $ipid);
			if($hide_deleted)
			{
				$drop->andWhere('isdelete = 0');
			}

			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			return $droparray;
		}

		/**
		 * Ancuta ISPC-2614 Added 2 extra params  $source_client = false, $target_client = false
		 * @param unknown $ipid
		 * @param unknown $target_ipid
		 * @param boolean $source_client
		 * @param boolean $target_client
		 * @return unknown|boolean
		 */
		public function clone_records($ipid, $target_ipid,$source_client = false, $target_client = false)
		{
		    if( !empty($source_client) && !empty($target_client) ){
		        $all_lists_connections = ConnectionMasterTable::_find_all_lists_connections();
    		    $medication_connected_tables = array('FamilyDegree');
    		    $allowed_connections = array();
    		    $allowed_connections_data = array();
    		    $clone_value = array();
    		    
    		    foreach($medication_connected_tables as $list_model){
    		        if( ( !empty($all_lists_connections[$list_model]['parent2child'][$source_client]) && in_array($target_client,$all_lists_connections[$list_model]['parent2child'][$source_client]) )
    		            ||  ( !empty($all_lists_connections[$list_model]['child2parent'][$target_client]) && $all_lists_connections[$list_model]['child2parent'][$target_client] == $source_client)
    		            ||  ( !empty($all_lists_connections[$list_model]['child2parent'][$source_client]) && $all_lists_connections[$list_model]['child2parent'][$source_client] == $target_client)
    		            || ( in_array($target_client,$all_lists_connections[$list_model]['children']) && in_array($source_client,$all_lists_connections[$list_model]['children'])  )
    		            ){
    		                
    		                if(in_array($target_client,$all_lists_connections[$list_model]['parent2child'][$source_client])){
    		                    $allowed_connections_data[$list_model]['parent'] =  $source_client;
    		                    $allowed_connections_data[$list_model]['child'] =  $target_client;
    		                    $allowed_connections_data[$list_model]['connection_id'] =  $all_lists_connections[$list_model]['parent2connection'][$source_client];
    		                } else if(in_array($source_client,$all_lists_connections[$list_model]['parent2child'][$target_client])){
    		                    $allowed_connections_data[$list_model]['parent'] =  $target_client;
    		                    $allowed_connections_data[$list_model]['child'] =  $source_client;
    		                    $allowed_connections_data[$list_model]['connection_id'] =  $all_lists_connections[$list_model]['parent2connection'][$target_client];
    		                }
    		                
    		                $allowed_connections[$list_model] = true;
    		                
    		        } else{
    		            $allowed_connections[$list_model] = false;
    		        }
    		        
    		        if( $source_client == $allowed_connections_data[$list_model]['parent'] ){
    		            $query = Doctrine_Query::create()
    		            ->select('*')
    		            ->from($list_model)
    		            ->where('clientid = ? ', $target_client  )
    		            ->andWhere('connection_id = ?', $allowed_connections_data[$list_model]['connection_id'])
    		            ->andWhere('isdelete = 0');
    		            $q_res = $query->fetchArray();
    		            
    		            foreach($q_res as $k=>$values){
    		                $clone_value[$list_model][$values['master_id']] = $values['id'];
    		            }
    		            
    		            // search in list - where master id =  curent id and get   id
    		        } else if( $target_client == $allowed_connections_data[$list_model]['parent']  ){
    		            // search in list - where master id =  curent id and get   id
    		            $query = Doctrine_Query::create()
    		            ->select('*')
    		            ->from($list_model)
    		            ->where('clientid = ? ', $source_client  )
    		            ->andWhere('connection_id = ?', $allowed_connections_data[$list_model]['connection_id'])
    		            ->andWhere('isdelete = 0');
    		            $q_res = $query->fetchArray();
    		            
    		            foreach($q_res as $k=>$values){
    		                $clone_value[$list_model][$values['id'] ] = $values['master_id'];
    		            }
    		        }
    		    }
		    }
		    
		    
			$sql = "*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name";
			$sql .=",AES_DECRYPT(cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title";
			$sql .= ",AES_DECRYPT(cnt_salutation,'" . Zend_Registry::get('salt') . "') as cnt_salutation";
			$sql .=",AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone";
			$sql .= ",AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_hatversorgungsvollmacht,'" . Zend_Registry::get('salt') . "') as cnt_hatversorgungsvollmacht";
			$sql .= ",AES_DECRYPT(cnt_legalguardian,'" . Zend_Registry::get('salt') . "') as cnt_legalguardian";
			$sql .= ",AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody";
			$sql .=",AES_DECRYPT(cnt_sex,'" . Zend_Registry::get('salt') . "') as cnt_sex";

			//get contact persons
			$contact_master = Doctrine_Query::create()
				->select($sql)
				->from('ContactPersonMaster')
				->where("ipid=?", $ipid)
				->andWhere('isdelete = 0');
			$contact_persons = $contact_master->fetchArray();

			foreach($contact_persons as $k_contact => $v_contact)
			{
				$cust = new ContactPersonMaster();
				//ISPC-2614 Ancuta 20.07.2020 :: deactivate listner for clone
				$pc_listener = $cust->getListener()->get('IntenseConnectionListener');
				$pc_listener->setOption('disabled', true);
				// 						//--
				$cust->ipid = $target_ipid;
				$cust->cnt_first_name = Pms_CommonData::aesEncrypt($v_contact['cnt_first_name']);
				$cust->cnt_middle_name = Pms_CommonData::aesEncrypt($v_contact['cnt_middle_name']);
				$cust->cnt_last_name = Pms_CommonData::aesEncrypt($v_contact['cnt_last_name']);
				$cust->cnt_title = Pms_CommonData::aesEncrypt($v_contact['cnt_title']);
				$cust->cnt_salutation = Pms_CommonData::aesEncrypt($v_contact['cnt_salutation']);
				$cust->cnt_street1 = Pms_CommonData::aesEncrypt($v_contact['cnt_street1']);
				$cust->cnt_street2 = Pms_CommonData::aesEncrypt($v_contact['cnt_street2']);
				$cust->cnt_zip = Pms_CommonData::aesEncrypt($v_contact['cnt_zip']);
				$cust->cnt_city = Pms_CommonData::aesEncrypt($v_contact['cnt_city']);
				$cust->cnt_phone = Pms_CommonData::aesEncrypt($v_contact['cnt_phone']);
				$cust->cnt_mobile = Pms_CommonData::aesEncrypt($v_contact['cnt_mobile']);
				$cust->cnt_email = Pms_CommonData::aesEncrypt($v_contact['cnt_email']);
				$cust->cnt_birthd = $v_contact['cnt_birthd'];
				$cust->cnt_sex = Pms_CommonData::aesEncrypt($v_contact['cnt_sex']);
				$cust->cnt_denomination_id = $v_contact['cnt_denomination_id'];
				$cust->cnt_familydegree_id = isset($clone_value['FamilyDegree'][$v_contact['cnt_familydegree_id']]) ? $clone_value['FamilyDegree'][$v_contact['cnt_familydegree_id']] : $v_contact['cnt_familydegree_id'];//ISPC-2614
				$cust->cnt_custody = $v_contact['cnt_custody'];
				$cust->cnt_nation = Pms_CommonData::aesEncrypt($v_contact['cnt_nation']);
				$cust->cnt_custody = Pms_CommonData::aesEncrypt($v_contact['cnt_custody']);
				$cust->cnt_hatversorgungsvollmacht = $v_contact['cnt_hatversorgungsvollmacht'];
				$cust->cnt_legalguardian = $v_contact['cnt_legalguardian'];
				$cust->notify_funeral = $v_contact['notify_funeral'];
				$cust->quality_control = $v_contact['quality_control'];
				$cust->cnt_comment = Pms_CommonData::aesEncrypt($v_contact['cnt_comment']);
				$cust->save();
				//ISPC-2614 Ancuta 20.07.2020 :: deactivate listner for clone
				$pc_listener->setOption('disabled', false);
				// 	
				$return[] = $cust->id;
			}

			if($return)
			{
				return $return;
			}
			else
			{
				return false;
			}
		}

		public function getContactPersonsByIpids($ipids_array = false, $group_by = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipids_array != false)
			{
				$i = 1;
				foreach($ipids_array as $ipid)
				{
					if($i != count($ipids_array))
					{
						$end = ",";
					}
					else
					{
						$end = "";
					}

					$ipids_str .= '"' . $ipid . '"' . $end;
					$i++;
				}

				$ipids_sql = " ipid IN (" . $ipids_str . ")";
			}

			$contacts = Doctrine_Query::create()
				->select("*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name
					,AES_DECRYPT(cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name
					,AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name
					,AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title
					,AES_DECRYPT(cnt_salutation,'" . Zend_Registry::get('salt') . "') as cnt_salutation
					,AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1
					,AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2
					,AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip
					,AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city
					,AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone
					,AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile
					,AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email	
					,AES_DECRYPT(cnt_hatversorgungsvollmacht,'" . Zend_Registry::get('salt') . "') as cnt_hatversorgungsvollmacht
					,AES_DECRYPT(cnt_legalguardian,'" . Zend_Registry::get('salt') . "') as cnt_legalguardian
					,AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment
					,AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation
					,AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody
					,AES_DECRYPT(cnt_sex,'" . Zend_Registry::get('salt') . "') as cnt_sex")
				->from('ContactPersonMaster')
				->where("cnt_first_name != '' or cnt_last_name != ''")
				->andWhere($ipids_sql)
				->andWhere('isdelete = 0')
				->orderBy('id ASC');
			if($group_by != false)
			{
				$contacts->groupBy($group_by);
			}

			$contacts_array = $contacts->fetchArray();

			return $contacts_array;
		}

		public function get_funeral_contact_persons($ipids, $hide_deleted = true)
		{
			$hidemagic = Zend_Registry::get('hidemagic');

			$sql = "*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name";
			$sql .=",AES_DECRYPT(cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone";
			$sql .= ",AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody";

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('ContactPersonMaster')
				->whereIn("ipid", $ipids)
				->andWhere('notify_funeral = 1');
			if($hide_deleted)
			{
				$drop->andWhere('isdelete = 0');
			}
			$drop->orderby('create_date ASC');
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function get_contact_persons_by_ipids($ipids_array = false, $group_by = false, $hide_deleted = true)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipids_array != false)
			{
				$i = 1;
				foreach($ipids_array as $ipid)
				{
					$ipids[] = $ipid;
				}
			}
			else
			{
				$ipids[] = "99999999";
			}

			$contacts = Doctrine_Query::create()
				->select("*
				,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name
				,AES_DECRYPT(cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name
				,AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name
				,AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title
				,AES_DECRYPT(cnt_salutation,'" . Zend_Registry::get('salt') . "') as cnt_salutation
				,AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1
				,AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2
				,AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip
				,AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city
				,AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone
				,AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile
				,AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email		
				,AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment
				,AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation
				,AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody
				,AES_DECRYPT(cnt_sex,'" . Zend_Registry::get('salt') . "') as cnt_sex");
			$contacts->from('ContactPersonMaster');
			$contacts->where("cnt_first_name != '' or cnt_last_name != ''");

			if($ipids_array != false)
			{
				$contacts->andWhereIn("ipid", $ipids);
			}

			if($hide_deleted)
			{
				$contacts->andWhere('isdelete = 0');
			}

			$contacts->orderBy('create_date ASC');

			if($group_by != false)
			{
				$contacts->groupBy($group_by);
			}

			$contacts_array = $contacts->fetchArray();

			foreach($contacts_array as $kc => $vc)
			{
				$patients_contacts[$vc['ipid']][] = $vc;
			}

			if($patients_contacts)
			{
				return $patients_contacts;
			}
			else
			{
				return false;
			}
		}
		
		public function patient_check_cnt($ipid,$retrieved_data = false)
		{
		    if(!empty($retrieved_data))
		    {

		        if(is_array($retrieved_data) && count($retrieved_data) == "1")
		        {
    		        foreach($retrieved_data as $k=>$cnt){
    		            $cnt_details['first_name'] = Pms_CommonData::aesDecrypt($cnt['cnt_first_name']);
    		            $cnt_details['last_name'] = Pms_CommonData::aesDecrypt($cnt['cnt_first_name']);
    		        }
		        }
		        
		        $drop = Doctrine_Query::create()
		        ->select("*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name
					,AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name")
		        					->from('ContactPersonMaster')
		        					->where('AES_DECRYPT(cnt_last_name,"' . Zend_Registry::get('salt') . '")   LIKE "' . addslashes(Pms_CommonData::aesEncrypt($cnt_details['LAST_name'])) . '"  and 	AES_DECRYPT(cnt_last_name,"' . Zend_Registry::get('salt') . '")   LIKE "' . addslashes(Pms_CommonData::aesEncrypt($cnt_details['LAST_name'])) . '"  ')
		        					->andWhere("ipid=?", $ipid)
		        					->andWhere('isdelete = 0')
		                            ->limit(1);
		        $dropexec = $drop->execute();
		        $droparray = $dropexec->toArray();
		        
		        if($droparray){
		            $update_id = $droparray[0]['id'];
		        }
		    }
		    
		    if($update_id)
		    {
		        return $update_id;
		    } 
		    else
		    {
		        return false;
		    }
		}
	
	
		//8888%d 
		public static function getAllPatientContact($ipids = array())
		{
			$result = array();
			
			if (empty($ipids) || ! is_array($ipids)){
				return $result;
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
		
			$sql = "*,AES_DECRYPT(cnt_first_name,'" . Zend_Registry::get('salt') . "') as cnt_first_name";
			$sql .=",AES_DECRYPT(	cnt_middle_name,'" . Zend_Registry::get('salt') . "') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name,'" . Zend_Registry::get('salt') . "') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title,'" . Zend_Registry::get('salt') . "') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1,'" . Zend_Registry::get('salt') . "') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2,'" . Zend_Registry::get('salt') . "') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip,'" . Zend_Registry::get('salt') . "') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city,'" . Zend_Registry::get('salt') . "') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone,'" . Zend_Registry::get('salt') . "') as cnt_phone";
			$sql .=",AES_DECRYPT(cnt_email,'" . Zend_Registry::get('salt') . "') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_mobile,'" . Zend_Registry::get('salt') . "') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_comment,'" . Zend_Registry::get('salt') . "') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation,'" . Zend_Registry::get('salt') . "') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody,'" . Zend_Registry::get('salt') . "') as cnt_custody";
		

		
			$drop = Doctrine_Query::create()
			->select($sql)
			->from('ContactPersonMaster')
			->whereIn("ipid", $ipids);
			
			
			$drop->orderby('create_date ASC');
			$droparray = $drop->fetchArray();
			
			foreach ($droparray as $row) {
				$result[ $row['ipid'] ] [] = $row;
			}
		
			return $result;
		}
		
		/**
		 * 
		 * Aug 9, 2017 @claudiu 
		 * 
		 * @param array(string) $ipids
		 * @return Ambigous <multitype:, Doctrine_Collection>
		 */
		public function get_QualityControlByIpids( $ipids = array()) 
		{
			if( empty($ipids) || ! is_array($ipids)) {
				return;
			}
			
			$salt =  Zend_Registry::get('salt');
			
			$sql = "*,AES_DECRYPT(cnt_first_name, '{$salt}') as cnt_first_name";
			$sql .=",AES_DECRYPT(cnt_middle_name, '{$salt}') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name, '{$salt}') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title, '{$salt}') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1, '{$salt}') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2, '{$salt}') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip, '{$salt}') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city, '{$salt}') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone, '{$salt}') as cnt_phone";
			$sql .=",AES_DECRYPT(cnt_email, '{$salt}') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_mobile, '{$salt}') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_comment, '{$salt}') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation, '{$salt}') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody, '{$salt}') as cnt_custody";
			
			$q =  $this->getTable()->createQuery()
			->select($sql)
			->whereIn("ipid" , $ipids)
			->andWhere('isdelete = 0')
			->andWhere('quality_control = 1')
			->fetchArray();

			return $q ;				
		}
		
		/**
		 * 
		 * Aug 21, 2017 @claudiu 
		 * 
		 * @param unknown $ids
		 * @return void|Ambigous <multitype:, Doctrine_Collection>
		 */
		public function getById( $ids = array())
		{
			if ( empty($ids)) {
				return;
			}
			
			if ( ! is_array($ids)) {
				$ids =  array($ids);
			}
			
				
			$salt =  Zend_Registry::get('salt');
				
			$sql = "*,AES_DECRYPT(cnt_first_name, '{$salt}') as cnt_first_name";
			$sql .=",AES_DECRYPT(cnt_middle_name, '{$salt}') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name, '{$salt}') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title, '{$salt}') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1, '{$salt}') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2, '{$salt}') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip, '{$salt}') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city, '{$salt}') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone, '{$salt}') as cnt_phone";
			$sql .=",AES_DECRYPT(cnt_email, '{$salt}') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_mobile, '{$salt}') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_comment, '{$salt}') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation, '{$salt}') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody, '{$salt}') as cnt_custody";
				
			$q =  $this->getTable()->createQuery()
			->select($sql)
			->whereIn("id" , $ids)
			->andWhere('isdelete = 0')
			->fetchArray();
		
			return $q ;
		}
		
		
		public static function beautifyName( &$usrarray )
		{
			//mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
			if ( empty($usrarray) || ! is_array($usrarray)) {
				return;
			}
			foreach ( $usrarray as &$k )
			{
				if ( ! is_array($k) || isset($k['nice_name'])) {
					continue; // varaible allready exists, use another name for the variable
				}
		
				$k ['nice_name']  = trim($k['cnt_title']) != "" ? trim($k['cnt_title']) . " " : "";
				$k ['nice_name']  .= trim($k['cnt_last_name']);
				$k ['nice_name']  .= trim($k['cnt_first_name']) != "" ? (", " . trim($k['cnt_first_name'])) : "";
		
			}
		}

	
		/**
		 * @deprecated use parent::findOrCreateOneBy
		 */
// 		public function findOrCreateOneBy($fieldName, $value, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
// 		{
// 		    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
		
// 		        if ($fieldName != $this->getTable()->getIdentifier()) {
// 		            $entity = $this->getTable()->create(array( $fieldName => $value));
// 		        } else {
// 		            $entity = $this->getTable()->create();
// 		        }
// 		    }
		
//             $this->_encryptData($data);
		
// 		    $entity->fromArray($data); //update
		
// 		    $entity->save(); //at least one field must be dirty in order to persist
		
// 		    return $entity;
// 		}
		
		/**
		 * @deprecated use parent::findOrCreateOneByIpidAndId
		 */
// 		public function findOrCreateOneByIpidAndId($ipid = '', $id = 0, array $data = array())
// 		{
// 		    if ( empty($id) || ! $entity = $this->getTable()->findOneByIpidAndId($ipid, $id)) {
		
// 		        $entity = $this->getTable()->create(array('ipid' => $ipid));
// 		        unset($data[$this->getTable()->getIdentifier()]);
// 		    }
		
// 		    $this->_encryptData($data);
		    
// 		    $entity->fromArray($data); //update
		
// 		    $entity->save(); //at least one field must be dirty in order to persist
		
// 		    return $entity;
// 		}
		
		/**
		 * @deprecated use parent::_encryptData
		 */
// 		private function _encryptData(&$data)
// 		{
// 		    if (empty($data) || ! is_array($data)) {
// 		        return;
// 		    }
// 		    $data_encrypted = Pms_CommonData::aesEncryptMultiple($data);
// 		    foreach($data_encrypted as $column=>$val) {
// 		        if (in_array($column, $this->_encypted_columns)) {
// 		            $data[$column] = $val;
// 		        }
// 		    }
// 		}
// 		
	}

?>