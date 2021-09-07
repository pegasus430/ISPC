<?php
require_once ("Pms/Form.php");

class Application_Form_MemberMembershipEnd extends Pms_Form
{

    public function validate($post)
    {
        $error = 0;
        $val = new Pms_Validation();
        $Tr = new Zend_View_Helper_Translate();

        
        if (! $val->isstring($post['description'])) {
            $this->error_message['description'] = $Tr->translate('settlement_services_error_description');
            $error = 2;
        }
       
        if ($error == 0) {
            return true;
        }
        
        return false;
    }

    public function insert($post, $clientid = 0)
    {      
        $med = new MemberMembershipEnd();
        $med->description = $post['description'];
        $med->clientid = $clientid;
        $med->save();
        
        return $med;
    }

    public function update($post, $clientid = 0 )
    {
        $med = Doctrine::getTable('MemberMembershipEnd')->findByIdAndClientid((int)$post['id'], $clientid);
        $med = $med{0};
        $med->description = $post['description'];
        $med->save();
    }

    

}



?>