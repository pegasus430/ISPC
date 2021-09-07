<?php

	Doctrine_Manager::getInstance()->bindComponent('Messages', 'MDAT');

	class Messages extends BaseMessages {

		public $triggerformid = 3;
		public $triggerformname = "frmwritemessages";

		public function priority_ranks(){
			$Tr = new Zend_View_Helper_Translate();
			
			$priority = array(
				"none"=>$Tr->translate('priority_none'),
				"low"=>$Tr->translate('priority_low'),
				"middle"=>$Tr->translate('priority_middle'),
				"high"=>$Tr->translate('priority_high'),
			);
			
			return $priority; 
			
		}
		
		public function getAnlageWlNotifications($clientid, $userid, $date)
		{
			$mess = Doctrine_Query::create()
				->select("*")
				->from("Messages")
				->where("recipient = '" . $userid . "'")
				->andWhere("clientid = '" . $clientid . "'")
				->andWhere("sender = '0'")
				->andWhere("create_date = '" . $date . "'");
			$messarray = $mess->fetchArray();

			return count($messarray);
		}

		public function noAnlageNotifications($users, $date)
		{
			$mess = Doctrine_Query::create()
				->select("*")
				->from("Messages")
				->whereIn("recipient", $users)
				->andWhere("sender = '0'")
				->andWhere("source = '6_weeks_system_message'")
				->andWhere("create_date = '" . $date . "'");
			$messarray = $mess->fetchArray();

			$users_with_notification[] = '999999999999';

			if($messarray)
			{
				foreach($messarray as $user_message)
				{
					$users_with_notification[] = $user_message['recipient'];
				}
			}

			return $users_with_notification;
		}

		public function noAnlagefourNotifications($users, $date)
		{
			$mess = Doctrine_Query::create()
				->select("*")
				->from("Messages")
				->whereIn("recipient", $users)
				->andWhere("sender = '0'")
				->andWhere("source = '4_weeks_system_message'")
				->andWhere("create_date = '" . $date . "'");
			$messarray = $mess->fetchArray();

			$users_with_notification[] = '999999999999';

			if($messarray)
			{
				foreach($messarray as $user_message)
				{
					$users_with_notification[] = $user_message['recipient'];
				}
			}

			return $users_with_notification;
		}

		public function no_25days_vv_notifications($users, $ipids)
		{

			$mess = Doctrine_Query::create()
				->select("*")
				->from("Messages")
				->whereIn("recipient", $users)
				->andWhereIn('ipid', $ipids)
				->andWhere("sender = '0'")
				->andWhere("source = '25_daysvv_system_message'")
				->andWhere("create_date <= '" . date('Y-m-d H:i:s', time()) . "'");

			$messarray = $mess->fetchArray();

			$users_with_notification[] = '999999999999';

			if($messarray)
			{
				foreach($messarray as $user_message)
				{
					$users_with_notification[$user_message['recipient']][] = $user_message['ipid'];
				}
			}

			return $users_with_notification;
		}
		
		/* public function no_patbirth_notifications($users, $ipids)
		{
		
			$mess = Doctrine_Query::create()
			->select("*")
			->from("Messages")
			->whereIn("recipient", $users)
			->andWhereIn('ipid', $ipids)
			->andWhere("sender = '0'")
			->andWhere("source = 'dashboard_display_patbirthday'")
			->andWhere("create_date <= '" . date('Y-m-d H:i:s', time()) . "'")
			->andWhere("YEAR(create_date) = '" . date('Y', time()) . "'");
		
			$messarray = $mess->fetchArray();
		
			$users_with_notification[] = '999999999999';
		
			if($messarray)
			{
				foreach($messarray as $user_message)
				{
					$users_with_notification[$user_message['recipient']][] = $user_message['ipid'];
				}
			}
		
			return $users_with_notification;
		} */

		public function getSttiNotifications($clientid, $userid, $ipid, $date)
		{
			$mess = Doctrine_Query::create()
				->select("*")
				->from("Messages")
				->where("recipient = '" . $userid . "'")
				->andWhere("clientid = '" . $clientid . "'")
				->andWhere("sender = '0'")
				->andWhere('source = "stti_system_message"')
				->andWhere('ipid = "' . $ipid . '"')
				->andWhere("create_date = '" . $date . "'");
			$messarray = $mess->fetchArray();

			return count($messarray);
		}

		public function anlage6weeks()
		{
			//check the script last run
			$file_data = file_get_contents('runfile.txt');
			$run = false; //run the messaging script
			$save = false; //save or not the run file

			if($file_data)
			{
				if(strtotime(date('Y-m-d')) > strtotime(date('Y-m-d', $file_data)))
				{
					$run = true;
					$save = true;
				}
				else
				{
					$run = false;
					$save = false;
				}
			}
			else
			{
				//file not found or not created yet
				$run = true;
				$save = true;
			}


			if(($save === true && $run === true) || $_REQUEST['send_mails'])
			{
//		Load required models
				$client = new Client();
				$modules = new Modules();
				$user = new User();
				$Tr = new Zend_View_Helper_Translate();
//		$mess = new Messages();
//		1. get all clients and filter wl only
				$clients = $client->getClientData();

				foreach($clients as $v_client)
				{
					$clients_data[$v_client['id']] = $v_client;
					$clients_ids[] = $v_client['id'];
				}

				
				$wl_clients = $modules->checkClientsModulePrivileges('51', $clients_ids, $clients_data);

//		2. get users of remaining wl clients
				$users = $user->getClientsUsers($clients_ids);

				foreach($users as $user)
				{
					if($user['notifications']['sixweeks'] != 'none')
					{
						$users_ids[] = $user['id'];
						$users_to_notify[] = $user;
					}
				}

//		2.1 get assigned patients for following users id
				$fdoc = Doctrine_Query::create()
					->select("*, e.epid, e.ipid")
					->from('PatientQpaMapping q')
					->whereIn("q.userid", $users_ids)
					->andWhere('q.epid!=""')
					->leftJoin('q.EpidIpidMapping e')
					->where('q.epid = e.epid');

				$doc_assigned_patients = $fdoc->fetchArray();

				foreach($doc_assigned_patients as $doc_patient)
				{
					$users_patients[$doc_patient['userid']][] = $doc_patient['EpidIpidMapping']['ipid'];
				}

//		3. execute "6 week" query
				$sqlWeekDays = "";
				$sqlHaving = "";
				//6weeks changed to 8weeks
				for($i = 0; $i <= 8; $i++)
				{
					$sqlWeekDays .= "MOD( DATEDIFF(  '" . date("Y-m-d", strtotime("+ " . $i . " day")) . "',  `admission_date` ) , 56 ) AS sixWeeks" . $i . " ,";
					$sqlHaving .= "sixWeeks" . $i . " = 0 OR ";
				}

				$sqlHaving = substr($sqlHaving, 0, -4);

				$patientwl = Doctrine_Query::create()
					->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
						AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,
						ipid, admission_date, " . $sqlWeekDays . " e.epid, e.clientid")
					->from('PatientMaster as p')
					->where('isdelete = 0')
					->andWhere('isdischarged = 0')
					->andWhere('isstandby = 0')
					->andWhere('isarchived = 0')
					->andWhere('isstandbydelete = 0')
					->andWhere('admission_date < DATE(NOW())')
					->having($sqlHaving);
				$patientwl->leftJoin("p.EpidIpidMapping e");
				$patientwl->andWhere('e.ipid = p.ipid');
				$patientwl->andWhereIn('e.clientid', $wl_clients['ids']);

				$wl_client_patients = $patientwl->fetchArray();

				$wl_patients_ipids[] = '99999999999';
				foreach($wl_client_patients as $k_patient_wl => $v_patient_wl)
				{
					$wl_patients_ipids[] = $v_patient_wl['ipid'];
				}

				//exclude private patients from previous query
				$health = Doctrine_Query::create();
				$health->select("*")
					->from('PatientHealthInsurance')
					->whereIn('ipid', $wl_patients_ipids)
					->andWhere('privatepatient="1"');
				$health_arr = $health->fetchArray();

				foreach($health_arr as $k_health => $v_health)
				{
					$privat_patient[] = $v_health['ipid'];
				}
				
				//remove private patients
				if(!empty($privat_patient)){
					$patientwl->andWhereNotIn('p.ipid', $privat_patient);
				}

				
				
				//get hospiz location
				$fdoc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where("location_type=2")
				->andWhere('isdelete=0')
				->orderBy('location ASC');
				$lochospizarr = $fdoc->fetchArray();
				
				$locid_hospiz[] = '9999999999';
				
				foreach($lochospizarr as $k_hospiz => $v_hospiz)
				{
					$locid_hospiz[] = $v_hospiz['id'];
				}
				
				//get patient with location active Hospiz
				if(!empty($locid_hospiz)){
					
					$patlocs = Doctrine_Query::create()
					->select('location_id,ipid')
					->from('PatientLocation')
					->where('isdelete="0"')
					->andWhereIn('ipid', $wl_patients_ipids)
					->andWhereIn('location_id', $locid_hospiz)
					->andWhere("valid_till='0000-00-00 00:00:00'")
					->orderBy('id DESC');
					$patloc_hospizarr = $patlocs->fetchArray();
					
					foreach($patloc_hospizarr as $k_pathospiz => $v_pathospiz)
					{
						$ipids_hospiz[] = $v_pathospiz['ipid'];
					}
				}
				
				if(!empty($ipids_hospiz)){
					$patientwl->andWhereNotIn('p.ipid', $ipids_hospiz);
				}

				$patientidwlarray = $patientwl->fetchArray(); //no private patients here

				
				if($patientidwlarray)
				{
					foreach($patientidwlarray as $k_patient_today => $v_patient_today)
					{
						if($v_patient_today['sixWeeks0'] == '0')
						{
							//today only
							$anlagetoday[$v_patient_today['ipid']]['eventTitle'] = '<a href="patient/anlage4wl?id=' . Pms_Uuid::encrypt($v_patient_today['patientId']) . '">Prüfung Anlage 4 - ' . $v_patient_today['last_name'] . ", " . $v_patient_today['first_name'] . "</a>";
							$anlagetoday[$v_patient_today['ipid']]['startDate'] = date("d.m.Y");
							$anlagetoday[$v_patient_today['ipid']]['clientid'] = $v_patient_today['EpidIpidMapping']['clientid'];
						}
					}
				}

				//check which users from wlusers array have received the notification today
				// and set message to remaining users all of them have notify on
				$date = date('Y-m-d');
				if(count($users_ids) == '0')
				{
					$users_ids[] = '9999999';
				}
				$has_today_anlage = $this->noAnlageNotifications($users_ids, $date);
 
				foreach($users_to_notify as $notified_client_user)
				{
					//check if user dsettings allow to sent all client users in mail or just assigned users
					$message = '';
					if($notified_client_user['notifications']['sixweeks'] == 'assigned' && $users_patients[$notified_client_user['id']])
					{
						//prepare message with assigned patients only
						foreach($anlagetoday as $anlage_ipid_assigned => $anlage_asigned)
						{
							//make sure that the assigned user patients are from the same client as user
							if(in_array($anlage_ipid_assigned, $users_patients[$notified_client_user['id']]) && $anlage_asigned['clientid'] == $notified_client_user['clientid'])
							{
								$message .= $anlage_asigned['eventTitle'] . "\n";
								$anlage_ipids[$notified_client_user['id']][] = $anlage_ipid_assigned;
							}
						}
					}
					else if($notified_client_user['notifications']['sixweeks'] == 'all')
					{
						//prepare message with all client patients
						foreach($anlagetoday as $anlage_ipid => $anlage)
						{
							if($anlage['clientid'] == $notified_client_user['clientid'])
							{
								$message .= $anlage['eventTitle'] . "\n";
								$anlage_ipids[$notified_client_user['id']][] = $anlage_ipid;
							}
						}
					}
					
					
					// ####################################
					// ISPC-1600
					// ####################################
					$email_subject = $Tr->translate('youhavenewmailinispc'). ' ' . date('d.m.Y H:i');
					$email_text = "";
					$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
					// link to ISPC
					//$email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
					// ISPC-2475 @Lore 31.10.2019
					$email_text .= $Tr->translate('system_wide_email_text_login');
					
					// client details
					$client_details_string = "<br/>";
					$client_details_string  .= "<br/> ".$clients_data[$notified_client_user['clientid']]['team_name'];
					$client_details_string  .= "<br/> ".$clients_data[$notified_client_user['clientid']]['street1'];
					$client_details_string  .= "<br/> ".$clients_data[$notified_client_user['clientid']]['postcode']." ".$clients_data[$notified_client_user['clientid']]['city'];
					$client_details_string  .= "<br/> ".$clients_data[$notified_client_user['clientid']]['emailid'];
					$email_text .= $client_details_string;
					
					
					//TODO-3164 Ancuta 08.09.2020
					$email_data = array();
					$email_data['client_info'] = $client_details_string;
					$email_text = "";//overwrite
					$email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
					//-- 
					
					if(!empty($message) && !in_array($notified_client_user['id'], $has_today_anlage))
					{
						$message_header = "Für folgende Patienten muss heute (" . date("d.m.Y") . ") das Formular Anlage 4a ausgefüllt werden: \n";
						$insert_data[$notified_client_user['id']] = array();
						$insert_data[$notified_client_user['id']]['sender'] = '0';
						$insert_data[$notified_client_user['id']]['clientid'] = $notified_client_user['clientid'];
						$insert_data[$notified_client_user['id']]['recipient'] = $notified_client_user['id'];
						$insert_data[$notified_client_user['id']]['msg_date'] = date("Y-m-d H:i:s", time());
						$insert_data[$notified_client_user['id']]['title'] = Pms_CommonData::aesEncrypt(' Heutige Anlage 4 Formulare (' . date("d.m.Y") . ')');
						$insert_data[$notified_client_user['id']]['content'] = Pms_CommonData::aesEncrypt($message_header . $message);
						$insert_data[$notified_client_user['id']]['source'] = '6_weeks_system_message';
						$insert_data[$notified_client_user['id']]['create_date'] = date("Y-m-d", time());
						$insert_data[$notified_client_user['id']]['create_user'] = '0';
						$insert_data[$notified_client_user['id']]['read_msg'] = '0';

						$msg = new Messages();
						$msg->fromArray($insert_data[$notified_client_user['id']]);
						$msg->save();

						$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
						$mail = new Zend_Mail('UTF-8');
						$mail->setBodyHtml($email_text)
							->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
							->addTo($notified_client_user['emailid'], $notified_client_user['last_name'] . ' ' . $notified_client_user['first_name'])
							->setSubject($email_subject)
							->setIpids($anlage_ipids[$notified_client_user['id']])
							->send($mail_transport);
					}
				}
			}
			if($save)
			{
				//write in a file current timestamp
				$file_pointer = fopen('runfile.txt', 'w+');
				fwrite($file_pointer, strtotime(date('Y-m-d H:i:s')));
				fclose($file_pointer);
			}
		}

		public function anlage4weeks()
		{
			//check the script last run
			$file_data = file_get_contents('runfile_volversorgung.txt');
			//run the messaging script
			$run = false;
			//save or not the run file
			$save = false;

			if($file_data)
			{
				if(strtotime(date('Y-m-d')) > strtotime(date('Y-m-d', $file_data)))
				{
					$run = true;
					$save = true;
				}
				else
				{
					$run = false;
					$save = false;
				}
			}
			else
			{
				//file not found or not created yet
				$run = true;
				$save = true;
			}


			if(($save === true && $run === true) || $_REQUEST['send_mails'])
			{

				//Load required models
				$client = new Client();
				$modules = new Modules();
				$user = new User();
				$Tr = new Zend_View_Helper_Translate();
				//$mess = new Messages();
				//1. get all clients and filter wl only
				$clients = $client->getClientData();

				foreach($clients as $v_client)
				{
					$clients_data[$v_client['id']] = $v_client;
					$clients_ids[] = $v_client['id'];
				}

				$wl_clients = $modules->checkClientsModulePrivileges('51', $clients_ids, $clients_data);

				//2. get users of remaining wl clients
				$users = $user->getClientsUsers($clients_ids);

				foreach($users as $user)
				{
					if($user['notifications']['sixweeks'] != 'none')
					{
						$users_ids[] = $user['id'];
						$users_to_notify[] = $user;
					}
				}

				//2.1 get assigned patients for following users id
				$fdoc = Doctrine_Query::create()
					->select("*, e.epid, e.ipid")
					->from('PatientQpaMapping q')
					->whereIn("q.userid", $users_ids)
					->andWhere('q.epid!=""')
					->leftJoin('q.EpidIpidMapping e')
					->where('q.epid = e.epid');
				$doc_assigned_patients = $fdoc->fetchArray();

				foreach($doc_assigned_patients as $doc_patient)
				{
					$users_patients[$doc_patient['userid']][] = $doc_patient['EpidIpidMapping']['ipid'];
				}

				//3. execute "4 week" query
				$sqlWeekDays = "";
				$sqlHaving = "";
				//4weeks
				for($i = 0; $i <= 8; $i++)
				{
					$sqlWeekDays .= "MOD( DATEDIFF(  '" . date("Y-m-d", strtotime("+ " . $i . " day")) . "',  `vollversorgung_date` ) , 28 ) AS fourWeeks" . $i . " ,";
					$sqlHaving .= "fourWeeks" . $i . " = 0 OR ";
				}

				$sqlHaving = substr($sqlHaving, 0, -4);

				$patientwl = Doctrine_Query::create()
					->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
					AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,
					ipid, admission_date, " . $sqlWeekDays . " e.epid, e.clientid")
					->from('PatientMaster as p')
					->where('isdelete = 0')
					->andWhere('isdischarged = 0')
					->andWhere('isstandby = 0')
					->andWhere('isarchived = 0')
					->andWhere('isstandbydelete = 0')
					->andWhere('vollversorgung = 1')
					->andWhere('vollversorgung_date < DATE(NOW())')
					->having($sqlHaving);
				$patientwl->leftJoin("p.EpidIpidMapping e");
				$patientwl->andWhere('e.ipid = p.ipid');
				$patientwl->andWhereIn('e.clientid', $wl_clients['ids']);

				$wl_client_patients = $patientwl->fetchArray();

				$wl_patients_ipids[] = '99999999999';
				foreach($wl_client_patients as $k_patient_wl => $v_patient_wl)
				{
					$wl_patients_ipids[] = $v_patient_wl['ipid'];
				}

				//exclude private patients from previous query
				$health = Doctrine_Query::create();
				$health->select("*")
					->from('PatientHealthInsurance')
					->whereIn('ipid', $wl_patients_ipids)
					->andWhere('privatepatient="1"');
				$health_arr = $health->fetchArray();

				$privat_patient[] = '99999999';
				foreach($health_arr as $k_health => $v_health)
				{
					$privat_patient[] = $v_health['ipid'];
				}

				//get hospiz location
				$fdoc = Doctrine_Query::create()
					->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
					->from('Locations')
					->where("location_type=2")
					->andWhere('isdelete=0')
					->orderBy('location ASC');
				$lochospizarr = $fdoc->fetchArray();

				$locid_hospiz[] = '9999999999';

				foreach($lochospizarr as $k_hospiz => $v_hospiz)
				{
					$locid_hospiz[] = $v_hospiz['id'];
				}

				//get patient with location active Hospiz
				$patlocs = Doctrine_Query::create()
					->select('location_id,ipid')
					->from('PatientLocation')
					->where('isdelete="0"')
					->andWhereIn('ipid', $wl_patients_ipids)
					->andWhereIn('location_id', $locid_hospiz)
					->andWhere("valid_till='0000-00-00 00:00:00'")
					->orderBy('id DESC');

				$patloc_hospizarr = $patlocs->fetchArray();

				$ipids_hospiz[] = '999999999';
				foreach($patloc_hospizarr as $k_pathospiz => $v_pathospiz)
				{
					$ipids_hospiz[] = $v_pathospiz['ipid'];
				}

				//remove private patients and hospiz active location patients
				$patientwl->andWhereNotIn('p.ipid', $privat_patient);
				$patientwl->andWhereNotIn('p.ipid', $ipids_hospiz);
				$patientidwlarray = $patientwl->fetchArray(); //no private and hospiz location patients here


				if($patientidwlarray)
				{
					foreach($patientidwlarray as $k_patient_today => $v_patient_today)
					{
						if($v_patient_today['fourWeeks0'] == '0')
						{
							//today only
							$anlagetoday[$v_patient_today['ipid']]['eventTitle'] = '<a href="patient/anlage4awl?id=' . Pms_Uuid::encrypt($v_patient_today['patientId']) . '">Prüfung Anlage 4a WL - ' . $v_patient_today['last_name'] . ", " . $v_patient_today['first_name'] . "</a>";
							$anlagetoday[$v_patient_today['ipid']]['startDate'] = date("d.m.Y");
							$anlagetoday[$v_patient_today['ipid']]['clientid'] = $v_patient_today['EpidIpidMapping']['clientid'];
						}
					}
				}

				//check which users from wlusers array have received the notification today
				// and set message to remaining users all of them have notify on
				$date = date('Y-m-d');
				if(count($users_ids) == '0')
				{
					$users_ids[] = '9999999';
				}
				$has_today_anlage = $this->noAnlagefourNotifications($users_ids, $date);
				
				foreach($users_to_notify as $notified_client_user)
				{
					//check if user dsettings allow to sent all client users in mail or just assigned users
					$message = '';
					if($notified_client_user['notifications']['sixweeks'] == 'assigned' && $users_patients[$notified_client_user['id']])
					{
						//prepare message with assigned patients only
						foreach($anlagetoday as $anlage_ipid_assigned => $anlage_asigned)
						{
							//make sure that the assigned user patients are from the same client as user
							if(in_array($anlage_ipid_assigned, $users_patients[$notified_client_user['id']]) && $anlage_asigned['clientid'] == $notified_client_user['clientid'])
							{
								$message .= $anlage_asigned['eventTitle'] . "\n";
								$anlage_ipids[$notified_client_user['id']][] = $anlage_ipid_assigned;
							}
						}
					}
					else if($notified_client_user['notifications']['sixweeks'] == 'all')
					{
						//prepare message with all client patients
						foreach($anlagetoday as $anlage_ipid => $anlage)
						{
							if($anlage['clientid'] == $notified_client_user['clientid'])
							{
								$message .= $anlage['eventTitle'] . "\n";
								$anlage_ipids[$notified_client_user['id']][] = $anlage_ipid;
							}
						}
					}


					if(!empty($message) && !in_array($notified_client_user['id'], $has_today_anlage))
					{
						$message_header = "Für folgende Patienten muss heute (" . date("d.m.Y") . ") das Formular Anlage 4a WL ausgefüllt werden: \n";
						$insert_data[$notified_client_user['id']] = array();
						$insert_data[$notified_client_user['id']]['sender'] = '0';
						$insert_data[$notified_client_user['id']]['clientid'] = $notified_client_user['clientid'];
						$insert_data[$notified_client_user['id']]['recipient'] = $notified_client_user['id'];
						$insert_data[$notified_client_user['id']]['msg_date'] = date("Y-m-d H:i:s", time());
						$insert_data[$notified_client_user['id']]['title'] = Pms_CommonData::aesEncrypt(' Heutige Anlage 4a WL Formulare (' . date("d.m.Y") . ')');
						$insert_data[$notified_client_user['id']]['content'] = Pms_CommonData::aesEncrypt($message_header . $message);
						$insert_data[$notified_client_user['id']]['source'] = '4_weeks_system_message';
						$insert_data[$notified_client_user['id']]['create_date'] = date("Y-m-d", time());
						$insert_data[$notified_client_user['id']]['create_user'] = '0';
						$insert_data[$notified_client_user['id']]['read_msg'] = '0';

						$msg = new Messages();
						$msg->fromArray($insert_data[$notified_client_user['id']]);
						$msg->save();
 						
						// ####################################
						// ISPC-1600
						// ####################################
						$email_subject = $Tr->translate('youhavenewmailinispc'). ' ' . date('d.m.Y H:i');
						$email_text = "";
						$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
						// link to ISPC
						//$email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
						// ISPC-2475 @Lore 31.10.2019
						$email_text .= $Tr->translate('system_wide_email_text_login');
						
						// client details
						$client_details_string = "<br/>";
						$client_details_string  .= "<br/> ".$clients_data[$notified_client_user['clientid']]['team_name'];
						$client_details_string  .= "<br/> ".$clients_data[$notified_client_user['clientid']]['street1'];
						$client_details_string  .= "<br/> ".$clients_data[$notified_client_user['clientid']]['postcode']." ".$clients_data[$notified_client_user['clientid']]['city'];
						$client_details_string  .= "<br/> ".$clients_data[$notified_client_user['clientid']]['emailid'];
						$email_text .= $client_details_string;
						
						
						//TODO-3164 Ancuta 08.09.2020
						$email_data = array();
						$email_data['client_info'] = $client_details_string;
						$email_text = "";//overwrite
						$email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
						//-- 
						
						$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
						$mail = new Zend_Mail('UTF-8');
						$mail->setBodyHtml($email_text)
							->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
							->addTo($notified_client_user['emailid'], $notified_client_user['last_name'] . ' ' . $notified_client_user['first_name'])
							->setSubject($email_subject)
							->setIpids($anlage_ipids[$notified_client_user['id']])
							->send($mail_transport);
					}
				}
			}
			if($save)
			{
				//write in a file current timestamp
				$file_pointer = fopen('runfile_volversorgung.txt', 'w+');
				fwrite($file_pointer, strtotime(date('Y-m-d H:i:s')));
				fclose($file_pointer);
			}
		}

		public function dead_notification($ipid)
		{
			//PRE: load required models here
			$patients_linked = new PatientsLinked();

			//1. check if patient is shared with other clients
			$linked_patients = $patients_linked->get_related_patients($ipid);

			//make sure this array is never empty
			$linked_ipids[] = '9999999';

			//1.1 if shared make array with all linked ipids
			if($linked_patients)
			{
				$linked_ipids[] = $ipid;
				foreach($linked_patients as $k_link => $v_link)
				{
					$linked_ipids[] = $v_link['target'];
					$linked_ipids[] = $v_link['source'];
				}
			}
			else
			{
				//1.2 if not shared use curent ipid !!! UPDATE, sent message only if patient is shared
				//$linked_ipids[] = $ipid;
			}

			$linked_ipids = array_unique($linked_ipids);

			//2. get qpa mapping using ipids without any client id in filter
			$pmaster = Doctrine_Query::create()
				->select("p.ipid, e.ipid as ipid,e.epid as epidpatient, CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name, CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name")
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->andWhereIn('p.ipid', $linked_ipids);
			$pmaster_arr = $pmaster->fetchArray();

			$epids[] = '9999999999999';
			foreach($pmaster_arr as $v_pmaster)
			{
				$epids[] = $v_pmaster['epidpatient'];
				$patient_data[$v_pmaster['epidpatient']] = $v_pmaster;
			}

			$pqpa = Doctrine_Query::create()
				->select('*')
				->from("PatientQpaMapping")
				->whereIn('epid', $epids);

			$patients_users = $pqpa->fetchArray();

			//3.execute mesage insert to all users in array
			foreach($patients_users as $k_patuser => $v_patuser)
			{
				//print_r($patient_data[$v_patuser['epid']]); //patient data
				$message = "Ein Patient ist verstorben. \n Der Patient / die Patientin " . $patient_data[$v_patuser['epid']]['first_name'] . " " . $patient_data[$v_patuser['epid']]['first_name'] . " ist verstorben. \n Ein entsprechender Eintrag wurde in das System gemacht.";
				$insert_data[$v_patuser['id']] = array();
				$insert_data[$v_patuser['id']]['sender'] = '0';
				$insert_data[$v_patuser['id']]['clientid'] = $v_patuser['clientid'];
				$insert_data[$v_patuser['id']]['recipient'] = $v_patuser['id'];
				$insert_data[$v_patuser['id']]['msg_date'] = date("Y-m-d H:i:s", time());
				$insert_data[$v_patuser['id']]['title'] = Pms_CommonData::aesEncrypt('Ein Patient ist verstorben');
				$insert_data[$v_patuser['id']]['content'] = Pms_CommonData::aesEncrypt($message);
				$insert_data[$v_patuser['id']]['create_date'] = date("Y-m-d", time());
				$insert_data[$v_patuser['id']]['create_user'] = '0';
				$insert_data[$v_patuser['id']]['read_msg'] = '0';

				$msg = new Messages();
				$msg->fromArray($insert_data[$v_patuser['id']]);
				$msg->save();
			}
		}


		/**
		 * @author Loredana
		 * 13.08.2019
		 * ISPC-1547
		 * @param unknown $ipid
		 * @param boolean $userid
		 * @param string $inout
		 * @param string $action
		 */
		public function change_location_notification($ipid, $userid = false, $inout="", $action ="" )
		{
		    $Tr = new Zend_View_Helper_Translate();
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $decid = Pms_CommonData::getIdfromIpid($ipid);
		    
		    $user = new User();
		    $patientmaster = new PatientMaster();
		    
		    $allpatientinfo = $patientmaster->getMasterData($decid, 0);
		    $users = $user->getClientsUsers($clientid);
		    
		    $users_assigned = array();
		    $users_all = array();
		    
		    $notif_set = "patient_hospital_admission";
		    if ($inout == 'hospital_discharge'){
		        $notif_set = "patient_hospital_discharge";
		    }
		    
		    
		    foreach($users as $k_user => $v_users)
		    {
		        if($v_users['notifications'][$notif_set] == 'assigned' )
		        {
		            $users_assigned[] = $v_users['id'];
		        }
		        else if($v_users['notifications'][$notif_set] == 'all')
		        {
		            $users_all[] = $v_users['id'];
		        }
		    }
		    
		    if(!empty($users_assigned)){
		        
		        $fdoc = Doctrine_Query::create()
		        ->select("*, e.epid, e.ipid")
		        ->from('PatientQpaMapping q')
		        ->where('e.ipid LIKE ? ', $ipid)
		        ->andWhereIn("q.userid", $users_assigned)
		        ->andWhere('q.epid!=""');
		        $fdoc->leftJoin('q.EpidIpidMapping e');
		        $doc_assigned_patients = $fdoc->fetchArray();
		    }
		    
		    $users_to_notify = array();
		    foreach($doc_assigned_patients as $doc_patient)
		    {
		        $users_to_notify[] = $doc_patient['userid'];
		    }
		    
		    $users_to_notify = array_merge($users_all, $users_to_notify);
		    
		    //send message to the user who submits the form
		    if($userid && $users[$userid]['notifications'][$notif_set] != 'none')
		    {
		        $users_to_notify[] = $userid;
		    }
		    
		    $patient_name = "";
		    $patient_name = $allpatientinfo['last_name'] . " " . $allpatientinfo['first_name'];
		    
		    $msg = "";
/* 		    if ($inout == 'hospital_discharge'){
		        $msg = $Tr->translate('The patient %patient_name was discharged from the hospital.');
		    }else {
		        $msg = $Tr->translate('The patient %patient_name was admitted  in hospital.');
		    } */
		    //TODO-3695 Lore 25.01.2021
		    if ($inout == 'hospital_discharge'){
// 		        $msg = $Tr->translate('The patient <a href="patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($allpatientinfo['id']) . '"><b> %patient_name </b></a>, '.$allpatientinfo['EpidIpidMapping']['epid'].', was discharged from the hospital.');
		        //$msg = $Tr->translate('The patient %patient_name, '.$allpatientinfo['EpidIpidMapping']['epid'].', was discharged from the hospital.');

		        $msg = $Tr->translate('The patient %patient_name was discharged from the hospital.');//TODO-3835 Ancuta 08.02.2020
		    }else {
// 		        $msg = $Tr->translate('The patient <a href="patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($allpatientinfo['id']) . '"><b> %patient_name </b></a>, '.$allpatientinfo['EpidIpidMapping']['epid'].', was admitted  in hospital.');
		        //$msg = $Tr->translate('The patient %patient_name, '.$allpatientinfo['EpidIpidMapping']['epid'].', was admitted in hospital.');
		        $msg = $Tr->translate('The patient %patient_name was admitted  in hospital.');//TODO-3835 Ancuta 08.02.2020
		    }
		    
		    
		    //TODO-3835 Ancuta 08.02.2020 :: Correct translation 
		    //$message = str_replace('%patient_name',$patient_name, $msg);
		    $patient_name_link = '<a href="patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($allpatientinfo['id']) . '"><b> '.$patient_name.'</b></a>, '.$allpatientinfo['EpidIpidMapping']['epid'];
		    $message = str_replace('%patient_name',$patient_name_link, $msg);
		    // -- 
		    
		    // ############################
		    // ISPC-1600
		    // ############################
		    $email_subject = $Tr->translate('mail_subject_action_change_location_notification'). ' ' . date('d.m.Y H:i');
		    $email_text = "";
		    $email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
		    // link to ISPC
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
		    
		    $users_to_notify = array_unique($users_to_notify);
		    $insert_data = array();
		    foreach($users_to_notify as $k_user => $v_user)
		    {
		        if( ! empty($users[$v_user]['id']))
		        {
		            $insert_data[$k_user]['sender'] = '0';
		            $insert_data[$k_user]['clientid'] = $clientid;
		            $insert_data[$k_user]['recipient'] = $v_user;
		            $insert_data[$k_user]['msg_date'] = date("Y-m-d H:i:s", time());
		            $insert_data[$k_user]['title'] = Pms_CommonData::aesEncrypt(str_replace('%patient_name',$patient_name, $Tr->translate('The patient %patient_name has changed the location')));
		            //$insert_data[$k_user]['title'] = Pms_CommonData::aesEncrypt($Tr->translate('The patient has changed the location'));
		            $insert_data[$k_user]['content'] = Pms_CommonData::aesEncrypt($message);
		            $insert_data[$k_user]['source'] = $notif_set;
		            $insert_data[$k_user]['create_date'] = date("Y-m-d", time());
		            $insert_data[$k_user]['create_user'] = '0';
		            $insert_data[$k_user]['read_msg'] = '0';
		            $msg = new Messages();
		            $msg->fromArray($insert_data[$k_user]);
		            $msg->save();
		            
		            
		            if($msg->id > 0)
		            {
		                if(count($users[$v_user]) > 0 && !empty($users[$v_user]['emailid']))
		                {
		                    $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
		                    $mail = new Zend_Mail('UTF-8');
		                    $mail->setBodyHtml($email_text)
		                    ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
		                    ->addTo($users[$v_user]['emailid'], $users[$v_user]['last_name'] . ' ' . $users[$v_user]['first_name'])
		                    ->setSubject($email_subject)
		                    ->send($mail_transport);
		                }
		            }
		        }
		    }
		}
		
		
        /**
         *  @author Ancuta
         *  14.12.2019
         *  ISPC-2417
         * @param unknown $only_client
         */

 
		public function todo_reminder_notification( $only_client = null)
		{
		    $Tr = new Zend_View_Helper_Translate();

		    $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/todo_reminder.log');
		    $log = new Zend_Log($writer);
		    if($log)
		    {
		        $log->crit("======================TODO reminder [START] ===============================");
		    }
		    
		    // get all clients that have module "TODO reminder - 195 activated
		    $modules_array = array('195');
		    
		    $todo_reminder_clients = Modules::clients2modules($modules_array);
		    
		    if ( ! is_null($only_client)) {
		        
		        if (in_array($only_client, $todo_reminder_clients)) {
		            
		            $todo_reminder_clients =  array($only_client);
		            
		        } else {
		            
		            if($log)
		            {
		                $log->crit("TODO reminder this client does NOT have reminder module.. how did you managed to do this ?");
		            }
		            
		            return; //this client does NOT have TODO reminder module.. how did you managed to do this ?
		        }
		    }
		    
		    if (empty($todo_reminder_clients)) {
		        if($log)
		        {
		            $log->crit("TODO reminder:: no client has TODO reminder(195) module");
		        }
		        
		        return;// no client has reminder module
		    }
		    
		    //get all client_details
		    $clients_res = Doctrine_Query::create()
		    ->select("
	            id,
	            days_after_todo,
	            AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
	            "
		        )
		        ->from('Client indexBy id')
		        ->whereIn('id ', $todo_reminder_clients )
		        ->andWhere('isdelete = 0')
		        ->andWhere("days_after_todo IS NOT NULL")
		        ->andWhere("days_after_todo > '0' ")
		        ->fetchArray()
		        ;
		        
		        if (empty($clients_res)) {
		            if($log)
		            {
		                $log->crit("RUN TODO reminder:: - days_after_todo is not set in any client ");
		            }
		            return;// exit function, as the date  for reminders is not set
		        }
		        
		        $clients_arr = array();
		        $clients_arr = array_unique(array_column($clients_res, 'id'));
		        
		        
		        //get users/groups details
		        $clients_users_arr = array();
		        $clients_users_arr = Doctrine_Query::create()
		        ->select("id,clientid,groupid,first_name,last_name,emailid,isdelete,isactive")
		        ->from("User")
		        ->where("isdelete=0")
		        ->andWhere("isactive=0")
		        ->andWhereIn("clientid", $clients_arr)
		        ->fetchArray();
		        
		        $client_user_info = array();
		        $client_grup_users = array();
		        foreach($clients_users_arr as  $v_users)
		        {
		            $client_user_info[$v_users['clientid']][$v_users['id']] =$v_users;
		            $client_grup_users[$v_users['clientid']] [$v_users['groupid']] [] = $v_users['id'];
		        }
		        
		        foreach ($clients_res as $clientid => $client_details)
		        {
		            $start_todorem =  microtime(true);
		            
		            if($log)
		            {
		                $log->crit(" -------------- START for client: {$clientid} -----------");
		                $log->crit("RUN TODO reminder:: - auto todo reminder start for client {$clientid} ");
		            }

		            // get sent reminders for client
		            $client_sent_reminders = Doctrine_Query::create()
		            ->select('*')
		            ->from('TodosReminderLog')
		            ->where("client_id =?",$clientid)
		            ->fetchArray();
		            
		            $sent_Client_Todo2Users = array();
		            $sent_client_reminder_todos = array();
		            $sent_tosos_ids = array(0);
		            foreach($client_sent_reminders as $k => $dstr){
		                $sent_Client_Todo2Users[$dstr['client_id']][$dstr['todo_id']][] = $dstr['user_id'];
		                $sent_client_reminder_todos[$dstr['client_id']][] = $dstr['todo_id'].'_'.$dstr['user_id'].'_'.$dstr['ipid'];
		                $sent_tosos_ids[] = $dstr['todo_id'];
		            }
		            
		            if( empty($sent_tosos_ids)){
		                $sent_tosos_ids = array("X");
		            }
		            
		            
		            // get all UNcompleted todos 
		            $all_uncompleted_todos = array();
		            $all_uncompleted_todos = Doctrine_Query::create()
		            ->select("id,ipid,client_id,user_id,group_id,todo,until_date,iscompleted,isdelete,create_date")
		            ->from("ToDos")
		            ->where("iscompleted = 0" )
		            ->andwhere("isdelete = 0" )
		            ->andWhere("client_id = ?", $clientid)
		            ->andWhereNotIn('id', $sent_tosos_ids)
		            ->andWhere("until_date < ?", date("Y-m-d"))
		            ->orderBy('create_date ASC')
		            ->fetchArray();
		            
		            
		            $uncompleted_todos_ipids = array();
		            foreach($all_uncompleted_todos as $kt => $tv){
		                $uncompleted_todos_ipids[] = $tv['ipid'];
		            }
		            $uncompleted_todos_ipids = array_unique($uncompleted_todos_ipids);
		            
		            $client_todo_ipid = array();
		            $patient_information = array();
		            if(!empty($uncompleted_todos_ipids)){
		                
    		            $patient_details = array();
    		            $patient_details_q = Doctrine_Query::create()
    		            ->select("p.id, p.ipid,p.isdelete, AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name")
    		            ->from("PatientMaster as p")
    		            ->andWhere('p.isdelete = ?',0)
    		            ->andWhere('p.isstandbydelete = ?',0)
    		            ->andWhereIn("p.ipid", $uncompleted_todos_ipids);
    		            $patient_details_q->leftJoin('p.EpidIpidMapping e');
    		            $patient_details_q->andWhere('e.clientid = ?', $clientid);
    		            $patient_details = $patient_details_q->fetchArray();
    		            
    		            
    		            foreach($patient_details as   $patient_data){
    		                $patient_information[$patient_data['ipid']]['patient_name'] = $patient_data['first_name'].' '.$patient_data['last_name'];
    		                $patient_information[$patient_data['ipid']]['patient_link'] =  '<a href="'.APP_BASE.'patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($patient_data['id']) . '">' . $patient_information[$patient_data['ipid']]['patient_name'] . "</a>";
    		                $client_todo_ipid[] = $patient_data['ipid'];
    		            }
    		            
		            }
		            
		            
		            
	
		            if (empty($patient_information)) {
		                if($log)
		                {
		                    $log->crit("RUN TODO reminder:: - no patiets INFORMATION for this client  {$clientid} ");
		                }
		                
		                
		                continue; // jump to next client, this has no patients for todo reminders
		            }
		            
		            
		            if (empty($client_todo_ipid)) {
		                if($log)
		                {
		                    $log->crit("RUN TODO reminder:: - no patiets with uncompleted todos for this client  {$clientid} ");
		                }
		                
		                continue; // jump to next client, no patiets with uncompleted todos for this client
		            }
		            
		            
		            // get uncompleted todos
		            $uncompleted_todos = array();
		            $uncompleted_todos = Doctrine_Query::create()
		            ->select("id,ipid,client_id,user_id,group_id,todo,until_date,iscompleted,isdelete,create_date")
		            ->from("ToDos")
		            ->where("iscompleted = 0" )
		            ->andwhere("isdelete = 0" )
		            ->andWhere("client_id = ?", $clientid)
		            ->andWhereIn('ipid', $client_todo_ipid)
    		        ->andWhereNotIn('id', $sent_tosos_ids)
		            ->andWhere("until_date < ?", date("Y-m-d"))
		            ->orderBy('create_date ASC')
		            ->limit(100)
		            ->fetchArray();
		            
		
		            if (empty($uncompleted_todos)) {
		                if($log)
		                {
		                    $log->crit("RUN TODO reminder:: - NO uncompleted todos for this client  {$clientid} ");
		                }
		                continue;//jump to next client, this has no uncompleted todo
		            }

		            
		            //#########################################
		            //#########################################
		            //#########################################
		            // PREPARE AND SEND EMAILS

		            $todos2send = array();
		            $todos2send[$clientid] = array();

		            foreach($uncompleted_todos as $unc_todo_values){
		                if($unc_todo_values['client_id'] == $clientid && isset($patient_information[$unc_todo_values['ipid']])){
    		                
    	                    $date1 = new DateTime($unc_todo_values['until_date']);
    	                    $date2 = new DateTime(date("Y-m-d H:i:s"));
    	                    $day_dif =  date_diff($date1, $date2)->days;
    	                    
    	                    if ( $day_dif >= $client_details['days_after_todo']){
    	                        if (!empty($unc_todo_values['group_id']))
    	                        {// if todo is for group - split todo for users 
    	                            if(!empty($client_grup_users[ $clientid ] [ $unc_todo_values['group_id'] ]))
    	                            {
    	                                foreach($client_grup_users[ $clientid ] [ $unc_todo_values['group_id'] ] as $group_user){
    	                                    $todos2send[$clientid][] = array(
    	                                        'id' => $unc_todo_values['id'],
    	                                        'ipid' => $unc_todo_values['ipid'],
    	                                        'patient_name' => $patient_information[$unc_todo_values['ipid']]['patient_name'],
    	                                        'patient_link' => $patient_information[$unc_todo_values['ipid']]['patient_link'],
    	                                        'client_id' => $unc_todo_values['client_id'],
    	                                        'user_id' => $unc_todo_values['user_id'],
    	                                        'group_id' =>  $unc_todo_values['group_id'],
    	                                        'todo' =>  $unc_todo_values['todo'],
    	                                        'until_date' => $unc_todo_values['until_date'],
    	                                        'user_2_send' => $group_user
    	                                    );
    	                                }
    	                                
    	                            }
    	                            
    	                        } 
    	                        elseif(!empty($unc_todo_values['user_id'])) 
    	                        {
    	                            $todos2send[$clientid][] = array(
    	                                'id' => $unc_todo_values['id'],
    	                                'ipid' => $unc_todo_values['ipid'],
    	                                'patient_name' => $patient_information[$unc_todo_values['ipid']]['patient_name'],
    	                                'patient_link' => $patient_information[$unc_todo_values['ipid']]['patient_link'],
    	                                'client_id' => $unc_todo_values['client_id'],
    	                                'user_id' => $unc_todo_values['user_id'],
    	                                'group_id' =>  $unc_todo_values['group_id'],
    	                                'todo' =>  $unc_todo_values['todo'],
    	                                'until_date' => $unc_todo_values['until_date'],
    	                                'user_2_send' => $unc_todo_values['user_id']
    	                            );
    	                        }
    	                    }  
    		            }
		            }
		            
		            if (empty($todos2send[$clientid])) {
		                if($log)
		                {
		                    $log->crit("RUN TODO reminder:: - no todos to proccess for this client  {$clientid}");
		                }
		                
		                
		                continue; // jump to next client, this has no todo emails to send
		            }
		            
		            // proccess todos to send emails 
		            $todo_identification= "" ;
		            $todo_emails = array();
		            $todo_mesages = array();
		            $send_todos_ids = array();
		            $current_todos_ids = array();
		            
		            
		            foreach($todos2send[$clientid] as $todo){
		                
		                // first check if todo reminder for curent was sent 
		                $todo_identification = $todo['id'].'_'.$todo['user_2_send'].'_'.$todo['ipid'];
		                
		                //TODO-2914 Ancuta 12.02.2020
		                // extra check here
		                $sent_todo_check_arr = array();
		                $sent_todo_check_arr = Doctrine_Query::create()
		                ->select('*')
		                ->from('TodosReminderLog')
		                ->where("client_id = ? ",$clientid)
		                ->andWhere("todo_id = ? ",$todo['id'])
		                ->andWhere("user_id = ? ",$todo['user_2_send'])
		                ->andWhere("ipid = ? ",$todo['ipid'])
		                ->fetchArray();
		                
		                //TODO-2914 Ancuta 12.02.2020 - extra check if todo was sent
		                if(!empty($sent_client_reminder_todos[$clientid]) && in_array($todo_identification, $sent_client_reminder_todos[$clientid])  || !empty($sent_todo_check_arr) ){
		                    if($log)
		                    {
		                        //TODO-2914 Ancuta 12.02.2020 - allow to write in log for sent todos
		                        $log->crit("RUN TODO reminder:: this todo {$todo['id']} was sent for user {$todo['user_2_send']}");
		                    }
		                    $send_todos_ids[] = $todo_identification;
		                    continue; // jump to next todo, this todo was sent
		                }
		                $current_todos_ids[] = $todo_identification;
		                
		                // prepare and send emails + messages
		                $message = "";
		                $msg = "";
		                $msg = $Tr->translate('Das TODO für den Patienten %patient_name wurde noch nicht bearbeitet. Bitte bearbeiten Sie es zeitnah.');
		                $message = str_replace('%patient_name',$patient_information[$todo['ipid']]['patient_link'] , $msg);
		                
		                $title = "";
		                $title = 'ISPC / theraCase - Ihr TODO aus dem System ist überfällig';
		       
		                $email_subject = $Tr->translate('ISPC / theraCase - Ihr TODO aus dem System ist überfällig '). ' ' . date('d.m.Y H:i');
		                $email_text = "";
		                $email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
		                $email_text .= $Tr->translate('system_wide_email_text_login');

		                //TODO-3164 Ancuta 08.09.2020
		                $email_data = array();
		                $email_text = "";//overwrite
		                $email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
		                //-- 
		                
		                if(!empty($message)){
		                    
		                    //SEND MESSAGE
		                    $msg = new Messages();
		                    $msg->sender = 0; // system message
		                    $msg->clientid = $todo['client_id'];
		                    $msg->recipient = $todo['user_2_send'];
		                    $msg->ipid = $todo['ipid'];
		                    $msg->msg_date = date("Y-m-d H:i:s", time());
		                    $msg->title = Pms_CommonData::aesEncrypt($title);
		                    $msg->content = Pms_CommonData::aesEncrypt($message);
		                    $msg->source = 'todo_reminder_ISPC-2417';
		                    $msg->create_date = date("Y-m-d", time());
		                    $msg->create_user = 0;
		                    $msg->read_msg = '0';
		                    $msg->save();
		                    
		                    if($msg->id > 0)
		                    {
		                        $todo_mesages[] = $msg->id;
		                        
		                        //SEND EMAIL
		                        $user2_send_data = array();
		                        $user2_send_data = $client_user_info[$todo['client_id']][$todo['user_2_send']];
		                        
		                        if(!empty($todo['user_2_send']) && !empty($user2_send_data['emailid'])){
            		                $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
            		                $mail = new Zend_Mail('UTF-8');
            		                $mail->setBodyHtml($email_text)
            		                ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
            		                ->addTo($user2_send_data['emailid'], $user2_send_data['last_name'] . ' ' . $user2_send_data['first_name'])
            		                ->setSubject($email_subject)
            		                ->send($mail_transport);
            		                $todo_emails[] =$email_text;
		                        }
    		                
        		                // save to log 
        		                $remlog = new TodosReminderLog();
        		                $remlog->todo_id   = $todo['id'];
        		                $remlog->user_id   = $todo['user_2_send'];
        		                $remlog->client_id = $todo['client_id'];
        		                $remlog->ipid      = $todo['ipid'];
        		                $remlog->save();
		                    }
    		                
		                }
		            }
		            
		            /*
		            echo "<pre>";
		            print_r($current_todos_ids);
		            print_r($send_todos_ids);
		            
		            echo ("RUN TODO reminder:: - auto todo reminder function  was executed for client {$clientid} ({$end_todorem} seconds ) ,  Emails sent = " . count($todo_emails) );
		            echo "<br>";
		            */
		            $end_todorem = round( microtime(true) - $start_todorem, 0 );
		            if($log)
		            {
		                $log->crit("RUN TODO reminder:: - auto todo reminder function  was executed for client {$clientid} ({$end_todorem} seconds) ,  Emails sent: " . count($todo_emails).", Mesages sent : ".count($todo_mesages) );
		                $log->crit(" --------------- END for client: {$clientid} -------------------");
		            }
		            
		        }
		        
	            if($log)
	            {
	                $log->crit("======================TODO reminder [END] =================================");
	            }
		    
		}
		
		/**
		 * @author Loredana
		 * 29.08.2019
		 * ISPC-2417
		 * Demstepcare_upload - 10.09.2019 Ancuta
		 * $funct = new Messages();
		 * $apelfunc = $funct->todo_reminder_notification();
		 */
		
		public function todo_reminder_notification_Loredana()
		{
		    return;
		        $Tr = new Zend_View_Helper_Translate();
	        
		        //$wl_clients = $modules->checkClientsModulePrivileges('195', $clients_ids, $clients_data);
		         
		        $clients = Doctrine_Query::create()
		        ->select("*")
		        ->from("ClientModules")
		        ->andWhere("moduleid = 195 ")
				->andWhere("canaccess = 1");
		        $clsarray = $clients->fetchArray();
		        
		        $clientsarray = array();
		        
		        if (empty($clsarray)){
		            return;     
		        }else{
		            foreach($clsarray as $v_cl => $cl_arr ){
		                $clientsarray[] = $cl_arr['clientid'];
		            }   
		        }
		        
		        $daysclient = Doctrine_Query::create()
		        ->select("id,days_after_todo")
				->from("Client")
				->where("days_after_todo > 0" )
				->andWhereIn("id", $clientsarray);
				$daysclient->getSqlQuery();
				$daysarray = $daysclient->fetchArray();
				
				$days=array();
				foreach($daysarray as $k_days => $v_days)
				{
				    $days[$v_days['id']] = $v_days['days_after_todo'];   
				}
				if (empty($daysarray)){
				    return;
				}
				
				$client_grup_users = array();
				$users=array();
				$usrs = Doctrine_Query::create()
				->select("*")
				->from("User indexby id")
				->where("isdelete=0")
				->andWhereIn("clientid", $clientsarray);
				$users = $usrs->fetchArray();
				foreach($users as $k_user => $v_users)
				{
				    $client_grup_users[$v_users['clientid']] [$v_users['groupid']] [] = $v_users['id'];  
				}
				
				
				$todosclient = Doctrine_Query::create()
				->select("id,ipid,client_id,user_id,group_id,todo,until_date")
				->from("ToDos")
				->where("iscompleted = 0" )
				->andwhere("isdelete = 0" )
				->andWhereIn("client_id", $clientsarray);
				$todosarray = $todosclient->fetchArray();
				if (empty($todosarray)){
				    return;
				}
		        $ipids=array();
				foreach ($todosarray as $k_ipid => $v_ipid){
				    $ipids[]=$v_ipid['ipid'];
				}
                $ipids = array_unique($ipids);
                             
                $patinfo = Doctrine_Query::create()
                ->select("id, ipid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name")
                ->from("PatientMaster")
                ->WhereIn("ipid", $ipids);
                $allpatientinfo = $patinfo->fetchArray();
                
                $patinfoarray = array();
                foreach($allpatientinfo as $k_ipid => $v_ipid){
                    $patinfoarray[$v_ipid['ipid']] =$v_ipid;
                }
   
                
				$due_date = 0;
				$users_to_notify = array();
				$todo_reminder = array();
				$todos_ids = array();
				
				foreach($todosarray as $k_todo => $v_todo){
				    
				    if (isset($days[$v_todo['client_id']]) && ($days[$v_todo['client_id']]>0)){
				        
				        $date1 = new DateTime($v_todo['until_date']);
				        $date2 = new DateTime(date("Y-m-d H:i:s"));
				        $day_dif =  date_diff($date1, $date2)->days;
				        
				        if ( $day_dif >= $days[$v_todo['client_id']]){
				            if (!empty($v_todo['group_id'])){
				                $users_to_notify[$v_todo['id']] = $client_grup_users[$v_todo['client_id']] [$v_todo['group_id']]   ;
				            }
				            if (!empty($v_todo['user_id'])){
				                $users_to_notify[$v_todo['id']] = array($v_todo['user_id']);
				            }
				            $todo_reminder[$v_todo['id']]['patient'] = $patinfoarray[$v_todo['ipid']];
				            $todo_reminder[$v_todo['id']]['users'] =  $users_to_notify[$v_todo['id']];
				            $todo_reminder[$v_todo['id']]['clientid'] =  $v_todo['client_id'];
				            $todo_reminder[$v_todo['id']]['todoid'] =  $v_todo['id'];
				            $todos_ids[]= $v_todo['id'];
				            
				        }
				    }    

				}
				
				if (empty($todo_reminder)){
				    return;
				}
				
				
				$allready_remind = Doctrine_Query::create()
				->select('*')
				->from('TodosReminderLog')
				->whereIn("todo_id",$todos_ids);
				$allready_arr = $allready_remind->fetchArray();
				foreach($allready_arr as $k => $dstr){
				    $Sent[$dstr['client_id']][$dstr['todo_id']][] = $dstr['user_id'];
				}
				
				
				$users_to_send = array();
				foreach($todo_reminder as $k_todorem => $v_todorem){
				    
			        $patient_name = "";
			        $patient_name = $v_todorem['patient']['first_name'] . ", " . $v_todorem['patient']['last_name'];
				        
			        $msg = "";
			        $msg = $Tr->translate('Das TODO für den Patienten %patient_name wurde noch nicht bearbeitet. Bitte bearbeiten Sie es zeitnah.');
			        $patient_link = '<a href="'.APP_BASE.'patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($v_todorem['patient']['id']) . '">' . $patient_name . "</a>";
				        
			        $message = str_replace('%patient_name',$patient_link, $msg);
				        
			        $title = 'ISPC / theraCase - Ihr TODO aus dem System ist überfällig';
			        // ############################
			        // ISPC-1600
			        // ############################
			        $email_subject = $Tr->translate('ISPC / theraCase - Ihr TODO aus dem System ist überfällig '). ' ' . date('d.m.Y H:i');
			        $email_text = "";
			        $email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
			        // link to ISPC
			        //$email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
			        // ISPC-2475 @Lore 31.10.2019
			        $email_text .= $Tr->translate('system_wide_email_text_login');
			        
			        $users_to_send = array_unique($v_todorem['users']);
			        $insert_data = array();
			        
			        
			        //TODO-3164 Ancuta 08.09.2020
			        $email_data = array();
			        $email_text = "";//overwrite
			        $email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
			        //-- 
			        foreach($users_to_send as $k_user => $v_user)
			        {
			            //dd($v_user,$Sent,$Sent[$v_todorem['clientid']][$v_todorem['todoid']]);
			            if(!in_Array($v_user,$Sent[$v_todorem['clientid']][$v_todorem['todoid']])){
			               
			                if( ! empty($users[$v_user]['id'])){
			                    $insert_data[$k_user]['sender'] = '0';
			                    $insert_data[$k_user]['clientid'] = $v_todorem['clientid'];
			                    $insert_data[$k_user]['recipient'] = $v_user;
			                    $insert_data[$k_user]['msg_date'] = date("Y-m-d H:i:s", time());
			                    $insert_data[$k_user]['title'] = Pms_CommonData::aesEncrypt($title);
			                    $insert_data[$k_user]['content'] = Pms_CommonData::aesEncrypt($message);
			                    $insert_data[$k_user]['source'] = 'todo_reminder_ISPC-2417';
			                    $insert_data[$k_user]['create_date'] = date("Y-m-d", time());
			                    $insert_data[$k_user]['create_user'] = '0';
			                    $insert_data[$k_user]['read_msg'] = '0';
			                    
			                    $msg = new Messages();
			                    $msg->fromArray($insert_data[$k_user]);
			                    $msg->save();
			                    
			                    
			                    if($msg->id > 0)
			                    {
			                        if(count($users[$v_user]) > 0 && !empty($users[$v_user]['emailid']))
			                        {
			                            $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
			                            $mail = new Zend_Mail('UTF-8');
			                            $mail->setBodyHtml($email_text)
			                            ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
			                            ->addTo($users[$v_user]['emailid'], $users[$v_user]['last_name'] . ' ' . $users[$v_user]['first_name'])
			                            ->setSubject($email_subject)
			                            ->send($mail_transport);
			                        }
			                    }
			                   
			                    $remlog = new TodosReminderLog();
			                    $remlog->todo_id   = $v_todorem['todoid'];
			                    $remlog->user_id   = $v_user;
			                    $remlog->client_id = $v_todorem['clientid'];
			                    $remlog->ipid      = $v_todorem['patient']['ipid'];
			                    $remlog->save();
			                    
			                }
                        }
			        }				    
			    }    
		}
		
		public function krise_notification($pid, $ipid)
		{
			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$decid = Pms_Uuid::decrypt($pid);

			$patientmaster = new PatientMaster();
			$allpatientinfo = $patientmaster->getMasterData($decid, 0);
			$patname = $allpatientinfo['last_name'] . ', ' . $allpatientinfo['first_name'];

			$user = new User();
			$users = $user->getClientsUsers($clientid);

			$users_assigned[] = '99999999999';
			$users_all[] = '99999999999';

			foreach($users as $k_user => $v_users)
			{
				if($v_users['notifications']['krise'] == 'assigned')
				{
					$users_assigned[] = $v_users['id'];
				}
				else if($v_users['notifications']['krise'] == 'all')
				{
					$users_all[] = $v_users['id'];
				}
			}

			$fdoc = Doctrine_Query::create()
				->select("*, e.epid, e.ipid")
				->from('PatientQpaMapping q')
				->where('e.ipid LIKE "' . $ipid . '"')
				->andWhereIn("q.userid", $users_assigned)
				->andWhere('q.epid!=""');
			$fdoc->leftJoin('q.EpidIpidMapping e');

			$doc_assigned_patients = $fdoc->fetchArray();

			$users_to_notify[] = '999999999999';
			foreach($doc_assigned_patients as $doc_patient)
			{
				$users_to_notify[] = $doc_patient['userid'];
			}

			$users_to_notify = array_merge($users_all, $users_to_notify);


			$message_entry = "Der Status des  " . $patname . "  wurde auf Krise gesetzt";
			$users_to_notify = array_unique($users_to_notify);
			
			
			
			
			// ###################################
			// ISPC-1600
			// ###################################
			$email_subject = $Tr->translate('mail_subject_action_krise_notification'). ' ' . date('d.m.Y H:i');
			$email_text = "";
			$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
			// link to ISPC
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
			
			foreach($users_to_notify as $k_user => $v_user)
			{
				if($v_user != '999999999999' && !empty($users[$v_user]['id']))
				{
					$msg = new Messages();
					$msg->sender = 0; // system message
					$msg->clientid = $logininfo->clientid;
					$msg->recipient = $users[$v_user]['id'];
					$msg->msg_date = date("Y-m-d H:i:s", time());
					$msg->title = Pms_CommonData::aesEncrypt('Krise: ' . $patname . ' (' . date('d.m.Y') . ') ');
					$msg->content = Pms_CommonData::aesEncrypt(utf8_encode($message_entry));
					$msg->source = 'krise';
					$msg->create_date = date("Y-m-d", time());
					$msg->create_user = $logininfo->userid;
					$msg->read_msg = '0';
					$msg->save();

					if($msg->id > 0)
					{
						if(count($users[$v_user]) > 0 && !empty($users[$v_user]['emailid']))
						{
							$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
							$mail = new Zend_Mail('UTF-8');
							$mail->setBodyHtml($email_text)
								->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
								->addTo($users[$v_user]['emailid'], $users[$v_user]['last_name'] . ' ' . $users[$v_user]['first_name'])
								->setSubject($email_subject)
								->send($mail_transport);
						}
					}
				}
			}
		}

		public function wlvollversorgung_notification($pid, $ipid)
		{
			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$decid = Pms_Uuid::decrypt($pid);

			$patientmaster = new PatientMaster();
			$allpatientinfo = $patientmaster->getMasterData($decid, 0);
			$patname = $allpatientinfo['last_name'] . ', ' . $allpatientinfo['first_name'];

			$user = new User();
			$users = $user->getClientsUsers($clientid);

			$users_assigned[] = '99999999999';
			$users_all[] = '99999999999';

			foreach($users as $k_user => $v_users)
			{
				if($v_users['notifications']['wlvollversorgung'] == 'assigned')
				{
					$users_assigned[] = $v_users['id'];
				}
				else if($v_users['notifications']['wlvollversorgung'] == 'all')
				{
					$users_all[] = $v_users['id'];
				}
			}

			$fdoc = Doctrine_Query::create()
				->select("*, e.epid, e.ipid")
				->from('PatientQpaMapping q')
				->where('e.ipid LIKE "' . $ipid . '"')
				->andWhereIn("q.userid", $users_assigned)
				->andWhere('q.epid!=""');
			$fdoc->leftJoin('q.EpidIpidMapping e');
			$doc_assigned_patients = $fdoc->fetchArray();

			$users_to_notify[] = '999999999999';
			foreach($doc_assigned_patients as $doc_patient)
			{
				$users_to_notify[] = $doc_patient['userid'];
			}

			$users_to_notify = array_merge($users_all, $users_to_notify);


			$message_entry = "Vollversorgung\n";
			$message_entry .= 'Für  den/die Patient/in ' . $patname . ' wurde die Vollversorgung begonnen.';

			$users_to_notify = array_unique($users_to_notify);
			
			
            // #########################
			// ISPC-1600
            // #########################
			$email_subject = $Tr->translate('mail_subject_action_wl_volversorgung_notification'). ' ' . date('d.m.Y H:i');
			$email_text = "";
			$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
			// link to ISPC
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
			
			foreach($users_to_notify as $k_user => $v_user)
			{

				if($v_user != '999999999999' && !empty($users[$v_user]['id']))
				{
					$msg = new Messages();
					$msg->sender = 0; // system message
					$msg->clientid = $logininfo->clientid;
					$msg->recipient = $users[$v_user]['id'];
					$msg->msg_date = date("Y-m-d H:i:s", time());
					$msg->title = Pms_CommonData::aesEncrypt('Vollversorgung');
					$msg->content = Pms_CommonData::aesEncrypt($message_entry);
					$msg->source = 'vollversorgung';
					$msg->create_date = date("Y-m-d", time());
					$msg->create_user = $logininfo->userid;
					$msg->read_msg = '0';
					$msg->save();

					if($msg->id > 0)
					{
						if(count($users[$v_user]) > 0 && !empty($users[$v_user]['emailid']))
						{
							$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
							$mail = new Zend_Mail('UTF-8');
							$mail->setBodyHtml($email_text)
								->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
								->addTo($users[$v_user]['emailid'], $users[$v_user]['last_name'] . ' ' . $users[$v_user]['first_name'])
								->setSubject($email_subject)
								->send($mail_transport);
						}
					}
				}
			}
		}

		public function discharge_notification($pid, $ipid, $discharge_method, $discharge_date = false, $status = false)
		{
			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$decid = Pms_Uuid::decrypt($pid);

			$dm = new DischargeMethod();
			$user = new User();
			$patientmaster = new PatientMaster();

			$allpatientinfo = $patientmaster->getMasterData($decid, 0);

			$users = $user->getClientsUsers($clientid);


			$users_assigned[] = '999999999999';
			$users_all[] = '999999999999';

			foreach($users as $k_user => $v_users)
			{
				if($v_users['notifications']['discharge'] == 'assigned')
				{
					$users_assigned[] = $v_users['id'];
				}
				else if($v_users['notifications']['discharge'] == 'all')
				{
					$users_all[] = $v_users['id'];
				}
			}

			$fdoc = Doctrine_Query::create()
				->select("*, e.epid, e.ipid")
				->from('PatientQpaMapping q')
				->where('e.ipid LIKE "' . $ipid . '"')
				->andWhereIn("q.userid", $users_assigned)
				->andWhere('q.epid!=""');
			$fdoc->leftJoin('q.EpidIpidMapping e');
			$doc_assigned_patients = $fdoc->fetchArray();

			$users_to_notify[] = '999999999999';
			foreach($doc_assigned_patients as $doc_patient)
			{
				$users_to_notify[] = $doc_patient['userid'];
			}

			$users_to_notify = array_merge($users_all, $users_to_notify);
			$dm_array = $dm->getDischargeMethodById($discharge_method);

			if($status == 'edit_entry')
			{
				$message = "Für Patienten " . $allpatientinfo['last_name'] . ", " . $allpatientinfo['first_name'] . "  die Entlassung wurde geändert .\n";
			}
			else
			{
				$message = "Der Patient " . $allpatientinfo['last_name'] . ", " . $allpatientinfo['first_name'] . "  wurde entlassen.\n";
			}

			$message .="Entlassungsmethode: " . $dm_array[0]['description'] . ".\n";

			if($discharge_date)
			{
				$dis_date = date('d.m.Y H:i', strtotime($discharge_date));
				$message .="Entlassungsdatum: " . $dis_date . ".\n";
			}
			
			
			// #######################
			// ISPC-1600
			// #######################
			$email_subject = $Tr->translate('mail_subject_action_discharge_notification'). ' ' . date('d.m.Y H:i');
			$email_text = "";
			$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
			// link to ISPC
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
			
			$users_to_notify = array_unique($users_to_notify);
			foreach($users_to_notify as $k_user => $v_user)
			{
				if($v_user != '999999999999' && !empty($users[$v_user]['id']))
				{
					$insert_data[$k_user]['sender'] = '0';
					$insert_data[$k_user]['clientid'] = $clientid;
					$insert_data[$k_user]['recipient'] = $v_user;
					$insert_data[$k_user]['msg_date'] = date("Y-m-d H:i:s", time());
					$insert_data[$k_user]['title'] = Pms_CommonData::aesEncrypt(' Patient entlassen ');
					$insert_data[$k_user]['content'] = Pms_CommonData::aesEncrypt($message);
					$insert_data[$k_user]['source'] = 'discharge_patient';
					$insert_data[$k_user]['create_date'] = date("Y-m-d", time());
					$insert_data[$k_user]['create_user'] = '0';
					$insert_data[$k_user]['read_msg'] = '0';

					$msg = new Messages();
					$msg->fromArray($insert_data[$k_user]);
					$msg->save();
					
					if($msg->id > 0)
					{
						if(count($users[$v_user]) > 0 && !empty($users[$v_user]['emailid']))
						{
							$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
							$mail = new Zend_Mail('UTF-8');
							$mail->setBodyHtml($email_text)
								->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
								->addTo($users[$v_user]['emailid'], $users[$v_user]['last_name'] . ' ' . $users[$v_user]['first_name'])
								->setSubject($email_subject)
								->send($mail_transport);
						}
					}
				}
			}
		}

		public function admission_notification($ipid, $userid = false)
		{
			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$decid = Pms_CommonData::getIdfromIpid($ipid);

			$dm = new DischargeMethod();
			$user = new User();
			$patientmaster = new PatientMaster();

			$allpatientinfo = $patientmaster->getMasterData($decid, 0);

			$users = $user->getClientsUsers($clientid);

			$users_assigned[] = '999999999999';
			$users_all[] = '999999999999';
			foreach($users as $k_user => $v_users)
			{
				if($v_users['notifications']['admission'] == 'assigned')
				{
					$users_assigned[] = $v_users['id'];
				}
				else if($v_users['notifications']['admission'] == 'all')
				{
					$users_all[] = $v_users['id'];
				}
			}

			$fdoc = Doctrine_Query::create()
				->select("*, e.epid, e.ipid")
				->from('PatientQpaMapping q')
				->where('e.ipid LIKE "' . $ipid . '"')
				->andWhereIn("q.userid", $users_assigned)
				->andWhere('q.epid!=""');
			$fdoc->leftJoin('q.EpidIpidMapping e');
			$doc_assigned_patients = $fdoc->fetchArray();

			$users_to_notify[] = '999999999999';
			foreach($doc_assigned_patients as $doc_patient)
			{
				$users_to_notify[] = $doc_patient['userid'];
			}

			$users_to_notify = array_merge($users_all, $users_to_notify);

			//send message to the user who submits the form
			if($userid && $users[$userid]['notifications']['admission'] != 'none')
			{
				$users_to_notify[] = $userid;
			}

			$message = 'Ihnen wurde ein neuer Patient im ISPC zugewiesen.<br />' . $allpatientinfo['last_name'] . ", " . $allpatientinfo['first_name'] . '';

			
			
			// ############################
			// ISPC-1600
			// ############################
			$email_subject = $Tr->translate('mail_subject_action_admission_notification'). ' ' . date('d.m.Y H:i');
			$email_text = "";
			$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
			// link to ISPC
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
			
			$users_to_notify = array_unique($users_to_notify);
			foreach($users_to_notify as $k_user => $v_user)
			{
				if($v_user != '999999999999' && !empty($users[$v_user]['id']))
				{
					$insert_data[$k_user]['sender'] = '0';
					$insert_data[$k_user]['clientid'] = $clientid;
					$insert_data[$k_user]['recipient'] = $v_user;
					$insert_data[$k_user]['msg_date'] = date("Y-m-d H:i:s", time());
					$insert_data[$k_user]['title'] = Pms_CommonData::aesEncrypt(' Ihnen wurde ein neuer Patient zugewiesen ');
					$insert_data[$k_user]['content'] = Pms_CommonData::aesEncrypt($message);
					$insert_data[$k_user]['source'] = 'admission_patient';
					$insert_data[$k_user]['create_date'] = date("Y-m-d", time());
					$insert_data[$k_user]['create_user'] = '0';
					$insert_data[$k_user]['read_msg'] = '0';

					$msg = new Messages();
					$msg->fromArray($insert_data[$k_user]);
					$msg->save();
 
					if($msg->id > 0)
					{
						if(count($users[$v_user]) > 0 && !empty($users[$v_user]['emailid']))
						{
							$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
							$mail = new Zend_Mail('UTF-8');
							$mail->setBodyHtml($email_text)
								->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
								->addTo($users[$v_user]['emailid'], $users[$v_user]['last_name'] . ' ' . $users[$v_user]['first_name'])
								->setSubject($email_subject)
								->send($mail_transport);
						}
					}
				}
			}
		}

		public function anlage25days()
		{
			set_time_limit(0);
			//check the script last run
			$file_data = file_get_contents('runfile25daysvv.txt');
			//run the messaging script
			$run = false;
			//save or not the run file
			$save = false;

			if($file_data)
			{
				if(strtotime(date('Y-m-d')) > strtotime(date('Y-m-d', $file_data)))
				{
					$run = true;
					$save = true;
				}
				else
				{
					$run = false;
					$save = false;
				}
			}
			else
			{
				//file not found or not created yet
				$run = true;
				$save = true;
			}

//			if(($save === true && $run === true) || $_REQUEST['forced'])
    
			if($_REQUEST['forced']) //ISPC-916 WL Anlage 4 - this was stopped   
			{
				//Load required models
				$client = new Client();
				$modules = new Modules();
				$user = new User();
				$Tr = new Zend_View_Helper_Translate();

				//1. get all clients
				$clients = $client->getClientData();

				foreach($clients as $v_client)
				{
					$clients_data[$v_client['id']] = $v_client;
					$clients_ids[] = $v_client['id'];
				}

				//1.1 get only clients that have vv module active(REVERTED)
//				$frm = new ExtraForms();
//				$vollversorgung_ids = $frm->getClientsPersmission($clients_ids, '36');
//				
//				if(empty($vollversorgung_ids))
//				{
//					$vollversorgung_ids[] = '999999999999';
//				}
				//2. get users of clients
				$users = $user->getClientsUsers($clients_ids);

				$users_ids[] = '9999999999999';
				$users_to_notify[] = '999999999999';

				foreach($users as $k_user => $v_users)
				{
					if($v_users['notifications']['wlvollversorgung_25days'] != 'none' && !empty($v_users['notifications']))
					{
						$users_ids[] = $v_users['id'];
						$users_to_notify[] = $v_users;
						$users_details[$v_users['id']] = $v_users;
					}
				}

				$sql = "AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
				$sql .=",AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
				$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";

				// if super admin check if patient is visible or not
				if($logininfo->usertype == 'SA' && $clone === false)
				{
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				}

				//2.1 get assigned patients for following users id
				$fdoc = Doctrine_Query::create()
					->select("*, e.epid, e.ipid," . $sql)
					->from('PatientQpaMapping q')
					->whereIn("q.userid", $users_ids)
					->andWhere('q.epid!=""')
					->leftJoin('q.EpidIpidMapping e')
					->where('q.epid = e.epid')
					->leftJoin('e.PatientMaster p')
					->where('e.ipid = p.ipid')
					->andWhere('p.isdelete = "0"')
					->andWhere('p.isstandby = "0"')
					->andWhere('p.isstandbydelete = "0"');
				$doc_assigned_patients = $fdoc->fetchArray();

				foreach($doc_assigned_patients as $doc_patient)
				{
					$users_patients[$doc_patient['userid']][] = $doc_patient['EpidIpidMapping']['ipid'];
					$users_patients[$doc_patient['userid']] = array_values(array_unique($users_patients[$doc_patient['userid']]));

					$all_patients[] = $doc_patient['EpidIpidMapping']['ipid'];
					$all_patient_details[$doc_patient['EpidIpidMapping']['ipid']] = $doc_patient;
				}

				$all_patients = array_values(array_unique($all_patients));

//				3. WL Vollversorgung 25 days calculation
				$vv_history = new VollversorgungHistory();
				$vv_patients = $vv_history->get_vollversorgung_period($all_patients, '25', $all_patient_details);

				//4. check if users got the message earlier
				$vv_patients[] = '9999999999999999';
				$users_having_mesages = self::no_25days_vv_notifications($users_ids, $vv_patients);

				foreach($users_to_notify as $k_notif => $notified_client_user)
				{
					if($notified_client_user != '999999999999')
					{
						//check if user settings allow to sent all to receive message only from assigned patients
						if($notified_client_user['notifications']['wlvollversorgung_25days'] == 'assigned')
						{
							foreach($users_patients[$notified_client_user['id']] as $k_pats => $v_ipid)
							{
								if(in_array($v_ipid, $vv_patients) && !in_array($v_ipid, $users_having_mesages[$notified_client_user['id']]))
								{
									//ipids for assigned users to get messages (users dont have message for those ipids)
									$ipids_user_mail[$notified_client_user['id']][] = $v_ipid;
								}
							}
						}
					}
				}

//				print_r($ipids_user_mail);
//				exit;
//
				foreach($ipids_user_mail as $k_user => $v_ipids)
				{
					foreach($v_ipids as $k_ipid => $v_ipid)
					{
					    

					    // ############################
        				// ISPC-1600
					    // ############################
        				$email_subject = $Tr->translate('mail_subject_action_wl_vollversorgung25days_notification'). ' ' . date('d.m.Y H:i');
        				$email_text = "";
					    $email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
					    // link to ISPC
					    //$email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
					    // ISPC-2475 @Lore 31.10.2019
					    $email_text .= $Tr->translate('system_wide_email_text_login');
					    
					    // client details
					    $client_details_string = "<br/>";
					    $client_details_string  .= "<br/> ".$clients_data[$users_details[$k_user]['clientid']]['team_name'];
					    $client_details_string  .= "<br/> ".$clients_data[$users_details[$k_user]['clientid']]['street1'];
					    $client_details_string  .= "<br/> ".$clients_data[$users_details[$k_user]['clientid']]['postcode']." ".$clients_data[$users_details[$k_user]['clientid']]['city'];
					    $client_details_string  .= "<br/> ".$clients_data[$users_details[$k_user]['clientid']]['emailid'];
					    $email_text .= $client_details_string;
					    
					    //TODO-3164 Ancuta 08.09.2020 // ISSUE? 
					    $email_data = array();
					    $email_data['client_info'] = $client_details_string;
					    $email_text = "";//overwrite
					    $email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
					    //-- 
					    
						$insert_data[$v_ipid]['clientid'] = $users_details[$k_user]['clientid'];
						$insert_data[$v_ipid]['sender'] = '0';
						$insert_data[$v_ipid]['recipient'] = $k_user;
						$insert_data[$v_ipid]['folderid'] = '';
						$insert_data[$v_ipid]['msg_date'] = date("Y-m-d H:i:s", time());
						$insert_data[$v_ipid]['title'] = Pms_CommonData::aesEncrypt('WL Anlage 4a für Patient ' . $all_patient_details[$v_ipid]['last_name'] . ',  ' . $all_patient_details[$v_ipid]['first_name'] . '');

						$message = "Der Patient " . $all_patient_details[$v_ipid]['first_name'] . " " . $all_patient_details[$v_ipid]['last_name'] . " hat mittlerweile mindestens den 25. Tag der Vollversorgung erreicht. Bitte denken Sie nach 28 Tagen an das Versenden der Anlage 4a an die KV.";
						$insert_data[$v_ipid]['content'] = Pms_CommonData::aesEncrypt($message);

						$insert_data[$v_ipid]['read_msg'] = '0';
						$insert_data[$v_ipid]['del_msg'] = '0';
						$insert_data[$v_ipid]['replied_msg'] = '0';
						$insert_data[$v_ipid]['source'] = '25_daysvv_system_message';
						$insert_data[$v_ipid]['ipid'] = $v_ipid;
						$insert_data[$v_ipid]['recipients'] = '';
						$insert_data[$v_ipid]['create_date'] = date("Y-m-d", time());
						$insert_data[$v_ipid]['create_user'] = '0';
						$message = '';

						$msg = new Messages();
						$msg->fromArray($insert_data[$v_ipid]);
						$msg->save();
					}

					$insert_data = array_values($insert_data);

					
					$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
					$mail = new Zend_Mail('UTF-8');
					$mail->setBodyHtml($email_text)
						->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
						->addTo($users_details[$k_user]['emailid'], $user_details[$k_user]['last_name'] . ' ' . $users_details[$k_user]['first_name'])
						->setSubject($email_subject)
						->setIpids($ipids_user_mail[$k_user])
						->send($mail_transport);
				}
			}
			if($save)
			{
				//write in a file current timestamp
				$file_pointer = fopen('runfile25daysvv.txt', 'w+');
				fwrite($file_pointer, strtotime(date('Y-m-d H:i:s')));
				fclose($file_pointer);
			}
		}

		// send coordinators todo on 27th day
		// regarding the 29th day visit(checked in SH Anlage14 sh_folgeko)
		public function send_coordinator_todos()
		{
			set_time_limit(0);
			//check the script last run
			$file_location = APPLICATION_PATH . '/../public/run/';
			$file_data = file_get_contents($file_location . 'runfile27daysadmission');

			//run the messaging script
			$run = false;
			//save or not the run file
			$save = false;

			if($file_data)
			{
				if(strtotime(date('Y-m-d')) > strtotime(date('Y-m-d', $file_data)))
				{
					$run = true;
					$save = true;
				}
				else
				{
					$run = false;
					$save = false;
				}
			}
			else
			{
				//file not found or not created yet
				$run = true;
				$save = true;
			}

			if(($save === true && $run === true) || $_REQUEST['forced_sh'])
			{
				//get clients which have module (SH) active
				$modules = new Modules();
				$valid_client_ids = $modules->clients2modules(array("82"));

				if($valid_client_ids)
				{
					$used_patients_data = self::get_pats_24_days_admission($valid_client_ids);
					$save_todos = self::save_sh_todos($used_patients_data);
				}
			}

			if($save)
			{
				//write in a file current timestamp
				$file_pointer = fopen($file_location . 'runfile27daysadmission', 'w+');
				fwrite($file_pointer, time());
				fclose($file_pointer);
			}
		}

		public function save_sh_todos($details)
		{
			$Tr = new Zend_View_Helper_Translate();
			$coord_groups = array("6");
			$coord_groups_ids = Usergroup::get_clients_mastergroup_users($coord_groups, $details['valid_clients']);

			
			$coordinations_users_str = "";
			foreach($coord_groups_ids as $k_coord => $v_coord)
			{
				$coord_group_ids[$v_coord['clientid']][] = $v_coord['id'];
				$coordinations_users[] = 'u'.$v_coord['id'];
			}
			$coordinations_users_str = implode(";",$coordinations_users);

			$existing_todos = $this->get_existing_sh_todos($details['ipids']);

			$todo_data = array();
			foreach($details['ipids'] as $k_ipid => $v_ipid)
			{
				$allowed_users[$v_ipid] = array_diff($coord_group_ids[$details[$v_ipid]['client']], $existing_todos['users'][$v_ipid]);

				foreach($coord_group_ids[$details[$v_ipid]['client']] as $k_usr => $v_usr)
				{
					if(!in_array($v_ipid, $existing_todos['ipids']) || (in_array($v_ipid, $existing_todos['ipids']) && in_array($v_usr, $allowed_users[$v_ipid])))
					{
						$patient_name = Pms_CommonData::aesDecrypt($details[$v_ipid]['pat_data']['details']['last_name']) . ' ' . Pms_CommonData::aesDecrypt($details[$v_ipid]['pat_data']['details']['first_name']);
						$todo_date = $details[$v_ipid]['todo_date'];

						$todo_message = $Tr->translate('shortcut_todo_text_sh_folgeko');

						$todo_message = str_replace("%patient_name", $patient_name, $todo_message);
						$todo_message = str_replace("%shortcut_date", $todo_date, $todo_message);

						$todo_identifier = 'sh_folgeko';

						$todo_data[] = array(
							'client_id' => $details[$v_ipid]['client'],
							'user_id' => $v_usr,
							'group_id' => '0',
							'ipid' => $v_ipid,
							'todo' => $todo_message,
							'triggered_by' => $todo_identifier,
							'isdelete' => '0',
							'iscompleted' => '0',
							'patient_step_identification' => '0',
							'create_date' => date('Y-m-d H:i:s', time()),
							'until_date' => date('Y-m-d H:i:s', strtotime($todo_date)),
							'additional_info' => $coordinations_users_str
						);
					}
				}
			}

			if($todo_data)
			{
				$collection = new Doctrine_Collection('ToDos');
				$collection->fromArray($todo_data);
				$collection->save();

				return $todo_data;
			}
		}

		private function get_existing_sh_todos($ipids)
		{
			$ipids[] = '999999999999999';
			$todos = Doctrine_Query::create()
				->select("*")
				->from("ToDos")
				->whereIn("ipid", $ipids)
				->andWhere("triggered_by = 'sh_folgeko'")
				->andWhere("isdelete = '0'");
			$todos_res = $todos->fetchArray();

			if($todos_res)
			{
				foreach($todos_res as $k_res => $v_res)
				{
					$found_todos['ipids'][] = $v_res['ipid'];
					$found_todos['users'][$v_res['ipid']][] = $v_res['user_id'];
				}

				return $found_todos;
			}
		}

		//get patients wich have 24 (Client Days Settings related) days from first admission, today.
		public function get_pats_24_days_admission($clients = false)
		{

			set_time_limit(0);
			if($clients)
			{
				$sql = "e.ipid ,e.clientid, e.epid, p.ipid, p.isdischarged, p.isarchived, p.isstandby, p.isstandbydelete, p.isdelete";
				$patients = Doctrine_Query::create()
					->select($sql)
					->from('PatientMaster p')
					->where("p.ipid = e.ipid")
					->leftJoin("p.EpidIpidMapping e")
					->andWhereIn('e.clientid', $clients)
					->andWhere('p.isdischarged = "0"')
					->andWhere('p.isdelete = 0')
					->andWhere('p.isstandbydelete = 0');
				$patients_res = $patients->fetchArray();
				
				//if we have patients
				if($patients_res)
				{
					//construct ipids array
					foreach($patients_res as $k_pat => $v_pat)
					{
						$patient_ipids[$v_pat['EpidIpidMapping']['clientid']][] = $v_pat['EpidIpidMapping']['ipid'];
						$ipid2client[$v_pat['EpidIpidMapping']['ipid']] = $v_pat['EpidIpidMapping']['clientid'];
					}

					$patient_days = array();

					foreach($clients as $k_cl => $v_cl)
					{
					    
					    //ISPC-2478 Ancuta 27.10.2020
				        $fisrt_Sapv_trigger_flatrate = false;
					    $modules = new Modules();
					    if($modules->checkModulePrivileges("246", $v_cl)) 
					    {
					        $fisrt_Sapv_trigger_flatrate = true;
					    }
					    //--
					    
					    
						$conditions['periods'][0]['start'] = '2009-01-01';
						$conditions['periods'][0]['end'] = date('Y-m-d');
						$conditions['client'] = $v_cl;
						$conditions['ipids'] = $patient_ipids[$v_cl];

						$patient_days_res = Pms_CommonData::patients_days($conditions);

						if(empty($patient_days_res))
						{
							$patient_days_res = array();
						}

						$patient_days = array_merge($patient_days, $patient_days_res);

						$sapv_array[$v_cl] = SapvVerordnung::get_all_sapvs($patient_ipids[$v_cl]);
						$patient_Erstsapv_days = array();
						foreach($sapv_array[$v_cl] as $k_sapv => $v_sapv)
						{
							$start = date('Y-m-d', strtotime($v_sapv['verordnungam']));

							if($v_sapv['status'] == '1' && $v_sapv['verordnungam'] != '0000-00-00 00:00:00')
							{
								$end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
							}
							else
							{
								$end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
							}

							if(empty($patient_sapv_days[$v_sapv['ipid']]))
							{
								$patient_sapv_days[$v_sapv['ipid']] = array();
							}

							$patient_sapv_days[$v_sapv['ipid']] = array_merge($patient_sapv_days[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end, false, 'd.m.Y'));
							
							
							//ISPC-2478 Ancuta 27.10.2020
							if($fisrt_Sapv_trigger_flatrate){
    							if($v_sapv['sapv_order'] == '1'){
    							    $patient_Erstsapv_days[$v_sapv['ipid']][$v_sapv['id']] =   array_slice(PatientMaster::getDaysInBetween($start, $end,false,"d.m.Y"), 0, 29); ;
    							}
							}
							// --
						}

						//reset for each loop (clients)
						$start = "";
						$end = "";
						$patient_days_res = array();
						$conditions = array();
					}

					foreach($patient_days as $k_ipid => $v_patient_details)
					{
						$today_date = date('d.m.Y', time());

						if($_REQUEST['dbgsh1'])
						{
							print_r($k_ipid);
							print_r("\n");
							var_dump($patient_sapv_days[$k_ipid] && in_array($today_date, $patient_sapv_days[$k_ipid]));
							print_r("\n\n");
						}

						//eliminate those bastards which are wandering in array with no scope in it
						if($patient_sapv_days[$k_ipid] && in_array($today_date, $patient_sapv_days[$k_ipid]) && count($v_patient_details['treatment_days']) > '0' && in_array($today_date, $v_patient_details['treatment_days'])
						)
						{
							$v_patient_details['treatment_days'] = array_intersect($v_patient_details['treatment_days'], $patient_sapv_days[$k_ipid]);
							$v_patient_details['treatment_days'] = array_values(array_unique($v_patient_details['treatment_days']));

							$min_patient_days[$k_ipid]['treatment_days_sapv'] = $v_patient_details['treatment_days'];
							$min_patient_days[$k_ipid]['treatment_days_sapv_no'] = count($v_patient_details['treatment_days']);
							
//							$min_patient_days[$k_ipid]['treatment_days_no'] = count($v_patient_details['treatment_days']);
							
							$patient_days[$k_ipid]['treatment_days_sapv'] = $v_patient_details['treatment_days'];
							$patient_days[$k_ipid]['treatment_days_sapv_no'] = count($v_patient_details['treatment_days']);
						}
					}
					if($_REQUEST['dbgsh2'])
					{
						print_r($min_patient_days);
						print_r($patient_days['a88874d8cef70801df13cfd43098696c309abd42']);
						exit;
					}
					//formated date
					$today = date('d.m.Y', time());
					$matched_patients = array();

					//setting up some required data
					$today_allowed_days_no = range('22', '29', '1');
					$today_allowed_keys = range('21', '28', '1');

					//check every patient for treatment days
					foreach($patient_days as $k_ipid => $pat_details)
					{
						//we get "26"(22nd) day which is 27th(21st) array element (array starts from key 0)
						//changed to get 25th day
						//cganged to get 22nd day (ISPC-1196 - send todo ONCE 7 days before 29th day)
						$today_pos[$k_ipid] = array_search($today, array_values(array_unique($pat_details['treatment_days_sapv'])));

						if(in_array($pat_details['treatment_days_sapv_no'], $today_allowed_days_no) && in_array($today_pos[$k_ipid], $today_allowed_keys))
						{
							//here we have patients that have at least 24(22) days of treatment and current day is 24th day
							//return those ipids
							$matched_patients_ipids[] = $k_ipid;
						}
						
						//ISPC-2478 Ancuta 27.10.2020
						if($fisrt_Sapv_trigger_flatrate){
    						foreach($patient_Erstsapv_days[$k_ipid] as $sapv_id => $sdata){
    						    $today_pos_sapv[$k_ipid] = array_search($today, array_values(array_unique($sdata)));
    				 
    						    if(in_array(count($sdata), $today_allowed_days_no) && in_array($today_pos_sapv[$k_ipid], $today_allowed_keys))
        						{
        							$matched_patients_ipids[] = $k_ipid;
        						}
    						    
    						}
						}
						//--
					}

					//calculate when the user should have the 29th day
					$lastday_constant = "29";
					foreach($min_patient_days as $k_min_ipid => $v_min_ipid)
					{
						$current_day_diff = ($lastday_constant - $v_min_ipid['treatment_days_sapv_no']);
// 						if($k_min_ipid == "18e671d128d6ed4441a619109f0c53d67bb4cc92")
// 						{
// 							print_r($current_day_diff);
// 							exit;
// 						}
						if($current_day_diff >= '0')
						{
							$range_start = strtotime(end($v_min_ipid['treatment_days_sapv']));
							$range_end = strtotime(date('d.m.Y', strtotime('+' . $current_day_diff . ' days', strtotime(end($v_min_ipid['treatment_days_sapv'])))));
							$day_incr = (60 * 60 * 24);

							$predicted_treatment_days_diff = range($range_start, $range_end, $day_incr);
							array_walk($predicted_treatment_days_diff, function(&$value) {
								$value = date('d.m.Y', $value);
							});

							$predicted_treatment_days[$k_min_ipid] = array();
							if(count($predicted_treatment_days_diff) > '0' && count($v_min_ipid['treatment_days_sapv']) > '0')
							{
								$predicted_treatment_days[$k_min_ipid] = array_values(array_unique(array_merge($v_min_ipid['treatment_days_sapv'], $predicted_treatment_days_diff)));
							}
						}
						
						//ISPC-2478 Ancuta 27.10.2020
						if($fisrt_Sapv_trigger_flatrate){
						    
    						foreach($patient_Erstsapv_days[$k_min_ipid] as $sapv_id => $sdata){
    
    						    $today_pos_sapv[$k_min_ipid] = array_search($today, array_values(array_unique($sdata)));
    						    
    						    $current_day_diff = ($lastday_constant - count($sdata));
    
    						    if($current_day_diff >= '0' && in_array($today_pos_sapv[$k_ipid], $today_allowed_keys))
        						{
        						    $range_start = strtotime(end($sdata));
        						    $range_end = strtotime(date('d.m.Y', strtotime('+' . $current_day_diff . ' days', strtotime(end($sdata)))));
        							$day_incr = (60 * 60 * 24);
        
        							$predicted_treatment_days_diff = range($range_start, $range_end, $day_incr);
        							array_walk($predicted_treatment_days_diff, function(&$value) {
        								$value = date('d.m.Y', $value);
        							});
        							$predicted_treatment_days[$k_min_ipid] = array();
        							if(count($predicted_treatment_days_diff) > '0' && count($sdata) > '0')
        							{
        							    $predicted_treatment_days[$k_min_ipid]  = array_values(array_unique(array_merge($sdata, $predicted_treatment_days_diff)));
        							}
        						}
    						}
                        }  
						//-- 
						
					}
					//recheck if we have results and return them
					if($matched_patients_ipids)
					{
						//return more usefull data so we wont need to re-query
						foreach($matched_patients_ipids as $k_ipid => $v_ipid)
						{
							$matched_patients_ipids_arr['ipids'] = $matched_patients_ipids;
							$matched_patients_ipids_arr['valid_clients'] = $clients;
							$matched_patients_ipids_arr[$v_ipid]['client'] = $ipid2client[$v_ipid];
							$matched_patients_ipids_arr[$v_ipid]['pat_data'] = $patient_days[$v_ipid];
							$matched_patients_ipids_arr[$v_ipid]['predicted_treatment_days'] = $predicted_treatment_days;
							$matched_patients_ipids_arr[$v_ipid]['todo_date'] = end($predicted_treatment_days[$v_ipid]);
							$matched_patients_ipids_arr[$v_ipid]['today_treatment_pos'] = $today_pos[$v_ipid];
							$matched_patients_ipids_arr[$v_ipid]['treatment_days_no'] = $patient_days[$v_ipid]['treatment_days_no'];
							$matched_patients_ipids_arr[$v_ipid]['treatment_days_sapv_no'] = $patient_days[$v_ipid]['treatment_days_sapv_no'];
							$matched_patients_ipids_arr[$v_ipid]['treatment_days'] = $patient_days[$v_ipid]['treatment_days'];
							$matched_patients_ipids_arr[$v_ipid]['treatment_days_sapv'] = $patient_days[$v_ipid]['treatment_days_sapv'];
						}
						
						
						if($_REQUEST['dbgsh'])
						{
							print_r($matched_patients_ipids_arr);
							exit;
						}
						return $matched_patients_ipids_arr;
					}
					else
					{
						return false;
					}
				}
				else
				{
					//nothing to do here either
					return false;
				}
			}
			else
			{
				//nothing to do
				return false;
			}
		}
		/* public function patientbirthday_notification()
		{
			set_time_limit(0);
			//check the script last run
			$file_data = file_get_contents('runfilepathbirth.txt');
			//run the messaging script
			$run = false;
			//save or not the run file
			$save = false;
			
			if($file_data)
			{
				if(strtotime(date('Y-m-d')) > strtotime(date('Y-m-d', $file_data)))
				{
					$run = true;
					$save = true;
				}
				else
				{
					$run = false;
					$save = false;
				}
			}
			else
			{
				//file not found or not created yet
				$run = true;
				$save = true;
			}
		
			//-------------------
			if(($save === true && $run === true) || $_REQUEST['forced'])
			{
			
				//Load required models
				$client = new Client();
				$modules = new Modules();
				$user = new User();
				$Tr = new Zend_View_Helper_Translate();
				
				//1. get all clients 
				$clients = $client->getClientData();
			
				foreach($clients as $v_client)
				{
					$clients_data[$v_client['id']] = $v_client;
					$clients_ids[] = $v_client['id'];
				}
				
				//2. get users of clients
				$users = $user->getClientsUsers($clients_ids);
				
				$users_ids[] = '9999999999999';
				$users_to_notify[] = '999999999999';
				
				$users_clientid[]='99999999999';
				foreach($users as $k_user => $v_users)
				{
					if($v_users['notifications']['dashboard_display_patbirthday'] != 'none' && !empty($v_users['notifications']))
					{
						$users_ids[] = $v_users['id'];
						$users_to_notify[] = $v_users;
						$users_details[$v_users['id']] = $v_users;
						$users_clientid[] = $v_users['clientid'];
					}
				}
				
				//2.1 get assigned patients for following users id
				
				
				$fdoc = Doctrine_Query::create()
					->select("*,q.userid, e.epid, e.ipid")
					->from('EpidIpidMapping e')
					->andWhere('e.epid!=""')
					->leftJoin('e.PatientQpaMapping q')
					->where('e.epid = q.epid')
					->andWhereIn("q.userid", $users_ids);
				$doc_assigned_patients = $fdoc->fetchArray();
						
				$asigned_patients[]='999999999999';
				foreach($doc_assigned_patients as $doc_patient)
				{
					foreach($doc_patient['PatientQpaMapping'] as $k_doc => $v_doc)
					{
						$users_patients[$v_doc['userid']][] = $doc_patient['ipid'];
						
						$asigned_patients[] = $doc_patient['ipid'];
						
					}
					
				}

				$sql = "AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
				$sql .=",AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
				$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
				
				
				// if super admin check if patient is visible or not
				if($logininfo->usertype == 'SA' && $clone === false)
				{
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
					$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				}
				
				
				//3. Get patients with birthday today
				//3.1 Patients asigned to users wich must receive the messajes
				$patient_dasboard = Doctrine_Query::create()
				->select("p.ipid,p.birthd,".$sql)
						->from('PatientMaster as p')
						->where('isdelete = 0')
						->andWhereIn('ipid',$asigned_patients)
						->andWhere('isdischarged = 0')
						->andWhere('isstandby = 0')
						->andWhere('isarchived = 0')
						->andWhere('isstandbydelete = 0')
						->andWhere('MONTH(p.birthd) = MONTH(now()) and DAY(p.birthd) = DAY(now()');
				$patient_dashboard_assigned = $patient_dasboard->fetchArray();
					
				$patients_assigned_ipid[]='999999999999';
				if($patient_dashboard_assigned)
				{
					foreach($patient_dashboard_assigned as $k_pat => $v_pat)
					{
						$patients_birthdays[$v_pat['ipid']] = date('d.m.Y', strtotime($v_pat['birthd']));
						$patients_assigned_ipid[] = $v_pat['ipid'];
						$patients_delails[$v_pat['ipid']]=$v_pat;
					}
				}

				//3.2 Patients from clients wich have users with setting 'dashboard_display_patbirthday'='all'
				$patient_dasboard = Doctrine_Query::create()
				->select("p.ipid,p.birthd,ep.clientid,".$sql)
				->from('PatientMaster as p')
				->where('p.isdelete = 0')
				->andWhere('p.isdischarged = 0')
				->andWhere('p.isstandby = 0')
				->andWhere('p.isarchived = 0')
				->andWhere('p.isstandbydelete = 0')
				->andWhere('MONTH(p.birthd) = MONTH(now()) and DAY(p.birthd) = DAY(now()')
				->leftJoin('p.EpidIpidMapping ep')
				->andWhereIn('ep.clientid',$users_clientid)
				->andWhere('ep.ipid=p.ipid');
				$patient_dashboard_all = $patient_dasboard->fetchArray();
		
				$patients_clients_ipids[] ='999999999999';

			if($patient_dashboard_all)
			{ 
				foreach($patient_dashboard_all as $key => $client)
				{					
					$client_ids_all[]= $client['EpidIpidMapping']['clientid'];
					$client_ids_all = array_values(array_unique($client_ids_all));
					$patients_all_clients[$client['EpidIpidMapping']['clientid']][] = $client['ipid'];
					$patients_clients_ipids[] = $client['ipid'];
					$patients_clients_details[$client['ipid']] = $client;
				}	
			}

				//4. check if users got the message earlier
								
				$usersass_having_mesages = self::no_patbirth_notifications($users_ids, $patients_assigned_ipid);
				$usersall_having_mesages = self::no_patbirth_notifications($users_ids, $patients_clients_ipids);

				//5. Identify users and ipids for send mail 
				foreach($users_to_notify as $k_notif => $notified_client_user)
				{
					if($notified_client_user != '999999999999')
					{
						//check if user settings allow to sent all to receive message only from assigned patients
						if($notified_client_user['notifications']['dashboard_display_patbirthday'] == 'assigned')
						{
							foreach($users_patients[$notified_client_user['id']] as $k_pats => $v_ipid)
							{
								if(in_array($v_ipid, $patients_assigned_ipid)  &&  !in_array($v_ipid, $usersass_having_mesages[$notified_client_user['id']]) )
								{
									//ipids for assigned users to get messages (users dont have message for those ipids)
									$ipids_user_mail[$notified_client_user['id']][] = $v_ipid;
								}
							}
						}
						else if($notified_client_user['notifications']['dashboard_display_patbirthday'] == 'all' )
						{		
							foreach($patients_all_clients[$notified_client_user['clientid']] as  $v_ipid)
							{ 								
								if(in_array($notified_client_user['clientid'],$client_ids_all)  && !in_array($v_ipid, $usersall_having_mesages[$notified_client_user['id']]))
								{ 
									$ipids_user_mail[$notified_client_user['id']][] = $v_ipid;
									
								}	
							}	
									
						}
					}
					
				}
		
				foreach($ipids_user_mail as $k_user => $v_ipids)
				{
					foreach($v_ipids as $k_ipid => $v_ipid)
					{	
						if($users_details[$k_user]['notifications']['dashboard_display_patbirthday'] = 'all')
						{
							$message = 'Geburtstag des Patienten : ' . $patients_clients_details[$v_ipid]['last_name'] . '  ' . $patients_clients_details[$v_ipid]['first_name'] ;
						}
						else if($users_details[$k_user]['notifications']['dashboard_display_patbirthday'] = 'assigned')
						{
							$message = 'Geburtstag des Patienten : ' . $patients_delails[$v_ipid]['last_name'] . '  ' . $patients_delails[$v_ipid]['first_name'] ;
						}
						$insert_data[$v_ipid]['clientid'] = $users_details[$k_user]['clientid'];
						$insert_data[$v_ipid]['sender'] = '0';
						$insert_data[$v_ipid]['recipient'] = $k_user;
						$insert_data[$v_ipid]['folderid'] = '';
						$insert_data[$v_ipid]['msg_date'] = date("Y-m-d H:i:s", time());
						$insert_data[$v_ipid]['title'] = Pms_CommonData::aesEncrypt('Geburtstag des Patienten ');
				
						//$message = 'Geburtstag des Patienten : ' . $patients_delails[$v_ipid]['last_name'] . '  ' . $patients_delails[$v_ipid]['first_name'] ;
						$insert_data[$v_ipid]['content'] = Pms_CommonData::aesEncrypt($message);
				
						$insert_data[$v_ipid]['read_msg'] = '0';
						$insert_data[$v_ipid]['del_msg'] = '0';
						$insert_data[$v_ipid]['replied_msg'] = '0';
						$insert_data[$v_ipid]['source'] = 'dashboard_display_patbirthday';
						$insert_data[$v_ipid]['ipid'] = $v_ipid;
						$insert_data[$v_ipid]['recipients'] = '';
						$insert_data[$v_ipid]['create_date'] = date("Y-m-d", time());
						$insert_data[$v_ipid]['create_user'] = '0';
						$message = '';
				
						$mail = new Messages();
						$mail->fromArray($insert_data[$v_ipid]);
						$mail->save();
					}
				
					$insert_data = array_values($insert_data);
				
					$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
					$mail = new Zend_Mail('UTF-8');
					$mail->setBodyText($Tr->translate('youhavenewmailinyourispcinbox'))
					->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
					->addTo($users_details[$k_user]['emailid'], $user_details[$k_user]['last_name'] . ' ' . $users_details[$k_user]['first_name'])
					->setSubject($Tr->translate('youhavenewmailinispc') . ', ' . date('d.m.Y H:i'))
					->setIpids($ipids_user_mail[$k_user])
					->send($mail_transport);
				}
				
				if($save)
				{
					//write in a file current timestamp
					$file_pointer = fopen('runfilepathbirth.txt', 'w+');
					fwrite($file_pointer, strtotime(date('Y-m-d H:i:s')));
					fclose($file_pointer);
				}
			}
		}
 */
		
		public function organisation(){

		    $modules = new Modules();
		    $modules_array = array('72');
		    $organisation_clients = $modules->clients2modules($modules_array);
		    
		    
		    if(!empty($organisation_clients))
		    {
		        $file_location = APPLICATION_PATH . '/../public/run/';
		        $lock_filename = 'org_path.lockfile';
		        $lock_file = false;
		    
		        //check lock file
		        if(file_exists($file_location . $lock_filename))
		        {
		            //lockfile exists
		            $lock_file = true;
		        }
		        else
		        {
		            //no lock file exists, create it
		            $handle = fclose(fopen($file_location . $lock_filename, 'x'));
		            $lock_file = false;
		        }
		        //skip organisation path todos only if lockfile exists
		        if(!$lock_file)
		        {
		            $client_id_arr[] = '9999999999';
		            $organisation_clients[] = '99999999999';
		            foreach($organisation_clients as $client_id)
		            {
		                $client_id_str .= '"' . $client_id . '",';
		                $client_id_arr[] = $client_id;
		            }
		    
		            $users_groups = new Usergroup();
		            $ClientGroups = $users_groups->get_clients_groups($organisation_clients);
		    
		            foreach($ClientGroups as $kh => $gr_details)
		            {
		                $grup_details[$gr_details['clientid']][$gr_details['groupmaster']][] = $gr_details['id'];
		            }
		    
		            $sqlh = "ipid,isdischarged,e.clientid,e.epid,p.isstandby,p.isstandbydelete,";
		            $sqlh .= "AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
		            $sqlh .= "AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
		    
		            $org_patients_query = Doctrine_Query::create()
		            ->select($sqlh)
		            ->from('PatientMaster p')
		            ->where('isdelete=0');
// 		            ->andWhere('isstandby=0')
// 		            ->andWhere('isstandbydelete="0"');
		            $org_patients_query->leftJoin("p.EpidIpidMapping e");
		            $org_patients_query->andWhere('e.clientid  IN (' . substr($client_id_str, 0, -1) . ') ');
		            $org_patients_array = $org_patients_query->fetchArray();
		    
		            $all_ipids[] = '99999999999';
		            
		            foreach($org_patients_array as $k_pat_limit => $v_pat_limit)
		            {
		                $pat_name[$v_pat_limit['ipid']] = $v_pat_limit['last_name'] . ', ' . $v_pat_limit['first_name'];
		                $pat_epid[$v_pat_limit['ipid']] = $v_pat_limit['EpidIpidMapping']['epid'];
		    
		                $patient_details[$v_pat_limit['ipid']]['name'] = $v_pat_limit['last_name'] . ', ' . $v_pat_limit['first_name'];
		                $patient_details[$v_pat_limit['ipid']]['epid'] = $v_pat_limit['EpidIpidMapping']['epid'];
		                $patient_details[$v_pat_limit['ipid']]['clientid'] = $v_pat_limit['EpidIpidMapping']['clientid'];

		                
		                if($v_pat_limit['isstandby'] == "0" && $v_pat_limit['isstandbydelete'] == "0")
		                {
    		                $ipids2client[$v_pat_limit['EpidIpidMapping']['clientid']][] = $v_pat_limit['ipid'];
    		                $all_ipids[] = $v_pat_limit['ipid'];
		                }
		            }
		            
		            //get discharge dead patients
		            $distod = Doctrine_Query::create()
		            ->select("*")
		            ->from('DischargeMethod')
		            ->where("isdelete = 0")
		            ->andWhere("abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN' or abbr='TODENT' or abbr='Todent' or abbr='todent'")
		            ->andWhereIn('clientid', $organisation_clients);
		            $todarray = $distod->fetchArray();
		    
		            $todIds[] = "9999999999999";
		            foreach($todarray as $todmethod)
		            {
		                $todIds[] = $todmethod['id'];
		            }
		    
		            $dispat = Doctrine_Query::create()
		            ->select("*")
		            ->from("PatientDischarge")
		            ->whereIn('ipid', $all_ipids)
		            ->andWhere('isdelete = 0')
		            ->andWhereIn("discharge_method", $todIds);
		            $discharged_arr = $dispat->fetchArray();
		    
		            $discharged_patients_dead[] = '99999999999';
		            foreach($discharged_arr as $k_dis_pat => $v_dis_pat)
		            {
		                $discharged_patients_dead[] = $v_dis_pat['ipid'];
		            }
		    
		            if(count($pat_ipids) == '0')
		            {
		                $pat_ipids[] = '99999999999';
		            }
		    
		            $paths = new OrgPaths();
		            $clients_paths = $paths->get_clients_paths($organisation_clients);
		    
		            $steps = new OrgSteps();
		            $get_paths_steps = $steps->get_clients_paths_steps($client_id_str);
		    
		            $org_step_permissions = new OrgStepsPermissions();
		            $steps2group_permissions = $org_step_permissions->clients_steps2groups($organisation_clients);
		            foreach($steps2group_permissions as $client_k => $steps_per)
		            {
		                foreach($steps_per as $kstep => $vgroup_array)
		                {
		                    foreach($vgroup_array as $v_group_id)
		                    {
		                        if(empty($step2clientgroup_permission[$client_k][$kstep]))
		                        {
		                            $step2clientgroup_permission[$client_k][$kstep] = array();
		                        }
		                        if(!empty($grup_details[$client_k][$v_group_id]))
		                        {
		                            $step2clientgroup_permission[$client_k][$kstep] = array_merge($step2clientgroup_permission[$client_k][$kstep], $grup_details[$client_k][$v_group_id]);
		                        }
		                    }
		                }
		            }
		            	
		            foreach($get_paths_steps as $client_steps => $vstep_array)
		            {
		                foreach($vstep_array as $ks => $vstep)
		                {
		                    $steps_ids[$client_steps][] = $vstep['id'];
		                    $shortcut2stepid[$client_steps][$vstep['shortcut']] = $vstep['id'];
		                    $todo2stepid[$client_steps][$vstep['shortcut']]['todo_text'] = $vstep['todo_text'];
		                    $stepid2shortcut[$client_steps][$vstep['id']] = $vstep['shortcut'];
		                }
		            }
		    
		            foreach($clients_paths as $k_cl_path => $v_cl_path)
		            {
		                $all_clients_path_arr[$v_cl_path['client']][] = $v_cl_path['function'];
		            }
		    
		            if($_REQUEST['mode'] != 'old')
		            {
		                //new and fast way
		                $data = $paths->get_org_data_overview($all_ipids, $all_clients_path_arr);
		            }
		            else
		            {
		                //old and slow way
		                foreach($clients_paths as $k_c_path => $v_c_path)
		                {
		                    if(!in_array($v_c_path['function'], $executed_functions[$v_c_path['client']]))
		                    {
		    
		                        if(empty($data))
		                        {
		                            $data = array();
		                        }
		    
		                        $executed_functions[$v_c_path['client']][] = $v_c_path['function'];
		                        $retrived_data = $paths->{$v_c_path['function']}($ipids2client[$v_c_path['client']], $v_c_path['client']);
		    
		                        if($retrived_data)
		                        {
		                            $data = array_merge_recursive($data, $retrived_data);
		                        }
		                    }
		                }
		            }
		            // print_R($data); exit;
		            foreach($data as $k_ipid => $v_function_data)
		            {
		                if($k_ipid != '99999999999')
		                {
		                    foreach($v_function_data as $k_function => $v_function_arr)
		                    {
		                        foreach($v_function_arr as $k_short => $v_short_status)
		                        {
		                            if($v_short_status['status'] == "red")
		                            {
		                                $red_step2ipid4groups[$patient_details [$k_ipid] ['clientid']] [$k_ipid] [$k_short] = $step2clientgroup_permission [$patient_details [$k_ipid] ['clientid']] [$shortcut2stepid [$patient_details [$k_ipid] ['clientid']] [$k_short]];
		                                if($v_short_status['extra_info'] && !empty($v_short_status['extra_info']))
		                                {
		                                    $extra_info[$patient_details [$k_ipid] ['clientid']] [$k_ipid] [$k_short] = $v_short_status['extra_info'];
		                                }
		                                $step_identification[$patient_details [$k_ipid] ['clientid']] [$k_ipid] [$k_short] = $v_short_status['step_identification'];
		                            }
		                        }
		                    }
		                }
		            }
		            //2567bd95670c061ac1595af482334cd82da92a2c
		            // print_r($red_step2ipid4groups); exit;
		            $text = "";
		            foreach($red_step2ipid4groups as $clientk => $ipid_values)
		            {
		                foreach($ipid_values as $ipid_key => $sh_values)
		                {
		                    foreach($sh_values as $sh_key => $sh2group)
		                    {
		                        if(!empty($sh2group))
		                        {
		                            foreach($sh2group as $gk => $group_id)
		                            {
		                                //exclude dead patients which are not having E1 shortcut
		                                $exclude_patient = false;
		                                if(in_array($ipid_key, $discharged_patients_dead) && $sh_key != 'E1')
		                                {
		                                    $exclude_patient = true;
		                                }
		    
		                                if(!$exclude_patient)
		                                {
		                                    if($extra_info [$clientk] [$ipid_key] [$sh_key] && !empty($extra_info [$clientk] [$ipid_key] [$sh_key]))
		                                    {
		                                        $text = $patient_details[$ipid_key]['name'] . ' -  ' . $todo2stepid[$clientk][$sh_key]['todo_text'] . ' (' . $extra_info [$clientk] [$ipid_key] [$sh_key] . ')';
		                                    }
		                                    else
		                                    {
		                                        $text = $patient_details[$ipid_key]['name'] . ' -  ' . $todo2stepid[$clientk][$sh_key]['todo_text'];
		                                    }
		    
		    
		                                    $uk = $ipid_key . 'system_step_' . $shortcut2stepid[$clientk][$sh_key] . '_' . $sh_key . '' . $group_id;
		    
		                                    $records_todo[$uk] = array(
		                                        "client_id" => $clientk,
		                                        "user_id" => '0',
		                                        "group_id" => $group_id,
		                                        "ipid" => $ipid_key,
		                                        "todo" => $text,
		                                        "triggered_by" => 'system_step_' . $shortcut2stepid[$clientk][$sh_key] . '_' . $sh_key,
		                                        "patient_step_identification" => $step_identification [$clientk] [$ipid_key] [$sh_key],
		                                        "create_date" => date('Y-m-d H:i:s', time()),
		                                        "until_date" => date('Y-m-d H:i:s', time())
		                                    );
		    
		                                    $step_validation[$uk] = $step_identification [$clientk] [$ipid_key] [$sh_key];
		                                }
		                            }
		                        }
		                    }
		                }
		            }
		        }
		        // 				print_r($records_todo); exit;
		    
		        if(!empty($records_todo))
		        {
		            $record_keys = array_values(array_unique(array_keys($records_todo)));
		    
		            $sapv = Doctrine_Query::create()
		            ->select("CONCAT(ipid, triggered_by, group_id) as key_value, patient_step_identification")
		            ->from('ToDos')
		            ->where('isdelete = 0')
		            ->andWhere('iscompleted = 0')
		            ->andWhere('triggered_by != ""')
		            ->andWhere('group_id != "0"')
		            ->andWhereIn('client_id', $organisation_clients)
		            ->andWhereIn('CONCAT(ipid, triggered_by, group_id)', $record_keys);
		            $sapv_res = $sapv->fetchArray();
		            // 					print_R($sapv_res); exit;
		    
		            foreach($sapv_res as $k => $v)
		            {
		                // if  patient_step_identification != 0 we compare  the itentification from new todos with the identification from existing todos
		                if( $v['patient_step_identification'] != "0" ){
		                    if($v['patient_step_identification'] == $step_validation[$v['key_value']] ){
		                        unset($records_todo[$v['key_value']]);
		                    }
		                } else { // if patient_step_identification == 0 - this means that we already have a todo for this step and we don't insert a new one
		                    unset($records_todo[$v['key_value']]);
		                }
		            }
		                
		            
 		            if(count($records_todo) > 0)
 		            {
 		                $collection = new Doctrine_Collection('ToDos');
 		                $collection->fromArray($records_todo);
 		                $collection->save();
 		            }
		        }
		        unlink($file_location . $lock_filename);
		    }
		}

		

		public function notdienst_action_messages($ipid,$user_id)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
	        $clients_ids = array($clientid);

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();

	        // get patient details
	        $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
	        $patient_name = $patient_details[$ipid]['first_name'] . ' ' . $patient_details[$ipid]['last_name'];

	        //get client settings - ISPC 2271
	        $clientdata = $client->getClientDataByid($clientid);
	        $group_settings = $clientdata[0]['notfall_messages_settings'];
	        
	        // ISPC -2271
	        /*if(!empty($group_settings))
	        {
	        	$MasterGroups = $group_settings;
	        }
	        
	        // get user details
	        $usergroup = new Usergroup();	        
	        
	        $MasterGroups = array("4","9");
	        $master_group_ids = $usergroup->getUserGroups($MasterGroups);
	        	
	        foreach($master_group_ids as $key => $value)
	        {
	            $groups_id[$value['groupmaster']] = $value['id'];
	            $group_info[$value['id']]['master'] = $value['groupmaster'];
	        }*/
	        $usergroup = new Usergroup();
	         
	        $MasterGroups = array("4");
	        
	        $master_group_ids = $usergroup->getUserGroups($MasterGroups);
	        
	        foreach($master_group_ids as $key => $value)
	        {
	        	$groups_id[$value['groupmaster']] = $value['id'];
	        	$group_info[$value['id']]['master'] = $value['groupmaster'];
	        }
	        
	        $users_array = $user->getClientsUsers($clients_ids);
	        foreach($users_array as $user_val)
	        {
	            $user_details[$user_val['id']] = $user_val;
	            
	        	//ISPC - 2271
	            /*if($group_info[$user_val['groupid']]['master'] == '9')
	            {
	                $users ['not_dienst'][] = $user_val ['id']; // Hausarzt
	            }*/
	            
	            if(in_array($user_val['groupid'], $group_settings))
	            {
	            	$users ['not_dienst'][] = $user_val ['id']; // Hausarzt
	            }
	            
	            if($group_info[$user_val['groupid']]['master'] == '4')
	            {
	                $users ['doctor'][] = $user_val ['id'];
	            }
	        }

	        if(empty($users['doctor']))
	        {
	            $users['doctor'] [ ] = "999999999";
	        }
	        
	        if(!empty($users['not_dienst']) && in_array($user_id,$users['not_dienst']))
	        {
    	        $fdoc = Doctrine_Query::create()
    	        ->select("*, e.epid, e.ipid")
    	        ->from('PatientQpaMapping q')
    	        ->whereIn("q.userid", $users['doctor'])
    	        ->andWhere('q.epid!=""')
    	        ->leftJoin('q.EpidIpidMapping e')
    	        ->where('q.epid = e.epid')
    	        ->andWhere('e.ipid = ?',$ipid);
    	        $doc_assigned_patients = $fdoc->fetchArray();
    	        
    	        //TODO-2235
    	        // Ancuta 09.04.2019
    	        //If the entries are made by the assigned doctor - the messages should not be sent
    	        $assigned_users = array_column($doc_assigned_patients, 'userid');
    	        if( in_array($userid,$assigned_users) && in_array($userid,$users ['doctor'])){
    	            return;
    	        }
    	        // --
    	        
    	        
    	        
    	        //var_dump( $doc_assigned_patients); exit;
    	        foreach($doc_assigned_patients as $doc_patient)
    	        {
    	            if(in_array($doc_patient['userid'], $users ['doctor'])){ // Check if the user is DOCTOR/ARZT - oly docotrs should receive messages
    	               $users_to_notify[] = $user_details[$doc_patient['userid']];
    	               $users_ids[] = $doc_patient['userid'];
    	               $users_patients[$doc_patient['userid']][] = $doc_patient['EpidIpidMapping']['ipid'];
    	            }
    	        }
    	        
                // and set message to remaining users all of them have notify on
                $date = date('Y-m-d');
                
                // ########################
                // ISPC-1600
                // ########################
                $email_subject = $Tr->translate('mail_subject_not_diesnt_action_send_message'). ' ' . date('d.m.Y H:i');
                $email_text = "";
                $email_text .= "Bei einem Ihrer Patienten hat es einen Rufbereitschaftseinsatz gegeben. %patient_name%";
                $additional_text .= "Bei einem Ihrer Patienten hat es einen Rufbereitschaftseinsatz gegeben. %patient_name%";
                // link to ISPC
               // $email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
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
    
                
                $message = "Bei einem Ihrer Patienten hat es einen Rufbereitschaftseinsatz gegeben. %patient_name%";
                
                
                //TODO-3164 Ancuta 08.09.2020
                $email_data = array();
                $email_data['client_info'] = $client_details_string;
                $email_data['additional_text'] =str_replace('%patient_name%', $patient_name, $additional_text);  
                $email_text = "";//overwrite
                $email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
                //-- 
                
                foreach($users_to_notify as $uid => $notified_client_user)
    			{
                    //prepare message with assigned patients only
                    //make sure that the assigned user patients are from the same client as user
	                if(in_array($ipid, $users_patients[$notified_client_user['id']]))
	                {
                        $message_receiver[] = $notified_client_user['id'];
                    }
    		                
                    if(!empty($message)  && in_array($notified_client_user['id'],$message_receiver))
    				{
    					$message = str_replace('%patient_name%', $patient_name, $message);
                        $insert_data[$notified_client_user['id']] = array();
                        $insert_data[$notified_client_user['id']]['sender'] = '0';
                        $insert_data[$notified_client_user['id']]['clientid'] = $notified_client_user['clientid'];
                        $insert_data[$notified_client_user['id']]['recipient'] = $notified_client_user['id'];
                        $insert_data[$notified_client_user['id']]['msg_date'] = date("Y-m-d H:i:s", time());
                        $insert_data[$notified_client_user['id']]['title'] = Pms_CommonData::aesEncrypt($Tr->translate('not_dienst_actions_title').'(' . date("d.m.Y") . ')');
                        $insert_data[$notified_client_user['id']]['content'] = Pms_CommonData::aesEncrypt( $message);
                        $insert_data[$notified_client_user['id']]['ipid'] = $ipid;
                        $insert_data[$notified_client_user['id']]['source'] = 'not_dienst_action';
                        $insert_data[$notified_client_user['id']]['create_date'] = date("Y-m-d", time());
                        $insert_data[$notified_client_user['id']]['create_user'] = '0';
                        $insert_data[$notified_client_user['id']]['read_msg'] = '0';
                        
                        $msg = new Messages();
                        $msg->fromArray($insert_data[$notified_client_user['id']]);
                        $msg->save();
                        
                        //TODO-3164 Ancuta 08.09.2020
                        //$email_text = str_replace('%patient_name%', $patient_name, $email_text);
                        //--
                        
                        
                        $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
                        $mail = new Zend_Mail('UTF-8');
                        $mail->setBodyHtml($email_text)
                            ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
                            ->addTo($notified_client_user['emailid'], $notified_client_user['last_name'] . ' ' . $notified_client_user['first_name'])
                            ->setSubject($email_subject)
                            ->setIpids($users_patients[$notified_client_user['id']])
                            ->send($mail_transport);
                    }
                }
            }
        }
        

        // NOT USED - Added by Ancuta
		public function notdienst_action_messages_181004($ipid,$user_id)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
	        $clients_ids = array($clientid);

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();

	        // get patient details
	        $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
	        $patient_name = $patient_details[$ipid]['first_name'] . ' ' . $patient_details[$ipid]['last_name'];

	        
	        // get user details
	        $usergroup = new Usergroup();
	        $MasterGroups = array("4","9");
	        $master_group_ids = $usergroup->getUserGroups($MasterGroups);
	        	
	        foreach($master_group_ids as $key => $value)
	        {
	            $groups_id[$value['groupmaster']] = $value['id'];
	            $group_info[$value['id']]['master'] = $value['groupmaster'];
	        }

	        
	        $users_array = $user->getClientsUsers($clients_ids);
	        foreach($users_array as $user_val)
	        {
	            $user_details[$user_val['id']] = $user_val;
	        
	            if($group_info[$user_val['groupid']]['master'] == '9')
	            {
	                $users ['not_dienst'][] = $user_val ['id']; // Hausarzt
	            }
	            
	            if($group_info[$user_val['groupid']]['master'] == '4')
	            {
	                $users ['doctor'][] = $user_val ['id'];
	            }
	        }

	        if(empty($users['doctor']))
	        {
	            $users['doctor'] [ ] = "999999999";
	        }

	        
	        
	        if(!empty($users['not_dienst']) && in_array($user_id,$users['not_dienst']))
	        {
    	        $fdoc = Doctrine_Query::create()
    	        ->select("*, e.epid, e.ipid")
    	        ->from('PatientQpaMapping q')
    	        ->whereIn("q.userid", $users['doctor'])
    	        ->andWhere('q.epid!=""')
    	        ->leftJoin('q.EpidIpidMapping e')
    	        ->where('q.epid = e.epid')
    	        ->andWhere('e.ipid = "'.$ipid.'" ');
    	        $doc_assigned_patients = $fdoc->fetchArray();
    	
    	        
    	        foreach($doc_assigned_patients as $doc_patient)
    	        {
    	            $users_to_notify[] = $user_details[$doc_patient['userid']];
    	            $users_ids[] = $doc_patient['userid'];
    	            $users_patients[$doc_patient['userid']][] = $doc_patient['EpidIpidMapping']['ipid'];
    	        }

    	        
                // and set message to remaining users all of them have notify on
                $date = date('Y-m-d');
                
                // ########################
                // ISPC-1600
                // ########################
                $email_subject = $Tr->translate('mail_subject_not_diesnt_action_send_message'). ' ' . date('d.m.Y H:i');
                $email_text = "";
                $email_text .= "Bei einem Ihrer Patienten hat es einen Rufbereitschaftseinsatz gegeben. %patient_name%";
                $additional_text = "Bei einem Ihrer Patienten hat es einen Rufbereitschaftseinsatz gegeben. %patient_name%";
                // link to ISPC
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
    
                
                $message = "Bei einem Ihrer Patienten hat es einen Rufbereitschaftseinsatz gegeben. %patient_name%";
                
                
                //TODO-3164 Ancuta 08.09.2020
                $email_data = array();
                $email_data['client_info'] = $client_details_string;
                $email_data['additional_text'] =str_replace('%patient_name%', $patient_name, $additional_text);
                $email_text = "";//overwrite
                $email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
                //-- 
                
                foreach($users_to_notify as $uid => $notified_client_user)
    			{
                    //prepare message with assigned patients only
                    //make sure that the assigned user patients are from the same client as user
	                if(in_array($ipid, $users_patients[$notified_client_user['id']]))
	                {
                        $message_receiver[] = $notified_client_user['id'];
                    }
    		                
                    if(!empty($message)  && in_array($notified_client_user['id'],$message_receiver))
    				{
    					$message = str_replace('%patient_name%', $patient_name, $message);
                        $insert_data[$notified_client_user['id']] = array();
                        $insert_data[$notified_client_user['id']]['sender'] = '0';
                        $insert_data[$notified_client_user['id']]['clientid'] = $notified_client_user['clientid'];
                        $insert_data[$notified_client_user['id']]['recipient'] = $notified_client_user['id'];
                        $insert_data[$notified_client_user['id']]['msg_date'] = date("Y-m-d H:i:s", time());
                        $insert_data[$notified_client_user['id']]['title'] = Pms_CommonData::aesEncrypt($Tr->translate('not_dienst_actions_title').'(' . date("d.m.Y") . ')');
                        $insert_data[$notified_client_user['id']]['content'] = Pms_CommonData::aesEncrypt( $message);
                        $insert_data[$notified_client_user['id']]['ipid'] = $ipid;
                        $insert_data[$notified_client_user['id']]['source'] = 'not_dienst_action';
                        $insert_data[$notified_client_user['id']]['create_date'] = date("Y-m-d", time());
                        $insert_data[$notified_client_user['id']]['create_user'] = '0';
                        $insert_data[$notified_client_user['id']]['read_msg'] = '0';
                        
                        $msg = new Messages();
                        $msg->fromArray($insert_data[$notified_client_user['id']]);
                        $msg->save();
                        
                        //TODO-3164 Ancuta 08.09.2020
                        //$email_text = str_replace('%patient_name%', $patient_name, $email_text);
                        //--
                        
                        $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
                        $mail = new Zend_Mail('UTF-8');
                        $mail->setBodyHtml($email_text)
                            ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
                            ->addTo($notified_client_user['emailid'], $notified_client_user['last_name'] . ' ' . $notified_client_user['first_name'])
                            ->setSubject($email_subject)
                            ->setIpids($users_patients[$notified_client_user['id']])
                            ->send($mail_transport);
                    }
                }
            }
        }
        
        
        
		public function medication_acknowledge_messages($ipid,$message,$user = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();

	        // get patient details
	        $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
	        $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
	        
	        
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		    if(empty($approval_users)){
		        $approval_users[] = "XXXXXXXXXX";
		    }

		    $clients_ids = array($clientid);
		    $users = $user->getClientsUsers($clients_ids);
		
	        foreach($users as $user)
	        {
	            if($user['notifications']['medication_acknowledge'] != 'none' && in_array($user['id'],$approval_users))
	            {
	                $users_ids[] = $user['id'];
	                $users_to_notify[] = $user;
	            }
	        }

		    if(count($users_ids) == '0')
            {
                $users_ids[] = '9999999';
            }
	        
	
	        $fdoc = Doctrine_Query::create()
	        ->select("*, e.epid, e.ipid")
	        ->from('PatientQpaMapping q')
	        ->whereIn("q.userid", $users_ids)
	        ->andWhere('q.epid!=""')
	        ->leftJoin('q.EpidIpidMapping e')
	        ->where('q.epid = e.epid')
	        ->andWhere('e.ipid = "'.$ipid.'" ');
	        $doc_assigned_patients = $fdoc->fetchArray();
	
	        foreach($doc_assigned_patients as $doc_patient)
	        {
	            $users_patients[$doc_patient['userid']][] = $doc_patient['EpidIpidMapping']['ipid'];
	        }

            // and set message to remaining users all of them have notify on
            $date = date('Y-m-d');

            
            // ########################
            // ISPC-1600
            // ########################
            $email_subject = $Tr->translate('mail_subject_action_medical_acknowledge'). ' ' . date('d.m.Y H:i');
            $email_text = "";
            $email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
            // link to ISPC
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
            
            foreach($users_to_notify as $notified_client_user)
			{
                //check if user dsettings allow to sent all client users in mail or just assigned users
                if($notified_client_user['notifications']['medication_acknowledge'] == 'assigned' && $users_patients[$notified_client_user['id']])
                {
                    //prepare message with assigned patients only
                    //make sure that the assigned user patients are from the same client as user
	                if(in_array($ipid, $users_patients[$notified_client_user['id']]))
	                {
                        $message_receiver[] = $notified_client_user['id'];
                    }
                }
                else if($notified_client_user['notifications']['medication_acknowledge'] == 'all')
		        {
		            $message_receiver[] = $notified_client_user['id'];
                }

		                
                if(!empty($message)  && in_array($notified_client_user['id'],$message_receiver))
				{
					$message = str_replace('%patient_name%', $patient_name, $message);
                    $insert_data[$notified_client_user['id']] = array();
                    $insert_data[$notified_client_user['id']]['sender'] = '0';
                    $insert_data[$notified_client_user['id']]['clientid'] = $notified_client_user['clientid'];
                    $insert_data[$notified_client_user['id']]['recipient'] = $notified_client_user['id'];
                    $insert_data[$notified_client_user['id']]['msg_date'] = date("Y-m-d H:i:s", time());
                    $insert_data[$notified_client_user['id']]['title'] = Pms_CommonData::aesEncrypt($Tr->translate('medication_acknowledge title').'(' . date("d.m.Y") . ')');
                    $insert_data[$notified_client_user['id']]['content'] = Pms_CommonData::aesEncrypt( $message);
                    $insert_data[$notified_client_user['id']]['ipid'] = $ipid;
                    $insert_data[$notified_client_user['id']]['source'] = 'medication_acknowledge_function';
                    $insert_data[$notified_client_user['id']]['create_date'] = date("Y-m-d", time());
                    $insert_data[$notified_client_user['id']]['create_user'] = '0';
                    $insert_data[$notified_client_user['id']]['read_msg'] = '0';
                    
                    $msg = new Messages();
                    $msg->fromArray($insert_data[$notified_client_user['id']]);
                    $msg->save();
                    
                    $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
                    $mail = new Zend_Mail('UTF-8');
                    $mail->setBodyHtml($email_text)
                        ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
                        ->addTo($notified_client_user['emailid'], $notified_client_user['last_name'] . ' ' . $notified_client_user['first_name'])
                        ->setSubject($email_subject)
                        ->setIpids($anlage_ipids[$notified_client_user['id']])
                        ->send($mail_transport);
                }
                
            }
            
        }
        
        
		public function medication_acknowledge_todo($ipid, $todo_message, $drugplan_id, $alt_id, $user = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();
		    
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,false);
		    // get patient details
		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
		     
		    $todo_data= array();
		    
		    $todo_identifier = "";
		    $ident = "";
		    
		    $users_additional_info = array();
		    foreach($approval_users as $user_id=>$user_data){
		    	$users_additional_info[] = 'u'.$user_id;
		    }
		    
		    foreach($approval_users as $user_id=>$user_data){
		    	
		    	$todo_message = str_replace('%patient_name%', $patient_name, $todo_message);
		        $todo_identifier = "medacknowledge-".$drugplan_id;
		        $todo_date = date('d.m.Y',time());
		        $ident = $ipid.$todo_identifier.$user_id;
		        $todo_data[$ident] = array(
		            'client_id' => $clientid,
		            'user_id' => $user_id,
		            'group_id' => '0',
		            'record_id' => $alt_id,
		            'ipid' => $ipid,
		            'todo' => $todo_message,
		            'triggered_by' => $todo_identifier,
		            'isdelete' => '0',
		            'iscompleted' => '0',
		            'patient_step_identification' => '0',
					"create_date" => date('Y-m-d H:i:s', time()),
    				"until_date" => date('Y-m-d H:i:s', time()),
					"additional_info" => implode(";",$users_additional_info)
		        );
		        
		    }

		    if($todo_data && !empty($todo_data))
		    {
	    		$record_keys = array_values(array_unique(array_keys($todo_data)));
	    	
	    		// get existing todos
	    		$existing_todos_q = Doctrine_Query::create()
	    		->select("CONCAT(ipid,triggered_by,user_id) as key_value, id")
	    		->from('ToDos')
	    		->where('isdelete = 0')
	    		->andWhere('iscompleted = 0')
	    		->andWhere('triggered_by != ""')
	    		->andWhere('client_id = "'.$clientid.'"')
	    		->andWhereIn('CONCAT(ipid,triggered_by,user_id)', $record_keys);
	    		$existing_todos_res = $existing_todos_q->fetchArray();
 
		    	if($existing_todos_res)
		    	{
		    		foreach($existing_todos_res as $k => $v)
		    		{
		    			// if the todo is active it needs to be edited
		    			$cust = Doctrine::getTable('ToDos')->find($v['id']);
		    			if ($cust){
		    				$cust->todo = $todo_message;
		    				$cust->record_id = $alt_id;
		    				$cust->save();
		    			}	
		   				unset($todo_data[$v['key_value']]);
		    		}
		    	}
		    	
		        $collection = new Doctrine_Collection('ToDos');
		        $collection->fromArray($todo_data);
		        $collection->save();
		    
		        return $todo_data;
		    }
        }
        
        /**
         * @author Ancuta 09.03.2020
         * TODO-2850
         * @param unknown $ipid
         * @param unknown $todo_message
         * @param unknown $drugplan_id
         * @param unknown $alt_id
         * @param boolean $user
         * @return string[][]|unknown[][]|NULL[][]|boolean[][]|void[][]|array[][][]|Doctrine_Collection[][][]
         * copy of fn medication_acknowledge_todo
         */
        
		public function remove_medication_acknowledge_todo($ipid, $todo_message, $drugplan_id)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();
		    
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,false);
		    // get patient details
		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
		     
		    $todo_data= array();
		    
		    $todo_identifier = "";
		    $ident = "";
		    
		    $users_additional_info = array();
		    foreach($approval_users as $user_id=>$user_data){
		    	$users_additional_info[] = 'u'.$user_id;
		    }
		    
		    foreach($approval_users as $user_id=>$user_data){
		    	
		    	$todo_message = str_replace('%patient_name%', $patient_name, $todo_message);
		        $todo_identifier = "medacknowledge-".$drugplan_id;
		        $todo_date = date('d.m.Y',time());
		        $ident = $ipid.$todo_identifier.$user_id;
		        $todo_data[$ident] = array(
		            'client_id' => $clientid,
		            'user_id' => $user_id,
		            'group_id' => '0',
		            'ipid' => $ipid,
		            'todo' => $todo_message,
		            'triggered_by' => $todo_identifier,
		            'isdelete' => '0',
		            'iscompleted' => '0',
		            'patient_step_identification' => '0',
					"create_date" => date('Y-m-d H:i:s', time()),
    				"until_date" => date('Y-m-d H:i:s', time()),
					"additional_info" => implode(";",$users_additional_info)
		        );
		        
		    }


		    if($todo_data && !empty($todo_data))
		    {
	    		$record_keys = array_values(array_unique(array_keys($todo_data)));
	    	
	    		// get existing todos
	    		$existing_todos_q = Doctrine_Query::create()
	    		->select("CONCAT(ipid,triggered_by,user_id) as key_value, id")
	    		->from('ToDos')
	    		->where('isdelete = 0')
	    		->andWhere('iscompleted = 0')
	    		->andWhere('triggered_by != ""')
	    		->andWhere('client_id = "'.$clientid.'"')
	    		->andWhereIn('CONCAT(ipid,triggered_by,user_id)', $record_keys);
	    		$existing_todos_res = $existing_todos_q->fetchArray();

		    	if($existing_todos_res)
		    	{
		    		foreach($existing_todos_res as $k => $v)
		    		{
		    			// if the todo is active it needs to be deleted- as it is no longer needed
		    			
		    			$cust = Doctrine::getTable('ToDos')->find($v['id']);
		    			if ($cust){
		    				$cust->isdelete = 1;
		    				$cust->save();
		    			}	
		   				unset($todo_data[$v['key_value']]);
		    		}
		    	}
 
		        return $todo_data;
		    }
        }
        
        
		public function pump_medication_acknowledge_todo($ipid, $todo_message, $cocktail_id, $alt_id, $user = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();
		    
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,false);
		    // get patient details
		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
		     
		    $todo_data= array();
		    
		    $todo_identifier = "";
		    $ident = "";
		    
 
		    foreach($approval_users as $user_id=>$user_data){
		    	
		    	$todo_message = str_replace('%patient_name%', $patient_name, $todo_message);
		        $todo_identifier = "pumpmedacknowledge-".$cocktail_id;
		        $todo_date = date('d.m.Y',time());
		        $ident = $ipid.$todo_identifier.$user_id;
		        $todo_data[$ident] = array(
		            'client_id' => $clientid,
		            'user_id' => $user_id,
		            'group_id' => '0',
		            'record_id' => $alt_id,
		            'ipid' => $ipid,
		            'todo' => $todo_message,
		            'triggered_by' => $todo_identifier,
		            'isdelete' => '0',
		            'iscompleted' => '0',
		            'patient_step_identification' => '0',
					"create_date" => date('Y-m-d H:i:s', time()),
    				"until_date" => date('Y-m-d H:i:s', time())
		        );
		        
		    }

		    if($todo_data && !empty($todo_data))
		    {
	    		$record_keys = array_values(array_unique(array_keys($todo_data)));
	    	
	    		// get existing todos
	    		$existing_todos_q = Doctrine_Query::create()
	    		->select("CONCAT(ipid,triggered_by,user_id) as key_value, id")
	    		->from('ToDos')
	    		->where('isdelete = 0')
	    		->andWhere('iscompleted = 0')
	    		->andWhere('triggered_by != ""')
	    		->andWhere('client_id = "'.$clientid.'"')
	    		->andWhereIn('CONCAT(ipid,triggered_by,user_id)', $record_keys);
	    		$existing_todos_res = $existing_todos_q->fetchArray();
 
		    	if($existing_todos_res)
		    	{
		    		foreach($existing_todos_res as $k => $v)
		    		{
		    			// if the todo is active it needs to be edited
		    			$cust = Doctrine::getTable('ToDos')->find($v['id']);
		    			if ($cust){
		    				$cust->todo = $todo_message;
		    				$cust->record_id = $alt_id;
		    				$cust->save();
		    			}	
		   				unset($todo_data[$v['key_value']]);
		    		}
		    	}
		    	
		        $collection = new Doctrine_Collection('ToDos');
		        $collection->fromArray($todo_data);
		        $collection->save();
		    
		        return $todo_data;
		    }
        }
        
        /**
         * ISPC-2833 Ancuta 01.03.2021
         * copy of fn pump_medication_acknowledge_todo
         * @param unknown $ipid
         * @param unknown $todo_message
         * @param unknown $cocktail_id
         * @param unknown $alt_id
         * @param boolean $user
         * @return string[][]|unknown[][]|NULL[][]|boolean[][]|void[][]|array[][][]|Doctrine_Collection[][][]
         */
		public function ispumpe_pump_medication_acknowledge_todo($ipid, $todo_message, $cocktail_id, $alt_id, $user = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();
		    
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,false);
		    // get patient details
		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
		     
		    $todo_data= array();
		    
		    $todo_identifier = "";
		    $ident = "";
		    
 
		    foreach($approval_users as $user_id=>$user_data){
		    	
		    	$todo_message = str_replace('%patient_name%', $patient_name, $todo_message);
		        $todo_identifier = "ispumpe_pumpmedacknowledge-".$cocktail_id;
		        $todo_date = date('d.m.Y',time());
		        $ident = $ipid.$todo_identifier.$user_id;
		        $todo_data[$ident] = array(
		            'client_id' => $clientid,
		            'user_id' => $user_id,
		            'group_id' => '0',
		            'record_id' => $alt_id,
		            'ipid' => $ipid,
		            'todo' => $todo_message,
		            'triggered_by' => $todo_identifier,
		            'isdelete' => '0',
		            'iscompleted' => '0',
		            'patient_step_identification' => '0',
					"create_date" => date('Y-m-d H:i:s', time()),
    				"until_date" => date('Y-m-d H:i:s', time())
		        );
		        
		    }

		    if($todo_data && !empty($todo_data))
		    {
	    		$record_keys = array_values(array_unique(array_keys($todo_data)));
	    	
	    		// get existing todos
	    		$existing_todos_q = Doctrine_Query::create()
	    		->select("CONCAT(ipid,triggered_by,user_id) as key_value, id")
	    		->from('ToDos')
	    		->where('isdelete = 0')
	    		->andWhere('iscompleted = 0')
	    		->andWhere('triggered_by != ""')
	    		->andWhere('client_id = "'.$clientid.'"')
	    		->andWhereIn('CONCAT(ipid,triggered_by,user_id)', $record_keys);
	    		$existing_todos_res = $existing_todos_q->fetchArray();
 
		    	if($existing_todos_res)
		    	{
		    		foreach($existing_todos_res as $k => $v)
		    		{
		    			// if the todo is active it needs to be edited
		    			$cust = Doctrine::getTable('ToDos')->find($v['id']);
		    			if ($cust){
		    				$cust->todo = $todo_message;
		    				$cust->record_id = $alt_id;
		    				$cust->save();
		    			}	
		   				unset($todo_data[$v['key_value']]);
		    		}
		    	}
		    	
		        $collection = new Doctrine_Collection('ToDos');
		        $collection->fromArray($todo_data);
		        $collection->save();
		    
		        return $todo_data;
		    }
        }

        
        
        /**
         * @author Ancuta TODO-2850 + TODO-3620
         * @param unknown $ipid
         * @param unknown $todo_message
         * @param unknown $cocktail_id
         * @param unknown $alt_id
         * @param boolean $user
         * @return string[][]|unknown[][]|boolean[][]|void[][]|array[][][]|Doctrine_Collection[][][]
         */
		public function remove_pump_medication_acknowledge_todo($ipid, $todo_message, $cocktail_id=0, $alt_id = 0, $user = false)
		{
		    if(empty($cocktail_id) || empty($ipid)){
		        return;
		    }
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();
		    
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,false);
		    // get patient details
		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
		     
		    $todo_data= array();
		    
		    $todo_identifier = "";
		    $ident = "";
		    
 
		    foreach($approval_users as $user_id=>$user_data){
		    	
		    	$todo_message = str_replace('%patient_name%', $patient_name, $todo_message);
		        $todo_identifier = "pumpmedacknowledge-".$cocktail_id;
		        $todo_date = date('d.m.Y',time());
		        $ident = $ipid.$todo_identifier.$user_id;
		        $todo_data[$ident] = array(
		            'client_id' => $clientid,
		            'user_id' => $user_id,
		            'group_id' => '0',
		            'record_id' => $alt_id,
		            'ipid' => $ipid,
		            'todo' => $todo_message,
		            'triggered_by' => $todo_identifier,
		            'isdelete' => '0',
		            'iscompleted' => '0',
		            'patient_step_identification' => '0',
					"create_date" => date('Y-m-d H:i:s', time()),
    				"until_date" => date('Y-m-d H:i:s', time())
		        );
		        
		    }

		    if($todo_data && !empty($todo_data))
		    {
	    		$record_keys = array_values(array_unique(array_keys($todo_data)));
	    	
	    		// get existing todos
	    		$existing_todos_q = Doctrine_Query::create()
	    		->select("CONCAT(ipid,triggered_by,user_id) as key_value, id")
	    		->from('ToDos')
	    		->where('isdelete = 0')
	    		->andWhere('iscompleted = 0')
	    		->andWhere('triggered_by != ""')
	    		->andWhere('client_id = "'.$clientid.'"')
	    		->andWhereIn('CONCAT(ipid,triggered_by,user_id)', $record_keys);
	    		$existing_todos_res = $existing_todos_q->fetchArray();
 
		    	if($existing_todos_res)
		    	{
		    		foreach($existing_todos_res as $k => $v)
		    		{
		    			// if the todo is active it needs to be edited
		    			$cust = Doctrine::getTable('ToDos')->find($v['id']);
		    			if ($cust){
		    				$cust->isdelete=1 ;
		    				$cust->save();
		    			}	
		   				unset($todo_data[$v['key_value']]);
		    		}
		    	}
		    	
	 
		        return $todo_data;
		    }
        }
        
        
        /**
         * ISPC-2833 Ancuta 01.03.2021
         * copy of fn remove_pump_medication_acknowledge_todo
         * @param unknown $ipid
         * @param unknown $todo_message
         * @param number $cocktail_id
         * @param number $alt_id
         * @param boolean $user
         * @return void|string[][]|number[][]|unknown[][]|NULL[][]|boolean[][]|void[][]|array[][][]|Doctrine_Collection[][][]
         */
		public function remove_ispumpe_pump_medication_acknowledge_todo($ipid, $todo_message, $cocktail_id=0, $alt_id = 0, $user = false)
		{
		    if(empty($cocktail_id) || empty($ipid)){
		        return;
		    }
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;

		    $client = new Client();
	        $user = new User();
	        $Tr = new Zend_View_Helper_Translate();
		    
		    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,false);
		    // get patient details
		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
		     
		    $todo_data= array();
		    
		    $todo_identifier = "";
		    $ident = "";
		    
 
		    foreach($approval_users as $user_id=>$user_data){
		    	
		    	$todo_message = str_replace('%patient_name%', $patient_name, $todo_message);
		        $todo_identifier = "ispumpe_pumpmedacknowledge-".$cocktail_id;
		        $todo_date = date('d.m.Y',time());
		        $ident = $ipid.$todo_identifier.$user_id;
		        $todo_data[$ident] = array(
		            'client_id' => $clientid,
		            'user_id' => $user_id,
		            'group_id' => '0',
		            'record_id' => $alt_id,
		            'ipid' => $ipid,
		            'todo' => $todo_message,
		            'triggered_by' => $todo_identifier,
		            'isdelete' => '0',
		            'iscompleted' => '0',
		            'patient_step_identification' => '0',
					"create_date" => date('Y-m-d H:i:s', time()),
    				"until_date" => date('Y-m-d H:i:s', time())
		        );
		        
		    }

		    if($todo_data && !empty($todo_data))
		    {
	    		$record_keys = array_values(array_unique(array_keys($todo_data)));
	    	
	    		// get existing todos
	    		$existing_todos_q = Doctrine_Query::create()
	    		->select("CONCAT(ipid,triggered_by,user_id) as key_value, id")
	    		->from('ToDos')
	    		->where('isdelete = 0')
	    		->andWhere('iscompleted = 0')
	    		->andWhere('triggered_by != ""')
	    		->andWhere('client_id = "'.$clientid.'"')
	    		->andWhereIn('CONCAT(ipid,triggered_by,user_id)', $record_keys);
	    		$existing_todos_res = $existing_todos_q->fetchArray();
 
		    	if($existing_todos_res)
		    	{
		    		foreach($existing_todos_res as $k => $v)
		    		{
		    			// if the todo is active it needs to be edited
		    			$cust = Doctrine::getTable('ToDos')->find($v['id']);
		    			if ($cust){
		    				$cust->isdelete=1 ;
		    				$cust->save();
		    			}	
		   				unset($todo_data[$v['key_value']]);
		    		}
		    	}
		    	
	 
		        return $todo_data;
		    }
        }

        

        public function compleint_action_messages($ipid, $user_id = false, $formular_id = false, $status = 'opened')
        {
        	$logininfo = new Zend_Session_Namespace('Login_Info');
        	$userid = $logininfo->userid;
        	$clientid = $logininfo->clientid;
        
        	
        	
        	$client = new Client();
        	$user = new User();
        	$Tr = new Zend_View_Helper_Translate();
        
        	// get patient details
        	$patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
        	$patient_name = $patient_details[$ipid]['first_name'] . ' ' . $patient_details[$ipid]['last_name'];
        	$complaint_users_all = ComplaintUsers::get_complaint_users($clientid,true); // change if other users need to receive message when form is closed 
 
        	
        	
   	    	if($status == "closed")
   	    	{
	        	$patient_link = '<a href="'.APP_BASE.'patientformnew/complaintform?id=' . Pms_Uuid::encrypt($patient_details[$ipid]['id']) . '&formular_id='.$formular_id.'"> ' . $patient_name . "</a>";
	        	$complaint_users = $complaint_users_all['close_case'];  
			}
			else
			{
	        	$patient_link = '<a href="'.APP_BASE.'patientcourse/patientcourse?id=' . Pms_Uuid::encrypt($patient_details[$ipid]['id']) . '"> ' . $patient_name . "</a>";
	        	$complaint_users = $complaint_users_all['open_case'];  
			}

        	if(empty($complaint_users)){
        		return;
        	}
        
        	$clients_ids = array($clientid);
        	$users = $user->getClientsUsers($clients_ids,true);
        	$reporting_username = $users[$userid]['last_name'].', '.$users[$userid]['first_name'];

        	
        	foreach($users as $user)
        	{
				if(in_array($user['id'],$complaint_users)){
        			$users_ids[] = $user['id'];
        			$users_to_notify[] = $user;
				}
        	}
        	
        
        	if(count($users_ids) == '0')
        	{
        		return;
        	}
        	 
        
        	$fdoc = Doctrine_Query::create()
        	->select("*, e.epid, e.ipid")
        	->from('PatientQpaMapping q')
        	->whereIn("q.userid", $users_ids)
        	->andWhere('q.epid!=""')
        	->leftJoin('q.EpidIpidMapping e')
        	->where('q.epid = e.epid')
        	->andWhere('e.ipid = "'.$ipid.'" ');
        	$doc_assigned_patients = $fdoc->fetchArray();
        
        	foreach($doc_assigned_patients as $doc_patient)
        	{
        		$users_patients[$doc_patient['userid']][] = $doc_patient['EpidIpidMapping']['ipid'];
        	}
        
        	// and set message to remaining users all of them have notify on
        	$date = date('Y-m-d');
        
        
        	// ########################
        	// ISPC-1600
        	// ########################
        	$email_subject = $Tr->translate('mail_subject_complaint'). ' ' . date('d.m.Y H:i');
        	$email_text = "";
        	$email_text .= $Tr->translate('mail_content_complaint');
        	// link to ISPC
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
        	$email_data['additional_text'] =str_replace('%patient_name%', $patient_name, $additional_text);
        	$email_text = "";//overwrite
        	$email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
        	//-- 
        	
        	
        	
        	if($status == "closed"){
        		// change if oher message is needed
	        	$message_subject = "Reklamations-Fall wird geschlossen";
	        	$message = " Der Benutzer %reporting_username% hat beantragt die Reklamation %patient_link% zu schließen. Bitte prüfen Sie den Sachverhalt.";
        	} 
        	else
        	{
        		
	        	$message_subject = "Reklamation Patient %patient_name%";
	        	$message = " Der Benutzer (%reporting_username%) hat eine Reklamation für den Patienten %patient_link% gemeldet. Bitte veranlassen Sie weitere Schritte.";
	        	
        	}
        	foreach($users_to_notify as $notified_client_user)
        	{
        		$message_receiver[] = $notified_client_user['id'];
        
        		if(!empty($message)  && in_array($notified_client_user['id'],$message_receiver))
        		{
        			$message = str_replace('%patient_link%', $patient_link, $message);
        			$message = str_replace('%reporting_username%', $reporting_username, $message);
        			$message_subject = str_replace('%patient_name%', $patient_name, $message_subject);
        			$insert_data[$notified_client_user['id']] = array();
        			$insert_data[$notified_client_user['id']]['sender'] = '0';
        			$insert_data[$notified_client_user['id']]['clientid'] = $notified_client_user['clientid'];
        			$insert_data[$notified_client_user['id']]['recipient'] = $notified_client_user['id'];
        			$insert_data[$notified_client_user['id']]['msg_date'] = date("Y-m-d H:i:s", time());
        			$insert_data[$notified_client_user['id']]['title'] = Pms_CommonData::aesEncrypt($message_subject);
        			$insert_data[$notified_client_user['id']]['content'] = Pms_CommonData::aesEncrypt( $message);
        			$insert_data[$notified_client_user['id']]['ipid'] = $ipid;
        			$insert_data[$notified_client_user['id']]['source'] = 'complaint_form';
        			$insert_data[$notified_client_user['id']]['create_date'] = date("Y-m-d", time());
        			$insert_data[$notified_client_user['id']]['create_user'] = '0';
        			$insert_data[$notified_client_user['id']]['read_msg'] = '0';
        
        			$msg = new Messages();
        			$msg->fromArray($insert_data[$notified_client_user['id']]);
        			$msg->save();
        
        			$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
        			$mail = new Zend_Mail('UTF-8');
        			$mail->setBodyHtml($email_text)
        			->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
        			->addTo($notified_client_user['emailid'], $notified_client_user['last_name'] . ' ' . $notified_client_user['first_name'])
        			->setSubject($email_subject)
        			->setIpids($anlage_ipids[$notified_client_user['id']])
        			->send($mail_transport);
        		}
        
        	}
        
        }
        
        
        
        public function order_action_messages($ipid, $message_action, $sendTo, $order_creator)
        {

        	if( empty($ipid) || empty($message_action)  || (empty($sendTo) && empty ($order_creator) ) ){
        	    return;
        	}
            
            $logininfo = new Zend_Session_Namespace('Login_Info');
        	$userid = $logininfo->userid;
        	$clientid = $logininfo->clientid;
        
        	
        	
        	$client = new Client();
        	$user = new User();
        	$Tr = new Zend_View_Helper_Translate();
        
        	// user from post - and pseudogroups
        	$pseuso_users = array();
        	$users = array();
        	$sendToFinal = array();
        	foreach($sendTo as $k=>$post_user){
        	    // check if pseudo group 
        	   if(strpos($post_user, 'pseudogroup_') === 0){
        	       $pseuso_users[] = str_replace('pseudogroup_','',$post_user);
        	   }
        	   elseif( substr($post_user, 0, 1) === 'u' )
        	   {
        	       $users[] = str_replace('u','',$post_user);
        	       $sendToFinal[] = str_replace('u','',$post_user);
        	   }
        	}
        	
        	if(!empty($pseuso_users)){
        	    $clrec = new ClientOrderRecipients();
        	    $client_recipients = $clrec->get_client_recipients($clientid);
        	    
        	    // get assigned users to psgr
        	    $users_in_pseudogroups = array();
        	    $user_pseudo_users = new PseudoGroupUsers();
        	    $users_in_pseudogroups = $user_pseudo_users->get_users_by_groups($pseuso_users);
        	    
        	    
        	    foreach($users_in_pseudogroups as $ps_id=>$ps_users){
        	        foreach($ps_users as $k=>$pu_data){
        	            
        	            $users2pseudo[$pu_data['pseudo_id']][] = $pu_data['user_id'];
        	            if(in_array('u'.$pu_data['user_id'] ,$client_recipients)){
            	            $sendToFinal[] = $pu_data['user_id'];
        	            }
        	        }
        	    }
        	}
   	
        	
        	// get patient details
        	$patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
        	$patient_name = $patient_details[$ipid]['first_name'] . ' ' . $patient_details[$ipid]['last_name'];
        	
        	$order_management_link =  '<a href="'.APP_BASE.'orders/overview">hier</a>';
        	$recipients = array();
        	switch ($message_action){
        	    
        	    
        	    case "new_order_was_active":
        	        $message_subject = "Bestellung für Patient %patient_name%";
        	        $message = "Soeben wurde von %action_user% eine Bestellung für den Patienten %patient_name% ausgelöst. Klicken Sie bitte %link% um zur Bestellseite zu gelangen.";
        	        //$message .= "\n new_order_was_active \n";
        	        
        	        $recipients = $sendToFinal;
        	        $recipients[] = $order_creator;//ISPC-2464  05.11.2019 Ancuta 
        	        break;
        	        
        	        
        	    case "new_order_was_verified":
        	        $message_subject = "Bestellung für Patient %patient_name%";
        	        $message = "Soeben wurde von %action_user% eine Bestellung für den Patienten %patient_name% bestätigt. Klicken Sie bitte %link% um zur Bestellseite zu gelangen.";
        	        //$message .= "\n new_order_was_verified \n";
        	        
        	        //$recipients = $sendToFinal;
        	        //$recipients[] = $order_creator;//ISPC-2464 05.11.2019 Ancuta
        	        //TODO-2872 - Ancuta 26.03.2020 pct2 -  only the creator needs to receive message
        	        $recipients = array($order_creator);
        	        
        	        break;
        	        

        	    case "following_order_was_active":
        	        $message_subject = "Bestellung für Patient %patient_name%";
        	        $message = "Soeben wurde von %action_user% eine Bestellung für den Patienten %patient_name% ausgelöst. Klicken Sie bitte %link% um zur Bestellseite zu gelangen.";
        	        //$message .= "\n following_order_was_active \n";
        	        
        	        
        	        $recipients = $sendToFinal;
        	        $recipients[] = $order_creator;
        	        break;
        	    
        	    case "following_order_was_verified":
        	    case "order_was_edited":
        	        $message_subject = "Bestellung für Patient %patient_name%";
        	        $message = "Soeben wurde von %action_user% eine Bestellung für den Patienten %patient_name% bestätigt. Klicken Sie bitte %link% um zur Bestellseite zu gelangen.";
        	        //$message .= "\n following_order_was_verified OR order_was_edited \n";
        	        
        	        //$recipients = $sendToFinal;
           	        //$recipients[] = $order_creator;
        	        //TODO-2872 - Ancuta 26.03.2020 pct2 -  only the creator needs to receive message
        	        $recipients = array($order_creator);
        	        break;
        	        
        	    case "order_was_edited_by_updateing_prevoius":
        	        $message_subject = "Bestellung für Patient %patient_name%";
        	        $message = "Soeben wurde von %action_user% eine Bestellung für den Patienten %patient_name% bestätigt. Klicken Sie bitte %link% um zur Bestellseite zu gelangen.";
        	        //$message .= "\n order_was_edited_by_updateing_prevoius \n";
        	        
        	        $recipients = $sendToFinal;
        	        $recipients[] = $order_creator;
        	        break;
        	        
        	        
        	    case "order_was_paused":
        	        $message_subject = "Bestellung wurde pausiert";
        	        $message = "Die Bestellungen für den Patienten %patient_name% wurden durch %action_user% pausiert.";
        	        //$message .= "\n order_was_paused \n";
        	        $recipients = $sendToFinal;
        	        $recipients[] = $order_creator;
        	        break;
        	        
        	    case "order_was_stopped":
        	        $message_subject = "Bestellung wurde gestoppt";
        	        $message = "Die Bestellungen für den Patienten %patient_name% wurden durch %action_user% gestoppt.";
        	        //$message .= "\n order_was_stopped \n";
        	        $recipients = $sendToFinal;
        	        $recipients[] = $order_creator;
        	        break;
        	        
        	    case "order_was_canceled":
        	        $message_subject = "Bestellung wurde gelöscht";
        	        $message = "Die Bestellungen für den Patienten %patient_name% wurden durch %action_user% gelöscht.";
        	        //$message .= "\n order_was_canceled \n";
        	        
        	        
        	        //$recipients = $sendToFinal;
        	        //$recipients[] = $order_creator;
        	        //TODO-2872 - Ancuta 26.03.2020 pct2 -  only the creator needs to receive message
        	        $recipients = array($order_creator);
        	        break;
        	        
        	        
        	    case "orders_reactivated":
        	        $message_subject = "Bestellung wurde wieder aktiviert.";
        	        $message = "Die Bestellungen für den Patienten %patient_name% wurden durch %action_user%  wieder aktiviert.";
        	        //$message .= "\n orders_reactivated \n"; 
        	        $recipients = $sendToFinal;
        	        $recipients[] = $order_creator;
        	        break;
        	        
        	   default:
        	        break;
        	}
        	
        	
//         	dd(func_get_args(),$message,$recipients);
        	if(empty($recipients)){
        		return;
        	}
        
        	$clients_ids = array($clientid);
        	$users = $user->getClientsUsers($clients_ids,true);
        	$reporting_username = $users[$userid]['last_name'].', '.$users[$userid]['first_name'];

        	
        	foreach($users as $user)
        	{
				if(in_array($user['id'],$recipients)){
        			$users_ids[] = $user['id'];
        			$users_to_notify[] = $user;
				}
        	}
        	
        	if(count($users_ids) == '0')
        	{
        		return;
        	}
        
        	// and set message to remaining users all of them have notify on
        	$date = date('Y-m-d');
        
        
        	// ########################
        	// ISPC-1600
        	// ########################
        	//$email_subject = $Tr->translate('mail_subject_order'). ' ' . date('d.m.Y H:i');
        	$epid_ipid = Doctrine_Query::create()
        	->select('epid')
        	->from('EpidIpidMapping')        	
        	->where('ipid =?', $ipid)
        	->andWhere('epid IS NOT NULL');
        	$epid_ipid_res = $epid_ipid->fetchArray();
        	//$email_subject = "Bestellung für Patient ".$patient_name." - ".$epid_ipid_res[0]['epid']." aus ISPC ". date('d.m.Y H:i');       //ISPC-2639 Lore 24.07.2020
        	$email_subject = "Bestellung für ".$epid_ipid_res[0]['epid']." aus ISPC ". date('d.m.Y H:i');       //ISPC-2639 Lore 24.07.2020
        	
        	$email_text = "";
        	$email_text .= $Tr->translate('mail_subject_order');
        	// link to ISPC
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
        	
        	foreach($users_to_notify as $notified_client_user)
        	{
        		$message_receiver[] = $notified_client_user['id'];
        
        		if(!empty($message)  && in_array($notified_client_user['id'],$message_receiver))
        		{
        			$message = str_replace('%link%', $order_management_link, $message);
        			$message = str_replace('%action_user%', $reporting_username, $message);
        			$message = str_replace('%patient_name%', $patient_name, $message);
        			$message_subject = str_replace('%patient_name%', $patient_name, $message_subject);
        			$insert_data[$notified_client_user['id']] = array();
        			$insert_data[$notified_client_user['id']]['sender'] = '0';
        			$insert_data[$notified_client_user['id']]['clientid'] = $notified_client_user['clientid'];
        			$insert_data[$notified_client_user['id']]['recipient'] = $notified_client_user['id'];
        			$insert_data[$notified_client_user['id']]['msg_date'] = date("Y-m-d H:i:s", time());
        			$insert_data[$notified_client_user['id']]['title'] = Pms_CommonData::aesEncrypt($message_subject);
        			$insert_data[$notified_client_user['id']]['content'] = Pms_CommonData::aesEncrypt( $message);
        			$insert_data[$notified_client_user['id']]['ipid'] = $ipid;
        			$insert_data[$notified_client_user['id']]['source'] = 'order_management';
        			$insert_data[$notified_client_user['id']]['create_date'] = date("Y-m-d", time());
        			$insert_data[$notified_client_user['id']]['create_user'] = '0';
        			$insert_data[$notified_client_user['id']]['read_msg'] = '0';
        
        			$msg = new Messages();
        			$msg->fromArray($insert_data[$notified_client_user['id']]);
        			$msg->save();
        
        			$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
        			$mail = new Zend_Mail('UTF-8');
        			$mail->setBodyHtml($email_text)
        			->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
        			->addTo($notified_client_user['emailid'], $notified_client_user['last_name'] . ' ' . $notified_client_user['first_name'])
        			->setSubject($email_subject)
        			->setIpids($anlage_ipids[$notified_client_user['id']])
        			->send($mail_transport);
        		}
        
        	}
        
        }
        
        
        /**
         * @author Ancuta
         * ISPC-2432
         * @param unknown $clientid
         * @param unknown $ipid
         * @param unknown $todo_message
         * @return string[][]|unknown[][]|NULL[][]|boolean[][]|void[][]|array[][][]|Doctrine_Collection[][][]
         * Changes 12.02.2020 
         * Added translation for todo
         */
        public function mePatient_uploadImages_todos($clientid, $ipid, $todo_message = false)
        {
            $Tr = new Zend_View_Helper_Translate();
            
            // Ancuta: Changes applied on 12.03.2020
            $modules = new Modules();
            if($modules->checkModulePrivileges("215", $clientid))//specific ligetis labels
            {
                $mePatient_labels = Pms_CommonData::mePatientIdentification('ligetis');
            }
            else
            {
                $mePatient_labels = Pms_CommonData::mePatientIdentification('default');
            }
            //--
            
            
            // get all assigned users of patient
            $fdoc = Doctrine_Query::create()
            ->select("*, e.epid, e.ipid")
            ->from('PatientQpaMapping q')
            ->where('q.epid!=""')
            ->leftJoin('q.EpidIpidMapping e')
            ->where('q.epid = e.epid')
            ->andwhere('e.ipid= ?',$ipid);
            $doc_assigned_patients = $fdoc->fetchArray();

            $assigned_users = array();
            foreach($doc_assigned_patients as $doc_patient)
            {
                $assigned_users[$doc_patient['EpidIpidMapping']['ipid']][] = $doc_patient['userid'];
            }
            
            //2. get users of remaining wl clients
            $user = new User();
            $users = $user->getClientsUsers($clientid);
            
            $users_to_notify = array();
            foreach($users as $user)
            {
                if( $user['notifications']['mePatient_device_uploads'] == 'all' ||   ($user['notifications']['mePatient_device_uploads'] == 'assigned'  && in_array($user['id'],$assigned_users[$ipid])))
                {
                    $users_to_notify[] = $user;
                }
            }
            
            // get patient details
            $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
            $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
            
            $todo_data= array();
            
            $todo_identifier = "";
            $todo_message = $mePatient_labels['todos']['text'] ;//Now 12.03.2020 todo text is also  based on module  //$todo_message = $Tr->translate('Please check patient - %patient_name% , images were uploaded from device!');
            
            foreach($users_to_notify as $user_details)
            {
                $user_id = $user_details['id'];
                $todo_message = str_replace('%patient_name%', $patient_name, $todo_message);
                $todo_identifier = "device_uploaded_img";
                
                $todo_data[ ] = array(
                    'client_id' => $clientid,
                    'user_id' => $user_id,
                    'group_id' => '0',
                    'ipid' => $ipid,
                    'todo' => $todo_message,
                    'triggered_by' => $todo_identifier,
                    'isdelete' => '0',
                    'iscompleted' => '0',
                    'patient_step_identification' => '0',
                    "create_date" => date('Y-m-d H:i:s', time()),
                    "until_date" => date('Y-m-d H:i:s', time()) 
                );
                
            }
            if(!empty($todo_data)){
                $collection = new Doctrine_Collection('ToDos');
                $collection->fromArray($todo_data);
                $collection->save();
            }
            
            
            return "todos sent";
        }

        
        
        
        /**
         * TODO-3462 Ancuta 19.10.2020
         * Used for  ISPC-2507 :: When pharma request it is not acceptad (status = dont_agree) 
         * @param unknown $ipid
         * @param unknown $message
         * @param boolean $user
         */
        public function medication_pharma_request_messages($ipid, $message_info = array())
        {
            if(empty($ipid) || empty($message_info)){
                return;
            }
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
            
            $Tr = new Zend_View_Helper_Translate();
            
            // get patient details
            $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
            $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
            
 
            if(empty($message_info['text'])){
                return;
            } else{
                $message  = $message_info['text'];
            }
            $users = User::get_AllByClientid($clientid, array('us.manual_not_accepted_req_message', 'username'));
            
            //remove inactive and deleted, and the ones with clientid=0
            $users = array_filter($users, function($user) {
                return ( ! $user['isdelete']) && ( ! $user['isactive']) && ($user['clientid'] > 0) && ($user['UserSettings']['manual_not_accepted_req_message'] == 'yes');
            });
                
            if (empty($users)) {
                return; // no settings
            }
                
            //remove inactive and deleted, and the ones with clientid=0
            $users_to_notify = array_filter($users, function($user) {
                return strlen(trim($user['emailid']));
            });
                    
            $email_subject = $Tr->translate('mail_subject_action_pharma_request'). ' ' . date('d.m.Y H:i');
            $email_text = "";
            $email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
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
            
            foreach($users_to_notify as $k=> $notified_client_user)
            {
                $insert_data[$notified_client_user['id']] = array();
                $insert_data[$notified_client_user['id']]['sender'] = '0';
                $insert_data[$notified_client_user['id']]['clientid'] = $notified_client_user['clientid'];
                $insert_data[$notified_client_user['id']]['recipient'] = $notified_client_user['id'];
                $insert_data[$notified_client_user['id']]['msg_date'] = date("Y-m-d H:i:s", time());
                $insert_data[$notified_client_user['id']]['title'] = Pms_CommonData::aesEncrypt($Tr->translate('medication_pharme_request title').'(' . date("d.m.Y") . ')');
                $insert_data[$notified_client_user['id']]['content'] = Pms_CommonData::aesEncrypt($message);
                $insert_data[$notified_client_user['id']]['ipid'] = $ipid;
                $insert_data[$notified_client_user['id']]['source'] = 'pharma_request_not_accepted';
                $insert_data[$notified_client_user['id']]['create_date'] = date("Y-m-d", time());
                $insert_data[$notified_client_user['id']]['create_user'] = '0';
                $insert_data[$notified_client_user['id']]['read_msg'] = '0';
                
                $msg = new Messages();
                $msg->fromArray($insert_data[$notified_client_user['id']]);
                $msg->save();
                
                $mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($email_text)
                ->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
                ->addTo($notified_client_user['emailid'], $notified_client_user['last_name'] . ' ' . $notified_client_user['first_name'])
                ->setSubject($email_subject)
                ->send($mail_transport);
            
            }
            
        }
        
        
	}

?>