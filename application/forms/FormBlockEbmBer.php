<?php

	require_once("Pms/Form.php");
/**
 * 
 * @update Jan 23, 2018: @author claudiu, checked/modified for ISPC-2071
 * ancuta said this block is no longer user... so I will not modify
 * 
 * TODO : remove this block from ispc, so admin can no longer use-it info ContactForms
 *
 */
	class Application_Form_FormBlockEbmBer extends Pms_Form {

		public function clear_block_data($ipid, $contact_form_id)
		{
			if(!empty($contact_form_id))
			{
				$Q = Doctrine_Query::create()
					->update('FormBlockEbmBer')
					->set('isdelete', '1')
					->where("contact_form_id='" . $contact_form_id . "'")
					->andWhere('ipid LIKE "' . $ipid . '"');
				$Q->execute();

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
			$ebm_ber_block = new FormBlockEbmBer();

			$blocks_settings = new FormBlocksSettings();
			$block_ebm_ber_values = $blocks_settings->get_block($clientid, 'ebm_ber');

			$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));

			foreach($block_ebm_ber_values as $key => $action_values)
			{

				if($post['egblock']['ebm_ber'][$action_values['id']] > 0)
				{
					$value = 1;
				}
				else
				{

					$value = 0;
				}
				$records[] = array(
					"ipid" => $post['ipid'],
					"contact_form_id" => $post['contact_form_id'],
					"action_id" => $action_values['id'],
					"action_value" => $value
				);

// 			if ($post['old_contact_form_id'] == 0)
// 			{
// 				if($post['egblock']['ebm_ber'][$action_values['id']] > 0){
// 					$cust = new PatientCourse();
// 					$cust->ipid =  $post['ipid'];
// 					$cust->course_date = date("Y-m-d H:i:s", time());
// 					$cust->course_type = Pms_CommonData::aesEncrypt("K");
// 					$cust->course_title = Pms_CommonData::aesEncrypt("SGB XI Leistungen : " . $action_values['option_name']);
// 					$cust->user_id = $userid;
// 					$cust->done_date = $done_date;
// 					$cust->done_name = Pms_CommonData::aesEncrypt("contact_form_measures");
// 					$cust->done_id = $post['contact_form_id'];
// 					$cust->save();
// 				}
// 			}
			}

			//set the old block values as isdelete
			$clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);

			if(strlen($post['old_contact_form_id']) > 0)
			{
				$ebm_ber_old_data = $ebm_ber_block->getPatientFormBlockEbmBer($post['ipid'], $post['old_contact_form_id'], true);

				if($ebm_ber_old_data)
				{
					// overide post data if no permissions on ebm_ber block
					if(!in_array('ebm_ber', $allowed_blocks))
					{
						$records = array();
						foreach($block_ebm_ber_values as $ke => $action_values)
						{
							if($ebm_ber_old_data[$action_values['id']] > 0)
							{
								$value = 1;
							}
							else
							{
								$value = 0;
							}

							$records[] = array(
								"ipid" => $post['ipid'],
								"contact_form_id" => $post['contact_form_id'],
								"action_id" => $action_values['id'],
								"action_value" => $value
							);
						}
					}
					else
					{
						$course_str = "EBM: \n";

						$options = array();
						foreach($block_ebm_ber_values as $ke => $action_values)
						{
							//allow only checked values and those which are not in old cf
							if($post['egblock']['ebm_ber'][$action_values['id']] == '1' && $ebm_ber_old_data[$action_values['id']] != '1')
							{
								$options[] = '1';
								$course_str .= $action_values['option_name'] . "\n";
							}
						}

						if(!empty($options))
						{
							//ebm edited entry in verlauf
							$cust = new PatientCourse();
							$cust->ipid = $post['ipid'];
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt("K");
							$cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str));
							$cust->user_id = $userid;
							$cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
							$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
							$cust->done_id = $post['contact_form_id'];
							
							// ISPC-2071 - added tabname, this entry must be grouped/sorted
							$cust->tabname = Pms_CommonData::aesEncrypt("FormBlockEbmBer");
							
							$cust->save();
						}
					}
				}
			}

			$collection = new Doctrine_Collection('FormBlockEbmBer');
			$collection->fromArray($records);
			$collection->save();
		}

	}

?>