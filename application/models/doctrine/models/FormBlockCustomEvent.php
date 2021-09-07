<?php
	//ISPC-2519 Carmen 15.04.2020
    //#ISPC-2512PatientCharts
	Doctrine_Manager::getInstance()->bindComponent('FormBlockCustomEvent', 'MDAT');

	class FormBlockCustomEvent extends BaseFormBlockCustomEvent {
		
		/**
		 * translations are grouped into an array
		 * @var unknown
		 */
		const LANGUAGE_ARRAY    = 'formblockcustomevent_lang';
		
		/**
		 * define the FORMID and FORMNAME, if you want to piggyback some triggers
		 * @var unknown
		 */
		const TRIGGER_FORMID    = null;
		const TRIGGER_FORMNAME  = 'frm_formblockcustomevent';
		
		/**
		 * insert into patient_files will use this
		 */
		const PATIENT_FILE_TABNAME  = 'FormBlockCustomEvent';
		const PATIENT_FILE_TITLE    = 'FormBlockCustomEvent PDF'; //this will be translated
		
		/**
		 * insert into patient_course will use this
		 */
		const PATIENT_COURSE_TYPE       = 'K'; 
		
		const PATIENT_COURSE_TABNAME    = 'formblockcustomevent';
		
		//this is just for demo, another one is used on contact_form save
		const PATIENT_COURSE_TITLE      = 'FormBlockCustomEvent was created';

		
		/**
		 * @author Ancuta
		 * ISPC-2515 ISPC-2512 
		 * @param unknown $ipids
		 * @param boolean $period
		 * @return void|array|Doctrine_Collection
		 */
		public static function get_patients_chart($ipids, $period = false)
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
		    	//ISPC-2661 pct.13 Carmen 11.09.2020
		        //$sql_period = ' (DATE(custom_event_date) != "0000-00-00" AND custom_event_date BETWEEN ? AND ? ) ';
		    	$sql_period = ' (DATE(form_start_date) != "0000-00-00" AND form_start_date BETWEEN ? AND ? ) ';
		        //--
		        $sql_period_params = array( $period['start'], $period['end'] );
		    }
		    else
		    {
		    	//ISPC-2661 pct.13 Carmen 11.09.2020
		        //$sql_period = ' DATE(custom_event_date) != "0000-00-00"  ';
		    	$sql_period = ' DATE(form_start_date) != "0000-00-00"  ';
		    	//--
		        
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockCustomEvent')
		    ->where('isdelete= "0" ')
		    ->andWhereIn('ipid', $ipids)
		    //ISPC-2661 pct.13 Carmen 11.09.2020
		    //->orderBy('custom_event_date ASC');
		    ->orderBy('form_start_date ASC');
		    //--
		    
		    if ( ! empty($delcform)) {
		        $patient->andwhereNotIn("contact_form_id",$delcform);
		    }
		    
		    if ( ! empty($sql_period)) {
		        $patient->andWhere( $sql_period , $sql_period_params);
		    }
		    
		    $patientlimit = $patient->fetchArray();
		    
		    return $patientlimit;
		}
		
		
	}

?>