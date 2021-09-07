<?php
use Dompdf\Dompdf;
use Dompdf\Options;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class PatientnewController extends Pms_Controller_Action {
	
	//protected $_patientMasterData = false;
	
	protected $logininfo = false;
	protected $clientid = false;
	protected $userid = false;
	protected $usertype = false; 
	protected $filepass = false;
	protected $dec_id = false;
	protected $enc_id = false;
	protected $ipid = false;
	protected $epid = false;
		
	public function init()
	{
	    
		/* Initialize action controller here */
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->clientid = $logininfo->clientid;
		$this->userid = $logininfo->userid;
		$this->usertype = $logininfo->usertype;
		$this->filepass = $logininfo->filepass;
		$this->logininfo = $logininfo;
		


		if( strlen($_GET['id']) > '0')	{
			$this->dec_id = Pms_Uuid::decrypt($_GET['id']);
			$this->enc_id = $_GET['id'];
		}
		elseif(strlen($_REQUEST['id'])>'0')
		{
			$this->dec_id = Pms_Uuid::decrypt($_REQUEST['id']);
			$this->enc_id = $_REQUEST['id'];
		}
		else
		{
			//redir to overview if patient encripted is is empty
			$this->_redirect(APP_BASE . "overview/overview");
			exit;
		}
		/* Initialize basic patient verification (patient belongs to this client?) */
		if(!Pms_CommonData::getPatientClient($this->dec_id, $this->clientid))
		{
			//deny acces to this patient as is does not belong to this client
			$this->_redirect(APP_BASE . "overview/overview");
			exit;
		}

		/* Initialize patient common used vars here */
		$this->ipid = Pms_CommonData::getIpid($this->dec_id);
		$this->epid = Pms_CommonData::getEpid($this->ipid);

		//ISPC-791 secrecy tracker
		$user_access = PatientPermissions::document_user_acces();

		//Check patient permissions on controller and action
		$patient_privileges = PatientPermissions::checkPermissionOnRun();

		if(!$patient_privileges)
		{
			$this->_redirect(APP_BASE . 'error/previlege');
			exit;
		}
		
		
		$this
		->setActionsWithPatientinfoAndTabmenus([
		    /*
		     * actions that have the patient header
		     */
		    "muster1a1",
		    "medication",
		    "medicationedit",
		    "reciperequest",
		    "hospizregisterv3",
		    "maintenancestage",
		    "versorger",
		    "patientdetails",
            "patientklau", //INFO-1554
		])
		->setActionsWithJsFile([
		    /*
		     * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
		     */
		    "muster1a1",
		    "medication",
		    "medicationedit",
		    "reciperequest",
		    "hospizregisterv3",
		    "maintenancestage",
		    "versorger",
		    "patientdetails",
		])
		->setActionsWithLayoutNew([
		    /*
		     * actions that will use layout_new.phtml
		     * Actions With Patientinfo And Tabmenus also use layout_new.phtml
		     */
		])
		;

	}


		public function generateppunAction()
		{
			$ppun = new PpunIpid();
			
			if($this->getRequest()->isPost() && !empty($_POST['generate']))
			{
				$ppun_id = $ppun->check_patient_ppun($this->ipid, $this->clientid);
				print_r($ppun_id);

			}
		}
		
		
		public function medicationAction()
		{
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);
		    
		    if($_REQUEST['modal'] =="1"){
		        $this->_helper->layout->setLayout('layout_ajax');
		        $this->_helper->viewRenderer("medicationshort");
		    }
		    //Changes for ISPC-1848 F
		    //moved in the init()
		    /* ================ PATIENT HEADER ======================= */
		    // 		    $patientmaster = new PatientMaster();
		    // 		    $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		    
		    /* ================ PATIENT TAB MENU ======================= */
		    // 		    $tm = new TabMenus();
		    // 		    $this->view->tabmenus = $tm->getMenuTabs();
		    
		    // ISPC-2346
		    $barcodereaderKey="";
		    if (Zend_Registry::isRegistered('barcodereader')) {
		        $barcodereader_cfg = Zend_Registry::get('barcodereader');
		        $barcodereaderKey = $barcodereader_cfg['datamatrix']['licenseKey'];
		    }
		    $this->view->barcodereaderKey = $barcodereaderKey;
		    $this->view->clientid_view = $clientid;
		    
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		    if(in_array($userid, $approval_users)){
		        $this->view->approval_rights = "1";
		    }
		    else
		    {
		        $this->view->approval_rights = "0";
		    }
		    
		    
		    /* ================ CHECK PRIVILEGES ======================= */
		    $modules = new Modules();
		    /* Medication acknowledge */
		    if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $acknowledge = "1";
		        // 		        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		    }
		    else
		    {
		        $acknowledge = "0";
		    }
		    $this->view->acknowledge = $acknowledge;
		    
		    $this->view->ModulePrivileges = $modules->get_client_modules($clientid); //all the active modules of this client
		    
		    if($modules->checkModulePrivileges("129", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $this->view->force_full_width = "1";
		    } else{
		        $this->view->force_full_width = "0";
		    }
		    
		    /* ================ MEDICATION :: CLIENT SETTINGS======================= */
		    /*
		     $clientdata_array = Pms_CommonData::getClientData($this->clientid);
		     $clientdata = $clientdata_array[0];
		     
		     $show_new_fields = "0";
		     if($clientdata['new_medication_fields'] == "1"){
		     $show_new_fields = "1";
		     }
		     */
		    $show_new_fields = 1; //ISPC-1848 F p.7
		    $this->view->show_new_fileds = $show_new_fields;
		    
		    /* ================ MEDICATION SHARE ======================= */
		    // check if patient is shared
		    //write verlauf of other patient
		    $patients_linked = new PatientsLinked();
		    $linked_patients = $patients_linked->get_related_patients($ipid);
		    
		    if ($linked_patients)
		    {
		        // 		        $linked_ipids[] = $ipid;
		        foreach ($linked_patients as $k_link => $v_link)
		        {
		            $linked_ipids[] = $v_link['target'];
		            $linked_ipids[] = $v_link['source'];
		        }
		    }
		    
		    
		    $shared_drg_ids = PatientDrugPlanShare::get_shared($linked_ipids);
		    
		    foreach($shared_drg_ids as $ipidp =>$drgs_arr){
		        if($ipidp != $ipid){
		            $shared_p_ipids[] = $ipidp;
		            foreach($drgs_arr as $k=>$drgid){
		                $all_drg_ids[] = $drgid;
		            }
		        }
		    }
		    
		    // for the connected patients get all shaed medis
		    if(!empty($linked_ipids) && !empty($all_drg_ids)){
		        $show_take_over_btn = 1;
		    }else{
		        $show_take_over_btn = 0;
		    }
		    $this->view->show_take_over = $show_take_over_btn ;
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    
		    /* IV BLOCK -  i.v. / s.c. */
		    $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
		    if(!$iv_medication_block){
		        $medication_blocks = array_diff($medication_blocks,array("isivmed"));
		    }
		    
		    /* TREATMENT CARE BLOCK -  Behandlungspflege*/
		    $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
		    if(!$treatmen_care_block){
		        $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
		    }
		    
		    /* NUTRITION  BLOCK - Ernahrung */
		    $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
		    if(!$nutrition_block){
		        $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
		    }
		    /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
		    $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
		    if(!$schmerzepumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
		    }
		    
		    /* Intervall Medis  BLOCK - Intervall Medis */
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		        $this->view->allow_normal_scheduled = "0" ;
		    } else {
		        $this->view->allow_normal_scheduled = "1";
		    }
		    
		    /* CRISIS BLOCK */
		    $crisis_block = $modules->checkModulePrivileges("144", $clientid);
		    if(!$crisis_block){
		        $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
		    }
		    
		    // ISPC-2176 16.04.2018 @Ancuta
		    /* INTUBETED/INFUSION MEDICATION  BLOCK */
		    $intubated_block = $modules->checkModulePrivileges("167", $clientid);
		    if(!$intubated_block){
		        $medication_blocks = array_diff($medication_blocks,array("isintubated"));
		    }
		    
		    
		    
		    $this->view->medication_blocks = $medication_blocks;
		    
		    
		    /* PHARMACY ORDER */
		    $pharmacyorder = $modules->checkModulePrivileges("50", $clientid);
		    if($pharmacyorder)
		    {
		        $this->view->pharmacyorder = '1';
		    }
		    //recipe request privileges
		    $this->view->reciperequest_privileges = $modules->checkModulePrivileges("150", $clientid);
		    
		    //TODO-2508 ISPC: Lore 19.08.2019
		    $pharmacyprivileges = $modules->checkModulePrivileges("75", $clientid);
		    if($pharmacyprivileges){
		        $this->view->pharmacyprivileges = '1';
		    }
		    
		    
		    /* ================ PATIENT TIME SCHEME ======================= */
		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
		    if($individual_medication_time_m){
		        $individual_medication_time = 1;
		    }else {
		        $individual_medication_time = 0;
		    }
		    $this->view->individual_medication_time = $individual_medication_time;
		    
		    //get get saved data
		    if($individual_medication_time == "0"){
		        $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,array("all"));
		    } else {
		        $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,$medication_blocks);
		    }
		    
		    $this->view->intervals = $client_time_scheme;
		    
		    //get time scchedule options
		    $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
		    $this->view->client_medication_options = $client_med_options;
		    
		    $time_blocks = array('all');
		    $NOT_timed_scheduled_medications = array();
		    foreach($client_med_options as $mtype=>$mtime_opt){
		        if($mtime_opt['time_schedule'] == "1"){
		            $time_blocks[]  = $mtype;
		            $timed_scheduled_medications[]  = $mtype;
		        } else {
		            $NOT_timed_scheduled_medications[]  = $mtype;
		        }
		    }
		    
		    if($individual_medication_time == "0"){
		        $timed_scheduled_medications = array("actual","isivmed"); // default
		        $time_blocks  = array("actual","isivmed"); // default
		    }
		    
		    foreach($timed_scheduled_medications  as $tk=>$tmed){
		        if(in_array($tmed,$NOT_timed_scheduled_medications)){
		            unset($timed_scheduled_medications[$tk]);
		        }
		    }
		    
		    
		    $this->view->timed_scheduled_medications = $timed_scheduled_medications;
		    $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);
		    
		    
		    if($patient_time_scheme['patient']){
		        foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
		        {
		            if($med_type != "new"){
		                $set = 0;
		                foreach($dos_data  as $int_id=>$int_data)
		                {
		                    if(in_array($med_type,$patient_time_scheme['patient']['new'])){
		                        
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
		                        
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		                        
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                    else
		                    {
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
		                        $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
		                        
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		                        
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                }
		            }
		        }
		    }
		    else
		    {
		        foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
		        {
		            
		            $inf=1;
		            $setc= 0;
		            foreach($mtimes as $int_id=>$int_data){
		                
		                $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
		                $interval_array['interval'][$med_type][$inf]['custom'] = '1';
		                $dosage_settings[$med_type][$setc] = $int_data;
		                $setc++;
		                $inf++;
		                
		                $dosage_intervals[$med_type][$int_data] = $int_data;
		            }
		        }
		    }
		    
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";
		    
		    /* ================ BEDARF SETS  ======================= */
		    
		    $bdf = new BedarfsmedicationMaster();
		    $this->view->bedarfsdrop = $bdf->getbedarfsmedicationDrop($this->clientid);
		    
		    /* ================ NEW MED SETS  ======================= */
		    
		    $msets = new MedicationsSetsList();
		    $new_medication_sets = array();
		    $new_medication_sets['iscrisis'] = $msets->getmedicationssetsDrop($this->clientid,"iscrisis");
		    $new_medication_sets['isbedarfs'] = $msets->getmedicationssetsDrop($this->clientid,"isbedarfs");
		    
		    $this->view->new_medication_sets = $new_medication_sets;
		    
		    $this->view->js_medsetsdrop = json_encode($medsdrop);
		    
		    /* ========================== POST ======================= */
		    
		    if($this->getRequest()->isPost())
		    {
		        
		        if(!empty($_POST['interval']))
		        {
		            $a_post = $_POST;
		            $a_post['ipid'] = $ipid;
		            
		            $drugplan_intervals_form = new Application_Form_PatientDrugPlanDosageIntervals();
		            
		            if($drugplan_intervals_form->validate($a_post,$time_blocks))
		            {
		                
		                $drugplan_intervals_form->insert_data($a_post);
		                
		                $this->_redirect(APP_BASE . "patientnew/medication?id=" . $_REQUEST['id']);
		            }
		            else
		            {
		                $this->view->errors = $drugplan_intervals_form->getErrorMessages();
		                
		                $this->view->interval_array['interval'] = $_POST['interval'];
		                $this->view->deleted_intervals_ids = $_POST['deleted_intervals_ids'];
		            }
		        }
		    }
		    
		    
		    // get patient alergies
		    $aller = new PatientDrugPlanAllergies();
		    $allergies = $aller->getPatientDrugPlanAllergies($decid);
		    
		    
		    if(!empty($allergies))
		    {
		        $patient_allergies = $allergies[0];
		        $this->view->allergies_comment = $allergies[0]['allergies_comment'];
		    }
		    
		    
		    
		    // get patient drugplan
		    $m_medication = new PatientDrugPlan();
		    if($_REQUEST['modal'] =="1"){
		        $medicarr = $m_medication->getMedicationPlanAll($decid,false,true);
		    } else{
		        $medicarr = $m_medication->getMedicationPlanAll($decid);
		    }
		    
		    
		    if($_REQUEST['dbg']=="1")
		    {
		        $medicarr_old = $m_medication->getPatientDrugPlan($decid,true);
		        print_r("\n old \n" );
		        print_r($medicarr_old );
		        
		        print_r("\n new \n" );
		        print_r($medicarr); exit;
		    }
		    
		    $medications_array = array();// TODO-1488 Medication II 12.04.2018
		    foreach($medicarr as $k=>$medication_data)
		    {
		        if($medication_data['isbedarfs'] == "1")
		        {
		            $medications_array['isbedarfs'][] = $medication_data;
		        }
		        elseif($medication_data['isivmed'] == "1")
		        {
		            $medications_array['isivmed'][] = $medication_data;
		        }
		        elseif($medication_data['isschmerzpumpe'] == "1")
		        {
		            $medications_array['isschmerzpumpe'][] = $medication_data;
		            $cocktail_ids[] = $medication_data['cocktailid'];
		        }
		        //ISPC-2833 Ancuta 04.03.2021 
		        elseif($medication_data['ispumpe'] == "1")
		        {
		            $medications_array['ispumpe'][] = $medication_data;
		            $cocktail_ids[] = $medication_data['pumpe_id'];
		        }
		        // --
		        elseif($medication_data['treatment_care'] == "1")
		        {
		            $medications_array['treatment_care'][] = $medication_data;
		            $treatmen_care_med_ids[] = $medication_data['medication_master_id'];
		        }
		        elseif($medication_data['isnutrition'] == "1")
		        {
		            $medications_array['isnutrition'][] = $medication_data;
		            $nutrition_med_ids[] = $medication_data['medication_master_id'];
		        }
		        elseif($medication_data['scheduled'] == "1")
		        {
		            $medications_array['scheduled'][] = $medication_data;
		        }
		        elseif($medication_data['iscrisis'] == "1")
		        {
		            $medications_array['iscrisis'][] = $medication_data;
		        }
		        elseif($medication_data['isintubated'] == "1") // ISPC-2176 16.04.2018 @Ancuta
		        {
		            $medications_array['isintubated'][] = $medication_data;
		        }
		        else
		        {
		            $medications_array['actual'][] = $medication_data;
		        }
		        
		        $med_ids[] = $medication_data['medication_master_id'];
		    }
		    
		    // get medication details
		    if(empty($med_ids))
		    {
		        $med_ids[] = "99999999";
		    }
		    $med = new Medication();
		    $master_medication_array = $med->master_medications_get($med_ids, false);
		    
		    
		    
		    
		    
		    
		    // get schmerzpumpe details
		    $cocktail_ids = array_unique($cocktail_ids);
		    
		    // 		    if(count($cocktail_ids) == 0)
		        // 		    {
		        // 		        $cocktail_ids[] = '999999';
		        // 		    }
		            
		        $cocktailsC = new PatientDrugPlanCocktails();
		        $cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
		        
		        if(count($cocktails) > 0)
		        {
		            $addnew = 0;
		        }
		        else
		        {
		            $addnew = 1;
		        }
		        $this->view->addnewlink = $addnew;
		        // get drugplan_alt for cocktail
		        
		        
		        
		        $alt_cocktail_details = PatientDrugPlanAltCocktails:: get_drug_cocktails_alt($ipid,$cocktail_ids);
		        $alt_cocktail_declined = PatientDrugPlanAltCocktails:: get_declined_drug_cocktails_alt($ipid,$cocktail_ids,false);
		        $alt_cocktail_declined_offline = PatientDrugPlanAltCocktails:: get_declined_drug_cocktails_alt_offline($ipid, $cocktail_ids, false);
		        
		        $alt_cocktail_details_offline =  $alt_cocktail_details['offline'];
		        $alt_cocktail_details =  $alt_cocktail_details['online'];
		        
		        //if changes are not approved - then no description
		        
		        
		        foreach($medications_array['isschmerzpumpe']  as $smpkey => $medicationsmp)
		        {
		            
		            if(!in_array($medicationsmp['cocktailid'],$alt_cocktail_declined)){
		                
		                if($medications_array['isschmerzpumpe'][($smpkey + 1)]['cocktailid'] != $medicationsmp['cocktailid'])
		                {
		                    $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = $cocktails[$medicationsmp['cocktailid']];
		                    
		                    if(!empty($alt_cocktail_details[$medicationsmp['cocktailid']]))
		                    {
		                        $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = $alt_cocktail_details[$medicationsmp['cocktailid']];
		                    }
		                    else
		                    {
		                        $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
		                    }
		                }
		                else
		                {
		                    $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = "0";
		                    $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
		                }
		            } else{
		                $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = "0";
		                $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
		            }
		            
		            //offline changes
		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt_offline'] = null;
		            if( ! empty($alt_cocktail_details_offline[$medicationsmp['cocktailid']]))
		            {
		                $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt_offline'] = $alt_cocktail_details_offline[$medicationsmp['cocktailid']];
		            }
		            
		            
		        }
		        
		        // get treatment care details
		        if(empty($treatmen_care_med_ids))
		        {
		            $treatmen_care_med_ids[] = "99999999";
		        }
		        $medtr = new MedicationTreatmentCare();
		        $medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);
		        
		        foreach($medarr_tr as $k_medarr_tr => $v_medarr_tr)
		        {
		            $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
		        }
		        
		        foreach($medications_array['treatment_care'] as $tr_key =>$tr_data){
		            $medications_array['treatment_care'][$tr_key]['medication'] = $medication_tr_array[$tr_data['medication_master_id']]['name'];
		        }
		        
		        
		        // get nutrition  details
		        if(empty($nutrition_med_ids))
		        {
		            $nutrition_med_ids[] = "99999999";
		        }
		        $mednutrition = new Nutrition();
		        $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
		        
		        foreach($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition)
		        {
		            $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
		        }
		        
		        
		        foreach($medications_array['isnutrition'] as $nutrition_key =>$tr_data){
		            $medications_array['isnutrition'][$nutrition_key]['medication'] = $medication_nutrition_array[$tr_data['medication_master_id']]['name'];
		        }
		        
		        // get patient new dosage structure
		        $drugplan_dosage = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
		        
		        
		        // get patient extra details
		        $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);
		        
		        //get patient weight
		        $all_vital_signs = FormBlockVitalSigns::get_patients_weight_chart($ipid);
		        $patient_weight = 0;
		        if( isset($all_vital_signs[$ipid]) ) {
		            
		            $weight_arr = end($all_vital_signs[$ipid]);
		            if ( (int)$weight_arr['weight'] > 0) {
		                $patient_weight = $weight_arr['weight'];
		            }
		            
		        }
		        
		        foreach($medications_array as $medication_type => $med_array)
		        {
		            foreach($med_array as $km=>$vm)
		            {
		                // #################################################################
		                // MEDICATION NAME
		                // #################################################################
		                $medications_array[$medication_type ][$km]['medication'] = $vm['medication'];
		                
		                if($vm['treatment_care'] != "1" && $vm['isnutrition'] != "1")
		                {
		                    if(strlen($vm['medicatioin']) >  0 )
		                    {
		                        $medications_array[$medication_type ][$km]['medication'] = $vm['medication'];
		                    }
		                    else
		                    {
		                        $medications_array[$medication_type ][$km]['medication'] = $master_medication_array[$vm['medication_master_id']];
		                    }
		                }
		                
		                
		                if($vm['medication_change'] != '0000-00-00 00:00:00')
		                {
		                    $medications_array[$medication_type ][$km]['medication_change'] = date('d.m.Y', strtotime($vm['medication_change']));
		                }
		                elseif($medication_change == '0000-00-00 00:00:00' && $vm['change_date'] != '0000-00-00 00:00:00')
		                {
		                    $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['change_date']));
		                }
		                else
		                {
		                    $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['create_date']));
		                }
		                
		                
		                $medications_array[$medication_type ][$km]['has_interval'] = $vm['has_interval'];
		                if($vm['administration_date'] != '0000-00-00 00:00:00')
		                {
		                    $medications_array[$medication_type ][$km]['scheduled_date'] =  strtotime($vm['administration_date']. ' + '.$vm['days_interval'].' days');
		                    if($medications_array[$medication_type ][$km]['scheduled_date']  <= strtotime( date("Y-m-d 00:00:00",time()) )){
		                        $medications_array[$medication_type ][$km]['allow_restart'] = "1";
		                    } else{
		                        $medications_array[$medication_type ][$km]['allow_restart'] = "0";
		                    }
		                    
		                }  else {
		                    // 		                $medications_array[$medication_type ][$km]['administration_date'] =  "";
		                }
		                
		                // #################################################################
		                // DOSAGE
		                // #################################################################
		                $medications_array[$medication_type ][$km]['old_dosage'] = $vm['dosage'];
		                // 	                if(!in_array($medication_type,array("actual","isivmed")))
		                if(!in_array($medication_type,$timed_scheduled_medications))
		                {
		                    $medications_array[$medication_type ][$km]['dosage']= $vm['dosage'];
		                }
		                else
		                {
		                    // first get new dosage
		                    if(!empty($drugplan_dosage[$vm['id']]))
		                    {
		                        $medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']];
		                        
		                    }
		                    else if(strlen($vm['dosage'])> 0 )
		                    {
		                        $old_dosage_arr[$vm['id']] = array();
		                        $medications_array[$medication_type ][$km]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
		                        
		                        if(strpos($vm['dosage'],"-")){
		                            $old_dosage_arr[$vm['id']] = explode("-",$vm['dosage']);
		                            
		                            if(count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$medication_type])){
		                                //  create array from old
		                                for($x = 0; $x < count($dosage_settings[$medication_type]); $x++)
		                                {
		                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$vm['id']][$x];
		                                }
		                            }
		                            else
		                            {
		                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
		                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
		                                for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
		                                {
		                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
		                                }
		                            }
		                        }
		                        else
		                        {
		                            $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
		                            $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
		                            
		                            for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
		                            {
		                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
		                            }
		                        }
		                    }
		                    else
		                    {
		                        $medications_array[$medication_type ][$km]['dosage'] =  "";
		                    }
		                }
		                // ############################################################
		                // Extra details  - drug / unit/ type / indication / importance
		                // ############################################################
		                
		                $medications_array[$medication_type ][$km]['drug'] =  $medication_extra[$vm['id']]['drug'];
		                $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit'];
		                $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type'];
		                $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name'];
		                $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color'];
		                $medications_array[$medication_type ][$km]['importance'] =  trim($medication_extra[$vm['id']]['importance']);
		                $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form'];
		                $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id'];
		                $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
		                $medications_array[$medication_type ][$km]['concentration_full'] =  $medication_extra[$vm['id']]['concentration'];
		                
		                // ISPC-2176, p6
		                $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging'];
		                $medications_array[$medication_type ][$km]['packaging_name'] =  trim($medication_extra[$vm['id']]['packaging_name']);
		                $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal'];
		                $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];
		                
		                // ISPC-2247
		                $medications_array[$medication_type ][$km]['escalation'] =  $medication_extra[$vm['id']]['escalation'];
		                // --
		                
		                if($medication_type == "isschmerzpumpe") {
		                    if($medication_extra[$vm['id']]['unit']) {
		                        $medications_array[$medication_type ][$km]['concentration_full'] .= " ".$medication_extra[$vm['id']]['unit'].'/ml';
		                    }
		                    
		                    $medications_array[$medication_type ][$km]['carriersolution_extra_text'] = "";
		                    
		                    
		                    
		                } else {
		                    
		                    if($medication_extra[$vm['id']]['unit']) {
		                        $medications_array[$medication_type ][$km]['concentration_full'] .= " ".$medication_extra[$vm['id']]['unit'].'/'.$medication_extra[$vm['id']]['dosage_form'] ;
		                    }
		                }
		                
		                
		                if($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration']) > 0  && $medication_extra[$vm['id']]['concentration'] != 0 ){
		                    if(!empty($drugplan_dosage[$vm['id']]) ){
		                        foreach($drugplan_dosage[$vm['id']] as $dtime =>$dvalue){
		                            $dosage_value = str_replace(",",".",$dvalue);
		                            $concentration = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
		                            
		                            $result = "";
		                            $result = $dosage_value / $concentration;
		                            
		                            if(!is_int($result))
		                            {
		                                $result = round($result, 4);
		                                $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = rtrim(rtrim(number_format( $result,3,",","."), "0"), ",")." ".$medication_extra[$vm['id']]['dosage_form'];
		                            }
		                            else
		                            {
		                                $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = $result." ".$medication_extra[$vm['id']]['dosage_form'];
		                            }
		                        }
		                    }
		                    else
		                    {
		                        if(strlen($medication_extra[$vm['id']]['concentration']) > 0 && strlen($medications_array[$medication_type ][$km]['dosage'])>0 ){
		                            
		                            $dosage_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage']);
		                            $concentration = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
		                            
		                            $result = "";
		                            $result = $dosage_value / $concentration;
		                            if(!is_int($result))
		                            {
		                                $result = round($result, 4);
		                                $medications_array[$medication_type ][$km]['dosage_concentration'] =  rtrim(rtrim(number_format($result,3,",","."), "0"), ",")." ".$medication_extra[$vm['id']]['dosage_form'];
		                            }
		                            else
		                            {
		                                $medications_array[$medication_type ][$km]['dosage_concentration'] =  $result." ".$medication_extra[$vm['id']]['dosage_form'];
		                            }
		                        }
		                    }
		                }
		                
		                
		                if($medication_type == "isschmerzpumpe")
		                {
		                    $dosage_value = "";
		                    $dosage_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage']);
		                    $medications_array[$medication_type ][$km]['dosage_24h'] = round($dosage_value * 24, 2) ;
		                    
		                    
		                    $medications_array[$medication_type ][$km]['dosage_mg_h_kg'] = '';
		                    if ( (int)$dosage_value > 0 && (int)$patient_weight > 0) {
		                        $medications_array[$medication_type ][$km]['dosage_mg_h_kg'] = " (". rtrim(rtrim(number_format(  $dosage_value / $patient_weight ,3,",","."), "0"), ",") . $medications_array[$medication_type ][$km]['unit'] . "/h/kg)";
		                    }
		                    
		                    //1848 p VI)
		                    
		                    if($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration'])> 0  && $medication_extra[$vm['id']]['concentration'] != 0 )
		                    {
		                        $dosage_24h_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage_24h']);
		                        $concentration_24h = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
		                        
		                        $result_24h = "";
		                        $result_24h = $dosage_24h_value / $concentration_24h;
		                        
		                        if(!is_int($result_24h))
		                        {
		                            $result_24h = round($result_24h, 4);
		                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  number_format($result_24h,3,",",".")." ".$medication_extra[$vm['id']]['dosage_form'];
		                        }
		                        else
		                        {
		                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  $result_24h." ".$medication_extra[$vm['id']]['dosage_form'];
		                        }
		                    }
		                }
		                
		                
		                // #################################################################
		                // MEDICATION comment
		                // #################################################################
		                $medications_array[$medication_type ][$km]['comments'] = nl2br($vm['comments']);
		                //    	                $medications_array[$medication_type ][$km]['comments'] = $vm['comments'];
		            }
		        }
		        
		        if(!empty($medications_array['isschmerzpumpe'])){
		            
		            foreach($medications_array['isschmerzpumpe'] as $drug_id_ke =>$med_details)
		            {
		                $alt_medications_array["isschmerzpumpe"][$med_details['cocktailid']][] =  $med_details;
		            }
		            
		            unset($medications_array['isschmerzpumpe']);
		            $medications_array['isschmerzpumpe'] = $alt_medications_array["isschmerzpumpe"];
		        }
		        
		        $allow_new_fields = array("actual","isbedarfs","iscrisis","isivmed","isnutrition");
		        
		        foreach($medication_blocks as $k=>$medt){
		            if(in_array($medt,array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isintubated"))){
		                $header[$medt][0] = "medication_change_full";
		                $header[$medt][1] = "medication";
		                $header[$medt][2] = "drug";
		                $index = 3;
		                
		                if($medt == "isintubated"){
		                    $header[$medt][$index] = "packaging";
		                    $index++;
		                }
		                
		                if($show_new_fields  == "1"){
		                    $header[$medt][$index] = "unit";
		                    $index++;
		                    $header[$medt][$index] = "type";
		                    $index++;
		                    $header[$medt][$index] = "dosage_form";
		                    $index++;
		                    $header[$medt][$index] = "concentration";
		                }
		                
		                if(in_array($medt,$timed_scheduled_medications)){
		                    foreach($dosage_settings[$medt] as $k=>$time){
		                        $index++;
		                        $header[$medt][$index] = $time;
		                    }
		                }
		                else
		                {
		                    $index++;
		                    $header[$medt][$index] = "dosage";
		                }
		                $index++;
		                $header[$medt][$index] = "indication";
		                if($medt == "isintubated"){
		                    $index++;
		                    $header[$medt][$index] = "kcal";
		                    $index++;
		                    $header[$medt][$index] = "volume";
		                }
		                $index++;
		                $header[$medt][$index] = "comments";
		                
		                
		                
		                $index++;
		                $header[$medt][$index] = "medication_change";
		                
		                if(in_array($medt,array("isbedarfs","iscrisis"))){
		                    $index++;
		                    $header[$medt][$index] = "escalation";
		                }
		                
		                if($medt == "actual" && $this->view->allow_normal_scheduled == "1"){
		                    $index++;
		                    $header[$medt][$index] = "medication_days_interval";
		                }
		                
		                $index++;
		                $header[$medt][$index] = "importance";
		            }
		            elseif($medt == "isschmerzpumpe")
		            {
		                $header[$medt][0] = "medication_change_full";
		                $header[$medt][1] = "medication";
		                $header[$medt][2] = "drug";
		                $index = 3;
		                if($show_new_fields  == "1"){
		                    $header[$medt][$index] = "unit";
		                    $index++;
		                    $header[$medt][$index] = "type";
		                    $index++;
		                    $header[$medt][$index] = "dosage_form";
		                    $index++;
		                    $header[$medt][$index] = "concentration";
		                }
		                
		                if(in_array($medt,$timed_scheduled_medications)){
		                    foreach($dosage_settings[$medt] as $k=>$time){
		                        $index++;
		                        $header[$medt][$index] = $time;
		                    }
		                }
		                else
		                {
		                    $index++;
		                    $header[$medt][$index] = "dosage";
		                }
		                $index++;
		                $header[$medt][$index] = "indication";
		                $index++;
		                $header[$medt][$index] = "comments";
		                $index++;
		                $header[$medt][$index] = "medication_change";
		                $index++;
		                $header[$medt][$index] = "importance";
		                
		            }
		            elseif($medt == "treatment_care")
		            {
		                $index = 0;
		                $header[$medt][$index] = "medication_change_full";
		                $index++;
		                $header[$medt][$index] = "medication";
		                $index++;
		                $header[$medt][$index] = "comments";
		                $index++;
		                $header[$medt][$index] = "medication_change";
		                $index++;
		                $header[$medt][$index] = "importance";
		                
		            }
		            elseif($medt == "scheduled")
		            {
		                $header[$medt][0] = "medication_change_full";
		                $header[$medt][1] = "medication";
		                $header[$medt][2] = "drug";
		                $header[$medt][3] = "dosage";
		                $header[$medt][4] = "indication";
		                $header[$medt][5] = "comments";
		                $header[$medt][6] = "days_interval";
		                $header[$medt][7] = "administration_date";
		                $header[$medt][8] = "medication_change";
		                $header[$medt][9] = "importance";
		            }
		            
		        }
		        
		        /* ================ MEDICATION :: USER SORTING ======================= */
		        $usort = new UserTableSorting();
		        // 		    $saved_data = $usort->user_saved_sorting($userid,false, false, false ,$ipid);
		        $saved_data = $usort->user_saved_sorting($userid, false, $ipid);
		        
		        foreach($saved_data as $k=>$sord){
		            if($sord['name'] == "order"){
		                
		                $med_type_sarr = explode("-",$sord['page']);
		                if($med_type_sarr[0] == "medication"){
		                    
		                    $page = $med_type_sarr[1];
		                    if($page){
		                        $order_value = unserialize($sord['value']);
		                        if($order_value[0][0] < count($header[$page])){
		                            $saved_order[$page]['col'] = $order_value[0][0];
		                            $saved_order[$page]['ord'] = $order_value[0][1];
		                        }
		                    }
		                }
		            }
		            
		        }
		        // 		    print_r($saved_order); exit;
		        
		        foreach($medication_blocks as $k=>$mt){
		            
		            if(empty($saved_order[$mt]['col']) || empty($saved_order[$mt]['ord'])){
		                // 		            $saved_order[$mt]['col'] =  array_search("medication",$header[$mt]);
		                if($mt == "scheduled"){
		                    $saved_order[$mt]['col'] = "2";
		                    
		                } else {
		                    $saved_order[$mt]['col'] = "1";
		                }
		                $saved_order[$mt]['ord'] = "asc";
		            }
		        }
		        
		        $this->view->saved_order= $saved_order;
		        $this->view->js_saved_order= json_encode($saved_order);
		        
		        
		        // 		    print_R($saved_order); exit;
		        // 		    print_R($header); exit;
		        // 		    print_R($dosage_settings); exit;
		        // 		    $dosage_settings[$medication_type]
		        
		        // 		    dd($medications_array);
		        if($_REQUEST['final'] == "1")
		        {
		            print_R($medications_array); exit;
		        }
		        // 		    $medications_array = Pms_CommonData::clear_pdf_data($medications_array);// ISPC-2120::  WHY?
		        $this->view->medication = $medications_array;
		}
		
		/*
		 * TODO-2643 Lore 12.11.2019
		 */
		public function medicationinfoAction()
		{
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);

		    if($_REQUEST['modal'] =="1"){
		    	$this->_helper->layout->setLayout('layout_ajax');
		    	$this->_helper->viewRenderer("medicationshort");
		    }

		    // ISPC-2346
		    $barcodereaderKey="";
		    if (Zend_Registry::isRegistered('barcodereader')) {
		        $barcodereader_cfg = Zend_Registry::get('barcodereader');
		        $barcodereaderKey = $barcodereader_cfg['datamatrix']['licenseKey'];
		    }
		    $this->view->barcodereaderKey = $barcodereaderKey;
		    
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		    if(in_array($userid, $approval_users)){
		        $this->view->approval_rights = "1";
		    }
		    else
		    {
		        $this->view->approval_rights = "0";
		    }
		    
		    /* ================ CHECK PRIVILEGES ======================= */
		    $modules = new Modules();
		    /* Medication acknowledge */
		    if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $acknowledge = "1";
// 		        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		    }
		    else
		    {
		        $acknowledge = "0";
		    }
		    $this->view->acknowledge = $acknowledge;
		   
		    $this->view->ModulePrivileges = $modules->get_client_modules($clientid); //all the active modules of this client
		    		    
		    if($modules->checkModulePrivileges("129", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $this->view->force_full_width = "1";
		    } else{
		        $this->view->force_full_width = "0";
		    }
		    

		    $show_new_fields = 1; //ISPC-1848 F p.7
		    $this->view->show_new_fileds = $show_new_fields;
		    
		    /* ================ MEDICATION SHARE ======================= */
		    // check if patient is shared
		    //write verlauf of other patient
		    $patients_linked = new PatientsLinked();
		    $linked_patients = $patients_linked->get_related_patients($ipid);
		    
		    if ($linked_patients)
		    {
// 		        $linked_ipids[] = $ipid;
		        foreach ($linked_patients as $k_link => $v_link)
		        {
		            $linked_ipids[] = $v_link['target'];
		            $linked_ipids[] = $v_link['source'];
		        }
		    }
            
            
            $shared_drg_ids = PatientDrugPlanShare::get_shared($linked_ipids);
            
            foreach($shared_drg_ids as $ipidp =>$drgs_arr){
                if($ipidp != $ipid){
                    $shared_p_ipids[] = $ipidp;
                    foreach($drgs_arr as $k=>$drgid){
                        $all_drg_ids[] = $drgid;
                    }
                }
            }
            
		    // for the connected patients get all shaed medis 
		    if(!empty($linked_ipids) && !empty($all_drg_ids)){
		        $show_take_over_btn = 1;
		    }else{
		        $show_take_over_btn = 0;
		    }
		    $this->view->show_take_over = $show_take_over_btn ; 
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    
		    /* IV BLOCK -  i.v. / s.c. */
		    $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
		    if(!$iv_medication_block){
		        $medication_blocks = array_diff($medication_blocks,array("isivmed"));
		    }
		    
		    /* TREATMENT CARE BLOCK -  Behandlungspflege*/
		    $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
		    if(!$treatmen_care_block){
		        $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
		    }
		    
		    /* NUTRITION  BLOCK - Ernahrung */
		    $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
		    if(!$nutrition_block){
		        $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
		    }
		    /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
		    $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
		    if(!$schmerzepumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
		    }
		    
		    /* Intervall Medis  BLOCK - Intervall Medis */
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		        $this->view->allow_normal_scheduled = "0" ;
		    } else {
		        $this->view->allow_normal_scheduled = "1";
		    }
		    
		    /* CRISIS BLOCK */
		    $crisis_block = $modules->checkModulePrivileges("144", $clientid);
		    if(!$crisis_block){
		        $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
		    }
		    
		    // ISPC-2176 16.04.2018 @Ancuta
			/* INTUBETED/INFUSION MEDICATION  BLOCK */
		    $intubated_block = $modules->checkModulePrivileges("167", $clientid);
		    if(!$intubated_block){
		        $medication_blocks = array_diff($medication_blocks,array("isintubated"));
		    }
		    
		    
		    
		    $this->view->medication_blocks = $medication_blocks;
		    

		    

		    /* ================ PATIENT TIME SCHEME ======================= */
		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
		    if($individual_medication_time_m){
		        $individual_medication_time = 1;
		    }else {
		        $individual_medication_time = 0;
		    }
		    $this->view->individual_medication_time = $individual_medication_time;
		    
		    //get get saved data
		    if($individual_medication_time == "0"){
		        $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,array("all"));
		    } else {
		        $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,$medication_blocks);
		    }
		    
		    $this->view->intervals = $client_time_scheme;
		    
    		    //get time scchedule options
    		    $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
    		    $this->view->client_medication_options = $client_med_options;
    		    
    		    $time_blocks = array('all');
    		    $NOT_timed_scheduled_medications = array();
    		    foreach($client_med_options as $mtype=>$mtime_opt){
    		        if($mtime_opt['time_schedule'] == "1"){
    		            $time_blocks[]  = $mtype;
    		            $timed_scheduled_medications[]  = $mtype;
    		        } else {
    		            $NOT_timed_scheduled_medications[]  = $mtype;
    		        }
    		    }
		    
    		if($individual_medication_time == "0"){
		        $timed_scheduled_medications = array("actual","isivmed"); // default
		         $time_blocks  = array("actual","isivmed"); // default
		    }
		    
		    foreach($timed_scheduled_medications  as $tk=>$tmed){
		        if(in_array($tmed,$NOT_timed_scheduled_medications)){
		            unset($timed_scheduled_medications[$tk]);
		        }
		    }
		    
		    
		    $this->view->timed_scheduled_medications = $timed_scheduled_medications;
                $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);
		    

		    if($patient_time_scheme['patient']){
		        foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
		        {
		            if($med_type != "new"){
		                $set = 0;
        		        foreach($dos_data  as $int_id=>$int_data)
        		        {
                            if(in_array($med_type,$patient_time_scheme['patient']['new'])){
                                
                                $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                                $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
                                
                                $dosage_settings[$med_type][$set] = $int_data;
                                $set++;
                                
                                $dosage_intervals[$med_type][$int_data] = $int_data;
                            }
                            else
                            {
            		            $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
            		            $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
            		            $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
            		            
            		            $dosage_settings[$med_type][$set] = $int_data;
            		            $set++;
            		    
            		            $dosage_intervals[$med_type][$int_data] = $int_data;
                            }
            		    }
		            }
		        }
		    }
		    else
		    {
		        foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
		        {
		            
		        $inf=1;
		        $setc= 0;
		            foreach($mtimes as $int_id=>$int_data){
		                
    		            $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
    		            $interval_array['interval'][$med_type][$inf]['custom'] = '1';
    		            $dosage_settings[$med_type][$setc] = $int_data;
    		            $setc++;
    		            $inf++;
    		    
    		            $dosage_intervals[$med_type][$int_data] = $int_data;
		            }
		        }
		    }
		    
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";

	 
   
		    
 
		    
            // get patient drugplan		    
		    $m_medication = new PatientDrugPlan();
		    if($_REQUEST['modal'] =="1"){
			    $medicarr = $m_medication->getMedicationPlanAll($decid,false,true);
		    } else{
			    $medicarr = $m_medication->getMedicationPlanAll($decid);
		    }
		    

		    $medications_array = array();// TODO-1488 Medication II 12.04.2018
		    foreach($medicarr as $k=>$medication_data)
		    {
		        if($medication_data['isbedarfs'] == "1")
		        {
                    $medications_array['isbedarfs'][] = $medication_data;    
		        }
		        elseif($medication_data['isivmed'] == "1")
		        {
    		        $medications_array['isivmed'][] = $medication_data;    
		        }
		        elseif($medication_data['isschmerzpumpe'] == "1")
		        {
    		        $medications_array['isschmerzpumpe'][] = $medication_data;
    		        $cocktail_ids[] = $medication_data['cocktailid'];     
		        }
		        elseif($medication_data['treatment_care'] == "1")
		        {
    		        $medications_array['treatment_care'][] = $medication_data;
    		        $treatmen_care_med_ids[] = $medication_data['medication_master_id'];     
		        }
		        elseif($medication_data['isnutrition'] == "1")
		        {
    		        $medications_array['isnutrition'][] = $medication_data;    
    		        $nutrition_med_ids[] = $medication_data['medication_master_id'];     
		        }
		        elseif($medication_data['scheduled'] == "1")
		        {
		            $medications_array['scheduled'][] = $medication_data;
		        }
		        elseif($medication_data['iscrisis'] == "1")
		        {
                    $medications_array['iscrisis'][] = $medication_data;    
		        }
		        elseif($medication_data['isintubated'] == "1") // ISPC-2176 16.04.2018 @Ancuta
		        {
                    $medications_array['isintubated'][] = $medication_data;    
		        }
		        else
		        {
    		        $medications_array['actual'][] = $medication_data;    
		        }
		        
		        $med_ids[] = $medication_data['medication_master_id'];     
		    }
		    
		    // get medication details
		    if(empty($med_ids))
		    {
		        $med_ids[] = "99999999";
		    }
		    $med = new Medication();
		    $master_medication_array = $med->master_medications_get($med_ids, false);
		    

		    
		    // get schmerzpumpe details
		    $cocktail_ids = array_unique($cocktail_ids);
		    		    
		    $cocktailsC = new PatientDrugPlanCocktails();
		    $cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
		    
		     if(count($cocktails) > 0)
			{
				$addnew = 0;
			}
			else
			{
				$addnew = 1;
			}
			$this->view->addnewlink = $addnew;
		    // get drugplan_alt for cocktail 
			
			
			
			$alt_cocktail_details = PatientDrugPlanAltCocktails:: get_drug_cocktails_alt($ipid,$cocktail_ids);
			$alt_cocktail_declined = PatientDrugPlanAltCocktails:: get_declined_drug_cocktails_alt($ipid,$cocktail_ids,false);
			$alt_cocktail_declined_offline = PatientDrugPlanAltCocktails:: get_declined_drug_cocktails_alt_offline($ipid, $cocktail_ids, false);

			$alt_cocktail_details_offline =  $alt_cocktail_details['offline'];
			$alt_cocktail_details =  $alt_cocktail_details['online'];
			
			//if changes are not approved - then no description
			
		    
		    foreach($medications_array['isschmerzpumpe']  as $smpkey => $medicationsmp)
		    {

		        if(!in_array($medicationsmp['cocktailid'],$alt_cocktail_declined)){
		            
    		        if($medications_array['isschmerzpumpe'][($smpkey + 1)]['cocktailid'] != $medicationsmp['cocktailid'])
    		        {
    		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = $cocktails[$medicationsmp['cocktailid']];
        		        
        		        if(!empty($alt_cocktail_details[$medicationsmp['cocktailid']]))
        		        {
        		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = $alt_cocktail_details[$medicationsmp['cocktailid']];
        		        } 
        		        else
        		        {
        		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
        		        }
    		        }
    		        else
    		        {
    		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = "0";
       		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
    		        }
		        } else{
		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = "0";
   		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
		        }
		        
		        //offline changes
		        $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt_offline'] = null;
		        if( ! empty($alt_cocktail_details_offline[$medicationsmp['cocktailid']]))
		        {
		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt_offline'] = $alt_cocktail_details_offline[$medicationsmp['cocktailid']];
		        } 
		        
		        
		    }
		    
		    // get treatment care details
		    if(empty($treatmen_care_med_ids))
		    {
		        $treatmen_care_med_ids[] = "99999999";
		    }
		    $medtr = new MedicationTreatmentCare();
		    $medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);
		    
		    foreach($medarr_tr as $k_medarr_tr => $v_medarr_tr)
		    {
		        $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
		    }
		    
		    foreach($medications_array['treatment_care'] as $tr_key =>$tr_data){
		        $medications_array['treatment_care'][$tr_key]['medication'] = $medication_tr_array[$tr_data['medication_master_id']]['name'];
		    }
		    
		    
		    // get nutrition  details
		    if(empty($nutrition_med_ids))
		    {
		        $nutrition_med_ids[] = "99999999";
		    }
		    $mednutrition = new Nutrition();
		    $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
		    
		    foreach($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition)
		    {
		        $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
		    }
		    
		    
		    foreach($medications_array['isnutrition'] as $nutrition_key =>$tr_data){
		        $medications_array['isnutrition'][$nutrition_key]['medication'] = $medication_nutrition_array[$tr_data['medication_master_id']]['name'];
		    }
		    
		    // get patient new dosage structure
		    $drugplan_dosage = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);

		    
		    // get patient extra details
		    $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);

			//get patient weight
		    $all_vital_signs = FormBlockVitalSigns::get_patients_weight_chart($ipid);
		    $patient_weight = 0; 
		    if( isset($all_vital_signs[$ipid]) ) {
		    	
		    	$weight_arr = end($all_vital_signs[$ipid]);
		    	if ( (int)$weight_arr['weight'] > 0) {
		    		$patient_weight = $weight_arr['weight'];
		    	}
		  
		    }
		    
		    foreach($medications_array as $medication_type => $med_array)
		    {
		        foreach($med_array as $km=>$vm)
		        {
		            // #################################################################
		            // MEDICATION NAME
		            // #################################################################		            
	                $medications_array[$medication_type ][$km]['medication'] = $vm['medication'];

		            if($vm['treatment_care'] != "1" && $vm['isnutrition'] != "1")
		            {
    		            if(strlen($vm['medicatioin']) >  0 )
    		            {
    		                $medications_array[$medication_type ][$km]['medication'] = $vm['medication'];
    		            } 
    		            else 
    		            {
    		                $medications_array[$medication_type ][$km]['medication'] = $master_medication_array[$vm['medication_master_id']];
    		            }
		            }

		            
		            if($vm['medication_change'] != '0000-00-00 00:00:00')
		            {
		                $medications_array[$medication_type ][$km]['medication_change'] = date('d.m.Y', strtotime($vm['medication_change']));
		            } 
		            elseif($medication_change == '0000-00-00 00:00:00' && $vm['change_date'] != '0000-00-00 00:00:00') 
		            {
		                $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['change_date']));
		            } 
		            else 
		            {
		                $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['create_date']));
		            }

		            
                    $medications_array[$medication_type ][$km]['has_interval'] = $vm['has_interval'];
		            if($vm['administration_date'] != '0000-00-00 00:00:00')
		            {
		                $medications_array[$medication_type ][$km]['scheduled_date'] =  strtotime($vm['administration_date']. ' + '.$vm['days_interval'].' days');
		                if($medications_array[$medication_type ][$km]['scheduled_date']  <= strtotime( date("Y-m-d 00:00:00",time()) )){
                            $medications_array[$medication_type ][$km]['allow_restart'] = "1";
		                } else{
                            $medications_array[$medication_type ][$km]['allow_restart'] = "0";
		                }
		                
		            }  else {
// 		                $medications_array[$medication_type ][$km]['administration_date'] =  "";
		            }
		            
		            // #################################################################
		            // DOSAGE
		            // #################################################################
	                $medications_array[$medication_type ][$km]['old_dosage'] = $vm['dosage']; 
// 	                if(!in_array($medication_type,array("actual","isivmed")))
	                if(!in_array($medication_type,$timed_scheduled_medications))
	                {
	                    $medications_array[$medication_type ][$km]['dosage']= $vm['dosage'];
	                }
	                else
    	            {
    		            // first get new dosage
    		            if(!empty($drugplan_dosage[$vm['id']]))
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']]; 
    		                
    		            }
    		            else if(strlen($vm['dosage'])> 0 )
    		            {
    		                $old_dosage_arr[$vm['id']] = array();
    		                $medications_array[$medication_type ][$km]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
    		                
    		                if(strpos($vm['dosage'],"-")){
            		            $old_dosage_arr[$vm['id']] = explode("-",$vm['dosage']);

        		                if(count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$medication_type])){
             		                //  create array from old
                		            for($x = 0; $x < count($dosage_settings[$medication_type]); $x++)
                		            {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$vm['id']][$x]; 
                                    }
            		            } 
            		            else
            		            {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage']; 
                                    for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
                                    {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                    }
            		            }
    		                } 
    		                else
    		                {
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
                                
                                for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
                                {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                } 
    		                }
    		            } 
    		            else
    		            {
        	                $medications_array[$medication_type ][$km]['dosage'] =  ""; 
    		            }
    		        }
		            // ############################################################
		            // Extra details  - drug / unit/ type / indication / importance
		            // ############################################################
		            
   	                $medications_array[$medication_type ][$km]['drug'] =  $medication_extra[$vm['id']]['drug']; 
   	                $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit']; 
   	                $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type']; 
   	                $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name']; 
   	                $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color']; 
   	                $medications_array[$medication_type ][$km]['importance'] =  trim($medication_extra[$vm['id']]['importance']); 
   	                $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form']; 
   	                $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id'];
     	           $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
     	           $medications_array[$medication_type ][$km]['concentration_full'] =  $medication_extra[$vm['id']]['concentration'];
   	                
     	           	// ISPC-2176, p6
   	                $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging']; 
   	                $medications_array[$medication_type ][$km]['packaging_name'] =  trim($medication_extra[$vm['id']]['packaging_name']); 
   	                $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal']; 
   	                $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];
     	           
   	                // ISPC-2247  
   	                $medications_array[$medication_type ][$km]['escalation'] =  $medication_extra[$vm['id']]['escalation']; 
                    // -- 
                    
   	                if($medication_type == "isschmerzpumpe") {
   	                	if($medication_extra[$vm['id']]['unit']) {
   	                		$medications_array[$medication_type ][$km]['concentration_full'] .= " ".$medication_extra[$vm['id']]['unit'].'/ml';
   	                	}
   	                	
   	                	$medications_array[$medication_type ][$km]['carriersolution_extra_text'] = "";
   	                	
   	                	
   	                	
   	                } else {
   	                	
   	                	if($medication_extra[$vm['id']]['unit']) {
       	                	$medications_array[$medication_type ][$km]['concentration_full'] .= " ".$medication_extra[$vm['id']]['unit'].'/'.$medication_extra[$vm['id']]['dosage_form'] ;
   	                	} 
   	                }
   	                
   	                 
   	                if($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration']) > 0  && $medication_extra[$vm['id']]['concentration'] != 0 ){
   	                    if(!empty($drugplan_dosage[$vm['id']]) ){
       	                    foreach($drugplan_dosage[$vm['id']] as $dtime =>$dvalue){
       	                        $dosage_value = str_replace(",",".",$dvalue);
       	                        $concentration = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
                                
       	                        $result = "";
   	                            $result = $dosage_value / $concentration;

   	                            if(!is_int($result))
   	                            {
                                    $result = round($result, 4);
       	                            $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = rtrim(rtrim(number_format( $result,3,",","."), "0"), ",")." ".$medication_extra[$vm['id']]['dosage_form'];
       	                        } 
       	                        else
       	                        {
           	                        $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = $result." ".$medication_extra[$vm['id']]['dosage_form'];
       	                        }
       	                    }
   	                    } 
   	                    else
   	                    {
   	                        if(strlen($medication_extra[$vm['id']]['concentration']) > 0 && strlen($medications_array[$medication_type ][$km]['dosage'])>0 ){
   	                            
   	                            $dosage_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage']);
   	                            $concentration = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
   	                            
   	                            $result = "";
   	                            $result = $dosage_value / $concentration;
   	                            if(!is_int($result))
   	                            {
       	                           $result = round($result, 4);
               	                   $medications_array[$medication_type ][$km]['dosage_concentration'] =  rtrim(rtrim(number_format($result,3,",","."), "0"), ",")." ".$medication_extra[$vm['id']]['dosage_form'];
   	                            } 
   	                            else
   	                            {
               	                   $medications_array[$medication_type ][$km]['dosage_concentration'] =  $result." ".$medication_extra[$vm['id']]['dosage_form'];
   	                            }
   	                        }
   	                    }
   	                }
   	                
   	                
   	                if($medication_type == "isschmerzpumpe") 
   	                {
   	                    $dosage_value = "";
   	                    $dosage_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage']);
   	                    $medications_array[$medication_type ][$km]['dosage_24h'] = round($dosage_value * 24, 2) ;
   	                
   	                    
   	                    $medications_array[$medication_type ][$km]['dosage_mg_h_kg'] = '';
   	                    if ( (int)$dosage_value > 0 && (int)$patient_weight > 0) { 
   	                    	$medications_array[$medication_type ][$km]['dosage_mg_h_kg'] = " (". rtrim(rtrim(number_format(  $dosage_value / $patient_weight ,3,",","."), "0"), ",") . $medications_array[$medication_type ][$km]['unit'] . "/h/kg)";
   	                    }
   	                    
   	                    //1848 p VI) 
   	                    
       	                if($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration'])> 0  && $medication_extra[$vm['id']]['concentration'] != 0 )
       	                {
       	                    $dosage_24h_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage_24h']);
       	                    $concentration_24h = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
       	                    
       	                    $result_24h = "";
       	                    $result_24h = $dosage_24h_value / $concentration_24h;
       	                    
       	                    if(!is_int($result_24h))
       	                    {
       	                        $result_24h = round($result_24h, 4);
       	                        $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  number_format($result_24h,3,",",".")." ".$medication_extra[$vm['id']]['dosage_form'];
       	                    }
       	                    else
       	                    {
       	                        $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  $result_24h." ".$medication_extra[$vm['id']]['dosage_form'];
       	                    }
       	                }
   	                }
   	                
   	                
   	                // #################################################################
   	                // MEDICATION comment
   	                // #################################################################
   	                $medications_array[$medication_type ][$km]['comments'] = nl2br($vm['comments']);
		        }
		    }
		    
		    if(!empty($medications_array['isschmerzpumpe'])){
		    
		        foreach($medications_array['isschmerzpumpe'] as $drug_id_ke =>$med_details)
		        {
		            $alt_medications_array["isschmerzpumpe"][$med_details['cocktailid']][] =  $med_details;
		        }
		    
		        unset($medications_array['isschmerzpumpe']);
		        $medications_array['isschmerzpumpe'] = $alt_medications_array["isschmerzpumpe"];
		    }
		    
		    $allow_new_fields = array("actual","isbedarfs","iscrisis","isivmed","isnutrition");
		    

		    /* ================ MEDICATION :: USER SORTING ======================= */
		    $usort = new UserTableSorting();
		    $saved_data = $usort->user_saved_sorting($userid, false, $ipid);
		     
		    foreach($saved_data as $k=>$sord){
		        if($sord['name'] == "order"){
		            
		        $med_type_sarr = explode("-",$sord['page']);
		        if($med_type_sarr[0] == "medication"){
    		            
    		        $page = $med_type_sarr[1];
    		        if($page){
        		        $order_value = unserialize($sord['value']);
        		          if($order_value[0][0] < count($header[$page])){
    	       	               $saved_order[$page]['col'] = $order_value[0][0];
        		              $saved_order[$page]['ord'] = $order_value[0][1];
    		              } 
        		        }
    		        }
		        }
		        
		    }
		    
		    foreach($medication_blocks as $k=>$mt){
		        
		        if(empty($saved_order[$mt]['col']) || empty($saved_order[$mt]['ord'])){
// 		            $saved_order[$mt]['col'] =  array_search("medication",$header[$mt]);
                    if($mt == "scheduled"){
	   	               $saved_order[$mt]['col'] = "2";
                            
                    } else {
    		            $saved_order[$mt]['col'] = "1";
                    }
		            $saved_order[$mt]['ord'] = "asc";
		        }
		    }
		    
		    $this->view->saved_order= $saved_order;
		    $this->view->js_saved_order= json_encode($saved_order);
		    
		    
		    if($_REQUEST['final'] == "1")
		    {
		      print_R($medications_array); exit;
		    }
            $this->view->medication = $medications_array;
		}
		
		
		public function medicationeditAction()
		{
			$logininfo = $this->logininfo;
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = ($this->dec_id !== false) ? $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ? $this->ipid : Pms_CommonData::getIpId($decid);

		    //moved in the init()
		    /* ================ PATIENT HEADER ======================= */
// 		    $patientmaster = new PatientMaster();
// 		    $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		    
		    /* ================ PATIENT TAB MENU ======================= */
// 		    $tm = new TabMenus();
// 		    $this->view->tabmenus = $tm->getMenuTabs();
		    
		    $approval_users = array();
		    $change_users = array();
		    
		    $modules = new Modules();
		    if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		        
		        $acknowledge = "1";
		    }
		    else
		    {
		        $acknowledge = "0";
		    }
		    $this->view->acknowledge = $acknowledge;

		    
		    if($modules->checkModulePrivileges("129", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $this->view->force_full_width = "1";
		    } else{
		        $this->view->force_full_width = "0";
		    }
		    
		    
		    // get patient drugplan
		    $m_medication = new PatientDrugPlan();
		    $medicarr = $m_medication->getMedicationPlanAll($decid);
		    
		    foreach($medicarr as $k=>$medication_data)
		    {
		        if($medication_data['isschmerzpumpe'] == "1")
		        {
		            $medications_array['isschmerzpumpe'][] = $medication_data;
		            $cocktail_ids[] = $medication_data['cocktailid'];
		        }
		    
		    }
		    // get schmerzpumpe details
		    $cocktail_ids = array_unique($cocktail_ids);
		    
		    if(count($cocktail_ids) == 0)
		    {
		        $cocktail_ids[] = '999999';
		    }
		    
		    $cocktailsC = new PatientDrugPlanCocktails();
		    $cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
		    
		    if($this->getRequest()->isPost())
		    {
		        
		        
		        if($acknowledge == "1"){
		            if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA'){
		                // do nothing
		            }
		            else
		            {
		                $this->_redirect(APP_BASE . "error/previlege");
		            }
		        }
		        
		        
		        
		        if($acknowledge =="1")
		        {
		            $_POST['skip_trigger'] = "1";
		        }
		        
       
		        /*===============================*/
				/* update dosages hours:minutes  Dosierung from top of the table*/
				/*===============================*/
		        $dosage_column_inputs_array = array(
		        		'interval' => $_POST['interval'],
		        		'deleted_intervals_ids' => $_POST['deleted_intervals_ids'],
		        		'ipid' => $ipid
		        );
// 		        die(print_r($dosage_column_inputs_array));
		        $drugplan_intervals_form = new Application_Form_PatientDrugPlanDosageIntervals();

		        if($drugplan_intervals_form->validate_v2($dosage_column_inputs_array))
		        {
		        	$drugplan_intervals_form->insert_data($dosage_column_inputs_array);
		        }
		        else
		        {
		        	//$this->retainValues($_POST);
		        	
		        	$this->view->errors = $drugplan_intervals_form->getErrorMessages();
		        	//retain values as a session for the ajax?.. and return
		        	return true;

		        }

		        
		        $patient_medication_form = new Application_Form_Medication();
		        $patient_medication_isnutrition_form = new Application_Form_Nutrition();
		        $patient_medication_tr_form = new Application_Form_MedicationTreatmentCare();
		        $med_form = new Application_Form_PatientDrugPlan();
		       // print_r($_POST); exit;
		        $a_post = $_POST;
		        
		        $a_post['ipid'] = $ipid;
                foreach($_POST['medication_block'] as $type => $med_values)
                {
                    if($type == "isschmerzpumpe")
                    {
                        foreach($med_values as $pumpe_number=>$sch_med_values)
                        {
                            // get initial data 
                            //find out edited/added medis
                            foreach($sch_med_values['medication'] as $k_meds => $v_meds)
                            {
                                
                                $cust_old = Doctrine::getTable('PatientDrugPlan')->find($sch_med_values['drid'][$k_meds]);
                                if($cust_old) {
                                    if($cust_old->dosage != $sch_med_values['dosage'][$k_meds] || $cust_old->medication_master_id != $sch_med_values['hidd_medication'][$k_meds] || $cust_old->verordnetvon != $sch_med_values['verordnetvon'][$k_meds]) {
                                        $sch_med_values['status'][$k_meds] = "edited";
                                    } else {
                                        $sch_med_values['status'][$k_meds] = "not_edited";
                                    }
                                } else {
                                    $sch_med_values['status'][$k_meds] = "new";
                                }
                                
                                
                                if(strlen($sch_med_values['cocktail']['id']) > 0 && !empty($cocktails[$sch_med_values['cocktail']['id']])){
                                    $cocktail_details = $cocktails[$sch_med_values['cocktail']['id']];
                                    
                                    if(
                                        $sch_med_values['cocktail']['description'] != $cocktail_details['description']
                                       || $sch_med_values['cocktail']['pumpe_medication_type'] != $cocktail_details['pumpe_medication_type']
                                       || $sch_med_values['cocktail']['flussrate'] != $cocktail_details['flussrate']
                                       || $sch_med_values['cocktail']['carrier_solution'] != $cocktail_details['carrier_solution']
                                       || $sch_med_values['cocktail']['bolus'] != $cocktail_details['bolus']
                                       || $sch_med_values['cocktail']['max_bolus'] != $cocktail_details['max_bolus']
                                       || $sch_med_values['cocktail']['sperrzeit'] != $cocktail_details['sperrzeit'])
                                    {
                                        $post_cocktails[$pumpe_number] = "edited";
                                    } else {
                                        $post_cocktails[$pumpe_number] = "not_edited";
                                    }
                                } else{
                                    $post_cocktails[$pumpe_number] = "new";
                                }
                                
                                
                                
                            }
                            
                            $sch_post_data = $sch_med_values;
                            foreach($sch_med_values['medication'] as $amedikey => $amedi)
                            {
                                if(strlen($amedi) > 0 && empty($sch_med_values['hidd_medication'][$amedikey]) && !empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey]))
                                {
                            
                                    $sch_post_data['newmids'][$amedikey] = $sch_med_values['drid'][$amedikey];
                                    $sch_post_data['newmedication'][$amedikey] = $amedi;
                                }
                            
                                if(strlen($amedi) > 0 && (!empty($sch_med_values['hidd_medication'][$amedikey]) && empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey])))
                                {
                                    $sch_post_data['newmids'][$amedikey] = $sch_med_values['hidd_medication'][$amedikey];
                                    $sch_post_data['newmedication'][$amedikey] = $amedi;
                                }
                            
                                if(strlen($amedi) > 0 && (empty($sch_med_values['hidd_medication'][$amedikey]) && empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey])))
                                {
                                    $sch_post_data['newmedication'][$amedikey] = $amedi;
                                }
                            }
                            
                            if(is_array($sch_post_data['newmedication']))
                            {
                                $dts = $patient_medication_form->InsertNewData($sch_post_data);
                                foreach($dts as $key => $dt)
                                {
                                    $sch_post_data['newhidd_medication'][$key] = $dt->id;
                                }
                            }
                            $sch_post_data[$type] =  "1";
                            $sch_post_data['ipid'] =  $ipid;
                            if($acknowledge =="1")
                            {
                                $sch_post_data['skip_trigger'] = "1";
                            }   
                            
                            $_POST['add_sets'] = "1";
                            // save data for each pumpe    
                            $med_form->update_schmerzpumpe_data($sch_post_data);
                        
                
                            //find out edited/added medis
                            $list[$pumpe_number] = 0;
                            foreach($sch_med_values['medication'] as $k_meds => $v_meds)
                            {
                                $cust = Doctrine::getTable('PatientDrugPlan')->find($sch_med_values['drid'][$k_meds]);
                                if($cust)
                                {
                                    if( $cust->dosage != $sch_med_values['dosage'][$k_meds] ||
                                        $cust->medication_master_id != $sch_med_values['hidd_medication'][$k_meds] ||
                                        $cust->verordnetvon != $sch_med_values['verordnetvon'][$k_meds]
                                        || $sch_med_values['status'][$k_meds] == "edited"
                                        || $sch_med_values['status'][$k_meds] == "new"
                                        || $post_cocktails[$pumpe_number]  == "edited"
                                        )
                                    {
                                        $list[$pumpe_number]++;
                                    }
                                     
                                } else {
                                    if($sch_med_values['status'][$k_meds] == "new"){
                                        $list[$pumpe_number]++;
                                    }
                                }
                            
                                if(!array_key_exists($k_meds, $sch_post_data['newmedication']) && $list[$pumpe_number] > 0) //new medis
                                {
                                    $meds[] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds] . "\n";
                                }
                            }
                            
                            if($list[$pumpe_number] > 0)
                            {   
                                $course_cocktail_entry ="";
                                $course_cocktail_entry .= "Kommentar: " . $sch_med_values['cocktail']['description'];
                                $course_cocktail_entry .= "\n".$this->view->translate('Applikationsweg').": " . $sch_med_values['cocktail']['pumpe_medication_type'];
                                $course_cocktail_entry .= "\n".$this->view->translate('Flussrate').": " . $sch_med_values['cocktail']['flussrate'];
                                $course_cocktail_entry .= "\n".$this->view->translate('medication_carrier_solution').": " . $sch_med_values['cocktail']['carrier_solution'];
                                
                                if($sch_med_values['cocktail']['pumpe_type'] == "pca") {
                                    $course_cocktail_entry .= "\n".$this->view->translate('Bolus').": " . $sch_med_values['cocktail']['bolus'];
                                    $course_cocktail_entry .= "\n".$this->view->translate('Max Bolus').": " . $sch_med_values['cocktail']['max_bolus'];
                                    $course_cocktail_entry .= "\n".$this->view->translate('Sperrzeit').": " . $sch_med_values['cocktail']['sperrzeit'] ;
                                }
                                
                                
                                if($acknowledge == "1"){
                                    if(in_array($userid,$approval_users)){
                                        $cust_course = new PatientCourse();
                                        $cust_course->ipid = $ipid;
                                        $cust_course->course_date = date("Y-m-d H:i:s", time());
                                        $cust_course->course_type = Pms_CommonData::aesEncrypt("Q");
                                        $cust_course->course_title = Pms_CommonData::aesEncrypt(addslashes(implode('', $meds).$course_cocktail_entry));
                                        $cust_course->user_id = $userid;
                                        $cust_course->save();
                                    }
                                } 
                                else
                                {
                                    $cust_course = new PatientCourse();
                                    $cust_course->ipid = $ipid;
                                    $cust_course->course_date = date("Y-m-d H:i:s", time());
                                    $cust_course->course_type = Pms_CommonData::aesEncrypt("Q");
                                    $cust_course->course_title = Pms_CommonData::aesEncrypt(addslashes(implode('', $meds).$course_cocktail_entry));
                                    $cust_course->user_id = $userid;
                                    $cust_course->save();
                                }
                            }
                        }
                    } 
                    else
                    {
                        $post_data = $med_values;
                        foreach($med_values['medication'] as $amedikey => $amedi)
                        {
                            if(strlen($amedi) > 0 && empty($med_values['hidd_medication'][$amedikey]) && !empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey]))
                            {
                            
                                $post_data['newmids'][$amedikey] = $med_values['drid'][$amedikey];
                                $post_data['newmedication'][$amedikey] = $amedi;
                            }
                            
                            if(strlen($amedi) > 0 && (!empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
                            {
                                $post_data['newmids'][$amedikey] = $med_values['hidd_medication'][$amedikey];
                                $post_data['newmedication'][$amedikey] = $amedi;
                            }
                            
                            if(strlen($amedi) > 0 && (empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
                            {
                                $post_data['newmedication'][$amedikey] = $amedi;
                            }
                        }
                        
                        if(is_array($post_data['newmedication']))
                        {
                            if($type == 'treatment_care')
                            {
                                $dts = $patient_medication_tr_form->InsertNewData($post_data);
                            }
                            elseif ($type == 'isnutrition')
                            {
                                $dts = $patient_medication_isnutrition_form->InsertNewData($post_data);
                            }
                            else
                            {
                                $dts = $patient_medication_form->InsertNewData($post_data);
                            }
                        
                            foreach($dts as $key => $dt)
                            {
                                $post_data['newhidd_medication'][$key] = $dt->id;
                            }
                        }
                        
                        $post_data[$type] =  "1";
                        $post_data['ipid'] =  $ipid;
                        if($acknowledge =="1" || $type == "deleted")
                        {
                            $post_data['skip_trigger'] = "1";
                            $_POST['skip_trigger'] = "1";
                        }
                        $_POST['add_sets'] = "1";
                        // save medication changes
                        if($type == "deleted")
                        {
//                             print_R($post_data); exit;
                            $med_form->update_multiple_data_deletedmeds($post_data);
                        } 
                        else
                        {
//                         	print_r($post_data);
                            $med_form->update_multiple_data($post_data);
                        }
                        
                    }
      
                } // END FOREACH
                
//                 exit;
                //ISPC-1848 F p.4
                if( !empty($_REQUEST['save_and_continue']) ){
                	$this->_redirect(APP_BASE . 'patientnew/medicationedit?flg=suc&id=' . $_GET['id']);
                } else {
	                $this->_redirect(APP_BASE . 'patientnew/medication?flg=suc&id=' . $_GET['id']);
                }
		    }
		}
		
		public function medicationeditblocksAction()
		{
			//ISPC-1848 F
			/*
			 * !!! point 3 and 7 are NOT done !
			 * 3) on receipt page we display the TAB preisvergleich and mark the discounted products in yellow or green.
			 * plz add this too on medication page.
			 * 7) we get rid of "extended view" and "normal view". we re arrange the fields in EDIT and VIEW.
			 * see screenshot (to come....)
			 * 
			 * !!! in the viewer change/add all datatables to have columns.data definition (only medication_actual for pc was done
			 * !!! in the pc_viewer there is function save_sorting_v2(this); applied only to medication_actual.... add to all tables  
			 * 
			 */
			
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = ($this->dec_id !== false) ? $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ? $this->ipid : Pms_CommonData::getIpId($decid);
            $this->view->clientid = $clientid;
            $this->view->userid = $userid;
		    
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    //TODO-3365 Carmen 21.08.2020
		    //get client settings for pharmaindex values got from mmi
		    $client_details = Client::getClientDataByid($clientid);
		    
		    if( ! empty($client_details)){
		    	if($client_details[0]['pharmaindex_settings'])
		    	{
		    		$this->view->js_pharmaindex_settings = json_encode($client_details[0]['pharmaindex_settings']);
		    	}
	    		else
	    		{
	    			$this->view->js_pharmaindex_settings = json_encode(array(
	    					'atc' => 'yes',
	    					'drug' => 'yes',
	    					'dosage_form' => 'no',
	    					'unit' => 'no',
	    					'takinghint' => 'no',
	    					'type' => 'no'
	    			));
	    		}
		    }		    
		    //--
		    
		    /* ================ CLIENT USER DETAILS ======================= */
		    $usr = new User();
		    $all_users = $usr->getUserByClientid($clientid, '1', true);
		    $this->view->all_users = $all_users;
		    
		    
		    
		    $pq = new User();
		    $pqarr = $pq->getUserByClientid($clientid);
		    
		    $comma = ",";
		    $userval = "'0'";
		    
		    foreach($pqarr as $key => $val)
		    {
		        $userval .= $comma . "'" . $val['id'] . "'";
		        $comma = ",";
		    }
		    
		    $usergroup = new Usergroup();
		    $groupid = $usergroup->getMastergroupGroups($clientid, array('4'));
		    
		    $this->view->verordnetvon = $userid;
		    
		    $usr = new User();
		    $users = $usr->getuserbyidsandGroupId($userval, $groupid, 1);
		    
		    $this->view->users = $users;
		    $this->view->jsusers = json_encode($users);
		    
		    
		    
		    /* ================ CHECK PRIVILEGES ======================= */
		    $modules = new Modules();
		    //ISPC-2554 pct.1 Carmen 03.04.2020
		    $clientModules = $modules->get_client_modules($this->logininfo->clientid);
		    //--
		    /* Medication acknowledge */
		    if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $acknowledge = "1";
		        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		    
		        if(in_array($userid,$approval_users)){
		            $this->view->approval_rights = "1";
		        }
		        else
		        {
		            $this->view->approval_rights = "0";
		        }
		        
		        if(in_array($userid,$change_users)){
		            $this->view->allow_changes = "1";
		        }else{
		            $this->view->allow_changes = "0";
		        }
		    }
		    else
		    {
		        $acknowledge = "0";
		    }
		    $this->view->acknowledge = $acknowledge;
		    
		    $this->view->ModulePrivileges = $modules->get_client_modules($clientid);
		    

		    
		    /* MMI functionality*/
		    if($modules->checkModulePrivileges("87", $clientid))
		    {
		        $this->view->show_mmi = "1";
		    }
		    else
		    {
		        $this->view->show_mmi = "0";
		    }
		    	
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    
		    /* IV BLOCK -  i.v. / s.c. */
		    $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
		    if(!$iv_medication_block){
		        $medication_blocks = array_diff($medication_blocks,array("isivmed"));
		    }
		    
		    /* TREATMENT CARE BLOCK -  Behandlungspflege*/
		    $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
		    if(!$treatmen_care_block){
		        $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
		    }
		    
		    /* NUTRITION  BLOCK - Ernahrung */
		    $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
		    if(!$nutrition_block){
		        $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
		    }
		    
		    /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
		    $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
		    if(!$schmerzepumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
		    }
		    /* SCHEDULED  BLOCK - Intervall Medis */
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		        $this->view->allow_normal_scheduled = "0";
		    } else {
		        $this->view->allow_normal_scheduled = "1";
		    }
		    
		    /* CRISIS  BLOCK  */
		    $crisis_block = $modules->checkModulePrivileges("144", $clientid);
		    if(!$crisis_block ){
		        $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
		    }

		    // ISPC-2176 16.04.2018 @Ancuta
		    /* INTUBETED/INFUSION MEDICATION  BLOCK */
		    $intubated_block = $modules->checkModulePrivileges("167", $clientid);
		    if(!$intubated_block){
		    	$medication_blocks = array_diff($medication_blocks,array("isintubated"));
		    }
		    
		    
		    
		    $this->view->medication_blocks = $medication_blocks;

		    // get details for packaging:: ISPC-2176 p6
		    $packaging_array = PatientDrugPlanExtra::intubated_packaging();
		    $this->view->packaging_array = $packaging_array;
		    
		     $this->view->js_packaging_array = json_encode($packaging_array);
		    
		    /* PHARMACY ORDER */
		    $pharmacyorder = $modules->checkModulePrivileges("50", $clientid);
		    if($pharmacyorder)
		    {
		        $this->view->pharmacyorder = '1';
		    }
		    
		    /* ================ MEDICATION :: CLIENT SETTINGS======================= */
		    /*
		    $clientdata_array = Pms_CommonData::getClientData($this->clientid);
		    $clientdata = $clientdata_array[0];
		    
		    $show_new_fields = "0";
		    if($clientdata['new_medication_fields'] == "1"){
		        $show_new_fields = "1";
		    }
		    */
		    $show_new_fields = 1; //ISPC-1848 F p.7
		    $this->view->show_new_fileds = $show_new_fields;  
		    
		    
		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $this->clientid);
		    if($individual_medication_time_m){
		        $individual_medication_time = 1;
		    }else {
		        $individual_medication_time = 0;
		    }
		    $this->view->individual_medication_time = $individual_medication_time;
		    
		    
	        
		    if($individual_medication_time == "1")
		    {
    		    //get time scchedule options
    		    $client_med_options = MedicationOptions::client_saved_medication_options($this->clientid);
    		    $this->view->client_medication_options = $client_med_options;

    		    $time_blocks = array('all');
    		    foreach($client_med_options as $mtype=>$mtime_opt){
    		        if($mtime_opt['time_schedule'] == "1"){
    		            $time_blocks[]  = $mtype;
    		            $timed_scheduled_medications[]  = $mtype;
    		        }
    		    }
		    } 
		    else
		    {
	           $timed_scheduled_medications = array("actual","isivmed"); // default
	           $time_blocks = array("actual","isivmed"); // default
		    }

		    $this->view->timed_scheduled_medications = $timed_scheduled_medications;
		    $this->view->js_timed_scheduled_medications = json_encode($timed_scheduled_medications);
		    
		    //163, 'MEDICATION :: Pumpe :: Live calculation'
		    //$this->view->client_pumpe_autocalculus = $modules->checkModulePrivileges("163", $clientid);
		    
		    /* ================ MEDICATION :: CLIENT EXTRA ======================= */
		    //UNIT
		    $medication_unit = MedicationUnit::client_medication_unit($clientid);
		    
		    foreach($medication_unit as $k=>$unit){
		        $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
		    }
		    $this->view->js_med_unit = json_encode($client_medication_extra['unit']);
		    $this->view->js_clientunit = json_encode($medication_unit); //ISPC-2554 Carmen 11.05.2020
		    
		    
		    //TYPE
		    $medication_types = MedicationType::client_medication_types($clientid,true);
		    foreach($medication_types as $k=>$type){
		        if($type['extra'] == 0 ){
    		        $client_medication_extra['type'][$type['id']] = $type['type'];
		        } 
    		    $client_medication_extra['type_custom'][$type['id']] = $type['type'];
		        
		    }
		    $this->view->js_med_type = json_encode($client_medication_extra['type']);
		    $this->view->js_med_type_custom = json_encode($client_medication_extra['type_custom']);
		    
		    
		    
		    //DOSAGE FORM
		    $medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid,true); // retrive all- incliding extra
		    $this->view->js_clientdosageform = json_encode($medication_dosage_forms);
		    //ISPC-2554 pct.1 Carmen 03.04.2020
		    $mmi_dosage_custom = array();
		    $medication_dosageform_mmi = array();
		    $cl_dosage_form = array();
		    //--
		    foreach($medication_dosage_forms as $k=>$df){
		    	if($df['extra'] == 0){
		    		$client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
		    	}
 		        //ISPC-2554 pct.1 Carmen 03.04.2020
 		        if($clientModules['87'])
 		        {
 		        	if($df['isfrommmi'] == 1)
 		        	{
 		        		$mmi_codes[$k] = $df['mmi_code'];
 		        		$mmi_dosage_custom[$df['id']] = $df['dosage_form'];
 		        	}
 		        	if($df['extra'] == 1  && $df['isfrommmi'] == 0)
 		        	{
 		        		$client_medication_extra['dosage_form_custom'][$df['id']] = $df['dosage_form'];
 		        	}
 		        }
 		        else
 		        {
 		        	if($df['isfrommmi'] == 1)
 		        	{
 		        		$mmi_dosage_custom[$df['id']] = $df['dosage_form'];
 		        	}
 		        	if($df['extra'] == 1  && $df['isfrommmi'] == 0)
 		        	{
 		        		$client_medication_extra['dosage_form_custom'][$df['id']] = $df['dosage_form'];
 		        	}
 		        }
 		        //--
 		        
		    }
		    
		    //ISPC-2554 pct.1 Carmen 03.04.2020
		    if(!$clientModules['87'])
		    {
		    	$cl_dosage_form = $client_medication_extra['dosage_form'];
		    	if(!$cl_dosage_form)
		    	{
		    		$cl_dosage_form = array();
		    	}
		    	$dosage_form_all = $cl_dosage_form + $mmi_dosage_custom;
		    }
		    else
		    {
		    	$dosage_form_all = $client_medication_extra['dosage_form'];;
		    }
		    
		    natcasesort($dosage_form_all);
		    $client_medication_extra['dosage_form'] = $dosage_form_all;
		    //--
		    
		    $this->view->js_med_dosage_form = json_encode($client_medication_extra['dosage_form']);
		    $this->view->js_med_dosage_form_custom = json_encode($client_medication_extra['dosage_form_custom']);
		    
		    //ISPC-2554 pct.1 Carmen 06.04.2020
		    if($clientModules['87'])
		    {
		    	$medication_dosagefrom_mmi = MedicationDosageformMmiTable::getInstance()->getfrommmi();
		    	//var_dump($medication_dosagefrom_mmi); exit;
		    	if(!empty($medication_dosagefrom_mmi))
		    	{
		    		foreach($medication_dosagefrom_mmi as $kr => $vr)
		    		{
		    			if(in_array($vr['dosageform_code'], $mmi_codes))
		    			{
		    				unset($medication_dosagefrom_mmi[$kr]);
		    			}
		    			else
		    			{
		    				$medication_dosageform_mmi['mmi_'.$vr['dosageform_code']] = $vr['dosageform_name'];
		    			}
		    		}		    		
		    	}
		    }
		    
	    	$medication_dosageform_mmi_all = $mmi_dosage_custom + $medication_dosageform_mmi;
	    	asort($medication_dosageform_mmi_all);
	    	$client_medication_extra['dosageform_mmi'] = $medication_dosageform_mmi_all;
	    	foreach($medication_dosageform_mmi_all as $kr => $vr)
	    	{
	    		$medication_dosageform_mmi_all_forjs[] = array($kr, $vr);
	    	}
	    	$this->view->js_med_dosageform_mmi = json_encode($medication_dosageform_mmi_all_forjs);		    
		    //--
		    
		    //INDICATIONS
		    $medication_indications = MedicationIndications::client_medication_indications($clientid);
		    
		    foreach($medication_indications as $k=>$indication){
		        $client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
		        $client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
		    }
		    
		    //ESKALATION  ( ISPC-2247)
		    $medication_escalation = PatientDrugPlanExtra::getMedicationEscalation();
		    foreach($medication_escalation as $esc_id=>$escalation_label){
		        $client_medication_extra['escalation'][$esc_id] = $escalation_label;
		    }
		    $this->view->js_med_escalation = json_encode($client_medication_extra['escalation']);
		    
		    
		    //TODO-1268
		    uasort($client_medication_extra['indication'], array(new Pms_Sorter('name'), "_strcmp"));
		    
		    //TODO-1268
// 		    $this->view->js_med_indication = json_encode($client_medication_extra['indication']);
		    $js_med_indication = array_combine(
		        array_map(function($key){ return ' '.$key; }, array_keys($client_medication_extra['indication'])),
		        $client_medication_extra['indication']
		    );
		    $this->view->js_med_indication = json_encode($js_med_indication);
		    
		    $this->view->client_medication_extra = $client_medication_extra;

		    /* ================ PATIENT HEALTH INSURANCE ======================= */
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);
		    
		    $this->view->kassen_no = $healthinsu_array[0]['kvk_no'];
		    
		    /* ================ PATIENT TIME SCHEME ======================= */
		   /*  $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
		    
		    if($patient_time_scheme['patient']){
		        $set = 0;
		        foreach($patient_time_scheme['patient']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$int_id]['time'] = $int_data;
		            $interval_array['interval'][$int_id]['custom'] = '0';
		            $interval_array['interval'][$int_id]['interval_id'] = $int_id;
		            
		            $dosage_settings[$set] = $int_data;
		            $set++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    else
		    {
		        $inf=1;
		        $setc= 0;
		        foreach($patient_time_scheme['client']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$inf]['time'] = $int_data;
		            $interval_array['interval'][$inf]['custom'] = '1';
		            $dosage_settings[$setc] = $int_data;
		            $setc++;
		            $inf++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    $this->view->js_dosage_intervals = json_encode($dosage_intervals);
		    
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0"; */

		    $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$this->clientid,$time_blocks);
		    
		    if($patient_time_scheme['patient']){
		        foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
		        {
		            if($med_type != "new"){
		                $set = 0;
		                foreach($dos_data  as $int_id=>$int_data)
		                {
		                    if(in_array($med_type,$patient_time_scheme['patient']['new'])){
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                    else
		                    {
		    
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
		                        $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                }
		            }
		        }
		    }
		    else
		    {
		        foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
		        {
		    
		            $inf=1;
		            $setc= 0;
		            foreach($mtimes as $int_id=>$int_data){
		    
		                $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
		                $interval_array['interval'][$med_type][$inf]['custom'] = '1';
		                $dosage_settings[$med_type][$setc] = $int_data;
		                $setc++;
		                $inf++;
		    
		                $dosage_intervals[$med_type][$int_data] = $int_data;
		            }
		        }
		    }

		    $this->view->js_dosage_intervals = json_encode($dosage_intervals);
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";
		    
		    
		    
            // get patient drugplan		    
		    $m_medication = new PatientDrugPlan();
		    $medicarr = $m_medication->getMedicationPlanAll($decid);
		    
		    $medications_array = array();// TODO-1488 Medication II 12.04.2018
		    foreach($medicarr as $k=>$medication_data)
		    {
		        if($medication_data['isbedarfs'] == "1")
		        {
                    $medications_array['isbedarfs'][] = $medication_data;    
		        }
		        elseif($medication_data['isivmed'] == "1")
		        {
    		        $medications_array['isivmed'][] = $medication_data;    
		        }
		        elseif($medication_data['isschmerzpumpe'] == "1")
		        {
    		        $medications_array['isschmerzpumpe'][] = $medication_data;    
    		        $cocktail_ids[] = $medication_data['cocktailid'];
		        }
		        //ISPC-2833 Ancuta 04.03.2021
		        elseif($medication_data['ispumpe'] == "1")
		        {
    		        $medications_array['ispumpe'][] = $medication_data;    
    		        $cocktail_ids[] = $medication_data['pumpe_id'];
		        }
		        //--
		        elseif($medication_data['treatment_care'] == "1")
		        {
    		        $medications_array['treatment_care'][] = $medication_data;
    		        $treatmen_care_med_ids[] = $medication_data['medication_master_id'];     
		        }
		        elseif($medication_data['isnutrition'] == "1")
		        {
    		        $medications_array['isnutrition'][] = $medication_data;    
    		        $nutrition_med_ids[] = $medication_data['medication_master_id'];     
		        }
		        elseif($medication_data['scheduled'] == "1")
		        {
		            $medications_array['scheduled'][] = $medication_data;
		        }
		        elseif($medication_data['iscrisis'] == "1")
		        {
                    $medications_array['iscrisis'][] = $medication_data;    
		        }
		        elseif($medication_data['isintubated'] == "1") // ISPC-2176 16.04.2018 @Ancuta
		        {
                    $medications_array['isintubated'][] = $medication_data;    
		        }
		        else
		        {
    		        $medications_array['actual'][] = $medication_data;    
		        }
		        $med_ids[] = $medication_data['medication_master_id'];     
		    }
		    
		    // get medication details 
		   	$med = new Medication();
// 		    $master_medication_array = $med->master_medications_get($med_ids, false); //= only names are fetched here
		    $master_medication_array = $med->getMedicationById($med_ids, true); //changed to this so I can fetch pzn, etc..
		    		    
		    // get schmerzpumpe details
		    $cocktail_ids = array_unique($cocktail_ids);
		    
		    if(count($cocktail_ids) == 0)
		    {
		        $cocktail_ids[] = '999999';
		    }
		    
		    $cocktailsC = new PatientDrugPlanCocktails();
		    $cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
		    
		    
		    if(count($cocktails) > 0)
		    {
		        $addnew = 0;
		    }
		    else
		    {
		        $addnew = 1;
		    }
		    $this->view->addnewlink = $addnew;
		    $this->view->cocktail_array = $cocktails;
		     
		    $alt_cocktail_details = PatientDrugPlanAltCocktails:: get_drug_cocktails_alt($ipid,$cocktail_ids);
		    $alt_cocktail_declined = PatientDrugPlanAltCocktails:: get_declined_drug_cocktails_alt($ipid,$cocktail_ids,false);
		    $alt_cocktail_declined_offline = PatientDrugPlanAltCocktails:: get_declined_drug_cocktails_alt_offline($ipid, $cocktail_ids, false);
		    
		    $alt_cocktail_details_offline =  $alt_cocktail_details['offline'];
		    $alt_cocktail_details =  $alt_cocktail_details['online'];
		    
		    
		    foreach($medications_array['isschmerzpumpe']  as $smpkey => $medicationsmp)
		    {
		        if(!in_array($medicationsmp['cocktailid'],$alt_cocktail_declined)){
    	            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = $cocktails[$medicationsmp['cocktailid']];
    	            
    	            if(!empty($alt_cocktail_details[$medicationsmp['cocktailid']]))
    	            {
    	                $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = $alt_cocktail_details[$medicationsmp['cocktailid']];
    	            }
    	            else
    	            {
    	                $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
    	            }
		        }
		        
		        //offline changes
		        $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt_offline'] = null;
		        if( ! empty($alt_cocktail_details_offline[$medicationsmp['cocktailid']]))
		        {
		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt_offline'] = $alt_cocktail_details_offline[$medicationsmp['cocktailid']];
		        }
		    }
		    
		    
		    // get treatment care details
		    if(empty($treatmen_care_med_ids))
		    {
		        $treatmen_care_med_ids[] = "99999999";
		    }
		    $medtr = new MedicationTreatmentCare();
		    $medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);
		    
		    foreach($medarr_tr as $k_medarr_tr => $v_medarr_tr)
		    {
		        $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
		    }
		    
		    foreach($medications_array['treatment_care'] as $tr_key =>$tr_data){
		        $medications_array['treatment_care'][$tr_key]['medication'] = $medication_tr_array[$tr_data['medication_master_id']]['name'];
		    }
		    
		    
		    // get nutrition  details
		    if(empty($nutrition_med_ids))
		    {
		        $nutrition_med_ids[] = "99999999";
		    }
		    $mednutrition = new Nutrition();
		    $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
		    
		    foreach($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition)
		    {
		        $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
		    }
		    
		    
		    foreach($medications_array['isnutrition'] as $nutrition_key =>$tr_data){
		        $medications_array['isnutrition'][$nutrition_key]['medication'] = $medication_nutrition_array[$tr_data['medication_master_id']]['name'];
		    }
		    
		    // get patient new dosage structure
		    $drugplan_dosage = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
            
		    // get patient extra details
		    $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);

		    //ISPC-2554 pct.3 Carmen 27.03.2020
		    $medatc = PatientDrugPlanAtcTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
		    foreach($medatc as $km => $vm)
		    {
		    	$medication_atc[$vm['drugplan_id']] = json_encode(array(
		    			'atc_code' => $vm['atc_code'],
		    			'atc_description' => $vm['atc_description'],
		    			'atc_groupe_code' => $vm['atc_groupe_code'],
		    			'atc_groupe_description' => $vm['atc_groupe_description'],
		    	));
		    }
		    //--
		    
		    foreach($medications_array as $medication_type => $med_array)
		    {

		        foreach($med_array as $km=>$vm)
		        {

		        	// #################################################################
		        	// MEDICATION NAME
		        	// #################################################################
		        	$medications_array[$medication_type ][$km]['medication'] = $vm['medication'];
		        	
		        	if($vm['treatment_care'] != "1" && $vm['isnutrition'] != "1")
		        	{
		        		if(strlen($vm['medicatioin']) >  0 )
		        		{
		        			$medications_array[$medication_type ][$km]['medication'] = $vm['medication'];
		        		}
		        		else
		        		{
		        			$medications_array[$medication_type ][$km]['medication'] = $master_medication_array[$vm['medication_master_id']]['name'];
		        		}
		        	}

		        	//append all the info from medication_master table 
		        	$medications_array[$medication_type ][$km]['MedicationMaster'] = $master_medication_array[$vm['medication_master_id']];
		        	//ISPC-2554 pct.3 Carmen 27.03.2020
		        	$medications_array[$medication_type ][$km]['MedicationMaster']['atc'] =  $medication_atc[$vm['id']];
		        	//--
		        	
		            if($vm['medication_change'] != '0000-00-00 00:00:00')
		            {
		                $medications_array[$medication_type ][$km]['medication_change'] = date('d.m.Y', strtotime($vm['medication_change']));
		                $medications_array[$medication_type ][$km]['replace_with'] = "none";
		                
		            } elseif($medication_change == '0000-00-00 00:00:00' && $vm['change_date'] != '0000-00-00 00:00:00') {
		                $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['change_date']));
		                $medications_array[$medication_type ][$km]['replace_with'] = "change";
		                
		            } else {
                        $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['create_date']));
                        $medications_array[$medication_type ][$km]['replace_with'] = "create";
		            }

		            // #################################################################
		            // DOSAGE
		            // #################################################################
	                $medications_array[$medication_type ][$km]['old_dosage'] = $vm['dosage']; 
		            
// 	                if(!in_array($medication_type,array("actual","isivmed")))
	                if(!in_array($medication_type,$timed_scheduled_medications))
	                {
	                    $medications_array[$medication_type ][$km]['dosage']= $vm['dosage'];
	                }
	                else
	                {
    		            // first get new dosage
    		            if(!empty($drugplan_dosage[$vm['id']]))
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']]; 
    		            }
    		            else if(strlen($vm['dosage'])> 0 )
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
    		                
    		                if(strpos($vm['dosage'],"-")){
            		            $old_dosage_arr[$vm['id']] = explode("-",$vm['dosage']);
    
        		                if(count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$medication_type])){
             		                //  create array from old
                		            for($x = 0; $x < count($dosage_settings[$medication_type]); $x++)
                		            {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$vm['id']][$x]; 
                                    }
            		            } 
            		            else
            		            {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage']; 
                                    for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
                                    {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                    }
            		            }
    		                } 
    		                else
    		                {
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
                                
                                for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
                                {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                } 
    		                }
    		            } 
    		            else
    		            {
        	                $medications_array[$medication_type ][$km]['dosage'] =  ""; 
    		            }
		            
                    }
		            // ############################################################
		            // Extra details  - drug / unit/ type / indication / importance
		            // ############################################################
		            
   	                $medications_array[$medication_type ][$km]['drug'] =  $medication_extra[$vm['id']]['drug']; 
   	                $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit']; 
   	                $medications_array[$medication_type ][$km]['unit_id'] =  $medication_extra[$vm['id']]['unit_id']; 
   	                $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type']; 
   	                $medications_array[$medication_type ][$km]['type_id'] =  $medication_extra[$vm['id']]['type_id']; 
   	                $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name']; 
   	                $medications_array[$medication_type ][$km]['indication_id'] =  $medication_extra[$vm['id']]['indication_id']; 
   	                $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color']; 
   	                $medications_array[$medication_type ][$km]['importance'] =  $medication_extra[$vm['id']]['importance'];
   	                $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form']; 
   	                $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id']; 
   	                $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
   	                
   	                $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging'];
   	                $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal'];
   	                $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];
   	                // ISPC-2247
   	                $medications_array[$medication_type ][$km]['escalation_label'] =  $medication_extra[$vm['id']]['escalation'];
   	                $medications_array[$medication_type ][$km]['escalation'] =  $medication_extra[$vm['id']]['escalation_id'];
   	                // -- 

   	                if($show_new_fields == "1" && !empty($drugplan_dosage[$vm['id']])  && strlen($medication_extra[$vm['id']]['concentration'])> 0  && $medication_extra[$vm['id']]['concentration'] != 0 ){
   	                    foreach($drugplan_dosage[$vm['id']] as $dtime =>$dvalue)
   	                    {
   	                        $dosage_value = str_replace(',','.',$dvalue); 
   	                        $concentration_value = str_replace(',','.',$medication_extra[$vm['id']]['concentration']); 
   	                        if(!empty($dvalue) && strlen($dvalue)> 0 )
   	                        {
   	                            
   	                            $result = $dosage_value / $concentration_value;
   	                            if(!is_int ($result ))
   	                            {
   	                                $result = round($result,4);
   	                                $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] =  rtrim(rtrim(number_format(  $result ,3,",","."), "0"), ",");
   	                            } 
   	                            else
   	                            {
   	                                $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] =  $result;
   	                            }
   	                        } 
   	                        else
   	                        {
   	                            $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = "";
   	                        }
   	                
   	                    }
   	                }
   	                
//    	                if($medication_type == "isschmerzpumpe"){
//    	                    $medications_array[$medication_type ][$km]['dosage_24h'] =  $medications_array[$medication_type ][$km]['dosage'] * 24;   	                    
//    	                }
   	                if($medication_type == "isschmerzpumpe")
   	                {
   	                
   	                    $dosage_value = "";
   	                    $dosage_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage']);
   	                    $medications_array[$medication_type ][$km]['dosage_24h'] = round($dosage_value * 24, 2);
   	                
   	                    $medications_array[$medication_type ][$km]['carriersolution_extra_text'] = '';
   	                    if ( (int)$medications_array[$medication_type ][$km]['smpdescription']['flussrate'] > 0 ) {
   	                    	
   	                    	$first_value =  $medications_array[$medication_type ][$km]['smpdescription']['carrier_solution'] / $medications_array[$medication_type ][$km]['smpdescription']['flussrate'];
   	                    	$first_value = rtrim(rtrim(number_format($first_value, 3, ",", "."), "0"), ",");
   	                    	
   	                    	// ISPC-1848 F ( details to follow...)
   	                    	$second_value = $medications_array[$medication_type ][$km]['smpdescription']['carrier_solution'] / $medications_array[$medication_type ][$km]['smpdescription']['flussrate'];;
   	                    	$second_value = rtrim(rtrim(number_format($second_value, 3, ",", "."), "0"), ",");
   	                    	
   	                    	$carriersolution_extra_text = $this->view->translate('carriersolution_extra_text');
   	                    	
   	                    	$medications_array[$medication_type ][$km]['carriersolution_extra_text'] = str_replace(array('%1%', '%2%'), array($first_value, $second_value), $carriersolution_extra_text);
   	                    	
   	                    }

   	                    if($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration'])> 0  && $medication_extra[$vm['id']]['concentration'] != 0 )
   	                    {
   	                        $dosage_24h_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage_24h']);
   	                        $concentration_24h = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
   	                
   	                        $result_24h = "";
   	                        $result_24h = $dosage_24h_value / $concentration_24h;
   	                
   	                        if(!is_int($result_24h))
   	                        {
   	                            $result_24h = round($result_24h, 4);
   	                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  number_format($result_24h,3,",",".")." ".$medication_extra[$vm['id']]['dosage_form'];
   	                        }
   	                        else
   	                        {
   	                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  $result_24h." ".$medication_extra[$vm['id']]['dosage_form'];
   	                        }
   	                    }
   	                }
   	                
   	                
		        }
		    }
		    
		    if(!empty($medications_array['isschmerzpumpe'])){
		        
                foreach($medications_array['isschmerzpumpe'] as $drug_id_ke =>$med_details)
                {
                    $alt_medications_array["isschmerzpumpe"][$med_details['cocktailid']][] =  $med_details; 
                }
                
                unset($medications_array['isschmerzpumpe']);
                $medications_array['isschmerzpumpe'] = $alt_medications_array["isschmerzpumpe"];
		    }
		    
		    
		    //ISPC-1848 F p.6
		    foreach($medication_blocks as $k=>$medt){
		    	
		    	$header[$medt] = array();
		    	
		    	if(in_array($medt, array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isintubated"))) {
		    		
		    		$header[$medt][0] = "last_update_hidden";
		    		$header[$medt][] = "medication";
		    		$header[$medt][] = "drug";
		    		
		    		if($show_new_fields  == "1"){
			    		$header[$medt][] = "concentration";
			    		$header[$medt][] = "type";
			    		$header[$medt][] = "unit";
			    		$header[$medt][] = "dosage_form";
		    		}
		    		
		    		if(in_array($medt,$timed_scheduled_medications)){
		    			foreach($dosage_settings[$medt] as $k=>$time){
		    				$header[$medt][] = $time;
		    			}
		    		}
		    		else
		    		{
		    			$header[$medt][] = "dosage";
		    		}
		    		
		    		$header[$medt][] = "indication";
		    		$header[$medt][] = "comments"; //comments
		    		$header[$medt][] = "medication_change";// Datum
		    		
		    		if($medt == "actual" && $this->view->allow_normal_scheduled == "1"){
		    			$header[$medt][] = "days_interval";
		    		}
		    		
		    		$header[$medt][] = "importance"; // sort
		    		
		    	
		    	} 
		    	elseif($medt == "isschmerzpumpe")
		    	{
		    		$header[$medt][0] = "medication_change_full";
		    		$header[$medt][] = "medication";
		    		$header[$medt][] = "drug";
		    		
		    		
		    		if($show_new_fields  == "1"){
		    			$header[$medt][] = "unit"; //unit_id
		    			$header[$medt][] = "dosage_form"; //dosage_form_id
		    			$header[$medt][] = "concentration"; //concentration
		    		}
		    
		    		if(in_array($medt,$timed_scheduled_medications)){
		    			foreach($dosage_settings[$medt] as $k=>$time){
		    				$header[$medt][] = $time;
		    			}
		    		}
		    		else
		    		{
		    			$header[$medt][] = "dosage"; //dosage
		    			$header[$medt][] = "dosage_24h"; //dosage_24h
		    			
		    		}
		    		
		    		$header[$medt][] = "indication"; //indication_id
		    		$header[$medt][] = "medication_change"; //medication_change
		    		$header[$medt][] = "importance"; //importance
		    			
		    	}
		    	elseif($medt == "treatment_care")
		    	{
		    		
		    		$header[$medt][] = "medication_change_full";
		    		$header[$medt][] = "medication";
		    		$header[$medt][] = "comments";
		    		$header[$medt][] = "medication_change";
		    		$header[$medt][] = "importance";
		    			
		    	}
		    	elseif($medt == "scheduled")
		    	{
		    		$header[$medt][0] = "medication_change_full";
		    		$header[$medt][2] = "medication";
		    		$header[$medt][3] = "drug";
		    		$header[$medt][4] = "dosage";
		    		$header[$medt][5] = "indication";
		    		$header[$medt][6] = "comments";
		    		$header[$medt][7] = "days_interval";
		    		$header[$medt][8] = "administration_date";
		    		$header[$medt][9] = "medication_change";
		    		$header[$medt][10] = "importance";
		    	}
		    }


		    
		     /* ================ MEDICATION :: USER SORTING ======================= */
		    $usort = new UserTableSorting();
		    $saved_data = $usort->user_saved_sorting($userid, false, $ipid);

		    foreach($saved_data as $k=>$sord) {
		    	
		        if($sord['name'] == "order") {
		            
		        	$med_type_sarr = explode("-",$sord['page']);
		        	$page = $med_type_sarr[1];
		        	
		        	if(!empty($page)) {

		        		$order_value = unserialize($sord['value']);
		        		
		        		if (!empty($order_value['extra_info']['columnDef_data'])) {
		        			$saved_order[$page]['col_data'] = $order_value['extra_info']['columnDef_data'];
		        			$saved_order[$page]['ord'] = $order_value[0][1];
		        		}
    		          	elseif($order_value[0][0] < count($header[$page])) {
    		          		
	       	            	$saved_order[$page]['col'] = $order_value[0][0];
    		           		$saved_order[$page]['ord'] = $order_value[0][1];
    		           		
    		          	} 
    		        }
		        }
		    }

		   // ############ APPLY SORTING ##############
		    foreach($medication_blocks as $med_type)
		    {

		    	
		        if($med_type != "isschmerzpumpe" )
		        {	

		        	if ( !empty($saved_order[$med_type]['col_data'])
		        			|| (!empty($saved_order[$med_type]) && !empty($header[$med_type] [$saved_order[$med_type] ['col']]))
		        		) 
		        	{
		        		
		        		$sort_by_column_name =  !empty($saved_order[$med_type]['col_data']) ? $saved_order[$med_type]['col_data'] : $header[$med_type] [$saved_order[$med_type] ['col']];

		        		if ($saved_order[$med_type] ['ord'] == 'desc'){
		        			$order = SORT_DESC|SORT_NATURAL|SORT_FLAG_CASE;
		        		} else {
		        			$order = SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE;
		        		}
		        		
		        		//$medications_array[$med_type]= $this->array_sort($medications_array[$med_type], $sort_by_column_name, $order);
		        		$this->array_sort_by_column($medications_array[$med_type], $sort_by_column_name, $order);
		        		
		        	}else{
		        		//$medications_array[$med_type]= $this->array_sort($medications_array[$med_type],"medication", SORT_ASC);
		        		$this->array_sort_by_column($medications_array[$med_type], 'medication', SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE);
		        	}

		        }
		        else //this is isschmerzpumpe
		        { 
		            foreach($medications_array[$med_type] as $sch_id => $sch_data){
		            	
		            	if ( !empty($saved_order[$med_type]['col_data'])
		            			|| (!empty($saved_order[$med_type]) && !empty($header[$med_type] [$saved_order[$med_type] ['col']]) )
		            		) 
		            	{
		            		
			            	if ($saved_order[$med_type] ['ord'] == 'desc'){
			        			$order = SORT_DESC|SORT_NATURAL|SORT_FLAG_CASE;
			        		} else {
			        			$order = SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE;
			        		}
		        		
		        			$sort_by_column_name =  !empty($saved_order[$med_type]['col_data']) ? $saved_order[$med_type]['col_data'] : $header[$med_type] [$saved_order[$med_type] ['col']];
			        		
		            		
// echo $sort_by_column_name;
		            		$this->array_sort_by_column($medications_array[$med_type][$sch_id], $sort_by_column_name, $order);
		                	//$medications_array[$med_type][$sch_id] = $this->array_sort($medications_array[$med_type][$sch_id],"medication", SORT_ASC);
		            	}
		            	else{
		            		
		            		$this->array_sort_by_column($medications_array[$med_type][$sch_id], 'medication', SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE);
		            		//$medications_array[$med_type][$sch_id] = $this->array_sort($medications_array[$med_type][$sch_id],"medication", SORT_ASC);
		            		
		            	}
		            }
		        }
		    }

		    $medications_array = Pms_CommonData::clear_pdf_data($medications_array);
		    $this->view->medication = $medications_array;
		}
		
		
		public function medicationdeletedAction()
		{
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = Pms_Uuid::decrypt($_GET['id']);
		    $ipid = Pms_CommonData::getIpId($decid);
            $this->view->clientid = $clientid;
            $this->view->userid = $userid;
		    
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    /* ================ CLIENT USER DETAILS ======================= */
		    $usr = new User();
		    $all_users = $usr->getUserByClientid($clientid, '1', true);
		    $this->view->all_users = $all_users;
		    
		    
		    
		    $pq = new User();
		    $pqarr = $pq->getUserByClientid($clientid);
		    
		    $comma = ",";
		    $userval = "'0'";
		    
		    foreach($pqarr as $key => $val)
		    {
		        $userval .= $comma . "'" . $val['id'] . "'";
		        $comma = ",";
		    }
		    
		    $usergroup = new Usergroup();
		    $groupid = $usergroup->getMastergroupGroups($clientid, array('4'));
		    
		    $this->view->verordnetvon = $userid;
		    
		    $usr = new User();
		    $users = $usr->getuserbyidsandGroupId($userval, $groupid, 1);
		    
		    $this->view->users = $users;
		    $this->view->jsusers = json_encode($users);
		    
		    
		    
		    /* ================ CHECK PRIVILEGES ======================= */
		    $modules = new Modules();
		    /* Medication acknowledge */
		    if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $acknowledge = "1";
		        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		    
		        if(in_array($userid,$approval_users)){
		            $this->view->approval_rights = "1";
		        }
		        else
		        {
		            $this->view->approval_rights = "0";
		        }
		    }
		    else
		    {
		        $acknowledge = "0";
		    }
		    $this->view->acknowledge = $acknowledge;

		    
		    /* MMI functionality*/
		    if($modules->checkModulePrivileges("87", $clientid))
		    {
		        $this->view->show_mmi = "1";
		    }
		    else
		    {
		        $this->view->show_mmi = "0";
		    }
		    	
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    
		    /* IV BLOCK -  i.v. / s.c. */
		    $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
		    if(!$iv_medication_block){
		        $medication_blocks = array_diff($medication_blocks,array("isivmed"));
		    }
		    
		    /* TREATMENT CARE BLOCK -  Behandlungspflege*/
		    $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
		    if(!$treatmen_care_block){
		        $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
		    }
		    
		    /* NUTRITION  BLOCK - Ernahrung */
		    $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
		    if(!$nutrition_block){
		        $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
		    }
		    /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
		    $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
		    if(!$schmerzepumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
		    }

		    /* Intervall Medis  BLOCK - Intervall Medis */
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		        $this->view->allow_normal_scheduled = "0" ;
		    } else {
		        $this->view->allow_normal_scheduled = "1";
		    }
		    
		    /* CRISIS  BLOCK  */
		    $crisis_block = $modules->checkModulePrivileges("144", $clientid);
		    if(!$crisis_block ){
		        $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
		    }
		    
		    // ISPC-2176 16.04.2018 @Ancuta
		    /* INTUBETED/INFUSION MEDICATION  BLOCK */
		    $intubated_block = $modules->checkModulePrivileges("167", $clientid);
		    if(!$intubated_block){
		    	$medication_blocks = array_diff($medication_blocks,array("isintubated"));
		    }
		    
		    
		    $this->view->medication_blocks = $medication_blocks;

		    /* PHARMACY ORDER */
		    $pharmacyorder = $modules->checkModulePrivileges("50", $clientid);
		    if($pharmacyorder)
		    {
		        $this->view->pharmacyorder = '1';
		    }
		    
		    /* ================ MEDICATION :: CLIENT SETTINGS======================= */
		    /*
		    $clientdata_array = Pms_CommonData::getClientData($this->clientid);
		    $clientdata = $clientdata_array[0];
		    
		    $show_new_fields = "0";
		    if($clientdata['new_medication_fields'] == "1"){
		        $show_new_fields = "1";
		    }
		    */
		    $show_new_fields = 1; //ISPC-1848 F p.7
		    $this->view->show_new_fileds = $show_new_fields;
		    /* ================ MEDICATION :: CLIENT EXTRA ======================= */
		    //UNIT
		    $medication_unit = MedicationUnit::client_medication_unit($clientid);
		    
		    foreach($medication_unit as $k=>$unit){
		        $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
		    }
		    $this->view->js_med_unit = json_encode($client_medication_extra['unit']);
		    
		    
		    //TYPE
		    $medication_types = MedicationType::client_medication_types($clientid);
		    foreach($medication_types as $k=>$type){
		        $client_medication_extra['type'][$type['id']] = $type['type'];
		    }
		    $this->view->js_med_type = json_encode($client_medication_extra['type']);
		    
		    //INDICATIONS
		    $medication_indications = MedicationIndications::client_medication_indications($clientid);
		    
		    foreach($medication_indications as $k=>$indication){
		        $client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
		        $client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
		    }
		    
		    $this->view->js_med_indication = json_encode($client_medication_extra['indication']);
		    
		    
		    $this->view->client_medication_extra = $client_medication_extra;

		    /* ================ PATIENT HEALTH INSURANCE ======================= */
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);
		    
		    $this->view->kassen_no = $healthinsu_array[0]['kvk_no'];
		    
		    /* ================ PATIENT TIME SCHEME ======================= */
		   /*  $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
		    
		    if($patient_time_scheme['patient']){
		        $set = 0;
		        foreach($patient_time_scheme['patient']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$int_id]['time'] = $int_data;
		            $interval_array['interval'][$int_id]['custom'] = '0';
		            $interval_array['interval'][$int_id]['interval_id'] = $int_id;
		            
		            $dosage_settings[$set] = $int_data;
		            $set++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    else
		    {
		        $inf=1;
		        $setc= 0;
		        foreach($patient_time_scheme['client']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$inf]['time'] = $int_data;
		            $interval_array['interval'][$inf]['custom'] = '1';
		            $dosage_settings[$setc] = $int_data;
		            $setc++;
		            $inf++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    $this->view->js_dosage_intervals = json_encode($dosage_intervals);
		    
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";
 */
	
		    
		    
		    
		    
		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
		    if($individual_medication_time_m){
		        $individual_medication_time = 1;
		    }else {
		        $individual_medication_time = 0;
		    }

		    //get time scchedule options
		    $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
		    $this->view->client_medication_options = $client_med_options;
		    
		    $time_blocks = array('all');
		    foreach($client_med_options as $mtype=>$mtime_opt){
		        if($mtime_opt['time_schedule'] == "1"){
		            $time_blocks[]  = $mtype;
		            $timed_scheduled_medications[]  = $mtype;
		        }
		    }
		    
		    if(empty($timed_scheduled_medications)){
		        $timed_scheduled_medications = array("actual","isivmed");
		    }
		    
		    
		    if($individual_medication_time == "0")
		    {
		        $timed_scheduled_medications = array("actual","isivmed");
		        $time_blocks = array("actual","isivmed");
		    }
		    
		    
		    $this->view->timed_scheduled_medications = $timed_scheduled_medications;
		    
		    $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);
		    
		    if($patient_time_scheme['patient']){
		        foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
		        {
		            if($med_type != "new"){
		                $set = 0;
		                foreach($dos_data  as $int_id=>$int_data)
		                {
		                    if(in_array($med_type,$patient_time_scheme['patient']['new'])){
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                    else
		                    {
		    
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
		                        $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                }
		            }
		        }
		    }
		    else
		    {
		        foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
		        {
		    
		            $inf=1;
		            $setc= 0;
		            foreach($mtimes as $int_id=>$int_data){
		    
		                $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
		                $interval_array['interval'][$med_type][$inf]['custom'] = '1';
		                $dosage_settings[$med_type][$setc] = $int_data;
		                $setc++;
		                $inf++;
		    
		                $dosage_intervals[$med_type][$int_data] = $int_data;
		            }
		        }
		    }
		    
		    
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";
		    
		    
		    
		    
		    
		    
		    
		    // get patient alergies
		    $aller = new PatientDrugPlanAllergies();
		    $allergies = $aller->getPatientDrugPlanAllergies($decid);
		    
		    if(!empty($allergies))
		    {
		        $patient_allergies = $allergies[0];
		        $this->view->allergies_comment = $allergies[0]['allergies_comment'];
		    }
		    
            // get patient drugplan		    
		    $m_medication = new PatientDrugPlan();
		    $medicarr = $m_medication->getDeletedMedication($decid,true,true);
		    
		    $medications_array = array();// TODO-1488 Medication II 12.04.2018
		    foreach($medicarr as $k=>$medication_data)
		    {
		        $medications_array['deleted'][] = $medication_data;
		        
		       if($medication_data['treatment_care'] == "1")
		        {
		            $treatmen_care_med_ids[] = $medication_data['medication_master_id'];
		        }
		        elseif($medication_data['isnutrition'] == "1")
		        {
		            $nutrition_med_ids[] = $medication_data['medication_master_id'];
		        } 
		        else
		        {
		            $med_ids[] = $medication_data['medication_master_id'];
		            
		        }
		    }
		    // get medication details
		    if(empty($med_ids))
		    {
		        $med_ids[] = "99999999";
		    }
		    $med = new Medication();
		    $master_medication_array = $med->master_medications_get($med_ids, false);
		  
		    
		    
		    
		    // get treatment care details
		    if(empty($treatmen_care_med_ids))
		    {
		        $treatmen_care_med_ids[] = "99999999";
		    }
		    $medtr = new MedicationTreatmentCare();
		    $medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);
		    
		    foreach($medarr_tr as $k_medarr_tr => $v_medarr_tr)
		    {
		        $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
		    }
		    
		    foreach($medications_array['treatment_care'] as $tr_key =>$tr_data){
		        $medications_array['treatment_care'][$tr_key]['medication'] = $medication_tr_array[$tr_data['medication_master_id']]['name'];
		    }
		    
		    
		    // get nutrition  details
		    if(empty($nutrition_med_ids))
		    {
		        $nutrition_med_ids[] = "99999999";
		    }
		    $mednutrition = new Nutrition();
		    $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
		    
		    foreach($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition)
		    {
		        $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
		    }
		    
		    
		    foreach($medications_array['isnutrition'] as $nutrition_key =>$ntr_data){
		        $medications_array['isnutrition'][$nutrition_key]['medication'] = $medication_nutrition_array[$ntr_data['medication_master_id']]['name'];
		    }
		    
		    // get patient new dosage structure
		    $drugplan_dosage = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
            
		    // get patient extra details
		    $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);


		    foreach($medications_array as $medication_type => $med_array)
		    {

		        foreach($med_array as $km=>$vm)
		        {
		            
		            if($vm['isbedarfs'] == "1")
		            {
		                $deleted_medication_type = "isbedarfs";  
		            }
		            elseif($vm['isivmed'] == "1")
		            {
		                $deleted_medication_type = "isivmed";  
		            }
		            elseif($vm['treatment_care'] == "1")
		            {
		                $deleted_medication_type = "treatment_care";  
		            }
		            elseif($vm['isnutrition'] == "1")
		            {
		                $deleted_medication_type = "isnutrition";  
		            }
		            elseif($vm['isschmerzpumpe'] == "1")
		            {
		                $deleted_medication_type = "isschmerzpumpe";  
		            }
		            elseif($vm['isintubated'] == "1")
		            {
		                $deleted_medication_type = "isintubated"; //  ISPC-2176
		            }
		            else
		            {
		                $deleted_medication_type = "actual";  
		            }
		            $medications_array[$medication_type ][$km]['medication_type'] = $deleted_medication_type;
		            
		            
		            // #################################################################
		            // name
		            // #################################################################
		            if($vm['treatment_care'] == "1"){
    		            $medications_array[$medication_type ][$km]['medication'] = $medication_tr_array[$vm['medication_master_id']]['name'];
		            }
		            elseif($vm['isnutrition'] == "1"){
    		            $medications_array[$medication_type ][$km]['medication'] = $medication_nutrition_array[$vm['medication_master_id']]['name'];
		            }else{
    		            $medications_array[$medication_type ][$km]['medication'] = $master_medication_array[$vm['medication_master_id']];
		            }
		            
		            
		            
		            if($vm['medication_change'] != '0000-00-00 00:00:00')
		            {
		                $medications_array[$medication_type ][$km]['medication_change'] = date('d.m.Y', strtotime($vm['medication_change']));
		                $medications_array[$medication_type ][$km]['replace_with'] = "none";
		                
		            } elseif($medication_change == '0000-00-00 00:00:00' && $vm['change_date'] != '0000-00-00 00:00:00') {
		                $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['change_date']));
		                $medications_array[$medication_type ][$km]['replace_with'] = "change";
		                
		            } else {
                        $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['create_date']));
                        $medications_array[$medication_type ][$km]['replace_with'] = "create";
		            }
		            
		            // #################################################################
		            // DOSAGE
		            // #################################################################
	                $medications_array[$medication_type ][$km]['old_dosage'] = $vm['dosage']; 
 
	                
// 	                if($vm['isbedarfs'] == "1" || $vm['isschmerzpumpe'] == "1" || $vm['isnutrition'] == "1" )
// 	                if($vm['isschmerzpumpe'] == "1" )
	                if(!in_array($deleted_medication_type,$timed_scheduled_medications) )
	                {
	                    $medications_array[$medication_type ][$km]['dosage']= $vm['dosage'];
	                }
	                else
	                {
    		            // first get new dosage
    		            if(!empty($drugplan_dosage[$vm['id']]))
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']]; 
    		            }
    		            else if(strlen($vm['dosage'])> 0 )
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
    		                
    		                if(strpos($vm['dosage'],"-")){
            		            $old_dosage_arr[$vm['id']] = explode("-",$vm['dosage']);
    
        		                if(count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$deleted_medication_type])){
             		                //  create array from old
                		            for($x = 0; $x < count($dosage_settings[$deleted_medication_type]); $x++)
                		            {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][$x]] = $old_dosage_arr[$vm['id']][$x]; 
                                    }
            		            } 
            		            else
            		            {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][1]] = $vm['dosage']; 
                                    for($x = 2; $x < count($dosage_settings[$deleted_medication_type]); $x++)
                                    {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][$x]] = "";
                                    }
            		            }
    		                } 
    		                else
    		                {
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][1]] = $vm['dosage'];
                                
                                for($x = 2; $x < count($dosage_settings[$deleted_medication_type]); $x++)
                                {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][$x]] = "";
                                } 
    		                }
    		            } 
    		            else
    		            {
        	                $medications_array[$medication_type ][$km]['dosage'] =  ""; 
    		            }
		            
	                }
		            // ############################################################
		            // Extra details  - drug / unit/ type / indication / importance
		            // ############################################################
		            
   	                $medications_array[$medication_type ][$km]['drug'] =  $medication_extra[$vm['id']]['drug']; 
   	                $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit']; 
   	                $medications_array[$medication_type ][$km]['unit_id'] =  $medication_extra[$vm['id']]['unit_id']; 
   	                $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type']; 
   	                $medications_array[$medication_type ][$km]['type_id'] =  $medication_extra[$vm['id']]['type_id']; 
   	                $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name']; 
   	                $medications_array[$medication_type ][$km]['indication_id'] =  $medication_extra[$vm['id']]['indication_id']; 
   	                $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color']; 
   	                $medications_array[$medication_type ][$km]['importance'] =  $medication_extra[$vm['id']]['importance'];
   	                $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form'];
   	                $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id'];
   	                $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
   	                
   	                $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging'];
   	                $medications_array[$medication_type ][$km]['packaging_name'] =  $medication_extra[$vm['id']]['packaging_name'];
   	                $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal'];
   	                $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];

   	                // #################################################################
   	                // DELETED date
   	                // #################################################################
   	                if($vm['delete_date'] )
   	                {
   	                    $medications_array[$medication_type ][$km]['delete_date'] = date('d.m.Y', strtotime($vm['delete_date']));
   	                } else{
   	                    $medications_array[$medication_type ][$km]['delete_date'] =  $medications_array[$medication_type ][$km]['medication_change'];
   	                
   	                }
		        }
		    }
		    
		    
		   
		    if($_REQUEST['dbg'] == "del")
		    {
		        print_r($medications_array); 
		        exit;
		    }
		    
		    $this->view->medication = $medications_array;
		}
		
		//order by medication name ASC
		private function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) 
		{
			$sort_col = array();
			foreach ($arr as $key=> $row) {
				$sort_col[$key] = $row[$col];
			}
		
			array_multisort($sort_col, $dir, $arr);
		}
				
		
		public function medicationdeletededitAction()
		{
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = Pms_Uuid::decrypt($_GET['id']);
		    $ipid = Pms_CommonData::getIpId($decid);
            $this->view->clientid = $clientid;
            $this->view->userid = $userid;
		    
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    /* ================ CLIENT USER DETAILS ======================= */
		    $usr = new User();
		    $all_users = $usr->getUserByClientid($clientid, '1', true);
		    $this->view->all_users = $all_users;
		    
		    
		    
		    $pq = new User();
		    $pqarr = $pq->getUserByClientid($clientid);
		    
		    $comma = ",";
		    $userval = "'0'";
		    
		    foreach($pqarr as $key => $val)
		    {
		        $userval .= $comma . "'" . $val['id'] . "'";
		        $comma = ",";
		    }
		    
		    $usergroup = new Usergroup();
		    $groupid = $usergroup->getMastergroupGroups($clientid, array('4'));
		    
		    $this->view->verordnetvon = $userid;
		    
		    $usr = new User();
		    $users = $usr->getuserbyidsandGroupId($userval, $groupid, 1);
		    
		    $this->view->users = $users;
		    $this->view->jsusers = json_encode($users);
		    
		    
		    
		    /* ================ CHECK PRIVILEGES ======================= */
		    $modules = new Modules();
		    /* Medication acknowledge */
		    if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $acknowledge = "1";
		        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		    
		        if(in_array($userid,$approval_users)){
		            $this->view->approval_rights = "1";
		        }
		        else
		        {
		            $this->view->approval_rights = "0";
		        }
		    }
		    else
		    {
		        $acknowledge = "0";
		    }
		    $this->view->acknowledge = $acknowledge;

		    
		    /* MMI functionality*/
		    if($modules->checkModulePrivileges("87", $clientid))
		    {
		        $this->view->show_mmi = "1";
		    }
		    else
		    {
		        $this->view->show_mmi = "0";
		    }
		    	
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    
		    /* IV BLOCK -  i.v. / s.c. */
		    $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
		    if(!$iv_medication_block){
		        $medication_blocks = array_diff($medication_blocks,array("isivmed"));
		    }
		    
		    /* TREATMENT CARE BLOCK -  Behandlungspflege*/
		    $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
		    if(!$treatmen_care_block){
		        $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
		    }
		    
		    /* NUTRITION  BLOCK - Ernahrung */
		    $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
		    if(!$nutrition_block){
		        $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
		    }
		    /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
		    $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
		    if(!$schmerzepumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
		    }
		    
		    /* Intervall Medis  BLOCK - Intervall Medis */
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		        $this->view->allow_normal_scheduled = "0" ;
		    } else {
		        $this->view->allow_normal_scheduled = "1";
		    }
		    
		    /* CRISIS  BLOCK  */
		    $crisis_block = $modules->checkModulePrivileges("144", $clientid);
		    if(!$crisis_block ){
		        $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
		    }
		    
		    // ISPC-2176 16.04.2018 @Ancuta
		    /* INTUBETED/INFUSION MEDICATION  BLOCK */
		    $intubated_block = $modules->checkModulePrivileges("167", $clientid);
		    if(!$intubated_block){
		    	$medication_blocks = array_diff($medication_blocks,array("isintubated"));
		    }
		    
		    
		    $this->view->medication_blocks = $medication_blocks;

		    /* PHARMACY ORDER */
		    $pharmacyorder = $modules->checkModulePrivileges("50", $clientid);
		    if($pharmacyorder)
		    {
		        $this->view->pharmacyorder = '1';
		    }
		    
		    /* ================ MEDICATION :: CLIENT SETTINGS======================= */
		    /*
		    $clientdata_array = Pms_CommonData::getClientData($this->clientid);
		    $clientdata = $clientdata_array[0];
		    
		    $show_new_fields = "0";
		    if($clientdata['new_medication_fields'] == "1"){
		        $show_new_fields = "1";
		    }
		    */
		    $show_new_fields = 1; //ISPC-1848 F p.7
		    $this->view->show_new_fileds = $show_new_fields;
		    /* ================ MEDICATION :: CLIENT EXTRA ======================= */
		    //UNIT
		    $medication_unit = MedicationUnit::client_medication_unit($clientid);
		    
		    foreach($medication_unit as $k=>$unit){
		        $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
		    }
		    $this->view->js_med_unit = json_encode($client_medication_extra['unit']);
		    
		    
		    //TYPE
		    $medication_types = MedicationType::client_medication_types($clientid);
		    foreach($medication_types as $k=>$type){
		        $client_medication_extra['type'][$type['id']] = $type['type'];
		    }
		    $this->view->js_med_type = json_encode($client_medication_extra['type']);
		    
		    //INDICATIONS
		    $medication_indications = MedicationIndications::client_medication_indications($clientid);
		    
		    foreach($medication_indications as $k=>$indication){
		        $client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
		        $client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
		    }
		    
		    $this->view->js_med_indication = json_encode($client_medication_extra['indication']);
		    
		    
		    $this->view->client_medication_extra = $client_medication_extra;

		    /* ================ PATIENT HEALTH INSURANCE ======================= */
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);
		    
		    $this->view->kassen_no = $healthinsu_array[0]['kvk_no'];
		    
		    /* ================ PATIENT TIME SCHEME ======================= */
		    /* $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
		    
		    if($patient_time_scheme['patient']){
		        $set = 0;
		        foreach($patient_time_scheme['patient']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$int_id]['time'] = $int_data;
		            $interval_array['interval'][$int_id]['custom'] = '0';
		            $interval_array['interval'][$int_id]['interval_id'] = $int_id;
		            
		            $dosage_settings[$set] = $int_data;
		            $set++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    else
		    {
		        $inf=1;
		        $setc= 0;
		        foreach($patient_time_scheme['client']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$inf]['time'] = $int_data;
		            $interval_array['interval'][$inf]['custom'] = '1';
		            $dosage_settings[$setc] = $int_data;
		            $setc++;
		            $inf++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    $this->view->js_dosage_intervals = json_encode($dosage_intervals);
		    
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0"; */

		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
		    if($individual_medication_time_m){
		        $individual_medication_time = 1;
		    }else {
		        $individual_medication_time = 0;
		    }
		    
		    //get time scchedule options
		    $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
		    $this->view->client_medication_options = $client_med_options;
		    
		    $time_blocks = array('all');
		    foreach($client_med_options as $mtype=>$mtime_opt){
		        if($mtime_opt['time_schedule'] == "1"){
		            $time_blocks[]  = $mtype;
		            $timed_scheduled_medications[]  = $mtype;
		        }
		    }
		    
		    if(empty($timed_scheduled_medications)){
		        $timed_scheduled_medications = array("actual","isivmed");
		    }
		    
		    if($individual_medication_time == "0")
		    {
		        $timed_scheduled_medications = array("actual","isivmed");
		        $time_blocks = array("actual","isivmed");
		    }
		    
		    $this->view->timed_scheduled_medications = $timed_scheduled_medications;
		    
		    $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);
		    
		    if($patient_time_scheme['patient']){
		        foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
		        {
		            if($med_type != "new"){
		                $set = 0;
		                foreach($dos_data  as $int_id=>$int_data)
		                {
		                    if(in_array($med_type,$patient_time_scheme['patient']['new'])){
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                    else
		                    {
		    
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
		                        $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                }
		            }
		        }
		    }
		    else
		    {
		        foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
		        {
		    
		            $inf=1;
		            $setc= 0;
		            foreach($mtimes as $int_id=>$int_data){
		    
		                $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
		                $interval_array['interval'][$med_type][$inf]['custom'] = '1';
		                $dosage_settings[$med_type][$setc] = $int_data;
		                $setc++;
		                $inf++;
		    
		                $dosage_intervals[$med_type][$int_data] = $int_data;
		            }
		        }
		    }
		    
		    
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";
		    
		    
		    
		    
		    
	
		    
		    // get patient alergies
		    $aller = new PatientDrugPlanAllergies();
		    $allergies = $aller->getPatientDrugPlanAllergies($decid);
		    
		    if(!empty($allergies))
		    {
		        $patient_allergies = $allergies[0];
		        $this->view->allergies_comment = $allergies[0]['allergies_comment'];
		    }
		    
            // get patient drugplan		    
		    $m_medication = new PatientDrugPlan();
		    $medicarr = $m_medication->getDeletedMedication($decid,true,true);
		    
		    $medications_array = array();// TODO-1488 Medication II 12.04.2018
		    foreach($medicarr as $k=>$medication_data)
		    {
		        $medications_array['deleted'][] = $medication_data;
		        
		       if($medication_data['treatment_care'] == "1")
		        {
		            $treatmen_care_med_ids[] = $medication_data['medication_master_id'];
		        }
		        elseif($medication_data['isnutrition'] == "1")
		        {
		            $nutrition_med_ids[] = $medication_data['medication_master_id'];
		        } 
		        else
		        {
		            $med_ids[] = $medication_data['medication_master_id'];
		            
		        }
		    }
		    // get medication details
		    if(empty($med_ids))
		    {
		        $med_ids[] = "99999999";
		    }
		    $med = new Medication();
		    $master_medication_array = $med->master_medications_get($med_ids, false);
		  
		    
		    
		    
		    // get treatment care details
		    if(empty($treatmen_care_med_ids))
		    {
		        $treatmen_care_med_ids[] = "99999999";
		    }
		    $medtr = new MedicationTreatmentCare();
		    $medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);
		    
		    foreach($medarr_tr as $k_medarr_tr => $v_medarr_tr)
		    {
		        $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
		    }
		    
		    foreach($medications_array['treatment_care'] as $tr_key =>$tr_data){
		        $medications_array['treatment_care'][$tr_key]['medication'] = $medication_tr_array[$tr_data['medication_master_id']]['name'];
		    }
		    
		    
		    // get nutrition  details
		    if(empty($nutrition_med_ids))
		    {
		        $nutrition_med_ids[] = "99999999";
		    }
		    $mednutrition = new Nutrition();
		    $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
		    
		    foreach($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition)
		    {
		        $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
		    }
		    
		    
		    foreach($medications_array['isnutrition'] as $nutrition_key =>$ntr_data){
		        $medications_array['isnutrition'][$nutrition_key]['medication'] = $medication_nutrition_array[$ntr_data['medication_master_id']]['name'];
		    }
		    
		    // get patient new dosage structure
		    $drugplan_dosage = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
            
		    // get patient extra details
		    $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);

		    
		    foreach($medications_array as $medication_type => $med_array)
		    {

		        foreach($med_array as $km=>$vm)
		        {
		            if($vm['isbedarfs'] == "1")
		            {
		                $deleted_medication_type = "isbedarfs";
		            }
		            elseif($vm['isivmed'] == "1")
		            {
		                $deleted_medication_type = "isivmed";
		            }
		            elseif($vm['treatment_care'] == "1")
		            {
		                $deleted_medication_type = "treatment_care";
		            }
		            elseif($vm['isnutrition'] == "1")
		            {
		                $deleted_medication_type = "isnutrition";
		            }
		            elseif($vm['isschmerzpumpe'] == "1")
		            {
		                $deleted_medication_type = "isschmerzpumpe";
		            }
		            elseif($vm['iscrisis'] == "1")
		            {
		                $deleted_medication_type = "iscrisis";
		            }
		            elseif($vm['isintubated'] == "1")
		            {
		                $deleted_medication_type = "isintubated";
		            }
		            else
		            {
		                $deleted_medication_type = "actual";
		            }
		            $medications_array[$medication_type ][$km]['medication_type'] = $deleted_medication_type;
		            
		            
		            
		            // #################################################################
		            // name
		            // #################################################################
		            if($vm['treatment_care'] == "1"){
    		            $medications_array[$medication_type ][$km]['medication'] = $medication_tr_array[$vm['medication_master_id']]['name'];
		            }
		            elseif($vm['isnutrition'] == "1"){
    		            $medications_array[$medication_type ][$km]['medication'] = $medication_nutrition_array[$vm['medication_master_id']]['name'];
		            }else{
    		            $medications_array[$medication_type ][$km]['medication'] = $master_medication_array[$vm['medication_master_id']];
		            }
		            
		            
		            
		            if($vm['medication_change'] != '0000-00-00 00:00:00')
		            {
		                $medications_array[$medication_type ][$km]['medication_change'] = date('d.m.Y', strtotime($vm['medication_change']));
		                $medications_array[$medication_type ][$km]['replace_with'] = "none";
		                
		            } elseif($medication_change == '0000-00-00 00:00:00' && $vm['change_date'] != '0000-00-00 00:00:00') {
		                $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['change_date']));
		                $medications_array[$medication_type ][$km]['replace_with'] = "change";
		                
		            } else {
                        $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['create_date']));
                        $medications_array[$medication_type ][$km]['replace_with'] = "create";
		            }
		            
		            // #################################################################
		            // DOSAGE
		            // #################################################################
	                $medications_array[$medication_type ][$km]['old_dosage'] = $vm['dosage']; 
 
	                
// 	                if($vm['isbedarfs'] == "1" || $vm['isschmerzpumpe'] == "1" || $vm['isnutrition'] == "1" )
	                 if(!in_array($deleted_medication_type,$timed_scheduled_medications) )
	                {
	                    $medications_array[$medication_type ][$km]['dosage']= $vm['dosage'];
	                }
	                else
	                {
    		            // first get new dosage
    		            if(!empty($drugplan_dosage[$vm['id']]))
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']]; 
    		            }
    		            else if(strlen($vm['dosage'])> 0 )
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = array();// TODO-1488 Medication II 12.04.2018
    		                
    		                if(strpos($vm['dosage'],"-")){
            		            $old_dosage_arr[$vm['id']] = explode("-",$vm['dosage']);
    
        		                if(count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$deleted_medication_type])){
             		                //  create array from old
                		            for($x = 0; $x < count($dosage_settings[$deleted_medication_type]); $x++)
                		            {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][$x]] = $old_dosage_arr[$vm['id']][$x]; 
                                    }
            		            } 
            		            else
            		            {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][1]] = $vm['dosage']; 
                                    for($x = 2; $x < count($dosage_settings[$deleted_medication_type]); $x++)
                                    {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][$x]] = "";
                                    }
            		            }
    		                } 
    		                else
    		                {
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][1]] = $vm['dosage'];
                                
                                for($x = 2; $x < count($dosage_settings[$deleted_medication_type]); $x++)
                                {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$deleted_medication_type][$x]] = "";
                                } 
    		                }
    		            } 
    		            else
    		            {
        	                $medications_array[$medication_type ][$km]['dosage'] =  ""; 
    		            }
		            
	                }
		            // ############################################################
		            // Extra details  - drug / unit/ type / indication / importance
		            // ############################################################
		            
   	                $medications_array[$medication_type ][$km]['drug'] =  $medication_extra[$vm['id']]['drug']; 
   	                $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit']; 
   	                $medications_array[$medication_type ][$km]['unit_id'] =  $medication_extra[$vm['id']]['unit_id']; 
   	                $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type']; 
   	                $medications_array[$medication_type ][$km]['type_id'] =  $medication_extra[$vm['id']]['type_id']; 
   	                $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name']; 
   	                $medications_array[$medication_type ][$km]['indication_id'] =  $medication_extra[$vm['id']]['indication_id']; 
   	                $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color']; 
   	                $medications_array[$medication_type ][$km]['importance'] =  $medication_extra[$vm['id']]['importance'];
   	                $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form'];
   	                $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id'];
   	                $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
   	                // ISPC-2176
   	                $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging'];
   	                $medications_array[$medication_type ][$km]['packaging_name'] =  $medication_extra[$vm['id']]['packaging_name'];
   	                $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal'];
   	                $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];
   	                
   	                // #################################################################
   	                // DELETED date
   	                // #################################################################
   	                if($vm['delete_date'] )
   	                {
   	                    $medications_array[$medication_type ][$km]['delete_date'] = date('d.m.Y', strtotime($vm['delete_date']));
   	                } else{
   	                    $medications_array[$medication_type ][$km]['delete_date'] =  $medications_array[$medication_type ][$km]['medication_change'];
   	                
   	                }
		        }
		    }
		    
		    if($_REQUEST['dbg'] == "del")
		    {
		        print_r($medications_array);
		        exit;
		    }
		    $medications_array = Pms_CommonData::clear_pdf_data($medications_array);
		    $this->view->medication = $medications_array;
		    
		}
		
		public function medicationhistoryAction()
		{
		    
// 		    set_time_limit(0);
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    $clientid = $this->clientid;
		    $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);
		    
		    
		    /* ================ CLIENT USER DETAILS ======================= */
		    $usr = new User();
		    $user_details = $usr->getUserByClientid($clientid, '1', true);
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $modules = new Modules();
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    
		    /* IV BLOCK -  i.v. / s.c. */
		    $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
		    if(!$iv_medication_block){
		        $medication_blocks = array_diff($medication_blocks,array("isivmed"));
		    }
		    
		    /* TREATMENT CARE BLOCK -  Behandlungspflege*/
		    $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
		    if(!$treatmen_care_block){
		        $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
		    }
		    
		    /* NUTRITION  BLOCK - Ernahrung */
		    $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
		    if(!$nutrition_block){
		        $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
		    }
		    /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
		    $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
		    if(!$schmerzepumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
		    }
		    
		    /* Intervall Medis  BLOCK - Intervall Medis */
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		    }
		    
		    /* CRISIS  BLOCK  */
		    $crisis_block = $modules->checkModulePrivileges("144", $clientid);
		    if(!$crisis_block ){
		        $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
		    }
		    
		    // ISPC-2176 16.04.2018 @Ancuta
		    /* INTUBETED/INFUSION MEDICATION  BLOCK */
		    $intubated_block = $modules->checkModulePrivileges("167", $clientid);
		    if(!$intubated_block){
		    	$medication_blocks = array_diff($medication_blocks,array("isintubated"));
		    }

		    $this->view->ModulePrivileges = $modules->get_client_modules($clientid); //all the active modules of this client
		    
		    $this->view->medication_blocks = $medication_blocks;
		    
		    // get details for packaging:: ISPC-2176 p6
		    $packaging_array = PatientDrugPlanExtra::intubated_packaging();
		    
		    /* ================ PATIENT TIME SCHEME ======================= */
		   /*  $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
		    
		    if($patient_time_scheme['patient']){
		        $set = 0;
		        foreach($patient_time_scheme['patient']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$int_id]['time'] = $int_data;
		            $interval_array['interval'][$int_id]['custom'] = '0';
		            $interval_array['interval'][$int_id]['interval_id'] = $int_id;
		    
		            $dosage_settings[$set] = $int_data;
		            $set++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    else
		    {
		        $inf=1;
		        $setc= 0;
		        foreach($patient_time_scheme['client']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$inf]['time'] = $int_data;
		            $interval_array['interval'][$inf]['custom'] = '1';
		            $dosage_settings[$setc] = $int_data;
		            $inf++;
		            $setc++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals; */
		    
		    
		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
		    if($individual_medication_time_m){
		        $individual_medication_time = 1;
		    }else {
		        $individual_medication_time = 0;
		    }
		    $this->view->individual_medication_time = $individual_medication_time;
		    
		    //get get saved data
		    if($individual_medication_time == "0"){
		        $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,array("all"));
		    } else {
		        $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($clientid,$medication_blocks);
		    }
		    
		    $this->view->intervals = $client_time_scheme;
		    
		    //get time scchedule options
		    $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
		    $this->view->client_medication_options = $client_med_options;
		    
		    $time_blocks = array('all');
		    foreach($client_med_options as $mtype=>$mtime_opt){
		        if($mtime_opt['time_schedule'] == "1"){
		            $time_blocks[]  = $mtype;
		            $timed_scheduled_medications[]  = $mtype;
		        }
		    }
		    
		    if($individual_medication_time == "0"){
		        $timed_scheduled_medications = array("actual","isivmed"); // default
		    }
		    
		    $this->view->timed_scheduled_medications = $timed_scheduled_medications;
		    $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);
		    
		    
		    if($patient_time_scheme['patient']){
		        foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
		        {
		            if($med_type != "new"){
		                $set = 0;
		                foreach($dos_data  as $int_id=>$int_data)
		                {
		                    if(in_array($med_type,$patient_time_scheme['patient']['new'])){
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                    else
		                    {
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
		                        $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                }
		            }
		        }
		    }
		    else
		    {
		        foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
		        {
		    
		            $inf=1;
		            $setc= 0;
		            foreach($mtimes as $int_id=>$int_data){
		    
		                $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
		                $interval_array['interval'][$med_type][$inf]['custom'] = '1';
		                $dosage_settings[$med_type][$setc] = $int_data;
		                $setc++;
		                $inf++;
		    
		                $dosage_intervals[$med_type][$int_data] = $int_data;
		            }
		        }
		    }
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";
		    
		    /* ================ MEDICATION :: CLIENT SETTINGS======================= */
		    /*
		    $clientdata_array = Pms_CommonData::getClientData($this->clientid);
		    $clientdata = $clientdata_array[0];
		    
		    $show_new_fields = "0";
		    if($clientdata['new_medication_fields'] == "1"){
		        $show_new_fields = "1";
		    }
		    */
		    $show_new_fields = 1; //ISPC-1848 F p.7
		    $this->view->show_new_fileds = $show_new_fields;
		    
		    /* ================ MEDICATION :: CLIENT EXTRA ======================= */
		    //UNIT
		    $medication_unit = MedicationUnit::client_medication_unit($clientid);
		    
		    foreach($medication_unit as $k=>$unit){
		        $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
		    }
		    
		    //DOSAGE FORM
		    $medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid);
		    
		    foreach($medication_dosage_forms as $k=>$df){
		        $client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
		    }
		    
		    
		    
		    //TYPE
		    $medication_types = MedicationType::client_medication_types($clientid);
		    foreach($medication_types as $k=>$type){
		        $client_medication_extra['type'][$type['id']] = $type['type'];
		    }
		    
		    //INDICATIONS
		    $medication_indications = MedicationIndications::client_medication_indications($clientid);
		    
		    foreach($medication_indications as $k=>$indication){
		        $client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
		        $client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
		    }
		    
		    //ESKALATION
		    $medication_escalation = PatientDrugPlanExtra::getMedicationEscalation();
		    foreach($medication_escalation as $esc_id=>$escalation_label){
		        $client_medication_extra['escalation'][$esc_id] = $escalation_label;
		    }
		    
		    if(!empty($_REQUEST['drugplan_id']))
		    {
    		    $drugplan_id = $_REQUEST['drugplan_id'];
    		    
    		    
    		    $drugplanid_details = PatientDrugPlan::get_drugplan_id_details($ipid,$drugplan_id); 
    		    if($drugplanid_details['isbedarfs'] == "1"){
        		    $medication_type = "isbedarfs";
    		    } 
    		    elseif($drugplanid_details['isschmerzpumpe'] == "1"){
        		    $medication_type = "isschmerzpumpe";
    		    }  
    		    elseif($drugplanid_details['isnutrition'] == "1"){
        		    $medication_type = "isnutrition";
    		    }  
    		    elseif($drugplanid_details['treatment_care'] == "1"){
        		    $medication_type = "treatment_care";
    		    }  
    		    elseif($drugplanid_details['isivmed'] == "1"){
        		    $medication_type = "isivmed";
    		    }  
    		    elseif($drugplanid_details['scheduled'] == "1"){
        		    $medication_type = "scheduled";
    		    }  
    		    elseif($drugplanid_details['iscrisis'] == "1"){
        		    $medication_type = "iscrisis";
    		    }  
    		    elseif($drugplanid_details['isintubated'] == "1"){
        		    $medication_type = "isintubated";
    		    }  
    		    else
    		    {
        		    $medication_type = "actual";
    		    }
    		    $this->view->medication_type  = $medication_type;
    		    

    		    // get history
    		    $drugplanid_history = PatientDrugPlanHistory::drugplanid_history($ipid,$drugplan_id);
    		    
    		    $medication_master_meds[0] = $drugplanid_details['medication_master_id'];
    		    foreach($drugplanid_history as $k =>$pdh)
    		    {
    		        $medication_history[$pdh['id']] =  $pdh;
    		        $medication_master_meds[] = $pdh['pd_medication_master_id'];
    		    }
    		    
    		    //get the data from master medications array
    		    $med = new Medication();
    		    $medtr = new MedicationTreatmentCare();
    		    $mednutrition = new Nutrition();

    		    
    		    if($medication_type == 'treatment_care')
    		    {
    		        $medarr = $medtr->getMedicationTreatmentCareById($medication_master_meds);
    		    }
    		    elseif($medication_type == 'isnutrition'){
    		        $medarr = $mednutrition->getMedicationNutritionById($medication_master_meds);
    		    }
    		    else
    		    {
    		        $medarr = $med->getMedicationById($medication_master_meds);
    		    }
    		    
    		    if($medarr)
    		    {
    		        foreach($medarr as $k=>$mm)
    		        {
    		            $medication_master_details[$mm['id']] = $mm;
    		            $medication_master_name[$mm['id']]= $mm['name'];
    		        }
    		    }
    		    
    		    /* ================ PATIENT - MEDICATION  NEW DOSAGE STRUCTURE =========================== */
    		    $drugplan_dosage = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid,$drugplan_id);

    		    
    		    /* ================ PATIENT - MEDICATION  EXTRA DETAILS =========================== */
    		    $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$drugplan_id);
    		    
    		    $medication_data['current'] = $drugplanid_details; 
    		    if($drugplanid_details['verordnetvon'] != 0 )
    		    {
        		    $medication_data['current']['verordnetvon'] = $user_details[$drugplanid_details['verordnetvon']]; 
    		    } 
    		    else
    		    {
        		    $medication_data['current']['verordnetvon'] = ""; 
    		    }

    		    
    		    if($medication_master_name[$drugplanid_details['medication_master_id']])
    		    {
            		$medication_data['current']['medication'] = $medication_master_name[$drugplanid_details['medication_master_id']]; 
    		    } 
    		    else
    		    {
            		$medication_data['current']['medication'] = $drugplanid_details['medication']; 
    		    }

    		    
           		$medication_data['current']['created_by'] = $user_details[$drugplanid_details['create_user']]; 
           		$medication_data['current']['changed_by'] = $user_details[$drugplanid_details['change_user']];
           		
           		$medication_data['current']['drug'] = $medication_extra[$drugplanid_details['id']]['drug'];
           		$medication_data['current']['importance'] = $medication_extra[$drugplanid_details['id']]['importance'];
           		$medication_data['current']['unit'] = $medication_extra[$drugplanid_details['id']]['unit'];
           		$medication_data['current']['type'] = $medication_extra[$drugplanid_details['id']]['type'];
           		$medication_data['current']['indication'] = $medication_extra[$drugplanid_details['id']]['indication']['name']; 
           		$medication_data['current']['indication_color'] = $medication_extra[$drugplanid_details['id']]['indication']['color']; 
           		$medication_data['current']['dosage_form'] =  $medication_extra[$drugplanid_details['id']]['dosage_form'];
           		$medication_data['current']['dosage_form_id'] =  $medication_extra[$drugplanid_details['id']]['dosage_form_id'];
           		$medication_data['current']['concentration'] =  $medication_extra[$drugplanid_details['id']]['concentration'];
           		
           		$medication_data['current']['packaging'] =  $medication_extra[$drugplanid_details['id']]['packaging'];
           		$medication_data['current']['packaging_name'] =  $medication_extra[$drugplanid_details['id']]['packaging_name'];
           		$medication_data['current']['kcal'] =  $medication_extra[$drugplanid_details['id']]['kcal'];
           		$medication_data['current']['volume'] =  $medication_extra[$drugplanid_details['id']]['volume'];
           		
           		
           		// ISPC-2247
           		$medication_data['current']['escalation_label'] =  $medication_extra[$drugplanid_details['id']]['escalation'];
           		$medication_data['current']['escalation'] =  $medication_extra[$drugplanid_details['id']]['escalation_id'];
           		// --
           		
           		// #################################################################
           		// DOSAGE
           		// #################################################################
//                 if(!in_array($medication_type,array("actual","isivmed"))){
                if(!in_array($medication_type,$timed_scheduled_medications) ){
                    $medication_data['current']['dosage'] = $drugplanid_details['dosage'];
                }
                else
                {
               		// first get new dosage
               		if(!empty($drugplan_dosage[$drugplanid_details['id']]))
               		{
               		    $medication_data['current']['dosage'] = $drugplan_dosage[$drugplanid_details['id']];
               		}
               		else if(strlen($drugplanid_details['dosage'])> 0 )
               		{
               		    $medication_data['current']['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
               		
               		    if(strpos($drugplanid_details['dosage'],"-")){
               		        $old_dosage_arr[$drugplanid_details['id']] = explode("-",$drugplanid_details['dosage']);
               		
               		        if(count($old_dosage_arr[$drugplanid_details['id']]) <= count($dosage_settings[$medication_type])){
               		            //  create array from old
               		            for($x = 0; $x < count($dosage_settings[$medication_type]); $x++)
               		            {
               		                $medication_data['current']['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$drugplanid_details['id']][$x];
               		            }
               		        }
               		        else
               		        {
                                 $medication_data['current']['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
               		             $medication_data['current']['dosage'][$dosage_settings[$medication_type][1]] = $drugplanid_details['dosage'];
               		            
               		            for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
               		            {
               		               $medication_data['current']['dosage'][$dosage_settings[$medication_type][$x]] = "";
               		            }
                            }
                        }
               		    else
               		    {
               		       $medication_data['current']['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
               		       $medication_data['current']['dosage'][$dosage_settings[$medication_type][1]] = $drugplanid_details['dosage'];
               		
                           for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
               		       {
               		           $medication_data['current']['dosage'][$dosage_settings[$medication_type][$x]] = "";
               		       }
                        }
                    }
                    else
                    {
                        $medication_data['current']['dosage'] =  "";
                    }
                
                }
                
                
                // Medication change date
                if($drugplanid_details['medication_change'] != '0000-00-00 00:00:00')
                {
                     $medication_data['current']['medication_change'] = date('d.m.Y', strtotime($drugplanid_details['medication_change'])) ;
//                      $medication_data['current']['medication_change_full'] = $drugplanid_details['medication_change'] ;
                     //TODO-1079
                     $medication_data['current']['medication_change_full'] =  date("Y-m-d H:i:s");//$drugplanid_details['change_date'];
                
                } elseif($drugplanid_details['medication_change'] == '0000-00-00 00:00:00' && $drugplanid_details['change_date'] != '0000-00-00 00:00:00') {
                
                     $medication_data['current']['medication_change']  = date('d.m.Y', strtotime($drugplanid_details['change_date']));
                     $medication_data['current']['medication_change_full']  = $drugplanid_details['change_date'];
                
                } else {
                     $medication_data['current']['medication_change']  = date('d.m.Y', strtotime($drugplanid_details['create_date']));
                     $medication_data['current']['medication_change_full']  = $drugplanid_details['create_date'];
                }
                
                
                $medication_data['current']['create_date_full']  = $drugplanid_details['create_date'];
                $medication_data['current']['create_date_dmy']  = date('d.m.Y H:i', strtotime($drugplanid_details['create_date']));

                


                if($show_new_fields == "1" && !empty($drugplan_dosage[$drugplanid_details['id']])  && strlen($medication_extra[$drugplanid_details['id']]['concentration'])> 0  && $medication_extra[$drugplanid_details['id']]['concentration'] != 0 ){
                    foreach($drugplan_dosage[$drugplanid_details['id']] as $dtime =>$dvalue)
                    {
                        if(!empty($dvalue) && strlen($dvalue)> 0 ){
                            
                            $dosage_value = str_replace(",",".",$dvalue);
                            $concentration = str_replace(",",".", $medication_extra[$drugplanid_details['id']]['concentration']);
                            $medication_data['current']['dosage_concentration'][$dtime] = rtrim(rtrim(number_format(round($dosage_value / $concentration, 4),3,",","."), "0"), ",")." ".$medication_extra[$drugplanid_details['id']]['dosage_form'];
                            
                            
//                             $medication_data['current']['dosage_concentration'][$dtime] =round($dvalue / $medication_extra[$drugplanid_details['id']]['concentration'], 2)." ".$medication_extra[$drugplanid_details['id']]['dosage_form'];
                        } else{
                            $medication_data['current']['dosage_concentration'][$dtime] = "";
                        }
                            
                    }
                }
                
                
                
                
                $medication_history[0] = $medication_data['current'];

                // get dosage                
                $drugplanid_dosage_history = PatientDrugPlanDosageHistory::drugplanid_dosage_history($ipid,$drugplan_id);
                
                foreach($drugplanid_dosage_history as $k=> $dosage_data){
                    $dosage_medication_history[$dosage_data['history_id']]['dosage'][date("H:i",strtotime($dosage_data['pdd_dosage_time_interval']))] = $dosage_data['pdd_dosage'];
                    $dosage_medication_history[$dosage_data['history_id']]['dosage_concentration'][date("H:i",strtotime($dosage_data['pdd_dosage_time_interval']))] = $dosage_data['pdd_dosage_concentration'];//TODO-3624 Ancuta 23.11.2020
                }

                // get extra history
                $drugplanid_extra_history = PatientDrugPlanExtraHistory::drugplanid_extra_history($ipid,$drugplan_id);
     
                $medication_data['history']['extra'] = $drugplanid_extra_history;
                
                foreach($drugplanid_extra_history as $k=>$extra_dta){
                    $medication_history[$extra_dta['history_id']]['drug'] = $extra_dta['pde_drug'];
                    $medication_history[$extra_dta['history_id']]['unit'] =  $client_medication_extra['unit'][$extra_dta['pde_unit']];
                    $medication_history[$extra_dta['history_id']]['type'] = $client_medication_extra['type'][$extra_dta['pde_type']];
                    $medication_history[$extra_dta['history_id']]['indication'] = $client_medication_extra['indication'][$extra_dta['pde_indication']]['name'];
                    $medication_history[$extra_dta['history_id']]['indication_color'] = $client_medication_extra['indication'][$extra_dta['pde_indication']]['color'];
                    $medication_history[$extra_dta['history_id']]['importance'] = $extra_dta['pde_importance'];
                    
//                     $medication_history[$extra_dta['history_id']]['dosage_form'] = $extra_dta['pde_dosage_form'];
                    $medication_history[$extra_dta['history_id']]['dosage_form'] = $client_medication_extra['dosage_form'][$extra_dta['pde_dosage_form']];    ;
                    $medication_history[$extra_dta['history_id']]['concentration'] = $extra_dta['pde_concentration'];
                    
                    
                    $medication_history[$extra_dta['history_id']]['packaging'] = $extra_dta['pde_packaging'];
                    $medication_history[$extra_dta['history_id']]['packaging_name'] = $packaging_array[$extra_dta['pde_packaging']];
                    $medication_history[$extra_dta['history_id']]['kcal'] = $extra_dta['pde_kcal'];
                    $medication_history[$extra_dta['history_id']]['volume'] = $extra_dta['pde_volume'];
                    
                    

                    // ISPC-2247
                    $medication_history[$extra_dta['history_id']]['escalation_label'] =  $client_medication_extra['escalation'][$extra_dta['pde_escalation']];
                    $medication_history[$extra_dta['history_id']]['escalation'] = $extra_dta['pde_escalation'];
                    // --
                    
                }

    		    foreach($drugplanid_history as $k =>$pdh)
    		    {
    		        if($medication_type == "treatment_care"){
    		            
    		            $medication_history[$pdh['id']]['medication'] = $medication_master_name[$pdh['pd_medication_master_id']];
    		        }
    		        elseif($medication_type == "isnutrition")
    		        {
    		            $medication_history[$pdh['id']]['medication'] = $medication_master_name[$pdh['pd_medication_master_id']];
    		        }
    		        else
    		        {
        		        $medication_history[$pdh['id']]['medication'] =  $pdh['pd_medication_name'];
    		        }
    		        
    		        
    		        if($pdh['pd_verordnetvon'] != 0 )
    		        {
    		           $medication_history[$pdh['id']]['verordnetvon']  = $user_details[$pdh['pd_verordnetvon']];
    		        }
    		        else
    		        {
    		            $medication_history[$pdh['id']]['verordnetvon']  = $user_details[$pdh['pd_verordnetvon']];
    		        }
    		        
    		        $medication_history[$pdh['id']]['created_by'] = $user_details[$pdh['pd_create_user']];
    		        $medication_history[$pdh['id']]['changed_by'] = $user_details[$pdh['create_user']];
    		        

    		        
    		        $medication_history[$pdh['id']]['comments'] = $pdh['pd_comments'];
    		        
    		           
    		        //TODO-1079
    		        if($pdh['create_date'] != "0000-00-00 00:00:00")
    		        {
    		        	$medication_history[$pdh['id']]['medication_change'] =  date("d.m.Y",strtotime($pdh['create_date'])) ;
    		        	$medication_history[$pdh['id']]['medication_change_full'] =  $pdh['create_date'];
    		        }
    		        elseif($pdh['pd_medication_change'] != "0000-00-00 00:00:00")
    		        {
        		        $medication_history[$pdh['id']]['medication_change'] =  date("d.m.Y",strtotime($pdh['pd_medication_change']));
        		        $medication_history[$pdh['id']]['medication_change_full'] =  $pdh['pd_medication_change'];
    		        } 
    		        elseif($pdh['pd_change_date'] != "0000-00-00 00:00:00")
    		        {
        		        $medication_history[$pdh['id']]['medication_change'] =  date("d.m.Y",strtotime($pdh['pd_change_date']));
        		        $medication_history[$pdh['id']]['medication_change_full'] =  $pdh['pd_change_date'];
    		        } 
    		        else
    		        {
        		        $medication_history[$pdh['id']]['medication_change'] =  date("d.m.Y",strtotime($pdh['pd_create_date']));
        		        $medication_history[$pdh['id']]['medication_change_full'] =  $pdh['pd_create_date'];
    		        }
    		        
    		        
    		        // ############################################################
    		        // DOSAGE 
    		        // ############################################################
    		        if(!in_array($medication_type,$timed_scheduled_medications)){
    		            $medication_history[$pdh['id']]['dosage'] =  $pdh['pd_dosage'];
    		        } 
    		        else
    		        {
    		            // first get new dosage
    		            if(!empty( $dosage_medication_history[$pdh['id']]['dosage']))
                        {
        		            $medication_history[$pdh['id']]['dosage'] =  $dosage_medication_history[$pdh['id']]['dosage'];
                        }
    		             
    		            else if(strlen($pdh['pd_dosage'])> 0 )
    		            {
    		                $medication_history[$pdh['id']]['dosage'] = array();// TODO-1488 Medication II 12.04.2018
    		            
    		                if(strpos($pdh['pd_dosage'],"-"))
    		                {
                                $old_dosage_arr[$pdh['id']] = explode("-",$pdh['pd_dosage']);
    		            
        		                if(count($old_dosage_arr[$pdh['id']]) <= count($dosage_settings[$medication_type]))
        		                {
        		                    //  create array from old
                                    for($x = 0; $x < count($dosage_settings[$medication_type]); $x++)
                                    {
                                      $medication_history[$pdh['id']]['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$pdh['id']][$x];
                                    }
                                }
    		                    else
    		                    {
    		                        $medication_history[$pdh['id']]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
    		                        $medication_history[$pdh['id']]['dosage'][$dosage_settings[$medication_type][1]] = $pdh['pd_dosage'];
    		                        
    		                        for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
    		                        {
    		                          $medication_history[$pdh['id']]['dosage'][$dosage_settings[$medication_type][$x]] = "";
    		                        }
                                }
                            }
    		                else
    		                {
                                $medication_history[$pdh['id']]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
                                $medication_history[$pdh['id']]['dosage'][$dosage_settings[$medication_type][1]] = $pdh['pd_dosage'];
    		            
    		                    for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
    		                    {
    		                      $medication_history[$pdh['id']]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                }
                            }
                        }
    		            else
    		            {
                            $medication_history[$pdh['id']]['dosage']=  "";
    		            }
    		        }
    		        
    		        
    		        $medication_history[$pdh['id']]['create_date_full'] = $pdh['create_date'];
    		        //TODO-1079
//     		        $medication_history[$pdh['id']]['create_date_dmy'] = date("d.m.Y H:i", strtotime($pdh['create_date'])) . " xx2";
    		        $medication_history[$pdh['id']]['create_date_dmy'] = $medication_data['current']['create_date_dmy'];
    		        
    		        

    		        
    		        if($show_new_fields == "1" && !empty($dosage_medication_history[$pdh['id']]['dosage'])  && strlen($medication_history[$pdh['id']]['concentration'])> 0  && $medication_history[$pdh['id']]['concentration'] != 0 ){
    		            foreach($dosage_medication_history[$pdh['id']]['dosage'] as $dtime =>$dvalue){
    		                if($_REQUEST['dbg']==1){
    		                    var_dump($dvalue);
    		                }
    		                
    		                if(!empty($dvalue) && strlen($dvalue) > 0 ){
    		                    $dosage_value = str_replace(",",".",$dvalue);
    		                    $concentration = str_replace(",",".",$medication_history[$pdh['id']]['concentration']);
    		                    
    		                    $result = "";
    		                    $result = $dosage_value / $concentration;
    		                    
    		                    if(!is_int($result))
    		                    {
    	   	                       $result = round($result, 4);
        		                   $medication_history[$pdh['id']]['dosage_concentration'][$dtime] = rtrim(rtrim(number_format($result ,3,",","."), "0"), ",")." ".$medication_history[$pdh['id']]['dosage_form'];
    		                    } 
    		                    else
    		                    {
        		                    $medication_history[$pdh['id']]['dosage_concentration'][$dtime] = $result." ".$medication_history[$pdh['id']]['dosage_form'];
    		                    }
    		                } 
    		                else
    		                {
        		                $medication_history[$pdh['id']]['dosage_concentration'][$dtime] = "";
    		                }
    		            }
    		        }
    		    }

    		    $listed_his = array();
    		    foreach($medication_history as $hid => $hdata){
    		        if(is_array($hdata['dosage'])){
        		        $details[$hid] = $hdata['medication'].implode("-",$hdata['dosage']);
    		            
    		        } else {
        		        $details[$hid] = $hdata['medication'].$hdata['dosage'];
        		        //ISPC-2110 p.4
        		        $details[$hid] .= ! empty($hdata['pd_dosage_interval']) ? $hdata['pd_dosage_interval'] : '';
        		        $details[$hid] .= ! empty($hdata['pd_dosage_product']) ? $hdata['pd_dosage_product'] : '';
    		        }
    		        
       		        if(!in_array($details[$hid],$listed_his)){
        		        $listed_his[$hid] = $details[$hid];
    		            $allow[] =$hid;
    		        } else {
    	                unset($medication_history[$hid]);   	            
    	                $exclude[] = $hid;
    		        }
    		    }
    		    
    		    if($_REQUEST['dbg']==1){
    		        print_r($medication_history); exit;
    		    }

    		    $medication_history = Pms_CommonData::clear_pdf_data($medication_history);
    		    $this->view->medication_data  = $medication_history;
    		    
		    }
		}
		
		private function retainValues($values, $prefix = '')
		{
			foreach($values as $key => $val)
			{
				if(!is_array($val))
				{
					$this->view->{$prefix.$key} = $val;
				}
				else
				{
					//retain 1 level array used in multiple hospizvbulk form
					foreach($val as $k_val => $v_val)
					{
						if(!is_array($v_val))
						{
							$this->view->{$prefix . $key . $k_val} = $v_val;
						}
					}
				}
			}
		}


		public function medicationprintAction()
		{
		    $this->_helper->layout->setLayout('layout_ajax');
		    $this->_helper->viewRenderer->setNoRender();
		    
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);
		    
		    $post =  array(); // this array is sent to pdf fn
		    /* ================ CHECK PRIVILEGES ======================= */
		    $modules = new Modules();
		    /* Medication acknowledge */
		    if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $acknowledge = "1";
		        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		    
		        if(in_array($userid,$approval_users)){
		            $this->view->approval_rights = "1";
		        }
		        else
		        {
		            $this->view->approval_rights = "0";
		        }
		    }
		    else
		    {
		        $acknowledge = "0";
		    }
		    $this->view->acknowledge = $acknowledge;

		    if($this->getRequest()->isPost())
		    {
// 		    print_r($_POST); exit;
    		    if(strlen($_POST['insert_bedarf']) > 0 &&  !empty($_POST['bid']))
    		    {
    		        if($acknowledge == "1"){
    		            if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA'){
    		                // do nothing
    		            }
    		            else
    		            {
    		                $this->_redirect(APP_BASE . "error/previlege");
    		            }
    		        }
    		        
    		        if($acknowledge =="1")
    		        {
    		            $_POST['skip_trigger'] = "1";
    		        }
    		        $_POST['add_sets'] = "1";
    		        $a_post = $_POST;
    		        $a_post['ipid'] = $ipid;
    		        $bm = new Application_Form_Bedarfsmedication();
    		        $bm->InsertData($a_post);
    		        

    		        $this->_redirect(APP_BASE . "patientnew/medication?id=" . $_REQUEST['id']);
    		        exit;
    		        //break;
    		    }
		    }
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    
		    /* IV BLOCK -  i.v. / s.c. */
		    $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
		    if(!$iv_medication_block){
		        $medication_blocks = array_diff($medication_blocks,array("isivmed"));
		    }
		    
		    /* TREATMENT CARE BLOCK -  Behandlungspflege*/
		    $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
		    if(!$treatmen_care_block){
		        $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
		    }
		    
		    /* NUTRITION  BLOCK - Ernahrung */
		    $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
		    if(!$nutrition_block){
		        $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
		    }
		    /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
		    $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
		    if(!$schmerzepumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
		    }
		    
		    /* Scheduled BLOCK - interval meds*/
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		    }
		    
		    /* CRISIS  BLOCK */
		    $crisis_block = $modules->checkModulePrivileges("144", $clientid);
		    if(!$crisis_block ){
		        $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
		    }
		    
		    // ISPC-2176 16.04.2018 @Ancuta
		    /* INTUBETED/INFUSION MEDICATION  BLOCK */
		    $intubated_block = $modules->checkModulePrivileges("167", $clientid);
		    if(!$intubated_block){
		    	$medication_blocks = array_diff($medication_blocks,array("isintubated"));
		    }
		    
		    $this->view->medication_blocks = $medication_blocks;

		    
		    
		    
		    /* PHARMACY ORDER */
		    $pharmacyorder = $modules->checkModulePrivileges("50", $clientid);
		    if($pharmacyorder)
		    {
		        $this->view->pharmacyorder = '1';
		    }

		    /* ================ MEDICATION :: CLIENT SETTINGS======================= */
		    /*
		    $clientdata_array = Pms_CommonData::getClientData($this->clientid);
		    $clientdata = $clientdata_array[0];
		    
		    $show_new_fields = "0";
		    if($clientdata['new_medication_fields'] == "1"){
		        $show_new_fields = "1";
		    }
		    */
		    $show_new_fields = 1; //ISPC-1848 F p.7
		    $this->view->show_new_fileds = $show_new_fields;
		    /* ================ PATIENT TIME SCHEME ======================= */
		   /*  $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
		    
		    if($patient_time_scheme['patient']){
		        $set = 0;
		        foreach($patient_time_scheme['patient']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$int_id]['time'] = $int_data;
		            $interval_array['interval'][$int_id]['custom'] = '0';
		            $interval_array['interval'][$int_id]['interval_id'] = $int_id;
		            
		            $dosage_settings[$set] = $int_data;
		            $set++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    else
		    {
		        $inf=1;
		        $setc= 0;
		        foreach($patient_time_scheme['client']  as $int_id=>$int_data)
		        {
		            $interval_array['interval'][$inf]['time'] = $int_data;
		            $interval_array['interval'][$inf]['custom'] = '1';
		            $dosage_settings[$setc] = $int_data;
		            $setc++;
		            $inf++;
		    
		            $dosage_intervals[$int_data] = $int_data;
		        }
		    }
		    
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";
 */
		    
		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
		    if($individual_medication_time_m){
		        $individual_medication_time = 1;
		    }else {
		        $individual_medication_time = 0;
		    }
		    $this->view->individual_medication_time = $individual_medication_time;
		    
		    if($individual_medication_time == "1"){
    		    //get time scchedule options
    		    $client_med_options = MedicationOptions::client_saved_medication_options($clientid);
    		    $this->view->client_medication_options = $client_med_options;
    		    
    		    $time_blocks = array('all');
    		    $NOT_timed_scheduled_medications = array();
    		    foreach($client_med_options as $mtype=>$mtime_opt){
    		        if($mtime_opt['time_schedule'] == "1"){
    		            $time_blocks[]  = $mtype;
    		            $timed_scheduled_medications[]  = $mtype;
    		        } else {
    		            $NOT_timed_scheduled_medications[]  = $mtype;
    		        }
    		    }
    		    if(empty($timed_scheduled_medications)){
    		        $timed_scheduled_medications = array("actual","isivmed");
    		    }
    		    
    		    foreach($timed_scheduled_medications  as $tk=>$tmed){
    		        if(in_array($tmed,$NOT_timed_scheduled_medications)){
    		            unset($timed_scheduled_medications[$tk]);
    		        }
    		    }
		    } else {
		        $timed_scheduled_medications = array("actual","isivmed");
		        $time_blocks = array("actual","isivmed");
		    }
		    
		    
		    $this->view->timed_scheduled_medications = $timed_scheduled_medications;
		    
		    $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);
		    
		    if($patient_time_scheme['patient']){
		        foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
		        {
		            if($med_type != "new"){
		                $set = 0;
		                foreach($dos_data  as $int_id=>$int_data)
		                {
		                    if(in_array($med_type,$patient_time_scheme['patient']['new'])){
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '1';
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                    else
		                    {
		    
		    
		                        $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
		                        $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
		                        $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;
		    
		                        $dosage_settings[$med_type][$set] = $int_data;
		                        $set++;
		    
		                        $dosage_intervals[$med_type][$int_data] = $int_data;
		                    }
		                }
		            }
		        }
		    }
		    else
		    {
		        foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
		        {
		    
		            $inf=1;
		            $setc= 0;
		            foreach($mtimes as $int_id=>$int_data){
		    
		                $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
		                $interval_array['interval'][$med_type][$inf]['custom'] = '1';
		                $dosage_settings[$med_type][$setc] = $int_data;
		                $setc++;
		                $inf++;
		    
		                $dosage_intervals[$med_type][$int_data] = $int_data;
		            }
		        }
		    }
		    
		    $this->view->interval_array = $interval_array;
		    $this->view->dosage_intervals = $dosage_intervals;
		    $this->view->deleted_intervals_ids = "0";
		    

            // get patient drugplan		    
		    $m_medication = new PatientDrugPlan();
		    $medicarr = $m_medication->getMedicationPlanAll($decid,true);
		    
		    $medications_array = array(); // TODO-1488 Medication II 12.04.2018
		    foreach($medicarr as $k=>$medication_data)
		    {
		        if($medication_data['isbedarfs'] == "1")
		        {
                    $medications_array['isbedarfs'][] = $medication_data;    
		        }
		        elseif($medication_data['isivmed'] == "1")
		        {
    		        $medications_array['isivmed'][] = $medication_data;    
		        }
		        elseif($medication_data['isschmerzpumpe'] == "1")
		        {
    		        $medications_array['isschmerzpumpe'][] = $medication_data;
    		        $cocktail_ids[] = $medication_data['cocktailid'];     
		        }
		        //ISPC-2833 Ancuta 04.03.2021 
		        elseif($medication_data['ispumpe'] == "1")
		        {
    		        $medications_array['ispumpe'][] = $medication_data;
    		        $cocktail_ids[] = $medication_data['pumpe_id'];     
		        }
		        // --
		        elseif($medication_data['treatment_care'] == "1")
		        {
    		        $medications_array['treatment_care'][] = $medication_data;
    		        $treatmen_care_med_ids[] = $medication_data['medication_master_id'];     
		        }
		        elseif($medication_data['isnutrition'] == "1")
		        {
    		        $medications_array['isnutrition'][] = $medication_data;    
    		        $nutrition_med_ids[] = $medication_data['medication_master_id'];     
		        }
		        elseif($medication_data['scheduled'] == "1")
		        {
    		        $medications_array['scheduled'][] = $medication_data;    
		        }
		        elseif($medication_data['iscrisis'] == "1")
		        {
    		        $medications_array['iscrisis'][] = $medication_data;    
		        }
		        elseif($medication_data['isintubated'] == "1") // ISPC-2176 16.04.2018 @Ancuta
		        {
		        	$medications_array['isintubated'][] = $medication_data;
		        }
		        else
		        {
    		        $medications_array['actual'][] = $medication_data;    
		        }
		        $med_ids[] = $medication_data['medication_master_id'];     
		    }

		    // get medication details
		    if(empty($med_ids))
		    {
		        $med_ids[] = "99999999";
		    }
		    $med = new Medication();
		    $master_medication_array = $med->master_medications_get($med_ids, false , true);

		    
		    //@claudiu ispc-2002
		    $post['medication_master'] = $master_medication_array['Medication'];
		    //this is sent to have the full info from medication_master table
		    
		    $aller = new PatientDrugPlanAllergies();
		    $allergies = $aller->getPatientDrugPlanAllergies(null, $ipid);
		    $post['patient_allergies'] = $allergies[0];
		    

		    
		    // get schmerzpumpe details
		    $cocktail_ids = array_unique($cocktail_ids);
		    
		    if(count($cocktail_ids) == 0)
		    {
		        $cocktail_ids[] = '999999';
		    }
		    
		    $cocktailsC = new PatientDrugPlanCocktails();
		    $cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
		    
		     if(count($cocktails) > 0)
			{
				$addnew = 0;
			}
			else
			{
				$addnew = 1;
			}
			$this->view->addnewlink = $addnew;
		    
		    
		    foreach($medications_array['isschmerzpumpe']  as $smpkey => $medicationsmp)
		    {

// 		        if($medications_array['isschmerzpumpe'][($smpkey + 1)]['cocktailid'] != $medicationsmp['cocktailid'])
// 		        {
		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = $cocktails[$medicationsmp['cocktailid']];
// 		        }
// 		        else
// 		        {
// 		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = "0";
// 		        }
		    }
		    
		    
		    
		    
		    
		    // get treatment care details
		    if(empty($treatmen_care_med_ids))
		    {
		        $treatmen_care_med_ids[] = "99999999";
		    }
		    $medtr = new MedicationTreatmentCare();
		    $medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);
		    
		    foreach($medarr_tr as $k_medarr_tr => $v_medarr_tr)
		    {
		        $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
		    }
		    
		    foreach($medications_array['treatment_care'] as $tr_key =>$tr_data){
		        $medications_array['treatment_care'][$tr_key]['medication'] = $medication_tr_array[$tr_data['medication_master_id']]['name'];
		    }
		    
		    
		    // get nutrition  details
		    if(empty($nutrition_med_ids))
		    {
		        $nutrition_med_ids[] = "99999999";
		    }
		    $mednutrition = new Nutrition();
		    $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
		    
		    foreach($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition)
		    {
		        $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
		    }
		    
		    
		    foreach($medications_array['isnutrition'] as $nutrition_key =>$tr_data){
		        $medications_array['isnutrition'][$nutrition_key]['medication'] = $medication_nutrition_array[$tr_data['medication_master_id']]['name'];
		    }
		    
		    // get patient new dosage structure
		    $drugplan_dosage = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
            
		    // get patient extra details
		    $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);

		    
		    foreach($medications_array as $medication_type => $med_array)
		    {

		        foreach($med_array as $km=>$vm)
		        {
		            
		        	// #################################################################
		        	// MEDICATION NAME
		        	// #################################################################
		        	$medications_array[$medication_type ][$km]['medication'] = $vm['medication'];
		        	
		        	if($vm['treatment_care'] != "1" && $vm['isnutrition'] != "1")
		        	{
		        		if(strlen($vm['medicatioin']) >  0 )
		        		{
		        			$medications_array[$medication_type ][$km]['medication'] = $vm['medication'];
		        		}
		        		else
		        		{
		        			$medications_array[$medication_type ][$km]['medication'] = $master_medication_array[$vm['medication_master_id']];
		        		}
		        	}
		        	
		        	$medications_array[$medication_type ][$km]['medication'] =  str_replace(array("<",">"), array(" "," "), $vm['medication']); // ISPC-2224 31.07.2018
		        	
		            $medications_array[$medication_type ][$km]['comments'] =  str_replace(array("<",">"), array(" ",""), $vm['comments']); // ISPC-2224 31.07.2018
		            
		            
		            if($vm['medication_change'] != '0000-00-00 00:00:00')
		            {
		                $medications_array[$medication_type ][$km]['medication_change'] = date('d.m.Y', strtotime($vm['medication_change']));
		            } 
		            elseif($medication_change == '0000-00-00 00:00:00' && $vm['change_date'] != '0000-00-00 00:00:00') 
		            {
		                $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['change_date']));
		            } 
		            else 
		            {
		                $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['create_date']));
		            }

		            
		            // #################################################################
		            // DOSAGE
		            // #################################################################
	                $medications_array[$medication_type ][$km]['old_dosage'] = $vm['dosage']; 
		            
// 	                if(!in_array($medication_type,array("actual","isivmed")))
	                if(!in_array($medication_type,$timed_scheduled_medications))
	                {
	                    $medications_array[$medication_type ][$km]['dosage']= $vm['dosage'];
	                }
	                else
	                {
    		            // first get new dosage
    		            if(!empty($drugplan_dosage[$vm['id']]))
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']]; 
    		            }
    		             
    		            else if(strlen($vm['dosage'])> 0 )
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
    		                
    		                if(strpos($vm['dosage'],"-")){
            		            $old_dosage_arr[$vm['id']] = explode("-",$vm['dosage']);
    
        		                if(count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$medication_type])){
             		                //  create array from old
                		            for($x = 0; $x < count($dosage_settings[$medication_type]); $x++)
                		            {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$vm['id']][$x]; 
                                    }
            		            } 
            		            else
            		            {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage']; 
                                    $medications_array[$medication_type ][$km]['dosage_alt'] = $vm['dosage']; // TODO-2071 
                                    for($x = 2; $x < count($dosage_settings); $x++)
                                    {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                    }
            		            }
    		                } 
    		                else
    		                {
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
                                $medications_array[$medication_type ][$km]['dosage_alt'] = $vm['dosage']; // TODO-2071 
                                
                                for($x = 2; $x < count($dosage_settings); $x++)
                                {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                } 
    		                }
    		            } 
    		            else
    		            {
        	                $medications_array[$medication_type ][$km]['dosage'] =  ""; 
    		            }
	                }
		            // ############################################################
		            // Extra details  - drug / unit/ type / indication / importance
		            // ############################################################

	                //$medications_array[$medication_type ][$km]['drug'] =  str_replace(array("<",">"), array(" "," "), $vm['drug']); // ISPC-2224 31.07.2018
	                $medications_array[$medication_type ][$km]['drug'] =  str_replace(array("<",">"), array(" "," "), $medication_extra[$vm['id']]['drug']); // ISPC-2224 31.07.2018
   	                $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit']; 
   	                $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type']; 
   	                $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name']; 
   	                $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color']; 
   	                $medications_array[$medication_type ][$km]['importance'] =  $medication_extra[$vm['id']]['importance'];
   	                $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form'];
   	                $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id'];
   	                $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
   	                $medications_array[$medication_type ][$km]['concentration_full'] =  $medication_extra[$vm['id']]['concentration'];
   	                
   	                $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging'];
   	                $medications_array[$medication_type ][$km]['packaging_name'] =  $medication_extra[$vm['id']]['packaging_name'];
   	                $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal'];
   	                $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];
   	                
   	                if($medication_extra[$vm['id']]['unit']){
   	                    $medications_array[$medication_type ][$km]['concentration_full'] .= " ".htmlspecialchars($medication_extra[$vm['id']]['unit']);
   	                }
   	                if($medication_extra[$vm['id']]['dosage_form']){
   	                    $medications_array[$medication_type ][$km]['concentration_full'] .= "/".htmlspecialchars($medication_extra[$vm['id']]['dosage_form']);
   	                }
   	                
   	                 
   	                if($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration'] ) > 0  && $medication_extra[$vm['id']]['concentration'] != 0 ){
   	                    
   	                    if( !empty($drugplan_dosage[$vm['id']]) ){
       	                    foreach($drugplan_dosage[$vm['id']] as $dtime =>$dvalue){
       	                        $dosage_value = str_replace(",",".",$dvalue);
       	                        $concentration = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
       	                        
       	                        $result = "";
       	                        $result = $dosage_value / $concentration;
       	                        
       	                        if(!is_int($result))
       	                        {
       	                            $result = round($result, 4);
           	                        $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = rtrim(rtrim(number_format($result,3,",","."), "0"), ",")." ".$medication_extra[$vm['id']]['dosage_form'];
       	                        } 
       	                        else
       	                        {
           	                        $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = $result." ".$medication_extra[$vm['id']]['dosage_form'];
       	                        }
       	                    }
   	                    }
   	                    else
   	                    {
   	                    	
   	                    	if(is_array($medications_array[$medication_type ][$km]['dosage'])){
   	                    		/* WHAT SHOULD HAPPEN IN THIS CASE */
   	                    		
   	                    		
   	                    		foreach($medications_array[$medication_type ][$km]['dosage'] as $dtime =>$dvalue){
   	                    			$dosage_value = str_replace(",",".",$dvalue);
   	                    			$concentration = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
   	                    		
   	                    			$result = "";
   	                    			$result = $dosage_value / $concentration;
   	                    		
   	                    			if(!is_int($result))
   	                    			{
   	                    				$result = round($result, 4);
   	                    				$medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = rtrim(rtrim(number_format($result,3,",","."), "0"), ",")." ".$medication_extra[$vm['id']]['dosage_form'];
   	                    			}
   	                    			else
   	                    			{
   	                    				$medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = $result." ".$medication_extra[$vm['id']]['dosage_form'];
   	                    			}
   	                    		}
   	                    		
   	                    	}
   	                    	else
   	                    	{
   	                    		$dosage_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage'] );
	   	                        $concentration = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
	   	                        
	   	                        $result = "";
	   	                        $result = $dosage_value / $concentration;
	   	                        
	   	                        if(!is_int($result))
	   	                        {
	   	                            $result = round($result, 4);       	                        
	       	                        $medications_array[$medication_type ][$km]['dosage_concentration'] = rtrim(rtrim(number_format($result,3,",","."), "0"), ",")." ".$medication_extra[$vm['id']]['dosage_form'];
	   	                        } 
	   	                        else
	   	                        {
	       	                        $medications_array[$medication_type ][$km]['dosage_concentration'] = $result." ".$medication_extra[$vm['id']]['dosage_form'];
	   	                        }
   	                    	}
     	                }
   	                }
   	                
   	                
   	                if($medication_type == "isschmerzpumpe")
   	                {
   	                    
   	                    $dosage_value = "";
   	                    $dosage_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage']);
   	                    $medications_array[$medication_type ][$km]['dosage_24h'] = $dosage_value * 24;
   	                
   	                    if($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration'])> 0  && $medication_extra[$vm['id']]['concentration'] != 0 )
   	                    {
   	                        $dosage_24h_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage_24h']);
   	                        $concentration_24h = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
   	                
   	                        $result_24h = "";
   	                        $result_24h = $dosage_24h_value / $concentration_24h;
   	                
   	                        if(!is_int($result_24h))
   	                        {
   	                            $result_24h = round($result_24h, 4);
   	                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  number_format($result_24h,3,",",".")." ".$medication_extra[$vm['id']]['dosage_form'];
   	                        }
   	                        else
   	                        {
   	                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  $result_24h." ".$medication_extra[$vm['id']]['dosage_form'];
   	                        }
   	                    }
   	                }
   	                // #################################################################
   	                // MEDICATION comment
   	                // #################################################################
   	                $medications_array[$medication_type ][$km]['comments'] =  str_replace(array("<",">"), array(" "," "), $vm['comments']); // ISPC-2224 31.07.2018
   	                
   	                
		        }
		    }
		    

		    if(!empty($medications_array['isschmerzpumpe'])){
		    
		        foreach($medications_array['isschmerzpumpe'] as $drug_id_ke =>$med_details)
		        {
		            $alt_medications_array["isschmerzpumpe"][$med_details['cocktailid']][] =  $med_details;
		        }
		    
		        unset($medications_array['isschmerzpumpe']);
		        $medications_array['isschmerzpumpe'] = $alt_medications_array["isschmerzpumpe"];
		         
		    }
		    if($this->getRequest()->isPost())
		    {
		        
		      if(!empty($_POST['print'])){
		          
		          $print_data = $_POST['print']; 
		          foreach($medication_blocks as $med_type)
		          {
		              
		              if($med_type != "isschmerzpumpe" )
		              {
    		              if(!isset($print_data[$med_type]['allow']) || empty($medications_array[$med_type]))
    		              {
    		                  unset($medications_array[$med_type]);
    		              } 
    		              else
    		              {
    		                  $allow_print['medication_types'][] = $med_type;
    		                  if(isset($print_data[$med_type]['sortby'])  && strlen($print_data[$med_type]['sortby']) > 0  ){
        		                  $sort[$med_type]['column'] = $print_data[$med_type]['sortby'];
    		                  } 
    		                  else
    		                  {
        		                  $sort[$med_type]['column'] = "medication";
    		                  }
    		                  
    		                  if(isset($print_data[$med_type]['sortdir'])){
        		                  if($print_data[$med_type]['sortdir'] == "asc"){
        		                      $sort[$med_type]['direction'] = SORT_ASC;
        		                  }
        		                  else
        		                  {
        		                      $sort[$med_type]['direction'] = SORT_DESC;
        		                  }
    		                  } 
    		                  else
    		                  {
       		                      $sort[$med_type]['direction'] = SORT_ASC;
    		                  }
    		              }
		              }
		              else
		              {
		                  foreach($print_data[$med_type] as $sch_id => $sch_data){
		                      if(!isset($sch_data['allow'])){
		                          unset($medications_array[$med_type][$sch_id ]);
		                      }
		                      else
		                      {
		                          $allow_print['medication_types'][] = $med_type;
		                          $allow_print['medication_pumpe'][] = $sch_id;
		                          
		                          if(isset($sch_data['sortby']) && strlen($sch_data['sortby']) > 0 ){
		                              $sort[$med_type][$sch_id]['column'] = $sch_data['sortby'];
		                          }
		                          else
		                          {
		                              $sort[$med_type][$sch_id]['column'] = "medication";
		                          }
		                          
		                          if(isset($sch_data['sortdir'])){
		                              if($sch_data['sortdir'] == "asc"){
		                                  $sort[$med_type][$sch_id]['direction'] = SORT_ASC;
		                              }
		                              else
		                              {
		                                  $sort[$med_type][$sch_id]['direction'] = SORT_DESC;
		                              }
		                          }
		                          else
		                          {
		                              $sort[$med_type][$sch_id]['direction'] = SORT_ASC;
		                          }
		                      }
		                      
		                  }
		              }
		          }
		      }  
		      	
                // ############ APPLY SORTING ##############
                foreach($medication_blocks as $med_type)
                {
                     if($med_type != "isschmerzpumpe" )
		             {
                        $medications_array[$med_type]= $this->array_sort($medications_array[$med_type],$sort[$med_type]['column'], $sort[$med_type]['direction']);
		             } 
		             else
		             {
		                 foreach($medications_array[$med_type] as $sch_id => $sch_data){
                            $medications_array[$med_type][$sch_id] = $this->array_sort($medications_array[$med_type][$sch_id],$sort[$med_type][$sch_id]['column'], $sort[$med_type][$sch_id]['direction']);
		                 }
		             }
                }
                
//                 dd($medications_array);
                $post['allow_print'] = $allow_print; 
                
                $medications_array = Pms_CommonData::clear_pdf_data($medications_array);
                $post['medications_array'] = $medications_array;
                 
                $medication_blocks = Pms_CommonData::clear_pdf_data($medication_blocks);
                $post['medication_blocks'] = $medication_blocks; 
                
                $post['dosage_intervals'] = $this->view->dosage_intervals; 
                $post['show_new_fileds'] = $this->view->show_new_fileds;
                
                $post['timed_scheduled_medications'] = $timed_scheduled_medications;
                if($_REQUEST['dbg'] == "2"){
                    print_r($post); exit;
                }
                switch ($_POST['pdf_layout']){
                    case "medicationplan":
                        $this->generatePdfNew(3, $post, 'medication', "medicationprint.html");
                        break;
                        
                    case "medicationplanpatient":
                        $this->generatePdfNew(3, $post, 'medication_plan_patient', "medicationplanpatient.html");
                        break;

                    case "medicationplanpatient_active_substance":
                       	$this->generatePdfNew(3, $post, 'medication_plan_patient_active_substance', "medicationplanpatient_active_substance.html");
                       	break;
                       	
                   	case "medicationplanpatient_bedarfsmedication":
                    	$this->generatePdfNew(3, $post, 'medication_plan_bedarfsmedication', "medicationplanpatient_bedarfsmedication.html");
                    	break;
                        	 
                    	
                    case "medicationplanpatient_applikation":
                    	$this->generatePdfNew(3, $post, 'medication_plan_applikation', "medicationplanpatient_applikation.html");
                    	break;

                    //ISPC-2002
                    case "medicationplanpatient_datamatrix":
                    	$this->_generatePdfNew_datamatrix(3, $post, 'medication_plan_datamatrix', "medicationplanpatient_datamatrix.html");
                    	break;
                    		
                    default:
                        
                        $this->_redirect(APP_BASE . "patientnew/medication?id=" . $_REQUEST['id']);
                        break;
                            
                }
                
                
                exit;
		    }
		}
		
		
		
		private function generatePdfNew($chk, $post, $pdfname, $filename)
		{
			//print_r($post);die();
		    $clientid = $this->clientid;
		    $clientinfo = Pms_CommonData::getClientData($clientid);
		    $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpid($decid);
		    $excluded_keys = array(
		        'stamp_block'
		    );
		    $post = Pms_CommonData::clear_pdf_data($post, $excluded_keys);
		
		    $post['ipid'] = $ipid; //Pms_CommonData::getIpid($decid);
		    $userid = $this->userid;  
		    $patientmaster = new PatientMaster();
// 		    $parr = $patientmaster->getMasterData($decid, 0);
		    $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		    $parr = $patientmaster->get_patientMasterData();

		    //get weight
		    $all_vital_signs = FormBlockVitalSigns::get_patients_weight_chart($ipid);
		    if( isset($all_vital_signs[$ipid]) ) {
		    	
		    	$weight_arr = end($all_vital_signs[$ipid]);
		    	//$this->view->{patientinfo['last_weight']} = $weight_arr['weight'];
		    	$parr['last_weight'] = $weight_arr['weight'];
		    	$post['last_weight'] = $weight_arr['weight'];
		  
		    }

		    
		    $previleges = new Modules();
		    if($previleges->checkModulePrivileges("131", $clientid)){
		        $med_module = "1";
		    } else{
		        $med_module = "0";
		    }
		    
		    
		    $epid = Pms_CommonData::getEpidFromId($decid);
		    $this->view->epid = $epid;
		
		    $post['patientname'] = htmlspecialchars($parr['last_name']) . ", " . htmlspecialchars($parr['first_name']) . " \n" . htmlspecialchars($parr['street1']) . "\n" . htmlspecialchars($parr['zip']) . "&nbsp;" . htmlspecialchars($parr['city']);
		    $post['patientaddress'] = htmlspecialchars($parr['street1']) . " \n " . htmlspecialchars($parr['zip']) . " " . htmlspecialchars($parr['city']);
		    $post['pataddress'] = htmlspecialchars($parr['street1']) . ", " . htmlspecialchars($parr['zip']) . " " . htmlspecialchars($parr['city']);
		    $post['patname'] = htmlspecialchars($parr['last_name']) . ", " . htmlspecialchars($parr['first_name']);
		    $post['patbirth'] = $parr['birthd'];
		    $post['epid'] = $epid;
		
		    if($parr['sex'] == 1)
		    {
		        $this->view->male = "checked='checked'";
		    }
		    if($parr['sex'] == 2)
		    {
		        $this->view->female = "checked='checked'";
		    }
		
		    if($parr['sex'] == 1)
		    {
		        $this->view->gender = $this->view->translate("male");
		    }
		    elseif($parr['sex'] == 2)
		    {
		        $this->view->gender = $this->view->translate("female");
		    }
		    elseif($parr['sex'] != null && $parr['sex'] == 0) 
		    {
		        $this->view->gender = $this->view->translate("divers");  //ISPC-2442 @Lore   30.09.2019
		    }
		    else{
		        $this->view->gender = $this->view->translate("gender_not_documented");
		    }
		
		    $dian = new Application_Form_Diagnosis();
		    $sortarr = $dian->getHDdiagnosis($parr['ipid']);
		    foreach($sortarr as $key => $diagnosis)
		    {
		        $maind .= ' ' . $diagnosis['description'] . ',';
		    }
		
		    $post['maindiagnosis'] = substr($maind, 0, -1);
		
		    $ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
		    $this->view->refarray = $ref['referred_name'];
		
		    $loguser = Doctrine::getTable('User')->find($this->userid);
		
		    if($loguser)
		    {
		        $loguserarray = $loguser->toArray();
		        $this->view->lastname = $loguserarray['last_name'];
		        $this->view->firstname = $loguserarray['first_name'];
		    }
		
		    $symp = new Symptomatology();
		    $symptomarr = $symp->getPatientSymptpomatologyLast($ipid);
		
		    if(empty($symptomarr))
		    {
		        $sympval = new SymptomatologyValues();
		        $set_details = $sympval->getSymptpomatologyValues(1); //HOPE set
		        foreach($set_details as $key => $sym)
		        {
		            $symptomarr[$key] = $sym;
		            $symptomarr[$key]['symptomid'] = $sym['id'];
		        }
		    }
		    else
		    {
		        foreach($symptomarr as $key => $sym)
		        {
		            $symptomarr[$key]['sym_desc_array'] = $sym['sym_description'];
		            $symptomarr[$key]['sym_description'] = utf8_encode($sym['sym_description']['value']);
		        }
		    }
		
		    $post['symptomarr'] = $symptomarr;
		
		    $clientdata = Pms_CommonData::getClientData($this->clientid);
		    $post['clientname'] = $clientdata[0]['clientname'];
		    $post['clientfax'] = $clientdata[0]['fax'];
		    $post['clientphone'] = $clientdata[0]['phone'];
		    $post['clientemail'] = $clientdata[0]['emailid'];
		    $post['clientcity'] = $clientdata[0]['city'];
		
		    $pmf = new PatientMoreInfo();
		    $pat_moreinfo = $pmf->getpatientMoreInfoData($ipid);
		
		    $post['dk'] = $pat_moreinfo[0]['dk'];
		    $post['peg'] = $pat_moreinfo[0]['peg'];
		    $post['port'] = $pat_moreinfo[0]['port'];
		    $post['pumps'] = $pat_moreinfo[0]['pumps'];
		
		    $post['sapsymp'] = Sapsymptom::get_patient_sapvsymptom(Pms_CommonData::getIpid($decid));
		    
// 		    $patientmaster = new PatientMaster();
// 		    $patientinfo = $patientmaster->getMasterData($decid, 0);
		    $patientinfo = $parr;

		    $post['bdate'] = $patientinfo['birthd'];
		
		    if($patientinfo['isdischarged'] != 1)
		    {
		        $sav = new SapvVerordnung();
		        $post['savarry'] = $sav->getSapvVerordnungData($patientinfo['ipid']);
		    }
		
		    $phelathinsurance = new PatientHealthInsurance();
		    $healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);
		
		    $post['insurance_company_name'] = $healthinsu_array[0]['company_name'];
		    $post['insurance_no'] = $healthinsu_array[0]['insurance_no'];
		    $post['insurance_status'] = $healthinsu_array[0]['insurance_status'];
		
		    $hquery = Doctrine_Query::create()
		    ->select('*')
		    ->from('HealthInsurance')
		    ->where("id='" . $healthinsu_array[0]['companyid'] . "' or name='" . htmlentities($healthinsu_array[0]['company_name'], ENT_QUOTES) . "'");
		    $harray = $hquery->fetchArray();
		    $post['kvnumber'] = $harray[0]['kvnumber'];
		
		    /* analage3 */
// 		    $patientmaster = new PatientMaster();
// 		    $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		
// 		    $tm = new TabMenus();
// 		    $this->view->tabmenus = $tm->getMenuTabs();
		
		    $imgtag = Doctrine::getTable('SapfiveImagetags')->findBy('ipid', $ipid);
		    $post['tagarray'] = $imgtag->toArray();
		
		    $post['tablepatientinfo'] = Pms_Template::createTemplate($parr, 'templates/pdfprofile.html');
		
		    $post['tag'] = date("d");
		    $post['month'] = date("m");
		    $post['jahr'] = date("Y");
		
		    //get main diagnosis types
		    $abb = "'HD','ND'";
		    $dg = new DiagnosisType();
		    $darr = $dg->getDiagnosisTypes($clientid, $abb);
		
		    foreach($darr as $k_dt => $v_dt)
		    {
		        $dtypearray[$v_dt['abbrevation']] = $v_dt['id'];
		    }
		
		    foreach($post['dtype'] as $k_dtype => $v_dtype)
		    {
		        if(in_array($v_dtype, $dtypearray))
		        {
		            if(!empty($post['diagnosis'][$k_dtype]))
		            {
		                $current_diagnosis_type = array_search($v_dtype, $dtypearray);
		                $diagnosis_arr[$current_diagnosis_type][] = trim(rtrim($post['icdnumber'][$k_dtype] . ' ' . $post['diagnosis'][$k_dtype]));
		            }
		        }
		    }
		
		    $metas = array('');
		    foreach($post['meta_title'] as $k_meta => $v_meta)
		    {
		        $metas = array_merge($metas, $v_meta);
		    }

	
		    //get all metastases
		    $dm = new DiagnosisMeta();
		    $diagnosismeta = $dm->getDiagnosisMetaData(1);
		
		    foreach($metas as $k_metas => $v_metas)
		    {
		        if(!empty($v_metas))
		        {
		            $metastases[] = trim(rtrim($diagnosismeta[$v_metas]));
		        }
		    }
		
		    $post['main_diagnosis'] = implode(', ', $diagnosis_arr['HD']);
		    $post['metastases'] = implode(', ', $metastases);
		    $post['side_diagnosis'] = implode(', ', $diagnosis_arr['ND']);
		
		    // sapv questionnaire
		    $htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
// 		   echo $filename; die();
// 		     print_r($htmlform);exit; 
// 		    if($chk == 1)
// 		    {
// 		        // $dlSession = new Zend_Session_Namespace('doctorLetterSession');
// 		        $tmpstmp = time();
// 		        mkdir("uploads/" . $tmpstmp);
// 		        $pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
// 		        $_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 		        $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
// 		        exec($cmd);
// 		        $zipname = $tmpstmp . ".zip";
// 		        $filename = "uploads/" . $tmpstmp . ".zip";
// 		        $con_id = Pms_FtpFileupload::ftpconnect();
// 		        if($con_id)
// 		        {
// 		            $upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
// 		            Pms_FtpFileupload::ftpconclose($con_id);
// 		        }
// 		    }
		    if($chk == 2)
		    {
		        ob_end_clean();
		        ob_start();
		        $pdf->Output($pdfname . '.pdf', 'D');
		        exit;
		    }
		
		    if($chk == 3)
		    {
		
		        $navnames = array(
		            /* "medication"=> "Medikamente",
		            "medication_plan"=> "Medikationsplan Patient",
		        	"medication_plan_applikation"=> "Medikament Patient + Applikation",
		            "medication_plan_bedarfsmedication"=> "Medikationsplan Bedarfsmedikamente", */
		        	"medication" => "Medikationsplan",
		        	"medication_plan_patient"=> "Medikationsplan Patient",
		        	"medication_plan_patient_active_substance"=> "Medikationsplan Patient Wirkstoff ",
		        	"medication_plan_bedarfsmedication"=> "Bedarfsmedikamente",
		        	"medication_plan_applikation"=> "Medikationsplan Patient + Applikation",
		        	"medication_plan_datamatrix"=> "Bundeseinheitlicher Medikationsplan ",
		            "Stammblatt"=> "Stammblatt",
		            "Sapvfb8lmu"=> "Leistungsnachweis Kinder-SAPV",
		        		
					"muster1a1_pdf" => "Muster 1a " . date('d.m.Y'),
		        		
		        );
		
		        //$pdf = new Pms_PDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
		        /* if($pdfname == 'medication')
		        {
		            $orientation = 'L';
		            $bottom_margin = '0';
		            $format = "A4";
		        }
		        elseif($pdfname == 'medication_plan' || $pdfname == 'medication_plan_bedarfsmedication' || $pdfname == 'medication_plan_applikation')
		        {
		            $orientation = 'L';
		            $bottom_margin = '0';
		            $format = "A4";
		        } */
		        if($pdfname == 'medication' || $pdfname == 'medication_plan_patient' || $pdfname == 'medication_plan_patient_active_substance' || $pdfname == 'bedarfsmedication' || $pdfname == 'medication_plan_applikation' || $pdfname == 'medication_plan_datamatrix')
		        {
		        	$orientation = 'L';
		        	$bottom_margin = '0';
		        	$format = "A4";
		        }
		        elseif($pdfname == 'Sapvfb8lmu')
		        {
		            $orientation = 'P';
		            $bottom_margin = '5';
		            $format = "A4";
		        }
		        elseif($pdfname == 'muster1a1_pre' || $pdfname == 'muster1a1_pdf')
		        {
		        	$orientation = 'P';
		        	$bottom_margin = '5';
		        	$format = "A5";
		        }
		        else
		        {
		            $orientation = 'P';
		            $bottom_margin = '20';
		            $format = "A4";
		        }
		
		        $pdf = new Pms_PDF($orientation, 'mm', $format, true, 'UTF-8', false);
		        $pdf->SetMargins(10, 5, 10); //reset margins
		        $pdf->setDefaults(true, $orientation, $bottom_margin); //defaults with header
		        $pdf->setImageScale(1.6);
		        $pdf->format = $format;

		        if ($pdfname == 'medication' ||  $pdfname == 'medication_plan_patient' ||  $pdfname == 'medication_plan_patient_active_substance' || $pdfname == 'medication_plan_bedarfsmedication' || $pdfname == 'medication_plan_applikation' ||  $pdfname == 'medication_plan_datamatrix')
		        {
		        	$pdf->setPrintFooter(true);
		        	//ISPC-2524 add full patient name in the footline Carmen 11.02.2020
			        $pdf->footer_text = $this->view->translate("new_medication pdf footer text") . ' ' . $post['patname'];
			        $pdf->setFooterType('1 of n date 12px');
			    } else {
			    	$pdf->setPrintFooter(false);
			    }
			    //ISPC - 2162
			    $saved_plans = PlansMediPrintSettingsTable::findAllClientPlansMediPrintSettings($this->logininfo->clientid);
			    $saved_plans_plid = array();
			    foreach($saved_plans as $kr=>$vr)
			    {
			    	$saved_plans_plid[$vr['plansmedi_id']] = $vr;
			    }
			    //var_dump($saved_plans_plid); exit;
		        switch($pdfname)
		        {
		            case 'medication':
		                $background_type = "63";
		                $pdf->SetMargins(10, 30, 10); //reset margins

		                //ISPC - 2162
		                if($saved_plans_plid['medication'])
		                {
		                	$pdf->SetFont('', '', $saved_plans_plid['medication']['plansmedi_settings']['plan_font_size']);// ISPC-2162 -> Change font acoording to client settings.
		                }
		                else 
		                {
		                	$pdf->SetFont('', '', 9);
		                }
		                
		                $pdf->setHeaderFont(Array('helvetica', '', 12));
		                $pdf->SetHeaderMargin(2);
		                $pdf->SetAutoPageBreak(true, 5);
		                break;		                
		            case 'medication_plan_patient':
		            	$background_type = "64";
		            	
		            	if (isset($post['last_weight']) && trim($post['last_weight']) !='' ) {
		            		$pdf_top_margin = 30;
		            	}
		            	else {
		            		$pdf_top_margin = 25;
		            	}
		            	
		            	$pdf->SetMargins(10, $pdf_top_margin, 10); //reset margins
		        		//ISPC - 2162
		                if($saved_plans_plid['medication_plan_patient'])
		                {
		                	$pdf->SetFont('', '', $saved_plans_plid['medication_plan_patient']['plansmedi_settings']['plan_font_size']);// ISPC-2162 -> Change font acoording to client settings.
		                }
		                else 
		                {
		                	$pdf->SetFont('', '', 9);
		                }
		            	
		            	$pdf->setHeaderFont(Array('helvetica', '', 10));
		            	$pdf->SetHeaderMargin(2);
		            	$pdf->SetAutoPageBreak(true, 20);
		            	break;
		            case 'medication_plan_patient_active_substance':
		            		$background_type = "64";
		            		 
		            		if (isset($post['last_weight']) && trim($post['last_weight']) !='' ) {
		            			$pdf_top_margin = 30;
		            		}
		            		else {
		            			$pdf_top_margin = 25;
		            		}
		            		 
		            		$pdf->SetMargins(10, $pdf_top_margin, 10); //reset margins
			        		//ISPC - 2162
			                if($saved_plans_plid['medication_plan_patient_active_substance'])
			                {
			                	$pdf->SetFont('', '', $saved_plans_plid['medication_plan_patient_active_substance']['plansmedi_settings']['plan_font_size']);// ISPC-2162 -> Change font acoording to client settings.
			                }
			                else 
			                {
			                	$pdf->SetFont('', '', 9);
			                }
		            		 
		            		$pdf->setHeaderFont(Array('helvetica', '', 10));
		            		$pdf->SetHeaderMargin(2);
		            		$pdf->SetAutoPageBreak(true, 20);
		            		break;
		            case 'medication_plan_bedarfsmedication':
		                $background_type = "64";
		                
		                if (isset($post['last_weight']) && trim($post['last_weight']) !='' ) {
		                	$pdf_top_margin = 30;
		                } 
		                else {
		                	$pdf_top_margin = 25;
		                }
		                
		                $pdf->SetMargins(10, $pdf_top_margin, 10); //reset margins

		        		//ISPC - 2162
		                if($saved_plans_plid['medication_plan_bedarfsmedication'])
		                {
		                	$pdf->SetFont('', '', $saved_plans_plid['medication_plan_bedarfsmedication']['plansmedi_settings']['plan_font_size']);// ISPC-2162 -> Change font acoording to client settings.
		                }
		                else 
		                {
		                	$pdf->SetFont('', '', 9);
		                }
		                
		                $pdf->setHeaderFont(Array('helvetica', '', 10));
		                $pdf->SetHeaderMargin(2);
		                $pdf->SetAutoPageBreak(true, 20);
		                break;
		            case 'medication_plan_applikation':
		            	$background_type = "64";
		            	
		            	if (isset($post['last_weight']) && trim($post['last_weight']) !='' ) {
		            		$pdf_top_margin = 30;
		            	}
		            	else {
		            		$pdf_top_margin = 25;
		            	}
		            	
		            	$pdf->SetMargins(10, $pdf_top_margin, 10); //reset margins
		        		//ISPC - 2162
		                if($saved_plans_plid['medication_plan_applikation'])
		                {
		                	$pdf->SetFont('', '', $saved_plans_plid['medication_plan_applikation']['plansmedi_settings']['plan_font_size']);// ISPC-2162 -> Change font acoording to client settings.
		                }
		                else 
		                {
		                	$pdf->SetFont('', '', 9);
		                }
		            	
		            	$pdf->setHeaderFont(Array('helvetica', '', 10));
		            	$pdf->SetHeaderMargin(2);
		            	$pdf->SetAutoPageBreak(true, 20);
		            	break;
		            case 'medication_plan_datamatrix':
		            		$background_type = "64";
		            		 
		            		if (isset($post['last_weight']) && trim($post['last_weight']) !='' ) {
		            			$pdf_top_margin = 30;
		            		}
		            		else {
		            			$pdf_top_margin = 25;
		            		}
		            		 
		            		$pdf->SetMargins(10, $pdf_top_margin, 10); //reset margins
			        		//ISPC - 2162
			                if($saved_plans_plid['medication_plan_datamatrix'])
			                {
			                	$pdf->SetFont('', '', $saved_plans_plid['medication_plan_datamatrix']['plansmedi_settings']['plan_font_size']);// ISPC-2162 -> Change font acoording to client settings.
			                }
			                else 
			                {
			                	$pdf->SetFont('', '', 9);
			                }
		            		 
		            		$pdf->setHeaderFont(Array('helvetica', '', 10));
		            		$pdf->SetHeaderMargin(2);
		            		$pdf->SetAutoPageBreak(true, 20);
		            		break;
		            case 'Stammblatt':
		            	$pdf->firstpagebackground = true; // set pdf background only for the first page
		            	$background_type = "69"; // STammblat LMU
		                $pdf->SetMargins(20, 10, 20); //reset margins
		                break;
		                
		            case 'Sapvfb8lmu':
		                $pdf->SetMargins(10, 50, 10); //reset margins
		                break;
		            case 'muster1a1_pre':
		            case 'muster1a1_pdf':
		              	$pdf->SetMargins(8, 6, 10); //reset margins
		               	break;
		            default:
		                $background_type = false;
		                $pdf->SetMargins(10, 5, 10); //reset margins
		                break;
		        }
		
		        if($pdfname == 'Sapvfb8lmu')
		        {
		            $pdf->setHeaderFont(Array('helvetica', '', 9));
		            $pdf->SetHeaderMargin("1");
		            $header_text .='<table width="190mm">
                                	<tr>
                                		<td colspan="5"></td> 
                                	</tr>
                                	<tr>
                                		<td colspan="5"><h2>Leistungsnachweis Kinder-SAPV</h2></td> 
                                	</tr>
                                	<tr>
                                		<td colspan="5"></td> 
                                	</tr>
                                	<tr>
                                		<td width="25%"><b>Verischerter(Name, Vorname):</b></td> 
                                		<td width="25%">'.$post['patient']['details']['name'].'</td>
                                		<td width="5%"></td>
                                		<td><b>Leistungserbringer:</b></td> 
                                		<td>'.$post['client']['team_name'].'</td>
                  
                                	</tr>
                                	<tr>
                                		<td><b>Anschrift:</b></td> 
                                		<td>'.nl2br($post['patient']['details']['pdf_address']).'</td>
                                		<td></td>
                                		<td><b>Anschrift:</b></td> 
                                		<td>'.nl2br($post['client']['pdf_address']).'</td>
                                	</tr>
                                	
                                	<tr>
                                		<td><b>Geburtsdatum:</b></td> 
                                		<td>'.$post['patient']['details']['birthd'].'</td>
                                		<td></td>
                                		<td><b>Institutskennzeichen:</b></td> 
                                		<td>'.$post['client']['ik_number'].'</td>
                                	</tr>
                                	
                                	<tr>
                                		<td><b>Versichertennummer:</b></td> 
                                		<td>'.$post['patient']['health_insurance']['insurance_no'].'</td>
                                		<td></td>
                                		<td></td> 
                                		<td></td>
                                	</tr>
                                	
                                	<tr>
                                		<td><b>Krankenkasse</b>:</td> 
                                		<td>'.$post['patient']['health_insurance']['name'].'</td>
                                		<td></td>
                                		<td></td> 
                                		<td></td>
                                	</tr>
                                </table>';
    		        $pdf->HeaderText = $header_text;
		        } //date("d.m.Y H:i",time())
		        elseif($chk == 3 && ($pdfname == 'medication' ||  $pdfname == 'medication_plan_patient' ||  $pdfname == 'medication_plan_patient_active_substance' || $pdfname == 'medication_plan_bedarfsmedication' || $pdfname == 'medication_plan_applikation')){
		        	$header_text = '<table cellpadding="2" width="277mm" >
			        					<tr>
			        						<td width="50%">Name: '.  $post['patname'] .'</td>
							        		<td width="50%" align="right">'. '</td>
							        	</tr>
							        	<tr>
							        		<td>Geb.-Datum: '.  $post['patbirth'] .'</td>
							        		<td align="right"></td>
							        	</tr>';
					if (isset($post['last_weight']) && trim($post['last_weight']) !='' ) {
						$header_text .= '<tr>
							        		<td>'.$this->view->translate('weight').': '.  $post['last_weight'] .' kg</td>
							        		<td align="right"></td>
							        	</tr>';
					}
					$header_text .= '</table>';
		        	 
		        	
		        	$pdf->HeaderText = $header_text;
		        	
		        	 
		        }
		        else{
    		        $pdf->HeaderText = false;
		        }
		
		        if($background_type != false)
		        {
		            $bg_image = Pms_CommonData::getPdfBackground($clientinfo[0]['id'], $background_type);
		            if($bg_image !== false)
		            {
		                $bg_image_path = PDFBG_PATH . '/' . $clientinfo[0]['id'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];

		                if(is_file($bg_image_path))
		                {
		                    $pdf->setBackgroundImage($bg_image_path);
		                }
		            }
		        }
		
		        $html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
		
		        $excluded_css_cleanup_pdfs = array(
		            'medication',
		            'medication_plan_patient',
		        	'medication_plan_patient_active_substance',
		        	'medication_plan_bedarfsmedication',
		            'medication_plan_applikation',
		            'medication_plan_datamatrix',
		            'Sapvfb8lmu',
		        	'muster1a1_pdf',
		        );
		        //$pdf->setBackgroundImage("/home/www/ispc/public/snapshot6.png");
		        if(!in_array($pdfname, $excluded_css_cleanup_pdfs))
		        {
		            $html = preg_replace('/style=\"(.*)\"/i', '', $html);
		        }
		        
		        
		        /*
		         * ISPC-2110 , ISPC-2130
		         * 2) if an Allergie is documented on medi page, please add the allergy printed on each medi plan. so IF allergy is documented add
		         * "Alergien: ________" at the top of the medi plans (below the name)
		         * 
		         * first IF is taken from the header
		         */
		        if($chk == 3 
		            && ($pdfname == 'medication' 
		                ||  $pdfname == 'medication_plan_patient'
		            	||  $pdfname == 'medication_plan_patient_active_substance'
		                || $pdfname == 'medication_plan_bedarfsmedication' ) 
		            || $pdfname == 'medication_plan_applikation'
		        	||  $pdfname == 'medication_plan_datamatrix' ) 
		        {
		        
    		        if ( isset($post['patient_allergies']) && ! empty($post['patient_allergies']['allergies_comment']) && trim($post['patient_allergies']['allergies_comment']) != "Keine Allergien / Kommentare") {
    		            $patient_allergies_comment =  "<table cellpadding=\"2\" width=\"277mm\"> <tr><td colspan=2>Allergien: " . html_entity_decode($post['patient_allergies']['allergies_comment']) . "</td></tr></table>";
    		        
    		            $html = $patient_allergies_comment . $html ;
    		        }
		        }
		        
		        
		        
		        
// 		        echo $html; exit;
		        $pdf->setHTML($html);
// 		        $tmpstmp = $pdf->uniqfolder(PDF_PATH);
		
// 		        $file_name_real = basename($tmpstmp);
		
// 		        $pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
		
// 		        $_SESSION ['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
		        
// 		        $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
		
// 		        exec($cmd);
// 		        $zipname = $file_name_real . ".zip";
		
// 		        $filename = "uploads/" . $file_name_real . ".zip";
		
				/*
		        $con_id = Pms_FtpFileupload::ftpconnect();
		
		        if($con_id)
		        {
		            $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
		            Pms_FtpFileupload::ftpconclose($con_id);
		        }
		        */
		
// 		        $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , "uploads" );
		        
		        $_SESSION ['filename'] = $pdf->toFTP( $pdfname );
		        
		        
		        if($pdfname == 'sgbvverordungen' || $pdfname == 'sgbvverordungen_pre')
		        {
		            $record_id = $post['sgbv_form_id'];
		            $form_tabname = 'sgbv_form';
		        }
		        else
		        {
		            $record_id = '';
		            $form_tabname = '';
		        }
		
	 
		  
		        if($pdfname == 'Sapvfb8lmu' )
		        {
    		        $cust = new PatientFileUpload();
    		        $cust->title = Pms_CommonData::aesEncrypt(addslashes($navnames[$pdfname]));
    		        $cust->ipid = $ipid;
    		        $cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
    		        $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
    		        $cust->system_generated = "1";
    		        $cust->tabname = "sapv_fb8_lmu";
    		        $cust->save();
    		        $recordid = $cust->id;
		        }
		        
		        
		        
	            if($pdfname == "medication" && $med_module == "1")
	            {
				    $cust = new PatientFileUpload();
				    $cust->title = Pms_CommonData::aesEncrypt("Medikationsplan");
				    $cust->ipid = $ipid;
				    $cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
				    $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
				    $cust->system_generated = "1";
				    $cust->tabname = "medikationsplan";
				    $cust->save();
				    $recordid_pf = $cust->id;
				
				    $custcourse = new PatientCourse();
				    $custcourse->ipid = $ipid;
				    $custcourse->course_date = date("Y-m-d H:i:s", time());
				    $custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				    $comment = 'Medikationsplan wurde erstellt';
				    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				    $custcourse->user_id = $userid;
				    $custcourse->recordid = $recordid_pf;
				    $custcourse->tabname = Pms_CommonData::aesEncrypt('medikationsplan');
				    $custcourse->save();
	            }
	            elseif(($pdfname == "medication_plan_patient" || $pdfname == "medication_plan_patient_active_substance") && $med_module == "1")
	            {
				    $cust = new PatientFileUpload();
				    $cust->title = Pms_CommonData::aesEncrypt("Medikationsplan Patient");
				    $cust->ipid = $ipid;
				    $cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
				    $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
				    $cust->system_generated = "1";
				    $cust->tabname = "medikationsplan_patient";
				    $cust->save();
				    $recordid_pf = $cust->id;
				
				    $custcourse = new PatientCourse();
				    $custcourse->ipid = $ipid;
				    $custcourse->course_date = date("Y-m-d H:i:s", time());
				    $custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				    $comment = 'Medikationsplan Patient wurde erstellt';
				    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				    $custcourse->user_id = $userid;
				    $custcourse->recordid = $recordid_pf;
				    $custcourse->tabname = Pms_CommonData::aesEncrypt('medikationsplan_patient');
				    $custcourse->save();
	            }
	            elseif(($pdfname == "medication_plan_applikation" || $pdfname == "medication_plan_datamatrix") && $med_module == "1")
	            {
	            	$cust = new PatientFileUpload();
	            	$cust->title = Pms_CommonData::aesEncrypt("Medikament Patient + Applikation");
	            	$cust->ipid = $ipid;
	            	$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
	            	$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
	            	$cust->system_generated = "1";
	            	$cust->tabname = "medikationsplan_patient_app";
	            	$cust->save();
	            	$recordid_pf = $cust->id;
	            
	            	$custcourse = new PatientCourse();
	            	$custcourse->ipid = $ipid;
	            	$custcourse->course_date = date("Y-m-d H:i:s", time());
	            	$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
	            	$comment = 'Medikament Patient + Applikation wurde erstellt';
	            	$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
	            	$custcourse->user_id = $userid;
	            	$custcourse->recordid = $recordid_pf;
	            	$custcourse->tabname = Pms_CommonData::aesEncrypt('medikationsplan_patient_app');
	            	$custcourse->save();
	            }
	            elseif( $pdfname == "medication_plan_bedarfsmedication" && $med_module == "1")
	            {
	            	$cust = new PatientFileUpload();
	            	$cust->title = Pms_CommonData::aesEncrypt("Medikationsplan Bedarfsmedikamente");
	            	$cust->ipid = $ipid;
	            	$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
	            	$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
	            	$cust->system_generated = "1";
	            	$cust->tabname = "medikationsplan_patient";
	            	$cust->save();
	            	$recordid_pf = $cust->id;
	            
	            	$custcourse = new PatientCourse();
	            	$custcourse->ipid = $ipid;
	            	$custcourse->course_date = date("Y-m-d H:i:s", time());
	            	$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
	            	$comment = 'Medikationsplan Bedarfsmedikamente wurde erstellt';
	            	$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
	            	$custcourse->user_id = $userid;
	            	$custcourse->recordid = $recordid_pf;
	            	$custcourse->tabname = Pms_CommonData::aesEncrypt('medikationsplan_patient');
	            	$custcourse->save();
	            }
	            elseif($pdfname == "muster1a1_pdf"){
	            	
	            	$cust = new PatientFileUpload();
				    $cust->title = Pms_CommonData::aesEncrypt($navnames [$pdfname] );
				    $cust->ipid = $ipid;
				    $cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
				    $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
				    $cust->system_generated = "1";
				    $cust->tabname = "muster1a1_pdf";
				    $cust->save();
				    $recordid_pf = $cust->id;
				
				    $comment = 'PDF des ' . $navnames [$pdfname] . ' in Dateien und Dokumente wurde hinterlegt';
				    
				    $custcourse = new PatientCourse();
				    $custcourse->ipid = $ipid;
				    $custcourse->course_date = date("Y-m-d H:i:s", time());
				    $custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				    $custcourse->user_id = $userid;
				    $custcourse->recordid = $recordid_pf;
				    $custcourse->tabname = Pms_CommonData::aesEncrypt('muster1a1_pdf');
				    $custcourse->save();	
	            	
	            }
	            elseif($pdfname != "medication" && $pdfname != "medication_plan_patient" && $pdfname != "medication_plan_patient_active_substance" && $pdfname != "medication_plan_bedarfsmedication" && $pdfname != "medication_plan_applikation" && $pdfname != "medication_plan_datamatrix")
	            { 
	                
	                $cust = new PatientCourse ();
	                $cust->ipid = $ipid;
	                $cust->course_date = date("Y-m-d H:i:s", time());
	                $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                $cust->course_title = Pms_CommonData::aesEncrypt(addslashes('Formular ' . $navnames[$pdfname] . ' wurde erstellt'));
	                $cust->user_id = $this->userid;
	                $cust->save();
	            }
		
		        if($pdfname != "participationpolicy_save"){
		            ob_end_clean();
		            ob_start();
		            $pdf->toBrowser($pdfname . '.pdf', "d");
		            exit;
		        }
		    }
		
		    //dont return the pdf file to user
		    if($chk == 4)
		    {
		        if($pdfname == "sgbvverordungen")
		        {
		            $record_id = $post['sgbv_form_id'];
		            $form_tabname = 'sgbv_form';
		        }
		        else if($pdfname == "FormFiveInstance")
		        {
		            $record_id = $post['form_id'];
		            $form_tabname = 'final_documentation_form'; // fromfive
		        }
		        if($pdfname == "anlage2save")
		        {
		            $record_id = $post['id'];
		            $form_tabname = 'anlage2_form';
		        }
		        $navnames = array(
		            "sgbvverordungen" => 'SGB V Verordungen',
		            "FormFiveInstance" => 'Abschlussdokumentation_' . date('d-m-Y').'',
		            "painquestionnaire_save" => 'Schmerzerhebungsbogen',
		            	
		        );
		
		        $pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
		        $pdf->setDefaults(true);
		        $pdf->setImageScale(1.6);
		        $pdf->SetMargins(10, 5, 10);
		        $background_type = false;
		        $pdf->HeaderText = false;
		
		        $html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
		        $html = preg_replace('/style=\"(.*)\"/i', '', $html);
		
		        $pdf->setHTML($html);
		
		        
		        $_SESSION ['filename'] = $pdf->toFTP( $pdfname );
		        
		        
// 		        $tmpstmp = $pdf->uniqfolder(PDF_PATH);
// 		        $file_name_real = basename($tmpstmp);
// 		        $pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
// 		        $_SESSION ['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 		        $cmd = "zip -9 -r -P " . $this->filepass . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
// 		        exec($cmd);
// 		        $zipname = $file_name_real . ".zip";
// 		        $filename = "uploads/" . $file_name_real . ".zip";
		        /*
		        $con_id = Pms_FtpFileupload::ftpconnect();
		        if($con_id)
		        {
		            $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
		            Pms_FtpFileupload::ftpconclose($con_id);
		        }
		        */
// 		        $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue ( PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , "uploads" );
		        
		        $cust = new PatientFileUpload ();
		        $cust->title = Pms_CommonData::aesEncrypt(addslashes($navnames[$pdfname]));
		        $cust->ipid = $ipid;
		        $cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']); //$post['fileinfo']['filename']['name'];
		        $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
		        $cust->recordid = $record_id;
		        $cust->tabname = $form_tabname;
		        $cust->system_generated = "1";
		        $cust->save();
		        $recordid = $cust->id;
		    }
		}
		
        public function medicationackAction(){
            $this->_helper->layout->setLayout('layout_ajax');
            $this->_helper->viewRenderer->setNoRender();
            $clientid = $this->clientid;
            
            $modules = new Modules();
            /* Medication acknowledge */
            if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
            {
                $acknowledge = "1";
                $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
                $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
            
                if(in_array($userid,$approval_users)){
                    $approval_rights = "1";
                }
                else
                {
                    $approval_rights = "0";
                }
            }
            else
            {
                $acknowledge = "0";
            }
            
            if(!empty($_REQUEST)){
                
                $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_REQUEST['id']);
                $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);
                
                $alt_details = PatientDrugPlanAlt::get_patient_drugplan_byipid($ipid,$_REQUEST['drug']);
                
                if(!empty($alt_details)){
                
                    $red_back = " red_back";
                    if($alt_details['status'] == "delete")
                    {
                        $set_for_deletion = '<span class="red_text">'.$this->translate("Delete medication: ").'</span>';
                    }
                    else
                    {
                        $set_for_deletion = '';
                    }
                
                    $rowspan =' rowspan="2"';
                
                    if($approval_rights == "1")
                    {
                        $app_rights = '<button class="med_approve_rights approvem" data-action="approve" data-recordid="'.$alt_details['id'].'" data-alt_id="'.$alt_details['alt_id'].'">"Approve"</button><button class="med_approve_rights denym"   data-action="decline" data-recordid="'.$alt_details['id'].'" data-alt_id="'.$alt_details['alt_id'].'">"Decline"</button>';
                    }
                    else
                    {
                        $app_rights = '';
                    }
                }
                else
                {
                    $rowspan ='';
                    $red_back = "";
                }
                
                
                echo "<span>".$alt_details['change_values']."</span>".$app_rights;
//                 echo "pus";
                
            }
        }

        
        
        

        private function array_sort($array, $on = NULL, $order = SORT_ASC)
        {
            $new_array = array();
            $sortable_array = array();
            if(count($array) > 0)
            {
                foreach($array as $k => $v)
                {
                    if(is_array($v))
                    {
                        foreach($v as $k2 => $v2)
                        {
                            if($k2 == $on)
                            {
                                if($on == 'birthd' || $on == 'admissiondate'  ||  $on == 'medication_change')
                                {
        
                                    if($on == 'birthdyears')
                                    {
                                        $v2 = substr($v2, 0, 10);
                                    }
                                    $sortable_array[$k] = strtotime($v2);
                                }
                                elseif($on == 'epid')
                                {
                                    $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
                                }
                                elseif($on == 'percentage')
                                {
                                    $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                                }
                                else
                                {
                                    $sortable_array[$k] = ucfirst($v2);
                                }
                            }
                        }
                    }
                    else
                    {
                        if($on == 'birthd' || $on == 'admissiondate'  ||  $on == 'medication_change')
                        {
                            if($on == 'birthdyears')
                            {
                                $v = substr($v, 0, 10);
                            }
                            $sortable_array[$k] = strtotime($v);
                        }
                        elseif($on == 'epid' || $on == 'percentage')
                        {
                            $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
                        }
                        elseif($on == 'percentage')
                        {
                            $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                        }
                        else
                        {
                            $sortable_array[$k] = ucfirst($v);
                        }
                    }
                }
                switch($order)
                {
                    case SORT_ASC:
                        $sortable_array = Pms_CommonData::a_sort($sortable_array);
                        break;
        
                    case SORT_DESC:
                        $sortable_array = Pms_CommonData::ar_sort($sortable_array);
        
                        break;
                }
        
                foreach($sortable_array as $k => $v)
                {
                    $new_array[$k] = $array[$k];
                }
            }
        
            return $new_array;
        }
        
        public function stammblattlmuAction()
        {
        	//ISPC-2022
        	// - add label in fron of phone and fax
        	// - add title in front of users name
        	// - list all parteners of one type and complete the list of partners
        	
            
            // ISPC-2327
            // Changes made by Ancuta 23.01.2019
            // Cleaned all commented unnecessary code  23.01.2019
            $logininfo= ($this->logininfo !== false) ?  $this->logininfo : (new Zend_Session_Namespace('Login_Info'));
            $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
            $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpid($decid);
            $epid =  ($this->epid !== false) ? $this->epid : Pms_CommonData::getEpidFromId($decid);
            $userid = $logininfo->userid;
            $clientid = $logininfo->clientid;
        
        
            $this->view->patient_id=$_GET['id'];
            
            if($this->getRequest()->isPost() && $_POST['btnsubmitsave'])
            {
               
                $stammblat_lmu_form = new Application_Form_Stammblattlmu();
                    
                $_POST['ipid'] = $ipid;
                $stammblat_lmu_form->insert_data($_POST);
                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
               
            }else{
        
                $old_forms = $this->view->old_forms = Stammblattlmu::get_all_entries($ipid);
            }
        
            $this->view->notruf="Im Fall einer Krisensituation soll die 24 Stunden Rufbereitschaft angerufen werden.\nMobiltelefon: 01520 - 908 9 779";
            $this->view->morephones="Büro: Montag bis Freitag 8:30 Uhr bis 16:30 Uhr";

            $patientmaster = new PatientMaster();
            $this->view->patientinfo = $patientmaster->getMasterData($decid,1);
        
            $tm = new TabMenus();
            $this->view->tabmenus = $tm->getMenuTabs();
        
            /*-------------------Patient Data---------------------------------------*/
            $patientarr = $patientmaster->getMasterData($decid,0);
            $this->view->birth = $patientarr['birthd'];
            $this->view->lastname = $patientarr['last_name'];
            $this->view->firstname = $patientarr['first_name'];
            $this->view->street = $patientarr['street1'];
            $this->view->zip = $patientarr['zip'];
            $this->view->patcity = $patientarr['city'];
            $this->view->pattel = $patientarr['phone'];
            $this->view->pathandy = $patientarr['mobile'];
            $this->view->patientenverfugung= $patientarr['living_will'];
        
            $herrfrau="Herr";
            if ($patientarr['sex']=="2"){
                $herrfrau="Frau";
            }
            $this->view->wirdversorgt=$herrfrau . " " . $patientarr['last_name'] . " wird von der Spezialisierten Ambulanten Palliativversorgung (SAPV) der Klinik und Poliklinik für Palliativmedizin der LMU München zu Hause medizinisch versorgt";
        
            
            /*--------------Client settings ----------------------------*/
            $ClientObj = new Client();
            $clientdata = $ClientObj->findOneById($clientid);
            $this->view->client_working_schedule = $clientdata['working_schedule'];
            
            
            
            /*------------------- Health Insurance Data-----------------------------*/
            $phelathinsurance = new PatientHealthInsurance();
           	$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientarr['ipid']);            
            $this->view->healthinsurance_company = $healthinsu_array[0]['company_name'] . " " . $healthinsu_array[0]['kvk_no'];
            $this->view->healthinsurance_kknr = $healthinsu_array[0]['kvk_no'];
            $this->view->healthinsurance_versnr = $healthinsu_array[0]['insurance_no'];
            $this->view->zuzahlung = $healthinsu_array[0]['rezeptgebuhrenbefreiung'];
            if(!empty($healthinsu_array[0]['companyid']) && $healthinsu_array[0]['companyid'] != 0){
        
                $helathins = Doctrine::getTable('HealthInsurance')->find($healthinsu_array[0]['companyid']);
                $healtharray = $helathins->toArray();
                $this->view->healthinsurance_companytel = $healtharray['phone'];
                $this->view->healthinsurance_companyfax = $healtharray['phonefax'];
            }
            
            //ISPC-1828 p.2
            //get fachstellen row only if this is a new form
            if(!isset($_GET['stbl']) || !isset($old_forms[(int)$_GET['stbl']]))
            {
	            
	            /*--------------Patient family doctor----------------------------*/
	            //familydoc = Hausarzt
            	$newentry = array();
            	$newentry['group'] = $this->view->translate('family_doc');
            	if($patientarr['familydoc_id'] > 0)
	            {
		            $fdoc = new FamilyDoctor();
		           	$docarray = $fdoc->getFamilyDoc($patientarr['familydoc_id']);
		            
		            if($docarray[0]){
		                $overall_data['FamilyDoctor'] = array($docarray[0]); 
		                if ($docarray[0]['last_name'] != '')
		                {
		                	if ($docarray[0]['first_name'] != '')
		                	{
		                       $namep = $docarray[0]['last_name'].", ".$docarray[0]['first_name'];
		               		 }
		               		 else
		                	{
		                		$namep = $docarray[0]['last_name'];
		                	}	
		            	}
		                else
		                {
		                	if ($docarray[0]['first_name'] != '')
		                	{
		                		//$docarray[0]['first_name']=$docarray[0]['first_name'].", ";
		                		$namep = $docarray[0]['first_name'];
		                	}
		                	else
		                	{
		                		$namep = '';
		                	}
		                }
		                $newentry['text'] .= $this->view->hausarzt_details  = $namep."<br/>";
		                if($docarray[0]['street1'] != '')
		                {
		                	$newentry['text'] .= $docarray[0]['street1'].", ".$docarray[0]['zip']." ".$docarray[0]['city'];
		                }
		                else
		                {
		                	$newentry['text'] .= $docarray[0]['zip']." ".$docarray[0]['city'];
		                }
		                if($docarray[0]['street1'] != '' && $docarray[0]['zip'] != '' && $docarray[0]['city'])
		                {
		                	$newentry['text'] .= "<br/>";
		                }
		                if($docarray[0]['phone_practice'] != '')
		                {
		                	$newentry['text'] .= $this->view->hausarzt_tel = 'Tel.: '.$docarray[0]['phone_practice'];
		                	if($docarray[0]['fax'] != '')
		                	{
		                		$newentry['text'] .= ", ";
		                	}
		                }
						if($docarray[0]['fax'] != '')
						{
		                	$newentry['text'] .= $this->view->hausarzt_fax = 'Fax: '.$docarray[0]['fax'];
						}
		            }
	            }
	            $fachstellen['family_doc'] = $newentry;
	            
	            
	            
	            /*--------------Patient (First) All Facharzte----------------------------*/
	            $newentry = array();
	            $newentry['group'] = $this->view->translate('specialist');
	             
	            $pspec = new PatientSpecialists();
	            $pkspecarray = $pspec->get_patient_specialists($patientarr['ipid'], true);
	            $pspecarray = array();
	            
	            foreach($pkspecarray as $ksp=>$vsp)
	            {
	            	$pspecarray[] = $vsp['master'];
	            }
	            $lastelem = end($pspecarray);
	            
	            $overall_data['PatientSpecialists'] = $pspecarray;
	            foreach($pspecarray as $kspec=>$detspec)
	            {
	            	if ($detspec['last_name'] != '')
	            	{
	            		if ($detspec['first_name'] != '')
	            		{
	            			$namep = $detspec['last_name'].", ".$detspec['first_name'];
	            		}
	            		else
	            		{
	            			$namep = $detspec['last_name'];
	            		}
	            	}
	            	else
	            	{
	            		if ($detspec['first_name'] != '')
	            		{
	            			$namep = $detspec['first_name'];
	            		}
	            		else
	            		{
	            			$namep = '';
	            		}
	            	}
	            	$newentry['text'] .= $this->view->spec_details  = $namep."<br/>";
	             	if($detspec['street1'] != '')
	             	{
	             		$newentry['text'] .= $detspec['street1'].", ".$detspec['zip']." ".$detspec['city'];
	             	}
	             	else
	             	{
	             		$newentry['text'] .= $detspec['zip']." ".$detspec['city'];
	             	}
	             	if($detspec['street1'] != '' && $detspec['zip'] != '' && $detspec['city'])
	             	{
	             		$newentry['text'] .= "<br/>";
	             	}
	             	if($detspec['phone_practice'] !='')
	             	{
	             		$newentry['text'] .= $this->view->spec_tel = 'Tel.: '.$detspec['phone_practice'];
	             		if($detspec['fax'] != '')
	             		{
	             			$newentry['text'] .= ", ";
	             		}
	             		else 
	             		{
	             			if($detspec != $lastelem)
	             			{
	             				$newentry['text'] .= "<br/><br/>";
	             			}
	             		}
	             	}
	             	else 
	             	{
	             		if($detspec['fax'] == '')
	             		{
	             			if($detspec != $lastelem)
	             			{
	             				$newentry['text'] .= "<br/><br/>";
	             			}
	             		}
	             	}
	             	if($detspec['fax'] != '')
	             	{
	             		
	            		if($detspec != $lastelem)
	            		{
	            			$newentry['text'] .= $this->view->spec_fax = 'Fax: '.$detspec['fax']. "<br/><br/>";
	            		}
	            		else
	            		{
	            			$newentry['text'] .= $this->view->spec_fax = 'Fax: '.$detspec['fax'];	            			
	            		}
	             	}	            	 
	            }
	             
	            $fachstellen['specialist'] = $newentry;
	           
	            
	            
	            
	            
	            /*--------------Patient (First) All Pflegediensten----------------------------*/
	            $newentry = array();
	            $newentry['group'] = $this->view->translate('pflegedienste');
	            
	            $ppfle = new PatientPflegedienste();
	            $ppflearray = $ppfle->getPatientPflegedienste($patientarr['ipid']);
 
	            $lastelem = end($ppflearray);
	            
	            $overall_data['PatientPflegedienste'] = $ppflearray;
	         	foreach($ppflearray as $kpfl=>$detpfl)
	         	{
	         		$newentry['text'] .= $this->view->pflegedienst_details  = $detpfl['nursing']."<br/>";
	         		if($detpfl['street1'] != '')
	         		{
	         			$newentry['text'] .= $detpfl['street1'].", ".$detpfl['zip']." ".$detpfl['city'];
	         		}
	         		else
	         		{
	         			$newentry['text'] .= $detpfl['zip']." ".$detpfl['city'];
	         		}
	         		if($detpfl['street1'] != '' && $detpfl['zip'] != '' && $detpfl['city'])
	         		{
	         			$newentry['text'] .= "<br/>";
	         		}
	         		if($detpfl['pf_phone_practice'] != '')
	         		{
	         			$newentry['text'] .= $this->view->pflegedienst_tel = 'Tel.: '.$detpfl['pf_phone_practice'];
	         			if($detpfl['pf_fax'] != '')
	         			{
	         				$newentry['text'] .= ", ";
	         			}
	         			else 
	             		{
	             			if($detpfl != $lastelem)
	             			{
	             				$newentry['text'] .= "<br/><br/>";
	             			}
	             		}
	             	}
	             	else 
	             	{
	             		if($detpfl['pf_fax'] == '')
	             		{
	             			if($detpfl != $lastelem)
	             			{
	             				$newentry['text'] .= "<br/><br/>";
	             			}
	             		}
	             	}
	             	if($detpfl['pf_fax'] != '')
	             	{
	         			if($detpfl != $lastelem)
	         			{	                	
	                		$newentry['text'] .= $this->view->pflegedienst_fax = 'Fax: '.$detpfl['pf_fax']. "<br/><br/>";
	         			}
	               	 	else
	                	{
	                		$newentry['text'] .= $this->view->pflegedienst_fax = 'Fax: '.$detpfl['pf_fax'];
	                	}
	             	}
	         	}   
	        
	            $fachstellen['pflegedienste'] = $newentry;
	            
	            
	            
	            
	            /*--------------Patient (First) All Sanitätshäuser----------------------------*/
	            //supplies = Sanitätshäuser
	            $newentry = array();
	            $newentry['group'] = $this->view->translate('supplies');
	            
	            $pat_pfl = new PatientSupplies();
	            $supplies_arr = $pat_pfl->getPatientSupplies($patientarr['ipid']);
	            $lastelem = end($supplies_arr);
	            
	            $overall_data['PatientSupplies'] = $supplies_arr;
	            foreach($supplies_arr as $ksupl=>$detsupl)
	            {
	            	if($detsupl['last_name'] != '')
	            	{
	            		if ($detsupl['first_name'] != '')
	            		{
	            			$namep = $detsupl['last_name'].", ".$detsupl['first_name'];
	            		}
	            		else
	            		{
	            			$namep = $detsupl['last_name'];
	            		}
	            	}
	            	else 
	            	{
	            		if ($detsupl['first_name'] != '')
	            		{
	            			$namep = $detsupl['first_name'];
	            		}
	            		else
	            		{
	            			$namep = '';
	            		}
	            	}
	            	if($namep == '')
	            	{
	            		$newentry['text'] .= $detsupl['supplier'] ;
	            	}
	            	else 
	            	{
	            		$newentry['text'] .= $namep. " ".$detsupl['supplier'] ;
	            	}
	            	$newentry['text'] .= "<br/>";
	            	if($detsupl['street1'] != '')
	            	{
	            		$newentry['text'] .= $detsupl['street1'].", ".$detsupl['zip']." ".$detsupl['city'];
	            	}
	            	else
	            	{
	            		$newentry['text'] .= $detsupl['zip']." ".$detsupl['city'];
	            	}
	            	if($detsupl['street1'] != '' || $detsupl['zip'] != '' || $detsupl['city'] != '')
	            	{
	            		$newentry['text'] .= "<br/>";
	            	}
	            	if($detsupl['phone'] != '')
	            	{
	            		$newentry['text'] .= "Tel.: ".$detsupl['phone'];
	            		if($detsupl['fax'] != '')
	            		{
	            			$newentry['text'] .= ", ";
	            		}
	            		else
	            		{
	            			if($detsupl != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	else
	            	{
	            		if($detsupl['fax'] == '')
	            		{
	            			if($detsupl != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	if($detsupl['fax'] != '')
	            	{
	            		if($detsupl != $lastelem)
	            		{
	            			$newentry['text'] .= "Fax: ".$detsupl['fax']."<br/><br/>";
	            		}
	            		else 
	            		{
	            			$newentry['text'] .= "Fax: ".$detsupl['fax'];
	            		}
	            	}
	            }
	            
	            $fachstellen['supplies'] = $newentry;
	            
	            
	            
	            
	            /*--------------Patient (First) All Apotheken----------------------------*/
	            $newentry = array();
	            $newentry['group'] = $this->view->translate('pharmacy');
	            
	            $ppharmacy = new PatientPharmacy();
	            $ppharmacy_array = $ppharmacy->getPatientPharmacy($patientarr['ipid']);
	            
	            
	            $lastelem = end($ppharmacy_array);
	            $overall_data['PatientPharmacy'] = $ppharmacy_array;
	            foreach($ppharmacy_array as $kphar=>$detphar)
	            {
	            	$newentry['text'] .= $this->view->pharmacy_details  = $detphar['apotheke']."<br/>";
	            	if($detphar['street1'] != '')
	            	{
	            		$newentry['text'] .= $detphar['street1'].", ".$detphar['zip']." ".$detphar['city'];
	            	}
	            	else
	            	{
	            		$newentry['text'] .= $detphar['zip']." ".$detphar['city'];
	            	}
	            	
	            	if($detphar['street1'] != '' ||  $detphar['zip'] != '' || $detphar['city'] !="")
	            	{
	            		$newentry['text'] .= "<br/>";
	            	}
	            	
	            	
	            	if($detphar['phone'] != '')
	            	{
	            	$newentry['text'] .= $this->view->pharmacy_tel = "Tel.: ".$detphar['phone'];
	            	if($detphar['fax'] != '')
	            	{
	            		$newentry['text'] .= ", ";
	            	}
	            	else
	            	{
	            		if($detphar != $lastelem)
	            		{
	            			$newentry['text'] .= "<br/><br/>";
	            		}
	            	}
	            	}
	            	else
	            	{
	            		if($detphar['fax'] == '')
	            		{
	            			if($detphar != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	if($detphar['fax'] != '')
	            	{
	            		if($detphar != $lastelem)
	            		{
	            			$newentry['text'] .= $this->view->pharmacy_fax = "Fax: ".$detphar['fax']."<br/><br/>";
	            		}
	            		else 
	            		{
	            			$newentry['text'] .= $this->view->pharmacy_fax = "Fax: ".$detphar['fax'];
	            		}
	            	}
	            }
	            $fachstellen['pharmacy'] = $newentry;
	            
	          
	            /*--------------Patient (First) All sonst. Versorger----------------------------*/
	            //suppliers = sonst. Versorger
	            $newentry = array();
	            $newentry['group'] = $this->view->translate('suppliers');
	            
	            $pat_pfl = new PatientSuppliers();
	            $suppliers_arr = $pat_pfl->getPatientSuppliers($patientarr['ipid']);
	            $lastelem = end($suppliers_arr);
	            
	            $overall_data['PatientSuppliers'] = $suppliers_arr;
	            foreach($suppliers_arr as $ksuppl=>$detsuppl)
	            {
	            	if($detsuppl['last_name'] != '')
	            	{
	            		if ($detsuppl['first_name'] != '')
	            		{
	            			$namep = $detsuppl['last_name'].", ".$detsuppl['first_name'];
	            		}
	            		else
	            		{
	            			$namep = $detsuppl['last_name'];
	            		}
	            	}
	            	else
	            	{
	            		if ($detsuppl['first_name'] != '')
	            		{
	            			$namep = $$detsuppl['first_name'];
	            		}
	            		else
	            		{
	            			$namep = '';
	            		}
	            	}
	            	if($namep == '')
	            	{
	            		$newentry['text'] .= $detsuppl['supplier'] ;
	            	}
	            	else
	            	{
	            		$newentry['text'] .= $namep. " ".$detsuppl['supplier'] ;
	            	}
	            	$newentry['text'] .= "<br/>";
	            	if($detsuppl['street1'] != '')
	            	{
	            		$newentry['text'] .= $detsuppl['street1'].", ".$detsuppl['zip']." ".$detsuppl['city'];
	            	}
	            	else
	            	{
	            		$newentry['text'] .= $detsuppl['zip']." ".$detsuppl['city'];
	            	}
	            	if($detsuppl['street1'] != '' && $detsuppl['zip'] != '' && $detsuppl['city'])
	            	{
	            		$newentry['text'] .= "<br/>";
	            	}
	            	if($detsuppl['phone'])
	            	{
	            		$newentry['text'] .= "Tel.: ".$detsuppl['phone'];
	            		if($detsuppl['fax'] != '')
	            		{
	            			$newentry['text'] .= ", ";
	            		}
	            		else
	            		{
	            			if($detsuppl != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	else
	            	{
	            		if($detsuppl['fax'] == '')
	            		{
	            			if($detsuppl != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	if($detsuppl['fax'] != '')
	            	{
	            		if($detsuppl != $lastelem)
	            		{
	            			$newentry['text'] .= "Fax: ".$detsuppl['fax']."<br/><br/>";
	            		}
	            		else 
	            		{
	            			$newentry['text'] .= "Fax: ".$detsuppl['fax'];
	            		}
	            	}
	            }
	            
	            $fachstellen['suppliers'] = $newentry;
	            
	            
	            
	            /*--------------Patient (First) All Physiotherapeuten----------------------------*/
	            $newentry = array();
	            $newentry['group'] = $this->view->translate('physiotherapist');
	            
	            $pphys = new PatientPhysiotherapist();
	            $pkphysarray = $pphys->get_patient_physiotherapists($patientarr['ipid'], true);
	            $pphysarray = array();
	          
	            foreach($pkphysarray as $kpy=>$vpy)
	            {
	            	$pphysarray[] = $vpy['master'];
	            }
	             
	            $lastelem = end($pphysarray);
	            
	            $overall_data['PatientPhysiotherapist'] = $pphysarray;
	            foreach($pphysarray as $kpsys=>$detpsys)
	            {
	            	if($detpsys['last_name'] != '')
	            	{
	            		if ($detpsys['first_name'] != '')
	            		{
	            			//$docarray[0]['first_name']=$docarray[0]['first_name'].", ";
	            			$namep = $detpsys['last_name'].", ".$detpsys['first_name'];
	            		}
	            		else
	            		{
	            			$namep = $detpsys['last_name'];
	            		}
	            	}
	            	else
	            	{
	            		if ($detpsys['first_name'] != '')
	            		{
	            			$namep = $detpsys['first_name'];
	            		}
	            		else
	            		{
	            			$namep = '';
	            		}
	            	}
	            	if($namep == '')
	            	{
	            		$newentry['text'] .= $detpsys['physiotherapist'] ;
	            	}
	            	else
	            	{
	            		$newentry['text'] .= $namep. " ".$detpsys['physiotherapist'] ;
	            	}
	            	$newentry['text'] .= "<br/>";
	            	if($detpsys['street1'] != '')
	            	{
	            		$newentry['text'] .= $detpsys['street1'].", ".$detpsys['zip']." ".$detpsys['city'];
	            	}
	            	else
	            	{
	            		$newentry['text'] .= $detpsys['zip']." ".$detpsys['city'];
	            	}
	            	if($detpsys['street1'] != '' && $detpsys['zip'] != '' && $detpsys['city'])
	            	{
	            		$newentry['text'] .= "<br/>";
	            	}
	            	if($detpsys['phone_practice'] != '')
	            	{
	            		$newentry['text'] .= $this->view->psys_tel = 'Tel.: '.$detpsys['phone_practice'];
	            		if($detpsys['fax'] != '')
	            		{
	            			$newentry['text'] .= ", ";
	            		}
	            		else
	            		{
	            			if($detpsys != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	else
	            	{
	            		if($detpsys['fax'] == '')
	            		{
	            			if($detpsys != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	if($detpsys['fax'] != '')
	            	{
	            		if($detpsys != $lastelem)
	            		{
	            			$newentry['text'] .= $this->view->psys_fax = 'Fax: '.$detpsys['fax']. "<br/><br/>";
	            		}
	            		else
	            		{
	            			$newentry['text'] .= $this->view->psys_fax = 'Fax: '.$detpsys['fax'];
	            		}
	            	}
	            }
	            
	            $fachstellen['physiotherapist'] = $newentry;
	
	            
	            
	            
	            /*--------------Patient (First) All Homecare----------------------------*/
	            $newentry = array();
	            $newentry['group'] = $this->view->translate('homecare');
	             
	            $phcare = new PatientHomecare();
	            $pkhcarearray = $phcare->get_patient_homecares($patientarr['ipid'], true);
	            $phcarearray = array();
	           
	            foreach($pkhcarearray as $khc=>$vhc)
	            {
	            	$phcarearray[] = $vhc['master'];
	            }
	            
	            $lastelem = end($phcarearray);
	            $overall_data['PatientHomecare'] = $phcarearray;
	            foreach($phcarearray as $kphcare=>$detphcare)
	            {
	            	if($detphcare['last_name'] != '')
	            	{
	            		if ($detphcare['first_name'] != '')
	            		{
	            			$namep = $detphcare['last_name'].", ".$detphcare['first_name'];
	            		}
	            		else
	            		{
	            			$namep = $detphcare['last_name'];
	            		}
	            	}
	            	else
	            	{
	            		if ($detphcare['first_name'] != '')
	            		{
	            			$namep = $detphcare['first_name'];
	            		}
	            		else
	            		{
	            			$namep = '';
	            		}
	            	}
	            	if($namep == '')
	            	{
	            		$newentry['text'] .= $detphcare['homecare'];
	            	}
	            	else
	            	{
	            		$newentry['text'] .= $namep. " ".$detphcare['homecare'];
	            	}
	            	$newentry['text'] .= "<br/>";
	            	if($detphcare['street1'] != '')
	            	{
	            		$newentry['text'] .= $detphcare['street1'].", ".$detphcare['zip']." ".$detphcare['city'];
	            	}
	            	else 
	            	{
	            		$newentry['text'] .= $detphcare['zip']." ".$detphcare['city'];
	            	}
	            	if($detphcare['street1'] != '' && $detphcare['zip'] != '' && $detphcare['city'])
	            	{
	            		$newentry['text'] .= "<br/>";
	            	}
	            	if($detphcare['phone_practice'] != '')
	            	{
	            		$newentry['text'] .= $this->view->pphcare_tel = 'Tel.: '.$detphcare['phone_practice'];
	            		if($detphcare['fax'] != '')
	            		{
	            			$newentry['text'] .= ", ";
	            		}
	            		else
	            		{
	            			if($detphcare != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	else
	            	{
	            		if($detphcare['fax'] == '')
	            		{
	            			if($detphcare != $lastelem)
	            			{
	            				$newentry['text'] .= "<br/><br/>";
	            			}
	            		}
	            	}
	            	if($detphcare['fax'] != '')
	            	{
	            		if($detphcare != $lastelem)
	            		{
	            			$newentry['text'] .= $this->view->phcare_fax = 'Fax: '.$detphcare['fax']. "<br/><br/>";
	            		}
	            		else
	            		{
	            			$newentry['text'] .= $this->view->phcare_fax = 'Fax: '.$detphcare['fax'];
	            		}
	            	}
	            }
	             
	            $fachstellen['homecare'] = $newentry;
	            
	            /*--------------Patient (First) All voluntaryworkers----------------------------*/
	            //voluntaryworkers
	            $newentry = array();
	            $newentry['group'] = $this->view->translate('voluntaryworkers');
	            
	            $pvw = new PatientVoluntaryworkers();
	            $pvwarray = $pvw->getPatientVoluntaryworkers($patientarr['ipid']);
	            $lastelem = end($pvwarray);
	            
	            $overall_data['PatientVoluntaryworkers'] = $pvwarray;
	            
	            if (is_array($pvwarray)){
	             	$newentry['text'] = "";
	             	foreach ($pvwarray as $vw)
	             	{
	             		if($vw['last_name'] != '')
	             		{
	             			if ($vw['first_name'] != '')
	             			{
	             				$namep = $vw['last_name'].", ".$vw['first_name'];
	             			}
	             			else
	             			{
	             				$namep = $vw['last_name'];
	             			}
	             		}
	             		else
	             		{
	             			if ($vw['first_name'] != '')
	             			{
	             				$namep = $vw['first_name'];
	             			}
	             			else
	             			{
	             				$namep = '';
	             			}
	             		}

	             		$newentry['text'] .= $namep ."<br/>";
			            $newentry['text'] .= $vw['street'] ." ". $vw['zip'] ." ". $vw['city']."<br/>";
			            if($vw['phone'] != '')
			            {
			            	$newentry['text'] .= "Tel.: ".$vw['phone'];
			            	if($vw['mobile'] != '')
			            	{
			            		$newentry['text'] .= ", ";
			            	}
			            	else
			            	{
			            		if($vw != $lastelem)
			            		{
			            			$newentry['text'] .= "<br/><br/>";
			            		}
			            	}
			            	}
			            	else
			            	{
			            		if($vw['mobile'] == '')
			            		{
			            			if($vw != $lastelem)
			            			{
			            				$newentry['text'] .= "<br/><br/>";
			            			}
			            		}
			            	}
			            	if($vw['mobile'] != '')
			            	{
			            		if($vw != $lastelem)
			            		{
			            			$newentry['text'] .= "Hdy.: ".$vw['mobile']."<br/><br/>";
			            		}
			            		else 
			            		{
			            			$newentry['text'] .= "Hdy.: ".$vw['mobile'];
			            		}
			            	}
		             } 	
	            }
	            $fachstellen['voluntaryworkers'] = $newentry;
	            
	            
	            //br 2 nl 
	            foreach($fachstellen as $k=>$v){ 
// 	            	$fachstellen[$k] ['text'] =  htmlentities( preg_replace('/\<br(\s*)?\/?\>/i', "\n", $v['text'])) ;
	            	$fachstellen[$k] ['text'] =  preg_replace('/\<br(\s*)?\/?\>/i', "\n", $v['text']) ;
	            	
	            }
            }
            
 
            /*--------------Patient Contact persons Data ----------------------------*/
            $pc = new  ContactPersonMaster();
            $pcs = $pc->getPatientContact($ipid);
       
            $angeh="";
            $contacts_names = array();
            foreach($pcs as $contact){
                $contacts_names[$contact['id']] = $contact['cnt_last_name'].', '.$contact['cnt_first_name'];
                
                $tmp_n=$contact['cnt_first_name'].' '.$contact['cnt_last_name'];
                $tmp_s=$contact['cnt_street1'] .", ". $contact['cnt_zip'] ." ". $contact['cnt_city'];
                $tmp_s=trim($tmp_s);
                if ($tmp_s){
                    $tmp_n=$tmp_n . ", " . $tmp_s;
                }
                $tmp_t=$contact['cnt_phone'];
                $tmp_m=$contact['cnt_mobile'];
                $tmp_p="";
                if ($tmp_t){
                    $tmp_p="Tel.: ".$tmp_t;
                }
                if ($tmp_p && $tmp_m){
                    $tmp_p.=", ";
                }
                if ($tmp_m){
                    $tmp_p.="Hdy.: ".$contact['cnt_mobile'];
                }
                if($tmp_p){
                    $tmp_n.=", ".$tmp_p;
                }
                $tmp_n.="\n";
                $angeh.=$tmp_n;
            }
        
            $this->view->angehorige=$angeh;
            
            /*--------------Patient GET ACP DATA ----------------------------*/
            
            // ACP
            $acp = new PatientAcp();
            $acp_data = $acp->getByIpid(array($ipid));
            $current_acp_data = $acp_data[$ipid];

            if ( ! empty($current_acp_data)) {
                foreach ($current_acp_data as $k => $block) {
            
                    switch ($block['division_tab']) {
            
                        case "living_will" :
                            $this->view->patientenverfugung = ($block['active'] == "yes") ? "1" : "0";
                            break;
            
                        case "healthcare_proxy" :
                            $this->view->bevollmachtigter = ($block['active'] == "yes" && !empty($block['contactperson_master_id'])) ? $contacts_names[$block['contactperson_master_id']] : "";
                            break;
                    }
                }
            }
            
            
            /*-------------------Patient Diagnosis  -------------------------------*/
            $abb = "'HD','ND','AD','DD'";
            $dg = new DiagnosisType();
            $ddarr = $dg->getDiagnosisTypes($clientid,$abb);
            if(!$ddarr[0]['id']){$ddarr[0]['id']=0;}
            $comma="";
            $other_diagnosis=array();
            $other_diagnosis['HD']="";
            $other_diagnosis['ND']="";
            foreach($ddarr as $key1=>$val1)
            {
        
                $diagcat="HD";
                if($val1['abbrevation']=="ND"){
                    $diagcat="ND";
                }
                //echo $val1['id'];
                $stam_diagno = array();
                $diagns = new PatientDiagnosis();
             	$stam_diagno = $diagns->getFinalData($ipid,$val1['id']);
       
                foreach($stam_diagno as $key=>$val)
                {
                    $comma="";
                    if($other_diagnosis[$diagcat]){
                        $comma=", ";
                    }
                    if(strlen($val['diagnosis'])>0)
                    {
                        if(strlen($val['icdnumber'])>0)
                        {
                            $other_diagnosis[$diagcat] .= $comma.$val['diagnosis'].' ('.$val['icdnumber'].')';
                        }else{
                            $other_diagnosis[$diagcat] .= $comma.$val['diagnosis'];
                        }
        
                    }
                }
            }
            $this->view->diagnosis = $other_diagnosis['HD'];
            if($other_diagnosis['HD'] && $other_diagnosis['ND']){
                $this->view->diagnosis.= ", ";
            }
            $this->view->diagnosis.=$other_diagnosis['ND'];
            
            //dd($fachstellen);
            $this->view->fachdienste=$fachstellen;
        
            /*-------------------PDF Actions--------------------------------*/
        
            if(strlen($_POST['btnsubmit'])>0)
            {
                $fdienste=$_POST['fachdienst_entry'];
                $post = $_POST;
                $this->retainValues($post);
        
                $post['fachdienst_entry']=$fdienste;
                $post['birth'] = $this->view->birth;
                $post['lastname'] =$this->view->lastname;
                $post['firstname'] =$this->view->firstname;
                $post['street'] =$this->view->street;
                $post['zip'] =$this->view->zip;
                $post['patcity'] =$this->view->patcity;
                $post['pattel'] =$this->view->pattel;
                $post['pathandy'] =$this->view->pathandy;
                $post['cntpers2name'] = $cntpers2name;
                $post['hausarzt_details'] =	$this->view->hausarzt_details;
                $post['pflegedienst_details'] =	$this->view->pflegedienst_details;
                $post['pharmacy_details'] =	$this->view->pharmacy_details;
                $post['healthinsurance_company'] =	$this->view->healthinsurance_company;
                $post['healthinsurance_versnr'] =	$this->view->healthinsurance_versnr ;
                $post['diagnosis'] =	$this->view->diagnosis ;
                $post['therapy'] =	$this->view->therapy ;
                $post['angehorige'] =	$_POST['angehorige'];
                $post['wirdversorgt'] =	$_POST['wirdversorgt'];
                $this->generatePdfNew(3,$post,'Stammblatt',"stammblatt_lmu_pdf.html");
        
            } else{
                
                /* ----------------------Get master group for user------------------------------ */
                $doctorgroups = array("4", "9"); //4 - Arzt, 9 - Hausarzt
                $nursegroups = array("5"); //5-Pflege
                $doc_nurse_groups = array("4", "9", "5"); //5-Pflege
                
                $userdata = Pms_CommonData::getUserData($logininfo->userid);
                $groupid = $userdata[0]['groupid'];
                
                $ug = new Usergroup();
                
                $usergroup = new Usergroup();
                $usersgroups = $usergroup->getUserGroups($doc_nurse_groups);
                foreach($usersgroups as $k=>$groupdata){
                    if(in_array($groupdata['groupmaster'],$doctorgroups)){
                        $users_groups['docs'][] = $groupdata['id']; 
                    }
                    elseif(in_array($groupdata['groupmaster'],$nursegroups)){
                        $users_groups['nurses'][] = $groupdata['id']; 
                    }
                }
                
                $assigneddocs = array();
                $assignednurses = array();
                
                $assigned_users= PatientQpaMapping::get_patient_assigned_doctors(array($epid), $clientid, "details");
                foreach($assigned_users as $epid=> $user_array){
                    foreach($user_array as $k=>$user_data){
                        if(in_array($user_data['groupid'],$users_groups['docs'])){
                            $assigneddocs[] = $user_data;
                        }
                        elseif(in_array($user_data['groupid'],$users_groups['nurses'])){
                            $assignednurses[] = $user_data;
                        }
                    }
                }

                $names="";
                foreach ($assigneddocs as $doc){
                    if($doc['title']){
                        if ($doc['title']=="Frau"){
                            $name= $name .$doc['title']." ".$doc['first_name'] . " " . $doc['last_name']."";
                        } else{
                            $name= $name .$doc['title']." ".$doc['first_name'] . " " . $doc['last_name']."";
                        }
                    } else{
                        $name= $name ." ".$doc['first_name'] . " " . $doc['last_name']."";
                    }
        
                    if($doc['phone']){
                        $name = $name . ", Tel.: " . $doc['phone'];
                    }
                    
                    if(strlen($doc['mobile'])>0){
                        $name = $name . ", ".$this->view->translate('mobile').": ". $doc['mobile'];
                    }
                    if ($name){
                    	if($names == "")
                    	{
                    		$names=$names . $name;
                    	}
                    	else 
                    	{
                        	$names=$names . "\n" . $name;
                    	}
                    	$name = "";
                    }
                }
                foreach ($assignednurses as $nurse){
                    $name=$nurse['first_name'] . " " . $nurse['last_name'];
                    if($nurse['title']){
                        $name = $nurse['title'] . " " . $name;
                    }
                    $name=$name.", Palliative Care Fachkraft ";
                    if($nurse['phone']){
                        $name = $name . ", Tel.: " . $nurse['phone'];
                    }
                    if(strlen($nurse['mobile'])>0){
                        $name = $name . ", ".$this->view->translate('mobile').": ". $nurse['mobile'];
                    }
                    if ($name){
                        $names=$names . "\n" . $name;
                    }
                }
                $this->view->morephones=$names;
        
                //load saved formdata
                if($_GET['stbl'] && isset($old_forms[(int)$_GET['stbl']]))
                {
                    //$olddata = Stammblattlmu::get_entry($ipid,$_GET['stbl']);
                    $olddata = $old_forms[ (int)$_GET['stbl'] ];
                    
                    $this->view->pattel = $olddata['pattel'];
                    $this->view->cntpers1name = $olddata['cntpers1name'];
                    $this->view->cntpers1tel = $olddata['cntpers1tel'];
                    $this->view->cntpers1handy = $olddata['cntpers1handy'];
                    $this->view->patientenverfugung = $olddata['patientenverfugung'];
                    $this->view->bevollmachtigter = $olddata['bevollmachtigter'];
                    $this->view->angehorige = $olddata['angehorige'];
                    $this->view->diagnosis = $olddata['diagnosis'];
                    $this->view->therapy = $olddata['therapy'];
                    $this->view->wirdversorgt = $olddata['wirdversorgt'];
                    $this->view->notruf = $olddata['notruf'];
                    $this->view->morephones = $olddata['morephones'];
                    $this->view->client_working_schedule = $olddata['client_working_schedule'];
                    $this->view->fachdienste = unserialize($olddata['fachdienst_entry']);
                }
            }
        }
        
        
        private function generateformPdf($chk, $post, $pdfname, $filename)
        {
            $logininfo = ($this->logininfo !== false) ? $this->logininfo : new Zend_Session_Namespace('Login_Info');
            $decid = ($this->dec_id !== false) ? $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
            $ipid = ($this->ipid!== false) ? $this->ipid : Pms_CommonData::getIpid($decid);
            
            $post = Pms_CommonData::clear_pdf_data($post, array('patient_name'));
            $post['ipid'] = $ipid; //Pms_CommonData::getIpid($decid);
        
            $patientmaster = new PatientMaster();
            $parr = $patientmaster->getMasterData($decid, 0);
        
            $post['patientname'] = htmlspecialchars($parr['last_name']) . ", " . htmlspecialchars($parr['first_name']) . "<br>" . htmlspecialchars($parr['street1']) . "<br>" . htmlspecialchars($parr['zip']) . "&nbsp;" . htmlspecialchars($parr['city']);
        
            if($parr['sex'] == 1)
            {
                $this->view->male = "checked='checked'";
            }
        
            if($parr['sex'] == 2)
            {
                $this->view->female = "checked='checked'";
            }
        
            $ref = Doctrine::getTable('PatientReferredBy')->find($parr['referred_by']);
            $this->view->refarray = $ref['referred_name'];
        
            $epid = ($this->epid !== false) ? $this->epid : Pms_CommonData::getEpidFromId($decid);
            $this->view->epid = $epid;
        
            $loguser = Doctrine::getTable('User')->find($logininfo->userid);
        
            if($loguser)
            {
                $loguserarray = $loguser->toArray();
                $this->view->lastname = $loguserarray['last_name'];
                $this->view->firstname = $loguserarray['first_name'];
            }
        
            $clientdata = Pms_CommonData::getClientData($logininfo->clientid);
            $post['clientname'] = $clientdata[0]['clientname'];
            $post['clientfax'] = $clientdata[0]['fax'];
            $post['clientphone'] = $clientdata[0]['phone'];
            $post['clientemail'] = $clientdata[0]['emailid'];
        
            $post['sapsymp'] = Sapsymptom::get_patient_sapvsymptom(Pms_CommonData::getIpid($decid));
        
            $patientmaster = new PatientMaster();
            $patientinfo = $patientmaster->getMasterData($decid, 0);
        
            $post['bdate'] = $patientinfo['birthd'];
        
            $phelathinsurance = new PatientHealthInsurance();
            $healthinsu_array = $phelathinsurance->getPatientHealthInsurance($patientinfo['ipid']);
        
            $post['insurance_company_name'] = $healthinsu_array[0]['company_name'];
            $post['insurance_no'] = $healthinsu_array[0]['insurance_no'];
            $post['insurance_status'] = $healthinsu_array[0]['insurance_status'];
        
            $hquery = Doctrine_Query::create()
            ->select('*')
            ->from('HealthInsurance')
            ->where("id='" . $healthinsu_array[0]['companyid'] . "' or name='" . htmlentities($healthinsu_array[0]['company_name'], ENT_QUOTES) . "'");
            $hexec = $hquery->execute();
            $harray = $hexec->toArray();
            $post['kvnumber'] = $harray[0]['kvnumber'];
        
            /* analage3 */
            $patientmaster = new PatientMaster();
            $this->view->patientinfo = $patientmaster->getMasterData($decid, 1); // strike 3 ! and youre out
        
            $tm = new TabMenus();
            $this->view->tabmenus = $tm->getMenuTabs();
        
            $post['tablepatientinfo'] = Pms_Template::createTemplate($parr, 'templates/pdfprofile.html');
            $post['tag'] = date("d");
            $post['month'] = date("m");
            $post['jahr'] = date("Y");
        
            $htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
        
            $pdf = new Pms_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator('IPSC');
            $pdf->SetAuthor('ISPC');
            $pdf->SetTitle('ISPC');
            $pdf->SetSubject('ISPC');
            $pdf->SetKeywords('ISPC');
            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            //set margins
            	
            $pdf->SetMargins(30, 10, 30);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 10);
            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            //set some language-dependent strings
            $pdf->setLanguageArray('de');
            // set font
            $pdf->SetFont('times', '', 10);
            // add a page
            $pdf->AddPage('P', 'A4');
            //print_r($htmlform); exit;
            $pdf->writeHTML($htmlform, true, 0, true, 0);
        
            if($chk == 1)
            {
//                 $tmpstmp = time();
//                 mkdir("uploads/" . $tmpstmp);
                $tmpstmp = Pms_PDF :: uniqfolder(PDF_PATH);
                $pdf->Output(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
                $_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
//                 $cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
//                 exec($cmd);
                $zipname = $tmpstmp . ".zip";
                $filename = "uploads/" . $tmpstmp . ".zip";
                /*
                $con_id = Pms_FtpFileupload::ftpconnect();
                if($con_id)
                {
                    $upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
                    Pms_FtpFileupload::ftpconclose($con_id);
                }
                */
                $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue ( PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , "uploads" );
                
            }
        
            if($chk == 2)
            {
                ob_end_clean();
                ob_start();
                $pdf->Output($pdfname . '.pdf', 'I');
                exit;
            }
        
            if($chk == 3)
            {
        
                $navnames = array("SAPV_fanfrage" => $this->view->translate('sapv_fanfrage'),
                    "Uberleitungsbogen" => $this->view->translate('uberleitungsbogen'),
                    "Verordnung" => $this->view->translate('verordnung'),
                    "verordnungtp" => $this->view->translate('verordnungtp'),
                    "verordnungtpside" => $this->view->translate('verordnungtpside'),
                    "munster4" => $this->view->translate('munster4'),
                    "Palliativ_versorgung_a7" => $this->view->translate('palliativ_versorgung_a7'),
                    "folgeverordnung" => $this->view->translate('folgeverordnung'),
                    "Form_one" => $this->view->translate('form_one'),
                    "Form_two" => $this->view->translate('form_two'),
                    "Anlage_4(Teil 1)" => $this->view->translate('anlage_4teil'),
                    "Anlage4wl" => $this->view->translate('Anlage 4 WL'),
                    "formthree" => $this->view->translate('formthree'),
                    "Stammblatt" => $this->view->translate('stammblatt'),
                    "hopeform" => $this->view->translate('hopeform'),
                    "formfour" => $this->view->translate('formfour'),
                    "formfive" => $this->view->translate('formfive'),
                    "SAPVF_B3" => $this->view->translate('sapvf_b3'),
                    "SAPVF_B4" => $this->view->translate('sapvf_b4'),
                    "SAPVF_B5" => $this->view->translate('sapvf_b5'),
                    "SAPVF_B12" => $this->view->translate('sapvf_b12'),
                    "SAPVF_B8" => $this->view->translate('sapvf_b8'));
        
        
                if($pdfname == "verordnungtp")
                {
                    $pdf->SetAutoPageBreak(true, 0);
                }
//                 $tmpstmp = time();
//                 mkdir("uploads/" . $tmpstmp);
                $tmpstmp = Pms_PDF :: uniqfolder(PDF_PATH);
//                 $pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
                $pdf->Output(PDF_PATH. '/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
        
                $_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
//                 $cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
//                 exec($cmd);
        
                //moved from sapvfb8_paging.html
                unset($_SESSION['final1'], $_SESSION['final2'], $_SESSION['final3'], $_SESSION['final4'], $_SESSION['final5'], $_SESSION['final6'], $_SESSION['final7'], $_SESSION['final8'], $_SESSION['final9'], $_SESSION['final10'], $_SESSION['final11']);
                $zipname = $tmpstmp . ".zip";
                $filename = "uploads/" . $tmpstmp . ".zip";
                /*
                $con_id = Pms_FtpFileupload::ftpconnect();
        
                if($con_id)
                {
                    $upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
                    Pms_FtpFileupload::ftpconclose($con_id);
                }
                */
                $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue ( PDF_PATH. '/' . $tmpstmp . '/' . $pdfname . '.pdf' , "uploads" );
                
        
                $cust = new PatientFileUpload();
                $cust->title = Pms_CommonData::aesEncrypt(addslashes($navnames[$pdfname]));
                $cust->ipid = $ipid;
                $cust->file_name = Pms_CommonData::aesEncrypt($_SESSION['filename']); //$post['fileinfo']['filename']['name'];
                $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
                $cust->system_generated = "1";
                $cust->save();
                $recordid = $cust->id;
        
                if($pdfname == "verordnungtp")
                {
                    //insert system file tags
                    $insert_tag = Application_Form_PatientFile2tags::insert_file_tags($recordid, array('5'));
                }
                else if($pdfname == "Stammblatt")
                {
                    //insert system file tags
                    $insert_tag = Application_Form_PatientFile2tags::insert_file_tags($recordid, array('8'));
                }
        
                $cust = new PatientCourse();
                $cust->ipid = $ipid;
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                $cust->course_title = Pms_CommonData::aesEncrypt(addslashes('Formular ' . $navnames[$pdfname] . ' wurde erstellt'));
                $cust->user_id = $logininfo->userid;
                $cust->save();
        
                ob_end_clean();
                ob_start();
                $pdf->Output($pdfname . '.pdf', 'D');
                exit;
            }
        }
        
        
        
        public function hospizregisterAction(){
        
            $clientid = $this->clientid;
            $userid = $this->userid;
            $decid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpId($decid);
            
            /* ISPC-1775,ISPC-1678 */
            /* ================ PATIENT HEADER ======================= */
            $patientmaster = new PatientMaster();
            $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
            
            /* ================ PATIENT TAB MENU ======================= */
            $tm = new TabMenus();
            $this->view->tabmenus = $tm->getMenuTabs();
            
            $patientarr = $patientmaster->getMasterData($decid,0);
            
            
            if($patientarr['isdischarged'] == 1)
            {
                
                $patdis = Doctrine_Query::create()
                ->select("*")
                ->from('PatientDischarge')
                ->where("ipid='" . $ipid . "'")
                ->andWhere('isdelete=0');
                $patdisarr = $patdis->fetchArray();
                
                if(!empty($patdisarr))
                {
                    $this->view->entlasung_date = date("d.m.Y", strtotime($patdisarr[0]['discharge_date']));
                    $entlasung_date = date("d.m.Y", strtotime($patdisarr[0]['discharge_date']));
                    $dis = Doctrine_Core::getTable('DischargeMethod')->findBy('id', $patdisarr[0]['discharge_method']);
                    $disarr = $dis->toArray();
                    $abbrev = strtolower($disarr[0]['abbr']);
                    
                    if($abbrev == "tod")
                    {
                        $disloc = new DischargeLocation();
                        $dislocarr = $disloc->getDischargeLocationbyId($patdisarr[0]['discharge_location'], $logininfo->clientid);
                
                        $dgp['kern']['discharge']['sterbeort_dgp'] = $dislocarr[0]['id'];
                        
                        $death_date = date("d.m.Y", strtotime($patdisarr[0]['discharge_date']));
                        
                    }
                    else
                    {
                        $dgp['kern']['discharge']['sterbeort_dgp'] = "";
                        $death_date = "0000.00.00";
                    }
                    
                    if($patdisarr[0]['discharge_location']!= "0")
                    {
                        $disl = Doctrine_Core::getTable('DischargeLocation')->findBy('id', $patdisarr[0]['discharge_location']);
                        $dislarr = $disl->toArray();
                        
                        if(!empty($dislarr)){
                            if($dislarr[0]['type'] == "6"){
                                $dgp['kern']['discharge']['wohnsituation'] = "2";
                            }
                        }
                            
                        
                    }
                }
                else
                {
                    $entlasung_date = date("d.m.Y");
                    $dgp['kern']['discharge']['sterbeort_dgp'] = "";
                }
            }

            $dgp['kern']['admission']['datum_der_erfassung1'] = date('d.m.Y');
            
            
            /*Wohnsituation:
            "alleine" = location (zu Hause, alleine)
            "im haus der Angehörigen" = admission in location at contact person / discharge in location at contact person
            "Heim" = all location which have status "Altenheim" or" Pflegeheim"
            "sonstiges" = everything else
            */
            
            $sql = 'e.epid, p.ipid, e.ipid,';
            $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
            $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
            $sql .= "AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as gender,";
            $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
            $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
            $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
            $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
            $sql .= "IF(p.admission_date != '0000-00-00',DATE_FORMAT(p.admission_date,'%d\.%m\.%Y'),'') as day_of_admission,";
            $sql .= "IF(p.birthd != '0000-00-00',DATE_FORMAT(p.birthd,'%d\.%m\.%Y'),'') as birthd,";
            
            $conditions['periods'][0]['start'] = '2009-01-01';
            $conditions['periods'][0]['end'] = date('Y-m-d');
            $conditions['client'] = $clientid;
            $conditions['include_standby'] = true;
            $conditions['ipids'] = array($ipid);
            
            $patient_days = Pms_CommonData::patients_days($conditions,$sql);
            
            
            $around_admission_days="";
            $around_admission_date['start']  = date("d.m.Y",strtotime("-3 days",strtotime( $patient_days[$ipid]['details']['admission_date'])));
            $around_admission_date['end']  = date("d.m.Y",strtotime("+3 days",strtotime( $patient_days[$ipid]['details']['admission_date'])));
            $around_admission_days = $patientmaster->getDaysInBetween($around_admission_date['start'],$around_admission_date['end'],false,"d.m.Y");
            
            $around_admission_days_start = $patientmaster->getDaysInBetween($patient_days[$ipid]['details']['day_of_admission'],$around_admission_date['end'],false,"d.m.Y");
            
            /* ----------------- Patient Details -Admission location----------------------------------------- */
            $ploc = new PatientLocation();
             
            $patient_locations = new PatientLocation();
            $patient_period_locations = $patient_locations->getPatientLocations($ipid,true);
           
            foreach($patient_period_locations as $k=>$location_details){
                $location_start = date("d.m.Y",strtotime($location_details['valid_from']));
                if(in_array($location_start,$around_admission_days_start) )
                {
                    $admission_location[] = $location_details;
                }
                
                $all_location[] = $location_details;
            }
            
            if($admission_location)
            {
                $first_location = $admission_location[0];
            
                if($first_location['master_location']['location_type'] == "6")
                {
                    $dgp['kern']['admission']['wohnsituation'] = "2";
                }
                else if($first_location['master_location']['location_type'] == "5")
                {
                    if($first_location['master_location']['location_sub_type'] == "alone")
                    {
                        $dgp['kern']['admission']['wohnsituation'] = "1";
                    } 
                    else 
                    {
                        $dgp['kern']['admission']['wohnsituation'] = "6";
                    }
                } 
                else if($first_location['master_location']['location_type'] == "3" || $first_location['master_location']['location_type'] == "4" ) 
                {
                    $dgp['kern']['admission']['wohnsituation'] = "4";
                } 
                else 
                {
                    $dgp['kern']['admission']['wohnsituation'] = "6";
                }
            }
            
            
            
            if($all_location)
            {
                $last_location = end($all_location);
                 
                if($last_location['master_location']['location_type'] == "6")
                {
                    //$this->view->house_of_relatives = "1"; // house_of_relatives -  im Haus der Angehörigen
                    $dgp['kern']['discharge']['wohnsituation'] = "2";
                }
                else if($last_location['master_location']['location_type'] == "5")
                {
                    if($last_location['master_location']['location_sub_type'] == "alone")
                    {
                        //$this->view->alone = "1";//alone
                        $dgp['kern']['discharge']['wohnsituation'] = "1";
                    } else {
                        //$this->view->sonstiges="1"; // sonstiges
                        $dgp['kern']['discharge']['wohnsituation'] = "6";
                    }
            
                }else if($last_location['master_location']['location_type'] == "3" || $last_location['master_location']['location_type'] == "4" )
                {
                    //$this->view->home = "1";// home
                    $dgp['kern']['discharge']['wohnsituation'] = "4";
                } else {
                    //$this->view->sonstiges="1"; // sonstiges
                    $dgp['kern']['discharge']['wohnsituation'] = "6";
                }
            }
            
            
            // get ecog details 
            $ecog_value['first'] = $patientmaster->ecog_values($ipid,true);
            $ecog_value['last']= $patientmaster->ecog_values($ipid,false,true);
            
            // Symptomatology 
            $symp = new Symptomatology();
            $first_symptomarr = $symp->getPatientSymptpomatologyFirst($ipid);

            foreach($first_symptomarr as $k=>$value) {
                $symp_first_values[$value['symptomid']] = $value['input_value']; 
            }
            
            $symptomarr = $symp->getPatientSymptpomatologyLast($ipid);
            foreach($symptomarr as $k=>$value) {
                $symp_last_values[$value['symptomid']] = $value['input_value']; 
            }
            
            /* ------------------Patient->  assesment Symptomatics------------------ */
            $assesment = Doctrine_Core::getTable('KvnoAssessment')->findBy('ipid', $ipid);
            $assesmentarr = $assesment->toArray();
            
            $dgp['kern']['admission']['ecog'] = $ecog_value['first']; 
            
            $dgp['kern']['admission']['sapvteam_as'] = $assesmentarr[0]['sapvteam'];
            $dgp['kern']['admission']['hausarzt'] = $assesmentarr[0]['hausarzt'];
            $dgp['kern']['admission']['pflege'] = $assesmentarr[0]['pflege'];
            $dgp['kern']['admission']['palliativ'] = $assesmentarr[0]['palliativ'];
            $dgp['kern']['admission']['palliativpf'] = $assesmentarr[0]['palliativpf'];
            $dgp['kern']['admission']['palliativber'] = $assesmentarr[0]['palliativber'];
            $dgp['kern']['admission']['dienst'] = $assesmentarr[0]['dienst'];
            
            
            $sym_relevant_values = array('0' =>'1','1'=>'2','2'=>'2','3'=>'2','4'=>'2','5'=>'3','6'=>'3','7'=>'3','8'=>'4','9'=>'4','10'=>'4');
            
            $dgp['kern']['admission']['schmerzen'] = $sym_relevant_values[$symp_first_values["1"]];
            $dgp['kern']['admission']['ubelkeit'] = $sym_relevant_values[$symp_first_values["2"]];
            $dgp['kern']['admission']['erbrechen'] = $sym_relevant_values[$symp_first_values["4"]];
            $dgp['kern']['admission']['luftnot'] = $sym_relevant_values[$symp_first_values["5"]];
            $dgp['kern']['admission']['verstopfung'] = $sym_relevant_values[$symp_first_values["6"]];
            $dgp['kern']['admission']['swache'] = $sym_relevant_values[$symp_first_values["7"]];;
            $dgp['kern']['admission']['appetitmangel'] = $sym_relevant_values[$symp_first_values["8"]];
            $dgp['kern']['admission']['dekubitus'] = $sym_relevant_values[$symp_first_values["10"]];
            $dgp['kern']['admission']['hilfebedarf'] = $sym_relevant_values[$symp_first_values["11"]];
            $dgp['kern']['admission']['depresiv'] = $sym_relevant_values[$symp_first_values["12"]];
            $dgp['kern']['admission']['angst'] = $sym_relevant_values[$symp_first_values["13"]];
            $dgp['kern']['admission']['anspannung'] = $sym_relevant_values[$symp_first_values["14"]];
            $dgp['kern']['admission']['desorientier'] = $sym_relevant_values[$symp_first_values["15"]];
            $dgp['kern']['admission']['versorgung'] = $sym_relevant_values[$symp_first_values["16"]];
            $dgp['kern']['admission']['umfelds'] = $sym_relevant_values[$symp_first_values["17"]];
            $dgp['kern']['admission']['sonstige_probleme'] = $sym_relevant_values[$symp_first_values["18"]];
            
            
            $dgp['kern']['discharge']['ecog'] =  $ecog_value['last']; // last ecog ever from last visits
            
            
            $dgp['kern']['discharge']['sapvteam_as'] = $assesmentarr[0]['sapvteam'];
            $dgp['kern']['discharge']['hausarzt'] = $assesmentarr[0]['hausarzt'];
            $dgp['kern']['discharge']['pflege'] = $assesmentarr[0]['pflege'];
            $dgp['kern']['discharge']['palliativ'] = $assesmentarr[0]['palliativ'];
            $dgp['kern']['discharge']['palliativpf'] = $assesmentarr[0]['palliativpf'];
            $dgp['kern']['discharge']['palliativber'] = $assesmentarr[0]['palliativber'];
            $dgp['kern']['discharge']['dienst'] = $assesmentarr[0]['dienst'];

            
            $dgp['kern']['discharge']['schmerzen'] =  $sym_relevant_values[$symp_last_values["1"]];
            $dgp['kern']['discharge']['ubelkeit'] = $sym_relevant_values[$symp_last_values["2"]];
            $dgp['kern']['discharge']['erbrechen'] = $sym_relevant_values[$symp_last_values["4"]];
            $dgp['kern']['discharge']['luftnot'] = $sym_relevant_values[$symp_last_values["5"]];
            $dgp['kern']['discharge']['verstopfung'] = $sym_relevant_values[$symp_last_values["6"]];
            $dgp['kern']['discharge']['swache'] = $sym_relevant_values[$symp_last_values["7"]];;
            $dgp['kern']['discharge']['appetitmangel'] = $sym_relevant_values[$symp_last_values["8"]];
            $dgp['kern']['discharge']['dekubitus'] = $sym_relevant_values[$symp_last_values["10"]];
            $dgp['kern']['discharge']['hilfebedarf'] = $sym_relevant_values[$symp_last_values["11"]];
            $dgp['kern']['discharge']['depresiv'] = $sym_relevant_values[$symp_last_values["12"]];
            $dgp['kern']['discharge']['angst'] = $sym_relevant_values[$symp_last_values["13"]];
            $dgp['kern']['discharge']['anspannung'] = $sym_relevant_values[$symp_last_values["14"]];
            $dgp['kern']['discharge']['desorientier'] = $sym_relevant_values[$symp_last_values["15"]];
            $dgp['kern']['discharge']['versorgung'] = $sym_relevant_values[$symp_last_values["16"]];
            $dgp['kern']['discharge']['umfelds'] = $sym_relevant_values[$symp_last_values["17"]];
            $dgp['kern']['discharge']['sonstige_probleme'] = $sym_relevant_values[$symp_last_values["18"]];
            
            
            //get all patients sapvs
            $sapv = new SapvVerordnung();
            $sapvarray = $sapv->getPatientSapvVerordnungDetails($ipid, true);
            $patient_sapv_array = $sapvarray[$ipid];
            
            // get saved kern froms
            
            //get first and last kvno data
            $patient_saved_kern_q = Doctrine_Query::create()
            ->select('*')
            ->from('DgpKern ka')
            ->where('ka.ipid = "'.$ipid.'"  ')
            ->orderby('create_date asc');
            $patient_saved_kern_arr = $patient_saved_kern_q->fetchArray();

            if(!empty($patient_saved_kern_arr))
            {
                $dgp['kern']['admission'] = $patient_saved_kern_arr[0];
                $dgp['kern']['admission']['dgp_kern_id'] = $patient_saved_kern_arr[0]['id'];
                
                $begleitung = explode(",", $dgp['kern']['admission']['begleitung']);
                
                if(in_array('1', $begleitung))
                {
                   $dgp['kern']['admission']['sapvteam_as'] = '1';
                }
                
                if(in_array('2', $begleitung))
                {
                    $dgp['kern']['admission']['hausarzt'] = '1';
                }
                
                if(in_array('3', $begleitung))
                {
                    $dgp['kern']['admission']['pflege'] = '1';
                }
                
                if(in_array('4', $begleitung))
                {
                    $dgp['kern']['admission']['palliativ'] = '1';
                }
                
                if(in_array('5', $begleitung))
                {
                    $dgp['kern']['admission']['palliativpf'] = '1';
                }
                
                if(in_array('6', $begleitung))
                {
                    $dgp['kern']['admission']['palliativber'] = '1';
                }
                
                if(in_array('7', $begleitung))
                {
                    $dgp['kern']['admission']['dienst'] = '1';
                }
                
                if(in_array('8', $begleitung))
                {
                    $dgp['kern']['admission']['palliativstation'] = '1';
                }
                
                if(in_array('9', $begleitung))
                {
                    $dgp['kern']['admission']['hospiz_stationaer'] = '1';
                }
                
                if(in_array('10', $begleitung))
                {
                    $dgp['kern']['admission']['kh_andere_station'] = '1';
                }
                
                if(in_array('11', $begleitung))
                {
                    $dgp['kern']['admission']['heim'] = '1';
                }
                
                if(in_array('12', $begleitung))
                {
                    $dgp['kern']['admission']['palliative_care_team'] = '1';
                }
                
                if(in_array('13', $begleitung))
                {
                    $dgp['kern']['admission']['sonstige_behandlung'] = '1';
                }
                
                
                $dgp['kern']['admission']['wohnsituation'] = $patient_saved_kern_arr[0]['wohnsituation'];
                
                $dgp['kern']['admission']['schmerzen'] = $patient_saved_kern_arr[0]['schmerzen'];
                $dgp['kern']['admission']['ubelkeit'] = $patient_saved_kern_arr[0]['ubelkeit'];
                $dgp['kern']['admission']['erbrechen'] = $patient_saved_kern_arr[0]['erbrechen'];
                $dgp['kern']['admission']['luftnot'] = $patient_saved_kern_arr[0]['luftnot'];
                $dgp['kern']['admission']['verstopfung'] = $patient_saved_kern_arr[0]['verstopfung'];
                $dgp['kern']['admission']['swache'] = $patient_saved_kern_arr[0]['swache'];
                $dgp['kern']['admission']['appetitmangel'] = $patient_saved_kern_arr[0]['appetitmangel'];
                $dgp['kern']['admission']['dekubitus'] = $patient_saved_kern_arr[0]['dekubitus'];
                $dgp['kern']['admission']['hilfebedarf'] = $patient_saved_kern_arr[0]['hilfebedarf'];
                $dgp['kern']['admission']['depresiv'] = $patient_saved_kern_arr[0]['depresiv'];
                $dgp['kern']['admission']['angst'] = $patient_saved_kern_arr[0]['angst'];
                $dgp['kern']['admission']['anspannung'] = $patient_saved_kern_arr[0]['anspannung'];
                $dgp['kern']['admission']['desorientier'] = $patient_saved_kern_arr[0]['desorientier'];
                $dgp['kern']['admission']['versorgung'] = $patient_saved_kern_arr[0]['versorgung'];
                $dgp['kern']['admission']['umfelds'] = $patient_saved_kern_arr[0]['umfelds'];
                $dgp['kern']['admission']['sonstige_probleme'] = $patient_saved_kern_arr[0]['sonstige_probleme'];
                
                $dgp['kern']['admission']['aufwand_mit'] = $patient_saved_kern_arr[0]['aufwand_mit'];
                
                
                if(count($patient_saved_kern_arr) > 1)
                {
                   $kern_discharge_array = end($patient_saved_kern_arr);
                   $dgp['kern']['discharge'] = $kern_discharge_array ;
                   $dgp['kern']['discharge']['dgp_kern_id'] = $kern_discharge_array['id'] ;
                   
                   
                   $dgp['kern']['discharge']['wohnsituation'] = $kern_discharge_array['wohnsituation'];
                   $dgp['kern']['discharge']['schmerzen'] = $kern_discharge_array['schmerzen'];
                   $dgp['kern']['discharge']['ubelkeit'] = $kern_discharge_array['ubelkeit'];
                   $dgp['kern']['discharge']['erbrechen'] = $kern_discharge_array['erbrechen'];
                   $dgp['kern']['discharge']['luftnot'] = $kern_discharge_array['luftnot'];
                   $dgp['kern']['discharge']['verstopfung'] = $kern_discharge_array['verstopfung'];
                   $dgp['kern']['discharge']['swache'] = $kern_discharge_array['swache'];
                   $dgp['kern']['discharge']['appetitmangel'] = $kern_discharge_array['appetitmangel'];
                   $dgp['kern']['discharge']['dekubitus'] = $kern_discharge_array['dekubitus'];
                   $dgp['kern']['discharge']['hilfebedarf'] = $kern_discharge_array['hilfebedarf'];
                   $dgp['kern']['discharge']['depresiv'] = $kern_discharge_array['depresiv'];
                   $dgp['kern']['discharge']['angst'] = $kern_discharge_array['angst'];
                   $dgp['kern']['discharge']['anspannung'] = $kern_discharge_array['anspannung'];
                   $dgp['kern']['discharge']['desorientier'] = $kern_discharge_array['desorientier'];
                   $dgp['kern']['discharge']['versorgung'] = $kern_discharge_array['versorgung'];
                   $dgp['kern']['discharge']['umfelds'] = $kern_discharge_array['umfelds'];
                   $dgp['kern']['discharge']['sonstige_probleme'] = $kern_discharge_array['sonstige_probleme'];
                   
                   $dgp['kern']['discharge']['aufwand_mit'] = $kern_discharge_array['aufwand_mit'];
                  
                   $dgp['kern']['discharge']['sterbeort_dgp'] = $kern_discharge_array['sterbeort_dgp'];
                   
                } 
                
            }
            
            
            // get all saved sapvs forms
            if(!empty($patient_sapv_array))
            {

                $sapv_arr = array(); 
                $saround_admission_days = array(); 
                foreach($patient_sapv_array as $k=>$sapv_data)
                {
                    
                    if($sapv_data['sapv_order'] != 0 ){ // saved data in sapv:: ISPC-1805 Stammdaten Verordnung
                        
                        if($sapv_data['sapv_order'] == "1") 
                        {
                            $dgp['sapv'][$sapv_data['id']]['art_der_erordnung'] = "Erstverordnung";
                        }  else  {
                            $dgp['sapv'][$sapv_data['id']]['art_der_erordnung'] = "Folgeverordnung";
                        }
                    }
                    else 
                    {
                        if(empty($sapv_arr)) {
                            $dgp['sapv'][$sapv_data['id']]['art_der_erordnung'] = "Erstverordnung";
                        } else {
                            $dgp['sapv'][$sapv_data['id']]['art_der_erordnung'] = "Folgeverordnung";
                        }
                    }
                    $sapv_arr[] = $sapv_data['id'];
                    
                    
                    $sapv_details[$sapv_data['id']] = $sapv_data;
                    $sapv_details[$sapv_data['id']]['period'] = date("d.m.Y",strtotime($sapv_data['verordnungam']));
                    
                    if($sapv_data['verordnungbis'] != "0000-00-00 00:00:00") 
                    {
                        $sapv_details[$sapv_data['id']]['period'] .= "-".date("d.m.Y",strtotime($sapv_data['verordnungbis']));
                    } 
                    else
                    {
                        $sapv_details[$sapv_data['id']]['period'] .= "-".date("d.m.Y",time());
                    }
                    
                    $dgp['sapv'][$sapv_data['id']]['verordnung_datum'] = $sapv_details[$sapv_data['id']]['period'];
                    $dgp['sapv'][$sapv_data['id']]['dgp_sapv_id'] = "0";
                    
                    
                    
                    
                    $around_admission_days="";
                    $around_sapv_end['start']  = date("d.m.Y",strtotime( $sapv_data['verordnungam']));
                    $around_sapv_end['end']  = date("d.m.Y",strtotime( $sapv_data['verordnungbis']));
                    $around_sapv_end_days = $patientmaster->getDaysInBetween($around_sapv_end['start'],$around_sapv_end['end'],false,"d.m.Y");
                    
                    if(in_array($death_date,$around_sapv_end_days )){
                        $dgp['sapv'][$sapv_data['id']]['therapieende'] = "3";
                    }
                }
                
                
                $patient_sapv_forms = Doctrine_Query::create()
                ->select('*')
                ->from('DgpSapv ds')
                ->where("ipid='" . $ipid . "'")
                ->orderby('ds.id asc');
                $patient_sapv_forms_filled = $patient_sapv_forms ->fetchArray();

                if(!empty($patient_sapv_forms_filled)){
                    foreach($patient_sapv_forms_filled as $k_sapvf=>$v_sapvf)
                    {
                        $dgp['sapv'][$v_sapvf['sapv']] = $v_sapvf;
                        $dgp['sapv'][$v_sapvf['sapv']]['dgp_sapv_id'] = $v_sapvf['id'];
                        
                        if(!empty($v_sapvf['arztlich'])){
                            $dgp['sapv'][$v_sapvf['sapv']]['arztlich'] = explode(",",$v_sapvf['arztlich']);
                        }
                        if(!empty($v_sapvf['pflegerisch'])){
                            $dgp['sapv'][$v_sapvf['sapv']]['pflegerisch'] = explode(",",$v_sapvf['pflegerisch']);
                        }
                        
                        if(!empty($v_sapvf['ambulanter_hospizdienst'])){
                            $dgp['sapv'][$v_sapvf['sapv']]['ambulanter_hospizdienst'] = explode(",",$v_sapvf['ambulanter_hospizdienst']);
                        }
                        
                        if(!empty($v_sapvf['weitere_professionen'])){
                            $dgp['sapv'][$v_sapvf['sapv']]['weitere_professionen'] = explode(",",$v_sapvf['weitere_professionen']);
                        }
                        
                        $sapv_saved_forms[] = $v_sapvf['id'];
                        $sapv_saved_ids[$v_sapvf['sapv']] = $v_sapvf['id'];
                    }
                }

                $this->view->patient_sapv = $sapv_details;
            }
            
            $this->view->dgp =  $dgp;
            
            
            if($this->getRequest()->isPost())
            {
               
                $post = $_POST['dgp'];
                
                $kern_form = new Application_Form_DgpKern();
                $sapv_form = new Application_Form_DgpSapv();
                
                $datum_der_erfassung1 = date("Y-m-d H:i:00",time());
                
                
                $post['kern']['admission']['course'] = "0";
                $post['kern']['admission']['entlasung_date'] = $entlasung_date;
                if(strlen($dgp['kern']['admission']['wohnsituation'])>0) {
                    $post['kern']['admission']['wohnsituation'] =  $dgp['kern']['admission']['wohnsituation'];
                } else {
                    $post['kern']['admission']['wohnsituation'] =  $dgp['kern']['admission']['wohnsituations'];
                }
                $post['kern']['admission']['schmerzen'] = $dgp['kern']['admission']['schmerzen'];
                $post['kern']['admission']['ubelkeit'] = $dgp['kern']['admission']['ubelkeit'];
                $post['kern']['admission']['erbrechen'] = $dgp['kern']['admission']['erbrechen'];
                $post['kern']['admission']['luftnot'] = $dgp['kern']['admission']['luftnot'];
                $post['kern']['admission']['verstopfung'] = $dgp['kern']['admission']['verstopfung'];
                $post['kern']['admission']['swache'] = $dgp['kern']['admission']['swache'];
                $post['kern']['admission']['appetitmangel'] = $dgp['kern']['admission']['appetitmangel'];
                $post['kern']['admission']['dekubitus'] = $dgp['kern']['admission']['dekubitus'];
                $post['kern']['admission']['hilfebedarf'] = $dgp['kern']['admission']['hilfebedarf'];
                $post['kern']['admission']['depresiv'] = $dgp['kern']['admission']['depresiv'];
                $post['kern']['admission']['angst'] = $dgp['kern']['admission']['angst'];
                $post['kern']['admission']['anspannung'] = $dgp['kern']['admission']['anspannung'];
                $post['kern']['admission']['desorientier'] = $dgp['kern']['admission']['desorientier'];
                $post['kern']['admission']['versorgung'] = $dgp['kern']['admission']['versorgung'];
                $post['kern']['admission']['umfelds'] = $dgp['kern']['admission']['umfelds'];
                $post['kern']['admission']['sonstige_probleme'] = $dgp['kern']['admission']['sonstige_probleme'];
                
                $post['kern']['discharge']['course'] = "0";
                $post['kern']['discharge']['entlasung_date'] = $entlasung_date;
                if(strlen($dgp['kern']['discharge']['wohnsituation']) > 0 ){
                    $post['kern']['discharge']['wohnsituation'] =  $dgp['kern']['discharge']['wohnsituation'];
                } else{
                    $post['kern']['discharge']['wohnsituation'] =  $dgp['kern']['discharge']['wohnsituations'];
                }
                
                $post['kern']['discharge']['schmerzen'] = $dgp['kern']['discharge']['schmerzen'];
                $post['kern']['discharge']['ubelkeit'] = $dgp['kern']['discharge']['ubelkeit'];
                $post['kern']['discharge']['erbrechen'] = $dgp['kern']['discharge']['erbrechen'];
                $post['kern']['discharge']['luftnot'] = $dgp['kern']['discharge']['luftnot'];
                $post['kern']['discharge']['verstopfung'] = $dgp['kern']['discharge']['verstopfung'];
                $post['kern']['discharge']['swache'] = $dgp['kern']['discharge']['swache'];
                $post['kern']['discharge']['appetitmangel'] = $dgp['kern']['discharge']['appetitmangel'];
                $post['kern']['discharge']['dekubitus'] = $dgp['kern']['discharge']['dekubitus'];
                $post['kern']['discharge']['hilfebedarf'] = $dgp['kern']['discharge']['hilfebedarf'];
                $post['kern']['discharge']['depresiv'] = $dgp['kern']['discharge']['depresiv'];
                $post['kern']['discharge']['angst'] = $dgp['kern']['discharge']['angst'];
                $post['kern']['discharge']['anspannung'] = $dgp['kern']['discharge']['anspannung'];
                $post['kern']['discharge']['desorientier'] = $dgp['kern']['discharge']['desorientier'];
                $post['kern']['discharge']['versorgung'] = $dgp['kern']['discharge']['versorgung'];
                $post['kern']['discharge']['umfelds'] = $dgp['kern']['discharge']['umfelds'];
                $post['kern']['discharge']['sonstige_probleme'] = $dgp['kern']['discharge']['sonstige_probleme'];

                $post['kern']['discharge']['sterbeort_dgp'] =  $dgp['kern']['discharge']['sterbeort_dgp'];
                
                
                if( $_REQUEST['dbg'] == "post" ){
                    print_r($dgp['kern']['admission']);
                    print_r($dgp['kern']['discharge']);
                    print_r($symp_last_values);
                    print_r($post);
                    exit;
                }
                
                // ADMISSION 
                if(!empty($post['kern']['admission']['dgp_kern_id']) && $post['kern']['admission']['dgp_kern_id'] !="0")
                {
                    // update kern info
                    $kern_form->update_from_admission($post['kern']['admission'],$ipid);
                } else {
                    // insert new kern form
                    $kern_form->insert_from_admission($post['kern']['admission'],$ipid);
                }
                
                // DISCHARGE 
                if(!empty($post['kern']['discharge']['dgp_kern_id']) && $post['kern']['discharge']['dgp_kern_id'] !="0")
                {
                    // update kern info
                    $kern_form->update_from_discharge($post['kern']['discharge'],$ipid);
                } else {
                    // insert new kern form
                    $kern_form->insert_from_discharge($post['kern']['discharge'],$ipid);
                }

                // SAPV
                foreach ($post['sapv'] as $sapv_id =>$sapv_data){
                    $sapv_data['sapv'] = $sapv_id ;
                    $sapv_data['course'] = "0";
                    if(!empty($sapv_data['dgp_sapv_id']) &&  $sapv_data['dgp_sapv_id'] !="0" && in_array( $sapv_data['dgp_sapv_id'],$sapv_saved_forms)){
                        // upodate
                        $sapv_form->update_minimal_dgp_sapv($sapv_data, $ipid);
                    } else {
                        //insert
                        if( strlen($sapv_data['verordnung_durch']) > 0 
                            || strlen($sapv_data['ubernahme_aus']) > 0 
                            || strlen($sapv_data['therapieende']) > 0 
                            || strlen($sapv_data['grund_einweisung']) > 0
                            
                            || isset($sapv_data['pcteam'])
                            || !empty($sapv_data['arztlich']) 
                            || strlen($sapv_data['arztlich_more']) > 0
                            || !empty($sapv_data['pflegerisch']) 
                            || !empty($sapv_data['ambulanter_hospizdienst']) 
                            || !empty($sapv_data['weitere_professionen']) 
                            || strlen($sapv_data['weitere_professionen_more']) > 0
                            ){ // insert only if data is filled
                            $sapv_form->insert_minimal_dgp_sapv($sapv_data, $ipid);
                        }
                    }
                }
                
                // Update patient master -  to verify if the las changes were uploaded
                
                $update= Doctrine_Query::create()
                ->update('PatientMaster')
                ->set('last_update_user', $userid)
                ->set('last_update', "'" . date("Y-m-d H:i:s") . "'")
                ->where("ipid ='" . $ipid . "'");
                $update->execute();
                
                
                
                $cust = new PatientCourse();
                $cust->ipid = $ipid;
                $cust->course_date = date("Y-m-d H:i:s",time());
                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                $cust->course_title = Pms_CommonData::aesEncrypt("Hospiz new register");
                $cust->tabname = Pms_CommonData::aesEncrypt("new_hospiz_register_edited");
                $cust->user_id = $userid;
                $cust->save();
                
                $this->_redirect(APP_BASE . 'patientcourse/patientcourse?id=' . $_REQUEST['id']);
                exit;
                
            }        
        }
        

        /**
         * @claudiu 09.06.2018
         * ! this replaces @ancuta rev.3496, 
         * ! it is a full re-write
         * 
         * it uses the current Falls of the patient... so if you delete a fall, form for that fall cannot be edited
         * 
         * TODO ! URGENT!!! save the discharge part
         * 
         * @throws Zend_Exception
         * @return void|boolean
         */
        public function hospizregisterv3Action()
        {
            $sym_relevant_values = array(
                '0' =>'1',
                '1'=>'2',
                '2'=>'2',
                '3'=>'2',
                '4'=>'2',
                '5'=>'3',
                '6'=>'3',
                '7'=>'3',
                '8'=>'4',
                '9'=>'4',
                '10'=>'4',
            );
            
            $clientid = $this->clientid;
            $userid = $this->userid;
            $decid = $this->dec_id;
            $ipid = $this->ipid;
            
            $patientarr = $this->getPatientMasterData();

            
            //array format
            $dgp = array(
                //this will holds the values needed for the view
                'kern' => array (
                    'admission' => array(),
                    'discharge' => array(),
                ),
                // this holds some texts to create the checkboxes, etc.. ?
                'form' => array(),
            );

            //start with the easy..
            $partners_array = DgpKern::get_form_texts();
            
            foreach ($partners_array['medication_references_a'] as $k=>$mr_data) {
                $partners_array['medication_references_a_final'][$mr_data['code']] = $mr_data['label'];
            }
            
            foreach ($partners_array['medication_references_b'] as $k=>$mr_data) {
                $partners_array['medication_references_b_final'][$mr_data['code']] = $mr_data['label'];
            }
            
            $dgp['form']['texts'] = $partners_array;
            
            $form_name = $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();
             
            $dgp['form']['form_name'] = $form_name;
            
            $formstextslist_model = new FormsTextsList();
            $standard_texts_arr =  $formstextslist_model->get_client_list($clientid, $form_name);
            
            foreach ($standard_texts_arr as $k=>$st) {
                $standard_texts[$st['field_name']][] = $st['field_value'];
            }
            $dgp['form']['standard_texts'] = $standard_texts;
            
            
            
            
            // Symptomatology
            $symp = new Symptomatology();
            $first_symptomarr = $symp->getPatientSymptpomatologyFirst($ipid);
            
            $first_symptoms_ids = array();
            
            $symp_first_values = array();
            
            foreach ($first_symptomarr as $k=>$value) {
                $first_symptoms_ids[] = $value['id'];
                $symp_first_values[$value['symptomid']] = $value['input_value'];
            }
             
            $symptomarr = $symp->getPatientSymptpomatologyLast($ipid);
            
            $symp_last_values = array();
            
            foreach ($symptomarr as $k=>$value) {
                if ( ! in_array($value['id'], $first_symptoms_ids)) {
                    $symp_last_values[$value['symptomid']] = $value['input_value'];
                }
            }
            

            
            
            /*
             * if you want to see/edit forms from deleted falls... you must change this
            */
            $patient_falls = PatientReadmission::findFallsOfIpid($ipid);
            
            $selected_fall = 0; // this is fetched from post or get
            
            $last_fall_start_id = 0;
            
            $patient_falls_array = array();
            $this->view->patient_falls_array = array();
            $this->view->patient_falls_selectbox = array();//only to populate a formSelect
            
            
            foreach ($patient_falls as $fall) {
            
                $start = ! empty($fall['admission']['date']) && $fall['admission']['date'] != '0000-00-00' ? date('d.m.Y', strtotime($fall['admission']['date'])) : '' ;
                $end = ! empty($fall['discharge']['date']) && $fall['discharge']['date'] != '0000-00-00' ? date('d.m.Y', strtotime($fall['discharge']['date'])) : '' ;
            
                $patient_falls_array[$fall['admission']['id']] = array(
                    'start' => ! empty($fall['admission']['date']) && $fall['admission']['date'] != '0000-00-00' ? $fall['admission']['date'] : null, 
                    'end' => ! empty($fall['discharge']['date']) && $fall['discharge']['date'] != '0000-00-00' ? $fall['discharge']['date'] : null,
                    
                    'start_ID' => ! empty($fall['admission']['id']) ? $fall['admission']['id'] : null,
                    'end_ID' => ! empty($fall['discharge']['id']) ? $fall['discharge']['id'] : null,
                ); 
                
                $this->view->patient_falls_selectbox[$fall['admission']['id']] = $start . " - " . $end;
            
                $last_fall_start_id = $fall['admission']['id'];
            }
            $this->view->patient_falls_array = $patient_falls_array;
            
            
            if ($this->getRequest()->isPost()) {
                
                $selected_fall = $this->getRequest()->getPost('selected_fall') ;
                $dgp_post = $this->getRequest()->getPost('dgp') ;
                $admission_id =  $dgp_post['kern']['admission']['dgp_kern_id'];
                
            } else {
                
                $admission_id = null;
                $selected_fall = $this->getRequest()->getParam('selected_fall', $last_fall_start_id) ; //defaults to last Fall
            }
            
            /*
             * fetch all forms of this ipid
             * we need then for the save too, to validate the ownership of what you need saved, and fetch some pre-saved data for re-update?
             */
            $allDgpKernForms = DgpKern::findFormsOfIpids(array($ipid));
            
          
            $selectedDgpKernForm = array_filter ( $allDgpKernForms, function($val) use ($selected_fall, $admission_id) {
                if ($admission_id) {
                    return $val['patient_readmission_ID'] == $selected_fall && $val['id'] == $admission_id;
                } else {
                    return $val['patient_readmission_ID'] == $selected_fall ;                    
                }
            });
            
            $selectedDgpKernForm = reset($selectedDgpKernForm);
            
            if ( ! empty($selectedDgpKernForm)) {
                
                $part_discharge = $selectedDgpKernForm['TwinDgpKern']; // this is the discharge part.. cause we have  ->andWhere("dk.form_type = 'adm' ")
                $dgp['kern']['discharge'] = $part_discharge;
                $dgp['kern']['discharge']['dgp_kern_id'] = isset($part_discharge['id']) ? $part_discharge['id'] :  '';
                
                
                if ( ! empty($part_discharge)) {
                    
                    // partners
                    $begleitung_discharge = explode(",", $part_discharge['begleitung']);
                    $dgp['kern']['discharge']['begleitung'] = $part_discharge['begleitung'];
                    $dgp['kern']['discharge']['begleitung_arr'] = $begleitung_discharge;
                     
                    // location
                    $dgp['kern']['discharge']['wohnsituation'] = $part_discharge['wohnsituation'];
                     
                    // symptomatics
                    $use_saved_discharge = 0 ;
                    
                    foreach ($partners_array['symptoms'] as $sym_id => $sym_details) {
                        
                        if ($part_discharge[$sym_details['code']] != "0") {
                            $use_saved_discharge++;
                        }
                    }
                    
                    if ($use_saved_discharge > 0 ) {
                        foreach ($partners_array['symptoms'] as $sym_id => $sym_details) {
                            $dgp['kern']['discharge'][$sym_details['code']] = $part_discharge[$sym_details['code']];
                        }
                    } else {
                        foreach ($partners_array['symptoms'] as $sym_id => $sym_details) {
                            $dgp['kern']['discharge'][$sym_details['code']] = $sym_relevant_values[$symp_last_values[$sym_id]];
                        }
                    }
                    
                    $dgp['kern']['discharge']['aufwand_mit'] = $part_discharge['aufwand_mit'];
                    $dgp['kern']['discharge']['sterbeort_dgp'] = $part_discharge['sterbeort_dgp'];
                     
                    if ($part_discharge['who'] == "2") {
                        $part_discharge['who'] = '0';
                    }
                    
                    foreach ($partners_array['medication_references_a'] as $k => $mr_data) {
                        $dgp['kern']['discharge'][$mr_data['code']] = $part_discharge[$mr_data['code']];
                    }
                     
                     
                    if ($part_discharge['steroide'] == "2") {
                        $part_discharge['steroide'] = '0';
                    }
                     
                    foreach ($partners_array['medication_references_b'] as $k => $mr_data) {
                        $dgp['kern']['discharge'][$mr_data['code']] = $part_discharge[$mr_data['code']];
                    }
                    
                }
                
                unset($selectedDgpKernForm['TwinDgpKern']); // for `easier` understanding
                
                
                $part_admission = $selectedDgpKernForm;                
                $dgp['kern']['admission'] = $part_admission;
                $dgp['kern']['admission']['dgp_kern_id'] = $part_admission['id'];
                
                
                // partners
                $begleitung = explode(",", $part_admission['begleitung']);
                $dgp['kern']['admission']['begleitung'] = $part_admission['begleitung'];
                $dgp['kern']['admission']['begleitung_arr'] = $begleitung;
                
                
                // location
                $dgp['kern']['admission']['wohnsituation'] = $part_admission['wohnsituation'];
            
                // symptomatics
                $use_saved = 0 ;
                foreach ($partners_array['symptoms'] as $sym_id => $sym_details) {
                    if ($part_admission[$sym_details['code']] != "0") {
                        $use_saved++;
                    }
                }
            
                if ($use_saved > 0 ) {
                    foreach ($partners_array['symptoms'] as $sym_id => $sym_details) {
                        $dgp['kern']['admission'][$sym_details['code']] = $part_admission[$sym_details['code']];
                    }
                } else {
                    foreach ($partners_array['symptoms'] as $sym_id=>$sym_details) {
                        $dgp['kern']['admission'][$sym_details['code']] = $sym_relevant_values[$symp_first_values[$sym_id]];
                    }
                }
                $dgp['kern']['admission']['aufwand_mit'] = $part_admission['aufwand_mit'];
            
            } else {
                
                /*
                 * this is the first time you want to fill this form, for this fall
                 */
                $dgp['kern'] = $this->_gatherData_hospizregisterv3($ipid , $patient_falls_array[$selected_fall]['start'], $patient_falls_array[$selected_fall]['end']);
            }
 
            $this->view->selected_fall =  $selected_fall;
            
            $this->view->dgp =  $dgp;
            
            
            /**
             * this is the POST-save the formular
             * it is after the get, because we use datat that user does not fill
             */
            if ($this->getRequest()->isPost()) {
                //just save the form ! ... then we redirect to another page.. 
                //we don't need some chischarge infos about this patient
                
                $kern_form = new Application_Form_DgpKern();
                
                
                $admission_id =  $dgp_post['kern']['admission']['dgp_kern_id'];
                $discharge_id =  $dgp_post['kern']['discharge']['dgp_kern_id'];
                
                
                if ( ! empty($admission_id) && ! empty($discharge_id)) {
                    /*
                     * you have both form saved for this Fall ..
                     * validate this are the same from in $dgp
                     */
                    if ($dgp_post['kern']['admission']['dgp_kern_id'] != $dgp['kern']['admission']['id'] 
                        || $dgp_post['kern']['discharge']['dgp_kern_id'] != $dgp['kern']['discharge']['id'] )
                    {
                        //what did you post? id's do not match ! what is he saving?
                        throw new Zend_Exception('Failed To save form,  error 1. Admin was informed. ' 
                            . $dgp_post['kern']['admission']['dgp_kern_id'] . "!=". $dgp['kern']['admission']['id'] 
                            . " || " . $dgp_post['kern']['discharge']['dgp_kern_id'] . "!=" . $dgp['kern']['discharge']['id'], 
                        0);
                    }
                    
                    $kern_form->update_from_admission($dgp_post['kern']['admission'],$ipid);
                    $kern_form->update_from_discharge($dgp_post['kern']['discharge'],$ipid);
                                   
                } elseif ( ! empty($admission_id)) {
                    /*
                     * we have admission allready saved ..update it
                     * we do not save discharge-part untill you actualy discharge the patient
                     */
                    if ($dgp_post['kern']['admission']['dgp_kern_id'] != $dgp['kern']['admission']['id']) {
                        //what did you post? id's do not match ! what is he saving?
                        throw new Zend_Exception('Failed To save form,  error 2. Admin was informed. ' 
                            . $dgp['kern']['admission']['id'] . " != " . $dgp_post['kern']['admission']['dgp_kern_id'] , 
                        0);
                    }
                    //TODO-3327 Ancuta 07.08.2020 :: START
					//$kern_form->update_from_admission($dgp_post['kern']['admission'], $ipid);
                    $adm_part = $kern_form->update_from_admission($dgp_post['kern']['admission'], $ipid);
                    
                    // If there is a discharge - but no dgp form for this discharge 
                    if (empty($discharge_id) && !empty($patient_falls_array[$selected_fall]['end'])){

                        $dgp_post['kern']['discharge']['course'] = "0";
                        $dgp_post['kern']['discharge']['patient_readmission_ID'] = $patient_falls_array[$selected_fall]['end_ID'];
                        
                        if ( ! isset ($dgp_post['kern']['discharge']['wohnsituation'])) {
                            if (strlen($dgp['kern']['discharge']['wohnsituation']) > 0 ) {
                                $dgp_post['kern']['discharge']['wohnsituation'] =  $dgp['kern']['discharge']['wohnsituation'];
                            } else {
                                $dgp_post['kern']['discharge']['wohnsituation'] =  $dgp['kern']['discharge']['wohnsituations'];//wacky way with an `s`.. this is actualy the column
                            }
                        }
                        
                        if ( ! isset ($dgp_post['kern']['discharge']['sterbeort_dgp'])) {
                            $dgp_post['kern']['discharge']['sterbeort_dgp'] =  $dgp['kern']['discharge']['sterbeort_dgp'];
                        }
                        
                        
                        if ( ! isset($dgp_post['kern']['discharge']['pverfuegung'])) {
                            $dgp_post['kern']['discharge']['pverfuegung'] = $dgp['kern']['discharge']['pverfuegung'];
                        }
                        
                        if ( ! isset($dgp_post['kern']['discharge']['vollmacht'])) {
                            $dgp_post['kern']['discharge']['vollmacht'] = $dgp['kern']['discharge']['vollmacht'];
                        }
                        
                        if ( ! isset($dgp_post['kern']['discharge']['betreuungsurkunde'])) {
                            $dgp_post['kern']['discharge']['betreuungsurkunde'] = $dgp['kern']['discharge']['betreuungsurkunde'];
                        }
                        
                        $dgp_post['kern']['discharge']['twin_ID'] =  $dgp_post['kern']['admission']['dgp_kern_id'];
                        
                        $dis_part = $kern_form->insert_from_discharge($dgp_post['kern']['discharge'], $ipid);
                        
                        // update admission  with relevant id 
                        if ($adm_part instanceof DgpKern) {
                            // continue link this 2 records via twin_ID
                            $adm_part->twin_ID = $dis_part->id;
                            $adm_part->save();
                        }
                    }
                    //TODO-3327 Ancuta 07.08.2020 :: END
                    
                    
                } elseif (empty($admission_id)) {
                    //we have NO admission form saved 

                    
                    $dgp_post['kern']['admission']['course'] = "0";
                    $dgp_post['kern']['admission']['patient_readmission_ID'] = $patient_falls_array[$selected_fall]['start_ID'];
                    
                    $dgp_post['kern']['discharge']['course'] = "0";
                    $dgp_post['kern']['discharge']['patient_readmission_ID'] = $patient_falls_array[$selected_fall]['end_ID'];
                    
                    
                    /*
                     * next are some datas on form, that user cannot change
                     */
                    
                    // partners
                    if ( ! isset ($dgp_post['kern']['admission']['begleitung'])) {
                        $dgp_post['kern']['admission']['begleitung'] = $dgp['kern']['admission']['begleitung'];
                    }
                    // location
                    if ( ! isset ($dgp_post['kern']['admission']['wohnsituation'])) {
                        if (strlen($dgp['kern']['admission']['wohnsituation']) > 0) {
                            $dgp_post['kern']['admission']['wohnsituation'] =  $dgp['kern']['admission']['wohnsituation'];
                        } else {
                            $dgp_post['kern']['admission']['wohnsituation'] =  $dgp['kern']['admission']['wohnsituations'];//wacky way with an `s`.. this is actualy the column
                        }
                    }

                    
                    if ( ! isset ($dgp_post['kern']['discharge']['wohnsituation'])) {
                        if (strlen($dgp['kern']['discharge']['wohnsituation']) > 0 ) {
                            $dgp_post['kern']['discharge']['wohnsituation'] =  $dgp['kern']['discharge']['wohnsituation'];
                        } else {
                            $dgp_post['kern']['discharge']['wohnsituation'] =  $dgp['kern']['discharge']['wohnsituations'];//wacky way with an `s`.. this is actualy the column
                        }
                    }
                    
                    if ( ! isset ($dgp_post['kern']['discharge']['sterbeort_dgp'])) {
                        $dgp_post['kern']['discharge']['sterbeort_dgp'] =  $dgp['kern']['discharge']['sterbeort_dgp'];  
                    }
                    
                    
                    if ( ! isset($dgp_post['kern']['discharge']['pverfuegung'])) {
                        $dgp_post['kern']['discharge']['pverfuegung'] = $dgp['kern']['discharge']['pverfuegung'];
                    }
                    
                    if ( ! isset($dgp_post['kern']['discharge']['vollmacht'])) {
                        $dgp_post['kern']['discharge']['vollmacht'] = $dgp['kern']['discharge']['vollmacht'];
                    }
                    
                    if ( ! isset($dgp_post['kern']['discharge']['betreuungsurkunde'])) {
                        $dgp_post['kern']['discharge']['betreuungsurkunde'] = $dgp['kern']['discharge']['betreuungsurkunde'];
                    }
                    
                    
                    
                    if ($patient_falls_array[$selected_fall]['start_ID'] > 0 && $patient_falls_array[$selected_fall]['end_ID'] > 0) {

                        /*
                         * id's are both empty in the post, but this and older Fall user wans to fill
                         */
                        $adm_part = $kern_form->insert_from_admission($dgp_post['kern']['admission'], $ipid);
                        
                        if ($adm_part instanceof DgpKern) {
                            $dgp_post['kern']['discharge']['twin_ID'] = $adm_part->id;
                        }
                        
                        $dis_part = $kern_form->insert_from_discharge($dgp_post['kern']['discharge'], $ipid);
                        
                        if ($adm_part instanceof DgpKern) {
                            // continue link this 2 records via twin_ID
                            $adm_part->twin_ID = $dis_part->id;
                            $adm_part->save();
                        }
                        
                    } else {
                        /*
                         * we do not save discharge-part untill you actualy discharge the patient
                         */
                        $kern_form->insert_from_admission($dgp_post['kern']['admission'], $ipid);
                    }
                    
                    
                    ///wtf? you have discharge without admission?
                    if ( ! empty($discharge_id)) {
                        $this->_helper->log("This patient has a dgp-discharge without having a dgp-admission ? @dev please investigate id: {$discharge_id} from {$ipid}" , 0);
                    }
                    
                    
                }
                
                
                

                // Update patient master -  to verify if the las changes were uploaded
                $update= Doctrine_Query::create()
                ->update('PatientMaster')
                ->set('last_update_user', $userid)
                ->set('last_update', "?", date("Y-m-d H:i:s"))
                ->where("ipid = ?", $ipid)
                ->execute()
                ;
                
                
                $cust = new PatientCourse();
                $cust->ipid = $ipid;
                $cust->course_date = date("Y-m-d H:i:s",time());
                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                $cust->course_title = Pms_CommonData::aesEncrypt("Hospiz new register");
                $cust->tabname = Pms_CommonData::aesEncrypt("new_hospiz_register_v3_edited");
                $cust->user_id = $userid;
                $cust->save();
                
                
                $this->redirect(APP_BASE . "patientcourse/patientcourse?id=" . $this->enc_id , array("exit"=>true)); 

                exit; //for readability
                
                
            } // end POST-save
            
            
            
            return;
               
        }

        /**
         * first time you fill in the hospizregisterv3, you need multiple data from patient
         * from a fall start-end
         * 
         * @param string $ipid
         * @param string $start_date
         * @param string $end_date
         * @return void|Ambigous <string, unknown>
         */
        private function _gatherData_hospizregisterv3($ipid = '' , $start_date = 'date(Y-m-d)', $end_date = null) 
        {
            $clientid = $this->clientid;
            //$ipid = $this->ipid;
            
            
            if (empty($ipid) || empty($start_date) || $start_date == 'date(Y-m-d)') {
                return; //fail-safe
            }
            
            
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = ! empty($end_date) ? date('Y-m-d', strtotime($end_date)) : date('Y-m-d');

            $patientmaster = new PatientMaster();
            
            $dgp = array(); // this will be the result
            
            
            /*Wohnsituation:
             "alleine" = location (zu Hause, alleine)
             "im haus der Angehörigen" = admission in location at contact person / discharge in location at contact person
             "Heim" = all location which have status "Altenheim" or" Pflegeheim"
             "sonstiges" = everything else
                */
            
            $sql = 'e.epid, p.ipid, e.ipid,';
            $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
            $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
            $sql .= "AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as gender,";
            $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
            $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
            $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
            $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
            $sql .= "IF(p.admission_date != '0000-00-00',DATE_FORMAT(p.admission_date,'%d\.%m\.%Y'),'') as day_of_admission,";
            $sql .= "IF(p.birthd != '0000-00-00',DATE_FORMAT(p.birthd,'%d\.%m\.%Y'),'') as birthd,";
            
            $conditions['periods'][0]['start'] = '2009-01-01';
            $conditions['periods'][0]['end'] = date('Y-m-d');
            $conditions['client'] = $clientid;
            $conditions['include_standby'] = true;
            $conditions['ipids'] = array($ipid);
            
            $patient_days = Pms_CommonData::patients_days($conditions,$sql);
            
           
            $around_admission_days="";
            $around_admission_date['start']  = date("d.m.Y",strtotime("-3 days",strtotime( $patient_days[$ipid]['details']['admission_date'])));
            $around_admission_date['end']  = date("d.m.Y",strtotime("+3 days",strtotime( $patient_days[$ipid]['details']['admission_date'])));
            $around_admission_days = $patientmaster->getDaysInBetween($around_admission_date['start'],$around_admission_date['end'],false,"d.m.Y");
            
            $around_admission_days_start = $patientmaster->getDaysInBetween($patient_days[$ipid]['details']['day_of_admission'],$around_admission_date['end'],false,"d.m.Y");
            
            /* ----------------- Patient Details -Admission location----------------------------------------- */
            $ploc = new PatientLocation();
             
            $patient_locations = new PatientLocation();
            $patient_period_locations = $patient_locations->getPatientLocations($ipid,true);
// dd($patient_period_locations);
            foreach($patient_period_locations as $k=>$location_details){
                //TODO-3413 Ancuta 25.11.2020 Change wohnsituation take "location at day of admission"
                /* 
                $location_start = date("d.m.Y",strtotime($location_details['valid_from']));
                if(in_array($location_start,$around_admission_days_start) )
                {
                    $admission_location[] = $location_details;
                }
                 */
                if(in_array(date("d.m.Y",strtotime( $patient_days[$ipid]['details']['admission_date'])),$location_details['days']) )
                {
                    $admission_location[] = $location_details;
                }
                //--
            
                $all_location[] = $location_details;
            }
            if($admission_location)
            {
                $first_location = $admission_location[0];
            
                switch ($first_location['master_location']['location_type']){
            
                    case "6":// bei Kontaktperson
                        $dgp['kern']['admission']['wohnsituation'] = "2";
                        break;
            
                    case "5":// Zu Hause
                        if($first_location['master_location']['location_sub_type'] == "alone")
                        {
                            $dgp['kern']['admission']['wohnsituation'] = "1";
                        }
                        else
                        {
                            $dgp['kern']['admission']['wohnsituation'] = "6";
                        }
                        break;
            
                         
                    case "3":// Pflegeheim
                    case "4":// Altenheim
                        $dgp['kern']['admission']['wohnsituation'] = "4";
                        break;
                         
                    case "2":// Hospiz // NEW
                        $dgp['kern']['admission']['wohnsituation'] = "5";
                        break;
            
                    case "0":// Sonstige
                        $dgp['kern']['admission']['wohnsituation'] = "4";
                        break;
                         
                    default:
                        $dgp['kern']['admission']['wohnsituation'] = "0";//not applicable
                        break;
                }
            }
            
            
            if($all_location)
            {
                $last_location = end($all_location);
            
                switch ($last_location['master_location']['location_type']){
            
                    case "6":// bei Kontaktperson
                        $dgp['kern']['discharge']['wohnsituation'] = "2";
                        break;
            
                    case "5":// Zu Hause
                        if($last_location['master_location']['location_sub_type'] == "alone")
                        {
                            $dgp['kern']['discharge']['wohnsituation'] = "1";
                        }
                        else
                        {
                            $dgp['kern']['discharge']['wohnsituation'] = "6";
                        }
                        break;
            
                         
                    case "3":// Pflegeheim
                    case "4":// Altenheim
                        $dgp['kern']['discharge']['wohnsituation'] = "4";
                        break;
                         
                    case "2":// Hospiz // NEW
                        $dgp['kern']['discharge']['wohnsituation'] = "5";
                        break;
            
                    case "0":// Sonstige
                        $dgp['kern']['discharge']['wohnsituation'] = "4";
                        break;
                         
                    default:
                        $dgp['kern']['discharge']['wohnsituation'] = "0";//not applicable
                        break;
                }
            }
            
            	
            // get ecog details
            //$ecog_value['first'] = $patientmaster->ecog_values($ipid,true);
            //$ecog_value['last']= $patientmaster->ecog_values($ipid,false,true);
            $ecog_values_array = array();
            $ecog_values_array= $patientmaster->findEcogValuesInPeriod($ipid, $start_date, $end_date);
            $ecog_value['first'] = reset($ecog_values_array);
            $ecog_value['last'] = end($ecog_values_array);
            
            
            
            // Symptomatology
            $symp = new Symptomatology();
            $first_symptomarr = $symp->getPatientSymptpomatologyFirst($ipid);
            
            $first_symptoms_ids = array();
            foreach($first_symptomarr as $k=>$value) {
                $first_symptoms_ids[] = $value['id'];
                $symp_first_values[$value['symptomid']] = $value['input_value'];
            }
             
            $symptomarr = $symp->getPatientSymptpomatologyLast($ipid);
            foreach($symptomarr as $k=>$value) {
                if(!in_array($value['id'],$first_symptoms_ids))
                {
                    $symp_last_values[$value['symptomid']] = $value['input_value'];
                }
            }
            
            /* ------------------Patient->  assesment Symptomatics------------------ */
            $assesment = Doctrine_Core::getTable('KvnoAssessment')->findBy('ipid', $ipid);
            $assesmentarr = $assesment->toArray();
            
            $dgp['kern']['admission']['ecog'] = $ecog_value['first'];
            
            $dgp['kern']['admission']['sapvteam_as'] = $assesmentarr[0]['sapvteam'];
            $dgp['kern']['admission']['hausarzt'] = $assesmentarr[0]['hausarzt'];
            $dgp['kern']['admission']['pflege'] = $assesmentarr[0]['pflege'];
            $dgp['kern']['admission']['palliativ'] = $assesmentarr[0]['palliativ'];
            $dgp['kern']['admission']['palliativpf'] = $assesmentarr[0]['palliativpf'];
            $dgp['kern']['admission']['palliativber'] = $assesmentarr[0]['palliativber'];
            $dgp['kern']['admission']['dienst'] = $assesmentarr[0]['dienst'];
            
            
            foreach($partners_array['symptoms'] as $sym_id=>$sym_details){
                $dgp['kern']['admission'][$sym_details['code']] = $sym_relevant_values[$symp_first_values[$sym_id]];
            }
             
            
            
            $dgp['kern']['discharge']['ecog'] =  $ecog_value['last']; // last ecog ever from last visits
            $dgp['kern']['discharge']['sapvteam_as'] = $assesmentarr[0]['sapvteam'];
            $dgp['kern']['discharge']['hausarzt'] = $assesmentarr[0]['hausarzt'];
            $dgp['kern']['discharge']['pflege'] = $assesmentarr[0]['pflege'];
            $dgp['kern']['discharge']['palliativ'] = $assesmentarr[0]['palliativ'];
            $dgp['kern']['discharge']['palliativpf'] = $assesmentarr[0]['palliativpf'];
            $dgp['kern']['discharge']['palliativber'] = $assesmentarr[0]['palliativber'];
            $dgp['kern']['discharge']['dienst'] = $assesmentarr[0]['dienst'];
            
            foreach($partners_array['symptoms'] as $sym_id=>$sym_details){
                $dgp['kern']['discharge'][$sym_details['code']] = $sym_relevant_values[$symp_last_values[$sym_id]];
            }
            
            // ACP
            $acp = new PatientAcp();
            $acp_data = $acp->getByIpid(array($ipid));
            $current_acp_data = $acp_data[$ipid];
            
            if ( ! empty($current_acp_data)) {
                foreach ($current_acp_data as $k => $block) {
            
                    switch ($block['division_tab']) {
                        
                        case "living_will" :
                            $dgp['kern']['discharge']['pverfuegung'] = ($block['active'] == "yes") ? "1" : "-1";
                            break;
                            
                        case "healthcare_proxy" :
                            $dgp['kern']['discharge']['vollmacht'] = ($block['active'] == "yes") ? "1" : "-1";
                            break;
                            
                        case "care_orders" :
                            $dgp['kern']['discharge']['betreuungsurkunde'] = ($block['active'] == "yes") ? "1" : "-1";
                            break;
                    }
                }
            }

            
            
            //return $dgp;
            // Added ['kern'] by Ancuta(25.07.2018) - as where the that is retrived  the ['kern']  was already added - resulting in $dgp['kern']['kern'] -> and data was not shown in form 
            return $dgp['kern'];  
        }
        
        
        
        
        public function sapvfb8lmuAction(){
            setlocale(LC_ALL, 'de_DE.UTF-8');
            $logininfo= new Zend_Session_Namespace('Login_Info');
            
            
            $decid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpid($decid);
            $epid = Pms_CommonData::getEpidFromId($decid);
            $userid = $logininfo->userid;
            $clientid = $logininfo->clientid;
            $this->view->patient_id=$_GET['id'];

            /* ------------- Patient header ---------------------------------------- */
            $patientmaster = new PatientMaster();
            $this->view->patientinfo = $patientmaster->getMasterData($decid,1);
        
            /* ------------- Patient Menu---------------------------------------- */
            $tm = new TabMenus();
            $this->view->tabmenus = $tm->getMenuTabs();
            
            $form_data = array();
            /* ------------- Client details ---------------------------------------- */
            $clientdata = Pms_CommonData::getClientData($clientid);
            $client['signature'] = $clientdata[0]['lastname'].', '.$clientdata[0]['firstname'];
            $client['team_name'] = $clientdata[0]['team_name'];
            $client['address'] = $clientdata[0]['street1'].'<br/>'.$clientdata[0]['zip'].' '.$clientdata[0]['city'];
            $client['pdf_address'] = $clientdata[0]['street1']."\n".$clientdata[0]['zip'].' '.$clientdata[0]['city'];
            $client['ik_number'] = $clientdata[0]['institutskennzeichen'];
            $client['city'] = $clientdata[0]['city'];
            $this->view->client = $client;
            

            $sql = 'e.epid, p.ipid, e.ipid,';
            $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
            $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
            $sql .= "AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as gender,";
            $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
            $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
            $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
            $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
            $sql .= "IF(p.admission_date != '0000-00-00',DATE_FORMAT(p.admission_date,'%d\.%m\.%Y'),'') as day_of_admission,";
            $sql .= "IF(p.birthd != '0000-00-00',DATE_FORMAT(p.birthd,'%d\.%m\.%Y'),'') as birthd,";
            
            $conditions['periods'][0]['start'] = '2009-01-01';
            $conditions['periods'][0]['end'] = date('Y-m-d');
            $conditions['client'] = $clientid;
            $conditions['include_standby'] = true;
            $conditions['ipids'] = array($ipid);
            
            $patient_days = Pms_CommonData::patients_days($conditions,$sql);
            
            
            foreach($patient_days as $pat_ipid => $data)
            {
//                 $adm_substitute = 1;
                foreach($data['active_periods'] as $period_identification => $period_details)
                {
                    	
                    $admission_periods[$pat_ipid][$period_identification ]['start'] = $period_details['start'];
                    $admission_periods[$pat_ipid][$period_identification ]['end'] = $period_details['end'];
                    $admission_q_periods[$pat_ipid]['days'][] = $patientmaster->getDaysInBetween(date("d.m.Y",strtotime($period_details['start'])),date("d.m.Y",strtotime($period_details['end'])),false,"d.m.Y");
                    
                    $admission_ids[$pat_ipid][] = $period_identification ;
//                     $adm_substitute++;
                }
                 
                $overall_periods[$ipid] = array_values($data['active_periods']);
                $overall[$ipid]['start'] = $overall_periods[$ipid][0]['start'];
                $last_period[$ipid] = end($overall_periods[$ipid]);
                $overall[$ipid]['end'] = $last_period[$ipid]['end'];
                 
                $patient_details[$pat_ipid]['last_name'] = $data['details']['last_name'];
                $patient_details[$pat_ipid]['first_name'] = $data['details']['first_name'];
                $patient_details[$pat_ipid]['street1'] = $data['details']['street1'];
                $patient_details[$pat_ipid]['zip'] = $data['details']['zip'];
                $patient_details[$pat_ipid]['birthd'] = date("d.m.Y",strtotime($data['details']['birthd']));
                 
                // days of treatment in  period
                $patient_details[$pat_ipid]['active_days'] = $data['real_active_days'];
                if(!empty( $data['hospital']['real_days_cs'])){
                    $patient_details[$pat_ipid]['hospital_days'] = $data['hospital']['real_days_cs'];
                } else{
                    $patient_details[$pat_ipid]['hospital_days'] = array();
                }
                $patient_details[$pat_ipid]['hospital_days_no'] = $data['hospital']['real_days_cs_no'];
                
                $patient_details[$pat_ipid]['treatment_days'] = $data['treatment_days'];
            }
             
            $this->view->admission_periods = $admission_periods[$ipid];
            $last_admissionid[$ipid] = end($admission_ids[$ipid]);
            
             
             
             foreach($admission_q_periods[$ipid]['days'] as $d_k=>$d_values)
             {
                 foreach($d_values as $kd=>$d_val){
                    //$qdays[$ipid][ceil(date("m",strtotime($d_val))/3).'-'.date("Y",strtotime($d_val))][] = $d_val;     
                    $qdays[$ipid][strtotime('01-0'.ceil(date("m",strtotime($d_val))/3).'-'.date("Y",strtotime($d_val)))][] = $d_val;     
                 }
             }
              
             ksort($qdays[$ipid]);
             $q_start = "";
             $q_end = "";
             foreach($qdays[$ipid] as $q_indication => $q_values){
                 $q_start = $q_values[0]; 
                 $q_end = end($q_values); 
                 //$q_drop[strtotime($q_start).'-'.strtotime($q_end)] = '0'.$q_indication; 
                 $q_drop[strtotime($q_start).'-'.strtotime($q_end)] = date("m-Y",$q_indication); 
             }
             
             $this->view->q_drop = $q_drop;
             
             
             
            //depending on fall
             $form_admissionid = "";
            if(empty($_REQUEST['period']) )
            {
                //show current admission if opened OR last admission if closed
                foreach($admission_periods[$ipid] as $admisison_id =>$period_detailss ){
            
                    $current_date = date("d.m.Y",time());
                    if(Pms_CommonData::isintersected(strtotime($current_date), strtotime($current_date), strtotime($period_detailss['start']), strtotime($period_detailss['end'])))
                    {
                        $selected_period['start'] = $period_detailss['start'];
                        $selected_period['end'] =  $period_detailss['end'];
            
                        foreach($patient_details[$ipid]['active_days'] as $kd => $aval)
                        {
                            $r1start = strtotime($aval);
                            $r1end = strtotime($aval);
                            $r2start = strtotime($selected_period['start']);
                            $r2end = strtotime($selected_period['end']);
                            if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)){
                                $active_days[$ipid][] =  $aval;
                            }
                        }
                        $form_admissionid  = "a-".strtotime($selected_period['start']).'-'.strtotime($selected_period['end']);
                    }
                }

                
                if(strlen($form_admissionid) == "0")
                {
                    $selected_period['start'] = $admission_periods[$ipid][$last_admissionid[$ipid]]['start'];
                    $selected_period['end'] =  $admission_periods[$ipid][$last_admissionid[$ipid]]['end'];
                    
                    foreach($patient_details[$ipid]['active_days'] as $kd => $aval)
                    {
                        $r1start = strtotime($aval);
                        $r1end = strtotime($aval);
                        $r2start = strtotime($selected_period['start']);
                        $r2end = strtotime($selected_period['end']);
                        if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)){
                            $active_days[$ipid][] =  $aval;
                        }
                    }
                    $form_admissionid  = "a-".strtotime($selected_period['start']).'-'.strtotime($selected_period['end']);
                }
                    
                
                
                
                
                
                
            }
            elseif($_REQUEST['period'] == "overall")
            {
                $selected_period['start'] = "01.01.2009";
                $selected_period['end'] =  date('d.m.Y',time());
                
                foreach($patient_details[$ipid]['active_days'] as $kd => $aval)
                {
                    $r1start = strtotime($aval);
                    $r1end = strtotime($aval);
                    $r2start = strtotime($selected_period['start']);
                    $r2end = strtotime($selected_period['end']);
                    if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)){
                        $active_days[$ipid][] =  $aval;
                    }
                }
                $form_admissionid = "overall";
            }
            else
            {

                $r_period = $_REQUEST['period'];
                $period_array = explode("-",$_REQUEST['period']);
                if(is_array($period_array)){
                    $selected_period['start'] = date("d.m.Y",$period_array['1']);
                    $selected_period['end'] =  date("d.m.Y",$period_array['2']);
                     
                    foreach($patient_details[$ipid]['active_days'] as $kd => $aval)
                    {
                        $r1start = strtotime($aval);
                        $r1end = strtotime($aval);
                        $r2start = strtotime($selected_period['start']);
                        $r2end = strtotime($selected_period['end']);
                        if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)){
                            $active_days[$ipid][] =  $aval;
                        }
                    }
                    $form_admissionid = $_REQUEST['period'];
                }    
                else
                {
                    
                    $selected_period['start'] = "01.01.2009";
                    $selected_period['end'] =  date('d.m.Y',time());
                    
                    foreach($patient_details[$ipid]['active_days'] as $kd => $aval)
                    {
                        $r1start = strtotime($aval);
                        $r1end = strtotime($aval);
                        $r2start = strtotime($selected_period['start']);
                        $r2end = strtotime($selected_period['end']);
                        if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)){
                            $active_days[$ipid][] =  $aval;
                        }
                    }
                    
                    $form_admissionid = "overall";
                }
            }
            $this->view->period = $form_admissionid;            
            
            /* ------------- Patient details ---------------------------------------- */
            $patient['details']['name'] = $patient_days[$ipid]['details']['last_name'].', '.$patient_days[$ipid]['details']['first_name']; 
            $patient['details']['birthd'] = $patient_days[$ipid]['details']['birthd'];
            $patient['details']['address'] = $patient_days[$ipid]['details']['street1'].'<br/>'.$patient_days[$ipid]['details']['zip'].' '.$patient_days[$ipid]['details']['city']; 
            $patient['details']['pdf_address'] = $patient_days[$ipid]['details']['street1']."\n".$patient_days[$ipid]['details']['zip'].' '.$patient_days[$ipid]['details']['city']; 

            /* ------------- Patient discharge details ---------------------------------------- */
            
            if($patient_days[$ipid]['details']['isdischarged'] == "1"){
                
                $distod = Doctrine_Query::create()
                ->select("*")
                ->from('DischargeMethod')
                ->where("isdelete = 0  and clientid=" . $clientid . " and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN')");
                $todarray = $distod->fetchArray();
                
                $dm_dead_ids[] = "99999999";
                foreach($todarray as $todmethod)
                {
                    $dm_dead_ids[] = $todmethod['id'];
                }
                if(!empty($patient_days[$ipid]['discharge_details'])){
                    
                    foreach($patient_days[$ipid]['discharge_details'] as $dis_id=>$dis_data){
                        $discharge_array[$ipid][strtotime($dis_data['discharge_date'])] = $dis_data; 
                    }
                    
                    ksort($discharge_array[$ipid]);
                    $last_discharge[$ipid] = end($discharge_array[$ipid]);
                    
                    if(in_array($last_discharge[$ipid]['discharge_method'],$dm_dead_ids)){
                        $last_discharge[$ipid]['status'] = "dead";
                    } else{
                        $last_discharge[$ipid]['status'] = "discharged";
                    }
                    $last_discharge[$ipid]['date_Ymd'] = date("Y-m-d",strtotime($last_discharge[$ipid]['discharge_date']));
                    $last_discharge[$ipid]['date_dmY'] = date("d.m.Y",strtotime($last_discharge[$ipid]['discharge_date']));
                }
            }
            
            /* ------------- Patient Health Insurance ---------------------------------------- */
            $phelathinsurance = new PatientHealthInsurance();
            $healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);
            
            if(strlen($healthinsu_array[0]['insurance_no']) > 0 && $healthinsu_array[0]['insurance_no'] != '0')
            {
                $pathealthinsurancenr = $healthinsu_array[0]['insurance_no'];
            }
            else
            {
                $pathealthinsurancenr = "--";
            }
            $patient['health_insurance']['insurance_no'] = $pathealthinsurancenr;
            
            if(!empty($healthinsu_array[0]['companyid']) && $healthinsu_array[0]['companyid'] != 0)
            {
                $insucom = new HealthInsurance();
                $insucomarray = $insucom->getCompanyinfofromId($healthinsu_array[0]['companyid']);
            
                $insurance_company_name = $insucomarray[0]['name'];
            }
            else if($healthinsu_array[0]['companyid'] == '0')
            {
                $insurance_company_name = $healthinsu_array[0]['company_name'];
            }
            $patient['health_insurance']['name'] = $insurance_company_name;
            
            $this->view->patient =$patient; 

            
            /* ------------------------------------------------------------------------------ */
            /* ------------- Get all partners of Patient---------------------------------- */
            /* ------------------------------------------------------------------------------ */
            $partners = array();
            
            /* ------------- Patient Pharmacy ---------------------------------------- */
            $pharmacy = new PatientPharmacy();
            $pharm_pat = $pharmacy->getPatientPharmacy($ipid);
            
            if(sizeof($pharm_pat) > 0){
                $partners_data['pharmacy'] = $pharm_pat;
                foreach($pharm_pat as $k=>$value){
                    $partners[] =  $value['apotheke'].', '.$value['street1'].', '.$value['zip'].' '.$value['city'];;
                }
            }
            
            /* ------------- Patient Pflegedienst ---------------------------------------- */
            $pfleg = Pflegedienstes::getPflegedienstes($ipid);
            
            if($pfleg > 0)
            {
                $partners_data['nursing'] = $pfleg;
                foreach($pfleg as $k=>$value){
                    $partners[] =  $value['nursing'].', '.$value['street1'].', '.$value['zip'].' '.$value['city'];;
                }
            }

            /* ------------- Patient sonst. Versorger ---------------------------------------- */
            $suppliers = new PatientSuppliers();
            $pat_suppliers = $suppliers->getPatientSuppliers($ipid);

            if(sizeof($pat_suppliers) > 0)
            {
                $partners_data['suppliers'] = $pat_suppliers;
                foreach($pat_suppliers as $k=>$value){
                    $partners[] =  $value['m_supplier'].', '.$value['street1'].', '.$value['zip'].' '.$value['city'];;
                }
            }

            /* ------------- Patient Voluntaryworkers ---------------------------------------- */
            $pvw = new PatientVoluntaryworkers();
            $pvwarray = $pvw->getPatientVoluntaryworkers($ipid);

            if(sizeof($pvwarray) > 0)
            {
                $partners_data['voluntary_workers'] = $pvwarray;
                foreach($pvwarray as $k=>$value){
                    $partners[] = $value['first_name'].' '.$value['last_name'].', '.$value['street1'].', '.$value['zip'].' '.$value['city'];
                }
            }
            
            /* ------------- Patient Hausarzt---------------------------------------- */
            $famdoc = FamilyDoctor::getFamilyDoctors($ipid);

            if($famdoc){
                $fam_doctor = $famdoc[0]['first_name'].' '.$famdoc[0]['last_name'].', '.$famdoc[0]['street1'].', '.$famdoc[0]['zip'].' '.$famdoc[0]['city'];
                $partners[] = $fam_doctor;
                $partners_data['fam_doctor'] = $fam_doctor;
            }

            /* ------------- Patient Facharzt---------------------------------------- */
            $m_specialists_types = new SpecialistsTypes();
            $specialists_types  =$m_specialists_types->get_specialists_types($logininfo->clientid);
            	
            if(!empty($specialists_types)){
                foreach($specialists_types as $k=>$tp){
                    $s_type[$tp['id']] = $tp['name'];
                }
            }
            $this->view->s_type =$s_type;
            $specialists = new PatientSpecialists();
            $specialists_arr = $specialists->get_patient_specialists($ipid, true);
            	
            if(count($specialists_arr))
            {
                $partners_data['specialists'] = $specialists_arr;
                foreach($specialists_arr as $k=>$value){
                    $partners[] = $value['master']['first_name'].' '.$value['master']['last_name'].', '.$value['master']['street1'].', '.$value['master']['zip'].' '.$value['master']['city'];
                }
            }
            	
            $this->view->partners = $partners;
            
            /* ----------------- Patient Details - SAPV ----------------------------------------- */
            $sapv_verordnets = Pms_CommonData::get_sapv_verordnets();
            $sapv_model = new SapvVerordnung();
            if($_REQUEST['period'] == "overall") {
                $sapv_array =  $sapv_model->get_all_sapvs($ipid);
            } else {
                $sapv_array = $sapv_model->get_sapvs_in_period(array($ipid),$selected_period['start'],$selected_period['end']); 
            }

            if(!empty($sapv_array)){
                $sapv['total'] = 0;
                foreach($sapv_array as $k=>$sapv_data){
                    
                    $sapv_types = explode(',',$sapv_data['verordnet']);
                    $stypes = array();
                    foreach($sapv_types as  $type_id){
                        $stypes[] = $sapv_verordnets[$type_id];
                    }
                    
                    if(in_array('3',$sapv_types) || in_array('4',$sapv_types)){
                        $sapv_period_days[] = $patientmaster->getDaysInBetween(date("d.m.Y",strtotime($sapv_data['verordnungam'])),date("d.m.Y",strtotime($sapv_data['verordnungbis'])),false,"d.m.Y");
                    }
                    
                    $sapv['list'] [$k]['stype'] = implode(', ',$stypes);
                    $sapv['list'] [$k]['start'] = date("d.m.Y",strtotime($sapv_data['verordnungam']));

                    if($patient_days[$ipid]['details']['isdischarged'] == "1"){
                        if(strtotime(date("Y-m-d",strtotime($sapv_data['verordnungbis']))) >= strtotime( $last_discharge[$ipid]['date_Ymd']) ){
                            $sapv_data['verordnungbis'] =  $last_discharge[$ipid]['date_Ymd'];
                            
                            if($last_discharge[$ipid]['status'] == "dead"){
                                $extra_text = " (verstorben)";
                            } else{
                                $extra_text = "";
                            }
                            
                        }
                    }
                    $sapv['list'] [$k]['end'] = date("d.m.Y",strtotime($sapv_data['verordnungbis']));
                    $sapv['list'] [$k]['end'] .= $extra_text;
                    
                    $sapv['list'] [$k]['interval_days'] = date("d.m.Y",strtotime($sapv_data['verordnungbis']));
                    $sapv['list'] [$k]['interval_days'] = $patientmaster->getDaysInBetween(date("d.m.Y",strtotime($sapv_data['verordnungam'])),date("d.m.Y",strtotime($sapv_data['verordnungbis'])),"number","d.m.Y");
                    
                    
                    $sapv['total'] += $sapv['list'] [$k]['interval_days'];
                }
            }
            $this->view->sapv_data = $sapv;

            foreach($sapv_period_days as $k=>$sdays){
                foreach($sdays as  $sk=>$sday){
                    if(in_array($sday,$patient_details[$ipid]['treatment_days']) && in_array($sday,$active_days[$ipid])){
                        $form_data['active_tv_vv'][$sday] = "X";
                    }  
                }
            }
            
            

            /* ----------------- Get all cf types allowed in this form ----------------------- */
             
            $allowed_cf_types[] = "XXXXXXXXXXXXXXX";
            $current_form = array('sapvfb8_lmu');
            $form_items = FormsItems::get_all_form_items($clientid, $current_form, 'v');
            
            foreach($form_items[$current_form[0]] as $k_item => $v_item)
            {
                $items_arr[] = $v_item['id'];
            }
            
            $items_contact_forms = Forms2Items::get_items_forms($clientid, $items_arr);
            
            foreach($items_contact_forms as $item=>$cf_type){
                foreach($cf_type as $k =>$tp)
                {
                    $allowed_cf_types[] = $tp;
                }
            } 

            /* ----------------- Get all contact forms ----------------------- */
            $contact_forms = ContactForms::get_period_contact_forms($ipid, $selected_period, true);
            $cf_ids[]="XXXXXXXX";
            foreach($contact_forms as $cf_day=>$cf_data){
                foreach($cf_data as $ck=>$cf_value){
                    $cf_ids[] = $cf_value['id'];
                }
            }
            
            
            $fb = new FormBlockDrivetimedoc();
            $fbd_data = $fb->get_multiple_FormBlockDrivetimedoc($ipid,$cf_ids);
            
            
            foreach($contact_forms as $cf_day=>$cf_data){
                foreach($cf_data as $ck=>$cf_value){
                    if(    in_array($cf_value['form_type'],$allowed_cf_types)
                        && in_array(date("d.m.Y",strtotime($cf_value['billable_date'])),$patient_details[$ipid]['treatment_days']) 
                        && in_array(date("d.m.Y",strtotime($cf_value['billable_date'])),$active_days[$ipid])
                        )
                    {
                        $form_data['visits']['amount'][date('d.m.Y',strtotime($cf_day))] +=1; 
                        $form_data['visits']['duration'][date('d.m.Y',strtotime($cf_day))] += $cf_value['visit_duration']; 
                        $form_data['day_total_minutes'][date('d.m.Y',strtotime($cf_day))] += $cf_value['visit_duration']; // From cf
                        
                        if(!empty($fbd_data[$cf_value['id']]['fahrtzeit1'])){
                            $form_data['visits']['driving_time'][date('d.m.Y',strtotime($cf_day))] += $fbd_data[$cf_value['id']]['fahrtzeit1']; // FORM BLOCK 
                            $form_data['day_total_minutes'][date('d.m.Y',strtotime($cf_day))] += $fbd_data[$cf_value['id']]['fahrtzeit1']; // FROM BLOCK
                        } else {
                            $form_data['visits']['driving_time'][date('d.m.Y',strtotime($cf_day))] += $cf_value['fahrtzeit']; // From cf 
                            $form_data['day_total_minutes'][date('d.m.Y',strtotime($cf_day))] += $cf_value['fahrtzeit']; // From cf 
                        }
                        
                        if(!empty($fbd_data[$cf_value['id']]['fahrt_doc1'])){
                            $form_data['visits']['documentation_time'][date('d.m.Y',strtotime($cf_day))] += $fbd_data[$cf_value['id']]['fahrt_doc1']; // FROM BLOCK
                            $form_data['day_total_minutes'][date('d.m.Y',strtotime($cf_day))] += $fbd_data[$cf_value['id']]['fahrt_doc1']; // FROM BLOCK
                        }
                    }
                    
                    
                    if(    in_array($cf_value['form_type'],$allowed_cf_types)
                        && in_array(date("d.m.Y",strtotime($cf_value['billable_date'])),$patient_details[$ipid]['hospital_days']) 
                        && in_array(date("d.m.Y",strtotime($cf_value['billable_date'])),$active_days[$ipid]))
                    { // get visits done in hospital 
                        $form_data['visits']['in_hospital_minutes'][date('d.m.Y',strtotime($cf_day))] += $cf_value['visit_duration']; 
                        $form_data['day_total_minutes'][date('d.m.Y',strtotime($cf_day))] += $cf_value['visit_duration']; // From cf
                    }
                }
            }
            
            /* ----------------- Get XT  and V values  (Patient course)----------------------- */
            $pc = new PatientCourse(); 
            $course_sh = array("XT","V");
            $pc_data = $pc->get_sh_patient_shortcuts_course($ipid,$course_sh,$selected_period);
            
            if(!empty($pc_data))
            {
                foreach($pc_data as $k=>$course_val)
                {
                    if($course_val['course_type'] == "V" || $course_val['course_type'] == "XT")
                    {
                        $coursearr = explode("|", $course_val['course_title']);
                        if(count($coursearr) == 3)
                        {
                            $date = date('d.m.Y',strtotime($coursearr[2]));
                            $duration = $coursearr[0];
                        }
                        else if(count($coursearr) != 3 && count($coursearr) < 3)
                        {
                            $date = date('d.m.Y',strtotime($course_val['course_date']));
                            $duration = $coursearr[0];
                        }
                        else if(count($coursearr) != 3 && count($coursearr) > 3)
                        {
                            $date = date('d.m.Y',strtotime($coursearr[3]));
                            $duration = $coursearr[1];
                        }
                        
                        if( in_array($date,$patient_details[$ipid]['treatment_days']) && in_array($date,$active_days[$ipid]) ){
                            $form_data[$course_val['course_type']]['amount'][date('d.m.Y',strtotime($date))] += 1;
                            $form_data[$course_val['course_type']]['duration'][date('d.m.Y',strtotime($date))] += $duration;
                            $form_data['day_total_minutes'][date('d.m.Y',strtotime($date))] += $duration;
                        }
                    }
                }
            }
            /* ----------------- Get Client team meetings ----------------------- */
            $tm = new TeamMeeting();
            $meetings = $tm->get_team_meetings($clientid);
            
            foreach($meetings as $kt =>$vt){
                if( in_array(date('d.m.Y',strtotime($vt['date'])),$patient_details[$ipid]['treatment_days']) && in_array(date('d.m.Y',strtotime($vt['date'])),$active_days[$ipid]) ){
                    $start_ts = strtotime($vt['from_time']);
                    $end_ts = strtotime($vt['till_time']);
                    $duration = round(($end_ts - $start_ts) / 60);;
                    $form_data['teammeeting']['amount'][date('d.m.Y',strtotime($vt['date']))] += 1;
                    $form_data['teammeeting']['duration'][date('d.m.Y',strtotime($vt['date']))] += $duration;
                    $form_data['day_total_minutes'][date('d.m.Y',strtotime($vt['date']))] += $duration;
                }
            }
            
            /* ----------------- Create moths  ----------------------- */
            foreach($active_days[$ipid] as $k=>$aday){
                $ym = date('Y', strtotime($aday)).'-'.date('m', strtotime($aday));
                $year_months_array[$ym][] = $aday;

                if(!function_exists('cal_days_in_month'))
                {
                    $system_year_months_array[$ym] = date('t', mktime(0, 0, 0, date("n", strtotime($ym . "-01")), 1, date("Y", strtotime($ym . "-01"))));
                }
                else
                {
                    $system_year_months_array[$ym] = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($ym . "-01")), date("Y", strtotime($ym . "-01")));
                }
            }
            
            $this->view->sys_months_array = $system_year_months_array;
            
            
            
            $this->view->form_data = $form_data;
            
            /* ----------------- POST ----------------------- */
            if($this->getRequest()->isPost())
            {
                $post = $_POST;
                $post['sys_months_array'] = $system_year_months_array;
                $post['pages'] = ceil(count($system_year_months_array)/2);
                $post['patient'] = $patient;
                $post['client'] = $client;
                $post['sapv_data'] = $sapv;
                $post['partners'] = $partners;
                $post['form_data'] = $form_data;

                
                $this->generatePdfNew(3,$post,'Sapvfb8lmu',"sapvfb8lmu_pdf.html");
            
            }
            
        }

        
        function medicationsharedAction(){
            set_time_limit(0);
            $this->_helper->layout->setLayout('layout_ajax');
            
            $clientid = $this->clientid;
            $decid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpId($decid);
            $medication = new PatientDrugPlan();
            $modules = new Modules();
            $cocktailsC = new PatientDrugPlanCocktails();
            
            /* ================ CLIENT USER DETAILS ======================= */
            $usr = new User();
            $user_details = $usr->getUserByClientid($clientid, '1', true);

            $cl = new Client();
            $clients_details = $cl->getClientData();
            foreach($clients_details as $clk=>$clv){ 
                $clients_data[$clv['id']]['team_name'] = $clv['team_name']; 
                $clients_data[$clv['id']]['client_name'] = $clv['client_name']; 
            }

            /* ================ MEDICATION ACKNOWLEDGE ======================= */
            if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
            {
                $acknowledge = "1";
                $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
                $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
            
                if(in_array($userid,$approval_users)){
                    $this->view->approval_rights = "1";
                }
                else
                {
                    $this->view->approval_rights = "0";
                }
            }
            else
            {
                $acknowledge = "0";
            }
            $this->view->acknowledge = $acknowledge;
            
            /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
            $medication_blocks = array("actual","isbedarfs","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
            
            /* IV BLOCK -  i.v. / s.c. */
            $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
            if(!$iv_medication_block){
                $medication_blocks = array_diff($medication_blocks,array("isivmed"));
            }
            
            /* TREATMENT CARE BLOCK -  Behandlungspflege*/
            $treatmen_care_block = $modules->checkModulePrivileges("85", $clientid);
            if(!$treatmen_care_block){
                $medication_blocks = array_diff($medication_blocks,array("treatment_care"));
            }
            
            /* NUTRITION  BLOCK - Ernahrung */
            $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
            if(!$nutrition_block){
                $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
            }
            /* SCHMERZPUMPE  BLOCK - Schmerzpumpe */
            $schmerzepumpe_block = $modules->checkModulePrivileges("54", $clientid);
            if(!$schmerzepumpe_block){
                $medication_blocks = array_diff($medication_blocks,array("isschmerzpumpe"));
            }
            
            /* Intervall Medis  BLOCK - Intervall Medis */
            $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
            if(!$scheduled_block){
                $medication_blocks = array_diff($medication_blocks,array("scheduled"));
            }
            
            $this->view->medication_blocks = $medication_blocks;
            
            // get current patient shared medications
            // get patient drugplan
            $medicarr = $medication->getMedicationPlanAll($decid);
            
            foreach ($medicarr as $k=>$pat_meds){
                $own_meds[] = $pat_meds['id'];
                $own_corelation[$pat_meds['id']] =  $pat_meds;
                if( $pat_meds['isschmerzpumpe'] == "1"){
                    $own_cockailsids[] =  $pat_meds['cocktailid'];
                }
                
                $target_source_connection[$pat_meds['id']]['drid'] = $pat_meds['id']; 
                $target_source_connection[$pat_meds['id']]['medication_master_id'] = $pat_meds['medication_master_id']; 
                
                if($pat_meds['source_drugplan_id'] != 0 ){
                    $shared_meds[] = $pat_meds['source_drugplan_id'];

                    $shared_meds_src2id[$pat_meds['source_drugplan_id']] = $pat_meds['id'];
                    $shared_meds_src2medication_master_id[$pat_meds['source_drugplan_id']] = $pat_meds['master_medication_id'];
                    $source_corelation[$pat_meds['source_drugplan_id']] =  $pat_meds;
                    $target_corelation[ $pat_meds['id']] =  $pat_meds['source_drugplan_id'];
                    
                    $source_targed_connection[$pat_meds['source_drugplan_id']]['drid'] = $pat_meds['id']; 
                    $source_targed_connection[$pat_meds['source_drugplan_id']]['medication_master_id'] = $pat_meds['medication_master_id'];
                }
            }
            if(!empty($own_cockailsids)){
                $own_cockailsids = array_unique($own_cockailsids);
                $own_cocktails = $cocktailsC->getDrugCocktails($own_cockailsids );
                foreach($own_cocktails as $k=>$cd){
                    if($cd['source_cocktailid'] != 0 ){
                        $shared_cocktails[] = $cd['source_cocktailid'];
                        $targe_cockt_corelation[$cd['source_cocktailid']] = $cd['id']; 
                    }
                }
            }
            
            
            /*###########################    ##############################*/
            
            $patients_linked = new PatientsLinked();
            $linked_patients = $patients_linked->get_related_patients($ipid);
            
            if ($linked_patients)
            {
                foreach ($linked_patients as $k_link => $v_link)
                {
//                     if($ipid != $v_link['target']){
                        $linked_ipids[] = $v_link['target'];
//                     } 
                    
//                     if($ipid != $v_link['source']){
                        $linked_ipids[] = $v_link['source'];
//                     }
                }
            }
            
            if(!empty($linked_patients)){
                
                $patientmaster = new PatientMaster();
                $ipids_details = $patientmaster->get_multiple_patients_details($linked_ipids,true);
                foreach($ipids_details as $pipid=>$details){
                    $linked_pat_details[$pipid]['epid'] = $details['EpidIpidMapping']['epid'];
                    $linked_pat_details[$pipid]['client_name'] = $clients_data[$details['EpidIpidMapping']['clientid']]['client_name'];
                    $linked_pat_details[$pipid]['patient_name'] = $details['last_name'].', '. $details['first_name'];
                    $linked_pat_details[$pipid]['pat_id'] = $details['id'];
                    $pid2iipid[$details['id']] = $pipid;
                    $ipid2id[$pipid] = $details['id'];
                }
                
                $this->view->patients_details = $linked_pat_details;
                $shared_drg_ids = PatientDrugPlanShare::get_shared($linked_ipids);

                foreach($shared_drg_ids as $ipidp =>$drgs_arr){
                    if($ipidp != $ipid){
                        $shared_p_ipids[] = $ipidp;
                        foreach($drgs_arr as $k=>$drgid){
                            $all_drg_ids[] = $drgid;
                        }
                    }
                }
                if(!empty($shared_p_ipids)){
                    $sipids_details = $patientmaster->get_multiple_patients_details($shared_p_ipids,true);
                    foreach($sipids_details as $pipid=>$details){
                        $pid2iipid[$details['id']] = $pipid;
                        $ipid2id[$pipid] = $details['id'];
                    }
                }

               
               // check in patient meds - if thei correspund with alreadi shared meds
               $all_drg_ids = array_unique($all_drg_ids);
               if(empty($all_drg_ids)){
                   $all_drg_ids[] = "999999";
               }
               $all_drug_details =  $medication->get_details($all_drg_ids);

               
               IF($_REQUEST['dbgs'] == "1" ){
                   print_R($all_drug_details); 
               }
               
               
               
               $listed_meds = array($listed_meds);
               foreach($all_drug_details as $k=>$data){
                    
                    if($data['isbedarfs'] =="1") {
                        $type = "isbedarfs";
                    } elseif ($data['isivmed'] == "1") {
                        $type = "isivmed";
                    } elseif ($data['isschmerzpumpe'] == "1") {
                        $type = "isschmerzpumpe";
                        $cocktail_ids[] = $data['cocktailid'];
                    } elseif ($data['treatment_care'] == "1") {
                        $type = "treatment_care";
                        $treatmen_care_med_ids[] = $data['master_medication_id'];
                    } elseif ($data['isnutrition'] == "1") {
                        $type = "isnutrition";
                        $nutrition_med_ids[] = $data['master_medication_id'];
                    } elseif ($data['scheduled'] == "1") {
                        $type = "scheduled";
                    } else {
                        $type = "actual";
                    }
                        
                    $medications_array[$data['ipid']][$type][$k] = $data;
                    
                    if (in_array($data['id'],$shared_meds))
                    {
                        if (!in_array($data['id'],$listed_meds)){
                            $listed_meds[]= $data['id'];
                             
                            $medications_array[$data['ipid']][$type][$k]['drid'] =  $source_targed_connection[$data['id']]['drid'];
                            $medications_array[$data['ipid']][$type][$k]['hidd_medication'] = $source_targed_connection[$data['id']]['medication_master_id'];
                            $medications_array[$data['ipid']][$type][$k]['source_patient'] = "";
                            
                            // check if file med was edited in source
                            $medications_array[$data['ipid']][$type][$k]['connected'] = "1";
                            
                            
                            if ($data['treatment_care'] == "1"){
                                if($data['medication'] != $source_corelation[$data['id']]['medication'] ){
                                    $medications_array[$data['ipid']][$type][$k]['source_changed'] = "1";
                                } else{
                                    $medications_array[$data['ipid']][$type][$k]['source_changed'] = "0";
                                }
                                
                            } 
                            else{
                                
                                if($data['medication'] != $source_corelation[$data['id']]['medication'] || $data['dosage'] != $source_corelation[$data['id']]['dosage']){
                                    $medications_array[$data['ipid']][$type][$k]['source_changed'] = "1";
                                } else{
                                    $medications_array[$data['ipid']][$type][$k]['source_changed'] = "0";
                                }
                            }
                        }
                    }
                    elseif(in_array($data['source_drugplan_id'],$own_meds))
                    {
                        if (!in_array($data['id'],$listed_meds)){
                            $listed_meds[]= $data['id'];
                            $medications_array[$data['ipid']][$type][$k]['drid'] =  $target_source_connection[$data['source_drugplan_id']]['drid'];
                            $medications_array[$data['ipid']][$type][$k]['hidd_medication'] = $target_source_connection[$data['source_drugplan_id']]['medication_master_id'];
                            $medications_array[$data['ipid']][$type][$k]['source_patient'] =  $ipid2id[$ipid];
                            
                            $medications_array[$data['ipid']][$type][$k]['own_medication'] = $own_corelation[$data['source_drugplan_id']]['medication'];
                            $medications_array[$data['ipid']][$type][$k]['shared_medication'] = $data['medication'] ;
                            
                            
                            // check if file med was edited in source
                            $medications_array[$data['ipid']][$type][$k]['connected'] = "1";
                        
                        
                            if ($data['treatment_care'] == "1"){
                                if($data['medication'] != $own_corelation[$data['source_drugplan_id']]['medication'] ){
                                    $medications_array[$data['ipid']][$type][$k]['source_changed'] = "1";
                                } else{
                                    $medications_array[$data['ipid']][$type][$k]['source_changed'] = "0";
                                }
                        
                            }
                            else
                            {
                        
                                if($data['medication'] != $own_corelation[$data['source_drugplan_id']]['medication'] || $data['dosage'] != $own_corelation[$data['source_drugplan_id']]['dosage']){
                                    $medications_array[$data['ipid']][$type][$k]['source_changed'] = "1";
                                } else{
                                    $medications_array[$data['ipid']][$type][$k]['source_changed'] = "0";
                                }
                            }
                        }
                    }
                    else 
                    {
                        $medications_array[$data['ipid']][$type][$k]['connected'] = "0";
                        $medications_array[$data['ipid']][$type][$k]['drid'] = "";
                    }
                    
                    $drugs_details[$data['id']] = $data;
                }
                
                
                // get schmerzpumpe details
                $cocktail_ids = array_unique($cocktail_ids);
                
                if(count($cocktail_ids) == 0)
                {
                    $cocktail_ids[] = '999999';
                }
                
           
                $cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
            }
            
            IF($_REQUEST['dbgs'] == "1" ){
            }
//                 print_R($medications_array); exit;
            
            
           /*  foreach($medications_array as $pat=>$medss){
                foreach($medss as $mt=>$med){
                    foreach($med as $k=>$md){
                        if($mt == "isschmerzpumpe"){
                            $medications_array_final[$pat][$mt][$md['cocktailid']][]= $md;
                            $medications_array_final[$pat][$mt][$md['cocktailid']]['cocktail']= $cocktails[$md['cocktailid']];
                        }
                        else
                        {
                            $medications_array_final[$pat][$mt][]= $md;
                        }
                    }
                }
            }
            print_R($medications_array_final); exit; */
            
            $this->view->medications_array = $medications_array;

            
            
            
            
            
            
            
            
            if($this->getRequest()->isPost())
            {
                
                $patient_medication_form = new Application_Form_Medication();
                $patient_medication_isnutrition_form = new Application_Form_Nutrition();
                $patient_medication_tr_form = new Application_Form_MedicationTreatmentCare();
                $med_form = new Application_Form_PatientDrugPlan();
                
           
                if(!empty($_POST['drugs'])){
                    

                    foreach($_POST['drugs'] as $source_pat_id => $post_drugdetails_d){
                    
                        foreach($post_drugdetails_d as $type => $med_valuesdd){
                            
                            foreach($med_valuesdd['use'] as $k=>$drigs){
                                if($type !="isschmerzpumpe"){
                                    $_POST['medication_block'][$source_pat_id][$type]['medication'][] = $_POST['drugs'][$source_pat_id][$type]['medication'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type]['hidd_medication'][] = $_POST['drugs'][$source_pat_id][$type]['hidd_medication'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type]['drid'][] = $_POST['drugs'][$source_pat_id][$type]['drid'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type]['edited'][] = $_POST['drugs'][$source_pat_id][$type]['edited'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type]['source_ipid'][] = $pid2iipid[$_POST['drugs'][$source_pat_id][$type]['source_patient'][$k]];
                                    $_POST['medication_block'][$source_pat_id][$type]['source_drugplan_id'][] = $_POST['drugs'][$source_pat_id][$type]['source_drugplan_id'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type]['dosage'][] = $_POST['drugs'][$source_pat_id][$type]['dosage'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type]['comments'][] = $_POST['drugs'][$source_pat_id][$type]['comments'][$k];
                                } else {
                                    $source_cocktailid = $_POST['drugs'][$source_pat_id][$type]['cocktailid'][$k];
                                    $pumpe_number = $source_cocktailid;
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['medication'][] = $_POST['drugs'][$source_pat_id][$type]['medication'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['hidd_medication'][] = $_POST['drugs'][$source_pat_id][$type]['hidd_medication'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['drid'][] = $_POST['drugs'][$source_pat_id][$type]['drid'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['edited'][] = $_POST['drugs'][$source_pat_id][$type]['edited'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['source_ipid'][] = $pid2iipid[$_POST['drugs'][$source_pat_id][$type]['source_patient'][$k]];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['source_drugplan_id'][] = $_POST['drugs'][$source_pat_id][$type]['source_drugplan_id'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['dosage'][] = $_POST['drugs'][$source_pat_id][$type]['dosage'][$k];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['comments'][] = $_POST['drugs'][$source_pat_id][$type]['comments'][$k];
                                    
                                    
                                    if ($targe_cockt_corelation[$source_cocktailid]){
                                        $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['id'] = $targe_cockt_corelation[$source_cocktailid]; // add or edit in the existing shared schmerz
                                    } 
                                    else
                                    {
                                        $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['id'] = "";
                                    }
                                        
                                    
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['description'] = $cocktails[$source_cocktailid]['description'];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['bolus'] = $cocktails[$source_cocktailid]['bolus'];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['max_bolus'] = $cocktails[$source_cocktailid]['max_bolus'];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['flussrate'] = $cocktails[$source_cocktailid]['flussrate'];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['sperrzeit'] = $cocktails[$source_cocktailid]['sperrzeit'];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['carrier_solution'] = $cocktails[$source_cocktailid]['carrier_solution'];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['pumpe_type'] = $cocktails[$source_cocktailid]['pumpe_type'];
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['pumpe_medication_type'] = $cocktails[$source_cocktailid]['pumpe_medication_type'];
                                    
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['source_cocktailid'] = $source_cocktailid;
                                    $_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['source_ipid'] = $pid2iipid[$_POST['drugs'][$source_pat_id][$type]['source_patient'][$k]];
                                }
                            } 
                        }
                    }

                    
//                     print_r($_POST['medication_block']);exit;
                    
                    foreach($_POST['medication_block'] as $source_pat_id => $post_drugdetails){
                        
                        foreach($post_drugdetails as $type => $med_values){
                            
                            if($type == "isschmerzpumpe")
                            {
                                foreach($med_values as $pumpe_number=>$sch_med_values)
                                {
                                    $sch_post_data = $sch_med_values;
                                    foreach($sch_med_values['medication'] as $amedikey => $amedi)
                                    {
                                        if(strlen($amedi) > 0 && empty($sch_med_values['hidd_medication'][$amedikey]) && !empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey]))
                                        {
                            
                                            $sch_post_data['newmids'][$amedikey] = $sch_med_values['drid'][$amedikey];
                                            $sch_post_data['newmedication'][$amedikey] = $amedi;
                                        }
                            
                                        if(strlen($amedi) > 0 && (!empty($sch_med_values['hidd_medication'][$amedikey]) && empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey])))
                                        {
                                            $sch_post_data['newmids'][$amedikey] = $sch_med_values['hidd_medication'][$amedikey];
                                            $sch_post_data['newmedication'][$amedikey] = $amedi;
                                        }
                            
                                        if(strlen($amedi) > 0 && (empty($sch_med_values['hidd_medication'][$amedikey]) && empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey])))
                                        {
                                            $sch_post_data['newmedication'][$amedikey] = $amedi;
                                        }
                                    }
                            
                                    if(is_array($sch_post_data['newmedication']))
                                    {
                                        $dts = $patient_medication_form->InsertNewData($sch_post_data);
                                        foreach($dts as $key => $dt)
                                        {
                                            $sch_post_data['newhidd_medication'][$key] = $dt->id;
                                        }
                                    }
                                    $sch_post_data[$type] =  "1";
                                    $sch_post_data['ipid'] =  $ipid;
                                    if($acknowledge =="1")
                                    {
                                        $sch_post_data['skip_trigger'] = "1";
                                    }
                            
                                    $_POST['add_sets'] = "1";
                                    // save data for each pumpe
                                    $med_form->update_schmerzpumpe_data($sch_post_data);
                            
                            
                                    //find out edited/added medis
                                    foreach($sch_med_values['medication'] as $k_meds => $v_meds)
                                    {
                                        $cust = Doctrine::getTable('PatientDrugPlan')->find($sch_med_values['drid'][$k_meds]);
                                        $list = true; //list curent medi
                                        if($cust)
                                        {
                            
                                            if($cust->dosage != $sch_med_values['dosage'][$k_meds] ||
                                                $cust->medication_master_id != $sch_med_values['hidd_medication'][$k_meds] ||
                                                $cust->verordnetvon != $sch_med_values['verordnetvon'][$k_meds])
                                            {
                                                $list = false; //don`t list curent medi
                                            }
                                        }
                            
                                        if(!array_key_exists($k_meds, $sch_post_data['newmedication']) && $list) //new medis
                                        {
                                            $meds[] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds] . "\n";
                                        }
                                    }
                            
                                    if($list)
                                    {
                                        $course_cocktail_entry ="";
                                        $course_cocktail_entry .= "Kommentar: " . $sch_med_values['cocktail']['description'];
                                        $course_cocktail_entry .= "\n".$this->view->translate('Applikationsweg').": " . $sch_med_values['cocktail']['pumpe_medication_type'];
                                        $course_cocktail_entry .= "\n".$this->view->translate('Flussrate').": " . $sch_med_values['cocktail']['flussrate'];
                                        $course_cocktail_entry .= "\n".$this->view->translate('medication_carrier_solution').": " . $sch_med_values['cocktail']['carrier_solution'];
                            
                                        if($sch_med_values['cocktail']['pumpe_type'] == "pca") {
                                            $course_cocktail_entry .= "\n".$this->view->translate('Bolus').": " . $sch_med_values['cocktail']['bolus'];
                                            $course_cocktail_entry .= "\n".$this->view->translate('Max Bolus').": " . $sch_med_values['cocktail']['max_bolus'];
                                            $course_cocktail_entry .= "\n".$this->view->translate('Sperrzeit').": " . $sch_med_values['cocktail']['sperrzeit'] ;
                                        }
                            
                            
                                        if($acknowledge == "1"){
                                            if(in_array($userid,$approval_users)){
                                                $cust_course = new PatientCourse();
                                                $cust_course->ipid = $ipid;
                                                $cust_course->course_date = date("Y-m-d H:i:s", time());
                                                $cust_course->course_type = Pms_CommonData::aesEncrypt("Q");
                                                $cust_course->course_title = Pms_CommonData::aesEncrypt(addslashes(implode('', $meds).$course_cocktail_entry));
                                                $cust_course->user_id = $userid;
                                                $cust_course->save();
                                            }
                                        }
                                        else
                                        {
                                            $cust_course = new PatientCourse();
                                            $cust_course->ipid = $ipid;
                                            $cust_course->course_date = date("Y-m-d H:i:s", time());
                                            $cust_course->course_type = Pms_CommonData::aesEncrypt("Q");
                                            $cust_course->course_title = Pms_CommonData::aesEncrypt(addslashes(implode('', $meds).$course_cocktail_entry));
                                            $cust_course->user_id = $userid;
                                            $cust_course->save();
                                        }
                                    }
                                }
                            }
                            else
                            {
                                
                                $post_data = $med_values;
                                foreach($med_values['medication'] as $amedikey => $amedi)
                                {
                                    if(strlen($amedi) > 0 && empty($med_values['hidd_medication'][$amedikey]) && !empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey]))
                                    {
                            
                                        $post_data['newmids'][$amedikey] = $med_values['drid'][$amedikey];
                                        $post_data['newmedication'][$amedikey] = $amedi;
                                    }
                            
                                    if(strlen($amedi) > 0 && (!empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
                                    {
                                        $post_data['newmids'][$amedikey] = $med_values['hidd_medication'][$amedikey];
                                        $post_data['newmedication'][$amedikey] = $amedi;
                                    }
                            
                                    if(strlen($amedi) > 0 && (empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
                                    {
                                        $post_data['newmedication'][$amedikey] = $amedi;
                                    }
                                }
                            
                                
                                if(is_array($post_data['newmedication']))
                                {
                                    if($type == 'treatment_care')
                                    {
                                        $dts = $patient_medication_tr_form->InsertNewData($post_data);
                                    }
                                    elseif ($type == 'isnutrition')
                                    {
                                        $dts = $patient_medication_isnutrition_form->InsertNewData($post_data);
                                    }
                                    else
                                    {
                                        $dts = $patient_medication_form->InsertNewData($post_data);
                                    }
                            
                                    foreach($dts as $key => $dt)
                                    {
                                        $post_data['newhidd_medication'][$key] = $dt->id;
                                    }
                                }
                            
                                $post_data[$type] =  "1";
                                $post_data['ipid'] =  $ipid;
                                if($acknowledge =="1" || $type == "deleted")
                                {
                                    $post_data['skip_trigger'] = "1";
                                    $_POST['skip_trigger'] = "1";
                                }
                                $_POST['add_sets'] = "1";
                                // save medication changes
                                if($type == "deleted")
                                {
                                    $med_form->update_multiple_data_deletedmeds($post_data);
                                }
                                else
                                {
                                    $med_form->update_multiple_data($post_data);
                                }
                            
                            }
                            
                        }
                    }
                }
                
                
                $this->_redirect(APP_BASE . "patientnew/medication?id=" . $_REQUEST['id']);
                
            }        
            
            
        }
        
        function medicationsetitemsAction(){
            $clientid = $this->clientid;
            $decid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpId($decid);
            $this->_helper->layout->setLayout('layout_ajax');
            
            if( empty  ($_REQUEST['set_id'])){
                return false;
            }
            
            
            $set_id = $_REQUEST['set_id'];
            $medication_sets_obj = new MedicationsSetsList();
            $medication_sets_arr = $medication_sets_obj->get_medications_sets_details($this->clientid,false,array($set_id));

            if( empty($medication_sets_arr)) {
                return false;
            }
            $set_values = $medication_sets_arr[$set_id];
            $set_items = $medication_sets_arr[$set_id]['MedicationsSetsItems'];
            $set_indikation = $set_values['set_indication']; 
            
            $client_medication_extra = array();
            $medication_dosage_forms_custom = MedicationDosageform::client_medication_dosage_form($this->clientid, true);
            foreach($medication_dosage_forms_custom as $k=>$df){
                $client_medication_extra['dosage_form_custom'][$df['id']] = $df['dosage_form'];
            }

            $medication_frequency_custom = MedicationFrequency::client_medication_frequency($this->clientid, true);
            foreach($medication_frequency_custom as $k=>$df){
                $client_medication_extra['frequency'][$df['id']] = $df['frequency'];
            }
            
            $medication_type_custom = MedicationType::client_medication_types($this->clientid, true);
            foreach($medication_type_custom as $k=>$dft){
                $client_medication_extra['medication_type'][$dft['id']] = $dft['type'];
            }
            
            $medication_indication_arr = MedicationIndications::client_medication_indications($this->clientid);
            foreach($medication_indication_arr as $ki=>$vi){
                $client_medication_extra['medication_indication'][$vi['id']]['id'] = $vi['id'];
                $client_medication_extra['medication_indication'][$vi['id']]['name'] = $vi['indication'];
                $client_medication_extra['medication_indication'][$vi['id']]['color'] = $vi['indication_color'];
            }
            
            foreach($set_items as $k => $item)
            {
                $set_items[$k]['indication'] = $client_medication_extra['medication_indication'][$set_indikation];
                
                foreach ($item['frequency'] as $kf=>$fid)
                {
                    $set_items[$k]['frequency_array'][$client_medication_extra['frequency'][$fid]] = $client_medication_extra['frequency'][$fid];// EDIT - HERE SHOW VALUES
                }
                foreach ($item['dosage'] as $ksf=>$fids)
                {
                    $set_items[$k]['dosage_array'][$fids] = $fids;// EDIT - HERE SHOW VALUES
                }
                
                foreach ($item['med_dosage_form'] as $kd=>$dfid)
                {
                    if( array_key_exists($dfid,$client_medication_extra['dosage_form_custom']) ){
                        $set_items[$k]['med_dosage_form_array'][$dfid] =  $client_medication_extra['dosage_form_custom'][$dfid];
                    }
                }
                
                foreach ($item['type'] as $kdt=>$dfidt)
                {
                    if( array_key_exists($dfidt,$client_medication_extra['medication_type']) ){
                        $set_items[$k]['medication_type_array'][$dfidt] =  $client_medication_extra['medication_type'][$dfidt];
                    }
                }
            }
//             print_r($client_medication_extra); 
//             print_r($set_items); exit;
            
//             dd($set_items);
            $this->view->set_items = $set_items; 
            $this->view->set_type = $_REQUEST['set_type']; 
        }
        
        function medicationsetsAction(){
            set_time_limit(0);
            $this->_helper->layout->setLayout('layout_ajax');
            
            $clientid = $this->clientid;
            $userid = $this->userid;
            $decid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpId($decid);
            $medication = new PatientDrugPlan();
            $modules = new Modules();
            $cocktailsC = new PatientDrugPlanCocktails();
            
            /* ================ CLIENT USER DETAILS ======================= */
            $usr = new User();
            $user_details = $usr->getUserByClientid($clientid, '1', true);

            $cl = new Client();
            $clients_details = $cl->getClientData();
            foreach($clients_details as $clk=>$clv){ 
                $clients_data[$clv['id']]['team_name'] = $clv['team_name']; 
                $clients_data[$clv['id']]['client_name'] = $clv['client_name']; 
            }

            /* ================ MEDICATION ACKNOWLEDGE ======================= */
            if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge ISPC - 1483
            {
                $acknowledge = "1";
                $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
                $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
            
                if(in_array($userid,$approval_users)){
                    $this->view->approval_rights = "1";
                }
                else
                {
                    $this->view->approval_rights = "0";
                }
            }
            else
            {
                $acknowledge = "0";
            }
            $this->view->acknowledge = $acknowledge;
            
            //Get all sets
            if(!empty($_REQUEST['set_type'])){
                $set_type = $_REQUEST['set_type'];
            }  
            
            $this->view->set_type = $set_type;
            $medication_sets_obj = new MedicationsSetsList();
            $medication_sets_arr = $medication_sets_obj->get_medications_sets_details($this->clientid,$set_type);
            
            
            if(empty($medication_sets_arr)){
                return false;
            }
            
            $sets = array();
            $sets['set_drop'][] = "";
            foreach($medication_sets_arr as $set_id=>$set_data){
                if(isset($set_data['MedicationsSetsItems']) && !empty($set_data['MedicationsSetsItems'])){
                        
                    $sets['set_drop'][$set_id] = $set_data['title'];
                    $sets['set_items'][$set_id] = $set_data['MedicationsSetsItems'];
                }
            }
            $this->view->sets = $sets;
            
            
            
            if ($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
 
                if($acknowledge == "1"){
                    $post['skip_trigger'] = "1";
                    if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA'){
                        // do nothing
                    }
                    else
                    {
                        $this->_redirect(APP_BASE . "error/previlege");
                    }
                }

                $med_type_block = $post['set_type'];
                $post['medication_block'] = array();
                if(!empty($post['item']) && !empty($post['set_type'])){
                    
                    $r = 1 ; 
                    foreach($post['item'] as $med_row=>$med_details)
                    {
                        if(isset($med_details['add_medication']) && $med_details['add_medication'] == "1"){

                            $post['medication_block'][$med_type_block]['medication'][$r] = $med_details['medication']; 
                            $post['medication_block'][$med_type_block]['medication'][$r] = !empty($med_details['medication']) ? $med_details['medication'] : "-"; 
                            $post['medication_block'][$med_type_block]['hidd_medication'][$r] = ""; 
                            $post['medication_block'][$med_type_block]['drid'][$r] = ""; 
                            $post['medication_block'][$med_type_block]['drug'][$r] =  $med_details['drug']; 
                            $post['medication_block'][$med_type_block]['dosage'][$r] =  $med_details['dosage'];
                             
                            $post['medication_block'][$med_type_block][$r]['dosage_interval'] =  $med_details['dosage_interval']; 
                            
                            $post['medication_block'][$med_type_block]['dosage_form'][$r] =  $med_details['dosage_form']; 
                            $post['medication_block'][$med_type_block]['type'][$r] =  $med_details['type']; 
                            $post['medication_block'][$med_type_block]['escalation'][$r] =  $med_details['escalation'];
                            
                            // NEW
                            $post['medication_block'][$med_type_block]['indication'][$r] =  $med_details['indication'];
                             
                            $escalation = "";
                            $escalation = !empty($med_details['escalation']) ? $med_details['escalation'].". ESKALATION" : "" ;
                            //$post['medication_block'][$med_type_block]['comments'][$r] =  $med_details['comments']." \n ".$escalation; 
                            $post['medication_block'][$med_type_block]['comments'][$r] =  $med_details['comments']; 
                            
                        }
                        
                        $r++;
                    }
                    
                }

//                 dd($post);
                if(!empty($post['medication_block'])){
                
                    $_POST['medication_block'] = $post['medication_block'];
                    $post_medication_data = $post;
                    $post_medication_data['clientid'] = $clientid;
                    $post_medication_data['userid'] = $userid;
                    $med_form = new Application_Form_PatientDrugPlan();
                    
                    
                    $med_form->save_medication($ipid,$post_medication_data);
                
                }
                
                $this->_redirect(APP_BASE . "patientnew/medication?id=" . $_REQUEST['id']);
                exit;
            }
        }
        
        
/**
 * 
 * @param array $array
 * @param array $result
 * @param string $pad
 */
       private  function printArray($array = array(), &$result,  $pad = ''){
        	
       		if ($pad !="") $pad .= "_";
       	
        	foreach ($array as $key => $value){
        		
        		if(is_array($value)){      			
        			self::printArray($value , $result, $pad . $key);
        		}else{	
        			$result[$pad . $key] = $value;	
        		}
        	
        	}
        }

        
       // work disability
		public function  muster1a1Action(){
						

			//this is a post
			if($this->getRequest()->isPost())
	       	{
	       		switch($_POST['post_status']){
	       			
	       			case "reset":
	       				$save_form = new Application_Form_Munster1a();
	       				$save_form->mark_as_completed($this->ipid);
	       				
	       				break;
	       			
	       			case "save":
	       				
	       				/*
	       				 * do not save this fields 
	       				 * 1 - they must be encrypted
	       				 * 2 - can be edited only for pdf print
	       				 * 
	       				 * insurance_company_name
	       				 * patient_name
	       				 * geb
	       				 * kassen_nr
	       				 * versicherten_nr
	       				 * status
	       				 * betriebsstatten_nr
	       				 * arzt_nr
	       				 * topdatum
	       				 * post_status
	       				 */
	       				unset( $_POST['post_status'], 
	       				$_POST['topdatum'],
	       				$_POST['arzt_nr'],
	       				$_POST['betriebsstatten_nr'],
	       				$_POST['status'],
	       				$_POST['versicherten_nr'],
	       				$_POST['kassen_nr'],
	       				$_POST['geb'],
	       				$_POST['patient_name'],
	       				$_POST['insurance_company_name']
	       				);
	
	       				$result = array();
	       				unset($_POST['post_status']);
	       				$this->printArray($_POST , $result, '' );
	       				$save = array();
	       				foreach ($result as $input=>$value){	
	       					$save[] =  array("input_name" => $input,
	       							"input_value" => $value,
	       							'ipid'=>  $this->ipid,
	       							'completed_date' => date("Y-m-d")
	       					);
	       				}
	       				
	       				
	       				$save_form = new Application_Form_Munster1a();
	       				//@TODO: verify function
	       				if ((int)$_POST['lastformular_formular_id'] > 0){
	       					$save_form -> update_data($this->ipid , $save , $_POST['lastformular_formular_id']);
	       				}else{
	       					$save_form -> insert_multiple_data($this->ipid , $save );
	       				}
	       				
	       				//$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_GET['id']);
	       				$this->forward("patientcourse","patientcourse");
	       				break;
	       			
	       			case "pdf_print":
	       				$this->generatePdfNew(3, $_POST, 'muster1a1_pdf', "muster1a1_pdf.html");
	       				exit;
	       				break;
	       			
	       			case "pdf_pre_print":      					
	       				$this->generatePdfNew(3, $_POST, 'muster1a1_pre', "muster1a1_pre_pdf.html");
	       				exit;
	       				break;
	       		
	       		}	  		
	
			}
			
			//hardcoded non-editables
			
			/* --------------------Check for MultipleArzstemple----------------------------- */
			$multiplestamps_previleges = new Modules();
			if($multiplestamps_previleges->checkModulePrivileges("64", $this->clientid))
			{
				$multiplestamps_option = true;
			}
			else
			{
				$multiplestamps_option = false;
			}
			$this->view->multiplestamps_option = $multiplestamps_option;
			$this->view->lastformular_stampusers = $this->userid;
						
			
			/* --------------------------------User select------------------------------------- */
			if($this->usertype == 'SA' || $this->usertype == 'CA')
			{
				$isadmin = '1';
			}
			
			$users = new User();
			$userarray = $users->getUserByClientid($this->clientid);
			$userarraylast[] = $this->view->translate('selectuser');
			$userarraylast_ids = array();
				
			foreach($userarray as $user)
			{
				$userarraylast[$user['id']] = trim($user['user_title']) . " " .trim($user['last_name']) . ", " . trim($user['first_name']);
				$userarraylast_ids[] = $user['id'];
			
				if ($user['id'] == $this->userid){
					$this->view->betriebsstatten_nr = $user['betriebsstattennummer'];
					$this->view->arzt_nr = $user['LANR'];
				}
			
			}
			$this->view->users = $userarraylast;
			
			if($isadmin == 1)
			{
				$showselect = 1;
			}
			else
			{
				$showselect = 1; // show select to all
			}
			
			$this->view->showselect = $showselect;
			
			$ustamp = new UserStamp();
			$multipleuser_stamp = $ustamp->getAllUsersActiveStamps($userarraylast_ids);
			
			foreach($multipleuser_stamp as $ks => $uspamp)
			{
				$users_mstamps[$uspamp['userid']]['user_id'] = $uspamp['userid'];
				$users_mstamps[$uspamp['userid']]['user_name'] = $userarraylast[$uspamp['userid']];
				$users_mstamps[$uspamp['userid']]['user_stamps'][$uspamp['id']] = $uspamp['stamp_name'];
			}
			$this->view->users_mstamps = $users_mstamps;
			
				
				
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($this->ipid);
				
			$this->view->insurance_company_name = $healthinsu_array[0]['company_name'];
			$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];
			$this->view->insurance_status = $healthinsu_array[0]['insurance_status'];
				
			$insucom = new HealthInsurance();
			$insucomarray = $insucom->getCompanyinfofromId($healthinsu_array[0]['companyid']);
			$this->view->kvnumber = $healthinsu_array[0]['kvk_no'];
			
				
			$parr = $this->_patientMasterData;
							
			$this->view->patientname = $parr['last_name'] . ", " . $parr['first_name'] . "\n" . $parr['street1'] . "\n" . $parr['zip'] . "&nbsp;" . $parr['city'];
			$this->view->birthdate = $parr['birthd'];
			$this->view->patientname1 = $parr['last_name'] . ", " . $parr['first_name'];
			$this->view->patietnaddress = $parr['street1'] . "&nbsp;" . $parr['zip'] . "\n" . $parr['city'];
				
			/* formular history */
			$fileupload = new PatientFileUpload();
			$form_files = $fileupload->get_muster1a_file_data($this->ipid);
			$this->view->muster1a_files_history = $form_files;
			
			
			//fetch formulars data from last edited
			$munster1a = new Munster1a();
			if ( $munster1a_data = $munster1a->get_munster1a_patient_data($this->ipid , true) ){
				$last_formular_id = max(array_keys($munster1a_data));	
				
				$munster1a_data[$last_formular_id]['formular_id'] = $last_formular_id;
				
				$this->retainValues($munster1a_data[$last_formular_id] ,"lastformular_");
				
	
			}else{
				//no old formular, begin new formular
				$this->view->lastformular_formular_id = 0; 	
	
				$dg = new DiagnosisType();
				$darr = $dg->getDiagnosisTypes($this->clientid, '"HD"');
				
				if(!empty($darr)){
					$darr_id = array_column($darr, 'id');
					if (!empty($darr_id)){
						$darr_id = implode(",", $darr_id);
						$diagns = new PatientDiagnosis();
						$a_diagno = $diagns->getFinalData($this->ipid, $darr_id);
						$a_diagno = array_values($a_diagno);
						foreach ($a_diagno as $k=>$v){
							$this->view->{"lastformular_icd_10_code_".$k} = $v['icdnumber'];
						}
						
						
					}
				}
				
				
				$date = date("dmY");
				$date = str_split(date("dmy"));
				foreach($date as $k=>$v){
					$this->view->{"lastformular_arbeitsunfafestgestellt_".$k} = $v;
				}
				
			}
				
	}
		
	/* ISPC-1884
	 * duplicate the "Medikamenten-Bestellung" button and function on medi page.
	 */
		public function reciperequestAction()
		{
			$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
			
			$previleges = new Modules();
			
			$pharmacyorder = $previleges->checkModulePrivileges("150", $this->clientid);

			if(! $pharmacyorder ) {
				$this->redirect(APP_BASE . 'error/previlege', array("exit"=>true));
			}
			
			//this is a post
			if($this->getRequest()->isPost())
			{
				
				$form_post =  array();
				$form_post['data'] = $_POST;
				$form_post['epid'] = $this->epid;
				$form_post['clientid'] = $this->clientid;
				$form_post['userid'] = $this->userid;
				$form_post['ipid'] = $this->ipid;
				$form_post['patientMasterData'] = $this->_patientMasterData;
				 
				$PatientRecipeRequest_Form = new Application_Form_DoctorRecipeRequest();
				$res = $PatientRecipeRequest_Form->InsertData($form_post);
				
 				$this->redirect(APP_BASE . "patientcourse/patientcourse?id=" . $this->enc_id , array("exit"=>true)); // . "&res=".$res
				
				return;
			}
			
			//this is NOT a post
			$medis = array();
			$medic = new PatientDrugPlan();
			$medicarr = $medic->getPatientDrugPlan($this->dec_id);
			
			if (is_array($medicarr)) {
				
				$medication_master_id_arr = array_column($medicarr, 'medication_master_id');
			
				$med = new Medication();
				$medarr = $med->getMedicationById($medication_master_id_arr);
				foreach ($medarr  as $k=>$v) {
					$medis [ $v['id'] ] = $v['name'];
				}
				
			}
			asort($medis, SORT_STRING);
			$this->view->medis = $medis;
			$this->view->jsmedis = json_encode($medis);
			
			/*
			$pharmacyusersarr = array( 
					"" => $this->view->translate('select'), 
					$selectbox_separator_string['all'] => $this->view->translate('all')
			);
					
			
			$usergroup = new Usergroup();
			$todogroups = $usergroup->getClientGroups($this->clientid);
			$grouparraytodo = array();
			foreach ($todogroups as $group)
			{
				$grouparraytodo[$selectbox_separator_string['group'] .  $group['id']] = trim($group['groupname']);
			}
			
			$users = new User();
			$userarray = $users->getUserByClientid($this->clientid);
			$users->beautifyName($userarray);
						
			$userarraytodo = array();
			foreach ($userarray as $user)
			{
				$userarraytodo[$selectbox_separator_string['user'] . $user['id']] = $user['nice_name'];
			}		
			
			asort($userarraytodo);
			asort($grouparraytodo);

			$pharmacyusersarr[$this->view->translate('group_name')] = $grouparraytodo;
			$pharmacyusersarr[$this->view->translate('users')] = $userarraytodo;
			
			$user_pseudo =  new UserPseudoGroup();
			$user_ps =  $user_pseudo->get_pseudogroups_for_todo($this->clientid);
			$pseudogrouparraytodo = array();
			if ( ! empty ($user_ps)) {
				foreach($user_ps as $row) {
					$pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = $row['servicesname'];
				}
				
				$pharmacyusersarr[$this->view->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
			}

			$this->view->pharmacyusers = $pharmacyusersarr;
			*/
			
			//ISPC-2502 Lore 13.12.2019
			//$this->view->pharmacyusers = $this->get_nice_name_multiselect();
/* 			$groupandusers_arr = $this->get_nice_name_multiselect();
			$only_users_arr = array();
			$only_users_arr = $groupandusers_arr['Benutzer'];
			$this->view->pharmacyusers = $only_users_arr; */
			//... end 
			//ISPC-2502 Lore 21.10.2020
			$allow_groups = $previleges->checkModulePrivileges("245", $this->clientid);
			if($allow_groups){
			    $this->view->pharmacyusers = $this->get_nice_name_multiselect();
			}else {
			    $groupandusers_arr = $this->get_nice_name_multiselect();
			    $only_users_arr = array();
			    $only_users_arr = $groupandusers_arr['Benutzer'];
			    $this->view->pharmacyusers = $only_users_arr;
			}
			//

			
			//get assigned users start
			$assigned_id_arr =  array();
			$patientMasterData = $this->_patientMasterData;
			
			if ( ! empty($patientMasterData['PatientQpaMapping'])) {
				$assigned_id_arr = array_column($patientMasterData['PatientQpaMapping'], 'userid');				
				$assigned_id_arr = preg_replace('/^/', $selectbox_separator_string['user'], $assigned_id_arr);			
				
			} else {
				
				$assignid = Doctrine_Query::create()
				->select('id,userid')
				->from('PatientQpaMapping')
				->where("epid = :epid");
				$assignidarray = $assignid->fetchArray(array("epid"=>$this->epid ));
				if (count($assignidarray) >0) {				
					$assigned_id_arr = array_column($assignidarray, 'userid');
					$assigned_id_arr = preg_replace('/^/', $selectbox_separator_string['user'], $assigned_id_arr);
						
				}
			}
				
			$this->view->assigned_users = json_encode($assigned_id_arr);

						
			
		}
		
		
		private function get_nice_name_multiselect ()
		{
				
			$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
				
			$todousersarr = array(
					"0" => $this->view->translate('select'),
					$selectbox_separator_string['all'] => $this->view->translate('all')
			);
				
			$usergroup = new Usergroup();
			$todogroups = $usergroup->getClientGroups($this->logininfo->clientid);
			$grouparraytodo = array();
			foreach ($todogroups as $group)
			{
				$grouparraytodo[$selectbox_separator_string['group'] .  $group['id']] = trim($group['groupname']);
			}
		
			if (isset( $this->{'_patientMasterData'}['User'])){
				$userarray = $this->{'_patientMasterData'}['User'];
			} else {
				$users = new User();
				$userarray = $users->getUserByClientid($this->logininfo->clientid);
			}
			User::beautifyName($userarray);
				
			$userarraytodo = array();
			foreach ($userarray as $user)
			{
				// TODO-1286
				if($user['isactive'] == 0 ){
					$userarraytodo[$selectbox_separator_string['user'] . $user['id']] = $user['nice_name'];
				}
			}
		
			asort($userarraytodo);
			asort($grouparraytodo);
				
			$todousersarr[$this->view->translate('group_name')] = $grouparraytodo;
			$todousersarr[$this->view->translate('users')] = $userarraytodo;
		
			$user_pseudo =  new UserPseudoGroup();
			$user_ps =  $user_pseudo->get_pseudogroups_for_todo($this->logininfo->clientid);
			$pseudogrouparraytodo = array();
			if ( ! empty ($user_ps)) {
		
				//pseudogroup must have users in order to display
				$user_ps_ids =  array_column($user_ps, 'id');
				$user_pseudo_users = new PseudoGroupUsers();
				$users_in_pseudogroups = $user_pseudo_users->get_users_by_groups($user_ps_ids);
		
				foreach($user_ps as $row) {
					if ( ! empty($users_in_pseudogroups[$row['id']]))
						$pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = $row['servicesname'];
				}
					
				$todousersarr[$this->view->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
			}
		
			return $todousersarr;
		}
		
		
	

	/**
	 * 
	 * @throws Exception
	 */
	public function datamatriximportAction() {
				
		$form = new Application_Form_Datamatrix();
				
		$default_result = array("success"=> false, "message" => "first error, something is very wrong");
		
		//only xhr allowed
		if ( ! $this->getRequest()->isXmlHttpRequest()) {
			throw new Exception( __METHOD__ . ' : ! isXmlHttpRequest');
		}
		
		if ($this->getRequest()->isPost()) {
		
			$post = $this->getRequest()->getPost();
			
			$post['ipid'] =  ! isset($post['ipid']) ? $this->ipid : $post['ipid'];
			
			$result = $form->process_step($post);

			if ( is_array($result) ) {
				$this->_helper->json->sendJson($result);
				exit; // for readability
				
			} else {
				$this->_helper->json->sendJson($default_result);
				exit; // for readability
			}
			
		} else {
			throw new Exception( __METHOD__ . ' : ! isPost');
		}
			
	}
	
	
	

	/**
	 * todo: a list imported xml action will need to be added, so client can see all that was imported/patient
	 * as of now just a basic print was done
	 */
	public function datamatrixexportAction() 
	{
	
		$pdi_obj = new PatientDatamatrixImport();
		
		$history = $pdi_obj->getByIpid( array( $this->ipid ));
		
		$table_html = $this->view->tabulate($history[$this->ipid]);

		echo $table_html;
		exit;		
	}
	
	
	/**
	 * 
	 * @param unknown $chk
	 * @param unknown $post
	 * @param unknown $pdfname
	 * @param unknown $filename
	 * @throws Exception
	 *  @deprecated - new version used now 2.6 ISPC-2551 ANcuta 31.03.2020
	 */
	private function _generatePdfNew_datamatrix_v24($chk, $post, $pdfname, $filename)
	{
	    
		if ( empty($post['allow_print']['medication_types']) && empty($post['allow_print']['medication_pumpe'])) {
			//nothing to print
			throw new Exception( __METHOD__ . ' you selected no medication to print', 1 );
			return;
		}
			
	
		$decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		$ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpid($decid);
			
		$excluded_keys = array(
				'stamp_block'
		);
			
		if ( ! $this->_patientMasterData && ! is_array($var)) {
			$patientmaster = new PatientMaster();
			$patientinfo = $patientmaster->getMasterData($decid, 1);
			$this->_patientMasterData = $patientmaster->get_patientMasterData();
		}
			
		// this are used in $MP_data['A']
		// if by doctor
		$nice_doctor = User::getUsersNiceName( array($this->userid), $this->clientid, array( 'phone', 'mobile', 'LANR', 'street1', 'zip', 'city', 'emailid' ) );
		$nice_doctor = $nice_doctor[ $this->userid ];

		// TODO-1284
		$client_details = Client::getClientDataByid($this->clientid);
		if( ! empty($client_details)){
			$nice_team = $client_details[0];
		}
		 
		// this are used in $MP_data['O']
		$latest_vital_signs = FormBlockVitalSigns::get_patients_chart_last_values($ipid);
			
		//do we need something from the client? we could display some client info in the footer
		//$clientinfo = Pms_CommonData::getClientData($this->clientid);
	
			
		$MP_data = array(
					
				'v' => '241',
				'l' => 'de-DE',
					
				'P' => array(),
				'A' => array(),
				'O' => array(),
				'S' => array(),
					
		);
			
		$MP_data['P'] =  array(
				'g' => $this->_patientMasterData['first_name'],
				'f' => $this->_patientMasterData['last_name'],
				'b' => $this->_patientMasterData['birthd'],
		);
			
		if ( ! empty($this->_patientMasterData['egk']) ) {
			$MP_data['P'] ['egk'] =  $this->_patientMasterData['egk'] ;
		}
			
		if ( ! empty($this->_patientMasterData['sex']) ) {
			$MP_data['P'] ['s'] = $this->_patientMasterData['sex'] == "1" ? "M" : ($this->_patientMasterData['sex'] == "1" ? "W" : "X");
		}
			
		if ( ! empty($this->_patientMasterData['title']) ) {
			$MP_data['P'] ['t'] = $this->_patientMasterData['title'];
		}
			
		if ( isset($post['patient_allergies']) && ! empty($post['patient_allergies']['allergies_comment']) && trim($post['patient_allergies']['allergies_comment']) != "Keine Allergien / Kommentare") {
		    $MP_data['O'] ['ai'] = substr(utf8_decode(Pms_CommonData::br2nl($post['patient_allergies']['allergies_comment'])), 0 , 100);
		}
			
			
			
		if ( isset($latest_vital_signs[$ipid]) && ! empty($latest_vital_signs[$ipid])) {
	
			if ( ! empty($latest_vital_signs[$ipid]['weight']) ) {
				$MP_data['O'] ['w'] = floatval($latest_vital_signs[$ipid]['weight']);
			}
	
			if ( ! empty($latest_vital_signs[$ipid]['height']) ) {
				$MP_data['O'] ['h'] = floatval($latest_vital_signs[$ipid]['height']);
			}
	
			if ( ! empty($latest_vital_signs[$ipid]['creatinine']) ) {
				$MP_data['O'] ['c'] = $latest_vital_signs[$ipid]['creatinine'];
			}
		}
			
		$nice_doctor = array();
		if ( isset($nice_doctor) && ! empty($nice_doctor)) {
	
			if ( ! empty($nice_doctor['nice_name']) ) {
				$MP_data['A'] ['n'] = $nice_doctor['nice_name'];
			}
	
			if ( ! empty($nice_doctor['LANR']) ) {
				$MP_data['A'] ['lanr'] = $nice_doctor['LANR'];
			}
	
			if ( ! empty($nice_doctor['street1']) ) {
				$MP_data['A'] ['s'] = $nice_doctor['street1'];
			}
	
			if ( ! empty($nice_doctor['zip']) ) {
				$MP_data['A'] ['z'] = $nice_doctor['zip'];
			}
	
			if ( ! empty($nice_doctor['city']) ) {
				$MP_data['A'] ['c'] = $nice_doctor['city'];
			}
	
			if ( ! empty($nice_doctor['phone']) || ! empty($nice_doctor['mobile'])) {
				$MP_data['A'] ['p'] = ! empty($nice_doctor['phone'])?  $nice_doctor['phone'] : $nice_doctor['mobile'];
			}
	
			if ( ! empty($nice_doctor['emailid'])) {
				$MP_data['A'] ['e'] = $nice_doctor['emailid'];
			}
		} 

		if ( isset($nice_team) && ! empty($nice_team)) {
	
			if ( ! empty($nice_team['team_name']) ) {
				$MP_data['A'] ['n'] = $nice_team['team_name'];
			}
	
// 			if ( ! empty($nice_team['LANR']) ) {
// 				$MP_data['A'] ['lanr'] = $nice_team['LANR'];
// 			}
	
			if ( ! empty($nice_team['street1']) ) {
				$MP_data['A'] ['s'] = $nice_team['street1'];
			}
	
			if ( ! empty($nice_team['postcode']) ) {
				$MP_data['A'] ['z'] = $nice_team['postcode'];
			}
	
			if ( ! empty($nice_team['city']) ) {
				$MP_data['A'] ['c'] = $nice_team['city'];
			}
	
			if ( ! empty($nice_team['phone']) || ! empty($nice_team['mobile'])) {
				$MP_data['A'] ['p'] = ! empty($nice_team['phone'])?  $nice_team['phone'] : $nice_team['mobile'];
			}
	
			if ( ! empty($nice_team['emailid'])) {
				$MP_data['A'] ['e'] = $nice_team['emailid'];
			}
		}
	
		$my_medication_groups = PatientDrugPlan::$KBV_BMP2_ZWISCHENUEBERSCHRIFT_ASSOC;
		$my_medication_groups_flipped = array_flip($my_medication_groups);
// 		die_claudiu($post, $post['allow_print']['medication_types']);

		// TODO-2829 ISPC : Bundeseinheitlicher Medikationsplan in RP_Worms Ancuta 07.02.2020
		// Administrator: we remove the "PUMPE" section completly from that plan. as the fields are too many, for too less printable fields.
		if( in_array("isschmerzpumpe", $post['allow_print']['medication_types'])
		    && ! empty($post['medications_array'] ['isschmerzpumpe']))
		{
		    if (($key = array_search('isschmerzpumpe', $post['allow_print']['medication_types'])) !== false) {
		        unset($post['allow_print']['medication_types'][$key]);
		    }
		}
		//--- 
		
		//insert all isschmerzpumpe as single groups ... with _%pumpe_no% 
		if( in_array("isschmerzpumpe", $post['allow_print']['medication_types']) 
				&& ! empty($post['medications_array'] ['isschmerzpumpe'])) 
		{
			$pumpe_no = 0;
			
			foreach ( $post['medications_array'] ['isschmerzpumpe'] as $grup_pumpe) {
				
				$post['medications_array'] ['isschmerzpumpe' . '_' . $pumpe_no ] = $grup_pumpe;
				
				$post['allow_print']['medication_types'][] = 'isschmerzpumpe' . '_' . $pumpe_no;
				
				$pumpe_no ++;
			}
		}

		foreach ($post['allow_print']['medication_types'] as $medication_types) {
			if ( ! empty($post['medications_array'] [$medication_types])) {

				if ( $medication_types == 'isschmerzpumpe') {
					continue;
				}
				
				
				$MP_S = array();//one group in $MP_data['S']
				$MP_S_M = array(); //$MP_data['S'] [] ['M']
				
				$group_title = $medication_types;
				if ( strpos($medication_types, 'isschmerzpumpe') === 0) {
					$group_title = 'isschmerzpumpe';
				}
				
				
				if ( isset($my_medication_groups_flipped[$group_title]) 
						&& ! empty($my_medication_groups_flipped[$group_title])
						&& is_numeric($my_medication_groups_flipped[$group_title])){
					$MP_S['c'] = $my_medication_groups_flipped[$group_title];
				} else {
					$MP_S['t'] = $this->translate($group_title . " medication title");
				}
				
				
				
				foreach ( $post['medications_array'] [$medication_types] as $row_medication) {
	
					$MP_S_M_r = array();
					/*
					 $MP_S_M_r = array();//on row in $MP_data['S'] [] ['M']
					 $MP_S_M_r['p'] pzn
					 Handelsname = $MP_S_M_r['a'] drug name
					 $MP_S_M_r['f'] dosage form pharmaceutical code as IFA code
					 $MP_S_M_r['fd'] freetext dosage form if not IFA
					 $MP_S_M_r['m'] morning dosage schedule
					 $MP_S_M_r['n'] noon dosage schedule
					 $MP_S_M_r['v'] evening dosage schedule
					 $MP_S_M_r['h'] night dosage schedule
					 $MP_S_M_r['t'] freetext dosage schedule if not m,n,v,h
					 Einheit = $MP_S_M_r['du'] dosage unit code
					 Einheit = $MP_S_M_r['dud'] freetext dosage unit code if not du
					 Hinweise = $MP_S_M_r['i'] freetext notes, instructions of use, storage, ingestion etc
					 Grund = $MP_S_M_r['r'] freetext reason for treatment
					 lineunder = $MP_S_M_r['x'] freetext aditional line info
	
					 max 3 W - if active substance is changed, pzn should be the same and the info introduced here
					 $MP_S_M_r['W'] = array(
					 'w', name of an active substance
					 's' freetext active strength
					 )
					 //wichtige angaben
					 $MP_S_M_r['X'] = array(
					 't' text without reference to a mdication entry
					 )
					 //receipt
					 $MP_S_M_r['R'] = array(
					 't' text without reference to a mdication entry
					 )
					*/
	
					//get from pzn
					if ( ! empty($post['medication_master'] [$row_medication['medication_master_id']] ['pzn'] )
							&& $post['medication_master'] [$row_medication['medication_master_id']] ['pzn'] !="00000000")
					{
						$MP_S_M_r['p'] = $post['medication_master'] [$row_medication['medication_master_id']] ['pzn'];
					}
					else {
						//get from what user has written
						if($row_medication['treatment_care'] == "1" || $row_medication['isnutrition'] == "1" ){
    					    $MP_S_M_r['a'] = $row_medication['medication'];// drug name // 23.01.2019 changed by Ancuta - as this 
						}else{
						  $MP_S_M_r['a'] = $post['medication_master'] [$row_medication['medication_master_id']] ['name'];// drug name
						}
						
						//this is not 100% ok
						if ( ! empty( $row_medication['drug'] ) || ! empty($row_medication['concentration'])){
							$MP_S_M_r['W'] = array(
									'w' => $row_medication['drug'],
									's' => $row_medication['concentration']. " ". $row_medication['unit'],
							);
						}
					}
	
					
	
	
					if ( ! empty($row_medication['dosage']) && is_array($row_medication['dosage'])) {
					    
					    //    TODO-2071 NEW RUSES APPLIED - 23.01.2019 Ancuta
					    					 
// 						$dosages_grouped = $this->_groupConcatDosages($row_medication['dosage']);
// 						if ( ! empty($dosages_grouped)) {
// 							if (! empty($dosages_grouped['m'])) { $MP_S_M_r['m'] = $dosages_grouped['m']; }
// 							if (! empty($dosages_grouped['d'])) { $MP_S_M_r['d'] = $dosages_grouped['d']; }
// 							if (! empty($dosages_grouped['v'])) { $MP_S_M_r['v'] = $dosages_grouped['v']; }
// 							if (! empty($dosages_grouped['h'])) { $MP_S_M_r['h'] = $dosages_grouped['h']; }
// 						}
							
					    $dosages_assoc = $this->_dosageIntervallAssociation($row_medication['dosage']);
						if ( ! empty($dosages_assoc['intervals']) && empty($row_medication['dosage_alt'])) {
							if (! empty($dosages_assoc['intervals']['m'])) { $MP_S_M_r['m'] = $dosages_assoc['intervals']['m']; }
							if (! empty($dosages_assoc['intervals']['d'])) { $MP_S_M_r['d'] = $dosages_assoc['intervals']['d']; }
							if (! empty($dosages_assoc['intervals']['v'])) { $MP_S_M_r['v'] = $dosages_assoc['intervals']['v']; }
							if (! empty($dosages_assoc['intervals']['h'])) { $MP_S_M_r['h'] = $dosages_assoc['intervals']['h']; }
						} else {
						    $MP_S_M_r['t'] = $this->translate('dosage not compatible');
						}
						
					} else if( ! empty($row_medication['dosage'])) {
							
						// 							$MP_S_M_r['t'] = $row_medication['dosage'];
							
						if (strpos($row_medication['dosage'], "-")){
							$row_medication_d = explode("-",$row_medication['dosage']);
							if (count($row_medication_d) == 4) {
								if (! empty($row_medication_d[0])) { $MP_S_M_r['m'] = $row_medication_d[0]; }
								if (! empty($row_medication_d[1])) { $MP_S_M_r['d'] = $row_medication_d[1]; }
								if (! empty($row_medication_d[2])) { $MP_S_M_r['v'] = $row_medication_d[2]; }
								if (! empty($row_medication_d[3])) { $MP_S_M_r['h'] = $row_medication_d[3]; }
							} else if (count($row_medication_d) != 4) {
							    
							    $MP_S_M_r['t'] = $this->translate('dosage not compatible');
							}
						} else {
							$MP_S_M_r['t'] = $row_medication['dosage'];
						}
							
					}  else if(  empty($row_medication['dosage'])) {
							$MP_S_M_r['t'] = "";
					    
					}
	
					//$MP_S_M_r['du']
					if ( ! empty ($row_medication['dosage_form'])) {
						$MP_S_M_r['dud'] = $row_medication['unit'];
					}
	
					// 						[type] => oral
					// 						concentration_full
	
					if ( ! empty($row_medication['comments'])) {
						$MP_S_M_r['i'] = $row_medication['comments'];
					}
	
					if ( ! empty($row_medication['indication'])) {
						$MP_S_M_r['r'] = $row_medication['indication'];
					}
	
	
					//$MP_S_M_r['f']
					if ( ! empty ($row_medication['dosage_form'])) {
						$MP_S_M_r['fd'] = $row_medication['dosage_form'];
					}
	
					array_push ($MP_S_M, $MP_S_M_r);
				}
				if ( ! empty($MP_S_M)) {
					$MP_S['M'] = $MP_S_M;
				}
					
	
				//create dossage hours infotext
				if ( ! empty($post['dosage_intervals'] [$medication_types])) {
					$MP_S['X']['t'] .= "Dosierung: " . implode("; ", $post['dosage_intervals'] [$medication_types]);
				}
					
				array_push($MP_data['S'], $MP_S);
			}
		}
		
		/**
		 * ISPC-2110 , ISPC-2130
		 * allergies in the dot-matrix
		 */
		/*
		if ( isset($post['patient_allergies']) 
		    && ! empty($post['patient_allergies']['allergies_comment']) 
		    && trim($post['patient_allergies']['allergies_comment']) != "Keine Allergien / Kommentare" 
		    && strlen($post['patient_allergies']['allergies_comment']) > 100) 
		{
		    $allergies_comment = Pms_CommonData::br2nl($post['patient_allergies']['allergies_comment']);
		    
		    $allergies_comment = preg_replace("/([\r\n]{4,}|[\n]{2,}|[\r]{2,})/", "\n", $allergies_comment);
		    $allergies_comment = str_replace(array("\"", "'"), '`', $allergies_comment);
		    
		    $allergies_comment = explode("\n", $allergies_comment);
		    $allergies_comment = array_map('trim', $allergies_comment);
// 		    $allergies_comment = array_map('html_entity_decode', $allergies_comment);
// 		    $allergies_comment = array_map('htmlentities', $allergies_comment);
// 		    $allergies_comment = array_map('utf8_encode', $allergies_comment);
		    $allergies_comment = array_filter($allergies_comment, 'trim');
		    
		   
		    $MP_S_X = array();
		    
		    $allergies_comment = array_chunk($allergies_comment, 2);
		    
		    foreach($allergies_comment as $lineX2) {
		        
		        $MP_S_X_t = array( 't' => implode("\n",$lineX2));
		        array_push($MP_S_X , $MP_S_X_t);
		    }
		    
		    if ( ! empty($MP_S_X)) {
    		    $MP_S = array(
    		        'c' => '419',
    		        'X' => $MP_S_X,
    		        //'X' => array(array( 't' => html_entity_decode(Pms_CommonData::br2nl($post['patient_allergies']['allergies_comment'])))),
    		        //'X' => array(array( 't' => $allergies_comment)),
    		        
    		    );
    		    array_push($MP_data['S'] , $MP_S);
		    }
		}
		*/
		
			
	
		// 			die_claudiu($MP_data, $this->testArray_MP, $this->_patientMasterData);
			
			
		// 			die_claudiu($this->testArray_MP);
			
		$version = "241"; // xsd
			
		$tcpdfService = new Pms_DeKbv_TcpdfService();
		$barcodeSvc = new Pms_DeKbv_BarcodeService();
			
	
		$DeKbv_Bmp2 = new Pms_DeKbv_Bmp2(array(
				'tcpdf_service'   => $tcpdfService ,
				// 'tcpdf_name'      => $type . '_' . $rndUid ,
				'barcode_service' => $barcodeSvc ,
				'version'         => $version ,
				'generic'         => $this
		) );
			
			
		// 			$DeKbv_Bmp2->importDataMatrixArray($MP_data);
			
		// 			$dataArray = $DeKbv_Bmp2->getArrayFromNode(
		// 					$DeKbv_Bmp2->getDataMatrixDOM(),
		// 					array('multiple' => ['MP.S','MP.S.M','MP.S.X','MP.S.R'] ));
		// 			die_claudiu($dataArray, $MP_data);
	
		// 			$testXml1 = file_get_contents('/home/www/ispc2017_08/library/Pms/DeKbv/testpaket/' . 'bmp-0005a.xml');
		// 			$testXml2 = $barcodeSvc->generateMediplanDMapXML( $this->mpData );
		// 			$testXml2 = $barcodeSvc->generateMediplanDMapXML( $this->testArray_MP );
		$testXml2 = $barcodeSvc->generateMediplanDMapXML( $MP_data );
		// 			die_claudiu($testXml2);
		// 			$testXml2 = '<MP v="024" U="4001B2C2231B4E32AF1A24DE10C31E03" l="de-DE"><P g="Ricarda" f="Musterfrau" b="19470425"/><A n="Praxis Dr. Michael M�ller" s="Schlo�str. 22" z="10555" c="Berlin" p="030-1234567" e="dr.mueller@kbv-net.de" t="2017-05-02T12:00:00"/><O ai="Laktose"/><S><M f="TAB" m="1" v="1" du="1" r="Diabetes"><W w="Metformin" s="500 mg"/></M><M f="TAB" m="1" du="1" r="Blutdruck"><W w="Lisinopril" s="5 mg"/></M></S><S t="Antibiotikatherapie f�r 7 Tage (31.5. bis 6.6.)"><M p="2394397" m="1" d="1" v="1" du="1" r="Bronchitis"/></S><S t="Neurologische Medikation (Dr. A. Schneider)"><M p="11186232" t="siehe n�chste Zeile" i="Feste Einnahmezeiten beachten!" r="Parkinson" x="Einnahmezeiten Parkinsonmedikation: 8:30 = 1 Tabl.; 12:30 = 2 Tabl.; 16:00 = 1 Tabl.; 18:30 = 1 Tabl."/></S></MP>';
	
		$DeKbv_Bmp2->importDataMatrixXml($testXml2);
			
			
			
			
		// 			$DeKbv_Bmp2->importDataMatrixDOM($this->testArray_MP);
			
	
		// 			$DeKbv_Bmp2->dumpDataMatrixDOM();
	
		// 			$dataArray = $DeKbv_Bmp2->getArrayFromNode( $DeKbv_Bmp2->getDataMatrixDOM());
			
		// 			die_claudiu($dataArray , $dom, $codeToSTitle, $codeToForm, $codeToUnit);
			
	
			
		$options = array(
		// 			'footer_html'=> "<img src=\"%smartq_footer_logo%\" >"
		);
	
		$result = $DeKbv_Bmp2->generatePDF("Medikationsplan_Bundeseinheitlicher.pdf", $options);
	
		
	}

	
	/**
	 *
	 * @param unknown $chk
	 * @param unknown $post
	 * @param unknown $pdfname
	 * @param unknown $filename
	 * @throws Exception
	 * Changed from version 2.4 to 2.6 ISPC-2551 Ancuta 31.03.2020
	 */
	private function _generatePdfNew_datamatrix($chk, $post, $pdfname, $filename)
	{
	    
	    if ( empty($post['allow_print']['medication_types']) && empty($post['allow_print']['medication_pumpe'])) {
	        //nothing to print
	        throw new Exception( __METHOD__ . ' you selected no medication to print', 1 );
	        return;
	    }
	    
	    
	    $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
	    $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpid($decid);
	    
	    $excluded_keys = array(
	        'stamp_block'
	    );
	    
	    if ( ! $this->_patientMasterData && ! is_array($var)) {
	        $patientmaster = new PatientMaster();
	        $patientinfo = $patientmaster->getMasterData($decid, 1);
	        $this->_patientMasterData = $patientmaster->get_patientMasterData();
	    }
	    
	    // this are used in $MP_data['A']
	    // if by doctor
	    $nice_doctor = User::getUsersNiceName( array($this->userid), $this->clientid, array( 'phone', 'mobile', 'LANR', 'street1', 'zip', 'city', 'emailid' ) );
	    $nice_doctor = $nice_doctor[ $this->userid ];
	    
	    // TODO-1284
	    $client_details = Client::getClientDataByid($this->clientid);
	    if( ! empty($client_details)){
	        $nice_team = $client_details[0];
	    }
	    
	    // this are used in $MP_data['O']
	    $latest_vital_signs = FormBlockVitalSigns::get_patients_chart_last_values($ipid);
	    
	    //do we need something from the client? we could display some client info in the footer
	    //$clientinfo = Pms_CommonData::getClientData($this->clientid);
	    
	    
	    $MP_data = array(
	        
	    // 				'v' => '241',
	        'v' => '26',//ISPC-2551 Ancuta 31.03.2020
	        'l' => 'de-DE',
	        
	        'P' => array(),
	        'A' => array(),
	        'O' => array(),
	        'S' => array(),
	        
	    );
	    
	    //ISPC-2551 Ancuta 31.03.2020
	    $MP_data_header = array(
	        
	        'P' => array(),
	        'A' => array(),
	        'O' => array(),
	    );
	    
	    //restriction base="xs:string" maxLength value="45" minLength value="1"
	    if(strlen($this->_patientMasterData['first_name']) > 45 ){
	        $this->_patientMasterData['first_name'] = str_pad(mb_substr($this->_patientMasterData['first_name'], 0, 43, "UTF-8"), 45, "...");
	    }
	    
	    //restriction base="xs:string" maxLength value="45" minLength value="1"
	    if(strlen($this->_patientMasterData['last_name']) > 45 ){
	        $this->_patientMasterData['last_name'] = str_pad(mb_substr($this->_patientMasterData['last_name'], 0, 43, "UTF-8"), 45, "...");
	    }
	    
	    //string length value="10" pattern value="[A-Z]\d{9}"
	    if(strlen($this->_patientMasterData['egk']) > 10 ){
	        mb_substr($this->_patientMasterData['egk'], 0, 10, "UTF-8");
	    }
	    //--
	    
	    
	    $MP_data['P'] =
	    $MP_data_header['P'] =  array(
	        'g' => $this->_patientMasterData['first_name'],
	        'f' => $this->_patientMasterData['last_name'],
	        'b' => date('Ymd',strtotime($this->_patientMasterData['birthd'])),
	    );
	    
	    
	    
	    if ( ! empty($this->_patientMasterData['egk']) ) {
	        $MP_data['P'] ['egk'] =  $this->_patientMasterData['egk'] ;
	        $MP_data_header['P'] ['egk'] =  $this->_patientMasterData['egk'];
	    }
	    
	    $patient_gender="";
	    if ( ! empty($this->_patientMasterData['sex']) ) {
	        if($this->_patientMasterData['sex'] == "1"){
	            $patient_gender = "M";
	        }
	        else if($this->_patientMasterData['sex'] == "2"){
	            $patient_gender = "W";
	        }
	        else if($this->_patientMasterData['sex'] == "0" || $this->_patientMasterData['sex'] != null){
	            $patient_gender = "D";
	        } else{
	            $patient_gender = "X";
	        }
	        if($patient_gender){
	            
	            $MP_data['P'] ['s'] = $patient_gender;
	            $MP_data_header['P'] ['s'] = $patient_gender;
	        }
	    }
	    
	    
	    if ( ! empty($this->_patientMasterData['title']) ) {
	        //$MP_data['P'] ['t'] = $this->_patientMasterData['title'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	        $MP_data_header['P'] ['t'] = $this->_patientMasterData['title'];
	    }
	    
	    if ( isset($post['patient_allergies']) && ! empty($post['patient_allergies']['allergies_comment']) && trim($post['patient_allergies']['allergies_comment']) != "Keine Allergien / Kommentare") {
	        //$MP_data['O'] ['ai'] = substr(utf8_decode(Pms_CommonData::br2nl($post['patient_allergies']['allergies_comment'])), 0 , 100);
	        $MP_data_header['O'] ['ai'] = mb_substr( Pms_CommonData::br2nl($post['patient_allergies']['allergies_comment']), 0 , 50, "UTF-8");//ISPC-2551 Ancuta 31.03.2020
	    }
	    
	    
	    if ( isset($latest_vital_signs[$ipid]) && ! empty($latest_vital_signs[$ipid])) {
	        
	        if ( ! empty($latest_vital_signs[$ipid]['weight']) ) {
	            //$MP_data['O'] ['w'] = floatval($latest_vital_signs[$ipid]['weight']);//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['O'] ['w'] = floatval($latest_vital_signs[$ipid]['weight']);
	        }
	        
	        if ( ! empty($latest_vital_signs[$ipid]['height']) ) {
	            //$MP_data['O'] ['h'] = floatval($latest_vital_signs[$ipid]['height']);//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['O'] ['h'] = floatval($latest_vital_signs[$ipid]['height']);
	        }
	        
	        if ( ! empty($latest_vital_signs[$ipid]['creatinine']) ) {
	            //$MP_data['O'] ['c'] = $latest_vital_signs[$ipid]['creatinine'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['O'] ['c'] = $latest_vital_signs[$ipid]['creatinine'];
	        }
	    }
	    
	    $nice_doctor = array();
	    if ( isset($nice_doctor) && ! empty($nice_doctor)) {
	        
	        if ( ! empty($nice_doctor['nice_name']) ) {
	            $MP_data['A'] ['n'] = $nice_doctor['nice_name'];
	            $MP_data_header['A'] ['n'] = $nice_doctor['nice_name'];
	        }
	        
	        if ( ! empty($nice_doctor['LANR']) ) {
	            //$MP_data['A'] ['lanr'] = $nice_doctor['LANR'];
	            $MP_data_header['A'] ['lanr'] = $nice_doctor['LANR'];
	        }
	        
	        if ( ! empty($nice_doctor['street1']) ) {
	            if(strlen($nice_doctor['street1']) > 30 ){
	                $nice_doctor['street1'] = str_pad(mb_substr($nice_doctor['street1'], 0, 27, "UTF-8"), 30, "...");
	            }
	            //$MP_data['A'] ['s'] = $nice_doctor['street1'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['s'] = $nice_doctor['street1'];
	        }
	        
	        if ( ! empty($nice_doctor['zip']) ) {
	            //$MP_data['A'] ['z'] = $nice_doctor['zip'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['z'] = $nice_doctor['zip'];
	        }
	        
	        if ( ! empty($nice_doctor['city']) ) {
	            //$MP_data['A'] ['c'] = $nice_doctor['city']; //ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['c'] = $nice_doctor['city'];
	        }
	        
	        if ( ! empty($nice_doctor['phone']) || ! empty($nice_doctor['mobile'])) {
	            //$MP_data['A'] ['p'] = ! empty($nice_doctor['phone'])?  $nice_doctor['phone'] : $nice_doctor['mobile'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['p'] = ! empty($nice_doctor['phone'])?  $nice_doctor['phone'] : $nice_doctor['mobile'];
	        }
	        
	        if ( ! empty($nice_doctor['emailid'])) {
	            //$MP_data['A'] ['e'] = $nice_doctor['emailid'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['e'] = $nice_doctor['emailid'];
	        }
	    }
	    
	    if ( isset($nice_team) && ! empty($nice_team)) {
	        
	        if ( ! empty($nice_team['team_name']) ) {
	            //ISPC-2551 Ancuta 31.03.2020 - limit length
	            $max_length = 80;
	            if(strlen($nice_team['team_name']) > $max_length ){//string" minLength value="1" maxLength value="80"
	                $nice_team['team_name'] = str_pad(mb_substr($nice_team['team_name'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	            }
	            $MP_data['A'] ['n'] = $nice_team['team_name'];
	            $MP_data_header['A'] ['n'] = $nice_team['team_name'];//ISPC-2551 Ancuta 31.03.2020  Allow on pdf
	        }
	        
	        // 			if ( ! empty($nice_team['LANR']) ) {
	        // 				$MP_data['A'] ['lanr'] = $nice_team['LANR'];
	        // 			}
	        
	        if ( ! empty($nice_team['street1']) ) {
	            
	            //ISPC-2551 Ancuta 31.03.2020 - limit length
	            $max_length = 30;
	            if(strlen($nice_team['street1']) > $max_length ){//string" minLength value="1" maxLength value="30"
	                $nice_team['street1'] = str_pad(mb_substr($nice_team['street1'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	            }
	            //--
	            
	            //$MP_data['A'] ['s'] = $nice_team['street1'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['s'] = $nice_team['street1'];//ISPC-2551 Ancuta 31.03.2020  Allow on pdf
	        }
	        
	        if ( ! empty($nice_team['postcode']) ) {
	            
	            //ISPC-2551 Ancuta 31.03.2020 - limit length
	            if(strlen($nice_team['postcode']) > 5 ){//string" length value="5" pattern value="\d{5}"
	                $nice_team['postcode'] = mb_substr($nice_team['postcode'], 0, 5, "UTF-8");
	            }
	            //--
	            
	            //$MP_data['A'] ['z'] = $nice_team['postcode'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['z'] = $nice_team['postcode']; //ISPC-2551 Ancuta 31.03.2020  Allow on pdf
	        }
	        
	        if ( ! empty($nice_team['city']) ) {
	            //ISPC-2551 Ancuta 31.03.2020 - limit length
	            $max_length = 20;
	            if(strlen($nice_team['city']) > $max_length ){//string minLength value="1" maxLength value="20"
	                $nice_team['city'] = str_pad(mb_substr($nice_team['city'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	            }
	            //--
	            
	            //$MP_data['A'] ['c'] = $nice_team['city'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['c'] = $nice_team['city'];//ISPC-2551 Ancuta 31.03.2020  Allow on pdf
	        }
	        
	        if ( ! empty($nice_team['phone']) || ! empty($nice_team['mobile'])) {
	            //$MP_data['A'] ['p'] = ! empty($nice_team['phone'])?  $nice_team['phone'] : $nice_team['mobile'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['p'] = ! empty($nice_team['phone'])?  $nice_team['phone'] : $nice_team['mobile'];//ISPC-2551 Ancuta 31.03.2020  Allow on pdf
	        }
	        
	        if ( ! empty($nice_team['emailid'])) {
	            
	            //ISPC-2551 Ancuta 31.03.2020 - limit length
	            $max_length = 30;
	            if(strlen($nice_team['emailid']) > $max_length ){//string minLength value="1" maxLength value="50"
	                $nice_team['emailid'] = str_pad(mb_substr($nice_team['emailid'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	            }
	            //--
	            
	            // $MP_data['A'] ['e'] = $nice_team['emailid'];//ISPC-2551 Ancuta 31.03.2020  Hide from QR code
	            $MP_data_header['A'] ['e'] = $nice_team['emailid'];//ISPC-2551 Ancuta 31.03.2020  Allow on pdf
	        }
	    }
	    
	    //ISPC-2551 Ancuta 31.03.2020
	    $current_date = date('Y-m-d');
	    $current_time = date('h:i:s');
	    $MP_data['A'] ['t'] = $current_date.'T'.$current_time;
	    // --
	    $my_medication_groups = PatientDrugPlan::$KBV_BMP2_ZWISCHENUEBERSCHRIFT_ASSOC;
	    $my_medication_groups_flipped = array_flip($my_medication_groups);
	    // 		die_claudiu($post, $post['allow_print']['medication_types']);
	    
	    
	    // TODO-2829 ISPC : Bundeseinheitlicher Medikationsplan in RP_Worms Ancuta 07.02.2020
	    // Administrator: we remove the "PUMPE" section completly from that plan. as the fields are too many, for too less printable fields.
	    if( in_array("isschmerzpumpe", $post['allow_print']['medication_types'])
	        && ! empty($post['medications_array'] ['isschmerzpumpe']))
	    {
	        if (($key = array_search('isschmerzpumpe', $post['allow_print']['medication_types'])) !== false) {
	            unset($post['allow_print']['medication_types'][$key]);
	        }
	    }
	    //---
	    
	    
	    //insert all isschmerzpumpe as single groups ... with _%pumpe_no%
	    if( in_array("isschmerzpumpe", $post['allow_print']['medication_types'])
	        && ! empty($post['medications_array'] ['isschmerzpumpe']))
	    {
	        $pumpe_no = 0;
	        
	        foreach ( $post['medications_array'] ['isschmerzpumpe'] as $grup_pumpe) {
	            
	            $post['medications_array'] ['isschmerzpumpe' . '_' . $pumpe_no ] = $grup_pumpe;
	            
	            $post['allow_print']['medication_types'][] = 'isschmerzpumpe' . '_' . $pumpe_no;
	            
	            $pumpe_no ++;
	        }
	    }
	    
	    foreach ($post['allow_print']['medication_types'] as $medication_types) {
	        if ( ! empty($post['medications_array'] [$medication_types])) {
	            
	            if ( $medication_types == 'isschmerzpumpe') {
	                continue;
	            }
	            
	            
	            $MP_S = array();//one group in $MP_data['S']
	            $MP_S_M = array(); //$MP_data['S'] [] ['M']
	            
	            $group_title = $medication_types;
	            if ( strpos($medication_types, 'isschmerzpumpe') === 0) {
	                $group_title = 'isschmerzpumpe';
	            }
	            
	            
	            if ( isset($my_medication_groups_flipped[$group_title])
	                && ! empty($my_medication_groups_flipped[$group_title])
	                && is_numeric($my_medication_groups_flipped[$group_title])){
	                    $MP_S['c'] = $my_medication_groups_flipped[$group_title];
	            } else {
	                $MP_S['t'] = $this->translate($group_title . " medication title");
	            }
	            
	            
	            foreach ( $post['medications_array'] [$medication_types] as $row_medication) {
	                
	                $MP_S_M_r = array();
	                /*
	                 $MP_S_M_r = array();//on row in $MP_data['S'] [] ['M']
	                 $MP_S_M_r['p'] pzn
	                 Handelsname = $MP_S_M_r['a'] drug name
	                 $MP_S_M_r['f'] dosage form pharmaceutical code as IFA code
	                 $MP_S_M_r['fd'] freetext dosage form if not IFA
	                 $MP_S_M_r['m'] morning dosage schedule
	                 $MP_S_M_r['n'] noon dosage schedule
	                 $MP_S_M_r['v'] evening dosage schedule
	                 $MP_S_M_r['h'] night dosage schedule
	                 $MP_S_M_r['t'] freetext dosage schedule if not m,n,v,h
	                 Einheit = $MP_S_M_r['du'] dosage unit code
	                 Einheit = $MP_S_M_r['dud'] freetext dosage unit code if not du
	                 Hinweise = $MP_S_M_r['i'] freetext notes, instructions of use, storage, ingestion etc
	                 Grund = $MP_S_M_r['r'] freetext reason for treatment
	                 lineunder = $MP_S_M_r['x'] freetext aditional line info
	                 
	                 max 3 W - if active substance is changed, pzn should be the same and the info introduced here
	                 $MP_S_M_r['W'] = array(
	                 'w', name of an active substance
	                 's' freetext active strength
	                 )
	                 //wichtige angaben
	                 $MP_S_M_r['X'] = array(
	                 't' text without reference to a mdication entry
	                 )
	                 //receipt
	                 $MP_S_M_r['R'] = array(
	                 't' text without reference to a mdication entry
	                 )
	                 */
	                
	                //get from pzn
	                if ( ! empty($post['medication_master'] [$row_medication['medication_master_id']] ['pzn'] )
	                    && $post['medication_master'] [$row_medication['medication_master_id']] ['pzn'] !="00000000")
	                {
	                    $MP_S_M_r['p'] = $post['medication_master'] [$row_medication['medication_master_id']] ['pzn'];
	                }
	                else {
	                    //get from what user has written
	                    if($row_medication['treatment_care'] == "1" || $row_medication['isnutrition'] == "1" ){
	                        //ISPC-2551 Ancuta 31.03.2020 - limit length
	                        $max_length = 50;
	                        if(strlen($row_medication['medication']) > $max_length ){//string minLength value="1" maxLength value="50"
	                            $row_medication['medication'] = str_pad(mb_substr($row_medication['medication'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	                        }
	                        //--
	                        
	                        $MP_S_M_r['a'] = $row_medication['medication'];// drug name // 23.01.2019 changed by Ancuta - as this
	                    }else{
	                        //ISPC-2551 Ancuta 31.03.2020 - limit length
	                        $max_length = 50;
	                        if(strlen($post['medication_master'] [$row_medication['medication_master_id']] ['name']) > $max_length ){//string minLength value="1" maxLength value="80"
	                            $post['medication_master'] [$row_medication['medication_master_id']] ['name'] = str_pad(mb_substr($post['medication_master'] [$row_medication['medication_master_id']] ['name'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	                        }
	                        //--
	                        
	                        $MP_S_M_r['a'] =  $post['medication_master'] [$row_medication['medication_master_id']] ['name'];// drug name//ISPC-2551 Ancuta 31.03.2020 limit to required length
	                    }
	                    
	                    //this is not 100% ok
	                    if ( ! empty( $row_medication['drug'] )){
	                        
	                        //ISPC-2551 Ancuta 31.03.2020 - limit length
	                        $max_length = 80;
	                        if(strlen($row_medication['drug']) > $max_length ){//string minLength value="1" maxLength value="80"
	                            $row_medication['drug'] = str_pad(mb_substr($row_medication['drug'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	                        }
	                        //--
	                        
	                        $MP_S_M_r['W']['w'] = $row_medication['drug'];
	                        
	                        if(!empty($row_medication['concentration']) || !empty($row_medication['unit'])){
	                            $MP_S_M_r['W']['s'] = $row_medication['concentration']. " ". $row_medication['unit'];
	                            
	                        }
	                        
	                    }
	                }
	                
	                
	                
	                
	                if ( ! empty($row_medication['dosage']) && is_array($row_medication['dosage'])) {
	                    
	                    //    TODO-2071 NEW RUSES APPLIED - 23.01.2019 Ancuta
	                    
	                    // 						$dosages_grouped = $this->_groupConcatDosages($row_medication['dosage']);
	                    // 						if ( ! empty($dosages_grouped)) {
	                    // 							if (! empty($dosages_grouped['m'])) { $MP_S_M_r['m'] = $dosages_grouped['m']; }
	                        // 							if (! empty($dosages_grouped['d'])) { $MP_S_M_r['d'] = $dosages_grouped['d']; }
	                        // 							if (! empty($dosages_grouped['v'])) { $MP_S_M_r['v'] = $dosages_grouped['v']; }
	                        // 							if (! empty($dosages_grouped['h'])) { $MP_S_M_r['h'] = $dosages_grouped['h']; }
	                        // 						}
	                        
	                        $dosages_assoc = $this->_dosageIntervallAssociation($row_medication['dosage']);
	                        if ( ! empty($dosages_assoc['intervals']) && empty($row_medication['dosage_alt'])) {
	                            if (! empty($dosages_assoc['intervals']['m'])) { $MP_S_M_r['m'] = str_replace( '.', ',',$dosages_assoc['intervals']['m'] ); }
	                            if (! empty($dosages_assoc['intervals']['d'])) { $MP_S_M_r['d'] = str_replace( '.', ',',$dosages_assoc['intervals']['d'] ); }
	                            if (! empty($dosages_assoc['intervals']['v'])) { $MP_S_M_r['v'] = str_replace( '.', ',',$dosages_assoc['intervals']['v'] ); }
	                            if (! empty($dosages_assoc['intervals']['h'])) { $MP_S_M_r['h'] = str_replace( '.', ',',$dosages_assoc['intervals']['h'] ); }
	                        } else {
	                            $MP_S_M_r['t'] = $this->translate('dosage not compatible');
	                            //$MP_S_M_r['t'] = implode('-',$row_medication['dosage']);
	                        }
	                        
	                    } else if( ! empty($row_medication['dosage'])) {
	                        
	                        // 							$MP_S_M_r['t'] = $row_medication['dosage'];
	                        
	                        if (strpos($row_medication['dosage'], "-")){
	                            $row_medication_d = explode("-",$row_medication['dosage']);
	                            if (count($row_medication_d) == 4) {
	                                if (! empty($row_medication_d[0])) { $MP_S_M_r['m'] = str_replace( '.', ',', $row_medication_d[0] ); }
	                                if (! empty($row_medication_d[1])) { $MP_S_M_r['d'] = str_replace( '.', ',', $row_medication_d[1] ); }
	                                if (! empty($row_medication_d[2])) { $MP_S_M_r['v'] = str_replace( '.', ',', $row_medication_d[2] ); }
	                                if (! empty($row_medication_d[3])) { $MP_S_M_r['h'] = str_replace( '.', ',', $row_medication_d[3] ); }
	                            } else if (count($row_medication_d) != 4) {
	                                
	                                // 									$MP_S_M_r['t'] = $this->translate('dosage not compatible');
	                                //ISPC-2329 - do not list Not compatible, allow the dosage to be treated as text
	                                //ISPC-2551 Ancuta 31.03.2020 - limit length
	                                $max_length = 20;
	                                if(strlen($row_medication['dosage']) > $max_length ){//string minLength value="1" maxLength value="20"
	                                    $row_medication['dosage'] = str_pad(mb_substr($row_medication['dosage'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	                                }
	                                //--
	                                if(!empty($row_medication['dosage'])){
	                                    $MP_S_M_r['t'] = str_replace( '.', ',', $row_medication['dosage']);
	                                }
	                            }
	                        } else {
	                            if($row_medication['isschmerzpumpe'] == "1")
	                            {
	                                $MP_S_M_r['t'] = round($row_medication['dosage'], 2);
	                            }
	                            else
	                            {
	                                //ISPC-2551 Ancuta 31.03.2020 - limit length
	                                $max_length = 20;
	                                if(strlen($row_medication['dosage']) > $max_length ){//string minLength value="1" maxLength value="20"
	                                    $row_medication['dosage'] = str_pad(mb_substr($row_medication['dosage'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	                                }
	                                //--
	                                if(!empty($row_medication['dosage'])){
	                                    $MP_S_M_r['t'] = $row_medication['dosage'];
	                                }
	                            }
	                        }
	                        
	                    }
	                    //ISPC-2551 Ancuta 31.03.2020 :: according to 26 xsd- not t if empty
	                    /* else if(  empty($row_medication['dosage'])) {
	                     $MP_S_M_r['t'] = "";
	                     
	                     } */
	                    
	                    //$MP_S_M_r['du']
	                    //TODO-2829 Ancuta 20.01.2020 - changed if condition - from: dosage_form to  unit
	                    if ( ! empty ($row_medication['unit'])) {
	                        $MP_S_M_r['dud'] = $row_medication['unit'];
	                    }
	                    
	                    // 						[type] => oral
	                    // 						concentration_full
	                    
	                    if ( ! empty($row_medication['comments'])) {
	                        $max_length = 80;
	                        if(strlen($row_medication['comments']) > $max_length ){//string minLength value="1" maxLength value="20"
	                            $row_medication['comments'] = str_pad(mb_substr($row_medication['comments'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	                        }
	                        $MP_S_M_r['i'] = $row_medication['comments'];
	                    }
	                    
	                    if ( ! empty($row_medication['indication'])) {
	                        
	                        $max_length = 50;
	                        if(strlen($row_medication['indication']) > $max_length ){//string minLength value="1" maxLength value="20"
	                            $row_medication['indication'] = str_pad(mb_substr($row_medication['indication'], 0, $max_length-3, "UTF-8"), $max_length, "...");
	                        }
	                        $MP_S_M_r['r'] = $row_medication['indication'];
	                    }
	                    
	                    
	                    //$MP_S_M_r['f']
	                    if ( ! empty ($row_medication['dosage_form'])) {
	                        $MP_S_M_r['fd'] = $row_medication['dosage_form'];
	                    }
	                    
	                    //TODO-2829 Ancuta 20.01.2020
	                    if($row_medication['isschmerzpumpe'] == "1")
	                    {
	                        $MP_S_M_r['fd'] = 'ml';//hardcoded dosage_form! 20.01.2020
	                    }
	                    // --
	                    array_push ($MP_S_M, $MP_S_M_r);
	            }
	            if ( ! empty($MP_S_M)) {
	                $MP_S['M'] = $MP_S_M;
	            }
	            
	            
	            //create dossage hours infotext
	            if ( ! empty($post['dosage_intervals'] [$medication_types])) {
	                $MP_S['X']['t'] .= "Dosierung: " . implode("; ", $post['dosage_intervals'] [$medication_types]);
	            }
	            
	            array_push($MP_data['S'], $MP_S);
	        }
	    }
	    
	    /**
	     * ISPC-2110 , ISPC-2130
	     * allergies in the dot-matrix
	     */
	    /*
	     if ( isset($post['patient_allergies'])
	     && ! empty($post['patient_allergies']['allergies_comment'])
	     && trim($post['patient_allergies']['allergies_comment']) != "Keine Allergien / Kommentare"
	     && strlen($post['patient_allergies']['allergies_comment']) > 100)
	     {
	     $allergies_comment = Pms_CommonData::br2nl($post['patient_allergies']['allergies_comment']);
	     
	     $allergies_comment = preg_replace("/([\r\n]{4,}|[\n]{2,}|[\r]{2,})/", "\n", $allergies_comment);
	     $allergies_comment = str_replace(array("\"", "'"), '`', $allergies_comment);
	     
	     $allergies_comment = explode("\n", $allergies_comment);
	     $allergies_comment = array_map('trim', $allergies_comment);
	     // 		    $allergies_comment = array_map('html_entity_decode', $allergies_comment);
	     // 		    $allergies_comment = array_map('htmlentities', $allergies_comment);
	     // 		    $allergies_comment = array_map('utf8_encode', $allergies_comment);
	     $allergies_comment = array_filter($allergies_comment, 'trim');
	     
	     
	     $MP_S_X = array();
	     
	     $allergies_comment = array_chunk($allergies_comment, 2);
	     
	     foreach($allergies_comment as $lineX2) {
	     
	     $MP_S_X_t = array( 't' => implode("\n",$lineX2));
	     array_push($MP_S_X , $MP_S_X_t);
	     }
	     
	     if ( ! empty($MP_S_X)) {
	     $MP_S = array(
	     'c' => '419',
	     'X' => $MP_S_X,
	     //'X' => array(array( 't' => html_entity_decode(Pms_CommonData::br2nl($post['patient_allergies']['allergies_comment'])))),
	     //'X' => array(array( 't' => $allergies_comment)),
	     
	     );
	     array_push($MP_data['S'] , $MP_S);
	     }
	     }
	     */
	    
	    
	    
	    // 			die_claudiu($MP_data, $this->testArray_MP, $this->_patientMasterData);
	    
	    
	    // 			die_claudiu($this->testArray_MP);
	    
	    //$version = "241"; // xsd
	    $version = "26"; // xsd//ISPC-2551 Ancuta 31.03.2020
	    
	    $tcpdfService = new Pms_DeKbv_TcpdfService();
	    $barcodeSvc = new Pms_DeKbv_BarcodeService();
	    
	    
	    $DeKbv_Bmp2 = new Pms_DeKbv_Bmp2(array(
	        'tcpdf_service'   => $tcpdfService ,
	        // 'tcpdf_name'      => $type . '_' . $rndUid ,
	        'barcode_service' => $barcodeSvc ,
	        'version'         => $version ,
	        'generic'         => $this
	    ) );
	    
	    
	    // 			$DeKbv_Bmp2->importDataMatrixArray($MP_data);
	    
	    // 			$dataArray = $DeKbv_Bmp2->getArrayFromNode(
	    // 					$DeKbv_Bmp2->getDataMatrixDOM(),
	    // 					array('multiple' => ['MP.S','MP.S.M','MP.S.X','MP.S.R'] ));
	    // 			die_claudiu($dataArray, $MP_data);
	    
	    // 			$testXml1 = file_get_contents('/home/www/ispc2017_08/library/Pms/DeKbv/testpaket/' . 'bmp-0005a.xml');
	    // 			$testXml2 = $barcodeSvc->generateMediplanDMapXML( $this->mpData );
	    // 			$testXml2 = $barcodeSvc->generateMediplanDMapXML( $this->testArray_MP );
	    $testXml2 = $barcodeSvc->generateMediplanDMapXML( $MP_data );
	    // 			die_claudiu($testXml2);
	    // 			$testXml2 = '<MP v="024" U="4001B2C2231B4E32AF1A24DE10C31E03" l="de-DE"><P g="Ricarda" f="Musterfrau" b="19470425"/><A n="Praxis Dr. Michael M�ller" s="Schlo�str. 22" z="10555" c="Berlin" p="030-1234567" e="dr.mueller@kbv-net.de" t="2017-05-02T12:00:00"/><O ai="Laktose"/><S><M f="TAB" m="1" v="1" du="1" r="Diabetes"><W w="Metformin" s="500 mg"/></M><M f="TAB" m="1" du="1" r="Blutdruck"><W w="Lisinopril" s="5 mg"/></M></S><S t="Antibiotikatherapie f�r 7 Tage (31.5. bis 6.6.)"><M p="2394397" m="1" d="1" v="1" du="1" r="Bronchitis"/></S><S t="Neurologische Medikation (Dr. A. Schneider)"><M p="11186232" t="siehe n�chste Zeile" i="Feste Einnahmezeiten beachten!" r="Parkinson" x="Einnahmezeiten Parkinsonmedikation: 8:30 = 1 Tabl.; 12:30 = 2 Tabl.; 16:00 = 1 Tabl.; 18:30 = 1 Tabl."/></S></MP>';
	    
	    $DeKbv_Bmp2->importDataMatrixXml($testXml2);
	    
	    
	    
	    
	    // 			$DeKbv_Bmp2->importDataMatrixDOM($this->testArray_MP);
	    
	    
	    // 			$DeKbv_Bmp2->dumpDataMatrixDOM();
	    
	    // 			$dataArray = $DeKbv_Bmp2->getArrayFromNode( $DeKbv_Bmp2->getDataMatrixDOM());
	    
	    // 			die_claudiu($dataArray , $dom, $codeToSTitle, $codeToForm, $codeToUnit);
	    
	    
	    
	    $options = array(
	        // 			'footer_html'=> "<img src=\"%smartq_footer_logo%\" >"
	        'Pdf_header' => $MP_data_header, //ISPC-2551 Ancuta 31.03.2020
	        
	    );
	    
	    
	    if($this->userid == "338XX"){
	        $options['view_xml_file']= '1';
	    }
	    
	    //TODO-2819 Ancuta 16.01.2020
	    ob_end_clean();
	    //--
	    $result = $DeKbv_Bmp2->generatePDF("Medikationsplan_Bundeseinheitlicher.pdf", $options);
	    
	    
	}
	
	
	//group multiple dosabes from different hours into 4 groups only
	private function _groupConcatDosages( $dosages_array  = array() )
	{
		//@todo get this $morning, $noon etc from client settings, and leave this below as default if it has no settings
		$morning = array(
				//m
				'start' => strtotime( "05:00" ),
				'end' => strtotime( "11:59" )
		);
		$noon = array(
				//n
				'start' => strtotime( "12:00" ),
				'end' => strtotime( "16:59" )
		);
		$evening = array(
				//v
				'start' => strtotime( "17:00" ),
				'end' => strtotime( "20:59" )
		);
			
		$night = array(
				//h
				'start' => strtotime( "21:00" ),
				'end' => strtotime("+1 day", strtotime( "04:59" )), // this needs attention if you move to client settings
		);
							
		$dosage_gropped = array();
					
		$dosage_gropped['hours'] =  "";
					
		if ( ! empty($dosages_array)) {
		    
		    //$fmt = new NumberFormatter( 'de_DE', NumberFormatter::DECIMAL );
		    
			foreach ($dosages_array as $dosage_h => $dosage_value) {
	
						
				$dosage_hhhhh =  $dosage_h;
				
				$dosage_h = strtotime($dosage_h);
				
				$dosage = "unknown";
					
				if ($morning['start'] <= $dosage_h && $morning['end'] >= $dosage_h ) {
					$dosage = "m"; //echo $dosage . " " .$dosage_hhhhh;
				} elseif ($noon['start'] <= $dosage_h && $noon['end'] >= $dosage_h ) {
					$dosage = "d"; //echo $dosage . " " .$dosage_hhhhh;
				} elseif ($evening['start'] <= $dosage_h && $evening['end'] >= $dosage_h ) {
					$dosage = "v"; //echo $dosage . " " .$dosage_hhhhh;
				} else {
					
					if ($morning['start'] >= $dosage_h) {
						$dosage_h = strtotime("+1 day", $dosage_h);
					}
					
					if ( $night['start'] <= $dosage_h && $night['end'] >= $dosage_h ) {
						$dosage = "h"; //echo $dosage . " " .$dosage_hhhhh;
					}
				}
				if ( ! isset($dosage_gropped[$dosage])) {
					$dosage_gropped[$dosage] = 0;
				}
				//$dosage_gropped[$dosage] += $fmt->parse($dosage_value);
				$dosage_gropped[$dosage] += (float)str_replace(array(',',' '), array('.',''), $dosage_value);
				
			}
		}
		return $dosage_gropped;
	}

	
	/**
	 * Ancuta
	 * 23.01.2019
	 * @param unknown $dosages_array
	 * @return multitype:multitype:
	 */
	private function _dosageIntervallAssociation( $dosages_array  = array() )
	{
// 	    dd($dosages_array);
		//@todo get this $morning, $noon etc from client settings, and leave this below as default if it has no settings
		$morning = array(
				//m
				'start' => strtotime( "05:00" ),
				'end' => strtotime( "11:59" )
		);
		$noon = array(
				//n
				'start' => strtotime( "12:00" ),
				'end' => strtotime( "16:59" )
		);
		$evening = array(
				//v
				'start' => strtotime( "17:00" ),
				'end' => strtotime( "20:59" )
		);
			
		$night = array(
				//h
				'start' => strtotime( "21:00" ),
				'end' => strtotime("+1 day", strtotime( "04:59" )), // this needs attention if you move to client settings
		);

		// check all dosages
		// if more then 4 - then not compatible
		// if more then one in the same interval - then not compatible
		 
		
		$dosage_gropped = array();
					
		$dosage_gropped['intervals'] =  array();

		$not_compatible = array();
		if ( ! empty($dosages_array)) {
		    
		    if(count($dosages_array) > 4){
		        // NOT COMPATIBILE
		        $not_compatible[] = '1';
		        
		    } else {

		        foreach ($dosages_array as $dosage_h => $dosage_value) {
		        
		        
		            $dosage_hhhhh =  $dosage_h;
		        
		            $dosage_h = strtotime($dosage_h);
		        
		            $dosage = "unknown";
		            	
		            if ($morning['start'] <= $dosage_h && $morning['end'] >= $dosage_h ) {
		                $dosage = "m"; //echo $dosage . " " .$dosage_hhhhh;
		            } elseif ($noon['start'] <= $dosage_h && $noon['end'] >= $dosage_h ) {
		                $dosage = "d"; //echo $dosage . " " .$dosage_hhhhh;
		            } elseif ($evening['start'] <= $dosage_h && $evening['end'] >= $dosage_h ) {
		                $dosage = "v"; //echo $dosage . " " .$dosage_hhhhh;
		            } else {
		                	
		                if ($morning['start'] >= $dosage_h) {
		                    $dosage_h = strtotime("+1 day", $dosage_h);
		                }
		                	
		                if ( $night['start'] <= $dosage_h && $night['end'] >= $dosage_h ) {
		                    $dosage = "h"; //echo $dosage . " " .$dosage_hhhhh;
		                }
		            }

		            
		            if ( ! isset($dosage_gropped[$dosage])) {
		                $dosage_gropped[$dosage] = array();
		            }
		            
		            $dosage_gropped[$dosage][] = $dosage_value;
		        
		        }
		        
		        
		        foreach($dosage_gropped as $interval=>$values){
		            
		            if(count($values) > 1) {
		                $not_compatible[] = 1;
		            } else {
		                $dosage_gropped['intervals'][$interval] = $values[0];
		            }
		        }
		        
		    }
		    
		    
		    //$fmt = new NumberFormatter( 'de_DE', NumberFormatter::DECIMAL );
		    
			if(!empty($not_compatible)){
			    $dosage_gropped['intervals'] = array();
			}
		}
		
		return $dosage_gropped;
	}
	
	
	public function maintenancestageAction()
	{
		$clientid = $this->clientid;
		$userid = $this->userid;
		$decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		$ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);
		
		$pms = new PatientMaintainanceStage();
		$history = new BoxHistory();
		
		if($this->getRequest()->isPost() )
		{
			$post = $this->getRequest()->getParams();
			
			
			foreach($post['form_data'] as $k=>$data){
				if(strlen($data['fromdate']) == 0 ){
					$post['form_data'][$k]['fromdate'] = '0000-00-00'; //SPCISPC-2245 Patients details "Plfegegrade"//date("Y-m-d"); // HACK - PLEASE change
				}
			}
			
			// sort post by fromdate
			usort($post['form_data'], array(new Pms_Sorter('fromdate'), "_date_compare"));
			
			$post['form_data'] = array_reverse($post['form_data']);
			$tilldate = '';
			foreach($post['form_data'] as $row){
				if($row['isdelete'] !="1"){
					if(strlen($row['stage'])>0 ){
						$row['ipid'] = $ipid;
						$row['tilldate'] = $tilldate;
						if(strlen($row['fromdate'])>0){
							$row['fromdate'] = date("Y-m-d",strtotime($row['fromdate']));
						} else {
							$row['fromdate'] = date("Y-m-d");
						}
						
						if($row['erstantrag'] == "1" && !empty($row['e_fromdate'])){
							$row['e_fromdate'] = date("Y-m-d",strtotime($row['e_fromdate']));
						}
						if($row['horherstufung'] == "1" && !empty($row['h_fromdate'])){
							$row['h_fromdate'] = date("Y-m-d",strtotime($row['h_fromdate']));
						}
						
						$pms->findOrCreateOneById($row['id'],$row);
						$tilldate = $row['fromdate'];
					}
				} else{
					$pms->delete_row($row['id']);
				}

				//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				// NOT CORRECT - PLEASE CHANGE !!!!!
				//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				$history = new BoxHistory();
				$history->ipid = $ipid;
				$history->clientid = $clientid;
				$history->fieldname = 'stage';
				$history->fieldvalue = $row['stage'].' von: '.date("d.m.Y",strtotime($row['fromdate']));
				$history->formid = "grow7"; //grow7
				$history->save();
				
				
			}
			
			
			$this->_redirect(APP_BASE . 'patient/patientdetails?flg=suc&id=' . $_REQUEST['id']);
			
		}
		
		
		$pms = new PatientMaintainanceStage();
		$pms_data['stage_system_values'] = $pms->get_MaintainanceStage_array();
		$patient_stage = $pms->getpatientMaintainanceStage($ipid);

		$row = 1;
		foreach($patient_stage as $k=>$psdata){
			$pms_data['patient_stage'][$row]['id'] = $psdata['id'];
			$pms_data['patient_stage'][$row]['stage'] = $psdata['stage'];
			$pms_data['patient_stage'][$row]['fromdate'] = date('d.m.Y',strtotime($psdata['fromdate']));
			if($psdata['erstantrag'] == "1"){
				$pms_data['patient_stage'][$row]['erstantrag'] = $psdata['erstantrag'];
				$pms_data['patient_stage'][$row]['e_fromdate'] = date('d.m.Y',strtotime($psdata['e_fromdate']));
			}
			if($psdata['horherstufung'] == "1"){
				$pms_data['patient_stage'][$row]['horherstufung'] = $psdata['horherstufung'];
				$pms_data['patient_stage'][$row]['h_fromdate'] = date('d.m.Y',strtotime($psdata['h_fromdate']));
			}
			$row++;
		}
		
		
		$this->view->form_data = $pms_data; 
		
		
	}
	
	    
	
	public function patientdetailshistoryAction(){
	    $this->_helper->layout->setLayout('layout_ajax');
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_CommonData::getIpId($decid);
	    $pd=new PatientDetails($ipid);
	    $this->view->rows=$pd->get_history();
	}
	
	
	/**
	 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 * !!! i've changed the viewSuffix to phtml !!!
	 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 */
	public function patientdetailsAction()
	{
	    
	    //ISPC-2827 Ancuta 31.03.2021
	    if (!$this->getRequest()->isPost() && $this->logininfo->isEfaClient == '1' && $this->logininfo->isEfaUser == '1')
	    {
	        $this->_redirect(APP_BASE."patientformnew/ambulatorycurve?id=" . $_GET['id']);
	    }
	    //--
	    
	    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
	    
	    $this->getMasterData_extradata(); //populate the extra array
	    
	    $patientMasterData = $this->getPatientMasterData();
	    
	    $this->view->encid = $this->enc_id;
	    
	    $memoKey = "memo-patientdetails";
	    
	    $block_name = "PatientDetails"; //this var is just for info
	    
	    $boxOrder = [101, 102, 103]; //101 = left //ISPC-2703, elena, 04.01.2021
	    
	    $ef = new ExtraForms();
	    $allowedFormBoxes = $ef->get_client_forms($this->logininfo->clientid);
	    
	    if ($this->getRequest()->isPost()) {
	        /*
	         * this is the POST part
	         * in this part you update, insert or delete
	         * or
	         * only if this is XHR you can also just fetch data
	         */
	        switch ($action = $this->getRequest()->getPost('__action')) {
	            
	            case "updateBoxOrder" :
	                /*
	                 * when you drag-drop a box from his place to another spot in the form
	                 */
	                $patientDetailsForm = new Application_Form_PatientDetails(
	                    array(
	                        '_block_name'          => $block_name,
	                        '_onlyThisModel'       => "NULLLL",
	                        '_clientForms'         => $allowedFormBoxes,
	                    ),
	                    $this->ipid
	                );
                    $default_categories = $patientDetailsForm->get_default_categories();
	                
	                $col = $this->getRequest()->getPost("col");
	                $col = ($col == "provider_left") ? 101 : (($col == "provider_right") ? 102 : 103); //ISPC-2703, elena, 04.01.2021
	                
	                $order = $this->getRequest()->getPost("order");
	                
	                array_walk($order, function(&$item) use ($default_categories) {
	                   $item = str_replace('box-', '', $item);
	                   $item = $default_categories[$item]['extra_form_ID'];
	                });
	                
	                $this->__updateBoxOrder( ['col' => $col, "order" => $order]);

	                $responsArr = array(
	                    'success' => true,
	                    'message' => "box sorted",
	                );
	                
                    $this->_helper->json($responsArr, true);
	                
	                exit; //for readbility
	                break;
	            
	            case "loadBoxHistory" :
	                /*
	                 *  if hasHistory==true , inside the box there is a button History
	                 */
	                $category = $this->getRequest()->getPost('__category');
	                
	                $responsArr = array(
	                    'success' => true,
	                    'message' => "",
	                    "category" => $category,
	                );

	                $patientDetailsForm = new Application_Form_PatientDetails(
	                    array(
	                    	'_patientMasterData'   => $patientMasterData,
	                        '_block_name'          => $block_name,
	                        '_onlyThisModel'       => "NULLLL",
	                        '_clientForms'         => $allowedFormBoxes,
	                    ),
	                    $this->ipid
	                );
	                
	                $history = $patientDetailsForm->create_box_history($category);
	                
	                if ($history) {
	                    $responsArr['message'] = $history; 
	                }
	                
	                $responsJson =  $this->_safe_json_encode($responsArr);
	                
	                $this->getResponse()
	                ->setHeader('Content-Type', 'application/json')
	                ->setBody($responsJson)
	                ->sendResponse();
	                 
	                //$this->_helper->json($responsArr, true);
	                 
	                exit; //for readbility
	                
	                break;
	                
	            case "deletePatientDetails" :
	                /*
	                 * when you edit a box via the editDialogHtml dialog, the is a button called Delete entry = Eintrag entfernen 
	                 */
	                $category = $this->getRequest()->getPost('__category');
	                
	                $responsArr = array(
    	                'success' => false,
    	                'message' => $this->translate('Something went wrong, please contact admin.') . " err:pd1",
	                );
	                 
	                $patientDetailsForm = new Application_Form_PatientDetails(
	                    array(
	                        '_patientMasterData'   => $patientMasterData,
	                        '_block_name'          => $block_name,
	                        '_clientForms'         => $allowedFormBoxes,
	                        '_onlyThisModel'       => $category,
	                    ),
	                    $this->ipid
	                );
	                	                 
	                $result = $patientDetailsForm->deletePatientDetails($this->getRequest()->getPost());
	                 
	                if ($result === true) {
	    
	                    $success = true;
	                    
	                    $data = $patientDetailsForm->getPatientData();
	    
	                    $responsArr = array(
	                        'success' => true,
	                        'data' => $data
	                    );
	    
	                     
	                }
	                 
	                $responsJson =  $this->_safe_json_encode($responsArr);
	                 
	                $this->getResponse()
	                ->setHeader('Content-Type', 'application/json')
	                ->setBody($responsJson)
	                ->sendResponse();
	    
	                //$this->_helper->json($responsArr, true);
	    
	                exit; //for readbility
	                 
	                break;
	                 
	            case "savePatientDetails":
	                /*
	                 * when you save a box via the dialog editDialogHtml or via addnewDialogHtml
	                 */
	                $category = $this->getRequest()->getPost('__category');
	                
	                $responsArr = array(
    	                'success' => false,
    	                'message' => $this->translate('Something went wrong, please contact admin.') . " err:pd2",
	                );
	                
	                
	                /*
	                 * this if is needed because we don't auto-fetch data on ajax requests..
	                 * .. and somoe default values are in here :( should be moved in static
	                 * fetch like this the minimum info data... this will be needed to send emails or other stuff
	                 */
	                if (empty($patientMasterData)) {
	                    $patM = new PatientMaster();
	                    $patM->getMasterData($this->dec_id, 1, null, $this->ipid); 
	                    $patM->getMasterData_extradata($this->ipid, $category);	                    
	                    $patientMasterData = $patM->get_patientMasterData();
	                }
	                 
	                
	                $patientDetailsForm = new Application_Form_PatientDetails(
	                    array(
	                        '_patientMasterData'   => $patientMasterData,
	                        '_block_name'          => $block_name,
	                        '_clientForms'         => $allowedFormBoxes,
	                        '_onlyThisModel'       => $category,
	                    ),
	                    $this->ipid
	                );
	                 
	                   

	                //ISPC-2432 Ancuta 21.01.2020
	                
	                if($category == 'MePatientDevices'){
	                   $me_data = $patientDetailsForm->updatePatientDetails($this->getRequest()->getPost());
	                   $result = null;
	                   $device_saved_data = array();
	                   $device_saved_data = $me_data['entry'];
	                   

	                   if($me_data['result'] === true){
	                       $result = true;
	                   }
	                } else{
    	                $result = $patientDetailsForm->updatePatientDetails($this->getRequest()->getPost());
	                }
	                //--  
	                
	                
	                if ($result === true) {
	                     
	                    $success = true;
	                    
	                    $data = $patientDetailsForm->getPatientData();

	                    $responsArr = array(
	                        'success'  => true,
	                        'data'     => $data,
	                        'device_data' => !empty($device_saved_data) ? $device_saved_data : null,
	                    );
	    
	      
	                } elseif ($result === false)  {
	                    $responsArr = array(
	                        'success' => false,
	                        'message' => $this->translate('Something went wrong, please contact admin.') . " err:pd3",
	                    );
	                     
	                } else {
	                    //we have errors in the form validate
	                    $message = $success;
	                    $success = false;
	                    $data = array();
	                     
	                    $responsArr = array(
	                        'success' => false,
	                        'editDialogHtmlWithErrors' => $result,
	                    );
	                }
	      
	                $responsJson =  $this->_safe_json_encode($responsArr);
	    
	                $this->getResponse()
	                ->setHeader('Content-Type', 'application/json')
	                ->setBody($responsJson)
	                ->sendResponse();
	                 
	                //$this->_helper->json($responsArr, true);
	                 
	                exit; //for readbility
	                 
	                 
	                break;
	    
	            case "updateMemos":
	                /*
	                 *  inside the box there is a button Memo, that has a dialog to save infos about that box
	                 */
	                $entry = PatientVersorger::getEntry($this->ipid, $memoKey);
	    
	                $category = $this->getRequest()->getPost('__category');
	    
	                $entry[$category] = array(
	                    "color"    => $this->getRequest()->getPost('color'),
	                    "memo"     => $this->getRequest()->getPost('memo'),
	                );
	    
	                PatientVersorger::updateEntry($this->ipid, $memoKey, $entry);
	    
	                $this->_helper->json(array('success'=>true, 'message' => 'OK!'), true);
	    
	                exit(); //for readbility
	                 
	                break;
	        }
	         
	    } else {
	        /*
	         * this is the GET part
	         * in this part you fetch data,
	         * you DO NOT alter the main records
	         * you can only log the visit or other stuff.. but do NOT edit the main details via a GET
	         */
	        
	        
	        /*
	         * fetch extra data
	         */
// 	        $entity2 = new PatientMaster();
// 	        $entity2->getMasterData_extradata($this->ipid);
// 	        $this->setPatientMasterData($entity2->get_patientMasterData());
	        
// 	        $patientMasterData = $this->getPatientMasterData();
	        
// 	        //re-send to view, so we have the extra data
// 	        $this->view->patientMasterData = $patientMasterData;
	        
	        
// 	        $this->_helper->flashMessenger('Error message just for funTest' , "ErrorMessages");
// 	        $this->__versorger_populateCurrentMessages();
	        
	        $boxesOpen = OverviewCookie::getCookieData($this->logininfo->userid, 'patientdetails');

	        
	        if (empty($boxesOpen[0])) {
	            //use default as open only those with data
	            $boxesOpen = "ONLY_WITH_CONTENT";
// 	            $boxesOpen = "ALL_OPENED";
	             
	        } else {
	             
	            $boxesOpen = $boxesOpen[0];
	             
	            switch ($boxesOpen['useroption']) {
	                case "1" :
	                    $boxesOpen = "ALL_CLOSED";
	                    break;
	        
	                case "2" :
	                    $boxesOpen = "ONLY_WITH_CONTENT";
	                    break;
	                     
	                case "3" :
	                    $boxesOpen = explode(",", $boxesOpen['cookie']);
	        
	                    array_walk($boxesOpen, function(&$item){
	                        $item = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
	                    });
	                         
                        $boxesOpen = array_filter($boxesOpen);
                        break;
	            }
	        }
	        
	        $patientDetailsForm = new Application_Form_PatientDetails(
	            array(
	                '_patientMasterData'   => $this->getPatientMasterData(),
	                '_block_name'          => $block_name,
	                '_clientForms'         => $allowedFormBoxes,
	            ),
	            $this->ipid
            );
	        
	        $allCategories = $patientDetailsForm->getAllCategories();
	         
	        $boxesPlacement = [];
	        
	        foreach ($allCategories as $key => $cat) {
	            
	            if ( ! isset($allowedFormBoxes[$cat['extra_form_ID']]) 
	                || ! $allowedFormBoxes[$cat['extra_form_ID']]) 
	            {
	                //not allowed to this box (@dev you can go to /extraforms/formlist and assign a box to a client)    
	                continue;
	            }
	            
	            if ($cat['placement'] == 'left') {
	                $boxesPlacement['left'][] = $key;
	            } elseif ($cat['placement'] == 'right') {
	                $boxesPlacement['right'][] = $key;
	            } elseif($cat['placement'] == 'third') { //ISPC-2703, elena, 04.01.2021
                    $boxesPlacement['third'][] = $key;
	            }
	        }
	        
	        $userBoxOrder = BoxOrder::fetchUserCol($this->logininfo->userid, $boxOrder);
	        if ( ! empty($userBoxOrder)) {
	            
    	        $newBoxesPlacement = ["left" => [], "right" => [], "third" => []]; //ISPC-2703, elena, 04.01.2021
    	        
	            foreach ($userBoxOrder as $order) {
	                
	                $placement = ($order['boxcol'] == 101) ? "left" : (($order['boxcol'] == 102)  ? "right" : "third"); //ISPC-2703, elena, 04.01.2021
	                
	                $boxID = $order['boxid'];
	                	                
	                $boxes = array_filter($allCategories, function($item) use ($boxID) {
	                    return $item['extra_form_ID'] == $boxID;
	                });
	                
	                $newBoxesPlacement[$placement] = array_merge($newBoxesPlacement[$placement], array_keys($boxes));
	            }
	            
	            foreach ($boxesPlacement as $placement => $boxes) {
	                foreach ($boxes as $box) {
	                    if ( ! in_array($box, $newBoxesPlacement ['left']) && ! in_array($box, $newBoxesPlacement ['right']) && ! in_array($box, $newBoxesPlacement ['third'])) { //ISPC-2703, elena, 05.01.2021
	                        //newbox
	                        $newBoxesPlacement [$placement][] = $box;
	                    }
	                }
	            }
	            
	            $boxesPlacement = $newBoxesPlacement;
	               
	        }
	        
	        
	        $this->view->boxesOpened       = $boxesOpen;
	        $this->view->boxesPlacement    = $boxesPlacement;
	        $this->view->mappings          = $allCategories;
	        $this->view->data              = $patientDetailsForm->getPatientData($this->ipid);
	        
	        $this->view->memos             = PatientVersorger::getEntry($this->ipid, $memoKey);
	        
	        
// 	        $this->view->disabledversorger = ClientConfig::getConfig($this->clientid, 'versorgerboxes'); //TODO.. nothing was done here... 
	    
	        
	        
	        switch ($action = $this->getRequest()->getQuery('__action')) {
	             
	            case "patientdetailsToPrint" :
	                
	                $boxesOpen = "ONLY_WITH_CONTENT";
	                $this->view->boxesOpened       = $boxesOpen;
	                
	                $output = $this->view->render('patientnew/patientdetails.phtml');
	                
	                $this->__versorger_ToPDForPRINT($output , "PatientDetailsPDF_" . $patientMasterData["nice_name_epid"] , "PRINT");
	                
	                exit; //for read-ability.. exit is done before
	                
	                break;
	            
	            case "patientdetailsToPDF" :
	                
	                $boxesOpen = "ONLY_WITH_CONTENT";
	                $this->view->boxesOpened       = $boxesOpen;
	                
	                $output = $this->view->render('patientnew/patientdetails.phtml');
	                
	                $this->__versorger_ToPDForPRINT($output , "PatientDetailsPDF_" . $patientMasterData["nice_name_epid"] , "PDF");
	                
	                exit; //for read-ability.. exit is done before 
	                
	                break;
	        }   
	    }
	    
	}
	
	private function __updateBoxOrder($data = ['col', 'order']) 
	{
	        
	    $userid = $this->logininfo->userid;
	        
        $delete = new BoxOrder();
        $del = $delete->deleteOrder($userid, $data['col']);
         
        if ( ! empty($data['order'])) {
            $boxOrder = [];
            foreach($data['order'] as $position => $item)
            {
                $boxOrder[] = [
                    'userid'    => $userid,
                    'boxcol'    => $data['col'],
                    'boxid'     => $item,
                    'boxorder'  => $position,
                ];
            }
            
            $obj = new Doctrine_Collection('BoxOrder');
            $obj->fromArray($boxOrder);
            $obj->save();
        }
	}
	
	/**
	 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 * !!! i've changed the viewSuffix to phtml !!!
	 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 */
	public function versorgerAction()
	{
	    //TODO-3589 Ancuta 12.11.2020
	    ob_clean();
	    //--
	    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
	    
        if ( ! $this->_patientMasterData) {
            $patientmaster = new PatientMaster();
            $patientinfo = $patientmaster->getMasterData($this->dec_id, 1);
            $this->_patientMasterData = $patientmaster->get_patientMasterData();
        }

	    $patientMasterData = $this->getPatientMasterData();
	    
	    $this->view->encid = $this->enc_id;
	    
	    $memoKey = "memo-versorger";
	    	     
	    $block_name = "PatientVersorger"; //this var is just for info
	    
	    $boxOrder = [201, 202, 203]; //201 = left //ISPC-2703, elena, 04.01.2021
	    
	    $ef = new ExtraForms();
	    $allowedFormBoxes = $ef->get_client_forms($this->logininfo->clientid);
	     
	    
	    if ($this->getRequest()->isPost()) {
	        
	        switch ($action = $this->getRequest()->getPost('__action')) {
	            
	            case "updateBoxOrder" :
	                 
	                $vv = new Application_Form_Versorger( 
    	               array(
	                       '_block_name'          => $block_name,
	                       '_onlyThisModel'       => "NULLLL",
	                       '_clientForms'         => $allowedFormBoxes,
	                   ),
	                   $this->ipid
	                );
	                
	                $default_categories = $vv->get_default_categories();
	                 
	                $col = $this->getRequest()->getPost("col");
	                $col = ($col == "provider_left") ? 201 : (($col == "provider_right") ? 202 : 203); //ISPC-2703,elena,05.01.2021
	                 
	                $order = $this->getRequest()->getPost("order");
	                 
	                array_walk($order, function(&$item) use ($default_categories) {
	                    $item = str_replace('box-', '', $item);
	                    $item = $default_categories[$item]['extra_form_ID'];
	                });
	                     
                    $this->__updateBoxOrder( ['col' => $col, "order" => $order]);
            
                    $responsArr = array(
                        'success' => true,
                        'message' => "box sorted",
                    );
                     
                    $this->_helper->json($responsArr, true);
                     
                    exit; //for readbility
                    
                    break;
                    
	                    
	            case "deleteVersorger" :
	                
	                $responsArr = array(
	                    'success' => false,
	                    'message' => $this->translate('Something went wrong, please contact admin.') . " err:v1",
	                );
	                
	                $category = $this->getRequest()->getPost('__category');
	                 
	                
	                $vv = new Application_Form_Versorger(
	                    array(
	                        '_patientMasterData'   => $patientMasterData,
	                        '_block_name'          => $block_name,
	                        '_clientForms'         => $allowedFormBoxes,
	                        '_onlyThisModel'       => $category,
	                    ),
	                    $this->ipid
	                );
	                
	                $result = $vv->deletePatientVersorger($this->getRequest()->getPost());
	                
	                if ($result === true) {
	                     
	                    //ISPC-2807 Lore 25.02.2021
	                    $was_deleted = $this-> save_deleteStamdatenVersorger_toVerlauf($_POST);
	                    
	                    $success = true;
	                     
	                    $data = $vv->getPatientData();
	                     
	                    $responsArr = array(
	                        'success' => true,
	                        'data' => $data[$category]);
	                     
	                
	                }
	                
	                $responsJson =  $this->_safe_json_encode($responsArr);
	                
	                $this->getResponse()
	                ->setHeader('Content-Type', 'application/json')
	                ->setBody($responsJson)
	                ->sendResponse();
	                 
	                //$this->_helper->json($responsArr, true);
	                 
	                exit; //for readbility
	                
                    break;
	            
	            case "saveVersorger":
	                
	                $responsArr = array(
	                   'success' => false,
	                   'message' => $this->translate('Something went wrong, please contact admin.') . " err:v2", 
	                );


	                /*
	                 * this if is needed because we don't auto-fetch data on ajax requests
	                 * fetch like this the minimum info data... this will be needed to send emails or other stuff
	                 */
	                if (empty($patientMasterData)) {
	                    $patM = new PatientMaster();
	                    $patM->getMasterData($this->dec_id);
	                    $patientMasterData = $patM->get_patientMasterData();
	                }
	                
	                $category = $this->getRequest()->getPost('__category');
	                
	                $vv = new Application_Form_Versorger(
	                    array(
	                        '_patientMasterData'   => $patientMasterData,
	                        '_block_name'          => $block_name,
	                        '_clientForms'         => $allowedFormBoxes,
	                        '_onlyThisModel'       => $category,
	                    ),
	                    $this->ipid
	                );
	                
	                
	                $result = $vv->updatePatientVersorger($this->getRequest()->getPost());
	                
	                if ($result === true) {
	                    
	                    $success = true;
	                    
	                    $data = $vv->getPatientData();
	                    
	                    $responsArr = array(
	                        'success' => true, 
	                        'data' => $data[$category]);
	                        

	                } elseif ($result === false)  {
	                    $responsArr = array(
	                        'success' => false,
	                        'message' => $this->translate('Something went wrong, please contact admin.') . " err:v3", 
	                   );
	                    
	                } else {
	                    //we have errors in the form validate
	                    $message = $success;
	                    $success = false;
	                    $data = array();
	                    
	                    $responsArr = array(
	                        'success' => false, 
	                        'editDialogHtmlWithErrors' => $result,
	                    );
	                }
	                
	                
	                $responsJson =  $this->_safe_json_encode($responsArr);
	               
	                $this->getResponse()
	                ->setHeader('Content-Type', 'application/json')
	                ->setBody($responsJson)
	                ->sendResponse();
	                
	                //$this->_helper->json($responsArr, true);
	                
	                exit; //for readbility
	                
                break;
                
	            case "updateMemos":
	                
	                $entry = PatientVersorger::getEntry($this->ipid, $memoKey);
	                 
	                $category = $this->getRequest()->getPost('__category');
	                 
	                $entry[$category] = array(
	                    "color"    => $this->getRequest()->getPost('color'), 
	                    "memo"     => $this->getRequest()->getPost('memo'),
                    );
	                 
	                PatientVersorger::updateEntry($this->ipid, $memoKey, $entry);
	                 
	                $this->_helper->json(array('success'=>true, 'message' => 'OK!'), true);
	                 
	                exit(); //for readbility
	                
                break;
	        }
	        
	    } else {
	        /*
	         * this is the GET part
	         * in this part you fetch data,
	         * you DO NOT alter the main records
	         * you can only log the visit or other stuff.. but do NOT edit the main details via a GET
	         */
	        	    
	        
// 	        $this->_helper->flashMessenger('Error message just for funTest' , "ErrorMessages");
// 	        $this->__versorger_populateCurrentMessages();
	        /* // ISPC-2612 Ancuta 29.06.2020 + ISPC-2381 Carmen 18.01.2021
	        $client_is_follower_aid = ConnectionMasterTable::_check_client_connection_follower('Aid', $this->clientid);
	        if($client_is_follower_aid){
	           	$this->view->data['hilfsmittel_favs'] = Aid::get_default_aids($this->clientid,true);
	        } else{
    	    	$this->view->data['hilfsmittel_favs'] = Aid::get_default_aids($this->clientid);
	        }
	        // -- */
	        $boxesOpen = OverviewCookie::getCookieData($this->logininfo->userid, 'patientdetails');
	        if (empty($boxesOpen[0])) {
	            //use default as open only those with data
	            $boxesOpen = "ONLY_WITH_CONTENT";
// 	            $boxesOpen = "ALL_OPENED";
	             
	        } else {
	             
	            $boxesOpen = $boxesOpen[0];
	             
	            switch ($boxesOpen['useroption']) {
	                case "1" :
	                    $boxesOpen = "ALL_CLOSED";
	                    break;
	        
	                case "2" :
	                    $boxesOpen = "ONLY_WITH_CONTENT";
	                    break;
	                     
	                case "3" :
	                    $boxesOpen = explode(",", $boxesOpen['cookie']);
	        
	                    array_walk($boxesOpen, function(&$item){
	                        $item = filter_var($item, FILTER_SANITIZE_NUMBER_INT);
	                    });
	                         
                        $boxesOpen = array_filter($boxesOpen);
                        break;
	            }
	        }
	        
	        $vv = new Application_Form_Versorger(
	            array(
	                '_patientMasterData'   => $this->getPatientMasterData(),
	                '_block_name'          => $block_name,
	                '_clientForms'         => $allowedFormBoxes,
	            	'_is_clinic_sync' => true, //ISPC-2381 Carmen 26.01.2021
	            ),
	            $this->ipid
	        );
	        
	        $allCategories = $vv->getAllCategories();
	         
	        $boxesPlacement = [];
	        
	        foreach ($allCategories as $key => $cat) {
	            
	            if ( ! isset($allowedFormBoxes[$cat['extra_form_ID']]) 
	                || ! $allowedFormBoxes[$cat['extra_form_ID']]) 
	            {
	                //not allowed to this box (@dev you can go to /extraforms/formlist and assign a box to a client)    
	                continue;
	            }
	            
	            if ($cat['placement'] == 'left') {
	                $boxesPlacement['left'][] = $key;
	            } elseif ($cat['placement'] == 'right') {
	                $boxesPlacement['right'][] = $key;
	            }elseif ($cat['placement'] == 'third') { //ISPC-2703,elena,05.01.2021
	                $boxesPlacement['third'][] = $key;
	            }
	        }
	        
	        $userBoxOrder = BoxOrder::fetchUserCol($this->logininfo->userid, $boxOrder);
	        if ( ! empty($userBoxOrder)) {
	             
	            $newBoxesPlacement = ["left" => [], "right" => [], "third" => []];//ISPC-2703,elena,05.01.2021
	             
	            foreach ($userBoxOrder as $order) {
	                 
	                $placement = $order['boxcol'] == 201 ? "left" : ( ($order['boxcol'] == 202) ? "right"  : "third"); //ISPC-2703,elena,05.01.2021
	                 
	                $boxID = $order['boxid'];
	        
	                $boxes = array_filter($allCategories, function($item) use ($boxID) {
	                    return $item['extra_form_ID'] == $boxID;
	                });
	                     
	                    $newBoxesPlacement[$placement] = array_merge($newBoxesPlacement[$placement], array_keys($boxes));
	            }
	             
	            foreach ($boxesPlacement as $placement => $boxes) {
	                foreach ($boxes as $box) {
	                    if ( ! in_array($box, $newBoxesPlacement ['left']) && ! in_array($box, $newBoxesPlacement ['right'])&& ! in_array($box, $newBoxesPlacement ['third'])) { //ISPC-2703,elena,05.01.2021
	                        //newbox
	                        $newBoxesPlacement [$placement][] = $box;
	                    }
	                }
	            }
	             
// 	            dd($newBoxesPlacement);
	            $boxesPlacement = $newBoxesPlacement;
	        
	        }
	        
            if($_REQUEST['ajax']){
                $this->_helper->layout->setLayout('layout_ajax');
	         
                //hide Boxes in ajax mode
                $disable=['SYSTEMSYNC'];
                foreach ($boxesPlacement as $i=>$foo){
                    foreach($boxesPlacement[$i] as $j=>$name){
                        if(in_array($name, $disable)){
                            unset($boxesPlacement[$i][$j]);
                        }
                    }
                }
            }
	         
	        $this->view->boxesOpened       = $boxesOpen;
	        $this->view->boxesPlacement    = $boxesPlacement;
	        $this->view->mappings          = $allCategories;
	        $this->view->data              = $vv->getPatientData($this->ipid);
	        
	        $this->view->memos             = PatientVersorger::getEntry($this->ipid, $memoKey);
	        $this->view->disabledversorger = ClientConfig::getConfig($this->clientid, 'versorgerboxes'); //TODO.. nothing was done here... 
	    
	        // ISPC-2612 Ancuta 29.06.2020 + ISPC-2381 Carmen 18.01.2021
	        $client_is_follower_aid = ConnectionMasterTable::_check_client_connection_follower('Aid', $this->clientid);
	        if($client_is_follower_aid){
	        	$this->view->data['hilfsmittel_favs'] = Aid::get_default_aids($this->clientid,true);
	        } else{
	        	$this->view->data['hilfsmittel_favs'] = Aid::get_default_aids($this->clientid);
	        }
	        // --
	        
	        
	        switch ($action = $this->getRequest()->getQuery('__action')) {
	             
	            case "versorgerToPrint" :
	                
	                $boxesOpen = "ONLY_WITH_CONTENT";
	                $this->view->boxesOpened       = $boxesOpen;
	                
	                $output = $this->view->render('patientnew/versorger.phtml');
	                
	                $this->__versorger_ToPDForPRINT($output , "VersorgerPDF_" . $patientMasterData["nice_name_epid"] , "PRINT");
	                
	                exit; //for read-ability.. exit is done before
	                
	                break;
	            
	            case "versorgerToPDF" :
	                
	                $boxesOpen = "ONLY_WITH_CONTENT";
	                $this->view->boxesOpened       = $boxesOpen;
	                
	                $output = $this->view->render('patientnew/versorger.phtml');
	                
	                $this->__versorger_ToPDForPRINT($output , "VersorgerPDF_" . $patientMasterData["nice_name_epid"] , "PDF");
	                
	                exit; //for read-ability.. exit is done before 
	                
	                break;
	        }   
	    }
	    
	}
	
	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function versorgerreportAction(){
        $this->_helper->layout->setLayout('layout_ajax');

        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpId($decid);
        $this->view->encid=Pms_Uuid::encrypt($decid);
        $pat_details=new ClinicVersorger();

        $fdata=$pat_details->getPatientData($ipid);
        $data=$pat_details->renderreportextract($fdata);
        echo json_encode($data,1);
        exit();
    }


	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function psychosocialstatusreportAction(){
        $this->_helper->layout->setLayout('layout_ajax');
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpId($decid);
        $this->view->encid=Pms_Uuid::encrypt($decid);

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $categories = Client::getClientconfig($clientid, 'boxes_psychosocial_status');

        $merge_cat = array_merge(array_values($categories['left']), array_values($categories['right']));
        $pat_details=new PatientDetails($ipid);
        $fdata=$pat_details->patientdata_get_pretty($merge_cat);

        $data=$pat_details->renderreportextract($fdata);
        echo $data;
       // echo json_encode($data, 1);
        exit();

    }

	//Maria:: Migration CISPC to ISPC 22.07.2020
    public function clinicdiagnosisreportAction()
    {
        $this->_helper->layout->setLayout('layout_ajax');
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpId($decid);
        $options['reloadDiagnosis'] = true;

        $af_fbkv = new Application_Form_FormBlockKeyValue();
        $form = $af_fbkv->create_form_clinic_diagnosis($options, $ipid);

        $data = $form->render();
        echo $data;

       // echo json_encode($data, 1);
        exit();

    }
	
	
	//http://php.net/manual/en/function.json-last-error.php
	private function _safe_json_encode($value, $options = 0, $depth = 512) {
	    $encoded = json_encode($value, $options, $depth);
	    if ($encoded === false && $value && json_last_error() == JSON_ERROR_UTF8) {
	        $encoded = json_encode($this->_utf8ize($value), $options, $depth);
	    }
	    return $encoded;
	}
	
	private function _utf8ize($mixed) {
	    if (is_array($mixed)) {
	        foreach ($mixed as $key => $value) {
	            $mixed[$key] = $this->_utf8ize($value);
	        }
	    } elseif (is_string($mixed)) {
	        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
	    }
	    return $mixed;
	}
	
	
	/**
	 * 
	 * @param string $html_form
	 * @param string $filename
	 * @param string $PDForPRINT , "PDF" or "PRINT"
	 */
	private function __versorger_ToPDForPRINT($html_form = '' , $filename = "VersorgerPDF", $PDForPRINT = "PDF")
	{
        $filename = Pms_CommonData::filter_filename($filename);
    
        $patientMasterData = $this->getPatientMasterData();
	    $nice_name_epid = $patientMasterData['nice_name_epid'];
        $birthd = $patientMasterData['birthd'];	    
	    $today_date = date('d.m.Y');
	    
	    if ($PDForPRINT == "PDF") {
	       
	        $css_link = APPLICATION_PATH . '/../public';
	       
	       $this->_print_css_path = PUBLIC_PATH;//used in __versorger_ToPDForPRINT_prepare_dompdf
	       
	    } else {
	        
	        $css_link = RES_FILE_PATH;
	        
	        $this->_print_css_path = RES_FILE_PATH; //used in __versorger_ToPDForPRINT_prepare_dompdf
	        
	    }
	    
	    $what_page_is_printing = $_REQUEST['__action'] == 'versorgerToPrint' || $_REQUEST['__action'] == 'versorgerToPDF' ? 'Versorger' : 'Stammdaten';      // TODO-3827 Lore 08.02.2021
	    $wlassessmentHead = <<<EOT
<html>
    <head>
        <link href="{$css_link}/css/page-css/versorger_pdf.css" rel="stylesheet" type="text/css" />
        <style>
            @page { margin: 20px 20px 60px 40px; }
        </style>
    </head>
    <body class='wlassessment_form_class'>
    
        <div id="content">
            
            <div class="info_first_page_head">
    	       <span><b>ISPC - Informationssystem Palliative Care</b></span>
    	       <br/>
    	       <span><b>{$nice_name_epid} ({$birthd})</b></span>
    	       <br/>
    	       <span><b>Datum : {$today_date}</b></span>
    	       <br/>
    	       <br/>
    	       <!-- <span><b>Versorger</b></span> -->
               <span><b>{$what_page_is_printing}</b></span>       <!-- TODO-3827 Lore 08.02.2021-->
            </div>
            
            <br/>
  
        
EOT;
	    
	    
	    $wlassessmentFoot = <<<EOT
        </div>
    </body>
</html>
EOT;
	    
//Example: to user header and footer in html using counters
//example is not used in our fn
 $wlassessmentHead_with_counters = <<<EOT
<html>
    <head>
        <link href="%s/../public/css/page-css/versorger_pdf.css" rel="stylesheet" type="text/css" />
        <style>
            @page { margin: 20px 20px 50px 20px; }
            #header {
                position: fixed;
                left: 0px;
                top: -50px;
                right: 0px;
                height: 0px;
                text-align: center;
            }
            #footer {
                position: fixed;
                left: 0px;
                bottom: -50px;
                right: 0px;
                height: 40px;
                text-align: center;
                font-size:10px;
            }
            #footer .page:after { content: counter(page, decimal); }
        </style>
    </head>
    <body class='wlassessment_form_class'>
        <div id="header">
        </div>
        <div id="footer">
            Seite: <span class="page"></span> | Datum: {$today_date} | {$nice_name_epid}
        </div>
        <div id="content">
EOT;
        
	    
	    
        $html_form =  $this->__versorger_ToPDForPRINT_prepare_dompdf($html_form, $css_link);
        
        
        //$html_print = sprintf($wlassessmentHead, APPLICATION_PATH )
        $html_print = $wlassessmentHead
        //                             sprintf($wlassessmentHead, APP_BASE)
        . $html_form
        . $wlassessmentFoot
        ;
        
        
        
        if ($PDForPRINT == "PRINT") {
           echo $html_print;
           exit;
        }

        //$html_print = file_get_contents('/home/www/ispc20172/public/temp/wlassessment.html');

        //TODO-3589 Ancuta 12.11.2020
        ob_get_clean ( );
        //--
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('enable_html5_parser', true);         //TODO-3848 Lore 12.02.2021 - No block-level parent found. Not good.// get_cellmap() 
        $dompdf = new Dompdf($options);

        //                 $dompdf = new Dompdf(array('isRemoteEnabled'=> false));
        $dompdf->loadHtml($html_print);
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');




        $dompdf->set_option("enable_php",true);
        $dompdf->set_option('defaultFont', 'times');
        $dompdf->set_option("fontHeightRatio",0.90);

        // Render the HTML as PDF
        //TODO-3589 Ancuta 12.11.2020
        ob_clean();
        //--
        $dompdf->render();


        // add the footer
        //TODO move this footer in to a config class, along with default font ant others
        $canvas = $dompdf->get_canvas();

        $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
        $footer_font_size = 10;

        $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
        $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
        $footer_text = "Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text


        $canvas->page_text(
            ($canvas->get_width() - $text_width)/2,
            $canvas->get_height()-30,
            $footer_text,
            $footer_font_family,
            $footer_font_size,
            array(0,0,0));



       $output = $dompdf->output();

        // Output the generated PDF to Browser
       $dompdf->stream($filename, array('Attachment' => true));
       //$dompdf->stream();    
        
        exit;
    
	}
	
	
	// define functions used for callbacks
	private function radio_check($match) {
	    if (stripos ( $match [0], 'checked="checked"' ) !== false) {
	        return '<img src="' . $this->_print_css_path . '/images/radio-selected-btn.jpg"  style="width:12px; margin:2px 2px 0 0;" alt="" />';
	    } else {
	        return '<img src="' . $this->_print_css_path . '/images/radio-btn.jpg"  style="width:12px;margin:2px 2px 0 0;"  alt="" />';
	    }
	}
	private function checkbox_check($match) {
	    if (stripos ( $match [0], 'checked="checked"' ) !== false) {
	        return '<img src="' . $this->_print_css_path . '/images/check02.jpg" style="width:12px; margin:2px 2px 0 0;" alt="" />&nbsp;';
	    } else {
	        return '<img src="' . $this->_print_css_path . '/images/check01.jpg"  style="width:12px; margin:2px 2px 0 0;" alt="" />&nbsp;'; //ISPC-2157 ANCUTA 05.03.2018 :: added spacing after img - PLEASE CHECK
	    }
	}
	
	
	private function __versorger_ToPDForPRINT_prepare_dompdf($html) 
	{ 
	    // replaces form elements for PDF generating
	
	    // match checkboxes
	    $checkbox_pat = "/<input.*type=[\"']?checkbox[\"']?.*>/iU";
	
	    // match radios
	    $radio_pat = "/<input.*type=[\"|']?radio[\"|']?.*>/iU";
	    
	    // replace
	    $html = preg_replace_callback ( $radio_pat, array($this, 'radio_check'), $html );
	    $html = preg_replace_callback ( $checkbox_pat,array($this, 'checkbox_check'), $html );
	
	    //replace info buttons
	    $html = preg_replace("/<div(.*?)class=[\"']?info-button[\"']?>(.*?)<\/div>/" , "" , $html);

	    //remove ibutton info
	    $html = preg_replace("/<span(.*?)class=[\"']?ibutton(.*)>(.*?)<\/span>/" , "" , $html);
	    
	    $html = preg_replace("/<script type=\"text\/javascript\">(.*?)<\/script>/s" , "<!-- INLINE SCRIPT WAS HERE -->" , $html);

	   /* TODO-3848,Elena,10.02.2021 */
	    $html = preg_replace("/<script>(.*?)<\/script>/s" , "<!-- INLINE SCRIPT 2 WAS HERE -->" , $html);

	    $html = preg_replace("/<noscript>(.*?)<\/noscript>/" , "<!-- INLINE NOSCRIPT WAS HERE -->" , $html);
	    
	    
	    
// 	    ('/<([^\s]+)[^>]*rel="nofollow"[^>]*>.*?(/\1)/si'
	    
// 	    dd ($html);
	    return $html;
	}
	
	
	private function __versorger_populateCurrentMessages()
	{
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	}
	/**
	 * ISPC-2508 Carmen
	 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
	 */
	public function updateartificialentryexitAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->setLayout('layout_ajax');
	
		if($_REQUEST['action'] == 'delete')
		{
			$entity = PatientArtificialEntriesExitsTable::getInstance()->findOneBy('id', $_REQUEST['delid']);
			echo $entity->delete();
		}
	}
	

    /**
     * ISPC-2241, elena, 02.09.2020 (took from clinic)
     */
    public function addressbookAction(){
        $vv=new ClinicVersorger;
        $this->view->patientdata=$vv->getPatientData($this->ipid);


        $this->view->categories=$vv->getAllCategories();
        $this->view->addressbook=array();

        $this->view->encid=Pms_Uuid::encrypt($this->dec_id);


        /* ================ PATIENT HEADER ======================= */
        $patientmaster = new PatientMaster();
        $this->view->patientinfo = $patientmaster->getMasterData($this->dec_id, 1);

        /* ================ PATIENT TAB MENU ======================= */
        $tm = new TabMenus();
        $this->view->tabmenus = $tm->getMenuTabs();

    }

    /**
     * ISPC-2241, elena, 02.09.2020 (took from clinic)
     */
    public function addressbookentriesAction(){
        $this->_helper->layout->setLayout('layout_ajax');
        $vv=new ClinicVersorger();
        $this->view->categories=$vv->getAllCategories();
        $this->view->tab=$_REQUEST['tab'];
        $tempstr = Array("Ä" => "AE", "Ö" => "OE", "Ü" => "UE", "ä" => "ae", "ö" => "oe", "ü" => "ue", "ß"=>"ss");
        //TODO-3496, elena, 12.10.2020
        $aCatTypes = [
            'family_doctor' => 'H',
            'specialists' => 'F',
            'health_insurance' => 'I',
            'funeral' => 'B',
            'location' => 'AU',
            'pharmacy' => 'A',
            'supplies' => 'SH',
            'suppliers' => 'SR',
            'homecare' => 'HC',
            'pflegedienst' => 'P',
            'hospice_association' => 'HA',
            'physiotherapists' => 'PH'


        ];
        $this->view->cattypes = $aCatTypes;
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $m_specialists_types = new SpecialistsTypes();
        $specialists_types  =$m_specialists_types->get_specialists_types($clientid);
        $s_type = [];
        if(!empty($specialists_types)){
            foreach($specialists_types as $k=>$tp){
                $s_type[$tp['id']] = $tp['name'];
            }
        }
        $this->view->s_type = $s_type;

        if($this->view->tab == "patient") {
            $this->view->entries=array();
            $pd=new PatientDetails($this->ipid);
            $cp=$pd->patientdata_get_by_cat('contactperson');

            if(count($cp)) {
                $this->view->categories['contactperson'] = array('label' => 'Kontaktperson');
                foreach ($cp[0]['meta']['cols'] as $cpp) {
                    $this->view->categories['contactperson']['cols'][] = array('label' => $cpp['label'], 'class' => $cpp['db']);
                }

                foreach ($cp as $cpi=>$cpp){
                    $address=array();
                    $address[]=array('Name', trim(implode(", ",[$cpp['data']['cnt_last_name'], $cpp['data']['cnt_first_name']])));
                    $address[]=array('Straße', $cpp['data']['cnt_street1']);
                    $address[]=array('PLZ/Ort', $cpp['data']['cnt_zip'] . " " . $cpp['data']['cnt_city']);

                    $cp[$cpi]['address']=$address;
                }

            }

            $this->view->entries['contactperson']=$cp;

            $vvd = $vv->getPatientData($this->ipid);
            foreach($vvd as $vvk=>$vvd){
                $this->view->entries[$vvk]=$vvd;
            }

        }elseif($this->view->tab == "all"){ //TODO-3496, elena, 09.10.2020
           $sortarray= array();
           $alldata = [];
           $cats = array_keys($vv->categories);

           foreach($cats as $cat){ //aggregate all
                  $vvdata = array_merge($vv->getAddressbook($cat, $this->clientid, 0));

                   foreach ($vvdata as $vi => $entry) {
                       //echo $vi;
                       $keys = array();
                       $name = $entry['data']['name'];
                       //print_r($entry);

                       $name2 = trim($entry['data']['title'] . " " . $entry['data']['first_name']);
                       $name2 = trim(implode(', ', [$entry['data']['last_name'], $name2]));

                       if (!strlen($name) || (($this->view->tab == "family_doctor" || $this->view->tab == "specialists") && strlen($name2))) {
                           $name = $name2;
                           if (strlen($entry['data']['last_name'])) {
                               $keys[] = strtolower(substr(strtr($entry['data']['last_name'], $tempstr), 0, 1));
                           }
                           if (strlen($entry['data']['first_name'])) {
                               $keys[] = strtolower(substr(strtr($entry['data']['first_name'], $tempstr), 0, 1));
                           }
                       } else {
                           if (strlen($entry['data']['name'])) {
                               $keys[] = strtolower(substr(strtr($entry['data']['name'], $tempstr), 0, 1));

                           }
                       }
                       $vvdata[$vi]['data']['sortname'] = $name;
                       $vvdata[$vi]['data']['sortkeys'] = array_unique($keys);
                       $sortarray[] = strtolower(strtr($name, $tempstr));
                       //TODO-3496, elena, 12.10.2020
                       if($cat == 'specialists' && isset($s_type[$vvdata[$vi]['data']['medical_speciality']])){
                           $vvdata[$vi]['data']['medical_speciality'] = $s_type[$vvdata[$vi]['data']['medical_speciality']];
                       }
                       $vvdata[$vi]['cat'] = $cat;
                       $alldata[] = $vvdata[$vi];
                   }


           }
            array_multisort($sortarray, SORT_ASC, SORT_STRING, $alldata);

            $this->view->entries=array($this->view->tab => $alldata);

        }else{
            $vvdata=$vv->getAddressbook($this->view->tab, $this->clientid, 0);
            $sortarray=array();
            foreach ($vvdata as $vi=>$entry){
                $keys=array();
                $name=$entry['data']['name'];

                $name2=trim($entry['data']['title'] ." " . $entry['data']['first_name']);
                $name2=trim(implode(', ', [$entry['data']['last_name'], $name2]));

                if(!strlen($name) || (($this->view->tab =="family_doctor" || $this->view->tab =="specialists") && strlen($name2))){
                    $name=$name2;
                    if(strlen($entry['data']['last_name'])){
                        $keys[]=strtolower(substr(strtr($entry['data']['last_name'],$tempstr),0,1));
                    }
                    if(strlen($entry['data']['first_name'])){
                        $keys[]=strtolower(substr(strtr($entry['data']['first_name'],$tempstr),0,1));
                    }
                }else{
                    if(strlen($entry['data']['name'])){
                        $keys[]=strtolower(substr(strtr($entry['data']['name'],$tempstr),0,1));

                    }
                }
                //TODO-3496, elena, 12.10.2020
                if($this->view->tab == 'specialists' && isset($s_type[$vvdata[$vi]['data']['medical_speciality']])){
                    $vvdata[$vi]['data']['medical_speciality'] = $s_type[$vvdata[$vi]['data']['medical_speciality']];
                }
                $vvdata[$vi]['data']['sortname'] = $name;
                $vvdata[$vi]['data']['sortkeys'] = array_unique($keys);
                $sortarray[]=strtolower(strtr($name, $tempstr));
            }

            array_multisort($sortarray, SORT_ASC, SORT_STRING, $vvdata);

            $this->view->entries=array($this->view->tab => $vvdata);
        }

    }

    public function clinichospizregisterAction(){
        $clientid = $this->clientid;
        $userid = $this->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpId($decid);
        $this->view->encid=Pms_Uuid::encrypt($decid);
        
        if(! isset($_REQUEST['diswidget']) && ! isset($_REQUEST['ajax'])) {
            /* ================ PATIENT HEADER ======================= */
            $patientmaster = new PatientMaster();
            $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
            
            /* ================ PATIENT TAB MENU ======================= */
            $tm = new TabMenus();
            $this->view->tabmenus = $tm->getMenuTabs();
        }
        
        
        if($_POST){
            if(isset($_POST['dgp'])){
                $caseid=$_POST['dgp']['case_id'];
            }
            if(isset($_POST['dgp_d'])){
                $caseid=$_POST['dgp_d']['case_id'];
            }
            $addcourse=true;
            if($caseid>0){
                if(isset($_POST['dgp'])) {
                    ClinicDgpKern::set_patient_data($ipid, $caseid, $_POST['dgp']);
                    $addcourse=false;
                }
                if(isset($_POST['dgp_d'])) {
                    $dgp_temp=$_POST['dgp_d'];
                    unset($dgp_temp['case_id']);
                    $str_temp=implode("",$dgp_temp);
                    if(strlen($str_temp)){
                        ClinicDgpKern::set_patient_data($ipid, $caseid, $_POST['dgp_d'], false, $addcourse);
                    }
                }
            }
            if(!isset($_REQUEST['diswidget'])){
                $this->_redirect(APP_BASE . 'patientcourse/patientcourse?id=' . $_REQUEST['id']);
                exit();
            }
            if(isset($_REQUEST['ajax'])){
                $this->_helper->viewRenderer->setNoRender();
                $this->_helper->layout->setLayout('layout_ajax');
                echo "OK";
                exit();
            }
        }
        
        
        $this->view->show_adm_block=true;
        $this->view->show_dis_block=true;
        
        
        $cases=ClinicDgpKern::get_patient_data($ipid,1);
        $this->view->cases=$cases['cases'];

        $this->view->prefill_vorlage=$cases['vorlage'];
        $this->view->prefill_fachdienste=$cases['fachdienste'];
        $this->view->prefill_wohn=$cases['wohn'];
        $listmodel = new Selectboxlist();
        $this->view->list_grund=$listmodel->getList('register_grund');
        
        if($_REQUEST['case_id']){
            $this->view->selected_case=intval($_REQUEST['case_id']);
            
            foreach ($this->view->cases as $case){
                if($case['case_id'] == $this->view->selected_case){
                    $this->view->adm_case=$case['saved_adm'];
                    $this->view->dis_case=$case['saved_dis'];
                }
            }
            
        }else{
            $a=end($this->view->cases);
            $this->view->selected_case=$a['case_id'];;
            $this->view->adm_case=$a['saved_adm'];
            $this->view->dis_case=$a['saved_dis'];
        }
        if(isset($this->view->dis_case) && is_array($this->view->dis_case)){
            $this->view->show_dis_block=true;
        }
        
        $this->view->diswidget=false;
        if(isset($_REQUEST['diswidget'])){
            $this->view->show_dis_block=true;
            $this->view->show_adm_block=false;
            $this->view->diswidget=true;
            $this->_helper->layout->setLayout('layout_ajax');
            
        }
        
        if(isset($_REQUEST['ajax'])){
            $this->_helper->layout->setLayout('layout_ajax');
            $this->view->ajax=true;
        }
        
    }
    
    //ISPC-2807 Lore 25.02.2021
    public function save_deleteStamdatenVersorger_toVerlauf($post){
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $list_box = array("FamilyDoctor");
        $course_title = '';
        $recordid = '';
        
        if(in_array($post['__category'], $list_box)){
            $box_name = $this->translate("[".$post['__category']." Box Name]");
            
            foreach($post as $key => $vals){
                if(isset($vals[$post['__category']])){
                    $course_title .= "Des ". $box_name.' wurde entfernt: '. $vals[$post['__category']]['nice_name']. "\n\r";
                    $recordid = $vals[$post['__category']]['id'];
                }
            }
        }
        
        if(!empty($course_title)){
            $insert_pc = new PatientCourse();
            $insert_pc->ipid =  $ipid;
            $insert_pc->course_date = date("Y-m-d H:i:s", time());
            $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
            $insert_pc->tabname = Pms_CommonData::aesEncrypt($post['__category']);
            $insert_pc->recordid = $recordid;
            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_title));
            $insert_pc->user_id = $userid;
            $insert_pc->save();
        }

        
    }

    /**
     * INFO-1554 Nico
     * Page to display KLAU from HL7
     */
    public function patientklauAction()
    {
        if(isset($_GET['ki'])){
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->setLayout('layout_ajax');
            $ki=intval($_GET['ki']);

            $html=PatientKlau::render_klau($this->ipid, $ki, 'full');
            echo $html;
            exit();
        }else{
            $this->view->encid=$this->enc_id;
            $this->view->klaus_list = Doctrine::getTable('PatientKlau')->findBy('ipid', $this->ipid);
        }
    }
    

}