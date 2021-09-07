<?php

	require_once("Pms/Form.php");

	class Application_Form_RpTermination extends Pms_Form {

		public function insert($post, $ipid, $new = false)
		{
		    
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
		
			$ins = new RpTermination();
			$ins->ipid = $ipid;
			$ins->rp_pat_last_name = $post['rp_pat_last_name'];
			$ins->rp_pat_first_name = $post['rp_pat_first_name'];
			
			if(!empty($post['rp_pat_birthd']) && date('Y', strtotime($post['rp_pat_birthd'])) != '1970')
			{
				$ins->rp_pat_birthd = date('Y-m-d H:i:s', strtotime($post['rp_pat_birthd']));
			}
			$ins->rp_pat_address = $post['rp_pat_address'];
			$ins->rp_pat_zip_city = $post['rp_pat_zip_city'];
			$ins->rp_pat_phone = $post['rp_pat_phone'];
			$ins->rp_pat_epid = $post['rp_pat_epid'];
			
			
			$ins->rp_health_insurance = $post['rp_health_insurance'];
			$ins->rp_insurance_number = $post['rp_insurance_number'];
			$ins->rp_sex = $post['rp_sex'];

			//erst
			if(!empty($post['rp_start_date_erst']) && date('Y', strtotime($post['rp_start_date_erst'])) != '1970')
			{
				$ins->rp_start_date_erst = date('Y-m-d H:i:s', strtotime($post['rp_start_date_erst']));
			}
			else
			{
				$ins->rp_start_date_erst = '0000-00-00 00:00:00';
			}
			
			if(!empty($post['rp_date_erst']) && date('Y', strtotime($post['rp_date_erst'])) != '1970')
			{
				$ins->rp_date_erst = date('Y-m-d H:i:s', strtotime($post['rp_date_erst']));
			}
			else
			{
				$ins->rp_date_erst = '0000-00-00 00:00:00';
			}

			if(count($post['rp_sapv_erst']) > 0)
			{
				$ins->rp_sapv_erst = implode(',', $post['rp_sapv_erst']);
			}
			else
			{
				$ins->rp_sapv_erst = '';
			}

			//folge
			if(!empty($post['rp_start_date_folge']) && date('Y', strtotime($post['rp_start_date_folge'])) != '1970')
			{
				$ins->rp_start_date_folge = date('Y-m-d H:i:s', strtotime($post['rp_start_date_folge']));
			}
			else
			{
				$ins->rp_start_date_folge = '0000-00-00 00:00:00';
			}
			
			if(!empty($post['rp_date_folge']) && date('Y', strtotime($post['rp_date_folge'])) != '1970')
			{
				$ins->rp_date_folge = date('Y-m-d H:i:s', strtotime($post['rp_date_folge']));
			}
			else
			{
				$ins->rp_date_folge = '0000-00-00 00:00:00';
			}

			if(count($post['rp_sapv_folge']) > 0)
			{
				$ins->rp_sapv_folge = implode(',', $post['rp_sapv_folge']);
			}
			else
			{
				$ins->rp_sapv_folge = '';
			}

			$ins->rp_vat_representative = $post['rp_vat_representative'];
			$ins->rp_info_dependant = $post['rp_info_dependant'];

			$ins->rp_sapv_team = $post['rp_sapv_team'];
			$ins->rp_hausarzt_details = $post['rp_hausarzt_details'];
			if(!empty($post['rp_doc_id']))
			{
				$ins->rp_doc_id = $post['rp_doc_id'];
			}
			$ins->rp_doctor_user = $post['rp_doctor_user'];
			$ins->rp_home_care = $post['rp_home_care'];
			
			if(!empty($post['rp_last_day_sapv']) && date('Y', strtotime($post['rp_last_day_sapv'])) != '1970')
			{
				$ins->rp_last_day_sapv = date('Y-m-d H:i:s', strtotime($post['rp_last_day_sapv']));
			}
			else
			{
				$ins->rp_last_day_sapv = '0000-00-00 00:00:00';
			}
	 
			
			
			
 
			$ins->rp_sapv_not_needed = $post['rp_sapv_not_needed'];
			$ins->rp_power_requirement_a = $post['rp_power_requirement_a'];
			$ins->rp_power_requirement_b = $post['rp_power_requirement_b'];

			
			
			$ins->rp_sapv_ended = $post['rp_sapv_ended'];
			if(date('Y', strtotime($post['rp_sapv_ended_day'])) != '1970')
			{
			    $ins->rp_sapv_ended_day = date('Y-m-d H:i:s', strtotime($post['rp_sapv_ended_day']));
			}
			else
			{
			    $ins->rp_sapv_ended_day = '0000-00-00 00:00:00';
			}
			
			$ins->rp_hospitalization = $post['rp_hospitalization'];
			if(date('Y', strtotime($post['rp_hospitalization_day'])) != '1970')
			{
			    $ins->rp_hospitalization_day = date('Y-m-d H:i:s', strtotime($post['rp_hospitalization_day']));
			}
			else
			{
			    $ins->rp_hospitalization_day = '0000-00-00 00:00:00';
			}
			
			$ins->rp_patient_death = $post['rp_patient_death'];
			if(date('Y', strtotime($post['rp_patient_death_day'])) != '1970')
			{
			    $ins->rp_patient_death_day = date('Y-m-d H:i:s', strtotime($post['rp_patient_death_day']));
			}
			else
			{
			    $ins->rp_patient_death_day = '0000-00-00 00:00:00';
			}
			$ins->rp_in_hospiz = $post['rp_in_hospiz'];
			if(date('Y', strtotime($post['rp_in_hospiz_day'])) != '1970')
			{
			    $ins->rp_in_hospiz_day = date('Y-m-d H:i:s', strtotime($post['rp_in_hospiz_day']));
			}
			else
			{
			    $ins->rp_in_hospiz_day = '0000-00-00 00:00:00';
			}
			$ins->rp_sapv_accordance = $post['rp_sapv_accordance'];
			if(date('Y', strtotime($post['rp_sapv_accordance_day'])) != '1970')
			{
			    $ins->rp_sapv_accordance_day = date('Y-m-d H:i:s', strtotime($post['rp_sapv_accordance_day']));
			}
			else
			{
			    $ins->rp_sapv_accordance_day = '0000-00-00 00:00:00';
			}
			$ins->save();


			if($ins->id > 0)
			{
				$custcourse = new PatientCourse();
				$custcourse->ipid = $ipid;
				$custcourse->course_date = date("Y-m-d H:i:s", time());
				$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				$comment = "Beendigung Formular wurde angelegt";
				$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				$custcourse->user_id = $userid;
				$custcourse->tabname = Pms_CommonData::aesEncrypt('rp_termination');
				$custcourse->save();
				
    			return $ins->id ;
			}
			else
			{
				return false;
			}
		}

		public function update($post, $ipid)
		{
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
		
			if(!empty($post['fid']))
			{
				$ins = Doctrine::getTable('RpTermination')->findOneByIdAndIpid($post['fid'], $ipid);
				
				$ins->rp_pat_last_name = $post['rp_pat_last_name'];
				$ins->rp_pat_first_name = $post['rp_pat_first_name'];

				if(!empty($post['rp_pat_birthd']) && date('Y', strtotime($post['rp_pat_birthd'])) != '1970')
				{
					$ins->rp_pat_birthd = date('Y-m-d H:i:s', strtotime($post['rp_pat_birthd']));
				}
				$ins->rp_pat_address = $post['rp_pat_address'];
				$ins->rp_pat_zip_city = $post['rp_pat_zip_city'];
				$ins->rp_pat_phone = $post['rp_pat_phone'];
				$ins->rp_pat_epid = $post['rp_pat_epid'];
			
				$ins->rp_health_insurance = $post['rp_health_insurance'];
				$ins->rp_insurance_number = $post['rp_insurance_number'];
				$ins->rp_sex = $post['rp_sex'];

				//erst
				if(!empty($post['rp_start_date_erst']) && date('Y', strtotime($post['rp_start_date_erst'])) != '1970')
				{
					$ins->rp_start_date_erst = date('Y-m-d H:i:s', strtotime($post['rp_start_date_erst']));
				}
				else
				{
					$ins->rp_start_date_erst = '0000-00-00 00:00:00';
				}
				
				if(!empty($post['rp_date_erst']) && date('Y', strtotime($post['rp_date_erst'])) != '1970')
				{
					$ins->rp_date_erst = date('Y-m-d H:i:s', strtotime($post['rp_date_erst']));
				}
				else
				{
					$ins->rp_date_erst = '0000-00-00 00:00:00';
				}

				if(count($post['rp_sapv_erst']) > 0)
				{
					$ins->rp_sapv_erst = implode(',', $post['rp_sapv_erst']);
				}
				else
				{
					$ins->rp_sapv_erst = '';
				}

				//folge
				if(!empty($post['rp_start_date_folge']) && date('Y', strtotime($post['rp_start_date_folge'])) != '1970')
				{
					$ins->rp_start_date_folge = date('Y-m-d H:i:s', strtotime($post['rp_start_date_folge']));
				}
				else
				{
					$ins->rp_start_date_folge = '0000-00-00 00:00:00';
				}
				
				if(!empty($post['rp_date_folge']) && date('Y', strtotime($post['rp_date_folge'])) != '1970')
				{
					$ins->rp_date_folge = date('Y-m-d H:i:s', strtotime($post['rp_date_folge']));
				}
				else
				{
					$ins->rp_date_folge = '0000-00-00 00:00:00';
				}

				if(count($post['rp_sapv_folge']) > 0)
				{
					$ins->rp_sapv_folge = implode(',', $post['rp_sapv_folge']);
				}
				else
				{
					$ins->rp_sapv_folge = '';
				}

				$ins->rp_vat_representative = $post['rp_vat_representative'];
				$ins->rp_info_dependant = $post['rp_info_dependant'];

				$ins->rp_sapv_team = $post['rp_sapv_team'];
				$ins->rp_hausarzt_details = $post['rp_hausarzt_details'];
				if(!empty($post['rp_doc_id']))
				{
					$ins->rp_doc_id = $post['rp_doc_id'];
				}
				$ins->rp_doctor_user = $post['rp_doctor_user'];
				$ins->rp_home_care = $post['rp_home_care'];
				
				if(!empty($post['rp_last_day_sapv']) && date('Y', strtotime($post['rp_last_day_sapv'])) != '1970')
				{
					$ins->rp_last_day_sapv = date('Y-m-d H:i:s', strtotime($post['rp_last_day_sapv']));
				}
				else
				{
					$ins->rp_last_day_sapv = '0000-00-00 00:00:00';
				}

				


				$ins->rp_sapv_not_needed = $post['rp_sapv_not_needed'];
				$ins->rp_power_requirement_a = $post['rp_power_requirement_a'];
				$ins->rp_power_requirement_b = $post['rp_power_requirement_b'];
				
					
					
				$ins->rp_sapv_ended = $post['rp_sapv_ended'];
				if(date('Y', strtotime($post['rp_sapv_ended_day'])) != '1970')
				{
				    $ins->rp_sapv_ended_day = date('Y-m-d H:i:s', strtotime($post['rp_sapv_ended_day']));
				}
				else
				{
				    $ins->rp_sapv_ended_day = '0000-00-00 00:00:00';
				}
					
				$ins->rp_hospitalization = $post['rp_hospitalization'];
				if(date('Y', strtotime($post['rp_hospitalization_day'])) != '1970')
				{
				    $ins->rp_hospitalization_day = date('Y-m-d H:i:s', strtotime($post['rp_hospitalization_day']));
				}
				else
				{
				    $ins->rp_hospitalization_day = '0000-00-00 00:00:00';
				}
					
				$ins->rp_patient_death = $post['rp_patient_death'];
				if(date('Y', strtotime($post['rp_patient_death_day'])) != '1970')
				{
				    $ins->rp_patient_death_day = date('Y-m-d H:i:s', strtotime($post['rp_patient_death_day']));
				}
				else
				{
				    $ins->rp_patient_death_day = '0000-00-00 00:00:00';
				}
				$ins->rp_in_hospiz = $post['rp_in_hospiz'];
				if(date('Y', strtotime($post['rp_in_hospiz_day'])) != '1970')
				{
				    $ins->rp_in_hospiz_day = date('Y-m-d H:i:s', strtotime($post['rp_in_hospiz_day']));
				}
				else
				{
				    $ins->rp_in_hospiz_day = '0000-00-00 00:00:00';
				}
				$ins->rp_sapv_accordance = $post['rp_sapv_accordance'];
				if(date('Y', strtotime($post['rp_sapv_accordance_day'])) != '1970')
				{
				    $ins->rp_sapv_accordance_day = date('Y-m-d H:i:s', strtotime($post['rp_sapv_accordance_day']));
				}
				else
				{
				    $ins->rp_sapv_accordance_day = '0000-00-00 00:00:00';
				}
				$ins->save();
				
			 
					//formular editat -- not completed or new
					$custcourse = new PatientCourse();
					$custcourse->ipid = $ipid;
					$custcourse->course_date = date("Y-m-d H:i:s", time());
					$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
					$comment = "Beendigung Formular wurde editiert.";
					$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
					$custcourse->user_id = $userid;
					$custcourse->save();
			}
		}
 
	}

?>