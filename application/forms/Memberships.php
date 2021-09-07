<?php
require_once ("Pms/Form.php");

class Application_Form_Memberships extends Pms_Form
{

    public function validate($post)
    {
        $error = 0;
        $val = new Pms_Validation();
        $Tr = new Zend_View_Helper_Translate();
        if (! $val->isstring($post['membership'])) {
            $this->error_message['membership'] = $Tr->translate('membership name is mandatory');
            $error = 1;
        }
        if (! $val->isstring($post['shortcut'])) {
            $this->error_message['shortcut'] = $Tr->translate('shortcut');
            $error = 1;
        }
        
        if ($error == 0) {
            return true;
        }
        
        return false;
    }

    public function InsertData($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        
        $med = new Memberships();
        $med->membership = $post['membership'];
        $med->shortcut = $post['shortcut'];
        $med->clientid = $logininfo->clientid;
        $med->save();
        return $med;
    }

    public function UpdateData($post, $medipump)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $med = Doctrine::getTable('Memberships')->find($medipump);
        $med->membership = $post['membership'];
        $med->shortcut = $post['shortcut'];
        $med->clientid = $logininfo->clientid;
        $med->save();
    }
}

?>