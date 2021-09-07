<?php
//require_once("Pms/Form.php");

class Application_Form_SisStationary extends Pms_Form{
	
	private $triggerformid = PatientSavoir::TRIGGER_FORMID;
	private $triggerformname = PatientSavoir::TRIGGER_FORMNAME;

	/*public function insert($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$ins = new SisAmbulant();
		$ins->ipid = $ipid;
		$ins->clientid = $clientid;
		$ins->dependent_person = $post['dependent_person'];
		$ins->save();
		$id = $ins->id;

		if($id > 0)
		{
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "SIS - ambulant Formular  hinzugefügt";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('ambulant_sis_form');
			$custcourse->recordid = $id;
			$custcourse->done_name = Pms_CommonData::aesEncrypt('ambulant_sis_form');
			$custcourse->done_id = $id;
			$custcourse->save();
				
			return $id;
		}
		else
			
		{
			return false;
		}
	}
	
	public function update($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
	
		$upd = Doctrine::getTable('SisAmbulant')->findOneById($post['form_id']);
		$upd->dependent_person = $post['dependent_person'];
		$upd->save();
		
		
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "SIS - ambulant Formular  wurde editiert";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('ambulant_sis_form');
			$custcourse->recordid = $post['form_id'];
			$custcourse->done_name = Pms_CommonData::aesEncrypt('ambulant_sis_form');
			$custcourse->done_id = $post['saved_id'];
			$custcourse->save();
		
	}*/
	
	public function save_form_sisstationary($ipid = null, array $data = array())
	{
		if (empty($ipid)) {
			throw new Exception('Contact Admin, formular cannot be saved.', 0);
		}
		//print_r($data); exit;
		$sisstationary = null;
		 
		//formular will be saved first so we have a id
		if ( ! empty($data)) {
			
			$sisstat['ipid'] = $data['ipid'];
			$sisstat['clientid'] = $data['clientid'];
			$sisstat['dependent_person'] = $data['dependent_person'];
			$sisstat['id'] = $data['form_id'];
			$entitysiss  = new SisStationary();
			$sisstationary =  $entitysiss->findOrCreateOneBy('id', $data['form_id'], $sisstat);
			 
			if ( ! $sisstationary->id) {
	
				throw new Exception('Contact Admin, formular cannot be saved.', 1);
				return null;//we cannot save... contact admin
	
			} else {
	
				/*
				 * delete all the older formulars
				 * this should be a commitTransactions... but user whould still view the old data.. so on save what do we do?
				 * cascade= delete not working properly with softdelete... need to update the listener first
				 *
				 *
				 */
				if($data['form_id'] && $data['form_id'] == $sisstationary->id)
				{
					$custcourse = new PatientCourse();
					$custcourse->ipid = $data['ipid'];
					$custcourse->course_date = date("Y-m-d H:i:s", time());
					$custcourse->course_type = Pms_CommonData::aesEncrypt(SisStationary::PATIENT_COURSE_TYPE);
					$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes(SisStationary::PATIENT_COURSE_TITLE_EDIT));
					$custcourse->user_id = $data['userid'];
					$custcourse->tabname = Pms_CommonData::aesEncrypt(SisStationary::PATIENT_COURSE_TABNAME_SAVE);
					$custcourse->recordid = $sisstationary->id;
					$custcourse->done_name = Pms_CommonData::aesEncrypt(SisStationary::PATIENT_COURSE_TABNAME_SAVE);
					$custcourse->done_id = $sisstationary->id;
					$custcourse->save();
				}
				else 
				{					
					$custcourse = new PatientCourse();
					$custcourse->ipid = $data['ipid'];
					$custcourse->course_date = date("Y-m-d H:i:s", time());
					$custcourse->course_type = Pms_CommonData::aesEncrypt(SisStationary::PATIENT_COURSE_TYPE);
					$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes(SisStationary::PATIENT_COURSE_TITLE_CREATE));
					$custcourse->user_id = $data['userid'];
					$custcourse->tabname = Pms_CommonData::aesEncrypt(SisStationary::PATIENT_COURSE_TABNAME_SAVE);
					$custcourse->recordid = $sisstationary->id;
					$custcourse->done_name = Pms_CommonData::aesEncrypt(SisStationary::PATIENT_COURSE_TABNAME_SAVE);
					$custcourse->done_id = $sisstationary->id;
					$custcourse->save();
				}
	
				//update SisStationaryThematics
				$entitysisst = new SisStationaryThematics();
				
				$q = $entitysisst->getTable()->createQuery()
				->delete()
				->where('ipid = ?', $data['ipid'])
				->andWhereIn('form_id', $sisstationary->id)
				->execute();
				$sisstt = array();
				$kthem = 0;
				if ( ! empty($data['theme'])) {
	
					foreach ($data['theme'] as $theme => $values) {
						$sisstt[$kthem]['ipid'] = $data['ipid'];
						$sisstt[$kthem]['clientid'] = $data['clientid'];
						$sisstt[$kthem]['form_id'] = $sisstationary->id;
						$sisstt[$kthem]['thematic'] = $theme;
						foreach($values as $kv=>$vv)
						{
							$sisstt[$kthem][$kv] = $vv;
						}
						$kthem++;
					}
					
					$sisstf = new Application_Form_SisStationaryThematics();
					$sisstf->save_form_sisstationary_thematics($data['ipid'], $sisstt);
					 
				}
	
			}
		} else {
			//nothing to save... you should not be here
			throw new Exception('Contact Admin, empty formular cannot be saved.', 0);
		}
	
	
		return $sisstationary;
	}
}