<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationPatientHistory', 'MDAT');

	class MedicationPatientHistory extends BaseMedicationPatientHistory {

		public function check_new_entries($clientid, $ipid, $medi)
		{
			$medic = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationPatientHistory')
				->where('clientid = "' . $clientid . '" ')
				->andWhere('ipid LIKE "' . $ipid . '"')
				->andWhere('medicationid = "' . $medi . '"')
				->andWhere('isdelete = "0"');
			$medicsch = $medic->fetchArray();

			if($medicsch)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function get_patient_stock($clientid, $ipid = false, $medication = false)
		{
			$medic = Doctrine_Query::create()
				->select('*, SUM(amount) as total_amount, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationPatientHistory')
				->where('clientid = "' . $clientid . '" ')
				->andWhere('isdelete = "0"');
			if($ipid)
			{
				$medic->andWhere('ipid LIKE "' . $ipid . '"');
			}

			if($medication)
			{
				$medic->andWhere('medicationid = "' . $medication . '"');
			}
			$medic->groupBy('medicationid, ipid');
			$medicsch = $medic->fetchArray();

			if($medicsch)
			{
				foreach($medicsch as $k_med => $v_med)
				{
					if(strlen($v_med['total_amount']) > '0' && $v_med['total_amount'] > 0)
					{
						$pat_med[] = $v_med;
					}
				}

				if($pat_med)
				{
					return $pat_med;
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

		public function get_patients_stocks($clientid, $ipids = false)
		{
			$medic = Doctrine_Query::create()
				->select('*, SUM(amount) as total_amount, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationPatientHistory')
				->where('clientid = "' . $clientid . '" ')
				->andWhere('isdelete = "0"')
				->groupBy('medicationid, ipid');
			if($ipids)
			{
				$medic->andWhereIn('ipid', $ipids);
			}
			$medicsch = $medic->fetchArray();

			if($medicsch)
			{
				foreach($medicsch as $k_med => $v_med)
				{
					$patient_medications[$v_med['medicationid']][$v_med['ipid']] = $v_med['total_amount'];
				}

				return $patient_medications;
			}
			else
			{
				return false;
			}
		}

		public function getAllMedicationPatientHistory($clientid, $ipid, $medication_id = false)
		{
			$medic = Doctrine_Query::create()
				->select('*, SUM(amount) as total_amount, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationPatientHistory')
				->where('clientid = "' . $clientid . '" ')
				->andWhere('ipid = "' . $ipid . '" ')
				->andWhere('isdelete = "0"');
    			if($medication_id && strlen($medication_id) > 0)
    			{
    				$medic->andWhere('medicationid = "' . $medication_id . '" ');
    			}
    			
				$medic->groupBy('medicationid');
			$medicsch = $medic->fetchArray();

			if($medicsch)
			{
				return $medicsch;
			}
		}

		public function getVerlaufMedicationPatientHistory($clientid, $ipid = false, $medications_arr = false, $methods = false)
		{
			$medic = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationPatientHistory')
				->Where('clientid =' . $clientid . ' ')
				->andWhere('isdelete = "0"')
				->orderBy('create_date ASC');

			if($ipid)
			{
				if(is_array($ipid))
				{
					$patients_ipids = $ipid;
				}
				else
				{
					$patients_ipids = array($ipid);
				}

				$medic->andWhereIn('ipid', $patients_ipids);
			}

			if(	$medications_arr !== false 
					&& ! empty($medications_arr) 
					&& is_array($medications_arr))
			{
// 				$medications_arr[] = "9999999999";
				$medic->andWhereIn('medicationid', $medications_arr);
			}

			if(	$methods !== false
					&& ! empty($medications_arr) 
					&& is_array($medications_arr))
			{
// 				$methods[] = "9999999999";
				$medic->andWhereIn('methodid', $methods);
			}
//			print_r($medic->getSqlQuery());
//			exit;
			$medicsch = $medic->fetchArray();

			if($medicsch)
			{
				return $medicsch;
			}
		}

		public function get_patient_given_users($ipid, $clientid)
		{
			$medic = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationPatientHistory')
				->where('clientid = "' . $clientid . '" ')
				->andWhere('ipid = "' . $ipid . '" ')
				->andWhere('amount > "0"')
				->andWhere('userid > "0"')
				->andWhere('isdelete = "0"');
			$med_res = $medic->fetchArray();

			if($med_res)
			{
				foreach($med_res as $k_med => $v_med)
				{
					$patient_given_users[] = $v_med['userid'];
				}

				return $patient_given_users;
			}
			else
			{
				return false;
			}
		}

		public function get_stock_entry_details($stock_id)
		{
			$stock = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationPatientHistory')
				->where('id = "' . $stock_id . '"');
			$stock_res = $stock->fetchArray();

			if($stock_res)
			{
				return $stock_res[0];
			}
			else
			{
				return false;
			}
		}

		public function updateNewMedicationId($old_mid, $new_mid, $clientid)
		{
			$upd = Doctrine_Query::create()
				->update('MedicationPatientHistory')
				->set('medicationid', $new_mid)
				->where('medicationid = "' . $old_mid . '"')
				->andWhere('clientid = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$rows = $upd->execute();
		}

		public function toggle_stock_status($stock_id, $clientid)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('MedicationPatientHistory')
				->where('id = "' . $stock_id . '" ')
				->andWhere('isdelete = "1"');
			$med_res = $medic->fetchArray();

			if(!$med_res)
			{
				$upd = Doctrine_Query::create()
					->update('MedicationPatientHistory')
					->set('isdelete', "1")
					->where('id="' . $stock_id . '"')
					->andWhere('clientid = "' . $clientid . '"');
				$rows = $upd->execute();
			}
		}

		/**
		 * 
		 * @param array $post
		 * @return boolean|multitype:boolean string Ambigous <boolean, string> mixed Ambigous <number, unknown>
		 */
		public function validate_amount_by_date( array $post )
		{
			
			if ( empty($post)
					|| empty($post['clientid'])
					|| empty($post['ipid'])
					|| empty($post['medicationid'])
					|| empty($post['amount'])
			) {
				return false;
			}
				
			if (empty ($post['date'])) {
				$post['date'] = date("Y-m-d");
			}
			if (empty ($post['time'])) {
				$post['time'] = date("H:i:s");
			}
			
			$ipid = $post['ipid'];
			$clientid = $post['clientid'];
			$user = $post['selectUser'];
			$medicationid = $post['medicationid'];
			$amount = $post['amount']; // qty we want to deduct from the user stock
			$date_time = date("Y-m-d H:i:s" , strtotime($post['date']. " " .$post['time']));
			
				
			//get the stock at t0 = date

			
			$query_at_t0 = Doctrine_Query::create()
			->select('id, SUM(amount) as total_amount ')
			->from('MedicationPatientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('ipid = ? ', $ipid )
			->andWhere('medicationid = ? ', $medicationid )
			->andWhere('isdelete = 0')
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) <= STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time );
			
			$stock_at_t0 = $query_at_t0->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
// 			Pms_DoctrineUtil::get_raw_sql($query_at_t0);
					
			
			if ( ! isset($stock_at_t0['total_amount'])
					|| (isset($stock_at_t0['total_amount']) && $amount > $stock_at_t0['total_amount'])
			) {
				// this is a first hint that stock cannot be documented at that date in time ...
				// discover at what date we can document this action, so we can inform/help the benutzer
				$next_available_date = false;
			} else {
				$next_available_date = true; // the date user has chosen is ok at first glance
			}
				
			
			//ELSE :
			//get the stock after this date to validate our assumptions that we can deduct this xAmount, or to find the next possible date
			$query_in_the_present = Doctrine_Query::create()
			->select('id, amount, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as done_create_date')
			->from('MedicationPatientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('ipid = ? ', $ipid )
			->andWhere('medicationid = ? ', $medicationid )
			->andWhere('isdelete = 0')
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time )
			->orderBy('done_create_date ASC');
			
			$stock_after_t0 = $query_in_the_present->fetchArray();
			
			$stock_after_this_row = (isset($stock_at_t0['total_amount'])) ? $stock_at_t0['total_amount'] : 0;
			$amount_available_date = 0;
				
			
			foreach( $stock_after_t0 as $row ) {
			
				$stock_after_this_row += $row['amount'];
			
				if ($stock_after_this_row < $amount) {
					//validation failed
					$next_available_date = false;
				} elseif( ! $next_available_date ) {
						
					$next_available_date = date('d.m.Y H:i',strtotime($row['done_create_date']) + 60); //use this as a possible date
					$amount_available_date = $stock_after_this_row;
				}
			
			
			}
				
			$result =  array(
					"result" => ($next_available_date === true) ? true : false,
					"t0" => date("d.m.Y H:i" , strtotime($date_time)),
					"t0_amount" => $stock_at_t0['total_amount'],
					"next_available_date" => $next_available_date,
					"amount_available_date" => $amount_available_date
						
			);
			
			
			
			return $result ;
		}
		
		
		//used in btm history
		public static function validate_positive_stock_after_correction_date (array $post)
		{
		
		
// 						die(print_r($post));
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
			->from('MedicationPatientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('ipid = ? ' , $post['datatables_data']['ipid'] )
			->andWhere('isdelete = "0"')
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) < STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time );
		
// 			 			Pms_DoctrineUtil::get_raw_sql($query_in_the_past);
			
			$stock_before_t0 = $query_in_the_past->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
			//get the stock at t0 = date
			$query_in_the_past = Doctrine_Query::create()
			->select('id, SUM(amount) as total_amount ')
			->from('MedicationPatientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('ipid = ? ' , $post['datatables_data']['ipid'] )
			->andWhere('isdelete = "0"')
			->andWhere('methodid != 13 ' )
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) = STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time );
// 			Pms_DoctrineUtil::get_raw_sql($query_in_the_past);
				
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
		
			//?exclude $correction_new_id performed after this date?
			//change amount with corrected 
			$correction_new_id = array();
			$mcbtmc = new MedicationClientBTMCorrection();
			$mcbtmc_r = $mcbtmc->get_by_correction_table( $post['table'] , $clientid);		
			

		
			//ELSE :
			//get the stock after this date to validate our assumptions that we can deduct this xAmount, or to find the next possible date
			$query_in_the_present = Doctrine_Query::create()
			->select('*, id, amount, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as done_create_date')
			->from('MedicationPatientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('ipid = ? ' , $post['datatables_data']['ipid'] )
			->andWhere('isdelete = "0"')
			->andWhere('methodid != 13 ' )
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time )
			->orderBy('done_create_date ASC');
		
			//exclude $correction_new_id performed after this date
// 			if ( ! empty($correction_new_id)) {
// 				$query_in_the_present->andWhereIn('id' ,  $correction_new_id, true);
// 			}
// 						Pms_DoctrineUtil::get_raw_sql($query_in_the_present);
		
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
					"table_name" => 'MedicationPatientHistory',
					
		
			);
			
			
			if ( $result['result'] && $post['validate_step'] == 1 ) {
				
				//validate2 because correction will be performed on connected also
				//validate connected user
				//connected patient is not validated
				
				
				$post['validate_step'] = 2;
				
				$this_row = self::find_connected_row($post['id']);
				
				if ($this_row['self_id'] == 0){
				
					$connected_patient_stock_id = MedicationClientHistory :: get_connected_from_patient_stock_id($post['id']);

					if ( ! empty($connected_patient_stock_id) ) {
							
						$post['old_amount'] = (-1) * $post['old_amount'] ;
						$post['datatables_data']['userid'] = $connected_patient_stock_id['userid'];
						
						$post['table'] = 'MedicationClientHistory' ;
						$post['id'] = $connected_patient_stock_id['id'];
						
						
						$result = MedicationClientHistory :: validate_positive_stock_after_correction_date($post);

					}
				
				} else {
					//validate connected patient?
					//until now,  patient is connected with self ipid only, so amount - amount = ok allways
					
				}
				
				
			}
			
			
			return $result;
		}
		
		
		private function sign($n) {
			return ($n > 0) - ($n < 0);
		}
		
		

		public static function find_connected_row ( $id = 0 )
		{
			$row = Doctrine_Query::create()
			->select('id, self_id')
			->from('MedicationPatientHistory')
			->where('id = ? ', $id )
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

			
			return $row;	
				
		}
		
		public static function get_connected_from_id ( $id = 0 )
		{
			$row = Doctrine_Query::create()
			->select('*')
			->from('MedicationPatientHistory')
			->where('id = ? ', $id )
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
			return ($row);
		}
		
	}

	

	
?>