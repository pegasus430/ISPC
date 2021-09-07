<?php

class XbdtController extends Zend_Controller_Action
{

	public function init()
	{
		$this->codes = array(

								'media'	=> array(
													'start' => '0020',
													'end' => '0021'
													),
								'package' => array(
													'start' => '8000',
													'length' => '8100',
													),
								'file' => array(
													'doctor_number' => '9100', //01691002488531
													'creation_date' => '9103',
													'encoding' => '9106', //2 is 8-bit ASCII
													'exportperiod' => '9601', //ddmmyyyyddmmyyyy 02596011610200717102007
													'begintransfer' => '9602', //hhmmsscc 017960219174500
													'alllength' => '9202', 
													'noofpackages' => '9203', //1 ????
													),

								'software' => array(
													'company' => '0102', //smart-Q Softwaresysteme GmbH
													'name' => '0103', //ISPC
													'compatible' => '0104', //0220104AT-Kompatible
													),
														
								'doctor' => array(
													'number' => '0201', //01602012488531
													'practice' => '0202', //practice type ← we take here "4" 
													'name' => '0203',
													'group' => '0204',
													'street' => '0205',
													'zipcity' => '0206', //023020640000 Hasenhausen 
													'phone' => '0208',
													'fax' => '0209',
													'nofodocs' => '0225',
													),
														
								'patient' => array(
													'id' => '3000',
													'lastname' => '3101',
													'firstname' => '3102',
													'dob' => '3103',
													'healthinsno' => '3105', 
													'healthinsno2' => '3119', //ISPC-1438 IF the Versichertennummer in ISPC has a leading letter (like "A123456789") then please use the field 3119 instead of 3105. if the versichertennummer has no leading letter then dont change anything and continue using field 3105
													'zipcity' => '3106',
													'street' => '3107',
													'street2' => '1270', //ISPC-1438 the heimnetz quartalsexport plz change the STREET id from 3107 to 1270
													'insurance_type' => '3108', //01331085000 <--- insurance type = 1 = Mitglied , 3 = Familienversicherter 5 = Rentner;
													'gender' => '3110', //1 = male, 2 = female
													'admissiondate' => '3610', //017361004082007
													'admissiondate2' => '4102', //017410201092011
													'generationdate' => '4102',
													'generationquarter' => '4101',
													'firstiddate' => '4102',
													'phone' => '3626',
													'familydoctor' => '3702',
													'familydoctoraddress' => '3703', //0713703Dr.Unbekannt#Krefelder Str. 3#40000#Hasenhausen<--- family doctor adress
													//'kili1' => '4111', //41118377503
													//'kili2' => '4112', //4112M
													'kili3' => '4113', //41131
													'kili4' => '4106', //011410600
													'kili5' => '4122', //011412200  
													'kili6' => '4239', //011423900
													'kili7' => '4110', //add 4110 as empty field
													'kili8' => '4121', //add 4121 as empty field
													'bsnr'	=> '5098', //(BSNR of client)
													'lanr'	=> '5099', //(LANR of user )
													),

								'patient_insurance' => array (
																//correct:  4111 - IK (7-digit) 4104 - Kassennummer (5-digit), ISPC-1564
																//possible error
																//Skype 16-01-05, fixed the error
																'ikno' => '4111', //01641114080005<--- IK Number of health insurance 
																'kassenno' => '4104', //014410437601<--- Kassennummer of health insurance
																'type' => '4112', //insurance type = 1 = Mitglied , 3 = Familienversicherter 5 = Rentner;
																'ewstatus' => '4113' //east / west status : means all client except the Dessau one have here a "1"
													),
														
								'action' => array (
													'start' => '5000', // 017500016102007 <---here starts a refundable ACTION<-- code 5000 ← in this case DATE  
													'actioncode' => '5001', //Leistungsziffer of action - action code
													'name' => '5002', 
													'time' => '5006', // hhmm
													),
														
														
								'diagnosis' => array(
													'name' => '6000', //0316000Verdacht auf Gastritis<---main diagnosis
													'icd' => '6001',
													'end' => '6003',
													'date' => '5000', //date
													),
				
								'diagnosis_side' => array(
										'name' => '6205', 
										'date' => '6200', 
										'patient' => '3000',
										'length' => '8100',
										'identification' => '8000', //date
								),
				
				
								'heimnetz' => array( //ISPC-1438
										'admission_first' => '93531', //Ersteinschreibung Heimnetz
										'next_quartal' => '93532', //Folgequartal Heimnetz
										'eva1' => '93555', //EVA Symbolziffer
										'eva2' => '93556',
										'eva3' => '93557',
										'lkoor_va' => '93536',
										'lkoor_kh' => '93537',
										'lkoor_other' => '93535',
								),
														
								'kili' => array(
													'1' => '9105',//keep it like it is 0129105001
													'2' => '9210',//keep it like it is 014921010/93
													'3' => '9213',//keep it like it is 014921302/94
													'4' => '9600',//keep it like it is 01096002
													'5' => '0101',//keep it like it is 0170101A0008083
													'6' => '4106',//keep it like it is 011410600
													'7' => '4110', //add 4110 as empty field
													'8' => '4121', //add 4121 as empty field
													),

													);
	}


	public function generatefilecsAction() {
		set_time_limit(0);
		//error_reporting(E_ALL);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$codes = $this->codes;
		$insurance_statuses = array(
    								'F' => '3000',
    								'M' => '1000',
    								'R' => '5000',
    								'N' => 'XYX',
    								'S' => 'XYX',
		);
		
/*		$insurance_statuses = array(
    								'F' => '3',
    								'M' => '1',
    								'R' => '5',
    								'N' => 'XYX',
    								'S' => 'XYX',
		);*/
			
		$client_details = Client::getClientDataByid($clientid);
			
		//$client_details['betriebsstattennummer']
			
		$user_details =  User::getUserDetails($logininfo->userid);
		
		for($y = date('Y'); $y > 2009; $y--) {
			$quarters[$y.'-4'] = '4/'.$y;
			$quarters[$y.'-3'] = '3/'.$y;
			$quarters[$y.'-2'] = '2/'.$y;
			$quarters[$y.'-1'] = '1/'.$y;
		}
		
		$this->view->quarters = $quarters;
		$this->view->types = array(
									'long' => 'SAPV Version mit Leistungen',
									'long2' => 'SAPV mit Leistungen und Nebendiagnosen',
									'isynet' => 'SAPV mit Leistungen - ISYNET',
									'isynetlong' => 'SAPV - ISYNET mit Leistungen und Nebendiagnosen',
									'short' => 'aktive Patienten ohne Leistungen',
									'ado' => 'WL - Nur Neuaufnahmen des Quartals',
									'heimnetz' => 'Heimnetz - Quartalsexport',
									'hisynet' => 'Heimnetz - ISYNET',
									
									
											);
		
		$module = new Modules();
		if($module->checkModulePrivileges("108", $clientid))
		{
			$vv_only_if_visit_day = "1";
		}
		else
		{
			$vv_only_if_visit_day = "0";
		}
		
			
		if ($this->getRequest()->isPost()) {
			
			$quarter = explode('-', $_POST['quarter']);
			$lanr = $_POST['lanr'];
			$type = $_POST['type'];
			$s = array(" ","	","\n","\r");
			$ado_id = trim(str_replace($s,array(),$_POST['ado_id']));
			$ado_text_id = trim(str_replace($s,array(),$_POST['ado_text_id']));
			$ado_text = $_POST['ado_text'];
			
			
			
			switch($quarter[1]) {
				case 1:
					$period_start = date('Y-m-d', strtotime('first day of january '.$quarter[0]));
					$period_end = date('Y-m-d', strtotime('last day of march '.$quarter[0]));
					break;
					
				case 2:
					$period_start = date('Y-m-d', strtotime('first day of april '.$quarter[0]));
					$period_end = date('Y-m-d', strtotime('last day of june '.$quarter[0]));
					break;
					
				case 3:
					$period_start = date('Y-m-d', strtotime('first day of july '.$quarter[0]));
					$period_end = date('Y-m-d', strtotime('last day of september '.$quarter[0]));
					break;
					
				case 4:
					$period_start = date('Y-m-d', strtotime('first day of october '.$quarter[0]));
					$period_end = date('Y-m-d', strtotime('last day of december '.$quarter[0]));
					break;
				
			}	

			$quarterid = $quarter[1].$quarter[0];
			$quarter_first_day = strtotime($period_start.' 00:00:00');
			$quarter_last_day = strtotime($period_end.' 23:59:59');
			
			$active_cond = $this->getTimePeriod(array($quarter[1]), array($quarter[0])); 
			
			$period_days = PatientMaster::getDaysInBetween($period_start, $period_end); 
			
			
			
			$select = "AES_DECRYPT(p.last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(p.first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(p.zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(p.street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(p.city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(p.phone,'".Zend_Registry::get('salt')."') using latin1) as phone, convert(AES_DECRYPT(p.sex,'".Zend_Registry::get('salt')."') using latin1) as sex,";
			
			
			$periods = array ( '0' => array('start' => $period_start, 'end' => $period_end));
			
			if($_REQUEST['tp'] == 'ipids') {
				$ipids = array('e100574a46f9dc689a7136365f4d17897d0d3d6e',
							'd5917b36df09c72a8b460af9e3f58bbd178c40c7'
							);
			} elseif($type == 'ado') {
				//get only newly admitted patients
				$ipids = $this->admitted_in_period(array($quarter[1]),array($quarter[0]));
				$periods = array();
			} else {
				$ipids = null;
			}
			
			//$patients_arr = Pms_CommonData::patients_days(array('ipids' => array( 0 => '1189f42a5bf3968b631e2d5874bb6f021a128cbc'), 'periods' => array ( '0' => array('start' => $period_start, 'end' => $period_end)), 'client' => $clientid), $select);
			// $patients_arr = Pms_CommonData::patients_days(array('ipids' => $ipids, 'include_standby' => '1','periods' => $periods, 'client' => $clientid), $select);
			$patients_arr = Pms_CommonData::patients_days(array('ipids' => $ipids, 'periods' => $periods, 'client' => $clientid), $select); // TODO -498 - remove standby 02.09.2016
			//$patients_arr = Pms_CommonData::patients_days(array('periods' => array ( '0' => array('start' => $period_start, 'end' => $period_end)), 'client' => $clientid), $select);
			
			
			if($type == 'hisynet' || $type == 'heimnetz' || $type == 'isynet' || $type == 'isynetlong') {
				// $patients_full_periods = Pms_CommonData::patients_days(array('ipids' => array_keys($patients_arr), 'include_standby' => '1','client' => $clientid), $select);
				$patients_full_periods = Pms_CommonData::patients_days(array('ipids' => array_keys($patients_arr),'client' => $clientid), $select); // TODO -498 - remove standby 02.09.2016
			}
			
			if($_REQUEST['dbg'] == '5') {
				flush();
				ob_flush();
				echo 'before foreach';
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
					
					//4111 is 9 DIGIT so dont change anything for IK if it is 9 digit (ISPC-1564)
					//Skype 16-01-06, reactive this
					if(strlen($ik_no) == 9) { //ISPC-1438 field id 4111 (affects all types) it shows the Institutskennzeichen from ISPC its too long. it has 9 or 7 digits. in case of 7 digits its ok. in case of 9 digits IGNORE the FIRST TWO.
						$ik_no = substr($ik_no, 2);
					} elseif($type == 'hisynet' || $type == 'isynet' || $type == 'isynetlong') {
						$ik_no = substr($ik_no, -7);
					}
					
					/*http://jira.significo.de:8080/browse/TODO-60
					 * 4112 (For this export only)
					 * "M" for Health insurance status "Mitglied"
					 * "F" for health insurance status "Familienversichert"
					 * "R" for health insurance status "Rentner"
					 * 
					 * http://jira.significo.de:8080/browse/ISPC-1606 (isynet)
					 * 
					 * 
					 *	- 4112 should be "M" or "F" or "R"
					 *	- 3108 should be "1000" "3000" "5000"
					 *
					 * 
					 */

					
					if($type == 'hisynet' || $type == 'heimnetz' || $type == 'isynet' || $type == 'isynetlong') {
						$ins_type = $pathealthins[0]['insurance_status'];
						if($ins_type == 'S' || $ins_type == 'N') {
							$ins_type = 'XYX';
						}
						$ins_type_3108 = $insurance_statuses[$pathealthins[0]['insurance_status']];
					} else {
						$ins_type = $insurance_statuses[$pathealthins[0]['insurance_status']];
						$ins_type_3108 = $insurance_statuses[$pathealthins[0]['insurance_status']];
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
					->where("isdelete=0 AND (".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql']).") ")
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
					$overall_nursevisits_dates= array();

					//get form from verlauf, created to see what`s deleted
					$nursecourse_q = Doctrine_Query::create()
					->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title, AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') as tabname")
					->from('PatientCourse')
					->where('ipid ="'. $patient['ipid'].'"')
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
					->where('ipid ="'. $patient['ipid'].'"')
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
					
					
					// Contact forms 
					$contactforms = array();
					$contactforms_final = array();
					$contactforms_final_fide = array();
					$overall_contactforms_dates = array();
					
					//Get deleted contact froms from patient course
					$deleted_cf = Doctrine_Query::create()
					->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
					->from('PatientCourse')
					->where('wrong=1')
					->andWhere('ipid ="'. $patient['ipid'].'"')
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
					->where('ipid ="'.$patient['ipid'].'"')
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
					->where('ipid ="'.$patient['ipid'].'"')
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
						->where('ipid ="'.$patient['ipid'].'"')
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
						$patient_no_contactforms = false;
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
								$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
								//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
								//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
								$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
								
							} else {
								// display only if visits are done on that day								
								if( in_array($unique_day,$overall_nursevisits_dates) || in_array($unique_day,$overall_doctorvisits_dates) || in_array($unique_day,$overall_contactforms_dates))
								{
									$first_exported_id[$patient['ipid']][] = strtotime($vv_all_days_final_fide[$unique_day]);
									$export_patients[$patient['ipid']][] = $codes['action']['start'].$unique_day;
									$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92157';
									$export_patients[$patient['ipid']][] = $codes['action']['name'].'SAPV VOLLVERSORGUNG';
									$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
									$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
								$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
								$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
								$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
								$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
						        $export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
								$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
						$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
								$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
									$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
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
							$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
							$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$lanr;
							$ado_admission_dates[$patient['ipid']] = strtotime($patient['details']['admission_date']);
						}
					}
					
					
					if($type != 'hisynet') { // ISPC-1606 can you please remove ALL diagnosis export in the HEIMNETZ- ISYNET export
						
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
					
					if($type == 'long2' || $type == 'isynetlong') {
						
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
					
					/*if($type == 'ado') {
						if($ado_text && $ado_text_id) {
							$export_patients_stamm[$patient['ipid']][] = $ado_text_id.$ado_text;
						}
						if($ado_id) {
							$export_patients_stamm[$patient['ipid']][] = $ado_id.date('dmY',strtotime($patient['details']['admission_date']));
						}
					} else {
						$export_patients_stamm[$patient['ipid']][] = $codes['patient']['admissiondate'].date('dmY',strtotime($patient['details']['admission_date']));
					}*/
					
					
					$export_patients_stamm[$patient['ipid']][] = $codes['patient']['phone'].$patient['details']['phone'];
						
					$fdoc = new FamilyDoctor();
					$famdoc = $fdoc->getFamilyDoc($patient['details']['familydoc_id']);
						
					$export_patients_stamm[$patient['ipid']][] = $codes['patient']['familydoctor'].$famdoc[0]['last_name'].' '.$famdoc[0]['first_name'];
					$export_patients_stamm[$patient['ipid']][] = $codes['patient']['familydoctoraddress'].$famdoc[0]['street1'].'#'.$famdoc[0]['zip'].'#'.$famdoc[0]['city'];

					//$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili1'].'8377503';
					//$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili2'].'M';
					$export_patients_stamm[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type_3108;
					$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili3'].'1';
						

					if((($type == 'hisynet' || $type == 'heimnetz') && ($patient_no_lkoor === false || $patient_no_quartal === false || $patient_no_evas === false)) || $type == 'ado' || ($patient_no_asses === false || $patient_no_vv === false || $patient_no_nurse === false || $patient_no_doctor === false || $patient_no_contactforms === false || $patient_no_tel === false)){ //if patient has activities add to export

						$debug_epids[] = $patient['epid'];
						$debug_ipids[] = $patient['ipid'];

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


				
			//$export_mediaend = str_replace('{FOL}', strlen($export_mediaend), $export_mediaend);
				
			//    	$export['doctor'] = $codes['doctor']['noofdocs'].$client_details['0']['client_name'];


			//    	$export['patient'] = $codes['package']['start'].'0010'; //doctor start header
			//    	$export['patient'] = $codes['package']['length'].'{package_length}'; //length
		}
			
	}


	/*----------------------------------------------------------------------------*/
	/*----------------------------------------------------------------------------*/
	/*----------------------------------------------------------------------------*/
	/*----------------------------------------------------------------------------*/

	
	public function generatefileAction() {
		set_time_limit(0);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$codes = $this->codes;
		$insurance_statuses = array(
    								'F' => '3000',
    								'M' => '1000',
    								'R' => '5000',
    								'N' => 'XYX',
    								'S' => 'XYX',
		);
		 
		$client_details = Client::getClientDataByid($clientid);
		 
		//$client_details['betriebsstattennummer']
		 
		$user_details =  User::getUserDetails($logininfo->userid);
		 
		//$user_details['LANR']
		 
		$active_cond = $this->getTimePeriod(array(3), array(2014)); //hack change me
		 
		$patientmaster = new PatientMaster();
		$period_days = $patientmaster->getDaysInBetween('2014-07-01','2014-09-30'); //hack change me
		 
		 
		$activepat = $this->getActivePatients(array(3),array(2014)); //hack change me
		 
		//ceil(date('n')/3).date('Y')
		$quarterid = '32014'; //change this too!
		$quarter_first_day = strtotime('2014-07-01 00:00:00'); //hack change me
		$quarter_last_day = strtotime('2014-09-30 23:59:59'); //hack change me
		 
		$activepat[] = '999';
		 
		//$activepat = array('62c192fc84cacddb639bdf6944a834b290fcf3fd');
		 
		//$activepat = array('b239259295f069138dc149e3402120b8fcc76df3','af171a18ce0c0da446b2fc9a0841feb6b658532e');
		 
		//$activepat = array('11d4231b8f561187e32edad92408dc8f253d6e67', 'e334d2635d454647259b6e93e4b9621078edd8ff');
		//$activepat = array('11d4231b8f561187e32edad92408dc8f253d6e67', 'e334d2635d454647259b6e93e4b9621078edd8ff');
		 
		/*$patients = Doctrine_Query::create()
		 ->select("*,e.epid as epid,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		 ->from('PatientMaster p')
		 ->Where('isdelete = 0')
		 ->andWhere('isstandby = 0')
		 ->andWhere('isstandbydelete = 0');
		 $patients->leftJoin("p.EpidIpidMapping e");
		 $patients->andWhere('e.clientid = '.$clientid);
		 //$patients->andWhere('e.epid = "pms144"');

		 $patients_arr = $patients->fetchArray();*/
		 
		$patients = Doctrine_Query::create()
		->select("*,e.epid as epid,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->whereIn('p.ipid', $activepat)
		->andWhere('p.isdelete = 0')
		->andWhere('p.isstandbydelete = 0')
		->andWhere('p.isstandby = 0');
		$patients->leftJoin("p.EpidIpidMapping e");
		$patients->andWhere('e.clientid = '.$clientid);
		$patients_arr = $patients->fetchArray();


		//READMISSION MADNESS

		$pm = new PatientMaster ();
		$patientTreatmentDays = $pm->getTreatedDaysRealMultiple ( $activepat, false );

		foreach ( $patientTreatmentDays as $patientTreatmentIpid => $patientTreatmentData ) {
				
			if (count ( $patientTreatmentData ['dischargeDates'] ) > 0 && count ( $patientTreatmentData ['dischargeDates'] ) > count ( $patientTreatmentData ['admissionDates'] )) {
				foreach ( $patientTreatmentData ['dischargeDates'] as $keydischarge => $dischargevalues ) {
					if (count ( $patientTreatmentData ['admissionDates'] ) == 0) {
						$admissionDate = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) );
					} else {
						$admissionDate = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admissionDates'] [$keydischarge] ['date'] ) );
					}
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = $admissionDate;
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $dischargevalues ['date'] ) );
				}
			} else if (count ( $patientTreatmentData ['dischargeDates'] ) == 0 && count ( $patientTreatmentData ['admissionDates'] ) > 0) {

				if (count ( $patientTreatmentData ['dischargeDates'] ) == 0 && empty ( $patientTreatmentData ['discharge_date'] )) {
					$dischargeDate = date ( "Y-m-d", strtotime ( end ( $finalPeriodDays ) ) );
				} else if (count ( $patientTreatmentData ['dischargeDates'] ) == 0 && ! empty ( $patientTreatmentData ['discharge_date'] )) {
					$dischargeDate = $patientTreatmentData ['discharge_date'];
				}

				if (strtotime ( date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) ) ) == strtotime ( date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) ) )) {
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) );
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = $dischargeDate;
				} else {
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admissionDates'] [0] ['date'] ) );
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = $dischargeDate;
				}
					
			} else if (count ( $patientTreatmentData ['dischargeDates'] ) == 0 && count ( $patientTreatmentData ['admissionDates'] ) == 0) {
				$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) );
				$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) );
			} else if (count ( $patientTreatmentData ['admissionDates'] ) > count ( $patientTreatmentData ['dischargeDates'] )) {
				foreach ( $patientTreatmentData ['admissionDates'] as $keyadmission => $admissionvalues ) {
					if (empty ( $patientTreatmentData ['dischargeDates'] [$keyadmission] ['date'] )) {
						$admission = date ( "Y-m-d", strtotime ( $admissionvalues ['date'] ) );
						$discharge = date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) );
							
					} else {
						$admission = date ( "Y-m-d", strtotime ( $admissionvalues ['date'] ) );
						$discharge = date ( "Y-m-d", strtotime ( $patientTreatmentData ['dischargeDates'] [$keyadmission] ['date'] ) );
					}
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $admission ) );
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $discharge ) );
				}
			} else if (count ( $patientTreatmentData ['admissionDates'] ) == count ( $patientTreatmentData ['dischargeDates'] ) && count ( $patientTreatmentData ['admissionDates'] ) != 0) {
				foreach ( $patientTreatmentData ['dischargeDates'] as $keydischarged => $dischargevalues ) {
					$admissionDate = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admissionDates'] [$keydischarged] ['date'] ) );
						
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = $admissionDate;
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $dischargevalues ['date'] ) );
				}
			}
				
			if (date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) ) == date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) )) {
				$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) );
				$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) );
			}
		}

		//generate days array...kinda gay
		foreach ( $patientDateRange as $treatmentIpid => $range ) {
				
			foreach ( $range as $ktreat => $treatmentarr ) {

				foreach ( $treatmentarr ['start'] as $keytreatval => $treatmentval ) {
					$patient_active_days [$treatmentIpid] [] = $pm->getDaysInBetween ( $treatmentval, $treatmentarr ['end'] [$keytreatval] );
				}
			}
		}

		//normalize some stuff with the rest of the method
		foreach($patient_active_days as $ipid => $periods) {
			foreach($periods as $days) {
				foreach($days as $sday) {
					$dayr = date('dmY',strtotime($sday));
					$patient_active_days_final[$ipid][$dayr] = $dayr;
				}
			}
		}


		foreach($patients_arr as $patient) {
			$healthins = new  PatientHealthInsurance();
			$pathealthins = $healthins->getPatientHealthInsurance($patient['ipid']);
				
			if($pathealthins[0]['privatepatient'] != '1') { //no private patients

				$patins_no = $pathealthins[0]['insurance_no']; //patient insurance number
				$kassen_no = $pathealthins[0]['kvk_no']; //kassen nummer
				$ik_no = $pathealthins[0]['institutskennzeichen']; //IK number
				$ins_type = $insurance_statuses[$pathealthins[0]['insurance_status']];


				if(!empty($pathealthins[0]['companyid']) && $pathealthins[0]['companyid'] != 0 && empty($ik_no)){
					$helathins = Doctrine::getTable('HealthInsurance')->find($pathealthins[0]['companyid']);
					$healtharray = $helathins->toArray();
					$ik_no = $healtharray['iknumber'];
				}
				
				if(strlen($ik_no) == '9') {
					$ik_no = substr($ik_no, 2);
				}

				//var_dump($pathealthins);

				//				$export_patients[$patient['ipid']][] = $codes['package']['start'].'0010'; //doctor start header
				$export_patients[$patient['ipid']][] = $codes['package']['start'].'0101'; //doctor start header
				$export_patients[$patient['ipid']][] = $codes['package']['length'].'{PPL}'; //length
				$export_patients[$patient['ipid']][] = $codes['patient']['id'].preg_replace('/[^0-9]/','',$patient['epid']); //only numbers
				$export_patients[$patient['ipid']][] = $codes['patient']['lastname'].$patient['last_name'];
				$export_patients[$patient['ipid']][] = $codes['patient']['firstname'].$patient['first_name'];
				$export_patients[$patient['ipid']][] = $codes['patient']['dob'].date('dmY',strtotime($patient['birthd']));
				$export_patients[$patient['ipid']][] = $codes['patient']['healthinsno'].$patins_no;
				$export_patients[$patient['ipid']][] = $codes['patient']['zipcity'].$patient['zip'].' '.$patient['city'];
				$export_patients[$patient['ipid']][] = $codes['patient']['street'].$patient['street1'];
				$export_patients[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type;
				$export_patients[$patient['ipid']][] = $codes['patient']['gender'].$patient['sex'];
				//$export_patients[$patient['ipid']][] = $codes['patient']['admissiondate2'].date('dmY',strtotime($patient['admission_date']));

				$export_patients[$patient['ipid']][] = $codes['patient']['generationquarter'].$quarterid;
				//$export_patients[$patient['ipid']][] = $codes['patient']['generationdate'].date('dmY');
				$export_patients[$patient['ipid']][] = $codes['patient']['firstiddate'].'{FIDEXP}';





				$export_patients[$patient['ipid']][] = $codes['patient_insurance']['kassenno'].$kassen_no; //.' kassen no'
				$export_patients[$patient['ipid']][] = $codes['patient']['kili4'].'00';
				$export_patients[$patient['ipid']][] = $codes['patient']['kili7'];
				$export_patients[$patient['ipid']][] = $codes['patient_insurance']['ikno'].$ik_no; //.'IK no'
				$export_patients[$patient['ipid']][] = $codes['patient_insurance']['type'].$ins_type;
				$export_patients[$patient['ipid']][] = $codes['patient_insurance']['ewstatus'].'1';
				$export_patients[$patient['ipid']][] = $codes['patient']['kili5'].'00';
				$export_patients[$patient['ipid']][] = $codes['patient']['kili8'];
				$export_patients[$patient['ipid']][] = $codes['patient']['kili6'].'00';





					
					
				//reset activities checks

				$patient_no_vv = false;
				$patient_no_asses = false;
				$patient_no_nurse = false;
				$patient_no_doctor = false;
				$patient_no_tel = false;
				$pat_discharge_date = '';


				$pat_discharge = PatientDischarge::getPatientDischarge($patient['ipid']);
				if($pat_discharge) {
					$pat_discharge_date = strtotime($pat_discharge[0]['discharge_date']);
				}

				//SAPV VOLLVERSORGUNG 92157
					
				$sapv_vv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where("isdelete=0 and ".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql'])." ")
				->andWhere("ipid='" . $patient['ipid'] . "'")
				->andWhere("verordnet LIKE '%4%'")
				->orderBy("id");
				if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
					echo 'VV query:'.$sapv_vv->getSqlQuery().'<br /><br />';
				}
				$sapv_vv_arr = $sapv_vv->fetchArray();

				//var_dump($sapv_vv_arr);
				//exit;
					
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
						$vv_range_days = Pms_CommonData::generateDateRangeArray($vv['verordnungam'],$vv['verordnungbis'],'+1 day','Y-m-d');
						foreach ($vv_range_days as $vv_day){
							if(in_array($vv_day, $period_days)) {
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
				->where("isdelete=0 and ".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql'])." ")
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
						$tv_range_days = Pms_CommonData::generateDateRangeArray($tv['verordnungam'],$tv['verordnungbis'],'+1 day','Y-m-d');
						foreach ($tv_range_days as $tv_day){
							if(in_array($tv_day, $period_days)) {
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
				->where("isdelete=0 and ".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql'])." ")
				->andWhere("ipid='" . $patient['ipid'] . "'")
				->andWhere("verordnet LIKE '%1%'")
				->orderBy("id");
				if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
					echo 'BE query:'.$sapv_be->getSqlQuery().'<br /><br />';
				}
				$sapv_be_arr = $sapv_be->fetchArray();

				//var_dump($sapv_be_arr);
				//exit;
					
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
						$be_range_days = Pms_CommonData::generateDateRangeArray($be['verordnungam'],$be['verordnungbis'],'+1 day','Y-m-d');
						foreach ($be_range_days as $be_day){
							if(in_array($be_day, $period_days)) {
								$be_all_days[] = date('dmY',strtotime($be_day));
								$be_all_days_of[] = date('Y-m-d',strtotime($be_day));
							}
						}
					}
				}
					
				$be_all_days = array_unique($be_all_days);
				$be_all_days_of = array_unique($be_all_days_of);

				$hospitalPeriod = '';
				$dayshospital = '';
				$dayshospitalSecondArr = '';
				$hospitalStartDays = '';
				$hospitalEndDays = '';
				$hospFinalDays = '';



				/*****************************************************************
				 * COPIED FROM ANLAGE10
				 */

				// get hospital locations START
				$hospitalids = Doctrine_Query::create()
				->select("*")
				->from('Locations')
				->where('isdelete = 0')
				->andWhere('client_id ="' . $clientid . '"')
				->andWhere('location_type = 1');

				$hosparray = $hospitalids->fetchArray();

				$hosparr[] = "9999999999";
				foreach ($hosparray as $hospital) {
					$hosparr[] = $hospital['id'];
				}

				//	get patient hospital locations if it has
				$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid ="' . $patient['ipid'] . '"')
				->andWhere('isdelete="0"')
				->andWhereIn('location_id', $hosparr)
				->orderBy('valid_from,id ASC');
				if ($_REQUEST['dbg'] == "1") {
					//print_r($patloc->getDql());
				}
				$patlocationsArr = $patloc->fetchArray();

				foreach ($patlocationsArr as $hospitalPeriod) {
					if ($hospitalPeriod['valid_till'] == "0000-00-00 00:00:00") {
						$hospitalPeriod['valid_till'] = date("Y-m-d");
					}

					$dayshospital[] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($hospitalPeriod['valid_from'])), $hospitalPeriod['valid_till']);

					if (strtotime(date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])))) <= strtotime(date("Y-m-d", strtotime("-1 day", strtotime($hospitalPeriod['valid_till']))))) {
						$valid_from = date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])));
						$valid_till = date("Y-m-d", strtotime("-1 day", strtotime($hospitalPeriod['valid_till'])));

						$dayshospitalSecondArr[] = $patientmaster->getDaysInBetween($valid_from, $valid_till); //used in 24h =>[26]
					} else if (strtotime(date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])))) <= strtotime(date("Y-m-d", strtotime($hospitalPeriod['valid_till'])))) {

						$valid_from = date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])));
						$valid_till = date("Y-m-d", strtotime($hospitalPeriod['valid_till']));

						$dayshospitalSecondArr[] = $patientmaster->getDaysInBetween($valid_from, $valid_till); //used in 24h =>[26]
					} else if (strtotime(date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])))) == strtotime(date("Y-m-d", strtotime($hospitalPeriod['valid_till'])))) {

						$valid_till = date("Y-m-d", strtotime("-1 day", strtotime($hospitalPeriod['valid_till'])));
						$valid_from = date("Y-m-d", strtotime($hospitalPeriod['valid_from']));

						$dayshospitalSecondArr[] = $patientmaster->getDaysInBetween($valid_from, $valid_till); //used in 24h =>[26]
					} else {
						$valid_from = date("Y-m-d", strtotime($hospitalPeriod['valid_from']));
						$valid_till = date("Y-m-d", strtotime($hospitalPeriod['valid_till']));

						$dayshospitalSecondArr[] = $patientmaster->getDaysInBetween($valid_from, $valid_till); //used in 24h =>[26]
					}
					$hospitalStartDays[]  = date("Y-m-d", strtotime($hospitalPeriod['valid_from']));
					$hospitalEndDays[]  = date("Y-m-d", strtotime($hospitalPeriod['valid_till']));
				}

				$hospFinalDays = array();
				if (count($dayshospital) > 1) {
					foreach ($dayshospital as $hospKey => $hospDays) {
						$hospFinalDays = array_merge($hospFinalDays, $hospDays);
					}
				} else if (count($dayshospital) == "1") {
					$hospFinalDays = $dayshospital[0];
				}

				//if hospital discharge is the same as hospital admission => hospital day

				foreach($hospitalEndDays as $hed) {
					if(in_array($hed, $hospitalStartDays)) {
						$hospFinalDays[] = $hed;
					}
				}

				array_unique($hospFinalDays);

				if($_REQUEST['dbg'] == '1' && $patient['ipid'] == '11d4231b8f561187e32edad92408dc8f253d6e67') {
					var_dump($hospFinalDays);
					var_dump($hospitalStartDays);
					var_dump($hospitalEndDays);
				}

				$hospFinalDaysSecond = array("999999999999");
				if (count($dayshospitalSecondArr) > 1) {
					foreach ($dayshospitalSecondArr as $hospKeyS => $hospDaysS) {
						$hospFinalDaysSecond = array_merge($hospFinalDaysSecond, $hospDaysS);
					}
				} else if (count($dayshospitalSecondArr) == "1") {
					$hospFinalDaysSecond = $dayshospitalSecondArr[0];
				}

				array_unique($hospFinalDaysSecond);

				// check if death date is in hospital START
				$distod = Doctrine_Query::create()
				->select("*")
				->from('DischargeMethod')
				->where("isdelete = 0  and clientid=".$logininfo->clientid." and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN')");

				$todarray = $distod->fetchArray();

				$todIds[] = "9999999999999";
				foreach($todarray as $todmethod) {
					$todIds[] = $todmethod['id'];
				}
				$dispat = Doctrine_Query::create()
				->select("*")
				->from("PatientDischarge")
				->where("ipid LIKE '".$patient['ipid']."'")
				->andWhereIn("discharge_method", $todIds);
				//TO DO HERE
				$dischargedArr = $dispat->fetchArray();

				if (in_array(date("Y-m-d", strtotime($dischargedArr[0]['discharge_date'])), $hospFinalDays) && in_array($dischargedArr[0]['discharge_method'], $todIds)) {
					$death_date = date("Y-m-d", strtotime($dischargedArr[0]['discharge_date']));
				} else {
					if (count($dischargedArr) > 0) {
						$death_date = date("Y-m-d", strtotime("+1 day", strtotime($dischargedArr[0]['discharge_date'])));
					} else {
						$death_date = date("Y-m-d", strtotime("+1 day"));
					}
				}


				/*************************************************************
				 * END OF COPY FROM ANLAGE10
				 */

				foreach($vv_all_days as $vv_key => $vv_day_item){
					$vv_day_rearranged = $vv_all_days_of[$vv_key];
					if(((!in_array($vv_day_rearranged, $hospFinalDaysSecond) && $vv_day_rearranged != $death_date)
					|| (in_array($vv_day_rearranged, $hospitalStartDays)) //sapv on hospital entrance
					|| (in_array($vv_day_rearranged, $hospitalEndDays) && $vv_day_rearranged != $death_date) //sapv on hospital exit and not death
					|| (in_array($vv_day_rearranged, $hospitalStartDays) && in_array($vv_day_rearranged, $hospitalEndDays))) //hospital entrance & exit the same day
					&& (in_array($vv_day_item, $patient_active_days_final[$patient['ipid']]))) //match everytyhing against the active days array
					{
						$vv_all_days_final[$vv_day_item] = $vv_day_item;
						$vv_all_days_final_fide[$vv_day_item] = $vv_day_rearranged;
					}
				}

				array_unique($vv_all_days_final);
				array_unique($vv_all_days_final_fide);

				foreach($tv_all_days as $tv_key => $tv_day_item){
					$tv_day_rearranged = $tv_all_days_of[$tv_key];
					if(((!in_array($tv_day_rearranged, $hospFinalDaysSecond) && $tv_day_rearranged != $death_date)
					|| (in_array($tv_day_rearranged, $hospitalStartDays)) //sapv on hospital entrance
					|| (in_array($tv_day_rearranged, $hospitalEndDays) && $tv_day_rearranged != $death_date) //sapv on hospital exit and not death
					|| (in_array($tv_day_rearranged, $hospitalStartDays) && in_array($tv_day_rearranged, $hospitalEndDays))) //hospital entrance & exit the same day
					&& (!in_array($tv_day_item, $vv_all_days_final)) //VV is stronger
					&& (in_array($tv_day_item, $patient_active_days_final[$patient['ipid']]))) //match everytyhing against the active days array
					{
						$tv_all_days_final[$tv_day_item] = $tv_day_item;
						$tv_all_days_final_fide[$tv_day_item] = $tv_day_rearranged;
					}
				}

				array_unique($tv_all_days_final);
				array_unique($tv_all_days_final_fide);


				foreach($be_all_days as $be_key => $be_day_item){
					$be_day_rearranged = $be_all_days_of[$be_key];
					if(((!in_array($be_day_rearranged, $hospFinalDaysSecond) && $be_day_rearranged != $death_date)
					|| (in_array($be_day_rearranged, $hospitalStartDays)) //sapv on hospital entrance
					|| (in_array($be_day_rearranged, $hospitalEndDays) && $be_day_rearranged != $death_date) //sapv on hospital exit and not death
					|| (in_array($be_day_rearranged, $hospitalStartDays) && in_array($be_day_rearranged, $hospitalEndDays))) //hospital entrance & exit the same day
					&& (!in_array($be_day_item, $vv_all_days_final))  //VV is stronger
					&& (in_array($be_day_item, $patient_active_days_final[$patient['ipid']]))) //match everytyhing against the active days array
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

				//get form from verlauf, created to see what`s deleted
				$nursecourse_q = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title, AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') as tabname")
				->from('PatientCourse')
				->where('ipid ="'. $patient['ipid'].'"')
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
				}


				//doctor visits now

				$doctorvisits = array();
				$doctorvisits_final = array();
				$doctorvisits_final_fide = array();
				$doctorstr = '';

				//get form from verlauf, created to see what`s deleted
				$doctorcourse_q = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title, AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') as tabname")
				->from('PatientCourse')
				->where('ipid ="'. $patient['ipid'].'"')
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
				}


				$telephones = array();
				$tel_final = array();
				$tel_final_fide = array();

				$telephone_q = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
				->from('PatientCourse')
				->where('ipid ="'.$patient['ipid'].'"')
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

				//19c7e7a31dbffcfd9a19997ca4d3d5d1eeae1ddd
				if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
					//print_r("\n\n\n AAA");
					//print_r($death_date);
					//var_dump($death_date);
					//print_r($hospFinalDaysSecond);
					//var_dump($vv_all_days);
					//echo '<br />';
					
					echo 'LK<br/>';
					var_dump($lkoo);
					echo '<br /><br />';
					
					
					echo 'Telephones<br/>';
					var_dump($tel_final);
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

				if(sizeof($tel_final) == 0) {
					$patient_no_tel = true;
				}

				foreach($vv_all_days_final as $unique_day){
					$first_exported_id[$patient['ipid']][] = strtotime($vv_all_days_final_fide[$unique_day]);
					$export_patients[$patient['ipid']][] = $codes['action']['start'].$unique_day;
					$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92157';
					$export_patients[$patient['ipid']][] = $codes['action']['name'].'SAPV VOLLVERSORGUNG';
					$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
					$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
					//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
					$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
						
				}
					
					
				//Assessment 92154 + "Erstkoordination" 92153
					
					
				$kvnoq =  Doctrine_Query::create()
				->select('*')
				->from('KvnoAssessment')
				->where('ipid = "'.$patient['ipid'].'"')
				->andWhere('status = "0"')
				->andWhere('iscompleted = 1');
				$assesment = $kvnoq->fetchArray();
				if($assesment && !empty($assesment[0]['completed_date']) && in_array(date('Y-m-d',strtotime($assesment[0]['completed_date'])),$period_days)) {
					$ass_completed_date =  date('dmY',strtotime($assesment[0]['completed_date']));
					$first_exported_id[$patient['ipid']][] = strtotime($assesment[0]['completed_date']);
					$export_patients[$patient['ipid']][] = $codes['action']['start'].$ass_completed_date;
					$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92154';
					$export_patients[$patient['ipid']][] = $codes['action']['name'].'Assesmentpauschale';
					$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
					$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
					//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
					$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
						

					//$export_patients[$patient['ipid']][] = $codes['action']['start'].$ass_completed_date;
					$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92153';
					$export_patients[$patient['ipid']][] = $codes['action']['name'].'Koordinationationspauschale';
					$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
					$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
					//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
					$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst
						
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
						$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
						//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
						$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst

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
						$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
						//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
						$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst

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
						$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
						//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
						$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst

					}
				}
					
				//Diagnosis

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
				if(count($dianoarray)>0)
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
							$export_patients[$patient['ipid']][] = $codes['diagnosis']['name'].$valdia['diagnosis'];
							$export_patients[$patient['ipid']][] = $codes['diagnosis']['icd'].$valdia['icdnumber'];
							$export_patients[$patient['ipid']][] = $codes['diagnosis']['end'].'G'; //.'diagno'


						}
					}

				}
					
				$export_patients_stamm[$patient['ipid']][] = $codes['package']['start'].'6100'; //patient stammdaten
				$export_patients_stamm[$patient['ipid']][] = $codes['package']['length'].'{SPL}'; //length
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['id'].preg_replace('/[^0-9]/','',$patient['epid']); //only numbers
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['lastname'].$patient['last_name'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['firstname'].$patient['first_name'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['dob'].date('dmY',strtotime($patient['birthd']));
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['zipcity'].$patient['zip'].' '.$patient['city'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['street'].$patient['street1'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type;
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['gender'].$patient['sex'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['admissiondate'].date('dmY',strtotime($patient['admission_date']));
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['phone'].$patient['phone'];
					
				$fdoc = new FamilyDoctor();
				$famdoc = $fdoc->getFamilyDoc($patient['familydoc_id']);
					
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['familydoctor'].$famdoc[0]['last_name'].' '.$famdoc[0]['first_name'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['familydoctoraddress'].$famdoc[0]['street1'].'#'.$famdoc[0]['zip'].'#'.$famdoc[0]['city'];

				//$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili1'].'8377503';
				//$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili2'].'M';
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type;
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili3'].'1';
					

				if($patient_no_asses === false || $patient_no_vv === false || $patient_no_nurse === false || $patient_no_doctor === false || $patient_no_tel === false){ //if patient has activities add to export
						
					$debug_epids[] = $patient['epid'];
						
					//Begin patient data export
					$patient_export_string = '';
					$patient_stamm_export_string = '';


					foreach($export_patients[$patient['ipid']] as $line) {
						$patient_export_string .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
					}

					$patient_export_string = str_replace('{PPL}', strlen($patient_export_string), $patient_export_string);

						

					foreach($export_patients_stamm[$patient['ipid']] as $line) {
						$patient_stamm_export_string .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
					}

					$patient_stamm_export_string = str_replace('{SPL}', strlen($patient_stamm_export_string), $patient_stamm_export_string);
						
					if($_REQUEST['dbg'] == 1 && $patient['ipid'] == '11d4231b8f561187e32edad92408dc8f253d6e67') {
						var_dump($first_exported_id[$patient['ipid']]);
					}
						
					$patient_export_string = str_replace('{FIDEXP}', date('dmY',min($first_exported_id[$patient['ipid']])), $patient_export_string);

					$all_patient_data_str .=  $patient_export_string.$patient_stamm_export_string;
				}
			}
		}

		 
		 
		$export['header'][] = $codes['package']['start'].'0020'; //media start header
		$export['header'][] = $codes['package']['length'].'{HPL}'; //length
		//$export['header'][] = $codes['file']['doctor_number'].'999999900'; //LANR of user grabenhorst
		//$export['header'][] = $codes['file']['doctor_number'].$user_details['LANR'];
		$export['header'][] = $codes['file']['doctor_number'].'999999900';  //LANR of user grabenhorst
		$export['header'][] = $codes['file']['creation_date'].date('dmY');
		$export['header'][] = $codes['kili']['1'].'001';
		$export['header'][] = $codes['file']['encoding'].'1';
		 
		foreach($export['header'] as $line) {
			$export_header .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}
			
		$export_header = str_replace('{HPL}', str_pad((strlen($export_header)),5,'0',STR_PAD_LEFT), $export_header);
		 
		$export['file'][] = $codes['package']['start'].'0022'; //package start header
		$export['file'][] = $codes['package']['length'].'{FPL}'; //length
		$export['file'][] = $codes['kili']['2'].'10/93';
		$export['file'][] = $codes['kili']['3'].'02/94';
		$export['file'][] = $codes['kili']['4'].'2';
		$export['file'][] = $codes['file']['exportperiod'].'01092010'.date('dmY');
		$export['file'][] = $codes['file']['begintransfer'].date('His');
		 
		foreach($export['file'] as $line) {
			$export_file .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}
			
		$export_file = str_replace('{FPL}', str_pad((strlen($export_file)),5,'0',STR_PAD_LEFT), $export_file);
		 
		 
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
			$export_doctor .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}
			
		$export_doctor = str_replace('{DPL}',  str_pad((strlen($export_doctor)),5,'0',STR_PAD_LEFT), $export_doctor);
		 
		 
		$export['footer'][] = $codes['package']['start'].'0023'; //package end
		$export['footer'][] = $codes['package']['length'].'{FOL}'; //length
		$export['footer'][] = $codes['file']['alllength'].'{000AOL}';
		$export['footer'][] = $codes['file']['noofpackages'].'1';
		 
		foreach($export['footer'] as $line) {
			$export_footer .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}
			
		$export_footer = str_replace('{FOL}',  str_pad((strlen($export_footer)),5,'0',STR_PAD_LEFT), $export_footer);
		 
		$export['mediaend'][] = $codes['package']['start'].'0021'; //media end
		$export['mediaend'][] = $codes['package']['length'].'00027'; //length
		 
		 
		foreach($export['mediaend'] as $line) {
			$export_mediaend .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}




		$final_export_string = $export_header.$export_file.$export_doctor.$all_patient_data_str.$export_footer.$export_mediaend;

		$final_export_string = str_replace('{000AOL}',  str_pad((strlen($final_export_string)),8,'0',STR_PAD_LEFT), $final_export_string);

		if($_REQUEST['dbg'] == "5"){
			echo nl2br($final_export_string);
			//echo implode('","',array_unique(array_keys($export_patients)));
			sort($debug_epids);
			echo implode('",'."\n".'"', $debug_epids);
			exit();
		}
			
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		header("Content-Type: plain/text; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"export.BDT\";" );
		header("Content-Transfer-Encoding: binary");
		//header("Content-Length: ".filesize($final_export_string));
		echo html_entity_decode($final_export_string);
		//echo $final_export_string;
		exit();


			
		//$export_mediaend = str_replace('{FOL}', strlen($export_mediaend), $export_mediaend);
		 
		//    	$export['doctor'] = $codes['doctor']['noofdocs'].$client_details['0']['client_name'];


		//    	$export['patient'] = $codes['package']['start'].'0010'; //doctor start header
		//    	$export['patient'] = $codes['package']['length'].'{package_length}'; //length
		 
	}


	/*----------------------------------------------------------------------------*/
	/*----------------------------------------------------------------------------*/
	/*----------------------------------------------------------------------------*/
	/*----------------------------------------------------------------------------*/


	public function generatefileadmAction() {
		set_time_limit(0);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$codes = $this->codes;
		$insurance_statuses = array(
    			'F' => '3000',
    			'M' => '1000',
    			'R' => '5000',
    			'N' => 'XYX',
    			'S' => 'XYX',
		);

		$client_details = Client::getClientDataByid($clientid);

		//$client_details['betriebsstattennummer']

		$user_details =  User::getUserDetails($logininfo->userid);

		//$user_details['LANR']

		$active_cond = $this->getTimePeriod(array(2), array(2014)); //hack change me

		$patientmaster = new PatientMaster();
		$period_days = $patientmaster->getDaysInBetween('2014-04-01','2014-06-30'); //hack change me


		// 		$activepat = $this->getActivePatients(array(1),array(2013)); //hack change me

		$activepat = $this->getNewAddedPatients(array(2),array(2014)); //hack change me   (ANCUTA)



		//ceil(date('n')/3).date('Y')
		$quarterid = '22014'; //change this too!
		$quarter_first_day = strtotime('2014-04-01 00:00:00'); //hack change me
		$quarter_last_day = strtotime('2014-06-30 23:59:59'); //hack change me

		$activepat[] = '999';

		//$activepat = array('1251efe529c7a8712d6ae1a8b955a6d3d22bc667','b276e520d25fadb7a063dfab6673f54fb5d210c0','adb82e21dce473b0a50cd7567012486024ab2681','a4bd88bfe774789443914d49cb5987b64e58ec80');

		//$activepat = array('b239259295f069138dc149e3402120b8fcc76df3','af171a18ce0c0da446b2fc9a0841feb6b658532e');

		//$activepat = array('11d4231b8f561187e32edad92408dc8f253d6e67', 'e334d2635d454647259b6e93e4b9621078edd8ff');
		//$activepat = array('11d4231b8f561187e32edad92408dc8f253d6e67', 'e334d2635d454647259b6e93e4b9621078edd8ff');

		/*$patients = Doctrine_Query::create()
		 ->select("*,e.epid as epid,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		 ->from('PatientMaster p')
		 ->Where('isdelete = 0')
		 ->andWhere('isstandby = 0')
		 ->andWhere('isstandbydelete = 0');
		 $patients->leftJoin("p.EpidIpidMapping e");
		 $patients->andWhere('e.clientid = '.$clientid);
		 //$patients->andWhere('e.epid = "pms144"');

		 $patients_arr = $patients->fetchArray();*/

		$patients = Doctrine_Query::create()
		->select("*,e.epid as epid,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->whereIn('p.ipid', $activepat)
		->andWhere('p.isdelete = 0')
		->andWhere('p.isstandbydelete = 0')
		->andWhere('p.isstandby = 0');
		$patients->leftJoin("p.EpidIpidMapping e");
		$patients->andWhere('e.clientid = '.$clientid);
		$patients_arr = $patients->fetchArray();


		//READMISSION MADNESS

		$pm = new PatientMaster ();
		$patientTreatmentDays = $pm->getTreatedDaysRealMultiple ( $activepat, false );

		foreach ( $patientTreatmentDays as $patientTreatmentIpid => $patientTreatmentData ) {

			if (count ( $patientTreatmentData ['dischargeDates'] ) > 0 && count ( $patientTreatmentData ['dischargeDates'] ) > count ( $patientTreatmentData ['admissionDates'] )) {
				foreach ( $patientTreatmentData ['dischargeDates'] as $keydischarge => $dischargevalues ) {
					if (count ( $patientTreatmentData ['admissionDates'] ) == 0) {
						$admissionDate = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) );
					} else {
						$admissionDate = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admissionDates'] [$keydischarge] ['date'] ) );
					}
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = $admissionDate;
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $dischargevalues ['date'] ) );
				}
			} else if (count ( $patientTreatmentData ['dischargeDates'] ) == 0 && count ( $patientTreatmentData ['admissionDates'] ) > 0) {

				if (count ( $patientTreatmentData ['dischargeDates'] ) == 0 && empty ( $patientTreatmentData ['discharge_date'] )) {
					$dischargeDate = date ( "Y-m-d", strtotime ( end ( $finalPeriodDays ) ) );
				} else if (count ( $patientTreatmentData ['dischargeDates'] ) == 0 && ! empty ( $patientTreatmentData ['discharge_date'] )) {
					$dischargeDate = $patientTreatmentData ['discharge_date'];
				}

				if (strtotime ( date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) ) ) == strtotime ( date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) ) )) {
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) );
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = $dischargeDate;
				} else {
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admissionDates'] [0] ['date'] ) );
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = $dischargeDate;
				}

			} else if (count ( $patientTreatmentData ['dischargeDates'] ) == 0 && count ( $patientTreatmentData ['admissionDates'] ) == 0) {
				$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) );
				$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) );
			} else if (count ( $patientTreatmentData ['admissionDates'] ) > count ( $patientTreatmentData ['dischargeDates'] )) {
				foreach ( $patientTreatmentData ['admissionDates'] as $keyadmission => $admissionvalues ) {
					if (empty ( $patientTreatmentData ['dischargeDates'] [$keyadmission] ['date'] )) {
						$admission = date ( "Y-m-d", strtotime ( $admissionvalues ['date'] ) );
						$discharge = date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) );

					} else {
						$admission = date ( "Y-m-d", strtotime ( $admissionvalues ['date'] ) );
						$discharge = date ( "Y-m-d", strtotime ( $patientTreatmentData ['dischargeDates'] [$keyadmission] ['date'] ) );
					}
					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $admission ) );
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $discharge ) );
				}
			} else if (count ( $patientTreatmentData ['admissionDates'] ) == count ( $patientTreatmentData ['dischargeDates'] ) && count ( $patientTreatmentData ['admissionDates'] ) != 0) {
				foreach ( $patientTreatmentData ['dischargeDates'] as $keydischarged => $dischargevalues ) {
					$admissionDate = date ( "Y-m-d", strtotime ( $patientTreatmentData ['admissionDates'] [$keydischarged] ['date'] ) );

					$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = $admissionDate;
					$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $dischargevalues ['date'] ) );
				}
			}

			if (date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) ) == date ( "Y-m-d", strtotime ( $patientTreatmentData ['admission_date'] ) )) {
				$patientDateRange [$patientTreatmentIpid] ['range'] ['start'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) );
				$patientDateRange [$patientTreatmentIpid] ['range'] ['end'] [] = date ( "Y-m-d", strtotime ( $patientTreatmentData ['discharge_date'] ) );
			}
		}
		//generate days array...kinda gay
		foreach ( $patientDateRange as $treatmentIpid => $range ) {

			foreach ( $range as $ktreat => $treatmentarr ) {

				foreach ( $treatmentarr ['start'] as $keytreatval => $treatmentval ) {
					$patient_active_days [$treatmentIpid] [] = $pm->getDaysInBetween ( $treatmentval, $treatmentarr ['end'] [$keytreatval] );
				}
			}
		}


		//normalize some stuff with the rest of the method
		foreach($patient_active_days as $ipid => $periods) {
			foreach($periods as $days) {
				foreach($days as $sday) {
					$dayr = date('dmY',strtotime($sday));
					$patient_active_days_final[$ipid][$dayr] = $dayr;
				}
			}
		}


		foreach($patients_arr as $patient) {
			$healthins = new  PatientHealthInsurance();
			$pathealthins = $healthins->getPatientHealthInsurance($patient['ipid']);

			if($pathealthins[0]['privatepatient'] != '1') { //no private patients

				$patins_no = $pathealthins[0]['insurance_no']; //patient insurance number
				$kassen_no = $pathealthins[0]['kvk_no']; //kassen nummer
				$ik_no = $pathealthins[0]['institutskennzeichen']; //IK number
				$ins_type = $insurance_statuses[$pathealthins[0]['insurance_status']];


				if(!empty($pathealthins[0]['companyid']) && $pathealthins[0]['companyid'] != 0 && empty($ik_no)){
					$helathins = Doctrine::getTable('HealthInsurance')->find($pathealthins[0]['companyid']);
					$healtharray = $helathins->toArray();
					$ik_no = $healtharray['iknumber'];
				}

				//var_dump($pathealthins);

				//				$export_patients[$patient['ipid']][] = $codes['package']['start'].'0010'; //doctor start header
				$export_patients[$patient['ipid']][] = $codes['package']['start'].'0101'; //doctor start header
				$export_patients[$patient['ipid']][] = $codes['package']['length'].'{PPL}'; //length
				$export_patients[$patient['ipid']][] = $codes['patient']['id'].preg_replace('/[^0-9]/','',$patient['epid']); //only numbers
				$export_patients[$patient['ipid']][] = $codes['patient']['lastname'].$patient['last_name'];
				$export_patients[$patient['ipid']][] = $codes['patient']['firstname'].$patient['first_name'];
				$export_patients[$patient['ipid']][] = $codes['patient']['dob'].date('dmY',strtotime($patient['birthd']));
				$export_patients[$patient['ipid']][] = $codes['patient']['healthinsno'].$patins_no;
				$export_patients[$patient['ipid']][] = $codes['patient']['zipcity'].$patient['zip'].' '.$patient['city'];
				$export_patients[$patient['ipid']][] = $codes['patient']['street'].$patient['street1'];
				$export_patients[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type;
				$export_patients[$patient['ipid']][] = $codes['patient']['gender'].$patient['sex'];
				//$export_patients[$patient['ipid']][] = $codes['patient']['admissiondate2'].date('dmY',strtotime($patient['admission_date']));

				$export_patients[$patient['ipid']][] = $codes['patient']['generationquarter'].$quarterid;
				//$export_patients[$patient['ipid']][] = $codes['patient']['generationdate'].date('dmY');
				$export_patients[$patient['ipid']][] = $codes['patient']['firstiddate'].'{FIDEXP}';





				$export_patients[$patient['ipid']][] = $codes['patient_insurance']['kassenno'].$kassen_no; //.' kassen no'
				$export_patients[$patient['ipid']][] = $codes['patient']['kili4'].'00';
				$export_patients[$patient['ipid']][] = $codes['patient']['kili7'];
				$export_patients[$patient['ipid']][] = $codes['patient_insurance']['ikno'].substr($ik_no, (strlen($ik_no) - 7)); //.'IK no'
				$export_patients[$patient['ipid']][] = $codes['patient_insurance']['type'].$ins_type;
				$export_patients[$patient['ipid']][] = $codes['patient_insurance']['ewstatus'].'1';
				$export_patients[$patient['ipid']][] = $codes['patient']['kili5'].'00';
				$export_patients[$patient['ipid']][] = $codes['patient']['kili8'];
				$export_patients[$patient['ipid']][] = $codes['patient']['kili6'].'00';







				//reset activities checks

				$patient_no_vv = false;
				$patient_no_asses = false;
				$patient_no_nurse = false;
				$patient_no_doctor = false;
				$patient_no_tel = false;
				$pat_discharge_date = '';


				$pat_discharge = PatientDischarge::getPatientDischarge($patient['ipid']);
				if($pat_discharge) {
					$pat_discharge_date = strtotime($pat_discharge[0]['discharge_date']);
				}

				//SAPV VOLLVERSORGUNG 92157

				$sapv_vv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where("isdelete=0 and ".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql'])." ")
				->andWhere("ipid='" . $patient['ipid'] . "'")
				->andWhere("verordnet LIKE '%4%'")
				->orderBy("id");
				if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
					echo 'VV query:'.$sapv_vv->getSqlQuery().'<br /><br />';
				}
				$sapv_vv_arr = $sapv_vv->fetchArray();

				//var_dump($sapv_vv_arr);
				//exit;

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
						$vv_range_days = Pms_CommonData::generateDateRangeArray($vv['verordnungam'],$vv['verordnungbis'],'+1 day','Y-m-d');
						foreach ($vv_range_days as $vv_day){
							if(in_array($vv_day, $period_days)) {
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
				->where("isdelete=0 and ".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql'])." ")
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
						$tv_range_days = Pms_CommonData::generateDateRangeArray($tv['verordnungam'],$tv['verordnungbis'],'+1 day','Y-m-d');
						foreach ($tv_range_days as $tv_day){
							if(in_array($tv_day, $period_days)) {
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
				->where("isdelete=0 and ".str_replace(array('%date_start%','%date_end%'),array('verordnungam','verordnungbis'),$active_cond['interval_sql'])." ")
				->andWhere("ipid='" . $patient['ipid'] . "'")
				->andWhere("verordnet LIKE '%1%'")
				->orderBy("id");
				if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
					echo 'BE query:'.$sapv_be->getSqlQuery().'<br /><br />';
				}
				$sapv_be_arr = $sapv_be->fetchArray();

				//var_dump($sapv_be_arr);
				//exit;

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
						$be_range_days = Pms_CommonData::generateDateRangeArray($be['verordnungam'],$be['verordnungbis'],'+1 day','Y-m-d');
						foreach ($be_range_days as $be_day){
							if(in_array($be_day, $period_days)) {
								$be_all_days[] = date('dmY',strtotime($be_day));
								$be_all_days_of[] = date('Y-m-d',strtotime($be_day));
							}
						}
					}
				}

				$be_all_days = array_unique($be_all_days);
				$be_all_days_of = array_unique($be_all_days_of);

				$hospitalPeriod = '';
				$dayshospital = '';
				$dayshospitalSecondArr = '';
				$hospitalStartDays = '';
				$hospitalEndDays = '';
				$hospFinalDays = '';



				/*****************************************************************
				 * COPIED FROM ANLAGE10
				 */

				// get hospital locations START
				$hospitalids = Doctrine_Query::create()
				->select("*")
				->from('Locations')
				->where('isdelete = 0')
				->andWhere('client_id ="' . $clientid . '"')
				->andWhere('location_type = 1');

				$hosparray = $hospitalids->fetchArray();

				$hosparr[] = "9999999999";
				foreach ($hosparray as $hospital) {
					$hosparr[] = $hospital['id'];
				}

				//	get patient hospital locations if it has
				$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid ="' . $patient['ipid'] . '"')
				->andWhere('isdelete="0"')
				->andWhereIn('location_id', $hosparr)
				->orderBy('valid_from,id ASC');
				if ($_REQUEST['dbg'] == "1") {
					//print_r($patloc->getDql());
				}
				$patlocationsArr = $patloc->fetchArray();

				foreach ($patlocationsArr as $hospitalPeriod) {
					if ($hospitalPeriod['valid_till'] == "0000-00-00 00:00:00") {
						$hospitalPeriod['valid_till'] = date("Y-m-d");
					}

					$dayshospital[] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($hospitalPeriod['valid_from'])), $hospitalPeriod['valid_till']);

					if (strtotime(date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])))) <= strtotime(date("Y-m-d", strtotime("-1 day", strtotime($hospitalPeriod['valid_till']))))) {
						$valid_from = date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])));
						$valid_till = date("Y-m-d", strtotime("-1 day", strtotime($hospitalPeriod['valid_till'])));

						$dayshospitalSecondArr[] = $patientmaster->getDaysInBetween($valid_from, $valid_till); //used in 24h =>[26]
					} else if (strtotime(date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])))) <= strtotime(date("Y-m-d", strtotime($hospitalPeriod['valid_till'])))) {

						$valid_from = date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])));
						$valid_till = date("Y-m-d", strtotime($hospitalPeriod['valid_till']));

						$dayshospitalSecondArr[] = $patientmaster->getDaysInBetween($valid_from, $valid_till); //used in 24h =>[26]
					} else if (strtotime(date("Y-m-d", strtotime("+1 day", strtotime($hospitalPeriod['valid_from'])))) == strtotime(date("Y-m-d", strtotime($hospitalPeriod['valid_till'])))) {

						$valid_till = date("Y-m-d", strtotime("-1 day", strtotime($hospitalPeriod['valid_till'])));
						$valid_from = date("Y-m-d", strtotime($hospitalPeriod['valid_from']));

						$dayshospitalSecondArr[] = $patientmaster->getDaysInBetween($valid_from, $valid_till); //used in 24h =>[26]
					} else {
						$valid_from = date("Y-m-d", strtotime($hospitalPeriod['valid_from']));
						$valid_till = date("Y-m-d", strtotime($hospitalPeriod['valid_till']));

						$dayshospitalSecondArr[] = $patientmaster->getDaysInBetween($valid_from, $valid_till); //used in 24h =>[26]
					}
					$hospitalStartDays[]  = date("Y-m-d", strtotime($hospitalPeriod['valid_from']));
					$hospitalEndDays[]  = date("Y-m-d", strtotime($hospitalPeriod['valid_till']));
				}

				$hospFinalDays = array();
				if (count($dayshospital) > 1) {
					foreach ($dayshospital as $hospKey => $hospDays) {
						$hospFinalDays = array_merge($hospFinalDays, $hospDays);
					}
				} else if (count($dayshospital) == "1") {
					$hospFinalDays = $dayshospital[0];
				}

				//if hospital discharge is the same as hospital admission => hospital day

				foreach($hospitalEndDays as $hed) {
					if(in_array($hed, $hospitalStartDays)) {
						$hospFinalDays[] = $hed;
					}
				}

				array_unique($hospFinalDays);

				if($_REQUEST['dbg'] == '1' && $patient['ipid'] == '11d4231b8f561187e32edad92408dc8f253d6e67') {
					var_dump($hospFinalDays);
					var_dump($hospitalStartDays);
					var_dump($hospitalEndDays);
				}

				$hospFinalDaysSecond = array("999999999999");
				if (count($dayshospitalSecondArr) > 1) {
					foreach ($dayshospitalSecondArr as $hospKeyS => $hospDaysS) {
						$hospFinalDaysSecond = array_merge($hospFinalDaysSecond, $hospDaysS);
					}
				} else if (count($dayshospitalSecondArr) == "1") {
					$hospFinalDaysSecond = $dayshospitalSecondArr[0];
				}

				array_unique($hospFinalDaysSecond);

				// check if death date is in hospital START
				$distod = Doctrine_Query::create()
				->select("*")
				->from('DischargeMethod')
				->where("isdelete = 0  and clientid=".$logininfo->clientid." and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN')");

				$todarray = $distod->fetchArray();

				$todIds[] = "9999999999999";
				foreach($todarray as $todmethod) {
					$todIds[] = $todmethod['id'];
				}
				$dispat = Doctrine_Query::create()
				->select("*")
				->from("PatientDischarge")
				->where("ipid LIKE '".$patient['ipid']."'")
				->andWhereIn("discharge_method", $todIds);
				//TO DO HERE
				$dischargedArr = $dispat->fetchArray();

				if (in_array(date("Y-m-d", strtotime($dischargedArr[0]['discharge_date'])), $hospFinalDays) && in_array($dischargedArr[0]['discharge_method'], $todIds)) {
					$death_date = date("Y-m-d", strtotime($dischargedArr[0]['discharge_date']));
				} else {
					if (count($dischargedArr) > 0) {
						$death_date = date("Y-m-d", strtotime("+1 day", strtotime($dischargedArr[0]['discharge_date'])));
					} else {
						$death_date = date("Y-m-d", strtotime("+1 day"));
					}
				}


				/*************************************************************
				 * END OF COPY FROM ANLAGE10
				 */

				foreach($vv_all_days as $vv_key => $vv_day_item){
					$vv_day_rearranged = $vv_all_days_of[$vv_key];
					if(((!in_array($vv_day_rearranged, $hospFinalDaysSecond) && $vv_day_rearranged != $death_date)
					|| (in_array($vv_day_rearranged, $hospitalStartDays)) //sapv on hospital entrance
					|| (in_array($vv_day_rearranged, $hospitalEndDays) && $vv_day_rearranged != $death_date) //sapv on hospital exit and not death
					|| (in_array($vv_day_rearranged, $hospitalStartDays) && in_array($vv_day_rearranged, $hospitalEndDays))) //hospital entrance & exit the same day
					&& (in_array($vv_day_item, $patient_active_days_final[$patient['ipid']]))) //match everytyhing against the active days array
					{
						$vv_all_days_final[$vv_day_item] = $vv_day_item;
						$vv_all_days_final_fide[$vv_day_item] = $vv_day_rearranged;
					}
				}

				array_unique($vv_all_days_final);
				array_unique($vv_all_days_final_fide);

				foreach($tv_all_days as $tv_key => $tv_day_item){
					$tv_day_rearranged = $tv_all_days_of[$tv_key];
					if(((!in_array($tv_day_rearranged, $hospFinalDaysSecond) && $tv_day_rearranged != $death_date)
					|| (in_array($tv_day_rearranged, $hospitalStartDays)) //sapv on hospital entrance
					|| (in_array($tv_day_rearranged, $hospitalEndDays) && $tv_day_rearranged != $death_date) //sapv on hospital exit and not death
					|| (in_array($tv_day_rearranged, $hospitalStartDays) && in_array($tv_day_rearranged, $hospitalEndDays))) //hospital entrance & exit the same day
					&& (!in_array($tv_day_item, $vv_all_days_final)) //VV is stronger
					&& (in_array($tv_day_item, $patient_active_days_final[$patient['ipid']]))) //match everytyhing against the active days array
					{
						$tv_all_days_final[$tv_day_item] = $tv_day_item;
						$tv_all_days_final_fide[$tv_day_item] = $tv_day_rearranged;
					}
				}

				array_unique($tv_all_days_final);
				array_unique($tv_all_days_final_fide);


				foreach($be_all_days as $be_key => $be_day_item){
					$be_day_rearranged = $be_all_days_of[$be_key];
					if(((!in_array($be_day_rearranged, $hospFinalDaysSecond) && $be_day_rearranged != $death_date)
					|| (in_array($be_day_rearranged, $hospitalStartDays)) //sapv on hospital entrance
					|| (in_array($be_day_rearranged, $hospitalEndDays) && $be_day_rearranged != $death_date) //sapv on hospital exit and not death
					|| (in_array($be_day_rearranged, $hospitalStartDays) && in_array($be_day_rearranged, $hospitalEndDays))) //hospital entrance & exit the same day
					&& (!in_array($be_day_item, $vv_all_days_final))  //VV is stronger
					&& (in_array($be_day_item, $patient_active_days_final[$patient['ipid']]))) //match everytyhing against the active days array
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

				//get form from verlauf, created to see what`s deleted
				$nursecourse_q = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title, AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') as tabname")
				->from('PatientCourse')
				->where('ipid ="'. $patient['ipid'].'"')
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
				}


				//doctor visits now

				$doctorvisits = array();
				$doctorvisits_final = array();
				$doctorvisits_final_fide = array();
				$doctorstr = '';

				//get form from verlauf, created to see what`s deleted
				$doctorcourse_q = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title, AES_DECRYPT(tabname,'".Zend_Registry::get('salt')."') as tabname")
				->from('PatientCourse')
				->where('ipid ="'. $patient['ipid'].'"')
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
				}


				$telephones = array();
				$tel_final = array();
				$tel_final_fide = array();

				$telephone_q = Doctrine_Query::create()
				->select("*, AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
				->from('PatientCourse')
				->where('ipid ="'.$patient['ipid'].'"')
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

				//19c7e7a31dbffcfd9a19997ca4d3d5d1eeae1ddd
				if($_REQUEST['dbg'] == "dump" && $patient['ipid'] == $_REQUEST['ipid']) {
					//print_r("\n\n\n AAA");
					//print_r($death_date);
					//var_dump($death_date);
					//print_r($hospFinalDaysSecond);
					//var_dump($vv_all_days);
					//echo '<br />';

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

				if(sizeof($tel_final) == 0) {
					$patient_no_tel = true;
				}


				//NO ACTIONS NEEDED
				/*
				 
				foreach($vv_all_days_final as $unique_day){
				$first_exported_id[$patient['ipid']][] = strtotime($vv_all_days_final_fide[$unique_day]);
				$export_patients[$patient['ipid']][] = $codes['action']['start'].$unique_day;
				$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92157';
				$export_patients[$patient['ipid']][] = $codes['action']['name'].'SAPV VOLLVERSORGUNG';
				$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
				$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
				//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
				$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst

				}


				//Assessment 92154 + "Erstkoordination" 92153


				$kvnoq =  Doctrine_Query::create()
				->select('*')
				->from('KvnoAssessment')
				->where('ipid = "'.$patient['ipid'].'"')
				->andWhere('status = "0"')
				->andWhere('iscompleted = 1');
				$assesment = $kvnoq->fetchArray();
				if($assesment && !empty($assesment[0]['completed_date']) && in_array(date('Y-m-d',strtotime($assesment[0]['completed_date'])),$period_days)) {
				$ass_completed_date =  date('dmY',strtotime($assesment[0]['completed_date']));
				$first_exported_id[$patient['ipid']][] = strtotime($assesment[0]['completed_date']);
				$export_patients[$patient['ipid']][] = $codes['action']['start'].$ass_completed_date;
				$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92154';
				$export_patients[$patient['ipid']][] = $codes['action']['name'].'Assesmentpauschale';
				$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
				$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
				//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
				$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst


				//$export_patients[$patient['ipid']][] = $codes['action']['start'].$ass_completed_date;
				$export_patients[$patient['ipid']][] = $codes['action']['actioncode'].'92153';
				$export_patients[$patient['ipid']][] = $codes['action']['name'].'Koordinationationspauschale';
				$export_patients[$patient['ipid']][] = $codes['action']['time'].'1200';
				$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
				//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
				$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst

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
				$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
				//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
				$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst

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
				$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
				//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
				$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst

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
				$export_patients[$patient['ipid']][] = $codes['patient']['bsnr'].$client_details[0]['betriebsstattennummer'];
				//$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].$user_details[0]['LANR'];
				$export_patients[$patient['ipid']][] = $codes['patient']['lanr'].'999999900'; //LANR of user grabenhorst

				}
				}
				 
				*/
				//NO ACTIONS NEEDED


				//Diagnosis

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
				if(count($dianoarray)>0)
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
							$export_patients[$patient['ipid']][] = $codes['diagnosis']['name'].$valdia['diagnosis'];
							$export_patients[$patient['ipid']][] = $codes['diagnosis']['icd'].$valdia['icdnumber'];
							$export_patients[$patient['ipid']][] = $codes['diagnosis']['end'].'G'; //.'diagno'


						}
					}

				}

				$export_patients_stamm[$patient['ipid']][] = $codes['package']['start'].'6100'; //patient stammdaten
				$export_patients_stamm[$patient['ipid']][] = $codes['package']['length'].'{SPL}'; //length
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['id'].preg_replace('/[^0-9]/','',$patient['epid']); //only numbers
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['lastname'].$patient['last_name'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['firstname'].$patient['first_name'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['dob'].date('dmY',strtotime($patient['birthd']));
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['zipcity'].$patient['zip'].' '.$patient['city'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['street'].$patient['street1'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type;
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['gender'].$patient['sex'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['admissiondate'].date('dmY',strtotime($patient['admission_date']));
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['phone'].$patient['phone'];

				$fdoc = new FamilyDoctor();
				$famdoc = $fdoc->getFamilyDoc($patient['familydoc_id']);

				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['familydoctor'].$famdoc[0]['last_name'].' '.$famdoc[0]['first_name'];
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['familydoctoraddress'].$famdoc[0]['street1'].'#'.$famdoc[0]['zip'].'#'.$famdoc[0]['city'];

				//$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili1'].'8377503';
				//$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili2'].'M';
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['insurance_type'].$ins_type;
				$export_patients_stamm[$patient['ipid']][] = $codes['patient']['kili3'].'1';


				//see below, export all patients
				if(true || $patient_no_asses === false || $patient_no_vv === false || $patient_no_nurse === false || $patient_no_doctor === false || $patient_no_tel === false){ //if patient has activities add to export

					$debug_epids[] = $patient['epid'];

					//Begin patient data export
					$patient_export_string = '';
					$patient_stamm_export_string = '';


					foreach($export_patients[$patient['ipid']] as $line) {
						$patient_export_string .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
					}

					$patient_export_string = str_replace('{PPL}', strlen($patient_export_string), $patient_export_string);



					foreach($export_patients_stamm[$patient['ipid']] as $line) {
						$patient_stamm_export_string .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
					}

					$patient_stamm_export_string = str_replace('{SPL}', strlen($patient_stamm_export_string), $patient_stamm_export_string);

					if($_REQUEST['dbg'] == 1 && $patient['ipid'] == '11d4231b8f561187e32edad92408dc8f253d6e67') {
						var_dump($first_exported_id[$patient['ipid']]);
					}

					$patient_export_string = str_replace('{FIDEXP}', date('dmY',min($first_exported_id[$patient['ipid']])), $patient_export_string);

					$all_patient_data_str .=  $patient_export_string.$patient_stamm_export_string;
				}
			}
		}



		$export['header'][] = $codes['package']['start'].'0020'; //media start header
		$export['header'][] = $codes['package']['length'].'{HPL}'; //length
		//$export['header'][] = $codes['file']['doctor_number'].'999999900'; //LANR of user grabenhorst
		//$export['header'][] = $codes['file']['doctor_number'].$user_details['LANR'];
		$export['header'][] = $codes['file']['doctor_number'].'999999900';  //NO LANR
		$export['header'][] = $codes['file']['creation_date'].date('dmY');
		$export['header'][] = $codes['kili']['1'].'001';
		$export['header'][] = $codes['file']['encoding'].'1';

		foreach($export['header'] as $line) {
			$export_header .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}

		$export_header = str_replace('{HPL}', str_pad((strlen($export_header)),5,'0',STR_PAD_LEFT), $export_header);

		$export['file'][] = $codes['package']['start'].'0022'; //package start header
		$export['file'][] = $codes['package']['length'].'{FPL}'; //length
		$export['file'][] = $codes['kili']['2'].'10/93';
		$export['file'][] = $codes['kili']['3'].'02/94';
		$export['file'][] = $codes['kili']['4'].'2';
		$export['file'][] = $codes['file']['exportperiod'].'01092010'.date('dmY');
		$export['file'][] = $codes['file']['begintransfer'].date('His');

		foreach($export['file'] as $line) {
			$export_file .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}

		$export_file = str_replace('{FPL}', str_pad((strlen($export_file)),5,'0',STR_PAD_LEFT), $export_file);


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
			$export_doctor .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}

		$export_doctor = str_replace('{DPL}',  str_pad((strlen($export_doctor)),5,'0',STR_PAD_LEFT), $export_doctor);


		$export['footer'][] = $codes['package']['start'].'0023'; //package end
		$export['footer'][] = $codes['package']['length'].'{FOL}'; //length
		$export['footer'][] = $codes['file']['alllength'].'{000AOL}';
		$export['footer'][] = $codes['file']['noofpackages'].'1';

		foreach($export['footer'] as $line) {
			$export_footer .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}

		$export_footer = str_replace('{FOL}',  str_pad((strlen($export_footer)),5,'0',STR_PAD_LEFT), $export_footer);

		$export['mediaend'][] = $codes['package']['start'].'0021'; //media end
		$export['mediaend'][] = $codes['package']['length'].'00027'; //length


		foreach($export['mediaend'] as $line) {
			$export_mediaend .= str_pad((strlen($line) + 5),3,'0',STR_PAD_LEFT).$line."\r\n";
		}




		$final_export_string = $export_header.$export_file.$export_doctor.$all_patient_data_str.$export_footer.$export_mediaend;

		$final_export_string = str_replace('{000AOL}',  str_pad((strlen($final_export_string)),8,'0',STR_PAD_LEFT), $final_export_string);

		if($_REQUEST['dbg'] == "5"){
			echo nl2br($final_export_string);
			//echo implode('","',array_unique(array_keys($export_patients)));
			sort($debug_epids);
			echo implode('",'."\n".'"', $debug_epids);
			exit();
		}

		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		header("Content-Type: plain/text; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"export.BDT\";" );
		header("Content-Transfer-Encoding: binary");
		//header("Content-Length: ".filesize($final_export_string));
		echo html_entity_decode($final_export_string);
		//echo $final_export_string;
		exit();



		//$export_mediaend = str_replace('{FOL}', strlen($export_mediaend), $export_mediaend);

		//    	$export['doctor'] = $codes['doctor']['noofdocs'].$client_details['0']['client_name'];


		//    	$export['patient'] = $codes['package']['start'].'0010'; //doctor start header
		//    	$export['patient'] = $codes['package']['length'].'{package_length}'; //length

	}






	function getAllClientPatients($clientid, $whereepid) {
		$actpatient = Doctrine_Query::create()
		->select("p.ipid")
		->from('PatientMaster p');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->where($whereepid.'e.clientid = '.$clientid);


		$actipidarray = $actpatient->fetchArray();

		return $actipidarray;
	}

	private function getActivePatients($quarterarr,$yeararr,$montharr = array()){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		//		$whereepid = $this->getDocCondition();
		$finalipidval = array();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$allpatients = $this->getAllClientPatients($logininfo->clientid, $whereepid);
		foreach($allpatients as $allpatient_item){
			$allpatients_str .= '"'.$allpatient_item['ipid'].'",';
			$allpatients_arr[] = $allpatient_item['ipid'];
		}

		$allpatients_arr[] = '999999';

		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdischarged = 0')
		->andWhere('isdelete = 0')
		->andWhere('isstandby = 0')
		->andWhere('isstandbydelete = 0')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
			
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");


		$actipidarray = $actpatient->fetchArray();

		foreach($actipidarray as $key=>$val)
		{
			$finalipidval[]= $val['ipid'];
		}


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdischarged = 1')
		->andWhere('isdelete = 0')
		->andWhere('isstandby = 0')
		->andWhere('isstandbydelete = 0')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
			
		//->andWhere('ipid in ('.$ipidval.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
			
		$ipidarray = $patient->fetchArray();

		if(is_array($ipidarray) && sizeof($ipidarray) > 0){

			foreach($ipidarray as $key=>$val)
			{
				$disipidval .= '"'.$val['ipid'].'",';
				$disipidarr[] = $val['ipid'];
			}
				
			$disipidarr[] = '999999';
				
			$disquery = Doctrine_Query::create()
			->select("*")
			->from('PatientDischarge')
			//->where('ipid in ('.substr($disipidval,0,-1).') AND ('.str_replace('%date%', 'discharge_date', $active_cond['active_sql']).')');
			->where('('.str_replace('%date%', 'discharge_date', $active_cond['active_sql']).')')
			->andWhereIn('ipid', $disipidarr);

			$disarray = $disquery->fetchArray();
			//			print_r($disquery->getDql());exit;
			foreach($disarray as $key=>$val)
			{
				$finalipidval[]=$val['ipid'];
			}
		}


		if($active_cond['onlynowactive'] != 1){
			$readmission_add = Doctrine_Query::create()
			->select("p.ipid as theipid, p.date AS date_start, id")
			->addSelect("(SELECT r.date FROM PatientReadmission r WHERE r.date_type = 2 and r.date > p.date and p.ipid=r.ipid order by r.date ASC limit 1) AS date_end")
			->from("PatientReadmission p")
			//			->where('p.date_type = 1 and p.ipid in ('.$allpatients_str.' "999999")')
			->where('p.date_type = 1')
			->andWhereIn('p.ipid', $allpatients_arr)
			->having(str_replace('%','',$active_cond['interval_sql']))
			->orderBy("theipid asc, date_start, date_end");

			//			echo $readmission_add->getSqlQuery();
			$add_array = $readmission_add->fetchArray(); //get patients that WERE active during time frame and add to final array

			if(sizeof($add_array) > 0){
				foreach($add_array as $add_pat){
					$finalipidval[] = $add_pat['theipid'];
				}
			}

			$finalipidval = array_unique($finalipidval);
			//				print_r($finalipidval); exit;
			$readmission_del = Doctrine_Query::create()
			->select("p.ipid as theipid, p.date AS date_start, id")
			->addSelect("(SELECT r.date FROM PatientReadmission r WHERE r.date_type = 1 and r.date > p.date and p.ipid=r.ipid order by r.date ASC limit 1) AS date_end")
			->from("PatientReadmission p")
			//->where('p.date_type = 2 and p.ipid in ('.$allpatients_str.' "999999")')
			->where('p.date_type = 2')
			->andWhereIn('p.ipid', $allpatients_arr)
			->having(str_replace('%','',$active_cond['readmission_delete_sql']))
			->orderBy("theipid asc, date_start, date_end");
				
			$del_array = $readmission_del->fetchArray(); //get patients that WERE NOT active during time frame and DELETE from final array
				
			if(sizeof($del_array) > 0){
				foreach($del_array as $del_pat){
					$thekey = array_search($del_pat['theipid'], $finalipidval);
					if($thekey !== false){
						unset($finalipidval[$thekey]);
					}
				}
			}
		}

		//		$activeipid ="'0'";
		//		$comma=",";
		//		foreach($finalipidval as $keyip=>$valipid)
		//		{
		//			$activeipid.=$comma."'".$valipid."'";
		//			$comma=",";
		//		}

		return $finalipidval;
	}
	
	private function admitted_in_period($quarterarr, $yeararr, $montharr = array()) {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		// $whereepid = $this->getDocCondition();
		$final_ipids = array ();
		
		$active_cond = $this->getTimePeriod ( $quarterarr, $yeararr, $montharr );
		$report_period = Pms_CommonData::getPeriodDates($quarterarr, $yeararr, $montharr);
		
		$allpatients = $this->getAllClientPatients ( $logininfo->clientid, $whereepid );
		foreach ( $allpatients as $allpatient_item ) {
			$allpatients_str .= '"' . $allpatient_item ['ipid'] . '",';
			$allpatients_arr [] = $allpatient_item ['ipid'];
		}
		
		$allpatients_arr [] = '999999';
		
		$actpatient = Doctrine_Query::create ()->select ( "*,AES_DECRYPT(last_name,'" . Zend_Registry::get ( 'salt' ) . "') as last_name,AES_DECRYPT(first_name,'" . Zend_Registry::get ( 'salt' ) . "') as first_name,convert(AES_DECRYPT(zip,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as zip,convert(AES_DECRYPT(street1,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as street1,convert(AES_DECRYPT(city,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as city,convert(AES_DECRYPT(phone,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as phone,convert(AES_DECRYPT(sex,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as sex" )->from ( 'PatientMaster p' )->where ( 'isdelete = 0' )->
		andWhere ( 'isstandby = 0' )->andWhere ( 'isstandbydelete = 0' )->
		andWhere ( '(' . str_replace ( '%date%', 'admission_date', $active_cond ['date_sql'] ) . ')' )->orderBy ( "convert(AES_DECRYPT(last_name,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) ASC" );
		
		$actpatient->leftJoin ( "p.EpidIpidMapping e" );
		$actpatient->andWhere ( $whereepid . ' e.clientid = ' . $logininfo->clientid );
		$actpatient->orderBy ( "convert(AES_DECRYPT(last_name,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) ASC" );
		
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
			$admissions[$rad['ipid']][] = $rad['date'];
		}
		
		foreach($admissions as $ipid => $pat_adm)
		{
			foreach($pat_adm as $k_admission => $v_admision)
			{
				if($k_admission == '0') //check only if first admission is in period
				{
					$pat_adm_dates[$ipid] = date('d.m.Y', strtotime($v_admision));
					foreach($report_period['start'] as $k_start => $v_start)
					{
						if(Pms_CommonData::isintersected(strtotime($v_admision), strtotime($v_admision), strtotime($v_start), strtotime($report_period['end'][$k_start].' 23:59:59')))
						{
							$patient_ipids[] = $ipid;
						}
					}
				}
			}
		}
		
		return $patient_ipids;
	}
	

	private function getNewAddedPatients($quarterarr,$yeararr,$montharr = array()){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		//		$whereepid = $this->getDocCondition();
		$final_ipids = array();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$allpatients = $this->getAllClientPatients($logininfo->clientid, $whereepid);
		foreach($allpatients as $allpatient_item){
			$allpatients_str .= '"'.$allpatient_item['ipid'].'",';
			$allpatients_arr[] = $allpatient_item['ipid'];
		}

		$allpatients_arr[] = '999999';

		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdelete = 0')
		// 		->andWhere('isdischarged = 0')
		->andWhere('isstandby = 0')
		->andWhere('isstandbydelete = 0')
		// 		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['date_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
			
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$actipidarray = $actpatient->fetchArray();

		foreach($actipidarray as $key=>$val)
		{
			$admited_in_q[]= $val['ipid'];
		}

		if(empty($admited_in_q)){
			$admited_in_q[] = '999999';
		}

		$readmission_add = Doctrine_Query::create()
		->select("ipid,count(ipid) as admissions")
		->from("PatientReadmission p")
		->where('p.date_type = 1')
		->andWhereIn('p.ipid', $admited_in_q)
		->groupBy('ipid')
		->orderBy('date ASC');
		$add_array = $readmission_add->fetchArray();


		foreach($add_array as $par=>$rad){
			if($rad['admissions'] == '1'){
				$final_ipids[] = $rad['ipid'];
			}
		}


		// 		$controlq = Doctrine_Query::create()
		// 		->select("ipid,admission_date as date")
		// 		->from("PatientMaster p")
		// 		->andWhereIn('p.ipid', $final_ipids);
		// 		$controlq_array = $controlq->fetchArray();

		// 		$controlq = Doctrine_Query::create()
		// 		->select("*")
		// 		->from("PatientReadmission p")
		// 		->andWhereIn('p.ipid', $final_ipids);
		// 		$controlq_array = $controlq->fetchArray();


		// 		foreach($controlq_array as $spar=>$srad){
		// 			if($rad['admissions'] == '1'){
		// 				$controlq_view[$srad['ipid']][$srad['date']] = $srad['date'];
		// 				$controlq_view[$srad['ipid']][$srad['date']] = $srad['date_type'];
		// 			}
		// 		}
		// 		print_R($controlq_view ); exit;



		return $final_ipids;
	}



	public function getTimePeriod ($quarterarr,$yeararr,$montharr = array()) {

		if($quarterarr == 'only_now' && $yeararr == 'only_now' && $montharr == 'only_now') {
			$active_sql = '(date(%date%) >= "'.date('Y').'-'.date('m').'-'.date('d').'") OR ';
			$admission_sql = '(date(%date%) < "'.date('Y').'-'.(date('m')+1).'-01") OR ';
			$date_sql = ' year(%date%) > "1900" AND ';
			$interval_location_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
			$interval_vv_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
			$interval_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
			$negated_interval_sql = '(year(%date_start%) < "1900" AND year(%date_end%) > "2100") OR ';
			$onlynowactive = 1;
		} else {
			$onlynowactive = 0;
			if(!empty($quarterarr)){
				$montharr = array();
				foreach($quarterarr as $quart){
					switch ($quart) {
						case '2':
							$montharr[] = 4;
							$montharr[] = 5;
							$montharr[] = 6;
							break;

						case '3':
							$montharr[] = 7;
							$montharr[] = 8;
							$montharr[] = 9;
							break;

						case '4':
							$montharr[] = 10;
							$montharr[] = 11;
							$montharr[] = 12;
							break;

						default:
							$montharr[] = 1;
							$montharr[] = 2;
							$montharr[] = 3;
							break;
					}
				}
			}

			foreach($yeararr as $year){
				if(is_numeric($year)){
					$year_sql .= '"'.$year.'",';

					if(is_array($montharr) && sizeof($montharr)){
						foreach($montharr as $month){
							if(is_numeric($month)){
								//$active_sql .= '(month(%date%) >= "'.$month.'" AND year(%date%) >= "'.$year.'") OR ';
								//$admission_sql .= '(year(%date%) <= "'.$year.'" AND month(%date%) <= "'.$month.'") OR ';

								$this_month = $year.'-'.$month.'-01';
								$this_month_end = date('Y-m-d',strtotime('-1 day',strtotime('+1 month',strtotime($this_month))));
								$next_month = date('Y-m-',strtotime('+1 month',strtotime($this_month))).'01';

								$active_sql .= '(date(%date%) >= "'.$year.'-'.$month.'-01") OR ';
								$admission_sql .= '(date(%date%) < "'.$next_month.'") OR ';
								$interval_location_sql .= '(((date(%date_start%) >= "'.$year.'-'.$month.'-01") AND date(%date_start%) < "'.$next_month.'" AND (date(%date_end%) >= "'.$year.'-'.$month.'-01"  OR date(%date_end%) = "0000-00-00") ) OR ((date(%date_start%) >= "'.$year.'-'.$month.'-01" AND date(%date_start%) < "'.$next_month.'") AND (date(%date_end%) < "'.$next_month.'" AND (date(%date_end%) >= "'.$year.'-'.$month.'-01" OR date(%date_end%) = "0000-00-00")))) OR ';
								$interval_vv_sql .= '(((date(%date_start%) >= "'.$year.'-'.$month.'-01") AND date(%date_start%) < "'.$next_month.'" AND (date(%date_end%) >= "'.$year.'-'.$month.'-01"  OR date(%date_end%) = "0000-00-00") ) OR ((date(%date_start%) >= "'.$year.'-'.$month.'-01" AND date(%date_start%) < "'.$next_month.'") AND (date(%date_end%) < "'.$next_month.'" AND (date(%date_end%) >= "'.$year.'-'.$month.'-01" OR date(%date_end%) = "0000-00-00")))) OR ';
								$interval_sql .= '(((date(%date_start%) <= "'.$year.'-'.$month.'-01") AND (date(%date_end%) >= "'.$year.'-'.$month.'-01")) OR ((date(%date_start%) >= "'.$year.'-'.$month.'-01") AND (date(%date_start%) < "'.$next_month.'"))) OR ';
								$negated_interval_sql .= '((date(%date_start%) < "'.$year.'-'.$month.'-01") AND date(%date_end%) < "'.$year.'-'.$month.'-01" AND %date_end% IS NOT NULL) OR  date(%date_start%) >= "'.$next_month.'") OR ';
								$readmission_delete_sql .= '(%date_start% IS NOT NULL AND (date(%date_start%) < "'.$year.'-'.$month.'-01") AND date(%date_end%) > "'.$this_month_end.'") AND ';
							} else {
								$active_sql .= '(year(%date%) >= "'.$year.'") OR ';
								$admission_sql .= '(year(%date%) <= "'.$year.'") OR ';
								$interval_location_sql .= '(((year(%date_start%) <= "'.$year.'") AND (year(%date_end%) >= "'.$year.'")) OR ((year(%date_start%) >= "'.$year.'") AND (year(%date_start%) < "'.($year+1).'"))) OR ';
								$interval_vv_sql .= '(((year(%date_start%) <= "'.$year.'") AND (year(%date_end%) >= "'.$year.'")) OR ((year(%date_start%) >= "'.$year.'") AND (year(%date_start%) < "'.($year+1).'"))) OR ';
								$interval_sql .= '(((year(%date_start%) <= "'.$year.'") AND (year(%date_end%) >= "'.$year.'")) OR ((year(%date_start%) >= "'.$year.'") AND (year(%date_start%) < "'.($year+1).'"))) OR ';
								$negated_interval_sql .= '((year(%date_start%) > "'.$year.'") OR (year(%date_end%) < "'.$year.'")) OR ';
								$readmission_delete_sql .= '(%date_start% IS NOT NULL AND (year(%date_start%) < "'.$year.'") AND (year(%date_end%) > "'.$year.'")) AND ';
							}
						}
					} else {
						$active_sql .= '(year(%date%) >= "'.$year.'") OR ';
						$admission_sql .= '(year(%date%) <= "'.$year.'") OR ';
						//$interval_sql .= '((year(%date_start%) >= "'.$year.'") AND (year(%date_end%) <= "'.$year.'")) OR ';
						$interval_location_sql .= '(((year(%date_start%) <= "'.$year.'") AND (year(%date_end%) >= "'.$year.'")) OR ((year(%date_start%) >= "'.$year.'") AND (year(%date_start%) < "'.($year+1).'"))) OR ';
						$interval_vv_sql .= '(((year(%date_start%) <= "'.$year.'") AND (year(%date_end%) >= "'.$year.'")) OR ((year(%date_start%) >= "'.$year.'") AND (year(%date_start%) < "'.($year+1).'"))) OR ';
						$interval_sql .= '(((year(%date_start%) <= "'.$year.'") AND (year(%date_end%) >= "'.$year.'")) OR ((year(%date_start%) >= "'.$year.'") AND (year(%date_start%) < "'.($year+1).'"))) OR ';
						$negated_interval_sql .= '((year(%date_start%) > "'.$year.'") OR (year(%date_end%) < "'.$year.'" AND %date_end% IS NOT NULL)) OR ';
						$readmission_delete_sql .= '(%date_start% IS NOT NULL AND (year(%date_start%) < "'.$year.'") AND (year(%date_end%) > "'.$year.'")) AND ';
					}

				}
			}

			foreach($montharr as $month){
				if(is_numeric($month)){
					$month_sql .= '"'.$month.'",';
				}
			}

			if(!empty($month_sql)) {
				$date_sql .= ' month(%date%) IN ('.substr($month_sql,0,-1).') AND ';
			}

			if(!empty($year_sql)) {
				$date_sql .= ' year(%date%) IN ('.substr($year_sql,0,-1).') AND ';
			}

		}

		if(!empty($date_sql)) {
			$return['date_sql'] = substr($date_sql, 0, -5);
			$return['active_sql'] = substr($active_sql, 0, -4);
			$return['admission_sql'] = substr($admission_sql, 0, -4);
			$return['interval_location_sql'] = substr($interval_location_sql, 0, -4);
			$return['interval_vv_sql'] = substr($interval_vv_sql, 0, -4);
			$return['interval_sql'] = substr($interval_sql, 0, -4);
			$return['negated_interval_sql'] = substr($negated_interval_sql, 0, -4);
			$return['readmission_delete_sql'] = substr($readmission_delete_sql, 0, -5);
			$return['onlynowactive'] = $onlynowactive;
				
			return $return;
		} else {
			return false;
		}

	}


















}
?>