<?php
/**
 * 
 * @author claudiu
 * missleading class name ... this class includes search for both MMI and client list
 *
 */
class Pms_MMI
{
    
    protected $_search_text;
    protected $_fulltextsearch;
    protected $_clientid;
    protected $_ipid;
    protected $_has_mmi;
    protected $_has_personal_list;
    protected $_maxresults;
    protected $_last_result;
    
    
    
    protected $_sourcepage;
    protected $_sourcepages = array(
        "receipt", 
        "medication",
    );
    
    protected $_mmi_url;
    protected $_mmi_licensekey;
    protected $_mmi_licensename;
    protected $_httpService;
    protected $_httpServiceAdapter;
    
    public function __construct($search_text = null , $fulltextsearch = 0 , $clientid = null, $ipid = null) {
        
        $this->_mmi_url = "http://" . Zend_Registry::get('mmilicserver') . "/rest/pharmindexv2";
        $this->_mmi_licensekey = Zend_Registry::get('mmilicserial');
        $this->_mmi_licensename = Zend_Registry::get('mmilicname');
        
        if (empty( $this->_httpService ) ) {
            $this->_httpService =  $this->initHttpService();
        }
        
        $this->setSearchText($search_text);
        
        $this->setFulltextSearch($fulltextsearch);
        
        $this->setClientid($clientid);
        
        $this->setIpid($ipid);
        
        $this->setHasMmi();
        
        $this->setHasPersonalList();
       
        $this->setMaxResults(100);
        
        $this->setSourcePage('medication');
        
    }

    //todo add the $options for adapter and client
    private function initHttpService($options = array('curloptions'=> array(), 'config' =>array())) {
        
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_RETURNTRANSFER  => true,
            )
        ));
        $this->_httpServiceAdapter = $adapter;
        
        $client = new Zend_Http_Client();
        $client->setAdapter($this->_httpServiceAdapter);

        return $client;
    }
    
    public function setMmiUrl($url) {
        $this->_mmi_url = $url;
    }
    
    public function setSearchText($searchtext) {
        $this->_search_text = $searchtext;
    }
    
    public function setFulltextSearch($fsearch) {
        $this->_fulltextsearch = (int)$fsearch;
    }
    
    public function setMaxResults($maxresults) {
        $this->_maxresults =  (int)$maxresults;
    }
    
    public function setSourcePage($sourcepage) {
        if (in_array($source_page, $this->_sourcepages)) {
            $this->_sourcepage = $sourcepage;
        } else {
            $this->_sourcepage = "medication"; // set default medication
        }
    }
    
    public function setIpid($ipid) {
        
        $this->_ipid = $ipid;
    }
    
    public function setClientid($clientid) {
        $this->_clientid = $clientid;
    }
    
    public function setHasMmi($has = null) {
        if ( ! is_null( $this->_clientid ) && is_null($has)) {
            $this->_has_mmi = Modules::checkModulePrivileges(87, $this->_clientid);
        } else {
            $this->_has_mmi = (int)$has;
        }
    }
    
    public function setHasPersonalList($has = true) {
        $this->_has_personal_list = (int)$has;
    }
    
    public function getLastResult() {
        return $this->_last_result;
    }
    
    private function _callServicePlatform($function = '', $params = null) {   
        
        $url = $this->_mmi_url . '/' . $function . '/' . $this->_mmi_licensekey . '/' . $this->_mmi_licensename . '/';
        
        if ( ! is_null($params)) {
            $params = urlencode(json_encode($params));
            $params = str_replace('+', '%20', $params);
        } else {
            $params = "{}";
        }
        $url = sprintf("%s%s", $url, $params);
        
        
        $this->_httpService->setUri($url);
        $this->_httpService->request("GET");
        $result = $this->_httpService->getLastResponse()->getBody();
        
        return $result;
    }

    /**
     * 
     * @param string $searchtext
     * @param number $clientid
     * @param string $source_page
     */
    public function getDrugList( $searchtext = null, $fulltextsearch = null, $ipid = null )
    {
        
        $searchtext     =  ! is_null($searchtext) ? $searchtext : $this->_search_text;
        $fulltextsearch =  ! is_null($fulltextsearch) ? $fulltextsearch : $this->_fulltextsearch;
        $ipid           =  ! is_null($ipid) ? $ipid : $this->_ipid;

        if (strlen($searchtext) == 0)  return; //nothing to search for

        $droparray = array();
        $result = array(); // this is what we return
        $i = 0;
        
        if ($this->_has_mmi) {
            
            $iknrGroupId =  null;
            if ( ! empty($ipid)) {
                $phi = new PatientHealthInsurance();
                $kvk_no_arr = $phi->get_kvk_no($ipid); 
                if(isset($kvk_no_arr[$ipid])) {
                    $iknrGroupId = self::_callServicePlatform('getInsuranceCompanyGroups', array(
                        'iknr' => $kvk_no_arr[$ipid]
                    )); 
                    $iknrGroupId = json_decode($iknrGroupId);
                    $iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;
                }
            }
            
            $searchParams = array();
            $searchParams['name'] = '[' . $searchtext . ']';
            $searchParams['moleculename'] = null;
            $searchParams['companyname'] = null;
            $searchParams['moleculetype'] = null;
            $searchParams['fulltextsearch'] = $fulltextsearch;
            $searchParams['disabledobjects'] = array();
            $searchParams['maxresult'] = $this->_maxresults;
            $searchParams['pzn_orlist'] = null;
            $searchParams['insurancegroupid'] = $iknrGroupId;
            $searchParams['assortment'] = null;
            $searchParams['tolerance'] = 0;
            $searchParams['sortorder'] = 'NONE';
            
            //  $searchtext= preg_match('/^\d$/', $searchtext); // only allow any digit 0-9
            if (is_numeric($searchtext) && strlen($searchtext) >= 7 && strlen($searchtext) <= 8) {
                $searchtext = ltrim(rtrim((string) $searchtext), ' 0'); // remove leading 0
                $searchParams['name'] = null;
                $searchParams['pzn_orlist'] = array($searchtext);
            }
            
            $return = self::_callServicePlatform('getProducts', $searchParams);
            
            if ($return) {
                
                $drop_array = json_decode($return);
                
                foreach ($drop_array->PRODUCT as $key => $val) {
                    $wirkstoffe = ''; // active substance = drug
                    
                    if ($this->_sourcepage == "receipt") {
                        
                        foreach ($val->PACKAGE_LIST as $f => $pval) {
                            $droparray[$i]['id'] = $i;
                            $droparray[$i]['name'] = $pval->NAME;
                            $droparray[$i]['comment'] = "";
                            $droparray[$i]['wirkstoffe'] = $wirkstoffe;
                            
                            $droparray[$i]['PZN'] = $pval->PZN;
                            $droparray[$i]['DBF_ID'] = $pval->ID;
                            $droparray[$i]['TYPE'] = 'mmi_receipt_dropdown';
                            
                            $i ++;
                        }
                    } else {
                        
                        $wirkstoffe_array = array();
                        
                        foreach ($val->ITEM_LIST[0]->COMPOSITIONELEMENTS_LIST as $ak => $av) {
                            if ($av->MOLECULETYPECODE == "A") {
                                $unit = $av->MOLECULEUNITCODE;
                                $name = $av->MOLECULENAME;
                                $mass = $av->MASSFROM;
                                if (! empty($name) || (! empty($mass) && ! empty($unit))) {
                                    $extra = '';
                                    if (! empty($mass) && ! empty($unit)) {
                                        $extra = "(" . $mass . " " . strtolower($unit) . ")";
                                    }
                                    $wirkstoffe_array[] = $name . $extra;
                                }
                            }
                        }
                        
                        $wirkstoffe = implode(", ", $wirkstoffe_array);
                        
                        $droparray[$i]['id'] = $i;
                        $droparray[$i]['name'] = $val->NAME;
                        $droparray[$i]['wirkstoffe'] = $wirkstoffe;
                        $droparray[$i]['comment'] = "";
                        
                        // @todo: need info on how to modify the packages
                        // !!! for now i use the first PZN ... same in php livesearch on inpout !!!!
                        // $droparray[$i]['PZN'] = $val->PACKAGE_LIST[0]->PZN;
                        $droparray[$i]['PZN'] = 0;
                        $droparray[$i]['DBF_ID'] = $val->PACKAGE_LIST[0]->ID;
                        $droparray[$i]['TYPE'] = 'mmi_notreceipt_dropdown';
                        
                        $i++;
                    }
                }
                
            }
            
            
            $result['mmi'] = $droparray;
        }
        
        if ( $this->_has_personal_list && ! empty($this->_clientid)) {
            // insert personal data
            $clientid = $this->_clientid;
            $querystr = "
    			     select m.id,m.name,m.pzn,m.comment, m.pkgsz from
            			(select distinct(name),min(id)as id,pzn,  package_size as pkgsz, comment as comment
            			from medication_master
            			where clientid = '" . $clientid . "'
            			and extra=0
            			and isdelete=0
            			group by name)as m
            			inner join medication_master b on m.id=b.id
            			where(trim(lower(m.name)) like trim(lower(:search_string)))
            			and isdelete=0
            			and clientid = '" . $clientid . "'
            			and extra=0";
            
            $manager = Doctrine_Manager::getInstance();
            $manager->setCurrentConnection('SYSDAT');
            $conn = $manager->getCurrentConnection();
            $query = $conn->prepare($querystr);
            $search_string = $searchtext . "%";
            $query->bindValue(':search_string', $search_string);
            $dropexec = $query->execute();
            $personal_drop_array = $query->fetchAll();
            
            $personal_droparray =  array();
            
            foreach ($personal_drop_array as $key => $val) {
                
                $personal_droparray[] = array(
                    'id' => $val['id'],
                    'name' => html_entity_decode($val['name'], ENT_QUOTES, "UTF-8"),
                    'comment' => html_entity_decode($val['comment'], ENT_QUOTES, "UTF-8"),
                    'PZN' => $val['pzn'],
                    'DBF_ID' =>$val['id'],
                );
            }
            
            $result['personal'] = $personal_droparray;
        } 
        
        $this->_last_result =  $result;
        
        return $result;
    }
}