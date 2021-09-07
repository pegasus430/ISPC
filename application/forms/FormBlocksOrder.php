<?php
require_once("Pms/Form.php");
class Application_Form_FormBlocksOrder extends Pms_Form
{

	public function save_form_blocks ( $client, $form_type, $order )
	{
		if ($client && $form_type)
		{
			$clear_perms = $this->clear_block_order($client, $form_type);

			$save_order = new FormBlocksOrder();
			$save_order->client = $client;
			$save_order->form_type = $form_type;
			$save_order->box_order = implode(',', $order);
			$save_order->save();

			if ($save_order->id)
			{
				$blocks_order = new FormBlocksOrder();
				$blocks_order_res = $blocks_order->get_blocks_order($client, $form_type);
				return $blocks_order_res;
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

	public function clear_block_order ( $client, $form_type )
	{
		$del_assigned = Doctrine_Query::create()
		->delete('*')
		->from('FormBlocksOrder')
		->where('client="' . $client . '"')
		->andWhere('form_type="' . $form_type . '"');
		$del_assigned_exec = $del_assigned->execute();
	}

}
?>