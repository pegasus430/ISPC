<?php

Doctrine_Manager::getInstance()->bindComponent('MembersInvoices', 'SYSDAT');

class MembersInvoices extends BaseMembersInvoices 
{
	    /**
	     * @param unknown $invoice
	     * @param boolean $status
	     * @param boolean $remove_archived
	     * @return unknown|boolean
	     * TODO-2970 ISPC: Member invoices printing in archiv Ancuta 04.03.2020 
	     * - add new param $remove_archived 
	     */
		public function get_members_invoices($invoice, $status = false,$remove_archived = true) 
		{
			$shinvoice_items = new MembersInvoiceItems();

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('MembersInvoices')
				->where("id='" . $invoice . "'")
				->andWhere('isdelete = "0"');

			if($remove_archived)
			{//TODO-2970
			    $invoices->andWhere('isarchived = "0"');
			}
			
			if($status)
			{
				$invoices->andWhere('status = "' . $status . '"');
			}
			$invoices->limit('1');

			$invoices_res = $invoices->fetchArray();
			if($invoices_res)
			{
				//get all invoice items
				$invoice_items = $shinvoice_items->getInvoicesItems($invoices_res[0]['id']);

				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$invoice_details = $v_invoice_res;

					if($invoice_items)
					{
						$invoice_details['items'] = $invoice_items[$v_invoice_res['id']];
					}
				}


				return $invoice_details;
			}
			else
			{
				return false;
			}
		}

		public function get_invoice($invoiceid)
		{
			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('MembersInvoices')
				->andWhere('id = "' . $invoiceid . '"')
				->andWhere('isdelete = 0')
				->andWhere('isarchived = 0');
			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return $invoices_res;
			}
			else
			{
				return $invoices;
			}
		}

		public function get_highest_invoice_number($clientid, $prefix = false, $all = false)
		{
			$invoice_number = Doctrine_Query::create()
				->select("*")
				->from('MembersInvoices')
				->where("client='" . $clientid . "'")
				->andWhere('isdelete = 0')
				->orderBy('invoice_number DESC')
				->limit('1');
			if($prefix)
			{
				$invoice_number->andWhere('prefix = "' . $prefix . '"');
			}
			else if($all === false)
			{
				$invoice_number->andWhere('prefix = ""');
			}

			$invoice_number_data = $invoice_number->fetchArray();

			if($invoice_number_data)
			{
				return $invoice_number_data[0];
			}
			else
			{
				return false;
			}
		}

		public function get_period_sh_invoices($clientid, $period)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('MembersInvoices')
				->where("client='" . $clientid . "'")
				->andWhere('isdelete = 0')
				->andWhere('isarchived = 0');

			if($period)
			{
				$invoices->andWhere('create_date BETWEEN "' . date('Y-m-d H:i:s', strtotime($period['start'])) . '" AND "' . date('Y-m-d H:i:s', strtotime($period['end'])) . '"');
			}


			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return $invoices_res;
			}
			else
			{
				return false;
			}
		}

		public function create_storno_invoice($invoiceid , $request = array())
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$found_invoice = false;
			
			$find_invoice = Doctrine::getTable('MembersInvoices')->findOneByIdAndClient($invoiceid , $logininfo->clientid);

			if($find_invoice)
			{
				$found_invoice = $find_invoice->toArray();
				$has_storno = self::has_storno($invoiceid);
			}

			if($found_invoice && !$has_storno)
			{
				$inv = new MembersInvoices();
				$inv->member = $found_invoice['member'];
				$inv->invoice_start = $found_invoice['invoice_start'];
				$inv->invoice_end = $found_invoice['invoice_end'];
				$inv->membership_start = $found_invoice['membership_start'];
				$inv->membership_end = $found_invoice['membership_end'];
				$inv->membership_data = $found_invoice['membership_data'];
				$inv->invoiced_month = $found_invoice['invoiced_month'];
				$inv->client = $found_invoice['client'];
				
				//ISPC-2532 Carmen 14.02.2020
				$invoice_number = $this->get_next_invoice_number($found_invoice['client']);
				if($found_invoice['client'] != 0)
				{
					$inv->prefix = $invoice_number['prefix'];
					$inv->invoice_number = $invoice_number['invoicenumber'];
				}
				else
				{
					$inv->prefix = $found_invoice['prefix'];
					$inv->invoice_number = $found_invoice['invoice_number'];
				}
				//--
				
				$inv->invoice_total = $found_invoice['invoice_total'];
				$inv->status = $found_invoice['status'];
				$inv->recipient = $found_invoice['recipient'];
				$inv->comment = $found_invoice['comment'];
				$inv->isdelete = '0';
				$inv->isarchived = '0';
				$inv->record_id = $invoiceid;
				$inv->storno = '1';
				$inv->storno_comment = (! empty($request['storno_comment'])) ? urldecode($request['storno_comment']) : '';
				
				//$inv->completed_date = $found_invoice['completed_date'];
				$inv->completed_date = date('Y-m-d H:i:s', time());        //ISPC-2532 Lore 11.11.2020
				$inv->save();
			}
		}

		public function del_storno_invoice($invoiceid)
		{
			$del_storno_invoice = Doctrine::getTable('MembersInvoices')->findOneById($invoiceid);
			$del_storno_invoice->isdelete = '1';
			$del_storno_invoice->save();
		}

		public function get_period_patients_sapv_invoices($ipid, $clientid, $period)
		{
			if(!is_array($ipid))
			{
				$ipids = array($ipid);
			}
			else
			{
				$ipids = $ipid;
			}

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('MembersInvoices')
				->whereIn('ipid', $ipids)
				->andWhere("client='" . $clientid . "'")
				->andWhere('isdelete = 0')
				->andWhere('isarchived = 0');

			if($period)
			{
				$invoices->andWhere(' DATE(invoice_start) BETWEEN DATE("' . date('Y-m-d', strtotime($period['start'])) . '") AND DATE("' . date('Y-m-d', strtotime($period['end'])) . '")  OR  DATE(invoice_end) BETWEEN DATE("' . date('Y-m-d', strtotime($period['start'])) . '") AND DATE("' . date('Y-m-d', strtotime($period['end'])) . '")');
			}
			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return $invoices_res;
			}
			else
			{
				return false;
			}
		}

		public function get_next_invoice_number($clientid, $temp = false)
		{
			$client = new Client();
			$invoice_settings = new InvoiceSettings();
			$invoices = new MembersInvoices();
			$client_data = $client->getClientDataByid($clientid);

			$collective = '0';
			if($client_data[0]['invoice_number_type'] == '1')
			{
				$collective = '1';
			}
			$required_shortcuts = array('members_invoice');

			$invoice_settings_arr = $invoice_settings->getClientInvoiceSettings($clientid, $required_shortcuts, $collective);

			if($client_data[0]['invoice_number_type'] == '0') //individual type
			{
				//sgbv invoice
				$members_invoice_rnummer = $invoices->get_highest_invoice_number($clientid, $invoice_settings_arr['members_invoice']['invoice_prefix']);
				if($members_invoice_rnummer)
				{
					if($members_invoice_rnummer['invoice_number'] >= $invoice_settings_arr['members_invoice']['invoice_start'] && $members_invoice_rnummer['prefix'] == $invoice_settings_arr['members_invoice']['invoice_prefix'])
					{
						$prefix = $invoice_settings_arr['members_invoice']['invoice_prefix'];
						$i_number = $members_invoice_rnummer['invoice_number'];
						$i_number++;
					}
					else
					{
						$prefix = $invoice_settings_arr['members_invoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['members_invoice']['invoice_start'];
						if($invoice_settings_arr['members_invoice']['invoice_start'] == '0')
						{
							$i_number++;
						}
					}
				}
				else
				{
					if(strlen($invoice_settings_arr['members_invoice']['invoice_start']) > 0)
					{
						$prefix = $invoice_settings_arr['members_invoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['members_invoice']['invoice_start'];
						if($invoice_settings_arr['members_invoice']['invoice_start'] == '0')
						{
							$i_number++;
						}
					}
					else
					{
						$prefix = "";
						$i_number = '1000';
					}
				}
				$invoicenumber = $i_number;
			}
			else
			{
				//get all client invoices highest number if collective settings are applied
				$collective_highest_number = $invoice_settings->get_all_invoices_high_number($clientid);
				foreach($invoice_settings_arr as $k_inv_settigns => $v_inv_settings)
				{
					if(in_array($v_inv_settings['invoice_prefix'], $collective_highest_number['prefix']))
					{
						foreach($collective_highest_number['number'] as $k_coll_high => $v_coll_high)
						{
							if($collective_highest_number['prefix'][$k_coll_high] == $v_inv_settings['invoice_prefix'])
							{
								$coll_numbers[] = $v_coll_high;
							}
						}

						if(count($coll_numbers) > 0)
						{
							$max_collection_number = max($coll_numbers);

							if($max_collection_number > $v_inv_settings['invoice_start'])
							{
								$i_number[$k_inv_settigns] = $max_collection_number;
							}
							else
							{
								$i_number[$k_inv_settigns] = $v_inv_settings['invoice_start'];
							}

							$i_number[$k_inv_settigns] ++;
						}
					}
					else
					{

						$i_number[$k_inv_settigns] = $v_inv_settings['invoice_start'];
						if($v_inv_settings['invoice_start'] == '0')
						{
							$i_number[$k_inv_settigns] ++;
						}
					}
				}

				$final_invoice_number = max($i_number);

				$prefix = $client_data[0]['invoice_number_prefix'];
				$invoicenumber = $final_invoice_number;
			}

			if($temp === false)
			{
				$invoice_nr_arr['prefix'] = $prefix;
				$invoice_nr_arr['invoicenumber'] = $invoicenumber;
			}
			else
			{
				$invoice_nr_arr = $this->generate_temp_invoice_number($clientid);
			}

			return $invoice_nr_arr;
		}

		private function generate_temp_invoice_number($clientid)
		{
			$invoices = new MembersInvoices();
			$temp_prefix = 'TEMP_';
			$high_inv_nr = $invoices->get_highest_invoice_number($clientid, $temp_prefix);

			if($high_inv_nr)
			{
				$high_inv_nr['invoice_number'] ++;
				$inv_nr = $high_inv_nr['invoice_number'];
			}
			else
			{
				$inv_nr = '1';
			}

			$invoice_nr_arr['prefix'] = $temp_prefix;
			$invoice_nr_arr['invoicenumber'] = $inv_nr;

			return $invoice_nr_arr;
		}

		public function get_storned_invoices($clientid)
		{
			$storno_invoices = Doctrine_Query::create()
				->select("*")
				->from('MembersInvoices')
				->where("client='" . $clientid . "'")
				->andwhere('storno = "1"');
			$storno_invoices_res = $storno_invoices->fetchArray();

			if($storno_invoices_res)
			{
				$storned_invoices[] = '999999999999';
				foreach($storno_invoices_res as $k_storno => $v_storno)
				{
					$storned_invoices[] = $v_storno['record_id'];
				}

				return $storned_invoices;
			}
			else
			{
				return false;
			}
		}

		public function has_storno($invoiceid)
		{
			$invoice_storno = Doctrine_Query::create()
				->select("*")
				->from('MembersInvoices')
				->where("record_id='" . $invoiceid . "'")
				->andwhere('storno = "1"')
				->limit('1');
			$invoice_storno_res = $invoice_storno->fetchArray();

			if($invoice_storno_res)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function get_members_invoiced($users)
		{
			$ipids[] = '9999999999999';

			$invoiced_sapvs = Doctrine_Query::create()
				->select("*")
				->from('MembersInvoices')
				->whereIn("user", $users)
				->andwhere('storno = "0"')
				->andwhere('isdelete = "0"');
			$invoiced_sapvs_res = $invoiced_sapvs->fetchArray();

			if($invoiced_sapvs_res)
			{
				foreach($invoiced_sapvs_res as $k_res => $v_res)
				{
					$invoices_sapvs_ids['fall'][] = $v_res['user'] . '_' . date("Y_m", strtotime($v_res['invoiced_month']));
					$invoices_sapvs_ids['fall'] = array_values(array_unique($invoices_sapvs_ids['fall']));
				}
				return $invoices_sapvs_ids;
			}
			else
			{
				return false;
			}
		}

		/**
		 * 
		 * @param unknown $invoices_ids
		 * @param boolean $remove_archived
		 * @return unknown|boolean
		 * TODO-2970 ISPC: Member invoices printing in archiv Ancuta 04.03.2020 
	     * - add new param $remove_archived 
		 */
		public function get_invoices($invoices_ids,$remove_archived = true)
		{
			$shinvoice_items = new MembersInvoiceItems();
			$invoices_ids[] = '9999999999999999';
			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('MembersInvoices')
				->whereIn("id", $invoices_ids)
				->andWhere('isdelete = "0"');
				if($remove_archived){//TODO-2970
				    $invoices->andWhere('isarchived = "0"');
				}
			$invoices_res = $invoices->fetchArray();

			foreach($invoices_res as $k_inv => $v_inv)
			{
				if($v_inv['storno'] == "1")
				{
					$storned_ids[$v_inv['id']] = $v_inv['record_id'];
					$invoices_ids[] = $v_inv['record_id'];
				}
			}

			if($invoices_res)
			{
				//get all invoice items
				$invoice_items = $shinvoice_items->getInvoicesItems($invoices_ids);

				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$master_data['invoices_users'][] = $v_invoice_res['user'];

					$master_data['invoices_data'][$v_invoice_res['id']] = $v_invoice_res;
					if($invoice_items)
					{
						if(array_key_exists($v_invoice_res['id'], $storned_ids))
						{
							//make sure that storno invoices have inherited the items of storned invoice
							$master_data['invoices_data'][$v_invoice_res['id']]['items'] = $invoice_items[$storned_ids[$v_invoice_res['id']]];
						}
						else
						{
							$master_data['invoices_data'][$v_invoice_res['id']]['items'] = $invoice_items[$v_invoice_res['id']];
						}
					}
				}

				return $master_data;
			}
			else
			{
				return false;
			}
		}

		public function get_last_invoice_date($clientid, $member,$membership)
		{
		    
		    $invoices_mspd_q = Doctrine_Query::create()
		    ->select("invoice_end")
		    ->from('MembersInvoices')
		    ->where("client='" . $clientid . "'")
		    ->andWhere("member='" . $member . "'")
		    ->andWhere("membership_data='" .$membership . "'")
		    ->andWhere('isdelete = "0"  ')
		    ->orderBy('invoice_end DESC')
		    ->limit('1');
		    $invoices_mspd = $invoices_mspd_q->fetchArray();
		    
		    if($invoices_mspd)
		    {
		        $last_invoice_date = $invoices_mspd[0]['invoice_end'];
		        
		        return $last_invoice_date;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		public function get_invoices_of_client($clientid = 0 , $ids = false){
			
			// get invoices of one client
			$invoices_q = Doctrine_Query::create()
			->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			->from('MembersInvoices')
			->where("client = ?" , $clientid)
			->andWhere('isdelete = "0"  ');
			
			if ($ids!==false){
				if (!is_array($ids)){
					$ids = (empty($ids)) ? array("0") : array($ids);
				}
				$invoices_q->andWhereIn('id', $ids);
			}
			
			$invoices_array = $invoices_q->fetchArray();
			
			return $invoices_array;
			
		}
		
		
		
		public function get_invoices_of_clients($clientid = 0 , $ids = false){
				
			// get invoices of one client
			$invoices_q = Doctrine_Query::create()
			->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			->from('MembersInvoices')
			->where("client = ?" , $clientid)
			->andWhere('isdelete = "0"  ');
				
			if ($ids!==false){
				if (!is_array($ids)){
					$ids = (empty($ids)) ? array("0") : array($ids);
				}
				$invoices_q->andWhereIn('id', $ids);
			}
				
			$invoices_array = $invoices_q->fetchArray();
				
			return $invoices_array;
				
		}
	
	
	
	public function get_client_invoices ( $clientid =  0 , $filters = array() , $columns = "*" )
	{
		$result = array();
		
		$query = $this->getTable()->createQuery()
		->select( $columns ) // please select only the needed columns
		->where("client= ? " , $clientid);
		//->andWhere("mi.isdelete= 0 " ); // isdelete must be sent in the filters
	
		$customer_orderBy =  false;
		if ( ! empty($filters) && is_array($filters))
			foreach ($filters  as $row) {
	
				if ( ! empty($row['where']) && is_string($row['where'])) {
	
					$query->andWhere($row['where'], $row['params']); // i used only string
	
				}
	
				elseif ( ! empty($row['whereIn']) && is_array($row['params'])) {
	
					$query->andWhereIn($row['whereIn'], $row['params']);
	
				}
	
				elseif ( ! empty($row['whereNotIn']) && is_array($row['params'])) {
	
					$query->andWhereNotIn($row['whereIn'], $row['params']);
	
				}
	
				elseif ( ! empty($row['limit'])) {
	
					$query->limit($row['limit']); //please sanitize in your script
				}
	
				elseif ( ! empty($row['offset'])) {
	
					$query->offset($row['offset']); //please sanitize in your script
				}
	
				elseif ( ! empty($row['orderBy'])) {
					$customer_orderBy =  true;
					$query->orderBy($row['orderBy']);//please sanitize in your script
				}
	
			}
	
		$invoices = $query->fetchArray();
		
		foreach ($invoices as $row) {
			$result[$row['id']] = $row; //INDEXBY id
		} 

		return $result;
		
	}
	
	
	/**
	 * taken from InvoicenewController :: membersinvoicesAction
	 * @param number $clientid
	 */
	public function fetchUngeneratedMemberInvoicesByClientid($clientid = 0)
	{
	
	    if (empty($clientid)) {
	        return;
	    }
	
// 	    $members_invoices = new MembersInvoices();
// 	    $members_invoices_items = new MembersInvoiceItems();
// 	    $members_invoices_form = new Application_Form_MembersInvoices();
	
	    // get client settings.
	    $client_data_array = Client::getClientDataByid($clientid);
	    $client_data = $client_data_array[0];
	    $billing_method = $client_data['membership_billing_method'];
	
	    // get all invoices
	    $invoices_q = Doctrine_Query::create()
	    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
	    ->from('MembersInvoices')
	    ->where("client= ?", $clientid)
	    ->andWhere('isdelete = "0" ');
	    $invoices_array = $invoices_q->fetchArray();
	    $invoices_q->free();
	
	    $invoice_data = array();
	    $members2invoices = array();
	    $invoice_data_s = array();
	    
	    /*
	     * if you manualy create invoice for 2015 and 2018, by jumping over 2016,2017, autogen will only do the next 2019
	     * hold the highest end_date of one [member][membership_data]
	     */
	    $latest_invoices_by_membership = array();
	    
	    foreach($invoices_array as $k => $invoice){
	        $invoice_data[$invoice['member']][$invoice['membership_data']][$invoice['id']]['start'] = strtotime($invoice['invoice_start']);
	        $invoice_data[$invoice['member']][$invoice['membership_data']][$invoice['id']]['end'] = strtotime($invoice['invoice_end']);
	        $invoice_data_s[$invoice['member']][$invoice['id']]['start'] = $invoice['invoice_start'];
	        $invoice_data_s[$invoice['member']][$invoice['id']]['end'] = $invoice['invoice_end'];
	        $members2invoices[] = $invoice['member'];
	        
	        //membership_data
	        if( ! isset($latest_invoices_by_membership [$invoice['member']] [$invoice['membership_data']]) 
	            || strtotime($latest_invoices_by_membership [$invoice['member']] [$invoice['membership_data']]['invoice_end']) < strtotime($invoice['invoice_end'])) 
	        {
	            $latest_invoices_by_membership [$invoice['member']] [$invoice['membership_data']]['invoice_start'] = $invoice['invoice_start'];
	            $latest_invoices_by_membership [$invoice['member']] [$invoice['membership_data']]['invoice_end'] = $invoice['invoice_end']; // strtotime one-time here... but leave like this for readability
	        }
	        
	    }
	    if(empty($members2invoices)){
	        $members2invoices[] = "9999999999";
	    }
	    $members2invoices = array_unique($members2invoices);
	
	    //get all client members
// 	    $client_members = Member::get_client_members($clientid,0);
	    $client_members = Member::get_client_members($clientid,0, false, 0, $members2invoices);
	
	    $member_ids = array();
	    $inactive_details = array();
	    $fully_inactiv = array();
	    foreach($client_members as $mk =>$member_value){
	
	        if($member_value['inactive'] == "1" &&  $member_value['inactive_from'] == "0000-00-00"){
	            $fully_inactiv[] = $member_value['id'];
	        } else {
	            $member_ids[] = $member_value['id'];
	        }
	
	        $inactive_details[$member_value['id']]['inactive'] = $member_value['inactive'];
	        $inactive_details[$member_value['id']]['date'] = $member_value['inactive_from'];
	
	    }
	
	    if(empty($member_ids)) {
	
	        //if empty $member_ids then what should we list? invoices of deleted members?
	        return;
	    }
	
	
	    // get all membership data.
	    $membership2members = Member2Memberships::get_memberships_history($clientid,$member_ids);
	     
	    $current_date = date("d.m.Y", time());
	    // check if membership periods were invoiced
	    if(!empty($membership2members)){
	         
	        //################################################
	        foreach($membership2members as $sk=>$md_h){
	            //IMPORTANT
	            // - CHANGE MEMBERSHIP  END DATE IF INACTIVE DATE IS SET
	            if($memberarray['inactive'] == "1" && $memberarray['inactive_from'] !=  "0000-00-00"){
	                $inactive_date = date("Y-m-d H:i:s", strtotime($memberarray['inactive_from']));
	                if( $md_h['end_date'] != "0000-00-00 00:00:00"){
	                    if( strtotime($md_h['end_date'])  >  strtotime($inactive_date)){
	                        $membership2members[$$sk]['end_date'] =$inactive_date;
	                    }
	                } else {
	                     
	                    if( strtotime($md_h['start_date']) < strtotime($inactive_date) ){
	                        $membership2members[$sk]['end_date'] = $inactive_date;
	                    } else {
	                        unset($membership2members[$sk]); // remove periods that are after the inactive date
	                    }
	                }
	            }
	        }
	         
	         
	        $m = 0;
	        foreach($membership2members as $k=>$md){
	             
	            $membership_history[$md['member']][$md['id']] = $md;
	
	            if($client_data['membership_billing_method'] == "membership"){
	                 
	                if($md['start_date'] != "0000-00-00 00:00:00"){
	                    $membership_history[$md['member']][$md['id']]['start'] = date( 'd.m.Y',strtotime($md['start_date']));
	                } else{
	                    $membership_history[$md['member']][$md['id']]['start'] = "";
	                }
	                 
	                if($md['end_date'] != "0000-00-00 00:00:00"){
	                     
	                    $membership_history[$md['member']][$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
	                    $membership_history_cal[$md['member']][$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
	                     
	                } else {
	                     
	                    $membership_history[$md['member']][$md['id']]['end'] = "";
	                    // 			                $membership_history_cal[$md['member']][$md['id']]['end'] = $current_date;
	
	                    if(strtotime($md['start_date']) >= strtotime(date("d.m.Y",time()))){
	                         
	                        $membership_history_cal[$md['member']][$md['id']]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($md['start_date']))));
	                         
	                    } else{
	                         
	                        $membership_history_cal[$md['member']][$md['id']]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", time())));
	                         
	                    }
	                }
	                 
	                 
	                // get last invoiced date for member and membership
// 	                $last_invoiced_date[$md['member']][$md['id']] = MembersInvoices:: get_last_invoice_date($clientid,$md['member'],$md['id']);
	                if (isset($latest_invoices_by_membership[$md['member']][$md['id']]['invoice_end'])) {
	                    $last_invoiced_date[$md['member']][$md['id']] =  $latest_invoices_by_membership[$md['member']][$md['id']]['invoice_end'];
	                } else {
	                    $last_invoiced_date[$md['member']][$md['id']] = false; // false like the MembersInvoices:: get_last_invoice_date
	                }
	
	                // make date interval between last_invoiced_date and today
	                if($last_invoiced_date[$md['member']][$md['id']]){
	                    // get months number between last invoice date and current date
	                    $start_interval[$md['member']][$md['id']] = new DateTime(date("Y-m-d", strtotime("+1 day", strtotime($last_invoiced_date[$md['member']][$md['id']]))));
	                    //         			        $end_interval[$md['member']][$md['id']] = new DateTime(date("Y-m-d", strtotime($current_date)));
	                    $end_interval[$md['member']][$md['id']] = new DateTime(date("Y-m-d", strtotime(date("d.m.Y", strtotime("-1 day", strtotime("+12 months", time()))))));
	                    $overall_months[$md['member']][$md['id']] = 0;
	                     
	                    $start_interval[$md['member']][$md['id']]->add(new \DateInterval('P1M'));
	                    while ($start_interval[$md['member']][$md['id']] <= $end_interval[$md['member']][$md['id']]){
	                        $overall_months[$md['member']][$md['id']] ++;
	                        $start_interval[$md['member']][$md['id']]->add(new \DateInterval('P1M'));
	                    }
	                     
	                    if($overall_months[$md['member']][$md['id']] < 12){
	                        // do nothing
	                         
	                    } elseif ($overall_months[$md['member']][$md['id']] >= 12){
	                         
	                        $membership_intervals[$md['member']][$md['id']] = Pms_CommonData::generateDateRangeArray(  $membership_history[$md['member']][$md['id']]['start'], $membership_history_cal[$md['member']][$md['id']]['end'],"+1 year");
	                         
	                        foreach($membership_intervals[$md['member']][$md['id']] as $kmps => $start_dates){
	
	                            if($membership_intervals[$md['member']][$md['id']] >= 1 && $membership_history[$md['id']]['end'] == "") {
	                                 
	                                if( date( 'Y',strtotime($start_dates)) <= date("Y",time()) ){
	                                    $msp_intervals[$md['member']][$md['id']][$kmps]['start'] = $start_dates;
	                                    if($membership_intervals[$md['member']][$md['id']][$kmps+1]){
	                                        $msp_intervals[$md['member']][$md['id']][$kmps]['end'] = date("d.m.Y",strtotime("-1 day", strtotime($membership_intervals[$md['member']][$md['id']][$kmps+1])));
	                                    } else{
	                                        $msp_intervals[$md['member']][$md['id']][$kmps]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($membership_intervals[$md['member']][$md['id']][$kmps]))));
	                                    }
	                                }
	                                 
	                            } else{
	                                $msp_intervals[$kmps]['start'] = $start_dates;
	                                if($membership_intervals[$md['member']][$md['id']][$kmps+1]){
	                                    $msp_intervals[$md['member']][$md['id']][$kmps]['end'] = date("d.m.Y",strtotime("-1 day", strtotime($membership_intervals[$md['member']][$md['id']][$kmps+1])));
	                                } else{
	                                    $msp_intervals[$md['member']][$md['id']][$kmps]['end'] = date("d.m.Y", strtotime("-1 day", strtotime("+12 months", strtotime($start_dates))));
	                                }
	                            }
	                        }
	                         
	                        foreach($msp_intervals[$md['member']][$md['id']] as $int_k => $int_dates){
	                            $start_m_interval[$int_k] = new DateTime(date("Y-m-d", strtotime($int_dates['start'])));
	                            $end_m_interval[$int_k] =  new DateTime(date("Y-m-d", strtotime("+1 day" ,strtotime($int_dates['end']))));
	                            $membership_months[$md['member']] [$md['id']] [$int_k] = 0;
	
	                            $start_m_interval[$int_k] ->add(new \DateInterval('P1M'));
	                            while ( $start_m_interval[$int_k] <= $end_m_interval[$int_k]){
	                                $membership_months[$md['member']][$md['id']][$int_k]  ++;
	                                $start_m_interval[$int_k] ->add(new \DateInterval('P1M'));
	                            }
	                        }
	                         
	                        $m=0;
	                        foreach($msp_intervals[$md['member']][$md['id']] as $int_kk => $int_dates){
	                             
	                            //if you have an invoice generated after this, skip
	                            if (isset($latest_invoices_by_membership [$md['member']] [$md['id']] ['invoice_end'])
	                                &&
	                                strtotime($latest_invoices_by_membership[$md['member']] [$md['id']]['invoice_end']) > strtotime($int_dates['start'])  )
	                            {
	                                continue;
	                            }
	                            
	                            if( $membership_months[$md['member']][$md['id']][$int_kk] == 12 ){
	                                 
	                                $invoiced_periods[$md['member']][$md['id']][$m]['invoiced'] = 0;
	                                $invoiced_periods[$md['member']][$md['id']][$m]['member'] = $md['member'];
	                                $invoiced_periods[$md['member']][$md['id']][$m]['membership'] = $md['membership'];
	                                $invoiced_periods[$md['member']][$md['id']][$m]['membership_start'] = $md['start_date'];
	                                if($md['end_date'] == "0000-00-00 00:00:00"){
	                                    $invoiced_periods[$md['member']][$md['id']][$m]['membership_end'] = " - ";
	                                } else {
	                                    $invoiced_periods[$md['member']][$md['id']][$m]['membership_end'] = $md['end_date'];
	                                }
	                                $invoiced_periods[$md['member']][$md['id']][$m]['membership_price'] = $md['membership_price'];
	                                $invoiced_periods[$md['member']][$md['id']][$m]['start'] =  $int_dates['start'];
	                                $invoiced_periods[$md['member']][$md['id']][$m]['end'] =  $int_dates['end'];
	
	                                // check if periods are invoiced
	                                foreach($invoice_data[$md['member']][$md['id']] as $inv_id =>$inv_dates){
	                                    if(strtotime($invoiced_periods[$md['member']][$md['id']][$m]['start']) == $inv_dates['start']  && strtotime($invoiced_periods[$md['member']][$md['id']][$m]['end']) == $inv_dates['end']  ){
	                                        $invoiced_periods[$md['member']][$md['id']][$m]['invoiced'] += 1;
	                                    }
	                                    else
	                                    {
	                                        $invoiced_periods[$md['member']][$md['id']][$m]['invoiced'] += 0;
	                                    }
	                                }
	                                $m++;
	                            }
	                        }
	                    }
	                     
	                }
	                 
	            } else { // CALENDAR YEAR
	                 
	                if($md['start_date'] != "0000-00-00 00:00:00"){
	                    $membership_history[$md['member']][$md['id']]['start'] = date( 'd.m.Y',strtotime($md['start_date']));
	                } else{
	                    $membership_history[$md['member']][$md['id']]['start'] = "";
	                }
	
	                if($md['end_date'] != "0000-00-00 00:00:00"){
	                    $membership_history[$md['member']][$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
	                    $membership_history_cal[$md['member']][$md['id']]['end'] = date( 'd.m.Y',strtotime($md['end_date']));
	                } else{
	                    $membership_history[$md['member']][$md['id']]['end'] = "";
	                    // 			                $membership_history_cal[$md['member']][$md['id']]['end'] = $current_date;
	                    if( date( 'Y',strtotime($md['start_date'])) > date("Y",time()) ){
	                        $membership_history_cal[$md['member']][$md['id']]['end'] = date("d.m.Y",mktime(0,0,0,12,31,date( 'Y',strtotime($md['start_date']))));;
	                    } else{
	                        $membership_history_cal[$md['member']][$md['id']]['end'] = date("d.m.Y",mktime(0,0,0,12,31,date("Y",strtotime($current_date))));;
	                    }
	                     
	                }
	                 
	                 
	                // break membership period in calendar year intervals
	                $start_year = date('Y',strtotime($membership_history[$md['member']][$md['id']]['start']));
	                $end_year  = date('Y',strtotime($membership_history_cal[$md['member']][$md['id']]['end']));
	                $i= 0;
	                $interval[$md['id']] = array();
	                 
	                for ($i = $start_year; $i <= $end_year; $i++ ){
	                    if($i ==  $start_year &&  $start_year != $end_year){
	                        $interval[$md['id']][$i]['start'] = $membership_history[$md['member']][$md['id']]['start'];
	                        $interval[$md['id']][$i]['end'] =  date("d.m.Y",mktime(0,0,0,12,31,$i));
	                    } else if($i ==  $end_year && $start_year  != $end_year ){
	                        $interval[$md['id']][$i]['start'] = date("d.m.Y",mktime(0,0,0,01,01,$i));
	                        $interval[$md['id']][$i]['end'] =  $membership_history_cal[$md['member']][$md['id']]['end'];
	                    } else if($start_year == $end_year ){
	                        $interval[$md['id']][$i]['start'] = $membership_history[$md['member']][$md['id']]['start'];
	                        $interval[$md['id']][$i]['end'] =  $membership_history_cal[$md['member']][$md['id']]['end'];
	                    } else {
	                        $interval[$md['id']][$i]['start'] = date("d.m.Y",mktime(0,0,0,01,01,$i));
	                        $interval[$md['id']][$i]['end'] =  date("d.m.Y",mktime(0,0,0,12,31,$i));
	                    }
	                }
	                $y=0;
	                 
	                 
	                foreach($interval[$md['id']]  as $int_k => $int_dates){
	
	                    $start_m_interval[$int_k] = new DateTime(date("Y-m-d", strtotime($int_dates['start'])));
	                    $end_m_interval[$int_k] =  new DateTime(date("Y-m-d", strtotime("+1 day" ,strtotime($int_dates['end']))));
	
	                    $membership_months[$md['member']] [$md['id']] [$int_k] = 0;
	                     
	                    $start_m_interval[$int_k] ->add(new \DateInterval('P1M'));
	                    while ( $start_m_interval[$int_k] <= $end_m_interval[$int_k]){
	                        $membership_months[$md['member']][$md['id']][$int_k]  ++;
	                        $start_m_interval[$int_k] ->add(new \DateInterval('P1M'));
	                    }
	                }
	                 
	                 
	
	                foreach($interval[$md['id']] as $int_kk => $int_dates){
	                    
                        //if you have an invoice generated after this, skip
                        if (isset($latest_invoices_by_membership [$md['member']] [$md['id']] ['invoice_end'])
                            && 
                            strtotime($latest_invoices_by_membership[$md['member']] [$md['id']]['invoice_end']) > strtotime($int_dates['start'])  ) 
                        {
                            continue;
                        }
	                    
	                    if( $membership_months[$md['member']][$md['id']][$int_kk] == 12 ){
	                        $invoiced_periods[$md['member']][$md['id']][$y]['invoiced'] = 0;
	                        $invoiced_periods[$md['member']][$md['id']][$y]['member'] = $md['member'];
	                        $invoiced_periods[$md['member']][$md['id']][$y]['membership'] = $md['membership'];
	                        $invoiced_periods[$md['member']][$md['id']][$y]['membership_start'] = $md['start_date'];
	                        $invoiced_periods[$md['member']][$md['id']][$y]['membership_end'] = $md['end_date'];
	                        $invoiced_periods[$md['member']][$md['id']][$y]['membership_price'] = $md['membership_price'];
	                        $invoiced_periods[$md['member']][$md['id']][$y]['start'] = $int_dates['start'];
	                        $invoiced_periods[$md['member']][$md['id']][$y]['end'] = $int_dates['end'];
	                        foreach($invoice_data[$md['member']][$md['id']] as $inv_id =>$inv_dates){
	                            if(strtotime($invoiced_periods[$md['member']][$md['id']][$y]['start']) == $inv_dates['start']  && strtotime($invoiced_periods[$md['member']][$md['id']][$y]['end']) == $inv_dates['end']  ){
	                                $invoiced_periods[$md['member']][$md['id']][$y]['invoiced'] += 1;
	                            } else{
	                                $invoiced_periods[$md['member']][$md['id']][$y]['invoiced'] += 0;
	                            }
	                        }
	                        $y++;
	                    }
	                }
	            }
	        }
	    }
	
	    $invoice_data_params = array();
	    $period2invoice = array();
	    foreach($invoiced_periods as  $member_id => $m_invoiced_periods){
	        
	        
	        $period2invoice[$member_id] = array();
	        
	        if(in_array($member_id ,$members2invoices)){ // Only if member alredy has a generated invoice
	            foreach($m_invoiced_periods as $membership2member => $membership_intervals){
	                foreach($membership_intervals as $k=>$mintv){
	                    if( $mintv['invoiced'] == "0" &&  date("Y",strtotime($mintv['start'])) <=  date("Y",time()) ){
	                        $period2invoice[$member_id][$k]['invoiced'] = $mintv['invoiced'];
	                        $period2invoice[$member_id][$k]['member'] = $member_id;
	                        $period2invoice[$member_id][$k]['membership'] = $mintv['membership'];
	                        $period2invoice[$member_id][$k]['membership_start'] = $mintv['membership_start'];
	                        $period2invoice[$member_id][$k]['membership_end'] = $mintv['membership_end'];
	                        $period2invoice[$member_id][$k]['membership_data'] = $membership2member;
	                        $period2invoice[$member_id][$k]['membership_price'] = $mintv['membership_price'];
	                        $period2invoice[$member_id][$k]['int_start'] =  strtotime($mintv['start']);
	                        $period2invoice[$member_id][$k]['int_end'] = strtotime($mintv['end']);
	                        $period2invoice[$member_id][$k]['int_start_s'] = $mintv['start'];
	                        $period2invoice[$member_id][$k]['int_end_s'] = $mintv['end'];
	                        if(!in_array($member_id,$invoice_data_params['members'])){
	                            $invoice_data_params['members'][] = $member_id;
	                        }
	                        $invoice_data_params['selected_period'][$member_id] = $period2invoice[$member_id];
	                    }
	                }
	            }
	        }
	    }

	    if( ! empty($invoice_data_params)) {
	        
	        $invoice_data_params['auto_generate'] = "1";
	        
// 	        $this->generatemembersinvoice($invoice_data_params);
	    }
	    return $invoice_data_params; //;this return is used in Application_form_MembersInvoices -> generatemembersinvoice	    
	}
}

?>