<?php

	require_once("Pms/Form.php");
	/**
	 * @update Jan 25, 2018: @author claudiu, checked for ISPC-2071
	 * this block does NOT insert into patientCourse, nothing to change at this time
	 *
	 */
	class Application_Form_FormBlockVisitType extends Pms_Form {

		public function clear_block_data($ipid, $contact_form_id)
		{
			if(!empty($contact_form_id))
			{
				$Q = Doctrine_Query::create()
					->update('FormBlockVisitType')
					->set('isdelete', '1')
					->where("contact_form_id='" . $contact_form_id . "'")
					->andWhere('ipid LIKE "' . $ipid . '"');
				$result = $Q->execute();

				return true;
			}
			else
			{
				return false;
			}
		}

		public function InsertData($post, $allowed_blocks)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$vt_block = new FormBlockVisitType();


			$clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);

			if(strlen($post['old_contact_form_id']) > 0)
			{
				$vt_old_data = $vt_block->getPatientFormBlockVisitType($post['ipid'], $post['old_contact_form_id'], true);

				if($vt_old_data )
				{
					// overide post data if no permissions on befund block
					if(!in_array('kvno_visit_type', $allowed_blocks))
					{
						$post['kvno_visit_type']['ethically'] = $vt_old_data[0]['ethically'];
						$post['kvno_visit_type']['somatic'] = $vt_old_data[0]['somatic'];
						$post['kvno_visit_type']['psychosocial'] = $vt_old_data[0]['psychosocial'];
						$post['kvno_visit_type']['coordination'] = $vt_old_data[0]['coordination'];
					}
				}
			}

			$cust = new FormBlockVisitType();
			$cust->ipid = $post['ipid'];
			$cust->contact_form_id = $post['contact_form_id'];
			
			if($post['kvno_visit_type']['ethically'] == '1')
			{
				$cust->ethically = $post['kvno_visit_type']['ethically'];
			}
			else
			{
				$cust->ethically = '0';
			}
			
			if($post['kvno_visit_type']['somatic'] == '1')
			{
				$cust->somatic = $post['kvno_visit_type']['somatic'];
			}
			else
			{
				$cust->somatic = '0';
			}
			
			if($post['kvno_visit_type']['psychosocial'] == '1')
			{
				$cust->psychosocial = $post['kvno_visit_type']['psychosocial'];
			}
			else
			{
				$cust->psychosocial = '0';
			}
			
			
			if($post['kvno_visit_type']['coordination'] == '1')
			{
				$cust->coordination = $post['kvno_visit_type']['coordination'];
			}
			else
			{
				$cust->coordination = '0';
			}
			
			$cust->save();
		}

	}

?>