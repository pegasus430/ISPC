<?php
require_once ("Pms/Form.php");

class Application_Form_BraAnlage5 extends Pms_Form
{

    public function insert($ipid, $post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $ins = new BraAnlage5();
        $ins->ipid = $ipid;
        $ins->date = date('Y-m-d H:i:s', strtotime($post['date']));
        $ins->start_date = date('Y-m-d H:i:s', strtotime($post['start_date']));
        $ins->end_date = date('Y-m-d H:i:s', strtotime($post['end_date']));
        $ins->hospital_days = $post['hospital_days'];
        $ins->visit_date = date('Y-m-d', strtotime($post['visit_date']));
        $ins->visit_doctor = $post['visit_doctor'];
        $ins->visit_nurse = $post['visit_nurse'];
        $ins->doctor_data = $post['doctor_data'];
        $ins->nurse_data_i = $post['nurse_data_i'];
        $ins->nurse_data_ii = $post['nurse_data_ii'];
        $ins->nurse_data_iii = $post['nurse_data_iii'];
        $ins->location_data = implode(',', $post['location_data']);
        $ins->overall_amount = $post['overall_amount'];

        if(strlen($post['create_invoice']) > 0){
            $ins->status = 1; // invoiced
        } else{
            $ins->status = 0;
        }
        
        $ins->save();
        
        $result = $ins->id;
        
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
    
    
    public function update($ipid, $post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;

		$update = Doctrine::getTable('BraAnlage5')->find($post['anlage_id']);
        $update->date = date('Y-m-d H:i:s', strtotime($post['date']));
        $update->start_date = date('Y-m-d H:i:s', strtotime($post['start_date']));
        $update->end_date = date('Y-m-d H:i:s', strtotime($post['end_date']));
        $update->hospital_days = $post['hospital_days'];
        $update->visit_date = date('Y-m-d', strtotime($post['visit_date']));
        $update->visit_doctor = $post['visit_doctor'];
        $update->visit_nurse = $post['visit_nurse'];
        $update->doctor_data = $post['doctor_data'];
        $update->nurse_data_i = $post['nurse_data_i'];
        $update->nurse_data_ii = $post['nurse_data_ii'];
        $update->nurse_data_iii = $post['nurse_data_iii'];
        $update->location_data = implode(',', $post['location_data']);
        $update->overall_amount = $post['overall_amount'];
        
        if(strlen($post['create_invoice']) > 0){
            $update->status = 1; // invoiced
        } else{
            $update->status = 0;
        }
        
        $update->save();
    }
    

    
    
    
    
    
    
}
?>
