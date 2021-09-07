<?php

require_once("Pms/Form.php");

class Application_Form_PatientBarthel extends Pms_Form {

	public function validate($post) {
	}

	public function insert_data($post, $ipid)
	{
		$ins = new PatientBarthel();
		$ins->ipid = $ipid;
		$ins->total_score = $post['total_score'];
		$ins->save();

		$inserted_id = $ins->id;

		$comment = '';
		$now = date("Y-m-d H:i:s", time());
		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = $now;
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt(addslashes('Formular Barthel Index wurde erstellt'));
		$cust->tabname = Pms_CommonData::aesEncrypt('barthelscore_form_save');
		$cust->recordid = $inserted_id;
		$cust->user_id = $post['userid'];
		$cust->done_date = $now;
		$cust->save();

		if($inserted_id)
		{
			foreach($post['bi'] as $k_value=> $v_value)
			{
				$form_values_array[] = array(
						'form'		=>$inserted_id,
						'section'	=>$k_value,
						'value'		=>$v_value[0],
						'isdelete'	=>'0',
				);
			}

			$collection = new Doctrine_Collection('PatientBarthelValues');
			$collection->fromArray($form_values_array);
			$collection->save();

			return $inserted_id;
		}
		else
		{
			return false;
		}
	}

	public function update_data($post)
	{
		$u = Doctrine::getTable('PatientBarthel')->find($post['fid']);
		$u->total_score = $post['total_score'];
		$u->save();

		$form_data = PatientBarthel::get_patient_form_data($post['fid'], $post['ipid']);
		//write edited text in verlauf
		$now = date("Y-m-d H:i:s", time());
		$cust = new PatientCourse();
		$cust->ipid = $post['ipid'];
		$cust->course_date = $now;
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title = Pms_CommonData::aesEncrypt('Formular Barthel Index von '.date('d.m.Y H:i', strtotime($form_data['create_date'])).' wurde editiert');
		$cust->tabname = Pms_CommonData::aesEncrypt('barthelscore_form');
		$cust->recordid = $post['fid'];
		$cust->user_id = $post['userid'];
		$cust->done_date = $now;
		$cust->save();

		if(strlen($post['fid']))
		{
			$del_items = PatientBarthelValues::delete_barthel_values($post['fid']);

			foreach($post['bi'] as $k_value=> $v_value)
			{

				$form_values_array[] = array(
						'form'		=>$post['fid'],
						'section'	=>$k_value,
						'value'		=>$v_value[0],
						'isdelete'	=>'0',
				);
			}

			$collection = new Doctrine_Collection('PatientBarthelValues');
			$collection->fromArray($form_values_array);
			$collection->save();

			return $post['fid'];
		}
		else
		{
			return false;
		}
	}
}

?>