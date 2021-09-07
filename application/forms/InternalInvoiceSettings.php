<?php

	require_once("Pms/Form.php");

	class Application_Form_InternalInvoiceSettings extends Pms_Form {

		//individual invoice number settings
		public function insert_invoice_settings($post, $client, $user)
		{
			$del_old_entryes = $this->delete_invoice_settings($user, $client);

			$stmb = new InternalInvoiceSettings();
			$stmb->client = $client;
			$stmb->user = $user;
			$stmb->invoice_prefix = $post['invoice_prefix'];
			$stmb->invoice_start = $post['invoice_start'];
			$stmb->invoice_pay_days = $post['invoice_pay_days'];
			$stmb->save();
		}

		//used in individual settings table only
		public function delete_invoice_settings($user, $client)
		{
			$delete = Doctrine_Query::create()
				->delete('InternalInvoiceSettings')
				->where('user="' . $user . '"')
				->andWhere('client ="' . $client . '"');
			$del_res = $delete->execute();

			return $del_res;
		}

	}

?>