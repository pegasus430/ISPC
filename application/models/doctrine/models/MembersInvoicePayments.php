<?php

Doctrine_Manager::getInstance()->bindComponent('MembersInvoicePayments', 'SYSDAT');

class MembersInvoicePayments extends BaseMembersInvoicePayments 
{

	
	private static $mandatory_columns = array(
			'invoice',
			'amount',
			'status',
	);
	
	
		public function delete_invoice_payment($payment)
		{
			if($payment > 0)
			{
				$delInvoicePayment = Doctrine_Query::create()
					->update("MembersInvoicePayments")
					->set('isdelete', "1")
					->where('id = "' . $payment . '"');

				$d = $delInvoicePayment->execute();

				if($d)
				{
					return true;
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

		public function getInvoicePayments( $invoice )
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('MembersInvoicePayments')
				->Where("invoice= ? ", $invoice)
				->andWhere('isdelete = 0')
				->fetchArray();
			return $invoices;
		}

		public function getInvoicesPaymentsSum($invoiceids = array())
		{
			if (empty($invoiceids) || !is_array($invoiceids)){
				return;
			}
			
			$invoiceids = array_values(array_unique($invoiceids));

			$invoices = Doctrine_Query::create()
				->select("id, invoice, SUM(amount) as paid_sum")
				->from('MembersInvoicePayments')
				->WhereIn("invoice", $invoiceids)
				->andWhere("status = 'paid'")
				->andWhere('isdelete = 0')
				->groupBy('invoice');

			$items_array = $invoices->fetchArray();
			foreach($items_array as $kpay => $vpay)
			{
				$final_invoice_items[$vpay['invoice']]['paid_sum'] = $vpay['paid_sum'];
			}
			return $final_invoice_items;
		}

		public function get_invoice($invoiceid = 0)
		{
			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('MembersInvoices')
				->andWhere('id = ? ' , $invoiceid )
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

	/**
	 * be aware, the fn name may be misleading - this is how Doctrine works!
	 * this fn will insert new if there is no db-record object in our class... 
	 * if you called second time, or you fetchOne, it will update! 
	 * fn was intended for single record, not collection
	 * @param array $params
	 * @return boolean|number
	 * return $this->id | false if you don't have the mandatory_columns in the params 
	 */
	public function set_new_record($params = array())
	{
	
		if (empty($params) || !is_array($params)) {
			return false;// something went wrong
		}
	
		foreach (self::$mandatory_columns as $column) {
			if ( ! isset($params[$column]) || empty($params[$column]) ) {
				return false;
			}
		}
	
		foreach ($params as $k => $v)
			if (isset($this->{$k})) {
	
				//next columns should be encrypted
				switch ($k) {
					case "column_name_example1":
					case "column_name_example2":
						$v = Pms_CommonData::aesEncrypt($v);
						break;
				}
				$this->{$k} = $v;
	
			}
				
		$this->save();
		return $this->id;
	
	}
			
	/**
	 * insert multiple records at once
	 * 
	 * @param array(array()) $params
	 */
	public function set_new_collection($params = array(array()))
	{
		$records =  array();
		$cnt = 0;
		foreach ($params as $row) {
			
			foreach (self::$mandatory_columns as $column) {
				if ( ! isset($row[$column]) || empty($row[$column]) ) {
					continue;
				}
			}
			
			foreach ($row as $k => $v)
				if (isset($this->{$k})) {
						//next columns should be encrypted ...
					//@todo: created a encrypt Doctrine_Record_Listener, and just add a param on the column with encrypted=true, this will trigger the dql to make the column binary, and do the rest
					switch ($k) {
						case "column_name_example1":
						case "column_name_example2":
							$v = Pms_CommonData::aesEncrypt($v);
							break;
					}
					
					$records[$cnt][$k] = $v;
			
				}
			
			$cnt ++;
		}
		if (! empty($records)) {
			$__CLASS__ = get_called_class();
			$collection_obj = new Doctrine_Collection($__CLASS__);
			$collection_obj->fromArray($records);
			$collection_obj->save();
		}
		
	}
		
	public function get_paid( $invoce_ids =  array())
	{
		$result = $this->get_payments($invoce_ids , "paid");
		return $result;
	}
	
	public function get_unpaid( $invoce_ids =  array())
	{
		$result = $this->get_payments($invoce_ids , "!paid");
		return $result;
	}
	
	public function get_payments( $invoce_ids =  array() , $status = "")
	{
		if (empty($invoce_ids) || !is_array($invoce_ids)) {
			return false;// no ids, we should check first and return our collection
		}
		$result = array();
		$invoce_ids = array_values(array_unique($invoce_ids));
	
		$dq = $this->getTable()->createQuery()
		->select("*")
		->whereIn("invoice" , $invoce_ids )
		->andWhere('isdelete = 0')
		->orderBy('FIELD(status, \'paid\',\'storno\', \'payment-requested\', \'created\', \'installment\' ), create_date');
		
		if ($status != "") {
			if (substr($status, 0, 1) == "!") {
				$status =  substr($status, 1);
				$dq->andWhere('status != ?' , $status);
			} else {
				$dq->andWhere('status = ?' , $status);
			}
		}
		$invoices = $dq->fetchArray();
	
		foreach ($invoices as $row) {
			$result[$row['invoice']][] = $row;
		}
		return $result;
	}
	
	
	public function delete_row( $id = null )
	{		
		if (( ! is_null($id)) && ($obj = $this->getTable()->find($id))) 
		{
			$obj->delete();
			return true;
			
		} else {
			return false;
		}
	}
		
	/**
	 * 
	 * @param number $clientid
	 * @param array $filters
	 * @return multitype:Ambigous <multitype:, Doctrine_Collection>
	 */
	public function get_client_payments ( $clientid =  0 , $filters = array() , $count_total = false) 
	{
		$result = array();

		$query = $this->getTable()->createQuery('mip');
		if ( ! $count_total) {
			$query->select("*,
					IF(mip.paid_date != '0000-00-00 00:00:00', mip.paid_date, mip.scheduled_due_date) as custom_order_date1,					
					mi.id,
					mi.member,
					mi.invoice_total,
					CONCAT(mi.prefix,mi.invoice_number) AS full_invoice_number
					
					");
		} else {
			$query->select("count(*) as counter");
		}
		
		$query->leftJoin("mip.MembersInvoices mi")
		->where("mi.client= ? " , $clientid)
		->andWhere("mi.isdelete= 0 " )
		->andWhere("mip.isdelete= 0 " );

		$customer_orderBy =  false;
		if ( ! empty($filters) && is_array($filters))
			foreach ($filters  as $row) {
		
				if ( ! empty($row['where']) && is_string($row['where'])) {
		
					$query->andWhere($row['where'], $row['params']); // i used only string
		
				}
		
				elseif ( ! empty($row['whereIn']) && is_array($row['params'])) {
		
					$row['params'] = array_values(array_unique($row['params']));
					$query->andWhereIn($row['whereIn'], $row['params']);
		
				}
				
				elseif ( ! empty($row['whereNotIn']) && is_array($row['params'])) {
					
					$row['params'] = array_values(array_unique($row['params']));
					$query->andWhereNotIn($row['whereIn'], $row['params']);
				
				}
		
				elseif ( ! empty($row['limit']) && ! $count_total) {
		
					$query->limit($row['limit']); //please sanitize in your script
				}
		
				elseif ( ! empty($row['offset']) && ! $count_total) {
		
					$query->offset($row['offset']); //please sanitize in your script
				}
				
				elseif ( ! empty($row['orderBy']) && ! $count_total) {
					$customer_orderBy =  true;
					$query->orderBy($row['orderBy']);//please sanitize in your script
				}				
				
			}
	
		if ( ! $customer_orderBy && ! $count_total) {
			$query->orderBy('FIELD(mip.status, \'paid\',\'storno\', \'payment-requested\', \'created\', \'installment\' ), create_date');
				
		}
		
		if ( ! $count_total) {
// 			$invoices_obj = $query->execute();
// 			$invoices = $invoices_obj->toArray();
			$invoices = $query->fetchArray();
			foreach ($invoices as $row) {
				$result['grop_by_invoice'][$row['invoice']][] = $row;
			}
			$result['order_by_status'] = $invoices;
// 			$result['invoices_obj'] = &$invoices_obj; 
		} else {
			
			$invoices = $query->fetchOne();
			$result = array("counter" => $invoices['counter']);
		}
		
		return $result;
		
		
	}	
	
		
	public function set_status_payment_requested( $ids = array() )
	{	
		if (empty($ids) || !is_array($ids)) {
			return false;// something went wrong
		}	
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$change_user = $logininfo->userid;
		
		$ids = array_values(array_unique($ids));
		
		$dq = $this->getTable()->createQuery()
		->update()
		->set("status", "?", "payment-requested")
		->set("change_date", "NOW()")
		->set("change_user", "?", $change_user)
		->whereIn("id", $ids)
		->andwhere("status= 'created' or status='installment'")
		->execute();
	
	}
	
	
	
	public function set_status_paid( $ids = array() , $payment_comment = null , $payment_date = null )
	{
		if (empty($ids) || !is_array($ids)) {
			return false;// something went wrong
		}
	
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$change_user = $logininfo->userid;

		$ids = array_values(array_unique($ids));
		
		if (is_null($payment_date)) {
			$payment_date =  date("Y-m-d");
		} else {
			$payment_date = date("Y-m-d" , strtotime($payment_date));
		}
		
		if (is_null($payment_comment)) {
			$payment_comment =  '';
		}
		
		
		$dq = $this->getTable()->createQuery()
		->update()
		->set("status", "?", "paid")
		->set("comment", "?", $payment_comment)
		->set("paid_date", "STR_TO_DATE(?, '%Y-%m-%d')" , $payment_date)
		->set("change_date", "NOW()")
		->set("change_user", "?", $change_user)
		->whereIn("id", $ids)
		->andwhere("status != 'paid'")
		->execute();
		
	}
	
	//invoice has no payment of any status && isdelete=0
	public function find_InvoicesWithoutPayments($invoiceids = array())
	{
		if (empty($invoiceids) || !is_array($invoiceids)){
			return;
		}
			
		$invoiceids = array_values(array_unique($invoiceids));
	
		$dq = $this->getTable()->createQuery()
		->select("id, invoice")
		->WhereIn("invoice", $invoiceids)
		->andWhere('isdelete = 0')
		->groupBy('invoice')
		->fetchArray();
		
		$invoices_with_payments = array();
		$invoices_without_payments = array();
		
		foreach($dq as $row)
		{
			$invoices_with_payments[$row['invoice']] = true;
		}
		
		
		foreach ($invoiceids as $id) {
			if ( ! isset($invoices_with_payments[$id])) {
				$invoices_without_payments[$id] = true;
			}
		}

		return $invoices_without_payments;
		
	}
	
	
	
}//end of __CLASS__

?>