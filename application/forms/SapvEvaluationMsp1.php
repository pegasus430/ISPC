<?php
require_once ("Pms/Form.php");

class Application_Form_SapvEvaluationMsp1 extends Pms_Form
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
        $ind = new SapvEvaluationMsp1();
        foreach ($post as $field => $value) 
        {
            
            if ($field == "first_admission") 
            {
                if (strlen($value) > 0) 
                {
                    $ind->$field = date('Y-m-d 00:00:00', strtotime($value));
                } 
                else 
                {
                    $ind->$field = "0000-00-00 00:00:00";
                }
            } 
            elseif ($field == "first_sapv_types" || $field == "extra_nursing_care") 
            {
                $ind->$field = implode(',', $value);
            } 
            else 
            {
                $ind->$field = $value;
            }
        }
        $ind->save();
    }

    public function update($msp1_id,$post)
    {
        $ind = Doctrine::getTable('SapvEvaluationMsp1')->find($msp1_id);

        if(!$ind){
            $ind = new SapvEvaluationMsp1();
            $ind->ipid = $post['ipid'];
            $ind->form_id = $post['form_id'];
        }
        
        foreach($post as $field => $value )
        {
            if($field != "ipid" && $field != "form_id")
            {
                if ($field == "first_admission")
                {
                    if (strlen($value) > 0)
                    {
                        $ind->$field = date('Y-m-d 00:00:00', strtotime($value));
                    }
                    else
                    {
                        $ind->$field = "0000-00-00 00:00:00";
                    }
                }
                elseif ($field == "first_sapv_types" || $field == "extra_nursing_care")
                {
                    $ind->$field = implode(',', $value);
                }
                else
                {
                    $ind->$field = $value;
                }
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
        ->update('SapvEvaluationMsp1')
        ->set('isdelete', '1')
        ->set('change_user', $userid)
        ->set('change_date', '"' . $change_date . '"')
        ->where("form_id='" . $post['form_id'] . "'")
        ->andWhere("ipid='" . $post['ipid'] . "'");
        $sph->execute();
    }
    
}