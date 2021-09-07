<?php
require_once("Pms/Form.php");
class Application_Form_Paths extends Pms_Form
{

	public function validate ( $post )
	{

	}

	public function insert_data ( $post )
	{
		$insert_path = new OrgPaths();
		$insert_path->name = $post['name'];
		$insert_path->function = $post['function'];
		$insert_path->client = $post['client'];
		$insert_path->save();

		if ($insert_path)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function delete_path ( $path, $client )
	{
		$del_path = Doctrine_Query::create()
		->update("OrgPaths")
		->set('isdelete', '1')
		->where('id ="' . $path . '"')
		->andWhere('client ="' . $client . '"');
		$del_path->execute();

		if ($del_path)
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