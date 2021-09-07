<?php
require_once("Pms/Form.php");
class Application_Form_BwPerformanceRecord extends Pms_Form
{
	public function insert_values ( $ipid, $post, $current_period_days,$bw_shortcuts, $overall_flatrate_days )
	{
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$conditions_overall['periods'][0]['start'] = "2008-01-01";
		$conditions_overall['periods'][0]['end'] = date("Y-m-d");
		$conditions_overall['client'] = $clientid;
		$conditions_overall['ipids'] = array($ipid);
		$patient_days_overall = Pms_CommonData::patients_days($conditions_overall);
		
		$patient_active_days_arr = $patient_days_overall[$ipid]['treatment_days'];
	 
		foreach($patient_active_days_arr as $k=>$date){
			$patient_active_days[] = date("Y-m-d", strtotime($date));
		}		
		
		
		ksort($post);
		
		//print_r($patient_active_days);
		
		foreach($overall_flatrate_days as $pay_dayss => $fdays){
			foreach($fdays as $k=>$fdate){
				$flatrate_intervals_d2p[$fdate] = $pay_dayss;
			}
		}
		$saved_pay_days = array_keys($overall_flatrate_days);
		
		foreach($saved_pay_days as $k=>$exisitng_pay_date){
			if(in_array($exisitng_pay_date,$current_period_days)){
				$pay_date_in_period[] =$exisitng_pay_date; 
			}
		}
		
// 		print_r($pay_date_in_period); 
// 		print_r($saved_pay_days); 
// 		print_r($patient_active_days); 
	 
		
		$pay_days_array = array();
		$post_pay_days = array();
		foreach($post as $date=>$shortcut_data){
			$formatted_date = date('Y-m-d H:i:s', strtotime($date));
			foreach($shortcut_data as $shortcut => $post_values){
				if($shortcut == "37b1" && $post_values['pay_days'] == "1" ){
					$post_pay_days[] = $date;
				}
				
				if($shortcut == "37b1" && $post_values['qty'] == "1" ){
					if($post_values['pay_days'] == "1" && in_array($date,$saved_pay_days))
					{
						if(!empty($overall_flatrate_days[$date])){
							foreach($overall_flatrate_days[$date] as $k=>$ex_fl_date){
								$flat_rate_days[] = $ex_fl_date;
								$post_fl_intervals[$date][] = $ex_fl_date;
								$fd2pd[$ex_fl_date] = $date;
							}							
						}
						$pay_days_array[] = $date;
						
						
					} 
					else if($post_values['pay_days'] == "1" && !in_array($date,$saved_pay_days))
					{
// 						print_r("PAPUCI");
						// Check, the next valid days 
						if(in_array($date,$patient_active_days)){
// 							print_r("PUFOSI");
							
							$start_key = array_search($date,$patient_active_days);

							$pay_days_array[]= $patient_active_days[$start_key];
							for($i=$start_key; $i < $start_key+7; $i++){
								$flat_rate_days[] = $patient_active_days[$i];
								$post_fl_intervals[$patient_active_days[$start_key]][] = $patient_active_days[$i];
								$fd2pd[$patient_active_days[$i]] = $patient_active_days[$start_key];
							}
						}
					} else{
						// if flatrate_started in previuos month 
						
						// started in previous month, or it was removed
					}
				}
			}
		}
		
// 		print_r($flat_rate_days); 
// 		print_r($post_pay_days); 
// 		exit;
 
		
		if(!empty($flat_rate_days)){
			foreach($flat_rate_days as $k=>$fday){
				$post[$fday]['37b1']['qty'] = "1";
			}
		}
 
		if(!empty($pay_date_in_period) && empty($post_pay_days)){
			// save all 
			foreach($pay_date_in_period as $k=>$saved_pd){
// 				print_r($overall_flatrate_days[$saved_pd]); //exit;
				
				foreach($overall_flatrate_days[$saved_pd] as $ok=>$rem_fday){
					$post[$rem_fday]['37b1']['qty'] = "0";
					$post[$rem_fday]['37b1']['pay_days'] = "0";
					$post[$rem_fday]['37b1']['pay_date'] = $saved_pd;
					
					
					$ind = new BwPerformanceRecordFlatrate();
					$ind->ipid = $ipid;
					$ind->pay_date = $saved_pd;
					$ind->flatrate_date = $rem_fday;
					$ind->isdelete = '1';
					$ind->save();
				}
			}
		}
 
		
		foreach($post_fl_intervals as $pay_day=>$fldates){
			foreach($fldates as $k=>$fd){
				$ind = new BwPerformanceRecordFlatrate();
				$ind->ipid = $ipid;
				$ind->pay_date = $pay_day;
				$ind->flatrate_date = $fd;
				$ind->save();
			}
		}
		
		
		foreach($post as $date=>$shortcut_data){
			$formatted_date = date('Y-m-d H:i:s', strtotime($date));
			
			foreach($shortcut_data as $shortcut => $post_values){

				if($shortcut == "37b1"){
					if($fd2pd[$date]){
						$pay_date_save = $fd2pd[$date];
					} else{
						$pay_date_save = $post_values['pay_date'];
					}
				} else{
					$pay_date_save = $post_values['pay_date'];
				}
				
				$final_data[] = array(
						'ipid' => $ipid,
						'shortcut' => $shortcut,
						'qty' => $post_values['qty'],
						'pay_days' => $post_values['pay_days'],
						'pay_date' => $pay_date_save,
						'date' => $formatted_date,
				);
			}
		} 

		//insert current month data
		$brasapv_collection = new Doctrine_Collection('BwPerformanceRecord');
		$brasapv_collection->fromArray($final_data);
		$brasapv_collection->save();
	}

	public function reset_for_saving( $ipid, $start_date,$post = false,$flatrate = false, $days_in_period = false )
	{
		$start_date = date('Y-m-d H:i:s', strtotime($start_date));
		
		
		// update all 
		if(is_array($ipid))
		{
			if(count($ipid) > 0)
			{
				$ipid = $ipid;
			}
			else
			{
				$ipid[] = '9999999999999';
			}
		}
		else
		{
			$ipid = array($ipid);
		}

		$query = Doctrine_Query::create()
		->select('*')
		->from('BwPerformanceRecord')
		->whereIn('ipid', $ipid)
		->andWhere('isdelete = 0')
		->andWhere('MONTH(date) = MONTH("' . $start_date . '")')
		->andWhere('YEAR(date) = YEAR("' . $start_date . '")');
		$q_res = $query->fetchArray();
		

		$query_fl = Doctrine_Query::create()
		->select('*')
		->from('BwPerformanceRecordFlatrate')
		->whereIn('ipid', $ipid)
		->andWhere('isdelete = 0')
		->andWhere('pay_date != "0000-00-00" ')
		->andWhere('MONTH(pay_date) = MONTH("' . $start_date . '")')
		->andWhere('YEAR(pay_date) = YEAR("' . $start_date . '")');
		$q_res_fl = $query_fl->fetchArray();
		
		
		if(!empty($q_res_fl)){
			foreach($q_res_fl as $k_res => $v_res)
			{
				$mod = Doctrine::getTable('BwPerformanceRecordFlatrate')->find($v_res['id']);
				$mod->isdelete = 1;
				$mod->save();
				
				$saved_pay_dates[] = $v_res['pay_date'];
				$saved_pay_dates = array_unique($saved_pay_dates);
			}

			
			if(!empty($saved_pay_dates ))
			{
				// get all entries where this pay date is set
				$query_pdate = Doctrine_Query::create()
				->select('*')
				->from('BwPerformanceRecord')
				->whereIn('ipid', $ipid)
				->andWhereIn('pay_date', $saved_pay_dates )
				->andWhere('isdelete = 0');
				$q_res_pdates = $query_pdate->fetchArray();
				
				
				if(!empty($q_res_pdates)){
					foreach($q_res_pdates as $k=>$saved_dates_with_pdate){
						$mod = Doctrine::getTable('BwPerformanceRecord')->find($saved_dates_with_pdate['id']);
// 						$mod->isdelete = 1;
						$mod->qty = 0;
						$mod->save();
					}
				}
			}
			
		}
		
		
		if($q_res)
		{
			foreach($q_res as $k_res => $v_res)
			{
				$mod = Doctrine::getTable('BwPerformanceRecord')->find($v_res['id']);
				$mod->isdelete = 1;
				$mod->save();
			}
		}

	}

	
	public function reset( $ipid, $start_date, $flatrate = false, $days_in_period = false )
	{
			$start_date = date('Y-m-d H:i:s', strtotime($start_date));
		
		
		// update all 
		if(is_array($ipid))
		{
			if(count($ipid) > 0)
			{
				$ipid = $ipid;
			}
			else
			{
				$ipid[] = '9999999999999';
			}
		}
		else
		{
			$ipid = array($ipid);
		}

		$query = Doctrine_Query::create()
		->select('*')
		->from('BwPerformanceRecord')
		->whereIn('ipid', $ipid)
		->andWhere('isdelete = 0')
		->andWhere('MONTH(date) = MONTH("' . $start_date . '")')
		->andWhere('YEAR(date) = YEAR("' . $start_date . '")');
		$q_res = $query->fetchArray();
		

		$query_fl = Doctrine_Query::create()
		->select('*')
		->from('BwPerformanceRecordFlatrate')
		->whereIn('ipid', $ipid)
// 		->andWhere('isdelete = 0')
		->andWhere('pay_date != "0000-00-00" ')
		->andWhere('MONTH(pay_date) = MONTH("' . $start_date . '")')
		->andWhere('YEAR(pay_date) = YEAR("' . $start_date . '")');
		$q_res_fl = $query_fl->fetchArray();
		
		
		if(!empty($q_res_fl)){
			foreach($q_res_fl as $k_res => $v_res)
			{
				$mod = Doctrine::getTable('BwPerformanceRecordFlatrate')->find($v_res['id']);
				$mod->isdelete = 1;
				$mod->save();
				
				$saved_pay_dates[] = $v_res['pay_date'];
				$saved_pay_dates = array_unique($saved_pay_dates);
			}

			
			if(!empty($saved_pay_dates ))
			{
				// get all entries where this pay date is set
				$query_pdate = Doctrine_Query::create()
				->select('*')
				->from('BwPerformanceRecord')
				->whereIn('ipid', $ipid)
				->andWhereIn('pay_date', $saved_pay_dates )
				->andWhere('isdelete = 0');
				$q_res_pdates = $query_pdate->fetchArray();
				
				
				if(!empty($q_res_pdates)){
					foreach($q_res_pdates as $k=>$saved_dates_with_pdate){
						$mod = Doctrine::getTable('BwPerformanceRecord')->find($saved_dates_with_pdate['id']);
						$mod->isdelete = 1;
// 						$mod->qty = 0;
						$mod->save();
					}
				}
			}
			
		}
		
		
		if($q_res)
		{
			foreach($q_res as $k_res => $v_res)
			{
				$mod = Doctrine::getTable('BwPerformanceRecord')->find($v_res['id']);
				$mod->isdelete = 1;
				$mod->save();
			}
		}

	}
	

}
?>
