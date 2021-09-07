<?php

require_once("Pms/Form.php");

class Application_Form_PatientDischarge extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();

		if(strlen($post['discharge_date'])>0)
		{
			$bd_date = explode(".",$post['discharge_date']);
			$ddate = $bd_date[2]."-".$bd_date[1]."-".$bd_date[0]." ".$post['rec_timeh'].":".$post['rec_timem'].":00";

		}

		if(!$val->isstring($post['discharge_date'])){
			$this->error_message['discharge_date']=$Tr->translate('err_dischargedate'); $error=1;
		}

		if(strtotime($post['discharge_date']) > strtotime(date("d.m.Y",time()))){
			$this->error_message['discharge_date']=$Tr->translate('err_dischargedatefuture'); $error=5;
		}

		if(strtotime($ddate) <=  strtotime($post['last_adm_datetime'])) {
			$this->error_message['discharge_date']=$Tr->translate('err_dischargedateadmision').' ('.$post['last_adm_datetime'].')'; $error=5;
		}

		// Maria:: Migration CISPC to ISPC 22.07.2020
        if(isset($post['clinic_cases']) && $post['clinic_cases']==0){
            $this->error_message['discharge_clinic_cases']=$Tr->translate('err_dischargecliniccases'); $error=1;
        }
		// --


		if(!$val->isstring($post['discharge_method'])){
			$this->error_message['discharge_method']=$Tr->translate('err_dischargemethod'); $error=2;
		}

		if($post['discharge_method']==1)
		{
		 if(!$val->isstring($post['discharge_location'])){
		 	$this->error_message['discharge_location']=$Tr->translate('err_dischargelocation'); $error=3;
		 }
		}
		if($post['discharge_method']==2)
		{
			if(!$val->isstring($post['discharge_comment'])){
				$this->error_message['discharge_comment']=$Tr->translate('err_dischargecomment'); $error=3;
			}
		}
		if($error==0)
		{
		 return true;
		}

		return false;

	}

	public function InsertData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		
		//ISPC-2645 Carmen 24.07.2020
		$dl = new DischargeLocation();
		$discharge_locations_arr = $dl->getDischargeLocation($clientid, 1, true);
		
		$client_is_follower_dl = ConnectionMasterTable::_check_client_connection_follower('DischargeLocation',$clientid);
		if($client_is_follower_dl){
			$list_discharge_locations_arr = $dl->getDischargeLocation($clientid, 1,true,true);
		} else{
			$list_discharge_locations_arr = $discharge_locations_arr;
		}
		//--

		if(strlen($post['discharge_date'])>0)
		{
			$bd_date = explode(".",$post['discharge_date']);
			$ddate = $bd_date[2]."-".$bd_date[1]."-".$bd_date[0]." ".$post['rec_timeh'].":".$post['rec_timem'].":00";

		}

		$dism = Doctrine_Query::create()
		->select("*")
		->from('PatientDischarge')
		->where("ipid='".$ipid."' and isdelete='0'");
		$dmarr = $dism->fetchArray();


		if(count($dmarr)>0)
		{
			$dis = Doctrine::getTable('PatientDischarge')->find($dmarr[0]['id']);
			$dis->discharge_date = $ddate ;
			$dis->ipid = $ipid;
			$dis->discharge_method = $post['discharge_method'];
			$dis->discharge_location = $post['discharge_location'];
			$dis->discharge_comment = Pms_CommonData::aesEncrypt($post['discharge_comment']);
			$dis->death_wish= $post['death_wish'];
			$dis->save();

			$discharge_entry_id = $dmarr[0]['id'];
			
			
			$dismet = new DischargeMethod();
			$disemetdet = $dismet->getDischargeMethodById($post['discharge_method']);
				
				
			$messages = new Messages();
			$messages->discharge_notification($_REQUEST['id'], $ipid, $post['discharge_method'],$ddate,'edit_entry');

				
				
			if($disemetdet[0]['abbr'] == 'TOD') { //if discharged to hospiz mark as hospiz
				//send message to all assigned users from all patients
				//if discharge method is "tod" the traffic status is none
				$cust = Doctrine::getTable('PatientMaster')->find($decid);
				$cust->traffic_status = 0;
				$cust->save();

				//write verlauf of other patient
				$patients_linked = new PatientsLinked();
				$linked_patients = $patients_linked->get_related_patients($ipid);

				$linked_ipids[] = '9999999';
				if ($linked_patients)
				{
					$linked_ipids[] = $ipid;
					foreach ($linked_patients as $k_link => $v_link)
					{
						$linked_ipids[] = $v_link['target'];
						$linked_ipids[] = $v_link['source'];
					}

				}

				$dm  = new DischargeMethod();
				$dm_array = $dm->getDischargeMethodById($post['discharge_method']);

				$dis_method = $dm_array[0]['description'];
				//ISPC-2645 Carmen 24.07.2020
				$dis_location = $list_discharge_locations_arr[$post['discharge_location']];
				//--
				foreach($linked_ipids as $k_ipid => $v_ipid)
				{
					if($v_ipid != $ipid && $v_ipid != '9999999' && !empty($v_ipid))
					{
						//ISPC-2645 Carmen 24.07.2020
						$comment="Patient wurde am ".$post['discharge_date']." ".$post['rec_timeh'].":".$post['rec_timem']." entlassen \n Entlassungsart : ".$dis_method."\n Entlassungsort : ".$dis_location."\n ".$post['discharge_comment'];
						//--
						$pc = new PatientCourse();
						$pc->ipid = $v_ipid;
						$pc->course_date = date("Y-m-d H:i:s",time());
						$pc->course_type=Pms_CommonData::aesEncrypt("K");
						$pc->course_title=Pms_CommonData::aesEncrypt($comment);
						$pc->tabname=Pms_CommonData::aesEncrypt("discharge");
						$pc->source_ipid = $ipid;
						$pc->user_id = $userid;
						$pc->save();
					}
				}
			}
			else
			{
				$cust = Doctrine::getTable('PatientMaster')->find($decid);
				$cust->traffic_status = 1;
				$cust->save();
			}
			return $discharge_entry_id;
		}
		else
		{
			$cust = new PatientDischarge();
			$cust->discharge_date = $ddate ;
			$cust->ipid = $ipid;
			$cust->discharge_method = $post['discharge_method'];
			$cust->discharge_location = $post['discharge_location'];
			$cust->discharge_comment = Pms_CommonData::aesEncrypt($post['discharge_comment']);
			$cust->death_wish= $post['death_wish'];
			$cust->save();

			$discharge_entry_id =$cust->id; 
			
			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			$cust->isdischarged = 1;
			$cust->traffic_status = 1; //status 1 is default traffic status,status 0 is dead which is set below
			$cust->save();

			$loc = new PatientLocation();
			$locarr = $loc->getActiveLocationOpt($ipid);
				
			if(count($locarr)>0)
			{
				$bd_date = explode(".",$post['discharge_date']);
				$valid_till = $bd_date[2]."-".$bd_date[1]."-".$bd_date[0]." ".$post['rec_timeh'].":".$post['rec_timem'].":00";
					
				$q = Doctrine_Query::create()
				->update('PatientLocation')
				->set('valid_till','"'.$valid_till.'"')
				->where('ipid = "'.$ipid.'"')
				->andwhere("id=".$locarr[0]['id']);
					
				$q->execute();
					
					
			}
				
				
			$messages = new Messages();
			$messages->discharge_notification($_REQUEST['id'], $ipid, $post['discharge_method'],$ddate,'discharge_patient');


			$dismet = new DischargeMethod();
			$disemetdet = $dismet->getDischargeMethodById($post['discharge_method']);

			if($disemetdet[0]['abbr'] == 'HOSPIZ') { //if discharged to hospiz mark as hospiz
				$cust = Doctrine::getTable('PatientMaster')->find($decid);
				$cust->ishospiz = 1;
				$cust->save();
			}
			if($disemetdet[0]['abbr']== 'TOD') {//if discharge method is "tod" the traffic status is none
				$cust = Doctrine::getTable('PatientMaster')->find($decid);
				$cust->traffic_status = 0;
				$cust->save();


				//sent mesage to all asigned users
				$mess = new Messages();
				$mess->dead_notification($ipid);

				//write in verlauf of shared patient
				$patients_linked = new PatientsLinked();
				$linked_patients = $patients_linked->get_related_patients($ipid);

				$linked_ipids[] = '9999999';
				if ($linked_patients)
				{
					$linked_ipids[] = $ipid;
					foreach ($linked_patients as $k_link => $v_link)
					{
						$linked_ipids[] = $v_link['target'];
						$linked_ipids[] = $v_link['source'];
					}

				}

				$dm  = new DischargeMethod();
				$dm_array = $dm->getDischargeMethodById($post['discharge_method']);
				
				$dis_method = $dm_array[0]['description'];
				//ISPC-2645 Carmen 24.07.2020
				$dis_location = $list_discharge_locations_arr[$post['discharge_location']];
				//--
				foreach($linked_ipids as $k_ipid => $v_ipid)
				{
					if($v_ipid != $ipid && $v_ipid != '9999999' && !empty($v_ipid))
					{
						//ISPC-2645 Carmen 24.07.2020
						$comment="Patient wurde am ".$post['discharge_date']." ".$post['rec_timeh'].":".$post['rec_timem']." entlassen \n Entlassungsart : ".$dis_method."\n Entlassungsort : ".$dis_location."\n ".$post['discharge_comment'];
						//--
						$pc = new PatientCourse();
						$pc->ipid = $v_ipid;
						$pc->course_date = date("Y-m-d H:i:s",time());
						$pc->course_type=Pms_CommonData::aesEncrypt("K");
						$pc->course_title=Pms_CommonData::aesEncrypt($comment);
						$pc->tabname=Pms_CommonData::aesEncrypt("discharge");
						$pc->user_id = $userid;
						$pc->source_ipid = $ipid;
						$pc->save();
					}
				}

			}


// 			return $cust;
			return $discharge_entry_id;
		}

	}

	public function UpdateData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$admission_date = explode(".",$post['admission_date']);
		$admissiondt = $admission_date[2]."-".$admission_date[1]."-".$admission_date[0]." ".$post['adm_timeh'].":".$post['adm_timem'].":00";


		$cust = Doctrine::getTable('PatientCase')->find($decid);
		$cust->admission_date = $admissiondt;
		$cust->discharge_comment = Pms_CommonData::aesEncrypt($post['discharge_comment']);
		$cust->save();
	}

	public function test($func)
	{
		$func();
	}


}

?>