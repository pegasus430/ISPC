<?php
require_once("Pms/Form.php");
class Application_Form_PatientDeath extends Pms_Form
{

	public function validate ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();
		if (!$val->isstring($post['death_date']))
		{
			$this->error_message['death_date'] = $Tr->translate('err_dischargedate');
			$error = 1;
		}

		if (strtotime($post['death_date']) > strtotime(date("d.m.Y", time())))
		{
			$this->error_message['death_date'] = $Tr->translate('err_dischargedatefuture');
			$error = 5;
		}
		if (strtotime($post['death_date']) < strtotime($post['last_dis']))
		{
			$this->error_message['death_date'] = $Tr->translate('err_deathdatedischarge');
			$error = 5;
		}

		if ($error == 0)
		{
			return true;
		}

		return false;
	}

	public function InsertData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		if (strlen($post['death_date']) > 0)
		{
			$bd_date = explode(".", $post['death_date']);
			$ddate = $bd_date[2] . "-" . $bd_date[1] . "-" . $bd_date[0] . " " . $post['death_rec_timeh'] . ":" . $post['death_rec_timem'] . ":00";
			$course_ddate = $bd_date[0] . "." . $bd_date[1] . "." . $bd_date[2] . " " . $post['death_rec_timeh'] . ":" . $post['death_rec_timem'] . "";
		}

		$dism = Doctrine::getTable('PatientDeath')->findByIpidAndIsdelete($ipid, "0");

		if ($dism)
		{
			$dmarr = $dism->toArray();
		}


		if (count($dmarr) > 0)
		{
			$dis = Doctrine::getTable('PatientDeath')->find($dmarr[0]['id']);
			$dis->death_date = $ddate;
			$dis->ipid = $ipid;
			$dis->save();
			$result = $dmarr[0]['id'];

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt('Das Sterbedatum wurde editiert :' . $course_ddate);
			$cust->tabname = Pms_CommonData::aesEncrypt("patient_death");
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->save();

			//sent mesage to all asigned users
			$mess = new Messages();
			$mess->dead_notification($ipid);
		}
		else
		{
			$cust = new PatientDeath();
			$cust->death_date = $ddate;
			$cust->ipid = $ipid;
			$cust->save();
			$result = $cust->id;
			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			$cust->traffic_status = 0;
			$cust->save();


			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt('Patient verstorben ' . $course_ddate);
			$cust->tabname = Pms_CommonData::aesEncrypt("patient_death");
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->save();

			//sent mesage to all asigned users
			$mess = new Messages();
			$mess->dead_notification($ipid);

			//write verlauf of other patient
			$patients_linked = new PatientsLinked();
			$linked_patients = $patients_linked->get_related_patients($ipid);

			$linked_ipids[] = '9999999';
			if ($linked_patients)
			{
				$linked_ipids[] = $ipid;
				foreach ($linked_patients as $k_link => $v_link)
				{
					$linked_ipids[] = $v_link['target'];
					$linked_ipids[] = $v_link['source'];
				}
			}


			foreach ($linked_ipids as $k_ipid => $v_ipid)
			{
				if ($v_ipid != $ipid && $v_ipid != '9999999' && !empty($v_ipid))
				{
					$cust = new PatientCourse();
					$cust->ipid = $v_ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt('Patient verstorben ' . $course_ddate);
					$cust->tabname = Pms_CommonData::aesEncrypt("patient_death");
					$cust->source_ipid = $ipid;
					$cust->recordid = $result;
					$cust->user_id = $userid;
					$cust->save();
				}
			}
		}
		return $cust;
	}

}
?>