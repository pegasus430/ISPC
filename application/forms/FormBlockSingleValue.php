<?php
require_once("Pms/Form.php");
/**
 * @update Jan 25, 2018: @author claudiu, checked for ISPC-2071
 * this form+model are not used in this ISPC->Application
 */
class Application_Form_FormBlockSingleValue extends Pms_Form
{

	public function clear_block_data($ipid, $contact_form_id, $blockname )
	{
		if (!empty($contact_form_id))
		{
			$Q = Doctrine_Query::create()
			->update('FormBlockSingleValue')
			->set('isdelete','1')
			->where("contact_form_id='" . $contact_form_id. "'")
			->andWhere('blockname =?', $blockname)
			->andWhere('ipid LIKE "' . $ipid . '"');
			$result = $Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}


	public function InsertData($post,$allowed_blocks, $blockname)
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$tw_block = new FormBlockSingleValue();
		
		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id'], $blockname);
		
		if (strlen($post['old_contact_form_id']) > 0)
		{
			$tw_old_data = $tw_block->getPatientFormBlockSingleValue($post['ipid'], $post['old_contact_form_id'], true, $blockname);

			if ($tw_old_data)
			{
				// overide post data if no permissions on block
				if (!in_array($blockname, $allowed_blocks))
				{
					$post[$blockname]['text'] = $tw_old_data[0]['text'];
					$post[$blockname]['blockname'] = $tw_old_data[0]['blockname'];
				} 
			}
		}
		

		$cust = new FormBlockSingleValue();
		$cust->ipid = $post['ipid'];
		$cust->contact_form_id = $post['contact_form_id'];
		$cust->text = $post[$blockname]['text'];
		$cust->blockname = $blockname;
		$cust->save();
		
	
		if ($post[$blockname]['text']){
			$text = $post[$blockname]['text'];
			switch ($blockname){
				case 'lmu_physmed_documentation':
				case 'lmu_phone':
				case 'lmu_documentation':
					$text = "Dokumentation: " . $text;
					break;
				case 'lmu_psy_contacttype':
				case 'lmu_physmed_contacttype':
					$text = "Art des Kontakts: " . $text;
					break;
				case 'lmu_pharmaschulung':
					$text = "Patientenschulung: " . Pms_CommonData::splitPseudoMs($text);
					break;
				}

			
			
			$cust = new PatientCourse();
			$cust->ipid = $post['ipid'];
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("K");
			
			$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($text));
			$cust->user_id = $logininfo->userid;
			$cust->save();	

		}
	}


}

?>
