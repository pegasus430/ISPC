<?php

require_once("Pms/Form.php");

class Application_Form_Reassessment extends Pms_Form{

	public function insertReassessment($post, $ipid, $mode){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		$Q = Doctrine_Query::create()
		->delete('Reassessment')
		->where("ipid='".$ipid."'");
		$Q->execute();

		$ins = new Reassessment();
		$ins->ipid = $ipid;
		$ins->depresiv = $post['depresiv'];
		$ins->angst = $post['angst'];
		$ins->anspannung = $post['anspannung'];
		$ins->desorientier = $post['desorientier'];
		$ins->dekubitus = $post['dekubitus'];
		$ins->hilfebedarf = $post['hilfebedarf'];
		$ins->versorgung = $post['versorgung'];
		$ins->umfelds = $post['umfelds'];
		$ins->vigilanz = $post['vigilanz'];
		$ins->schmerzen = $post['schmerzen'];
		$ins->ubelkeit = $post['ubelkeit'];
		$ins->erbrechen = $post['erbrechen'];
		$ins->luftnot = $post['luftnot'];
		$ins->verstopfung = $post['verstopfung'];
		$ins->swache = $post['swache'];
		$ins->appetitmangel = $post['appetitmangel'];
		$ins->indic_sapv = $post['indic_sapv'];
		$ins->indic_sapv_txt = $post['indic_sapv_txt'];
		$ins->comment = $post['comment'];
		$fill_date = $post['fill_date'];
		$post['fill_date'] = date("Y-m-d H:i", strtotime($post['fill_date']));
		$ins->fill_date = $post['fill_date'];

		$ins->save();
		$custcourse = new PatientCourse();
		$custcourse->ipid = $ipid;
		$custcourse->course_date = date("Y-m-d H:i:s",time());
		$custcourse->course_type=Pms_CommonData::aesEncrypt("K");
		$comment = "Re-Assessment Formular wurde angelegt ";
		$custcourse->course_title=Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->tabname = Pms_CommonData::aesEncrypt('reassesment');
		$custcourse->save();

		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}
}

?>