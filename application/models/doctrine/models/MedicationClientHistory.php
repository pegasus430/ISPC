<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationClientHistory', 'SYSDAT');

	class MedicationClientHistory extends BaseMedicationClientHistory {

		public function getVerlaufMedicationClientHistory($clientid)
		{
			$medic = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientHistory')
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

		public function getVerlaufMedicationsClientHistory($clientid, $medisidsStr,$method_ids = false)
		{
			$medic = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientHistory')
				->where('clientid =' . $clientid . ' ')
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

		public function getVerlaufMedicationsClientHistory_original($clientid, $medisidsStr,$method_ids = false)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('MedicationClientHistory')
				->where('clientid =' . $clientid . ' ')
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

		public function getDataForUsers($clientid, $usersid = false)
		{
			if(is_array($usersid))
			{
				$users_str = "'99999999999'";
				$comma = ",";
				foreach($usersid as $userid)
				{
					$users_str .= $comma . "'" . $userid . "'";
					$comma = ",";
				}
			}
			else
			{
				if(empty($usersid))
				{
					$users_str = "'999999999'";
				}
				else
				{
					$users_str = $usersid;
				}
			}

			$usrsq = Doctrine_Query::create()
				->select('*, SUM(amount) as total, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientHistory')
				->where('clientid =' . $clientid . ' ')
				->andWhere('isdelete = "0"')
				->groupBy('medicationid, userid');
			if($usersid)
			{
				$usrsq->andWhere('userid IN (' . $users_str . ')');
			}
			$usrsexec = $usrsq->execute();
			if($usrsexec)
			{
				$usersarray = $usrsexec->toArray();
				return $usersarray;
			}
		}

		public function updateNewMedicationId($old_mid, $new_mid, $clientid)
		{
			$upd = Doctrine_Query::create()
				->update('MedicationClientHistory')
				->set('medicationid', $new_mid)
				->where('medicationid = "' . $old_mid . '"')
				->andWhere('clientid = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$rows = $upd->execute();
		}
		
		public function getUserDetails_160426($clientid, $medid, $userid, $date)
		{
			$usrsq = Doctrine_Query::create()
				->select('*, SUM(amount) as total, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientHistory')
				->where('clientid =' . $clientid . ' ')
				->andwhere('medicationid =' . $medid . ' ')
				->andwhere('userid =' . $userid . ' ')
				->andWhere('create_date <="' . $date . '"')
				->andWhere('isdelete = "0"')
				->groupBy('medicationid');
			$totaluserarr = $usrsq->fetchArray();

			if($totaluserarr)
			{
				$totaluser = $totaluserarr[0]['total']; // total user
				return $totaluser;
			}
		}
		
		public function getUserDetails($clientid, $medid, $userid, $date)
		{
		    $usrsq = Doctrine_Query::create()
		    ->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
		    ->from('MedicationClientHistory')
		    ->where('clientid =' . $clientid . ' ')
		    ->andwhere('medicationid =' . $medid . ' ')
		    ->andwhere('userid =' . $userid . ' ')
		    ->andWhere('isdelete = "0"');
		    $totaluserarr = $usrsq->fetchArray();
		
		    if($totaluserarr){
		        
    		    $total="0";
    		    foreach($totaluserarr as $k=>$hv){
    		        if($hv['create_date'] <= $date){
    		            $total += $hv['amount'];
    		        }
    		    }
    		    	
    		    return $total;
		    }
		}
		
		public function get_users_patients($clientid, $userids)
		{
			$patient_related_methods = array('5', '7', '9');
			$sql = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientHistory')
				->where('clientid =' . $clientid . ' ')
				->andWhere('ipid != "0"')
				->andWhere('isdelete = "0"')
				->andWhereIn('userid', $userids)
				->andWhereIn('methodid', $patient_related_methods);
			$sql_res = $sql->fetchArray();

			$users_patients['ipids'][] = '999999999999';
			foreach($sql_res as $k_res => $v_res)
			{
				$users_patients[$v_res['medicationid']][$v_res['userid']][] = $v_res['ipid'];
				$users_patients['ipids'][] = $v_res['ipid'];

				$users_patients[$v_res['medicationid']][$v_res['userid']] = array_values(array_unique($users_patients[$v_res['medicationid']][$v_res['userid']]));
				$users_patients['ipids'] = array_values(array_unique($users_patients['ipids']));
			}


			return $users_patients;
		}

		public function get_stock_entry_details($stock_id)
		{
			$stock = Doctrine_Query::create()
				->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientHistory')
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

		public function get_user_stock($clientid, $user = false, $medication = false)
		{
			$medic = Doctrine_Query::create()
				->select('*, SUM(amount) as total_amount, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as create_date')
				->from('MedicationClientHistory')
				->where('clientid = "' . $clientid . '" ')
				->andWhere('isdelete = "0"');
			if($user)
			{
				$medic->andWhere('userid LIKE "' . $user . '"');
			}

			if($medication)
			{
				$medic->andWhere('medicationid = "' . $medication . '"');
			}
			$medic->groupBy('medicationid, userid');
			$medicsch = $medic->fetchArray();

			if($medicsch)
			{
				foreach($medicsch as $k_med => $v_med)
				{
					if(strlen($v_med['total_amount']) > '0' && $v_med['total_amount'] > 0)
					{
						$usr_med[] = $v_med;
					}
				}

				if($usr_med)
				{
					return $usr_med;
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

		public function toggle_stock_status($stock_id, $clientid)
		{

			$medic = Doctrine_Query::create()
				->select('*')
				->from('MedicationClientHistory')
				->where('id = "' . $stock_id . '" ')
				->andWhere('isdelete = "1"');
			$med_res = $medic->fetchArray();

			if(!$med_res)
			{
				$upd = Doctrine_Query::create()
					->update('MedicationClientHistory')
					->set('isdelete', "1")
					->where('id="' . $stock_id . '"')
					->andWhere('clientid = "' . $clientid . '"');
				$rows = $upd->execute();
			}
		}

		public function getClientTotalbydate($clientid, $medid, $date,$dbg=false)
		{
		
		    $medictot = Doctrine_Query::create()
		    ->select('*, IF(done_date != "0000-00-00 00:00:00", done_date, create_date) as sec_create_date')
		    ->from('MedicationClientHistory')
		    ->Where('clientid = ?', $clientid)
		    ->andWhere('medicationid = ?', $medid)
		    ->andWhere('isdelete = ?', 0)
		    ->orderBy('create_date ASC');
		    $totalcleintarr = $medictot->fetchArray();
		
		    if($totalcleintarr)
		    {
		        $total="0";
		        foreach($totalcleintarr as $k=>$hv){
// 		            if($dbg){
// 		                var_dump(strtotime($hv['create_date']) <= strtotime($date));
// 		                print_r($hv['amount']);
// 		                print_r("\n");
// 		            }
		            if(strtotime($hv['sec_create_date']) <= strtotime($date)){
		                $total += $hv['amount'];
		                $full_array[] =  $hv;
		            }
		        }
		
		
// 		        if($dbg){
// 		            print_r($full_array);
// 		            exit;
// 		        }
		        	
		
		        return $total;
		    }
		}
		
		
		
		/**
		 * @todo - implement the next function
		 * alter tables to innodb or other transactional
		 * do a transaction on changing stocks
		 * @param MedicationClientStock $MCS
		 */
		public function changeStockAndUser( MedicationClientStock $MCS)
		{
			
			die( __CLASS__ );
			
			$conn = Doctrine_Manager::connection();
			
			die(print_r($conn) . "\n" . __CLASS__ );
			
			try {
			    $conn->beginTransaction();
			    // do some operations here
			
			    // creates a new savepoint called mysavepoint
			    $conn->beginTransaction('mysavepoint');
			    try {
			        // do some operations here
			        
			    	$MCS->save();
			    	
			
			        $conn->commit('mysavepoint');
			    } catch(Exception $e) {
			        $conn->rollback('mysavepoint');
			    }
			    $conn->commit();
			} catch(Exception $e) {
			    $conn->rollback();
			}
		}
		
		
		
		
		/**
		 * check if a drug qty exist at a given date, ifnot, find next available date when he has this qty
		 * used in ispc-1864 p.10
		 * @todo: link the SEAL_DATE functionality into this, so we don't validate *.*, instead we will validate just from the SEAL_DATE onwards.. the rest is history
		 * 
		 * @param array $post
		 * 
		 * @return
		 * array(
		 * 		result => boolean;
		 * 		t0 => date 'd.m.Y H:i'
		 * 		t0_amount => number
		 * 		next_available_date => date 'd.m.Y H:i'
		 * 		amount_available_date => number
		 * 
		 * );
		 * 
		 * result = true = if the date user has chosen is OK
		 * result = false = if the date user has chosen is BAD
		 * t0 = selected date_time
		 * t0_amount = amount on user selected date
		 * next_available_date = date string of next possible date to document the action
		 * amount_available_date = amount on the next possible date
		 * 		 
		 */
		public function validate_amount_by_date (array $post)
		{
			if ( empty($post) 
				|| empty($post['clientid']) 
				|| empty($post['selectUser']) 
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
						
			$clientid = $post['clientid'];
			$user = $post['selectUser'];
			$medicationid = $post['medicationid'];
			$amount = $post['amount']; // qty we want to deduct from the user stock			
			$date_time = date("Y-m-d H:i:s" , strtotime($post['date']. " " .$post['time']));
				
			
			//get the stock at t0 = date
			$query_in_the_past = Doctrine_Query::create()
			->select('id, SUM(amount) as total_amount ')
			->from('MedicationClientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('isdelete = "0"')
			->andWhere('userid =  ? ' , $user )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) <= STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time );
// 			->having("done_create_date < STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time );
// 			$query_in_the_past->removeDqlQueryPart('having');
// 			$query_in_the_past->getDqlPart('having');
			
			$stock_at_t0 = $query_in_the_past->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
				
// 			print_r($stock_at_t0);
// 			Pms_DoctrineUtil::get_raw_sql($query_in_the_past);
			

			
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
			->from('MedicationClientHistory')				
			->where('clientid = ? ', $clientid )
			->andWhere('isdelete = "0"')
			->andWhere('userid =  ? ' , $user )
			->andWhere('medicationid = ? ' , $medicationid )		
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time )
			->orderBy('done_create_date ASC');
			
			$stock_after_t0 = $query_in_the_present->fetchArray();
// 			print_r($stock_after_t0);
			
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
			
			return $result;
		}
		
		


		//used in btm history CORRECTION
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
			->from('MedicationClientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('userid = ? ' , $post['datatables_data']['userid'] )
			->andWhere('isdelete = "0"')
			->andWhere("IF(done_date != '0000-00-00 00:00:00', done_date, create_date) < STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s') " , $date_time );
		
			$stock_before_t0 = $query_in_the_past->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
			//get the stock at t0 = date
			$query_in_the_past = Doctrine_Query::create()
			->select('id, SUM(amount) as total_amount ')
			->from('MedicationClientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('userid = ? ' , $post['datatables_data']['userid'] )
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
			->from('MedicationClientHistory')
			->where('clientid = ? ', $clientid )
			->andWhere('medicationid = ? ' , $medicationid )
			->andWhere('userid = ? ' , $post['datatables_data']['userid'] )
			->andWhere('methodid != 13 ' )
			->andWhere('isdelete = "0"')
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
					"table_name" => 'MedicationClientHistory'
		
			);
			
			
			if ( $result['result'] && $post['validate_step'] == 1 ) {
				
				//validate2 because correction will be performed on connected also
				//validate connected tresor, user, patient
				
				
				$post['validate_step'] = 2;
				
				$this_row = self::get_connected_from_id($post['id']);
				
				if ( ! empty($this_row['stid']) ) {
					
					$post['old_amount'] = (-1) * $post['old_amount'] ;
					$post['table'] = 'MedicationClientStock' ;
					$post['id'] = $this_row['stid'];
					
					$result = MedicationClientStock :: validate_positive_stock_after_correction_date($post);
					
				} elseif ( ! empty($this_row['self_id']) ) {
									
					$connected_id = self::get_connected_from_id($this_row['self_id']);
					
					$post['old_amount'] = (-1) * $post['old_amount'] ;
					$post['datatables_data']['userid'] = $connected_id['userid'];
					$post['table'] = 'MedicationClientHistory' ;
					$post['id'] = $connected_id['id'];
					
					$result = MedicationClientHistory :: validate_positive_stock_after_correction_date($post);
					
				} elseif ( ! empty($this_row['patient_stock_id']) ) {

					//verbrauch or angabe an patient
					$post['table'] = 'MedicationPatientHistory' ;
					$post['id'] = $this_row['patient_stock_id'];

					$post['old_amount'] = (-1) * $post['old_amount'] ;

					$result = MedicationPatientHistory :: validate_positive_stock_after_correction_date($post);
				

				}			
			}
			return $result;
		}
		
		
		private function sign($n) {
			return ($n > 0) - ($n < 0);
		}
		
		
		public static function get_connected_from_self_id ( $id = 0 ) 
		{
			$row = Doctrine_Query::create()
			->select('*')
			->from('MedicationClientHistory')
			->where('id = ? ', $id )
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
			return ($row);
		}
		
		
		public static function get_connected_from_stid ( $id = 0 )
		{
			$row = Doctrine_Query::create()
			->select('*')
			->from('MedicationClientHistory')
			->where('stid = ? ', $id )
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
				
			return ($row);	
		}
		
		
		public static function get_connected_from_patient_stock_id ( $id = 0 )
		{
			$row = Doctrine_Query::create()
			->select('*')
			->from('MedicationClientHistory')
			->where('patient_stock_id = ? ', $id )
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
			return ($row);
		}
		
		
		public static function get_connected_from_id ( $id = 0 )
		{
			$row = Doctrine_Query::create()
			->select('*')
			->from('MedicationClientHistory')
			->where('id = ? ', $id )
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
			return ($row);
		
		
		}
	}

?>