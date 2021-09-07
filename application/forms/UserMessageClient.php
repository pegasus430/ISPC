<?php
require_once("Pms/Form.php");
class Application_Form_UserMessageClient extends Pms_Form
{
	public function validate ( $post )
	{

	}
	public function insert_data ( $post )
	{
		foreach ($post['message_clients'] as $k_post => $v_post)
		{
			$save_data[] = array('userid' => $post['userid'], 'client' => $v_post);
		}

		if (count($save_data) > 0)
		{
			$clear_user_data = $this->clear_user_data($post['userid']);
			$collection = new Doctrine_Collection('UserMessageClient');
			$collection->fromArray($save_data);
			$collection->save();

			$inserted_ids = $collection->getPrimaryKeys();

			if ($inserted_ids)
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
			//empty selection = clear db for this user
			$clear_user_data = $this->clear_user_data($post['userid']);

			if($clear_user_data)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	private function clear_user_data ( $user )
	{
		$del_user_data = Doctrine_Query::create()
		->delete('UserMessageClient')
		->where('userid = "' . $user . '"');
		$del_res = $del_user_data->execute();

		if ($del_res)
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