<?php

	Doctrine_Manager::getInstance()->bindComponent('MembersSepaSettings', 'SYSDAT');
	
	
	class MembersSepaSettings extends BaseMembersSepaSettings {

	
		/*
		 * if no results return false
		 * if $memberid is string it will return array
		 * if $memberid is array it will return multidimensional-array of memberid
		 */
		public function get_member_settings($memberid = false , $clientid = false, $limit = false)
		{
			$is_array = true;
			if( !is_array($memberid) || empty($memberid) ){
				$is_array = false;
				$memberid = (empty($memberid)) ? array('0') : array($memberid);
			}
			
			$query = Doctrine_Query::create()
				->select("*")
				->from('MembersSepaSettings')
				->whereIn("memberid" , $memberid)
				->andWhere('clientid = ?' , $clientid)
				->andWhere('isdelete = "0"');
			if ($limit !== false){
				$query->limit((int)$limit);
			}
				
			$query_res = $query->fetchArray();
			if (empty($query_res)){
				$result = false;
			}
			elseif ($is_array){
				foreach ($query_res as $row){
					$result[ $row['memberid'] ] [ $row['id'] ] = $row;
				}	
			}else{
				$result = $query_res;
			}
			return $result;
			
		}

		
		public function reset_settings($clientid = 0 , $memberid = 0)
		{
			$q = Doctrine_Query::create()
			->update('MembersSepaSettings')
			->set('isdelete', '1')
			->where('clientid = ?', $clientid)
			->andWhere('memberid = ?', $memberid)
			->andWhere("isdelete = '0'")
			->execute();
			return;
		}
		
		public function set_sepa_settings($clientid = 0 , $memberid = 0, $post )
		{
			//set isdelete for all others
			self::reset_settings($clientid, $memberid);
			
			switch ( $post['sepa_howoften'] ){
				case "monthly":
					$post['sepa_when_monthly'] = (int)$post['sepa_when_monthly'] > 30 ? 30 : (int)$post['sepa_when_monthly'];
					$post['sepa_when_monthly'] = (int)$post['sepa_when_monthly'] < 1 ? 1 : (int)$post['sepa_when_monthly'];
					$save_new = array();
					foreach( $post['sepa_month'] as $k=>$v ){
										
						foreach($post['sepa_month_amount'][$k] as $mid=>$val ){
							$save =  array();
							$save['memberid'] = $memberid;
							$save['clientid'] = $clientid;
							$save['member2membershipsid'] = $mid;
							$save['howoften'] = 'monthly';
							$save['when_day'] =  (int)$post['sepa_when_monthly'] ;
							$save['when_month'] =  (int)$k;
							$save['amount'] =  $val;
							$save['isdelete'] =  '0';							
							$save_new[] = $save;
						}
					}
					if (!empty($save_new)){
						$collection = new Doctrine_Collection('MembersSepaSettings');
						$collection->fromArray($save_new);
						$collection->save();
					}
					break;
					
				case "quarterly":
					
					$post['sepa_when_quarterly'] = (int)$post['sepa_when_quarterly'] > 90 ? 90 : (int)$post['sepa_when_quarterly'];
					$post['sepa_when_quarterly'] = (int)$post['sepa_when_quarterly'] < 1 ? 1 : (int)$post['sepa_when_quarterly'];
					$save_new = array();
					foreach($post['sepa_quarter'] as $k=>$v){
						foreach($post['sepa_quarter_amount'][$k] as $mid=>$val  ){
							$save =  array();
							$save['memberid'] = $memberid;
							$save['clientid'] = $clientid;	
							$save['member2membershipsid'] = $mid;
							$save['howoften'] = 'quarterly';
							$save['when_day'] =  (int)$post['sepa_when_quarterly'] ;
							$save['when_month'] =  (int)$k;
							$save['amount'] =  $val;
							$save['isdelete'] =  '0';
							$save_new[] = $save;
						}
						
					}	
					if (!empty($save_new)){
						$collection = new Doctrine_Collection('MembersSepaSettings');
						$collection->fromArray($save_new);
						$collection->save();
					}
					
					break;
					
				case "annually":
					//print_r($post);die();
					if (empty($post['sepa_when_annually_hidden'])){
						$post['sepa_when_annually_hidden'] = date("Y-m-d");
						break;
					}
					
					$date = DateTime::createFromFormat("Y-m-d",   $post['sepa_when_annually_hidden']);
					
					$save =  array();
					$save['memberid'] = $memberid;
					$save['clientid'] = $clientid;
					$save['member2membershipsid'] = 0;
					$save['howoften'] = 'annually';
					$save['when_day'] =  $date->format('j') ;
					$save['when_month'] =  $date->format('n');
					$save['amount'] =  0;
					$save['isdelete'] =  0;
					$save_new[] = $save;
					
					if (!empty($save_new)){
						$collection = new Doctrine_Collection('MembersSepaSettings');
						$collection->fromArray($save_new);
						$collection->save();
					}
					break;
			}
		
		}
		
		//function used by cron controler daily
		public function set_autogenerate_memeber_invoices( $clientid = 0 )
		{

// 			$members_invoices = new MembersInvoices();
// 			$members_invoices_items = new MembersInvoiceItems();
// 			$clientid = $this->clientid;
				
			// get client settings.
			$client_data_array = Client :: getClientDataByid( $clientid );
			$client_data = $client_data_array[0];
			$billing_method = $client_data['membership_billing_method'];
				
			// get all invoices of this client
			$invoices_array = MembersInvoices :: get_invoices_of_client( $clientid );
			foreach($invoices_array as $k => $invoice){
				$invoice_data[$invoice['member']][$invoice['membership_data']][$invoice['id']]['start'] = strtotime($invoice['invoice_start']);
				$invoice_data[$invoice['member']][$invoice['membership_data']][$invoice['id']]['end'] = strtotime($invoice['invoice_end']);
				$invoice_data[$invoice['member']][$invoice['membership_data']]['alldetails'] = $invoice;
				$invoice_data_s[$invoice['member']][$invoice['id']]['start'] = $invoice['invoice_start'];
				$invoice_data_s[$invoice['member']][$invoice['id']]['end'] = $invoice['invoice_end'];
				$members2invoices[] = $invoice['member'];
			}
			if(empty($members2invoices)){
				$members2invoices[] = "9999999999";
			}
			$members2invoices = array_unique($members2invoices);
		
			//get all client members
			$client_members = Member::get_client_members($clientid, 0);
				
			foreach($client_members as $mk =>$member_value){
					
				if($member_value['inactive'] == "1" &&  $member_value['inactive_from'] == "0000-00-00"){
					$fully_inactiv[] = $member_value['id'];
				} else {
					$member_ids[] = $member_value['id'];
				}
					
					
				$inactive_details[$member_value['id']]['inactive'] = $member_value['inactive'];
				$inactive_details[$member_value['id']]['date'] = $member_value['inactive_from'];
					
					
			}
		
			if(empty($member_ids)){
				$member_ids[] = "9999999999";
			}
		
			// get all membership data.
			$membership2members = Member2Memberships :: get_memberships_history($clientid, $member_ids);
			foreach($membership2members as $val){
				$new_membership2members[ $val['id'] ] = $val;
			}
			$membership2members = $new_membership2members;
		
			$member_id_arr = array_column($membership2members, 'member');
			$MembersSepaSettings = self :: get_member_settings($member_id_arr , $clientid);
		
			// if cronjob misfired we must manualy trigger this... so add a manual-param
				
			foreach($MembersSepaSettings as $memberid => $settings){ 
// 				die(print_r($MembersSepaSettings));
				foreach($settings as $id => $val){
						
					//monthly
					if ($val['howoften'] == 'monthly'){
						$issue_invoice_date =  date('Y-m-d', strtotime("-7 days", strtotime(date("Y")."-". sprintf('%02d', $val['when_month']) ."-" . sprintf('%02d', $val['when_day']))));
		
						// if cronjob misfired we must manualy trigger this... so add a manual-param
						if (strtotime($issue_invoice_date) == strtotime("Today")){
								
							$invoice_month_date_start =  strtotime( date("Y") . "-" . sprintf('%02d', $val['when_month']) . "-01" . " 00:00:00" );
								
							$month_start = DateTime::createFromFormat("Y-m-d H:i:s",  date("Y-m-d H:i:s", $invoice_month_date_start));
							$month_end = DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d H:i:s", strtotime($month_start->format( 'Y-m-t' ))));
								
							$new_invoice = true;
							//verify if member was allready invoiced for this interval
							foreach($invoice_data[ $val['memberid'] ] [ $val['member2membershipsid'] ] as $inv_id => $inv_val){
		
								if (	isset($inv_val['start'], $inv_val['end'])
										&& ($inv_val['start']<=$month_start->getTimestamp() && $inv_val['end'] >= $month_end->getTimestamp())
										)
								{
									//allready invoiced
									$new_invoice = false;
										
								}
		
							}
								
							if ($new_invoice){
								//generate new invoice draft
								$params = array();
		
								$params['members'] = array($memberid);
								$params['membership_data'] = $val['member2membershipsid'] ;
								$params['get_pdf'] = 0;
								$params['only_pdf'] = 0;
								$params['stornopdf'] = 0;
								$params['stornoid'] = 0;
								$params['invoices'] = array("0");
								$params['interval']['start'] = $month_start->format("Y-m-d H:i:s");
								$params['interval']['end'] = $month_end->format("Y-m-d H:i:s");
								$params['selected_period'] = array();
								$params['selected_period'][$memberid] =  array(
										'membership' => $membership2members[$val['member2membershipsid']]['membership'],
										'membership_start' => $membership2members[$val['member2membershipsid']]['start_date'] ,
										'membership_end' => $membership2members[$val['member2membershipsid']]['end_date'] ,
										'membership_price' => $val['amount'],
										'start' => $month_start->format("Y-m-d H:i:s"),
										'end' => $month_end->format("Y-m-d H:i:s"),
								);
								
								self::membersinvoice($params , $clientid, $client_members[$memberid]);
							}
						}
					}
						
					//quarterly
					elseif ($val['howoften'] == 'quarterly'){
		
						if  (self::CurrentQuarter() != $val['when_month']){
							continue;
						}
						$current_q = Pms_CommonData::get_dates_of_quarter ( 'current', null, "d.m.Y" );
		
						$issue_invoice_date =  date('Y-m-d', strtotime( ($val['when_day']-7)." days", strtotime( $current_q['start']) ) );
// 						print_r($val);
// 						print_r($client_members[$memberid]);
// 						die($issue_invoice_date);
						if (strtotime($issue_invoice_date) == strtotime("Today")){
		
							$params = array();
								
							$params['members'] = array($memberid);
							$params['membership_data'] = $val['member2membershipsid'] ;
							$params['get_pdf'] = 0;
							$params['only_pdf'] = 0;
							$params['stornopdf'] = 0;
							$params['stornoid'] = 0;
							$params['invoices'] = array("0");
							$params['interval']['start'] = $current_q['start'];
							$params['interval']['end'] = $current_q['end'];
							$params['selected_period'] = array();
							$params['selected_period'][$memberid] =  array(
									'membership' => $membership2members[$val['member2membershipsid']]['membership'],
									'membership_start' => $membership2members[$val['member2membershipsid']]['start_date'] ,
									'membership_end' => $membership2members[$val['member2membershipsid']]['end_date'] ,
									'membership_price' => $val['amount'],
									'start' => $current_q['start'],
									'end' => $current_q['end'],
							);
// 							print_r($val);
// 							print_r($client_members[$memberid]);
// 							die();
							self::membersinvoice($params , $clientid, $client_members[$memberid]);
						}
					}
					//annually
					elseif ($val['howoften'] == 'annually'){
		
						$issue_invoice_date =  date('Y-m-d', strtotime("-7 days", strtotime( date("Y") . "-" . sprintf('%02d', $val['when_month']) . "-" . sprintf('%02d', $val['when_day']) . " 00:00:00" ) ) );
		
						if (strtotime($issue_invoice_date) == strtotime("Today")){
								
							// 							die($issue_invoice_date);
							$membership_history_one_memeber = array();
							$member_details = $client_members[$memberid] ;
							if(!empty($membership2members)){
								foreach($membership2members as $k=>$md){
									if($md['member'] ==  $memberid){
										$membership_history_one_memeber[$k]=$md;
									}
								}
							}
								
							$membership_intervals = self::membership_intervals($membership_history_one_memeber, $billing_method, $member_details , $invoice_data , $clientid);
							// 							print_r($membership_intervals);
							// 							print_r($membership_history_one_memeber);
								
							foreach($membership_intervals['invoice_period'] as $membership_id => $period ){
								foreach ($period as $pval){
									if($pval['invoiced'] != '0'){
										//allready invoiced
										continue;
									}
										
									$params = array();
										
									$params['members'] = array($memberid);
									$params['membership_data'] = $membership_id ;
										
									$params['get_pdf'] = 0;
									$params['only_pdf'] = 0;
									$params['stornopdf'] = 0;
									$params['stornoid'] = 0;
									$params['invoices'] = array("0");
										
									$params['interval']['start'] = $pval['start'];
									$params['interval']['end'] = $pval['end'];
										
									$params['selected_period'] = array();
									$params['selected_period'][$memberid] =  array(
											'membership' => $membership_intervals['membership_history'][$membership_id]['membership'],
											'membership_start' => $pval['start'] ,
											'membership_end' => $pval['end'] ,
											'membership_price' => $membership_intervals['membership_history'][$membership_id]['price'],
											'start' => $pval['start'],
											'end' => $pval['end'],
									);
									self::membersinvoice($params , $clientid, $client_members[$memberid]);
								}
							}
						}
					}//elseif ($val['howoften'] == 'annually')
						
				}
		
			}
		
		}
		
		
		
		private function CurrentQuarter(){
			$n = date('n');
			if($n < 4){
				return "1";
			} elseif($n > 3 && $n <7){
				return "2";
			} elseif($n >6 && $n < 10){
				return "3";
			} elseif($n >9){
				return "4";
			}
		}
		
		private function membership_intervals($membership_history_array , $membership_billing_method = "membership", $memberarray, $invoice_data, $clientid)
		{
			if(!empty($membership_history_array)){
					
				$member_id = $memberarray['id'];
				//$clientid = $this->clientid;
		
				foreach($membership_history_array as $k=>$md){
						
					//IMPORTANT
					// - CHANGE MEMBERSHIP  END DATE IF INACTIVE DATE IS SET
					if($memberarray['inactive'] == "1" && $memberarray['inactive_from'] !=  "0000-00-00"){
						$inactive_date = date("Y-m-d H:i:s", strtotime($memberarray['inactive_from']));
						if( $md['end_date'] != "0000-00-00 00:00:00"){
							if( strtotime($md['end_date'])  >  strtotime($inactive_date)){
								$membership_history_array[$k]['end_date'] =$inactive_date;
							}
						} else {
								
							if( strtotime($md['start_date']) < strtotime($inactive_date) ){
								$membership_history_array[$k]['end_date'] = $inactive_date;
							} else {
								unset($membership_history_array[$k]);
							}
						}
					}
				}
				foreach($membership_history_array as $k=>$md){
						
					$membership_history[$md['id']] = $md;
					if($membership_billing_method == "membership"){
						// membership
						if($md['start_date'] != "0000-00-00 00:00:00"){
							$membership_history[$md['id']]['start'] = date( 'd.m.Y',strtotime($md['start_date']));
						} else{
							$membership_history[$md['id']]['start'] = "";
						}
							
							
						if($md['end_date'] != "0000-00-00 00:00:00"){
							$membership_history[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
							$membership_history_cal[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
						} else{
							$membership_history[$md['id']]['end'] = "";
								
							if(strtotime($md['start_date']) >= strtotime(date("d.m.Y",time()))){
								$membership_history_cal[$md['id']]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($md['start_date']))));
							} else{
								$membership_history_cal[$md['id']]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", time())));
							}
						}
							
						// break membership in 12 months interval
						$membership_intervals[$md['id']] = Pms_CommonData::generateDateRangeArray(  $membership_history[$md['id']]['start'] ,$membership_history_cal[$md['id']]['end'],"+1 year");
							
						foreach($membership_intervals[$md['id']] as $k => $start_dates){
							if(count($membership_intervals[$md['id']]) >= 1 && $membership_history[$md['id']]['end'] == "") {
									
								if( date( 'Y',strtotime($start_dates)) <= date("Y",time()) ){
									$msp_intervals[$md['id']][$k]['start'] = $start_dates;
									if($membership_intervals[$md['id']][$k+1]){
										$msp_intervals[$md['id']][$k]['end'] = date("d.m.Y",strtotime("-1 day", strtotime($membership_intervals[$md['id']][$k+1])));
									} else{
										$msp_intervals[$md['id']][$k]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($membership_intervals[$md['id']][$k]))));
									}
								}
									
							} else{
									
								$msp_intervals[$md['id']][$k]['start'] = $start_dates;
								if($membership_intervals[$md['id']][$k+1]){
									$msp_intervals[$md['id']][$k]['end'] = date("d.m.Y",strtotime("-1 day", strtotime($membership_intervals[$md['id']][$k+1])));
								} else{
									//   $msp_intervals[$md['id']][$k]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($start_dates))));
									$msp_intervals[$md['id']][$k]['end'] = date("d.m.Y", strtotime($membership_history[$md['id']]['end']));
								}
							}
						}
							
						$m=0;
						foreach($msp_intervals[$md['id']] as $int_k => $int_dates){
							$invoice_period[$md['id']][$m]['start'] =  $int_dates['start'];
							$invoice_period[$md['id']][$m]['end'] =  $int_dates['end'];
								
							$invoice_period[$md['id']][$m]['invoiced'] = 0;
							if($invoice_data[$member_id][$md['id']]){
								foreach($invoice_data[$member_id][$md['id']] as $inv_id =>$inv_dates){
									if(strtotime($invoice_period[$md['id']][$m]['start']) == $inv_dates['start']  && strtotime( $invoice_period[$md['id']][$m]['end']) == $inv_dates['end']  ){
										$invoice_period[$md['id']][$m]['invoiced'] += 1;
									} else
									{
										$invoice_period[$md['id']][$m]['invoiced'] += 0;
									}
								}
							}
							$m++;
						}
					} else { // CALENDAR YEAR METHOD
							
						// Membership interval
						if($md['start_date'] != "0000-00-00 00:00:00"){
							$membership_history[$md['id']]['start'] = date( 'd.m.Y',strtotime($md['start_date']));
						} else{
							$membership_history[$md['id']]['start'] = "";
						}
							
						if($md['end_date'] != "0000-00-00 00:00:00"){
							$membership_history[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
							$membership_history_cal[$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
						} else{
							$membership_history[$md['id']]['end'] = "";
		
							if( date( 'Y',strtotime($md['start_date'])) > date("Y",time()) ){
								$membership_history_cal[$md['id']]['end'] = date("d.m.Y",mktime(0,0,0,12,31,date( 'Y',strtotime($md['start_date']))));;
							} else{
								$membership_history_cal[$md['id']]['end'] = date("d.m.Y",mktime(0,0,0,12,31,date("Y",time())));;
							}
						}
							
						// break membership period in calendar year intervals
						$start_year = date('Y',strtotime($membership_history[$md['id']]['start']));
						$end_year  = date('Y',strtotime($membership_history_cal[$md['id']]['end']));
						$i= 0;
						$interval[$md['id']] = array();
							
						for ($i = $start_year; $i <= $end_year; $i++ ){
							if($i ==  $start_year &&  $start_year != $end_year){
								$interval[$md['id']][$i]['start'] = $membership_history[$md['id']]['start'];
								$interval[$md['id']][$i]['end'] =  date("d.m.Y",mktime(0,0,0,12,31,$i));
							} else if($i ==  $end_year && $start_year  != $end_year ){
								$interval[$md['id']][$i]['start'] = date("d.m.Y",mktime(0,0,0,01,01,$i));
								$interval[$md['id']][$i]['end'] =  $membership_history_cal[$md['id']]['end'];
							} else if($start_year == $end_year ){
								$interval[$md['id']][$i]['start'] = $membership_history[$md['id']]['start'];
								$interval[$md['id']][$i]['end'] =  $membership_history_cal[$md['id']]['end'];
							} else {
								$interval[$md['id']][$i]['start'] = date("d.m.Y",mktime(0,0,0,01,01,$i));
								$interval[$md['id']][$i]['end'] =  date("d.m.Y",mktime(0,0,0,12,31,$i));
							}
						}
							
						$m=0;
						foreach($interval[$md['id']] as $int_k => $int_dates){
							$invoice_period[$md['id']][$m]['start'] =  $int_dates['start'];
							$invoice_period[$md['id']][$m]['end'] =  $int_dates['end'];
								
							$invoice_period[$md['id']][$m]['invoiced'] = 0;
								
							if($invoice_data[$member_id][$md['id']]){
								foreach($invoice_data[$member_id][$md['id']] as $inv_id =>$inv_dates){
									if(strtotime($invoice_period[$md['id']][$m]['start']) == $inv_dates['start']  && strtotime( $invoice_period[$md['id']][$m]['end']) == $inv_dates['end']  ){
										$invoice_period[$md['id']][$m]['invoiced'] += 1;
									}
									else
									{
										$invoice_period[$md['id']][$m]['invoiced'] += 0;
									}
								}
							}
							$m++;
						}
					}
		
		
		
					$p_list = new PriceList();
						
					if($md['end_date'] == "0000-00-00 00:00:00") {
						if(strtotime( date('Y-m-d',strtotime($md['start_date']))) <= strtotime(date('Y-m-d'))){
							$md['end_date'] = date('Y-m-d H:i:s');
						} else{
							$md['end_date'] = $md['start_date'];
						}
					}
		
					$master_price_list[$md['id']] = $p_list->get_client_list_period(date('Y-m-d', strtotime($md['start_date'])), date('Y-m-d', strtotime($md['end_date'])));
						
					$current_pricelist = $master_price_list[$md['id']][0];
					if($current_pricelist)
					{
						$price_memberships_model = new PriceMemberships();
						$price_memberships = $price_memberships_model->get_prices($current_pricelist['id'], $clientid);
					}
		
		
					if($md['membership_price'] != "0.00"){
						$membership_history[$md['id']]['price'] = $md['membership_price'];
							
					} else {
						// get membership price from price list
						$membership_history[$md['id']]['price'] = $price_memberships[$md['membership']]['price'];
					}
					$membership_history[$md['id']]['price_from_list'] = $price_memberships[$md['membership']]['price'];
		
		
				}
			}
			return array('invoice_period'=>$invoice_period , 'membership_history'=> $membership_history);
		}
		
		
		//actual function which is generating blank member invoice
		// !! copy from invoicenewController
		private function membersinvoice($params , $clientid , $member)
		{
// 			print_r($params);
// 			print_r($clientid);
// 			print_r($member);die();
			$used_members = $params['members'];
			$master_data['client']['id'] = $clientid;
			$p_list = new PriceList();
			
			if($params['only_pdf'] == '0')
			{
				foreach($used_members as $k_usr => $v_usr)
				{
					$member_details[$v_usr] = $member;
					
					$recipient = array();
			
					// get client memberships data
					$membership_data = Memberships::membership_details($clientid,$params['selected_period'][$v_usr]['membership']);
						
					$curent_period[$v_usr]['start'] = $params['selected_period'][$v_usr]['start'];
					$curent_period[$v_usr]['end'] = $params['selected_period'][$v_usr]['end'];
						
					$membership_period[$v_usr]['start'] = $params['selected_period'][$v_usr]['membership_start'];
					$membership_period[$v_usr]['end'] = $params['selected_period'][$v_usr]['membership_end'];
			
					$curent_period_days[$v_usr] = $params['selected_period'][$v_usr]['days'];
			
					$master_data['members'][$v_usr]['invoice_data']['member'] = $v_usr;
					$master_data['members'][$v_usr]['invoice_data']['period'] = $curent_period[$v_usr];
					//ispc 1842
					$master_data['members'][$v_usr]['invoice_data']['membership_period'] = $membership_period[$v_usr];
					//$master_data['members'][$v_usr]['invoice_data']['membership_period'] = $curent_period[$v_usr];
			
// 					$members = new Member();
// 					$member_details = $members->getMemberDetails($v_usr);
			
					$recipient_title = trim(rtrim($member_details[$v_usr]['title']));
					
					$recipient_company = "";
					if($member_details[$v_usr]['type'] == "company" && !empty($member_details[$v_usr]['member_company'])){
					    $recipient_company = trim(rtrim($member_details[$v_usr]['member_company']));
					}
					$recipient_name = "";
					if(!empty($member_details[$v_usr]['first_name']) || !empty($member_details[$v_usr]['last_name'])){
    					$recipient_name = trim(rtrim($member_details[$v_usr]['first_name'])) . ' ' . trim(rtrim($member_details[$v_usr]['last_name']));
					}
					$recipient_street = trim(rtrim($member_details[$v_usr]['street1']));
					$recipient_zip = trim(rtrim($member_details[$v_usr]['zip']));
					$recipient_city = trim(rtrim($member_details[$v_usr]['city']));
			

					if($recipient_company)
					{
					    $recipient[$v_usr][] = $recipient_company;
					}
					
					if($recipient_name)
					{
						$recipient[$v_usr][] = $recipient_name;
					}
			
					if($recipient_street)
					{
						$recipient[$v_usr][] = $recipient_street;
					}
			
			
					if($recipient_zip || $recipient_city)
					{
						$recipient_blocks = array();
			
						if($recipient_zip)
						{
							$recipient_blocks[] = $recipient_zip;
						}
			
						if($recipient_city)
						{
							$recipient_blocks[] = $recipient_city;
						}
			
						$recipient[$v_usr][] = implode(" ", $recipient_blocks);
					}
			
					// member_adresse(recipient) - never changes
					$master_data['recipient'][$v_usr] = implode("<br />", $recipient[$v_usr]);
			
					if(!array_key_exists($v_usr, $master_price_list))
					{
						//print_R($curent_period);die();
						// 						$master_price_list[$v_usr] = $p_list->get_period_price_list(date('Y-m-d', strtotime($curent_period[$v_usr]['start'])), date('Y-m-d', strtotime($curent_period[$v_usr]['end'])));
						$master_price_list[$v_usr] = $p_list->get_client_list_period(date('Y-m-d', strtotime($curent_period[$v_usr]['start'])), date('Y-m-d', strtotime($curent_period[$v_usr]['end'])));
					}
			
					// 					$current_pricelist = end($master_price_list[$v_usr]);
					$current_pricelist = $master_price_list[$v_usr][0];
						
					if($current_pricelist)
					{
						$price_memberships_model = new PriceMemberships();
						$price_memberships = $price_memberships_model->get_prices($current_pricelist['id'], $clientid);
					}
						
						
						
					$item_details['description'] = $price_memberships[$membership_data['id']]['membership'];
					$item_details['shortcut'] = $price_memberships[$membership_data['id']]['shortcut'];
					$item_details['qty'] = "1";
					if($params['selected_period'][$v_usr]['membership_price'] !="0.00"){
						$item_details['price'] = $params['selected_period'][$v_usr]['membership_price'];
					} else{
						$item_details['price'] = $price_memberships[$membership_data['id']]['price'];
					}
					$item_details['custom'] = "0";
			
					$master_data['invoices'][$v_usr]['items'][] = $item_details;
						
						
					$master_data['invoiced_month'] = date('Y-m-d H:i:s', strtotime($params['selected_period'][$v_usr]['start']));
						
					$master_data['membership_data'] = $params['membership_data'];
			
					$master_data['invoices'][$v_usr]['pricelist'] = $current_pricelist;
				}
				$members_invoices_form = new Application_Form_MembersInvoices();
				$inserted_invoices = $members_invoices_form->insert_invoice($master_data);
			}
		
		}
		
	}

?>