<?php
require_once("Pms/Form.php");
class Application_Form_Anlage5part1 extends Pms_Form
{

	public function validate ( $post )
	{

	}

	public function InsertData ( $post, $ipid, $clientid, $status)
	{
		if (strlen($post['date']) > '0' && strlen($post['time']) > '0')
		{
			$tmp_date = $post['date'] . ' ' . $post['time'] . ":00";
			$date = date('Y-m-d H:i:s', strtotime($tmp_date));
		}
		else
		{
			$date = '0000-00-00 00:00:00';
		}

		if (strlen($post['living_will_more']) > 0)
		{
			$living_will_date = date('Y-m-d H:i:s', strtotime($post['living_will_more']));
		}
		else
		{
			$living_will_date = '0000-00-00 00:00:00';
		}

		//checkboxes
		$post['do_hearth'] = ($post['do_hearth'] == '1' ? '1' : '0');
		$post['do_neurologically'] = ($post['do_neurologically'] == '1' ? '1' : '0');
		$post['do_psychiatrically'] = ($post['do_psychiatrically'] == '1' ? '1' : '0');
		$post['do_lungs'] = ($post['do_lungs'] == '1' ? '1' : '0');
		$post['do_liver'] = ($post['do_liver'] == '1' ? '1' : '0');
		$post['do_kidney'] = ($post['do_kidney'] == '1' ? '1' : '0');
		$post['do_other'] = ($post['do_other'] == '1' ? '1' : '0');
		$post['ss_unknown'] = ($post['ss_unknown'] == '1' ? '1' : '0');
		$post['ss_living_alone'] = ($post['ss_living_alone'] == '1' ? '1' : '0');
		$post['ss_living_partner'] = ($post['ss_living_partner'] == '1' ? '1' : '0');
		$post['ss_no_support_partner'] = ($post['ss_no_support_partner'] == '1' ? '1' : '0');
		$post['ss_nurse_exists'] = ($post['ss_nurse_exists'] == '1' ? '1' : '0');

		//Save main form
		$reset_form = $this->reset($ipid);

		$insert = new Anlage5Part1();
		$insert->clientid = $clientid;
		$insert->ipid = $ipid;
		$insert->date_time = $date;
		$insert->team_name = $post['team_name'];
		$insert->user_details = $post['user_details'];
		$insert->patient_name = $post['patient_name'];
		$insert->hi_number_dob = $post['hi_number_dob'];
		$insert->disease_base = $post['disease_base'];
		$insert->general_condition = $post['general_condition'];
		$insert->living_will = $post['living_will'];
		$insert->living_will_more = $living_will_date;
		$insert->do_hearth = $post['do_hearth'];
		$insert->do_neurologically = $post['do_neurologically'];
		$insert->do_psychiatrically = $post['do_psychiatrically'];
		$insert->do_lungs = $post['do_lungs'];
		$insert->do_liver = $post['do_liver'];
		$insert->do_kidney = $post['do_kidney'];
		$insert->do_other = $post['do_other'];
		$insert->do_other_more = $post['do_other_more'];
		$insert->ss_unknown = $post['ss_unknown'];
		$insert->ss_living_alone = $post['ss_living_alone'];
		$insert->ss_living_partner = $post['ss_living_partner'];
		$insert->ss_no_support_partner = $post['ss_no_support_partner'];
		$insert->ss_nurse_exists = $post['ss_nurse_exists'];
		$insert->isdelete = '0';
		$insert->save();

		$inserted_id = $insert->id;

		if ($inserted_id)
		{
			// write in patient Course
			if($status == 'insert'){
				$comment = "Anlage 5 (Teil 1) Formular wurde angelegt.";
			} elseif($status == 'update'){
				$comment = "Anlage 5 (Teil 1) wurde editiert.";
			}
				
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("F");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt("anlage5_teil1");
			$cust->recordid = $inserted_id;
			$cust->done_date = $date;
			$cust->done_name = Pms_CommonData::aesEncrypt("anlage5_teil1");
			$cust->done_id = $inserted_id;
			$cust->save();
				
				
			//Save current problems table
			$anlage5p1 = new Anlage5Part1();
			$current_problems = $anlage5p1->get_current_problems();

			foreach ($current_problems as $k_problem => $v_problem)
			{
				$main_problem[$k_problem] = ($post['main_problem'][$k_problem] == '1' ? '1' : '0');
				$option[$k_problem] = (strlen($post['option'][$k_problem]) > '0' ? $post['option'][$k_problem] : '0');

				$current_problems_data[] = array(
						'ipid' => $ipid,
						'formid' => $inserted_id,
						'symptom' => $k_problem,
						'main_reason' => $main_problem[$k_problem],
						'value' => $option[$k_problem],
						'isdelete' => "0"
				);
			}

			$reset_cp = $this->reset_current_problems($ipid);

			$collection = new Doctrine_Collection('Anlage5CurrentProblems');
			$collection->fromArray($current_problems_data);
			$collection->save();


			//Save pretreatment table
			$pretreatment_problems = $anlage5p1->get_pretreatment_problems();

			foreach ($pretreatment_problems as $k_pre_problem => $v_pre_problem)
			{
				$pr_option[$k_pre_problem] = (strlen($post['pr_option'][$k_pre_problem]) > '0' ? $post['pr_option'][$k_pre_problem] : '0');
				$pretreatment_data[] = array(
						'ipid' => $ipid,
						'formid' => $inserted_id,
						'symptom' => $k_pre_problem,
						'value' => $pr_option[$k_pre_problem],
						'isdelete' => '0'
				);
			}

			$reset_pr = $this->reset_pretreatment_problems($ipid);

			$collection = new Doctrine_Collection('Anlage5Pretreatment');
			$collection->fromArray($pretreatment_data);
			$collection->save();
		}
		else
		{
			return false;
		}
	}

	public function reset ( $ipid )
	{
		$update = Doctrine_Query::create()
		->update('Anlage5Part1')
		->set('isdelete', 1)
		->where('ipid ="' . $ipid . '"')
		->andWhere('isdelete = "0"');
		$update_res = $update->execute();
	}

	public function reset_current_problems ( $ipid )
	{
		$update = Doctrine_Query::create()
		->update('Anlage5CurrentProblems')
		->set('isdelete', 1)
		->where('ipid ="' . $ipid . '"')
		->andWhere('isdelete = "0"');
		$update_res = $update->execute();
	}

	public function reset_pretreatment_problems($ipid)
	{
		$update = Doctrine_Query::create()
		->update('Anlage5Pretreatment')
		->set('isdelete', 1)
		->where('ipid ="' . $ipid . '"')
		->andWhere('isdelete = "0"');
		$update_res = $update->execute();
	}

}
?>