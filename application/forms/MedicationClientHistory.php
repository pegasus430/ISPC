<?php

	class Application_Form_MedicationClientHistory extends Pms_Form {

		public function InsertData($post)
		{
//			print_r($post);
// 			exit;
			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if(count($post['patientselect']) > 0)
			{
				foreach($post['patientselect'] as $keyp => $patientsel)
				{
					if($patientsel != "0")
					{
						$patientSelect = $post['patientselect'][$keyp];
					}
				}
				if(empty($patientSelect))
				{
					$patientSelect = "0";
				}
			}

			if(strlen($post['medicationid']))
			{
				$medi = Doctrine::getTable('Medication')->find($post['medicationid']);
				$mediarray = $medi->toArray();
				$post['medication'] = $mediarray;
			}

			if(($post['fromuserid'] == '0' || empty($post['fromuserid'])) && $post['method'] <= '6')
			{
				$string_key = 'tresor';
			}
			elseif($post['fromuserid'] > '0' && ($post['method'] <= '6' || $post['method'] == '12'))
			{
				$string_key = 'user';
			}
			elseif($ch['method'] >= '7' && $ch['method'] != '12')
			{
				$string_key = 'patient';
			}

// 			$methodsarr = array("1" => "Übergabe von", "2" => "Lieferung", "3" => "Sonstiges", "4" => "Übergabe an", "5" => "Abgabe an Patienten", "6" => "Sonstiges", "7" => "Abgabe an Patienten", "8" => "Verbrauch", "9" => "Rücknahme von Patienten", '10' => '', '11' => '', '12' => '');
			$methodsarr =  Medication::get_methodsarr();
			//new method names for verlauf grid
			foreach($methodsarr as $k_method => $v_method)
			{
				if($Tr->translate('btm_tresor_' . $k_method) != 'btm_tresor_' . $k_method)
				{
					$grid_methods_arr['tresor'][$k_method] = $Tr->translate('btm_tresor_' . $k_method);
				}

				if($Tr->translate('btm_user_' . $k_method) != 'btm_user_' . $k_method)
				{
					$grid_methods_arr['user'][$k_method] = $Tr->translate('btm_user_' . $k_method);
				}

				if($Tr->translate('btm_patient_' . $k_method) != 'btm_patient_' . $k_method)
				{
					$grid_methods_arr['patient'][$k_method] = $Tr->translate('btm_patient_' . $k_method);
				}

				asort($grid_methods_arr['tresor']);
				asort($grid_methods_arr['user']);
				asort($grid_methods_arr['patient']);
			}

			if($post['operation'] == 1)
			{ //+ doc takes from patient
				$amount = $post['amount'];
//			$comment = "Der Bestand von " . $post['medication']['name'] . " wurde reduziert. Benutzer: " . $post['user_fullname'][$post['fromuserid']] . ". Methode: " . $grid_methods_arr[$string_key][$post['method']] . '.';
				$comment = 'Der Bestand von ' . $post['medication']['name'] . ' des Patienten wurde durch ' . $grid_methods_arr[$string_key][$post['method']] . ' von ' . $post['user_fullname'][$post['fromuserid']] . ' um ' . $post['amount'] . ' erhöht.';
			}
			else if($post['operation'] == 2)
			{ //- doc gives to
				$amount = (abs($post['amount']) * (-1));
//			$comment = "Der Bestand von " . $post['medication']['name'] . " wurde erhöht. Benutzer:" . $post['user_fullname'][$post['fromuserid']] . ". Methode: " . $grid_methods_arr[$string_key][$post['method']] . '.';
				$comment = 'Der Bestand von ' . $post['medication']['name'] . ' von ' . $post['user_fullname'][$post['fromuserid']] . ' wurde um ' . $post['amount'] . ' durch ' . $grid_methods_arr[$string_key][$post['method']] . ' reduziert';
			}


			if($post['operation'] == 1)
			{
				//plus sign
				if($_REQUEST['dbga'])
				{
					print_r($post);
					exit;
				}
				
				if($post['method'] > 1 && $post['method'] < 4)
				{ //not transfer
					//die("add to group?");
					if(!empty($post['fromuserid']))
					{ //not stock
						$frm = new MedicationClientHistory();
						$frm->userid = $post['fromuserid'];
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = $post['amount'];
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->stid = "0";
						$frm->save();
						
						if($userid != $post['fromuserid'])
						{
							$res_usr = MedicationClientStock::user_ammount_changed_notification($clientid, $post);
						}
					}
					else
					{ //add in stock
						$frm = new MedicationClientStock();
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = $post['amount'];
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->userid = 0;
						if($post['method'] == '3')
						{
							$frm->sonstige_more = $post['sonstige_more'];
						}
						$frm->save();

						$res = MedicationClientStock::client_stock_notification($clientid, $post);
					}
				}
				else if($post['method'] == '12')
				{ //transfer from patient 2 user
					
					$ipid = $post['doc_patientselect'];

					//add to clicked "+" user
					//scriere in user and group with  +amount, ipid, userid, medid
					if($post['fromuserid'] != 0)
					{
						$clientHis = new MedicationClientHistory();
						$clientHis->userid = $post['fromuserid'];
						$clientHis->clientid = $clientid;
						$clientHis->medicationid = $post['medicationid'];
						$clientHis->amount = abs($post['amount']);
						$clientHis->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$clientHis->methodid = $post['method'];
						$clientHis->ipid = $ipid;
						$clientHis->save();

						$client_history_id = $clientHis->id;
						
						if($userid != $post['fromuserid'])
						{
							$res_usr = MedicationClientStock::user_ammount_changed_notification($clientid, $post);
						}
					}

					//substract from patient
					$frm = new MedicationPatientHistory();
					$frm->clientid = $clientid;
					$frm->userid = $userid;
					$frm->to_userid = $post['fromuserid'];
					$frm->ipid = $ipid;
					$frm->medicationid = $post['medicationid'];
					$frm->amount = (abs($post['amount']) * (-1));
					$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
					
					$frm->methodid = $post['method'];
					$frm->save();

					$patient_stock_id = $frm->id;
					

					//verlauf to patient Q
					if($client_history_id)
					{
						$clientHis->patient_stock_id = $patient_stock_id;
						$clientHis->save();
												
						$data['client_history_id'] = $client_history_id;
					}

					if($patient_stock_id)
					{
						$data['patient_stock_id'] = $patient_stock_id;
					}
					$data['ammount'] = $post['amount'];
					$data['medicationid'] = $post['medicationid'];

					//add verlauf entry with operation, medi name, amount
					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("Q");
					$cust->course_title = Pms_CommonData::aesEncrypt($comment);
					$cust->tabname = Pms_CommonData::aesEncrypt("btm_master");
					$cust->isserialized = "1";
					$cust->recorddata = serialize($data);
					$cust->user_id = $userid;
					$cust->save();
					//verlauf to patient Q
				}
				else
				{ //transfer
					//amount =  se reduce la user/group selectat, adauga la user/group la care s-a dat click pe +
					if($post['userselect'] == 0)
					{
						//scadere de la stock
						$frm = new MedicationClientStock();
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = (abs($post['amount']) * (-1));
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->userid = $post['fromuserid'];
						$frm->save();
						$stocid = $frm->id;
						
						$res = MedicationClientStock::client_stock_notification($clientid, $post);
					}
					
					//from user into stock
					if(empty($post['fromuserid']))
					{ //from stock
						//adaugare de la stock
						$frm = new MedicationClientStock();
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = $post['amount'];
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->userid = $post['userselect'];
						$frm->save();
						$stocid = $frm->id;
						
						$res = MedicationClientStock::client_stock_notification($clientid, $post);
					}

					//from user into stock
					if($post['userselect'] != 0)
					{

						//scadere de la userul selectat
						$frm_1 = new MedicationClientHistory();
						$frm_1->userid = $post['userselect'];
						$frm_1->clientid = $clientid;
						$frm_1->medicationid = $post['medicationid'];
						$frm_1->amount = (abs($post['amount']) * (-1));
						$frm_1->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm_1->methodid = $post['method'];
						if(!empty($stocid) && $stocid != 0)
						{
							$frm_1->stid = $stocid;
						}
						else
						{
							$frm_1->stid = 0;
						}
						$frm_1->save();
						
						$self_id = $frm_1->id; //self_id was introduced for user2user transfer, to "connect" the 2 rows
						
//						if($userid != $post['fromuserid'])
//						{
							$res_usr = MedicationClientStock::user_ammount_changed_notification($clientid, $post);
//						}
					}

					//this method notification is handled in user_ammount_changed_notification(both users get a message)
					if(!empty($post['fromuserid']))
					{ //+ on user to user
						//adaugare la user unde a dat click pe +
						$frm = new MedicationClientHistory();
						$frm->userid = $post['fromuserid'];
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = $post['amount'];
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						if(isset($stocid))
						{
							$frm->stid = $stocid;
						}
						else
						{
							$frm->stid = 0;
						}
						
						if(isset($self_id)) {
							$frm->self_id = $self_id;
						}
						$frm->save();
						
						//update for user2user .. update the user we received from
						if(isset($self_id)) {
							
							$frm_1->self_id = $frm->id;
							$frm_1->save();
							
						}
						
						
					}
				}
			}
			elseif($post['operation'] == 2)
			{//minus sign
				if($post['method'] > 5)
				{ //not transfer
					if(!empty($post['fromuserid']))
					{ //not stock
						$frm = new MedicationClientHistory();
						$frm->userid = $post['fromuserid'];
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = (abs($post['amount']) * (-1));
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->stid = "0";
						$frm->sonstige_more = $post['sonstige_more'];
						$frm->save();
						
						if($userid != $post['fromuserid'])
						{
							$res_usr = MedicationClientStock::user_ammount_changed_notification($clientid, $post);
						}
					}
					else
					{ //add in stock
						$frm = new MedicationClientStock();
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = (abs($post['amount']) * (-1));
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->ipid = $patientSelect;
						$frm->userid = 0;
						if($post['method'] == '6')
						{
							$frm->sonstige_more = $post['sonstige_more'];
						}
						$frm->save();
						
						$res = MedicationClientStock::client_stock_notification($clientid, $post);
					}
				}
				if($post['method'] == 4)
				{ //transfers
					//amount =  se reduce la user/group selectat, adauga la user/group la care s-a dat click
					if($post['userselect'] == 0 && $patientSelect == 0)
					{
						//adaugare la stock
						$frm = new MedicationClientStock();
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = $post['amount'];
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->userid = $post['fromuserid'];
						$frm->ipid = $patientSelect;
						$frm->save();
						$stocid = $frm->id;
						
						$res = MedicationClientStock::client_stock_notification($clientid, $post);
					}
					if(empty($post['fromuserid']))
					{ //from stock
						//scadere de la stock
						$frm = new MedicationClientStock();
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = (abs($post['amount']) * (-1));
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->userid = $post['userselect'];
						$frm->ipid = $patientSelect;
						$frm->save();
						$stocid = $frm->id;

						$res = MedicationClientStock::client_stock_notification($clientid, $post);
					}

					if($post['userselect'] != 0 && $patientSelect == 0)
					{
						//adaugare la userul selectat
						$frm_1 = new MedicationClientHistory();
						$frm_1->userid = $post['userselect'];
						$frm_1->clientid = $clientid;
						$frm_1->medicationid = $post['medicationid'];
						$frm_1->amount = $post['amount'];
						$frm_1->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm_1->methodid = $post['method'];
						$frm_1->ipid = $patientSelect;
						if(isset($stocid))
						{
							$frm_1->stid = $stocid;
						}
						else
						{
							$frm_1->stid = 0;
						}
						$frm_1->save();
						$self_id = $frm_1->id;
						
//						if($userid != $post['fromuserid'])
//						{
							$res_usr = MedicationClientStock::user_ammount_changed_notification($clientid, $post);
//						}
					}

					
					if((!empty($post['fromuserid']) || $patientSelect != 0))
					{ //-
						//scadere de la user unde a dat click pe -
						$frm = new MedicationClientHistory(); 						
						$frm->userid = $post['fromuserid'];
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = (abs($post['amount']) * (-1));
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->ipid = $patientSelect;
						if(isset($stocid))
						{
							$frm->stid = $stocid;
						}
						else
						{
							$frm->stid = 0;
						}
						
						//user2user
						if(isset($self_id)) {
							$frm->self_id = $self_id;
							
						}
						
						$frm->save();
						//user2user
						if(isset($self_id)) {
							$frm_1->self_id = $frm->id;
							$frm_1->save();
						}
						
						
					}
				}
				if($post['method'] == 5)
				{

					if(($patientSelect != 0 || !empty($patientSelect)) && ($post['userselect'] == 0 || empty($post['userselect'])))
					{

						$frm = new MedicationPatientHistory();
						$frm->clientid = $clientid;
						$frm->ipid = $patientSelect;
						$frm->userid = $post['fromuserid'];
						$frm->medicationid = $post['medicationid'];
						$frm->amount = abs($post['amount']);
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->save();

						$patient_stock_id = $frm->id;
					}

					if(empty($post['fromuserid']))
					{ //from stock
						//scadere de la stock
						$frm = new MedicationClientStock();
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = (abs($post['amount']) * (-1));
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->userid = $post['userselect'];
						$frm->ipid = $patientSelect;
						$frm->save();
						$stocid = $frm->id;
						
						if (isset($patient_stock_id)) {
							
						}

						$client_stock_id = $frm->id;
						
						$res = MedicationClientStock::client_stock_notification($clientid, $post);
					}
					else
					{ //- transfer from user to patient
						//scadere de la user unde a dat click pe -
						$frm = new MedicationClientHistory();
						$frm->userid = $post['fromuserid'];
						$frm->clientid = $clientid;
						$frm->medicationid = $post['medicationid'];
						$frm->amount = (abs($post['amount']) * (-1));
						$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
						
						$frm->methodid = $post['method'];
						$frm->ipid = $patientSelect;
						
						$frm->patient_stock_id = $patient_stock_id;
							
											
						$frm->save();

						$client_history_id = $frm->id;
						
						if($userid != $post['fromuserid'])
						{
							$res_usr = MedicationClientStock::user_ammount_changed_notification($clientid, $post);
						}
					}

					//verlauf to patient Q
					if($client_history_id)
					{
						$data['client_history_id'] = $client_history_id;
					}
					if($client_stock_id)
					{
						$data['client_stock_id'] = $client_stock_id;
					}
					if($patient_stock_id)
					{
						$data['patient_stock_id'] = $patient_stock_id;
					}
					$data['ammount'] = $post['amount'];
					$data['medicationid'] = $post['medicationid'];
					//add verlauf entry with operation, medi name, amount
					$cust = new PatientCourse();
					$cust->ipid = $patientSelect;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("Q");
					$cust->course_title = Pms_CommonData::aesEncrypt($comment);
					$cust->tabname = Pms_CommonData::aesEncrypt("btm_master");
					$cust->isserialized = "1";
					$cust->recorddata = serialize($data);
					$cust->user_id = $userid;
					$cust->save();
					//verlauf to patient Q
				}
			}
		}

		public function insertNewMedication($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if(!empty($post['add']['hidd_medication'][1]))
			{
				$medid = $post['add']['hidd_medication'][1];
			}
			else
			{
				$medid = $post['newhidd_medication'][0];
			}

			$frm = new MedicationClientStock();
			$frm->clientid = $clientid;
			$frm->medicationid = $medid;
			$frm->amount = 0;
			$frm->methodid = 0; //add as new
			$frm->save();

			return $frm->id;
//			print_r("stock altered #1 create medi");
//			exit;
		}

	}

?>