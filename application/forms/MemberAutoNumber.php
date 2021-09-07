<?php
require_once ("Pms/Form.php");

class Application_Form_MemberAutoNumber extends Pms_Form
{

    public function validate($post)
    {
        $error = 0;
        $val = new Pms_Validation();
        $Tr = new Zend_View_Helper_Translate();

        
        if ( ! $val->integer($post['next_auto_member_number'])) {
        	
        	$this->error_message['next_auto_member_number'] = $Tr->translate('error_next_auto_member_number');
        	$error = 1;
        	
        } else {
        	
        	$m_obj = new Member();
        	$next_member_number = $m_obj->get_highest_member_number( $get_client_defined_start = false);
        	
        	if ( (int)$next_member_number > (int)$post['next_auto_member_number']) {
        		$this->error_message['next_auto_member_number'] = sprintf($Tr->translate('error_next_auto_member_number minimum'), $next_member_number);
        		$error = 1;
        	}
        }
       
        if ($error == 0) {
            return true;
        } else {
        	return false;
        }
    }

    public function insert($post, $clientid = 0)
    {      
    	
    	$man_obj = new MemberAutoNumber();
    	
    	$man_obj->delete_by_clientid($clientid); // delete all previous
    	
    	$last_id = $man_obj->set_new_record(array(
    			"clientid" => $clientid,
    			"start_number" => $post['next_auto_member_number'],
    	));
    	
    	return $last_id;
    }



}



?>