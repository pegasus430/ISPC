<?php
require_once("Pms/Form.php");
class Application_Form_InvoiceSettings extends Pms_Form
{
	//individual invoice number settings
	public function insert_invoice_settings ( $post, $client )
	{
		$del_old_entryes = $this->delete_invoice_settings($client);

		foreach ($post as $k_post => $v_post)
		{
			if ($k_post != 'invoice_number_type')
			{
				$settings_data[] = array(
						'client' => $client,
						'invoice_type' => $k_post,
						'invoice_prefix' => $v_post['prefix'],
						'invoice_start' => $v_post['start']
				);
			}
		}

		$collection = new Doctrine_Collection('InvoiceSettings');
		$collection->fromArray($settings_data);
		$collection->save();
	}

	//used in individual settings table only
	public function delete_invoice_settings ( $client )
	{
		$delete = Doctrine_Query::create()
		->delete('InvoiceSettings')
		->where('client="' . $client . '"');
		$del_res = $delete->execute();

		return $del_res;
	}

	//collective invoice number settings
	public function update_collective_settings ( $post, $client )
	{
		$client = Doctrine::getTable('Client')->find($client);
		$client->invoice_number_prefix = $post['invoice_number_prefix'];
		$client->invoice_number_start = $post['invoice_number_start'];
		$client->save();

		if ($client->id)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}
?>