<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDischarge', 'IDAT');

	class PatientDischarge extends BasePatientDischarge {

		public $triggerformid = 10;
		public $triggerformname = "frmpatientdischarge";

		public function getPatientDischarge($ipid)
		{
			//ISPC-2746 Carmen 07.12.2020
			if(!is_array($ipid))
			{
				$ipid = array($ipid);
			}
			//--
			$loc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				//ISPC-2746 Carmen 07.12.2020
				//->where("ipid='" . $ipid . "' and isdelete='0'")
				->whereIn("ipid", $ipid)
				->andWhere("isdelete='0'");
				//--
			$locat = $loc->execute();

			if($locat)
			{
				$disarr = $locat->toArray();
				return $disarr;
			}
		}
		
		public function getPatientInactiveDischarge($ipid)
		{
			$loc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				->where("ipid='" . $ipid . "' and isdelete='1'")
				->orderBy('discharge_date ASC');
			$locat = $loc->execute();

			if($locat)
			{
				$disarr = $locat->toArray();
				return $disarr;
			}
		}

		public function get_patients_discharge($ipids)
		{
		    if(empty($ipids) ){
		        
		        return;
		    }
		    
		    $ipids = is_array($ipids) ? $ipids : array($ipids); 
		    
			$loc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				->whereIn('ipid', $ipids)
				->andWhere('isdelete = "0"');
			$locat = $loc->fetchArray();
			if($locat)
			{
				return $locat;
			}
		}

		public function getPatientLastDischarge($ipid)
		{
			$loc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				->where("ipid='" . $ipid . "' and isdelete='0'")
				->orderBy('discharge_date desc')
				->limit(1);
			$disarr = $loc->fetchArray();
			if($disarr)
			{
				return $disarr;
			}
		}

		public function getPatientsDischargeDetails($ipids, $order_by = 'discharge_date', $sort = 'asc')
		{

			$ipid_str = '99999999,';
			foreach($ipids as $ipid)
			{
				$ipid_str .= '"' . $ipid['ipid'] . '",';
				$ipidz[$ipid['ipid']] = $ipid;
				$ipidz_simple[] = $ipid['ipid'];
			}

			$discharge = Doctrine_Query::create()
				->select("d.ipid, date_format(d.discharge_date,'%d.%m.%Y') as discharge_date, unix_timestamp(d.discharge_date) as discharge_date_ut,d.discharge_method")
				->from('PatientDischarge d')
				->whereIn('d.ipid', $ipidz_simple)
				->andWhere('isdelete = 0') // this was added for sorting
				->orderBy('d.discharge_date ' . $sort);
			$discharge_data = $discharge->fetchArray();

			$pm = new PatientMaster();
			$real_dot = $pm->get_treatment_days($ipidz_simple); // new function using PatientActivity

			foreach($discharge_data as $discharge_item)
			{
				$patient_data[$discharge_item['ipid']]['discharge_date'] = $discharge_item['discharge_date'];
				$patient_data[$discharge_item['ipid']]['admission_date'] = $ipidz[$discharge_item['ipid']]['admission_date'];
				$patient_data[$discharge_item['ipid']]['discharge_method'] = $discharge_item['discharge_method'];
				$patient_data[$discharge_item['ipid']]['dot'] = $real_dot[$discharge_item['ipid']]['days_of_treatment'];
				$dots[$discharge_item['ipid']] = $patient_data[$discharge_item['ipid']]['dot'];
				$discharge_dates[$discharge_item['ipid']] = $discharge_item['discharge_date_ut'];
			}
			if($order_by == 'dot')
			{
				array_multisort($dots, (strtoupper($sort) == 'DESC' ? SORT_DESC : SORT_ASC), $patient_data);
			}
			else
			{
				array_multisort($discharge_dates, (strtoupper($sort) == 'DESC' ? SORT_DESC : SORT_ASC), SORT_NUMERIC, $patient_data);
			}

			return $patient_data;
		}
		public function getPatientsInactiveDischargeDetails($ipids, $order_by = 'discharge_date', $sort = 'asc')
		{

			$ipid_str = '99999999,';
			foreach($ipids as $ipid)
			{
				$ipid_str .= '"' . $ipid['ipid'] . '",';
				$ipidz[$ipid['ipid']] = $ipid;
				$ipidz_simple[] = $ipid['ipid'];
			}

			$discharge = Doctrine_Query::create()
				->select("d.ipid,d.discharge_date as discharge_date_full, date_format(d.discharge_date,'%d.%m.%Y') as discharge_date, unix_timestamp(d.discharge_date) as discharge_date_ut,d.discharge_method")
				->from('PatientDischarge d')
				->whereIn('d.ipid', $ipidz_simple)
				->andWhere('isdelete = 1') // this was added for sorting
				->orderBy('d.discharge_date ' . $sort);
			$discharge_data = $discharge->fetchArray();


			foreach($discharge_data as $discharge_item)
			{
				$patient_data[$discharge_item['ipid']]['discharge_date'] = $discharge_item['discharge_date'];
				$patient_data[$discharge_item['ipid']]['discharge_date_full'] = $discharge_item['discharge_date_full'];
				$patient_data[$discharge_item['ipid']]['admission_date'] = $ipidz[$discharge_item['ipid']]['admission_date'];
				$patient_data[$discharge_item['ipid']]['discharge_method'] = $discharge_item['discharge_method'];
				$discharge_dates[$discharge_item['ipid']] = $discharge_item['discharge_date_ut'];
			}
			if($order_by == 'dot')
			{
				array_multisort($dots, (strtoupper($sort) == 'DESC' ? SORT_DESC : SORT_ASC), $patient_data);
			}
			else
			{
				array_multisort($discharge_dates, (strtoupper($sort) == 'DESC' ? SORT_DESC : SORT_ASC), SORT_NUMERIC, $patient_data);
			}

			return $patient_data;
		}

		public static function isDischarged($pid)
		{

// 			$ipid = Pms_CommonData::getIpid($pid);
			$loc = Doctrine_Query::create()
// 				->select("*")
				->select("id")
				->from('PatientMaster')
// 				->where("ipid='" . $ipid . "'")
				->where("id = ? ", $pid)
				->andWhere("isdischarged=1");
			$locexe = $loc->execute();
			if($locexe)
			{
				$disarr = $locexe->toArray();
				if(count($disarr) > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		public static function isArchived($pid)
		{

// 			$ipid = Pms_CommonData::getIpid($pid);

			$loc = Doctrine_Query::create()
// 				->select("*")
				->select("id")
				->from('PatientMaster')
// 				->where("ipid='" . $ipid . "'")
				->where("id = ? " . $pid)
				->andWhere("isarchived=1");
			$locexe = $loc->execute();
			if($locexe)
			{
				$disarr = $locexe->toArray();
				if(count($disarr) > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		public function checkSttiDischarge($clientid)
		{
			$Tr = new Zend_View_Helper_Translate();
			if($clientid)
			{
				//1. get all STTI ids from all clients
				$dm = Doctrine_Query::create()
					->select("*")
					->from('DischargeMethod dm')
					->where('dm.abbr LIKE "%stti%"')
					->andWhere('dm.isdelete ="0"')
					->andWhere('dm.clientid="' . $clientid . '"')
					->orderBy('dm.clientid ASC');
				$dischargemethods = $dm->fetchArray();

// 				$dm_array[] = '99999999999';
				$dm_array = array();
				
				foreach($dischargemethods as $k_dm => $v_dm)
				{
					$dm_array[] = $v_dm['id'];
				}

				//2. get all patients from patient discharge where discharge method is stti AND 4 weeks multiplier today
				$sqlWeekDays = "";
				$sqlHaving = "";
				for($i = 0; $i <= 8; $i++)
				{
					$sqlWeekDays .= "MOD( DATEDIFF(  '" . date("Y-m-d", strtotime("+ " . $i . " day")) . "',  pd.discharge_date ) , 28 ) AS fourWeeks" . $i . " ,";
					$sqlHaving .= "fourWeeks" . $i . " = 0 OR ";
				}

				$sqlWeekDays = substr($sqlWeekDays, 0, -1);
				$sqlHaving = substr($sqlHaving, 0, -4);

				$discharged_patients = array();
				if ( ! empty($dm_array)) {
					$dis_pat = Doctrine_Query::create()
						->select('*, ' . $sqlWeekDays)
						->from('PatientDischarge pd')
						->whereIn('pd.discharge_method', $dm_array)
						->andwhere('isdelete = 0')
						->andWhere('discharge_date < DATE(NOW())')
						->having($sqlHaving);
					$discharged_patients = $dis_pat->fetchArray();
				}
				
// 				$dp_array[] = '999999999999';
				$dp_array = array();
// 				$dis_patients[] = '999999999999';
				$dis_patients = array();

				foreach($discharged_patients as $k_dp => $v_dp)
				{
					if($v_dp['fourWeeks0'] == '0')
					{
						$dp_array[] = $v_dp['ipid'];
						$dis_patients[$v_dp['ipid']] = $v_dp;
					}
				}

				//3. get patients that are dead by death button from stti ipids
				$dead_patients = array();
				if ( ! empty($dp_array)) {
					$dead_pat = Doctrine_Query::create()
						->select('*')
						->from('PatientDeath')
						->where('isdelete ="0"')
						->andwhereIn('ipid', $dp_array);
					$dead_patients = $dead_pat->fetchArray();
				}

// 				$dead_patients_arr[] = '999999999999';
				$dead_patients_arr = array();
				foreach($dead_patients as $k_dead_pat => $v_dead_pat)
				{
					$dead_patients_arr[$v_dead_pat['ipid']] = $v_dead_pat['ipid'];
				}

				//3.1 filter the dead patients from discharged array
// 				$dpat_ipids_array[] = '99999999';
				$dpat_ipids_array = array();
// 				$dpat_array[] = '99999999';
				$dpat_array[] = array();
				foreach($discharged_patients as $k_dpat => $v_dpat)
				{
					if(!in_array($v_dpat['ipid'], $dead_patients_arr))
					{
						if($v_dpat['fourWeeks0'] == '0')
						{
							$dpat_ipids_array[] = $v_dpat['ipid'];
							$dpat_array[$v_dpat['ipid']] = $dis_patients[$v_dpat['ipid']];
						}
					}
				}

				//4. get patient details and assigned doctors
				$patient_details = array();
				if ( ! empty($dpat_ipids_array)) {
					$patient_master = Doctrine_Query::create()
						->select('*, pm.id as patientId, e.epid,
							CONVERT(AES_DECRYPT(first_name,"' . Zend_Registry::get('salt') . '") using latin1) as first_name,
							CONVERT(AES_DECRYPT(last_name,"' . Zend_Registry::get('salt') . '") using latin1) as last_name')
						->from('PatientMaster pm')
						->leftJoin("pm.EpidIpidMapping e")
						->andwhere('pm.ipid = e.ipid')
						->andwhere('e.clientid = ' . $clientid)
						->andWhereIn('e.ipid', $dpat_ipids_array);
	
					$patient_details = $patient_master->fetchArray();
				}


// 				$patient_det_arr[] = '999999';
				$patient_det_arr = array();
				foreach($patient_details as $k_pat => $v_pat)
				{
					$patient_det_arr[$v_pat['ipid']] = $v_pat;
					$patient_det_arr[$v_pat['ipid']]['epid'] = $v_pat['EpidIpidMapping']['epid'];
				}

				//5. added new message center system permissions
				$user = new User();
				$users = $user->getClientsUsers($clientid);


// 				$users_assigned[] = '99999999999';
				$users_assigned = array();
// 				$users_all[] = '99999999999';
				$users_all =  array();
				foreach($users as $k_user => $v_users)
				{
					if($v_users['notifications']['fourwnote'] == 'assigned')
					{
						$users_assigned[] = $v_users['id'];
					}
					else if($v_users['notifications']['fourwnote'] == 'all')
					{
						$users_all[] = $v_users['id'];
					}
				}

				$patients_doctors = array();
				if ( ! empty($dpat_ipids_array) && ! empty($users_assigned)) {
					$qpamap = Doctrine_Query::create()
						->select('*, e.ipid')
						->from('PatientQpaMapping pqm')
						->leftJoin("pqm.EpidIpidMapping e")
						->andwhere('e.epid=pqm.epid')
						->andwhere('e.clientid = ' . $clientid)
						->andWhereIn('e.ipid', $dpat_ipids_array)
						->andWhereIn('pqm.userid', $users_assigned);
	
					$patients_doctors = $qpamap->fetchArray();
				}

				$doctors_arr = array_merge($users_assigned, $users_all);

				$doc_details_arr = array();
				if ( ! empty($doctors_arr)) {
					$doc_details = Doctrine_Query::create()
						->select('*')
						->from('User')
						->whereIn('id', $doctors_arr)
						->andWhere('isdelete="0" or fourwnote="1"')
						->orderBy('last_name ASC');
					$doc_details_arr = $doc_details->fetchArray();
				}

				foreach($doc_details_arr as $k_doc => $v_doc)
				{
					$doctor_details[$v_doc['id']] = $v_doc;
				}

				// #########################
				// ISPC-1600
				// #########################
				$email_subject = $Tr->translate('youhavenewmailinispc'). ' ' . date('d.m.Y H:i');
				$email_text = "";
				$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
				// link to ISPC
				// Maria:: Migration ISPC to CISPC 08.08.2020
				//$email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
				// ISPC-2475 @Lore 31.10.2019
				$email_text .= $Tr->translate('system_wide_email_text_login');
				
				// client details
				$client_details_array = Client::getClientDataByid($clientid);
				if(!empty($client_details_array)){
				    $client_details = $client_details_array[0];
				}
				$client_details_string = "<br/>";
				$client_details_string  .= "<br/> ".$client_details['team_name'];
				$client_details_string  .= "<br/> ".$client_details['street1'];
				$client_details_string  .= "<br/> ".$client_details['postcode']." ".$client_details['city'];
				$client_details_string  .= "<br/> ".$client_details['emailid'];
				$email_text .= $client_details_string;
				
				
				//TODO-3164 Ancuta 08.09.2020
				$email_data = array();
				$email_data['client_info'] = $client_details_string;
				$email_text = "";//overwrite
				$email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
				//-- 
				
				//send mail to users who selected assigned in message central
				foreach($patients_doctors as $k_pat_doc => $v_pat_doc)
				{
					$patient_ipid = $v_pat_doc['EpidIpidMapping']['ipid'];

					$current_date = date("Y-m-d");

					$message = 'Der Patient <a href="patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($patient_det_arr[$patient_ipid]['patientId']) . '"><b>' . $patient_det_arr[$patient_ipid]['first_name'] . ' ' . $patient_det_arr[$patient_ipid]['last_name'] . '</b></a> wurde mit dem Status Behandlungsunterbrechung entlassen. Diese Entlassung ist mittlerweile 4 Wochen her. Diese Nachricht erinnert Sie an die Überprüfung des Status des Patienten';

					$messages = new Messages();

					if($messages->getSttiNotifications($v_pat_doc['clientid'], $v_pat_doc['userid'], $patient_ipid, $current_date) == "0") //check if mesages where sent for today for a user
					{
						$mail = new Messages();
						$mail->sender = "0";
						$mail->clientid = $v_pat_doc['clientid'];
						$mail->recipient = $v_pat_doc['userid'];
						$mail->msg_date = date("Y-m-d H:i:s", time());
						$mail->title = Pms_CommonData::aesEncrypt(' Prüfung Behandlungsunterbrechung ' . $patient_det_arr[$patient_ipid]['first_name'] . ' ' . $patient_det_arr[$patient_ipid]['last_name'] . ' ');
						$mail->content = Pms_CommonData::aesEncrypt($message);
						$mail->source = 'stti_system_message';
						$mail->ipid = $patient_ipid;
						$mail->create_date = date("Y-m-d", time());
						$mail->create_user = "0";
						$mail->read_msg = '0';
						$mail->save();

						if(!$doctor_details[$v_pat_doc['userid']]['email'])
						{
							$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
							$email = new Zend_Mail('UTF-8');
							$email->setBodyHtml($email_text)
								->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
								->addTo($doctor_details[$v_pat_doc['userid']]['emailid'], $doctor_details[$v_pat_doc['userid']]['last_name'] . ' ' . $doctor_details[$v_pat_doc['userid']]['first_name'])
								->setSubject($email_subject)
								->setIpids($patient_ipid) //set logging ipids
								->send($mail_transport);
						}
					}
				}


				foreach($users_all as $k_user => $v_user)
				{
					foreach($dpat_ipids_array as $stti_patient_ipid)
					{
						$patient_ipid = $stti_patient_ipid;
						$current_date = date("Y-m-d");
						$message = 'Der Patient <a href="patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($patient_det_arr[$patient_ipid]['patientId']) . '"><b>' . $patient_det_arr[$patient_ipid]['first_name'] . ' ' . $patient_det_arr[$patient_ipid]['last_name'] . '</b></a> wurde mit dem Status Behandlungsunterbrechung entlassen. Diese Entlassung ist mittlerweile 4 Wochen her. Diese Nachricht erinnert Sie an die Überprüfung des Status des Patienten';
						$messages = new Messages();
						if($messages->getSttiNotifications($clientid, $v_user, $patient_ipid, $current_date) == "0" && $v_user != '99999999999' && $stti_patient_ipid != '99999999') //check if mesages where sent for today for a user
						{
							$mail = new Messages();
							$mail->sender = "0";
							$mail->clientid = $clientid;
							$mail->recipient = $v_user;
							$mail->msg_date = date("Y-m-d H:i:s", time());
							$mail->title = Pms_CommonData::aesEncrypt(' Prüfung Behandlungsunterbrechung ' . $patient_det_arr[$patient_ipid]['first_name'] . ' ' . $patient_det_arr[$patient_ipid]['last_name'] . ' ');
							$mail->content = Pms_CommonData::aesEncrypt($message);
							$mail->source = 'stti_system_message';
							$mail->ipid = $patient_ipid;
							$mail->create_date = date("Y-m-d", time());
							$mail->create_user = "0";
							$mail->read_msg = '0';
							$mail->save();

							if(!$doctor_details[$v_user]['email'])
							{
								$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
								$email = new Zend_Mail('UTF-8');
								$email->setBodyHtml($email_text)
								    ->setReplyTo(ISPC_SENDER)
									->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
									->addTo($doctor_details[$v_user]['emailid'], $doctor_details[$v_user]['last_name'] . ' ' . $doctor_details[$v_user]['first_name'])
									->setSubject($email_subject)
									->setIpids($patient_ipid) //set logging ipids
									->send($mail_transport);
							}
						}
					}
				}

				if($_REQUEST['dbg'])
				{
					print_r($patient_details);
					print_r($patients_doctors);
					print_r($epidarr);
					print_r($dpat_array);
					print_r($dis_patients);
					exit;
				}
			}
			else
			{
				return false;
			}
		}
		
		public function get_patient_dischargemethod_attached()
		{
			//get discharge_method attached
			$drop = Doctrine_Query::create()
			->select('DISTINCT(discharge_method) as discharge_method')
			->from('PatientDischarge')
			->where('isdelete = "0"');
			
			$droparray = $drop->fetchArray();
			//var_dump($droparray); exit;
		
			return $droparray;
		}
		
		public function get_patient_dischargelocation_attached()
		{
			//get discharge_method attached
			$drop = Doctrine_Query::create()
			->select('DISTINCT(discharge_location) as discharge_location')
			->from('PatientDischarge')
			->where('isdelete = "0"');
				
			$droparray = $drop->fetchArray();
			//var_dump($droparray); exit;
		
			return $droparray;
		}
		
		
		public function getPatientsDeathDate($clientid, $ipids, $death_time = false ){
		
		    if ( empty($clientid)){
		        return array();
		    }
		    
		    if(empty($ipids)){
		        //return array();
		        
		        // get client ipidds
		        $patient_master = Doctrine_Query::create()
		        ->select('pm.ipid as ipid')
				->from('PatientMaster pm')
				->leftJoin("pm.EpidIpidMapping e")
				->andwhere('pm.ipid = e.ipid')
				->andwhere('pm.isdelete =0')
				->andwhere('e.clientid = ' . $clientid);
		        $patient_details = $patient_master->fetchArray();
		        
		        if ( ! empty($patient_details)){
		            $ipids = array_column($patient_details, 'ipid');
		        }
		    }
		
		    // get discharge method for patients dead
		    $dm_dead = Doctrine_Query::create()
		    ->select("*")
		    ->from('DischargeMethod')
		    ->where("clientid = " . $clientid)
		    ->andwhere("abbr='TOD' OR abbr='tod' OR abbr='Verstorben' OR abbr='verstorben'  OR abbr='VERSTORBEN' OR abbr='Tod' OR abbr='TODNA'")
		    ->andwhere('isdelete = 0');
		    $dm_deadarray = $dm_dead->fetchArray();
		
		
		    foreach($dm_deadarray as $key => $val)
		    {
		        $dm_deadfinal[] = $val['id'];
		    }
		
		    if(empty($dm_deadfinal)){
		        return array();
		    }
		
		    //get tod patients
		    $todpatients = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientDischarge')
		    ->whereIn('ipid', $ipids)
		    ->andWhereIn('discharge_method', $dm_deadfinal)
		    ->andWhere('isdelete = 0');
		    $todpatientarray = $todpatients->fetchArray();
		
		    $patients_tod_date = array();
		    if($todpatientarray)
		    {
		        foreach($todpatientarray as $key => $todpatient)
		        {
		            if($death_time){
    		            $patients_tod_date[$todpatient['ipid']] = date("Y-m-d H:i:s", strtotime($todpatient['discharge_date']));
		            } else{
		                
    		            $patients_tod_date[$todpatient['ipid']] = date("Y-m-d", strtotime($todpatient['discharge_date']));
		            }
		        }
		
		        return $patients_tod_date;
		    } else {
		        return array();
		    }
		
		}


        /**
         * ISPC-2391, elena, 03.09.2020
         *
         * @param $ipids
         * @param $methods
         * @return mixed
         */
		public function get_count_patients_discharged_by_methods($ipids, $methods){
            //ISPC-2391,Elena,11.02.2021
            if(empty($ipids)){
                return 0;
            }
            $countpatients = Doctrine_Query::create()
                ->select('count(*)')
                ->from('PatientDischarge')
                ->whereIn('ipid', $ipids)
                ->andWhereIn('discharge_method', $methods)
                ->andWhere('isdelete = 0');
            $countpatientsarray = $countpatients->fetchArray();
            //print_r($countpatientsarray[0]);
            return $countpatientsarray[0]['count'];

        }

        /**
         * ISPC-2391, elena, 03.09.2020
         *
         * @param $ipids
         * @param $methods
         * @param $locations
         * @return mixed
         */
        public function get_count_patients_discharged_by_methods_and_locations($ipids, $methods, $locations){
            //ISPC-2391,Elena,11.02.2021
            if(empty($ipids)){
                return 0;
            }
            $countpatients = Doctrine_Query::create()
                ->select('count(*)')
                ->from('PatientDischarge')
                ->whereIn('ipid', $ipids)
                ->andWhereIn('discharge_method', $methods)
                ->andWhereIn('discharge_location', $locations)
                ->andWhere('isdelete = 0');
            $countpatientsarray = $countpatients->fetchArray();
            //print_r($countpatientsarray[0]);
            return $countpatientsarray[0]['count'];

        }
		
		

	}

?>