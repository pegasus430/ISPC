<?php
require_once("Pms/Form.php");
class Application_Form_PatientNraapv extends Pms_Form
{

	public function validatedata ( $post )
	{

	}
	
	public function insertdata($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		
		$ins = new PatientNraapv();
		$ins->ipid = $post['ipid'];
		$ins->client_fax = $post['client_fax'];
		$ins->patient_name = Pms_CommonData::aesEncrypt($post['patient_name']);
		$ins->patient_phone = Pms_CommonData::aesEncrypt($post['patient_phone']);
		$ins->patient_contactphone = $post['patient_contactphone'];
		$ins->qpa_name = $post['qpa_name'];
		$ins->qpa_phone = $post['qpa_phone'];
		$ins->qpa_fax = $post['qpa_fax'];
		$ins->fdoc_name = $post['fdoc_name'];
		$ins->fdoc_phone = $post['fdoc_phone'];
		$ins->fdoc_fax = $post['fdoc_fax'];
		$ins->pflege_name = $post['pflege_name'];
		$ins->pflege_phone = $post['pflege_phone'];
		$ins->pflege_fax = $post['pflege_fax'];
		$ins->other_info = $post['other_info'];
		$ins->save();
		
		$id = $ins->id;
		
		if($id > 0)
		{
			$custcourse = new PatientCourse();
			$custcourse->ipid = $post['ipid'];
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "Nordrhein AAPV - Formular  hinzugefügt";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('nraapv_form');
			$custcourse->recordid = $id;
			$custcourse->done_name = Pms_CommonData::aesEncrypt('nraapv_form');
			$custcourse->done_id = $id;
			$custcourse->save();
			
			return $id;
		}
		else 
		{
			return false;
		}
	}
	
	public function updatedata($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		
		$upd = Doctrine::getTable('PatientNraapv')->findOneById($post['form_id']);
		$upd->ipid = $post['ipid'];
		$upd->client_fax = $post['client_fax'];
		$upd->patient_name = Pms_CommonData::aesEncrypt($post['patient_name']);
		$upd->patient_phone = Pms_CommonData::aesEncrypt($post['patient_phone']);
		$upd->patient_contactphone = $post['patient_contactphone'];
		$upd->qpa_name = $post['qpa_name'];
		$upd->qpa_phone = $post['qpa_phone'];
		$upd->qpa_fax = $post['qpa_fax'];
		$upd->fdoc_name = $post['fdoc_name'];
		$upd->fdoc_phone = $post['fdoc_phone'];
		$upd->fdoc_fax = $post['fdoc_fax'];
		$upd->pflege_name = $post['pflege_name'];
		$upd->pflege_phone = $post['pflege_phone'];
		$upd->pflege_fax = $post['pflege_fax'];
		$upd->other_info = $post['other_info'];
		$upd->save();
		
		$custcourse = new PatientCourse();
		$custcourse->ipid = $post['ipid'];
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
		$comment = "Nordrhein AAPV - Formular  wurde editiert";
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->tabname = Pms_CommonData::aesEncrypt('nraapv_form');
		$custcourse->recordid = $post['form_id'];
		$custcourse->done_name = Pms_CommonData::aesEncrypt('nraapv_form');
		$custcourse->done_id = $post['form_id'];
		$custcourse->save();
	}
	public function reloaddata($reset_ipid)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;		
	
		$rst = Doctrine::getTable('PatientNraapv')->findOneById($_POST['form_id']);
		$rst->isdelete = 1;
		$rst->save();
		
		$custcourse = new PatientCourse();
		$custcourse->ipid = $reset_ipid;
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
		$comment = "Nordrhein AAPV - Formular wurde neu geladen";
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->tabname = Pms_CommonData::aesEncrypt('nraapv_form');
		$custcourse->recordid = $_POST['form_id'];
		$custcourse->done_name = Pms_CommonData::aesEncrypt('nraapv_form');
		$custcourse->done_id = $_POST['form_id'];
		$custcourse->save();
	}
}
?>