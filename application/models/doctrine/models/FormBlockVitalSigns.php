<?php
//	#ISPC-2512PatientCharts
	Doctrine_Manager::getInstance()->bindComponent('FormBlockVitalSigns', 'MDAT');
	
	class FormBlockVitalSigns extends BaseFormBlockVitalSigns {
		
		
		public function getPatientFormBlockVitalSigns($ipid = '', $contact_form_id = 0 , $allow_deleted = false)
		{
		   
			$vitalsigns = Doctrine_Query::create()
			 ->select('*')
			 ->from('FormBlockVitalSigns')
			 ->where('ipid = ? ', $ipid)
			 ->andWhere('contact_form_id = ?', $contact_form_id)
			 ;
			
			
			if ( ! $allow_deleted) {
			   $vitalsigns->andWhere('isdelete = "0"');
			}
			$vitalsigns->orderBy('id ASC');
			$vital_signs_array = $vitalsigns->fetchArray();

			
			if($vital_signs_array)
			{
				return $vital_signs_array;
			}
			 
		}
		
		public function getPatientFormBlockVitalSigns1($ipid, $contact_form_id )
		{
			$vitalsigns = Doctrine_Query::create()
			->select('*')
			->from('FormBlockVitalSigns')
			->where('ipid = "' . $ipid . '"')
			->andWhere('contact_form_id = "' . $contact_form_id . '"')
			->andWhere('isdelete = "0"');
			$vital_signs_array = $vitalsigns->fetchArray();
				
			if($vital_signs_array)
			{
				return $vital_signs_array;
			}
		
		}
		
		
		
		public function getWeightChart($ipid,$period = false)
		{
		    $cf = new ContactForms();
		    $delcf = $cf->get_deleted_contactforms($ipid);
		
		    foreach ($delcf as $keycf => $valcf)
		    {
		        $delcform[] = $valcf;
		    }
		    //print_r($delcontform); exit;
		    $delcontactform = implode( $delcform, ",");
		
		    if($period)
		    {
		        $sql_period = ' AND DATE(f.signs_date) != "1970-01-01"  AND DATE(f.signs_date) BETWEEN DATE("'.$period['start'].'") AND  DATE("'.$period['end'].'") ';
		    } 
		    else
		    { 
		        $sql_period = ' AND DATE(f.signs_date) != "1970-01-01"  ';
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockVitalSigns f')
		    ->where('f.isdelete="0"')
		    ->leftJoin("ContactForms c")
		    ->andWhere('c.id = f.contact_form_id ')
		    ->andWhere('c.isdelete = 0 '.$sql_period.'  ')
		    ->andWhere('f.weight != 0.00 ')
		    ->andWhere('f.ipid =  "'.$ipid.'" ')
		    ->andwhere("c.id  NOT IN (" . $delcontactform . ")")
		    ->orderBy('f.signs_date asc');
		    
		    $patientlimit = $patient->fetchArray();
		
		    foreach($patientlimit as $key_cf  => $val_cf)
		    {
		        $info_chart[$key_cf]['weight'] = $val_cf['weight'];
		        $info_chart[$key_cf]['date']= $val_cf['signs_date'];
		    }
		    	
		    return $info_chart;
		}
		
		
		
		public function get_patients_weight_chart($ipids,$period = false)
		{
		    
		    if(!is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		    
		    
		    $cf = new ContactForms();
		    $delcf = $cf->get_patients_deleted_contactforms($ipids);
		
		    foreach ($delcf as $key_ipid => $valcf)
		    {
		        foreach($valcf as $kdcf=>$vcfdel)
		        {
    		        $delcform[] = $vcfdel;
		        }
		    }

		    if(empty($delcform)){
    		    $delcform[]="99999999999";
		    }
		    
		    if($period)
		    {
		        $sql_period = ' AND DATE(signs_date) != "1970-01-01"  AND DATE(signs_date) BETWEEN DATE("'.$period['start'].'") AND  DATE("'.$period['end'].'") ';
		    } 
		    else
		    { 
		        $sql_period = ' AND DATE(signs_date) != "1970-01-01"  ';
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockVitalSigns')
		    ->where('isdelete="0"')
		    ->andWhere('weight != 0.00 '.$sql_period.'  ')
		    ->andWhereIn('ipid',$ipids)
		    ->andwhereNotIn("contact_form_id",$delcform)
		    ->orderBy('signs_date asc');
		    $patientlimit = $patient->fetchArray();
		
		    foreach($patientlimit as $key_cf  => $val_cf)
		    {
		        $info_chart[$val_cf['ipid']][$key_cf]['weight'] = $val_cf['weight'];
		        $info_chart[$val_cf['ipid']][$key_cf]['date']= $val_cf['signs_date'];
		    }
		    	
		    return $info_chart;
		}
		
		
		
		public function get_patients_head_circumference_chart($ipids,$period = false)
		{
		    
		    if(!is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		    
		    
		    $cf = new ContactForms();
		    $delcf = $cf->get_patients_deleted_contactforms($ipids);
		
		    foreach ($delcf as $key_ipid => $valcf)
		    {
		        foreach($valcf as $kdcf=>$vcfdel)
		        {
    		        $delcform[] = $vcfdel;
		        }
		    }

		    if(empty($delcform)){
    		    $delcform[]="99999999999";
		    }
		    
		    if($period)
		    {
		        $sql_period = ' AND DATE(signs_date) != "1970-01-01"  AND DATE(signs_date) BETWEEN DATE("'.$period['start'].'") AND  DATE("'.$period['end'].'") ';
		    } 
		    else
		    { 
		        $sql_period = ' AND DATE(signs_date) != "1970-01-01"  ';
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockVitalSigns')
		    ->where('isdelete="0"')
		    ->andWhere('head_circumference != 0.00 '.$sql_period.'  ')
		    ->andWhereIn('ipid',$ipids)
		    ->andwhereNotIn("contact_form_id",$delcform)
		    ->orderBy('signs_date asc');
		    $patientlimit = $patient->fetchArray();
		
		    foreach($patientlimit as $key_cf  => $val_cf)
		    {
		        $info_chart[$val_cf['ipid']][$key_cf]['head_circumference'] = $val_cf['head_circumference'];
		        $info_chart[$val_cf['ipid']][$key_cf]['date']= $val_cf['signs_date'];
		    }
		    	
		    return $info_chart;
		}
		
		
		
		
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

		    //flowerpower
// 		    if(empty($delcform)){
//     		    $delcform[]="99999999999";
// 		    }
		    
		    $sql_period_params = array();
		    
		    if($period)
		    {
// 		        $sql_period = ' AND DATE(signs_date) != "1970-01-01"  AND DATE(signs_date) BETWEEN DATE("'.$period['start'].'") AND  DATE("'.$period['end'].'") ';
		        
		    	$sql_period = ' (DATE(signs_date) != "1970-01-01" AND DATE(signs_date) BETWEEN DATE(?) AND DATE(?) ) ';

		        $sql_period_params = array( $period['start'], $period['end'] );
		    } 
		    else
		    { 
		        $sql_period = ' DATE(signs_date) != "1970-01-01"  ';
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockVitalSigns')
		    ->where('isdelete= "0" ')
		    //flowerpower
// 		    ->andWhere('1 '.$sql_period.'  ') 
		    ->andWhereIn('ipid', $ipids)
		    ->orderBy('signs_date ASC');
		    
		    if ( ! empty($delcform)) {
		    	$patient->andwhereNotIn("contact_form_id",$delcform);
		    }
		    
		    if ( ! empty($sql_period)) {
		    	$patient->andWhere( $sql_period , $sql_period_params);
		    }
		    
		    
		    $patientlimit = $patient->fetchArray();

		    $info_chart = array();
		    
		    foreach($patientlimit as $key_cf  => $val_cf)
		    {
		        $info_chart[$val_cf['ipid']][$key_cf]['weight'] = $val_cf['weight'];
		        $info_chart[$val_cf['ipid']][$key_cf]['height'] = $val_cf['height'];
		        $info_chart[$val_cf['ipid']][$key_cf]['head_circumference'] = $val_cf['head_circumference'];
		        $info_chart[$val_cf['ipid']][$key_cf]['waist_circumference'] = $val_cf['waist_circumference'];
		        $info_chart[$val_cf['ipid']][$key_cf]['date']= $val_cf['signs_date'];
		        
		        $info_chart[$val_cf['ipid']][$key_cf]['oxygen_saturation']= $val_cf['oxygen_saturation'];
		        $info_chart[$val_cf['ipid']][$key_cf]['temperature']= $val_cf['temperature'];
		        $info_chart[$val_cf['ipid']][$key_cf]['blood_sugar']= $val_cf['blood_sugar'];
		        $info_chart[$val_cf['ipid']][$key_cf]['puls']= $val_cf['puls'];//ISPC-2512 Ancuta 21.04.2020
		        $info_chart[$val_cf['ipid']][$key_cf]['respiratory_frequency']= $val_cf['respiratory_frequency'];//ISPC-2512 Ancuta 21.04.2020

		        $info_chart[$val_cf['ipid']][$key_cf]['blood_pressure']= array(
		        		'systolic'=>$val_cf['blood_pressure_b'], //ISPC-2661 pct.1 Carmen 10.09.2020
		        		'diastolic'=>$val_cf['blood_pressure_a'] //ISPC-2661 pct.1 Carmen 10.09.2020
		        ); 
		        
		        $info_chart[$val_cf['ipid']][$key_cf]['source']= $val_cf['source'];
		        
		        //ISPC-2661 pct.1 Carmen 10.09.2020
		        $info_chart[$val_cf['ipid']][$key_cf]['create_user']= $val_cf['create_user'];
		        //--	        
		        
		        if ($val_cf['height'] > 0 && $val_cf['weight'] > 0) {
		            
		            //https://www.cdc.gov/nccdphp/dnpao/growthcharts/training/bmiage/page5_1.html
		            //BMI = body mass index
		            $info_chart[$val_cf['ipid']][$key_cf]['__bmi'] = round( ($val_cf['weight'] / pow($val_cf['height'], 2))* 10000 , 2);
		            //Mosteller = body area surface  root of ((height * weight) / 3600)
		            $info_chart[$val_cf['ipid']][$key_cf]['__bas'] = round( sqrt( ($val_cf['height'] * $val_cf['weight']) / 3600) , 2);
		        } 
		        
		    }
		    	
		    return $info_chart;
		}
		
		

		/**
		 * ! this fn depends on get_patients_chart->orderBy('signs_date ASC');
		 * in other words if you change get_patients_chart orderBy() you have to edit this fn also
		 * @param array|string $ipids
		 * @param string $period
		 */
		public static function get_patients_chart_last_values($ipids, $period = false)
		{
			$all_vital_signs = self::get_patients_chart($ipids, $period);
				
			$latest_vital_signs = array();
				
			foreach ( $all_vital_signs as $k_ipid => $ipid_values) {
		
				foreach ( $ipid_values as $row ) {
		
					foreach ($row as $k => $v){
		
						if( is_array($v) ) {
							//for blood_pressure likes go 1 level deep
							foreach ($v as $k_l2 => $v_l2) {
		
		
								if( (float)$v_l2 != 0 ) {
									$latest_vital_signs [$k_ipid] [$k] [$k_l2] = $v_l2;
								}
							}
								
						} elseif( (float)$v != 0 ) {
							$latest_vital_signs [$k_ipid] [$k] = $v;
						} else {
							// 							die_claudiu("echo", $k, $v );
						}
		
					}
		
				}
		
			}
				
			return $latest_vital_signs;
		}
		
		/**
		 * ! this fn depends on get_patients_chart->orderBy('signs_date ASC');
		 * in other words if you change get_patients_chart orderBy() you have to edit this fn also
		 * @param array|string $ipids
		 * @param string $period
		 * ISPC-2661 pct.12 Carmen 14.09.2020
		 */
		public static function get_patients_chart_admission_values($ipids, $period = false, $first_ever_admission = true)
		{
			$all_vital_signs = self::get_patients_chart($ipids, $period);
			
			$admission_vital_signs = array();
		
			foreach ( $all_vital_signs as $k_ipid => $ipid_values) {
				if($first_ever_admission)
				{
					$row = $ipid_values[0];
					
					foreach ($row as $k => $v){
		
						if( is_array($v) ) {
							//for blood_pressure likes go 1 level deep
							foreach ($v as $k_l2 => $v_l2) {
		
		
								if( (float)$v_l2 != 0 ) {
									$admission_vital_signs [$k_ipid] [$k] [$k_l2] = $v_l2;
								}
							}
		
						} elseif( (float)$v != 0 ) {
							$admission_vital_signs [$k_ipid] [$k] = $v;
						} else {
							// 							die_claudiu("echo", $k, $v );
						}
					}
					break;
				}
				else
				{
					$pread = new PatientReadmission();
					$last_adm = $pread->findReadmissionFromDate($k_ipid);
					$last_date_adm = strtotime(date('Y-m-d', strtotime($last_adm['admission']['date'])));
					$dayofadmission = false;
					
					foreach ( $ipid_values as $row ) {
						
						if(strtotime(date('Y-m-d', strtotime($row['date']))) == $last_date_adm)
						{
							$dayofadmission = true;
							foreach ($row as $k => $v){
				
								if( is_array($v) ) {
									//for blood_pressure likes go 1 level deep
									foreach ($v as $k_l2 => $v_l2) {
				
				
										if( (float)$v_l2 != 0 ) {
											$admission_vital_signs [$k_ipid] [$k] [$k_l2] = $v_l2;
										}
									}
				
								} elseif( (float)$v != 0 ) {
									$admission_vital_signs [$k_ipid] [$k] = $v;
								} else {
									// 							die_claudiu("echo", $k, $v );
								}
							}
						}
						else if(strtotime(date('Y-m-d', strtotime($row['date']))) > $last_date_adm && !$dayofadmission)
						{
							foreach ($row as $k => $v){
									
								if( is_array($v) ) {
									//for blood_pressure likes go 1 level deep
									foreach ($v as $k_l2 => $v_l2) {
											
											
										if( (float)$v_l2 != 0 ) {
											$admission_vital_signs [$k_ipid] [$k] [$k_l2] = $v_l2;
										}
									}
										
								} elseif( (float)$v != 0 ) {
									$admission_vital_signs [$k_ipid] [$k] = $v;
								} else {
									// 							die_claudiu("echo", $k, $v );
								}
							}
							break;
						}
					}
					
				}
			}
			
			return $admission_vital_signs;
		}
		
		//ISPC-2664 Carmen 28.09.2020
		public static function get_patients_chart_byelement($ipids, $period = false, $element)
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
		
			//flowerpower
			// 		    if(empty($delcform)){
			//     		    $delcform[]="99999999999";
			// 		    }
		
			$sql_period_params = array();
		
			if($period)
			{
				// 		        $sql_period = ' AND DATE(signs_date) != "1970-01-01"  AND DATE(signs_date) BETWEEN DATE("'.$period['start'].'") AND  DATE("'.$period['end'].'") ';
		
				$sql_period = ' (DATE(signs_date) != "1970-01-01" AND DATE(signs_date) BETWEEN DATE(?) AND DATE(?) ) ';
		
				$sql_period_params = array( $period['start'], $period['end'] );
			}
			else
			{
				$sql_period = ' DATE(signs_date) != "1970-01-01"  ';
			}
		
			$patient = Doctrine_Query::create()
			->select('*')
			->from('FormBlockVitalSigns')
			->where('isdelete= "0" ')
			//flowerpower
			// 		    ->andWhere('1 '.$sql_period.'  ')
			->andWhereIn('ipid', $ipids)
			->orderBy('signs_date ASC');
		
			if ( ! empty($delcform)) {
				$patient->andwhereNotIn("contact_form_id",$delcform);
			}
		
			if ( ! empty($sql_period)) {
				$patient->andWhere( $sql_period , $sql_period_params);
			}
		
		
			$patientlimit = $patient->fetchArray();
		
			$info_chart = array();
		
			foreach($patientlimit as $key_cf  => $val_cf)
			{
				if($val_cf[$element] > 0)
				{
					$info_chart[$val_cf['ipid']][$key_cf][$element] = $val_cf[$element];
					$info_chart[$val_cf['ipid']][$key_cf]['date']= $val_cf['signs_date'];
				}		
			}
			 
			return $info_chart;
		}
		
		public static function get_patients_chart_last_values_byelement($ipids, $period = false, $element)
		{
			$all_vital_signs = self::get_patients_chart_byelement($ipids, $period, $element);
		
			$latest_vital_signs = array();
		
			foreach ( $all_vital_signs as $k_ipid => $ipid_values) {
		
				foreach ( $ipid_values as $row ) {
		
					foreach ($row as $k => $v){
		
						if( is_array($v) ) {
							//for blood_pressure likes go 1 level deep
							foreach ($v as $k_l2 => $v_l2) {
		
		
								if( (float)$v_l2 != 0 ) {
									$latest_vital_signs [$k_ipid] [$k] [$k_l2] = $v_l2;
								}
							}
		
						} elseif( (float)$v != 0 ) {
							$latest_vital_signs [$k_ipid] [$k] = $v;
						} else {
							// 							die_claudiu("echo", $k, $v );
						}
		
					}
		
				}
		
			}
		
			return $latest_vital_signs;
		}
		//--
		
	}
?>