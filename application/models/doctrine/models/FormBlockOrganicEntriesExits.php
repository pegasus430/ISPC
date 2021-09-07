<?php
	//ISPC-2518+ISPC-2520 Carmen 14.04.2020
    //#ISPC-2512PatientCharts
	Doctrine_Manager::getInstance()->bindComponent('FormBlockOrganicEntriesExits', 'MDAT');

	class FormBlockOrganicEntriesExits extends BaseFormBlockOrganicEntriesExits {
		
		/**
		 * translations are grouped into an array
		 * @var unknown
		 */
		const LANGUAGE_ARRAY    = 'formblockorganicentriesexits_lang';
		
		/**
		 * define the FORMID and FORMNAME, if you want to piggyback some triggers
		 * @var unknown
		 */
		const TRIGGER_FORMID    = null;
		const TRIGGER_FORMNAME  = 'frm_formblockorganicentriesexits';
		
		/**
		 * insert into patient_files will use this
		 */
		const PATIENT_FILE_TABNAME  = 'FormBlockOrganicEntriesExits';
		const PATIENT_FILE_TITLE    = 'FormBlockOrganicEntriesExits PDF'; //this will be translated
		
		/**
		 * insert into patient_course will use this
		 */
		const PATIENT_COURSE_TYPE       = 'K'; 
		
		const PATIENT_COURSE_TABNAME    = 'formblockorganicentriesexits';
		
		//this is just for demo, another one is used on contact_form save
		const PATIENT_COURSE_TITLE      = 'FormBlockOrganicEntriesExits was created';

		
		
		/**
		 * @author Ancuta
		 * ISPC-2515 16.04.2020 ISPC-2512 
		 * @param unknown $ipids
		 * @param boolean $period
		 * @return void|array|Doctrine_Collection
		 * #ISPC-2512PatientCharts
		 */
		public static function get_patients_chart($ipids, $period = false, $bilanzierung_chart = false) //ISPC-2661 Carmen
		{
		    if ( empty($ipids)) {
		        return;
		    }
		    
		    if( ! is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		    
		    
		    $cf = new ContactForms();
		    $delcf = $cf->get_patients_deleted_contactforms($ipids);
		    
		    $delcform = array();
		    
		    foreach ($delcf as $key_ipid => $valcf)
		    {
		        foreach($valcf as $kdcf=>$vcfdel)
		        {
		            $delcform[] = $vcfdel;
		        }
		    }
		    
		    
		    $sql_period_params = array();
		    
		    if($period)
		    {
		        $sql_period = ' (DATE(organic_date) != "0000-00-00" AND organic_date BETWEEN ? AND ? ) ';
		        
		        $sql_period_params = array( $period['start'], $period['end'] );
		    }
		    else
		    {
		        $sql_period = ' DATE(organic_date) != "1970-01-01"  ';
		    }
		    
		    //ISPC-2661 Carmen
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockOrganicEntriesExits')
		    ->where('isdelete= "0" ')
		    ->andWhereIn('ipid', $ipids);
		    if($bilanzierung_chart)
		    {
		    	$patient->andwhere('setid != "0"');
		    }
		    $patient->orderBy('organic_date ASC');
		    //--
		    if ( ! empty($delcform)) {
		        $patient->andwhereNotIn("contact_form_id",$delcform);
		    }
		    
		    if ( ! empty($sql_period)) {
		        $patient->andWhere( $sql_period , $sql_period_params);
		    }
		    
		    $patientlimit = $patient->fetchArray();
		    
		    $master_organic_events  = array();
		    $master_color_ids = array();
		    $master_type_ids = array();
		    if(!empty($patientlimit)){
		        $master_organic_events = array_column($patientlimit, 'organic_id');
		        $master_color_ids = array_column($patientlimit, 'organic_color');
		        $master_type_ids = array_column($patientlimit, 'organic_type');
		    }
		    
		    if(!empty($master_organic_events )){
    		    $main_organic_events = Doctrine_Query::create()
    		    ->select('*')
    		    ->from('OrganicEntriesExitsLists INDEXBY id' )
    		    ->wherein('id',$master_organic_events )
    		    ->fetchArray();
		    }
		    
		    
		    if(!empty($master_color_ids) || !empty($master_type_ids) ){
    		    $main_organic_extra_fields = Doctrine_Query::create()
    		    ->select('*')
    		    ->from('OrganicEntriesExitsExtrafields')
    		    ->fetchArray();
    		    
    		   
    		    $extra_fields_colors = array();
    		    $extra_fields_types = array();
    		    foreach($main_organic_extra_fields as $k=>$oextra){
    		        if($oextra['organic_extrafield'] == 'color'){
    		            $extra_fields_colors[$oextra['id']] = $oextra['organic_option'];
    		        }elseif($oextra['organic_extrafield'] == 'type'){
    		            $extra_fields_types[$oextra['id']] = $oextra['organic_option'];
    		        }
    		        
    		    }
		    }
    		    
		    foreach($patientlimit as $key=>$poe){
		        $patientlimit[$key]['organic_color_name'] = $extra_fields_colors[$poe['organic_color']];
		        $patientlimit[$key]['organic_type_name'] = $extra_fields_types[$poe['organic_type']];
		        $patientlimit[$key]['organic_id_master_name'] = $main_organic_events[$poe['organic_id']]['name'];
		        $patientlimit[$key]['organic_id_master_shortcut'] = $main_organic_events[$poe['organic_id']]['shortcut'];
		        
		        //ISPC-2661
		        $patientlimit[$key]['item_type'] = $main_organic_events[$poe['organic_id']]['type'];
		    }
		    
		    return $patientlimit;
		}
	}

?>