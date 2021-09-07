<?php

	Doctrine_Manager::getInstance()->bindComponent('RemindersInvoice', 'SYSDAT');

	class RemindersInvoice extends BaseRemindersInvoice {

		public function init()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->clientid = $logininfo->clientid;
			$this->userid = $logininfo->userid;
			$this->usertype = $logininfo->usertype;
			$this->filepass = $logininfo->filepass;
		}
		
		public function get_reminders($invoice_ids, $invoice_type, $clientid = "")
		{
			if (empty($invoice_ids) || !is_array($invoice_ids)) {
			return false;
			}
			$result = array();
			$invoice_ids = array_values(array_unique($invoice_ids));
			
			$dq = $this->getTable()->createQuery()
			->select("*")
			->whereIn("invoiceid" , $invoice_ids)
			->andWhere('invoice_type = ?', $invoice_type)
			->andWhere('isdeleted = 0');
			if($clientid != "")
			{
				$dq->andWhere('clientid = ?', $clientid);
			}
			$dq->orderBy('create_date');
			
			$warnings = $dq->fetchArray();
			
			foreach ($warnings as $row) {
				$result[$row['invoiceid']][] = $row;
			}
			//var_dump($result); exit;
			return $result;
		}
	}