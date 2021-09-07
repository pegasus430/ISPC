<?php
require_once("Pms/Form.php");
//service_entry Leistungserfassung BER is this no longer used?
	class Application_Form_ContactFormServiceEntry extends Pms_Form {
		
		public function Update_old_values($ipid ,$contact_form_id )
		{
		if(!empty($contact_form_id))
			{
				$Q = Doctrine_Query::create()
					->update('ContactFormServiceEntry')
					->set('isdelete', '1')
					->where("contact_form_id='" . $contact_form_id . "'")
					->andWhere('ipid LIKE "' . $ipid . '"');
				$result = $Q->execute();

				return true;
			}
			else
			{
				return false;
			}
		
		}
		
		
		public function InsertData( $post ,$allowed_blocks)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$contact_form_id = $post['contact_form_id'];
			$old_contact_form_id = $post['old_contact_form_id'];
			
			$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
						
			// new contact form 
			if(!$post['old_contact_form_id'])
			{ 		
			 foreach($post['services']['input_value'] as $k => $val)
				{
					if ($val)
					{	
						$service_input_values[$k]= $val;
					}
				}
				
				 if(is_array($service_input_values) && sizeof($service_input_values) > 0)
				 {	
				 	//edit last values isdelete=1
				 	$Q = Doctrine_Query::create()
				 	->update('ContactFormServiceEntry')
				 	->set('isdelete', '1')
				 	->where('isdelete = 0')
				 	->andWhere('ipid LIKE "' . $ipid . '"');
				 	$result = $Q->execute();
				 	
					$comment = $post['services']['comment'];
					$curent_values = $post['services']['curent_value'];	
					//print_r($curent_values);exit;
					foreach($post['services']['input_value'] as $serv_id => $val)
					{
					 if(strlen($val) > 0)
					 {
					 					 	
					 	//insert new values
						$servvals = new ContactFormServiceEntry();
						$servvals->contact_form_id = $contact_form_id;//id contact form
						$servvals->ipid = $ipid;
						$servvals->service_entry_id = $serv_id;
						$servvals->last_value = ($curent_values[$serv_id] == '' ? NULL : $current_values[$serv_id]);
						$servvals->curent_value = ($val == '' ? NULL : $val);
						$servvals->comment = htmlspecialchars($comment[$serv_id]);
						$servvals->entry_date = date("Y-m-d H:i:s", time());//$post['date'];
						$servvals->isdelete = 0;
						$servvals->save();
												
							$tocourse['input_value'] = $val;
							$tocourse['second_value'] = htmlspecialchars($post['services']['comment'][$serv_id]);//$value['comment'];
							$tocourse['servid'] = $serv_id;
							$tocourse['iskvno'] = '0';
							$coursecomment[] = $tocourse;
						}	
					}		
				 }
				
			}
				if(!empty($coursecomment) && strlen($_REQUEST['cid']) >= 0)
				{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("PD");
				$cust->course_title = Pms_CommonData::aesEncrypt(serialize($coursecomment));
				$cust->isserialized = 1;
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
				$cust->done_id = $post['contact_form_id'];
				
				// ISPC-2071 - added tabname, this entry must be grouped/sorted
				$cust->tabname = Pms_CommonData::aesEncrypt("ContactFormServiceEntry");
				
				$cust->save();
				}
			}
			
		
	public function UpdateData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$contact_form_id = $post['contact_form_id'];
		$old_contact_form_id = $post['old_contact_form_id'];
		
		$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
		
		$contactform_services = new ContactFormServiceEntry();
		$services = $contactform_services->getLastServiceEntry($ipid);
		
		if($services)
		{
			/* if (!in_array('service_entry',$allowed_blocks))
			 {
			$records = array();
			foreach($services as $ks => $value)
			{
			$records[]= array(
					"ipid" => $ipid,
					"contact_form_id" => $contact_form_id,
					"service_entry_id" => $ks,
					"last_value" => $value[$ks]['last_value'],
					"curent_value" => $value[$ks]['curent_value'],
					"isdelete" => $value[$ks]['isdelete']
			);
			}
			}
			else
			{ */
			foreach($services as $k => $val)
			{
			$last_values[$val]['service_entry_id']['last_val'] = $val['curent_value'];
			}
		 }
			//print_r($last_values);exit;
			foreach($post['services']['input_value'] as $k => $value)
			{
				if(strlen($value) > 0)
				{
					$serv_upd = new ContactFormServiceEntry();
					$serv_upd->contact_form_id = $contact_form_id;
					$serv_upd->ipid = $ipid;
					$serv_upd->service_entry_id = $k;
					$serv_upd->last_value = $last_values[$k]['last_val'];
					$serv_upd->curent_value = ($value == '' ? NULL : $value);
					$serv_upd->entry_date = date("Y-m-d H:i:s", time());//$post['date'];
					$serv_upd->isdelete = 0;
					$serv_upd->save();
					
					
				}
			}
			$clear_form_entryes = $this->Update_old_values($ipid , $old_contact_form_id);
		
			
			$qa = Doctrine_Query::create()
			->update('PatientCourse')
			->set('done_date', "'" . $done_date . "'")
			->where('done_name = AES_ENCRYPT("contact_form", "' . Zend_Registry::get('salt') . '")')
			->andWhere('done_id = "' . $contact_form_id . '"')
			->andWhere('ipid LIKE "' . $ipid . '"');
			$qa->execute();
		}
}
