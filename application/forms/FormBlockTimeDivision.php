<?php

// require_once("Pms/Form.php");
/**
 * 
 * @author claudiu 
 * @update Jan 22, 2018: checked for ISPC-2071, this has no PC
 * 
 * Zeitaufteilung
 *
 *
 */
class Application_Form_FormBlockTimeDivision extends Pms_Form 
{

	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if( ! empty($contact_form_id))
		{
			Doctrine_Query::create()
			->update('FormBlockTimeDivision')
			->set('isdelete', '1')
			->where("contact_form_id = ? ", $contact_form_id)
			->andWhere('ipid = ? ', $ipid )
			->execute();

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
		$vt_block = new FormBlockTimeDivision();


		$clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);

		if(strlen($post['old_contact_form_id']) > 0)
		{
			$vt_old_data = $vt_block->getPatientFormBlockTimeDivision($post['ipid'], $post['old_contact_form_id'], true);

			if($vt_old_data )
			{
				// overide post data if no permissions on befund block
				if(!in_array('time_division', $allowed_blocks))
				{
					$post['time_division']['on_patient'] = $vt_old_data[0]['on_patient'];
					$post['time_division']['relatives'] = $vt_old_data[0]['relatives'];
					$post['time_division']['systemic'] = $vt_old_data[0]['systemic'];
					$post['time_division']['professional'] = $vt_old_data[0]['professional'];
					$post['time_division']['remain'] = $vt_old_data[0]['remain'];
					$post['time_division']['on_call'] = $vt_old_data[0]['on_call'];
				}
			}
		}

		$cust = new FormBlockTimeDivision();
		
		$cust->ipid = $post['ipid'];
		$cust->contact_form_id = $post['contact_form_id'];
		
		if($post['time_division']['on_call'] == '1')
		{
			$cust->on_call = '1';
		}
		else
		{
			$cust->on_call = '0';
		}
		$cust->on_patient = $post['time_division']['on_patient'];
		$cust->relatives = $post['time_division']['relatives'];
		$cust->systemic = $post['time_division']['systemic'];
		$cust->professional = $post['time_division']['professional'];
		$cust->remain = $post['time_division']['remain'];
		
		$cust->save();
	}

}

?>