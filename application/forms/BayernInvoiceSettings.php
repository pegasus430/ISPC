<?php

require_once("Pms/Form.php");

class Application_Form_BayernInvoiceSettings extends Pms_Form
{
	public function insert_data($bayern_options, $list, $client)
	{
		if(!empty($bayern_options))
		{
			$delete = $this->clear_old_data($list);

			$bayern_settings = new BayernInvoiceSettings();
			$bayern_settings->listid = $list;
			$bayern_settings->clientid = $client;
			$bayern_settings->option_name = 'max_days_amount';
			$bayern_settings->value = $bayern_options['max_days_amount'];
			$bayern_settings->save();
		}
	}

	private function clear_old_data($list)
	{
		$q = Doctrine_Query::create()
		->delete('*')
		->from('BayernInvoiceSettings')
		->where('listid ="' . $list . '"');
		$q_res = $q->execute();
	}

}

?>