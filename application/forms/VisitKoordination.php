<?php

require_once("Pms/Form.php");

class Application_Form_VisitKoordination extends Pms_Form
{
	public function insertVisitKoordination($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);



		$stmb = new VisitKoordination();
		$stmb->ipid = $ipid;

		
		// validate visit date
		if(empty($post['visit_date']) || !Pms_Validation::isdate($post['visit_date']) ){
		    $post['visit_date'] = date('d.m.Y');
		}
		if(empty($post['visit_begin_date_h']) || strlen($post['visit_begin_date_h']) == 0){
		    $post['visit_begin_date_h'] = date('H', strtotime('-5 minutes'));
		}
			
		if(empty($post['visit_begin_date_m']) || strlen($post['visit_begin_date_m']) == 0){
		    $post['visit_begin_date_m'] = date('i', strtotime('-5 minutes'));
		}
			
		if(empty($post['visit_end_date_h']) || strlen($post['visit_end_date_h']) == 0){
		    $post['visit_end_date_h'] = date('H', strtotime('+10 minutes'));
		}
			
		if(empty($post['visit_end_date_m']) || strlen($post['visit_end_date_m']) == 0){
		    $post['visit_end_date_m'] = date('i', strtotime('+10 minutes'));
		}
		
		/*-----------------VISIT START DATE AND END DATE -------*/
		$stmb->start_date = date('Y-m-d H:i:s', strtotime($post['visit_date'].' '.$post['visit_begin_date_h'].':'.$post['visit_begin_date_m'].':00'));
		$stmb->end_date = date('Y-m-d H:i:s', strtotime($post['visit_date'].' '.$post['visit_end_date_h'].':'.$post['visit_end_date_m'].':00'));

		$visit_date =explode(".",$post['visit_date']);
		$stmb->visit_begin_date_h = $post['visit_begin_date_h'];
		$stmb->visit_begin_date_m = $post['visit_begin_date_m'];
		$stmb->visit_end_date_h = $post['visit_end_date_h'];
		$stmb->visit_end_date_m = $post['visit_end_date_m'];
		$stmb->visit_date = $visit_date[2]."-".$visit_date[1]."-".$visit_date[0];
		/*----------------------------------------------------------------------*/


		$stmb->quality=  $post['quality'];
		$stmb->fahrtzeit =  $post['fahrtzeit'];
		$stmb->fahrtstreke_km = $post['fahrtstreke_km'];
		$stmb->visit_comment = htmlspecialchars($post['visit_comment']);
		$stmb->visit_care_instructions = htmlspecialchars($post['visit_care_instructions']);
		$stmb->save();
		$result = $stmb->id;

		$done_date = date('Y-m-d H:i:s', strtotime($post['visit_date'].' '.$post['visit_begin_date_h'].':'.$post['visit_begin_date_m'].':'.date('s', time())));

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("F");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("visit_koordination_form");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->done_date = $done_date;
		$cust->done_name = Pms_CommonData::aesEncrypt("visit_koordination_form");
		$cust->done_id = $result;
		$cust->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($post['visit_begin_date_h'].":".$post['visit_begin_date_m'].' - '.$post['visit_end_date_h'].':'.$post['visit_end_date_m'].' '.$post['visit_date']);
		$cust->user_id = $userid;
		$cust->done_date = $done_date;
		$cust->done_name = Pms_CommonData::aesEncrypt("visit_koordination_form");
		$cust->done_id = $result;
		$cust->save();


		if ( !empty($post['fahrtzeit']) & $post['fahrtzeit'] != "--" )
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("K");
			$cust->course_title=Pms_CommonData::aesEncrypt("Fahrtzeit: ".$post['fahrtzeit']);
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->done_name = Pms_CommonData::aesEncrypt("visit_koordination_form");
			$cust->done_id = $result;
			$cust->save();
		}

		if ( !empty($post['visit_comment']))
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("K");
			$cust->course_title=Pms_CommonData::aesEncrypt("Sonstiges / Kommentar:".htmlspecialchars(addslashes($post['visit_comment'])));
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->done_name = Pms_CommonData::aesEncrypt("visit_koordination_form");
			$cust->done_id = $result;
			$cust->save();
		}

		if ( !empty($post['visit_care_instructions']))
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			if($clientid == '49'){ // If client HomeCare LNR
				$cust->course_type=Pms_CommonData::aesEncrypt("XP");
			} else{
				$cust->course_type=Pms_CommonData::aesEncrypt("K");
			}
			$cust->course_title=Pms_CommonData::aesEncrypt("Pflege-Anweisung:".htmlspecialchars(addslashes($post['visit_care_instructions'])));
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->done_name = Pms_CommonData::aesEncrypt("visit_koordination_form");
			$cust->done_id = $result;
			$cust->save();
		}
		if($stmb->id>0)
		{
			return true;
		}else{
			return false;
		}

	}


	public function UpdateVisitKoordination($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$koord_id = $post['koordination_id'];

		$stmb = Doctrine::getTable('VisitKoordination')->find($post['koordination_id']);

		$done_date = date('Y-m-d H:i:s', strtotime($post['visit_date'].' '.$post['visit_begin_date_h'].':'.$post['visit_begin_date_m'].':'.date('s', time())));

		/*-----------------VISIT START DATE AND END DATE -------*/
		$stmb->start_date = date('Y-m-d H:i:s', strtotime($post['visit_date'].' '.$post['visit_begin_date_h'].':'.$post['visit_begin_date_m'].':00'));
		$stmb->end_date = date('Y-m-d H:i:s', strtotime($post['visit_date'].' '.$post['visit_end_date_h'].':'.$post['visit_end_date_m'].':00'));

		$visit_date =explode(".",$post['visit_date']);
		$stmb->visit_begin_date_h = $post['visit_begin_date_h'];
		$stmb->visit_begin_date_m = $post['visit_begin_date_m'];
		$stmb->visit_end_date_h = $post['visit_end_date_h'];
		$stmb->visit_end_date_m = $post['visit_end_date_m'];
		$stmb->visit_date = $visit_date[2]."-".$visit_date[1]."-".$visit_date[0];
		/*----------------------------------------------------------------------*/

		$stmb->quality =  $post['quality'];
		$stmb->fahrtzeit =  $post['fahrtzeit'];
		$stmb->fahrtstreke_km = $post['fahrtstreke_km'];
		$stmb->visit_comment = htmlspecialchars($post['visit_comment']);
		$stmb->visit_care_instructions = htmlspecialchars($post['visit_care_instructions']);
		$stmb->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Besuch vom ".date('d.m.Y H:i',strtotime($done_date))." wurde editiert");
		$cust->recordid = $post['koordination_id'];
		$cust->user_id = $userid;
		$cust->save();

		$qa = Doctrine_Query::create()
		->update('PatientCourse')
		->set('done_date', "'" . $done_date . "'")
		->where('done_name = AES_ENCRYPT("visit_koordination_form", "' . Zend_Registry::get('salt') . '")')
		->andWhere('done_id = "' . $koord_id . '"')
		->andWhere('ipid LIKE "' . $ipid . '"');
		$qa->execute();

	}

}

?>