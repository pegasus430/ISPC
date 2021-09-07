<?php
require_once("Pms/Form.php");
class Application_Form_Dashboard extends Pms_Form
{
	public function add_label ( $post )
	{
		if ($post)
		{
			if ($post['old_id'])
			{
				$this->delete_label($post['old_id']);
			}
			$insert_label = new DashboardLabels();
			$insert_label->name = $post['label_name'];
			$insert_label->color = $post['color_label'];
			$insert_label->font_color = $post['color_font'];
			$insert_label->action = $post['select_action'];
			$insert_label->isdelete = '0';
			$insert_label->save();

			if ($insert_label->id)
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
			return false;
		}
	}

	public function delete_label ( $label )
	{
		$update_label = Doctrine_Query::create()
		->update("DashboardLabels")
		->set('isdelete', '1')
		->where('id ="' . $label . '"');
		$update_label->execute();

		if ($update_label)
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