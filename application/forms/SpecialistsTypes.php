<?php
require_once("Pms/Form.php");
class Application_Form_SpecialistsTypes extends Pms_Form
{

	public function insert_specialist_type ( $clientid, $post )
	{
		if ($clientid && count($post['specialist_type']) > 0)
		{
			$insert = new SpecialistsTypes();
			$insert->clientid = $clientid;
			$insert->name = $post['specialist_type'];
			$insert->save();

			return $insert->id;
		}
		else
		{
			return false;
		}
	}

	public function update_specialist_type ( $type_id, $post )
	{
		$update = Doctrine_Query::create()
		->update('SpecialistsTypes')
		->set('name', '"' . $post['specialist_type'] . '"')
		->where('id="' . $type_id . '"');
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

	public function delete_form_type ( $form_type_id )
	{

	}

}
?>
