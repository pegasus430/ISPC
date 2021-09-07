<?php

	require_once("Pms/Form.php");

	class Application_Form_PainQuestionnaire extends Pms_Form {

		public function insert($ipid, $post)
		{
 			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
				
			$ins = new PainQuestionnaire();
			$ins->ipid = $ipid;
			
			if(strlen($post['date']) > 0)
			{
			    $form_date = date("Y-m-d",strtotime($post['date']));
			} 
			else 
			{
			    $form_date = date("Y-m-d",time());
			}
	         		
			
			if(strlen($post['time']) > 0)
			{
			    $form_time = date("H:i:s",strtotime($post['time']));
			} 
			else 
			{
			    $form_time = date("H:i:s",time());
			}
	          		
			
			$date = $form_date.' '.$form_time;
			$verlauf_date = date("d.m.Y H:i",strtotime($date)); 
			$ins->date = $date;
			
			//1
			$ins->intensity = $post['intensity'];
			//2 
			$ins->quality = implode(',', $post['quality']);
            //ISPC-2802,Elena,16.03.2021
            $ins->quality_comment = $post['quality_comment'];
			
			//3
			$ins->localisation = $post['human'];
			$ins->point_location = implode(',', $post['point_location']);
			$ins->point_location_comment = $post['point_location_comment'];

			//4
			$ins->perception = implode(',', $post['perception']);
			//5
			$ins->expression = implode(',', $post['expression']);
			$ins->expression_other = $post['expression_other'];
			//6
			$ins->relief = implode(',', $post['relief']);
			$ins->relief_comment = $post['relief_comment'];

			$ins->save();
			
			$result = $ins->id;
			
			$tab_name = "painquestionnaire_form";
			$comment = 'Schmerzbogen ('.$verlauf_date.') wurde ausgefüllt'; 
			
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->done_date = $date;
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $result;
			$cust->save();
			return $ins->id;
		}

		
		public function update($formid, $post)
		{
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			
			$upd = Doctrine::getTable('PainQuestionnaire')->find($formid);
			$old_date = $upd->date; 
			if(strlen($post['date']) > 0)
			{
			    $form_date = date("Y-m-d",strtotime($post['date']));
			}
			else
			{
			    $form_date = date("Y-m-d",time());
			}
			
				
			if(strlen($post['time']) > 0)
			{
			    $form_time = date("H:i:s",strtotime($post['time']));
			}
			else
			{
			    $form_time = date("H:i:s",time());
			}
			
				          		
			
			$date = $form_date.' '.$form_time;
			$verlauf_date = date("d.m.Y H:i",strtotime($old_date ));
			$upd->date = $date;
			
			
			//1
			$upd->intensity = $post['intensity'];
			//2 
			$upd->quality = implode(',', $post['quality']);
			
			//3
			$upd->localisation = $post['human'];
			$upd->point_location = implode(',', $post['point_location']);
			$upd->point_location_comment = $post['point_location_comment'];

			//4
			$upd->perception = implode(',', $post['perception']);
			//5
			$upd->expression = implode(',', $post['expression']);
			$upd->expression_other = $post['expression_other'];
			//6
			$upd->relief = implode(',', $post['relief']);
			$upd->relief_comment = $post['relief_comment'];

			$upd->save();
			
			
			$tab_name = "painquestionnaire_form";
			$comment = 'Schmerzbogen ('.$verlauf_date.') wurde editiert';
			
			$cust = new PatientCourse();
			$cust->ipid = $post['ipid'];
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
			$cust->recordid = $formid;
			$cust->user_id = $userid;
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $formid;
			$cust->save();
			
		}

	}

?>