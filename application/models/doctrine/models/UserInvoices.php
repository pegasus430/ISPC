<?php

	Doctrine_Manager::getInstance()->bindComponent('UserInvoices', 'SYSDAT');

	class UserInvoices extends BaseUserInvoices {

		public function getUserInvoice($invoice, $status = false)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('UserInvoices')
				->where("id='" . $invoice . "'")
				->andWhere('isdelete = 0');
			if($status)
			{
				$invoices->andWhere('status = "' . $status . '"');
			}
			$invoices->limit('1');

			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return $invoices_res[0];
			}
			else
			{
				return false;
			}
		}

		public function get_invoice($invoiceid)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('UserInvoices')
				->andWhere('id = "' . $invoiceid . '"')
				->andWhere('isdelete = 0');
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

		public function get_highest_invoice_sub_number($invoice_number)
		{
			$invoice_number = Doctrine_Query::create()
				->select("*")
				->from('UserInvoices')
				->where("invoice_number='" . $invoice_number . "'")
				->andWhere('isdelete = 0')
				->orderBy('invoice_subnumber DESC')
				->limit('1');

			$invoice_number_res = $invoice_number->fetchArray();

			if($invoice_number_res)
			{
				return $invoice_number_res[0];
			}
			else
			{
				return false;
			}
		}

		public function get_period_user_invoices($clientid, $period)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('UserInvoices')
				->where("client='" . $clientid . "'")
				->andWhere('isdelete = 0');
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

		public function create_storno_invoice($invoiceid)
		{
			$find_invoice = Doctrine::getTable('UserInvoices')->findOneById($invoiceid);
			$found_invoice = $find_invoice->toArray();

			$has_storno = self::has_storno($invoiceid);

			if($found_invoice && !$has_storno)
			{
				//ISPC-2532 create new number for storno invoice Carmen 13.02.2020
				$invoice_subnumber = $this->get_highest_invoice_sub_number($found_invoice['invoice_number']);
				
				$inv = new UserInvoices();
				$inv->invoice_start = $found_invoice['invoice_start'];
				$inv->invoice_end = $found_invoice['invoice_end'];
				$inv->ipid = $found_invoice['ipid'];
				$inv->userid = $found_invoice['userid'];
				$inv->client = $found_invoice['client'];
				$inv->prefix = $found_invoice['prefix'];
				$inv->invoice_number = $found_invoice['invoice_number'];
				
				if(!$invoice_subnumber)
				{
					$inv->invoice_subnumber = $found_invoice['invoice_subnumber'];
				}
				else
				{
					$inv->invoice_subnumber = ($invoice_subnumber['invoice_subnumber'] + 1);
				}
				
				$inv->invoice_total = $found_invoice['invoice_total'];
				$inv->paid_date = $found_invoice['paid_date'];
				$inv->status = $found_invoice['status'];
				$inv->recipient = $found_invoice['recipient'];
				$inv->user_bank_details = $found_invoice['user_bank_details'];
				$inv->user_address = $found_invoice['user_address'];
				$inv->ikuser = $found_invoice['ikuser'];
				$inv->isdelete = '0';
				$inv->record_id = $invoiceid;
				$inv->storno = '1';
				$inv->save();
			}
		}

		public function del_storno_invoice($invoiceid)
		{
			$del_storno_invoice = Doctrine::getTable('UserInvoices')->findOneById($invoiceid);
			$del_storno_invoice->isdelete = '1';
			$del_storno_invoice->save();
		}

		public function get_storned_invoices($clientid)
		{
			$storno_invoices = Doctrine_Query::create()
				->select("*")
				->from('UserInvoices')
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
				->from('UserInvoices')
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
		
		//ISPC - 2365 - batch print
		public function get_invoices($invoices_ids)
		{
			if(!is_array($invoices_ids))
			{
				$invoices_ids = array($invoices_ids);
			}
			
			if(empty($invoices_ids)){
				return false;
			}
		
			$invoices = Doctrine_Query::create()
			->select("*")
			->from('UserInvoices')
			->whereIn("id", $invoices_ids)
			->andWhere('isdelete = "0"');
			//->andWhere('isarchived = "0"');
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
				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$master_data['userids'][] = $v_invoice_res['userid'];
					
					$master_data['invoices_ipdis'][] = $v_invoice_res['ipid'];
		
					$master_data['invoices_data'][$v_invoice_res['id']] = $v_invoice_res;
				}
		
				return $master_data;
			}
			else
			{
				return false;
			}
		}
		
		//ISPC-2747 Lore 03.12.2020
		public function getUserInvoiceandItems($invoice, $status = false)
		{
		    
		    $userinvoice_items = new UserInvoiceItems();
		    
		    $invoices = Doctrine_Query::create()
		    ->select("*")
		    ->from('UserInvoices')
		    ->where("id='" . $invoice . "'")
		    ->andWhere('isdelete = 0');
		    if($status)
		    {
		        $invoices->andWhere('status = "' . $status . '"');
		    }
		    $invoices->limit('1');
		    
		    $invoices_res = $invoices->fetchArray();
		    
		    if($invoices_res)
		    {
		        
		        //get all invoice items
		        $invoice_items = $userinvoice_items->getInvoicesItems($invoices_res[0]['id']);
		        
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

	}

?>