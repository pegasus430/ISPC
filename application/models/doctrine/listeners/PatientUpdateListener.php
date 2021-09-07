<?php

class PatientUpdateListener extends Doctrine_Record_Listener
{

    /*
     * @cla on 19.06.2018 ... i've taken a look on this and PatientInsertListener...
     *
     * it is my opinion that both have a fatal logic flaw, because they are using $_GET['id']
     *
     * how is a @dev supposed don't know that your query params are used in setters ?
     * 
     * is this request param 'id' supposed to ALLWAYS be the patient encrypted id? where is this enforced ?
     * 
     * how do you know the 'id' belongs to this client, if the insert is performed by a another obscure function in a formular ?
     * 
     * when you will endup with last_update_user from another client, assigned to a strage ipid, this is one possible flaw point
     * when last_update keeps changing even though you are not doying any action on that strage ipid, this is one possible flaw point
     */
    /**
     * (non-PHPdoc)
     * 
     * @see Doctrine_Record_Listener::postUpdate()
     */
    public function postUpdate(Doctrine_Event $event)
    {
        if ( ! isset($_GET['id'])) {
            return;
        }
        
        $decid = Pms_Uuid::decrypt($_GET['id']);
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        
        $userid = $logininfo->userid;
        
        if ($pm = Doctrine::getTable('PatientMaster')->find($decid)) {
            
            $pm->last_update = date("Y-m-d H:i:s", time());
            $pm->last_update_user = $userid;
            $pm->save();
            
            // added patient admission/readmission new procedure
            // $patient_ipid = Pms_CommonData::getIpid($decid);
            
            PatientMaster::get_patient_admissions($pm->ipid); // this is just a setter !
        }
    }
}

?>