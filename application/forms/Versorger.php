<?php
/**
 * 
 * @author claudiu 
 * Jul 6, 2018
 * 
 * class was original from Nico.. it is now a full re-write
 *
 * TODO generate_patient_exportpackage()
 *
 */
class Application_Form_Versorger extends Pms_Form
{
    
    private $ipid = null;
    
    private $enc_id = null;
    
    private $_categoriesForms = null;
    
    private $_categoriesForms_belongsTo = 'patientProvider';
    
    private $categories = null;
    
    private $_init_onlyThisModel = null;
    
    private $_is_clinic_sync = false; //set this to true for all boxes and all cols, metadata..
    
    public function __construct($options = null, $ipid = null) 
    {
        if (isset($options['_is_clinic_sync'])) {
            $this->_is_clinic_sync = (bool) $options['_is_clinic_sync'];
            unset($options['_is_clinic_sync']);
        }
        
        if ( ! empty($options['_onlyThisModel'])) {
            $this->_init_onlyThisModel = $options['_onlyThisModel'];
            unset($options['_onlyThisModel']);
        }
    
        parent::__construct($options);
    
        $options = func_get_arg(0);
        $ipid = func_get_arg(1);
    
        $patientMasterData = ! empty($options['_patientMasterData']) ? $options['_patientMasterData'] : null;
    
        if ( ! empty($patientMasterData)) {
            $this->enc_id = Pms_Uuid::encrypt($patientMasterData['id']);
        }
    
    
        if (empty($ipid)) {
            if (empty($patientMasterData) || empty($patientMasterData['ipid'])) {
                throw new Zend_Exception('Admin was informed, something is wrong an we cannot display this patient - err:1.11', 3);
            } else {
                $ipid = $patientMasterData['ipid'];
            }
        }
    
        $this->ipid = $ipid;
    
        if ($this->_is_clinic_sync) {
            
            $this->_init_clinic_sync();
            
        } else {
            //this is from view, be sure this is the patient we asserted permissions for
            $last_ipid_session = new Zend_Session_Namespace('last_ipid');
            
            if ($last_ipid_session->ipid != $this->ipid) {
                throw new Zend_Exception('Admin was informed, something is wrong an we cannot display this patient - err:2.22', 3);
            }
        }
    
        $this->_categoriesForms = new stdClass();
    
        $this->_set_default_categories();
    
        $this->_init_categories();
    
    }
    
    
    public function get_default_categories() {
        return $this->categories;
    }

    private function _set_default_categories()
    {
        /*
         * 'extra_form_ID' => 999, MUST be ExtraForms->id... so we kow what boxes to __init / show / hide
         *
         * 'placement' => 'left'
         * box is not displayed if you don't have a placement
         * you must start with a placement left, or they are reversed; TODO: fix this in controller if you have a user-order
         *
         * 'multipleEntries' => false, if one per pattient, like one familyDoctor
         *
         * relation, clinic_reversed_logic, clinic_mapped_key added to map columns in nico style
         * 
         * 
         * Application_Form_PatientDetails
         * 
         * 'inlineEdit' => this will bypass multipleEntries, and display the editForm instead of `extract`
         * 
         * 'extractEscape' => optional, set false if you have vsprintf in the getVersorgerExtract , or you manual extract (is not auto-set), default true
         * 
         * 'hasHistory' => optional, set true if you have BoxHistory, default false
         * 
         * '__motherForm' => optional, this box is from a mother form, used on _reConstruct
         * 
         * '__linked_categories' => optional,
         * when you update one of this categories, you must fetch data also from the linked ones
         * linked ones will have some values changed by the key category
         *
         */
        
        $this->categories = array(
        
            //Verordner
            "FamilyDoctor" => array(
                'extra_form_ID'     => 9, 
                'placement'         => 'left', 
                'multipleEntries'   => false ,
                
                'relation'          => 'PatientMaster',
                'clinic_mapped_key' => 'family_doctor',
                'clinic_reversed_logic' => false,
            ),
        
            //Fachärzt
            "PatientSpecialists" => array(
                'extra_form_ID'     => 44, 
                'placement'         => 'left', 
                'multipleEntries'   => true,
                
                'relation'          => 'Specialists', 
                'clinic_mapped_key' => 'specialists', 
                'clinic_reversed_logic' => true,
            ),
        
            //Krankenkasse
            "PatientHealthInsurance" => array(
                'extra_form_ID'     => 10, 
                'placement'         => 'left', 
                'multipleEntries'   => false, 
                
                'relation'          => 'HealthInsurance',
                'clinic_mapped_key' => 'health_insurance',
                'clinic_reversed_logic' => false,
            ),
        
            //Pflegedienst
            "PatientPflegedienste" => array(
                'extra_form_ID'     => 15,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                'relation'          => 'Pflegedienstes',
                'clinic_mapped_key' => 'pflegedienst',
                'clinic_reversed_logic' => true,
            ),
        
            //Sanitätshäuser
            "PatientSupplies"  => array(
                'extra_form_ID'     => 41,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                'relation'          => 'Supplies',
                'clinic_mapped_key' => 'supplies',
                'clinic_reversed_logic' => true,
            ),
        
            //Apotheke
            "PatientPharmacy" => array(
                'extra_form_ID'     => 26,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                'relation'          => 'Pharmacy',
                'clinic_mapped_key' => 'pharmacy',
                'clinic_reversed_logic' => true,
            ),
        
            //sonst. Versorger
            "PatientSuppliers" => array(
                'extra_form_ID'     => 46,
                'placement'         => 'right',
                'multipleEntries'   => true,
                'relation'          => 'Suppliers',
                
                'clinic_mapped_key' => 'suppliers',
                'clinic_reversed_logic' => true,
            ), //not to be confused with the previous
        
            //Homecare
            "PatientHomecare" => array(
                'extra_form_ID'     => 48,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                'relation'          => 'Homecare',
                'clinic_mapped_key' => 'homecare',
                'clinic_reversed_logic' => true,
            ),
        
            //Physiotherapeuten
            "PatientPhysiotherapist" => array(
                'extra_form_ID'     => 47,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                'relation'          => 'Physiotherapists',
                'clinic_mapped_key' => 'physiotherapists',
                'clinic_reversed_logic' => true,
            ),
        
            //Ehrenamtlichen / Koordinator
            "PatientVoluntaryworkers" => array(
                'extra_form_ID'     => 38,
                'placement'         => 'third', //ISPC-2703,elena,05.01.2021
                'multipleEntries'   => true,
                
                'relation'          => 'Voluntaryworkers',
                'clinic_mapped_key' => null,
                'clinic_reversed_logic' => null,
            ),
        
            //Pfarreien
            "PatientChurches" => array(
                'extra_form_ID'     => 53,
                'placement'         => 'third',//ISPC-2703,elena,05.01.2021
                'multipleEntries'   => true,
                
                'relation'          => 'Churches',
                'clinic_mapped_key' => null,
                'clinic_reversed_logic' => null,
            ),
        
            //Hospizdienst
            "PatientHospiceassociation" => array(
                'extra_form_ID'     => 42,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                'relation'          => 'Hospiceassociation',
                'clinic_mapped_key' => 'hospice_association',
                'clinic_reversed_logic' => true,
            ),
        
            //Hilfsmittel II
            //PatientRemedies2Supplies
            "PatientRemedies" => array(
                'extra_form_ID'     => 49,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                'relation'          => 'Supplies',
                'clinic_mapped_key' => null,
                'clinic_reversed_logic' => null,
            ),
        	
            //ISPC-2667 Lore 21.09.2020
            //Pflegeversicherung == care_insurance
            "PatientCareInsurance" => array(
                'extra_form_ID'     => 103,
                'placement'         => 'right',
                'multipleEntries'   => false,
                
                //'relation'          => '',
                'clinic_mapped_key' => null,
                'clinic_reversed_logic' => null,
            ),
        		
        	//ISPC-2672 Carmen 21.10.2020
        	//Kindergarten == kindergarten
        	"PatientKindergarten" => array(
        			'extra_form_ID'     => 105,
        			'placement'         => 'left',
        			'multipleEntries'   => true,
        	
        			//'relation'          => '',
        			'clinic_mapped_key' => 'kindergarten',
        			'clinic_reversed_logic' => null,
        	),
            
            //ISPC-2672 Lore 22.10.2020
            //Spielgruppe == playgroup
            "PatientPlaygroup" => array(
                'extra_form_ID'     => 106,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                //'relation'          => '',
                'clinic_mapped_key' => 'playgroup',
                'clinic_reversed_logic' => null,
            ),
            
            //ISPC-2672 Lore 23.10.2020
            //Schule == school
            "PatientSchool" => array(
                'extra_form_ID'     => 107,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                //'relation'          => '',
                'clinic_mapped_key' => 'school',
                'clinic_reversed_logic' => null,
            ),
            
            //ISPC-2672 Lore 26.10.2020
            //Werkstatt für behinderte Menschen == Workshop for disabled people
            "PatientWorkshopDisabledPeople" => array(
                'extra_form_ID'     => 108,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                //'relation'          => '',
                'clinic_mapped_key' => 'workshop_for_disabled_people',
                'clinic_reversed_logic' => null,
            ),
            
            //ISPC-2672 Lore 26.10.2020
            //Sonstiges == other
            "PatientOtherSuppliers" => array(
                'extra_form_ID'     => 109,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                //'relation'          => '',
                'clinic_mapped_key' => 'othersupplier',
                'clinic_reversed_logic' => null,
            ),
            
            //ISPC-2672 Lore 26.10.2020
            //SAPV Team
            "PatientSapvTeam" => array(
                'extra_form_ID'     => 110,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                //'relation'          => '',
                'clinic_mapped_key' => 'sapv_team',
                'clinic_reversed_logic' => null,
            ),
            
            //ISPC-2672 Lore 26.10.2020
            //Kinderhospiz == Childrens hospice
            "PatientChildrensHospice" => array(
                'extra_form_ID'     => 111,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                //'relation'          => '',
                'clinic_mapped_key' => 'childrens_hospice',
                'clinic_reversed_logic' => null,
            ),
            
            //ISPC-2672 Lore 26.10.2020
            //Familien unterstützender Dienst == Family Support Service
            "PatientFamilySupportService" => array(
                'extra_form_ID'     => 112,
                'placement'         => 'right',
                'multipleEntries'   => true,
                
                //'relation'          => '',
                'clinic_mapped_key' => 'family_support_service',
                'clinic_reversed_logic' => null,
            ),
        		
        	//ISPC-2672 Carmen 26.10.2020
        	//Ambulanter Kinderhospizdienst == ambulant_children_hospice_service
        	"PatientAmbulantChildrenHospiceService" => array(
        			'extra_form_ID'     => 113,
        			'placement'         => 'left',
        			'multipleEntries'   => true,
        				 
        			//'relation'          => '',
        			'clinic_mapped_key' => 'ambulant_children_hospice_service',
        			'clinic_reversed_logic' => null,
        	),
        		
        	//ISPC-2672 Carmen 26.10.2020
        	//Jugendamt == youth_welfare_office
        	"PatientYouthWelfareOffice" => array(
        			'extra_form_ID'     => 114,
        			'placement'         => 'left',
        			'multipleEntries'   => true,
        			 
        			//'relation'          => '',
        			'clinic_mapped_key' => 'youth_welfare_office',
        			'clinic_reversed_logic' => null,
        	),
        		
        	//ISPC-2672 Carmen 26.10.2020
        	//Eingliederungshilfe == integration_assistance
        	"PatientIntegrationAssistance" => array(
        			'extra_form_ID'     => 115,
        			'placement'         => 'left',
        			'multipleEntries'   => true,
        				 
        			//'relation'          => '',
        			'clinic_mapped_key' => 'integration_assistance',
        			'clinic_reversed_logic' => null,
        	),
            
        	//SYSTEMSYNC
        	"SYSTEMSYNC" => array(
        	    'extra_form_ID'     => 50,
        	    'placement'         => 'left',
        	    'multipleEntries'   => false,
        	    'inlineEdit'        => true,
        	    'extractEscape'     => false,
        	    'hasHistory'        => false,
        	),
        
        );
        
        
        //filter by client allowed oxes
        if ( ! empty($this->_clientForms)) {
            foreach ($this->categories as $cat => $val) {
                if ( ! isset($this->_clientForms[$val['extra_form_ID']])
                    || ! $this->_clientForms[$val['extra_form_ID']] )
                {
                    unset($this->categories[$cat]);
                    //(goto /extraforms/formlist to assign boxes)
                }
            }
        }
    }
    
    
    /**
     * 
     * this fn was created JUST for the updatePatientVersorger, to reload data
     * ! NOT to be used on unrelated fn
     */
    private function _reConstruct() 
    {
        
        $patientmaster = new PatientMaster();
        $patientmaster->getMasterData(null, 1, null, $this->ipid); //Patient header
        
        $this->_patientMasterData = $patientmaster->get_patientMasterData();
        
        $this->_init_categories();

    }

    public function getAllCategories()
    {
        return $this->categories;
    }
    
    
    public function getPatientData($ipid = null)
    {
        if (is_null($ipid)) {
            $ipid= $this->ipid;
        }
        
        $output=array();
        
        $meta_categorys = array();
       
        foreach ($this->categories as $catkey=>$cat) {
            
            $catrows=array();
            $addnewDialogHtml = '';
            
            if (isset($this->_patientMasterData[$cat['table']]) 
                && ! empty($this->_patientMasterData[$cat['table']]) 
                && is_array($this->_patientMasterData[$cat['table']])) 
            {
                foreach ($this->_patientMasterData[$cat['table']] as $mrow) {
   
                    if (isset($cat['addnewDialogHtml'])) {
                        unset($cat['addnewDialogHtml']);
                    }
                    
                    $catrows[] = $this->_aggregate_row($mrow, $catkey, $cat, $mrow);
                  
                }
                
                $output[$catkey]=$catrows;
                
            } else {
                
                if (isset($cat['addnewDialogHtml'])) {
                    unset($cat['addnewDialogHtml']);
                }
                
                $catrows[] = $this->_aggregate_row(array(), $catkey, $cat, array());
                $output[$catkey] = $catrows;
            }
            
            $meta_categorys[] = $cat;
            
        }
        
        $output['__meta-timestamp'] = true;
        $output['__meta-categorys'] = $this->categories;
        
        return $output;
    }
    

    /**
     * NOT USED
     * @cla on 04.09.2018
     * @return Ambigous <multitype:, multitype:unknown multitype:string unknown  , multitype:number string boolean , multitype:number string boolean NULL , NULL, multitype:unknown NULL string multitype:multitype:unknown   >
     */
    public function prepare_export_data_4_clinic()
    {        
        $this->_is_clinic_sync = true;
        
        $newdata=array();//this is styled 4 nico 
        
        $data = $this->getPatientData();
        
//         $this->_recursive_unset($data, 'extra_form_ID');
//         $this->_recursive_unset($data['__meta-categorys'], 'extra_form_ID');
//         $this->_recursive_unset($data['__meta-categorys'], 'placement');
//         $this->_recursive_unset($data['__meta-categorys'], 'multipleEntries');
        
        $create_date = Pms_CommonData::array_column_recursive($data, "create_date");
        $change_date = Pms_CommonData::array_column_recursive($data, "change_date");
        $timestamp = array_unique(array_merge($create_date, $change_date));
        arsort($timestamp);
        $newdata['__meta-timestamp'] = strtotime(reset($timestamp));
        
        
        foreach ($data['__meta-categorys'] as $category => $catdetails)
        {
            if (empty($catdetails['clinic_mapped_key']) || ! isset($data[$category])) {
                continue;
            }
            
            $nico_catdetails = [];
            
            $relations = $catdetails['relations']; 
            
            $key = $table = $cols = 
            $map_key = $map_local = $map_foreign = $map_table = $map_cols = null;
           
            if ($catdetails['clinic_reversed_logic']) {
                
                $table = $catdetails['relation'];
                $cols = $catdetails['relations'] [$catdetails['relation']] ['__columns'] ;
                
                $map_table = $category;
                $map_key = $catdetails['relations'] [$catdetails['relation']] ['local'];
                $map_local = $catdetails['relations'] [$catdetails['relation']] ['local'];
                $map_foreign = $catdetails['relations'] [$catdetails['relation']] ['foreign'];
                $map_cols = $catdetails['cols'];
                
            } else {
                
                $table = $category;
                $cols = $catdetails['cols'];
                
                if (isset($catdetails['relation']) && isset($catdetails['relations'] [$catdetails['relation']])) {
                    $map_table = $catdetails['relation'];
                    $map_key = $catdetails['relations'] [$catdetails['relation']]['foreign'];
                    $map_cols = $catdetails['relations'] [$catdetails['relation']] ['__columns'];
                }
                
            }
            
            //nico items => ispc userDefinedList
            if (isset($catdetails['userDefinedList']) && ($cols_items = array_intersect_key($cols, $catdetails['userDefinedList']))) {
                foreach ($cols_items as $k=>$v) {
                    $cols[$k]['items'] = $catdetails['userDefinedList'][$k];
                } 
            }            
            
            $nico_catdetails['table'] = $table;
            $nico_catdetails['cols'] = $cols;
            $nico_catdetails['patientmapping'] = [
                "table"     => $map_table,
                "key"       => $map_key,
                "ipid"      => "ipid",
                "addcols"   => $map_cols
            ];
            
            $newdata['__meta-categorys'][$catdetails['clinic_mapped_key']] = $nico_catdetails;
            
        }
        
        foreach ($this->categories as $category => $catdetails) 
        {
            if (empty($catdetails['clinic_mapped_key']) || ! isset($data[$category])) {
                continue;
            }
            
            
            $cnt_row = 0;
            
            foreach($data[$category] as $row) {
                
                if ($catdetails['clinic_reversed_logic']) {
                    
                    if (isset($catdetails['relation'])){
                        $clinic_reversed_logic = $catdetails['relation'];
                    } else {
                        $relations = $this->categories[$category]['relations'];
                        $relations_intersect = array_intersect_key($relations, $row['data']);
                        $clinic_reversed_logic = reset(array_keys($relations_intersect));
                    }
                    $newdata[$catdetails['clinic_mapped_key']][$cnt_row]['data'] = $row['data'][$clinic_reversed_logic];
                } else {
                    $newdata[$catdetails['clinic_mapped_key']][$cnt_row]['data'] = $row['data'];
                }
                
                $cnt_row++;
            }            
        }
        
        return $newdata;
    }
    
    
    
    
    /**
     * dangerous unset
     * 
     * @param unknown $array
     * @param unknown $unwanted_key
     */
    private function _recursive_unset(&$array, $unwanted_key) {
        unset($array[$unwanted_key]);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->_recursive_unset($value, $unwanted_key);
            }
        }
    }
    

    
    private function _aggregate_row($row, $catkey, $cat, $prow)
    {
        $myrow = $row;
    
        if ($this->_is_clinic_sync && APPLICATION_ENV ) 
        {
//             if ( ! isset($myrow['_id'])) {
//                     $myrow['_id'] = $row['id'];
//             }
            
//             if ( ! isset($myrow['_pid'])) {
//                     $myrow['_pid'] = $prow['id'];
//             }    
        
            if(isset($prow['create_date'])){
                $myrow['__pcreate_date'] = $prow['create_date'];
            }
        
            if(isset($prow['change_date'])){
                $myrow['__pchange_date'] = $prow['change_date'];
            }
        }
    
        $extract = $this->_getExtract($catkey, $myrow);
    
        $address = $this->_getAddress($catkey, $myrow);

        
        if (isset($myrow['editDialogHtml'])) {
            $editDialogHtml = $myrow['editDialogHtml'];
            unset($myrow['editDialogHtml']);
        } else {
            $editDialogHtml = null;
        }
        
        
        if ($this->_is_clinic_sync) {
            unset($cat['placement'], $cat['multipleEntries'], $cat['label'], $cat['extract'], $cat['address']);
        }
        elseif ( ! $this->_is_clinic_sync && APPLICATION_ENV !== "development") {       
            unset($cat['cols']); // do not send this to the view
        }
        
        
        $out=array(
//             'data'              => $myrow, // do not send this to the view
//             'editDialogHtml'    => $editDialogHtml, //this is sent only to view, not to sync
//             'extract'           => $extract,//this is sent only to view, not to sync
//             'address'           => $address,//this is sent only to view, not to sync
//             'meta'              => $cat,
        );
        
        
        if ($this->_is_clinic_sync) {
            
            /**
             * this was assigned with a manual key of `master`
             */
            if ($catkey == 'PatientSpecialists' && ! isset($myrow['Specialists']) && isset($myrow['master'])) {
                $myrow['Specialists'] = $myrow['master'];
                unset($myrow['master']);
            }
            
            /**
             * this was assigned with a manual key of `company`
             */
            if ($catkey == 'PatientHealthInsurance' && ! isset($myrow['HealthInsurance']) && isset($myrow['company'])) {
                $myrow['HealthInsurance'] = $myrow['company'];
                unset($myrow['company']);
            }
            
            //ISPC-2381 Carmen 27.01.2021
            if ($catkey == 'PatientRemedies')
            {
            	$out['data'] = $myrow;
            }
            else 
            {
            
            $out['editDialogHtml'] = $editDialogHtml;
            $out['extract'] = $extract;
            $out['address'] = $address;
            $out['meta'] = $cat;
            }
            //--
            
        } else {
            $out['editDialogHtml'] = $editDialogHtml;
            $out['extract'] = $extract;
            $out['address'] = $address;
            $out['meta'] = $cat;
        }
    
        return $out;
    }
    
    
    
    
    
    
    
    
    
    private function _getExtract($catkey, $row)
    {
        $extractmap = $this->categories[$catkey]['extract'];
        
        $userDefinedList = $this->categories[$catkey]['userDefinedList'];
                
        $rows=array();
        
        foreach ($extractmap as $rowmap) {
            
            $parts = array();
            
            foreach ($rowmap["cols"] as $col_key => $col) {
            
                if (is_numeric($col_key)) {         
                               
                    $part = $row[$col];
                    
                } else {   
                    
                    $part = $row[$col_key][$col];
                    
                }
                if ( ! empty($userDefinedList) && isset($userDefinedList[$col])) { 
                    
                    if (isset($userDefinedList[$col][$part])) {
                        $part = $userDefinedList[$col][$part];
                    } else {
                        //something is wrong... why is this missing from the list?
                        $part = '-- missing --';
                        $part = '';
                        
                    }
                }
                
                $parts[] = $part;
                
            }
            
            
            
            
            $text = trim(implode(" ", $parts));
            
            if (strlen($text) > 0) {
                
                $rows[] = array($rowmap["label"], $text);
            }
        }
        
        return $rows;
    }
    
    
    private function _getAddress($catkey, $row)
    {
        $extractmap = $this->categories[$catkey]['address'];
        
        $userDefinedList = $this->categories[$catkey]['userDefinedList'];
        
        $rows=array();
        
        foreach ($extractmap as $rowmap) {
        
            $parts = array();
        
            foreach ($rowmap as $cols) {
                
                foreach ($cols as $col_key => $col) {
                    
                    if (is_numeric($col_key)) {
                        $part = $row[$col];
            
                    } else {
            
                        $part = $row[$col_key][$col];
            
                    }
            
                    if ( ! empty($userDefinedList) && isset($userDefinedList[$col]) && isset($userDefinedList[$col][$part])) {
                        $part = $userDefinedList[$col][$part];
                    }
            
                    $parts[] = $part;
                }
        
            }
        
        
        
            $text = trim(implode(" ", $parts));
            
        
            if (strlen($text) > 0) {
        
                $rows[] = $text;
            }
        }
        
        return implode("\n", $rows) ;
        
    }
    
  


    
    /**
     * --- Chanegeing... ! SINGLE record update/insert
     * 
     * HOW TO VALIUDATE MULTIPLE SUBFORM IN THE SAME POST ??? 
     * validates a subform and return error if that is wrong ???????
     * 
     * @param array $post
     * @param string $ipid , optional
     * @return boolean|unknown
     */
    public function updatePatientVersorger($post = array() , $ipid = null)
    {
        if (empty($ipid)) {
            $ipid = $this->ipid;
        }

        $result = false;
        
        foreach ($post[$this->_categoriesForms_belongsTo] as $key => $data) {

            $model = $key;
            
            if ( ! isset($this->_categoriesForms->$model) && ! empty($post['__category'])) {
                
                $model = $post['__category'];
            }
           
            if (isset($this->_categoriesForms->$model) ) 
            if (isset($this->_categoriesForms->$model) 
                && $this->_categoriesForms->$model instanceof Pms_Form )
            {
                
                $result = $this ->_callFormSaveTriggers($model, $key, $data , $ipid);
            }
        }
        
        
        if ($result === true) {
            
            $this->_reConstruct();
            
            return true;
            
        } elseif ($result === false) {
            
            return false;
            
        } else {
            
            return $result;
        }
                
    }
    
    
    private function _callFormSaveTriggers($model, $key, $data = array(), $ipid = null)
    {
        if (empty($ipid)) {
            $ipid = $this->ipid;
        }
        
//         if (is_array($data) && count($data) != count($data, COUNT_RECURSIVE))
//         {
//             foreach ($data as $data_walk) {
                
//                 //i have a bug in PatientHealthInsurance2Subdivisions... an extra [] .. hence this next if
//                 if (empty($data_walk) || ! is_array($data_walk)) continue;  

//                 ddecho($data_walk);
//                 $this->_callFormSaveTriggers($model, $key, $data_walk, $ipid);
//             }
//         }
        
        $validatedForm = $this->_categoriesForms->$model->triggerValidateFunction($key, array($ipid, $data));
        
        
        if ($validatedForm === true || $validatedForm instanceof Zend_Form){
            
            if ($validatedForm instanceof Zend_Form) {
                $data = $validatedForm->getValidValues($data, true);
            }
            
            $out = $this->_categoriesForms->$model->triggerSaveFunction($key, array($ipid, $data));
        
            if ($out !== false) {
        
                $result = true;
        
            } else {
                //failed to save..return what?
                $result = false;
            }
        } else {
            //validate failed, return form as string
            $result = $validatedForm;
            //return $this->_categoriesForms->$model->getErrorMessages();
        }
        
        return $result;
        
    }
    
    /**
     * 
     * Special case tables are as of 30.06.2018:
     * FamilyDoctor, that has id in PatientMaster
     * 
     * @param array $post
     * @param string $ipid, p
     * @return boolean
     */
    public function deletePatientVersorger($post = array() , $ipid = null)
    {
        if (empty($ipid)) {
            $ipid = $this->ipid;
        }
        
        
        foreach ($post[$this->_categoriesForms_belongsTo] as $key => $data) {
        
            $model = $key;
        
            if ( ! isset($this->_categoriesForms->$model) && ! empty($post['__category'])) {
        
                $model = $post['__category'];
            }
        
            
            if (isset($this->_categoriesForms->$model)
                && $this->_categoriesForms->$model instanceof Pms_Form )
            {
                
                switch ($model) {
                    
                    case "FamilyDoctor" :
                        
                        $this->_deleteFamilyDoctor($data, $ipid);
                        
                        break;
                        
                    default:
                        
                        $primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
                        
                        $fn = "findOneBy{$primaryKey}AndIpid";
                        
                        if ($entity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
                            $entity->delete();
                        }
                        $this->_reConstruct();
                        
                        return true;       
                }
            }
        }
        
        
        $this->_reConstruct();
        
        return true;
    }
    
    
    /**
     * custom delete method for this $model = "FamilyDoctor";
     * 
     * @param array $data
     * @param string $ipid
     * @return boolean
     */
    private function _deleteFamilyDoctor($data = array(), $ipid = '')
    {
        if (empty($data) || empty($ipid)) {
            return false;
        }
        
        $model = "FamilyDoctor";
        
        $primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
        
        $q_updateFamilydocID = Doctrine_Query::create()
        ->update("PatientMaster")
        ->set('familydoc_id', Doctrine::NULL_NATURAL )
        ->where('ipid = ?', $ipid)
        ->andWhere('familydoc_id = ?', $data[$primaryKey])
        ->limit(1)//limit added because...
        ->execute()
        ;
        
        if ($q_updateFamilydocID) {
            $q_updateDelFamilydoc = Doctrine_Query::create()
            ->update($model)
            ->set('isdelete', 1 )
            ->where('id = ?', $data[$primaryKey])
            ->andWhere('indrop = 1')
            ->execute()
            ;
            
            return true;
        }
        
        return false;
    }
    
    
    
    
    
    

    /**
     * !! this is NOT used, Nico's original
     * 
     * Place Versorger into a Sync-Packet      
     */
    public function generate_patient_exportpackage($ipid)
    {
        
        $mydata=$this->getPatientData($ipid);
        $data_inside=false;
        foreach($this->categories as $cc=>$foo){
            if (count($mydata[$cc])>0){
                $data_inside=true;
                break;
            }
        }
        if($data_inside){
            SystemsSyncPackets::createPacket($ipid, array('versorger'=>$mydata, 'date'=>date('d.m.Y')), "versorger", 1);
        }
    }

    /**
     * !! this is NOT used, Nico's original
     * 
     * Grab the most recent SyncPacket and update the Patient with this dataset
     */
    function update_patient_from_exportpackage($ipid){
        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('SystemsSyncPackets')
            ->where('ipid=?',$ipid)
            ->andwhere('outgoing=0')
            ->andwhere('actionname=?','versorger')
            ->orderBy('id DESC')
            ->limit(1);     //all we are interested in is the most recent entry
        $vdata = $sql->fetchArray();

        if($vdata && $vdata[0]['done']==0) {
            $payload = $vdata[0]['payload'];
            $versorger = json_decode($payload, 1);
            $this->updatePatientData($ipid, $versorger['versorger']);
            $myentry = Doctrine::getTable('SystemsSyncPackets')->findOneBy('id', $vdata[0]['id']);
            $myentry->done = 1;
            $myentry->save();
        }
    }


    /**
     * NICO's original, what's the purpose of this ?
     */
    public function getTimestamp($ipid = null)
    {
        return true;
        
        if (is_null($ipid)) {
            $ipid= $this->ipid;
        }
        
        $ts=PatientVersorger::getEntry($ipid, '__timestamp');
        $time=0;
        if(count($ts)<1){

        }else{
            $time=$ts['time'];
        }
        return $time;
    }

    /**
     * NICO's original, what's the purpose of this ?
     */
    public function updateTimestamp($ipid = null, $time=0)
    {
        return true;
        
        if (is_null($ipid)) {
            $ipid= $this->ipid;
        }
        
        if($time==0) {
            $d = time();
        }else{
            $d=$time;
        }
        PatientVersorger::updateEntry($ipid, '__timestamp', array('time'=>$d));
    }
    
    
    private function _init_clinic_sync()
    {
        if ($this->_is_clinic_sync) {
            $this->_block_name = "PatientVersorger_SYNC"; //this var is just for info
            $this->_clientForms = array_fill(1,1000, 1);//till 08.2018 you had ~50 boxes... we force-allow first 1000
            $this->_init_onlyThisModel = null;
        }
    }
    
    
    private function  _init_categories ()
    {
      
//         if ( ! $this->_is_clinic_sync) {
            foreach ($this->categories as $model => $options)
            {
                //__init_box only is user has-it, so we don't lose time (goto /extraforms/formlist to assign boxes)
                if (isset($this->_clientForms[$this->categories[$model]['extra_form_ID']])
                    && $this->_clientForms[$this->categories[$model]['extra_form_ID']])
                {
                    if (is_null($this->_init_onlyThisModel) || $this->_init_onlyThisModel == $model) {
                        $initBoxFunction = "__init_box_{$model}";
                        $this->{$initBoxFunction}($model);
                    }
                }
            }
//         }
        
        
        
        
        
        
        foreach ($this->categories as $model => $options) 
        {
            if ( ! isset($this->_clientForms[$this->categories[$model]['extra_form_ID']])
                || ! $this->_clientForms[$this->categories[$model]['extra_form_ID']] )
            {
                continue;//__init_box only is user has-it, so we don't lose time (goto /extraforms/formlist to assign boxes)
            }
            
            if ( ! is_null($this->_init_onlyThisModel) && $this->_init_onlyThisModel != $model) {
                continue;
            }
            
            $this->categories[$model] ['label'] = $this->translate("[{$model} Box Name]");
            
            if (APPLICATION_ENV == "development") {
                $this->categories[$model] ['label'] .=  " - {$model}";
            }
            
            $this->categories[$model] ['table'] = $model;
            
            $this->categories[$model] ['cols'] = array();
            
            

            if ( ! isset($this->categories[$model]['extract'])) {
                if (isset($this->_categoriesForms->$model) && method_exists($this->_categoriesForms->$model, 'getVersorgerExtract')) {
                    $this->categories[$model]['extract'] = $this->_categoriesForms->$model->getVersorgerExtract();
                } elseif ( ! isset($this->categories[$model]['extract'])) {
                    $this->categories[$model]['extract'] = null;//array();
                }
            }
            
            if ( ! isset($this->categories[$model]['address'])) {
                if (isset($this->_categoriesForms->$model) && method_exists($this->_categoriesForms->$model, 'getVersorgerAddress')) {
                    $this->categories[$model]['address'] = $this->_categoriesForms->$model->getVersorgerAddress();
                } elseif ( ! isset($this->categories[$model]['address'])) {
                    $this->categories[$model]['address'] = null;//array();
                }
            }
            

            if (Doctrine_Core::getLoadedModels($model)) 
            {
                /*
                 * in patientDetails i am using the cols type to format date, timestamp
                 * @see  Application_Form_PatientDetails -> _tryFormatExtractDate()
                 */
                
                $table = [];
                
                $recordTable = Doctrine_Core::getTable($model);
                
                $cols = $recordTable->getColumns();
                
                foreach ($cols as $col_name=> $col_def) {
                
                    $this->categories[$model] ['cols'] [$col_name] = array_merge($col_def, [
                        'db' => $col_name,
                        'class' => $col_name,
                        //    'label' =>  $this->translate("[{$model} {$col_name} Col Name]")
                    ]);
                }
                
                
                
                if ($this->_is_clinic_sync) 
                {
                    
                    $table['tableName'] = $recordTable->getOption('tableName');
                    
                    $table['relations'] = [];
                    $relations = $recordTable->getRelations();
                    
                    foreach ($relations as $key => $relation) {
                        $relationData = $relation->toArray();
                    
                        $relationKey = $relationData['alias'];
                    
                        if (isset($relationData['refTable']) && $relationData['refTable']) {
                            $table['relations'][$relationKey]['refClass'] = $relationData['refTable']->getComponentName();
                        }
                    
                        if (isset($relationData['class']) && $relationData['class'] && $relation['class'] != $relationKey) {
                            $table['relations'][$relationKey]['class'] = $relationData['class'];
                        }
                    
                        $table['relations'][$relationKey]['local'] = $relationData['local'];
                        $table['relations'][$relationKey]['foreign'] = $relationData['foreign'];
                    
                        if ($relationData['type'] === Doctrine_Relation::ONE) {
                            $table['relations'][$relationKey]['type'] = 'one';
                        } else if ($relationData['type'] === Doctrine_Relation::MANY) {
                            $table['relations'][$relationKey]['type'] = 'many';
                        } else {
                            $table['relations'][$relationKey]['type'] = 'one';
                        }
                        
                        
                        if (isset($relationData['cascade']) && ! empty($relationData['cascade'])) {                        
                            $table['relations'][$relationKey]['cascade'] = $relationData['cascade'];
                        }
                        
                        $relationRecordTable = Doctrine_Core::getTable($relationKey);
                        
                        $table['relations'][$relationKey]['__tableName'] = $relationRecordTable->getOption('tableName');
                        
                        $relationColumns = $relationRecordTable->getColumns();
                        
                        foreach ($relationColumns as $col_name=> $col_def) {
                            
                            $table['relations'][$relationKey]['__columns'][$col_name] = array_merge($col_def, [
                                'db' => $col_name,
                                'class' => $col_name,
                            ]);
                        }
                    }
                    
                    $this->categories[$model] ['tableName'] = $table['tableName'];
                    $this->categories[$model] ['relations'] = $table['relations'];
                    
                    
                    
//                     $listenerChain = $recordTable->getRecordListener();
//                     $i = 0;
//                     while ($listener = $listenerChain->get($i))
//                     {
//                         $i++;
//                         $listener_class = get_class($listener);
//                         $this->categories[$model] ['behavior'] [] = $listener_class;
//                     }
                   
                }
            }
        }
        
        return;
        
    }
    
    
    private function __init_box_FamilyDoctor ($model = 'FamilyDoctor')
    {
        /*
         * FamilyDoctor
         */
        $createBox_formFn = 'create_form_family_doctor';
        
        $this->_categoriesForms->$model  = (new Application_Form_Familydoctor(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        $this->categories[$model]['userDefinedList']['shift_billing'] = [0=>"Nein", 1=>"Ja"];
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
         
        
    }
    
    private function __init_box_PatientPflegedienste ($model = 'PatientPflegedienste')
    {

        /*
         * PatientPflegedienste
         */
        $createBox_formFn = 'create_form_patient_pflegedienst';
        
        $this->_categoriesForms->$model = (new Application_Form_PatientPflegedienst(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        
    }
    
    private function __init_box_PatientHealthInsurance ($model = 'PatientHealthInsurance')
    {

        /*
         * PatientHealthInsurance
         * PatientHealthInsuranceSubdivizions
         * the ugly duck
         */
        
        $createBox_formFn = 'create_form_health_insurance';
        
        $this->_categoriesForms->$model = (new Application_Form_PatientHealthInsurance(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}(null, "{$this->_categoriesForms_belongsTo}[{$model}]")->__toString();
        
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction("PatientHealthInsurance2Subdivisions", 'save_form_patient_insurance_subdivision');
        
        $this->categories[$model]['userDefinedList']['insurance_status'] =  $this->_categoriesForms->$model->getInsuranceStatusArray();
         
        
        if ( ! empty($this->_patientMasterData[$model])) {
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['HealthInsuranceSubdivisions']['subdivision_name'] = $this->translate('patient_health_insurance');
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row, "{$this->_categoriesForms_belongsTo}[{$model}]")->__toString();
                $key_HealthInsurance = $key;
        
            }
        } else {
            //empty insurance
        }
        
        //add subdivisions
        
        //change key
        if (! empty($this->_patientMasterData["PatientHealthInsurance2Subdivisions"])) {
            $this->_patientMasterData["PatientHealthInsurance2Subdivisions"] = array_combine(
                array_map(function($k){ return 'subdivision_'.$k; }, array_keys($this->_patientMasterData["PatientHealthInsurance2Subdivisions"])),
                $this->_patientMasterData["PatientHealthInsurance2Subdivisions"]
            );
        }
        //add to addnewDialogHtml dialog.. cause this uses ui.tabs
        foreach ($this->_patientMasterData["PatientHealthInsurance2Subdivisions"] as $key => $row) {
            $subdivID = $row['HealthInsuranceSubdivisions']['subdiv_id'];
        
            if ( empty($subdivID)) {
                continue;
            }
        
            $this->categories[$model]['addnewDialogHtml'] .= $this->_categoriesForms->$model->create_form_patient_insurance_subdivision($row, "{$this->_categoriesForms_belongsTo}[PatientHealthInsurance2Subdivisions][{$subdivID}]")->__toString();
        }
        
        //         die(print_r($this->_patientMasterData["PatientHealthInsurance2Subdivisions"]));
        
        if ( ! empty($this->_patientMasterData[$model]) && ! empty($this->_patientMasterData["PatientHealthInsurance2Subdivisions"])) {
        
            foreach ($this->_patientMasterData["PatientHealthInsurance2Subdivisions"] as $key => $row) {
        
                $subdivID = $row['HealthInsuranceSubdivisions']['subdiv_id'];
        
                if ( empty($subdivID)) {
                    continue;
                }
        
                if (empty($row['HealthInsuranceSubdivisions'])
                    && ! empty($this->_patientMasterData["HealthInsuranceSubdivisions"]))
                {
                    $row += array('HealthInsuranceSubdivisions' => $this->_patientMasterData["HealthInsuranceSubdivisions"][$subdivID]);
                }
        
                $this->_patientMasterData["PatientHealthInsurance2Subdivisions"][$key]['editDialogHtml'] = $this->_categoriesForms->$model->create_form_patient_insurance_subdivision($row, "{$this->_categoriesForms_belongsTo}[PatientHealthInsurance2Subdivisions][{$subdivID}]")->__toString();
        
                $this->_patientMasterData[$model][$key_HealthInsurance]['editDialogHtml'] .= $this->_patientMasterData["PatientHealthInsurance2Subdivisions"][$key]['editDialogHtml'];
        
        
            }
        
            $this->_patientMasterData[$model] =  $this->_patientMasterData['PatientHealthInsurance'] + $this->_patientMasterData['PatientHealthInsurance2Subdivisions'];
        }
        
    }
    
    private function __init_box_PatientSpecialists ($model = 'PatientSpecialists')
    {

        /*
         * PatientSpecialists
         */
        $createBox_formFn = 'create_form_specialist';
        
        $this->_categoriesForms->$model = (new Application_Form_PatientSpecialist(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->categories[$model]['userDefinedList']['medical_speciality'] =  $this->_categoriesForms->$model->getSpecialistsTypesArray();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    private function __init_box_PatientSupplies ($model = 'PatientSupplies')
    {
        /*
         * PatientSupplies
         */
        $createBox_formFn = 'create_form_patient_supplies';
        
        $this->_categoriesForms->$model = new Application_Form_Supplies(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    private function __init_box_PatientPharmacy ($model = 'PatientPharmacy')
    {
        /*
         * PatientPharmacy
         */
        $createBox_formFn = 'create_form_patient_pharmacy';
        
        $this->_categoriesForms->$model = new Application_Form_Pharmacy(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}(null)->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    private function __init_box_PatientSuppliers ($model = 'PatientSuppliers')
    {
        /*
         * PatientSuppliers
         */
        $createBox_formFn = 'create_form_patient_suppliers';
        
        $this->_categoriesForms->$model = new Application_Form_PatientSuppliers(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
   
    //ISPC-2667 Lore 21.09.2020
    private function __init_box_PatientCareInsurance ($model = 'PatientCareInsurance')
    {
        //Pflegeversicherung
        $createBox_formFn = 'create_form_patient_care_insurance';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientCareInsurance(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        $this->categories[$model]['userDefinedList']['kind_of_insurance'] = $this->_categoriesForms->$model->getInsuranceStatusArray();
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
            
            
    }
    //.
    
    //ISPC-2672 Lore 22.10.2020
    private function __init_box_PatientPlaygroup ($model = 'PatientPlaygroup')
    {
        //Spielgruppe
        $createBox_formFn = 'create_form_patient_playgroup';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientPlaygroup(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    //.
    
    //ISPC-2672 Lore 23.10.2020
    private function __init_box_PatientSchool ($model = 'PatientSchool')
    {
        //Schule
        $createBox_formFn = 'create_form_patient_school';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientSchool(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    //.
    
    //ISPC-2672 Lore 26.10.2020
    private function __init_box_PatientWorkshopDisabledPeople ($model = 'PatientWorkshopDisabledPeople')
    {
        //Werkstatt für behinderte Menschen / Workshop for disabled people
        $createBox_formFn = 'create_form_patient_workshop';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientWorkshopDisabledPeople(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    //.
    
    //ISPC-2672 Lore 26.10.2020
    private function __init_box_PatientOtherSuppliers ($model = 'PatientOtherSuppliers')
    {
        //Sonstiges / other
        $createBox_formFn = 'create_form_patient_other_suppliers';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientOtherSuppliers(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    //.
    
    //ISPC-2672 Lore 26.10.2020
    private function __init_box_PatientSapvTeam ($model = 'PatientSapvTeam')
    {
        //Sonstiges / other
        $createBox_formFn = 'create_form_patient_sapv_team';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientSapvTeam(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    //.
    
    //ISPC-2672 Lore 26.10.2020
    private function __init_box_PatientChildrensHospice ($model = 'PatientChildrensHospice')
    {
        //Kinderhospiz == Childrens hospice
        $createBox_formFn = 'create_form_patient_childrens_hospice';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientChildrensHospice(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    //.
    
    //ISPC-2672 Lore 26.10.2020
    private function __init_box_PatientFamilySupportService ($model = 'PatientFamilySupportService')
    {
        //Kinderhospiz == Childrens hospice
        $createBox_formFn = 'create_form_patient_family_support_service';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientFamilySupportService(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    //.
    
    private function __init_box_PatientHomecare ($model = 'PatientHomecare')
    {
        /*
         * PatientHomecare
         */
        $createBox_formFn = 'create_form_patient_homecare';
        
        $this->_categoriesForms->$model = new Application_Form_Homecare(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}(null)->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        
    }
    
    private function __init_box_PatientPhysiotherapist ($model = 'PatientPhysiotherapist')
    {
        /*
         * PatientPhysiotherapist
         */
        $createBox_formFn = 'create_form_patient_physiotherapist';
        
        $this->_categoriesForms->$model = new Application_Form_Physiotherapists(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        
    }
    
    private function __init_box_PatientVoluntaryworkers ($model = 'PatientVoluntaryworkers')
    {
        /*
         * PatientVoluntaryworkers
         */
        $createBox_formFn = 'create_form_patient_voluntaryworker';
        
        $this->_categoriesForms->$model = new Application_Form_PatientVoluntaryworkers(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $clientids_of_vws = array();
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $clientids_of_vws[] = $row['Voluntaryworkers']['clientid'];
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        
        $this->categories[$model]['userDefinedList']['hospice_association'] =  $this->_categoriesForms->$model->getHospiceAssociationArray($clientids_of_vws);
        
    }
    
    private function __init_box_PatientChurches ($model = 'PatientChurches')
    {
        /*
         * PatientChurches
         */
        $createBox_formFn = 'create_form_patient_church';
        
        $this->_categoriesForms->$model = new Application_Form_PatientChurches(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    private function __init_box_PatientHospiceassociation ($model = 'PatientHospiceassociation')
    {
        /*
         * PatientHospiceassociation
         */
        $createBox_formFn = 'create_form_patient_hospiceassociation';
        
        $this->_categoriesForms->$model = new Application_Form_PatientHospiceassociation(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    private function __init_box_PatientRemedies ($model = 'PatientRemedies')
    {
        /*
         * PatientRemedies
         */
        $createBox_formFn = 'create_form_remedies_2_supplies';
        
        $this->_categoriesForms->$model = new Application_Form_PatientRemedy(array(
            "elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData" => $this->_patientMasterData
        ));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        
    }
    
    
    private function __init_box_SYSTEMSYNC ($model = 'SYSTEMSYNC')
    {
        /*
         * SYSTEMSYNC
         * this is the old one, copied to patientnew/patientdetails_box_systemsync_50.phtml
         */
        if ($this->_patientMasterData['ModulePrivileges']['115']) {
            $this->__box_SYSTEMSYNC_View_Assign();
            $this->categories[$model]['extract'] = $this->getView()->render("patientnew/patientdetails_box_systemsync_50.phtml");
            $this->categories[$model]['address'] = false;
        }
    
    }
    
    /**
     * @see PatientController::patientdetailsAction()
     */
    private function __box_SYSTEMSYNC_View_Assign()
    {
    
        $ipid = $this->ipid;
        $patientdetails =  $this->_patientMasterData;
    
        $this->getView()->patid2 = $this->enc_id;
        $this->getView()->versorger_or_stammdaten = 'versorger';
    }
    
    //ISPC-2672 Carmen 21.10.2020
    private function __init_box_PatientKindergarten ($model = 'PatientKindergarten')
    {
    	//Kindergarten
    	$createBox_formFn = 'create_form_patient_kindergarten';
    	
    	$this->_categoriesForms->$model  = (new Application_Form_PatientKindergarten(array(
    			"elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
    			"_patientMasterData" => $this->_patientMasterData
    	)));
    	
    	$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
    	$this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
    	$this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
    	
    	if ( ! empty($this->_patientMasterData[$model]))
    		foreach ($this->_patientMasterData[$model] as $key => $row) {
    			$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
    		}
    }
    //--
    //ISPC-2672 Carmen 26.10.2020
    private function __init_box_PatientAmbulantChildrenHospiceService ($model = 'PatientAmbulantChildrenHospiceService')
    {
    	//Kindergarten
    	$createBox_formFn = 'create_form_patient_ambulant_children_hospice_service';
    	 
    	$this->_categoriesForms->$model  = (new Application_Form_PatientAmbulantChildrenHospiceService(array(
    			"elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
    			"_patientMasterData" => $this->_patientMasterData
    	)));
    	 
    	$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
    	$this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
    	$this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
    	 
    	if ( ! empty($this->_patientMasterData[$model]))
    		foreach ($this->_patientMasterData[$model] as $key => $row) {
    			$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
    		}
    }
    //--
    //ISPC-2672 Carmen 26.10.2020
    private function __init_box_PatientYouthWelfareOffice ($model = 'PatientYouthWelfareOffice')
    {
    	//Kindergarten
    	$createBox_formFn = 'create_form_patient_youth_welfare_office';
    
    	$this->_categoriesForms->$model  = (new Application_Form_PatientYouthWelfareOffice(array(
    			"elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
    			"_patientMasterData" => $this->_patientMasterData
    	)));
    
    	$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
    	$this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
    	$this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
    
    	if ( ! empty($this->_patientMasterData[$model]))
    		foreach ($this->_patientMasterData[$model] as $key => $row) {
    			$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
    		}
    }
    //--
    //ISPC-2672 Carmen 27.10.2020
    private function __init_box_PatientIntegrationAssistance ($model = 'PatientIntegrationAssistance')
    {
    	//Kindergarten
    	$createBox_formFn = 'create_form_patient_integration_assistance';
    
    	$this->_categoriesForms->$model  = (new Application_Form_PatientIntegrationAssistance(array(
    			"elementsBelongTo" => "{$this->_categoriesForms_belongsTo}[{$model}]",
    			"_patientMasterData" => $this->_patientMasterData
    	)));
    
    	$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
    	$this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
    	$this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
    
    	if ( ! empty($this->_patientMasterData[$model]))
    		foreach ($this->_patientMasterData[$model] as $key => $row) {
    			$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
    		}
    }
    //--
}