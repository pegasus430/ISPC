<?php
/**
 *
 * !!!! STOP this logic to update db via only a variable provided by the user !!!
 *
 * who is $comment ??? 
 */
require_once("Pms/Form.php");

class Application_Form_DgpKern extends Pms_Form{
    
    
    private $_PatientReadmission_OBJ = array();
    
    public function __construct($options =  null)
    {
        if ($options)
        foreach ($options as $key => $val) {
            if ( property_exists($this, $key)) {
                $this->{$key} = $val;
                unset($options[$key]);
            }
        }
        
        parent::__construct($options);
    }
    
    
    /* ISPC-1775,ISPC-1678 */
	public function insertDgpKern($post, $ipid){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$datum_der_erfassung1 =explode(".",$post['datum_der_erfassung1']);
		$datum_der_erfassung2 =explode(".",$post['datum_der_erfassung2']);
		$entlasung_date =explode(".",$post['entlasung_date']);

		$stmb = new DgpKern();
		$stmb->ipid = $ipid;
		$stmb->wohnsituations = $post['wohnsituation'];
		$stmb->begleitung  = join(",",$post['begleitung']);
		$stmb->ecog = $post['ecog'];
		$stmb->datum_der_erfassung1 = $datum_der_erfassung1[2]."-".$datum_der_erfassung1[1]."-".$datum_der_erfassung1[0];
		$stmb->schmerzen = $post['schmerzen'];
		$stmb->ubelkeit = $post['ubelkeit'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->luftnot = $post['luftnot'];
		$stmb->verstopfung = $post['verstopfung'];
		$stmb->swache = $post['swache'];
		$stmb->appetitmangel = $post['appetitmangel'];
		$stmb->mudigkeit = $post['mudigkeit'];
		$stmb->dekubitus = $post['dekubitus'];
		$stmb->hilfebedarf = $post['hilfebedarf'];
		$stmb->depresiv = $post['depresiv'];
		$stmb->angst = $post['angst'];
		$stmb->anspannung = $post['anspannung'];
		$stmb->unruhe = $post['unruhe'];
		$stmb->desorientier = $post['desorientier'];
		$stmb->versorgung = $post['versorgung'];
		$stmb->umfelds = $post['umfelds'];
		$stmb->sonstige_probleme = $post['sonstige_probleme'];
		$stmb->kontaktes = $post['kontaktes'];// ** not in new hospiz register
		$stmb->who = $post['who'];
		$stmb->steroide = $post['steroide'];
		$stmb->chemotherapie = $post['chemotherapie'];
		$stmb->strahlentherapie = $post['strahlentherapie']; // ** not in new hospiz register
		$stmb->aufwand_mit = $post['aufwand_mit'];// ** not in new hospiz register
		$stmb->problem_besonders = $post['problem_besonders'];
		$stmb->problem_ausreichend = $post['problem_ausreichend'];
		$stmb->entlasung_date = $entlasung_date[2]."-".$entlasung_date[1]."-".$entlasung_date[0];
		$stmb->therapieende  = $post['therapieende'];// ** not in new hospiz register
		$stmb->sterbeort_dgp  = $post['sterbeort_dgp'];
		$stmb->zufriedenheit_mit  = $post['zufriedenheit_mit'];
		
		// ISPC-1994 04.09.2017 Ancuta
		$stmb->pverfuegung = $post['pverfuegung'];
		$stmb->vollmacht = $post['vollmacht'];
		$stmb->betreuungsurkunde = $post['betreuungsurkunde'];
		$stmb->acp = $post['acp'];
// 		$stmb->medication_references  = join(",",$post['medication_references']);
		
		
		$stmb->analgetika = $post['analgetika'];
		$stmb->who2 = $post['who2'];
		$stmb->nicht_opioide = $post['nicht_opioide'];
		$stmb->co_analgetika = $post['co_analgetika'];
		$stmb->anxiolytika = $post['anxiolytika'];
		$stmb->laxantien = $post['laxantien'];
		$stmb->sedativa = $post['sedativa'];
		$stmb->neuroleptika = $post['neuroleptika'];
		$stmb->anti_eleptika = $post['anti_eleptika'];
		$stmb->antiemetika = $post['antiemetika'];
		$stmb->antibiotika = $post['antibiotika'];
		$stmb->magenschutz = $post['magenschutz'];
		// Maria:: Migration ISPC to CISPC 08.08.2020			
		//ISPC-2496 Ancuta 02.12.2019 - Added new values
		$stmb->secretioninhibiting_sub = $post['secretioninhibiting_sub'];
		$stmb->benzodiazepines = $post['benzodiazepines'];
		$stmb->antidepressants = $post['antidepressants'];
		$stmb->antipsychotics = $post['antipsychotics'];
		$stmb->anti_infectives = $post['anti_infectives'];
		$stmb->anticoagulants = $post['anticoagulants'];
		$stmb->other_meds = $post['other_meds'];
		// --
 
		
		$stmb->bedarf = $post['bedarf'];
		$stmb->massnahmen = $post['massnahmen'];
		
		
		$stmb->save();

		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("dgpkernform");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->save();



		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}
	
	
	
	
	public function insert_from_admission($post, $ipid){
	    /* ISPC-1775,ISPC-1678 */
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		
		$stmb = new DgpKern();
		$stmb->ipid = $ipid;
		$stmb->ecog = $post['ecog'];
		$stmb->begleitung  = join(",",$post['begleitung']);
		$stmb->datum_der_erfassung1 = date("Y-m-d H:i:00",time());
		if(isset($post['entlasung_date'])){
    		$stmb->entlasung_date =  date("Y-m-d 00:00:00",strtotime($post['entlasung_date']));
		}
		$stmb->wohnsituations =$post['wohnsituation'];
		$stmb->kontaktes = $post['kontaktes'];
		$stmb->aufwand_mit = $post['aufwand_mit'];
		$stmb->schmerzen = $post['schmerzen'];
		$stmb->ubelkeit = $post['ubelkeit'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->luftnot = $post['luftnot'];
		$stmb->verstopfung = $post['verstopfung'];
		$stmb->swache = $post['swache'];
		$stmb->appetitmangel = $post['appetitmangel'];
		$stmb->mudigkeit = $post['mudigkeit'];
		$stmb->dekubitus = $post['dekubitus'];
		$stmb->hilfebedarf = $post['hilfebedarf'];
		$stmb->depresiv = $post['depresiv'];
		$stmb->angst = $post['angst'];
		$stmb->anspannung = $post['anspannung'];
		$stmb->unruhe = $post['unruhe'];
		$stmb->desorientier = $post['desorientier'];
		$stmb->versorgung = $post['versorgung'];
		$stmb->umfelds = $post['umfelds'];
		$stmb->sonstige_probleme = $post['sonstige_probleme'];
		
		// ISPC-1994 04.09.2017 Ancuta
		$stmb->pverfuegung = $post['pverfuegung'];
		$stmb->vollmacht = $post['vollmacht'];
		$stmb->betreuungsurkunde = $post['betreuungsurkunde'];
		$stmb->acp = $post['acp'];
		
		$stmb->bedarf = $post['bedarf'];
		$stmb->massnahmen = $post['massnahmen'];
		
		
		$stmb->form_type = 'adm';
		
		
		if (isset($this->_PatientReadmission_OBJ['admission_date']) 
		    && $this->{_PatientReadmission_OBJ}['admission_date'] instanceof PatientReadmission
		    && $this->{_PatientReadmission_OBJ}['admission_date']->id > 0
		) {
		    $stmb ->patient_readmission_ID = $this->{_PatientReadmission_OBJ}['admission_date']->id;
		}
		
		if ( ! empty($post['patient_readmission_ID'])) {
		    $stmb->patient_readmission_ID = $post['patient_readmission_ID'];
		}
		
		
		
		$stmb->save();

		$result = $stmb->id;
		
		if($post['course'] == "1")
		{
    		$cust = new PatientCourse();
    		$cust->ipid = $ipid;
    		$cust->course_date = date("Y-m-d H:i:s",time());
    		$cust->course_type = Pms_CommonData::aesEncrypt("K");
    		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
    		$cust->tabname = Pms_CommonData::aesEncrypt("new_hospiz_register_v3_created");
    		$cust->recordid = $result;
    		$cust->user_id = $userid;
    		$cust->save();
		}
		
		/*
		 * removed by @cla
		 */
// 		if($stmb->id>0){
// 			return true;
// 		}else{
// 			return false;
// 		}

		return $stmb; // !!! result is used in patientdischargeAction
	}
	

	public function update_from_admission($post, $ipid)
	{
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;

	    /**
	     * 
	     * !!!! STOP this f.ing logic to update db via only a variable provided by the user !!!
	     * 
	     */
// 	    $stmb = Doctrine::getTable('DgpKern')->find($post['dgp_kern_id']);
	    $stmb = Doctrine::getTable('DgpKern')->findOneByIdAndIpid($post['dgp_kern_id'], $ipid);
	    
	    /**
	     * also, you should check if you have something to update, cause like you do now you throw error
	     */
	    ///if ($stmb) ...
	    
		$stmb->ecog = $post['ecog'];
		$stmb->begleitung  = join(",",$post['begleitung']);
		if(isset($post['entlasung_date'])){
		    $stmb->entlasung_date =  date("Y-m-d 00:00:00",strtotime($post['entlasung_date']));
		}
		
		$stmb->wohnsituations =$post['wohnsituation'];
		
		$stmb->kontaktes = $post['kontaktes'];
		$stmb->aufwand_mit = $post['aufwand_mit'];
		

		$stmb->schmerzen = $post['schmerzen'];
		$stmb->ubelkeit = $post['ubelkeit'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->luftnot = $post['luftnot'];
		$stmb->verstopfung = $post['verstopfung'];
		$stmb->swache = $post['swache'];
		$stmb->appetitmangel = $post['appetitmangel'];
		$stmb->mudigkeit = $post['mudigkeit'];
		$stmb->dekubitus = $post['dekubitus'];
		$stmb->hilfebedarf = $post['hilfebedarf'];
		$stmb->depresiv = $post['depresiv'];
		$stmb->angst = $post['angst'];
		$stmb->anspannung = $post['anspannung'];
		$stmb->unruhe = $post['unruhe'];
		$stmb->desorientier = $post['desorientier'];
		$stmb->versorgung = $post['versorgung'];
		$stmb->umfelds = $post['umfelds'];
		$stmb->sonstige_probleme = $post['sonstige_probleme'];
   		$stmb->form_type = 'adm';
   		
   		// ISPC-1994 04.09.2017 Ancuta
   		$stmb->pverfuegung = $post['pverfuegung'];
   		$stmb->vollmacht = $post['vollmacht'];
   		$stmb->betreuungsurkunde = $post['betreuungsurkunde'];
   		$stmb->acp = $post['acp'];
   		
   		$stmb->bedarf = $post['bedarf'];
   		$stmb->massnahmen = $post['massnahmen'];
   		
   		
   		if (isset($this->_PatientReadmission_OBJ['admission_date']) 
   		    && $this->{_PatientReadmission_OBJ}['admission_date'] instanceof PatientReadmission
   		    && $this->{_PatientReadmission_OBJ}['admission_date']->id > 0
   		) {
   		    $stmb ->patient_readmission_ID = $this->{_PatientReadmission_OBJ}['admission_date']->id;
   		}
   		
   		
	    $stmb->save();
	     
	    if($post['course'] == "1")
	    {
    	    $cust = new PatientCourse();
    	    $cust->ipid = $ipid;
    	    $cust->course_date = date("Y-m-d H:i:s",time());
    	    $cust->course_type=Pms_CommonData::aesEncrypt("K");
    	    $cust->course_title = Pms_CommonData::aesEncrypt("DGP-Kerndatensatz - Formular \"Kerndatensatz für Palliativpatienten\" wurde editiert");
    	    $cust->tabname = Pms_CommonData::aesEncrypt("new_hospiz_register_v3_edited");
    	    $cust->recordid = $post['dgp_kern_id'];
    	    $cust->user_id = $userid;
	        $cust->save();
	    }
	    
	    return $stmb;
	    
	}
	
	
	
	
	
	public function insert_from_discharge($post, $ipid)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$stmb = new DgpKern();
		$stmb->ipid = $ipid;
		//ISPC-2217 Ancuta 25.07.2018
		$stmb->ecog = $post['ecog'];
		//--
		$stmb->begleitung  = join(",",$post['begleitung']);
		$stmb->datum_der_erfassung1 = date("Y-m-d H:i:00",time());
		$stmb->who = $post['who'];
		$stmb->steroide = $post['steroide'];
		$stmb->chemotherapie = $post['chemotherapie'];
		$stmb->problem_besonders = $post['problem_besonders'];
		$stmb->problem_ausreichend = $post['problem_ausreichend'];
		$stmb->zufriedenheit_mit  = $post['zufriedenheit_mit'];
		$stmb->aufwand_mit = $post['aufwand_mit'];
		$stmb->wohnsituations =$post['wohnsituation'];
		$stmb->strahlentherapie = $post['strahlentherapie'];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->schmerzen = $post['schmerzen'];
		$stmb->ubelkeit = $post['ubelkeit'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->luftnot = $post['luftnot'];
		$stmb->verstopfung = $post['verstopfung'];
		$stmb->swache = $post['swache'];
		$stmb->appetitmangel = $post['appetitmangel'];
		$stmb->mudigkeit = $post['mudigkeit'];
		$stmb->dekubitus = $post['dekubitus'];
		$stmb->hilfebedarf = $post['hilfebedarf'];
		$stmb->depresiv = $post['depresiv'];
		$stmb->angst = $post['angst'];
		$stmb->anspannung = $post['anspannung'];
		$stmb->unruhe = $post['unruhe'];
		$stmb->desorientier = $post['desorientier'];
		$stmb->versorgung = $post['versorgung'];
		$stmb->umfelds = $post['umfelds'];
		$stmb->sonstige_probleme = $post['sonstige_probleme'];
		$stmb->sterbeort_dgp = $post['sterbeort_dgp'];
		if(strlen($post['entlasung_date']) > 0 ){
    		$stmb->entlasung_date = date("Y-m-d H:i:s",strtotime($post['entlasung_date']));
		}
		//$stmb->create_date = date("Y-m-d H:i:s", strtotime("+5 sec"));
		
		
		// ISPC-1994 04.09.2017 Ancuta
		$stmb->pverfuegung = $post['pverfuegung'];
		$stmb->vollmacht = $post['vollmacht'];
		$stmb->betreuungsurkunde = $post['betreuungsurkunde'];
		$stmb->acp = $post['acp'];
		
		$stmb->bedarf = $post['bedarf'];
		$stmb->massnahmen = $post['massnahmen'];
// 		$stmb->medication_references  = join(",",$post['medication_references']);
		
		$stmb->analgetika = $post['analgetika'];
		$stmb->who2 = $post['who2'];
		$stmb->nicht_opioide = $post['nicht_opioide'];
		$stmb->co_analgetika = $post['co_analgetika'];
		$stmb->anxiolytika = $post['anxiolytika'];
		$stmb->laxantien = $post['laxantien'];
		$stmb->sedativa = $post['sedativa'];
		$stmb->neuroleptika = $post['neuroleptika'];
		$stmb->anti_eleptika = $post['anti_eleptika'];
		$stmb->antiemetika = $post['antiemetika'];
		$stmb->antibiotika = $post['antibiotika'];
		$stmb->magenschutz = $post['magenschutz'];
		
		// Maria:: Migration ISPC to CISPC 08.08.2020	
		//ISPC-2496 Ancuta 02.12.2019 - Added new values
		$stmb->secretioninhibiting_sub = $post['secretioninhibiting_sub'];
		$stmb->benzodiazepines = $post['benzodiazepines'];
		$stmb->antidepressants = $post['antidepressants'];
		$stmb->antipsychotics = $post['antipsychotics'];
		$stmb->anti_infectives = $post['anti_infectives'];
		$stmb->anticoagulants = $post['anticoagulants'];
		$stmb->other_meds = $post['other_meds'];
		// --
		
		
		$stmb->form_type = 'dis';
		
		//ISPC-2198
		if (isset($this->_PatientReadmission_OBJ['discharge_date']) 
		    && $this->{_PatientReadmission_OBJ}['discharge_date'] instanceof PatientReadmission
		    && $this->{_PatientReadmission_OBJ}['discharge_date']->id > 0
		) {
		    $stmb ->patient_readmission_ID = $this->{_PatientReadmission_OBJ}['discharge_date']->id;
		}
		
		if ( ! empty($post['patient_readmission_ID'])) {
		    $stmb->patient_readmission_ID = $post['patient_readmission_ID'];
		}
		
		if ( ! empty($post['twin_ID'])) {
		    $stmb->twin_ID = $post['twin_ID'];
		}
		
		$stmb->save();

		$result = $stmb->id;
		
		if($post['course'] == "1")
		{
            $cust = new PatientCourse();
    		$cust->ipid = $ipid;
    		$cust->course_date = date("Y-m-d H:i:s",time());
    		$cust->course_type = Pms_CommonData::aesEncrypt("K");
    		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
    		$cust->tabname = Pms_CommonData::aesEncrypt("new_hospiz_register_v3_edited");
    		$cust->recordid = $result;
    		$cust->user_id = $userid;
    		$cust->save();
		}

		/*
		 * removed by @cla 
		 * @dev .... please read what you copy-paste
		 */
// 		if($ins->id>0){
// 			return true;
// 		}else{
// 			return false;
// 		}
		
		return $stmb; // !!! result is used in patientdischargeAction 
	}
	
	
	
	public function update_from_discharge($post, $ipid)
	{
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
// print_r($post); exit;
	    
	    /*
	     * @cla removed this
	     * if you removed the usage of  $adm_create_date .. why do you keep this useless query ?
	     */
// 	    $patientKvnofirst = Doctrine_Query::create()
// 	    ->select('*')
// 	    ->from('DgpKern')
// 	    ->where('ipid = "'.$ipid.'" ')
// 	    ->groupBy('ipid')
// 	    ->orderby('id, create_date asc') //who invented this orderby ???? pease explain to @cla so I can learn why
// 	    ->limit(1);
// 	    $patientKvnoarrayfirst = $patientKvnofirst->fetchArray();
	    
// 	    if($patientKvnoarrayfirst )
// 	    {
// 	         $adm_create_date = $patientKvnoarrayfirst[0]['create_date'];
// 	     }
	    
	     /**
	      *
	      * !!!! STOP this f.ing logic to update db via only a variable provided by the user !!!
	      *
	      */
// 	    $stmb = Doctrine::getTable('DgpKern')->find($post['dgp_kern_id']);
	    $stmb = Doctrine::getTable('DgpKern')->findOneByIdAndIpid($post['dgp_kern_id'], $ipid);

        //ISPC-2217 Ancuta 25.07.2018
        $stmb->ecog = $post['ecog'];
		//--
	    $stmb->begleitung  = join(",",$post['begleitung']);
		$stmb->who = $post['who'];
		$stmb->steroide = $post['steroide'];
		$stmb->chemotherapie = $post['chemotherapie'];
		$stmb->problem_besonders = $post['problem_besonders'];
		$stmb->problem_ausreichend = $post['problem_ausreichend'];
		$stmb->zufriedenheit_mit  = $post['zufriedenheit_mit'];
		$stmb->wohnsituations =$post['wohnsituation'];
		$stmb->strahlentherapie = $post['strahlentherapie'];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->schmerzen = $post['schmerzen'];
		$stmb->ubelkeit = $post['ubelkeit'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->luftnot = $post['luftnot'];
		$stmb->verstopfung = $post['verstopfung'];
		$stmb->swache = $post['swache'];
		$stmb->appetitmangel = $post['appetitmangel'];
		$stmb->mudigkeit = $post['mudigkeit'];
		$stmb->dekubitus = $post['dekubitus'];
		$stmb->hilfebedarf = $post['hilfebedarf'];
		$stmb->depresiv = $post['depresiv'];
		$stmb->angst = $post['angst'];
		$stmb->anspannung = $post['anspannung'];
		$stmb->unruhe = $post['unruhe'];
		$stmb->desorientier = $post['desorientier'];
		$stmb->versorgung = $post['versorgung'];
		$stmb->umfelds = $post['umfelds'];
		$stmb->sonstige_probleme = $post['sonstige_probleme'];
		$stmb->aufwand_mit = $post['aufwand_mit'];
		$stmb->sterbeort_dgp = $post['sterbeort_dgp'];
		
		// ISPC-1994 04.09.2017 Ancuta
		$stmb->pverfuegung = $post['pverfuegung'];
		$stmb->vollmacht = $post['vollmacht'];
		$stmb->betreuungsurkunde = $post['betreuungsurkunde'];
		$stmb->acp = $post['acp'];
		
		$stmb->bedarf = $post['bedarf'];
		$stmb->massnahmen = $post['massnahmen'];

		$stmb->analgetika = $post['analgetika'];
		$stmb->who2 = $post['who2'];
		$stmb->nicht_opioide = $post['nicht_opioide'];
		$stmb->co_analgetika = $post['co_analgetika'];
		$stmb->anxiolytika = $post['anxiolytika'];
		$stmb->laxantien = $post['laxantien'];
		$stmb->sedativa = $post['sedativa'];
		$stmb->neuroleptika = $post['neuroleptika'];
		$stmb->anti_eleptika = $post['anti_eleptika'];
		$stmb->antiemetika = $post['antiemetika'];
		$stmb->antibiotika = $post['antibiotika'];
		$stmb->magenschutz = $post['magenschutz'];
		


		//ISPC-2496 Ancuta 02.12.2019 - Added new values
		$stmb->secretioninhibiting_sub = $post['secretioninhibiting_sub'];
		$stmb->benzodiazepines = $post['benzodiazepines'];
		$stmb->antidepressants = $post['antidepressants'];
		$stmb->antipsychotics = $post['antipsychotics'];
		$stmb->anti_infectives = $post['anti_infectives'];
		$stmb->anticoagulants = $post['anticoagulants'];
		$stmb->other_meds = $post['other_meds'];
		// --
				
		
		if(strlen($post['entlasung_date']) > 0 ){
		  $stmb->entlasung_date = date("Y-m-d H:i:s",strtotime($post['entlasung_date']));
		}
		
// 		if(isset($adm_create_date) && $adm_create_date == $stmb->create_date)
// 		{
// 		    $stmb->create_date = date("Y-m-d H:i:s", strtotime("+5 sec",strtotime($stmb->create_date)));
// 		}
		
        
        if (isset($this->_PatientReadmission_OBJ['discharge_date']) 
            && $this->{_PatientReadmission_OBJ}['discharge_date'] instanceof PatientReadmission
            && $this->{_PatientReadmission_OBJ}['discharge_date']->id > 0
        ) {
            $stmb ->patient_readmission_ID = $this->{_PatientReadmission_OBJ}['discharge_date']->id;
        }


		$stmb->form_type = 'dis';
	    $stmb->save();
	    
	    if($post['course'] == "1")
	    {
    	    $cust = new PatientCourse();
    	    $cust->ipid = $ipid;
    	    $cust->course_date = date("Y-m-d H:i:s",time());
    	    $cust->course_type=Pms_CommonData::aesEncrypt("K");
    	    $cust->course_title=Pms_CommonData::aesEncrypt("DGP-Kerndatensatz - Formular \"Kerndatensatz für Palliativpatienten\" wurde editiert");
    	    $cust->tabname = Pms_CommonData::aesEncrypt("new_hospiz_register_v3_edited");
    	    $cust->recordid = $post['dgp_kern_id'];
    	    $cust->user_id = $userid;
    	    $cust->save();
	    }
	}
	

	public function UnsertDgpKern($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$stmb = Doctrine::getTable('DgpKern')->find($post['dgp_kern_id']);
		$datum_der_erfassung1 =explode(".",$post['datum_der_erfassung1']);
		$datum_der_erfassung2 =explode(".",$post['datum_der_erfassung2']);
		$entlasung_date =explode(".",$post['entlasung_date']);
		$stmb->begleitung  = join(",",$post['begleitung']);
		$stmb->wohnsituations =$post['wohnsituation'];
		$stmb->ecog = $post['ecog'];
		$stmb->datum_der_erfassung1 = $datum_der_erfassung1[2]."-".$datum_der_erfassung1[1]."-".$datum_der_erfassung1[0];
		$stmb->schmerzen = $post['schmerzen'];
		$stmb->ubelkeit = $post['ubelkeit'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->luftnot = $post['luftnot'];
		$stmb->verstopfung = $post['verstopfung'];
		$stmb->swache = $post['swache'];
		$stmb->appetitmangel = $post['appetitmangel'];
		$stmb->mudigkeit = $post['mudigkeit'];
		$stmb->dekubitus = $post['dekubitus'];
		$stmb->hilfebedarf = $post['hilfebedarf'];
		$stmb->depresiv = $post['depresiv'];
		$stmb->angst = $post['angst'];
		$stmb->anspannung = $post['anspannung'];
		$stmb->unruhe = $post['unruhe'];
		$stmb->desorientier = $post['desorientier'];
		$stmb->versorgung = $post['versorgung'];
		$stmb->umfelds = $post['umfelds'];
		$stmb->sonstige_probleme = $post['sonstige_probleme'];

		$stmb->kontaktes = $post['kontaktes'];
		$stmb->who = $post['who'];
		$stmb->steroide = $post['steroide'];
		$stmb->chemotherapie = $post['chemotherapie'];
		$stmb->strahlentherapie = $post['strahlentherapie'];

		$stmb->aufwand_mit = $post['aufwand_mit'];
		$stmb->problem_besonders = $post['problem_besonders'];
		$stmb->problem_ausreichend = $post['problem_ausreichend'];
		$stmb->entlasung_date = $entlasung_date[2]."-".$entlasung_date[1]."-".$entlasung_date[0];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->sterbeort_dgp  = $post['sterbeort_dgp'];
		$stmb->zufriedenheit_mit  = $post['zufriedenheit_mit'];
		
		// ISPC-1994 04.09.2017 Ancuta
		$stmb->pverfuegung = $post['pverfuegung'];
		$stmb->vollmacht = $post['vollmacht'];
		$stmb->betreuungsurkunde = $post['betreuungsurkunde'];
		$stmb->acp = $post['acp'];
		
		$stmb->bedarf = $post['bedarf'];
		$stmb->massnahmen = $post['massnahmen'];
// 		$stmb->medication_references  = join(",",$post['medication_references']);
		
		$stmb->analgetika = $post['analgetika'];
		$stmb->who2 = $post['who2'];
		$stmb->nicht_opioide = $post['nicht_opioide'];
		$stmb->co_analgetika = $post['co_analgetika'];
		$stmb->anxiolytika = $post['anxiolytika'];
		$stmb->laxantien = $post['laxantien'];
		$stmb->sedativa = $post['sedativa'];
		$stmb->neuroleptika = $post['neuroleptika'];
		$stmb->anti_eleptika = $post['anti_eleptika'];
		$stmb->antiemetika = $post['antiemetika'];
		$stmb->antibiotika = $post['antibiotika'];
		$stmb->magenschutz = $post['magenschutz'];
		
		$stmb->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("DGP-Kerndatensatz - Formular \"Kerndatensatz für Palliativpatienten\" wurde editiert");
		$cust->recordid = $post['dgp_kern_id'];
		$cust->user_id = $userid;
		$cust->save();

	}

}

?>