<?php

	require_once("Pms/Form.php");

	class Application_Form_NieRecordingReport extends Pms_Form {

		public function insert_data($post)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$done_date = date('Y-m-d H:i:s', time());

			$insert = new NieRecordingReport();
			$insert->ipid = $ipid;
			if(strlen($post['admission_date']) > '0')
			{
				$insert->admission_date = date('Y-m-d H:i:s', strtotime($post['admission_date']));
			}
			else
			{
				$insert->admission_date = "";
			}
			$insert->background_text = $post['background_text'];
			$insert->neurological_findings_text = $post['neurological_findings_text'];
			$insert->physical_findings_text = $post['physical_findings_text'];
			$insert->diagnosis_text = $post['diagnosis_text'];
			$insert->arrangements_text = $post['arrangements_text'];
			$insert->medication_text = $post['medication_text'];
			$insert->bedarfsmedication_text = $post['bedarfsmedication_text'];
			$insert->isdelete = '0';
			$insert->save();
			$result = $insert->id;

			$comment = "Aufnahmebericht wurde erstellt";

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt("nie_recording_report");
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->save();

			if($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function update_data($post, $fid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$done_date = date('Y-m-d H:i:s', time());

			$upd = Doctrine::getTable('NieRecordingReport')->findOneByIdAndIpid($fid, $ipid);
			if(strlen($post['admission_date']) > '0')
			{
				$upd->admission_date = date('Y-m-d H:i:s', strtotime($post['admission_date']));
			}
			else
			{
				$upd->admission_date = "";
			}
			$upd->background_text = $post['background_text'];
			$upd->neurological_findings_text = $post['neurological_findings_text'];
			$upd->physical_findings_text = $post['physical_findings_text'];
			$upd->diagnosis_text = $post['diagnosis_text'];
			$upd->arrangements_text = $post['arrangements_text'];
			$upd->medication_text = $post['medication_text'];
			$upd->bedarfsmedication_text = $post['bedarfsmedication_text'];
			$upd->save();


			//form edited
			$comment_edit = "Aufnahmebericht wurde editiert";

			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment_edit));
			$custcourse->user_id = $userid;
			$custcourse->recordid = $fid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt(addslashes('nie_recording_report'));
			$custcourse->save();

			return true;
		}

	}

?>