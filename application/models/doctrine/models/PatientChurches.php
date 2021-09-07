<?php
Doctrine_Manager::getInstance()->bindComponent('PatientChurches', 'SYSDAT');

class PatientChurches extends BasePatientChurches {
	
	public function getPatientChurch($ipid, $chsid = false)
	{
		$sql = "*, id as chs_id";
		$sql .=",name as chs_name";
		$sql .=",church_comment as chs_com";
		$sql .=",phone as chs_phone";
		$sql .=",phone_cell as chs_phone_cell";		
		$sql .=",email as chs_email";
	
		if($chsid)
		{
			
			$q = "PatientChurches.chid = " . $chsid . " and";
		}
		else
		{
			$q = "";
		}
	
		$drop = Doctrine_Query::create()
		->select($sql)
		->from('Churches')
		->leftJoin('PatientChurches')
		->where("" . $q . " PatientChurches.chid = Churches.id and PatientChurches.ipid='" . $ipid . "' and PatientChurches.isdelete = 0 ");
		//$dropexec = $drop->execute();
		$droparray = $drop->fetchArray();
	
		return $droparray;
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
	        if (isset($k['Churches'])){
	            $k ['nice_name'] = trim($k['Churches']['contact_lastname']);
	            $k ['nice_name'] .= trim($k['Churches']['contact_firstname']) != "" ? (", " . trim($k['Churches']['contact_firstname'])) : "";
	        } else {
	            $k ['nice_name'] = trim($k['contact_lastname']);
	            $k ['nice_name'] .= trim($k['contact_firstname']) != "" ? (", " . trim($k['contact_firstname'])) : "";
	        }
	    }
	}
	
	/**
	 * ISPC-2614 Ancuta 19.07.2020
	 * @param unknown $ipid
	 * @param unknown $target_ipid
	 * @param unknown $target_client
	 * @return unknown
	 */
	public function clone_records($ipid, $target_ipid, $target_client)
	{
	    //get patient churces
	    $patient_data = $this->getPatientChurch($ipid);
	    if($patient_data)
	    {
	        foreach($patient_data as $k => $values)
	        {
	            $master_obj = new Churches();
	            $master_data = $master_obj->clone_record($values['id'], $target_client);
	            
	            if($master_data)
	            {
	                $insert2pateint = new PatientChurches();
	                //ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
	                $pc_listener = $insert2pateint->getListener()->get('IntenseConnectionListener');
	                $pc_listener->setOption('disabled', true);
	                //--
	                $insert2pateint->ipid = $target_ipid;
	                $insert2pateint->chid = $master_data;
	                $insert2pateint->church_comment = $values['church_comment'];
	                $insert2pateint->save();
	                //ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
	                $pc_listener->setOption('disabled', false);
	                //--
	            }
	        }
	        
	        return $insert2pateint->id;
	    }
	}
	
}
?>