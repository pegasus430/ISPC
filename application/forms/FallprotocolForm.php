<?php
require_once ("Pms/Form.php");

class Application_Form_FallprotocolForm extends Pms_Form
{

    public function InsertData($post)
    {
        print_r($post);
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $userid = $logininfo->userid;
        
        $sql = new FallprotocolForm();
        $sql->ipid = $post['ipid'];
        
        if (! empty($post['time_fall'])) {
            $time_of_fall = $post['time_fall'] . ":00";
        } else {
            $time_of_fall = "00:00:00";
        }
        
        if (! empty($post['date_fall'])) {
            $date_fall = date('Y-m-d', strtotime($post['date_fall'])) . " " . $time_of_fall;
        } else {
            $date_fall = '0000-00-00 00:00:00';
        }
        
        $sql->date_fall = $date_fall;
        $sql->wasthere = $post['wasthere'];
        $sql->falllocation = $post['falllocation'];
        $sql->prehistory_known = $post['prehistory_known'];
        $sql->falllocation = $post['falllocation'];
        $sql->guest_striking = $post['guest_striking'];
        $sql->guest_fixed = $post['guest_fixed'];
        $sql->fall_led = $post['fall_led'];
        $sql->shoes = $post['shoes'];
        $sql->glasses = $post['glasses'];
        $sql->auditiv = $post['auditiv'];
        $sql->walking = $post['walking'];
        $sql->external_circumstances = $post['external_circumstances'];
        $sql->last_contact = $post['last_contact'];
        if (is_array($post['consequences_visible'])) {
            $sql->consequences_visible = implode(",", $post['consequences_visible']);
        }
        $sql->reaction_fall = $post['reaction_fall'];
        $sql->guest_transport = $post['guest_transport'];
        $sql->save();
        
        $tab_name = 'fall_protocol';
        $result = $sql->id;
        
        if (strlen($post['date_fall']) > 0) {
            $done_date = date('Y-m-d', strtotime($post['date_fall'])) . ' ' . $time_of_fall;
        } else {
            $done_date = date("Y-m-d H:i:s", time());
        }
        
        $cust = new PatientCourse();
        $cust->ipid = $ipid;
        $cust->course_date = date("Y-m-d H:i:s", time());
        $cust->course_type = Pms_CommonData::aesEncrypt("F");
        $cust->course_title = Pms_CommonData::aesEncrypt('Sturzprotokoll hinzugefügt');
        $cust->user_id = $userid;
        $cust->done_date = $done_date;
        $cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
        $cust->done_id = $result;
        $cust->save();
        
        return $sql->id;
    }

    public function UpdateData($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $sql = Doctrine::getTable('FallprotocolForm')->findBy('ipid', ($post['ipid']))->getFirst();
        $sql->date_fall = $post['date_fall'];
        $sql->wasthere = $post['wasthere'];
        $sql->falllocation = $post['falllocation'];
        $sql->prehistory_known = $post['prehistory_known'];
        $sql->falllocation = $post['falllocation'];
        $sql->guest_striking = $post['guest_striking'];
        $sql->guest_fixed = $post['guest_fixed'];
        $sql->fall_led = $post['fall_led'];
        $sql->shoes = $post['shoes'];
        $sql->glasses = $post['glasses'];
        $sql->auditiv = $post['auditiv'];
        $sql->walking = $post['walking'];
        $sql->external_circumstances = $post['external_circumstances'];
        $sql->last_contact = $post['last_contact'];
        if (is_array($post['consequences_visible'])) {
            $sql->consequences_visible = implode(",", $post['consequences_visible']);
        }
        $sql->reaction_fall = $post['reaction_fall'];
        $sql->guest_transport = $post['guest_transport'];
        $sql->save();
        
        $tab_name = "fallprotocol_form";
        $result = $sql->id;
        // print_r($result ); exit;
        if (strlen($post['date_fall']) > 0) {
            $done_date = $post['date_fall'];
        } else {
            $done_date = date("Y-m-d H:i:s", time());
        }
        
        $cust = new PatientCourse();
        $cust->ipid = $ipid;
        $cust->course_date = date("Y-m-d H:i:s", time());
        $cust->course_type = Pms_CommonData::aesEncrypt("K");
        $cust->course_title = Pms_CommonData::aesEncrypt("Sturzprotokoll ... ");
        $cust->user_id = $userid;
        $cust->done_date = $done_date;
        $cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
        $cust->done_id = $result;
        $cust->save();
    }
}
?>