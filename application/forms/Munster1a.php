<?php
require_once("Pms/Form.php");
class Application_Form_Munster1a extends Pms_Form
{

	public function insert_multiple_data ( $ipid, $post )
	{
		$insert_data = new Munster1a();
		
		//insert firt row
		$insert_data->ipid = $ipid;
		$insert_data->input_name = $post[0]['input_name'];
		$insert_data->input_value = $post[0]['input_value'];
		$insert_data->completed_date = $post[0]['completed_date'];
		$insert_data->save();
		$id = $insert_data->id;
		
		$insert_data -> formular_id = $id;
		$insert_data->save();
		unset($post[0]);
		
		//use the last id for every other input as formular_id
		foreach ($post as $k=>$v){
			$post[$k]['formular_id'] = $id;
		}
		if (!empty($post)){
			$collection = new Doctrine_Collection('Munster1a');
			$collection->fromArray($post);
			$collection->save();
		}
		/*
		if ($id)
		{
			$comment = "Formular Muster 63 hinzugefügt.";
			$patient_file = new PatientCourse();
			$patient_file->ipid = $ipid;
			$patient_file->course_date = date("Y-m-d H:i:s", time());
			$patient_file->course_type = Pms_CommonData::aesEncrypt("F");
			$patient_file->course_title = Pms_CommonData::aesEncrypt($comment);
			$patient_file->user_id = $logininfo->userid;
			$patient_file->done_name = Pms_CommonData::aesEncrypt('muster_63_form');
			$patient_file->tabname = Pms_CommonData::aesEncrypt('muster_63_form');
			$patient_file->done_id = $id;
			$patient_file->save();
				
			return $id;
		}
		else
		{
			return false;
		}
		*/
	}

	
	
	public function update_data ($ipid, $post , $lastformular_formular_id)
	{
		$post2save = array();
		foreach($post as $k=>$v){
			$v['formular_id'] = $lastformular_formular_id;
			$post2save[$v['input_name']] = $v;
		}
		
		$cust = Doctrine::getTable('Munster1a')->findByFormular_id( $lastformular_formular_id );
		foreach($cust as $key => $value) {
		 	$cust->{$key}->input_value = $post2save[ $value->input_name ] ['input_value'];
		 	unset($post2save[ $value->input_name ]);
		}
		$cust->save();
		
		//some extra fields to save
		if (!empty($post2save)){
			$collection = new Doctrine_Collection('Munster1a');
			$collection->fromArray($post2save);
			$collection->save();
		}

		return true;
	}

	
	
	public function mark_as_completed ( $ipid )
	{

		$cust = Doctrine::getTable('Munster1a')->findByIpidAndIscompleted( $ipid , 0 );
		foreach($cust as $key => $value) {
			$cust->{$key}->completed_date = date("Y-m-d H:i:s", time());
			$cust->{$key}->iscompleted = 1;
		}
		$cust->save();
		
		return true;
	}
}
?>