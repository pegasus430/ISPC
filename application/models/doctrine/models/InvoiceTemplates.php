<?php

	Doctrine_Manager::getInstance()->bindComponent('InvoiceTemplates', 'SYSDAT');

	class InvoiceTemplates extends BaseInvoiceTemplates {

		public function init()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->clientid = $logininfo->clientid;
			$this->userid = $logininfo->userid;
			$this->usertype = $logininfo->usertype;
			$this->filepass = $logininfo->filepass;
		}

		public function get_template($clientid = false, $template = false, $limit = false, $invoice_type = false)
		{
			$res = Doctrine_Query::create()
				->select('*')
				->from('InvoiceTemplates')
				->where('isdeleted = "0"');
			//this is the requested client id
			if($clientid)
			{
				$res->andWhere('clientid="' . $clientid . '"');
			}

			if($template)
			{
				$res->andWhere('id = "' . $template . '"');
			}

			//skip the invoice type check (used in recall)
			if($invoice_type != 'skip')
			{
				//also check if invoice_type is in existing invoices arr from common_data
				if($invoice_type && in_array($invoice_type, Pms_CommonData::allinvoices()))
				{

					$res->andWhere('invoice_type LIKE "' . $invoice_type . '"');
				}
			}
			else
			{
				$res->andWhere('invoice_type = ""');
			}

			if($limit)
			{
				$res->limit($limit);
			}
			
			//always get last added template
			$res->orderBy("id DESC");
			$res_arr = $res->fetchArray();

			if($res_arr)
			{
				return $res_arr;
			}
			else
			{
				//fallback mode check if there is a template but without the invoice_type set
				//(a.k.a old template which is added prior to new invoice_type field in db)
				if($invoice_type !== false && $invoice_type != 'skip')
				{
					//recall curent function but with "skip" invoice_type
					return self::get_template($clientid, $template, $limit, 'skip');
				}
				else
				{
					return false;
				}
			}
		}

	}

?>