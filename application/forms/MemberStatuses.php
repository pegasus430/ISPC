<?php
require_once ("Pms/Form.php");

class Application_Form_MemberStatuses extends Pms_Form
{

    public function validate($post)
    {
        $error = 0;
        $Tr = new Zend_View_Helper_Translate();
        $val = new Pms_Validation();
        if (! $val->isstring($post['status'])) {
            $this->error_message['name'] = $Tr->translate('enterstatus');
            $error = 1;
        }
        
        if ($error == 0) {
            return true;
        }
        return false;
    }

    public function insert_data($post)
    {
        $fdoc = new MemberStatuses();
        $fdoc->clientid = $post['clientid'];
        $fdoc->status = $post['status'];
        $fdoc->save();
        
        if ($fdoc) {
            $inserted_id = $fdoc->id;
        }
        return $inserted_id;
    }

    public function update_data($post)
    {
        if($post['id']){
            $fdoc = Doctrine::getTable('MemberStatuses')->find($post['id']);
            if ($post['clientid'] > 0) {
                $fdoc->clientid = $post['clientid'];
            }
            $fdoc->status = $post['status'];
            $fdoc->save();
        }
    }
}

?>