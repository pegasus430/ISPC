<?php

	require_once("Pms/Form.php");

	class Application_Form_DailyPlanningVisits2 extends Pms_Form {

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
			//print_r($post); exit;
			//print($post); exit;
			//get patients ipids
			$patients_ipids = PatientMaster::get_patients_ipids($post['visit_patient']);
			$i_u_ids[] = '99999999';
			
			
			
			
			foreach($post['visit_userid'] as $k_visit_id => $v_visit_data)
			{
				if(strpos($k_visit_id, "visit_") !== false)
				{
					$new_visits[$k_visit_id]['visit_userid'] = $post['visit_userid'][$k_visit_id];
					$new_visits[$k_visit_id]['visit_order_number'] = $post['visit_order_number'][$k_visit_id];
					$new_visits[$k_visit_id]['visit_patient'] = $post['visit_patient'][$k_visit_id];
				}
				else
				{
					$old_visits[$k_visit_id]['visit_userid'] = $post['visit_userid'][$k_visit_id];
					$old_visits[$k_visit_id]['visit_order_number'] = $post['visit_order_number'][$k_visit_id];
					$old_visits[$k_visit_id]['visit_patient'] = $post['visit_patient'][$k_visit_id];
				}
			}
//  			print_r($old_visits); exit;
			foreach($new_visits as $k_visit_id => $visit_data)
			{

				$ins = new DailyPlanningVisits2();
				$ins->date = $post['current_date'];
				$ins->userid = $visit_data['visit_userid'];
				$ins->clientid = $post['clientid'];
				$ins->ipid = $patients_ipids[$visit_data['visit_patient']];
				$ins->order_number = $visit_data['visit_order_number'];
				$ins->save();

				//inserted_updated_ids
				$i_u_ids[] = $ins->id;
			}

			foreach($old_visits as $ko_visit_id => $vo_visit_data)
			{

				$stmb = Doctrine::getTable('DailyPlanningVisits2')->find($ko_visit_id);
				$stmb->order_number = $vo_visit_data['visit_order_number'];
				$stmb->save();
				$i_u_ids[] = $stmb->id;
			}

			$i_u_ids = array_values($i_u_ids);

			$this->clean_visits($post['clientid'], date('Y-m-d', strtotime($post['current_date'])), $i_u_ids);
		}

		private function clean_visits($clientid, $day, $excluded_visits = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_id = $logininfo->userid;
			
			$update = Doctrine_Query::create()
				->update('DailyPlanningVisits2')
				->set('isdelete', "1")
				->set('change_user',$user_id)
				->set("change_date", '"'.date('Y-m-d H:i:s', time()).'"')
				->where('isdelete = "0"')
				->andWhere('clientid = "'.$clientid.'"')
				->andWhere('DATE(date) = "'.$day.'"')
				->andWhereNotIn('id', $excluded_visits)
				->execute();
		}

		public function edit_visit($post)
		{
			$stmb = Doctrine::getTable('DailyPlanningVisits2')->find($post['visit_id']);
			$stmb->order_number = $post['order_number'];
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