<?php

require_once("Pms/Form.php");

class Application_Form_Feststellung extends Pms_Form{

	public function insertFeststellung($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$stmb = new Feststellung();
		$stmb->ipid = $ipid;
		$stmb->grund = $post['grund'];
		$stmb->grund_txt = $post['grund_txt'];
		$stmb->palliativzentrum = $post['palliativzentrum'];
		$stmb->institution = $post['institution'];
		$stmb->verordnung_durch = $post['verordnung_durch'];
		$stmb->f_krankenhaus = $post['f_krankenhaus'];
		$stmb->f_address = $post['f_address'];
		$stmb->einmalige = $post['einmalige'];

		$stmb->save();

		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("feststellung");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->save();



		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}

	public function UpdateFeststellung($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;


		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$stmb = Doctrine::getTable('Feststellung')->find($post['feststellung_id']);

		$stmb->grund = $post['grund'];
		$stmb->grund_txt = $post['grund_txt'];
		$stmb->palliativzentrum = $post['palliativzentrum'];
		$stmb->institution = $post['institution'];
		$stmb->verordnung_durch = $post['verordnung_durch'];
		$stmb->f_krankenhaus = $post['f_krankenhaus'];
		$stmb->f_address = $post['f_address'];
		$stmb->einmalige = $post['einmalige'];

		$stmb->save();


		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Feststellung des Nichtvorliegens der Teilnahmevorraussetzung  wurde editiert");
		$cust->recordid = $post['feststellung_id'];
		$cust->user_id = $userid;
		$cust->save();
	}

}

?>