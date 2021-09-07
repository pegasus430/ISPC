<?php
/*
 * @author al3x
 *  // Maria:: Migration ISPC to CISPC 08.08.2020
 * class to connect to my-pain.de start surveys & receive survey data
*/

class Pms_MyPain
{
    protected $_patient_id;
    protected $_patientchain;
    protected $_resumed;
    protected $_survey_id;
    protected $_project_id;
    protected $_dob_check;
    protected $_mypain_url;
    protected $_mypain_proxy;
    protected $_mypain_proxyport;
    protected $_mypain_proxytype;
    protected $_mypain_httpproxytunnel;
    protected $_mypain_name;
    protected $_mypain_local_key;
    protected $_mypain_central_name;
    protected $_mypain_central_key;
    protected $_mypain_central_ip;
    protected $_mypain_central_ip_v6;
    protected $_mypain_local_ip;
    protected $_mypain_local_ip_v6;
    protected $_mypain_date_format;
    protected $_mypain_time_drift;
    protected $_mypain_method;
    protected $_data;
    
    public function __construct() {
        
        
        if (Zend_Registry::isRegistered('mypain')) {
            $mypain_cfg = Zend_Registry::get('mypain');
            $this->_mypain_url = $mypain_cfg['service']['url'];
            $this->_mypain_proxy = $mypain_cfg['service']['proxy'];
            $this->_mypain_proxyport = $mypain_cfg['service']['proxyport'];
            $this->_mypain_proxytype = $mypain_cfg['service']['proxytype'];
            $this->_mypain_httpproxytunnel = $mypain_cfg['service']['httpproxytunnel'];
            $this->_mypain_name = $mypain_cfg['service']['name'];
            $this->_mypain_local_key = $mypain_cfg['service']['local_key'];
            $this->_mypain_central_name = $mypain_cfg['service']['central_name'];
            $this->_mypain_central_key = $mypain_cfg['service']['central_key'];
            $this->_mypain_central_ip = $mypain_cfg['service']['central_ip'];
            $this->_mypain_central_ip_v6 = $mypain_cfg['service']['central_ip_v6'];
            $this->_mypain_local_ip = $mypain_cfg['service']['local_ip'];
            $this->_mypain_local_ip_v6 = $mypain_cfg['service']['local_ip_v6'];
            $this->_mypain_date_format = $mypain_cfg['service']['date_format'];
            $this->_mypain_time_drift = $mypain_cfg['service']['time_drift'];
            
            //hardcoded stuff
            $this->setSurvey($mypain_cfg['ipos']['chain']);
            $this->setProject(0);
            $this->setResumed(false);
            $this->setDobCheck(false); // Changed from true to false  on 17.12.2019
            
            if (empty( $this->_httpService ) ) {
                $this->_httpService =  $this->initHttpService();
            }
            
        } else {
            echo 'No MyPain settings!';
            exit; //my-pain not configured
        }
    }
    
    private function _send_data() {
        
        $ipstr = $this->_mypain_central_ip.$this->_mypain_central_ip_v6;
        $datetime = gmdate($this->_mypain_date_format);
        $key = $this->_mypain_central_key;
        
        $str = $datetime.$ipstr.$key;
        
        $req = array();
        
        $req['request_auth_code'] = md5($str);
        $req['request_date_time'] = $datetime;
        
        
        $req['request_from'] = $this->_mypain_name;
        $req['request_to'] = $this->_mypain_central_name;
        
        
        $req['request_my_ip'] = array('v4' => $this->_mypain_central_ip, 'v6' => $this->_mypain_central_ip_v6);
        
        $data = array_merge($req, $this->_data);
        
        $data['document_type'] = 'mypain';
        $data['xml_version'] = 1;
        
        try
        {
            $array2xml = new Pms_Array2XML ();
            $xml_obj = $array2xml->createXML('root', $data);
            $xml_string = $xml_obj->saveXML();
            
        }
        catch (Exception $e)
        {
            throw new Exception('_create_simple_xml_v1: create xml error: '.$e->getMessage());
            
            return null;
        }
        
        $post = array(
            "data"=>	$xml_string,
        );
        
        $this->_httpService->setUri($this->_mypain_url);
        $this->_httpService->setParameterPost($post);
        $this->_httpService->request('POST');
        $raw_result = $this->_httpService->getLastResponse()->getBody();
        
        $results = $this->_read_data($raw_result);
        
        return $results;
    }
    
    private function _read_data($xml = null) {
        if (!$xml)
        {
            return null;
        }
        
        try
        {
            $xml2array = new Pms_XML2Array ();
            
            $data = $xml2array->createArray ( $xml );
        }
        catch (Exception $e)
        {
            
            throw new Exception('read_xml: parse xml error: '.$e->getMessage() . ', XML: ' . print_r($xml, true) );
            
            return null;
        }
        
        $data = $data['root'];

        $my_date = strtotime(gmdate('d.m.Y H:i:s'));
        
        $remote_date = strtotime($data['request_date_time']);
        
        $drift = abs($remote_date - $my_date);
        
        if ($drift > $this->_mypain_time_drift)
        {
            //clock diff is greater than allowed time drift
            
            throw new Exception( 'XmlService read_xml Clock difference: '.$drift.' seconds' );
            
            return null;
        }
        
        //$ipstr = $remote_ip['v4'].$remote_ip['v6'];
        
        $ipstr = $this->_mypain_local_ip.$this->_mypain_local_ip_v6;
        
        $str = $data['request_date_time'].$ipstr.$this->_mypain_local_key;
        $gen_auth = md5($str);
        
        if ($gen_auth != $data['request_auth_code'])
        {
            //auth code failed
            
            throw new Exception( 'XmlService read_xml Auth code mismatch, generated code: '.$gen_auth );
            
            return null;
        }
        
        
        return $data;
    }
    
    public function get_survey_data( $patient_id, $remote_chain )
    {
        
        $this->_data['request_method'] = 'get_survey_data';
        $this->_data['request_data']['patient_id'] = $patient_id;
        $this->_data['request_data']['remote_chain'] = $remote_chain;
       
        return $this->_send_data();
    }
    
    public function get_scores_data( $patient_id, $patient_chain_id )
    {
        
        $this->_data['request_method'] = 'get_scores_data';
        $this->_data['request_data']['patient_id'] = $patient_id;
        $this->_data['request_data']['remote_chain'] = $patient_chain_id;
       
        return $this->_send_data();
    }
    
    
    public function start_survey( $patient_id, $patient_chain_id)
    {
        
        $this->_data['request_method'] = 'start_survey';
        $this->_data['request_data']['patient_id'] = $patient_id;
        $this->_data['request_data']['remote_chain'] = $patient_chain_id;
        $this->_data['request_data']['qs_survey'] = $this->_survey_id;
        $this->_data['request_data']['sel_project'] = $this->_project_id;
        $this->_data['request_data']['dob_check'] = $this->_dob_check;
        $this->_data['request_data']['resume'] = $this->_resume;
       
        return $this->_send_data();
    }
    
    
    public function reset_survey( $patient_id )
    {
        
        $this->_data['request_method'] = 'reset_survey';
        $this->_data['request_data']['patient_id'] = $patient_id;
        $this->_data['request_data']['project'] = $this->_project_id;
        
        return $this->_send_data();
    }
    
    public function setPatient($patient_id = null) {
        if ( is_null( $this->_patientid ) && !is_null($patient_id)) {
            $this->_patientid =  (int) $patient_id;
        }
    }
    
    public function setPatientChain($patientchain = null) {
        if ( is_null( $this->_patientchain ) && !is_null($patientchain)) {
            $this->_patientchain =  (int) $patientchain;
        }
    }
    
    public function setSurvey($surveyid = null) {
        if ( is_null( $this->_survey_id ) && !is_null($surveyid)) {
            $this->_survey_id =  (int) $surveyid;
        }
    }
    
    public function setResumed($resumed = false) {
        if ( is_null( $this->_resumed ) && !is_null($resumed)) {
            $this->_resumed =  (int) $resumed;
        }
    }
    
    public function setDobCheck($dob_check = false) {
        if ( is_null( $this->_dob_check ) && !is_null($dob_check)) {
            $this->_dob_check =  (int) $dob_check;
        }
    }
    
    public function setProject($projectid = null) {
        if ( is_null( $this->_project_id ) && !is_null($projectid)) {
            $this->_project_id =  (int) $projectid;
        }
    }
    
    public function setMethod($method = null) {
        if ( is_null( $this->_method ) && !is_null($method)) {
            $this->_method =  $method;
        }
    }
    
    
    private function initHttpService($options = array('curloptions'=> array(), 'config' =>array())) {
        
        $curloptions = array(
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_RETURNTRANSFER  => true,
        );
        
        if(!empty($this->_mypain_proxy)) {
            $curloptions[CURLOPT_PROXY] = $this->_mypain_proxy;
            
            if(!empty($this->_mypain_proxyport)) {
                $curloptions[CURLOPT_PROXYPORT] = $this->_mypain_proxyport;
            }
            
            if(!empty($this->_mypain_proxytype)) {
                $curloptions[CURLOPT_PROXYTYPE] = $this->_mypain_proxytype;
            }
            
            if(!empty($this->_mypain_httpproxytunnel)) {
                $curloptions[CURLOPT_HTTPPROXYTUNNEL] = $this->_mypain_httpproxytunnel;
            }
        }
        
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => $curloptions
        ));
        $this->_httpServiceAdapter = $adapter;
        
        $client = new Zend_Http_Client();
        $client->setAdapter($this->_httpServiceAdapter);
        
        return $client;
    }
    
    
    public function send_ping()
    {
        $this->_data['request_method'] = 'ping';
         
        return $this->_send_data();
    }
}

?>