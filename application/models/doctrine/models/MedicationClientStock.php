<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationClientStock', 'SYSDAT');

	class MedicationClientStock extends BaseMedicationClientStock {

		public function getAllMedicationClientStock($clientid)
		{
			$medic = Doctrine_Query::create()
				->select('*, SUM(amount) as total, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientStock')
				->Where('clientid =' . $clientid . '')
				->andWhere('isdelete = "0"')
				->groupBy('medicationid');
			$medicsStock = $medic->execute();

			if($medicsStock)
			{
				$medicarr = $medicsStock->toArray();
//				print_r($medicarr); exit;
				return $medicarr;
			}
		}

		public function getVerlaufMedicationClientStock($clientid)
		{
			$medic = Doctrine_Query::create()
				->select('*, IF(`done_date` != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientStock')
				->Where('clientid =' . $clientid . ' ')
				->andWhere('isdelete = "0"')
				->orderBy('create_date ASC');

			$medicsch = $medic->execute();
			if($medicsch)
			{
				$medicarr = $medicsch->toArray();
				return $medicarr;
			}
		}

		public function getVerlaufMedicationsClientStock($clientid, $medisidsStr,$method_ids = false)
		{
			$medic = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientStock')
				->Where('clientid =' . $clientid . ' ')
				->andWhere('medicationid IN (' . $medisidsStr . ')')
				->andWhere('isdelete = "0"');
				if($method_ids){
    				$medic->andWhereIn('methodid',$method_ids);
			     }
				$medic->orderBy('create_date ASC');
			$medicsch = $medic->execute();

			if($medicsch)
			{
				$medicarr = $medicsch->toArray();
				return $medicarr;
			}
		}
		
		public function getVerlaufMedicationsClientStock_original($clientid, $medisidsStr,$method_ids = false)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('MedicationClientStock')
				->Where('clientid =' . $clientid . ' ')
				->andWhere('medicationid IN (' . $medisidsStr . ')')
				->andWhere('isdelete = "0"');
				if($method_ids){
    				$medic->andWhereIn('methodid',$method_ids);
			     }
				$medic->orderBy('create_date ASC');
			$medicsch = $medic->execute();

			if($medicsch)
			{
				$medicarr = $medicsch->toArray();
				return $medicarr;
			}
		}

		public function updateNewMedicationId($old_mid, $new_mid, $clientid)
		{
			$upd = Doctrine_Query::create()
				->update('MedicationClientStock')
				->set('medicationid', $new_mid)
				->where('medicationid = "' . $old_mid . '"')
				->andWhere('clientid = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$rows = $upd->execute();
		}

		public function getMedicationClientdetails($clientid, $medid)
		{
			$medic = Doctrine_Query::create()
				->select('*, SUM(amount) as total, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientStock')
				->Where('clientid =' . $clientid . '')
				->andWhere('medicationid =' . $medid . '')
				->andWhere('isdelete = "0"')
				->groupBy('medicationid');
			$medicarr = $medic->fetchArray();
			if($medicarr)
			{
				return $medicarr;
			}
		}

		public function getClientTotalbydate_160426($clientid, $medid, $date)
		{

			$medictot = Doctrine_Query::create()
				->select('*, SUM(amount) as total, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientStock')
				->Where('clientid =' . $clientid . '')
				->andWhere('medicationid=' . $medid . '')
				->andWhere('create_date <="' . $date . '"')
				->andWhere('isdelete = "0"')
				->groupBy('medicationid');
			$totalcleintarr = $medictot->fetchArray();


			if($totalcleintarr)
			{
				$totalcleint = $totalcleintarr[0]['total']; // total client
				return $totalcleint;
			}
		}
		public function getClientTotalbydate($clientid, $medid, $date,$dbg=false)
		{

			$medictot = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as sec_create_date')
				->from('MedicationClientStock')
				->Where('clientid = ?', $clientid )
				->andWhere('medicationid = ?', $medid)
				->andWhere('isdelete = ?',0)
			     ->orderBy('create_date ASC');
			$totalcleintarr = $medictot->fetchArray();
		
			
			if($totalcleintarr)
			{
				$total="0";
				foreach($totalcleintarr as $k=>$hv){
			    	if(strtotime($hv['sec_create_date']) <= strtotime($date)){
			        	$total += $hv['amount'];
			        	$full_array[] =  $hv;
				    }
				}
				
				return $total;
			}
		}

		public function toggle_stock_status($stock_id, $clientid, $status = "1")
		{
			$upd = Doctrine_Query::create()
				->update('MedicationClientStock')
				->set('isdelete', $status)
				->where('id="' . $stock_id . '"')
				->andWhere('clientid = "' . $clientid . '"');
			$rows = $upd->execute();
		}

		public function client_stock_notification($clientid, $post_data, $new_medication = false)
		{
			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$userinfo = Pms_CommonData::getUserData($logininfo->userid);
			$user_full_name = $userinfo[0]['last_name'] . ' ' . $userinfo[0]['first_name'];

			$btm_notification_users = BtmNotifications::get_btm_notification_users($clientid, 'tresor');

			//switch mesage based on (increase/decrease) method
			if($post_data['operation'] == '2')
			{
				//operation 2 = decrease
				$message_template = $Tr->translate('btm_tresor_notification_reduction_msg');
			}
			else
			{
				//operation 1 = increase
				$message_template = $Tr->translate('btm_tresor_notification_increase_msg');
			}

			$message_template = str_replace("%medication_name", $post_data['medication']['name'], $message_template);
			$message_template = str_replace("%ammount", $post_data['amount'], $message_template);
			$message_template = str_replace("%user_fullname", $user_full_name, $message_template);

			foreach($btm_notification_users as $kusr => $vusr)
			{
				$users_ids[] = $vusr['user'];
			}

			foreach($btm_notification_users as $k_usr => $v_usr)
			{
				$mail = new Messages();
				$mail->sender = '0';
				$mail->clientid = $logininfo->clientid;
				$mail->recipient = $v_usr['user'];
				$mail->msg_date = date("Y-m-d H:i:s", time());
				$mail->title = Pms_CommonData::aesEncrypt($Tr->translate('btm_tresor_notification_title'));
				$mail->content = Pms_CommonData::aesEncrypt($message_template);
				$mail->create_date = date("Y-m-d", time());
				$mail->create_user = $logininfo->userid;
				$mail->recipients = implode(',', $users_ids);
				$mail->read_msg = '0';
				$mail->source = 'btm_tresor_system_message';
				$mail->save();
			}
		}

		public function user_ammount_changed_notification($clientid, $post_data)
		{
			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$userinfo = Pms_CommonData::getUserData($userid);
			$user_full_name = $userinfo[0]['last_name'] . ' ' . $userinfo[0]['first_name'];

			if($post_data['fromuserid'] != $userid)
			{
				//switch mesage based on (increase/decrease) method
				if($post_data['operation'] == '2')
				{
					//operation 2 = decrease
					$message_template = $Tr->translate('btm_user_ammount_reduction_msg');
				}
				else
				{
					//operation 1 = increase
					$message_template = $Tr->translate('btm_user_ammount_increase_msg');
				}

				$message_template = str_replace("%medication_name", $post_data['medication']['name'], $message_template);
				$message_template = str_replace("%ammount", $post_data['amount'], $message_template);
				$message_template = str_replace("%user_fullname", $user_full_name, $message_template);

				if($post_data['fromuserid'] > '0')
				{
					$recipient = $post_data['fromuserid'];
				}

				$mail = new Messages();
				$mail->sender = '0';
				$mail->clientid = $clientid;
				$mail->recipient = $recipient; //affected user ammount
				$mail->msg_date = date("Y-m-d H:i:s", time());
				$mail->title = Pms_CommonData::aesEncrypt($Tr->translate('btm_user_ammount_notification_title'));
				$mail->content = Pms_CommonData::aesEncrypt($message_template);
				$mail->create_date = date("Y-m-d", time());
				$mail->create_user = $userid;
				$mail->read_msg = '0';
				$mail->source = 'btm_user_amount_system_message';
				$mail->save();
			}
			
			
			//transfer method, second message (one user gets the medis the other gives the medis)
			if($post_data['userselect'] > '0' && $post_data['userselect'] != $userid)
			{
				$second_recipient = $post_data['userselect'];

				//message is reverted and is only used in transfer method from user(gets the medi) to user(gives the medi)
				if($post_data['operation'] == '2')
				{
					$second_message_template = $Tr->translate('btm_user_ammount_increase_msg');
				}
				else
				{
					$second_message_template = $Tr->translate('btm_user_ammount_reduction_msg');
				}

				$second_message_template = str_replace("%medication_name", $post_data['medication']['name'], $second_message_template);
				$second_message_template = str_replace("%ammount", $post_data['amount'], $second_message_template);
				$second_message_template = str_replace("%user_fullname", $user_full_name, $second_message_template);

				$mail = new Messages();
				$mail->sender = '0';
				$mail->clientid = $clientid;
				$mail->recipient = $second_recipient; //affected user ammount
				$mail->msg_date = date("Y-m-d H:i:s", time());
				$mail->title = Pms_CommonData::aesEncrypt($Tr->translate('btm_user_ammount_notification_title'));
				$mail->content = Pms_CommonData::aesEncrypt($second_message_template);
				$mail->create_date = date("Y-m-d", time());
				$mail->create_user = $userid;
				$mail->read_msg = '0';
				$mail->source = 'btm_user_amount_system_message';
				$mail->save();
			}
		}

		//used in btm history
		public function get_all_medicationids($clientid)
		{
			$medic = Doctrine_Query::create()
			->select('id, medicationid, isdelete')
			->from('MedicationClientStock')
			->Where('clientid = ? ' , $clientid )
			//->andWhere('isdelete = "0"')
			->groupBy('medicationid')
			->fetchArray();
		
			return $medic;
			
			
		}
	
		
		
		
		
		
		
		
		//used in btm history CORRECTIOn
		//ispc-1864 p.11
		public static function validate_positive_stock_after_correction_date (array $post)
		{

			
// 			die(print_r($post));
			if ( empty($post)
					|| empty($post['clientid'])
					|| empty($post['medicationid'])
					|| ! isset ($post['amount'])
					|| ! isset ($post['old_amount'])
					|| empty($post['date_time'])
			) {
				return false;
			}

		
			$clientid = $post['clientid'];
			$medicationid = $post['medicationid'];
			$amount = $post['amount']; 
			
			$date_time = date("Y-m-d H:i:s" , strtotime($post['date_time']));
		
				
			//get the stock at t0 < date
			$query_in_the_past = Doctrine_Query::create()
			->select('id, SUM(amount) as total_amount ')
			->from('MedicationClientStock')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('isdelete = "0"')
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) < STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time );
		
			$stock_before_t0 = $query_in_the_past->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
			
			//get the stock at t0 = date
			$query_in_the_past = Doctrine_Query::create()
			->select('id, SUM(amount) as total_amount ')
			->from('MedicationClientStock')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('isdelete = "0"')
			->andWhere('methodid != 13 ' )
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) = STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time );
			$stock_at_t0 = $query_in_the_past->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
			
			$t0_amount_corrected = (int)$stock_before_t0['total_amount']  + (int)$stock_at_t0['total_amount'] + ( abs($post['amount']) - abs($post['old_amount']) )  * (int)self::sign($post['old_amount']) ;
					
			$break_stock_row = array();
			if ( $t0_amount_corrected  < 0  
			) {
				// this is a first hint that stock cannot be documented at that date in time ...
				// discover at what date we can document this action, so we can inform/help the benutzer
				$next_available_date = false;
				$break_stock_row = array("break_date" => date("d.m.Y H:i" , strtotime($date_time)) );
				
			} else {
				$next_available_date = true; // the date user has chosen is ok at first glance
			}

			
			//exclude $correction_new_id performed after this date
			$correction_new_id = array();
			$mcbtmc = new MedicationClientBTMCorrection();
			$mcbtmc_r = $mcbtmc->get_by_correction_table( $post['table'] , $clientid);
// 			if ( ! empty($mcbtmc_r)) {
// 				$correction_new_id = array_column($mcbtmc_r, 'correction_new_id');
				
// 			}
			
			//ELSE :
			//get the stock after this date to validate our assumptions that we can deduct this xAmount, or to find the next possible date
			$query_in_the_present = Doctrine_Query::create()
			->select('id, amount, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as done_create_date')
			->from('MedicationClientStock')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('isdelete = "0"')
			->andWhere('methodid != 13 ' )
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time )
			->orderBy('done_create_date ASC');
			
			//exclude $correction_new_id performed after this date
// 			if ( ! empty($correction_new_id)) {
// 				$query_in_the_present->andWhereIn('id' ,  $correction_new_id, true);
// 			}
// 			Pms_DoctrineUtil::get_raw_sql($query_in_the_present);
								
			$stock_after_t0 = $query_in_the_present->fetchArray();
				
			$stock_after_this_row = $t0_amount_corrected;
			
				
			foreach( $stock_after_t0 as $row ) {
		
				//modify amount with the corrected
				if ( ! empty($mcbtmc_r[$row['id']])) {
				
					$row['amount'] += $mcbtmc_r[$row['id']] ['amount_corrected'];
				}
				
				$stock_after_this_row += $row['amount'];
		
				if ($stock_after_this_row < 0) {
					//validation failed
					$next_available_date = false;
					$row['break_date'] =  date("d.m.Y H:i" , strtotime($row['done_create_date']));
					
					$break_stock_row = $row;
					break;
				} 

			}
				
			$result =  array(
					"result" => ($next_available_date === true) ? true : false,
					"t0" => date("d.m.Y H:i" , strtotime($date_time)),
					"t0_amount_corrected" => $t0_amount_corrected,
					"break_stock_row" => $break_stock_row,
					"table_name" => 'MedicationClientStock'
						
			);
			
			
			if ( $result['result'] && $post['validate_step'] == 1 ) {
				
				//validate2 because correction will be performed on connected also
				//validate connected user (if any), tresor should be only connected with a Benutzer, and NOT direclty to patient
				
				
				$post['validate_step'] = 2;
				
				$connected_stid = MedicationClientHistory::get_connected_from_stid($post['id']);
				
				if ( ! empty($connected_stid) ) {
					
					$post['old_amount'] = (-1) * $post['old_amount'] ;
					$post['table'] = 'MedicationClientHistory' ;
					$post['id'] = $connected_stid['id'];
					
					$result = MedicationClientHistory :: validate_positive_stock_after_correction_date($post);
				}
				
				
			}
			
			return $result;
		}
	
		
		private function sign($n) {
			return ($n > 0) - ($n < 0);
		}
		
	}

?>