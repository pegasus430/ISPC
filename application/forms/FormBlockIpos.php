<?php
// require_once("Pms/Form.php");
/**
 * @update Jan 24, 2018: @author claudiu, checked for ISPC-2071
 * this does NOT insert into PC?
 */
class Application_Form_FormBlockIpos extends Pms_Form
{

	public function clear_block_data($ipid, $contact_form_id )
	{
		if (!empty($contact_form_id))
		{

			$Q = Doctrine_Query::create()
			->update('FormBlockIpos')
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
		$ipos_block = new FormBlockIpos();
		
		$cols= array(
			'ipos0','ipos1a', 'ipos1b', 'ipos1c', 
			'ipos2a', 'ipos2b', 'ipos2c', 'ipos2d', 'ipos2e', 'ipos2f', 'ipos2g', 'ipos2h', 'ipos2i', 'ipos2j', 'ipos2k', 'ipos2l', 'ipos2m', 
			'ipos2ktext', 'ipos2ltext', 'ipos2mtext', 
			'ipos3', 'ipos4', 'ipos5', 'ipos6', 'ipos7', 'ipos8', 'ipos9',
			'score','special',
            'user','status'
			);

		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id']);
		if (strlen($post['old_contact_form_id']) > 0)
		{
			$ipos_old_data = $ipos_block->getPatientFormBlockIpos($post['ipid'], $post['old_contact_form_id'], true);

			if ($ipos_old_data)
			{
				// overide post data if no permissions on  block
				if (!(in_array('ipos', $allowed_blocks)||in_array('pflegeipos', $allowed_blocks)))
				{
					foreach ($cols as $col){
						$post['ipos'][$col] = $ipos_old_data[0][$col];
					}
				}
			}
		}
		$cust = new FormBlockIpos();
		$cust->ipid = $post['ipid'];
		$cust->contact_form_id = $post['contact_form_id'];
		
		foreach ($cols as $col){
			$cust->$col = $post['ipos'][$col];
			}
        $date=date('Y-m-d', strtotime($post['ipos']['date']));
        $date=$date . " " . $post['ipos']["date_h"] . ":" . $post['ipos']["date_m"]  .":00";
        $cust->date=$date;
		
		$cust->save();
	}


}

?>
