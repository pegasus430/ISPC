<?php
/**
 * 
 * @author claudiu 
 * Jul 6, 2018
 * 
 * splatOP was added on php 5.6 ... 
 * http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list
 *
 */
class Application_Form_PatientDetails extends Pms_Form
{
    
    //private $triggerformid = WlAssessment::TRIGGER_FORMID;
    //private $triggerformname = WlAssessment::TRIGGER_FORMNAME;
    //protected $_translate_lang_array = WlAssessment::LANGUAGE_ARRAY;
	//ISPC - 2320 - add info button in patientformnew/sapvevaluation formular
	protected $_block_name_allowed_inputs =  array(
	
			"PatientDetails" => [
					'create_form_all_patient_location_display' => [
							//this are removed
							'__removed' => [
							],
							//only this are allowed
							'__allowed' => [
							],
					],
	
			],
	
	
			"SapvEvaluationII" => [
	
					'create_form_all_patient_location_display_sapv' => [
							//this are removed
							'__removed' => [
							],
							//only this are allowed
							'__allowed' => [
							],
					],
			],
	);
    
    private $ipid = null;
    
    private $enc_id = null;
    
    private $_categoriesForms = null;
    
    private $_categoriesForms_belongsTo = 'patientDetails';
    
    private $categories = null;
    
    private $_init_onlyThisModel = null;
    
//     public function __construct(...$args) {
    public function __construct($options = null, $ipid = null) {
        
        if ( ! empty($options['_onlyThisModel'])) {
            $this->_init_onlyThisModel = $options['_onlyThisModel'];
            unset($options['_onlyThisModel']);            
        }
                
//         parent::__construct(...$args);
        parent::__construct($options);
        
        $options = func_get_arg(0);
        $ipid = func_get_arg(1);
        
        $patientMasterData = ! empty($options['_patientMasterData']) ? $options['_patientMasterData'] : null;
        
        if ( ! empty($patientMasterData)) {
            $this->enc_id = Pms_Uuid::encrypt($patientMasterData['id']);
        }
        
        
        if (empty($ipid)) {
            if (empty($patientMasterData) || empty($patientMasterData['ipid'])) {
                throw new Zend_Exception('Admin was informed, something is wrong an we cannot display this patient - err:1.1', 3);
            } else {
                $ipid = $patientMasterData['ipid'];
            }
        }
        
        $this->ipid = $ipid;
        
        $last_ipid_session = new Zend_Session_Namespace('last_ipid');
        if ($last_ipid_session->ipid != $this->ipid) {
            throw new Zend_Exception('Admin was informed, something is wrong an we cannot display this patient - err:2.2', 3);
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
         * 'inlineEdit' => this will bypass multipleEntries, and display the editForm instead of `extract`
         * 'inlineEdit_reload' => if inlineEdit, then you can decide if reload the box, default is not to reload
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
         * @since 04.10.2018, introduced category with NULL extra_form_ID  aka Fake-Category
         * this Fake-Category is used in PatientMoreInfo 4 Stammdatenerweitert_hilfsmittel
         * Stammdatenerweitert_hilfsmittel displays values both from the mother, and also from the linked ... 
         * if it takes values from multiple tables, use like in the example above
         */
        $this->categories = array(
    
            //Patient
            "PatientMaster" => array(
                'extra_form_ID'     => 14,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'hasHistory'        => false,
                '__motherForm'          => 'PatientMaster',
                '__linked_categories'   => [
                    'ContactPersonMaster'
                ],
            ),
            //Patient history == Fallhistorie// + 51 Fallhistorie II
            "PatientReadmission" => array(
                'extra_form_ID'     => 35,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Clinic Cases //Maria:: Migration CISPC to ISPC 22.07.2020
            "PatientCaseStatus" => array(
                'extra_form_ID'     => 101,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //SAPV Verordnung
            "SapvVerordnung" => array(
                'extra_form_ID'     => 11,
                'placement'         => 'left',
                'multipleEntries'   => true,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Ansprechpartner
            "ContactPersonMaster" => array(
                'extra_form_ID'     => 12,
                'placement'         => 'left',
                'multipleEntries'   => true,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => [
                    'PatientAcp'
                ],
            ),
            //SYSTEMSYNC
            "SYSTEMSYNC" => array(
                'extra_form_ID'     => 50,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Aufenthaltsort
            "PatientLocation" => array(
                'extra_form_ID'     => 13,
                'placement'         => 'right',
                'multipleEntries'   => true,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Ausscheidung
            "Stammdatenerweitert_ausscheidung" => array(
                'extra_form_ID'     => 21,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => null,
            ),
            //Vigilanz
            "Stammdatenerweitert_vigilanz" => array(
                'extra_form_ID'     => 18,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => null,
            ),
            //Künstliche Ausgänge
            "Stammdatenerweitert_kunstliche" => array(
                'extra_form_ID'     => 22,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => null,
            ),
            //Familienstand
            "Stammdatenerweitert_familienstand" => array(
                'extra_form_ID'     => 16,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => null,
            ),
            //Staatszugehörigkeit
            "Stammdatenerweitert_stastszugehorigkeit" => array(
                'extra_form_ID'     => 17,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => null,
            ),
            //Hilfsmittel
            "Stammdatenerweitert_hilfsmittel" => array(
                'extra_form_ID'     => 24,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => [
                    'PatientMoreInfo'
                ],
            ),
            //Ernährung
            "Stammdatenerweitert_ernahrung" => array(
                'extra_form_ID'     => 20,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => [
                    'PatientMoreInfo'
                ],
            ),
            //Wunsch des Patienten
            "Stammdatenerweitert_wunsch" => array(
                'extra_form_ID'     => 25,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => null,
            ),
            //Orientierung
            "Stammdatenerweitert_orientierung" => array(
                'extra_form_ID'     => 19,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true, 
                '__motherForm'      => 'Stammdatenerweitert',
                '__linked_categories'   => null,
            ),    
            //Keimbesiedelung
            "PatientGermination" => array(
                'extra_form_ID'     => 52,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Mobilität
            "PatientMobility" => array(
                'extra_form_ID'     => 5,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Mobilität II
            "PatientMobility2" => array(
                'extra_form_ID'     => 55,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Orientierung II
            "PatientOrientation" => array(
                'extra_form_ID'     => 54,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Patient lebt
            "PatientLives" => array(
                'extra_form_ID'     => 1,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Religionszugehörigkeit
            "PatientReligions" => array(
                'extra_form_ID'     => 8,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Versorgung
            "PatientSupply" => array(
                'extra_form_ID'     => 4,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Pflegegrade = Pflegestufe
            "PatientMaintainanceStage" => array(
                'extra_form_ID'     => 7,
                'placement'         => 'right',
                'multipleEntries'   => true,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Patientenverfügung = ACP = Advanced Care Planning
            "PatientAcp" => array(
                'extra_form_ID'     => 6,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => true,
                'extractEscape'     => false, //this is ignored by inlineEdit = true, .extract is not used at all in js
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => [
                    'ContactPersonMaster'
                ],
                
            ), //big
            //VisitsPlanning = Tourenplanung
            "PatientVisitsSettings" => array(
                'extra_form_ID'     => 45,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Vorausschauende Therapieplanung
            "PatientTherapieplanung" => array(
                'extra_form_ID'     => 37,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Hospizverein
            "PatientHospizverein" => array(
                'extra_form_ID'     => 39,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            //Hospiz - Hospizverein - SAPV/AAPV
            "PatientMaster_Hospiz_Hospizverein_SAPV_AAPV" => array(
                'extra_form_ID'     => 40,
                'placement'         => 'right',
                'multipleEntries'   => false,
                'inlineEdit'        => true,
                'inlineEdit_reload' => true,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'      => 'PatientMaster',
                '__linked_categories'   => null,
            ),
            //Hilfsmittel Verleih
            "PatientMedipumps" => array(
                'extra_form_ID'     => 43,
                'placement'         => 'right',
                'multipleEntries'   => true,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
        	
        	//Erwerbssituation = employment situation
        	"PatientEmploymentSituation" => array(
        	    'extra_form_ID'     => 56,
        	    'placement'         => 'third', //ISPC-2703,elena, 04.01.2021
        	    'multipleEntries'   => false,
        	    'inlineEdit'        => true,
        	    'inlineEdit_reload' => true,
        	    'extractEscape'     => false,
        	    'hasHistory'        => true,
        	    '__motherForm'          => null,
        	    '__linked_categories'   => null,
        	),
            //ISPC-2400
            //Krisengeschichte == crisishistory
            "PatientCrisisHistory" => array(
                'extra_form_ID'     => 60,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                'notaddnew'         =>  true,
                'hideDetails'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
            //this model does NOT have a box of it's own, values from it are user in some checkboxes in Stammdatenerweitert
            "PatientMoreInfo" => array(
                'extra_form_ID'     => null, // null will bypass filtering
                'placement'         => null,
                'multipleEntries'   => null,
                'inlineEdit'        => null,
                'inlineEdit_reload' => null,
                'extractEscape'     => null,
                'hasHistory'        => null,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
        	
            //36 ???????
            //3 ??????
            
            //ISPC-2669 Lore 23.09.2020
            //Schwerbehindertenausweis == patient_handicapped_card
            "PatientHandicappedCard" => array(
                'extra_form_ID'     => 102,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
            //ISPC-2673 Lore 30.09.2020
            //Ressourcen == FormBlockResources
            "FormBlockResources" => array(
                'extra_form_ID'     => 104,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
        	//PatientSurveySettings = patient Survey Settings (ISPC-2411)
        	"PatientSurveySettings" => array(
        			'extra_form_ID'     => 70,
        			'placement'         => 'left',
        			'multipleEntries'   => false,
        			'inlineEdit'        => true,
        			'inlineEdit_reload' => true,
        			'extractEscape'     => false, //this is ignored by inlineEdit = true, .extract is not used at all in js
        			'hasHistory'        => true,
        			'__motherForm'          => null,
        			'__linked_categories'   => null,
        	
        	), //big
        	
            //MePatientDevices - ISPC-2432 Ancuta 13.01.2020
            "MePatientDevices" => array(
                'extra_form_ID'     => 90,
                'placement'         => 'left',
                'multipleEntries'   => true,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),// big
            
            //MePatientDevicesNotifications = MePatient notifications for devices ISPC-2432 Ancuta 22.01.2020
            "MePatientDevicesNotifications" => array(
                'extra_form_ID'     => 91,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false, //this is ignored by inlineEdit = true, .extract is not used at all in js
                'hasHistory'        => true,
                '__motherForm'          => null,
                '__linked_categories'   => null,
                
            ), //big

            //ISPC-2508 Carmen
            //Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020            
        	//Künstliche Zugänge - Ausgänge - artificialentryexit
        	"PatientArtificialEntriesExits" => array(
        			'extra_form_ID'     => 100,
        			'placement'         => 'right',
        			'multipleEntries'   => true,
        			'inlineEdit'        => false,
        			'inlineEdit_reload' => false,
        			'extractEscape'     => false,
        			'hasHistory'        => true,
        			'hideDetails'        => true,
        			'__motherForm'      => null,
        			'__linked_categories'   => null,
        	),
            
            //ISPC-2773 Lore 14.12.2020
            //Familie == patient_family_info
            "PatientFamilyInfo" => array(
                'extra_form_ID'     => 116,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
            //ISPC-2776 Lore 15.12.2020
            //(Kinder)Krankheiten == patient_children_diseases
            "PatientChildrenDiseases" => array(
                'extra_form_ID'     => 118,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
        		
        	//ISPC-2774 Carmen 16.12.2020
        	//Therapien = therapy
        	"PatientTherapy" => array(
        		'extra_form_ID'     => 117,
        		'placement'         => 'right',
        		'multipleEntries'   => true,
        		'inlineEdit'        => false,
        		'inlineEdit_reload' => false,
        		'extractEscape'     => false,
        		'hasHistory'        => false,
        		'__motherForm'          => null,
        		'__linked_categories'   => null,
        	),
            
            //ISPC-2788 Lore 08.01.2021
            //Ernährung == patient_nutrition_info
            "PatientNutritionInfo" => array(
                'extra_form_ID'     => 119,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),


            //ISPC-2694,elena,16.12.2020
            "Anamnese" => array(
                'extra_form_ID'     => 110,
                'placement'         => 'third', //ISPC-2703,elena, 04.01.2021
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                'hideDetails'        => false,
                '__motherForm'      => null,
                '__linked_categories'   => null,
            ),
            
        	//ISPC-2381 Carmen 11.01.2021
        	//Hilfsmittel ELSA - new box hilfsmittel(aids)
        	"PatientAids" => array(
        		'extra_form_ID'     => 124,
        		'placement'         => 'right',
        		'multipleEntries'   => true,
        		'inlineEdit'        => false,
        		'inlineEdit_reload' => false,
        		'extractEscape'     => false,
        		'hasHistory'        => false,
        		'__motherForm'          => null,
        		'__linked_categories'   => null,
        	),
            
            //ISPC-2787 Lore 11.01.2021
            //Stimulatoren == patient_stimulators_info
            "PatientStimulatorsInfo" => array(
                'extra_form_ID'     => 120,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
            //ISPC-2790 Lore 12.01.2021
            //Finale Phase == patient_final_phase
            "PatientFinalPhase" => array(
                'extra_form_ID'     => 121,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
            //ISPC-2791 Lore 13.01.2021
            //Ausscheidung == patient_excretion_info
            "PatientExcretionInfo" => array(
                'extra_form_ID'     => 122,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
            //ISPC-2792 Lore 15.01.2021
            //Haut- und Körperpflege == patient_personal_hygiene
            "PatientPersonalHygiene" => array(
                'extra_form_ID'     => 123,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
            //ISPC-2793 Lore 18.01.2021
            //Kommunikation und Beschäftigung == patient_communication_employment
            "PatientCommunicationEmployment" => array(
                'extra_form_ID'     => 125,
                'placement'         => 'left',
                'multipleEntries'   => false,
                'inlineEdit'        => false,
                'inlineEdit_reload' => false,
                'extractEscape'     => false,
                'hasHistory'        => false,
                '__motherForm'          => null,
                '__linked_categories'   => null,
            ),
            
        );
        
        
        //filter by client allowed oxes
        if ( ! empty($this->_clientForms)) {
            foreach ($this->categories as $cat => $val) {
                if ( ! is_null($val['extra_form_ID'])
                    && (! isset($this->_clientForms[$val['extra_form_ID']])
                        || ! $this->_clientForms[$val['extra_form_ID']] )
                   )
                {
                    unset($this->categories[$cat]);
                    //(goto /extraforms/formlist to assign boxes)
                }
            }
        }
       
        
    }
    
    
    

    private function  _init_categories ($fn = null)
    {
        
        foreach ($this->categories as $model => $options)
        {
            //__init_box only is user has-it, so we don't lose time (goto /extraforms/formlist to assign boxes)
            if (isset($this->_clientForms[$this->categories[$model]['extra_form_ID']])
                && $this->_clientForms[$this->categories[$model]['extra_form_ID']]
                && (is_null($this->_init_onlyThisModel) 
                    || $this->_init_onlyThisModel == $model
                    || ( ! is_null($this->_init_onlyThisModel)
                        && $fn == '_reConstruct'
                        && isset($this->categories[$this->_init_onlyThisModel]['__linked_categories'])
                        && is_array($this->categories[$this->_init_onlyThisModel]['__linked_categories'])                        
                        && in_array($model, $this->categories[$this->_init_onlyThisModel]['__linked_categories'])
                        )
                    )
                )
            {
                
//                 if ($model != $this->_init_onlyThisModel)
//                 dd($this->categories[$this->_init_onlyThisModel]['__linked_categories']);
                
                $initBoxFunction = "__init_box_{$model}";
                
                if (method_exists($this, $initBoxFunction)) {
                    $this->{$initBoxFunction}($model);
                }

                
                
                $this->categories[$model] ['label'] = $this->translate("[{$model} Box Name]");
                
                if (APPLICATION_ENV == "development") {
                    $this->categories[$model] ['label'] .=  " - {$model}";
                }
                
                $this->categories[$model] ['table'] = $model;
                
                $this->categories[$model] ['cols'] = array();
                
                if ( ! isset($this->categories[$model]['extract'])) {
                    if (method_exists($this->_categoriesForms->$model, 'getVersorgerExtract')) {
                        $this->categories[$model]['extract'] = $this->_categoriesForms->$model->getVersorgerExtract();
                        
                    } elseif ( ! isset($this->categories[$model]['extract'])) {
                        $this->categories[$model]['extract'] = null;//array();
                    }
                }
                         
                                
                if ( ! isset($this->categories[$model]['address'])) {
                    if (method_exists($this->_categoriesForms->$model, 'getVersorgerAddress')) {
                        $this->categories[$model]['address'] = $this->_categoriesForms->$model->getVersorgerAddress();
                    } elseif ( ! isset($this->categories[$model]['address'])) {
                        $this->categories[$model]['address'] = null;//array();
                    }
                }

                
                if (Doctrine_Core::getLoadedModels($model)) {
                
                    $cols = Doctrine_Core::getTable($model)->getColumns();
                                       
                    foreach ($cols as $col_name=> $col_def) {
                
                        $this->categories[$model] ['cols'] [$col_name] = array_merge($col_def, ['db' => $col_name, 'class' => $col_name, 'label' =>  $this->translate("[{$model} {$col_name} Col Name]")]);
                    }
                }
            }
        }
    
    
//         foreach ($this->categories as $model => $options)
//         {
    
//             if ( ! isset($this->_clientForms[$this->categories[$model]['extra_form_ID']])
//                 || ! $this->_clientForms[$this->categories[$model]['extra_form_ID']] )
//             {
//                 continue;//__init_box only is user has-it, so we don't lose time (goto /extraforms/formlist to assign boxes)
//             }
    
//             if ( ! is_null($this->_init_onlyThisModel) && $this->_init_onlyThisModel != $model) {
//                 continue;
//             }
    
//             $this->categories[$model] ['label'] = $this->translate("[{$model} Box Name]");
    
//             if (APPLICATION_ENV == "development") {
//                 $this->categories[$model] ['label'] .=  " - {$model}";
//             }
    
//             $this->categories[$model] ['table'] = $model;
    
//             $this->categories[$model] ['cols'] = array();
    
    
//             if ( ! isset($this->categories[$model]['extract'])) {
//                 if (method_exists($this->_categoriesForms->$model, 'getVersorgerExtract')) {
//                     $this->categories[$model]['extract'] = $this->_categoriesForms->$model->getVersorgerExtract();
//                 } elseif ( ! isset($this->categories[$model]['extract'])) {
//                     $this->categories[$model]['extract'] = null;//array();
//                 }
//             }
    
//             if ( ! isset($this->categories[$model]['address'])) {
//                 if (method_exists($this->_categoriesForms->$model, 'getVersorgerAddress')) {
//                     $this->categories[$model]['address'] = $this->_categoriesForms->$model->getVersorgerAddress();
//                 } elseif ( ! isset($this->categories[$model]['address'])) {
//                     $this->categories[$model]['address'] = null;//array();
//                 }
//             }
    
    
//             if (Doctrine_Core::getLoadedModels($model)) {
    
//                 $cols = Doctrine_Core::getTable($model)->getColumns();
//                 foreach ($cols as $col_name=> $col_def) {
    
//                     $this->categories[$model] ['cols'] [$col_name] = array_merge($col_def, ['db' => $col_name, 'class' => $col_name, 'label' =>  $this->translate("[{$model} {$col_name} Col Name]")]);
//                 }
//             }
//         }
    
    
    
        //         dd($this->categories);
    
    
        return;
    
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
            
            //filter and send only the requested model
            if ( ! is_null($this->_init_onlyThisModel)) {
                if ($catkey != $this->_init_onlyThisModel
                    && (empty($this->categories[$this->_init_onlyThisModel]['__linked_categories']) || ! in_array($catkey, $this->categories[$this->_init_onlyThisModel]['__linked_categories'])))
                {
                    continue; 
                }
            }
            
            
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
         
        $output['__meta-timestamp'] = $this->getTimestamp($ipid);
        $output['__meta-categorys'] = $this->__get__meta_categorys();//$this->categories;
    
        return $output;
    }

    
    private function _aggregate_row($row, $catkey, $cat, $prow)
    {
        $myrow = $row;
    
//         $myrow['_id'] = $row['id'];
//         $myrow['_pid'] = $prow['id'];
    
    
        if(isset($prow['create_date'])){
            $myrow['__pcreate_date'] = $prow['create_date'];
        }
    
        if(isset($prow['change_date'])){
            $myrow['__pchange_date'] = $prow['change_date'];
        }
    
        
        $extract = $this->_getExtract($catkey, $myrow);
        
        $address = $this->_getAddress($catkey, $myrow);
        
        $editDialogHtml = $myrow['editDialogHtml'];
        $editDialogHtml_extra = $myrow['editDialogHtml_extra'];
        $editDialogHtml_extra_remove = $myrow['editDialogHtml_extra_remove'];
        
        if (isset($myrow['editDialogHtml'])) {
            unset($myrow['editDialogHtml']);
        }

        //ISPC-2508 Carmen // Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
        if (isset($myrow['editDialogHtml_extra'])) {
        	unset($myrow['editDialogHtml_extra']);
        }
        //ISPC-2508 Carmen 20.05.2020 new design
		//#ISPC-2512PatientCharts
        if (isset($myrow['editDialogHtml_extra_remove'])) {
        	unset($myrow['editDialogHtml_extra_remove']);
        }
		//--
        unset($cat['cols']); // do not send this
    
        $out=array(
    
            //'data'              => $myrow, // do not send this
            'editDialogHtml'    => $editDialogHtml,
        	'editDialogHtml_extra'    => $editDialogHtml_extra,
        	'editDialogHtml_extra_remove'    => $editDialogHtml_extra_remove, //ISPC-2508 Carmen 20.05.2020 new design
            'extract'           => $extract,
            'address'           => $address,
            'meta'              => $cat,
    
    
        );
    
        return $out;
    }
    
    private function _getExtract($catkey, $row)
    {
//         if ($catkey != 'SapvVerordnung') return;
        
        $extractmap = $this->categories[$catkey]['extract'];
        
        $userDefinedList = $this->categories[$catkey]['userDefinedList'];

        $rows=array();
        
        
        if (isset($row['extract']) && ! empty($row['extract'])) {
            
            //you manualy rendered something, and you want that dispayed
            return [[0 => null, 1 => $row['extract']]];
            
        } elseif ($this->categories[$catkey]['inlineEdit'] || is_null($extractmap)) {
            //inlineEdit has just one box, no details, no address
            if (isset($this->categories[$catkey]['extract']) 
                && ! is_null($this->categories[$catkey]['extract']) 
                && $this->categories[$catkey]['extract'] != false) 
            {
                $inlineHtml = $this->categories[$catkey]['extract'];
                
            } else {
                $inlineHtml = ! empty($row['editDialogHtml']) ? $row['editDialogHtml'] : $this->categories[$catkey]['addnewDialogHtml'];
                
            }
            
            return [[0 => null, 1 => $inlineHtml]];
            
        } 
        

        
        foreach ($extractmap as $rowmap) {
    
            $parts = array();
            
            foreach ($rowmap["cols"] as $col_key => $col) {
                
                if (is_numeric($col_key)) {
                     
                    $part = $row[$col];
                
                } else {
                
                    $part = $row[$col_key][$col];
                
                }
                
                if (is_array($part)) {
                    
                    foreach ($part as $subpartkey => $subpart) {
                        
                        $subpart = ! empty($userDefinedList) && isset($userDefinedList[$col]) && isset($userDefinedList[$col][$subpart]) ? $userDefinedList[$col][$subpart] : $subpart;
            
                        $this->_tryFormatExtractDate($subpart, $catkey, $col);
                        
                        $parts[$subpartkey] = $subpart;
                        
                    }
                } else {
                    
                    $part = ! empty($userDefinedList) && isset($userDefinedList[$col]) && isset($userDefinedList[$col][$part]) ? $userDefinedList[$col][$part] : $part;
                    
                    $this->_tryFormatExtractDate($part, $catkey, $col);
                    
                    $parts[$col] = $part;
                }
            }
            //ISPC-2381 Carmen 13.01.2021 
            if($catkey != "PatientAids")
            {
            	$text = $this->_extractFormatParts($parts, $rowmap);   
            }
            else 
            {
            	$text = '';
            	$texttable ="<table>";
            	foreach($parts as $pkey => $vpkey)
            	{
            	    if(!empty($vpkey)){        //TODO-3848 Lore 12.02.2021
            	        
            	        $texttable .= '<tr>';
            	        if($pkey != 'aid')
            	        {//dd($parts,$pkey,$vpkey);
                	        if(!is_array($vpkey))
                	        {
                	            if($vpkey != "0")
                	            {
                	                if($vpkey != "")
                	                {
                	                    $texttable .= '<td width="4%">&nbsp;</td>';
                	                    if($vpkey == 'Ja')
                	                    {
                	                        $texttable .= '<td width="50%">'.$this->translate($pkey) . '</td><td width="1%">&nbsp;</td><td width="44%">vorhanden</td>';
                	                    }
                	                    else if($vpkey == 'Nein')
                	                    {
                	                        $texttable .= '<td width="50%">'.$this->translate($pkey) . '</td><td width="1%">&nbsp;</td><td width="44%">nicht vorhanden</td>';
                	                    }
                	                    else
                	                    {
                	                        $texttable .= '<td width="50%">'.$this->translate($pkey) . '</td><td width="1%">&nbsp;</td><td width="44%">' .$vpkey . "</td>";
                	                    }
                	                }
                	            }
                	        }
                	        else
                	        {
                	            $texttable .= '<td width="4%">&nbsp;</td>';
                	            $texttable .= '<td width="50%">'.$this->translate($pkey) . '</td><td width="1%">&nbsp;</td><td width="44%">' .implode(",", $vpkey) . "</td>";
                	        }
            	        }
            	        else
            	        {
            	            $text .= $vpkey . "<br />";
            	        }
            	        $texttable .= '</tr>';
            	    }

            	}
            	$texttable .= '</table>';
            	$text = $text . $texttable;
            }
            //--
            if ( ! empty($text)) {
                $rows[] = array(
                    0 => $rowmap["label"], 
                    1 => $text
                );
            }
    
        }
    
        return $rows;
    }
    
    
    /**
     * @cla on 12.07.2018
     * !!! reference
     * Y-m-d                => d.m.Y
     * 0000-00-00           => -
     * Y-m-d H:i:s          => d.m.Y H:i
     * 0000-00-00 00:00:00  => -
     * 
     * @param string $part
     * @param string $catkey
     * @param string $col
     */
    private function _tryFormatExtractDate(&$part, $catkey = null, $col = null)
    {
        if ( ! empty($part)
            && ! empty($catkey) 
            && ! empty($col)
            && isset($this->categories[$catkey] ['cols'][$col])
            && in_array($this->categories[$catkey] ['cols'][$col]['type'], ['date', 'datetime', 'timestamp'])
//             && ( preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])(\s(((0|1)[0-9]{1}|2[0-4]{1}):([0-5]{1}[0-9]{1}):([0-5]{1}[0-9]{1})))?$/", $part)
//                 //or allready formated date but with 00:00:00
//                 || preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\.[0-9]{4}(\s(((0|1)[0-9]{1}|2[0-4]{1}):([0-5]{1}[0-9]{1}):([0-5]{1}[0-9]{1})))?$/", $part)
//             )
        ) {
            
            if ($part == "0000-00-00 00:00:00" || $part == "0000-00-00") {
                $part = "-";
            } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])(\s(((0|1)[0-9]{1}|2[0-4]{1}):([0-5]{1}[0-9]{1}):([0-5]{1}[0-9]{1})))?$/", $part)
                //or allready formated date but with 00:00:00
                || preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\.[0-9]{4}(\s(((0|1)[0-9]{1}|2[0-4]{1}):([0-5]{1}[0-9]{1}):([0-5]{1}[0-9]{1})))?$/", $part)) 
            {
                $dt = new DateTime($part);
                if ($dt !== false && ! array_sum($dt->getLastErrors())) {
                
                    if ($dt->format("H:i:s") == "00:00:00" ) {
                        //midnight of this should have been a DATE
                        $part = $dt->format("d.m.Y");
                    } else {
                        $part = $dt->format("d.m.Y H:i");
                    }
                } else {
                    //this is where 0000-00-00 00:00:00 ends up... what to do with this..
                    $part = "-";
                }
                 
            } 
              
            
            
        }
    }
    
    /**
     * 
     * 
     * @param string $part
     * @param array $rowmap
     */
    private function _extractFormatParts($parts = array(), $rowmap = array())
    {
        $result = '';
        
        if ( ! array_filter($parts)) {
            //to  bypases vsprintf and send empty text
        } elseif ( ! empty($parts) && ! empty($rowmap)
            && (isset($rowmap ['vsprintf']) || isset($rowmap ['vsprintf_named'])))
        {
            
            if ( ! empty($rowmap ['vsprintf_named'])) {
                
                $result = Pms_CommonData::vsprintf_named($rowmap ['vsprintf_named'], $parts);
                
            } elseif ( ! empty($rowmap ['vsprintf'])) {
                
                $result = vsprintf($rowmap ['vsprintf'], array_values($parts));
                
            }
                  
        } elseif ( ! empty($parts)) {
            $result = trim(implode(" ", $parts));
        }
        
        return $result;
        
    }
    
    
    
    
    
    
    private function _getAddress($catkey, $row)
    {
        $extractmap = $this->categories[$catkey]['address'];
    
        $userDefinedList = $this->categories[$catkey]['userDefinedList'];
    
        $rows=array();
        
        if (is_null($extractmap)) {
            return null;
        }
        
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
    
    
    private function __get__meta_categorys()
    {        
        
        $result = [];
        
        foreach ($this->categories as $catkey=>$cat) {
            //filter and send only the requested model
            if ( ! is_null($this->_init_onlyThisModel)) {
                if ($catkey != $this->_init_onlyThisModel
                    && (empty($this->categories[$this->_init_onlyThisModel]['__linked_categories']) || ! in_array($catkey, $this->categories[$this->_init_onlyThisModel]['__linked_categories'])))
                {
                    continue;
                }
            }
            
            if (APPLICATION_ENV !== "development") {
                $cat['cols'] = null;
            }
            
            $result[$catkey] = $cat;
        }
        
        return $result;
        //filter cols and other stuff.. from $this->categories 
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
    
    

    
    
    private function __init_PatientMaster_MotherForm()
    {
        $modelMother = "PatientMaster";
    
        if ( ! isset( $this->_categoriesForms->$modelMother)) {
    
            $this->_categoriesForms->$modelMother = (new Application_Form_PatientMaster(array(
                "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$modelMother}]",
                "_patientMasterData"    => $this->_patientMasterData,
                "_block_name"           => $this->_block_name,
                "_clientForms"          => $this->_clientForms,
            )));
        }
    }
    
    private function __init_box_PatientMaster ($model = 'PatientMaster')
    {
        
        $modelMother = "PatientMaster";
        $this->__init_PatientMaster_MotherForm();
        
        $createBox_formFn = 'create_form_patient_details';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['userDefinedList']['sex'] =  $this->_categoriesForms->$model->getSexArray();
        
        if ( ! empty($this->_patientMasterData[$model])) {
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData)->__toString();
        }
    }    
    
    
    
    private function __init_box_PatientMaster_Hospiz_Hospizverein_SAPV_AAPV ($model = 'PatientMaster_Hospiz_Hospizverein_SAPV_AAPV')
    {
        
        $modelMother = "PatientMaster";
        $this->__init_PatientMaster_MotherForm();
        
        /*
         * PatientMaster_Hospiz_Hospizverein_SAPV_AAPV
         * this is a child of PatientMaster
         */
        $createBox_formFn = 'create_form_patient_hospiz_hospizverein_sapv_aapv';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        $this->categories[$model]['addnewDialogHtml_subForms'] = array(
            'set_ishospiz_visitors' => $this->_categoriesForms->$model->create_form_patient_hospiz_hospizverein_sapv_aapv_set_ishospiz_visitors()->__toString(),
            'set_ishospizverein_visitors' => $this->_categoriesForms->$model->create_form_patient_hospiz_hospizverein_sapv_aapv_set_ishospizverein_visitors()->__toString(),
            
        );
    }    
    
    
    private function __init_box_PatientReadmission ($model = 'PatientReadmission')
    {
        /*
         * PatientReadmission
         * this is the old one, copied to patientnew/patientdetails_box_history_35.phtml
         */
        $this->__box_PatientReadmission_View_Assign();
        $this->categories[$model]['extract'] = $this->getView()->render("patientnew/patientdetails_box_history_35.phtml");
        $this->categories[$model]['address'] = false;
        $this->_patientMasterData[$model][0]['editDialogHtml'] = "justNotEmpty"; //force to open the box with fake content
        
    }


    //Maria:: Migration CISPC to ISPC 22.07.2020
    private function __init_box_PatientCaseStatus ($model = 'PatientCaseStatus')
    {
        /*
         * PatientCaseStatus
         * clinic cases
         */
        $this->__box_PatientCaseStatus_View_Assign();
        $this->categories[$model]['extract'] = $this->getView()->render("patientnew/patientdetails_box_casestatus_101.phtml");
        $this->categories[$model]['address'] = false;
        $this->_patientMasterData[$model][0]['editDialogHtml'] = "justNotEmpty"; //force to open the box with fake content

    }

    //ISPC-2400
    private function __init_box_PatientCrisisHistory ($model = 'PatientCrisisHistory')
    {
       
        $createBox_formFn = 'create_form_PatientCrisisHistory';
              
        $this->_categoriesForms->$model  = (new Application_Form_PatientCrisisHistory(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));

        
       // $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        //$this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));

 
        if ( ! empty($this->_patientMasterData[$model]))         
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            } 
    }
           
    //ISPC-2669 Lore 23.09.2020
    private function __init_box_PatientHandicappedCard ($model = 'PatientHandicappedCard')
    {
        
        $createBox_formFn = 'create_form_patient_handicapped_card';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientHandicappedCard(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
                
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    //ISPC-2773 Lore 14.12.2020
    private function __init_box_PatientFamilyInfo ($model = 'PatientFamilyInfo')
    {
        
        $createBox_formFn = 'create_form_patient_family_info';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientFamilyInfo(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    //ISPC-2776 Lore 15.12.2020
    private function __init_box_PatientChildrenDiseases ($model = 'PatientChildrenDiseases')
    {
        
        $createBox_formFn = 'create_form_patient_children_diseases';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientChildrenDiseases(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    //ISPC-2673 Lore 30.09.2020     Ressourcen == FormBlockResources
    private function __init_box_FormBlockResources ($model = 'FormBlockResources')
    {
        
        $createBox_formFn = 'create_form_block_resources';
        
        $this->_categoriesForms->$model  = (new Application_Form_FormBlockResources(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model])){
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        }
    }
    
    //ISPC-2788 Lore 08.01.2021
    private function __init_box_PatientNutritionInfo ($model = 'PatientNutritionInfo')
    {
        
        $createBox_formFn = 'create_form_patient_nutrition_info';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientNutritionInfo(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    //ISPC-2787 Lore 11.01.2021
    private function __init_box_PatientStimulatorsInfo ($model = 'PatientStimulatorsInfo')
    {
        
        $createBox_formFn = 'create_form_patient_stimulators_info';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientStimulatorsInfo(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    //ISPC-2790 Lore 12.01.2021
    private function __init_box_PatientFinalPhase ($model = 'PatientFinalPhase')
    {
        
        $createBox_formFn = 'create_form_patient_final_phase';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientFinalPhase(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    //ISPC-2791 Lore 13.01.2021
    private function __init_box_PatientExcretionInfo ($model = 'PatientExcretionInfo')
    {
        
        $createBox_formFn = 'create_form_patient_excretion_info';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientExcretionInfo(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    //ISPC-2792 Lore 15.01.2021
    private function __init_box_PatientPersonalHygiene ($model = 'PatientPersonalHygiene')
    {
        
        $createBox_formFn = 'create_form_patient_personal_hygiene';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientPersonalHygiene(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model]))
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
    }
    
    //ISPC-2793 Lore 18.01.2021
    private function __init_box_PatientCommunicationEmployment ($model = 'PatientCommunicationEmployment')
    {
        
        $createBox_formFn = 'create_form_patient_communication_employment';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientCommunicationEmployment(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
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
    
    private function __init_box_ContactPersonMaster ($model = 'ContactPersonMaster')
    {
        /*
         * ContactPersonMaster
         */
        $createBox_formFn = 'create_form_contact_person';
        
        $this->_categoriesForms->$model  = (new Application_Form_ContactPersonMaster(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['userDefinedList']['cnt_hatversorgungsvollmacht'] =  $this->_categoriesForms->$model->getCntHatversorgungsvollmachtArray();
        $this->categories[$model]['userDefinedList']['cnt_legalguardian'] =  $this->_categoriesForms->$model->getCntLegalguardianArray();
        $this->categories[$model]['userDefinedList']['cnt_familydegree_id'] =  $this->_categoriesForms->$model->getFamilyDegreeArray();
        
        if ( ! empty($this->_patientMasterData[$model])) {
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        }
    }
    
    private function __init_box_PatientMobility2 ($model = 'PatientMobility2')
    {
        /*
         * PatientMobility2
         * Mobility II
         * inlineEdit
         */
        $createBox_formFn = 'create_form_mobility2';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientMobility2(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {
        
            $values = array_column($this->_patientMasterData[$model], 'selected_value');
            $values = ['selected_value' => $values];
        
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        
        }
    }
    
    private function __init_box_PatientOrientation ($model = 'PatientOrientation')
    {
        /*
         * PatientOrientation
         * Orientation II
         * inlineEdit
         */
        $createBox_formFn = 'create_form_orientation2';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientOrientation2(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {
        
            $values = array_column($this->_patientMasterData[$model], 'orientation');
            $values = ['orientation' => $values];
        
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        
        }
    }
    
    private function __init_box_PatientReligions ($model = 'PatientReligions')
    {
        /*
         * PatientReligions
         * Religionszugehörigkeit
         * inlineEdit
         */
        $createBox_formFn = 'create_form_religion';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientReligions(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {
        
            //$values = $this->_patientMasterData[$model][0]['religion'];
            if($this->_patientMasterData[$model][0]['religion'] > 0)
            {
	            $values = $this->_patientMasterData[$model][0]; // GET ALL VALUES
	        
	            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
            }
        
        }
    }
    
    private function __init_box_PatientSupply ($model = 'PatientSupply')
    {
        /*
         * PatientSupply
         * Versorgung
         * inlineEdit
         */
        $createBox_formFn = 'create_form_patient_supply';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientSupply(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {
        	$supcnt = $this->_patientMasterData[$model][0]['even'];
        	$supcnt += $this->_patientMasterData[$model][0]['spouse'];
        	$supcnt += $this->_patientMasterData[$model][0]['member'];
        	$supcnt += $this->_patientMasterData[$model][0]['private_support'];
        	$supcnt += $this->_patientMasterData[$model][0]['nursing'];
        	$supcnt += $this->_patientMasterData[$model][0]['palliativpflegedienst'];
        	$supcnt += $this->_patientMasterData[$model][0]['heimpersonal'];
        	 
        	if($supcnt > 0)
        	{
        
	            //             $values = [];
	            //             $cbArr = $this->_categoriesForms->$model->getCbValuesArray();
	            //             foreach ($cbArr as $key => $cb) {
	            //                 if ($this->_patientMasterData[$model][0][$key] == 1) {
	            //                     array_push($values, $key);
	            //                 }
	            //             }
	            $values = ! empty($this->_patientMasterData[$model][0]) ? $this->_patientMasterData[$model][0] : array();
	        
	            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        	}
        
        }
    }
    
    private function __init_box_PatientMobility ($model = 'PatientMobility')
    {
        /*
         * PatientMobility
         * Mobilität
         * inlineEdit
         */
        $createBox_formFn = 'create_form_patient_mobility';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientMobility(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
       
        if ( ! empty($this->_patientMasterData[$model])) {
        	$mobcnt = $this->_patientMasterData[$model][0]['bed'];
        	$mobcnt += $this->_patientMasterData[$model][0]['walker'];
        	$mobcnt += $this->_patientMasterData[$model][0]['wheelchair'];
        	$mobcnt += $this->_patientMasterData[$model][0]['goable'];
        	$mobcnt += $this->_patientMasterData[$model][0]['nachtstuhl'];
        	$mobcnt += $this->_patientMasterData[$model][0]['wechseldruckmatraze'];
        	
        	if($mobcnt > 0)
			{
        
	            $values = ! empty($this->_patientMasterData[$model][0]) ? $this->_patientMasterData[$model][0] : array();
	        
	            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
			}
        
        }
    }
    
    private function __init_box_PatientLives ($model = 'PatientLives')
    {
        /*
         * PatientLives
         * Patient lebt
         * inlineEdit
         */
        $createBox_formFn = 'create_form_patient_lives';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientLives(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {
        	$livcnt = $this->_patientMasterData[$model][0]['alone'];
        	$livcnt += $this->_patientMasterData[$model][0]['house_of_relatives'];
        	$livcnt += $this->_patientMasterData[$model][0]['apartment'];
        	$livcnt += $this->_patientMasterData[$model][0]['home'];
        	$livcnt += $this->_patientMasterData[$model][0]['hospiz'];
        	$livcnt += $this->_patientMasterData[$model][0]['sonstiges'];
        	$livcnt += $this->_patientMasterData[$model][0]['with_partner'];
        	$livcnt += $this->_patientMasterData[$model][0]['with_child'];
        	
        	if($livcnt > 0)
        	{
            	$values = ! empty($this->_patientMasterData[$model][0]) ? $this->_patientMasterData[$model][0] : array();
        	
        
            	$this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        	}
        }
    }
    
    private function __init_box_PatientGermination ($model = 'PatientGermination')
    {
        /*
         * PatientGermination
         * Keimbesiedelung
         * inlineEdit
         */
        $createBox_formFn = 'create_form_patient_germination';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientGermination(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {
        	$gercnt = $this->_patientMasterData[$model][0]['germination_cbox'];
        	$gercnt += $this->_patientMasterData[$model][0]['iso_cbox'];
        	
        	if($gercnt > 0)
        	{
            	$values = ! empty($this->_patientMasterData[$model][0]) ? $this->_patientMasterData[$model][0] : array();
        
           		$this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        	}
        
        }
    }
    
    private function __init_box_PatientMaintainanceStage ($model = 'PatientMaintainanceStage')
    {
        /*
         * PatientMaintainanceStage
         * Pflegegrade == Pflegestufe
         * inlineEdit
         */
        $createBox_formFn = 'create_form_maintenance_stage';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientMaintainanceStage(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['userDefinedList']['erstantrag'] = $this->_categoriesForms->$model->getErstantragArray();
        $this->categories[$model]['userDefinedList']['horherstufung'] = $this->_categoriesForms->$model->getHorherstufungArray();
        
        
        if ( ! empty($this->_patientMasterData[$model])) {
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        }
    }
    
    private function __init_box_PatientAcp ($model = 'PatientAcp')
    {
        /*
         * PatientAcp
         * Patientenverfügung = ACP
         * inlineEdit
         */
        $createBox_formFn = 'create_form_acp';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientACP(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}(['contact_persons_arr' => $this->_patientMasterData[$model]['contact_persons_arr']])->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, 'save_form_acp_all_tabs');
        
        if ( ! empty($this->_patientMasterData[$model]) 
            && (isset($this->_patientMasterData[$model]['living_will']) 
                || isset($this->_patientMasterData[$model]['care_orders']) 
                || isset($this->_patientMasterData[$model]['healthcare_proxy'])
            	|| isset($this->_patientMasterData[$model]['emergencyplan'])
                )
            ) 
        {
            //!!! chnaged structure of array $this->_patientMasterData[$model]                
        	if(!$this->_patientMasterData[$model]['living_will']['active'] && !$this->_patientMasterData[$model]['care_orders']['active'] && !$this->_patientMasterData[$model]['healthcare_proxy']['active'] && !$this->_patientMasterData[$model]['emergencyplan']['files'])
        	{
        		$this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
        		$this->_patientMasterData[$model][1]['editDialogHtml'] = '[';
        	}
        	else
        	{
	            $this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];    
	            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData[$model][0])->__toString();
	             // just need the first to be populated with editDialogHtml
        	}
        }
        else
        {
        	$this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
        	$this->_patientMasterData[$model][1]['editDialogHtml'] = '[';
        }
    }
    
    private function __init_box_PatientVisitsSettings ($model = 'PatientVisitsSettings')
    {
        /*
         * PatientVisitsSettings
         * VisitsPlanning = Tourenplanung
         * inlineEdit ... fully js, NO form is created with the saved values
         */
        $createBox_formFn = 'create_form_patient_visits_settings';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientVisitsSettings(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData[$model])->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {            
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = "justNotEmpty";//$this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData[$model])->__toString();
                break; // just need the first to be populated with editDialogHtml
            }
        }
    }
    
    private function __init_box_PatientTherapieplanung ($model = 'PatientTherapieplanung')
    {
        /*
         * PatientTherapieplanung
         * Vorausschauende Therapieplanung
         * inlineEdit
         */
        $createBox_formFn = 'create_form_patient_therapieplanung';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientTherapieplanung(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {
        	$tercnt = $this->_patientMasterData[$model][0]['ernahrungstherapie'];
        	$tercnt += $this->_patientMasterData[$model][0]['infusionstherapie'];
        	$tercnt += $this->_patientMasterData[$model][0]['antibiose_bei_pneumonie'];
        	$tercnt += $this->_patientMasterData[$model][0]['antibiose_bei_HWI'];
        	$tercnt += $this->_patientMasterData[$model][0]['tumorreduktionstherapie_chemo'];
        	$tercnt += $this->_patientMasterData[$model][0]['krankenhausverlegung'];
        	$tercnt += $this->_patientMasterData[$model][0]['lagerung_durch_pflege'];
        	$tercnt += $this->_patientMasterData[$model][0]['orale_medikation_mehr'];
        	$tercnt += $this->_patientMasterData[$model][0]['blut_volumenersatztherapie'];
        	$tercnt += $this->_patientMasterData[$model][0]['palliative'];
        	 
        	if($tercnt > 0 || $this->_patientMasterData[$model][0]['freetext'] != "")
        	{
	            foreach ($this->_patientMasterData[$model] as $key => $row) {
	                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
	                break; // just need the first to be populated with editDialogHtml
	            }
        	}
        }
    }
    
    private function __init_box_PatientHospizverein ($model = 'PatientHospizverein')
    {
        /*
         * PatientHospizverein
         * Hospizverein
         */
        $createBox_formFn = 'create_form_patient_hospizverein';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientHospizverein(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        $this->categories[$model]['userDefinedList']['hospizverein'] = [1 => 'Ja', 2 => 'Nein'];
        
        if ( ! empty($this->_patientMasterData[$model])) {
            foreach ($this->_patientMasterData[$model] as $key => $row) {
        
                //hack from $displayhospizverein_special
                if ($this->logininfo->clientid != 48 ) {
                    $this->_patientMasterData[$model][$key]['hospizverein'] = null;
                }
                
                if($this->logininfo->clientid != 48)
                {
                	if($this->_patientMasterData[$model][$key]['hospizverein_txt'] != "")
                	{
                		$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
                	}
                }
                else 
                {
        
                	$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
                }
            }
           // print_R($this->_patientMasterData[$model][$key]['editDialogHtml']); exit;
        }
    }
    
    private function __init_box_PatientMedipumps ($model = 'PatientMedipumps')
    {
        /*
         * PatientMedipumps
         * Hilfsmittel Verleih
         */
        $createBox_formFn = 'create_form_patient_medipump';
        
        $this->_categoriesForms->$model  = (new Application_Form_PatientMedipumps(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        foreach ($this->_patientMasterData['Medipumps'] as $row) {
            $userDefinedList [ $row['id'] ] = $row['medipump'];
        }
        $this->categories[$model]['userDefinedList']['medipump'] = $userDefinedList;
        
        if ( ! empty($this->_patientMasterData[$model])) {
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        }
    }
    
    private function __init_box_SapvVerordnung ($model = 'SapvVerordnung')
    {
        /*
         * SapvVerordnung
         * Sapv Verordnung
         */
        $createBox_formFn = 'create_form_patient_sapv_verordnung';
        $boxopen = false;
        
        $this->_categoriesForms->$model  = (new Application_Form_SapvVerordnung(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        
        $division1 = array_filter($this->_patientMasterData[$model], function($item) {return $item["__division"] == 1 && ! empty($item['id']);});
        $total_saved_in_tab_sapv = (count($division1));
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}(["__total_saved_in_tab_sapv" => $total_saved_in_tab_sapv])->__toString();
        //         $this->categories[$model]['extract'] = $this->_patientMasterData[$model]['extract'];
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['userDefinedList']['verordnet'] = Pms_CommonData::getSapvCheckBox();
        
        if ( ! empty($this->_patientMasterData[$model])) {
        
            foreach ($this->_patientMasterData[$model] as $key => $row) {
        
                switch ($row['__division']) {
                    case "1" :
                    	$sapvdata = $this->_patientMasterData[$model][$key];
                    	unset($sapvdata['__division']);
                    	unset($sapvdata['__division_legend']);
                    	//var_dump($sapvdata); exit;
                    	if(!empty($sapvdata))
                    	{                    		
                        	$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
                        	$boxopen = true;
                    	}
                    	else 
                    	{
                    		$this->_patientMasterData[$model][1]['editDialogHtml'] = "[";
                    	}
        
                        break;
        
                    case "2" :
        
                        //                         patientdetails_box_sapv_sgbv_11.phtml
                        $this->__box_SapvVerordnung_SGBV_View_Assign($row);
                        if($boxopen === true)
                        {
	                        $this->_patientMasterData[$model][$key]['extract'] = $this->getView()->render("patientnew/patientdetails_box_sapv_sgbv_11.phtml");
	                        $this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                        }
                        else 
                        {
                        	if ( ! empty($row['patient_sgbv_actions']
                        			|| ! empty($row['patient_sgbv_actions_foc']))
                        			|| ! empty($row['sgbv_status']))
                        	{
                        		$this->_patientMasterData[$model][$key]['extract'] = $this->getView()->render("patientnew/patientdetails_box_sapv_sgbv_11.phtml");
                        		$this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                        		$boxopen = true;
                        	}
                        	else
                        	{
                        		$this->_patientMasterData[$model][$key]['extract'] = $this->getView()->render("patientnew/patientdetails_box_sapv_sgbv_11.phtml");
                        		$this->_patientMasterData[$model][1]['editDialogHtml'] = '[';
                        	}
                        }
        
                        break;
        
                    case "3" :
                        //                         $this->_patientMasterData[$model][$key]['extract'] = $row['__division_legend'];//'Pflegevertrag nach SGBXI';
                        //$this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                       if($boxopen === true)
                       {
                        	$this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                       }
                       else 
                       {
                       		$this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                    		$this->_patientMasterData[$model][1]['editDialogHtml'] = '[';
                       }
        
                        break;
        
                    case "4" :
                        //                         $this->_patientMasterData[$model][$key]['extract'] = $row['__division_legend'];//Überweisungsschein für ärztliche Leistung';
                		if($boxopen === true)
                       	{
                        	$this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                       	}
                       	else 
                       	{
                       		$this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                    		$this->_patientMasterData[$model][1]['editDialogHtml'] = '[';
                       	}
        
                        break;
        
                    case "5" :
                         
                        //                         patientdetails_box_sapv_pflegebesuche_11.phtml
                        $this->__box_SapvVerordnung_PflegebesucheV_View_Assign($row);
                        if($boxopen === true)
                        {
		                        $this->_patientMasterData[$model][$key]['extract'] = $this->getView()->render("patientnew/patientdetails_box_sapv_pflegebesuche_11.phtml");
		                        $this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                        }
                        else 
                        {
                        	if($row[1]['approved_visit_type'])
                        	{
                        		$this->_patientMasterData[$model][$key]['extract'] = $this->getView()->render("patientnew/patientdetails_box_sapv_pflegebesuche_11.phtml");
                        		$this->_patientMasterData[$model][$key]['editDialogHtml'] = 'justNotEmpty';
                        	}
                        	else
                        	{
                        		$this->_patientMasterData[$model][$key]['extract'] = $this->getView()->render("patientnew/patientdetails_box_sapv_pflegebesuche_11.phtml");
                        		$this->_patientMasterData[$model][$key]['editDialogHtml'] = '[';
                        	}
                        }
        
                        break;
                }
        
        
            }
        }
            
    }
    
    
    
    /*
     * multiple blocks from the same main model Stammdatenerweitert
     *
     * this are the childrens
     "Familienstand",  - done
     "Vigilanz", - done
     "Ernahrung", - done
     "Kunstliche", - done
     "Orientierung", -done
     "Sprachlich", - i did not found this box
     "Ausscheidung", - done
     "Stastszugehorigkeit", -done
     "Hilfsmittel", - done
     "Wunsch", - done
     */
    
    private function __init_Stammdatenerweitert_MotherForm() 
    {
        $modelMother = "Stammdatenerweitert";
        
        if ( ! isset( $this->_categoriesForms->$modelMother)) {
            
             $this->_categoriesForms->$modelMother = (new Application_Form_Stammdatenerweitert(array(
                "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$modelMother}]",
                "_patientMasterData"    => $this->_patientMasterData,
                "_block_name"           => $this->_block_name,
                "_clientForms"          => $this->_clientForms,
            )));
        }
    }
    
    private function __init_box_Stammdatenerweitert_ausscheidung ($model = 'Stammdatenerweitert_ausscheidung')
    {
        
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_ausscheidung
         * Ausscheidung
         * inlineEdit
         */
        $createBox_formFn = 'create_form_excretion';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$modelMother]['ausscheidung'])) {
            $values = explode(',', $this->_patientMasterData[$modelMother]['ausscheidung']);
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        }
    }
    
    
    private function __init_box_Stammdatenerweitert_vigilanz ($model = 'Stammdatenerweitert_vigilanz')
    {
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_vigilanz
         * Vigilanz
         * inlineEdit
         */
        $createBox_formFn = 'create_form_vigilanz';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$modelMother]['vigilanz'])) {
            $values = $this->_patientMasterData[$modelMother]['vigilanz'];
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        }
    }
    
    private function __init_box_Stammdatenerweitert_kunstliche ($model = 'Stammdatenerweitert_kunstliche')
    {
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_kunstliche
         * Künstliche Ausgänge
         * inlineEdit
         */
        $createBox_formFn = 'create_form_artificial_exits';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$modelMother]['kunstliche']) 
            || ! empty($this->_patientMasterData[$modelMother]['kunstlichemore'])) 
        {
            $values = array(
                'kunstliche' => explode(',', $this->_patientMasterData[$modelMother]['kunstliche']),
                'kunstlichemore' => $this->_patientMasterData[$modelMother]['kunstlichemore']
            );            
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        }
    }
    
    private function __init_box_Stammdatenerweitert_familienstand ($model = 'Stammdatenerweitert_familienstand')
    {
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_familienstand
         * Familienstand
         * inlineEdit
         */
        $createBox_formFn = 'create_form_marital_status';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$modelMother]['familienstand'])) {
            $values =  $this->_patientMasterData[$modelMother]['familienstand'];
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        }
    }
    
    private function __init_box_Stammdatenerweitert_stastszugehorigkeit ($model = 'Stammdatenerweitert_stastszugehorigkeit')
    {
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_stastszugehorigkeit
         * Staatszugehörigkeit
         * inlineEdit
         */
        $createBox_formFn = 'create_form_nationality';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( 
            ! empty($this->_patientMasterData[$modelMother]['stastszugehorigkeit']) 
            || ! empty($this->_patientMasterData[$modelMother]['anderefree']) 
            || ! empty($this->_patientMasterData[$modelMother]['2ndstastszugehorigkeit']) 
            || ! empty($this->_patientMasterData[$modelMother]['2ndanderefree'])
            || ! empty($this->_patientMasterData[$modelMother]['dolmetscher'])
            )
        {
            $values = array(
                'stastszugehorigkeit' => $this->_patientMasterData[$modelMother]['stastszugehorigkeit'],
                'anderefree' => $this->_patientMasterData[$modelMother]['anderefree'],
                '2ndstastszugehorigkeit' => $this->_patientMasterData[$modelMother]['2ndstastszugehorigkeit'],
                '2ndanderefree' => $this->_patientMasterData[$modelMother]['2ndanderefree'],
                'dolmetscher' => $this->_patientMasterData[$modelMother]['dolmetscher']
            );            
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        }
    }
    
    private function __init_box_Stammdatenerweitert_hilfsmittel ($model = 'Stammdatenerweitert_hilfsmittel')
    {
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_hilfsmittel
         * Hilfsmittel
         * inlineEdit
         * 
         * this is a combination, of Stammdatenerweitert->hilfsmittel + PatientMoreInfo->pumps
         * 
         */
        $createBox_formFn = 'create_form_hilfsmittel';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
      
        if ( ! empty($this->_patientMasterData[$modelMother]['hilfsmittel']) || ! empty($this->_patientMasterData['PatientMoreInfo'])) {
        	$hilfcnt = $this->_patientMasterData['PatientMoreInfo']['pumps'];
        	
            $values = array(
                'hilfsmittel' => $this->_patientMasterData[$modelMother]['hilfsmittel'] ? explode(",", $this->_patientMasterData[$modelMother]['hilfsmittel']) : null,
                'PatientMoreInfo' => $hilfcnt > 0 ? $this->_patientMasterData['PatientMoreInfo'] : null,
            );
            
            if($values['hilfsmittel'] || $values['PatientMoreInfo'])
            {
            	$this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
            }
        }
    }
    
    private function __init_box_Stammdatenerweitert_ernahrung ($model = 'Stammdatenerweitert_ernahrung')
    {
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_ernahrung
         * Ernahrung
         * inlineEdit
         * 
         * this is a combination, of Stammdatenerweitert->ernahrung + PatientMoreInfo-> peg, port, zvk, magensonde
         * 
         */
        $createBox_formFn = 'create_form_ernahrung';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        //print_r($this->_patientMasterData['PatientMoreInfo']); exit;
        if ( ! empty($this->_patientMasterData[$modelMother]['ernahrung']) || ! empty($this->_patientMasterData['PatientMoreInfo'])) {
        	$erncnt = $this->_patientMasterData['PatientMoreInfo']['port'];
        	$erncnt += $this->_patientMasterData['PatientMoreInfo']['zvk'];
        	$erncnt += $this->_patientMasterData['PatientMoreInfo']['magensonde'];
        	
            $values = array(
                'ernahrung' => $this->_patientMasterData[$modelMother]['ernahrung'] ? explode(",", $this->_patientMasterData[$modelMother]['ernahrung']) : null,
                'PatientMoreInfo' => $erncnt ? $this->_patientMasterData['PatientMoreInfo'] : null,
            ); 
            
            if($values['ernahrung'] || $values['PatientMoreInfo'])
            {
            	$this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
            }
            //dd($this->_patientMasterData[$model][0]['editDialogHtml']);
        }
    }
    
    private function __init_box_Stammdatenerweitert_wunsch ($model = 'Stammdatenerweitert_wunsch')
    {
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_wunsch
         * Wunsch des Patienten
         * inlineEdit
         */
        $createBox_formFn = 'create_form_wunsch';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$modelMother]['wunsch'])) {
            $values = array(
                'wunsch' => explode(",", $this->_patientMasterData[$modelMother]['wunsch']),
                'wunschmore' => $this->_patientMasterData[$modelMother]['wunschmore'],
            );            
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        }
    }
    
    private function __init_box_Stammdatenerweitert_orientierung ($model = 'Stammdatenerweitert_orientierung')
    {
        $modelMother = "Stammdatenerweitert";
        $this->__init_Stammdatenerweitert_MotherForm();
        
        /*
         * Stammdatenerweitert_orientierung
         * Wunsch des Patienten
         * inlineEdit
         */
        $createBox_formFn = 'create_form_orientierung';
        
        $this->_categoriesForms->$model  = $this->_categoriesForms->$modelMother;
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$modelMother]['orientierung'])) {
            $values = array(
                'orientierung' => explode(",", $this->_patientMasterData[$modelMother]['orientierung']),
            );
            $optionsCb2 = Stammdatenerweitert::getOrientierungfun2();
            foreach ($optionsCb2 as $k => $tr) {
                $values['orientierung'][] = $this->_patientMasterData[$modelMother][$k] == 1 ? $k : null;
            }
            
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();
        }
    }
    
    
    private function __init_box_PatientLocation ($model = 'PatientLocation')
    {
        /*
         * PatientLocation
         * Aufenthaltsort
         * fake inlineEdit, will redirect to original edit page
         *
         */
    	
    	//ISPC - 2320 - add info button in patientformnew/sapvevaluation formular
        //$createBox_formFn = 'create_form_all_patient_location_display';
    	$createBox_formFn = key($this->_block_name_allowed_inputs[$this->_block_name]);
    	
        $this->_categoriesForms->$model  = (new Application_Form_PatientLocation(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        if ( ! empty($this->_patientMasterData[$model])) {
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData[$model])->__toString();
        }
    }

    
    
    private function __init_box_PatientEmploymentSituation ($model = 'PatientEmploymentSituation')
    {
        /*
         * PatientEmploymentsituation
         * Erwerbssituation
         * inlineEdit
         *
         */
        $createBox_formFn = 'create_form_patient_employment_situation';
    
        $this->_categoriesForms->$model  = (new Application_Form_PatientEmploymentsituation(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
    
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        $this->categories[$model]['extract'] = false;
        $this->categories[$model]['address'] = false;
        
        $this->categories[$model]['userDefinedList']['status'] =  PatientEmploymentSituation::getStatusValuesArray();
    	
        if ( ! empty($this->_patientMasterData[$model])) {
        	//!!! chnaged structure of array $this->_patientMasterData[$model]
        	$this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
        	if($this->_patientMasterData[$model][0]['status'] || $this->_patientMasterData[$model][0]['supplementary_services'] || $this->_patientMasterData[$model][0]['comments'])
        	{
	            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData[$model][0])->__toString();
        	}
        	else 
        	{
        		$this->_patientMasterData[$model][1]['editDialogHtml'] = "[";
        	}
        }
    }

	//ISPC-2694 Elena ?
    private function __box_Anamnese_View_Assign()
    {

        $ipid = $this->ipid;
        $patientdetails = $this->_patientMasterData;
        $pdata = [];

        $this->getView()->pid = $this->enc_id;

        $this->getView()->custom = 'custom';

        $cc=new Anamnese();
        $rawdata=$cc->getLastBlockValues($ipid);
        if(is_array($rawdata)){
            $pdata['anamnese'] = $rawdata[0];
            foreach ($pdata as $key => $val) {
                $this->getView()->{$key} = $val;

            }
        }


    }

    /**
     * //ISPC-2694, elena, 17.12.2020
     * @param string $model
     */
    private function __init_box_Anamnese ($model = 'Application_Form_FormBlockAnamnese')
    {
        /*
         * Anamnese
         *
         * inlineEdit
         *
         */
        $createBox_formFn = 'create_stammdaten_box_anamnese';

        $createBox_showDataFn = 'createViewAnamnese';


        $this->_categoriesForms->$model  = (new Application_Form_FormBlockAnamnese(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));

        //echo $ipid;
        //print_r($options);
        //echo 'anamnese';

        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();

        $this->categories[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        //echo $createBox_formFn;
        //print_r($this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
       // $this->__box_Anamnese_View_Assign();

        $this->_patientMasterData[$model][0]['extract'] = $this->_categoriesForms->$model->{$createBox_showDataFn}();
        //$this->_patientMasterData[$model][0]['extract'] =  $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
            //$this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();


        //$this->categories[$model]['extract'] = false; // $this->_categoriesForms->$model->{$createBox_showDataFn}();;
        $this->categories[$model]['address'] = false;
        $this->categories[$model]['addDataHTML'] =  $this->_categoriesForms->$model->{$createBox_showDataFn}();
        //TODO-3848,Elena,11.02.2021
        if(isset($this->_patientMasterData[$model][0]['id'])){
            $this->categories[$model]['anamneseDataExists'] = 1;
        }
/*
        if ( ! empty($this->_patientMasterData[$model])) {
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();;
            $this->_patientMasterData['Anamnese'][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();;
        }else
        {
            $this->_patientMasterData[$model][1]['editDialogHtml'] = "[";
        }*/

        //$this->_patientMasterData[$model][0]['editDialogHtml'] =   $this->_categoriesForms->$model->{$createBox_showDataFn}(); // $this->_categoriesForms->$model->{$createBox_formFn}($values)->__toString();

        /*
        $this->categories[$model]['userDefinedList']['status'] =  PatientEmploymentSituation::getStatusValuesArray();

        if ( ! empty($this->_patientMasterData[$model])) {
        	//!!! chnaged structure of array $this->_patientMasterData[$model]
        	$this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
        	if($this->_patientMasterData[$model][0]['status'] || $this->_patientMasterData[$model][0]['supplementary_services'] || $this->_patientMasterData[$model][0]['comments'])
        	{
	            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData[$model][0])->__toString();
        	}
        	else
        	{
        		$this->_patientMasterData[$model][1]['editDialogHtml'] = "[";
        	}
        }*/
    }


    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function updatePatientDetails($post = array() , $ipid = null)
    {
        if (empty($ipid)) {
            $ipid = $this->ipid;
        }
    
        $result = false;
    
        if ( ! empty($post['__category']) && isset($this->_categoriesForms->{$post['__category']})) {
            
            $model = $post['__category'];
        
            $key = $model;
        
        } else {
        
            foreach ($post[$this->_categoriesForms_belongsTo] as $key => $data) {
                //if (isset($this->_categoriesForms->$model) ) { // ISPC-2671 Ancuta commented on 14.09.2020 - changed from $model to $key 
                if (isset($this->_categoriesForms->$key) ) {
                    $model = $key;
                    break;
                }
            }
        }
        
        
       
        if (isset($this->_categoriesForms->$model)
            && $this->_categoriesForms->$model instanceof Pms_Form )
        {
            
            //ISPC-2694, elena, 17.12.2020
            if($model == 'Anamnese'){
                $data = $post[$this->_categoriesForms_belongsTo];
                $data = is_array($data) ? $data : [$data];
            }else{
                $data = reset($post[$this->_categoriesForms_belongsTo]);
                $data = is_array($data) ? $data : [$data];
                //ISPC-2565,Elena,07.04.2021
                if($model == 'PatientAcp'){
                    $data['savedAcp'] = $this->_patientMasterData[$model];
                }
            }

            
            //ISPC-2508 Carmen 20.05.2020 new design
            /* if($post['__subaction'] && $post['__subaction'] == 'refresh') //ISPC-2508
            {
            	$data['__subaction'] = 'refresh';
            } */
            if($post['__subaction'])
            {
            	$data['__subaction'] = $post['__subaction'];
            }
			//--
            $result = $this ->_callFormSaveTriggers($model, $key, $data , $ipid);
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
        //ISPC-2508 Carmen 20.05.2020 new design
       /*  if($data['__subaction'] && $data['__subaction'] == 'refresh') //ISPC-2508
        {
        	$subaction = 'refresh';
        } */
        if($data['__subaction']) //ISPC-2508
        {
        	$data['action'] = $data['__subaction'];
        	$validatedForm = true;
        }
        else 
        {
        
        	$validatedForm = $this->_categoriesForms->$model->triggerValidateFunction($key, array($ipid, $data));
        }
		//--

        if ($validatedForm === true || $validatedForm instanceof Zend_Form){

            if ($validatedForm instanceof Zend_Form) {
            	/*
            	 * Zend_SubForms that have a mapValidateFunction(), will save only those elements
            	 */
                $data = $validatedForm->getValidValues($data, true);
            }
            //ISPC-2508 Carmen 20.05.2020 new design
            /* if($data['__subaction'] == 'refresh') //ISPC-2508
            {
            	$primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
            
            	$fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
            	
            	if ($entity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
            	
            		$entity->isremove = 1;
            		//$entity->remove_date = date('Y-m-d H:i:s', time());
            		$entity->remove_date = date('Y-m-d H:i:s', strtotime($data['remove_date'].' '.$data['remove_time'].":00"));
            		$entity->save();
            	}
            	
            	$data['id'] = '';
            	$data['option_id'] = $entity->option_id;
            	$data['option_localization'] = $entity->option_localization;
            	$current_data = date('Y-m-d H:i:s', time());
            	$data['option_date'] = date('Y-m-d', time());
            	$data['option_time'] = substr($current_data, 11, 5);
            	
            	unset($data['__subaction']);
            	unset($data['remove_date']);
            	unset($data['remove_time']);
            }*/
         	//--
         	//ISPC-2381 Carmen 19.01.2021
         	if($model == 'PatientAids')
         	{
	         	if($data['mode'] && $data['mode'] == 'add')
	         	{
	         		$out = $this->_categoriesForms->$model->triggerSaveFunction('create_form_patient_aids_for_add', array($ipid, $data));
	         	}
	         	else 
	         	{
	         		$out = $this->_categoriesForms->$model->triggerSaveFunction('create_form_patient_aids', array($ipid, $data));
	         	}
         	}
         	else
         	{
            	$out = $this->_categoriesForms->$model->triggerSaveFunction($key, array($ipid, $data));
         	}
         	//--

            if ($out !== false) {

                //ISPC-2432 Ancuta 21.01.2020
                if($model =="MePatientDevices"){
                    $result = array();
                    $result['entry'] =  $out->toArray();
                    $result['result'] = true;
                } else {
                    $result = true;
                }
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
     * Special case tables are as of 19.07.2018:
     * PatientMaster - do not delete !
     * PatientHospizverein, just a textarea
     * SpecialModel_Example
     *
     * @since 29.08.2018 ContactPersonMaster - when you delete one, you must also check PatientAcp
     *
     * @param array $post
     * @param string $ipid, p
     * @return boolean
     */
    public function deletePatientDetails($post = array() , $ipid = null)
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
    
                    case "PatientMaster" : //you cannot delete this !
                    case "PatientHospizverein" ://this is just a textarea, so you empty the text... so no delete button for you
                        
                        break;
                    
                    case "SpecialModel_Example" : //example
    
                        $this->_deleteSpecialModel($data, $ipid);
    
                        break;
                        
                    case "ContactPersonMaster" : //example
    
                        $this->_deleteContactPersonMaster($ipid, $data);
                        
                        break;
    
                    default:
                        
                        /*
                         * idea: you can create a deleteFn for each box, and map that when you create the form box.. then here you call the triggerDelete
                         * @update - this would come in handy since ContactPersonMaster special delete
                         */
                    	if($model == 'SapvVerordnung')
                    	{
                    		$this->_categoriesForms->$model->delete_form_patient_sapv_verordnung($ipid, $data);
                    	}
                    	elseif($model == 'MePatientDevices')
                    	{
                    	    $primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
                    	    
                    	    $fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
                    	    
                    	    if ($entity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
                    	        
                    	        $entity->isdelete =  '1';
                    	        $entity->save();
                    	    }
                    	}
                    	else 
                    	{
                    	if($model != 'PatientArtificialEntriesExits')
                    		{
                    	        //ISPC-2508 Carmen //Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
                    	    
		                        $primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
		                                                
		                        $fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
		    
		                        if ($entity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
		
		                            $entity->delete();
		                        }
                    		}
                    		else 
                    		{
                    	        //ISPC-2508 Carmen //Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
                    	        
                    			if($post[__subaction] == 'remove')
                    			{
                    				$primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
                    				
                    				$fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
                    				
                    				if ($entity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
                    				
                    					$entity->isremove = 1;
                    					$entity->remove_date = date('Y-m-d H:i:s', time());
                    					$entity->save();
                    				}
                    			}
                    			else 
                    			{
                    				$primaryKey = Doctrine_Core::getTable($model)->getIdentifier();
                    				
                    				$fn = "findOneBy" . Doctrine_Inflector::classify($primaryKey) . "AndIpid";
                    				
                    				if ($entity = Doctrine_Core::getTable($model)->{$fn}($data[$primaryKey], $ipid)) {
                    					
                    					$entity->delete();
                    					//TODO-3433 Carmen 21.09.2020
                    					$client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
	                    				foreach($client_options as $clopt)
	    								{
								    		$clopptdet[$clopt['id']] = $clopt;
								    	}
								    	
                    					$newEntity = [
                    							'option_id' => $entity->option_id,
                    							'option_date' => $entity->option_date,
                    							'option_localization' => $entity->option_localization,
                    					];
                    					$fieldsarr = [
                    							'option_id',
                    							'option_date',
                    							'option_localization'
                    					];
                    					
                    					foreach($fieldsarr as $fieldv)
                    					{
                    						if(($newEntity[$fieldv] != '' || $newEntity[$fieldv] != '0000-00-00 00:00:00') && $newEntity[$fieldv])
                    						{
                    							if($fieldv == 'option_date' || $fieldv == 'remove_date')
                    							{
                    								$newevalue[$fieldv] = date('d.m.Y H:i:s', strtotime($newEntity[$fieldv]));
                    							}
                    							elseif($fieldv == 'option_id')
                    							{
                    								$newevalue['option_name'] = $clopptdet[$newEntity[$fieldv]]['name'];
                    							}
                    							else
                    							{
                    								$newevalue[$fieldv] = $newEntity[$fieldv];
                    							}
                    							$history[] = [
                    									'ipid' => $ipid,
                    									'clientid' => $this->logininfo->clientid,
                    									'formid' => 'grow100',
                    									//'fieldname' => $this->getColumnMapping($kr) . '(' . date('d.m.Y', strtotime($verstart)) . ' - ' . date('d.m.Y', strtotime($verend)) . ')',
                    									'fieldname' =>  $fieldv == 'option_id' ? $this->translate('wrongentry'). '<br />' .$this->translate('artificial_option_name') : $this->translate('wrongentry'). '<br />' .$this->translate('artificial_'.$fieldv),
                    									'fieldvalue' => $fieldv == 'option_id' ? $newevalue['option_name'] : $newevalue[$fieldv] ,
                    							];
                    						}
                    					}
                    					
                    					if ( ! empty($history)) {
                    						$coll = new Doctrine_Collection("BoxHistory");
                    						$coll->fromArray($history);
                    						$coll->save();
                    					}
                    					//--
                    				}
                    			}
                    		}
                    	}
                        
                        break;
                }
            }
        }
    
    
        $this->_reConstruct();
    
        return true;
    }

    private function _deleteSpecialModel($data, $ipid) 
    {
        //example create your special delete function
    }
    
    private function _deleteContactPersonMaster($ipid = '', $data = []) 
    {
        $model = 'ContactPersonMaster';
            
        if (isset($this->_categoriesForms->$model)) {
            $this->_categoriesForms->$model->delete_form_contact_person($ipid, $data);
        }
        
        return true;
    }
    
    /**
     *
     * this fn was created JUST for the updatePatientVersorger == updatePatientDetails, to reload data
     * ! NOT to be used on unrelated fn
     */
    private function _reConstruct()
    {
        $patientmaster = new PatientMaster();
        
        $patientmaster->getMasterData(null, 1, null, $this->ipid); //Patient header data
        
        $patientmaster->getMasterData_extradata($this->ipid, $this->_init_onlyThisModel);
        
        if ( ! empty($this->_init_onlyThisModel)) {
            
            $this->_reConstruct_MotherForm($this->_init_onlyThisModel, $patientmaster);
            
            if ( ! empty($this->categories[$this->_init_onlyThisModel]['__linked_categories'])
                && is_array($this->categories[$this->_init_onlyThisModel]['__linked_categories']))
            {
                foreach ($this->categories[$this->_init_onlyThisModel]['__linked_categories'] as $linked_cat) 
                {                    
                    $this->_reConstruct_MotherForm($linked_cat, $patientmaster);
                }
            }    
        }
        
        
        $this->_patientMasterData = $patientmaster->get_patientMasterData();
        
        $this->_init_categories(__FUNCTION__);
    }
    
    /**
     * on save, we must create new instances for this mother forms, or set _patientMasterData for the available instance..
     * chosen to create new
     * 
     * @param string $modelMother
     */
    private function _reConstruct_MotherForm( $model, PatientMaster $patientmaster)
    {
        if ( ! empty($model) && isset($this->categories[$model])) {
            
            $this->_categoriesForms->$model = null;
            
            $patientmaster->getMasterData_extradata($this->ipid , $model);
               
            if ( ! empty($this->categories[$model]['__motherForm'])) {
                
                $this->_categoriesForms->{$this->categories[$model]['__motherForm']} = null;
                
                $patientmaster->getMasterData_extradata($this->ipid , $this->categories[$model]['__motherForm']);
            }
              
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
        $this->getView()->versorger_or_stammdaten = 'stammdaten';
    }
    
    //Maria:: Migration CISPC to ISPC 22.07.2020
    private function __box_PatientCaseStatus_View_Assign()
    {

        $ipid = $this->ipid;
        $patientdetails =  $this->_patientMasterData;
        $pdata = [];

        $this->getView()->pid = $this->enc_id;

        $this->getView()->isdischarged = $patientdetails['isdischarged'];
        $this->getView()->isstandby = $patientdetails['isstandby'];
        $this->getView()->isstandbydelete = $patientdetails['isstandbydelete'];


        //current status of patient
        if($patientdetails['isdischarged'] == "1"){
            $current_status = "discharged";
        } else{
            $current_status = "active";
        }

        /* ADD Clinic Cases*/
        $cc=new PatientCaseStatus();
        $pdata['clinic_cases']=$cc->get_list_patient_status($ipid);

        foreach ($pdata as $key => $val) {
            $this->getView()->{$key} = $val;

        }

    }


    /**
     * @see PatientController::patientdetailsAction()
     */
    
    private function __box_PatientReadmission_View_Assign()
    {
    
        $ipid = $this->ipid;
        $patientdetails =  $this->_patientMasterData;
        $pdata = [];
        
         $this->getView()->pid = $this->enc_id;
        
         $this->getView()->isdischarged = $patientdetails['isdischarged'];
         $this->getView()->isstandby = $patientdetails['isstandby'];
         $this->getView()->isstandbydelete = $patientdetails['isstandbydelete'];

        //         if($modules->checkModulePrivileges("147", $clientid))
        if ($patientdetails['ModulePrivileges'][147]) {
            $allow_history_changes = "1";
        } else {
            $allow_history_changes = "0";
        }
        $this->getView()->allow_history_changes = $allow_history_changes;
    
    
        if($allow_history_changes == "1"){
            $display_edit_history = $this->_clientForms[51]; // For Both Goe and LMU -- CHANGED to be the same every
        } else{
            $display_edit_history = "0";
        }
        
        $this->getView()->display_edit_history = $display_edit_history;
    
        $this->getView()->displayvvhistory = $this->_clientForms[36];
    

        /*			 * ******* Patient History ************ */
        $patientmaster = new PatientMaster();
        $patient_falls_master = $patientmaster->patient_falls($ipid);
        
        $pdata['first_admission_ever'] = $patient_falls_master['first_admission_ever'];
        $pdata['patient_falls'] = $patient_falls_master['falls'];
        
        
        //current status of patient
        if($patientdetails['isdischarged'] == "1"){
            $current_status = "discharged";
        } else{
            $current_status = "active";
        }
        
        
        $even = (count($date_array) % 2 == 0);
        $odd = (count($date_array) % 2 != 0);
        
        
        $not_continuu =  $patient_falls_master['not_continuu'];
        
        if($not_continuu != 0)
        {
            $allow_change = 0;
        } else{
            $allow_change = 1;
        }
        	
        $pdata['allow_change'] = $allow_change;
        
        
        
        $patient_history[$first_admission] = '1';
        if($lastdischarge)
        {
            $patient_history[$lastdischarge[0]['discharge_date']] = '2';
        }
        
        ksort($patient_history, SORT_STRING);
        // 			$pdata['patient_adm_history'] = $patient_history;
        $pdata['patient_adm_history'] = $patient_falls_master['falls'];
        	
        	
        
        /*
         * ISPC-2286
         * TODO-3837 Ancuta 25.05.2021
         */
        $this->getView()->display_HL7_PV1_19 = (int)$patientdetails['ModulePrivileges'][184];
        if ($patientdetails['ModulePrivileges'][184]) {
        
            $PV1_19 = [];
            $PV1_19_full = [];
            
            $visitNumbers = PatientVisitnumberTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
            
            foreach ($visitNumbers as $oneVisitnumber) {
                
                $fallFound = false;
                
                foreach($pdata['patient_falls'] as $kFall => $oneFall) {
                    
                    if ($oneFall['0'] == 'active') {
                        
                        if (strtotime($oneFall[1]) <= strtotime($oneVisitnumber['admit_date']) 
                            &&  (empty($oneFall[2]) || strtotime($oneFall[2]) >= strtotime($oneVisitnumber['admit_date'])) ) 
                        {
                            //visit in this period
                            $PV1_19[$kFall][$oneVisitnumber['visit_number'].'_'.$oneVisitnumber['id']] = date("d.m.Y H:i", strtotime($oneVisitnumber['admit_date']));
//                             $PV1_19[$kFall][$oneVisitnumber['visit_number'].'_'.$pk] = date("d.m.Y H:i", strtotime($oneVisitnumber['admit_date']));
                            $PV1_19_full[$kFall][$oneVisitnumber['id']] = $oneVisitnumber;
                            $PV1_19_full[$kFall][$oneVisitnumber['id']] ['date'] = date("d.m.Y H:i", strtotime($oneVisitnumber['admit_date']));
                            $fallFound = true;
//                             break 1;
                        }     
                    }
                }
                
                if ( ! $fallFound) {
                    //break 1 not reached...assign this visit to the previous fall..
                    foreach($pdata['patient_falls'] as $kFall => $oneFall) {
                        
                        if ($oneFall['0'] == 'active') {
                            
                            if (strtotime($oneFall[1]) >= strtotime($oneVisitnumber['admit_date'])) {
                                //add visit in this previous period
                                $PV1_19[$kFall][$oneVisitnumber['visit_number'].'_'.$oneVisitnumber['id']] = date("d.m.Y H:i", strtotime($oneVisitnumber['admit_date']));
//                                 $PV1_19[$kFall][$oneVisitnumber['visit_number'].'_'.$pk] = date("d.m.Y H:i", strtotime($oneVisitnumber['admit_date']));
                                $PV1_19_full[$kFall][$oneVisitnumber['id']] = $oneVisitnumber;
                                $PV1_19_full[$kFall][$oneVisitnumber['id']]['date'] = date("d.m.Y H:i", strtotime($oneVisitnumber['admit_date']));
//                                 break 1;
                            }
                        }
                    }
                }
            }
            
            $pdata["HL7_PV1_19"] = $PV1_19;
            $pdata["HL7_PV1_19_full"] = $PV1_19_full;
        }
//         dd($PV1_19,$PV1_19_full);
        
        //ISPC-2513 Lore 13.04.2020
        //#ISPC-2512PatientCharts
        if ($patientdetails['ModulePrivileges'][225]) {
            $display_edit_hist_details = "1";
        } else {
            $display_edit_hist_details = "0";
        }
        $this->getView()->display_edit_hist_details = $display_edit_hist_details;
        if($display_edit_hist_details = "1"){
            
            $readmission_dates = new PatientReadmission();
            $admisiondatesarray = $readmission_dates->get_patient_readmission_all($ipid);
            
            if(!empty($admisiondatesarray)) {
                $readm_ids_arr = array();
                $cnt = 0;
                foreach($admisiondatesarray as $ak=>$adates){
                    $cnt++;
                    if($adates['date_type'] == "1" ){
                        $readm_ids_arr[$cnt] = $adates['id'] ;
                    }

                }
            }
                
            $readm_detail = PatientReadmissionDetailsTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);

            $detailsdata = array();
            foreach($readm_ids_arr as $oneid => $valsid) {
                
                $detailsdata[$oneid][] = $valsid;
                
                foreach ($readm_detail as $onereadm_detail) {
                    
                    if ($onereadm_detail['readmission_id'] == $valsid) {
                        $detailsdata[$oneid][] = $onereadm_detail['admission_type'];
                        $detailsdata[$oneid][] = $onereadm_detail['admission_reason'];
                    }
                    
                }
                
            }
            //dd($detailsdata);
            $pdata["readmision_details"] = $detailsdata;
            
        }
        //.
        
        
        /*			 * ******* Vollversorgung History ************ */
        $vvhistory = new VollversorgungHistory();
        $historyvv = $vvhistory->getVollversorgungHistoryAll($ipid);
        
        //check if we have any data in history table
        if(count($historyvv) == "0" && $patientdetails['vollversorgung'] == "0")
        {
            $pdata['hideEditButton'] = "1";
        }
        if(count($historyvv) == "0" && $patientdetails['vollversorgung'] == "1")
        {
            $ins = new VollversorgungHistory();
            $ins->user_id = $logininfo->userid;
            $ins->ipid = $ipid;
            $ins->date = date("Y-m-d H:i:s", strtotime($patientdetails['vollversorgung_date']));
            $ins->date_type = "1";
            $ins->save();
        
            $historyvv[0]['date'] = $patientdetails['vollversorgung_date'];
            $historyvv[0]['date_type'] = $patientdetails['vollversorgung_date'];
            $pdata['hideEditButton'] = "0";
        }
        
        
        if($_REQUEST['vvdbg'])
        {
            print_r("historyvv\n");
            print_r($historyvv);
        }
        
        foreach($historyvv as $keyh => $valh)
        {
            if($valh['date_type'] == 1)
            {
                $startDatesHistory[] = $valh['date'];
                $start_dates_ids[] = $valh['id'];
                $has_prev_start[$keyh] = '1';
            }
            else if($valh['date_type'] == 2 && end($has_prev_start) == '1')
            {
                $endDatesHistory[] = $valh['date'];
                $end_dates_ids[] = $valh['id'];
                $has_prev_start[$keyh] = '0';
            }
        }
        
        $pdata['start_dates_ids'] = $start_dates_ids;
        $pdata['end_dates_ids'] = $end_dates_ids;
        
        if($startDatesHistory)
        {
            $pdata['startDatesHistory'] = $startDatesHistory;
        }
        else
        {
            $pdata['startDatesHistory'] = array();
        }
        if($endDatesHistory)
        {
            $pdata['endDatesHistory'] = $endDatesHistory;
        }
        else
        {
            $pdata['endDatesHistory'] = array();
        }
        
        
        foreach ($pdata as $key => $val) {
            $this->getView()->{$key} = $val;
            
        }
        
        
    }
  
  
    /**
     * @see PatientController::patientdetailsAction()
     * patientdetails_box_sapv_sgbv_11.phtml
     */
    private function __box_SapvVerordnung_SGBV_View_Assign($data = [])
    {
    
        $ipid = $this->ipid;
        $patientdetails =  $this->_patientMasterData;
        
        $this->getView()->pid = $this->enc_id;
        
        if ( ! empty($data['patient_sgbv_actions'] 
            || ! empty($data['patient_sgbv_actions_foc'])) 
            || ! empty($data['sgbv_status']))
        {
            $this->getView()->patient_sgbv = [$data["id"] => $data];
            $this->getView()->patient_sgbv_actions = [$data["id"] => $data['patient_sgbv_actions']];
            $this->getView()->patient_sgbv_actions_foc = [$data["id"] => $data['patient_sgbv_actions_foc']];
            $this->getView()->sgbv_status = $data['sgbv_status'];
        }
    }
    
    
    /**
     * @see PatientController::patientdetailsAction()
     * patientdetails_box_sapv_sgbv_11.phtml
     */
    private function __box_SapvVerordnung_PflegebesucheV_View_Assign($data = [])
    {
    
        $ipid = $this->ipid;
        $patientdetails =  $this->_patientMasterData;
    
//         dd($data);
        
//         approved_visit_type
//         approved_visit_type_history
//         only_default
        
        
        $this->getView()->pid = $this->enc_id;
        
        $this->getView()->approved_visit_type = $data['approved_visit_type'];
        $this->getView()->approved_visit_type_history =  $data['approved_visit_type_history'];
        $this->getView()->only_default =  $data['only_default'];
        $this->getView()->pavt_default =  $data['pavt_default'];
    
    }
   
    
    
    
    
    
    public function create_box_history($model = null)
    {   
        if ( ! empty($model) && isset($this->categories[$model])) {
            $formid = "grow" . $this->categories[$model]['extra_form_ID'];
        } else {
            $formid = null; //get all
        }
        
        
        $hist = new BoxHistory();
        
        $history = $hist->fetch_patient_box_history($this->ipid, $formid);
        
        if ( ! empty($history)) {
            $out = new Pms_Grid($history, 1, count($history), "patientboxhistory.html");
            $out = $out->renderGrid();
            return $out;
        } else {
            return false;
        }
        
    }
    
    // ISPC-2411
    private function __init_box_PatientSurveySettings ($model = 'PatientSurveySettings')
    {
    	/*
    	 * PatientSurveySettings
    	 * inlineEdit
    	 */
    	$createBox_formFn = 'create_form_PatientSurveySettings';
    
    	$this->_categoriesForms->$model  = (new Application_Form_PatientSurveySettings(array(
    			"elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
    			"_patientMasterData"    => $this->_patientMasterData,
    			"_block_name"           => $this->_block_name,
    			//"_clientForms"          => $this->_clientForms,
    	)));
    
    
    	$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}(['patient_emails_arr' => $this->_patientMasterData[$model]['patient_emails_arr']])->__toString();
    	$this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
    	$this->_categoriesForms->$model->mapSaveFunction($model, 'save_form_PatientSurveySettings');
   
    	if ( ! empty($this->_patientMasterData[$model]))
    	{
    		
    		$this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
    		$this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData[$model][0])->__toString();
    		// just need the first to be populated with editDialogHtml
    		
    	}
    	else
    	{
    		$this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
    		$this->_patientMasterData[$model][1]['editDialogHtml'] = '[';
    	}
    }
    
    /**
     * @author Ancuta
     * copy of fn __init_box_ContactPersonMaster
     * ISPC-2432 Ancuta 13.01.2020
     * @param string $model
     */
    private function __init_box_MePatientDevices ($model = 'MePatientDevices')
    {
        /*
         * MePatientDevices
         */
        $createBox_formFn = 'create_form_MePatientDevices';
        
        $this->_categoriesForms->$model  = (new Application_Form_MePatientDevices(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
        )));
        
        $this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
        
        
        if ( ! empty($this->_patientMasterData[$model])) {
            foreach ($this->_patientMasterData[$model] as $key => $row) {
                $this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
            }
        }   	
        /* else
        {
            $this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
            $this->_patientMasterData[$model][1]['editDialogHtml'] = '[';
        } */
        
    }

    private function __init_box_MePatientDevicesNotifications ($model = 'MePatientDevicesNotifications')
    {
        /*
         * MePatientDevicesNotifications
         * inlineEdit
         */
        $createBox_formFn = 'create_form_MePatientDevicesNotifications';
        
        $this->_categoriesForms->$model  = (new Application_Form_MePatientDevicesNotifications(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
        )));

        $this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
        $this->_categoriesForms->$model->mapSaveFunction($model, 'save_form_MePatientDevicesNotifications');
        
        
        
        if ( ! empty($this->_patientMasterData[$model]))
        {
            
            $this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
            $this->_patientMasterData[$model][0]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($this->_patientMasterData[$model][0])->__toString();
            // just need the first to be populated with editDialogHtml
            
        }
        else
        {
            $this->_patientMasterData[$model] = [$this->_patientMasterData[$model]];
            $this->_patientMasterData[$model][1]['editDialogHtml'] = '[';
        }
    }
    
    private function __init_box_PatientArtificialEntriesExits ($model = 'PatientArtificialEntriesExits')
    {
        /* ISPC-2508 Carmen 17.01.2020
         * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
    	 * PatientArtificialEntriesExits
    	 * Künstliche Zugänge - Ausgänge
    	 * use client options
    	 */
    	 
    	$createBox_formFn = 'create_form_artificial_entries_exits';
    
    	//get the options box from the client list
    	$client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($this->logininfo->clientid, Doctrine_Core::HYDRATE_ARRAY);
    
    	$this->_categoriesForms->$model = (new Application_Form_Stammdatenerweitert(array(
    			"elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
    			"_patientMasterData"    => $this->_patientMasterData,
    			"_block_name"           => $this->_block_name,
    			"_clientForms"          => $this->_clientForms,
    			"_client_options"		=> $client_options,
    	)));
    
    	$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
    	$this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
    	$this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
    	 
    	if ( ! empty($this->_patientMasterData[$model])) {
    
    		$entries = array();
    		$exits = array();
    		$maxrows = count($this->_patientMasterData[$model]);
    		$krows = $maxrows;
    		foreach($this->_patientMasterData[$model] as $kd => $vd)
    		{
    			if($vd['id'] >= $maxrows)
    			{
    				if($krows == $vd['id'])
    				{
    					$this->_patientMasterData[$model][$vd['id']]['editDialogHtml_extra'] = $this->_categoriesForms->$model->{$createBox_formFn}($vd)->__toString();
						//ISPC-2508 Carmen 19.05.2020 new design
						$vdrem = $vd;
						$vdrem['action'] = 'remove';
    					$this->_patientMasterData[$model][$vd['id']]['editDialogHtml_extra_remove'] = $this->_categoriesForms->$model->{$createBox_formFn}($vdrem)->__toString();
    					//--
    						
    				}
    				else
    				{
    					$difrows = $vd['id'] - $krows;
    					for($ir=1; $ir<=$difrows; $ir++)
    					{
    						$this->_patientMasterData[$model][$krows]['editDialogHtml_extra'] = null;
    						$krows++;
    					}
    					$this->_patientMasterData[$model][$vd['id']]['editDialogHtml_extra'] = $this->_categoriesForms->$model->{$createBox_formFn}($vd)->__toString();
    					//ISPC-2508 Carmen 19.05.2020 new design
						$vdrem = $vd;
						$vdrem['action'] = 'remove';
    					$this->_patientMasterData[$model][$vd['id']]['editDialogHtml_extra_remove'] = $this->_categoriesForms->$model->{$createBox_formFn}($vdrem)->__toString();
    					//--
    				}
    				$krows++;
    
    			}
    			else
    			{
    				$this->_patientMasterData[$model][$vd['id']]['editDialogHtml_extra'] = $this->_categoriesForms->$model->{$createBox_formFn}($vd)->__toString();
    				//ISPC-2508 Carmen 19.05.2020 new design
						$vdrem = $vd;
						$vdrem['action'] = 'remove';
    					$this->_patientMasterData[$model][$vd['id']]['editDialogHtml_extra_remove'] = $this->_categoriesForms->$model->{$createBox_formFn}($vdrem)->__toString();
    					//--
    			}
    			if($vd['option_type'] == 'entry')
    			{
    				$entries[] = $vd;
    			}
    			else
    			{
    				$exits[] = $vd;
    			}
    			 
    		}
    
    
    		if($entries)
    		{
    			$this->__box_Patient_Artificial_Entries_Exits($entries, $client_options);
    
    			/* foreach($entries as $key => $row)
    			 {
    			 $this->_patientMasterData[$model][$row['id']]['editDialogHtml_extra'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
    			 } */
    
    			$this->_patientMasterData[$model][0]['extract'] = $this->getView()->render("patientnew/patientdetails_box_artificial_entries.phtml");
    			$this->_patientMasterData[$model][0]['editDialogHtml'] = 'justNotEmpty';
    			 
    		}
    		else {
    			 
    			$this->_patientMasterData[$model][0]['extract'] = "";
    			$this->_patientMasterData[$model][0]['editDialogHtml'] = 'justNotEmpty';
    
    		}
    		 
    		if($exits)
    		{
    			$this->__box_Patient_Artificial_Entries_Exits($exits, $client_options);
    			/* foreach($exits as $key => $row)
    			 {
    			 $this->_patientMasterData[$model][$row['id']]['editDialogHtml_extra'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
    			 } */
    			 
    			$this->_patientMasterData[$model][1]['extract'] = $this->getView()->render("patientnew/patientdetails_box_artificial_exits.phtml");
    			$this->_patientMasterData[$model][1]['editDialogHtml'] = 'justNotEmpty';
    		}
    		else {
    
    				
    			$this->_patientMasterData[$model][1]['extract'] = "";
    			$this->_patientMasterData[$model][1]['editDialogHtml'] = 'justNotEmpty';
    
    		}
    		 
    	}
    	else
    	{
    		$this->getView()->patient_artificial_entries = array();
    		$this->_patientMasterData[$model][0]['extract'] = $this->getView()->render("patientnew/patientdetails_box_artificial_entries.phtml");
    		 
    		$this->getView()->patient_artificial_exits = array();
    		$this->_patientMasterData[$model][1]['extract'] = $this->getView()->render("patientnew/patientdetails_box_artificial_exits.phtml");
    
    	}
    
    
    }
    
    /**
     * ISPC 2508 Carmen 20.01.2020
     * @see PatientController::patientdetailsAction()
     * patientdetails_box_artificial_entries_exits.phtml
     */
    private function __box_Patient_Artificial_Entries_Exits($data = [], $client_options)
    {
    
    	$ipid = $this->ipid;
    	$patientdetails =  $this->_patientMasterData;
    	 
    	$this->getView()->pid = $this->enc_id;
    	$current_date = date('Y-m-d H:i:s');
    	 
    	if ( ! empty($data))
    	{
    		foreach($data as $kr => $vr)
    		{
    			//var_dump(Pms_CommonData::get_days_number_between($current_date, $vr['option_date'])); exit;
    			$patient_artificial_entries_exits[$kr]['option_name'] = $vr['option_name'];
    			//$patient_artificial_entries_exits[$kr]['option_type'] = $vr['option_type'];
    			$patient_artificial_entries_exits[$kr]['option_date'] = date('d.m.Y', strtotime($vr['option_date']));
    			$patient_artificial_entries_exits[$kr]['option_localization'] = $vr['option_localization'];
    			 
    			$optkey = array_search($vr['option_id'], array_column($client_options, 'id'));
    			if($optkey !== false)
    			{
    				$option_valability = $client_options[$optkey]['days_availability'];
    			}
    			
    			$option_age =  Pms_CommonData::get_days_number_between($current_date, $vr['option_date']);
    			
    			if($option_age < 0)
    			{
    				$option_age = 0;
    			}
    			 
    			if($option_age > 0)
    			{
	    			if($option_valability > 0 && $option_age > $option_valability)
	    			{
	    				$patient_artificial_entries_exits[$kr]['option_age'] = '<span><font style="color: red;">!</font>'.sprintf('%3s', $option_age).' '. $this->translate('days') . '</span>';
	    			}
	    			else
	    			{
	    				$patient_artificial_entries_exits[$kr]['option_age'] = '<span>'.sprintf('%3s', $option_age).' '. $this->translate('days') . '</span>';
	    			}
    			}
    			else 
    			{
    				$patient_artificial_entries_exits[$kr]['option_age'] = '<span>' . $this->translate('today new') . '</span>';
    			}
    			//ISPC-2508 Carmen 18.05.2020 new design
    			/* $patient_artificial_entries_exits[$kr]['actions'] = '<span class="info-button"><img title="'.$this->translate("edit").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" > </span>';
    			$patient_artificial_entries_exits[$kr]['actions'] .= '<span class="info-button"><img title="'.$this->translate("notneeded").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_remove.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" ><input type="hidden" name="action" value="remove" /> </span>';
    			$patient_artificial_entries_exits[$kr]['actions'] .= '<span class="info-button"><img title="'.$this->translate("refresh").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_renew.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" ><input type="hidden" name="action" value="refresh" /> </span>';
    			$patient_artificial_entries_exits[$kr]['actions'] .= '<span class="info-button"><img title="'.$this->translate("delete").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_delete.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" ><input type="hidden" name="action" value="delete" /></span>'; */
    			
    			$patient_artificial_entries_exits[$kr]['actions'] = '<span class="info-button"><img title="'.$this->translate("actions").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /><input type="hidden" class="hidden-json-data" value="'.$vr['id'].'" > </span>';
    			//--
    		}
    
    		if($data[0]['option_type'] == 'entry')
    		{
    			$this->getView()->patient_artificial_entries = $patient_artificial_entries_exits;
    		}
    		else
    		{
    			$this->getView()->patient_artificial_exits = $patient_artificial_entries_exits;
    		}
    		//var_dump($this->getView()->patient_artificial_entries); exit;
    	}
    }
    
    //ISPC-2774 Carmen 16.12.2020
    private function __init_box_PatientTherapy ($model = 'PatientTherapy')
    {
    	/*
    	 * PatientTherapy
    	 * Therapien == Therapy
    	 */
    	$createBox_formFn = 'create_form_therapy';
    
    	$this->_categoriesForms->$model  = (new Application_Form_PatientTherapy(array(
    			"elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
    			"_patientMasterData"    => $this->_patientMasterData,
    			"_block_name"           => $this->_block_name,
    			"_clientForms"          => $this->_clientForms,
    			"_categoriesForms_belongsTo"	=> $this->_categoriesForms_belongsTo,
    	)));
    	$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}()->__toString();
    	$this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
    	$this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
    	 
    	if ( ! empty($this->_patientMasterData[$model])) {
    		foreach ($this->_patientMasterData[$model] as $key => $row) {
    			$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
    		}
    	}
    }
    //--
    
    //ISPC-2381 Carmen 11.01.2021 Hilfsmittel Elsa
    private function __init_box_PatientAids ($model = 'PatientAids')
    {
    	/*
    	 * PatientAids
    	 * Hilfsmittel Elsa == Aids Elsa
    	 */
    	$createBox_formFn = 'create_form_patient_aids';
    	$createBox_formFn_add = 'create_form_patient_aids_for_add';
    
    	$this->_categoriesForms->$model  = (new Application_Form_PatientAids(array(
    			"elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
    			"_patientMasterData"    => $this->_patientMasterData,
    			"_block_name"           => $this->_block_name,
    			"_clientForms"          => $this->_clientForms,
    			"_categoriesForms_belongsTo"	=> $this->_categoriesForms_belongsTo,
    	)));
    	$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn_add}()->__toString();
    	$this->_categoriesForms->$model->mapValidateFunction($model, $this->_categoriesForms->$model->getValidateFunction($createBox_formFn));
    	$this->_categoriesForms->$model->mapSaveFunction($model, $this->_categoriesForms->$model->getSaveFunction($createBox_formFn));
    	
    	if ( ! empty($this->_patientMasterData[$model])) {
    		foreach ($this->_patientMasterData[$model] as $key => $row) {
    			$this->_patientMasterData[$model][$key]['editDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn}($row)->__toString();
    		}
    	}
    	else 
    	{
    		$this->categories[$model]['addnewDialogHtml'] = $this->_categoriesForms->$model->{$createBox_formFn_add}()->__toString();
    	}
    }
    //--
    
    
}


?>