<?php
require_once ("Pms/Form.php");

class Application_Form_SapvEvaluationIpos2 extends Pms_Form
{

    public function validate($post)
    {
        $Tr = new Zend_View_Helper_Translate();
        
        $error = 0;
        $val = new Pms_Validation();
        if ($error == 0) {
            return true;
        }
        
        return false;
    }

    public function insert($post)
    {
        $patients_fields = array('patient_epid','patient_last_name', 'patient_first_name', 'patient_birthd', 'patient_zip','patient_gender','patient_age');
        $ind = new SapvEvaluationIpos2();
        foreach ($post as $field => $value) 
        {
            if(!in_array($field,$patients_fields))
            {
	            $ind->$field = $value;
            }
        }
        $ind->save();
    }

    public function update($ipos2_id,$post)
    {
        $patients_fields = array('patient_epid','patient_last_name', 'patient_first_name', 'patient_birthd', 'patient_zip','patient_gender','patient_age');
        $ind = Doctrine::getTable('SapvEvaluationIpos2')->find($ipos2_id);
        
        if(!$ind){
            $ind = new SapvEvaluationIpos2();
            $ind->ipid = $post['ipid'];
            $ind->form_id = $post['form_id'];
        }
        
        foreach ($post as $field => $value)
        {
            if($field != "ipid" && $field != "form_id"  && !in_array($field,$patients_fields) )
            {
                $ind->$field = $value;
            }
        }
        $ind->save();
    }
    
    
    public function reset($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $change_date = date('Y-m-d H:i:s', time());
    
        $sph = Doctrine_Query::create()
        ->update('SapvEvaluationIpos2')
        ->set('isdelete', '1')
        ->set('change_user', $userid)
        ->set('change_date', '"' . $change_date . '"')
        ->where("form_id='" . $post['form_id'] . "'")
        ->andWhere("ipid='" . $post['ipid'] . "'");
        $sph->execute();
    }
}