<?php

require_once("Pms/Form.php");

class Application_Form_KvnoAssessment extends Pms_Form{

	public function validate ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();

		if (!$val->isdate(end($post['completeddate'])))
		{
			$this->error_message['completed_date_error'] = $Tr->translate('completed_date_err');
			$error = 1;
		}
		if ($error == 0)
		{
			return true;
		}
		return false;
	}

	public function insertKvnoAssessment($post, $ipid, $mode, $dummy = '0')
	{
	    
// 	    print_r($post); exit;
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		$ins = new KvnoAssessment();
		$ins->ipid = $ipid;
		$ins->fammore = $post['fammore'];
		$ins->depresiv = $post['depresiv'];
		$ins->angst = $post['angst'];
		$ins->anspannung = $post['anspannung'];
		$ins->desorientier = $post['desorientier'];
		$ins->angstemore = $post['angstemore'];
		$ins->wuschemore = $post['wuschemore'];
		$ins->behandlungmore = $post['behandlungmore'];
		$ins->ressourcenmore = $post['ressourcenmore'];
		$ins->dekubitus = $post['dekubitus'];
		$ins->hilfebedarf = $post['hilfebedarf'];
		$ins->versorgung = $post['versorgung'];
		$ins->umfelds = $post['umfelds'];
		$ins->hilfsmore = $post['hilfsmore'];
		$ins->vigilanz = $post['vigilanz'];
		$ins->vigilanzmore = $post['vigilanzmore'];
		$ins->mobilitatmore = $post['mobilitatmore'];
		$ins->schmerzen = $post['schmerzen'];
		$ins->who = $post['who'];
		$ins->whomore = $post['whomore'];
		$ins->ubelkeit = $post['ubelkeit'];
		$ins->erbrechen = $post['erbrechen'];
		$ins->luftnot = $post['luftnot'];
		$ins->verstopfung = $post['verstopfung'];
		$ins->swache = $post['swache'];
		$ins->appetitmangel = $post['appetitmangel'];
		$ins->anderemore = $post['anderemore'];
		$ins->biographiemore = $post['biographiemore'];
		$ins->sapvteam = $post['sapvteam'];
		$ins->hausarzt = $post['hausarzt'];
		$ins->pflege = $post['pflege'];
		$ins->palliativ = $post['palliativ'];
		$ins->palliativpf = $post['palliativpf'];
		$ins->palliativber = $post['palliativber'];
		$ins->dienst = $post['dienst'];
		$ins->stationar = $post['stationar'];
		$ins->psycho = $post['psycho'];
		$ins->ps_nochunklar_txt = $post['ps_nochunklar_txt'];
		$ins->anzahl = $post['anzahl'];
		$ins->absprache = $post['absprache'];
		$ins->kachexie = $post['kachexie'];
		$ins->mager = $post['mager'];
		$ins->normal = $post['normal'];
		$ins->adipos = $post['adipos'];
		$ins->nromaleaktiv = $post['nromaleaktiv'];

		$ins->pverfungung = $post['pverfungung'];
		$ins->versorgevoll = $post['versorgevoll'];
		$ins->gesetzl = $post['gesetzl'];
		$ins->a_nochunklar = $post['a_nochunklar'];
		$ins->a_nochunklar_txt = $post['a_nochunklar_txt'];

		$ins->pflegebedurftig = $post['pflegebedurftig'];
		$ins->kopf = $post['kopf'];
		$ins->kopfmore = $post['kopfmore'];
		$ins->thorax = $post['thorax'];
		$ins->thoraxmore = $post['thoraxmore'];
		$ins->abdomen = $post['abdomen'];
		$ins->abdomenmore = $post['abdomenmore'];
		$ins->extremitaten = $post['extremitaten'];
		$ins->extremitatenmore = $post['extremitatenmore'];
		$ins->haut = $post['haut'];
		$ins->hautmore = $post['hautmore'];
		$ins->fotodokmore = $post['fotodokmore'];
		$ins->sonstigesmore = $post['sonstigesmore'];
		$ins->estimation = $post['estimation'];
		$ins->status = $dummy;

		$ins->reeval = date("Y-m-d H:i", strtotime($post['reeval']));
		$ins->doc_id = $post['doc_id'];
		$ins->pfl_id = $post['pfl_id'];
		$ins->billing_mode = $post['billing_mode'];
		// ISPC-2193
		$ins->care_at_admission = $post['care_at_admission'];
		$ins->partners = $post['partners'];


		$end_post_completedtime = "";
		
		if (empty($post['completed']))
		{
			//saving form for first time without iscompleted and when adding new assessment to form
			$post['completed'] = "0";
			$post['completeddate'] = "0000-00-00 00:00";
		}
		else
		{
			//saving form for first time with iscompleted
			$post['completed'] = "1";
			
			$post['completeddate'] = date("Y-m-d", strtotime(end($post['completeddate'])));
			$end_post_completedtime = end($post['completedtime']);
			
			if(!empty($end_post_completedtime))
			{
			    $post['completeddate'] = $post['completeddate'].' '.end($post['completedtime']).':00';
			}
		}
		

		//saving start date
		if(empty($post['startdate']))
		{
	   	   $post['start_date'] = "0000-00-00 00:00:00";
		
		}
		else
		{
		    if(strlen(end($post['startdate'])) > 0 ){
                $post['start_date'] = date("Y-m-d", strtotime(end($post['startdate'])));
    		    $end_post_starttime = end($post['starttime']);
    		
        		if(!empty($end_post_starttime ))
        		{
        		    $post['start_date'] = $post['start_date'].' '.end($post['starttime']).':00';
        		}
		    }
		    else
		    {
                $post['start_date'] = "0000-00-00 00:00:00";
		    } 
		    
		}
		
		$ins->iscompleted = $post['completed'];
		$ins->completed_date = $post['completeddate'];
		$ins->start_date = $post['start_date'];

		$ins->save();

		if($post['completed'] == '0')
		{
			if ($mode != "live" && empty($post['btnnewassessment']))
			{
				$custcourse = new PatientCourse();
				$custcourse->ipid = $ipid;
				$custcourse->course_date = date("Y-m-d H:i:s", time());
				$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				$comment = "Assessment Formular wurde angelegt";
				$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				$custcourse->user_id = $userid;
				$custcourse->tabname = Pms_CommonData::aesEncrypt('new_kvno_assesment');
				$custcourse->save();
			}
			else if($mode != "live")
			{
				$custcourse = new PatientCourse();
				$custcourse->ipid = $ipid;
				$custcourse->course_date = date("Y-m-d H:i:s", time());
				$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				$comment = "Neues Assessment wurde gestartet.";
				$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				$custcourse->user_id = $userid;
				$custcourse->tabname = Pms_CommonData::aesEncrypt('new_kvno_assesment');
				$custcourse->save();
			}
		}
		else
		{

		}
		if ($ins->id > 0)
		{
			return $ins->id;
		}
		else
		{
			return false;
		}
	}


	public function updateKvnoAssessment($post, $ipid, $mode)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$last = $post['kvno_assessment_id'];

		if(!empty($last)){
			$ins = Doctrine::getTable('KvnoAssessment')->findOneByIdAndIpid($last, $ipid);
			$ins->fammore = $post['fammore'];
			$ins->depresiv = $post['depresiv'];
			$ins->angst = $post['angst'];
			$ins->anspannung = $post['anspannung'];
			$ins->desorientier = $post['desorientier'];
			$ins->angstemore = $post['angstemore'];
			$ins->wuschemore = $post['wuschemore'];
			$ins->behandlungmore = $post['behandlungmore'];
			$ins->ressourcenmore = $post['ressourcenmore'];
			$ins->dekubitus = $post['dekubitus'];
			$ins->hilfebedarf = $post['hilfebedarf'];
			$ins->versorgung = $post['versorgung'];
			$ins->umfelds = $post['umfelds'];
			$ins->hilfsmore = $post['hilfsmore'];
			$ins->vigilanz = $post['vigilanz'];
			$ins->vigilanzmore = $post['vigilanzmore'];
			$ins->mobilitatmore = $post['mobilitatmore'];
			$ins->schmerzen = $post['schmerzen'];
			$ins->who = $post['who'];
			$ins->whomore = $post['whomore'];
			$ins->ubelkeit = $post['ubelkeit'];
			$ins->erbrechen = $post['erbrechen'];
			$ins->luftnot = $post['luftnot'];
			$ins->verstopfung = $post['verstopfung'];
			$ins->swache = $post['swache'];
			$ins->appetitmangel = $post['appetitmangel'];
			$ins->anderemore = $post['anderemore'];
			$ins->biographiemore = $post['biographiemore'];
			$ins->sapvteam = $post['sapvteam'];
			$ins->hausarzt = $post['hausarzt'];
			$ins->pflege = $post['pflege'];
			$ins->palliativ = $post['palliativ'];
			$ins->palliativpf = $post['palliativpf'];
			$ins->palliativber = $post['palliativber'];
			$ins->dienst = $post['dienst'];
			$ins->stationar = $post['stationar'];
			$ins->psycho = $post['psycho'];
			$ins->ps_nochunklar_txt = $post['ps_nochunklar_txt'];
			$ins->anzahl = $post['anzahl'];
			$ins->absprache = $post['absprache'];
			$ins->kachexie = $post['kachexie'];
			$ins->mager = $post['mager'];
			$ins->normal = $post['normal'];
			$ins->adipos = $post['adipos'];
			$ins->nromaleaktiv = $post['nromaleaktiv'];

			$ins->pverfungung = $post['pverfungung'];
			$ins->versorgevoll = $post['versorgevoll'];
			$ins->gesetzl = $post['gesetzl'];
			$ins->a_nochunklar = $post['a_nochunklar'];
			$ins->a_nochunklar_txt = $post['a_nochunklar_txt'];

			$ins->pflegebedurftig = $post['pflegebedurftig'];
			$ins->kopf = $post['kopf'];
			$ins->kopfmore = $post['kopfmore'];
			$ins->thorax = $post['thorax'];
			$ins->thoraxmore = $post['thoraxmore'];
			$ins->abdomen = $post['abdomen'];
			$ins->abdomenmore = $post['abdomenmore'];
			$ins->extremitaten = $post['extremitaten'];
			$ins->extremitatenmore = $post['extremitatenmore'];
			$ins->haut = $post['haut'];
			$ins->hautmore = $post['hautmore'];
			$ins->fotodokmore = $post['fotodokmore'];
			$ins->sonstigesmore = $post['sonstigesmore'];
			$ins->estimation = $post['estimation'];

			$ins->reeval = date("Y-m-d H:i", strtotime($post['reeval']));
			$ins->doc_id = $post['doc_id'];
			$ins->pfl_id = $post['pfl_id'];
			$ins->billing_mode = $post['billing_mode'];
			// ISPC-2193
			$ins->care_at_admission = $post['care_at_admission'];
			$ins->partners = $post['partners'];
			
			if(!empty($post['startdate'][$last])) //update last completed date even if completed is disabled
			{
			     //TODO-3032 Lore 26.03.2020
			    if(!empty($post['starttime'][$last])){
			        $start_date_time[$last] = $post['startdate'][$last].' '.$post['starttime'][$last].':00';
			    }else {
			        $start_date_time[$last] = $post['startdate'][$last].' 00:00';
			    }
			    
		          //$start_date_time[$last] = $post['startdate'][$last].' '.$post['starttime'][$last].':00';
		          $ins->start_date = date('Y-m-d H:i:s', strtotime($start_date_time[$last]));
			} else{
    		    $ins->start_date = "0000-00-00 00:00:00";
			}
			
			$ins->save();

			if($mode != "live")
			{
				if(count($post['completed'])>0)
				{
					foreach($post['completed'] as $k_assessment => $v_assessment) //update non disabled iscomplete assessment dates
					{
						if($v_assessment['completed'] == '1' && $post['completeddate'][$k_assessment] && $k_assessment != '0')
						{
						    $completed_date_time[$k_assessment] = $post['completeddate'][$k_assessment].' '.$post['completedtime'][$k_assessment].':00';

						    $upd = Doctrine::getTable('KvnoAssessment')->findOneById($k_assessment);
							$upd->iscompleted = '1';
							$upd->completed_date = date('Y-m-d H:i:s', strtotime($completed_date_time[$k_assessment]));
							$upd->save();
						}
					}
				} else {
				    //TODO-3933 Lore 19.03.2021
				    //formular editat
				    $custcourse = new PatientCourse();
				    $custcourse->ipid = $ipid;
				    $custcourse->course_date = date("Y-m-d H:i:s", time());
				    $custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				    $comment = "Assessment Formular wurde editiert.";
				    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				    $custcourse->user_id = $userid;
				    $custcourse->save();
				}
				
				if(!empty($post['completeddate'][$last])) //update last completed date even if completed is disabled
				{
					$upd = Doctrine::getTable('KvnoAssessment')->findOneById($last);
					
					$completed_date_time[$last] = $post['completeddate'][$last].' '.$post['completedtime'][$last].':00';
					
					$upd->completed_date = date('Y-m-d H:i:s', strtotime($completed_date_time[$last]));
					$upd->save();
				}

/* 				//formular editat
				$custcourse = new PatientCourse();
				$custcourse->ipid = $ipid;
				$custcourse->course_date = date("Y-m-d H:i:s", time());
				$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				$comment = "Assessment Formular wurde editiert.";
				$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				$custcourse->user_id = $userid;
				$custcourse->save(); */
			}
			
		    return $last;
		}
		else
		{
		    return false;
		}
	}
}
?>