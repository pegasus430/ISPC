<?php
require_once("Pms/Form.php");
class Application_Form_Munster4 extends Pms_Form
{
	public function insert_data( $ipid, $post )
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
		$post['hauptleistung_datum'] = (strlen($post['hauptleistung_datum']) > '0' ? date('Y-m-d H:i:s', strtotime($post['hauptleistung_datum'])) : '0000-00-00 00:00:00');
		$mst= new Munster4();
		 
		$mst->ipid = $ipid;
		
		$mst->mittellung_von_krankheiten = $post['mittellung_von_krankheiten'];
		$mst->mittellung_sonstiger_schaden_txt = $post['mittellung_sonstiger_schaden_txt'];
		$mst->hauptleistung_krankenhaus = $post['hauptleistung_krankenhaus'];
		$mst->krankenhaus_behandlungsdaten_txt = $post['krankenhaus_behandlungsdaten_txt'];
		$mst->hauptleistung_datum = $post['hauptleistung_datum'];
		$mst->ambulante_op_behandlung = $post['ambulante_op_behandlung'];
		$mst->ambulante_op_behandlung_txt = $post['ambulante_op_behandlung_txt'];
		$mst->ambulante_behandlung = $post['ambulante_behandlung'];
		$mst->ambulante_behandlung_txt = $post['ambulante_behandlung_txt'];
		$mst->begrundung_des_ausnahmefalls = $post['begrundung_des_ausnahmefalls'];
		$mst->begrundung_des_ausnahmefalls_txt = $post['begrundung_des_ausnahmefalls_txt'];
		$mst->dauerhafte_mobilitat = $post['dauerhafte_mobilitat'];
		$mst->dauerhafte_mobilitat_txt = $post['dauerhafte_mobilitat_txt'];
		$mst->voraussichtliche_behandlungsfrequenz_woche = $post['voraussichtliche_behandlungsfrequenz_woche'];
		$mst->voraussichtliche_behandlungsfrequenz_monate = $post['voraussichtliche_behandlungsfrequenz_monate'];
		$mst->voraussichtliche_behandlungsdauer_txt = $post['voraussichtliche_behandlungsdauer_txt'];
		$mst->zeitraum_serienverordnung_txt = $post['zeitraum_serienverordnung_txt'];
		$mst->beforderungsmittel = $post['beforderungsmittel'];
		$mst->beforderungsmittel_txt = $post['beforderungsmittel_txt'];
		$mst->begrundung_beforderungsmittels_txt = $post['begrundung_beforderungsmittels_txt'];
		$mst->medizinisch_technische = $post['medizinisch_technische'];
		$mst->medizinisch_technische_txt = $post['medizinisch_technische_txt'];
		$mst->wohnung = $post['wohnung'];
		$mst->arztpraxis = $post['arztpraxis'];
		$mst->krankenhaus = $post['krankenhaus'];
		$mst->andere_beford = $post['andere_beford'];
		$mst->beforderungswege_txt = $post['beforderungswege_txt'];
		$mst->hinfahrt = join(",", $post['hinfahrt']);
		$mst->wartezeit_txt = $post['wartezeit_txt'];
		$mst->gemeinschaftsfahrt_txt =$post['gemeinschaftsfahrt_txt'];
		$mst->medizinisch_fachliche = $post['medizinisch_fachliche'];
		$mst->medizinisch_fachliche_folgende_txt = $post['medizinisch_fachliche_folgende_txt'];
		$mst->save();
		
		$id = $mst->id;
		
		if($id)
		{
			$comment = "Formular Muster 4 hinzugefügt.";
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("F");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->user_id = $logininfo->userid;
			$cust->done_name = Pms_CommonData::aesEncrypt('muster_4_form');
			$cust->tabname = Pms_CommonData::aesEncrypt('muster_4_form');
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_id = $id;
			$cust->recordid = $id;
			$cust->save();
			
			return $id;
			}
			else
			{
				return false;
			}	
		
	}
	
	public function update_data($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$post['hauptleistung_datum'] = (strlen($post['hauptleistung_datum']) > '0' ? date('Y-m-d H:i:s', strtotime($post['hauptleistung_datum'])) : '0000-00-00 00:00:00');
		
		$mst = Doctrine::getTable('Munster4')->findOneById($post['saved_id']);
		
		$mst->mittellung_von_krankheiten = $post['mittellung_von_krankheiten'];
		$mst->mittellung_sonstiger_schaden_txt = $post['mittellung_sonstiger_schaden_txt'];
		$mst->hauptleistung_krankenhaus = $post['hauptleistung_krankenhaus'];
		$mst->krankenhaus_behandlungsdaten_txt = $post['krankenhaus_behandlungsdaten_txt'];
		$mst->hauptleistung_datum = $post['hauptleistung_datum'];
		$mst->ambulante_op_behandlung = $post['ambulante_op_behandlung'];
		$mst->ambulante_op_behandlung_txt = $post['ambulante_op_behandlung_txt'];
		$mst->ambulante_behandlung = $post['ambulante_behandlung'];
		$mst->ambulante_behandlung_txt = $post['ambulante_behandlung_txt'];
		$mst->begrundung_des_ausnahmefalls = $post['begrundung_des_ausnahmefalls'];
		$mst->begrundung_des_ausnahmefalls_txt = $post['begrundung_des_ausnahmefalls_txt'];
		$mst->dauerhafte_mobilitat = $post['dauerhafte_mobilitat'];
		$mst->dauerhafte_mobilitat_txt = $post['dauerhafte_mobilitat_txt'];
		$mst->voraussichtliche_behandlungsfrequenz_woche = $post['voraussichtliche_behandlungsfrequenz_woche'];
		$mst->voraussichtliche_behandlungsfrequenz_monate = $post['voraussichtliche_behandlungsfrequenz_monate'];
		$mst->voraussichtliche_behandlungsdauer_txt = $post['voraussichtliche_behandlungsdauer_txt'];
		$mst->zeitraum_serienverordnung_txt = $post['zeitraum_serienverordnung_txt'];
		$mst->beforderungsmittel = $post['beforderungsmittel'];
		$mst->beforderungsmittel_txt = $post['beforderungsmittel_txt'];
		$mst->begrundung_beforderungsmittels_txt = $post['begrundung_beforderungsmittels_txt'];
		$mst->medizinisch_technische = $post['medizinisch_technische'];
		$mst->medizinisch_technische_txt = $post['medizinisch_technische_txt'];
		$mst->wohnung = $post['wohnung'];
		$mst->arztpraxis = $post['arztpraxis'];
		$mst->krankenhaus = $post['krankenhaus'];
		$mst->andere_beford = $post['andere_beford'];
		$mst->beforderungswege_txt = $post['beforderungswege_txt'];
		$mst->hinfahrt = implode(",", $post['hinfahrt']);
		$mst->wartezeit_txt = $post['wartezeit_txt'];
		$mst->gemeinschaftsfahrt_txt =$post['gemeinschaftsfahrt_txt'];
		$mst->medizinisch_fachliche = $post['medizinisch_fachliche'];
		$mst->medizinisch_fachliche_folgende_txt = $post['medizinisch_fachliche_folgende_txt'];
		$mst->save();
		
		$comment = "Formular Muster 4 wurde editiert.";
		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("F");
		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
		$cust->user_id = $logininfo->userid;
		$cust->done_name = Pms_CommonData::aesEncrypt('muster_4_form');
		$cust->tabname = Pms_CommonData::aesEncrypt('muster_4_form');
		$cust->done_date = date("Y-m-d H:i:s", time());
		$cust->done_id = $post['saved_id'];
		$cust->recordid = $post['saved_id'];
		$cust->save();
			
		return true;
	}
}