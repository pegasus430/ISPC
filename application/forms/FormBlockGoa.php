<?php

require_once("Pms/Form.php");
/**
 *
 * @update Jan 23, 2018: @author claudiu, checked for ISPC-2071
 * this saves nothing in PC... not changeing at this time
 *
 */
class Application_Form_FormBlockGoa extends Pms_Form
{

	public function clear_block_data($ipid, $contact_form_id )
	{
		if (!empty($contact_form_id))
		{

			$Q = Doctrine_Query::create()
			->update('FormBlockGoa')
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
		// 	print_r($post); exit;
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$form_goa = new FormBlockGoa();

		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id']);
		if (strlen($post['old_contact_form_id']) > 0)
		{
			$goa_old_data = $form_goa->getPatientFormBlockGoa($post['ipid'], $post['old_contact_form_id'], true);

			if ($goa_old_data)
			{
				// overide post data if no permissions on goa block
				if (!in_array('goa', $allowed_blocks))
				{
					$post['consultation'] = $goa_old_data[0]['consultation'];
					$post['recipe_transfer'] = $goa_old_data[0]['recipe_transfer'];
					$post['expert_advice'] = $goa_old_data[0]['expert_advice'];
					$post['charge_i_y1'] = $goa_old_data[0]['charge_i_y1'];
					$post['charge_i_y2'] = $goa_old_data[0]['charge_i_y2'];
					$post['charge_i_y3'] = $goa_old_data[0]['charge_i_y3'];
					$post['discussion_of_impact'] = $goa_old_data[0]['discussion_of_impact'];
					$post['consultant_discussion'] = $goa_old_data[0]['consultant_discussion'];
					$post['detailed_report'] = $goa_old_data[0]['detailed_report'];
					$post['charge_ii_y1'] = $goa_old_data[0]['charge_ii_y1'];
					$post['charge_ii_y2'] = $goa_old_data[0]['charge_ii_y2'];
					$post['charge_ii_y3'] = $goa_old_data[0]['charge_ii_y3'];
				}
			}
		}

		$cust = new FormBlockGoa();
		$cust->ipid = $post['ipid'];
		$cust->contact_form_id = $post['contact_form_id'];

		$cust->consultation= $post['consultation'];
		$cust->recipe_transfer = $post['recipe_transfer'];
		$cust->expert_advice = $post['expert_advice'];
		$cust->charge_i_y1 = $post['charge_i_y1'];
		$cust->charge_i_y2 = $post['charge_i_y2'];
		$cust->charge_i_y3 = $post['charge_i_y3'];
		$cust->discussion_of_impact = $post['discussion_of_impact'];
		$cust->consultant_discussion = $post['consultant_discussion'];
		$cust->detailed_report = $post['detailed_report'];
		$cust->charge_ii_y1 = $post['charge_ii_y1'];
		$cust->charge_ii_y2 = $post['charge_ii_y2'];
		$cust->charge_ii_y3 = $post['charge_ii_y3'];
		$cust->save();
	}
}

?>