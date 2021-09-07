<?php
require_once ("Pms/Form.php");

class Application_Form_HospizVisitsTypes extends Pms_Form
{

    public function validate($post)
    {
        $error = 0;
        $Tr = new Zend_View_Helper_Translate();
        $val = new Pms_Validation();
        if (! $val->isstring($post['grund'])) {
            $this->error_message['name'] = $Tr->translate('entergrund');
            $error = 1;
        }
        
        if ($error == 0) {
            return true;
        }
        return false;
    }

    public function insert_data($post)
    {
        $fdoc = new HospizVisitsTypes();
        $fdoc->clientid = $post['clientid'];
        $fdoc->grund = $post['grund'];
        $fdoc->billable = $post['billable'];
        $fdoc->save();
        
        if ($fdoc) {
            $inserted_id = $fdoc->id;
            $new_vw_id = $fdoc->id;
        }
        return $inserted_id;
    }

    public function update_data($post)
    {
        if($post['id']){
            $fdoc = Doctrine::getTable('HospizVisitsTypes')->find($post['id']);
            if ($post['clientid'] > 0) {
                $fdoc->clientid = $post['clientid'];
            }
            $fdoc->grund = $post['grund'];
            $fdoc->billable = $post['billable'];
            $fdoc->save();
        }
    }
}

?>