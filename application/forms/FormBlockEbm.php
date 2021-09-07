<?php
require_once("Pms/Form.php");
/**
 * @update Jan 23, 2018: @author claudiu, checked for ISPC-2071
 * this has no PatientCourse, nothing changed
 *
 */
class Application_Form_FormBlockEbm extends Pms_Form
{

	public function clear_block_data($ipid, $contact_form_id )
	{
		if (!empty($contact_form_id))
		{

			$Q = Doctrine_Query::create()
			->update('FormBlockEbm')
			->set('isdelete','1')
			->where("contact_form_id='" . $contact_form_id. "'")
			->andWhere('ipid LIKE "' . $ipid . '"');
			$result = $Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}


	public function InsertData($post,$allowed_blocks)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$ebm_block = new FormBlockEbm();

		//set the old block values as isdelete
		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id']);
		
		if (strlen($post['old_contact_form_id']) > 0)
		{
			$ebm_old_data = $ebm_block->getPatientFormBlockEbm($post['ipid'], $post['old_contact_form_id'], true);

			if ($ebm_old_data)
			{
				// overide post data if no permissions on ebm block
				if (!in_array('ebm', $allowed_blocks))
				{
					$post['unforseen_i'] = $ebm_old_data[0]['unforseen_i'];
					$post['unforseen_ii'] = $ebm_old_data[0]['unforseen_ii'];
					$post['first_prescription'] = $ebm_old_data[0]['first_prescription'];
					$post['follow_regulation'] = $ebm_old_data[0]['follow_regulation'];
					$post['physician_readiness'] = $ebm_old_data[0]['physician_readiness'];
					$post['psychosomatic_clarification'] = $ebm_old_data[0]['psychosomatic_clarification'];
					$post['psychosomatic_intervention'] = $ebm_old_data[0]['psychosomatic_intervention'];
				}
			}
		}
		$cust = new FormBlockEbm();
		$cust->ipid = $post['ipid'];
		$cust->contact_form_id = $post['contact_form_id'];

		$cust->unforseen_i = $post['unforseen_i'];
		$cust->unforseen_ii = $post['unforseen_ii'];
		$cust->first_prescription = $post['first_prescription'];
		$cust->follow_regulation = $post['follow_regulation'];
		$cust->physician_readiness = $post['physician_readiness'];
		$cust->psychosomatic_clarification = $post['psychosomatic_clarification'];
		$cust->psychosomatic_intervention = $post['psychosomatic_intervention'];
		$cust->save();
	}


}

?>