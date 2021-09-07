<?php
	//ISPC-2523 Carmen 13.04.2020
	//#ISPC-2512PatientCharts
	Doctrine_Manager::getInstance()->bindComponent('FormBlockSuckoff', 'MDAT');

	class FormBlockSuckoff extends BaseFormBlockSuckoff {
		
		/**
		 * translations are grouped into an array
		 * @var unknown
		 */
		const LANGUAGE_ARRAY    = 'formblocksuckoff_lang';
		
		/**
		 * define the FORMID and FORMNAME, if you want to piggyback some triggers
		 * @var unknown
		 */
		const TRIGGER_FORMID    = null;
		const TRIGGER_FORMNAME  = 'frm_formblocksuckoff';
		
		/**
		 * insert into patient_files will use this
		 */
		const PATIENT_FILE_TABNAME  = 'FormBlockSuckoff';
		const PATIENT_FILE_TITLE    = 'FormBlockSuckoff PDF'; //this will be translated
		
		/**
		 * insert into patient_course will use this
		 */
		const PATIENT_COURSE_TYPE       = 'K'; 
		
		const PATIENT_COURSE_TABNAME    = 'formblocksuckoff';
		
		//this is just for demo, another one is used on contact_form save
		const PATIENT_COURSE_TITLE      = 'FormBlockSuckoff was created';

		
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
		        $sql_period = ' (DATE(suckoff_date) != "0000-00-00" AND suckoff_date BETWEEN ? AND ? ) ';
		        
		        $sql_period_params = array( $period['start'], $period['end'] );
		    }
		    else
		    {
		        $sql_period = ' DATE(suckoff_date) != "0000-00-00"  ';
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockSuckoff')
		    ->where('isdelete= "0" ')
		    ->andWhereIn('ipid', $ipids)
		    ->orderBy('suckoff_date ASC');
		    
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