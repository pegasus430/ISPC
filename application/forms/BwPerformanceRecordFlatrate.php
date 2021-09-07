<?php
require_once("Pms/Form.php");
class Application_Form_BwPerformanceRecordFlatrate extends Pms_Form
{
	public function insert_values ( $ipid, $post, $current_period_days, $bw_shortcuts )
	{
		ksort($post);

// 		print_r($post);
// print_r($current_period_days); 
// exit;
		$pay_days_array = array();
		foreach($post as $date=>$shortcut_data){
			$formatted_date = date('Y-m-d H:i:s', strtotime($date));
			foreach($shortcut_data as $shortcut => $post_values){
				if($shortcut == "37b1"){
					if($post_values['pay_days'] == "1"){
						$flat_rate_days[]= PatientMaster::getDaysInBetween(date("Y-m-d", strtotime($date)), date('Y-m-d', strtotime('+6 days', strtotime($date))));
						$pay_days_array[]= $date;
					}
				}
			}
		}
// print_r($flat_rate_days); 
// exit;
// 		print_r($flat_rate_days); exit;
		if(!empty($flat_rate_days)){
			foreach($flat_rate_days as $k=>$fl_period){
				foreach($fl_period as $ks=>$fday){
					$post[$fday]['37b1']['qty'] = "1";
				}
			}
		}
		
// 		print_r($post); exit;
		foreach($post as $date=>$shortcut_data){
			$formatted_date = date('Y-m-d H:i:s', strtotime($date));
			foreach($shortcut_data as $shortcut => $post_values){
// 				if($shortcut == "37b1"){
// 					if($post_values['pay_days'] == "1"){
// 						$pay_days = "1"; //start flatrate
// 						$end_group_date[$shortcut][$date] = date('Y-m-d', strtotime('+6 days', strtotime($date)));
// 						print_r($end_group_date);
// 						print_r("\n");
// 						$qty = "1";			
// 					} else {
// 						$pay_days = "0";
// 						if( strlen($end_group_date[$shortcut][$date]) > 0 && strtotime($date) <= strtotime($end_group_date[$shortcut][$date])){
// 							$qty = "1";			
// 						}
// 					}
// 				}else{
// 					$pay_days = "0";
// 					$qty = $post_values['qty'];
// 				}
				$pay_days = $post_values['pay_days'];
				$qty = $post_values['qty'];

				$final_data[] = array(
						'ipid' => $ipid,
						'shortcut' => $shortcut,
						'qty' => $qty,
						'pay_days' => $pay_days,
						'date' => $formatted_date,
				);
				
			}
		}
		//insert current month data
		$brasapv_collection = new Doctrine_Collection('BwPerformanceRecordFlatrate');
		$brasapv_collection->fromArray($final_data);
		$brasapv_collection->save();
	}

	public function reset( $ipid, $start_date )
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
		->from('BwPerformanceRecordFlatrate')
		->whereIn('ipid', $ipid)
		->andWhere('isdelete = 0')
		->andWhere('MONTH(date) = MONTH("' . $start_date . '")')
		->andWhere('YEAR(date) = YEAR("' . $start_date . '")');
		$q_res = $query->fetchArray();
		
		
		// get all flatrate started in this month
		/// if it starts in this month, and it continueas in the next month the gets removed;
		
		foreach($q_res as $k=>$values){
			$date = date('Y-m-d',strtotime($values['date']));
			if($values['pay_days'] == "1"){
				$flat_rate_days[]= PatientMaster::getDaysInBetween(date("Y-m-d", strtotime($date)), date('Y-m-d', strtotime('+6 days', strtotime($date))));
				$pay_days_array[]= $date;
			}			
		}
		
		print_r($q_res); 
		print_r($flat_rate_days); exit;
		
		if($q_res)
		{
			foreach($q_res as $k_res => $v_res)
			{
				$mod = Doctrine::getTable('BwPerformanceRecordFlatrate')->find($v_res['id']);
				$mod->isdelete = 1;
				$mod->save();
			}
		}

	}
	
	// remove flatrate function
	

}
?>
