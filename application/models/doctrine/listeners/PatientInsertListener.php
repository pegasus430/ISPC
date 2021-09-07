<?php

class PatientInsertListener extends Doctrine_Record_Listener
{

    /*
     * @cla on 19.06.2018 , for my comments please @see PatientUpdateListener 
     */
    /**
     * (non-PHPdoc)
     * 
     * @see Doctrine_Record_Listener::postInsert()
     */
    public function postInsert(Doctrine_Event $event)
    {
        if ( ! isset($_GET['id'])) {
            
            $ipid = $event->getInvoker()->ipid;
            
        } else {
            
            $pid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpid($pid);
        }
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        
        if ( ! empty($userid)) {
            
            $pm = Doctrine_Query::create()->update('PatientMaster')
                ->set('last_update', '?', date("Y-m-d H:i:s"))
                ->set('last_update_user', '?', $userid)
                ->where("ipid = ?", $ipid)
                ->execute();
            
            // added patient admission/readmission new procedure
            PatientMaster::get_patient_admissions($ipid); // this is just a setter !
        }
    }
}

?>