<?php

class Application_Form_MedicationPatientHistory extends Pms_Form {

	public function InsertData($post)
	{
//		print_r($post);
// 		exit;
//		$post['amount_source'] = p || u
		$Tr = new Zend_View_Helper_Translate();
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$ipid = Pms_CommonData::getIpid($decid);
		$userid = $logininfo->userid;
		$user_details = User::getUserDetails($post['selectUser']);
		if(strlen($post['date'])>'0')
		{
			$done_date = date('Y-m-d', strtotime($post['date'])).' '.$post['time'].':00';
			
			//direct validation here, if failed redirect/exit?
			//ispc 1864 p.9
			//documenting a method BEFORE that SEAL_DATE is not possible.
			
			$mcss  = new MedicationClientStockSeal();
			$mcss_seal_date = $mcss->get_client_last_seal($clientid);
			if ( ! empty($mcss_seal_date['seal_date'])
					&& strtotime($done_date) < strtotime($mcss_seal_date['seal_date'])
			) {
				//btm seal_date error
				echo json_encode(array("seal_date_error"=>"dont re-post to this page!"));
				return false;
			}
			
			
		}
		else
		{
			$done_date = date("Y-m-d H:i:00",time());
		}
		
		if(($post['selectUser'] == '0' || empty($post['selectUser'])) && $post['method'] <= '6')
		{
			$string_key = 'tresor';
		}
		elseif($post['selectUser'] > '0' && ($post['method'] <= '6' || $post['method'] == '12'))
		{
			$string_key = 'user';
		}
		elseif($post['method'] >= '7' && $post['method'] != '12')
		{
			$string_key = 'patient';
		}

// 		$methodsarr = array("1" => "Übergabe von", "2" => "Lieferung", "3" => "Sonstiges", "4" => "Übergabe an", "5" => "Abgabe an Patienten", "6" => "Sonstiges", "7" => "Abgabe an Patienten", "8" => "Verbrauch", "9" => "Rücknahme von Patienten", '10' => '', '11' => '', '12' => '');
		$methodsarr = Medication::get_methodsarr();
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

		$amount = "";
		$comment = "";
		if($post['operation'] == 1)
		{ //+
			$amount = $post['amount'];
//			$comment = "Der Bestand von " . $post['medication']['name'] . " wurde erhöht. Benutzer: " . $user_details[0]['last_name'] . ' ' . $user_details[0]['first_name'] . ". Menge: " . $post['amount'] . ".  Methode: " . $grid_methods_arr[$string_key][$post['method']] . '.';
			$comment = 'Der Bestand von '. $post['medication']['name'] .' des Patienten wurde durch '.$grid_methods_arr[$string_key][$post['method']].' von '. $user_details[0]['last_name'] . ' ' . $user_details[0]['first_name'] . ' um '.$post['amount'].' erhöht.';
		}
		else if($post['operation'] == 2)
		{ //-
			$amount = (abs($post['amount']) * (-1));
//			$comment = "Der Bestand von " . $post['medication']['name'] . " wurde reduziert. Benutzer: " . $user_details[0]['last_name'] . ' ' . $user_details[0]['first_name'] . ". Menge: " . $post['amount'] . ". Methode: " . $grid_methods_arr[$string_key][$post['method']] . '.';
			
			if(strlen($post['sonstige_more']) > 0 ){
			    $sonstige_more = " (".$post['sonstige_more'].")";
			} else{
			    $sonstige_more = "";
			}
			
			
			if($post['amount_source'] == 'u')
			{
				$comment = 'Der Bestand von ' . $post['medication']['name'] . ' von ' . $user_details[0]['last_name'] . ' ' . $user_details[0]['first_name'] . ' wurde um ' . $post['amount'] . ' durch ' . $grid_methods_arr[$string_key][$post['method']] . ' reduziert';
			}
			elseif($post['amount_source'] == 'p')
			{
				$comment = 'Der Bestand von ' . $post['medication']['name'] . ' des Patienten wurde um ' . $post['amount'] . ' durch ' . $grid_methods_arr[$string_key][$post['method']] . $sonstige_more .'  reduziert';
			}
			else
			{
				$comment = 'Der Bestand von ' . $post['medication']['name'] . ' des Patienten wurde um ' . $post['amount'] . ' durch ' . $grid_methods_arr[$string_key][$post['method']] . $sonstige_more .' reduziert';
			}
		}

		//from ipid btm icon
		if($post['method'] == "7")
		{ //  (+) pacient <- user
			//scriere in user and group with  -amount, ipid, userid, medid
			if($post['selectUser'] != 0)
			{
				$clientHis = new MedicationClientHistory();
				$clientHis->userid = $post['selectUser'];
				$clientHis->clientid = $clientid;
				$clientHis->medicationid = $post['medicationid'];
				$clientHis->amount = (abs($post['amount']) * (-1));
				$clientHis->methodid = $post['method'];
				$clientHis->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
				
				$clientHis->ipid = $ipid;
				$clientHis->done_date = $done_date;
				$clientHis->save();

				//users stocks
				$client_history_id = $clientHis->id;
			}
			else
			{
				$clientStock = new MedicationClientStock();
				$clientStock->userid = $post['selectUser'];
				$clientStock->clientid = $clientid;
				$clientStock->medicationid = $post['medicationid'];
				$clientStock->amount = (abs($post['amount']) * (-1));
				$clientStock->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
				
				$clientStock->methodid = $post['method'];
				$clientStock->ipid = $ipid;
				$clientStock->done_date = $done_date;
				$clientStock->save();

				//group stocks
				$client_stock_id = $clientHis->id;
			}

			//insert the medicationid into the patient table, using methodid=0 (zero amount)
			$check_new_entry = MedicationPatientHistory::check_new_entries($clientid, $ipid, $post['medicationid']);
			if(!$check_new_entry)
			{
					
				$comm_new_entry = $post['medication']['name'] . ' wurde dem BTM Buch hinzugefügt.<br />';
				$comment = $comm_new_entry . $comment;
					
				$post['add']['hidd_medication'][1] = $post['medicationid'];
				$client_hist_form = new Application_Form_MedicationPatientHistory();
				$client_hist_form->insertNewMedication($post, '0', false);
			}

			$frm = new MedicationPatientHistory();
			$frm->clientid = $clientid;
			$frm->ipid = $ipid;
			$frm->userid = $post['selectUser'];
			$frm->medicationid = $post['medicationid'];
			$frm->amount = $amount;
			$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
			
			$frm->methodid = $post['method'];
			$frm->done_date = $done_date;
			$frm->save();

			$patient_stock_id = $frm->id;
			
			
			if ($client_history_id>0 && $clientHis instanceof MedicationClientHistory) {
				
				$clientHis->patient_stock_id = $patient_stock_id;
				$clientHis->save();
			}
			
			
		}
	
		
		if($post['method'] == "8")
		{
			//check patient stock
			$patient_stock_data = MedicationPatientHistory::get_patient_stock($clientid, $ipid, $post['medicationid']);


			if($post['amount_source'] == 'u')
			{
				//user as source
				//check if patient has the medi in patient btm
				$check_new_entry = MedicationPatientHistory::check_new_entries($clientid, $ipid, $post['medicationid']);
				//substract from user
				$clientHis = new MedicationClientHistory();
				$clientHis->userid = $post['selectUser'];
				$clientHis->clientid = $clientid;
				$clientHis->medicationid = $post['medicationid'];
				$clientHis->amount = (abs($post['amount']) * (-1));
				$clientHis->methodid = $post['method'];
				$clientHis->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
				
				$clientHis->ipid = $ipid;
				$clientHis->source = $post['amount_source'];
				$clientHis->done_date = $done_date;
				$clientHis->save();

				//users stocks
				$client_history_id = $clientHis->id;

				//add the medication as newly added with coresponding amount
				if(!$check_new_entry)
				{
					if($post['method'] == '8')
					{
						$comm_new_entry = $post['medication']['name'] . ' wurde dem BTM Buch hinzugefügt.<br />';
						$comment = $comm_new_entry . $comment;
					}
					
					// TODO-2125 ISPC:: BTM error (bug?) Ancuta on 19.02.2019 
					// add empty line first - as the medication does not exist in patient stock
					$post['add']['hidd_medication'][1] = $post['medicationid'];
					$client_hist_forms = new Application_Form_MedicationPatientHistory();
					$client_hist_forms->insertNewMedication($post, '0', false);
					// -- 
					
					
					$post['add']['hidd_medication'][1] = $post['medicationid'];
					$client_hist_form = new Application_Form_MedicationPatientHistory();
					$ipid_stock_id = $client_hist_form->insertNewMedication($post, $post['amount'], true, $post['method'] );

					// this 2 linkked ids have different method id's
					if ($client_history_id>0 && $clientHis instanceof MedicationClientHistory){
						$clientHis->patient_stock_id = $ipid_stock_id;
						$clientHis->save();
					}
					
					//remove the used (the newly inserted amount)
					$frm = new MedicationPatientHistory();
					$frm->clientid = $clientid;
					$frm->userid = $userid;
					$frm->ipid = $ipid;
					$frm->medicationid = $post['medicationid'];
					$frm->amount = (abs($post['amount']) * (-1));
					$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
					
					$frm->methodid = $post['method'];
					$frm->source = $post['amount_source'];
					$frm->done_date = $done_date;
					$frm->self_id = $ipid_stock_id;
					$frm->save();

					$patient_stock_id = $frm->id;

					//re-update first row
					$frm1 = Doctrine::getTable('MedicationPatientHistory')->find($ipid_stock_id);
					if ($frm1 instanceof MedicationPatientHistory) {
					      //TODO-2125 ISPC:: BTM error (bug?) Ancuta on 19.02.2019
						  $frm1->verlauf_hide = '1';
						  $frm1->done_date = $done_date;
						  // --
						$frm1->self_id = $patient_stock_id;
						$frm1->save();
					}
					
				}
				else
				{
					//remove the used amount (and show it in patient verlauf when removing from user)
					$frm1 = new MedicationPatientHistory();
					$frm1->clientid = $clientid;
					$frm1->userid = $userid;
					$frm1->ipid = $ipid;
					$frm1->medicationid = $post['medicationid'];
					$frm1->amount = abs($post['amount']);
					$frm1->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
					
					$frm1->methodid = $post['method'];
					$frm1->verlauf_hide = '1';
					$frm1->source = $post['amount_source'];
					$frm1->done_date = $done_date;
					$frm1->save();
					$self_id = $frm1->id;

					$frm = new MedicationPatientHistory();
					$frm->clientid = $clientid;
					$frm->userid = $userid;
					$frm->ipid = $ipid;
					$frm->medicationid = $post['medicationid'];
					$frm->amount = (abs($post['amount']) * (-1));
					$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
					
					$frm->methodid = $post['method'];
					$frm->source = $post['amount_source'];
					$frm->done_date = $done_date;
					$frm->self_id = $self_id;
					$frm->save();

					$patient_stock_id = $frm->id;
					
					//re-update first row
					$frm1->self_id = $patient_stock_id;
					$frm1->save();
					
					
				}
			}
			elseif($post['amount_source'] == 'p')
			{
				//patient as source
				$frm = new MedicationPatientHistory();
				$frm->clientid = $clientid;
				$frm->userid = $userid;
				$frm->ipid = $ipid;
				$frm->medicationid = $post['medicationid'];
				$frm->amount = (abs($post['amount']) * (-1));
				$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
				
				$frm->methodid = $post['method'];
				$frm->source = $post['amount_source'];
				$frm->done_date = $done_date;
				$frm->save();

				$patient_stock_id = $frm->id;
			}
		}

		if($post['method'] == "9")
		{ // (-) pacient -> user
			//scriere in user and group with  +amount, ipid, userid, medid
			if($post['selectUser'] != 0)
			{
				$clientHis = new MedicationClientHistory();
				$clientHis->userid = $post['selectUser'];
				$clientHis->clientid = $clientid;
				$clientHis->medicationid = $post['medicationid'];
				$clientHis->amount = $post['amount'];
				$clientHis->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
				
				$clientHis->methodid = $post['method'];
				$clientHis->ipid = $ipid;
				$clientHis->done_date = $done_date;
				$clientHis->save();

				//users stocks
				$client_history_id = $clientHis->id;
			}
			else
			{
				$clientStock = new MedicationClientStock();
				$clientStock->userid = $post['selectUser'];
				$clientStock->clientid = $clientid;
				$clientStock->medicationid = $post['medicationid'];
				$clientStock->amount = $post['amount'];
				$clientStock->btm_number = $post['btm_number'];           //ISPC-2768 Lore 05.01.2021
				
				$clientStock->methodid = $post['method'];
				$clientStock->ipid = $ipid;
				$clientStock->done_date = $done_date;
				$clientStock->save();

				//group stocks
				$client_stock_id = $clientHis->id;
			}
			$frm = new MedicationPatientHistory();
			$frm->clientid = $clientid;
			$frm->userid = $userid;
			$frm->to_userid = $post['selectUser'];
			$frm->ipid = $ipid;
			$frm->medicationid = $post['medicationid'];
			$frm->amount = (abs($post['amount']) * (-1));
			$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
			
			$frm->methodid = $post['method'];
			$frm->done_date = $done_date;
			$frm->save();

			$patient_stock_id = $frm->id;
			
			if ($client_history_id>0 && $clientHis instanceof MedicationClientHistory) {
				$clientHis->patient_stock_id =  $patient_stock_id;
				$clientHis->save();
			}
			
		}

		// (+) pacient (PA2 - Liferung)
		if($post['method'] == "10")
		{

			$check_new_entry = MedicationPatientHistory::check_new_entries($clientid, $ipid, $post['medicationid']);
			if(!$check_new_entry)
			{
					
				$comm_new_entry = $post['medication']['name'] . ' wurde dem BTM Buch hinzugefügt.<br />';
				$comment = $comm_new_entry . $comment;
					
				$post['add']['hidd_medication'][1] = $post['medicationid'];
				$client_hist_form = new Application_Form_MedicationPatientHistory();
				$client_hist_form->insertNewMedication($post, '0', false);
			}

			$frm = new MedicationPatientHistory();
			$frm->clientid = $clientid;
			$frm->userid = $userid;
			$frm->ipid = $ipid;
			$frm->medicationid = $post['medicationid'];
			$frm->amount = ($post['amount']);
			$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
			
			$frm->methodid = $post['method'];
			$frm->done_date = $done_date;
			$frm->save();

			$patient_stock_id = $frm->id;
		}

		// (-) pacient (PR3 - Sonstige)
		if($post['method'] == "11")
		{
			$frm = new MedicationPatientHistory();
			$frm->clientid = $clientid;
			$frm->userid = $userid;
			$frm->ipid = $ipid;
			$frm->medicationid = $post['medicationid'];
			$frm->amount = (abs($post['amount']) * (-1));
			$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
			
			$frm->methodid = $post['method'];
			$frm->done_date = $done_date;
			$frm->sonstige_more = $post['sonstige_more'];
			$frm->save();

			$patient_stock_id = $frm->id;
		}

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
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("Q");
		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("btm_patient_icon");
		$cust->isserialized = "1";
		$cust->recorddata = serialize($data);
		$cust->user_id = $userid;
		$cust->done_date = $done_date;
		$cust->save();
	}

	public function insertNewMedication($post, $amount = '0', $comment = true , $forced_methodid = 0)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$frm = new MedicationPatientHistory();
		$frm->clientid = $clientid;
		$frm->ipid = $ipid;
		$frm->medicationid = $post['add']['hidd_medication'][1];
		$frm->amount = $amount; //0 = add as new! maybe we have an amount field latter? LE: added amount as var
		$frm->btm_number = $post['btm_number'];           //ISPC-2768 Lore 04.01.2021
		
		// 0 was the default, $forced_methodid was introduced to pass method 8 from used to patient direct consumption
		$frm->methodid = $forced_methodid; //add as new 
		
		if ($forced_methodid == 8) {
			$frm->source = $post['amount_source'];
			$frm->userid = $post['selectUser'];
		}
		
		$frm->save();

		$patient_stock_id = $frm->id;

		if($patient_stock_id)
		{
			if($amount == '0')
			{
				$data['ammount'] = $amount;
				$data['medicationid'] = $post['add']['hidd_medication'][1];
				$data['patient_stock_id'] = $patient_stock_id;

				if($comment)
				{
					//verlauf entry medis added as new
					$comment = $post['medication']['name'] . " wurde dem BTM Buch hinzugefügt";
					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("Q");
					$cust->course_title = Pms_CommonData::aesEncrypt($comment);
					$cust->user_id = $userid;
					$cust->tabname = Pms_CommonData::aesEncrypt("btm_patient_icon");
					$cust->isserialized = "1";
					$cust->recorddata = serialize($data);
					$cust->save();
				}
			}
		}
		return $patient_stock_id;
	}

}

?>