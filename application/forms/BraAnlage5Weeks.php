<?php
require_once ("Pms/Form.php");

class Application_Form_BraAnlage5Weeks extends Pms_Form
{

    public function insert($ipid, $post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;

        if(strlen($post['anlage5_id']) > 0 ){

            foreach($post['week_data'] as $week_nr => $week_details){
            
                $records[] = array(
                    "ipid" => $ipid,
                    "anlage5_id" => $post['anlage5_id'],
                    "start_date" => date('Y-m-d H:i:s',strtotime($week_details['start_date'])),
                    "end_date" => date('Y-m-d H:i:s',strtotime($week_details['end_date'])),
                    "hospital_days" => $week_details['hospital_days'],
                    "products" => $week_details['products'],
                    "doctor_weg" => $week_details['doctor_weg'],
                    "nurse_weg" => $week_details['nurse_weg'],
                    "doctor_km" => $week_details['doctor_km'],
                    "nurse_km" => $week_details['nurse_km']
                );
            }
            
            
            if(!empty($records)){
                $collection = new Doctrine_Collection('BraAnlage5Weeks');
                $collection->fromArray($records);
                $collection->save();
            }
        } 
    }
}
?>
