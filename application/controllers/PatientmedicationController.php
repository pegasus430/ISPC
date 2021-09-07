<?php
use Dompdf\Dompdf;
use Dompdf\Options;
// Maria:: Migration ISPC to CISPC 08.08.2020
class PatientmedicationController extends Pms_Controller_Action {
	
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
		$this->groupid = $logininfo->groupid; //ISPC-2507 Ancuta 05.02.2020
		


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
		    "edit",
		    "editblocks",
		    
		    "overview",
		    //"efamedication",      //ISPC-2829 Lore 08.03.2021
		    
		])
		->setActionsWithJsFile([
		    /*
		     * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
		     */
		    "overview",
		    "edit",
		    "editblocks",
		    "requestchanges",		//ISPC-2507 Lore 03.02.2020
		    "efamedication",        //ISPC-2829 Lore 08.03.2021
		])
		->setActionsWithLayoutNew([
		    /*
		     * actions that will use layout_new.phtml
		     * Actions With Patientinfo And Tabmenus also use layout_new.phtml
		     */
		    'overview',
		    'edit',
		    'editblocks',
		    'deleted',
		    'deletededit',
		    'sets',
		    
		])
		;

	}

		public function overviewAction()
		{
		    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);

		    if($_REQUEST['modal'] =="1"){
		    	$this->_helper->layout->setLayout('layout_ajax');
		    	$this->_helper->viewRenderer("medicationshort");
		    }
		    
		    $barcodereaderKey="";
		    if (Zend_Registry::isRegistered('barcodereader')) {
		        $barcodereader_cfg = Zend_Registry::get('barcodereader');
		        $barcodereaderKey = $barcodereader_cfg['datamatrix']['licenseKey'];
		    }
		    $this->view->barcodereaderKey = $barcodereaderKey;
		    
		    //Changes for ISPC-1848 F
		    //moved in the init()
		    /* ================ PATIENT HEADER ======================= */
// 		    $patientmaster = new PatientMaster();
// 		    $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		    
		    /* ================ PATIENT TAB MENU ======================= */
// 		    $tm = new TabMenus();
// 		    $this->view->tabmenus = $tm->getMenuTabs();
		    
		    // ISPC-2664 Carmen 28.09.2020
		    $vital_signs_arr = array();
		    $latest_vital_signs_weight = FormBlockVitalSigns::get_patients_chart_last_values_byelement($ipid, false, 'weight');
		    $latest_vital_signs_height = FormBlockVitalSigns::get_patients_chart_last_values_byelement($ipid, false, 'height');
		    
		    if(!empty($latest_vital_signs_weight[$ipid])){
		    	$vital_signs_arr['weight'] = number_format($latest_vital_signs_weight[$ipid]['weight'], 3, ',', '.');
		    	$vital_signs_arr['weight_signs_date'] = date('d.m.Y', strtotime($latest_vital_signs_weight[$ipid]['date']));
		    }
		    if(!empty($latest_vital_signs_height[$ipid])){
		    	$vital_signs_arr['height'] = number_format($latest_vital_signs_height[$ipid]['height'], 2, ',', '.');
		    	$vital_signs_arr['height_signs_date'] = date('d.m.Y', strtotime($latest_vital_signs_height[$ipid]['date']));
		    }
		    if($vital_signs_arr['weight'] && $vital_signs_arr['height'])
		    {
		    	$vital_signs_arr['body_surface'] = number_format(0.007184*($vital_signs_arr['height']**0.725)*($vital_signs_arr['weight']**0.425), 3, ',', '.');
		    }
		    $this->view->age = $this->_patientMasterData['age_yearsAndMonths'];
		    $this->view->vital_signs = $vital_signs_arr;
		    //--
		    
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
		    
            //ISPC-2912,Elena,25.05.2021
		    if($modules->checkModulePrivileges("1021", $clientid))//show BTM or not
            {
                $this->view->show_btm = "1";
            } else{
                $this->view->show_btm = "0";
            }
		    
		    //TODO-2508 ISPC: Lore 19.08.2019
		    $pharmacyprivileges = $modules->checkModulePrivileges("75", $clientid);
		    if($pharmacyprivileges){
		        $this->view->pharmacyprivileges = '1';
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
		    $medication_blocks[] = "ispumpe"; //ISPC-2833 Ancuta 26.02.2021
		    
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
		    
		    // Show interval medi BLOCK
			/* Intervall Medis  BLOCK - Intervall Medis */
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		    }

		    // ISPC-ISPC-2329 pct.r)		    
		    // Show interval options  in Actual and IVmed
		    $actual_iv_scheduled_block = $modules->checkModulePrivileges("193", $clientid);
		    if(!$actual_iv_scheduled_block){
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
		    
		    //ISPC-2833 Ancuta 26.02.2021
		    $ispumpe_block = $modules->checkModulePrivileges("251", $clientid);
		    if(!$ispumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("ispumpe"));
		    }
		    //-- 
		    
		    
		    $this->view->medication_blocks = $medication_blocks;
		    
		    
		    /* PHARMACY ORDER */
		    $pharmacyorder = $modules->checkModulePrivileges("50", $clientid);
		    if($pharmacyorder)
		    {
		        $this->view->pharmacyorder = '1';
		    }
		    //recipe request privileges
		    $this->view->reciperequest_privileges = $modules->checkModulePrivileges("150", $clientid);


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
		    
		    /* ================ ISPC-2524 pct.1)  Lore 14.01.2020 ======================== */	
		    $previleges = new Modules();
		    if($previleges->checkModulePrivileges("131", $clientid)){
    		    $patient_files = Doctrine_Query::create()
    		    ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
    						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
    						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
    						->from('PatientFileUpload')
    						->where("ipid=?", $ipid )
    						->andWhere('tabname in ("medikationsplan","medikationsplan_patient","medikationsplan_patient_app")')
    		                ->orderBy("create_date DESC");
    			$contact_form_files = $patient_files->fetchArray();
    			
    		    foreach($contact_form_files as $k_file => $v_file)
    		    {
    		        $users_ids[] = $v_file['create_user'];
    		    }
    		    $this->view->users_data = Pms_CommonData::getUsersData($users_ids);
    		    $this->view->form_files = $contact_form_files;
		    }
		    /*  ...  */
		    
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
		    //ISPC-2329 pct.k) Lore 23.08.2019
		    $new_medication_sets['actual'] = $msets->getmedicationssetsDrop($this->clientid,"actual");
		    
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
		                
		                $this->_redirect(APP_BASE . "patientmedication/overview?id=" . $_REQUEST['id']);
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
		    
		    
		    
		    
		    //ISPC-2797 Ancuta 17.02.2021
		    $elsa_planned_medis = $modules->checkModulePrivileges("250", $clientid);
		    $this->view->elsa_planned_medis = 0;
		    
		    if($elsa_planned_medis){
                $this->view->elsa_planned_medis = 1;
    		    $drugplan_ids2planned_actions = PatientDrugplanPlanning::get_planned_drugs($ipid);
		    }
		    //--
		    
		    
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
		        //ISPC-2833 Ancuta 26.02.2021
		        elseif($medication_data['ispumpe'] == "1")
		        {
		            $medications_array['ispumpe'][] = $medication_data;
		            $pp_pumpe_ids[] = $medication_data['pumpe_id'];
		        }
		        //--
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
		    
		    
		    //ISPC-2833 Ancuta 02.03.2021
		    // get pumpe details
		    $pp_pumpe_ids = array_unique($pp_pumpe_ids);
		    
		    if(count($pp_pumpe_ids) == 0)
		    {
		        $pp_pumpe_ids[] = '999999';
		    }
		    
		    $pp_pumpe_obj = new PatientDrugplanPumpe();
		    $perfusor_pumpe_data = $pp_pumpe_obj->get_perfusor_pumpes($pp_pumpe_ids);
		    
		    
		    if(count($perfusor_pumpe_data) > 0)
		    {
		        $addnew = 0;
		    }
		    else
		    {
		        $addnew = 1;
		    }
		    $this->view->addnewlink_ispumpe = $addnew;
		    $this->view->perfusor_pumpe_data_array = $perfusor_pumpe_data;
		    
		    
		    $alt_pumpe_details = PatientDrugplanPumpeAlt:: get_drug_pumpe_alt($ipid,$pp_pumpe_ids);
		    $alt_pumpe_declined = PatientDrugplanPumpeAlt:: get_declined_drug_pumpe_alt($ipid,$pp_pumpe_ids,false);
		    $alt_cocktail_declined_offline = PatientDrugplanPumpeAlt:: get_declined_drug_pumpe_alt_offline($ipid, $pp_pumpe_ids, false);
	 
		    $alt_pumpe_details_offline =  $alt_pumpe_details['offline'];
		    $alt_pumpe_details =  $alt_pumpe_details['online'];
		    foreach($medications_array['ispumpe']  as $smpkey => $medicationsmp)
		    {
		        if(!in_array($medicationsmp['pumpe_id'],$alt_pumpe_declined)){
		            $medications_array['ispumpe'][$smpkey]['smpdescription'] = $perfusor_pumpe_data[$medicationsmp['pumpe_id']];
		            
		            if(!empty($alt_pumpe_details[$medicationsmp['pumpe_id']]))
		            {
		                $medications_array['ispumpe'][$smpkey]['smpdescription_alt'] = $alt_pumpe_details[$medicationsmp['pumpe_id']];
		            }
		            else
		            {
		                $medications_array['ispumpe'][$smpkey]['smpdescription_alt'] = "";
		            }
		        }
		        
		        //offline changes
		        $medications_array['ispumpe'][$smpkey]['smpdescription_alt_offline'] = null;
		        if( ! empty($alt_pumpe_details_offline[$medicationsmp['pumpe_id']]))
		        {
		            $medications_array['ispumpe'][$smpkey]['smpdescription_alt_offline'] = $alt_pumpe_details_offline[$medicationsmp['pumpe_id']];
		        }
		    }
		    //--  
		    
		    
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
			//TODO-3624 Ancuta 23.11.2020
		    $drugplan_dosage_concentration = PatientDrugPlanDosage::get_patient_drugplan_dosage_concentration($ipid);
		    
		    
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
	                
	                
	                //ISPC-2797 Ancuta 17.02.2021
	                if($elsa_planned_medis && isset($drugplan_ids2planned_actions[$vm['id']]) && !empty($drugplan_ids2planned_actions[$vm['id']])){
	                    $medications_array[$medication_type ][$km]['planned'] = $drugplan_ids2planned_actions[$vm['id']];
	                }
	                //--
	                

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
    		                
    		                // TODO-3585 Ancuta 10.11.2020  pct 1 - changes so dosage values are listed with  comma not dot
    		                //$medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']];
    		                
    		                $formated_dosages = array();
    		                if( !empty($drugplan_dosage[$vm['id']]) ){
    		                    foreach($drugplan_dosage[$vm['id']] as $dtime =>$dvalue){
    		                        $formated_dosages [$vm['id']][$dtime ] = str_replace(".",",",$dvalue);
    		                    }
    		                }
    		                $medications_array[$medication_type ][$km]['dosage'] = $formated_dosages[$vm['id']];
    		                //--
    		                
    		                
    		                
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
    		        
   		            //TODO-3829 Ancuta 24.02.2021
    		        if($medication_type == "isschmerzpumpe" && strlen($medication_extra[$vm['id']]['unit']) == 0 ) {
    		                $medication_extra[$vm['id']]['unit'] = "i.E.";
    		        }
   		            // --- 
		            
   	                $medications_array[$medication_type ][$km]['drug'] =  $medication_extra[$vm['id']]['drug']; 
   	                $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit']; 
   	                $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type']; 
   	                $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name']; 
   	                $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color']; 
   	                $medications_array[$medication_type ][$km]['importance'] =  trim($medication_extra[$vm['id']]['importance']); 
   	                $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form']; 
   	                $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id'];
   	                //ISPC-2676 Ancuta 25.09.2020
   	                //$medication_extra[$vm['id']]['concentration'] = str_replace(',','.',$medication_extra[$vm['id']]['concentration']);//Commented by ancuta  ISPC-2684 16.10.2020
   	                //
     	           $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
     	           //TODO-3585 Ancuta 10.11.2020
     	           //$medications_array[$medication_type ][$km]['concentration_full'] =  $medication_extra[$vm['id']]['concentration'];
     	           $medications_array[$medication_type ][$km]['concentration_full'] =  str_replace('.', ",", $medication_extra[$vm['id']]['concentration']);
     	           //--
   	                
     	           	// ISPC-2176, p6
   	                $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging']; 
   	                $medications_array[$medication_type ][$km]['packaging_name'] =  trim($medication_extra[$vm['id']]['packaging_name']); 
   	                $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal']; 
   	                $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];
     	           
   	                // ISPC-2247  
   	                $medications_array[$medication_type ][$km]['escalation'] =  $medication_extra[$vm['id']]['escalation']; 
                    // -- 
                    
   	                //ISPC-2833 Ancuta 02.03.2021
   	                $medications_array[$medication_type ][$km]['overall_dosage_h'] =  $medication_extra[$vm['id']]['overall_dosage_h'];
   	                $medications_array[$medication_type ][$km]['overall_dosage_24h'] =  $medication_extra[$vm['id']]['overall_dosage_24h'];
   	                $medications_array[$medication_type ][$km]['overall_dosage_pump'] =  $medication_extra[$vm['id']]['overall_dosage_pump'];
   	                $medications_array[$medication_type ][$km]['drug_volume'] =  $medication_extra[$vm['id']]['drug_volume'];
   	                $medications_array[$medication_type ][$km]['unit2ml'] =  $medication_extra[$vm['id']]['unit2ml'];
   	                
   	                $medications_array[$medication_type ][$km]['concentration_per_drug'] =  $medication_extra[$vm['id']]['concentration_per_drug'];
   	                $medications_array[$medication_type ][$km]['bolus_per_med'] =  $medication_extra[$vm['id']]['bolus_per_med'];
   	                //--
   	                
   	                
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
//    	                    $medications_array[$medication_type ][$km]['dosage_24h'] = round($dosage_value * 24, 2) ;
						//TODO-3624 Ancuta 23.11.2020
                        if(isset($medication_extra[$vm['id']]['dosage_24h_manual']) && !empty($medication_extra[$vm['id']]['dosage_24h_manual']) ){
                            $medications_array[$medication_type ][$km]['dosage_24h'] = str_replace(".",",",$medication_extra[$vm['id']]['dosage_24h_manual']);
                        } else{
       	                    $medications_array[$medication_type ][$km]['dosage_24h'] = $dosage_value * 24 ;
                        }
   	                    
   	                    //TODO-3585  Ancuta 10.11.202
//    	                    $medications_array[$medication_type ][$km]['dosage'] = round($dosage_value, 2);
   	                    //$medications_array[$medication_type ][$km]['dosage'] = $dosage_value;
   	                    //$medications_array[$medication_type ][$km]['dosage'] = number_format($dosage_value,3,",","."); // Ancuta - Pumpe-dosage 10.12.2020
   	                    $medications_array[$medication_type ][$km]['dosage'] = round($dosage_value, 3); // Ancuta - Pumpe-dosage 10.12.2020
   	                    $medications_array[$medication_type ][$km]['dosage'] = str_replace(".",",",$medications_array[$medication_type ][$km]['dosage']);
   	                    // --
//  	                    $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage'])  ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']) : str_replace(".",",",$dosage_value);           //ISPC-2684 Lore 08.10.2020
//  	                    $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;   //ISPC-2684 Lore 08.10.2020
//   	                    $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']);   //ISPC-2684 Lore 08.10.2020
   	                    
   	                    //TODO-3829 Lore 17.02.2021
   	                    $modules = new Modules();
   	                    if($modules->checkModulePrivileges("240", $clientid)){
   	                        if( isset($medication_extra[$vm['id']]['unit_dosage']) && strlen($medication_extra[$vm['id']]['unit_dosage'])>0){
   	          	                    $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage'])  ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']) : str_replace(".",",",$dosage_value);           //ISPC-2684 Lore 08.10.2020
   	                                $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;   //ISPC-2684 Lore 08.10.2020
   	                            
   	                        } else {
   	                            if($medications_array[$medication_type ][$km]['unit'] == 'ml'){
   	                                $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && strlen($medication_extra[$vm['id']]['unit_dosage'])>0   ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']) : '';
   	                                $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && strlen($medication_extra[$vm['id']]['unit_dosage_24h'])>0  ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : '' ;
   	                            } else {
   	                                $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && strlen($medication_extra[$vm['id']]['unit_dosage'])>0  ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']) : str_replace(".",",",$dosage_value);
   	                                $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && strlen($medication_extra[$vm['id']]['unit_dosage_24h'])>0 ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;
   	                                $medications_array[$medication_type ][$km]['dosage']     =  '';
   	                                $medications_array[$medication_type ][$km]['dosage_24h'] =  '';
   	                            }
   	                        }
   	                    }
   	                    //.
   	                    
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
//        	                        $result_24h = round($result_24h, 4);
       	                        $result_24h = $result_24h;//TODO-3624 Ancuta 23.11.2020
       	                        $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  number_format($result_24h,3,",",".")." ".$medication_extra[$vm['id']]['dosage_form'];
       	                    }
       	                    else
       	                    {
       	                        $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  $result_24h." ".$medication_extra[$vm['id']]['dosage_form'];
       	                    }
       	                    
       	                    //TODO-3585
       	                    ///$medications_array[$medication_type ][$km]['dosage_24h_concentration'] = str_replace('.', ",", $medications_array[$medication_type ][$km]['dosage_24h_concentration']);
       	                }
       	                
       	                //TODO-3585
       	                $medications_array[$medication_type ][$km]['dosage_24h'] =  str_replace('.', ",", $medications_array[$medication_type ][$km]['dosage_24h']);
       	                
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
		    
		    //ISPC-2833 Ancuta 26.02.2021
		    if(!empty($medications_array['ispumpe'])){
		        
		        foreach($medications_array['ispumpe'] as $drug_id_ke =>$med_details)
		        {
		            $alt_medications_array["ispumpe"][$med_details['pumpe_id']][] =  $med_details;
		        }
		        
		        unset($medications_array['ispumpe']);
		        $medications_array['ispumpe'] = $alt_medications_array["ispumpe"];
		    }
		    //--
		    
		    $allow_new_fields = array("actual","isbedarfs","iscrisis","isivmed","isnutrition");

/* 		    echo "<pre/>";
		    print_r($medications_array); exit; */
		    //ISPC-2636 Lore 29.07.2020
		    $cust = Doctrine_Query::create()
		    ->select("client_medi_sort, user_overwrite_medi_sort_option")
				->from('Client')
				->where('id = ?',  $clientid);
				$cust->getSqlQuery();
				$disarray = $cust->fetchArray();
				
				
		    $client_medi_sort = $disarray[0]['client_medi_sort'];
		    $user_overwrite_medi_sort_option = $disarray[0]['user_overwrite_medi_sort_option'];
		    
		    $uss = Doctrine_Query::create()
		    ->select('*')
		    ->from('UserTableSorting')
		    ->Where('client = ?', $clientid)
		    ->orderBy('change_date DESC')
		    ->limit(1);
		    $uss_arr = $uss->fetchArray();
		    $last_sort_order = unserialize($uss_arr[0]['value']);
		    //dd($last_sort_order[0][1]);
		    //.
		    
		    /* ================ MEDICATION :: USER SORTING ======================= */
		    $usort = new UserTableSorting();
// 		    $saved_data = $usort->user_saved_sorting($userid,false, false, false ,$ipid);
		    $saved_data = $usort->user_saved_sorting($userid, false, $ipid);
		     
		    
		    
		    foreach($saved_data as $k=>$sord){
		        if($sord['name'] == "order"){

    		        $med_type_sarr = explode("-",$sord['page']);
    		        $page = $med_type_sarr[0];
    		        $med_type = $med_type_sarr[1];
    		        if($page == "patientmedication" && $med_type){
        		        $order_value = unserialize($sord['value']);
                        $saved_order[$med_type]['col'] = $order_value[0][0] ;
        		        $saved_order[$med_type]['ord'] = $order_value[0][1];
    		              
                    }
		        }
		    }
  
		    //TODO-3450 Ancuta 22.09.2020 - added sorting in request - so we can use BOTH clent sorting - and the sorting in page, as  the page is refreshed when sorting is applied
		    if(!empty($client_medi_sort)){
    		    
    		    $request_sort = array();
    		    if(!empty($_REQUEST['sort_b']) && !empty($_REQUEST['sort_c']) && !empty($_REQUEST['sort_d'])){
    		        $request_sort[$_REQUEST['sort_b']]['col'] = $_REQUEST['sort_c'];
    		        $request_sort[$_REQUEST['sort_b']]['ord'] = $_REQUEST['sort_d'];
    		    }
    		    
    		    foreach($medication_blocks as $k=>$mt){
    		        if(!empty($request_sort[$mt])){
    		            $saved_order[$mt]['col'] = $request_sort[$mt]['col'];
    		            $saved_order[$mt]['ord'] = $request_sort[$mt]['ord'];
    		        }
    		        elseif(!empty($client_medi_sort)){
    		            $saved_order[$mt]['col'] = !empty($client_medi_sort) ? $client_medi_sort : "medication";              //ISPC-2636 Lore 29.07.2020
    		            $saved_order[$mt]['ord'] = "asc";
    		        }
    		        elseif(empty($saved_order[$mt])){
    		            $saved_order[$mt]['col'] = "medication";
    		            $saved_order[$mt]['ord'] = "asc";
    		        }
    		    }
		        
		    } else{
    		    foreach($medication_blocks as $k=>$mt){
    		        if(empty($saved_order[$mt])){
    		            $saved_order[$mt]['col'] = "medication";
    		            $saved_order[$mt]['ord'] = "asc";
    		        }
    		    }
		    }
		    //---

		    
		    //ISPC-2636 Lore 29.07.2020
		    if($user_overwrite_medi_sort_option != '0'){
		        $uomso = Doctrine_Query::create()
		        ->select('*')
		        ->from('UserSettingsMediSort')
		        ->Where('clientid = ?', $clientid)
		        ->orderBy('create_date DESC')
		        ->limit(1);
		        $uomso_arr = $uomso->fetchArray();
		        //dd($uomso_arr);
		        if(!empty($uomso_arr)){
		            $overwrite_saved_order = array();
		            foreach($saved_order as $block => $vals){
		                $overwrite_saved_order[$block]['col'] = !empty($uomso_arr[0]['sort_column'] ) ? $uomso_arr[0]['sort_column'] : 'medication';//Ancuta 17.09.2020-- Issue if empty
		                $overwrite_saved_order[$block]['ord'] = !empty($last_sort_order[0][1]) ? $last_sort_order[0][1] : "asc";
		            }
		            $saved_order = $overwrite_saved_order;
		        }
		    }
		    //.
		    
		    //dd($saved_order);
		    $this->view->sort_order = $saved_order;
		    
		    // ############ APPLY SORTING ##############
		    foreach($medications_array as $type=>$m_values){
		        if($type !="isschmerzpumpe" && $type !="ispumpe"){
		            if($saved_order[$type]['ord'] == "asc"){
		                $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_ASC);
		            } else{
		                $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_DESC);
		            }
		        } else{
		            foreach($medications_array[$type] as $sch_id=>$sh_m_values){
    		            if($saved_order[$type]['ord'] == "asc"){
    		                $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_ASC);
    		            } else{
    		                $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_DESC);
    		            }
		            }
		            
		        }
		    }
		    if(!empty($medications_array_sorted)){
		        $medications_array = array();
		        $medications_array = $medications_array_sorted;
		    }
		    
		    $this->view->saved_order= $saved_order;
		    $this->view->js_saved_order= json_encode($saved_order);
		    
 
		    if($_REQUEST['final'] == "1")
		    {
		      print_R($medications_array); exit;
		    }

		    $this->view->medication = $medications_array;
		}
		
		
		public function editAction()
		{
		    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
			$logininfo = $this->logininfo;
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = ($this->dec_id !== false) ? $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
		    $ipid = ($this->ipid !== false) ? $this->ipid : Pms_CommonData::getIpId($decid);
		    
		    
		    //    TODO-2612 ISPC: Changing Medicationtime Ancuta - 30.10.2019
		    $existing_dosage_array  = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
		    //-- 
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
		    
		    
		    //ISPC-2797 Ancuta 17.02.2021
		    $elsa_planned_medis = $modules->checkModulePrivileges("250", $clientid);
		    $this->view->elsa_planned_medis = 0;
		    
		    if($elsa_planned_medis){
		        $this->view->elsa_planned_medis = 1;
		    }
		    //--
		    
		    if($modules->checkModulePrivileges("129", $clientid))//Medication acknowledge ISPC - 1483
		    {
		        $this->view->force_full_width = "1";
		    } else{
		        $this->view->force_full_width = "0";
		    }
		    
		    //ISPC-2507 Ancuta 05.02.2020
		    /* MMI functionality*/
		    if($modules->checkModulePrivileges("87", $clientid))
		    {
		        $this->view->modal_show_mmi = "1";
		    }
		    else
		    {
		        $this->view->modal_show_mmi = "0";
		    }
		    // -- 

		    // Activate Button for AMTS-Check ISPC-2576 :: ISPC-2589 Ancuta 28.05.2020 [migration from clinic CISPC]
		    if($modules->checkModulePrivileges("10000", $clientid))
		    {
		        $this->view->show_amts_button = "1";
		    } else{
		        $this->view->show_amts_button = "0";
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
		        
		        //ISPC-2833 Ancuta 01.03.2021
		        if($medication_data['ispumpe'] == "1")
		        {
		            $medications_array['ispumpe'][] = $medication_data;
		            $pumpe_ids[] = $medication_data['pumpe_id'];
		        }
		        //
		    
		    }
		    
		    // get schmerzpumpe details
		    $cocktail_ids = array_unique($cocktail_ids);
		    
		    if(count($cocktail_ids) == 0)
		    {
		        $cocktail_ids[] = '999999';
		    }
		    
		    $cocktailsC = new PatientDrugPlanCocktails();
		    $cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
		    
		    
		    //ISPC-2833 Ancuta 01.03.2021
		    // get schmerzpumpe details
		    $pumpe_ids = array_unique($pumpe_ids);
		    
		    if(count($pumpe_ids) == 0)
		    {
		        $pumpe_ids[] = '999999';
		    }
		    
		    $pumpe_obj = new PatientDrugplanPumpe();
		    $pumpes_array = $pumpe_obj->get_perfusor_pumpes($pumpe_ids);
		    //--
		    
		    
		    //ISPC-2507 Ancuta 08.02.2020
		    $allow_medication_comunication_i = "0"; 
		    if($modules->checkModulePrivileges("214", $clientid))
		    {
                $allow_medication_comunication_i = "1";
		    }

		    
		    //ISPC-2507 Ancuta 19.02.2020
		    $allow_medication_comunication_ii = "0"; 
		    if($modules->checkModulePrivileges("216", $clientid))
		    {
	            $allow_medication_comunication_ii = "1";
	            
	            
	            /*  textarea & comment-ul */
	            $form_name = "patientmedication/requestchanges";
	            $formstextslist_model = new FormsTextsList();
	            $standard_texts_arr =  $formstextslist_model->get_client_list($clientid,$form_name);
	            
	            $request_reasons = array();
	            foreach($standard_texts_arr as $k=>$st){
	                $request_reasons[$st['id']] = trim($st['field_value']);
	            }
		    }
		    
		    if($this->getRequest()->isPost())
		    {

				//ISPC-2829 Ancuta  18.03.2021
		        if(!empty($_REQUEST['efaoption']) && $_REQUEST['efaoption'] == '1'){
		            $acknowledge = "1";
		            $change_users[] =$userid;
		        }
				//--
		        
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
		       
		        $medication_unit = MedicationUnit::client_medication_unit($clientid);
		        
		        $client_medication_extra = array();
		        foreach($medication_unit as $k=>$unit){
		            $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
		        }
		        
		        $a_post = $_POST;
		        
		        $a_post['ipid'] = $ipid;
		        
		        
		        if($allow_medication_comunication_ii == "1")
		        {
		            
		            $request_on_page = 0 ;
		            foreach($_POST['medication_block'] as $type => $med_values)
		            {
						// Changes 11.03.2020
		                if(!empty($med_values['has_request_change'])){
		                    foreach($med_values['has_request_change'] as $change){
		                      $request_on_page++;
		                    }
		                }
		            }
		            
   		            $post_request_id = $_POST['pending_request_id'];
		            if(!empty($a_post['custom_request']) || $request_on_page > 0){
		  
    		            if(  empty ($post_request_id) || $post_request_id == 0 ){
    		                //insert new reqest ONLY IF not other request is pending
    		                
    		                // verifi again if other request
    		                $pending_requests  = array();
    		                $pending_requests = PharmaPatientDrugplanRequestsTable::find_patient_user_requests($ipid,$this->userid,'pending');
    		                
    		                if(empty($pending_requests) && $_POST['post_user_type'] == "is_pharma_user"  ){
    		                    $cust_pdd = new PharmaPatientDrugplanRequests();
    		                    $cust_pdd->ipid   = $ipid;
    		                    $cust_pdd->user   = $userid;
    		                    $cust_pdd->status = "pending";
    		                    $cust_pdd->save();
    		                    
    		                    $post_request_id = $cust_pdd->id;
    		                } else{
    		                    $post_request_id = $pending_requests[0]['id'];
    		                }
    		            }
		            }
		 
		            // Proceed ONLY if we have a request id 
		            if(!empty($post_request_id)){
		                
    		            // save documented time
    		            if( !empty($_POST['request_duration']) ){
    		                
    		                $ppdr = Doctrine::getTable('PharmaPatientDrugplanRequests')->find($post_request_id);
    		                if($ppdr){
    		                    $ppdr->duration = $_POST['request_duration'];
    		                    $ppdr->save();
    		                    
    		                     $shortcut = "DG";
    		                     $custom_request_text = "";
    		                     $custom_request_text .= "DemStepCare - Zeitaufwand Apotheke: ";
    		                     $custom_request_text .= $_POST['request_duration'];
    		                     
    		                     $insert_pc = new PatientCourse();
    		                     $insert_pc->ipid = $ipid;
    		                     $insert_pc->course_date = date("Y-m-d H:i:s", time());
    		                     $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    		                     $insert_pc->tabname = Pms_CommonData::aesEncrypt("pharma_custom_request");
    		                     $insert_pc->recordid = $post_request_id;
    		                     $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($custom_request_text));
    		                     $insert_pc->user_id = $userid;
    		                     $insert_pc->save();  
    		                    
    		                }
    		            }
    		            
    		            
    		            //DELETE CUSTOM REQUESTS IF NEEDED
    		            if($_POST['post_user_type'] == "is_pharma_user" && !empty($a_post['delete_custom_requests']) ){
    		                $custom_ids4delete = array();
    		                $custom_ids4delete = explode(',',$a_post['delete_custom_requests']);
    		                
    		                foreach($custom_ids4delete as $custom_req_id){
    		                
    		                    if(!empty($custom_req_id) && $custom_req_id != '0' ){
    		                        $cust = Doctrine::getTable('PharmaPatientRequests')->find($custom_req_id);
        		                    if($cust){
       		                            $cust->isdelete = "1";
        		                        $cust->save();
        		                    }
    		                    }
    		                }
    		            }
    		            
    		            // ADD / UPDATE or Process Custom requets
    		            if(!empty($post_request_id) && !empty($a_post['custom_request']) ){
    		                // add edit
    		                $custom_request = array();
    		                $custom_request['custom_request'] = array();
    		                $lo =  0 ; 
    		                foreach($a_post['custom_request'] as $k => $custom_line){
    		                    $custom_request['custom_request'][$lo] = $custom_line;
    		                    $custom_request['custom_request'][$lo]['request_id'] = $post_request_id;
    		                    $custom_request['custom_request'][$lo]['ipid'] = $ipid;
    		                    $lo++;
    		                }
    		                
    		                if( $_POST['post_user_type'] == "is_pharma_user" ){
        		                $med_form->update_multiple_data_pharma_custom_request($custom_request,$ipid,$request_reasons,false);
    		                } 
    		                elseif($_POST['post_user_type'] == "is_assigned_doctor_user" )
    		                {
                                $med_form->update_multiple_data_pharma_custom_request($custom_request,$ipid,$request_reasons,true);
                            }
    		            }
    		        }
		        }
                
                
                
                
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
                                $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$sch_med_values['drid'][$k_meds]);
                                
                                if($modules->checkModulePrivileges('240',$clientid)){
                                    if($cust_old) {
                                        if($sch_med_values['edited'][$k_meds] == 1 && (
                                                round( str_replace(',','.',$cust_old->dosage) ,3)  != str_replace(',','.',$sch_med_values['dosage'][$k_meds])  
                                                || $cust_old->medication_master_id != $sch_med_values['hidd_medication'][$k_meds] 
                                                || $cust_old->verordnetvon != $sch_med_values['verordnetvon'][$k_meds]
                                                || str_replace(',','.',$medication_extra[$sch_med_values['drid'][$k_meds]]['unit_dosage'] )  !=  str_replace(',','.',$sch_med_values['unit_dosage'][$k_meds] )
                                                )
                                            ) {
                                            $sch_med_values['status'][$k_meds] = "edited";
                                        } else {
                                            $sch_med_values['status'][$k_meds] = "not_edited";
                                        }
                                    } else {
                                        $sch_med_values['status'][$k_meds] = "new";
                                    }
                                } else{
                                    
                                    if($cust_old) {
                                        if( round( str_replace(',','.',$cust_old->dosage) ,3)  != str_replace(',','.',$sch_med_values['dosage'][$k_meds])  
                                            || $cust_old->medication_master_id != $sch_med_values['hidd_medication'][$k_meds] 
                                            || $cust_old->verordnetvon != $sch_med_values['verordnetvon'][$k_meds]) {
                                            $sch_med_values['status'][$k_meds] = "edited";
                                        } else {
                                            $sch_med_values['status'][$k_meds] = "not_edited";
                                        }
                                    } else {
                                        $sch_med_values['status'][$k_meds] = "new";
                                    }
                                }
                                
                                
                                if(strlen($sch_med_values['cocktail']['id']) > 0 && !empty($cocktails[$sch_med_values['cocktail']['id']])){
                                    $cocktail_details = $cocktails[$sch_med_values['cocktail']['id']];
                                    
                                    //|| $sch_med_values['cocktail']['flussrate_type'] != $cocktail_details['flussrate_type']      //ISPC-2684 Lore 08.10.2020 - //TODO-3676 Ancuta 06.01.2021 - removed this line from check  
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
                                $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$sch_med_values['drid'][$k_meds]);

                                //TODO-3829 Ancuta 23.02.2021
                                if($modules->checkModulePrivileges('240',$clientid)){
                                    if($cust)
                                    {
                                        //TODO-3676 Ancuta 06.01.2021  -removed round  round($cust->dosage, 2) != $sch_med_values['dosage'][$k_meds] ||
                                        if( 
                                            $sch_med_values['edited'][$k_meds] == 1 && (
                                            round( str_replace(',','.',$cust->dosage) ,3)  != str_replace(',','.',$sch_med_values['dosage'][$k_meds]) ||
                                            $cust->medication_master_id != $sch_med_values['hidd_medication'][$k_meds] ||
                                            $cust->verordnetvon != $sch_med_values['verordnetvon'][$k_meds]
                                            || str_replace(',','.',$medication_extra[$sch_med_values['drid'][$k_meds]]['unit_dosage'] )  !=  str_replace(',','.',$sch_med_values['unit_dosage'][$k_meds] )
                                            || $sch_med_values['status'][$k_meds] == "edited"
                                            || $sch_med_values['status'][$k_meds] == "new"
                                            || $post_cocktails[$pumpe_number]  == "edited"
                                                )
                                            
                                            )
                                        {
                                            $list[$pumpe_number]++;
                                        }
                                         
                                    } else {
                                        if($sch_med_values['status'][$k_meds] == "new"){
                                            $list[$pumpe_number]++;
                                        }
                                    }
                                    
                                    
                                } else{
                                    if($cust)
                                    {
                                        //TODO-3676 Ancuta 06.01.2021  -removed round  round($cust->dosage, 2) != $sch_med_values['dosage'][$k_meds] ||
                                        if( round( str_replace(',','.',$cust->dosage) ,3)  != str_replace(',','.',$sch_med_values['dosage'][$k_meds]) ||
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
                                }
                                
                                //ISPC-2329 pct.v)  Lore
                                if($sch_post_data['unit'][$k_meds] == 0){
                                    $unit_post = "i.E.";
                                } else{
                                    $unit_post = $client_medication_extra['unit'][$sch_post_data['unit'][$k_meds]];
                                }
                                
//                                 dd($sch_med_values);
                                if(!array_key_exists($k_meds, $sch_post_data['newmedication']) && $list[$pumpe_number] > 0) //new medis
                                {
                                    //TODO-3829 Ancuta 08.02.2021 - added "ml" to dosage 
                                    //$meds[] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds].$unit_post."/h | " .$sch_med_values['dosage_24h'][$k_meds].$unit_post."/24h | " . "\n";
                                    //$meds[] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds]."ml/h | " .$sch_med_values['dosage_24h'][$k_meds]."ml/24h | " . "\n";
                                    //-- 
                                    //TODO-3829 Lore 16.02.2021
                                    if($modules->checkModulePrivileges("240", $clientid)){
                                        if($unit_post == "ml"){
                                            $meds[] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds].$unit_post."/h | " .$sch_med_values['dosage_24h'][$k_meds].$unit_post."/24h | " . "\n";
                                        }else{
                                            $meds[] = $v_meds . " | " . $sch_med_values['unit_dosage'][$k_meds].$unit_post."/h | " .$sch_med_values['unit_dosage_24h'][$k_meds].$unit_post."/24h | " . "\n";
                                        }
                                    } else {
                                        $meds[] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds].$unit_post."/h | " .$sch_med_values['dosage_24h'][$k_meds].$unit_post."/24h | " . "\n";
//                                         $meds[] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds]."ml/h | " .$sch_med_values['dosage_24h'][$k_meds]."ml/24h | " . "\n";
                                    }
                                }
                            }
                            
                            if($list[$pumpe_number] > 0)
                            {   
                                $course_cocktail_entry ="";
                                $course_cocktail_entry .= "Kommentar: " . $sch_med_values['cocktail']['description'];
                                $course_cocktail_entry .= "\n".$this->view->translate('Applikationsweg').": " . $sch_med_values['cocktail']['pumpe_medication_type'];
                                //$course_cocktail_entry .= "\n".$this->view->translate('Flussrate').": " . $sch_med_values['cocktail']['flussrate'];
                                //ISPC-2684 Lore 12.10.2020
                                if($modules->checkModulePrivileges("240", $clientid)){
                                    $course_cocktail_entry .= "\n".$this->view->translate('Flussrate_simple')." (".$sch_med_values['cocktail']['flussrate_type']."): " . $sch_med_values['cocktail']['flussrate'];
                                }else {
                                    $course_cocktail_entry .= "\n".$this->view->translate('Flussrate').": " . $sch_med_values['cocktail']['flussrate'];
                                }
                                
                                $course_cocktail_entry .= "\n".$this->view->translate('medication_carrier_solution').": " . $sch_med_values['cocktail']['carrier_solution'];
                                
                                //if($sch_med_values['cocktail']['pumpe_type'] == "pca") {
                                    $course_cocktail_entry .= "\n".$this->view->translate('Bolus').": " . $sch_med_values['cocktail']['bolus'];
                                    $course_cocktail_entry .= "\n".$this->view->translate('Max Bolus').": " . $sch_med_values['cocktail']['max_bolus'];
                                    $course_cocktail_entry .= "\n".$this->view->translate('Sperrzeit').": " . $sch_med_values['cocktail']['sperrzeit'] ;
                                //}
                                
                                
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
                    //ISPC-2833 Ancuta 01.03.2021 Start:
                    elseif($type == "ispumpe")
                    {
                        foreach($med_values as $pumpe_number=>$sch_med_values)
                        {
                            // get initial data 
                            //find out edited/added medis
                            foreach($sch_med_values['medication'] as $k_meds => $v_meds)
                            {
                                
                                $cust_old = Doctrine::getTable('PatientDrugPlan')->find($sch_med_values['drid'][$k_meds]);
                                $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$sch_med_values['drid'][$k_meds]);
                                if($cust_old) {
                                    if( round( str_replace(',','.',$cust_old->dosage) ,3)  != str_replace(',','.',$sch_med_values['dosage'][$k_meds])  
                                        || $cust_old->medication_master_id != $sch_med_values['hidd_medication'][$k_meds] 
                                        ) {
                                        $sch_med_values['status'][$k_meds] = "edited";
                                    } else {
                                        $sch_med_values['status'][$k_meds] = "not_edited";
                                    }
                                } else {
                                    $sch_med_values['status'][$k_meds] = "new";
                                }
                                
                                
                                if(strlen($sch_med_values['pumpe_id']['id']) > 0 && !empty($pumpes_array[$sch_med_values['pumpe_id']['id']])){
                                    $pumpe_details = $pumpes_array[$sch_med_values['pumpe_id']['id']];
                                    
                                    if( $sch_med_values['ispumpe_pumpe']['overall_volume'] !=$pumpe_details['overall_volume'] ||
                                        $sch_med_values['ispumpe_pumpe']['run_rate'] !=$pumpe_details['run_rate'] ||
                                        $sch_med_values['ispumpe_pumpe']['used_liquid'] !=$pumpe_details['used_liquid'] ||
                                        $sch_med_values['ispumpe_pumpe']['pat_weight'] !=$pumpe_details['pat_weight'] ||
                                        $sch_med_values['ispumpe_pumpe']['overall_drug_volume'] !=$pumpe_details['overall_drug_volume'] ||
                                        $sch_med_values['ispumpe_pumpe']['liquid_amount'] !=$pumpe_details['liquid_amount'] ||
                                        $sch_med_values['ispumpe_pumpe']['overall_running_time'] !=$pumpe_details['overall_running_time'] ||
                                        $sch_med_values['ispumpe_pumpe']['min_running_time'] !=$pumpe_details['min_running_time'] ||
                                        $sch_med_values['ispumpe_pumpe']['bolus'] !=$pumpe_details['bolus'] ||
                                        $sch_med_values['ispumpe_pumpe']['max_bolus_day'] !=$pumpe_details['max_bolus_day'] ||
                                        $sch_med_values['ispumpe_pumpe']['max_bolus_after'] !=$pumpe_details['max_bolus_after'] ||
                                        $sch_med_values['ispumpe_pumpe']['next_bolus'] !=$pumpe_details['next_bolus'] 
                                       )
                                    {
                                        $post_pumpes[$pumpe_number] = "edited";
                                    } else {
                                        $post_pumpes[$pumpe_number] = "not_edited";
                                    }
                                } else{
                                    $post_pumpes[$pumpe_number] = "new";
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
                            
                            $med_form->update_ispumpe_data($sch_post_data);
                        
                
                            //find out edited/added medis
                            $pp_list[$pumpe_number] = 0;
                            foreach($sch_med_values['medication'] as $k_meds => $v_meds)
                            {
                                $cust = Doctrine::getTable('PatientDrugPlan')->find($sch_med_values['drid'][$k_meds]);
                                $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$sch_med_values['drid'][$k_meds]);

                                
                                if($cust)
                                {
                                    if( round( str_replace(',','.',$cust->dosage) ,3)  != str_replace(',','.',$sch_med_values['dosage'][$k_meds]) ||
                                        $cust->medication_master_id != $sch_med_values['hidd_medication'][$k_meds]
                                        || $sch_med_values['status'][$k_meds] == "edited"
                                        || $sch_med_values['status'][$k_meds] == "new"
                                        || $post_pumpes[$pumpe_number]  == "edited"
                                        )
                                    {
                                        $pp_list[$pumpe_number]++;
                                    }
                                     
                                } else {
                                    if($sch_med_values['status'][$k_meds] == "new"){
                                        $pp_list[$pumpe_number]++;
                                    }
                                }
                                
                                //ISPC-2329 pct.v)  Lore
                                if($sch_post_data['unit'][$k_meds] == 0){
                                    $unit_post = "i.E.";
                                } else{
                                    $unit_post = $client_medication_extra['unit'][$sch_post_data['unit'][$k_meds]];
                                }
                                
                                if(!array_key_exists($k_meds, $sch_post_data['newmedication']) && $pp_list[$pumpe_number] > 0) //new medis
                                {
                                    $meds[] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds].$unit_post."/h | " .$sch_med_values['dosage_24h'][$k_meds].$unit_post."/24h | " . "\n";
                                }
                            }
                            
                            if($pp_list[$pumpe_number] > 0)
                            {   
                                $course_cocktail_entry="";
                                $course_cocktail_entry = "Zielvolumen Pumpe (ml): " . $sch_med_values['ispumpe_pumpe']['overall_volume']."";
                                $course_cocktail_entry .= "\ngewünschte Laufrate (ml/h): " .$sch_med_values['ispumpe_pumpe']['run_rate'];
                                $course_cocktail_entry .= "\nTrägerlösung: " .$sch_med_values['ispumpe_pumpe']['used_liquid'];
                                $course_cocktail_entry .= "\nGewicht (kg): " .$sch_med_values['ispumpe_pumpe']['pat_weight'];
                                $course_cocktail_entry .= "\nLaufzeit (ohne Bolus) in h: " .$sch_med_values['ispumpe_pumpe']['overall_running_time'];
                                $course_cocktail_entry .= "\nLaufzeit min. (mit Bolus): " .$sch_med_values['ispumpe_pumpe']['min_running_time'];
                                $course_cocktail_entry .= "\nBolusmenge (in ml): " .$sch_med_values['ispumpe_pumpe']['bolus'];
                                $course_cocktail_entry .= "\nmax Bolus pro Tag: " .$sch_med_values['ispumpe_pumpe']['max_bolus_day'];
                                $course_cocktail_entry .= "\nmax Bolus hintereiander: " .$sch_med_values['ispumpe_pumpe']['max_bolus_after'];
                                $course_cocktail_entry .= "\nSperrzeit (in Min.): " .$sch_med_values['ispumpe_pumpe']['next_bolus'];
                                
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
                    //ISPC-2833 Ancuta 01.03.2021 End
                    else
                    {
                        $post_data = $med_values;
                        $post_data['existing_dosage_array'] = $existing_dosage_array; //    TODO-2612 ISPC: Changing Medicationtime Ancuta - 30.10.2019
                        //TODO-3972 Lore 24.03.2021
                        foreach($post_data['dosage'] as $key => $vals){
                            if(isset($existing_dosage_array[$post_data['drid'][$key]])){
                                if(is_array($vals)){
                                    $post_data['existing_dosage_array_new_dosage'][$post_data['drid'][$key]] = $vals;
                                } else {
                                    $new_array_existing_dosage_array = explode("-",$vals);
                                    $nr_key_vals = 0;
                                    foreach($existing_dosage_array[$post_data['drid'][$key]] as $time => $vals_e){
                                        $post_data['existing_dosage_array_new_dosage'][$post_data['drid'][$key]][$time] = $new_array_existing_dosage_array[$nr_key_vals];
                                        $nr_key_vals++;
                                    }
                                }
                            }
                        }
                        //.
                        
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
               				//ISPC-2829 Ancuta  18.03.2021
                             if(!empty($_REQUEST['efaoption']) && $_REQUEST['efaoption'] == '1'){
                                 $post_data['efaoption'] = 1;
                             }
							// --
                            
                            $med_form->update_multiple_data($post_data);
                            
                            
                            if($allow_medication_comunication_ii == "1")
                            {
                                $post_data['request_id'] = $post_request_id;
                                
                                if($_POST['post_user_type'] == "is_pharma_user"){
                                    $med_form->update_multiple_data_pharma_request($post_data,$ipid,$request_reasons,false);
                                } elseif($_POST['post_user_type'] == "is_assigned_doctor_user") {
                                    $med_form->update_multiple_data_pharma_request($post_data,$ipid,$request_reasons,true);
                                }
                            }
                        }
                        
                        
                    }
      
                } // END FOREACH
    

                // ISPC-2524 pct.3)  Lore 14.01.2020
                FormsEditmodeTable::finishedEditing([
                    'pathname' => $this->getRequest()->getControllerName() . "/" . $this->getRequest()->getActionName(),
                    'client_id' => $this->logininfo->clientid,
                    'patient_master_id' => $decid,
                    'user_id' => $this->logininfo->userid,
                    'search' => 'in_edit',
                    'is_edited' => 'yes',
                ]);	
                
                //ISPC-2507 Ancuta 08.02.2020
                if(($allow_medication_comunication_i == "1" || $allow_medication_comunication_ii == "1") && !empty($post_request_id))
                {
                    if($_POST['post_user_type'] == "is_pharma_user") {
                        // find if todos were sent for this request 
                        //  Send request - and create todos for assigned doctor
                        $med_form->send_pharma_drugplan_request_todos($clientid,$ipid,$post_request_id,$userid);
                   
                     } elseif($_POST['post_user_type'] == "is_assigned_doctor_user") {
    
                        // Send request - and create todos for assigned doctor
                        $med_form->send_pharma_drugplan_request_todos($clientid,$ipid,$post_request_id,$userid, true);
                    }
                    
                    
                    if($_POST['post_user_type'] == "is_assigned_doctor_user") {
                        // check if any requests are still pending - if none, then marck as proccess the curent request id
                        $not_processed_requests = array();
                        $not_processed_requests = PharmaPatientDrugplanRequestsTable::find_patient_NotProessed_requests($ipid,false,false,$post_request_id);

                        
                        if(empty($not_processed_requests)){
                            $ppdr = Doctrine::getTable('PharmaPatientDrugplanRequests')->find($post_request_id);
                            if($ppdr){
                                $ppdr->processed = "yes";
                                $ppdr->save();
                            }
                        }
                    }
                }
                
                //ISPC-2797 Ancuta 24.02.2021
                if($elsa_planned_medis){
                    $pm =  new PatientDrugplanPlanning();
                    $pm->proccess_planned_medications($clientid,$userid,false,$ipid);
                }
                //-- 
//                 exit;
				//ISPC-2829 Ancuta  18.03.2021
                if(!empty($_REQUEST['efaoption']) && $_REQUEST['efaoption'] == '1'){
                    	$this->_redirect(APP_BASE . 'patientmedication/efamedication?flg=suc&id=' . $_GET['id']);
                } 
                else
                {
                    //ISPC-1848 F p.4
                    if( !empty($_REQUEST['save_and_continue']) ){
                    	$this->_redirect(APP_BASE . 'patientmedication/edit?flg=suc&id=' . $_GET['id']);
                    } else {
    	                $this->_redirect(APP_BASE . 'patientmedication/overview?flg=suc&id=' . $_GET['id']);
                    }
                }
		    }
		}
		
		public function editblocksAction()
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
		    
		    if($_REQUEST['efaoption'] == '1'){
		        $this->view->efaoption = $_REQUEST['efaoption'];
		    }
		    
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
		    
		    // ISPC-2664 Carmen 28.09.2020
		    $latest_vital_signs_weight = FormBlockVitalSigns::get_patients_chart_last_values_byelement($ipid, false, 'weight');
		    
		    if(!empty($latest_vital_signs_weight[$ipid])){
		    	$patientweight = number_format($latest_vital_signs_weight[$ipid]['weight'], 3, ',', '.');
		    	$this->view->patientweight = $patientweight;
		    }
		    //--
		    
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

		    //ISPC-2829 Ancuta  05.04.2021
		    if(!empty($_REQUEST['efaoption']) && $_REQUEST['efaoption'] == '1'){
		        $acknowledge = "1";
		        $change_users[] =$userid;
		        $this->view->allow_changes = "1";
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
		    	
		    //ISPC-2524 pct.2)  Lore 15.01.2020
		    if($modules->checkModulePrivileges("211", $clientid)) //Move medication from Dauermedikation to Bedarfsmedikation
		    {
		        $this->view->move_medication = "1";
		    } else{
		        $this->view->move_medication = "0";
		    }
		    
		    
		    //ISPC-2507 Ancuta 08.02.2020
		    $allow_medication_comunication_i = "0";
		    if($modules->checkModulePrivileges("214", $clientid))
		    {
		        $allow_medication_comunication_i = "1";
		    }
		    
		    
		    //ISPC-2507 Ancuta 19.02.2020
		    $allow_medication_comunication_ii = "0";
		    if($modules->checkModulePrivileges("216", $clientid))
		    {
		        $allow_medication_comunication_ii = "1";
		    }
		    
		    if($_REQUEST['efaoption'] == '1' && !empty($_REQUEST['drugplan_id'])){
		        $allow_medication_comunication_i = "0";
		        $allow_medication_comunication_ii = "0";
		    }
		    
		
		    
		    if($allow_medication_comunication_i == "1" || $allow_medication_comunication_ii == "1")
		    {
		        // get users  details, pharma and assigned doctors ?? DO WE NEED IT HERE?
		        $usergroup = new Usergroup();
		        $user = new User();
		        $MasterGroups = array("4",'8');
		        $master_group_ids = $usergroup->getUserGroups($MasterGroups);
		        
		        $group_info= array();
		        foreach($master_group_ids as $key => $value)
		        {
		            $groups_id[$value['groupmaster']] = $value['id'];
		            $group_info[$value['id']]['master'] = $value['groupmaster'];
		        }
		        
		        $users_array = $user->getClientsUsers(array($clientid));
		        $users = array();
		        foreach($users_array as $user_val)
		        {
		            $user_details[$user_val['id']] = $user_val;
		            
		            if($group_info[$user_val['groupid']]['master'] == '4')
		            {
		                $users ['doctor'][] = $user_val ['id'];
		            } else if($group_info[$user_val['groupid']]['master'] == '8')
		            {
		                $users ['pharma'][] = $user_val ['id'];
		            }
		        }
		        
		        $qpa = new PatientQpaMapping();
		        $assigned_users = array();
		        $assigned_users = $qpa->get_assigned_userid(array('ipids'=>array($ipid) ));
		        
		        $assigned_doctors = array();
		        if(!empty($assigned_users['ipids'][$ipid])){
		            foreach($assigned_users['ipids'][$ipid] as $assigned_user){
		                if(in_array($assigned_user,$users ['doctor'])){
		                    $assigned_doctors[] =  $assigned_user;
		                }
		            }
		        }
		        
		        $is_pharma_user = "0";
		        $is_assigned_doctor_user = "0";
		        if(in_array($this->userid,$users ['pharma']) || $this->usertype  == 'SA'){
		            $is_pharma_user = "1";
		        } elseif(in_array($this->userid,$assigned_doctors) ){
		            $is_assigned_doctor_user = "1";
		        }
		        
		        $this->view->is_pharma_user = $is_pharma_user;
		        $this->view->is_assigned_doctor_user  = $is_assigned_doctor_user ;
		        
		        
		        
		        // get current request details 
		        
		        
		        //check if for patient there are any "pending" requests:
		        $pending_requests  = array();
		        //
		        // get last not proccessed request
		        $not_processed_requests = array();
		        
		        $not_processed_requests = PharmaPatientDrugplanRequestsTable::find_patient_NotProessed_requests($ipid);
		        
		        
		        if(!empty($not_processed_requests)){
		            $current_pharma_request = $not_processed_requests[0];
		        }

		        $custom_request_info = array();
                if (! empty($current_pharma_request['PharmaPatientRequests'])) {
                    foreach ($current_pharma_request['PharmaPatientRequests'] as $k => $preq) {
    
                        if ($preq['custom'] == "yes") {
                            $current_pharma_request['PharmaPatientRequestsCustom']['custom_' . $preq['id']] = $preq;
                            
                            $custom_request_info[ $preq['id']]  = $preq;
                            $custom_request_info[ $preq['id']]['request_data']  = $preq;
                            
                            $reason_array = array();
                            $reason_array = !empty($preq['request_reason']) ?  unserialize($preq['request_reason']): array();;
                            $reason_texts= array();
                            foreach($reason_array as $reason_key => $reason_data){
                                foreach($reason_data as $k=>$reason){
                                    $reason_texts[] = $reason;
                                }
                            }
                            
                            $custom_request_info[ $preq['id']]['request_data']['request_reason_array']  = $reason_array;
                            $custom_request_info[ $preq['id']]['request_data']['request_reason_arr']  = !empty($reason_array) ?  array_keys($reason_array): array();
                            $custom_request_info[ $preq['id']]['request_data']['request_reason_text']  = !empty($reason_texts) ?  implode(', ',$reason_texts): array();
                            
                        } else {
                            $current_pharma_request['PharmaPatientRequests'][$preq['drugplan_id']] = $preq;
                        }
                    }
                }
                
//                 dd($custom_request_info);
                $this->view->custom_request_data = $custom_request_info;
		        
		        $this->view->current_request_id  = $current_pharma_request['id'];
		        $this->view->current_request_duration  = $current_pharma_request['duration'];
		        
		        $drugplan_request_info = array();
		        if(!empty($current_pharma_request['PharmaPatientDrugplan'])){
		            foreach($current_pharma_request['PharmaPatientDrugplan'] as $k=>$dr){
		                if(!empty($current_pharma_request['PharmaPatientRequests'][$dr['drugplan_id']]) && $current_pharma_request['PharmaPatientRequests'][$dr['drugplan_id']]['processed'] == 'no'  ){
                            $drugplan_request_info[$dr['drugplan_id']]  = $dr;
                            $drugplan_request_info[$dr['drugplan_id']]['request_data']  = $current_pharma_request['PharmaPatientRequests'][$dr['drugplan_id']];
                            
                            $reason_array = array();
                            $reason_array = !empty($current_pharma_request['PharmaPatientRequests'][$dr['drugplan_id']]['request_reason']) ?  unserialize($current_pharma_request['PharmaPatientRequests'][$dr['drugplan_id']]['request_reason']): array();;
                            $reason_texts= array();
                            foreach($reason_array as $reason_key => $reason_data){
                                foreach($reason_data as $k=>$reason){
                                    $reason_texts[] = $reason;
                                }
                            }
                            
                            $drugplan_request_info[$dr['drugplan_id']]['request_data']['request_reason_array']  = $reason_array;
                            $drugplan_request_info[$dr['drugplan_id']]['request_data']['request_reason_arr']  = !empty($reason_array) ?  array_keys($reason_array): array();
                            $drugplan_request_info[$dr['drugplan_id']]['request_data']['request_reason_text']  = !empty($reason_texts) ?  implode(', ',$reason_texts): array();
		                }
		            }
		        }
		        
		        $this->view->request_data = $drugplan_request_info;
		    }
		    
		    
		    
		    
		    
		   /*  
		    // send request
		    // Button visible ONLY for pharmacy group
		    $usergroup = new Usergroup();
		    $pharmacy_groups = $usergroup->getMastergroupGroups($clientid, array('8'));
		    
		    $allow_pharma_request = 0 ;
		    if(in_array($this->groupid,$pharmacy_groups) || $this->usertype  == 'SA'){
		        $allow_pharma_request = "1";
		    }
		    
		    if($allow_medication_comunication_i == "1" || $allow_medication_comunication_ii == "1"){
		        // get assigned doctors
		        // and doctor with requestes
		        
		        //check if for patient there are any "pending" requests:
		        $pending_requests  = array();
		        //
		        // get last not proccessed request
		        $not_processed_requests = array();
		        $not_processed_requests = PharmaRequestsReceivedTable::find_patient_last_NotProessed_request($ipid);
		        
		        
		        $current_request_id = 0 ;
		        $current_pharma_request = array();
		        if(!empty($not_processed_requests)){
		            $current_pharma_request = $not_processed_requests[0];
		        }
		        $current_request_id = $current_pharma_request['id'];
		        $this->view->current_request_id  = $current_request_id ;
		        
		    } */
		    
		    $this->view->allow_medication_comunication_i = $allow_medication_comunication_i;
		    $this->view->allow_medication_comunication_ii = $allow_medication_comunication_ii;
		    
		    
 
		    if($allow_medication_comunication_ii == 1){
		        //check if for patient and user - there are PENDING requests  ???? and do what ?? 
		        $pending_requests  = array();
		        $pending_requests = PharmaPatientDrugplanRequestsTable::find_patient_user_requests($ipid,$this->userid,'pending');
		        
		        $pending_request_id = 0 ;
		        $current_pharma_request = array();
		        if(!empty($pending_requests)){
		            $current_pharma_request = $pending_requests[0];
		            // mark as deleted the pending  request and start OVER!
		        }
		        $pending_request_id = $current_pharma_request['id'];
		        $this->view->pending_request_id  = $pending_request_id ;
		        
		        
		        /*  textarea & comment-ul */
		        $form_name = "patientmedication/requestchanges";
		        $formstextslist_model = new FormsTextsList();
		        $standard_texts_arr =  $formstextslist_model->get_client_list($clientid,$form_name);
		        
		        $request_reasons = array();
		        foreach($standard_texts_arr as $k=>$st){
		            $request_reasons[$st['id']] = trim($st['field_value']);
		            
		        }
		        
		        $this->view->reasons_arr = $request_reasons;
		        // --
		    }
		    //-- 
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    $medication_blocks[] = "ispumpe"; //ISPC-2833 Ancuta 26.02.2021
		    
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
 		    // SCHEDULED  BLOCK - Intervall Medis 
		    // Show interval medi BLOCK
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		    }

		    // ISPC-ISPC-2329 pct.r)		    
		    // Show interval options  in Actual and IVmed
		    $actual_iv_scheduled_block = $modules->checkModulePrivileges("193", $clientid);
		    if(!$actual_iv_scheduled_block){
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
		    
		    
		    //ISPC-2833 Ancuta 26.02.2021
		    $ispumpe_block = $modules->checkModulePrivileges("251", $clientid);
		    if(!$ispumpe_block){
		    	$medication_blocks = array_diff($medication_blocks,array("ispumpe"));
		    }
		    //-- 
		    if(!empty($_REQUEST['medication_type']) && $_REQUEST['efaoption'] == '1'){
		        $medication_blocks = array($_REQUEST['medication_type']);
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
	 		        if($df['extra'] == 1 && $df['isfrommmi'] == 0)
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

		    //ISPC-2554 pct.1 Carmen 03.04.2020
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
		    if($_REQUEST['efaoption'] == '1' && !empty($_REQUEST['drugplan_id'])){
		        $medicarr = $m_medication->getMedicationPlanAll($decid,false,false,$_REQUEST['drugplan_id']);
		    } else{
    		    $medicarr = $m_medication->getMedicationPlanAll($decid);
		    }
		        
		    
		    
		    //ISPC-2797 Ancuta 17.02.2021
		    $elsa_planned_medis = $modules->checkModulePrivileges("250", $clientid);
		    $this->view->elsa_planned_medis = 0;
		    
		    if($elsa_planned_medis){
		        $this->view->elsa_planned_medis = 1;
		        $drugplan_ids2planned_actions = PatientDrugplanPlanning::get_planned_drugs($ipid);
		    }
		    //--
		    
		    if($_REQUEST['efaoption'] == '1'){
		        $this->view->elsa_planned_medis = 0;
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
		        //ISPC-2833 Ancuta 26.02.2021
		        elseif($medication_data['ispumpe'] == "1")
		        {
    		        $medications_array['ispumpe'][] = $medication_data;    
    		        $pp_pumpe_ids[] = $medication_data['pumpe_id'];
		        }
		        //--
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

		    
		    
		    
		    
		    
		    //ISPC-2833 Ancuta 26.02.2021
		    // get pumpe details
		    $pp_pumpe_ids = array_unique($pp_pumpe_ids);
		    
		    if(count($pp_pumpe_ids) == 0)
		    {
		        $pp_pumpe_ids[] = '999999';
		    }
		    
		    $pp_pumpe_obj = new PatientDrugplanPumpe();
		    $perfusor_pumpe_data = $pp_pumpe_obj->get_perfusor_pumpes($pp_pumpe_ids);
		    
		    
		    if(count($perfusor_pumpe_data) > 0)
		    {
		        $addnew = 0;
		    }
		    else
		    {
		        $addnew = 1;
		    }
		    $this->view->addnewlink_ispumpe = $addnew;
		    $this->view->perfusor_pumpe_data_array = $perfusor_pumpe_data;
		     
		    
		    $alt_pumpe_details = PatientDrugplanPumpeAlt:: get_drug_pumpe_alt($ipid,$pp_pumpe_ids);
		    $alt_pumpe_declined = PatientDrugplanPumpeAlt:: get_declined_drug_pumpe_alt($ipid,$pp_pumpe_ids,false);
		    $alt_cocktail_declined_offline = PatientDrugplanPumpeAlt:: get_declined_drug_pumpe_alt_offline($ipid, $pp_pumpe_ids, false);
		    
		    $alt_pumpe_details_offline =  $alt_pumpe_details['offline'];
		    $alt_pumpe_details =  $alt_pumpe_details['online'];
		    
		    foreach($medications_array['ispumpe']  as $smpkey => $medicationsmp)
		    {
		        if(!in_array($medicationsmp['pumpe_id'],$alt_pumpe_declined)){
		            $medications_array['ispumpe'][$smpkey]['smpdescription'] = $perfusor_pumpe_data[$medicationsmp['pumpe_id']];
    	            
		            if(!empty($alt_pumpe_details[$medicationsmp['pumpe_id']]))
    	            {
    	                $medications_array['ispumpe'][$smpkey]['smpdescription_alt'] = $alt_pumpe_details[$medicationsmp['pumpe_id']];
    	            }
    	            else
    	            {
    	                $medications_array['ispumpe'][$smpkey]['smpdescription_alt'] = "";
    	            }
		        }
		        
		        //offline changes
		        $medications_array['ispumpe'][$smpkey]['smpdescription_alt_offline'] = null;
		        if( ! empty($alt_pumpe_details_offline[$medicationsmp['pumpe_id']]))
		        {
		            $medications_array['ispumpe'][$smpkey]['smpdescription_alt_offline'] = $alt_pumpe_details_offline[$medicationsmp['pumpe_id']];
		        }
		    }
		    //--  
		    
		    
		    
		    
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
			//TODO-3624 Ancuta 23.11.2020
		    $drugplan_dosage_concentration = PatientDrugPlanDosage::get_patient_drugplan_dosage_concentration($ipid);//TODO-3624 Ancuta 23.11.2020
		    //ISPC-2664 Carmen 29.09.2020
		    $dosageperkg = array();
		    $dosagedrugplantotal = array();
		    $dosagedrugplanperkgtotal = array();
		        
                foreach($drugplan_dosage as $ddr => $vdr)
                {
                	foreach($vdr as $kid => $vid)
                	{
            		    if($patientweight){
                            $dosageperkg[$ddr][$kid] = number_format($vid/$patientweight, 3, ',', '.');
                            $dosagedrugplanperkgtotal[$ddr] += $vid/$patientweight;
            		    } else {
                            $dosageperkg[$ddr][$kid] = "";
                            $dosagedrugplanperkgtotal[$ddr] += 0;
            		    }
                		$dosagedrugplantotal[$ddr] += $vid;
                	}
                	$dosagedrugplanperkgtotal[$ddr] = number_format($dosagedrugplanperkgtotal[$ddr], 3, ',', '.');
                }
            //--
            
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
		    //ISPC-2664 Carmen 30.09.2020
		    $olddosagetotal = array();
		    $olddosageperkgtotal = array();
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
 
		        	//ISPC-2797 Ancuta 17.02.2021
		        	if($elsa_planned_medis && isset($drugplan_ids2planned_actions[$vm['id']]) && !empty($drugplan_ids2planned_actions[$vm['id']])){
		        	    $medications_array[$medication_type ][$km]['planned'] = $drugplan_ids2planned_actions[$vm['id']];
		        	}
		        	//--
		        	
		        	
		        	
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
    		                // TODO-3585 Ancuta 10.11.2020  pct 1 - changes so dosage values are listed with  comma not dot
    		                //$medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']];
    		                
    		                $formated_dosages = array();
    		                if( !empty($drugplan_dosage[$vm['id']]) ){
    		                    foreach($drugplan_dosage[$vm['id']] as $dtime =>$dvalue){
    		                        $formated_dosages [$vm['id']][$dtime ] = str_replace(".",",",$dvalue);
    		                    }
    		                }
    		                $medications_array[$medication_type ][$km]['dosage'] = $formated_dosages[$vm['id']];
    		                //--
    		                
    		                
    		                //ISPC-2664 Carmen 29.09.2020
    		                $medications_array[$medication_type ][$km]['dosageperkg'] = $dosageperkg[$vm['id']];
    		                $medications_array[$medication_type ][$km]['dosagetotal'] = $dosagedrugplantotal[$vm['id']];
    		                $medications_array[$medication_type ][$km]['dosageperkgtotal'] = $dosagedrugplanperkgtotal[$vm['id']];
    		                //--
    		            }
    		            else if(strlen($vm['dosage'])> 0 )
    		            {
    		                $medications_array[$medication_type ][$km]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
    		                
    		                if(strpos($vm['dosage'],"-")){
            		            $old_dosage_arr[$vm['id']] = explode("-",$vm['dosage']);
            		            //ISPC-2664 Carmen 29.09.2020
            		            foreach($old_dosage_arr[$vm['id']] as $kod => $vod)
            		            {
               		            	$olddosagetotal[$vm['id']] += $vod;
            		                if($patientweight){
                		            	$olddosageperkg[$vm['id']]= number_format($vod/$patientweight, 3, ',', '.');
                		            	$olddosageperkgtotal[$vm['id']] += $vod/$patientweight;
            		                } else{
                		            	$olddosageperkg[$vm['id']]= "";
                		            	$olddosageperkgtotal[$vm['id']] += 0;
            		                }
            		            }
            		            $olddosageperkgtotal[$vm['id']] = number_format($olddosageperkgtotal[$vm['id']], 3, ',', '.');
            		            $medications_array[$medication_type ][$km]['dosagetotal'] = $olddosagetotal[$vm['id']];
            		            $medications_array[$medication_type ][$km]['dosageperkgtotal'] = $olddosageperkgtotal[$vm['id']];
            		            //
        		                if(count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$medication_type])){
             		                //  create array from old
                		            for($x = 0; $x < count($dosage_settings[$medication_type]); $x++)
                		            {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$vm['id']][$x]; 
                                        //ISPC-2664 Carmen 30.09.2020
                                        $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][$x]] = $olddosageperkg[$vm['id']][$x];
                                        //--
                                    }
            		            } 
            		            else
            		            {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
                                    
                                    //ISPC-2664 Carmen 30.09.2020
                                    $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][0]] = "";
                                    if($patientweight){
                                        $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][1]] = number_format($vm['dosage']/$patientweight, 3, ',', '.');
                                    } else{
                                        $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][1]] = "";
                                    }
                                    $olddosagetotal[$vm['id']] += $vm['dosage'];
                                    
                                    if($patientweight){
                                        $olddosageperkgtotal[$vm['id']] += $vm['dosage']/$patientweight;
                                    } else{
                                        $olddosageperkgtotal[$vm['id']] += 0;
                                    }
                                    $medications_array[$medication_type ][$km]['dosagetotal'] = $olddosagetotal[$vm['id']];
                                    $medications_array[$medication_type ][$km]['dosageperkgtotal'] = number_format($olddosageperkgtotal[$vm['id']], 3, ',', '.');
                                    //--
                                    
                                    
                                    for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
                                    {
                                        $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                        $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][$x]]="";
                                    }
            		            }
    		                } 
    		                else
    		                {
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!"; 
                                $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
                                //ISPC-2664 Carmen 30.09.2020
                                $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][0]] = "";
                                if($patientweight){
                                    $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][1]] = number_format($vm['dosage']/$patientweight, 3, ',', '.');
                                } else{
                                    $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][1]] = "";
                                }
                                $olddosagetotal[$vm['id']] += $vm['dosage'];
                                if($patientweight){
                                    $olddosageperkgtotal[$vm['id']] += $vm['dosage']/$patientweight;
                                } else{
                                    $olddosageperkgtotal[$vm['id']] += 0;
                                }
                                $medications_array[$medication_type ][$km]['dosagetotal'] = $olddosagetotal[$vm['id']];
                                $medications_array[$medication_type ][$km]['dosageperkgtotal'] = number_format($olddosageperkgtotal[$vm['id']], 3, ',', '.');
                                //--
                                for($x = 2; $x < count($dosage_settings[$medication_type]); $x++)
                                {
                                    $medications_array[$medication_type ][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                    $medications_array[$medication_type ][$km]['dosageperkg'][$dosage_settings[$medication_type][$x]]="";
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
   	                
   	                //ISPC-2676 Ancuta 25.09.2020
   	                $medication_extra[$vm['id']]['concentration'] = str_replace(',','.',$medication_extra[$vm['id']]['concentration']);
   	                //
   	                $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
   	                //TODO-3585 Ancuta 10.11.2020
   	                $medications_array[$medication_type ][$km]['concentration'] =  str_replace('.',',',$medication_extra[$vm['id']]['concentration']);
   	                //
   	                
   	                $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging'];
   	                $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal'];
   	                $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];
   	                // ISPC-2247
   	                $medications_array[$medication_type ][$km]['escalation_label'] =  $medication_extra[$vm['id']]['escalation'];
   	                $medications_array[$medication_type ][$km]['escalation'] =  $medication_extra[$vm['id']]['escalation_id'];
   	                // -- 

   	                //ISPC-2833 Ancuta 02.03.2021
   	                $medications_array[$medication_type ][$km]['overall_dosage_h'] =  $medication_extra[$vm['id']]['overall_dosage_h'];
   	                $medications_array[$medication_type ][$km]['overall_dosage_24h'] =  $medication_extra[$vm['id']]['overall_dosage_24h'];
   	                $medications_array[$medication_type ][$km]['overall_dosage_pump'] =  $medication_extra[$vm['id']]['overall_dosage_pump'];
   	                $medications_array[$medication_type ][$km]['drug_volume'] =  $medication_extra[$vm['id']]['drug_volume'];
   	                $medications_array[$medication_type ][$km]['unit2ml'] =  $medication_extra[$vm['id']]['unit2ml'];
   	                
   	                $medications_array[$medication_type ][$km]['concentration_per_drug'] =  $medication_extra[$vm['id']]['concentration_per_drug'];
   	                $medications_array[$medication_type ][$km]['bolus_per_med'] =  $medication_extra[$vm['id']]['bolus_per_med'];
   	                //--
   	                
   	                
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
//    	                    $medications_array[$medication_type ][$km]['dosage_24h'] = round($dosage_value * 24, 2);
   	                    
   	                    
                        if(isset($medication_extra[$vm['id']]['dosage_24h_manual']) && !empty($medication_extra[$vm['id']]['dosage_24h_manual'])){
                            $medications_array[$medication_type ][$km]['dosage_24h'] = $medication_extra[$vm['id']]['dosage_24h_manual'];
                        } else{
       	                    $medications_array[$medication_type ][$km]['dosage_24h'] = $dosage_value * 24;
                        }
   	                    
   	                    //TODO-3585 Ancuta 10.11.2020
//    	                    $medications_array[$medication_type ][$km]['dosage'] =  str_replace(".",",",round($dosage_value, 2));
                        //$medications_array[$medication_type ][$km]['dosage'] = $dosage_value;
                        //$medications_array[$medication_type ][$km]['dosage'] = number_format($dosage_value,3,",","."); // Ancuta - Pumpe-dosage 10.12.2020
                        $medications_array[$medication_type ][$km]['dosage'] = round($dosage_value,3); // Ancuta - Pumpe-dosage 10.12.2020
                        $medications_array[$medication_type ][$km]['dosage'] = str_replace(".",",",$medications_array[$medication_type ][$km]['dosage']);
   	                    //$medications_array[$medication_type ][$km]['dosage'] =  str_replace(".",",",$dosage_value); 
   	                    //
   	                    //$medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']): str_replace(".",",",$medications_array[$medication_type ][$km]['dosage'] );           //ISPC-2684 Lore 08.10.2020
   	                    //$medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;      //ISPC-2684 Lore 08.10.2020
   	                    //TODO-3829 Lore 17.02.2021
   	                    $modules = new Modules();
   	                    if($modules->checkModulePrivileges("240", $clientid)){
   	                        if( isset($medication_extra[$vm['id']]['unit_dosage']) && strlen($medication_extra[$vm['id']]['unit_dosage'])>0){
   	                            $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']): str_replace(".",",",$medications_array[$medication_type ][$km]['dosage'] );           //ISPC-2684 Lore 08.10.2020
   	                            $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;      //ISPC-2684 Lore 08.10.2020
   	                        } else {
   	                            if($medications_array[$medication_type ][$km]['unit'] == 'ml'){
   	                                $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']): '' ;           //ISPC-2684 Lore 08.10.2020
   	                                $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : '' ;      //ISPC-2684 Lore 08.10.2020
   	                            } else {
   	                                $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']): str_replace(".",",",$medications_array[$medication_type ][$km]['dosage'] );           //ISPC-2684 Lore 08.10.2020
   	                                $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;      //ISPC-2684 Lore 08.10.2020
   	                                $medications_array[$medication_type ][$km]['dosage']     =  '';
   	                                $medications_array[$medication_type ][$km]['dosage_24h'] =  '';
   	                            }
   	                        }
   	                    }
   	                    //.
   	                    
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
   	                            //$result_24h = round($result_24h, 4);
   	                            $result_24h = $result_24h;
   	                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  number_format($result_24h,3,",",".")." ".$medication_extra[$vm['id']]['dosage_form'];
   	                        }
   	                        else
   	                        {
   	                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  $result_24h." ".$medication_extra[$vm['id']]['dosage_form'];
   	                        }
   	                    }
   	                    
   	                    //TODO-3585 Ancuta 10.11.2020
   	                    $medications_array[$medication_type ][$km]['dosage_24h'] = str_replace('.', ",", $medications_array[$medication_type ][$km]['dosage_24h']);
   	                    $medications_array[$medication_type ][$km]['concentration'] = str_replace('.', ",", $medications_array[$medication_type ][$km]['concentration']);
   	                    //--
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
		    
		    //ISPC-2833 Ancuta 26.02.2021
		    if(!empty($medications_array['ispumpe'])){
		        
                foreach($medications_array['ispumpe'] as $drug_id_ke =>$med_details)
                {
                    $alt_medications_array["ispumpe"][$med_details['pumpe_id']][] =  $med_details; 
                }
                
                unset($medications_array['ispumpe']);
                $medications_array['ispumpe'] = $alt_medications_array["ispumpe"];
		    }
		    //--
		    
		    
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
		    	
		    	//ISPC-2833 Ancuta 26.02.2021
		    	elseif($medt == "ispumpe")
		    	{
/* 		    		$header[$medt][0] = "medication_change_full";
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
		    		$header[$medt][] = "importance"; //importance */
		    			
		    	}
		    	//--
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
		    
		    $saved_order = array();
		    foreach($saved_data as $k=>$sord){
		        if($sord['name'] == "order"){
		    
		            $med_type_sarr = explode("-",$sord['page']);
		            $page = $med_type_sarr[0];
		            $med_type = $med_type_sarr[1];
		            if($page == "patientmedication" && $med_type){
		                $order_value = unserialize($sord['value']);
		                $saved_order[$med_type]['col'] = $order_value[0][0] ;
		                $saved_order[$med_type]['ord'] = $order_value[0][1];
		    
		            }
		        }
		    }
		    
		    foreach($medication_blocks as $k=>$mt){
		        if(empty($saved_order[$mt])){
		            $saved_order[$mt]['col'] = "medication";
		            $saved_order[$mt]['ord'] ="asc";
		        }
		    
		    }


		   // ############ APPLY SORTING ##############

		    foreach($medications_array as $type=>$m_values){
		        if($type !="isschmerzpumpe" && $type !="ispumpe"){//ISPC-2833 Ancuta 26.02.2021, 01.03.2021 - added !- ispumpe
		            if($saved_order[$type]['ord'] == "asc"){
		                $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_ASC);
		            } else{
		                $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_DESC);
		            }
		        } else{
		            foreach($medications_array[$type] as $sch_id=>$sh_m_values){
		                if($saved_order[$type]['ord'] == "asc"){
		                    $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_ASC);
		                } else{
		                    $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_DESC);
		                }
		            }
		    
		        }
		    }
		    if(!empty($medications_array_sorted)){
		        $medications_array = array();
		        $medications_array = $medications_array_sorted;
		    }
		    
		    /* ================ BEDARF SETS  ======================= */
		    
		    $bdf = new BedarfsmedicationMaster();
		    $this->view->bedarfsdrop = $bdf->getbedarfsmedicationDrop($this->clientid);
		    
		    /* ================ NEW MED SETS  ======================= */
		    
		    $msets = new MedicationsSetsList();
		    $new_medication_sets = array();
		    $new_medication_sets['iscrisis'] = $msets->getmedicationssetsDrop($this->clientid,"iscrisis");
		    $new_medication_sets['isbedarfs'] = $msets->getmedicationssetsDrop($this->clientid,"isbedarfs");
		    //ISPC-2329 pct.k) Lore 23.08.2019
		    $new_medication_sets['actual'] = $msets->getmedicationssetsDrop($this->clientid,"actual");
		    
		    $this->view->new_medication_sets = $new_medication_sets;

		    $medications_array = Pms_CommonData::clear_pdf_data($medications_array);
// 		    dd($medications_array);
		    $this->view->medication = $medications_array;
		}
		
		
		public function deletedAction()
		{
		    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
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
		    $medication_blocks[] = "ispumpe"; //ISPC-2833 Ancuta 26.02.2021
		    
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
		    // Show interval medi BLOCK
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		    }
		    // ISPC-ISPC-2329 pct.r)		    
		    // Show interval options  in Actual and IVmed
		    $actual_iv_scheduled_block = $modules->checkModulePrivileges("193", $clientid);
		    if(!$actual_iv_scheduled_block){
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
		    //ISPC-2833 Ancuta 26.02.2021
		    $ispumpe_block = $modules->checkModulePrivileges("251", $clientid);
		    if(!$ispumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("ispumpe"));
		    }
		    //-- 
		    
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
		            //ISPC-2833 Ancuta 26.02.2021
		            elseif($vm['ispumpe'] == "1")
		            {
		                $deleted_medication_type = "ispumpe";  
		            }
		            //--
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
				
		
		public function deletededitAction()
		{
		    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
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

		    // Show interval medi BLOCK
		    $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
		    if(!$scheduled_block){
		        $medication_blocks = array_diff($medication_blocks,array("scheduled"));
		    }
		    // ISPC-ISPC-2329 pct.r)		    
		    // Show interval options  in Actual and IVmed
		    $actual_iv_scheduled_block = $modules->checkModulePrivileges("193", $clientid);
		    if(!$actual_iv_scheduled_block){
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
		
		public function historyAction()
		{
		    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
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
                            //$medication_data['current']['dosage_concentration'][$dtime] = rtrim(rtrim(number_format(round($dosage_value / $concentration, 4),3,",","."), "0"), ",")." ".$medication_extra[$drugplanid_details['id']]['dosage_form'];
                            //TODO-3624 
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
/*     		            unset($medication_history[$hid]);
    		            $exclude[] = $hid; */
    		            //ISPC-2524 pct.2)  Lore 16.01.2020
    		            if($hdata['istransition'] == '0'){
    		                unset($medication_history[$hid]);
    		                $exclude[] = $hid;
    		            }
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


		public function printAction()
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

		    //ISPC-2684 Lore 09.10.2020
		    $show_unit_dosage_flussrate_type = 0;
		    if($modules->checkModulePrivileges("240", $clientid)){
		        $show_unit_dosage_flussrate_type = 1;
		    }
		    $this->view->show_unit_dosage_flussrate_type = $show_unit_dosage_flussrate_type;
		    //.
		    
		    if($this->getRequest()->isPost())
		    {
    		    if(strlen($_POST['insert_bedarf']) > 0 &&  !empty($_POST['bid']))
    		    {
    		        if($acknowledge == "1"){
    		            if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $this->usertype == 'SA'){ //ISPC-2554
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
    		        

    		        $this->_redirect(APP_BASE . "patientmedication/overview?id=" . $_REQUEST['id']);
    		        exit;
    		        //break;
    		    }
    		    
    		    if ($this->getRequest()->isXmlHttpRequest())
    		    {
    		    	$bm = new Bedarfsmedication();
    		    	$bmarr = $bm->getbedarfsmedication($_POST['bid']);
    		    	
    		    	$mids = array_column($bmarr, 'medication_id');
    		    	
    		    	$mastermed = new Medication();
    		    	$master_details = $mastermed->getMedicationById($mids, true);
    		    	
    		    	foreach($bmarr as $kr=>&$vr)
    		    	{
    		    		$vr['medication'] = $master_details[$vr['medication_id']]['name'];
    		    		$vr['source'] = $master_details[$vr['medication_id']]['source'];
    		    		unset($vr['id']);
    		    		unset($vr['bid']);
    		    		unset($vr['medication_id']);
    		    		unset($vr['create_date']);
    		    		unset($vr['change_date']);
    		    		unset($vr['create_user']);
    		    		unset($vr['change_user']);
    		    	}
    		    	
    		    	$this->_helper->json->sendJson($bmarr);
    		    }
		    }
		    
		    /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
		    $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");
		    $medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta
		    $medication_blocks[] = "ispumpe"; //ISPC-2833 Ancuta 26.02.2021
		    
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
		    
		    //ISPC-2833 Ancuta 26.02.2021
		    $ispumpe_block = $modules->checkModulePrivileges("251", $clientid);
		    if(!$ispumpe_block){
		        $medication_blocks = array_diff($medication_blocks,array("ispumpe"));
		    }
		    //-- 
		    
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
		        elseif($medication_data['ispumpe'] == "1")
		        {
    		        $medications_array['ispumpe'][] = $medication_data;
    		        $pumpe_ids[] = $medication_data['pumpe_id'];     
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
                    // Lore 20.12.2019 ISPC-2329 - in the field "INDIKATION/ KOMMENTAR" the symbol "<" or ">" in pdf dont show correct
                    $cocktails[$medicationsmp['cocktailid']]['description'] = str_replace(array("<"), array("&lt;"), $cocktails[$medicationsmp['cocktailid']]['description']);
		            //.
                    $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = $cocktails[$medicationsmp['cocktailid']];
// 		        }
// 		        else
// 		        {
// 		            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = "0";
// 		        }
		    }
		    
		    
		    //ISPC-2833 Ancuta 01.03.2021
		    // get pumpe details
		    $pumpe_ids = array_unique($pumpe_ids);
		    
		    if(count($pumpe_ids) == 0)
		    {
		        $pumpe_ids[] = '999999';
		    }
	    
		    $pumpe_obj = new PatientDrugplanPumpe();
		    $pumpes_array = $pumpe_obj->get_perfusor_pumpes($pumpe_ids);

		    foreach($medications_array['ispumpe']  as $smpkey => $medicationsmp)
		    {
                $medications_array['ispumpe'][$smpkey]['smpdescription'] = $pumpes_array[$medicationsmp['pumpe_id']];

		    }
		    // ISPC-2833 Ancuta 01.03.2021
		    
		    
		    
		    
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
			//TODO-3624 Ancuta 23.11.2020
		    $drugplan_dosage_concentration = PatientDrugPlanDosage::get_patient_drugplan_dosage_concentration($ipid);
            
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
		        	
		        	//ISPC-2329 Lore 17.01.2020 - in the field "INDIKATION/ KOMMENTAR" the symbol "<" or ">" in pdf dont show; new function "clear_pdf_data_medi" in library/Pms/CommonData.php
		        	// $medications_array[$medication_type ][$km]['comments'] =  str_replace(array("<",">"), array(" ",""), $vm['comments']); // ISPC-2224 31.07.2018
		        	
		            
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
    		                
    		                // TODO-3585 Ancuta 10.11.2020  pct 1 - changes so dosage values are listed with  comma not dot 
    		                //$medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']]; 

    		                $formated_dosages = array();
    		                if( !empty($drugplan_dosage[$vm['id']]) ){
    		                    foreach($drugplan_dosage[$vm['id']] as $dtime =>$dvalue){
    		                        $formated_dosages [$vm['id']][$dtime ] = str_replace(".",",",$dvalue);
    		                    }
    		                }
    		                $medications_array[$medication_type ][$km]['dosage'] = $formated_dosages[$vm['id']];
    		                //--
    		                
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

	                //TODO-3829 Ancuta 24.02.2021
	                if($medication_type == "isschmerzpumpe" && strlen($medication_extra[$vm['id']]['unit']) == 0 ) {
	                    $medication_extra[$vm['id']]['unit'] = "i.E.";
	                }
	                // --- 
	                
	                //$medications_array[$medication_type ][$km]['drug'] =  str_replace(array("<",">"), array(" "," "), $vm['drug']); // ISPC-2224 31.07.2018
	                $medications_array[$medication_type ][$km]['drug'] =  str_replace(array("<",">"), array(" "," "), $medication_extra[$vm['id']]['drug']); // ISPC-2224 31.07.2018
   	                $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit']; 
   	                $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type']; 
   	                $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name']; 
   	                $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color']; 
   	                $medications_array[$medication_type ][$km]['importance'] =  $medication_extra[$vm['id']]['importance'];
   	                $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form'];
   	                $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id'];
   	                //ISPC-2676 Ancuta 25.09.2020
   	                $medication_extra[$vm['id']]['concentration'] = str_replace(',','.',$medication_extra[$vm['id']]['concentration']);
   	                //
   	                // TODO-3585 Ancuta 10.11.2020  pct 1 - changes so concentration is listed with  comma not dot 
   	                //$medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
   	                $medications_array[$medication_type ][$km]['concentration'] =  str_replace('.',',',$medication_extra[$vm['id']]['concentration']);
   	                //$medications_array[$medication_type ][$km]['concentration_full'] =  $medication_extra[$vm['id']]['concentration'];
   	                $medications_array[$medication_type ][$km]['concentration_full'] = str_replace('.',',',$medication_extra[$vm['id']]['concentration']);
   	                //--
   	                
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
   	                
   	                
   	                
   	                //ISPC-2833 Ancuta 02.03.2021
   	                $medications_array[$medication_type ][$km]['overall_dosage_h'] =  $medication_extra[$vm['id']]['overall_dosage_h'];
   	                $medications_array[$medication_type ][$km]['overall_dosage_24h'] =  $medication_extra[$vm['id']]['overall_dosage_24h'];
   	                $medications_array[$medication_type ][$km]['overall_dosage_pump'] =  $medication_extra[$vm['id']]['overall_dosage_pump'];
   	                $medications_array[$medication_type ][$km]['drug_volume'] =  $medication_extra[$vm['id']]['drug_volume'];
   	                $medications_array[$medication_type ][$km]['unit2ml'] =  $medication_extra[$vm['id']]['unit2ml'];
   	                
   	                $medications_array[$medication_type ][$km]['concentration_per_drug'] =  $medication_extra[$vm['id']]['concentration_per_drug'];
   	                $medications_array[$medication_type ][$km]['bolus_per_med'] =  $medication_extra[$vm['id']]['bolus_per_med'];
   	                //--
   	                
   	                 
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
           	                        $medications_array[$medication_type ][$km]['dosage_concentration_value'][$dtime] = rtrim(rtrim(number_format($result,3,",","."), "0"), ","); //TODO-3126 Ancuta 27-29.04.2020 
       	                        } 
       	                        else
       	                        {
           	                        $medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = $result." ".$medication_extra[$vm['id']]['dosage_form'];
           	                        $medications_array[$medication_type ][$km]['dosage_concentration_value'][$dtime] = $result; //TODO-3126 Ancuta 27-29.04.2020 
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
   	                    				$medications_array[$medication_type ][$km]['dosage_concentration_value'][$dtime] = rtrim(rtrim(number_format($result,3,",","."), "0"), ","); //TODO-3126 Ancuta 27-29.04.2020 
   	                    			}
   	                    			else
   	                    			{
   	                    				$medications_array[$medication_type ][$km]['dosage_concentration'][$dtime] = $result." ".$medication_extra[$vm['id']]['dosage_form'];
   	                    				$medications_array[$medication_type ][$km]['dosage_concentration_value'][$dtime] = $result; //TODO-3126 Ancuta 27-29.04.2020 
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
	       	                        $medications_array[$medication_type ][$km]['dosage_concentration_value'] = rtrim(rtrim(number_format($result,3,",","."), "0"), ","); //TODO-3126 Ancuta 27-29.04.2020 
	   	                        } 
	   	                        else
	   	                        {
	       	                        $medications_array[$medication_type ][$km]['dosage_concentration'] = $result." ".$medication_extra[$vm['id']]['dosage_form'];
	       	                        $medications_array[$medication_type ][$km]['dosage_concentration_value'] = $result; //TODO-3126 Ancuta 27-29.04.2020 
	   	                        }
   	                    	}
     	                }
   	                }
   	                
   	                
   	                
   	                
   	                if($medication_type == "isschmerzpumpe")
   	                {
   	                    
   	                    $dosage_value = "";
   	                    $dosage_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage']);
   	                    //$medications_array[$medication_type ][$km]['dosage_24h'] = round($dosage_value * 24, 2);
   	                    //TODO-3624 Ancuta 23.11.2020
                        if(isset($medication_extra[$vm['id']]['dosage_24h_manual']) && !empty($medication_extra[$vm['id']]['dosage_24h_manual'])){
                            $medications_array[$medication_type ][$km]['dosage_24h'] = $medication_extra[$vm['id']]['dosage_24h_manual'];
                        } else{
       	                    $medications_array[$medication_type ][$km]['dosage_24h'] = $dosage_value * 24;
                        }
   	                    
   	                    // TODO-3585
   	                    //$medications_array[$medication_type ][$km]['dosage'] = str_replace(".",",",$medications_array[$medication_type ][$km]['dosage']);
   	                    
   	                    //$medications_array[$medication_type ][$km]['dosage'] = str_replace(".",",",round($dosage_value, 2));
                        $medications_array[$medication_type ][$km]['dosage'] = round($dosage_value,3); // Ancuta - Pumpe-dosage 10.12.2020
                        $medications_array[$medication_type ][$km]['dosage'] = str_replace(".",",",$medications_array[$medication_type ][$km]['dosage']);
   	                    //$medications_array[$medication_type ][$km]['dosage'] = str_replace(".",",",$dosage_value);
   	                    
   	                    //$medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage']) ? $medication_extra[$vm['id']]['unit_dosage']: $medications_array[$medication_type ][$km]['dosage'];           //ISPC-2684 Lore 08.10.2020
   	                    //$medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? $medication_extra[$vm['id']]['unit_dosage_24h'] : $dosage_value * 24 ;   //ISPC-2684 Lore 08.10.2020
   	                    //TODO-3829 Lore 17.02.2021
   	                    $modules = new Modules();
   	                    if($modules->checkModulePrivileges("240", $clientid)){
   	                        if( isset($medication_extra[$vm['id']]['unit_dosage']) && strlen($medication_extra[$vm['id']]['unit_dosage'])>0){
   	                            $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage']) ? $medication_extra[$vm['id']]['unit_dosage']: $medications_array[$medication_type ][$km]['dosage'];           //ISPC-2684 Lore 08.10.2020
   	                            $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? $medication_extra[$vm['id']]['unit_dosage_24h'] : $dosage_value * 24 ;   //ISPC-2684 Lore 08.10.2020
   	                        } else {
   	                            if($medications_array[$medication_type ][$km]['unit'] == 'ml'){
   	                                $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage']) ? $medication_extra[$vm['id']]['unit_dosage']: '';
   	                                $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? $medication_extra[$vm['id']]['unit_dosage_24h'] : '' ;
   	                            } else {
   	                                $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage']) ? $medication_extra[$vm['id']]['unit_dosage']: $medications_array[$medication_type ][$km]['dosage'];
   	                                $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? $medication_extra[$vm['id']]['unit_dosage_24h'] : $dosage_value * 24 ;
   	                                $medications_array[$medication_type ][$km]['dosage']     =  '';
   	                                $medications_array[$medication_type ][$km]['dosage_24h'] =  '';
   	                            }
   	                        }
   	                    }
   	                    //.
   	                    
   	                    if($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration'])> 0  && $medication_extra[$vm['id']]['concentration'] != 0 )
   	                    {
   	                        $dosage_24h_value = str_replace(",",".",$medications_array[$medication_type ][$km]['dosage_24h']);
   	                        $concentration_24h = str_replace(",",".",$medication_extra[$vm['id']]['concentration']);
   	                
   	                        $result_24h = "";
   	                        $result_24h = $dosage_24h_value / $concentration_24h;
   	                
   	                        if(!is_int($result_24h))
   	                        {
   	                            //$result_24h = round($result_24h, 4);
   	                            $result_24h = $result_24h;//TODO-3624 Ancuta 23.11.2020
   	                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  number_format($result_24h,3,",",".")." ".$medication_extra[$vm['id']]['dosage_form'];
   	                        }
   	                        else
   	                        {
   	                            $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  $result_24h." ".$medication_extra[$vm['id']]['dosage_form'];
   	                        }
   	                    }
   	                    // TODO-3585 Ancuta 10.11.2020  pct 1 - changes so dosage_24h is listed with  comma not dot
   	                    $medications_array[$medication_type ][$km]['dosage_24h'] = str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']);
   	                    //$medications_array[$medication_type ][$km]['dosage_24h_concentration'] = str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h_concentration']);
   	                    $medications_array[$medication_type ][$km]['unit_dosage'] = str_replace(".",",",$medications_array[$medication_type ][$km]['unit_dosage']);
   	                    $medications_array[$medication_type ][$km]['unit_dosage_24h'] = str_replace(".",",",$medications_array[$medication_type ][$km]['unit_dosage_24h']);
   	                    //    
   	                    
   	                    
   	                }
   	                // #################################################################
   	                // MEDICATION comment
   	                // #################################################################
   	               // $medications_array[$medication_type ][$km]['comments'] =  str_replace(array("<",">"), array(" "," "), $vm['comments']); // ISPC-2224 31.07.2018
   	                //ISPC-2329 Lore 17.01.2020 - in the field "INDIKATION/ KOMMENTAR" the symbol "<" or ">" in pdf dont show; new function "clear_pdf_data_medi" in library/Pms/CommonData.php
   	                
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

		    //ISPC-2833 Ancuta 01.03.2021
		    if(!empty($medications_array['ispumpe'])){
		    
		        foreach($medications_array['ispumpe'] as $drug_id_ke =>$med_details)
		        {
		            $alt_medications_array["ispumpe"][$med_details['pumpe_id']][] =  $med_details;
		        }
		    
		        unset($medications_array['ispumpe']);
		        $medications_array['ispumpe'] = $alt_medications_array["ispumpe"];
		    }
		    //--
		    if($this->getRequest()->isPost())
		    {
		        
		      if(!empty($_POST['print'])){

				  //TODO-3563 Lore 02.11.2020
		          //ISPC-2636 Lore 29.07.2020
		          $cust = Doctrine_Query::create()
		          ->select("client_medi_sort, user_overwrite_medi_sort_option")
		          ->from('Client')
		          ->where('id = ?',  $clientid);
		          $cust->getSqlQuery();
		          $disarray = $cust->fetchArray();
		          
		          
		          $client_medi_sort = $disarray[0]['client_medi_sort'];
		          $user_overwrite_medi_sort_option = $disarray[0]['user_overwrite_medi_sort_option'];
		          
		          $uss = Doctrine_Query::create()
		          ->select('*')
		          ->from('UserTableSorting')
		          ->Where('client = ?', $clientid)
		          ->orderBy('change_date DESC')
		          ->limit(1);
		          $uss_arr = $uss->fetchArray();
		          $last_sort_order = unserialize($uss_arr[0]['value']);
		          
		          $usort = new UserTableSorting();
		          $saved_data = $usort->user_saved_sorting($userid, false, $ipid);
		          
		          foreach($saved_data as $k=>$sord){
		              if($sord['name'] == "order"){
		          
		                  $med_type_sarr = explode("-",$sord['page']);
		                  $page = $med_type_sarr[0];
		                  $med_type = $med_type_sarr[1];
		                  if($page == "patientmedication" && $med_type){
		                      $order_value = unserialize($sord['value']);
		                      $saved_order[$med_type]['col'] = $order_value[0][0] ;
		                      $saved_order[$med_type]['ord'] = $order_value[0][1];
		          
		                  }
		              }
		          }
		        
		          //TODO-3450 Ancuta 22.09.2020 - added sorting in request - so we can use BOTH clent sorting - and the sorting in page, as  the page is refreshed when sorting is applied
		          if(!empty($client_medi_sort)){
		              
		              $request_sort = array();
		              if(!empty($_REQUEST['sort_b']) && !empty($_REQUEST['sort_c']) && !empty($_REQUEST['sort_d'])){
		                  $request_sort[$_REQUEST['sort_b']]['col'] = $_REQUEST['sort_c'];
		                  $request_sort[$_REQUEST['sort_b']]['ord'] = $_REQUEST['sort_d'];
		              }
		              
		              foreach($medication_blocks as $k=>$mt){
		                  if(!empty($request_sort[$mt])){
		                      $saved_order[$mt]['col'] = $request_sort[$mt]['col'];
		                      $saved_order[$mt]['ord'] = $request_sort[$mt]['ord'];
		                  }
		                  elseif(!empty($client_medi_sort)){
		                      $saved_order[$mt]['col'] = !empty($client_medi_sort) ? $client_medi_sort : "medication";              //ISPC-2636 Lore 29.07.2020
		                      $saved_order[$mt]['ord'] = "asc";
		                  }
		                  elseif(empty($saved_order[$mt])){
		                      $saved_order[$mt]['col'] = "medication";
		                      $saved_order[$mt]['ord'] = "asc";
		                  }
		              }
		              
		          } else{
		              foreach($medication_blocks as $k=>$mt){
		                  if(empty($saved_order[$mt])){
		                      $saved_order[$mt]['col'] = "medication";
		                      $saved_order[$mt]['ord'] = "asc";
		                  }
		              }
		          }
		          //---
		          
		          
		          //ISPC-2636 Lore 29.07.2020
		          if($user_overwrite_medi_sort_option != '0'){
		              $uomso = Doctrine_Query::create()
		              ->select('*')
		              ->from('UserSettingsMediSort')
		              ->Where('clientid = ?', $clientid)
		              ->orderBy('create_date DESC')
		              ->limit(1);
		              $uomso_arr = $uomso->fetchArray();
		              
		              if(!empty($uomso_arr)){
		                  $overwrite_saved_order = array();
		                  foreach($saved_order as $block => $vals){
		                      $overwrite_saved_order[$block]['col'] = !empty($uomso_arr[0]['sort_column'] ) ? $uomso_arr[0]['sort_column'] : 'medication';//Ancuta 17.09.2020-- Issue if empty
		                      $overwrite_saved_order[$block]['ord'] = !empty($last_sort_order[0][1]) ? $last_sort_order[0][1] : "asc";
		                  }
		                  $saved_order = $overwrite_saved_order;
		              }
		          }
		          //.
		          
/* 		          foreach($medication_blocks as $k=>$mt){
		              if(empty($saved_order[$mt])){
		                  $saved_order[$mt]['col'] = "medication";
		                  $saved_order[$mt]['ord'] ="asc";
		              }
		          
		          } */
		          
		          $print_data = $_POST['print']; 
		          
		       
		          foreach($medication_blocks as $med_type)
		          {
		              
		              if($med_type != "isschmerzpumpe" && $med_type != "ispumpe" )
		              {
    		              if(!isset($print_data[$med_type]['allow']) || empty($medications_array[$med_type]))
    		              {
    		                  unset($medications_array[$med_type]);
    		              } 
    		              else
    		              {
    		                  $allow_print['medication_types'][] = $med_type;
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
		                      }
		                      
		                  }
		              }
		          }
		      }  
		      
                // ############ APPLY SORTING ##############
                 
                foreach($medications_array as $type=>$m_values){
                    if($type !="isschmerzpumpe" && $type !="ispumpe"){
                        if($saved_order[$type]['ord'] == "asc"){
                            $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_ASC);
                        } else{
                            $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_DESC);
                        }
                    } else{
                        foreach($medications_array[$type] as $sch_id=>$sh_m_values){
                            if($saved_order[$type]['ord'] == "asc"){
                                $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_ASC);
                            } else{
                                $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_DESC);
                            }
                        }
                
                    }
                }
                if(!empty($medications_array_sorted)){
                    $medications_array = array();
                    $medications_array = $medications_array_sorted;
                }
                
                $post['allow_print'] = $allow_print; 
                
                $medications_array = Pms_CommonData::clear_pdf_data($medications_array);
                $post['medications_array'] = $medications_array;
                 
                $medication_blocks = Pms_CommonData::clear_pdf_data($medication_blocks);
                $post['medication_blocks'] = $medication_blocks; 
                
                $post['dosage_intervals'] = $this->view->dosage_intervals; 
                $post['show_new_fileds'] = $this->view->show_new_fileds;
                
                $post['show_unit_dosage_flussrate_type'] = $show_unit_dosage_flussrate_type;    //ISPC-2684 Lore 09.10.2020
                
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
                        //TODO-2819 Ancuta 16.01.2020 - redirect if no medication available for print 
                        //$this->_generatePdfNew_datamatrix(3, $post, 'medication_plan_datamatrix', "medicationplanpatient_datamatrix.html");
                        if ( empty($post['allow_print']['medication_types']) && empty($post['allow_print']['medication_pumpe'])) {
                            $this->_redirect(APP_BASE . "patientmedication/overview?id=" . $_REQUEST['id']."&msg=KeineMedikation");
                        } else{
                        	$this->_generatePdfNew_datamatrix(3, $post, 'medication_plan_datamatrix', "medicationplanpatient_datamatrix.html");
                        }
                        //-- 
                    	break;
                    		
                    default:
                        
                        $this->_redirect(APP_BASE . "patientmedication/overview?id=" . $_REQUEST['id']);
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

	       // ISPC-2329 Lore 17.01.2020 - in the field "INDIKATION/ KOMMENTAR" the symbol "<" or ">" in pdf dont show ; new function
		    $medication_plans =  array('medication','medication_plan_patient', 'medication_plan_patient_active_substance', 'medication_plan_bedarfsmedication', 'medication_plan_applikation' );
		    if( in_array($pdfname, $medication_plans)){
		        $post = Pms_CommonData::clear_pdf_data_medi($post, $excluded_keys);        
		    } else{
		        $post = Pms_CommonData::clear_pdf_data($post, $excluded_keys);
		    }
		    
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
		            "medication" => "Medikationsplan",
		        	"medication_plan_patient"=> "Medikationsplan Patient",
		        	"medication_plan_patient_active_substance"=> "Medikationsplan Patient Wirkstoff ",
		        	"medication_plan_bedarfsmedication"=> "Bedarfsmedikamente",
		        	"medication_plan_applikation"=> "Medikationsplan Patient + Applikation",
		            "Stammblatt"=> "Stammblatt",
		            "Sapvfb8lmu"=> "Leistungsnachweis Kinder-SAPV",
		        		
					"muster1a1_pdf" => "Muster 1a " . date('d.m.Y'),
		        		
		        );
		
		        //$pdf = new Pms_PDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
		    	if($pdfname == 'medication' || $pdfname == 'medication_plan_patient' || $pdfname == 'medication_plan_patient_active_substance' || $pdfname == 'bedarfsmedication' || $pdfname == 'medication_plan_applikation')
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

		        if ($pdfname == 'medication' ||  $pdfname == 'medication_plan_patient' ||  $pdfname == 'medication_plan_patient_active_substance' || $pdfname == 'medication_plan_bedarfsmedication' || $pdfname == 'medication_plan_applikation')
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
		        //ISPC-2329 pct.h)   Lore 23.08.2019 --> font size="+5"
		        elseif($chk == 3 && ($pdfname == 'medication' ||  $pdfname == 'medication_plan_patient' ||  $pdfname == 'medication_plan_patient_active_substance' || $pdfname == 'medication_plan_bedarfsmedication' || $pdfname == 'medication_plan_applikation')){
		        	$header_text = '<table cellpadding="2" width="277mm" >
			        					<tr>
			        						<td width="50%"><font size="+5">Name: '.  $post['patname'] .'</font></td>
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
		            	|| $pdfname == 'medication_plan_applikation')  
		        {
		        
    		        if ( isset($post['patient_allergies']) && ! empty($post['patient_allergies']['allergies_comment']) && trim($post['patient_allergies']['allergies_comment']) != "Keine Allergien / Kommentare") {
    		            //TODO-2772 Ancuta 20.12.2019
    		            //$patient_allergies_comment =  "<table cellpadding=\"2\" width=\"277mm\"> <tr><td colspan=2>Allergien: " . html_entity_decode($post['patient_allergies']['allergies_comment']) . "</td></tr></table>";
    		            $allergies = html_entity_decode($post['patient_allergies']['allergies_comment'], ENT_COMPAT, 'UTF-8');
    		            $allergies = str_replace("<br/>", "|--|--|" ,$allergies );
    		            $allergies = str_replace("<br />", "|--|--|" ,$allergies );
    		            $allergies = str_replace("<", "&lt;" ,$allergies );
    		            $allergies = str_replace(">", "&gt;" ,$allergies );
    		            $allergies = str_replace("|--|--|", "<br/>" ,$allergies );
    		            $patient_allergies_comment =  "<table cellpadding=\"2\" width=\"277mm\"> <tr><td colspan=2>Allergien: " . $allergies . "</td></tr></table>";
    		            //-- 
    		        
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
	            elseif($pdfname == "medication_plan_applikation" && $med_module == "1")
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
	            elseif($pdfname != "medication" && $pdfname != "medication_plan_patient" && $pdfname != "medication_plan_patient_active_substance" && $pdfname != "medication_plan_bedarfsmedication" && $pdfname != "medication_plan_applikation")
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
        

        
        
        function shared_18032020Action(){
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
        							//TODO-2982 Lore 09.03.2020
        							$medications_array[$data['ipid']][$type][$k]['hidd_medication'] = '';
        							// --
        
        						} else{
        							$medications_array[$data['ipid']][$type][$k]['source_changed'] = "0";
        						}
        
        					}
        					else{
        
        						if($data['medication'] != $source_corelation[$data['id']]['medication'] || $data['dosage'] != $source_corelation[$data['id']]['dosage']){
        							$medications_array[$data['ipid']][$type][$k]['source_changed'] = "1";
        							//TODO-2982 Lore 09.03.2020
        							if($data['medication'] != $source_corelation[$data['id']]['medication']){
        								$medications_array[$data['ipid']][$type][$k]['hidd_medication'] = '';
        							}
        							// --
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
        							//TODO-2982 Lore 09.03.2020
        							$medications_array[$data['ipid']][$type][$k]['hidd_medication'] = '';
        							// --
        						} else{
        							$medications_array[$data['ipid']][$type][$k]['source_changed'] = "0";
        						}
        
        					}
        					else
        					{
        
        						if($data['medication'] != $own_corelation[$data['source_drugplan_id']]['medication'] || $data['dosage'] != $own_corelation[$data['source_drugplan_id']]['dosage']){
        							$medications_array[$data['ipid']][$type][$k]['source_changed'] = "1";
        							//TODO-2982 Lore 09.03.2020
        							if($data['medication'] != $own_corelation[$data['source_drugplan_id']]['medication']){
        								$medications_array[$data['ipid']][$type][$k]['hidd_medication'] = '';
        							}
        							// --
        
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
        
        
        		$this->_redirect(APP_BASE . "patientmedication/overview?id=" . $_REQUEST['id']);
        
        	}
        
        
        }
        
        function setitemsAction(){
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
            $mids = array_column($set_items, 'medication_id');
           
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
            
            $mastermed = new Medication();
            $master_details = $mastermed->getMedicationById($mids, true);

            // ISPC-2247 pct.1 Lore 06.05.2020
            $medication_blocks = array($set_values['med_type']);
            $patient_time_scheme_arr  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid, $clientid, $medication_blocks);
            $patient_time_scheme = array();
            foreach($patient_time_scheme_arr as $key => $vals){
                foreach( $vals[$set_values['med_type']] as $keyss => $valsss){
                    $patient_time_scheme[] = $valsss;
                }
            }
            $this->view->intervals = $patient_time_scheme;

            $dsg_arr = MedicationsSetsItemsDosage::get_set_dosage_and_schema_group_by_medi($set_id);
            //.
            
            foreach($set_items as $k => $item)
            {
                $set_items[$k]['indication'] = $client_medication_extra['medication_indication'][$set_indikation];
                
                foreach ($item['frequency'] as $kf=>$fid)
                {
                    $set_items[$k]['frequency_array'][$client_medication_extra['frequency'][$fid]] = $client_medication_extra['frequency'][$fid];// EDIT - HERE SHOW VALUES
                }
                
                // ISPC-2247 pct.1 Lore 06.05.2020
                if(!empty($dsg_arr[$item['medication_id']])){
                    $set_items[$k]['dosage_array'] = $dsg_arr[$item['medication_id']]['dosage_arr'];
                    $set_items[$k]['dosage_array_schema'] = $dsg_arr[$item['medication_id']]['dosage_arr_schema'];
                } else {
                    foreach ($item['dosage'] as $ksf=>$fids)
                    {
                        $set_items[$k]['dosage_array'][$fids] = $fids;// EDIT - HERE SHOW VALUES
                    }
                }
/*                 foreach ($item['dosage'] as $ksf=>$fids)
                {
                    $set_items[$k]['dosage_array'][$fids] = $fids;// EDIT - HERE SHOW VALUES
                } */
                
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
                
                $set_items[$k]['source'] = $master_details[$item['medication_id']]['source'];
                
                //ISPC-2554 pct.1 Carmen 08.04.2020
                $medication_atc = json_encode(array(
                		'atc_code' => $item['atc_code'],
                		'atc_description' => $item['atc_description'],
                		'atc_groupe_code' => $item['atc_groupe_code'],
                		'atc_groupe_description' => $item['atc_groupe_description'],
                ));
                $set_items[$k]['atc'] = htmlspecialchars($medication_atc);
                $set_items[$k]['unit'] = $item['unit'];
                //--                
            }
//             print_r($client_medication_extra); 
//             print_r($set_items); exit;
            
            // dd($set_items);
            $this->view->set_items = $set_items; 
            $this->view->set_type = $_REQUEST['set_type']; 
        }
        
        function setsAction(){
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
                    if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $this->usertype == 'SA'){ //ISPC-2554
                        // do nothing
                    }
                    else
                    {
                        $this->_redirect(APP_BASE . "error/previlege");
                    }
                }
                //dd($post);
                $med_type_block = $post['set_type'];
                $post['medication_block'] = array();
                if(!empty($post['item']) && !empty($post['set_type'])){
                    
                    $r = 1 ; 
                    foreach($post['item'] as $med_row=>$med_details)
                    {
                        if(isset($med_details['add_medication']) && $med_details['add_medication'] == "1"){

                            $post['medication_block'][$med_type_block]['medication'][$r] = $med_details['medication']; 
                            $post['medication_block'][$med_type_block]['medication'][$r] = !empty($med_details['medication']) ? $med_details['medication'] : "-";
                            $post['medication_block'][$med_type_block][$r]['source'] = $med_details['source'];
                            //ISPC-2554 pct.1 Carmen 08.04.2020
                            $post['medication_block'][$med_type_block][$r]['atc'] = $med_details['atc'];
                            //--
                            $post['medication_block'][$med_type_block]['hidd_medication'][$r] = ""; 
                            $post['medication_block'][$med_type_block]['drid'][$r] = ""; 
                            $post['medication_block'][$med_type_block]['drug'][$r] =  $med_details['drug']; 
                            
                            //ISPC-2247 pct.1 Lore 11.05.2020
                            //$post['medication_block'][$med_type_block]['dosage'][$r] =  $med_details['dosage'];
                            if(isset($med_details['dosage'])){
                                $post['medication_block'][$med_type_block]['dosage'][$r] =  $med_details['dosage'];
                            }else{
                                $keys_arr = array_keys($med_details);
                                
                                foreach ($keys_arr as $key_med) {
                                    if(($key_med == 'dosage_0') ){
                                        $post['medication_block'][$med_type_block]['dosage'][$r][$med_details['schema_0']] = $med_details[$key_med];
                                    }
                                    if(($key_med == 'dosage_1') ){
                                        $post['medication_block'][$med_type_block]['dosage'][$r][$med_details['schema_1']] = $med_details[$key_med];
                                    }
                                    if(($key_med == 'dosage_2') ){
                                        $post['medication_block'][$med_type_block]['dosage'][$r][$med_details['schema_2']] = $med_details[$key_med];
                                    }
                                    if(($key_med == 'dosage_3') ){
                                        $post['medication_block'][$med_type_block]['dosage'][$r][$med_details['schema_3']] = $med_details[$key_med];
                                    }
                                }
                            }
                            //.
                            
                            $post['medication_block'][$med_type_block][$r]['dosage_interval'] =  $med_details['dosage_interval'];
                            
                            $post['medication_block'][$med_type_block]['dosage_form'][$r] =  $med_details['dosage_form'];
                            $post['medication_block'][$med_type_block]['unit'][$r] = $med_details['unit'];
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

                //dd($post);
                if(!empty($post['medication_block'])){
                
                    $_POST['medication_block'] = $post['medication_block'];
                    $post_medication_data = $post;
                    $post_medication_data['clientid'] = $clientid;
                    $post_medication_data['userid'] = $userid;
                    $med_form = new Application_Form_PatientDrugPlan();
                    
                    
                    $med_form->save_medication($ipid,$post_medication_data);
                
                }
                
                $this->_redirect(APP_BASE . "patientmedication/overview?id=" . $_REQUEST['id']);
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
	

	/**
	 * @see PatientController::patientdetailsAction()
	 */
	private function __box_ActualMedicationBlock_View_Assign()
	{
	
	    $ipid = $this->ipid;
	    $patientdetails =  $this->_patientMasterData;
	    $pdata = [];
	
	    $this->view->pid = $this->enc_id;
	
	    $this->view->isdischarged = $patientdetails['isdischarged'];
	    $this->view->isstandby = $patientdetails['isstandby'];
	    $this->view->isstandbydelete = $patientdetails['isstandbydelete'];
	     
	     
	    //         if($modules->checkModulePrivileges("147", $clientid))
	    if ($patientdetails['ModulePrivileges'][147]) {
	        $allow_history_changes = "1";
	    } else {
	        $allow_history_changes = "0";
	    }
	    $this->view->allow_history_changes = $allow_history_changes;
	
	
	    if($allow_history_changes == "1"){
	        $display_edit_history = $this->_clientForms[51]; // For Both Goe and LMU -- CHANGED to be the same every
	    } else{
	        $display_edit_history = "0";
	    }
	
	    $this->view->display_edit_history = $display_edit_history;
	
	    $this->view->displayvvhistory = $this->_clientForms[36];
	
	
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
	    */
	    $this->view->display_HL7_PV1_19 = (int)$patientdetails['ModulePrivileges'][184];
	    if ($patientdetails['ModulePrivileges'][184]) {
	
	        $PV1_19 = [];
	
	        $visitNumbers = PatientVisitnumberTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	        foreach ($visitNumbers as $oneVisitnumber) {
	
	            $fallFound = false;
	
	            foreach($pdata['patient_falls'] as $kFall => $oneFall) {
	
	                if ($oneFall['0'] == 'active') {
	
	                    if (strtotime($oneFall[1]) <= strtotime($oneVisitnumber['admit_date'])
	                        &&  (empty($oneFall[2]) || strtotime($oneFall[2]) >= strtotime($oneVisitnumber['admit_date'])) )
	                    {
	                        //visit in this period
	                        $PV1_19[$kFall][$oneVisitnumber['visit_number']] = date("d.m.Y H:i", strtotime($oneVisitnumber['admit_date']));
	                        $fallFound = true;
	                        break 1;
	                    }
	                }
	            }
	
	            if ( ! $fallFound) {
	                //break 1 not reached...assign this visit to the previous fall..
	                foreach($pdata['patient_falls'] as $kFall => $oneFall) {
	
	                    if ($oneFall['0'] == 'active') {
	
	                        if (strtotime($oneFall[1]) >= strtotime($oneVisitnumber['admit_date'])) {
	                            //add visit in this previous period
	                            $PV1_19[$kFall][$oneVisitnumber['visit_number']] = date("d.m.Y H:i", strtotime($oneVisitnumber['admit_date']));
	                            break 1;
	                        }
	                    }
	                }
	            }
	        }
	
	        $pdata["HL7_PV1_19"] = $PV1_19;
	    }
	
	
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
	        $this->view->{$key} = $val;
	
	    }
	
	
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
			
		// TODO-3126 Ancuta 27.04.2020
		$use_calculated_concentration_slots= false;
		$modules = new Modules();
		if($modules->checkModulePrivileges("227", $this->clientid)){
		    $use_calculated_concentration_slots = true;
		}
		// -- 
		
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
			    $max_length = 43;//TODO-3136 Ancuta 13.05.2020
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
					 $MP_S_M_r['td'] freetext dosage schedule values if not m,n,v,h ISPC-2573 Carmen 08.05.2020
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
							
					    // TODO-3126 Ancuta 27.04.2020
   						//$dosages_assoc = $this->_dosageIntervallAssociation($row_medication['dosage']);
					    if($use_calculated_concentration_slots){
					        if(!empty($row_medication['concentration']) && (isset($row_medication['dosage_concentration_value']) && !empty($row_medication['dosage_concentration_value'])) ){
        						$dosages_assoc = $this->_dosageIntervallAssociation($row_medication['dosage_concentration_value']);
					        } else {
        						$dosages_assoc = $this->_dosageIntervallAssociation($row_medication['dosage']);
					        }
					    } else{
    						$dosages_assoc = $this->_dosageIntervallAssociation($row_medication['dosage']);
					    }
					    //
						if ( ! empty($dosages_assoc['intervals']) && empty($row_medication['dosage_alt'])) {
						    if (! empty($dosages_assoc['intervals']['m'])) { $MP_S_M_r['m'] = str_replace( '.', ',',$dosages_assoc['intervals']['m'] ); }
						    if (! empty($dosages_assoc['intervals']['d'])) { $MP_S_M_r['d'] = str_replace( '.', ',',$dosages_assoc['intervals']['d'] ); }
						    if (! empty($dosages_assoc['intervals']['v'])) { $MP_S_M_r['v'] = str_replace( '.', ',',$dosages_assoc['intervals']['v'] ); }
						    if (! empty($dosages_assoc['intervals']['h'])) { $MP_S_M_r['h'] = str_replace( '.', ',',$dosages_assoc['intervals']['h'] ); }
						} else {
							//ISPC-2573 Carmen 08.05.2020
							//$MP_S_M_r['t'] = $this->translate('dosage not compatible');
							$MP_S_M_r['t'] = $this->translate('see additional line');
							$freerow = '';
							$comma = '';
							
							foreach($post['dosage_intervals'] [$medication_types] as $vdi)
							{
								if($row_medication['dosage'][$vdi] != '')
								{
									$freerow .= $comma . $vdi . ' Uhr: ' . $row_medication['dosage'][$vdi];
									$comma = ', ';
								}
							}
							//$MP_S_M_r['td'] = implode('-',$row_medication['dosage']);
							$MP_S_M_r['td'] = $freerow;
							//--
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
					//ISPC-2573 Carmen 03.06.2020
					/* if ( ! empty($post['dosage_intervals'] [$medication_types])) {
						$MP_S['X']['t'] .= "Dosierung: " . implode("; ", $post['dosage_intervals'] [$medication_types]);
					} */
					//--
						
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
			
			
			if($this->userid == "338xx"){
			    $options['view_xml_file']= '1';
			}
			
			//TODO-2819 Ancuta 16.01.2020
			ob_end_clean();
			//--
			$result = $DeKbv_Bmp2->generatePDF("Medikationsplan_Bundeseinheitlicher.pdf", $options);
	
	
		}

		
		
		/**
		 *
		 * @param unknown $chk
		 * @param unknown $post
		 * @param unknown $pdfname
		 * @param unknown $filename
		 * @throws Exception
		 * @deprecated - new version used now 2.6 ISPC-2551 ANcuta 31.03.2020
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
		                            //$MP_S_M_r['t'] = implode('-',$row_medication['dosage']);
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
		                                
		                                // 									$MP_S_M_r['t'] = $this->translate('dosage not compatible');
		                                //ISPC-2329 - do not list Not compatible, allow the dosage to be treated as text
		                                $MP_S_M_r['t'] = $row_medication['dosage'];
		                            }
		                        } else {
		                            if($row_medication['isschmerzpumpe'] == "1")
		                            {
		                                $MP_S_M_r['t'] = round($row_medication['dosage'], 2);
		                            }
		                            else
		                            {
		                                $MP_S_M_r['t'] = $row_medication['dosage'];
		                            }
		                        }
		                        
		                    }  else if(  empty($row_medication['dosage'])) {
		                        $MP_S_M_r['t'] = "";
		                        
		                    }
		                    
		                    //$MP_S_M_r['du']
		                    //TODO-2829 Ancuta 20.01.2020 - changed if condition - from: dosage_form to  unit
		                    if ( ! empty ($row_medication['unit'])) {
		                        $MP_S_M_r['dud'] = $row_medication['unit'];
		                    }
		                    
		                    // 						[type] => oral
		                    // 						concentration_full
		                    
		                    if ( ! empty($row_medication['comments'])) {
		                        $MP_S_M_r['i'] = $row_medication['comments'];
		                    }
		                    
		                    if ( ! empty($row_medication['indication'])) {
		                        // 							$MP_S_M_r['r'] = $row_medication['indication'];
		                        $MP_S_M_r['r'] = str_replace('"','',$row_medication['indication']);//TODO-2999 Ancuta 13.02.2020
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
		        
		        //TODO-2819 Ancuta 16.01.2020
		        ob_end_clean();
		        //--
		        $result = $DeKbv_Bmp2->generatePDF("Medikationsplan_Bundeseinheitlicher.pdf", $options);
		        
		        
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
 
		/**
		 * ISPC-2507 Lore 31.01.2020
		 */
		function requestchangesAction(){
		    
		    set_time_limit(0);
		    $this->_helper->layout->setLayout('layout_ajax');
		    
		    $clientid = $this->clientid;
		    $userid = $this->userid;
		    $decid = Pms_Uuid::decrypt($_GET['id']);
		    $ipid = Pms_CommonData::getIpId($decid);
		    
		    $request_id = 0 ; 
		    if( ! empty($_REQUEST['request_id'])){
		        $request_id = $_REQUEST['request_id'];
		    }
		    $this->view->request_id = $request_id;
		    
		    if($this->getRequest()->isPost())
		    {
		        $result = array();
		        $patient_medication_form = new Application_Form_Medication();
		        $patient_medication_isnutrition_form = new Application_Form_Nutrition();
		        $patient_medication_tr_form = new Application_Form_MedicationTreatmentCare();
		        $med_form = new Application_Form_PatientDrugPlan();
		     
		        $post_request_id = $_POST['request_id'];
		        if( empty ($post_request_id) || $post_request_id == 0){
		            //insert new reqest ONLY IF not other request is pending 
		            
		            // verifi again if other request
		            $pending_requests  = array();
		            $pending_requests = PharmaPatientDrugplanRequestsTable::find_patient_user_requests($ipid,$this->userid,'pending');
		            
		            if(empty($pending_requests)){
		                $cust_pdd = new PharmaPatientDrugplanRequests();
    		            $cust_pdd->ipid   = $ipid;
    		            $cust_pdd->user   = $userid;
    		            $cust_pdd->status = "pending";
    		            $cust_pdd->save();
    		            
    		            
    		            $post_request_id = $cust_pdd->id;
		            } else{
		                $post_request_id = $pending_requests[0]['id'];
		            }
		        }
		        
		        $a_post = $_POST;
		        $a_post['ipid'] = $ipid;
		        $a_post['request_id'] = $post_request_id;
		        $existing_dosage_array  = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
		        
		        foreach($_POST['original_medication_block'] as $type => $med_values)
		        {
		            if($type != "isschmerzpumpe")
		            { 
		                $post_data = $med_values;
		                $post_data['existing_dosage_array'] = $existing_dosage_array;  
		                $post_data['request_id'] = $a_post['request_id'];  
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
		                
		                // save medication changes
		                if($type == "deleted")
		                {
		                    //                             print_R($post_data); exit;
		                    $med_form->update_multiple_data_deletedmeds($post_data);
		                }
		                else
		                {
		                    //                         	print_r($post_data);
		                    $med_form->update_multiple_data_pharma_request($post_data,$ipid);
		                }
		                
		            }
		            
		        } // END FOREACH
		        
		        
		        foreach($_POST['medication_block'] as $type => $med_values)
		        {
		            if($type != "isschmerzpumpe")
		            { 
		                $post_data = $med_values;
		                $post_data['request_reason'] = $_POST['request_reason']; 
		                $post_data['request_comment'] = $_POST['request_comment'];
		                $post_data['existing_dosage_array'] = $existing_dosage_array; 
		                $post_data['request_id'] = $a_post['request_id'];  
		                
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
		                
		                // save medication changes
		                if($type == "deleted")
		                {
		                    //                             print_R($post_data); exit;
		                    $med_form->update_multiple_data_deletedmeds($post_data);
		                }
		                else
		                {
		                    //                         	print_r($post_data);
		                    $med_form->update_multiple_data_pharma_request($post_data,$ipid);
		                }
		            }
		            
		        } // END FOREACH
		        
		        $result['success'] = true;
		        $result['request_id'] = $post_request_id;
		        
		        echo json_encode($result);
		        exit;
		    }  
		    else 
    		    {
    		        
    		    if(empty($_GET['med_id'])){
    		        echo "0";
    		        exit;
    		    }
    		    
    		    // get info from js
    		    $record_id = $_GET['med_id'];   
    		    $medi_type = trim($_GET['set_type']);
    		    $this->view->medi_type = $medi_type;
    		    $med_count = $_GET['med_count'];
    		    $this->view->med_count = $med_count;
    		    
    		    if(!empty($request_id)){
    		        // get data from request id 
    		        
    		    } else{
    		        
    		        
    		        
    		        
    		    }
    		    
    		    
    		    /// Client setings for time_sheduled
    		    $modules = new Modules();
    		    $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
    		    if($individual_medication_time_m){
    		        $individual_medication_time = 1;
    		    }else {
    		        $individual_medication_time = 0;
    		    }
    		    $this->view->individual_medication_time = $individual_medication_time;
    		    
    		    if($individual_medication_time == "1")
    		    {
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
    		    }
    		    else
    		    {
    		        $timed_scheduled_medications = array("actual","isivmed"); // default
    		        $time_blocks = array("actual","isivmed"); // default
    		    }
    		    
    		    $this->view->timed_scheduled_medications = $timed_scheduled_medications;
    		    $this->view->js_timed_scheduled_medications = json_encode($timed_scheduled_medications);
    		    
    		    // Show interval options  in Actual and IVmed
    		    $actual_iv_scheduled_block = $modules->checkModulePrivileges("193", $clientid);
    		    if(!$actual_iv_scheduled_block){
    		        $this->view->allow_normal_scheduled = "0" ;
    		    } else {
    		        $this->view->allow_normal_scheduled = "1";
    		    }
    		    
    		    $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);
    		    
    		    $dosage_settings = array();
    		    $interval_array = array();
    		    $dosage_intervals = array();
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
    		    ///. Client setings for time_sheduled
    		    
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
    		    
    		    
    		    
    		    /* ================ MEDICATION :: CLIENT EXTRA ======================= */
    		    $client_medication_extra = array();
    		    //UNIT
    		    $medication_unit = MedicationUnit::client_medication_unit($clientid);
    		    
    		    foreach($medication_unit as $k=>$unit){
    		        $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
    		    }
    		    $this->view->js_med_unit = json_encode($client_medication_extra['unit']);
    		    
    		    
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
    		    foreach($medication_dosage_forms as $k=>$df){
    		        if($df['extra'] == 0 ){
    		            $client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
    		        }
    		        $client_medication_extra['dosage_form_custom'][$df['id']] = $df['dosage_form'];
    		        
    		    }
    		    $this->view->js_med_dosage_form = json_encode($client_medication_extra['dosage_form']);
    		    $this->view->js_med_dosage_form_custom = json_encode($client_medication_extra['dosage_form_custom']);
    		    
    		    
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
    		    
    		    
    		    /* MMI functionality*/
    		    if($modules->checkModulePrivileges("87", $clientid))
    		    {
    		        $this->view->show_mmi = "1";
    		    }
    		    else
    		    {
    		        $this->view->show_mmi = "0";
    		    }
    		    
    		    /*  textarea & comment-ul */
    		    $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
    		    $action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    		    $form_name = $controller.'/'.$action;
    		    
    		    $reason_of_clientLists = Pms_CommonData::getFormsTextareas($form_name);
    		    
    		    $reason_of_clientLists_codes = array_keys($reason_of_clientLists);
    		    $this->view->thematics = $reason_of_clientLists;//
    		    
    		    $formstextslist_model = new FormsTextsList();
    		    $standard_texts_arr =  $formstextslist_model->get_client_list($clientid,$form_name);
    		    
    		    foreach($standard_texts_arr as $k=>$st){
    		        $standard_texts[$st['field_name']][] = $st['field_value'];
    		    }
    		    $this->view->form_name = $form_name;
    		    $this->view->standard_texts = $standard_texts;
    		    
    		    
    		    
    		    
    		    
    		    // get patient drugplan
    		        
                $m_medication = new PatientDrugPlan();
                $all_patient_medication = $m_medication->getMedicationPlanAll($decid);
                foreach($all_patient_medication as $k=>$medication_data)
                {
                    if($medication_data['isbedarfs'] == "1")
                    {
                        $all_patient_medication[$k]['medication_type'] = 'isbedarfs';
                    }
                    elseif($medication_data['isivmed'] == "1")
                    {
                        $all_patient_medication[$k]['medication_type'] = 'isivmed';
                    }
                    elseif($medication_data['isschmerzpumpe'] == "1")
                    {
                        $all_patient_medication[$k]['medication_type'] = 'isschmerzpumpe';
                    }
                    elseif($medication_data['treatment_care'] == "1")
                    {
                        $all_patient_medication[$k]['medication_type'] = 'treatment_care';
                    }
                    elseif($medication_data['isnutrition'] == "1")
                    {
                        $all_patient_medication[$k]['medication_type'] = 'isnutrition';
                    }
                    elseif($medication_data['scheduled'] == "1")
                    {
                        $all_patient_medication[$k]['medication_type'] = 'scheduled';
                    }
                    elseif($medication_data['iscrisis'] == "1")
                    {
                        $all_patient_medication[$k]['medication_type'] = 'iscrisis';
                    }
                    elseif($medication_data['isintubated'] == "1") // ISPC-2176 16.04.2018 @Ancuta
                    {
                        $all_patient_medication[$k]['medication_type'] = 'isintubated';
                    }
                    else
                    {
                        $all_patient_medication[$k]['medication_type'] = 'actual';
                    }
                }
                
                
                $drugplan_full_details = array_filter($all_patient_medication, function($item) use ($record_id) {
    		        return $item['id'] == $record_id;
    		    }); 
    		    
                $pdp_detail_arrs  = array_values($drugplan_full_details);
                $pdp_detail_arr = $pdp_detail_arrs[0];
                
                
                $old_dosage_arr = array();
                
                // Get medication master details - from medication_master_id;
                $medication_master_array  =array();
    		    if($pdp_detail_arr['medication_type'] == 'treatment_care' && !empty($pdp_detail_arr['medication_master_id']) ){
    		        $medtr = new MedicationTreatmentCare();
    		        $medication_master_array = $medtr->getMedicationTreatmentCareById($pdp_detail_arr['medication_master_id']);
    		    } else if($pdp_detail_arr['medication_type'] == 'isnutrition' && !empty($pdp_detail_arr['medication_master_id']) ){
    		        $mednutrition = new Nutrition();
    		        $medication_master_array= $mednutrition->getMedicationNutritionById($pdp_detail_arr['medication_master_id']);
    		    } else{
    		        $med = new Medication();
    		        $medication_master_array= $med->getMedicationById($pdp_detail_arr['medication_master_id'], true); //changed to this so I can fetch pzn, etc..
    		    }
    		    $master_medi_array = array();
    		    foreach($medication_master_array as $k=>$medication_master_data){
    		        $master_medi_array[$medication_master_data['id']] = $medication_master_data;
    		    }
    		   
    		    //Get extra data 
    		    $pdp_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$record_id);
    		    
    		    $existing_dosage_array  = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
    		    
    		    $medications_array = array();
    		    $medications_array[$medi_type][0] = $pdp_detail_arr;
    		    $medications_array[$medi_type][0]['medication'] = $master_medi_array[$pdp_detail_arr['medication_master_id']]['name'];
    		    if($existing_dosage_array[$record_id]){
        		    $medications_array[$medi_type][0]['dosage'] = $existing_dosage_array[$record_id];
    		    } else{
    		        $medications_array[$medi_type][0]['dosage'] = $pdp_detail_arr['dosage'];
    		        
    		    }
    		    
    		    
    		    
    		    // #################################################################
    		    // DOSAGE
    		    // #################################################################
    		    $medications_array[$medi_type ][0]['old_dosage'] = $pdp_detail_arr['dosage'];
    		    // 	                if(!in_array($medi_type,array("actual","isivmed")))
    		    
    		    if(!in_array($medi_type,$timed_scheduled_medications))
    		    {
    		        $medications_array[$medi_type ][0]['dosage']= $pdp_detail_arr['dosage'];
    		    }
    		    else
    		    {
    		        // first get new dosage
    		        if(!empty($existing_dosage_array[$pdp_detail_arr['id']]))
    		        {
    		            $medications_array[$medi_type ][0]['dosage'] = $existing_dosage_array[$pdp_detail_arr['id']];
    		            
    		        }
    		        else if(strlen($pdp_detail_arr['dosage'])> 0 )
    		        {
    		            $old_dosage_arr[$pdp_detail_arr['id']] = array();
    		            $medications_array[$medi_type ][0]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018
    		            
    		            if(strpos($pdp_detail_arr['dosage'],"-")){
    		                $old_dosage_arr[$pdp_detail_arr['id']] = explode("-",$pdp_detail_arr['dosage']);
    		                
    		                if(count($old_dosage_arr[$pdp_detail_arr['id']]) <= count($dosage_settings[$medi_type])){
    		                    //  create array from old
    		                    for($x = 0; $x < count($dosage_settings[$medi_type]); $x++)
    		                    {
    		                        $medications_array[$medi_type ][0]['dosage'][$dosage_settings[$medi_type][$x]] = $old_dosage_arr[$pdp_detail_arr['id']][$x];
    		                    }
    		                }
    		                else
    		                {
    		                    $medications_array[$medi_type ][0]['dosage'][$dosage_settings[$medi_type][0]] = "! ALTE DOSIERUNG!";
    		                    $medications_array[$medi_type ][0]['dosage'][$dosage_settings[$medi_type][1]] = $pdp_detail_arr['dosage'];
    		                    for($x = 2; $x < count($dosage_settings[$medi_type]); $x++)
    		                    {
    		                        $medications_array[$medi_type ][0]['dosage'][$dosage_settings[$medi_type][$x]] = "";
    		                    }
    		                }
    		            }
    		            else
    		            {
    		                $medications_array[$medi_type ][0]['dosage'][$dosage_settings[$medi_type][0]] = "! ALTE DOSIERUNG!";
    		                $medications_array[$medi_type ][0]['dosage'][$dosage_settings[$medi_type][1]] = $pdp_detail_arr['dosage'];
    		                
    		                for($x = 2; $x < count($dosage_settings[$medi_type]); $x++)
    		                {
    		                    $medications_array[$medi_type ][0]['dosage'][$dosage_settings[$medi_type][$x]] = "";
    		                }
    		            }
    		        }
    		        else
    		        {
    		            $medications_array[$medi_type ][0]['dosage'] =  "";
    		        }
    		    }
    		    
    		    
    		    
    		    $medications_array[$medi_type][0]['MedicationMaster'] = $master_medi_array[$pdp_detail_arr['medication_master_id']];
    		    $medications_array[$medi_type][0]['replace_with'] = 'none';
    		    $medications_array[$medi_type][0]['old_dosage']   = $pdp_detail_arr['dosage'];
    		    
    		    if(!empty($pdp_extra[$record_id])){
        		    $medications_array[$medi_type][0] = $medications_array[$medi_type][0] + $pdp_extra[$record_id];
    		    }
    		    $medications_array[$medi_type][0]['indication'] = $pdp_extra[$record_id]['indication']['name'];
    		    $medications_array[$medi_type][0]['indication_color'] = $pdp_extra[$record_id]['indication']['color'];
    		    $medications_array[$medi_type][0]['escalation_label'] =  $pdp_extra[$record_id]['escalation'];
    		    $medications_array[$medi_type][0]['escalation'] =  $pdp_extra[$record_id]['escalation_id'];
    		    
    		    if(!empty($medications_array[$medi_type][0]['concentration'])){
    		        foreach($existing_dosage_array[$record_id] as $k=>$val){	 
    		            
    		            $dosage_value = str_replace(',','.',$val);
    		            $concentration_value = str_replace(',','.',$medications_array[$medi_type][0]['concentration']);
    		            if(!empty($val) && strlen($val)> 0 )
    		            {
    		                $result = $dosage_value / $concentration_value;
    		                if(!is_int ($result ))
    		                {
    		                    $result = round($result,4);
    		                    $medications_array[$medi_type][0]['dosage_concentration'][$k] =  rtrim(rtrim(number_format(  $result ,3,",","."), "0"), ",");
    		                }
    		                else
    		                {
    		                    $medications_array[$medi_type][0]['dosage_concentration'][$k] =  $result;
    		                }
    		            } 
    		            else 
    		            {
    		                $medications_array[$medi_type][0]['dosage_concentration'][$k] =  "";
    		            }
    		        }
    		    }
    
    		    $medications_array = Pms_CommonData::clear_pdf_data($medications_array);
    		    $this->view->medication = $medications_array;
    		    //. have medication detail
    		    
    		}
    }
    
    function sharedAction(){
    	//TODO-2982 Carmen 18.03.2020 new function; the old one is shared_18032020
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
    			//TODO-2982 Carmen 19.03.2020
    			if($clientid != $details['EpidIpidMapping']['clientid'])
    			{
    				$sourceclid = $details['EpidIpidMapping']['clientid'];
    			}
    			//--
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
    		
    		$drgextra = new PatientDrugPlanExtra();
    		$drgextra_pat = $drgextra->get_patient_drugplan_extra($ipid,$clientid);    
    		//var_dump($drgextra_pat); exit;	 
    		IF($_REQUEST['dbgs'] == "1" ){
    			print_R($all_drug_details);
    		}

    		//TODO-2982 Carmen 19.03.2020
    		//get time scheme for the patients
    		$medopt = new MedicationOptions();
    		if($modules->checkModulePrivileges("141", $sourceclid))
    		{
    			
    			$medopt_det_source = $medopt->client_saved_medication_options($sourceclid);
    			
    		}
    		else
    		{    			
    			$medopt_det_source = array('actual' => array('time_schedule' => '1'), 'isivmed' => array('time_schedule' => '1'));
    		}
    		
    		if($modules->checkModulePrivileges("141", $clientid))
    		{
    			$medopt_det_target = $medopt->client_saved_medication_options($clientid);
    		}
    		else
    		{
    			$medopt_det_target = array('actual' => array('time_schedule' => '1'), 'isivmed' => array('time_schedule' => '1'));
    		}

    		foreach($linked_ipids as $lipid)
    		{
    			 
    			if($lipid == $ipid)
    			{
    				$patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($lipid,$clientid,array_keys($medopt_det_target));
    			}
    			else 
    			{
    				$patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($lipid,$sourceclid,array_keys($medopt_det_source));
    				$sourceipid = $lipid;
    			}
    			 
    			if($patient_time_scheme['patient']){
    				foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
    				{
    					if($med_type != "new"){
    						$set = 0;
    						foreach($dos_data  as $int_id=>$int_data)
    						{
    							if(in_array($med_type,$patient_time_scheme['patient']['new'])){
    
    								$interval_array['interval'][$lipid][$med_type][$int_id]['time'] = $int_data;
    								$interval_array['interval'][$lipid][$med_type][$int_id]['custom'] = '1';
    
    								/* $dosage_settings[$med_type][$set] = $int_data;
    								 $set++;
    								  
    								 $dosage_intervals[$med_type][$int_data] = $int_data; */
    							}
    							else
    							{
    
    
    								$interval_array['interval'][$lipid][$med_type][$int_id]['time'] = $int_data;
    								$interval_array['interval'][$lipid][$med_type][$int_id]['custom'] = '0';
    								$interval_array['interval'][$lipid][$med_type][$int_id]['interval_id'] = $int_id;
    
    								/* $dosage_settings[$med_type][$set] = $int_data;
    								 $set++;
    								  
    								 $dosage_intervals[$med_type][$int_data] = $int_data; */
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
    
    						$interval_array['interval'][$lipid][$med_type][$inf]['time'] = $int_data;
    						$interval_array['interval'][$lipid][$med_type][$inf]['custom'] = '1';
    						/* $dosage_settings[$med_type][$setc] = $int_data;
    						 $setc++; */
    						$inf++;
    
    						/* $dosage_intervals[$med_type][$int_data] = $int_data; */
    					}
    				}
    			}
    			 
    		}
    		
    		$patient_interval_array = $interval_array['interval'][$ipid];
    		$source_interval_array = $interval_array['interval'][$sourceipid];
    		//var_dump($patient_interval_array); exit;
    		 
    		$dif_time_scheme = array();
    		foreach($patient_interval_array as $med_type => $int_med_type)
    		{
    			if($med_type == 'all') continue;
    			
    			if($medopt_det_source[$med_type]['time_schedule'] == '0' && $medopt_det_target[$med_type]['time_schedule'] == '0')
    			{
    				$dif_time_scheme[$med_type] = false;
    			}
    			else 
    			{
    			if(array_key_exists($med_type, $source_interval_array))
    			{
    				$pat_tot_med_time_int = count($int_med_type);
    				$source_tot_med_time_int = count($source_interval_array[$med_type]);
    
    				if($pat_tot_med_time_int != $source_tot_med_time_int)
    				{
    					$dif_time_scheme[$med_type] = true;
    				}
    				else
    				{
    					foreach($int_med_type as $k_time => $int_time)
    					{
    						if(!in_array($int_time['time'], array_column($source_interval_array[$med_type], 'time')))
    						{
    							$dif_time_scheme[$med_type] = true;
    							break;
    						}
    					}
    				}
    			}
    			else
    			{
    				$dif_time_scheme[$med_type] = true;
    			}
    			}
    		}    		 
    		//--
    		 
    		
    		$listed_meds = array($listed_meds);
    		foreach($all_drug_details as $k=>&$data){
    			
    			if($data['isbedarfs'] =="1") {
    				$type = "isbedarfs";
    				if(!$medications_array[$data['ipid']][$type]['dif_time_scheme'])
    				{
    					$medications_array[$data['ipid']][$type]['dif_time_scheme'] = $dif_time_scheme[$type];
    				}
    				if($medopt_det_source[$type]['time_schedule'] == '1')
    				{
    					if(!$medications_array[$data['ipid']][$type]['interval'])
    					{
    						$medications_array[$data['ipid']][$type]['interval'] = $interval_array['interval'][$data['ipid']][$type];    						
    						$interval_arr_isbed = array_values($interval_array['interval'][$data['ipid']][$type]);    							
    					}
    					if(array_key_exists($type, $patient_interval_array) && !$medications_array[$data['ipid']][$type]['target_interval'])
    					{
    						$medications_array[$data['ipid']][$type]['target_interval'] = array_column($patient_interval_array[$type], 'time');
    					}
    					if( empty($drugs_dosage_array[$data['ipid']][$data['id']])){
    						if(strpos($data['dosage'],"-")){
    							$drugs_dosage_array[$data['ipid']][$data['id']] = explode('-', $data['dosage']);
    							if(count($drugs_dosage_array[$data['ipid']][$data['id']]) <= count($interval_arr_actual)){
    								foreach($interval_arr_isbed as $kt => $vt)
    								{
    									$data['newdosage'][$vt['time']] = $drugs_dosage_array[$data['ipid']][$data['id']][$kt];
    								}
    							}
    							else
    							{
    								foreach($interval_arr_isbed as $kt => $vt)
    								{
    									if($kt == 0)
    									{
    										$data['newdosage'][$vt['time']] = '';
    									}
    									elseif($kt == 1)
    									{
    										$data['newdosage'][$vt['time']] = $data['dosage'];
    									}
    									else
    									{
    										$data['newdosage'][$vt['time']] = '';
    									}
    				
    								}
    							}
    						}
    						else
    						{
    							foreach($interval_arr_isbed as $kt => $vt)
    							{
    								if($kt == 0)
    								{
    									$data['newdosage'][$vt['time']] = '';
    								}
    								elseif($kt == 1)
    								{
    									$data['newdosage'][$vt['time']] = $data['dosage'];
    								}
    								else
    								{
    									$data['newdosage'][$vt['time']] = '';
    								}
    				
    							}
    						}
    					}
    					else
    					{
    						foreach($interval_arr_isbed as $kt => $vt)
    						{
    							if($drugs_dosage_array[$data['ipid']][$data['id']][$vt['time']])
    							{
    								$data['newdosage'][$vt['time']] = $drugs_dosage_array[$data['ipid']][$data['id']][$vt['time']];
    							}
    						}
    					}
    				}
    				else
    				{
    					//de verificat daca sa ramana asa
    					if( ! empty($drugs_dosage_array[$data['ipid']][$data['id']])){
    						$drugs_dosage_arr = array();
    						foreach($drugs_dosage_array[$data['ipid']][$data['id']] as $kd => $vd)
    						{
    							$drugs_dosage_arr[] = $vd['value'];
    						}
    				
    						$data['newdosage'] = implode("-", $drugs_dosage_arr);
    				
    					}
    					$data['newdosage'] = $data['dosage'];
    					
    				}
    				$data['dosage'] = $data['newdosage'];
    				unset($data['newdosage']);
    			} elseif ($data['isivmed'] == "1") {
    				$type = "isivmed";
    				if(!$medications_array[$data['ipid']][$type]['dif_time_scheme'])
    				{
    					$medications_array[$data['ipid']][$type]['dif_time_scheme'] = $dif_time_scheme[$type];
    				}
    				if($medopt_det_source[$type]['time_schedule'] == '1')
    				{
    					if(!$medications_array[$data['ipid']][$type]['interval'])
    					{
    						$medications_array[$data['ipid']][$type]['interval'] = $interval_array['interval'][$data['ipid']][$type];
    						$interval_arr_isiv = array_values($interval_array['interval'][$data['ipid']][$type]);
    					}
    					if(array_key_exists($type, $patient_interval_array) && !$medications_array[$data['ipid']][$type]['target_interval'])
    					{
    						$medications_array[$data['ipid']][$type]['target_interval'] = array_column($patient_interval_array[$type], 'time');
    					}
    					if( empty($drugs_dosage_array[$data['ipid']][$data['id']])){
    						if(strpos($data['dosage'],"-")){
    							$drugs_dosage_array[$data['ipid']][$data['id']] = explode('-', $data['dosage']);
    							if(count($drugs_dosage_array[$data['ipid']][$data['id']]) <= count($interval_arr_actual)){
    								foreach($interval_arr_isiv as $kt => $vt)
    								{
    									$data['newdosage'][$vt['time']] = $drugs_dosage_array[$data['ipid']][$data['id']][$kt];
    								}
    							}
    							else
    							{
    								foreach($interval_arr_isiv as $kt => $vt)
    								{
    									if($kt == 0)
    									{
    										$data['newdosage'][$vt['time']] = '';
    									}
    									elseif($kt == 1)
    									{
    										$data['newdosage'][$vt['time']] = $data['dosage'];
    									}
    									else
    									{
    										$data['newdosage'][$vt['time']] = '';
    									}
    				
    								}
    							}
    						}
    						else
    						{
    							foreach($interval_arr_isiv as $kt => $vt)
    							{
    								if($kt == 0)
    								{
    									$data['newdosage'][$vt['time']] = '';
    								}
    								elseif($kt == 1)
    								{
    									$data['newdosage'][$vt['time']] = $data['dosage'];
    								}
    								else
    								{
    									$data['newdosage'][$vt['time']] = '';
    								}
    				
    							}
    						}
    					}
    					else
    					{
    						foreach($interval_arr_isiv as $kt => $vt)
    						{
    							if($drugs_dosage_array[$data['ipid']][$data['id']][$vt['time']])
    							{
    								$data['newdosage'][$vt['time']] = $drugs_dosage_array[$data['ipid']][$data['id']][$vt['time']];
    							}
    						}
    					}
    				}
    				else
    				{
    					//de verificat daca sa ramana asa
    					if( ! empty($drugs_dosage_array[$data['ipid']][$data['id']])){
    						$drugs_dosage_arr = array();
    						foreach($drugs_dosage_array[$data['ipid']][$data['id']] as $kd => $vd)
    						{
    							$drugs_dosage_arr[] = $vd['value'];
    						}
    				
    						$data['newdosage'] = implode("-", $drugs_dosage_arr);
    				
    					}
    					$data['newdosage'] = $data['dosage'];
    						
    				}
    				$data['dosage'] = $data['newdosage'];
    				unset($data['newdosage']);
    			} elseif ($data['isschmerzpumpe'] == "1") {
    				$type = "isschmerzpumpe";
    				$cocktail_ids[] = $data['cocktailid'];
    			} elseif ($data['treatment_care'] == "1") {
    				$type = "treatment_care";
    				$treatmen_care_med_ids[] = $data['master_medication_id'];
    			} elseif ($data['isnutrition'] == "1") {
    				$type = "isnutrition";
    				if(!$medications_array[$data['ipid']][$type]['dif_time_scheme'])
    				{
    					$medications_array[$data['ipid']][$type]['dif_time_scheme'] = $dif_time_scheme[$type];
    				}
    				$nutrition_med_ids[] = $data['master_medication_id'];
    				if($medopt_det_source[$type]['time_schedule'] == '1')
    				{
    					if(!$medications_array[$data['ipid']][$type]['interval'])
    					{
    						$medications_array[$data['ipid']][$type]['interval'] = $interval_array['interval'][$data['ipid']][$type];
    						$interval_arr_nutr = array_values($interval_array['interval'][$data['ipid']][$type]);
    					}
    					if(array_key_exists($type, $patient_interval_array) && !$medications_array[$data['ipid']][$type]['target_interval'])
    					{
    						$medications_array[$data['ipid']][$type]['target_interval'] = array_column($patient_interval_array[$type], 'time');
    					}
    					if( empty($drugs_dosage_array[$data['ipid']][$data['id']])){
    						if(strpos($data['dosage'],"-")){
    							$drugs_dosage_array[$data['ipid']][$data['id']] = explode('-', $data['dosage']);
    							if(count($drugs_dosage_array[$data['ipid']][$data['id']]) <= count($interval_arr_actual)){
    								foreach($interval_arr_nutr as $kt => $vt)
    								{
    									$data['newdosage'][$vt['time']] = $drugs_dosage_array[$data['ipid']][$data['id']][$kt];
    								}
    							}
    							else
    							{
    								foreach($interval_arr_nutr as $kt => $vt)
    								{
    									if($kt == 0)
    									{
    										$data['newdosage'][$vt['time']] = '';
    									}
    									elseif($kt == 1)
    									{
    										$data['newdosage'][$vt['time']] = $data['dosage'];
    									}
    									else
    									{
    										$data['newdosage'][$vt['time']] = '';
    									}
    				
    								}
    							}
    						}
    						else
    						{
    							foreach($interval_arr_nutr as $kt => $vt)
    							{
    								if($kt == 0)
    								{
    									$data['newdosage'][$vt['time']] = '';
    								}
    								elseif($kt == 1)
    								{
    									$data['newdosage'][$vt['time']] = $data['dosage'];
    								}
    								else
    								{
    									$data['newdosage'][$vt['time']] = '';
    								}
    				
    							}
    						}
    					}
    					else
    					{
    						foreach($interval_arr_nutr as $kt => $vt)
    						{
    							if($drugs_dosage_array[$data['ipid']][$data['id']][$vt['time']])
    							{
    								$data['newdosage'][$vt['time']] = $drugs_dosage_array[$data['ipid']][$data['id']][$vt['time']];
    							}
    						}
    					}
    				}
    				else
    				{
    					//de verificat daca sa ramana asa
    					if( ! empty($drugs_dosage_array[$data['ipid']][$data['id']])){
    						$drugs_dosage_arr = array();
    						foreach($drugs_dosage_array[$data['ipid']][$data['id']] as $kd => $vd)
    						{
    							$drugs_dosage_arr[] = $vd['value'];
    						}
    				
    						$data['newdosage'] = implode("-", $drugs_dosage_arr);
    				
    					}
    					$data['newdosage'] = $data['dosage'];
    				
    				}
    				$data['dosage'] = $data['newdosage'];
    				unset($data['newdosage']);
    			} elseif ($data['scheduled'] == "1") {
    				$type = "scheduled";
    			} else {
    				$type = "actual";
    				if(!$medications_array[$data['ipid']][$type]['dif_time_scheme'])
    				{
    					$medications_array[$data['ipid']][$type]['dif_time_scheme'] = $dif_time_scheme[$type];
    				}
    				if($medopt_det_source[$type]['time_schedule'] == '1')
    				{
    					if(!$medications_array[$data['ipid']][$type]['interval'])
    					{
    						$medications_array[$data['ipid']][$type]['interval'] = $interval_array['interval'][$data['ipid']][$type];    						
    						$interval_arr_actual = array_values($interval_array['interval'][$data['ipid']][$type]);    							
    					}
    					if(array_key_exists($type, $patient_interval_array) && !$medications_array[$data['ipid']][$type]['target_interval'])
    					{
    						$medications_array[$data['ipid']][$type]['target_interval'] = array_column($patient_interval_array[$type], 'time');
    					}
    					if( empty($drugs_dosage_array[$data['ipid']][$data['id']])){
    						if(strpos($data['dosage'],"-")){
    							$drugs_dosage_array[$data['ipid']][$data['id']] = explode('-', $data['dosage']);
    							if(count($drugs_dosage_array[$data['ipid']][$data['id']]) <= count($interval_arr_actual)){
    								foreach($interval_arr_actual as $kt => $vt)
    								{
    									$data['newdosage'][$vt['time']] = $drugs_dosage_array[$data['ipid']][$data['id']][$kt];
    								}
    							}
    							else
    							{
    								foreach($interval_arr_actual as $kt => $vt)
    								{
    									if($kt == 0)
    									{
    										$data['newdosage'][$vt['time']] = '';
    									}
    									elseif($kt == 1)
    									{
    										$data['newdosage'][$vt['time']] = $data['dosage'];
    									}
    									else
    									{
    										$data['newdosage'][$vt['time']] = '';
    									}
    				
    								}
    							}
    						}
    						else
    						{
    							foreach($interval_arr_actual as $kt => $vt)
    							{
    								if($kt == 0)
    								{
    									$data['newdosage'][$vt['time']] = '';
    								}
    								elseif($kt == 1)
    								{
    									$data['newdosage'][$vt['time']] = $data['dosage'];
    								}
    								else
    								{
    									$data['newdosage'][$vt['time']] = '';
    								}
    				
    							}
    						}
    					}
    					else
    					{
    						foreach($interval_arr_actual as $kt => $vt)
    						{
    							if($drugs_dosage_array[$data['ipid']][$data['id']][$vt['time']])
    							{
    								$data['newdosage'][$vt['time']] = $drugs_dosage_array[$data['ipid']][$data['id']][$vt['time']];
    							}
    						}
    					}
    				}
    				else
    				{
    					//de verificat daca sa ramana asa
    					if( ! empty($drugs_dosage_array[$data['ipid']][$data['id']])){
    						$drugs_dosage_arr = array();
    						foreach($drugs_dosage_array[$data['ipid']][$data['id']] as $kd => $vd)
    						{
    							$drugs_dosage_arr[] = $vd['value'];
    						}
    				
    						$data['newdosage'] = implode("-", $drugs_dosage_arr);
    				
    					}
    					$data['newdosage'] = $data['dosage'];
    					
    				}
    				$data['dosage'] = $data['newdosage'];
    				unset($data['newdosage']);
    			}
    
    			$medications_array[$data['ipid']][$type][$k] = $data;
 //print_r($medications_array);
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
    							//TODO-2982 Lore 09.03.2020
    							$medications_array[$data['ipid']][$type][$k]['hidd_medication'] = '';
    							// --
    
    						} else{
    							$medications_array[$data['ipid']][$type][$k]['source_changed'] = "0";
    						}
    
    					}
    					else{
    						//TODO-2982 Carmen 20.03.2020
    						$dosage_for_compare = '';
    						$separator = '';
    						if($medopt_det_source[$type]['time_schedule'] == '1')
    						{
    							foreach($data['dosage'] as $ki => $vi)
    							{
    								if(empty($source_interval_array_diff[$type]))
    								{
	    								if($vi != '' && $vi != '! ALTE DOSIERUNG!')
	    								{
	    									
		    								$dosage_for_compare .= $separator. $vi;
		    								$separator = '-';
	    								}
    								}
    								else 
    								{
    									$dosage_for_compare = 'notmatched';
    									break;
    								}
    							}
    						}
    						else
    						{
    							$dosage_for_compare = $data['dosage'];
    						}
    						
    						$dosage_for_compare = rtrim($dosage_for_compare, " -");
    						$source_corelation[$data['id']]['dosage'] = rtrim($source_corelation[$data['id']]['dosage'], " -");
    						//var_dump($source_corelation[$data['id']]['dosage']);
    						//var_dump($dosage_for_compare);
    						//if($data['medication'] != $source_corelation[$data['id']]['medication'] || $data['dosage'] != $source_corelation[$data['id']]['dosage']){
    						if($data['medication'] != $source_corelation[$data['id']]['medication'] || $dosage_for_compare != $source_corelation[$data['id']]['dosage']){
    						//--
    							$medications_array[$data['ipid']][$type][$k]['source_changed'] = "1";
    							//TODO-2982 Lore 09.03.2020
    							if($data['medication'] != $source_corelation[$data['id']]['medication']){
    								$medications_array[$data['ipid']][$type][$k]['hidd_medication'] = '';
    							}
    							// --
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
    							//TODO-2982 Lore 09.03.2020
    							$medications_array[$data['ipid']][$type][$k]['hidd_medication'] = '';
    							// --
    						} else{
    							$medications_array[$data['ipid']][$type][$k]['source_changed'] = "0";
    						}
    
    					}
    					else
    					{
    						//TODO-2982 Carmen 20.03.2020
    					$dosage_for_compare = '';
    						$separator = '';
    						if($medopt_det_source[$type]['time_schedule'] == '1')
    						{
    							foreach($data['dosage'] as $ki => $vi)
    							{
    								if(empty($source_interval_array_diff[$type]))
    								{
	    								if($vi != '' && $vi != '! ALTE DOSIERUNG!')
	    								{
	    									
		    								$dosage_for_compare .= $separator. $vi;
		    								$separator = '-';
	    								}
    								}
    								else
    								{
    									$dosage_for_compare = 'notmatched';
    									break;
    								}
    							}
    						}
    						else
    						{
    							$dosage_for_compare = $data['dosage'];
    						}
    						
    						$dosage_for_compare = rtrim($dosage_for_compare, " -");
    						$own_corelation[$data['source_drugplan_id']]['dosage'] = rtrim($own_corelation[$data['source_drugplan_id']]['dosage'], " -");
    						//if($data['medication'] != $own_corelation[$data['source_drugplan_id']]['medication'] || $data['dosage'] != $own_corelation[$data['source_drugplan_id']]['dosage']){
    						if($data['medication'] != $own_corelation[$data['source_drugplan_id']]['medication'] || $dosage_for_compare != $own_corelation[$data['source_drugplan_id']]['dosage']){
    						//--	
    							$medications_array[$data['ipid']][$type][$k]['source_changed'] = "1";
    							//TODO-2982 Lore 09.03.2020
    							if($data['medication'] != $own_corelation[$data['source_drugplan_id']]['medication']){
    								$medications_array[$data['ipid']][$type][$k]['hidd_medication'] = '';
    							}
    							// --
    
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
   //exit;
    
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
    
    //print_r($_POST['drugs']); exit;
    			foreach($_POST['drugs'] as $source_pat_id => $post_drugdetails_d){
    
    				foreach($post_drugdetails_d as $type => $med_valuesdd){
    					$cnt = 0;
    					foreach($med_valuesdd['use'] as $k=>$drigs){
    						if($type !="isschmerzpumpe"){
    							$_POST['medication_block'][$source_pat_id][$type]['medication'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['medication'][$k];
    							$_POST['medication_block'][$source_pat_id][$type]['hidd_medication'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['hidd_medication'][$k];
    							$_POST['medication_block'][$source_pat_id][$type]['drid'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['drid'][$k];
    							$_POST['medication_block'][$source_pat_id][$type]['edited'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['edited'][$k];
    							$_POST['medication_block'][$source_pat_id][$type]['source_ipid'][$cnt] = $pid2iipid[$_POST['drugs'][$source_pat_id][$type]['source_patient'][$k]];
    							$_POST['medication_block'][$source_pat_id][$type]['source_drugplan_id'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['source_drugplan_id'][$k];
    							//TODO-2982 Carmen 19.03.2020
    							if($med_valuesdd['dif_time_scheme'] == '1')
    							{

    								$dosage_string = 'Dosierungen aus anderem Team: ';
    								if($med_valuesdd['interval'] == '1')
    								{
    									foreach($med_valuesdd['dosage'][$k] as $ki => $vi)
    									{
    										if($vi == '')
    										{
    											$vi = 0;
    										}
    										$dosage_string .= $vi . ' um ' . $ki . ', '; 
    									}
    									
    									//$_POST['medication_block'][$source_pat_id][$type]['dosage'][$cnt] = '';
    									//$_POST['medication_block'][$source_pat_id][$type]['comments'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['comments'][$k] . ' ' . $dosage_string;
    								}
    								else 
    								{
    									//$_POST['medication_block'][$source_pat_id][$type]['dosage'][$cnt] = '';
    									//$_POST['medication_block'][$source_pat_id][$type]['comments'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['comments'][$k] . ' ' . $_POST['drugs'][$source_pat_id][$type]['dosage'][$k];
    									$dosage_string .= $_POST['drugs'][$source_pat_id][$type]['dosage'][$k];
    								}
    								$_POST['medication_block'][$source_pat_id][$type]['comments'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['comments'][$k] . ' ' . $dosage_string;
    							}
    							else 
    							{
    								if($med_valuesdd['interval'] == '1')
    								{
    									foreach($med_valuesdd['dosage'][$k] as $ki => $vi)
    									{
    										$_POST['medication_block'][$source_pat_id][$type]['dosage'][$cnt][$ki] = $vi;
    										
    										
    									}
    								}
    								else 
    								{
    									$_POST['medication_block'][$source_pat_id][$type]['dosage'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['dosage'][$k];
    								}
    								$_POST['medication_block'][$source_pat_id][$type]['comments'][$cnt] = $_POST['drugs'][$source_pat_id][$type]['comments'][$k];
    							}
    							
    							//TODO-2982 Carmen 23.03.2020 - add the medi info from patient
    							$_POST['medication_block'][$source_pat_id][$type]['dosage_interval'][$cnt] = $own_corelation[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['dosage_interval'];
    							$_POST['medication_block'][$source_pat_id][$type]['verordnetvon'][$cnt] = $own_corelation[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['verordnetvon'];
    							$_POST['medication_block'][$source_pat_id][$type]['has_interval'][$cnt] = $own_corelation[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['has_interval'];
    							$_POST['medication_block'][$source_pat_id][$type]['days_interval'][$cnt] = $own_corelation[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['days_interval'];
    							$_POST['medication_block'][$source_pat_id][$type]['administration_date'][$cnt] = $own_corelation[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['administration_date'];
    							$_POST['medication_block'][$source_pat_id][$type]['dosage_product'][$cnt] = $own_corelation[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['dosage_product'];
    							$_POST['medication_block'][$source_pat_id][$type]['days_interval_technical'][$cnt] = $own_corelation[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['days_interval_technical'];
    							
    							//extra
    							$_POST['medication_block'][$source_pat_id][$type]['indication'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['indication_id'];
    							$_POST['medication_block'][$source_pat_id][$type]['unit'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['unit_id'];
    							$_POST['medication_block'][$source_pat_id][$type]['dosage_form'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['dosage_form_id'];
    							$_POST['medication_block'][$source_pat_id][$type]['type'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['type_id'];
    							$_POST['medication_block'][$source_pat_id][$type]['concentration'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['concentration'];
    							$_POST['medication_block'][$source_pat_id][$type]['escalation'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['escalation_id'];
    							$_POST['medication_block'][$source_pat_id][$type]['packaging'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['packaging'];
    							$_POST['medication_block'][$source_pat_id][$type]['kcal'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['kcal'];
    							$_POST['medication_block'][$source_pat_id][$type]['volume'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['volume'];
    							$_POST['medication_block'][$source_pat_id][$type]['importance'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['importance'];
    							//TODO-2982 Carmen 23.03.2020 - add the medi info from patient
    							//--
    							
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
    	
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['verordnetvon'][$cnt] = $own_corelation[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['verordnetvon'];
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['indication'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['indication_id'];
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['unit'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['unit_id'];
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['dosage_form'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['dosage_form_id'];
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['type'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['type_id'];
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['concentration'][$cnt] = $drgextra_pat[$_POST['drugs'][$source_pat_id][$type]['drid'][$k]]['concentration'];
    							
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
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['flussrate_type'] = $cocktails[$source_cocktailid]['flussrate_type'];   //ISPC-2684 Lore 08.10.2020
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['sperrzeit'] = $cocktails[$source_cocktailid]['sperrzeit'];
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['carrier_solution'] = $cocktails[$source_cocktailid]['carrier_solution'];
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['pumpe_type'] = $cocktails[$source_cocktailid]['pumpe_type'];
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['pumpe_medication_type'] = $cocktails[$source_cocktailid]['pumpe_medication_type'];
    
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['source_cocktailid'] = $source_cocktailid;
    							$_POST['medication_block'][$source_pat_id][$type][$pumpe_number]['cocktail']['source_ipid'] = $pid2iipid[$_POST['drugs'][$source_pat_id][$type]['source_patient'][$k]];
    						}
    						$cnt++;
    					}
    				}
    			}
    
    
    			    //                print_r($_POST['medication_block']);exit;
    
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
    						//var_dump($post_data); exit;
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
    
    
    		$this->_redirect(APP_BASE . "patientmedication/overview?id=" . $_REQUEST['id']);
    
    	}
    
    
    }
    
    /*
     * ISPC-2829 Lore 08.03.2021
     */
    public function efamedicationAction(){
        
        $this->getHelper('viewRenderer')->setViewSuffix('phtml');
        $clientid = $this->clientid;
        $userid = $this->userid;
        $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
        $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);
        
        if($_REQUEST['modal'] =="1"){
            $this->_helper->layout->setLayout('layout_ajax');
            $this->_helper->viewRenderer("medicationshort");
        }
        
        $barcodereaderKey="";
        if (Zend_Registry::isRegistered('barcodereader')) {
            $barcodereader_cfg = Zend_Registry::get('barcodereader');
            $barcodereaderKey = $barcodereader_cfg['datamatrix']['licenseKey'];
        }
        $this->view->barcodereaderKey = $barcodereaderKey;
        
        
        $isdicharged = PatientDischarge::isDischarged($decid);
        $this->view->isdischarged = 0;
        if($isdicharged)
        {
            $this->view->isdischarged = 1;
        }
        
        
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
                if($df['extra'] == 1 && $df['isfrommmi'] == 0)
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
        
        //ISPC-2554 pct.1 Carmen 03.04.2020
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
        
        
        //Changes for ISPC-1848 F
        //moved in the init()
        /* ================ PATIENT HEADER ======================= */
        // 		    $patientmaster = new PatientMaster();
        // 		    $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
        
        /* ================ PATIENT TAB MENU ======================= */
        // 		    $tm = new TabMenus();
        // 		    $this->view->tabmenus = $tm->getMenuTabs();
        
        // ISPC-2664 Carmen 28.09.2020
        $vital_signs_arr = array();
        $latest_vital_signs_weight = FormBlockVitalSigns::get_patients_chart_last_values_byelement($ipid, false, 'weight');
        $latest_vital_signs_height = FormBlockVitalSigns::get_patients_chart_last_values_byelement($ipid, false, 'height');
        
        if(!empty($latest_vital_signs_weight[$ipid])){
            $vital_signs_arr['weight'] = number_format($latest_vital_signs_weight[$ipid]['weight'], 3, ',', '.');
            $vital_signs_arr['weight_signs_date'] = date('d.m.Y', strtotime($latest_vital_signs_weight[$ipid]['date']));
        }
        if(!empty($latest_vital_signs_height[$ipid])){
            $vital_signs_arr['height'] = number_format($latest_vital_signs_height[$ipid]['height'], 2, ',', '.');
            $vital_signs_arr['height_signs_date'] = date('d.m.Y', strtotime($latest_vital_signs_height[$ipid]['date']));
        }
        if($vital_signs_arr['weight'] && $vital_signs_arr['height'])
        {
            $vital_signs_arr['body_surface'] = number_format(0.007184*($vital_signs_arr['height']**0.725)*($vital_signs_arr['weight']**0.425), 3, ',', '.');
        }
        $this->view->age = $this->_patientMasterData['age_yearsAndMonths'];
        $this->view->vital_signs = $vital_signs_arr;
        //--
        
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
        
        //TODO-2508 ISPC: Lore 19.08.2019
        $pharmacyprivileges = $modules->checkModulePrivileges("75", $clientid);
        if($pharmacyprivileges){
            $this->view->pharmacyprivileges = '1';
        }
        
        
        
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
        $medication_blocks = array("actual","isbedarfs","iscrisis");

        
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
        
        // Show interval medi BLOCK
        /* Intervall Medis  BLOCK - Intervall Medis */
        $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
        if(!$scheduled_block){
            $medication_blocks = array_diff($medication_blocks,array("scheduled"));
        }
        
        // ISPC-ISPC-2329 pct.r)
        // Show interval options  in Actual and IVmed
        $actual_iv_scheduled_block = $modules->checkModulePrivileges("193", $clientid);
        if(!$actual_iv_scheduled_block){
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
        
        //ISPC-2833 Ancuta 26.02.2021
        $ispumpe_block = $modules->checkModulePrivileges("251", $clientid);
        if(!$ispumpe_block){
            $medication_blocks = array_diff($medication_blocks,array("ispumpe"));
        }
        //--
        

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
        //recipe request privileges
        $this->view->reciperequest_privileges = $modules->checkModulePrivileges("150", $clientid);
        
        
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
        
        
        $this->view->js_dosage_intervals = json_encode($dosage_intervals);
        $this->view->interval_array = $interval_array;
        $this->view->dosage_intervals = $dosage_intervals;
        $this->view->deleted_intervals_ids = "0";
        
        
        
        /* ================ ISPC-2524 pct.1)  Lore 14.01.2020 ======================== */
            $patient_files = Doctrine_Query::create()
            ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
    						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
    						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
    						->from('PatientFileUpload')
    						->where("ipid=?", $ipid )
    						->andWhere('tabname in ("etamedication")')
    						->orderBy("create_date DESC");
    						$contact_form_files = $patient_files->fetchArray();
    						$files_dates = array();
    						foreach($contact_form_files as $k_file => $v_file)
    						{
    						    $users_ids[] = $v_file['create_user'];
    						    $files_dates[] = $v_file['create_date'];
    						}
    						$this->view->users_data = Pms_CommonData::getUsersData($users_ids);
    						$this->view->form_files = $contact_form_files;
 
        /*  ...  */
        
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
        //ISPC-2329 pct.k) Lore 23.08.2019
        $new_medication_sets['actual'] = $msets->getmedicationssetsDrop($this->clientid,"actual");
        
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
                    
                    $this->_redirect(APP_BASE . "patientmedication/efamedication?id=" . $_REQUEST['id']);
                }
                else
                {
                    $this->view->errors = $drugplan_intervals_form->getErrorMessages();
                    
                    $this->view->interval_array['interval'] = $_POST['interval'];
                    $this->view->deleted_intervals_ids = $_POST['deleted_intervals_ids'];
                }
            }
            

            //patientfileupload          
            $action_name = "upload_patient_files{$decid}";
            
            $qquuid = $this->getRequest()->getPost('qquuid');
            $qquuid_title = $this->getRequest()->getPost('qquuid_title');
            $qquuid_tags= $this->getRequest()->getPost('qquuid_file2tag'); //ISPC-2642 Ancuta 10-11.08.2020
            
            if (is_array($qquuid) && ! empty($qquuid) && ($last_uploaded_files = $this->get_last_uploaded_file($action_name, $qquuid, $clientid))) {
                
                
                $upload_form = new Application_Form_PatientFileUpload();
                foreach ($qquuid as $k=>$qquuidID) {
                    
                    if (($last_uploaded_file = $last_uploaded_files[$qquuidID]) && $last_uploaded_file['isZipped'] == 1) {
                        
                        $file_name = pathinfo($last_uploaded_file['filepath'], PATHINFO_FILENAME) . "/" . $last_uploaded_file['fileInfo']['name'];
                        $file_type = strtoupper(pathinfo($last_uploaded_file['filename'], PATHINFO_EXTENSION));
                        if($this->getRequest()->getPost('active_version') != 0) //ISPC - 2129
                        {
                            $post = [
                                'ipid'      => $ipid,
                                'clientid'  => $clientid,
                                'title'     => ! empty($qquuid_title[$k]) ? $qquuid_title[$k] : $last_uploaded_file['filename'] ,
                                'filetype'  => $file_type,
                                'file_name' => $file_name,
                                'zipname'   => $last_uploaded_file['filepath'], //filepath
                                'pat_files_tags_rights' => $userarray[0]['patient_file_tag_rights'],
                                'tag_name'   => !empty($qquuid_tags[$k]) ? $qquuid_tags[$k] :  $this->getRequest()->getPost('tag_name'), //ISPC-2642 Ancuta 10-11.08.2020
                                'active_version' => $this->getRequest()->getPost('active_version'),
                            ];
                            
                        }
                        else
                        {
                            $post = [
                                'ipid'      => $ipid,
                                'clientid'  => $clientid,
                                'title'     => ! empty($qquuid_title[$k]) ? $qquuid_title[$k] : $last_uploaded_file['filename'] ,
                                'filetype'  => $file_type,
                                'file_name' => $file_name,
                                'zipname'   => $last_uploaded_file['filepath'], //filepath
                                'pat_files_tags_rights' => $userarray[0]['patient_file_tag_rights'],
                                'tag_name'   => !empty($qquuid_tags[$k]) ? $qquuid_tags[$k] :  $this->getRequest()->getPost('tag_name'), //ISPC-2642 Ancuta 10-11.08.2020
                                'active_version' => '0',
                                
                            ];
                            
                        }
                        
                        $post['tabname'] = 'etamedication';
                        $rec = $upload_form->insertData($post);
                        
                        $this->delete_last_uploaded_file($action_name, $qquuidID, $clientid);
                    }
                    
                }
            }
                            
            //remove session stuff
            $_SESSION['filename'] = '';
            $_SESSION['filetype'] = '';
            $_SESSION['filetitle'] = '';
            unset($_SESSION['filename']);
            unset($_SESSION['filetype']);
            unset($_SESSION['filetitle']);
            //. patientfileupload
            
            $this->_redirect(APP_BASE . "patientmedication/efamedication?id=" . $_REQUEST['id']);
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
        //1156831
//         dd($medicarr);
        
        
        //ISPC-2797 Ancuta 17.02.2021
        $elsa_planned_medis = $modules->checkModulePrivileges("250", $clientid);
        $this->view->elsa_planned_medis = 0;
        
        if($elsa_planned_medis){
            $this->view->elsa_planned_medis = 1;
            $drugplan_ids2planned_actions = PatientDrugplanPlanning::get_planned_drugs($ipid);
        }
        //--
        
        
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
            //ISPC-2833 Ancuta 26.02.2021
            elseif($medication_data['ispumpe'] == "1")
            {
                $medications_array['ispumpe'][] = $medication_data;
                $pp_pumpe_ids[] = $medication_data['pumpe_id'];
            }
            //--
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
            
            
            //ISPC-2833 Ancuta 02.03.2021
            // get pumpe details
            $pp_pumpe_ids = array_unique($pp_pumpe_ids);
            
            if(count($pp_pumpe_ids) == 0)
            {
                $pp_pumpe_ids[] = '999999';
            }
            
            $pp_pumpe_obj = new PatientDrugplanPumpe();
            $perfusor_pumpe_data = $pp_pumpe_obj->get_perfusor_pumpes($pp_pumpe_ids);
            
            
            if(count($perfusor_pumpe_data) > 0)
            {
                $addnew = 0;
            }
            else
            {
                $addnew = 1;
            }
            $this->view->addnewlink_ispumpe = $addnew;
            $this->view->perfusor_pumpe_data_array = $perfusor_pumpe_data;
            
            
            $alt_pumpe_details = PatientDrugplanPumpeAlt:: get_drug_pumpe_alt($ipid,$pp_pumpe_ids);
            $alt_pumpe_declined = PatientDrugplanPumpeAlt:: get_declined_drug_pumpe_alt($ipid,$pp_pumpe_ids,false);
            $alt_cocktail_declined_offline = PatientDrugplanPumpeAlt:: get_declined_drug_pumpe_alt_offline($ipid, $pp_pumpe_ids, false);
            
            $alt_pumpe_details_offline =  $alt_pumpe_details['offline'];
            $alt_pumpe_details =  $alt_pumpe_details['online'];
            foreach($medications_array['ispumpe']  as $smpkey => $medicationsmp)
            {
                if(!in_array($medicationsmp['pumpe_id'],$alt_pumpe_declined)){
                    $medications_array['ispumpe'][$smpkey]['smpdescription'] = $perfusor_pumpe_data[$medicationsmp['pumpe_id']];
                    
                    if(!empty($alt_pumpe_details[$medicationsmp['pumpe_id']]))
                    {
                        $medications_array['ispumpe'][$smpkey]['smpdescription_alt'] = $alt_pumpe_details[$medicationsmp['pumpe_id']];
                    }
                    else
                    {
                        $medications_array['ispumpe'][$smpkey]['smpdescription_alt'] = "";
                    }
                }
                
                //offline changes
                $medications_array['ispumpe'][$smpkey]['smpdescription_alt_offline'] = null;
                if( ! empty($alt_pumpe_details_offline[$medicationsmp['pumpe_id']]))
                {
                    $medications_array['ispumpe'][$smpkey]['smpdescription_alt_offline'] = $alt_pumpe_details_offline[$medicationsmp['pumpe_id']];
                }
            }
            //--
            
            
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
            //TODO-3624 Ancuta 23.11.2020
            $drugplan_dosage_concentration = PatientDrugPlanDosage::get_patient_drugplan_dosage_concentration($ipid);
            
            
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
                    $medications_array[$medication_type ][$km]['efaoption'] = 1;
                    $medications_array[$medication_type ][$km]['medication_block_type'] = $medication_type;
                    $medications_array[$medication_type ][$km]['medication'] = $vm['medication'];
                    
                    
                    //ISPC-2797 Ancuta 17.02.2021
                    if($elsa_planned_medis && isset($drugplan_ids2planned_actions[$vm['id']]) && !empty($drugplan_ids2planned_actions[$vm['id']])){
                        $medications_array[$medication_type ][$km]['planned'] = $drugplan_ids2planned_actions[$vm['id']];
                    }
                    //--
                    
                    
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
                        $medications_array[$medication_type ][$km]['medication_change_ymdHis']  = $vm['medication_change'];
                    }
                    elseif($medication_change == '0000-00-00 00:00:00' && $vm['change_date'] != '0000-00-00 00:00:00')
                    {
                        $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['change_date']));
                        $medications_array[$medication_type ][$km]['medication_change_ymdHis']  = $vm['change_date'];
                    }
                    else
                    {
                        $medications_array[$medication_type ][$km]['medication_change']  = date('d.m.Y', strtotime($vm['create_date']));
                        $medications_array[$medication_type ][$km]['medication_change_ymdHis']  = $vm['create_date'];
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
                            
                            // TODO-3585 Ancuta 10.11.2020  pct 1 - changes so dosage values are listed with  comma not dot
                            //$medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']];
                            
                            $formated_dosages = array();
                            if( !empty($drugplan_dosage[$vm['id']]) ){
                                foreach($drugplan_dosage[$vm['id']] as $dtime =>$dvalue){
                                    $formated_dosages [$vm['id']][$dtime ] = str_replace(".",",",$dvalue);
                                }
                            }
                            $medications_array[$medication_type ][$km]['dosage'] = $formated_dosages[$vm['id']];
                            //--
                            
                            
                            
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
                    
                    //TODO-3829 Ancuta 24.02.2021
                    if($medication_type == "isschmerzpumpe" && strlen($medication_extra[$vm['id']]['unit']) == 0 ) {
                        $medication_extra[$vm['id']]['unit'] = "i.E.";
                    }
                    // ---
                    
                    $medications_array[$medication_type ][$km]['drug'] =  $medication_extra[$vm['id']]['drug'];
                    $medications_array[$medication_type ][$km]['unit'] =  $medication_extra[$vm['id']]['unit'];
                    $medications_array[$medication_type ][$km]['type'] =  $medication_extra[$vm['id']]['type'];
                    $medications_array[$medication_type ][$km]['indication'] =  $medication_extra[$vm['id']]['indication']['name'];
                    $medications_array[$medication_type ][$km]['indication_color'] =  $medication_extra[$vm['id']]['indication']['color'];
                    $medications_array[$medication_type ][$km]['importance'] =  trim($medication_extra[$vm['id']]['importance']);
                    $medications_array[$medication_type ][$km]['dosage_form'] =  $medication_extra[$vm['id']]['dosage_form'];
                    $medications_array[$medication_type ][$km]['dosage_form_id'] =  $medication_extra[$vm['id']]['dosage_form_id'];
                    //ISPC-2676 Ancuta 25.09.2020
                    //$medication_extra[$vm['id']]['concentration'] = str_replace(',','.',$medication_extra[$vm['id']]['concentration']);//Commented by ancuta  ISPC-2684 16.10.2020
                    //
                    $medications_array[$medication_type ][$km]['concentration'] =  $medication_extra[$vm['id']]['concentration'];
                    //TODO-3585 Ancuta 10.11.2020
                    //$medications_array[$medication_type ][$km]['concentration_full'] =  $medication_extra[$vm['id']]['concentration'];
                    $medications_array[$medication_type ][$km]['concentration_full'] =  str_replace('.', ",", $medication_extra[$vm['id']]['concentration']);
                    //--
                    
                    // ISPC-2176, p6
                    $medications_array[$medication_type ][$km]['packaging'] =  $medication_extra[$vm['id']]['packaging'];
                    $medications_array[$medication_type ][$km]['packaging_name'] =  trim($medication_extra[$vm['id']]['packaging_name']);
                    $medications_array[$medication_type ][$km]['kcal'] =  $medication_extra[$vm['id']]['kcal'];
                    $medications_array[$medication_type ][$km]['volume'] =  $medication_extra[$vm['id']]['volume'];
                    
                    // ISPC-2247
                    $medications_array[$medication_type ][$km]['escalation'] =  $medication_extra[$vm['id']]['escalation'];
                    // --
                    
                    //ISPC-2833 Ancuta 02.03.2021
                    $medications_array[$medication_type ][$km]['overall_dosage_h'] =  $medication_extra[$vm['id']]['overall_dosage_h'];
                    $medications_array[$medication_type ][$km]['overall_dosage_24h'] =  $medication_extra[$vm['id']]['overall_dosage_24h'];
                    $medications_array[$medication_type ][$km]['overall_dosage_pump'] =  $medication_extra[$vm['id']]['overall_dosage_pump'];
                    $medications_array[$medication_type ][$km]['drug_volume'] =  $medication_extra[$vm['id']]['drug_volume'];
                    $medications_array[$medication_type ][$km]['unit2ml'] =  $medication_extra[$vm['id']]['unit2ml'];
                    
                    $medications_array[$medication_type ][$km]['concentration_per_drug'] =  $medication_extra[$vm['id']]['concentration_per_drug'];
                    $medications_array[$medication_type ][$km]['bolus_per_med'] =  $medication_extra[$vm['id']]['bolus_per_med'];
                    //--
                    
                    
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
                        //    	                    $medications_array[$medication_type ][$km]['dosage_24h'] = round($dosage_value * 24, 2) ;
                        //TODO-3624 Ancuta 23.11.2020
                        if(isset($medication_extra[$vm['id']]['dosage_24h_manual']) && !empty($medication_extra[$vm['id']]['dosage_24h_manual']) ){
                            $medications_array[$medication_type ][$km]['dosage_24h'] = str_replace(".",",",$medication_extra[$vm['id']]['dosage_24h_manual']);
                        } else{
                            $medications_array[$medication_type ][$km]['dosage_24h'] = $dosage_value * 24 ;
                        }
                        
                        //TODO-3585  Ancuta 10.11.202
                        //    	                    $medications_array[$medication_type ][$km]['dosage'] = round($dosage_value, 2);
                        //$medications_array[$medication_type ][$km]['dosage'] = $dosage_value;
                        //$medications_array[$medication_type ][$km]['dosage'] = number_format($dosage_value,3,",","."); // Ancuta - Pumpe-dosage 10.12.2020
                        $medications_array[$medication_type ][$km]['dosage'] = round($dosage_value, 3); // Ancuta - Pumpe-dosage 10.12.2020
                        $medications_array[$medication_type ][$km]['dosage'] = str_replace(".",",",$medications_array[$medication_type ][$km]['dosage']);
                        // --
                        //  	                    $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage'])  ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']) : str_replace(".",",",$dosage_value);           //ISPC-2684 Lore 08.10.2020
                        //  	                    $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;   //ISPC-2684 Lore 08.10.2020
                        //   	                    $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']);   //ISPC-2684 Lore 08.10.2020
                        
                        //TODO-3829 Lore 17.02.2021
                        $modules = new Modules();
                        if($modules->checkModulePrivileges("240", $clientid)){
                            if( isset($medication_extra[$vm['id']]['unit_dosage']) && strlen($medication_extra[$vm['id']]['unit_dosage'])>0){
                                $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && !empty($medication_extra[$vm['id']]['unit_dosage'])  ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']) : str_replace(".",",",$dosage_value);           //ISPC-2684 Lore 08.10.2020
                                $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;   //ISPC-2684 Lore 08.10.2020
                                
                            } else {
                                if($medications_array[$medication_type ][$km]['unit'] == 'ml'){
                                    $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && strlen($medication_extra[$vm['id']]['unit_dosage'])>0   ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']) : '';
                                    $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && strlen($medication_extra[$vm['id']]['unit_dosage_24h'])>0  ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : '' ;
                                } else {
                                    $medications_array[$medication_type ][$km]['unit_dosage']     =  isset($medication_extra[$vm['id']]['unit_dosage'])     && strlen($medication_extra[$vm['id']]['unit_dosage'])>0  ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage']) : str_replace(".",",",$dosage_value);
                                    $medications_array[$medication_type ][$km]['unit_dosage_24h'] =  isset($medication_extra[$vm['id']]['unit_dosage_24h']) && strlen($medication_extra[$vm['id']]['unit_dosage_24h'])>0 ? str_replace(".",",",$medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".",",",$medications_array[$medication_type ][$km]['dosage_24h']) ;
                                    $medications_array[$medication_type ][$km]['dosage']     =  '';
                                    $medications_array[$medication_type ][$km]['dosage_24h'] =  '';
                                }
                            }
                        }
                        //.
                        
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
                                //        	                        $result_24h = round($result_24h, 4);
                                $result_24h = $result_24h;//TODO-3624 Ancuta 23.11.2020
                                $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  number_format($result_24h,3,",",".")." ".$medication_extra[$vm['id']]['dosage_form'];
                            }
                            else
                            {
                                $medications_array[$medication_type ][$km]['dosage_24h_concentration'] =  $result_24h." ".$medication_extra[$vm['id']]['dosage_form'];
                            }
                            
                            //TODO-3585
                            ///$medications_array[$medication_type ][$km]['dosage_24h_concentration'] = str_replace('.', ",", $medications_array[$medication_type ][$km]['dosage_24h_concentration']);
                        }
                        
                        //TODO-3585
                        $medications_array[$medication_type ][$km]['dosage_24h'] =  str_replace('.', ",", $medications_array[$medication_type ][$km]['dosage_24h']);
                        
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
            
            //ISPC-2833 Ancuta 26.02.2021
            if(!empty($medications_array['ispumpe'])){
                
                foreach($medications_array['ispumpe'] as $drug_id_ke =>$med_details)
                {
                    $alt_medications_array["ispumpe"][$med_details['pumpe_id']][] =  $med_details;
                }
                
                unset($medications_array['ispumpe']);
                $medications_array['ispumpe'] = $alt_medications_array["ispumpe"];
            }
            //--
            
            $allow_new_fields = array("actual","isbedarfs","iscrisis","isivmed","isnutrition");
            
            /* 		    echo "<pre/>";
             print_r($medications_array); exit; */
            //ISPC-2636 Lore 29.07.2020
            $cust = Doctrine_Query::create()
            ->select("client_medi_sort, user_overwrite_medi_sort_option")
            ->from('Client')
            ->where('id = ?',  $clientid);
            $cust->getSqlQuery();
            $disarray = $cust->fetchArray();
            
            
            $client_medi_sort = $disarray[0]['client_medi_sort'];
            $user_overwrite_medi_sort_option = $disarray[0]['user_overwrite_medi_sort_option'];
            
            $uss = Doctrine_Query::create()
            ->select('*')
            ->from('UserTableSorting')
            ->Where('client = ?', $clientid)
            ->orderBy('change_date DESC')
            ->limit(1);
            $uss_arr = $uss->fetchArray();
            $last_sort_order = unserialize($uss_arr[0]['value']);
            //dd($last_sort_order[0][1]);
            //.
            
            /* ================ MEDICATION :: USER SORTING ======================= */
            $usort = new UserTableSorting();
            // 		    $saved_data = $usort->user_saved_sorting($userid,false, false, false ,$ipid);
            $saved_data = $usort->user_saved_sorting($userid, false, $ipid);
            
            
            
            foreach($saved_data as $k=>$sord){
                if($sord['name'] == "order"){
                    
                    $med_type_sarr = explode("-",$sord['page']);
                    $page = $med_type_sarr[0];
                    $med_type = $med_type_sarr[1];
                    if($page == "patientmedication" && $med_type){
                        $order_value = unserialize($sord['value']);
                        $saved_order[$med_type]['col'] = $order_value[0][0] ;
                        $saved_order[$med_type]['ord'] = $order_value[0][1];
                        
                    }
                }
            }
            
            //TODO-3450 Ancuta 22.09.2020 - added sorting in request - so we can use BOTH clent sorting - and the sorting in page, as  the page is refreshed when sorting is applied
            if(!empty($client_medi_sort)){
                
                $request_sort = array();
                if(!empty($_REQUEST['sort_b']) && !empty($_REQUEST['sort_c']) && !empty($_REQUEST['sort_d'])){
                    $request_sort[$_REQUEST['sort_b']]['col'] = $_REQUEST['sort_c'];
                    $request_sort[$_REQUEST['sort_b']]['ord'] = $_REQUEST['sort_d'];
                }
                
                foreach($medication_blocks as $k=>$mt){
                    if(!empty($request_sort[$mt])){
                        $saved_order[$mt]['col'] = $request_sort[$mt]['col'];
                        $saved_order[$mt]['ord'] = $request_sort[$mt]['ord'];
                    }
                    elseif(!empty($client_medi_sort)){
                        $saved_order[$mt]['col'] = !empty($client_medi_sort) ? $client_medi_sort : "medication";              //ISPC-2636 Lore 29.07.2020
                        $saved_order[$mt]['ord'] = "asc";
                    }
                    elseif(empty($saved_order[$mt])){
                        $saved_order[$mt]['col'] = "medication";
                        $saved_order[$mt]['ord'] = "asc";
                    }
                }
                
            } else{
                foreach($medication_blocks as $k=>$mt){
                    if(empty($saved_order[$mt])){
                        $saved_order[$mt]['col'] = "medication";
                        $saved_order[$mt]['ord'] = "asc";
                    }
                }
            }
            //---
            
            
            //ISPC-2636 Lore 29.07.2020
            if($user_overwrite_medi_sort_option != '0'){
                $uomso = Doctrine_Query::create()
                ->select('*')
                ->from('UserSettingsMediSort')
                ->Where('clientid = ?', $clientid)
                ->orderBy('create_date DESC')
                ->limit(1);
                $uomso_arr = $uomso->fetchArray();
                //dd($uomso_arr);
                if(!empty($uomso_arr)){
                    $overwrite_saved_order = array();
                    foreach($saved_order as $block => $vals){
                        $overwrite_saved_order[$block]['col'] = !empty($uomso_arr[0]['sort_column'] ) ? $uomso_arr[0]['sort_column'] : 'medication';//Ancuta 17.09.2020-- Issue if empty
                        $overwrite_saved_order[$block]['ord'] = !empty($last_sort_order[0][1]) ? $last_sort_order[0][1] : "asc";
                    }
                    $saved_order = $overwrite_saved_order;
                }
            }
            //.
            
            //dd($saved_order);
            $this->view->sort_order = $saved_order;
            
            // ############ APPLY SORTING ##############
            foreach($medications_array as $type=>$m_values){
                if($type !="isschmerzpumpe" && $type !="ispumpe"){
                    if($saved_order[$type]['ord'] == "asc"){
                        $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_ASC);
                    } else{
                        $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_DESC);
                    }
                } else{
                    foreach($medications_array[$type] as $sch_id=>$sh_m_values){
                        if($saved_order[$type]['ord'] == "asc"){
                            $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_ASC);
                        } else{
                            $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_DESC);
                        }
                    }
                    
                }
            }
            if(!empty($medications_array_sorted)){
                $medications_array = array();
                $medications_array = $medications_array_sorted;
            }
            
            $this->view->saved_order= $saved_order;
            $this->view->js_saved_order= json_encode($saved_order);
            
            if($_REQUEST['final'] == "1")
            {
                print_R($medications_array); exit;
            }
            // get last date where medis were changed


            foreach($medications_array as $medtype=>$blocks_data){
                foreach($blocks_data as $k=>$med){
                    if(isset($med['on_hold_changes']) && !empty($med['on_hold_changes'])){
                        foreach($med['on_hold_changes'] as $cid => $changes){
                            $medi_change_dates[] = $changes['create_date'];
                        }
                    }
                    
                    if($med['medication_change_ymdHis']){
                        $medi_change_dates[] = $med['medication_change_ymdHis'];
                    }
                }
            }
            
            usort($medi_change_dates, array(new Pms_Sorter(), "_date_compare"));
            usort($files_dates, array(new Pms_Sorter(), "_date_compare"));
            
            $show_file_red_bar = 0 ;
            if(!empty($files_dates)){
                
                $last_medi_change = end($medi_change_dates); 
                $last_upload_mediplan = end($files_dates); 
                
                if(strtotime($last_upload_mediplan) > strtotime($last_medi_change)){
                    $show_file_red_bar = 1 ;
                }
            }
            $this->view->show_file_red_bar = $show_file_red_bar;
            $this->view->last_file_date = date("d.m.Y",strtotime($last_upload_mediplan));
            
            $this->view->medication = $medications_array;
            
    }
    


    
		
}
	