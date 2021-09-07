<?php

	require_once("Pms/Form.php");

	class Application_Form_DailyPlanningVisits extends Pms_Form {

		public function save_visit($post)
		{
			$dpv = new DailyPlanningVisits();
			$dpv->date = $post['date'];
			$dpv->userid = $post['userid'];
			$dpv->clientid = $post['clientid'];
			$dpv->ipid = $post['ipid'];
			$dpv->start_date = $post['start_date'];
			$dpv->end_date = $post['end_date'];
			$dpv->save();
		}

		public function save_multiple_visits($post)
		{
			//get patients ipids 
			//only for new visits
			// Maria:: Migration ISPC to CISPC 08.08.2020
			/*
			 * default_autoasign_hour save new settings
			 * firt step fetch ipids
			*/
			$default_autoasign_hour_id = array();
			if( count($post['default_autoasign_hour']) > 0){
				$default_autoasign_hour_id = array_keys($post['default_autoasign_hour']);
			}
			
			$new_pat_id = array();
			$new_pat_epids = array();
			foreach($post['visit_patient'] as $k=>$v){
				if(strpos($k, "visit_") !== false){
					if (substr($v, 0, 7) === "isepid_"){
						$new_pat_epids[] = substr($v, 7, (strlen($v)-1)) ;
					}
					else{
						//echo $v."\n";
						$new_pat_id[] = $v;
					}
				}
			}	
		
			
			$new_pat_id = (array)$new_pat_id + (array)$default_autoasign_hour_id;
			
			//decrypt the id so we now have numeric_id for the default_autoasign_hour
			array_walk($new_pat_id, function(&$a){
				$a = Pms_Uuid::decrypt($a);	
			});
			
				
			$patients_ipids = array();
			if (!empty($new_pat_id)){
				$patients_ipids = PatientMaster::get_patients_ipids( array_unique( $new_pat_id ) );
			}
			/*
			 * default_autoasign_hour save new settings
			 * step2
			 */
			$visit = array();
			$day =  date("N", strtotime($post['current_date']) );
			foreach($post['default_autoasign_hour']  as $patient_id => $doc_hour) {
				
				$patient_id = Pms_Uuid::decrypt($patient_id);
				
				foreach($doc_hour as $user_id=>$hour){	
					if (!empty($patients_ipids[$patient_id])){
						$patient = Doctrine::getTable('PatientVisitsSettings')->findByIpidAndVisit_dayAndVisitor_typeAndVisitor_id( $patients_ipids[$patient_id], $day, "user", $user_id );
						$patient = $patient{0};
						if (!empty($patient->id)){
							//update
							$patient->visit_hour = (int)$hour;
							$patient->save();
						}	
					}
				}		
			}
			
			if (!empty($new_pat_epids)){
				
				$new_pat_epids = EpidIpidMapping :: get_ipids_of_epids( array_unique( $new_pat_epids ) );
			}

			$patients_ipids = (array)$patients_ipids + (array)$new_pat_epids; 
			

			$i_u_ids[] = '99999999';
			$cnt = 0;
			$new_visits = array();
			$old_visits = array();
			
						
			foreach($post['visit_order'] as $visit_userid => $hours){
				foreach ($hours as $h => $orders){
					foreach ($orders as $o => $patients){

						$cnt++;
					
						$patient_id = $patients['patid'];
						$data_usertype = $patients['data_usertype'];
						$comment = htmlspecialchars($patients['comment']);
						$data_type = $patients['data_type']; //0 normal, "epid" text is passed when istead of patient[id] we have patient[epid]
						if(substr($patient_id, 0, 4) === "new_")
						{
							//new visit
							if ($data_type == "0"){
								$patient_id = Pms_Uuid::decrypt($patients['patient_enc_id']);
							} else{
								$patient_id = substr($patient_id, 4, strlen($patient_id)-1);
							}			
													
							$new_visits[$cnt]['visit_userid'] = $visit_userid;
							$new_visits[$cnt]['visit_patient'] = $patient_id;
							$new_visits[$cnt]['hour'] = $h;
							$new_visits[$cnt]['order'] = $o;
							$new_visits[$cnt]['comment'] = $comment;
							$new_visits[$cnt]['userid_type'] = $data_usertype;
						}else{
							//old visits have instead the ID from the dbf
							$old_visits[$cnt]['visit_userid'] = $visit_userid;
							$old_visits[$cnt]['id'] = $patient_id;
							$old_visits[$cnt]['hour'] = $h;
							$old_visits[$cnt]['order'] = $o;
							$old_visits[$cnt]['comment'] = $comment;
							$old_visits[$cnt]['manual_edit'] = (int)$patients['manual_edit'];
							$old_visits[$cnt]['userid_type'] = $data_usertype;
							
								
						}
					}
				}
			}
			foreach($new_visits as $k_visit_id => $visit_data)
			{
				if ( ! isset($patients_ipids[$visit_data['visit_patient']])) continue;
				
				$ins = new DailyPlanningVisits();
				$ins->date = $post['current_date'];
				$ins->userid = $visit_data['visit_userid'];
				$ins->clientid = $post['clientid'];
				if($visit_data['visit_patient'] != "")
				{
					$ins->ipid = $patients_ipids[$visit_data['visit_patient']];
				}
				else 
				{
					$ins->custom = '1';
				}
				$ins->orderid = (int)$visit_data['order'];
				$ins->hour = (int)$visit_data['hour'];
				$ins->comment = htmlspecialchars($visit_data['comment']);
				$ins->userid_type = $visit_data['userid_type'];
				$ins->is_autoassigned = 0;
				$ins->save();
				//inserted_updated_ids
				$i_u_ids[] = $ins->id;
			}
			foreach($old_visits as $k_visit_id => $visit_data)
			{
				$stmb = Doctrine::getTable('DailyPlanningVisits')->find($visit_data['id']);
				if( $stmb->id > 0){
					if($visit_data['visit_userid'] != $stmb->userid)
					{
						$stmb->userid = (int)$visit_data['visit_userid']; //ISPC - 2369
					}
					if($visit_data['userid_type'] != $stmb->userid_type)
					{
						$stmb->userid_type = $visit_data['userid_type']; //ISPC - 2369
					}
					$stmb->orderid = (int)$visit_data['order'];
					$stmb->hour = (int)$visit_data['hour'];
					$stmb->comment = htmlspecialchars($visit_data['comment']);
					
					if($visit_data['manual_edit'] == 1) {
						$stmb->is_autoassigned = 0; // this visit is a manual one
					}

					$stmb->save();
					$i_u_ids[] = $stmb->id;
				} 
				//ISPC - 2369
				/*$stmb = Doctrine::getTable('DailyPlanningVisits')->find($visit_data['id']);
				if( $stmb->id > 0){
					$ins = new DailyPlanningVisits();
					$ins->date = $stmb->date;
					$ins->userid = $visit_data['visit_userid'];
					$ins->clientid = $stmb->clientid;
					$ins->ipid = $stmb->ipid;
					$ins->orderid = (int)$visit_data['order'];
					$ins->hour = (int)$visit_data['hour'];
					$ins->userid_type = $visit_data['userid_type'];
					$ins->comment = htmlspecialchars($visit_data['comment']);
						
					if($visit_data['manual_edit'] == 1) {
						$ins->is_autoassigned = 0; // this visit is a manual one
					}
					$ins->save();
					//inserted_updated_ids
					$i_u_ids[] = $ins->id;
					//$stmb->save();
					//$i_u_ids[] = $stmb->id;
				}*/
			}
			/*
			foreach($post['visit_start'] as $k_visit_id => $v_visit_data)
			{
				if(strpos($k_visit_id, "visit_") !== false)
				{
					$new_visits[$k_visit_id]['visit_start'] = $v_visit_data;
					$new_visits[$k_visit_id]['visit_end'] = $post['visit_end'][$k_visit_id];
					$new_visits[$k_visit_id]['visit_userid'] = $post['visit_userid'][$k_visit_id];
					$new_visits[$k_visit_id]['visit_patient'] = $post['visit_patient'][$k_visit_id];
				}
				else
				{
					$old_visits[$k_visit_id]['visit_start'] = $v_visit_data;
					$old_visits[$k_visit_id]['visit_end'] = $post['visit_end'][$k_visit_id];
					$old_visits[$k_visit_id]['visit_userid'] = $post['visit_userid'][$k_visit_id];
					$old_visits[$k_visit_id]['visit_patient'] = $post['visit_patient'][$k_visit_id];
				}
			}
			
			foreach($new_visits as $k_visit_id => $visit_data)
			{
				$visit_data['visit_start_date'] = date('Y-m-d', strtotime($post['current_date'])) . ' ' . $visit_data['visit_start'] . ':00';
				$visit_data['visit_end_date'] = date('Y-m-d', strtotime($post['current_date'])) . ' ' . $visit_data['visit_end'] . ':00';

				$ins = new DailyPlanningVisits();
				$ins->date = $post['current_date'];
				$ins->userid = $visit_data['visit_userid'];
				$ins->clientid = $post['clientid'];
				$ins->ipid = $patients_ipids[$visit_data['visit_patient']];
				$ins->start_date = $visit_data['visit_start_date'];
				$ins->end_date = $visit_data['visit_end_date'];
				$ins->save();

				//inserted_updated_ids
				$i_u_ids[] = $ins->id;
			}
			foreach($old_visits as $ko_visit_id => $vo_visit_data)
			{
				$vo_visit_data['visit_start_date'] = date('Y-m-d', strtotime($post['current_date'])) . ' ' . $vo_visit_data['visit_start'] . ':00';
				$vo_visit_data['visit_end_date'] = date('Y-m-d', strtotime($post['current_date'])) . ' ' . $vo_visit_data['visit_end'] . ':00';

				$stmb = Doctrine::getTable('DailyPlanningVisits')->find($ko_visit_id);
				$stmb->userid = $vo_visit_data['visit_userid'];
				$stmb->start_date = $vo_visit_data['visit_start_date'];
				$stmb->end_date = $vo_visit_data['visit_end_date'];
				$stmb->save();
				$i_u_ids[] = $stmb->id;
			}
			*/
			$i_u_ids = array_values($i_u_ids);

			$this->clean_visits($post['clientid'], date('Y-m-d', strtotime($post['current_date'])), $i_u_ids);
		}

		private function clean_visits($clientid, $day, $excluded_visits = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_id = $logininfo->userid;
			
			$update = Doctrine_Query::create()
				->update('DailyPlanningVisits')
				->set('isdelete', "1")
				->set('change_user',$user_id)
				->set("change_date", '"'.date('Y-m-d H:i:s', time()).'"')
				->where('isdelete = "0"')
				->andWhere('clientid = "'.$clientid.'"')
				->andWhere('DATE(date) = "'.$day.'"');
			
				if( $excluded_visits !==false ){
					$update->andWhereNotIn('id', $excluded_visits);
				}
				$update->execute();
		}

		public function edit_visit($post)
		{
			$stmb = Doctrine::getTable('DailyPlanningVisits')->find($post['visit_id']);
			$stmb->userid = $post['userid'];
			$stmb->start_date = $post['start_date'];
			$stmb->end_date = $post['end_date'];
			$stmb->save();
		}

		public function delete_visit($post)
		{
			$stmb = Doctrine::getTable('DailyPlanningVisits')->find($post['visit_id']);
			$stmb->isdelete = 1;
			$stmb->save();
		}

	}

?>