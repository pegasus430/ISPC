<?php
require_once ("Pms/Form.php");

class Application_Form_SettlementServices extends Pms_Form
{

    public function validate($post)
    {
        $error = 0;
        $val = new Pms_Validation();
        $Tr = new Zend_View_Helper_Translate();
        if (! $val->isstring($post['action_id'])) {
            $this->error_message['action_id'] = $Tr->translate('settlement_services_error_action_id');
            $error = 1;
        }
        if (! $val->isstring($post['description'])) {
            $this->error_message['description'] = $Tr->translate('settlement_services_error_description');
            $error = 2;
        }
       
        if ($error == 0) {
            return true;
        }
        
        return false;
    }

    public function insert($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        
        $med = new SettlementServices();
        $med->action_id = $post['action_id'];
        $med->description = $post['description'];
        $med->clientid = $logininfo->clientid;
        $med->save();
        
        return $med;
    }

    public function update($post)
    {
        $med = Doctrine::getTable('SettlementServices')->find($_GET['id']);
        $med->action_id = $post['action_id'];
        $med->description = $post['description'];
        $med->save();
    }
}

?>