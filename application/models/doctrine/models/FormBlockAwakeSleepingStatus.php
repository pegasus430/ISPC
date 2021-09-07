<?php
/**
 * @author Carmen
 * ISPC-2516 Carmen 09.04.2020
 * 
 * 
 * #ISPC-2512PatientCharts
 * 
 * 
 */
	
	Doctrine_Manager::getInstance()->bindComponent('FormBlockAwakeSleepingStatus', 'MDAT');

	class FormBlockAwakeSleepingStatus extends BaseFormBlockAwakeSleepingStatus {
		
		/**
		 * translations are grouped into an array
		 * @var unknown
		 */
		const LANGUAGE_ARRAY    = 'formblockawakesleepingstatus_lang';
		
		/**
		 * define the FORMID and FORMNAME, if you want to piggyback some triggers
		 * @var unknown
		 */
		const TRIGGER_FORMID    = null;
		const TRIGGER_FORMNAME  = 'frm_formblockawakesleepingstatus';
		
		/**
		 * insert into patient_files will use this
		 */
		const PATIENT_FILE_TABNAME  = 'FormBlockAwakeSleepingStatus';
		const PATIENT_FILE_TITLE    = 'FormBlockAwakeSleepingStatus PDF'; //this will be translated
		
		/**
		 * insert into patient_course will use this
		 */
		const PATIENT_COURSE_TYPE       = 'K'; 
		
		const PATIENT_COURSE_TABNAME    = 'formblockawakesleepingstatus';
		
		//this is just for demo, another one is used on contact_form save
		const PATIENT_COURSE_TITLE      = 'FFormBlockAwakeSleepingStatus was created';

		
		/**
		 * @author Ancuta 
		 * ISPC-2512 
		 * @param unknown $ipids
		 * @param boolean $period
		 * @return void|array|Doctrine_Collection
		 * #ISPC-2512PatientCharts
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
		    	//ISPC-2661 pct.13 Carmen 09.09.2020
		        //$sql_period = ' (DATE(status_date) != "0000-00-00" AND status_date BETWEEN ? AND ? ) ';
		    	$sql_period = ' (DATE(form_start_date) != "0000-00-00" AND form_start_date BETWEEN ? AND ? ) ';
		        //--
		        
		        $sql_period = ' DATE(positioning_date) != "0000-00-00" OR DATE(form_start_date) != "0000-00-00"  '; //ISPC-2661 pct.13 Carmen 09.09.2020
		    }
		    else
		    {
		        //ISPC-2661 pct.13 Carmen 09.09.2020
		        //$sql_period = ' DATE(status_date) != "0000-00-00"  ';
		    	$sql_period = ' DATE(form_start_date) != "0000-00-00"  ';
		    	//--
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockAwakeSleepingStatus')
		    ->where('isdelete= "0" ')
		    ->andWhereIn('ipid', $ipids)
		    //ISPC-2661 pct.13 Carmen 09.09.2020
		    //->orderBy('status_date ASC');
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