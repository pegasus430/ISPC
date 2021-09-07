<?php
require_once("Pms/Form.php");
class Application_Form_FormBlocks2Type extends Pms_Form
{

	public function assign_form_blocks ( $client, $form_type, $post_data )
	{
		if ($client && $form_type)
		{
			$clear_perms = $this->clear_block_permisions($client, $form_type);

			foreach ($post_data['assign'] as $block_key => $block_value)
			{
				$assign_arr[] = array(
						'clientid' => $client,
						'form_type' => $form_type,
						'form_block' => $block_key,
				);
			}

			if (count($assign_arr) > 0)
			{
				$collection = new Doctrine_Collection('FormBlocks2Type');
				$collection->fromArray($assign_arr);
				$collection->save();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	public function clear_block_permisions ( $client, $form_type )
	{
		$del_assigned = Doctrine_Query::create()
		->delete('*')
		->from('FormBlocks2Type')
		->where('clientid="' . $client . '"')
		->andWhere('form_type="' . $form_type . '"');
		$del_assigned_exec = $del_assigned->execute();
	}

}
?>