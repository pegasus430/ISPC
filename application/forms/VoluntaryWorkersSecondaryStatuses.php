<?php
require_once ("Pms/Form.php");

class Application_Form_VoluntaryWorkersSecondaryStatuses extends Pms_Form
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
        $vwst = new VoluntaryWorkersSecondaryStatuses();
        $vwst->description = $post['description'];
        $vwst->clientid = $clientid;
        $vwst->save();
        
        return $vwst;
    }

    public function update($post, $clientid = 0 )
    {
        //$vwst = Doctrine::getTable('VoluntaryWorkersSecondaryStatuses')->findByIdAndClientid((int)$post['id'], $clientid);
    	$vwst = Doctrine::getTable('VoluntaryWorkersSecondaryStatuses')->findById((int)$post['id']);
        $vwst = $vwst{0};
        $vwst->description = $post['description'];
        $vwst->save();
    }

    

}



?>