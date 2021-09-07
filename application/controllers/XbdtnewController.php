<?php
class XbdtnewController extends Zend_Controller_Action {
	public function init() {
		$this->codes = array (
				
				'media' => array (
						'start' => '0020',
						'end' => '0021' 
				),
				'package' => array (
						'start' => '8000',
						'length' => '8100' 
				),
				'file' => array (
						'doctor_number' => '9100', // 01691002488531
						'creation_date' => '9103',
						'encoding' => '9106', // 2 is 8-bit ASCII
						'exportperiod' => '9601', // ddmmyyyyddmmyyyy 02596011610200717102007
						'begintransfer' => '9602', // hhmmsscc 017960219174500
						'alllength' => '9202',
						'noofpackages' => '9203' 
				) // 1 ????
,
				
				'software' => array (
						'company' => '0102', // smart-Q Softwaresysteme GmbH
						'name' => '0103', // ISPC
						'compatible' => '0104' 
				) // 0220104AT-Kompatible
,
				
				'doctor' => array (
						'number' => '0201', // 01602012488531
						'practice' => '0202', // practice type ← we take here "4"
						'name' => '0203',
						'group' => '0204',
						'street' => '0205',
						'zipcity' => '0206', // 023020640000 Hasenhausen
						'phone' => '0208',
						'fax' => '0209',
						'nofodocs' => '0225' 
				),
				
				'patient' => array (
						'id' => '3000',
						'lastname' => '3101',
						'firstname' => '3102',
						'dob' => '3103',
						'healthinsno' => '3105',
						'healthinsno2' => '3119', // ISPC-1438 IF the Versichertennummer in ISPC has a leading letter (like "A123456789") then please use the field 3119 instead of 3105. if the versichertennummer has no leading letter then dont change anything and continue using field 3105
						'zipcity' => '3106',
						'street' => '3107',
						'street2' => '1270', // ISPC-1438 the heimnetz quartalsexport plz change the STREET id from 3107 to 1270
						'insurance_type' => '3108', // 01331085000 <--- insurance type = 1 = Mitglied , 3 = Familienversicherter 5 = Rentner;
						'gender' => '3110', // 1 = male, 2 = female
						'admissiondate' => '3610', // 017361004082007
						'admissiondate2' => '4102', // 017410201092011
						'generationdate' => '4102',
						'generationquarter' => '4101',
						'firstiddate' => '4102',
						'phone' => '3626',
						'familydoctor' => '3702',
						'familydoctoraddress' => '3703', // 0713703Dr.Unbekannt#Krefelder Str. 3#40000#Hasenhausen<--- family doctor adress
						                                 // 'kili1' => '4111', //41118377503
						                                 // 'kili2' => '4112', //4112M
						'kili3' => '4113', // 41131
						'kili4' => '4106', // 011410600
						'kili5' => '4122', // 011412200
						'kili6' => '4239', // 011423900
						'kili7' => '4110', // add 4110 as empty field
						'kili8' => '4121', // add 4121 as empty field
						'bsnr' => '5098', // (BSNR of client)
						'lanr' => '5099' 
				) // (LANR of user )
,
				
				'patient_insurance' => array (
						
						// correct: 4111 - IK (7-digit) 4104 - Kassennummer (5-digit), ISPC-1564
						// possible error
						// Skype 16-01-05, fixed the error
						'ikno' => '4111', // 01641114080005<--- IK Number of health insurance
						'kassenno' => '4104', // 014410437601<--- Kassennummer of health insurance
						'type' => '4112', // insurance type = 1 = Mitglied , 3 = Familienversicherter 5 = Rentner;
						'ewstatus' => '4113' 
				) // east / west status : means all client except the Dessau one have here a "1"
,
				
				'action' => array (
						'start' => '5000', // 017500016102007 <---here starts a refundable ACTION<-- code 5000 ← in this case DATE
						'actioncode' => '5001', // Leistungsziffer of action - action code
						'name' => '5002',
						'time' => '5006' 
				) // hhmm
,
				
				'diagnosis' => array (
						'name' => '6000', // 0316000Verdacht auf Gastritis<---main diagnosis
						'icd' => '6001',
						'end' => '6003',
						'date' => '5000' 
				) // date
,
				
				'diagnosis_side' => array (
						'name' => '6205',
						'date' => '6200',
						'patient' => '3000',
						'length' => '8100',
						'identification' => '8000' 
				) // date
,
				
				'heimnetz' => array ( // ISPC-1438
						'admission_first' => '93531', // Ersteinschreibung Heimnetz
						'next_quartal' => '93532', // Folgequartal Heimnetz
						'eva1' => '93555', // EVA Symbolziffer
						'eva2' => '93556',
						'eva3' => '93557',
						'lkoor_va' => '93536',
						'lkoor_kh' => '93537',
						'lkoor_other' => '93535' 
				),
				
				'kili' => array (
						'1' => '9105', // keep it like it is 0129105001
						'2' => '9210', // keep it like it is 014921010/93
						'3' => '9213', // keep it like it is 014921302/94
						'4' => '9600', // keep it like it is 01096002
						'5' => '0101', // keep it like it is 0170101A0008083
						'6' => '4106', // keep it like it is 011410600
						'7' => '4110', // add 4110 as empty field
						'8' => '4121' 
				) // add 4121 as empty field
 
		)
		;
	}
	function getAllClientPatients($clientid, $whereepid) {
		
		$actpatient = Doctrine_Query::create ()->select ( "p.ipid" )->from ( 'PatientMaster p' );
		$actpatient->leftJoin ( "p.EpidIpidMapping e" );
		$actpatient->where ( $whereepid . 'e.clientid = ' . $clientid );
		
		$actipidarray = $actpatient->fetchArray ();
		
		return $actipidarray;
	}
	
	
	private function admitted_in_period($start, $end) {
		
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		// $whereepid = $this->getDocCondition();
		$final_ipids = array ();
		$patient_ipids = array();
		
		//$active_cond = $this->getTimePeriod ( $quarterarr, $yeararr, $montharr );
// 		$active_cond['date_sql'] = ' %date% >= "'.date('Y-m-d', strtotime($start)).'" AND %date% <= "'.date('Y-m-d', strtotime($end)).'"';
		$active_cond['date_sql'] = ' DATE(%date%) >= "'.date('Y-m-d', strtotime($start)).'" AND DATE(%date%) <= "'.date('Y-m-d', strtotime($end)).'"';
		$report_period['start'][0] = $start;
		$report_period['end'][0] = $end;
		
		$allpatients = $this->getAllClientPatients ( $logininfo->clientid, $whereepid );
		foreach ( $allpatients as $allpatient_item ) {
			$allpatients_str .= '"' . $allpatient_item ['ipid'] . '",';
			$allpatients_arr [] = $allpatient_item ['ipid'];
		}
		
		$allpatients_arr [] = '999999';
		
		$actpatient = Doctrine_Query::create ()->select ( "*,AES_DECRYPT(last_name,'" . Zend_Registry::get ( 'salt' ) . "') as last_name,AES_DECRYPT(first_name,'" . Zend_Registry::get ( 'salt' ) . "') as first_name,convert(AES_DECRYPT(zip,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as zip,convert(AES_DECRYPT(street1,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as street1,convert(AES_DECRYPT(city,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as city,convert(AES_DECRYPT(phone,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as phone,convert(AES_DECRYPT(sex,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as sex" )->from ( 'PatientMaster p' )->where ( 'isdelete = 0' )->andWhere ( 'isstandby = 0' )->andWhere ( 'isstandbydelete = 0' )->andWhere ( '(' . str_replace ( '%date%', 'admission_date', $active_cond ['date_sql'] ) . ')' )->orderBy ( "convert(AES_DECRYPT(last_name,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) ASC" );
		
		$actpatient->leftJoin ( "p.EpidIpidMapping e" );
		$actpatient->andWhere ( $whereepid . ' e.clientid = ' . $logininfo->clientid );
		$actpatient->orderBy ( "convert(AES_DECRYPT(last_name,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) ASC" );
		
		//echo $actpatient->getSqlQuery(); exit;
		
		$actipidarray = $actpatient->fetchArray ();
		
		foreach ( $actipidarray as $key => $val ) {
			$admited_in_q [] = $val ['ipid'];
		}
		
		if (empty ( $admited_in_q )) {
			$admited_in_q [] = '999999';
		}

		$readmission_add = Doctrine_Query::create ()->select ( "*" )->from ( "PatientReadmission p" )->where ( 'p.date_type = 1' )->andWhereIn ( 'p.ipid', $admited_in_q )->groupBy ( 'ipid' )->orderBy ( 'date ASC' );
		$add_array = $readmission_add->fetchArray ();
		
		foreach ( $add_array as $par => $rad ) {
			$admissions [$rad ['ipid']] [] = $rad ['date'];
		}
		
		if (! empty ( $admissions )) {
			foreach ( $admissions as $ipid => $pat_adm ) {
				foreach ( $pat_adm as $k_admission => $v_admision ) {
					if ($k_admission == '0') { // check only if first admission is in period
						$pat_adm_dates [$ipid] = date ( 'd.m.Y', strtotime ( $v_admision ) );
						foreach ( $report_period ['start'] as $k_start => $v_start ) {
							if (Pms_CommonData::isintersected ( strtotime ( $pat_adm_dates [$ipid] ), strtotime ( $pat_adm_dates [$ipid] ), strtotime ( $v_start ), strtotime ( $report_period ['end'] [$k_start] ) )) {
// 							if (Pms_CommonData::isintersected ( strtotime ( $v_admision ), strtotime ( $v_admision ), strtotime ( $v_start ), strtotime ( $report_period ['end'] [$k_start] ) )) {
								$patient_ipids [] = $ipid;
							}
						}
					}
				}
			}
		}
		
		return $patient_ipids;
	}
	
	public function getTimePeriod($quarterarr, $yeararr, $montharr) {
		if ($quarterarr == 'only_now' && $yeararr == 'only_now' && $montharr == 'only_now') {
			$active_sql = '(date(%date%) >= "' . date ( 'Y' ) . '-' . date ( 'm' ) . '-' . date ( 'd' ) . '") OR ';
			$admission_sql = '(date(%date%) < "' . date ( 'Y' ) . '-' . (date ( 'm' ) + 1) . '-01") OR ';
			$date_sql = ' year(%date%) > "1900" AND ';
			$interval_location_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
			$interval_vv_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
			$interval_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
			$negated_interval_sql = '(year(%date_start%) < "1900" AND year(%date_end%) > "2100") OR ';
			$onlynowactive = 1;
		} else {
			$onlynowactive = 0;
			if (! empty ( $quarterarr )) {
				$montharr = array ();
				foreach ( $quarterarr as $quart ) {
					switch ($quart) {
						case '2' :
							$montharr [] = 4;
							$montharr [] = 5;
							$montharr [] = 6;
							break;
						
						case '3' :
							$montharr [] = 7;
							$montharr [] = 8;
							$montharr [] = 9;
							break;
						
						case '4' :
							$montharr [] = 10;
							$montharr [] = 11;
							$montharr [] = 12;
							break;
						
						default :
							$montharr [] = 1;
							$montharr [] = 2;
							$montharr [] = 3;
							break;
					}
				}
			}
			
			foreach ( $yeararr as $year ) {
				if (is_numeric ( $year )) {
					$year_sql .= '"' . $year . '",';
					
					if (is_array ( $montharr ) && sizeof ( $montharr )) {
						foreach ( $montharr as $month ) {
							if (is_numeric ( $month )) {
								// $active_sql .= '(month(%date%) >= "'.$month.'" AND year(%date%) >= "'.$year.'") OR ';
								// $admission_sql .= '(year(%date%) <= "'.$year.'" AND month(%date%) <= "'.$month.'") OR ';
								
								$this_month = $year . '-' . $month . '-01';
								$this_month_end = date ( 'Y-m-d', strtotime ( '-1 day', strtotime ( '+1 month', strtotime ( $this_month ) ) ) );
								$next_month = date ( 'Y-m-', strtotime ( '+1 month', strtotime ( $this_month ) ) ) . '01';
								
								$active_sql .= '(date(%date%) >= "' . $year . '-' . $month . '-01") OR ';
								$admission_sql .= '(date(%date%) < "' . $next_month . '") OR ';
								$interval_location_sql .= '(((date(%date_start%) >= "' . $year . '-' . $month . '-01") AND date(%date_start%) < "' . $next_month . '" AND (date(%date_end%) >= "' . $year . '-' . $month . '-01"  OR date(%date_end%) = "0000-00-00") ) OR ((date(%date_start%) >= "' . $year . '-' . $month . '-01" AND date(%date_start%) < "' . $next_month . '") AND (date(%date_end%) < "' . $next_month . '" AND (date(%date_end%) >= "' . $year . '-' . $month . '-01" OR date(%date_end%) = "0000-00-00")))) OR ';
								$interval_vv_sql .= '(((date(%date_start%) >= "' . $year . '-' . $month . '-01") AND date(%date_start%) < "' . $next_month . '" AND (date(%date_end%) >= "' . $year . '-' . $month . '-01"  OR date(%date_end%) = "0000-00-00") ) OR ((date(%date_start%) >= "' . $year . '-' . $month . '-01" AND date(%date_start%) < "' . $next_month . '") AND (date(%date_end%) < "' . $next_month . '" AND (date(%date_end%) >= "' . $year . '-' . $month . '-01" OR date(%date_end%) = "0000-00-00")))) OR ';
								$interval_sql .= '(((date(%date_start%) <= "' . $year . '-' . $month . '-01") AND (date(%date_end%) >= "' . $year . '-' . $month . '-01")) OR ((date(%date_start%) >= "' . $year . '-' . $month . '-01") AND (date(%date_start%) < "' . $next_month . '"))) OR ';
								$negated_interval_sql .= '((date(%date_start%) < "' . $year . '-' . $month . '-01") AND date(%date_end%) < "' . $year . '-' . $month . '-01" AND %date_end% IS NOT NULL) OR  date(%date_start%) >= "' . $next_month . '") OR ';
								$readmission_delete_sql .= '(%date_start% IS NOT NULL AND (date(%date_start%) < "' . $year . '-' . $month . '-01") AND date(%date_end%) > "' . $this_month_end . '") AND ';
							} else {
								$active_sql .= '(year(%date%) >= "' . $year . '") OR ';
								$admission_sql .= '(year(%date%) <= "' . $year . '") OR ';
								$interval_location_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
								$interval_vv_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
								$interval_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
								$negated_interval_sql .= '((year(%date_start%) > "' . $year . '") OR (year(%date_end%) < "' . $year . '")) OR ';
								$readmission_delete_sql .= '(%date_start% IS NOT NULL AND (year(%date_start%) < "' . $year . '") AND (year(%date_end%) > "' . $year . '")) AND ';
							}
						}
					} else {
						$active_sql .= '(year(%date%) >= "' . $year . '") OR ';
						$admission_sql .= '(year(%date%) <= "' . $year . '") OR ';
						// $interval_sql .= '((year(%date_start%) >= "'.$year.'") AND (year(%date_end%) <= "'.$year.'")) OR ';
						$interval_location_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
						$interval_vv_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
						$interval_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
						$negated_interval_sql .= '((year(%date_start%) > "' . $year . '") OR (year(%date_end%) < "' . $year . '" AND %date_end% IS NOT NULL)) OR ';
						$readmission_delete_sql .= '(%date_start% IS NOT NULL AND (year(%date_start%) < "' . $year . '") AND (year(%date_end%) > "' . $year . '")) AND ';
					}
				}
			}
			
			foreach ( $montharr as $month ) {
				if (is_numeric ( $month )) {
					$month_sql .= '"' . $month . '",';
				}
			}
			
			if (! empty ( $month_sql )) {
				$date_sql .= ' month(%date%) IN (' . substr ( $month_sql, 0, - 1 ) . ') AND ';
			}
			
			if (! empty ( $year_sql )) {
				$date_sql .= ' year(%date%) IN (' . substr ( $year_sql, 0, - 1 ) . ') AND ';
			}
		}
		
		if (! empty ( $date_sql )) {
			$return ['date_sql'] = substr ( $date_sql, 0, - 5 );
			$return ['active_sql'] = substr ( $active_sql, 0, - 4 );
			$return ['admission_sql'] = substr ( $admission_sql, 0, - 4 );
			$return ['interval_location_sql'] = substr ( $interval_location_sql, 0, - 4 );
			$return ['interval_vv_sql'] = substr ( $interval_vv_sql, 0, - 4 );
			$return ['interval_sql'] = substr ( $interval_sql, 0, - 4 );
			$return ['negated_interval_sql'] = substr ( $negated_interval_sql, 0, - 4 );
			$return ['readmission_delete_sql'] = substr ( $readmission_delete_sql, 0, - 5 );
			$return ['onlynowactive'] = $onlynowactive;
			
			return $return;
		} else {
			return false;
		}
	}
	
	/* ################################################################################## */
	/* ################################################################################## */
	/* ############################ ####################################### */
	/* ############################ ####################################### */
	/* ############################ NEW ####################################### */
	/* ############################ ####################################### */
	/* ############################ ####################################### */
	/* ################################################################################## */
	/* ################################################################################## */
	
	public function exportAction() {
		set_time_limit ( 0 );
		
		
		// error_reporting(E_ALL);
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		$codes = $this->codes;
		$insurance_statuses = array(
				'F' => '3000',
				'M' => '1000',
				'R' => '5000',
				'N' => 'XYX',
				'S' => 'XYX',
		);
		
		
		$this->view->types = array (
				'long' => 'SAPV Version mit Leistungen',
				'long2' => 'SAPV mit Leistungen und Nebendiagnosen',
				'isynet' => 'SAPV mit Leistungen - ISYNET',
				'isynetlong' => 'SAPV - ISYNET mit Leistungen und Nebendiagnosen',
				'short' => 'aktive Patienten ohne Leistungen',
				'ado' => 'WL - Nur Neuaufnahmen des Quartals',
				'heimnetz' => 'Heimnetz - Quartalsexport',
				'hisynet' => 'Heimnetz - ISYNET', 
				'only_with_le' => 'nur mit Leistungen' 
		);
		
		$client_details = Client::getClientDataByid ( $clientid );
		$user_details = User::getUserDetails ( $logininfo->userid );
		$client_users = User::get_client_users ( $clientid, '1' );
		$this->view->user_details = $user_details;
		$this->view->client_users = $client_users;
		
		// Group actions select
		$client_actions = XbdtActions::client_xbdt_actions ( $clientid );
		$groups = array ();
		foreach ( $client_actions as $k => $actions_data ) {
			if (! in_array ( $actions_data ['groupname'], $groups )) {
				$groups [] = $actions_data ['groupname'];
			}
		}
		
		$this->view->action_groups = $groups;
		
		
		
		$this->view->types = array (
				'long' => 'SAPV Version mit Leistungen',
				'long2' => 'SAPV mit Leistungen und Nebendiagnosen',
				'isynet' => 'SAPV mit Leistungen - ISYNET',
				'isynetlong' => 'SAPV - ISYNET mit Leistungen und Nebendiagnosen',
				'short' => 'aktive Patienten ohne Leistungen',
				'ado' => 'WL - Nur Neuaufnahmen des Quartals',
				'heimnetz' => 'Heimnetz - Quartalsexport',
				'hisynet' => 'Heimnetz - ISYNET' ,
				'only_with_le' => 'nur mit Leistungen' 
		);
		
		$current_q = Pms_CommonData::get_dates_of_quarter ( 'current', null, "d.m.Y" );
		
		$this->view->period_start = $current_q ['start'];
		$this->view->period_end = $current_q ['end'];
		
		$module = new Modules();
		if($module->checkModulePrivileges("108", $clientid))
		{
			$vv_only_if_visit_day = "1";
		}
		else
		{
			$vv_only_if_visit_day = "0";
		}
		
		
		
		if ($this->getRequest ()->isPost ()) {
			
			
			$lanr = $_POST['lanr'];
			$type = $_POST['type'];
			$s = array(" ","	","\n","\r");
			$ado_id = trim(str_replace($s,array(),$_POST['ado_id']));
			$ado_text_id = trim(str_replace($s,array(),$_POST['ado_text_id']));
			$ado_text = $_POST['ado_text'];
			$ik_option = $_POST['ik_option'];
			$status_option = $_POST['status_option'];
			$diagnosis_side = $_POST['diagnosis_side_option'];
			$diagnosis_main = $_POST['diagnosis_main_option'];
			$action_user = $_POST['user'];
			$action_group = $_POST['group_type'];
			
			$bsnr_option = $_POST['bsnr_option'];        //ISPC-2569 Lore 17.03.2020
			
			
			if(!empty($_REQUEST['patients']) && !empty($_REQUEST['period']['start']) && !empty($_REQUEST['period']['end'])) {
				
				$ipids = $_REQUEST['patients'];
				$period_start = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['start']));
				$period_end = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['end']));
				
				$quarter_first_day = strtotime($_REQUEST['period']['start']);
				$quarter_last_day = strtotime($_REQUEST['period']['end']);
			
				
				$qnr = $this->CurrentQuarter($period_start);
				
				$quarterid = $qnr.date('Y', strtotime($period_start));
				
				$select = "AES_DECRYPT(p.last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(p.first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(p.zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(p.street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(p.city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(p.phone,'".Zend_Registry::get('salt')."') using latin1) as phone, convert(AES_DECRYPT(p.sex,'".Zend_Registry::get('salt')."') using latin1) as sex,";
				$periods = array ( '0' => array('start' => date('Y-m-d H:i:s', strtotime($period_start)), 'end' => date('Y-m-d H:i:s', strtotime($period_end))));
				
				$period_days = PatientMaster::getDaysInBetween(date("Y-m-d", strtotime($period_start)), date("Y-m-d", strtotime($period_end)), false);

				$active_cond['interval_sql'] = '((date(%date_start%) <= "'.$periods[0]['start'].'") AND (date(%date_end%) >= "'.$periods[0]['start'].'")) OR ((date(%date_start%) >= "'.$periods[0]['start'].'") AND (date(%date_start%) < "'.$periods[0]['end'].'"))';
					
				if($_REQUEST['tp'] == 'ipids') {
					
				} elseif($type == 'ado') {
					//get only newly admitted patients
					$periods = array();
				}
				
				//$patients_arr = Pms_CommonData::patients_days(array('ipids' => $ipids, 'include_standby' => '1','periods' => $periods, 'client' => $clientid), $select);
				$patients_arr = Pms_CommonData::patients_days(array('ipids' => $ipids, 'periods' => $periods, 'client' => $clientid), $select); // TODO -498 - remove standby 02.09.2016
					
					
				if($type == 'hisynet' || $type == 'heimnetz' || $type == 'isynet' || $type == 'isynetlong') {
					//$patients_full_periods = Pms_CommonData::patients_days(array('ipids' => array_keys($patients_arr), 'include_standby' => '1','client' => $clientid), $select);
					$patients_full_periods = Pms_CommonData::patients_days(array('ipids' => array_keys($patients_arr), 'client' => $clientid), $select); // TODO -498 - remove standby 02.09.2016
				}
				
				
				
				foreach($patients_arr as $patient) {
				
					if($_REQUEST['dbg'] == '5') {
						flush();
						ob_flush();
						echo '-';
					}
				
					$patient['ipid'] = $patient['details']['ipid'];
					$patient['epid'] = $patient['details']['epid'];
				
					$healthins = new  PatientHealthInsurance();
					$pathealthins = $healthins->getPatientHealthInsurance($patient['ipid']);
				
				
					if($pathealthins[0]['privatepatient'] != '1' && ($pathealthins[0]['direct_billing'] != '1' || $type != 'isynetlong')) { //no private patients & no Direktabrechnung for isynetlong http://jira.significo.de:8080/browse/ISPC-1710
				
						$patins_no = $pathealthins[0]['insurance_no']; //patient insurance number
							
						if(!is_numeric(substr($patins_no,0,1)) && !empty($patins_no)) {
							$healthinstoken = 'healthinsno2';
						} else {
							$healthinstoken = 'healthinsno';
						}
							
						$kassen_no = $pathealthins[0]['kvk_no']; //kassen nummer
						$ik_no = $pathealthins[0]['institutskennzeichen']; //IK number
							
						
						
						switch($ik_option) {
							
							case '9':
								$ik_no = substr($ik_no, -9);
								break;
								
							case '7':
								$ik_no = substr($ik_no, -7);
								break;
							
							default:
								//do nothing
								break;
						}
						
							
						switch($status_option) {
							
							case '1':
								$ins_type = substr($insurance_statuses[$pathealthins[0]['insurance_status']],0,1);
								$ins_type_3108 = substr($insurance_statuses[$pathealthins[0]['insurance_status']],0,1);
								break;
								
							case '1000':
								$ins_type = $insurance_statuses[$pathealthins[0]['insurance_status']];
								$ins_type_3108 = $insurance_statuses[$pathealthins[0]['insurance_status']];
								break;
									
							default:
								$ins_type = $pathealthins[0]['insurance_status'];
								$ins_type_3108 = $pathealthins[0]['insurance_status'];
								break;
							
						}
				
				
						if(!empty($pathealthins[0]['companyid']) && $pathealthins[0]['companyid'] != 0 && empty($ik_no)){
							$helathins = Doctrine::getTable('HealthInsurance')->find($pathealthins[0]['companyid']);
							$healtharray = $helathins->toArray();
							$ik_no = $healtharray['iknumber'];
						}
				
				
						$export_patients[$patient['ipid']][] = $codes['package']['start'].'0101'; //doctor start header
						$export_patients[$patient['ipid']][] = $codes['package']['length'].'{PPL}'; //length
						$export_patients[$patient['ipid']][] = $codes['patient']['id'].preg_replace('/[^0-9]/','',$patient['details']['epid']); //only numbers
						$export_patients[$patient['ipid']][] = $codes['patient']['lastname'].$patient['details']['last_name'];
						$export_patients[$patient['ipid']][] = $codes['patient']['firstname'].$patient['details']['first_name'];
						$export_patients[$patient['ipid']][] = $codes['patient']['dob'].date('dmY',strtotime($patient['details']['birthd']));
						$export_patients[$patient['ipid']][] = $codes['patient'][$healthinstoken].$patins_no;
						$export_patients[$patient['ipid']][] = $codes['patient']['zipcity'].$patient['details']['zip'].' '.$patient['details']['city'];
						$export_patients[$patient['ipid']][] = $codes['patient']['street'].$patient['details']['street1'];
						$export_patients[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type_3108;
						$export_patients[$patient['ipid']][] = $codes['patient']['gender'].$patient['details']['sex'];
				
						$export_patients[$patient['ipid']][] = $codes['patient']['generationquarter'].$quarterid;
						$export_patients[$patient['ipid']][] = $codes['patient']['firstiddate'].'{FIDEXP}';
				
						$export_patients[$patient['ipid']][] = $codes['patient_insurance']['kassenno'].$kassen_no; //.' kassen no'
						$export_patients[$patient['ipid']][] = $codes['patient']['kili4'].'00';
						$export_patients[$patient['ipid']][] = $codes['patient']['kili7'];
						//$export_patients[$patient['ipid']][] = $codes['patient_insurance']['ikno'].substr($ik_no, (strlen($ik_no) - 7)); //.'IK no'
						$export_patients[$patient['ipid']][] = $codes['patient_insurance']['ikno'].$ik_no; //.'IK no'
						$export_patients[$patient['ipid']][] = $codes['patient_insurance']['type'].$ins_type;
						$export_patients[$patient['ipid']][] = $codes['patient_insurance']['ewstatus'].'1';
						$export_patients[$patient['ipid']][] = $codes['patient']['kili5'].'00';
						$export_patients[$patient['ipid']][] = $codes['patient']['kili8'];
						$export_patients[$patient['ipid']][] = $codes['patient']['kili6'].'00';
				
				
						//var_dump($patient);
				
						//var_dump($export_patients); exit;
				
				
				
						//reset activities checks
				
						$patient_no_vv = false;
						$patient_no_asses = false;
						$patient_no_nurse = false;
						$patient_no_doctor = false;
						$patient_no_contactforms = false;
						$patient_no_tel = false;
						$patient_no_quartal = false;
						$patient_no_evas = false;
						$patient_no_lkoor = false;
						$patient_no_le = false;
						$pat_discharge_date = '';
				
				
						$pat_discharge = PatientDischarge::getPatientDischarge($patient['ipid']);
						if($pat_discharge) {
							$pat_discharge_date = strtotime($pat_discharge[0]['discharge_date']);
						}
				
						//SAPV VOLLVERSORGUNG 92157
				
						$sapv_vv = Doctrine_Query::create()
						->select('*')
						->from('SapvVerordnung')
						->where("isdelete=0 AND (".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql']).") ")
						->andWhere("ipid='" . $patient['ipid'] . "'")
						->andWhere("verordnet LIKE '%4%'")
						->orderBy("id");
						if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
							echo 'VV query:'.$sapv_vv->getSqlQuery().'<br /><br />';
						}
						$sapv_vv_arr = $sapv_vv->fetchArray();
				
				
						$vv_all_days = array();
						$vv_all_days_final = array();
						$vv_all_days_of = array();
						$vv_cut = '';
				
						if($sapv_vv_arr) {
				
							$vv_range_days = array();
							foreach($sapv_vv_arr as $vv){
								if($vv['verordnungbis'] == '0000-00-00 00:00:00') {
									$vv_cut = time(); //stop SAPV counting at current day
								}
								if($vv['status'] == 1 && $vv['verorddisabledate'] !='0000-00-00 00:00:00' && $vv['verorddisabledate'] !='1970-01-01 00:00:00' ){
				
									$vv_disabled = strtotime($vv['verorddisabledate']);
									if($pat_discharge_date > 0) {
										if($pat_discharge_date < $vv_disable) {
											$vv_cut = $pat_discharge_date;
										} else {
											$vv_cut = strtotime("-1 day",$vv_disable);
										}
									} else {
										$vv_cut = strtotime("-1 day",$vv_disable);
									}
				
								} else {
									if($pat_discharge_date > 0 && strtotime($vv['verordnungbis']) >= $pat_discharge_date) {
										$vv_cut = $pat_discharge_date;
									}
								}
								if($vv_cut) {
									$vv['verordnungbis'] = date("Y-m-d H:i", $vv_cut);
								}
								$vv_range_days = Pms_CommonData::generateDateRangeArray($vv['verordnungam'],$vv['verordnungbis'],'+1 day','d.m.Y');
								foreach ($vv_range_days as $vv_day){
									if(in_array($vv_day, $patient['active_days'])) {
										$vv_all_days[] = date('dmY',strtotime($vv_day));
										$vv_all_days_of[] = date('Y-m-d',strtotime($vv_day));
									}
								}
							}
						}
				
							
							
						$vv_all_days = array_unique($vv_all_days);
						$vv_all_days_of = array_unique($vv_all_days_of);
				
						//SAPV Teilversorgung
				
						$sapv_tv = Doctrine_Query::create()
						->select('*')
						->from('SapvVerordnung')
						->where("isdelete=0 AND (".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql']).")")
						->andWhere("ipid='" . $patient['ipid'] . "'")
						->andWhere("verordnet LIKE '%3%'")
						->orderBy("id");
						if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
							echo 'TV query:'.$sapv_tv->getSqlQuery().'<br /><br />';
						}
						$sapv_tv_arr = $sapv_tv->fetchArray();
				
							
						//var_dump($sapv_tv_arr);
						//exit;
				
						$tv_all_days = array();
						$tv_all_days_final = array();
						$tv_all_days_of = array();
						$tv_cut = '';
				
						if($sapv_tv_arr) {
				
							$tv_range_days = array();
							foreach($sapv_tv_arr as $tv){
								if($tv['verordnungbis'] == '0000-00-00 00:00:00') {
									$tv_cut = time(); //stop SAPV counting at current day
								}
								if($tv['status'] == 1 && $tv['verorddisabledate'] !='0000-00-00 00:00:00' && $tv['verorddisabledate'] !='1970-01-01 00:00:00' ){
				
									$tv_disabled = strtotime($tv['verorddisabledate']);
									if($pat_discharge_date > 0) {
										if($pat_discharge_date < $tv_disable) {
											$tv_cut = $pat_discharge_date;
										} else {
											$tv_cut = strtotime("-1 day",$tv_disable);
										}
									} else {
										$tv_cut = strtotime("-1 day",$tv_disable);
									}
				
								} else {
									if($pat_discharge_date > 0 && strtotime($tv['verordnungbis']) >= $pat_discharge_date) {
										$tv_cut = $pat_discharge_date;
									}
								}
								if($tv_cut) {
									$tv['verordnungbis'] = date("Y-m-d H:i", $tv_cut);
								}
								$tv_range_days = Pms_CommonData::generateDateRangeArray($tv['verordnungam'],$tv['verordnungbis'],'+1 day','d.m.Y');
								foreach ($tv_range_days as $tv_day){
									if(in_array($tv_day, $patient['active_days'])) {
										$tv_all_days[] = date('dmY',strtotime($tv_day));
										$tv_all_days_of[] = date('Y-m-d',strtotime($tv_day));
									}
								}
							}
						}
				
						$tv_all_days = array_unique($tv_all_days);
						$tv_all_days_of = array_unique($tv_all_days_of);
				
						//SAPV Beratung
				
						$sapv_be = Doctrine_Query::create()
						->select('*')
						->from('SapvVerordnung')
						->where("isdelete=0 AND (".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql']).") ")
						->andWhere("ipid='" . $patient['ipid'] . "'")
						->andWhere("verordnet LIKE '%1%'")
						->orderBy("id");
						if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
							echo 'BE query:'.$sapv_be->getSqlQuery().'<br /><br />';
						}
						$sapv_be_arr = $sapv_be->fetchArray();
				
						$be_all_days = array();
						$be_all_days_final = array();
						$be_all_days_of = array();
						$be_cut = '';
				
						if($sapv_be_arr) {
				
							$be_range_days = array();
							foreach($sapv_be_arr as $be){
								if($be['verordnungbis'] == '0000-00-00 00:00:00') {
									$be_cut = time(); //stop SAPV counting at current day
								}
								if($be['status'] == 1 && $be['verorddisabledate'] !='0000-00-00 00:00:00' && $be['verorddisabledate'] !='1970-01-01 00:00:00' ){
				
									$be_disabled = strtotime($be['verorddisabledate']);
									if($pat_discharge_date > 0) {
										if($pat_discharge_date < $be_disable) {
											$be_cut = $pat_discharge_date;
										} else {
											$be_cut = strtotime("-1 day",$be_disable);
										}
									} else {
										$be_cut = strtotime("-1 day",$be_disable);
									}
				
								} else {
									if($pat_discharge_date > 0 && strtotime($be['verordnungbis']) >= $pat_discharge_date) {
										$be_cut = $pat_discharge_date;
									}
								}
								if($be_cut) {
									$be['verordnungbis'] = date("Y-m-d H:i", $be_cut);
								}
								$be_range_days = Pms_CommonData::generateDateRangeArray($be['verordnungam'],$be['verordnungbis'],'+1 day','d.m.Y');
								foreach ($be_range_days as $be_day){
									if(in_array($be_day, $patient['active_days'])) {
										$be_all_days[] = date('dmY',strtotime($be_day));
										$be_all_days_of[] = date('Y-m-d',strtotime($be_day));
									}
								}
							}
						}
				
						$be_all_days = array_unique($be_all_days);
						$be_all_days_of = array_unique($be_all_days_of);
							
							
						foreach($vv_all_days as $vv_key => $vv_day_item){
							$vv_day_rearranged = date('d.m.Y', strtotime($vv_all_days_of[$vv_key]));
				
							if(!in_array($vv_day_rearranged, $patient['hospital']['real_days_cs']) && in_array($vv_day_rearranged, $patient['real_active_days']))
							{
								$vv_all_days_final[$vv_day_item] = $vv_day_item;
								$vv_all_days_final_fide[$vv_day_item] = $vv_day_rearranged;
							}
						}
				
						array_unique($vv_all_days_final);
						array_unique($vv_all_days_final_fide);
							
						foreach($tv_all_days as $tv_key => $tv_day_item){
							$tv_day_rearranged = date('d.m.Y', strtotime($tv_all_days_of[$tv_key]));
				
							if(!in_array($tv_day_rearranged, $patient['hospital']['real_days_cs']) && in_array($tv_day_rearranged, $patient['real_active_days']))
							{
								$tv_all_days_final[$tv_day_item] = $tv_day_item;
								$tv_all_days_final_fide[$tv_day_item] = $tv_day_rearranged;
							}
						}
				
						array_unique($tv_all_days_final);
						array_unique($tv_all_days_final_fide);
							
							
						foreach($be_all_days as $be_key => $be_day_item){
							$be_day_rearranged = date('d.m.Y', strtotime($be_all_days_of[$be_key]));
							if(!in_array($be_day_rearranged, $patient['hospital']['real_days_cs']) && in_array($be_day_rearranged, $patient['real_active_days']))
							{
								$be_all_days_final[$be_day_item] = $be_day_item;
								$be_all_days_final_fide[$be_day_item] = $be_day_rearranged;
							}
						}
				
						array_unique($be_all_days_final);
						array_unique($be_all_days_final_fide);
							
							
							
							
						//nurse visits now
				
						$nursevisits = array();
						$nursevisits_final = array();
						$nursevisits_final_fide = array();
						$nursestr = '';
						$overall_nursevisits_dates = array();
						
						//get form from verlauf, created to see what`s deleted
						$nursecourse_q = Doctrine_Query::create()
						->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title, AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') as tabname")
						->from('PatientCourse')
						->where('ipid = ?', $patient['ipid'])
						->andWhere("AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') = 'F'")
						->andWhere("wrong = 0")
						->andWhere("AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') = 'kvno_nurse_form'")
						->orderBy('course_date ASC');
						//				if($patient['ipid'] == '1bd43099956683c8a2ffae86e51e40c8abe89ed1') {
						//					echo $nursecourse_q->getSqlQuery();
						//				}
						$nursecourse = $nursecourse_q->fetchArray();
				
						$nursestr = '';
				
						foreach($nursecourse as $nurse_visit){
							$nursestr .= '"'.$nurse_visit['recordid'].'",';
						}
				
						$nursestr .= '"9999999999999999999999"';
				
				
						$nursevisit_q = Doctrine_Query::create()
						->select("*")
						->from("KvnoNurse")
						->where('id IN ('.$nursestr.')');
						//				if($patient['ipid'] == '1bd43099956683c8a2ffae86e51e40c8abe89ed1') {
						//					echo $nursevisit_q->getSqlQuery();
						//				}
						$nursevisits = $nursevisit_q->fetchArray();
				
						$i = 0;
						foreach($nursevisits as $nurse_visit) {
							$visit_date = date('dmY', strtotime($nurse_visit['vizit_date']));
							if(in_array($visit_date, $tv_all_days_final)) { //only if TV
								$nursevisits_final[$i]['date'] = $visit_date;
								$nursevisits_final_fide[$i] = $nurse_visit['vizit_date'];
								$i++;
							}
							$overall_nursevisits_dates[] = $visit_date; 
						}
				
				
						//doctor visits now
				
						$doctorvisits = array();
						$doctorvisits_final = array();
						$doctorvisits_final_fide = array();
						$doctorstr = '';
						$overall_doctorvisits_dates = array();
				
						//get form from verlauf, created to see what`s deleted
						$doctorcourse_q = Doctrine_Query::create()
						->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title, AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') as tabname")
						->from('PatientCourse')
						->where('ipid = ?', $patient['ipid'])
						->andWhere("AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') = 'F'")
						->andWhere("wrong = 0")
						->andWhere("AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') = 'kvno_doctor_form'")
						->orderBy('course_date ASC');
						//				if($patient['ipid'] == '1bd43099956683c8a2ffae86e51e40c8abe89ed1') {
						//					echo $doctorcourse_q->getSqlQuery();
						//				}
						$doctorcourse = $doctorcourse_q->fetchArray();
				
						$doctorstr = '';
				
						foreach($doctorcourse as $doctor_visit){
							$doctorstr .= '"'.$doctor_visit['recordid'].'",';
						}
				
						$doctorstr .= '"9999999999999999999999"';
				
				
						$doctorvisit_q = Doctrine_Query::create()
						->select("*")
						->from("KvnoDoctor")
						->where('id IN ('.$doctorstr.')');
						//				if($patient['ipid'] == '1bd43099956683c8a2ffae86e51e40c8abe89ed1') {
						//					echo $doctorvisit_q->getSqlQuery();
						//				}
						$doctorvisits = $doctorvisit_q->fetchArray();
				
						$i = 0;
						foreach($doctorvisits as $doctor_visit) {
							$visit_date = date('dmY', strtotime($doctor_visit['vizit_date']));
							if(in_array($visit_date, $tv_all_days_final)) { //only if TV
								$doctorvisits_final[$i]['date'] = $visit_date;
								$doctorvisits_final_fide[$i] = $doctor_visit['vizit_date'];
								$i++;
							}
							$overall_doctorvisits_dates[] = $visit_date;
						}
				
						
						
						//Contact forms 03.11.2016
						$contactforms = array();
						$contactforms_final = array();
						$contactforms_final_fide = array();
						$overall_contactforms_dates = array();
						
						//Get deleted contact froms from patient course
						$deleted_cf = Doctrine_Query::create()
						->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
						->from('PatientCourse')
						->where('wrong=1')
						->andWhere('ipid = ?', $patient['ipid'])
						->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
						->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'");
						$deleted_cf_array = $deleted_cf->fetchArray();
						
						$excluded_cf_ids[] = '99999999999';
						foreach($deleted_cf_array as $k_dcf => $v_dcf)
						{
						    $excluded_cf_ids[] = $v_dcf['recordid'];
						}
						
						
						//get cf in period exclude deleted
						$cf = new ContactForms();
						$contactforms  = $cf->get_multiple_contact_form_by_periods(array($patient['ipid']), $periods, $excluded_cf_ids);

						$i = 0;
						foreach($contactforms[$patient['ipid']] as $cf_id=>$cf_data) {
						    $cf_visit_date = date('dmY', strtotime($cf_data['billable_date']));
						    if(in_array($cf_visit_date, $tv_all_days_final)) { //only if TV
						        $contactforms_final[$i]['date'] = $cf_visit_date;
						        $contactforms_final_fide[$i] = $cf_data['billable_date'];
						        $i++;
						    }
						    $overall_contactforms_dates[] = $cf_visit_date;
						}

						
						$telephones = array();
						$tel_final = array();
						$tel_final_fide = array();
				
						$telephone_q = Doctrine_Query::create()
						->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
						->from('PatientCourse')
						->where('ipid = ?', $patient['ipid'])
						->andWhere("AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') = 'U' ")
						->andWhere("wrong = 0")
						->andWhere('source_ipid = ""')
						->orderBy('course_date ASC');
				
						$telephones = $telephone_q->fetchArray();
				
						$i = 0;
						foreach ( $telephones as $telephone ) {
							$telarr = explode ( "|", $telephone ['course_title'] );
				
							if (count ( $telarr ) == 3) { //method implemented with 3 inputs
								$tel_date = $telarr [2];
							} else if (count ( $telarr ) != 3 && count ( $telarr ) < 3) { //old method before anlage 10
								$tel_date = $telephone ['course_date'];
							} else if (count ( $telarr ) != 3 && count ( $telarr ) > 3) { //new method (U) 3 inputs and 1 select newly added in verlauf
								$tel_date = $telarr [3];
								$with = trim($telarr [0]);
							}
				
							$tel_date_rearranged = date('dmY',strtotime($tel_date));
							if(in_array($tel_date_rearranged, $be_all_days_final) && $with){
								$tel_final[$i]['date'] = $tel_date_rearranged;
								$tel_final[$i]['with'] = $with;
								$tel_final_fide[$i] = $tel_date;
								$i++;
							}
						}
							
							
						//first admmission / next quartal
							
							
						$first_period = $patients_full_periods[$patient['ipid']]['active_periods'][min(array_keys($patients_full_periods[$patient['ipid']]['active_periods']))]; //sexy, eh?
							
						$period_start_ts = strtotime($period_start);
						$period_end_ts = strtotime($period_end);
							
						$fp_start_ts = strtotime($first_period['start']);
							
						if($first_period['end'] == '01.01.1970') {
							$fp_end_ts = time(); //open end, use "now"
						} else {
							$fp_end_ts = strtotime($first_period['end']);
						}
							
						if (
								($fp_start_ts >= $period_start_ts && $fp_start_ts <= $period_end_ts)
						) {
							$first_quartal = true;
						} else {
							$first_quartal = false;
						}
							
						if($first_quartal == true && $patient['ipid'] == 'bffad4c083fe00827f2cb64bc083ca6547b40fe9') {
							//echo $fp_start_ts.' - '.$fp_end_ts.' - '.$period_start_ts.' - '.$period_end_ts; var_dump($patients_full_periods[$patient['ipid']]['active_periods']); var_dump($first_period);exit;
						}
							
							
						//var_dump($patients_full_periods[$patient['ipid']]['active_periods']); exit;
							
						//XB - EVA
							
						$evas = array();
						$eva_final = array();
							
						$eva_q = Doctrine_Query::create()
						->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
						->from('PatientCourse')
						->where('ipid = ?', $patient['ipid'])
						->andWhere("AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') = 'XB' ")
						->andWhere("wrong = 0")
						->andWhere('course_date BETWEEN "'.$period_start.'" AND "'.$period_end.'"')
						->andWhere('source_ipid = ""')
						->orderBy('course_date ASC');
							
						$evas = $eva_q->fetchArray();
							
						$i = 0;
						foreach ( $evas as $eva ) {
				
							$first_char = substr(trim($eva ['course_title']),0,1);
							$eva_date = date('dmY',strtotime($eva ['course_date']));
				
							if(!in_array($eva_date, $eva_dates)) {
								if($first_char == '1' || $first_char == '2' || $first_char == '3') {
									$eva_final[$i]['type'] = $first_char;
									$eva_final[$i]['date'] = $eva_date;
									$eva_final[$i]['time'] = date('Hm',strtotime($eva ['course_date']));;
									$eva_dates[$i] = $eva_date;
									$eva_final_fide[$i] = $eva ['course_date'];
									$i++;
								}
							}
						}
							
							
						//Verlauf "L" koordinators ISPC-1724
				
						$lkoors = array();
						$lkoors_final = array();
							
						//grab koordinators
						$usergroup = new Usergroup();
						$MasterGroups = array("6");
						$master_group_ids = $usergroup->getUserGroups($MasterGroups);
				
						foreach($master_group_ids as $key => $value)
						{
							$groups_id[$value['groupmaster']] = $value['id'];
							$group_info[$value['id']]['master'] = $value['groupmaster'];
						}
				
						$usermod = new User();
						$groups_users_array = $usermod->getuserbyGroupId($groups_id, $clientid, true);
				
				
						foreach($groups_users_array as $key => $val)
						{
							if($group_info[$val['groupid']]['master'] == '6')
							{
								$koordinators[] = $val ['id'];
							}
								
						}
							
						if($koordinators) {
				
							$lkoors_q = Doctrine_Query::create()
							->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
							->from('PatientCourse')
							->where('ipid = ?', $patient['ipid'])
							->andWhere("AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') = 'L' ")
							->andWhere("wrong = 0")
							->andWhereIn("create_user", $koordinators)
							->andWhere('course_date BETWEEN "'.$period_start.'" AND "'.$period_end.'"')
							->andWhere('source_ipid = ""')
							->orderBy('course_date ASC');
								
							$lkoors = $lkoors_q->fetchArray();
								
							$i = 0;
							foreach ( $lkoors as $lkoor ) {
				
								$first_2chars = substr(trim($lkoor ['course_title']),0,2);
								$lkoor_date = date('dmY',strtotime($lkoor ['course_date']));
				
								if(!in_array($lkoor_date, $lkoor_dates)) {
									$lkoors_final[$i]['type'] = strtolower($first_2chars);
									$lkoors_final[$i]['date'] = $lkoor_date;
									$lkoors_final[$i]['time'] = date('Hm',strtotime($lkoor ['course_date']));;
									$lkoor_dates[$i] = $lkoor_date;
									$lkoor_final_fide[$i] = $lkoor ['course_date'];
									$i++;
								}
							}
								
						}
						
						$actions_le_final = array(); 
						$actions_le = array(); 
						
						if($action_user || $action_group || $type == "only_with_le") {
							$pa = new PatientXbdtActions();
							
							$cond['client'] = $clientid;
							$cond['ipids'] = array($patient['ipid']);
							
							if($action_user || $action_group){
								$cond['user'] = $action_user;
								$cond['group'] = $action_group;
							}
							
							if(!empty($_REQUEST['period']['start']) && !empty($_REQUEST['period']['end']) ){
								$cond['start'] = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['start']));
								$cond['end'] = date('Y-m-d 23:59:59', strtotime($_REQUEST['period']['end']));
							}
							
							$actions_le = $pa->get_actions_filtered($cond);
							
							if(!empty($actions_le)) {
								$i = 0;
								foreach ( $actions_le as $action_le ) {
									 	$action_le_date =  date('dmY',strtotime($action_le ['action_date']));
										$actions_le_final[$i]['code'] = $action_le['XbdtActions']['action_id'];
										$actions_le_final[$i]['type'] = $action_le['XbdtActions']['name'];
										$actions_le_final[$i]['date'] = $action_le_date;
										$actions_le_final[$i]['time'] = date('Hm',strtotime($action_le ['action_date']));;
										$actions_le_final_fide[$i] = $action_le ['action_date'];
										$actions_le_billed[$patient['ipid']][$i] = $action_le ['id'];
										$i++;
								}
							}
							
						}
						
							
						//19c7e7a31dbffcfd9a19997ca4d3d5d1eeae1ddd
						if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
							//print_r("\n\n\n AAA");
							//print_r($death_date);
							//var_dump($death_date);
							//print_r($hospFinalDaysSecond);
							//var_dump($vv_all_days);
							//echo '<br />';
				
							echo 'LK:<br/>';
							var_dump($lkoor_final);
							echo '<br /><br />';
				
							echo 'Hospital days:<br/>';
							var_dump($hospFinalDaysSecond);
							echo '<br /><br />';
				
							echo 'Hospital start days:<br/>';
							var_dump($hospitalStartDays);
							echo '<br /><br />';
				
							echo 'Hospital end days:<br/>';
							var_dump($hospitalEndDays);
							echo '<br /><br />';
				
							echo 'ACTIVE DAYS:<br/>';
							var_dump($patient_active_days_final[$patient['ipid']] );
							echo '<br /><br />';
				
							echo 'VV<br/>';
							var_dump($vv_all_days_final);
							echo '<br /><br />';
				
							echo 'TV<br/>';
							var_dump($tv_all_days_final);
							echo '<br /><br />';
				
							echo 'BE<br/>';
							var_dump($be_all_days_final);
							echo '<br /><br />';
				
							echo 'Nurse visits<br/>';
							var_dump($nursevisits_final);
							echo '<br /><br />';
				
							echo 'Doctor visits<br/>';
							var_dump($doctorvisits_final);
							echo '<br /><br />';
				
							echo 'Telephones<br/>';
							var_dump($tel_final);
							echo '<br /><br />';
							exit;
							//var_dump($vv_all_days_of);
						}
				
				
				
						/*START GENERATING HERE*/
				
						if(sizeof($vv_all_days_final) == 0) {
							$patient_no_vv = true;
						}
				
				
						if(sizeof($nursevisits_final) == 0) {
							$patient_no_nurse = true;
						}
				
				
						if(sizeof($doctorvisits_final) == 0) {
							$patient_no_doctor = true;
						}
				
						if(sizeof($contactforms_final) == 0) {
							$patient_no_contactforms = true;
						}
				
						if(sizeof($tel_final) == 0) {
							$patient_no_tel = true;
						}
							
						if(sizeof($eva_final) == 0) {
							$patient_no_evas = true;
						}
							
						if(sizeof($lkoors_final) == 0) {
							$patient_no_lkoor = true;
						}
				
						if(sizeof($actions_le_final) == 0) {
							$patient_no_le = true;
						}
							
						//begin output based on type
							
							
						if($type == 'long' || $type == 'long2' || $type == 'isynet' || $type == 'isynetlong') {
				
							if ($type == 'isynet' || $type == 'isynetlong') {
								if($first_quartal === true) {
									$admission_date_4102 = strtotime($patient['details']['admission_date']);
								} else {
									$admission_date_4102 = $quarter_first_day;
								}
							}
								
							foreach($vv_all_days_final as $unique_day){
								if($vv_only_if_visit_day == "0") // no visit required for counting sapv VV (ISPC-1556)
								{
									$first_exported_id[$patient['ipid']][] = strtotime($vv_all_days_final_fide[$unique_day]);
									$export_patients[$patient['ipid']][] = $codes['action']['start'].$unique_day;
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92157';
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'SAPV VOLLVERSORGUNG';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
									//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
									$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];      //ISPC-2569 Lore 17.03.2020
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
									$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
								} 
								else
								{
									// display only if visits are done on that day
									if( in_array($unique_day,$overall_nursevisits_dates) || in_array($unique_day,$overall_doctorvisits_dates) || in_array($unique_day,$overall_contactforms_dates))
									{
										$first_exported_id[$patient['ipid']][] = strtotime($vv_all_days_final_fide[$unique_day]);
										$export_patients[$patient['ipid']][] = $codes['action']['start'].$unique_day;
										$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92157';
										$export_patients[$patient['ipid']][] = $codes['action']['name'].'SAPV VOLLVERSORGUNG';
										$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
										//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
										$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
										
										//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
										//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
										$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
									}
									
								}
				
							}
				
				
				
				
							$kvno_module = new Modules();
							if($kvno_module->checkModulePrivileges("86", $clientid))
							{
								$kvno_module_perms = "1";
							}
							else
							{
								$kvno_module_perms = "0";
							}
								
								
							//Assessment 92154 + "Erstkoordination" 92153
								
								
							$kvnoq =  Doctrine_Query::create()
							->select('*')
							->from('KvnoAssessment')
							->where('ipid = "'.$patient['ipid'].'"')
							->andWhere('status = "0"')
							->andWhere('iscompleted = 1');
							$assesment = $kvnoq->fetchArray();
							if($assesment && !empty($assesment[0]['completed_date']) && in_array(date('Y-m-d',strtotime($assesment[0]['completed_date'])), $period_days)) {
								$ass_completed_date =  date('dmY',strtotime($assesment[0]['completed_date']));
								$first_exported_id[$patient['ipid']][] = strtotime($assesment[0]['completed_date']);
									
								//case  "change billing method" module disabled or enabled but method 3(Koord and assessment) is used
								if($kvno_module_perms == '0' || ($kvno_module_perms == '1' && ($assesment[0]['billing_mode'] == "2" || $assesment[0]['billing_mode'] == "3")))
								{
									//if module is deactivate this should work as initial (adding both assessment and koord in anlage10)
									$export_patients[$patient['ipid']][] = $codes['action']['start'].$ass_completed_date;
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92154';
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'Assesmentpauschale';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
									//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
									$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
									
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
									$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
								}
				
				
								if($kvno_module_perms == '0' || ($kvno_module_perms == '1' && ($assesment[0]['billing_mode'] == "1" || $assesment[0]['billing_mode'] == "3")))
								{
									//$export_patients[$patient['ipid']][] = $codes['action']['start'].$ass_completed_date;
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92153';
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'Koordinationationspauschale';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
									//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
									$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
									
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
									$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
								}
				
							} else {
								$patient_no_asses = true;
							}
				
				
							//nurse visits
				
							if(sizeof($nursevisits_final) > 0) {
								foreach($nursevisits_final as $nurse_key => $nurse_day){
									$first_exported_id[$patient['ipid']][] = strtotime($nursevisits_final_fide[$nurse_key]);
									$export_patients[$patient['ipid']][] = $codes['action']['start'].$nurse_day['date'];
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92155';
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'Teilversorgung Besuch';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
									//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
									$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
									
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
									$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
				
								}
							}
				
				
							//doctor visits
				
							if(sizeof($doctorvisits_final) > 0) {
								foreach($doctorvisits_final as $doctor_key => $doctor_day){
									$first_exported_id[$patient['ipid']][] = strtotime($doctorvisits_final_fide[$doctor_key]);
									$export_patients[$patient['ipid']][] = $codes['action']['start'].$doctor_day['date'];
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92155';
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'Teilversorgung Besuch';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
									//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
									$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
									
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
									$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
				
								}
							}
				
				
							// contact forms
				
							if(sizeof($contactforms_final) > 0) {
							    
								foreach($contactforms_final as $cf_key => $cf_day){
									$first_exported_id[$patient['ipid']][] = strtotime($contactforms_final_fide[$cf_key]);
									$export_patients[$patient['ipid']][] = $codes['action']['start'].$cf_day['date'];
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92155';
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'Teilversorgung Besuch';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
									//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
									$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
									
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
									$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
				
								}
							}

							//telephones
				
							if(sizeof($tel_final) > 0) {
								foreach($tel_final as $tel_key => $tel_day){
									if($tel_day['with'] == 'mit Betroffenen') {
										$telcode = '92150';
									} else {
										$telcode = '92151';
									}
									$first_exported_id[$patient['ipid']][] = strtotime($tel_final_fide[$tel_key]);
									$export_patients[$patient['ipid']][] = $codes['action']['start'].$tel_day['date'];
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].$telcode;
									//						$export_patients[$patient['ipid']][] = $codes['action']['name'].'Beratung '.$tel_day['with'];
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'Beratungsleistung';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
									//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
									$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
									
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
									//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
									$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
				
								}
							}
						} elseif($type == 'hisynet' || $type == 'heimnetz') {
							$admission_date_4102 = '';
							$export_patients[$patient['ipid']][] = $codes['action']['start'].date('dmY',strtotime($patient['details']['admission_date']));
							if($first_quartal === true) {
								$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].$codes['heimnetz']['admission_first'];
								$export_patients[$patient['ipid']][] = $codes['action']['name'].'Ersteinschreibung Heimnetz';
								$admission_date_4102 = strtotime($patient['details']['admission_date']);
							} else {
								$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].$codes['heimnetz']['next_quartal'];
								$export_patients[$patient['ipid']][] = $codes['action']['name'].'Folgequartal Heimnetz';
								$admission_date_4102 = $quarter_first_day;
							}
							$export_patients[$patient['ipid']][] = $codes['action']['time'].date('Hm',strtotime($patient['details']['admission_date']));
							//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
							$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
							
							$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
				
				
							//EVA
				
							if(sizeof($eva_final) > 0) {
								foreach($eva_final as $eva_key => $eva_day){
									$evacode = 93554 + $eva_day['type'];
									$first_exported_id[$patient['ipid']][] = strtotime($eva_final_fide[$eva_key]);
									$export_patients[$patient['ipid']][] = $codes['action']['start'].$eva_day['date'];
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].$evacode;
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'EVA Symbolziffer';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].$eva_day['time'];
									//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
									$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
									
									$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
				
								}
							}
				
				
							if($type == 'hisynet') {
								//L koordinators
								if(sizeof($lkoors_final) > 0) {
									foreach($lkoors_final as $lk_key => $lk_day){
										switch($lk_day['type']) {
				
											case 'va':
												$lkcode = $codes['heimnetz']['lkoor_va'];
												break;
				
											case 'kh':
												$lkcode = $codes['heimnetz']['lkoor_kh'];
												break;
				
											default:
												$lkcode = $codes['heimnetz']['lkoor_other'];
												break;
										}
											
										$first_exported_id[$patient['ipid']][] = strtotime($lkoor_final_fide[$lk_key]);
										$export_patients[$patient['ipid']][] = $codes['action']['start'].$lk_day['date'];
										$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].$lkcode;
										$export_patients[$patient['ipid']][] = $codes['action']['time'].$lk_day['time'];
										//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
										$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
										
										$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
											
									}
								}
									
								if($_REQUEST['dbg'] == 'dump2' && $patient['ipid'] == $_REQUEST['ipid']) {
									var_dump($lkoors_final);
									var_dump($export_patients[$patient['ipid']]);
									exit;
								}
									
							}
				
						} elseif ($type == 'ado') {
							if($ado_text && $ado_text_id) {
								$export_patients[$patient['ipid']][] = $codes['action']['start'].date('dmY',strtotime($patient['details']['admission_date']));
								$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].$ado_text_id;
								$export_patients[$patient['ipid']][] = $codes['action']['name'].$ado_text;
								$export_patients[$patient['ipid']][] = $codes['action']['time'].date('Hm',strtotime($patient['details']['admission_date']));
								//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
								$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
								
								$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
							}
							$ado_admission_dates[$patient['ipid']] = strtotime($patient['details']['admission_date']);
						}
							
						
						// LE actions
						
						if(sizeof($actions_le_final) > 0) {
							foreach($actions_le_final as $le_key => $action_le){
								$first_exported_id[$patient['ipid']][] = strtotime($actions_le_final_fide[$le_key]);
								
								$export_patients[$patient['ipid']][] = $codes['action']['start'].$action_le['date'];
								$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].$action_le['code'];
								$export_patients[$patient['ipid']][] = $codes['action']['name'].$action_le['type'];
								$export_patients[$patient['ipid']][] = $codes['action']['time'].$action_le['time'];
								//$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
								$export_patients[$patient['ipid']][] = $bsnr_option.$client_details[0]['betriebsstattennummer'];        //ISPC-2569 Lore 17.03.2020
								
								$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
						
							}
						}
						
						//Diagnosis main
							
						//if($type != 'hisynet') { // ISPC-1606 can you please remove ALL diagnosis export in the HEIMNETZ- ISYNET export
						if($diagnosis_main == '1') {
				
							//Diagnosis main
				
							$dg = new DiagnosisType();
							$abb2 = "'HD'";
							$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);
							$comma=",";
							$typeid ="'0'";
							foreach($ddarr2 as $key=>$valdia)
							{
								$typeid .=$comma."'".$valdia['id']."'";
								$comma=",";
							}
				
							$patdia = new PatientDiagnosis();
							$dianoarray = $patdia->getFinalData($patient['ipid'],$typeid);
				
							$patientmeta = new PatientDiagnosisMeta();
							$metaids =$patientmeta->getPatientDiagnosismeta($patient['ipid']);
				
							if(count($metaids)>0)
							{
								$diagnosismeta = new DiagnosisMeta();
								$comma=",";
								$metadiagnosis ="";
								foreach($metaids as $keymeta=>$valmeta)
								{
									$metaarray = $diagnosismeta->getDiagnosisMetaDataById($valmeta['metaid']);
				
									foreach($metaarray as $keytit=>$metatitle)
									{
										$metadiagnosis .= $comma.$metatitle['meta_title'];
										$comma = ",";
									}
								}
							}
				
							if(count($dianoarray) > 0)
							{
								foreach($dianoarray as $key=>$valdia)
								{
									if(strlen($valdia['diagnosis'])>0 && strlen($valdia['icdnumber'])>0 )
									{
										$diagnosis .= ' -- '.$valdia['diagnosis'].' ('.$valdia['icdnumber'].')';
				
										$diag_date = strtotime($valdia['create_date']);
				
										if($diag_date >= $quarter_first_day && $diag_date <= $quarter_last_day) {
											$diag_date_exp = $diag_date;
										} else {
											$diag_date_exp = $quarter_first_day;
										}
				
										$export_patients[$patient['ipid']][] = $codes['diagnosis']['date'].date('dmY', $diag_date_exp);
										$export_patients[$patient['ipid']][] = $codes['diagnosis']['name'].trim(html_entity_decode($valdia['diagnosis'], ENT_COMPAT, 'UTF-8'));
										$export_patients[$patient['ipid']][] = $codes['diagnosis']['icd'].$valdia['icdnumber'];
										$export_patients[$patient['ipid']][] = $codes['diagnosis']['end'].'G'; //.'diagno'
				
				
									}
								}
				
							}
								
						}
							
						//if($type == 'long2' || $type == 'isynetlong') {
						if($diagnosis_side == '1') {
				
							//Diagnosis side
				
							$export_patients_diagnosis_side = null;
							$export_patients_diagnosis_side_start = null;
								
							$dg = new DiagnosisType();
							$abb2 = "'ND'";
							$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);
							$comma=",";
							$typeid ="'0'";
							foreach($ddarr2 as $key=>$valdia)
							{
								$typeid .=$comma."'".$valdia['id']."'";
								$comma=",";
							}
								
							$patdia = new PatientDiagnosis();
							$dianoarray = $patdia->getFinalData($patient['ipid'],$typeid);
								
							$patientmeta = new PatientDiagnosisMeta();
							$metaids =$patientmeta->getPatientDiagnosismeta($patient['ipid']);
								
							if(count($metaids)>0)
							{
								$diagnosismeta = new DiagnosisMeta();
								$comma=",";
								$metadiagnosis ="";
								foreach($metaids as $keymeta=>$valmeta)
								{
									$metaarray = $diagnosismeta->getDiagnosisMetaDataById($valmeta['metaid']);
										
									foreach($metaarray as $keytit=>$metatitle)
									{
										$metadiagnosis .= $comma.$metatitle['meta_title'];
										$comma = ",";
									}
								}
							}
				
							if(count($dianoarray) > 0)
							{
								foreach($dianoarray as $key=>$valdia)
								{
									if(strlen($valdia['diagnosis'])>0 && strlen($valdia['icdnumber'])>0 )
									{
										$diagnosis .= ' -- '.$valdia['diagnosis'].' ('.$valdia['icdnumber'].')';
				
										$diag_date = strtotime($valdia['create_date']);
				
										if($diag_date >= $quarter_first_day && $diag_date <= $quarter_last_day) {
											$diag_date_exp = $diag_date;
										} else {
											$diag_date_exp = $quarter_first_day;
										}
				
										$export_patients_diagnosis_side[$patient['ipid']][] = $codes['diagnosis_side']['date'].date('dmY', $diag_date_exp);
										$export_patients_diagnosis_side[$patient['ipid']][] = $codes['diagnosis_side']['name'].trim(html_entity_decode($valdia['diagnosis'], ENT_COMPAT, 'UTF-8'));
									}
								}
				
							}
				
							$export_patients_diagnosis_side_str = null;
							$digit_fix_sd = null;
							$side_diag_length = null;
				
							if(sizeof($export_patients_diagnosis_side[$patient['ipid']]) > 0) {
								$export_patients_diagnosis_side_start[$patient['ipid']][] = $codes['diagnosis_side']['identification'].'6200';
								$export_patients_diagnosis_side_start[$patient['ipid']][] = $codes['diagnosis_side']['length'].'{SDL}';
								$export_patients_diagnosis_side_start[$patient['ipid']][] = $codes['diagnosis_side']['patient'].preg_replace('/[^0-9]/','',$patient['details']['epid']); //only numbers
									
								foreach ($export_patients_diagnosis_side_start[$patient['ipid']] as $line) {
									$export_patients_diagnosis_side_str .= str_pad((mb_strlen($line, 'utf8') + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
								}
									
								foreach ($export_patients_diagnosis_side[$patient['ipid']] as $line) {
									$export_patients_diagnosis_side_str .= str_pad((mb_strlen($line, 'utf8') + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
								}
									
									
								$side_diag_length = mb_strlen($export_patients_diagnosis_side_str, 'utf8');
									
								/*if($patient['ipid'] == 'c4f5ecf973fe161bf17493111da7168d096ba61f') {
								 var_dump($export_patients_diagnosis_side_str);exit;
								}*/
									
								$digit_fix_sd = (mb_strlen($side_diag_length) - 5); //placeholder has 5
									
								$side_diag_length_real = $side_diag_length + $digit_fix_sd;
									
									
								//add to export_patients
									
								$export_patients[$patient['ipid']][] = $codes['diagnosis_side']['identification'].'6200';
								$export_patients[$patient['ipid']][] = $codes['diagnosis_side']['length'].$side_diag_length_real;
								$export_patients[$patient['ipid']][] = $codes['diagnosis_side']['patient'].preg_replace('/[^0-9]/','',$patient['details']['epid']); //only numbers
									
								foreach ($export_patients_diagnosis_side[$patient['ipid']] as $line) {
									$export_patients[$patient['ipid']][] = $line;
								}
									
							}
						}
							
				
						$export_patients_stamm[$patient['ipid']][] = $codes['package']['start'].'6100'; //patient stammdaten
						$export_patients_stamm[$patient['ipid']][] = $codes['package']['length'].'{SPL}'; //length
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['id'].preg_replace('/[^0-9]/','',$patient['details']['epid']); //only numbers
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['lastname'].$patient['details']['last_name'];
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['firstname'].$patient['details']['first_name'];
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['dob'].date('dmY',strtotime($patient['details']['birthd']));
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['zipcity'].$patient['details']['zip'].' '.$patient['details']['city'];
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['street'].$patient['details']['street1'];
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type_3108;
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['gender'].$patient['details']['sex'];
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['admissiondate'].date('dmY',strtotime($patient['details']['admission_date']));
							
							
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['phone'].$patient['details']['phone'];
				
						$fdoc = new FamilyDoctor();
						$famdoc = $fdoc->getFamilyDoc($patient['details']['familydoc_id']);
				
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['familydoctor'].$famdoc[0]['last_name'].' '.$famdoc[0]['first_name'];
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['familydoctoraddress'].$famdoc[0]['street1'].'#'.$famdoc[0]['zip'].'#'.$famdoc[0]['city'];
				
						//$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili1'].'8377503';
						//$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili2'].'M';
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type_3108;
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili3'].'1';
				
				
						if((
						    ($type == 'hisynet' || $type == 'heimnetz') && ($patient_no_lkoor === false || $patient_no_quartal === false || $patient_no_evas === false || $patient_no_le === false)) 
						    || $type == 'ado' 
						    || ($patient_no_asses === false || $patient_no_vv === false || $patient_no_nurse === false || $patient_no_doctor === false || $patient_no_contactforms === false || $patient_no_tel === false || $patient_no_le === false )){
						    //if patient has activities add to export
				
							$debug_epids[] = $patient['epid'];
							$debug_ipids[] = $patient['ipid'];
							$exported_patietns_array[] = $patient['ipid'];
 
							//Begin patient data export
							$patient_export_string = '';
							$patient_stamm_export_string = '';
				
				
							foreach($export_patients[$patient['ipid']] as $line) {
								if(stripos($line,'{PPL}') === false) {
									/*if(stripos($line, 'Anus')) {
									 var_dump($line);
									 echo strlen($line). '<br />';
									 echo mb_strlen($line,'latin1'). '<br />';
									 exit;
									 }*/
									$patient_export_string .= str_pad((mb_strlen($line, 'utf8') + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
								} else {
									$patient_export_string .= '#P#'.$line."\r\n";
								}
							}
				
				
							//var_dump($patient_export_string);
				
							$pack_length = (mb_strlen($patient_export_string, 'utf8') - 5); //substract tokens from package length
				
							if(strlen($pack_length) < strlen($pack_length + strlen($pack_length))) {
								$digit_fix = 1;
							} else {
								$digit_fix = 0;
							}
							$pack_length = $pack_length + strlen($pack_length) + $digit_fix; //itself to the length and the 2 to 3 digits fix
				
							$pack_line_length = str_pad((strlen($pack_length) + 3 + 4 + 2),3,'0',STR_PAD_LEFT); //header + code + line break + length calculated above
				
							$patient_export_string = str_replace(array('{PPL}','#P#'), array($pack_length, $pack_line_length), $patient_export_string);
				
							//var_dump($patient_export_string);
							//exit;
				
				
							foreach($export_patients_stamm[$patient['ipid']] as $line) {
								if(stripos($line,'{SPL}') === false) {
									$patient_stamm_export_string .= str_pad((mb_strlen($line, 'utf8') + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
								} else {
									$patient_stamm_export_string .= '#S#'.$line."\r\n";
								}
							}
				
							//var_dump($patient_stamm_export_string);
				
				
							$stamm_length = (mb_strlen($patient_stamm_export_string, 'utf8') - 5); //substract tokens from package length
							if(strlen($stamm_length) < strlen($stamm_length) + strlen($stamm_length)) {
								$digit_fix = 1;
							} else {
								$digit_fix = 0;
							}
							$stamm_length = $stamm_length + strlen($stamm_length) + $digit_fix; //itself to the length and the 2 to 3 digits fix
				
							$stamm_line_length = str_pad((strlen($stamm_length) + 3 + 4 + 2),3,'0',STR_PAD_LEFT); //header + code + line break + length calculated above
				
							$patient_stamm_export_string = str_replace(array('{SPL}','#S#'), array($stamm_length, $stamm_line_length), $patient_stamm_export_string);
				
				
							if($_REQUEST['dbg'] == 1 && $patient['ipid'] == '11d4231b8f561187e32edad92408dc8f253d6e67') {
								var_dump($first_exported_id[$patient['ipid']]);
							}
				
							//4102 "FIXDEP" is different for long/admission only
				
							if($type == 'long') {
								$fixdep = min($first_exported_id[$patient['ipid']]);
							} else if($type == 'short') {
								$fixdep = $quarter_first_day;
							} elseif($type == 'heimnetz') {
								$fixdep = $admission_date_4102;
							} elseif($type == 'isynet' || $type == 'isynetlong') {
								$fixdep = $admission_date_4102;
							} else {
								$fixdep = $ado_admission_dates[$patient['ipid']];
							}
				
							$patient_export_string = str_replace('{FIDEXP}', date('dmY',$fixdep), $patient_export_string);
				
							$all_patient_data_str .=  $patient_export_string.$patient_stamm_export_string;
						} elseif($_REQUEST['dbg'] == '5') {
							echo  '<br />+++++++++++++++++++<br />'.$patient['epid'].'<br />++++++++++++++++++++<br />';
						}
					}
				}
				
				
				
				$export['header'][] = $codes['package']['start'].'0020'; //media start header
				$export['header'][] = $codes['package']['length'].'{HPL}'; //length
				//$export['header'][] = $codes['file']['doctor_number'].'999999900'; //LANR of user grabenhorst
				//$export['header'][] = $codes['file']['doctor_number'].$user_details['LANR'];
				//$export['header'][] = $codes['file']['doctor_number'].'999999900';  //LANR of user grabenhorst
				$export['header'][] = $codes['file']['doctor_number'].$lanr;
				$export['header'][] = $codes['file']['creation_date'].date('dmY');
				$export['header'][] = $codes['kili']['1'].'001';
				$export['header'][] = $codes['file']['encoding'].'1';
				
				foreach($export['header'] as $line) {
					//if(stripos($line,'{HPL}') === false) {
					$export_header .= str_pad((mb_strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
					//} else {
					//					$export_header .= '#H#'.$line."\r\n";
					//			}
				}
					
				$head_length = (mb_strlen($export_header, 'utf8') - 5); //substract tokens from package length
				
				if(strlen($head_length) < strlen($head_length + strlen($head_length))) {
					$digit_fix = 1;
				} else {
					$digit_fix = 0;
				}
				$head_length = $head_length + strlen($head_length) + $digit_fix; //itself to the length and the 2 to 3 digits fix
				
				$head_line_length = str_pad((strlen($head_length) + 3 + 4 + 2),3,'0',STR_PAD_LEFT); //header + code + line break + length calculated above
					
				//$export_header = str_replace(array('{HPL}','#H#'), array($head_length, $head_line_length), $export_header);
				
				$export_header = str_replace('{HPL}', str_pad((mb_strlen($export_header)),5,'0',STR_PAD_LEFT), $export_header);
				
				$export['file'][] = $codes['package']['start'].'0022'; //package start header
				$export['file'][] = $codes['package']['length'].'{FPL}'; //length
				$export['file'][] = $codes['kili']['2'].'10/93';
				$export['file'][] = $codes['kili']['3'].'02/94';
				$export['file'][] = $codes['kili']['4'].'2';
				$export['file'][] = $codes['file']['exportperiod'].'01092010'.date('dmY');
				$export['file'][] = $codes['file']['begintransfer'].date('His');
				
				foreach($export['file'] as $line) {
					$export_file .= str_pad((mb_strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
				}
				
				$export_file = str_replace('{FPL}', str_pad((mb_strlen($export_file)),5,'0',STR_PAD_LEFT), $export_file);
				
				
				//     	$export['doctor'][] = $codes['package']['start'].'0101'; //doctor start header
				$export['doctor'][] = $codes['package']['start'].'0010'; //doctor start header
				$export['doctor'][] = $codes['package']['length'].'{DPL}'; //length
				$export['doctor'][] = $codes['kili']['5'].'A0008083';
				$export['doctor'][] = $codes['software']['company'].'smart-Q Softwaresysteme GmbH';
				$export['doctor'][] = $codes['software']['name'].'ISPC';
				$export['doctor'][] = $codes['software']['compatible'].'AT-Kompatible';
				$export['doctor'][] = $codes['doctor']['number'].'999999900';
				$export['doctor'][] = $codes['doctor']['practice'].'4';
				$export['doctor'][] = $codes['doctor']['name'].$client_details['0']['client_name'];
				$export['doctor'][] = $codes['doctor']['group'].'Palliativteam';
				$export['doctor'][] = $codes['doctor']['street'].$client_details['0']['street1'].' '.$client_details['0']['street2'];
				$export['doctor'][] = $codes['doctor']['zipcity'].$client_details['0']['postcode'].' '.$client_details['0']['city'];
				$export['doctor'][] = $codes['doctor']['phone'].$client_details['0']['phone'];
				$export['doctor'][] = $codes['doctor']['fax'].$client_details['0']['fax'];
				
				foreach($export['doctor'] as $line) {
					$export_doctor .= str_pad((mb_strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
				}
				
				$export_doctor = str_replace('{DPL}',  str_pad((mb_strlen($export_doctor)),5,'0',STR_PAD_LEFT), $export_doctor);
				
				
				$export['footer'][] = $codes['package']['start'].'0023'; //package end
				$export['footer'][] = $codes['package']['length'].'{FOL}'; //length
				$export['footer'][] = $codes['file']['alllength'].'{000AOL}';
				$export['footer'][] = $codes['file']['noofpackages'].'1';
				
				foreach($export['footer'] as $line) {
					$export_footer .= str_pad((mb_strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
				}
				
				$export_footer = str_replace('{FOL}',  str_pad((mb_strlen($export_footer)),5,'0',STR_PAD_LEFT), $export_footer);
				
				$export['mediaend'][] = $codes['package']['start'].'0021'; //media end
				$export['mediaend'][] = $codes['package']['length'].'00027'; //length
				
				
				foreach($export['mediaend'] as $line) {
					$export_mediaend .= str_pad((mb_strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
				}
				
				
				
				
				$final_export_string = $export_header.$export_file.$export_doctor.$all_patient_data_str.$export_footer.$export_mediaend;
				
				$final_export_string = str_replace('{000AOL}',  str_pad((mb_strlen($final_export_string)),8,'0',STR_PAD_LEFT), $final_export_string);
				
				if($_REQUEST['dbg'] == "5"){
					echo nl2br($final_export_string);
				
					echo '<br /><br />Debug start';
					//echo implode('","',array_unique(array_keys($export_patients)));
					sort($debug_epids);
					echo implode('",'."\n".'"', $debug_epids);
					echo '<br /><br />'.sizeof($debug_ipids);
					echo '<br /><br />Patients export: '.sizeof($export_patients);
					echo '<br /><br />Patients all:'.sizeof($patients_arr);
					echo '<br /><br />Periods :'.serialize($periods);
					exit();
				}
				
				// #################################################################
				// #################### SAVE FILE ##################################
				// #################################################################
				// insert in new db
				if(!empty($actions_le_billed)){
				    foreach($actions_le_billed as $ipid=>$actions_details){
				        foreach($actions_details as $k=>$db_pat_action){
    				        $billed_actions[] = $db_pat_action;
				        }
				    }
				}
				$save_file = new  XbdtFiles();
				$save_file->clientid = $clientid;
				$save_file->file_content = base64_encode($final_export_string);
				if(!empty($actions_le_billed)){
       				$save_file->file_actions = serialize($actions_le_billed);
				}
				$_REQUEST['exported_patients'] = $exported_patietns_array;
   				$save_file->export_request = serialize($_REQUEST);
				$save_file->save();
				
				$file_id = $save_file->id;
				
                if($file_id){
                    foreach($billed_actions as $k=>$db_id){
                        $edit_pat_actions = Doctrine::getTable('PatientXbdtActions')->find($db_id);
                        $edit_pat_actions->file_id = $file_id;
                        $edit_pat_actions->save();
                    }   
                }
                // #################################################################				
				
				header("Pragma: public"); // required
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false); // required for certain browsers
				header("Content-Type: plain/text; charset=ISO-8859-1");
				header("Content-Disposition: attachment; filename=\"export.BDT\";" );
				header("Content-Transfer-Encoding: binary");
				//header("Content-Length: ".filesize($final_export_string));
				echo iconv("UTF-8", "ISO-8859-1//TRANSLIT",($final_export_string));
				//echo $final_export_string;
				exit();

			}
		}
	}
	
	
	public function getpatientsAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		
		$quarter = explode ( '-', $_REQUEST ['quarter'] );
		$q_month = trim ( $quarter [1] );
		$q_year = trim ( $quarter [0] );
		// $quarter_dates = Pms_CommonData::get_dates_of_quarter($q_month,$q_year,"Y-m-d");
		
		// $period_start = $quarter_dates['start'];
		// $period_end = $quarter_dates['end'];
		
		$period_start = date ( "Y-m-d", strtotime ( $_REQUEST ['period_start'] ) ); // ?????? if not empty
		$period_end = date ( "Y-m-d", strtotime ( $_REQUEST ['period_end'] ) ); // ?????? if not empty
		
		$type = $_REQUEST ['type'];
		
		$select = "AES_DECRYPT(p.last_name,'" . Zend_Registry::get ( 'salt' ) . "') as last_name,AES_DECRYPT(p.first_name,'" . Zend_Registry::get ( 'salt' ) . "') as first_name,convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as zip,convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as street1,convert(AES_DECRYPT(p.city,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as city,convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as phone, convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as sex,";
		
		$periods = array (
				'0' => array (
						'start' => $period_start,
						'end' => $period_end 
				) 
		);
		if (strlen ( $_REQUEST ['length'] )) {
			$limit = $_REQUEST ['length'];
		} else {
			$limit = "100";
		}
		
		$offset = $_REQUEST ['start'];
		
		$limit_options = array (
				"limit" => $limit,
				'offset' => $offset 
		);
		
		$ipids = array();
		
		if ($_REQUEST ['tp'] == 'ipids') {
			
		} elseif ($type == 'ado') {
			// get only newly admitted patients
			$ipids = $this->admitted_in_period ($period_start, $period_end);
			if(empty($ipids)) {
				$ipids = array('999999');
			}
			$periods = array ();
		} 
		if ($type == 'only_with_le' || ! empty($_REQUEST ['user']) || ! empty($_REQUEST ['group_type'])) {
			// show only patients which have at least one NOT ALREADY BILLED LE entry in the period
			$pa = new PatientXbdtActions();
			$cond =  array();
			$cond['client'] = $clientid;
			
			if(!empty($_REQUEST ['period_start']) && !empty($_REQUEST ['period_end']) ){
				$cond['start'] = date('Y-m-d H:i:s', strtotime($_REQUEST ['period_start']));
				$cond['end'] = date('Y-m-d 23:59:59', strtotime($_REQUEST ['period_end']));
			}
			
			if ( ! empty($_REQUEST ['user'])){
			    $cond['user'] = (int)$_REQUEST ['user'];
			}
			
			if ( ! empty($_REQUEST ['group_type'])){
				$cond['group'] = $_REQUEST ['group_type'];
			}
				
			
			$actions_le_array = $pa->get_actions_filtered($cond);

			$action_ipids = array();
			if ( ! empty($actions_le_array)) {
				foreach ($actions_le_array as $kact => $vact) {
					if ( ! in_array($vact['ipid'], $action_ipids)) {
						$action_ipids[] = $vact['ipid'];
					}
				}
			}
			
			if ( $type == 'ado' ) {
			    //intersetc $ipids with $action_ipids
			   $ipids = array_intersect($ipids, $action_ipids);
			} elseif ( ! empty($action_ipids)) { 
			    $ipids = array_unique($action_ipids);
			}
			
			if(empty($ipids)) {
				$ipids = array('999999');
			}

		} 
		
		if(empty($ipids)) {
		    $ipids = null;
		}

		//$patients_arr = Pms_CommonData::patients_days ( array ('ipids' => $ipids,'include_standby' => '1',	'periods' => $periods,'client' => $clientid), $select );
		$patients_arr = Pms_CommonData::patients_days ( array (
					'ipids' => $ipids,
					'periods' => $periods,
					'client' => $clientid 
			), $select );// TODO -498 - remove standby 02.09.2016

		if ($type == 'hisynet' || $type == 'heimnetz' || $type == 'isynet' || $type == 'isynetlong') {
		    
			//$patients_full_periods = Pms_CommonData::patients_days ( array ('ipids' => array_keys ( $patients_arr ),'include_standby' => '1','client' => $clientid), $select );
			$patients_full_periods = Pms_CommonData::patients_days ( array (
					'ipids' => array_keys ( $patients_arr ),
					'client' => $clientid 
			), $select );// TODO -498 - remove standby 02.09.2016
		}

		$resulted_ipids = array_keys ( $patients_arr );
		
		$full_count = count ( $resulted_ipids );
		
		// get health insurance data for all ipids
		$healthins = new PatientHealthInsurance ();
		$pathealthins = $healthins->get_multiple_patient_healthinsurance ( $resulted_ipids, true );
		foreach($pathealthins as $ph_ipid=>$phdata){
			$insurance_option[$ph_ipid] = "";
			if($phdata ['privatepatient'] == "1" )
			{
				$insurance_option[$ph_ipid] = "Privatpatient";
			}
			elseif($phdata ['direct_billing'] == "1" )
			{
				$insurance_option[$ph_ipid]  = "Direktabrechnung";
			}
			elseif($phdata ['bg_patient'] == "1" )
			{
				$insurance_option[$ph_ipid]  = "BG Patient";
			} else {
				$insurance_option[$ph_ipid]  = "Keiner";
			}
		}
		
		// print_R($patients_arr); exit;
		
		$row_id = 0;
		$link = "";
		$resulted_data = array ();
		$discharge_date = "";
		$admission_date = "";
		
		if (! empty ( $patients_arr )) {
			foreach ( $patients_arr as $ipid => $mdata ) {
				$link = '%s ';
				
				$admission_date = end ( $mdata ['admission_days'] );
				if (! empty ( $mdata ['discharge'] )) {
					$discharge_date = end ( $mdata ['discharge'] );
				} else {
					$discharge_date = "";
				}
				
				$resulted_data [$row_id] ['epid_number'] = sprintf ( $link, $mdata ['details'] ['epid'] );
				$resulted_data [$row_id] ['admission_date_full'] = sprintf ( $link, date ( 'Y-m-d', strtotime ( $admission_date ) ) );
				if (strlen ( $discharge_date ) > 0) {
					$resulted_data [$row_id] ['discharge_date_full'] = sprintf ( $link, date ( 'Y-m-d', strtotime ( $discharge_date ) ) );
				} else {
					$resulted_data [$row_id] ['discharge_date_full'] = sprintf ( $link, "" );
				}
				$resulted_data [$row_id] ['dob_full'] = sprintf ( $link, $mdata ['details'] ['birthd'] );
				$resulted_data [$row_id] ['select_patient'] = '<input type="checkbox" name="patients[]" class="patients_select" value="' . $mdata ['details'] ['ipid'] . '"/>'; // CHANGE !!!!!!!!!!!!! add what is needded
				$resulted_data [$row_id] ['epid'] = sprintf ( $link, $mdata ['details'] ['epid'] );
				$resulted_data [$row_id] ['first_name'] = sprintf ( $link, $mdata ['details'] ['first_name'] );
				$resulted_data [$row_id] ['last_name'] = sprintf ( $link, $mdata ['details'] ['last_name'] );
				if ($mdata ['details'] ['birthd'] != "0000-00-00") {
					$resulted_data [$row_id] ['birthd'] = sprintf ( $link, date ( "d.m.Y", strtotime ( $mdata ['details'] ['birthd'] ) ) );
				} else {
					$resulted_data [$row_id] ['birthd'] = sprintf ( $link, "-" );
				}
				
				$resulted_data [$row_id] ['admission_date'] = sprintf ( $link, $admission_date );
				$resulted_data [$row_id] ['discharge_date'] = sprintf ( $link, $discharge_date );
				if (! empty ( $pathealthins [$ipid] )) {
					
					$resulted_data [$row_id] ['health_insurance_company'] = sprintf ( $link, $pathealthins [$ipid] ['company_name'] );
					$resulted_data [$row_id] ['health_insurance_number'] = sprintf ( $link, $pathealthins [$ipid] ['insurance_no'] );
					$resulted_data [$row_id] ['health_insurance_kassennummer'] = sprintf ( $link, $pathealthins [$ipid] ['kvk_no'] );
					$resulted_data [$row_id] ['health_insurance_status'] = sprintf ( $link, $pathealthins [$ipid] ['insurance_status'] );
					$resulted_data [$row_id] ['health_insurance_ik'] = sprintf ( $link, $pathealthins [$ipid] ['institutskennzeichen'] );
					$resulted_data [$row_id] ['insurance_options'] = sprintf ( $link, $insurance_option[$ipid] );
					
				} else {
					
					$resulted_data [$row_id] ['health_insurance_company'] = "";
					$resulted_data [$row_id] ['health_insurance_number'] = "";
					$resulted_data [$row_id] ['health_insurance_kassennummer'] = "";
					$resulted_data [$row_id] ['health_insurance_status'] = "";
					$resulted_data [$row_id] ['health_insurance_ik'] = "";
					$resulted_data [$row_id] ['insurance_options'] = "";
				}
				
				$row_id ++;
			}
		}
		
		$columns_array = $_REQUEST ['columns'];
		$sort_by = $columns_array[$_REQUEST ['order']['column']];
		$sort_dir = $_REQUEST ['order']['dir'];
		
	
// 		print_r($sort_by ."    ");
// 		print_r($sort_dir ."    ");
// 		print_r($resulted_data);
		if($sort_dir == "asc"){
			$resulted_data = Pms_CommonData::array_sort($resulted_data,$sort_by,SORT_ASC);
		}else{
			$resulted_data = Pms_CommonData::array_sort($resulted_data,$sort_by,SORT_DESC);
		}
		$resulted_data = array_values($resulted_data);
// 		print_r($resulted_data);
// 		exit;
		$response ['draw'] = $_REQUEST ['draw']; // ? get the sent draw from data table
		$response ['recordsTotal'] = $full_count;
		$response ['recordsFiltered'] = $full_count; // ??
		$response ['data'] = $resulted_data;
		$response ['order'] = $_REQUEST ['order'];
		$response ['columns'] = $_REQUEST ['columns'];
		
		header ( "Content-type: application/json; charset=UTF-8" );
		
		echo json_encode ( $response );
		exit ();
	}
	
	
	
	
	
	
	public function getxbdtfilesAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		
		$query = Doctrine_Query::create()
		->select('*')
		->from('XbdtFiles')
		->where('clientid =  '.$clientid)
		->andWhere('isdelete = 0');
		$query->orderBy("create_date DESC");
		$q_res = $query->fetchArray();

		$full_count = count ($q_res);
		
		$row_id = 0;
		$link = "";
		$resulted_data = array ();
		foreach ( $q_res as $r => $mdata ) {
			$link = '%s ';
			$resulted_data [$row_id] ['export_date'] = sprintf ( $link, date ( "d.m.Y H:i", strtotime ( $mdata ['create_date']  ) ) );
// 			$resulted_data [$row_id] ['download_link'] = '<a href="'.APP_BASE .'xbdtnew/downloadfile?id='.$mdata['id'].'"> Speichern </a>';
// 			$resulted_data [$row_id] ['download_link'] = '';
			$resulted_data [$row_id] ['delete'] = '';
			$resulted_data [$row_id] ['delete'] .= '<a href="'.APP_BASE .'xbdtnew/downloadfile?id='.$mdata['id'].'" title="export.bdt" ><img border="0" src="'.RES_FILE_PATH.'/images/file_download.png"></a>';
			$resulted_data [$row_id] ['delete'] .= '<a href="'.APP_BASE .'xbdtnew/downloadfile?type=pdf&id='.$mdata['id'].'" title="export.pdf"><img border="0" src="'.RES_FILE_PATH.'/images/doc_pdf.png"></a>';
			$resulted_data [$row_id] ['delete'] .= '<a href="javascript:void(0);"  class="delete_file" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
			$row_id ++;
		}
		
		$response ['draw'] = $_REQUEST ['draw']; // ? get the sent draw from data table
		$response ['recordsTotal'] = $full_count;
		$response ['recordsFiltered'] = $full_count; // ??
		$response ['data'] = $resulted_data;
		
		header ( "Content-type: application/json; charset=UTF-8" );
		
		echo json_encode ( $response );
		exit ();
	}
	

	public function deletefileAction ()
	{
	    $logininfo = new Zend_Session_Namespace ( 'Login_Info' );
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;

	    $this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->setLayout('layout_ajax');
	    
	            	
         if(!empty($_GET['id']) && strlen($_GET['id']) > 0 ){
             // get file details
             $db_id = $_GET['id'];
             
             $file_details = XbdtFiles::client_xbdt_files($clientid,$db_id);
             if(!empty($file_details[$db_id])){
                 // Mark all billed actions from  patient as unbilled, so they can be billed again - where file id is the deleted id
                 $q = Doctrine_Query::create()
                 ->update('PatientXbdtActions')
                 ->set('file_id', '0')
                 ->set('change_date', '"'.date("Y-m-d H:i:s").'"')
                 ->set('change_user', '"'.$userid.'"')
                 ->where('clientid = ?',  $clientid)
                 ->andWhere('file_id = ?',  $db_id);
                 $q->execute();
                 
                // mark as delete from xbdtfiles
                 $edit_pat_actions = Doctrine::getTable('XbdtFiles')->find($db_id);
                 $edit_pat_actions->isdelete = 1;
                 $edit_pat_actions->save();
             }
         }

         $this->_redirect(APP_BASE . "xbdtnew/export");
         exit;
	}
	
	public function downloadfileAction ()
	{
	    $logininfo = new Zend_Session_Namespace ( 'Login_Info' );
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->setLayout('layout_ajax');
		
		if(!empty($_GET['id'])){
			
		    // get file details
		     $db_id = $_GET['id'];
             $file_details = XbdtFiles::client_xbdt_files($clientid,$db_id);
             if(!empty($file_details)) {
             	
             	
				if($_GET['type'] == "pdf") { // PRINT PDF 
					$file_data = array();
					$pdf_data = array();
					
					$file_details[$db_id]['export_request_details'] = unserialize($file_details[$db_id]['export_request']);
					$file_details[$db_id]['file_actions_details'] = unserialize($file_details[$db_id]['file_actions']);
					$pdf_data['file']['create_date'] = date("d.m.Y H:i",strtotime($file_details[$db_id]['create_date']));
					$pdf_data['file']['period'] = $file_details[$db_id]['export_request_details']['period'];
// 					print_r($file_details); exit;
					
					if(!empty($file_details[$db_id]['export_request_details'])){
// 						$file_data['patients'] = $file_details[$db_id]['export_request_details']['patients'];
						$file_data['patients'] = $file_details[$db_id]['export_request_details']['exported_patients'];
					}
					
					
					if(!empty($file_data['patients'])){
						
						if(!empty($file_details[$db_id]['file_actions_details']))
						{
							$file_data['actions'] = $file_details[$db_id]['file_actions_details'];
						}
						
						// get users
						$client_users = User::get_client_users ( $clientid, '1' );
						
						// get patient actions 
						$act_cond['ipids'] = $file_data['patients'];
						$act_cond['client'] = $clientid;
						// $act_cond['file_id'] = $db_id;
						$act_cond['all'] = true;
						$patient_actions = PatientXbdtActions::get_actions_filtered($act_cond);
						
						
						foreach($patient_actions as $k=>$pact){
							$patient_detail_full[$pact['ipid']][$pact['id']] = $pact;
						}
						
						
						foreach($file_data['actions']  as $ipid=>$actions){
							$row_act = 0;
							foreach($actions as $k=>$act_id){
// 								$patient_details[$ipid]['actions'][$act_id] = $patient_detail_full[$ipid][$act_id];
								$patient_details[$ipid]['actions'][$row_act] = $patient_detail_full[$ipid][$act_id];
								
								if($patient_detail_full[$ipid][$act_id]['team'] == "1")
								{
// 									$patient_details[$ipid]['actions'][$act_id]['user_name'] = "Team";
									$patient_details[$ipid]['actions'][$row_act]['user_name'] = "Team";
								}
								else
								{
// 									$patient_details[$ipid]['actions'][$act_id]['user_name'] = $client_users[$patient_detail_full[$ipid][$act_id]['userid']];
									$patient_details[$ipid]['actions'][$row_act]['user_name'] = $client_users[$patient_detail_full[$ipid][$act_id]['userid']];
								}
								$row_act++;
							}
						}

						
						//get patients details
						$actpatient = Doctrine_Query::create ()
						->select ( "ipid,AES_DECRYPT(last_name,'" . Zend_Registry::get ( 'salt' ) . "') as last_name,AES_DECRYPT(first_name,'" . Zend_Registry::get ( 'salt' ) . "') as first_name" )
								->from ( 'PatientMaster p' )
								->WhereIn( 'ipid',$file_data['patients'])
								->andWhereIn ( 'isdelete = 0' );
						$actipidarray = $actpatient->fetchArray ();
						foreach($actipidarray as $k=>$pdataa){
							$patient_details[$pdataa['ipid']]['last_name'] = $pdataa['last_name']; 
							$patient_details[$pdataa['ipid']]['first_name'] = $pdataa['first_name']; 
						}
						
						$i = 0;						
						foreach($file_data['patients'] as $k=>$pipid){
							$i++; 
							
// 							if($i < 2){
								$pdf_data['patients'][$pipid] = $patient_details[$pipid]; 
		
								if(!isset($patient_details[$pipid]['actions'])){
									$pdf_data['patients'][$pipid]['actions'] = array();
								}
// 							}
						}
						
					} 

					$pdf_data['patients'] = Pms_CommonData::array_sort($pdf_data['patients'],"last_name",SORT_ASC);
					
					$post['pdf_data'] = $pdf_data;
					
					
					if($_REQUEST['show_data'] == "1"){
						print_r($file_data['patients'] );
						print_r($post);
						 exit;
					}
					if($_REQUEST['old_version'] == "1"){
						$this->generate_pdf($post, 'xbdt_export_pdf', "xbdt_export_pdf.html");
					} else{
						$this->generate_pdf_export($post, 'xbdt_export_pdf', "xbdt_export_pdf.html");
					}
					
				} else {  // PRINT File
					
		             $final_export_string = base64_decode($file_details[$db_id]['file_content']);
		             header("Pragma: public"); // required
		             header("Expires: 0");
		             header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		             header("Cache-Control: private",false); // required for certain browsers
		             header("Content-Type: plain/text; charset=ISO-8859-1");
		             header("Content-Disposition: attachment; filename=\"export.BDT\";" );
		             header("Content-Transfer-Encoding: binary");
		             echo iconv("UTF-8", "ISO-8859-1//TRANSLIT",($final_export_string));
		             exit();
				}
             }
		}
 
		exit;
		
	}
	
	
	private function CurrentQuarter($date = "today"){
		$n = date('n', strtotime($date));
		if($n < 4){
			return "1";
		} elseif($n > 3 && $n <7){
			return "2";
		} elseif($n >6 && $n < 10){
			return "3";
		} elseif($n >9){
			return "4";
		}
	}
	
	
	
	
	private function generate_pdf($post, $pdfname, $filename)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$clientinfo = Pms_CommonData::getClientData($clientid);
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
		$excluded_keys = array(
				'stamp_block'
		);
			
		$post = Pms_CommonData::clear_pdf_data($post, $excluded_keys);
			
		
		$htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
	
	
		$navnames = array(
				"print_export_patients" => 'DienstPlan'
		);

		//$pdf = new Pms_PDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
		if($pdfname == 'print_export_patients')
		{
			$orientation = 'P';
			$bottom_margin = '10';
			$format = "A4";
		}

		$pdf = new Pms_PDF($orientation, 'mm', $format, true, 'UTF-8', false);
		$pdf->SetMargins(10, 5, 10); //reset margins
		$pdf->setDefaults(true, $orientation, $bottom_margin); //defaults with header
		$pdf->setImageScale(1.6);
		$pdf->format = $format;
		$pdf->setPrintFooter(false); // remove black line at bottom

		switch($pdfname)
		{
			default:
				$background_type = false;
				$pdf->SetMargins(10, 5, 10); //reset margins
				break;
		}

		$pdf->HeaderText = false;
		
		
		$pdf->setPrintFooter(true);
		$pdf->footer_text = $this->view->translate("Xbdt pdf export footer");
		$pdf->setFooterType('1 of n');
		
		
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
				'printrosterpdf'
		);

		if(!in_array($pdfname, $excluded_css_cleanup_pdfs))
		{
			$html = preg_replace('/style=\"(.*)\"/i', '', $html);
		}
		
		if($_REQUEST['show_data_html'] == "1"){
			echo $html; exit;
		}
		
		$pdf->setHTML($html);

		//upload pdf to ftp as foster file 
		$pdf->toFTP($pdfname, "uploads", NULL, true);

		if($pdfname != "participationpolicy_save"){
			ob_end_clean();
			ob_start();
			$pdf->toBrowser($pdfname . '.pdf', "d");
			exit;
		}
	
	
	}
	
	private function generate_pdf_export($post, $pdfname, $filename)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$clientinfo = Pms_CommonData::getClientData($clientid);
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
		$excluded_keys = array(
				'stamp_block'
		);
			
		$post = Pms_CommonData::clear_pdf_data($post, $excluded_keys);
			
		
		$htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
	
	
		$navnames = array(
				"print_export_patients" => 'DienstPlan'
		);

		//$pdf = new Pms_PDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
		if($pdfname == 'print_export_patients')
		{
			$orientation = 'P';
			$bottom_margin = '10';
			$format = "A4";
		}

		$pdf = new Pms_PDF($orientation, 'mm', $format, true, 'UTF-8', false);
		$pdf->SetMargins(10, 5, 10); //reset margins
		$pdf->setDefaults(true, $orientation, $bottom_margin); //defaults with header
		$pdf->setImageScale(1.6);
		$pdf->format = $format;
		$pdf->setPrintFooter(false); // remove black line at bottom

		switch($pdfname)
		{
			default:
				$background_type = false;
				$pdf->SetMargins(10, 5, 10); //reset margins
				break;
		}

		$pdf->HeaderText = false;
		
		
		$pdf->setPrintFooter(true);
		$pdf->footer_text = $this->view->translate("Xbdt pdf export footer");
		$pdf->setFooterType('1 of n');
		
		
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

// 		$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);

		$excluded_css_cleanup_pdfs = array(
				'printrosterpdf'
		);

		if(!in_array($pdfname, $excluded_css_cleanup_pdfs))
		{
// 			$html = preg_replace('/style=\"(.*)\"/i', '', $html);
		}
		
		if($_REQUEST['show_data_html'] == "1"){
		}
			
		$cnt = 0 ; 
		
		$rowcount = 1;
		

		$block = 1; 
		foreach($post['pdf_data']['patients'] as $ipid=>$data){
			$row_array[$block][] = $data;
			if(count($row_array[$block]) == 70){
				$block++;
			}
		}
		if(empty($row_array)){
			$html ='<table>';
			$html .='<tr>';
			$html .='<td><font size="8pt"><b>'.$this->view->translate("BDT file from").' '.$post['pdf_data']['file']['create_date'].'</b></font></td>';
			$html .='</tr>';
			$html .='</table>';
			$pdf->setHTML($html);
		}
		
		foreach($row_array as $cnt=>$row_data){
			$rowcount = 0;
			if($cnt == "1"){
				$html ='<table>';
				$html .='<tr>';
				$html .='<td><font size="8pt"><b>'.$this->view->translate("BDT file from").' '.$post['pdf_data']['file']['create_date'].'</b></font></td>';
				$html .='</tr>';
				$html .='</table>';
			} else {
				$html = "";
			}
			
			$html .= '<table border="1">';
			$html .= '<tr class="row">';
			$html .= '<th valign="top" rowspan="2"><b>' . $this->view->translate('last_name') . '</b></th>';
			$html .= '<th valign="top" rowspan="2"><b>' . $this->view->translate('first_name') . '</b></th>';
			$html .= '<th valign="top" colspan="3"><b>' . $this->view->translate('Xbdt actions list') . '&nbsp;</b></th>';
			$html .= '</tr>';
	
			$html .= '<tr>';
			$html .= '<th ><b>' . $this->view->translate('xbdt action date') . '</b></th>';
			$html .= '<th ><b>' . $this->view->translate('xbdt action name') . '</b></th>';
			$html .= '<th ><b>' . $this->view->translate('xbdt action user') . '</b></th>';
			$html .= '</tr>';
			
			foreach($row_data as $key=>$row){
				$rowspan = sizeof($row['actions']);
			
				if(count($row['actions']) != 0)
				{
					foreach($row['actions'] as $sec_key => $sec_row)
					{
						if($sec_key == min(array_keys($row['actions'])))
						{
							$html .= '<tr class="row">';
							$html .= '<td valign="top" rowspan="' . $rowspan . '">' . $row['last_name'] . '</td>';
							$html .= '<td valign="top" rowspan="' . $rowspan . '">' . $row['first_name'] . '</td>';
							$html .= '<td valign="top">' . date("d.m.Y H:i",strtotime($sec_row['action_date'])) . '</td>';
							$html .= '<td valign="top">' . $sec_row['XbdtActions']['name'] . '&nbsp;</td>';
							$html .= '<td valign="top">' . $sec_row['user_name'] . '&nbsp;</td>';
							$html .='</tr>';
						}
						else
						{
							$html .='<tr>';
							$html .= '<td valign="top">' . date("d.m.Y H:i",strtotime($sec_row['action_date'])) . '</td>';
							$html .= '<td valign="top">' . $sec_row['XbdtActions']['name'] . '&nbsp;</td>';
							$html .= '<td valign="top">' . $sec_row['user_name'] . '&nbsp;</td>';
							$html .='</tr>';
						}
					}
					$rowcount++;
				}
				else
				{
					$html .= '<tr class="row">';
					//$html .= '<td valign="top" rowspan="0">' . $rowcount . '</td>';
					$html .= '<td valign="top">' . $row['last_name'] . '</td>';
					$html .= '<td valign="top">' . $row['first_name'] . '</td>';
					$html .= '<td valign="top">&nbsp; - &nbsp;</td>';
					$html .= '<td valign="top">&nbsp; - &nbsp;</td>';
					$html .= '<td valign="top">&nbsp; - &nbsp;</td>';
					$html .='</tr>';
					$rowcount++;
				}
			}
			$html .= '</table>';
			
			
			$pdf->setHTML($html);
		}
 

		//upload pdf to ftp as foster file 
		$pdf->toFTP($pdfname, "uploads", NULL, true);

		if($pdfname != "participationpolicy_save"){
			ob_end_clean();
			ob_start();
			$pdf->toBrowser($pdfname . '.pdf', "d");
			exit;
		}
	
	
	
	
	}
	
	
	
	
}
?>