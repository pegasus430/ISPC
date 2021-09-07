<?php
require_once ("Pms/Form.php");

class Application_Form_XbdtActions extends Pms_Form
{

    public function validate($post)
    {
        $error = 0;
        $val = new Pms_Validation();
        $Tr = new Zend_View_Helper_Translate();
        if (! $val->isstring($post['action_id'])) {
            $this->error_message['action_id'] = $Tr->translate('enteractionaction_id');
            $error = 1;
        }
        if (! $val->isstring($post['name'])) {
            $this->error_message['name'] = $Tr->translate('enteractionname');
            $error = 2;
        }
        
        if (! $val->isstring($post['groupname'])) {
            $this->error_message['groupname'] = $Tr->translate('enteractiongroupname');
            $error = 3;
        }
        
        if ($error == 0) {
            return true;
        }
        
        return false;
    }

    public function insert($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        
        $med = new XbdtActions();
        $med->action_id = $post['action_id'];
        $med->name = $post['name'];
        $med->groupname = $post['groupname'];
        $med->clientid = $logininfo->clientid;
        $med->available = $post['available'];
        $med->contact_form_block = $post['contact_form_block'];
        $med->save();
        
        return $med;
    }

    public function update($post)
    {
        $med = Doctrine::getTable('XbdtActions')->find($_GET['id']);
        $med->action_id = $post['action_id'];
        $med->name = $post['name'];
        $med->groupname = $post['groupname'];
        $med->available = $post['available'];
        $med->contact_form_block = $post['contact_form_block'];
        $med->save();
    }
}

?>