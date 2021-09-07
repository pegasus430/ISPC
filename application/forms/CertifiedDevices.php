<?php

	require_once("Pms/Form.php");

	class Application_Form_CertifiedDevices extends Pms_Form {

		public function insert($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if(strlen($post['device_id']) > '0' && $clientid > '0')
			{
				$c_dev = new CertifiedDevices();
				$c_dev->clientid = $clientid;
				$c_dev->deviceid = $post['device_id'];
				$c_dev->userid = $userid;
				$c_dev->create_date = date('Y-m-d H:i:s', time());
				$c_dev->isdelete = '0';
				$c_dev->save();
			}
		}

		public function delete($record)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if(strlen($record) > '0' && $record > '0' && $clientid > '0')
			{
				$kuns_up = Doctrine::getTable('CertifiedDevices')->findOneByIdAndClientidAndIsdelete($record, $clientid, "0");
				if($kuns_up)
				{
					$kuns_up->isdelete = "1";
					$kuns_up->save();
				}
			}
		}

		public function insert_sync_patients($decrypted_patient_ids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$ipids = Pms_CommonData::get_ipids($decrypted_patient_ids);


			foreach($ipids as $k_pat => $ipid)
			{
				$sync_patients_arr[] = array(
					'ipid' => $ipid,
					'client' => $clientid,
					'userid' => $userid,
				);
			}

			if(count($sync_patients_arr) > '0')
			{

			    // do not cleasr all! 
			    $this->clear_user_sync_patients();

				$collection = new Doctrine_Collection('PatientSync');
				$collection->fromArray($sync_patients_arr);
				$collection->save();
			}
		}

		public function clear_user_sync_patients()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$doc_del = Doctrine_Query::create()->delete('*')->from('PatientSync')->where('userid = "' . $logininfo->userid . '"')->andWhere('client = "'.$logininfo->clientid.'"')->execute();
		}

		
		
		
		public function edit_sync_patients($decrypted_patient_ids, $action)
		{
			//$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $this->logininfo->userid;
			$clientid = $this->logininfo->clientid;

			$synced_ipids = array();
			$unsynced_ipids = array();
			
			if($action == 'sync')
			{
				$synced_ipids = Pms_CommonData::get_ipids($decrypted_patient_ids['selected']);
				$unsynced_ipids = Pms_CommonData::get_ipids($decrypted_patient_ids['deselected']);

				/*if(empty($synced_ipids)){
				    $synced_ipids[] = "9999999999";
				}
				if(empty($unsynced_ipids)){
				    $unsynced_ipids[] = "9999999999";
				}*/
				
	            $clear_ipids = array_merge($synced_ipids,$unsynced_ipids );
			 
				foreach($synced_ipids as $k_pat => $ipid)
				{
					$sync_patients_arr[] = array(
						'ipid' => $ipid,
						'client' => $clientid,
						'userid' => $userid,
					);
				}
	
				if(count($sync_patients_arr) > '0')
				{
				    $this->clear_user_sync_patientsbyipids($clear_ipids);
	
					$collection = new Doctrine_Collection('PatientSync');
					$collection->fromArray($sync_patients_arr);
					$collection->save();
				}
			}
			elseif($action == 'unsync')
			{				
				$unsynced_ipids = Pms_CommonData::get_ipids($decrypted_patient_ids['selected']);
				//$unsynced_ipids = Pms_CommonData::get_ipids($decrypted_patient_ids['deselected']);
				
				/*if(empty($synced_ipids)){
				 $synced_ipids[] = "9999999999";
				 }
				 if(empty($unsynced_ipids)){
				 $unsynced_ipids[] = "9999999999";
				 }*/
				
				//$clear_ipids = array_merge($synced_ipids,$unsynced_ipids );
				
				/*foreach($synced_ipids as $k_pat => $ipid)
				{
					$sync_patients_arr[] = array(
							'ipid' => $ipid,
							'client' => $clientid,
							'userid' => $userid,
					);
				}*/
				
				if(count($unsynced_ipids) > '0')
				{
					$this->clear_user_sync_patientsbyipids($unsynced_ipids);
				
					/*$collection = new Doctrine_Collection('PatientSync');
					$collection->fromArray($sync_patients_arr);
					$collection->save();*/
				}
			}
		}

	    public function clear_user_sync_patientsbyipids($ipids)
        {
            if(empty($ipids)){
               // $ipids[] = "9999999999";
               return;
            }
            
            //$logininfo = new Zend_Session_Namespace('Login_Info');
            $doc_del = Doctrine_Query::create()->delete('*')
                ->from('PatientSync')
                ->where('userid = "' . $this->logininfo->userid . '"')
                ->andWhere('client = "' . $this->logininfo->clientid . '"')
                ->andWhereIn('ipid',$ipids)
                ->execute();
        }

        
    public function save_certified_device($post)
    {
        if (empty($post['userid']) || !isset($post['clientid']) || empty($post['deviceid'])) {
            return; //fail-safe
        }
        
        $obj = new CertifiedDevices();
    
        return $obj->findOrCreateOneByClientidAndUseridAndDeviceid($post['clientid'], $post['userid'], $post['deviceid'], $post);
    }
    
    public function delete_certified_device($post)
    {
        if (empty($post['userid']) || empty($post['remove'])) {
            return; //fail-safe
        }
        
        return Doctrine_Query::create()->delete('CertifiedDevices')
        ->whereIn('id', $post['remove'])
        ->andWhere('userid = ?', $post['userid'])
        ->execute();
        
    }
    
    
    
}

?>