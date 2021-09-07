<?php

require_once("Pms/Form.php");

class Application_Form_EpidIpidMapping extends Pms_Form
{

	private $try = 0;
	private $max_try = 5;
	
	
	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid =$logininfo->clientid;

        //IM-117 START
		//Maria:: Migration CISPC to ISPC 22.07.2020
		if($post['visible_since']===null){
            $post['visible_since']="0000-00-00";
		}
        if($post['discharge_since']===null){
            $post['discharge_since']="0000-00-00";
		}
        //IM-117 END
		$visible_since = split(".",$post['visible_since']);
		$discharge_since = split(".",$post['discharge_since']);

		
		$sortepid = Pms_Uuid::GenerateSortEpid($clientid);
			
		// check if epid exists
		$check_sorted_epid = EpidIpidMapping::check_epid($post['clientid'],$sortepid['epid_num']);
		
		$r = 1;
		while ($check_sorted_epid === false){
		    $r++;
		    $sortepid = Pms_Uuid::GenerateSortEpid($clientid);
		    
		    if ($r > 10)
		    {
		        return false;
		        break;
		    }
		}
		
		
		
		
		$x=true;
		$res = new EpidIpidMapping();
		$res->clientid = $clientid;
		$res->ipid = $post['ipid'];
		$res->epid = $post['epid'];
		$res->epid_chars = $sortepid['epid_chars'];
		$res->epid_num = $sortepid['epid_num'];
		// Maria:: Migration ISPC to CISPC 08.08.2020	
		if(!empty($visible_since)){
    		$res->visible_since =  $visible_since[2]."-".$visible_since[1]."-".$visible_since[0];
		}
		if(!empty($discharge_since)){
    		$res->discharge_since =  $discharge_since[2]."-".$discharge_since[1]."-".$discharge_since[0];
		}
		try {
			// do some operations here
			$res->save();
			
		} catch(Exception $e) {
			$this->try = $this->try + 1;
			
			if($this->try >= $this->max_try){
				
				$writer = new Zend_Log_Writer_Stream(PUBLIC_PATH . '/log/add_patient.log');
				$log    = new Zend_Log($writer);
				if ($log) {
					$log->crit("Duplicate on epid and clientid, ".__CLASS__." ".__FUNCTION__." ".__LINE__);
				}
				$x=false;
				return null; 
			} else {
				// update patient case, 
				$patient_caseform = new Application_Form_PatientCase();
				$new_case_data = $patient_caseform->regenerate($post);
				$post['epid'] = $new_case_data->epid; 
				$this->InsertData($post);
			}
		}
		if(!$x)
			return false;
// 		$res->save();
		

		
		$userdata = Pms_CommonData::getUserData($logininfo->userid);
		$groupid = $userdata[0]['groupid'];
		$grp = new Usergroup();
		$groupdata = $grp->getUserGroupData($groupid);

		
		$client_data= Pms_CommonData::getClientData($clientid);
		$automatically_assign_users = $client_data['0']['automatically_assign_users'];// ISPC-871 client setting auto assign user

		//always assign the user to the patient he added
		if($post['no_assign'] != '1' && $automatically_assign_users == "1") { //needed for imports
			$assign = new PatientQpaMapping();
			$assign->epid = $post['epid'];
			$assign->userid = $userdata[0]['id'];
			$assign->clientid = $logininfo->clientid;
			$assign->save();

			//add visibility for this
			$vizibility = new PatientUsers();
			$vizibility->clientid = $logininfo->clientid;
			$vizibility->ipid = $post['ipid'];
			$vizibility->userid = $userdata[0]['id'];
			$vizibility->create_date = date("Y-m-d H:i:s",time());
			$vizibility->save();
		}
		if(  $post['nurse_practicing'] &&  $post['nurse_practicing'] != '0'  && $post['nurse_practicing'] != $userdata[0]['id']	){
				
			$nurse_id = $post['nurse_practicing'];
			$assign = new PatientQpaMapping();
			$assign->epid = $post['epid'];
			$assign->userid = $nurse_id ;
			$assign->clientid = $logininfo->clientid;
			$assign->save();
				
			//add visibility for this
			$vizibility = new PatientUsers();
			$vizibility->clientid = $logininfo->clientid;
			$vizibility->ipid = $post['ipid'];
			$vizibility->userid = $nurse_id;
			$vizibility->create_date = date("Y-m-d H:i:s",time());
			$vizibility->save();
		}



		return $res;
	}

	public function UpdateData($post)
	{
		$a_date = split("/",$post['birthd']);
		$cust = Doctrine::getTable('PatientMaster')->find($_GET['id']);
		$cust->first_name = $post['first_name'];
		$cust->middle_name = $post['middle_name'];
		$cust->last_name = $post['last_name'];
		$cust->street1 = $post['street1'];
		$cust->street2 = $post['street2'];
		$cust->zip = $post['zip'];
		$cust->city = $post['city'];
		$cust->title = $post['title'];
		$cust->phone =$post['phone'];
		$cust->mobile =$post['mobile'];
		$cust->birthd = $a_date[2]."-".$a_date[0]."-".$a_date[1];
		$cust->sex =$post['sex'];
		$cust->nation =$post['nation'];
		$cust->fdoc_caresalone =$post['fdoc_caresalone'];
		$cust->save();
	}
	 
	public function test($func){

		$func();

	}


}

?>