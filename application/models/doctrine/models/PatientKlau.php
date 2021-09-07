<?php

//INFO-1554

/**
 * PatientKlau Konsilauftrag
 */
Doctrine_Manager::getInstance()->bindComponent('PatientKlau', 'MDAT');
class PatientKlau extends BasePatientKlau
{

    public static function get_popup_data($ipid){
        return PatientKlau::render_klau($ipid, 0, 'popup');
    }

    public static function render_klau($ipid, $id, $mode){

        if($id<1){
            $klaus = Doctrine::getTable('PatientKlau')->findBy('ipid', $ipid);
            if($klaus) {

                foreach ($klaus as $klau) {
                    //take last klau
                }
            }
        }

        $patientmaster = new PatientMaster();
        $ki=$id;

        $decid=Pms_CommonData::getIdfromIpid($ipid);
        $encid=  Pms_Uuid::encrypt($decid);
        $v=new Zend_View();
        $v->encid=$encid;
        $v->mode=$mode;
        if(!isset($klau)) {
            $klau = Doctrine::getTable('PatientKlau')->findOneBy('id', $ki);
        }
        $json=json_decode($klau->messagejson,1);
        $v->klau=$json;
        $v->patmaster=$patientmaster->getMasterData(0,0,0,$ipid);

        $hi=new PatientHealthInsurance;
        $ins=$hi->get_patients_healthinsurance([$ipid]);
        $v->ins=array();
        if($ins && count($ins)){
            $v->ins=$ins[0];
        }

        $fdoc=new FamilyDoctor();
        $doc=$fdoc->getFamilyDoctors($ipid);
        $v->doc=false;
        if($doc && count($doc)){
            $v->doc=$doc[0];
        }

        $nok=new ContactPersonMaster();
        $v->cps=$nok->getContactPersonsByIpids([$ipid]);

        $v->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $classname=Client::getClientconfig($clientid, 'hl7_processor_class');
        if($classname=="Net_ProcessHL7bbregensburg"){
            $html=$v->render('patientklau_bbregensburg.html');
        }else{
            $html=$v->render('patientklau_lmu.html');
        }

        return $html;
    }

}