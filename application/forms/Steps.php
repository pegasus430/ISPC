<?php
require_once("Pms/Form.php");
class Application_Form_Steps extends Pms_Form
{

	public function validate ( $post )
	{

	}

	public function insert_data ( $post )
	{
		$insert_step = new OrgSteps();
		$insert_step->path = $post['path'];
		$insert_step->master = $post['master'];
		$insert_step->name = $post['name'];
		$insert_step->shortcut = $post['shortcut'];
		$insert_step->tabname = $post['tabname'];
		$insert_step->todo_text = $post['todo_text'];
		$insert_step->ismanual = $post['manual'];
		$insert_step->save();

		if ($insert_step)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function delete_step ( $step )
	{
		$del_step = Doctrine_Query::create()
		->update("OrgSteps")
		->set('isdelete', 1)
		->where('id="' . $step . '"');

		$del_step->execute();

		if ($del_step)
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