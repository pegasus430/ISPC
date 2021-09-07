<?php

/**
 * Class OntoDrugController
 *
 * This Controller is for the Drug-Safety-Check with OntoDrug
 * ISPC-2589 Ancuta 28.05.2020 [migration from clinic CISPC]
 * 
 */
class OntodrugController extends Zend_Controller_Action {

    // Layout
    public function init(){
        // $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax'); // das hier wieder einkommentieren ! wenn ich fertig bin

    }
    /**
     * this initial method opens the Popup-Window for the OntoDrug-Interface when the user clicks on the "OntoDrugCheck"-Button
     * at the same time it calls the methods for the needed patient-data an the method for the OntoDrugService
     * */

    public function getlogAction(){

        $f=file_get_contents(APPLICATION_PATH . '/../public/log/stats_ontodrug.log');
        echo "<pre>";
        echo $f;
        echo "</pre>";
        exit();

        }

    public function buttonlogAction(){

        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;
        $button = $_REQUEST['button'];
        $logger = new Zend_Log;
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/stats_ontodrug.log');
        $logger->addWriter($writer);
        $logger->log($button . " - Client:" . $clientid,0);
        echo "log success";
        exit();
    }


    public function patientontoAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $this->view->pid = Pms_Uuid::encrypt($decid);
        $ipid =Pms_CommonData::getIpid($decid);


        $patient=$this->getpdata($ipid); // needed patient data
        $patient=$this->diagnosestag($patient); // creates diagnosis-tags for the XML-Request

        $medpzn=array(); // Array with medication includes PZN
        $mednopzn=array();// Array with medication without PZN

        $medis =array();

        $allmedication = array();

        // in this step the medication from a patient is read and saved in medpzn or mednopzn
        if(isset($_POST['checked'])) {
            $checkedmedis = $_POST['checked'];
            foreach( $checkedmedis as $checked){
                if(!isset($_POST[$checked]))
                    continue;
                $allmedication = array_merge($allmedication, $_POST[$checked]);
            }
        }
        else {
            $allmedication = isset($_POST['medi']) ? $_POST['medi'] : array();
        }

        foreach($allmedication as $med){
            if($med[2]== "00000000"){
                $mednopzn[]= $med;
            }else if($med[2]== ""){
                $mednopzn[]= $med;
            }
            else {
                $medpzn[]= $med;
            }
        }

        $riskgroup= ($_POST['riskg']);

        $this->view->mednopzn=$mednopzn;


        $patient=$this->meditag($patient, $medpzn);


        $patient=$this->chemotag($patient, $riskgroup);
        $patient=$this->dialysetag($patient, $riskgroup);
        $return=$this->interaction($patient);
        try {
            $erg = $this->groupby($return);
        }catch(Error $e){
            echo"<h1>ERROR</h1>";
            echo "<pre>";
            echo htmlspecialchars(json_encode($patient));
            echo "\n\n\n\n";
            echo htmlspecialchars(json_encode($return));
            echo "</pre>";
            exit;
        }
        //$erg=$this->datenangui($return);
        $this->view->return=$erg;
        $this->view->checkedmedis = $checkedmedis;


    }


/*
    private function datenangui($simplexml, $grouped)
    {

        $warnings = array();

        // $simplexml->drugSafetyCheckMessages->drugSafetyCheckMessage;

        foreach($simplexml->drugSafetyCheckMessages->drugSafetyCheckMessage as $results){

            $warning=array();
            $warning['Modul'] =  $results->type->shortname->__toString(); // Modul

            $warning['Titel'] = $results->title->__toString(); // Betroffenes Medikament / betroffene Diagnose

            $warning['Beschreibung'] =  $results->description->__toString(); // Beschreibung der Warnung

            // Schweregrad nur bei Interaktionen

            $modul = $results->type->shortname->__toString();
            if($modul != "INTERACTION"){
                $warning['Refname'] =  $results->reference->description->__toString();
                $warning['Schweregrad'] = "";
                $warning['Wahrscheinlichkeit']= "";

            }else if($modul == "INTERACTION"){
                    $warning['Refname'] = $results->reference->source->name->__toString();
                    $warning['Schweregrad'] =  $results->severity->level->name->__toString();// nur bei Interaktionen !
                    $warning['Wahrscheinlichkeit']=  $results->data->interaction->likelihood->name->__toString() ;
                    $warning['Hinweis']=  $results->data->interaction->precaution->__toString() ; // Hinweis

            }
            $warnings[]=$warning;
        }

       return $warnings;

    }
*/
/** this method reads the simplexml from the out-message create by Onto-Drug-Service
 * all information will be prepared for the UI
*/

    public function groupby($simplexml)
    {
        $grouparray = array();
        $erg = array();

        foreach ($simplexml->drugSafetyCheckMessages->drugSafetyCheckMessage as $results) {

            $modul = $results->type->shortname->__toString();
            if ($modul == "CONTRAINDICATION") {
                $groupbyelement = $results->data->contraindication->medicine->code->description->__toString(); // Element for group by
                $groupkey = $results->data->contraindication->medicine->code->code->__toString(); // Element for group by
                $reason = $results->data->contraindication->diagnosis->code->description->__toString(); // Aufgrund von
                $description = $results->description->__toString(); // Beschreibung der Warnung
                $refname = $results->reference->description->__toString();
                $grouparray['CONTRAINDICATION'][$groupbyelement][] = array("Titel" => $reason, "Beschreibung" => $description, "Refname" => $refname, "Schweregrad" => "", "Wahrscheinlichkeit" => "", "Hinweis" => "");
            } elseif ($modul == "INTERACTION") {
                $groupbyelement = $results->data->interaction->medicine1->code->description->__toString(); // Element for group by
                $groupkey = $results->data->interaction->medicine1->code->code->__toString(); // Element for group by
                $reason = $results->data->interaction->medicine2->code->description->__toString(); // Aufgrund von
                /* $groupbyelement = $results->data->interaction->medicine->code->description->__toString(); // Element for group by
                 $groupkey = $results->data->interaction->medicine->code->code->__toString(); // Element for group by
                 $reason = $results->data->interaction->medicine[1]->code->description->__toString(); // Aufgrund von*/
                $description = $results->description->__toString(); // Beschreibung der Warnung
                $refname = $results->reference->source->name->__toString();
                $severity = $results->severity->level->name->__toString();// nur bei Interaktionen !
                $likelihood = $results->data->interaction->likelihood->name->__toString();
                $precaution = $results->data->interaction->precaution->__toString(); // Hinweis
                $grouparray['INTERACTION'][$groupbyelement][] = array("Titel" => $reason, "Beschreibung" => $description, "Refname" => $refname, "Schweregrad" => $severity, "Wahrscheinlichkeit" => $likelihood, "Hinweis" => $precaution);
            } else {
                $titel = $results->title->__toString(); // Betroffenes Medikament / betroffene Diagnose
                $refname = $results->reference->description->__toString();
                $description = $results->description->__toString(); // Beschreibung der Warnung
                $erg[] = array('Modul' => $modul, "Titel" => $titel, 'warningdetails' => array(array("Beschreibung" => $description, "Refname" => $refname, "Schweregrad" => "", "Wahrscheinlichkeit" => "", "Hinweis" => "")));
            }
        }

        foreach ($grouparray['CONTRAINDICATION'] as $titel => $value) {
            $erg[] = array('Modul' => 'CONTRAINDICATION', 'Titel' => $titel, 'warningdetails' => $value);
        }

        foreach ($grouparray['INTERACTION'] as $titel => $value) {
            $erg[] = array('Modul' => 'INTERACTION', 'Titel' => $titel, 'warningdetails' => $value);
        }

        return $erg;
    }
    // this get-method returns the patient data needed for the OntoDrug-Service

    public function getpdata($ipid)    {
        $patient = array();

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $patient['id']=$decid;


        $patientmaster= new PatientMaster();
        $test[]=$patientmaster->get_Masterdata_quick($ipid);

        $patient['nachname'] = $test[0]['last_name'];
        $patient['vorname'] = $test[0]['first_name'];
        $patient['geburtsdatum']=  $test[0]['dob'];
        $patient['alter']=  $test[0]['age']['years'];

        $patientmaster2= new PatientMaster();
        //$pid = $decid;
        $test2[]=$patientmaster2->getMasterData($pid=$decid, $istemplate = false, $showinf = NULL, $ipid , $isprint = null, $clone = false,$is_pdf_template = NULL,$print_target = 'html');

        $patient['title']=  $test2[0]['title'];
        $patient['anrede']=  $test2[0]['salutation'];

        if ($test2[0]['sex']==1){
            $patient['geschlecht']=  "M";
        }else if ($test2[0]['sex']==0){
            $patient['geschlecht']=  "F";}

        $vital_params=FormBlockVitalSigns::get_patients_chart($ipid,$period=false);
        $patient['groesse']=$vital_params[$ipid][0]['height'];
        $patient['gewicht']=$vital_params[$ipid][0]['weight'];
        $patient['bmi']=$vital_params[$ipid][0]['__bmi'];


        $aller = new PatientDrugPlanAllergies();
        $allergies = $aller->getPatientDrugPlanAllergies($pid = $decid , $ipid);


        $patient['allergies'] = $allergies[0]['allergies_comment'];

        $diag = new PatientDiagnosis();
        $diagnosis = $diag->get_multiple_patients_diagnosis($ipid);


        foreach($diagnosis AS $diag){
            $patient['diagnoses'][] = $diag['diagnosis']; // Beschreibung Freitext
        }


        $test = implode(', ', $patient[diagnoses]);

        foreach($diagnosis AS $diag_icd){
            $patient['diagnoses_icd'][] = $diag_icd['icdnumber'];
        }

        $patient['schwanger']= 0;
        $patient['etdate']= 0;
        $patient['stillzeit']= 0;

        $this->view->patient=$patient; // Übergabe an die View

        return $patient;
    }

    // this method is to get the complete diagnoses. The Interface OntoDrug need an Codesystem and an Code
    // The Codesystem of diagnosis is always 1.2.276.0.76.5.384 beause of the ICD10 Classification in OID
    // returns an array with all ICD Codes

    /**mit dieser Funktion wird in Abhängig der Anzahl der Diagnosen die Struktur tns:diagnosis für die Schnittstelle gebaut
     * der Aufbau des Tags für die Diagnose sieht wie folgt aus:
     *  <code>[string?]</code> --> konkreter ICD-Code
     * <codesystem>[string?]</codesystem> Codesystem für die ICD10 laut OID ist immer: 1.2.276.0.76.5.384
     * <description>[string?]</description> --> optional
     * <version>[string?]</version> --> optional
     */

   public function diagnosestag($patient)
   {
       $diagtest= array();

       foreach($patient['diagnoses_icd'] AS $diag){

           $diagtest[]='<diagnosis>
                    <code>
                    <code>'.$diag.'</code>
                    <codesystem>1.2.276.0.76.5.384</codesystem>
                    <description> </description>
                    <version> </version> 
                    </code>
                      </diagnosis> ';
       }
       $diagtag = implode(' ', $diagtest);

       $patient['diagtag']= $diagtag;
       return $patient;
   }

    // this method is to get the complete medication. The Interface OntoDrug need an Codesystem and an Code
    // The Codesystem of drug is always 1.2.276.0.76.4.6 beause of the PZN Classification in OID

   public function meditag($patient, $medpzn)
   {

       $meditag= array();

       foreach($medpzn AS $medi){

           $mtag[]='<medicine>
                    <code>
                    <code>'.$medi[2].'</code>
                    <codesystem>1.2.276.0.76.4.6</codesystem>
                    <description> </description>
                    <version> </version> 
                    </code>
                      </medicine> ';
       }
       $meditag = implode(' ', $mtag);

       $patient['meditag']= $meditag;
       return $patient;
   }

   public function chemotag($patient, $riskgroup){

       foreach($riskgroup as $risk){
       if ($risk['id']=="ch" & $risk['val']== "on" ){

                    $chemotag = "<riskGroup>
                                <code>
                                <code>CHEMOTHERAPY</code>
                                <codesystem>1.2.276.0.3.1.58.1.3</codesystem>
                                <description></description>
                                <version></version>
                             </code>
                             </riskGroup>";}
      // else{$chemotag="";}
       }

       $patient['chemo']= $chemotag;
       return $patient;
   }
    public function dialysetag($patient, $riskgroup){
        foreach($riskgroup as $risk) {
            if ($risk['id'] == "dia" & $risk['val'] == "on") {
                $dialysetag = "<riskGroup>
                        <code>
                                <code>DIALYSIS</code>
                                <codesystem>1.2.276.0.3.1.58.1.3</codesystem>
                                <description></description>
                                <version></version>
                        </code>
                    </riskGroup>";
            }
            //  else{$dialysetag="";}
        }
        $patient['dialyse']= $dialysetag ;
        return $patient;
    }


    public function interaction($patient)
    {
        $return = $this->callOntoDrugService($patient);
        return $return;
        // exit;
    }

    public function testAction(){
       $o=$this->callOntoDrugService([]);
       var_dump($o);
       exit();
}

    // Call  OntoDrug AMTS Service "drugSafetyCheckRequest"
    /* Parameters:
     * 1) ChecksToPerform(0..1): comma-seperated list of moduls that must be taken during the check
     * Bsp.: CONTRAINDICATION, INTERACTION. DEFAULT-: all check would be performed
     * 2) Patient: Patient Data
     * 3) Parameters: comma-seperated list of parameters , Bsp.: INTERACTION_NO_DUPLICATE
     * XML-Structure with all needed Paramters to fill in
     * */

    private function callOntoDrugService($patient){


       // $soapUrl = "http://localhost:7778/ontodrugamts/OntoDrugAMTSService";
        //var_dump(Zend_Registry::get('ontodruglicserver'));
        //var_Dump(Zend_Registry::get('mmilicserver'));
        $url = "http://" . Zend_Registry::get('ontodruglicserver') . "/ontodrugamts/OntoDrugAMTSService";

        //echo file_get_contents($url);//should return server info
        //echo"ENDE";
        //die();
$f=Zend_Registry::get('mmilicserver');
        $licensekey = Zend_Registry::get('ontodruglicserial');
        $licensename = Zend_Registry::get('ontodruglicname');
        
        $soapUrl = $url . '/' . $licensekey . '/' . $licensename . '/';

        //die($soapUrl);
        
        //$headers  "SOAPAction: //"SOAPAction: http://localhost:7778/ontodrugamts/OntoDrugAMTSService/check",
        $check_action = "http://" . Zend_Registry::get('ontodruglicserver') . "/ontodrugamts/OntoDrugAMTSService/check";
        $check_action_url = $check_action . '/' . $licensekey . '/' . $licensename . '/';
        
        // <check xmlns="http://service.ontodrug.com/OntoDrugAMTSService/">
        $check_xmlns = "http://" . Zend_Registry::get('ontodruglicserver') . "/ontodrugamts/OntoDrugAMTSService";
        $check_xmlns_url = $check_xmlns . '/' . $licensekey . '/' . $licensename . '/';


        // xml post structure
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                    <ns2:check xmlns:ns2="http://service.ontodrug.com/OntoDrugAMTSService/">
                    <ns2:checkRequest>
                    <checkToPerform>CONTRAINDICATION,INTERACTION,PRISCUS,DRIVEABILITY,DOUBLEMEDICATION,SIDEEFFECTS,RENALFAILURE</checkToPerform>
                    <parameters>NO_PRISCUS_IF_CONTRAINDICATION_EXISTS</parameters>
                    <patient>
                        <age>'.$patient['alter'].'</age>
                        <birthday>'.$patient['geburtsdatum'].'</birthday>
                        <weight>'.$patient['gewicht'].'</weight>
                        <height>'.$patient['groesse'].'</height>
                        <bmi>'.$patient['bmi'].'</bmi>
                        <firstname>'.$patient['vorname'].'</firstname>
                        <id>'.$patient['id'].'</id>
                        <lastname>'.$patient['nachname'].'</lastname>
                        <sex>'.$patient['geschlecht'].'</sex>
                        <diagnoses>'.$patient['diagtag'].'</diagnoses>
                        <prescriptions>'.$patient['meditag'].'</prescriptions>
                        <riskGroups>'.$patient['chemo'].' '.$patient['dialyse'].'</riskGroups>
                    </patient>
                    </ns2:checkRequest>
                    </ns2:check>
                    </soapenv:Body>
                    </soapenv:Envelope>';



            $headers = array(
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: '.$check_action_url.'",  //"SOAPAction: http://localhost:7778/ontodrugamts/OntoDrugAMTSService/check",
                "Content-length: " . strlen($xml_post_string),
            );

            //$url = $check_action_url;

            // PHP cURL for SOAP POST
        $s=function_exists(curl_init);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // converting
            $response = curl_exec($ch);

            curl_close($ch);
            // convertingc to XML
            preg_match('/<ns2:check>(.*)<\/ns2:check>/s', $response, $matches);
            $out=simplexml_load_string($matches[0]);

            return $out;

        }



}
