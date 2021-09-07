<?php
require_once("Pms/Form.php");
class Application_Form_FormTypes extends Pms_Form
{

	public function insert_form_type ( $clientid, $post )
	{
		if ($clientid && count($post['form_type']) > 0)
		{
			$insert = new FormTypes();
			$insert->clientid = $clientid;
			$insert->name = $post['form_type'];
			$insert->action = $post['form_type_action'];
			$insert->calendar_color = $post['calendar_color'];
			$insert->calendar_text_color = $post['calendar_text_color'];
			$insert->save();

			return $insert->id;
		}
		else
		{
			return false;
		}
	}

	public function update_form_type ( $form_type_id, $post )
	{
		$update = Doctrine_Query::create()
		->update('FormTypes')
		->set('name','?',$post['form_type'])
		->set('action', '?', $post['form_type_action'])
		->set('calendar_color','?',  $post['calendar_color'])
		->set('calendar_text_color','?',  $post['calendar_text_color'])
		->where('id="' . $form_type_id . '"');
		$update_ex = $update->execute();

		if ($update_ex)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function delete_form_type ( $form_type_id, $post )
	{
		$update = Doctrine_Query::create()
		->update('FormTypes')
		->set('isdelete','?',1)
		->where('id="' . $form_type_id . '"');
		$update_ex = $update->execute();

		if ($update_ex)
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
