<?php
require_once("Pms/Form.php");
class Application_Form_FormSapvBlockPermissions extends Pms_Form
{

	public function insert_block_permisions ( $client, $post_data )
	{
		if ($client)
		{
			$clear_perms = $this->clear_block_sapv_perms($client);

			foreach ($post_data['has_sapv'] as $block_key => $block_value)
			{
				$perms_arr[] = array(
						'clientid' => $client,
						'block' => $block_key,
						'value' => $block_value
				);
			}

			if (count($perms_arr) > 0)
			{
				$collection = new Doctrine_Collection('FormSapvBlockPermissions');
				$collection->fromArray($perms_arr);
				$collection->save();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	public function clear_block_sapv_perms ( $client )
	{
		$del_perms = Doctrine_Query::create()
		->delete('*')
		->from('FormSapvBlockPermissions')
		->where('clientid="' . $client . '"');
		$del_perms_exec = $del_perms->execute();
	}

}
?>