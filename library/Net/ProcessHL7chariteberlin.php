<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 
class Net_ProcessHL7chariteberlin extends Net_ProcessHL7{

    //use the default class with no changes?!?
    public function __construct($conf) {
        parent::__construct($conf);
        $this->config['serverpath_sh']  ="/data01/ispc_hl7server/ispc_hl7server.sh";
        $this->config['serverpath_php'] ="/data01/ispc_hl7server/ispc_hl7server.php";
    }

    public function parseFamilydoctor(){
        $pv1=$this->message->getSegmentsByName("PV1");
        if(!count($pv1)){
            return;
        }

        $pv1=$pv1[0];
        $fdocfield = $pv1->getConsultingDoctor();

        $doc=array();
        $doc['first_name']      = $fdocfield->firstname;
        $doc['last_name']       = $fdocfield->lastname;
        $doc['street']          = $fdocfield->country;
        $doc['title']           = $fdocfield->degree;
        $doc['zip']             = $fdocfield->street;
        $doc['city']            = $fdocfield->zipcity;
        $doc['phone_practice']  = $fdocfield->fax;
        $doc['fax']             = $fdocfield->fax;
        $doc['doctornumber']    = $fdocfield->id;

        if(strlen($doc['last_name'])){
            $this->parsed_familydoctor=$doc;
        }


    }
}