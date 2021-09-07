<?php

	require_once("Pms/Form.php");

	class Application_Form_Receipts extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function insert_receipt($post)
		{

			if(strlen($post['date']) > '0' && !empty($post['date']))
			{
				$receipt_date = date('Y-m-d 00:00:00', strtotime($post['date']));
			}
			else
			{
				$receipt_date = "0000-00-00 00:00:00";
			}

			if(strlen($post['datum']) > '0' && !empty($post['datum']))
			{
				$receipt_datum = date('Y-m-d', strtotime($post['datum']));
			}
			else
			{
				$receipt_datum = "0000-00-00";
			}

			if(strlen($post['stampusers']) > '0')
			{
				$expl_stamp = explode('-', $post['stampusers']);
				if(count($expl_stamp) == "2")
				{
					$stampuser = $expl_stamp[0];
					$stampid = $expl_stamp[1];
				}
				else
				{
					$stampuser = $post['stampusers'];
					$stampid = '0';
				}
			}

			if(strlen($post['birthdate']) > '0')
			{
				$expl_birthd = explode('.', $post['birthdate']);
				if(count($expl_birthd) == '3')
				{
					$birthdate = date('Y-m-d H:i:s', strtotime($post['birthdate']));
				}
				else
				{
					$birthdate = '0000-00-00';
				}
			}

			if($post['getiuhrfrei'])
			{
				$left_checkboxes_data = implode(',', $post['getiuhrfrei']);
			}
			else
			{
				$left_checkboxes_data = "";
			}

			$ins = new Receipts();
			$ins->ipid = $post['ipid'];
			$ins->client = $post['clientid'];
			$ins->type = $post['receipt_type'];
			$ins->date = $receipt_date;
			$ins->foc = $left_checkboxes_data;
			$ins->bvg = $post['bvg'];
			$ins->aid = $post['mttel'];
			$ins->vaccine = $post['soff'];
			$ins->bedarf = $post['bedaf'];
			$ins->price = $post['pricht'];
			$ins->insurance_name = $post['insurancecomname'];
			$ins->first_name = $post['patientfirstname'];
			$ins->last_name = $post['patientlastname'];
			$ins->street = $post['street'];
			$ins->zipcode = $post['zipcode'];
			$ins->city = $post['city'];
			$ins->birthdate = $birthdate;
			$ins->ins_kassenno = $post['kassenno'];
			$ins->ins_insuranceno = $post['insuranceno'];
			$ins->ins_status = $post['status'];
			$ins->bsnr = $post['betriebsstatten_nr'];
			$ins->lanr = $post['lanr'];
			$ins->datum = $receipt_datum;
			$ins->medication_1 = $post['med1'];
			$ins->custom_line_1 = $post['line1'];
			$ins->medication_2 = $post['med4'];
			$ins->custom_line_2 = $post['line2'];
			$ins->medication_3 = $post['med7'];
			$ins->custom_line_3 = $post['line3'];
			$ins->medication1line1 = $post['medication1line1'];
			$ins->medication2line2 = $post['medication4line2'];
			$ins->medication3line3 = $post['medication7line3'];
			$ins->stampuser = $stampuser;
			$ins->stampid = $stampid;
			$ins->isdelete = "0";
			if(!empty($post['receipt_status']))
			{
				$ins->receipt_status = $post['receipt_status'];
			}
			else
			{
				$ins->receipt_status = "gww";
			}
			//ISPC-2711 Ancuta
			if(!empty($post['print_save_receipt_btm_a'])){
// 			if( $post['receipt_type'] == 'kv_btm'){         //ISPC-2711 Lore 31.03.2021
			    $ins->btm_a_symbol = "1";
			} else{
			    $ins->btm_a_symbol = "0";
			}
			
			$ins->save();

			$receipt_id = $ins->id;


			if($receipt_id)
			{
				//prepare save data
				$ipid = $post['ipid'];
				$client = $post['clientid'];
				$data['user'] = $post['userid'];
				$data['receipt'] = $receipt_id;
				$data['date'] = $ins->create_date;
				$data['operation'] = "created";


				//save log
				$receipt_log = new Application_Form_ReceiptLog();
				$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
				
				
				//save each medication as an receipt_item
				$this->insert_receipt_items($receipt_id, $post);
				
			}

			return $receipt_id;
		}

		public function update_receipt($receipt = false, $post)
		{
			if($receipt)
			{

				if(strlen($post['date']) > '0' && !empty($post['date']))
				{
					$receipt_date = date('Y-m-d 00:00:00', strtotime($post['date']));
				}
				else
				{
					$receipt_date = "0000-00-00 00:00:00";
				}

				if(strlen($post['datum']) > '0' && !empty($post['datum']))
				{
					$receipt_datum = date('Y-m-d', strtotime($post['datum']));
				}
				else
				{
					$receipt_datum = "0000-00-00";
				}

				if(strlen($post['stampusers']) > '0')
				{
					$expl_stamp = explode('-', $post['stampusers']);
					if(count($expl_stamp) == "2")
					{
						$stampuser = $expl_stamp[0];
						$stampid = $expl_stamp[1];
					}
					else
					{
						$stampuser = $post['stampusers'];
						$stampid = '0';
					}
				}

				if(strlen($post['birthdate']) > '0')
				{
					$expl_birthd = explode('.', $post['birthdate']);
					if(count($expl_birthd) == '3')
					{
						$birthdate = date('Y-m-d H:i:s', strtotime($post['birthdate']));
					}
					else
					{
						$birthdate = '0000-00-00';
					}
				}
				
				if($post['getiuhrfrei'])
				{
					$left_checkboxes_data = implode(',', $post['getiuhrfrei']);
				}
				else
				{
					$left_checkboxes_data = "";
				}

				$upd = Doctrine::getTable('Receipts')->findOneByIdAndClient($receipt, $post['clientid']);

				//added pseudofix $post['ipid'] == $upd->ipid 
				if($upd && $post['ipid'] == $upd->ipid)
				{
					$upd->ipid = $post['ipid'];
					$upd->client = $post['clientid'];
					$upd->type = $post['receipt_type'];
					$upd->date = $receipt_date;
					$upd->foc = $left_checkboxes_data;
					$upd->bvg = $post['bvg'];
					$upd->aid = $post['mttel'];
					$upd->vaccine = $post['soff'];
					$upd->bedarf = $post['bedaf'];
					$upd->price = $post['pricht'];
					$upd->insurance_name = $post['insurancecomname'];
					$upd->first_name = $post['patientfirstname'];
					$upd->last_name = $post['patientlastname'];
					$upd->street = $post['street'];
					$upd->zipcode = $post['zipcode'];
					$upd->city = $post['city'];
					$upd->birthdate = $birthdate;
					$upd->ins_kassenno = $post['kassenno'];
					$upd->ins_insuranceno = $post['insuranceno'];
					$upd->ins_status = $post['status'];
					$upd->bsnr = $post['betriebsstatten_nr'];
					$upd->lanr = $post['lanr'];
					$upd->datum = $receipt_datum;
					$upd->medication_1 = $post['med1'];
					$upd->custom_line_1 = $post['line1'];
					$upd->medication_2 = $post['med4'];
					$upd->custom_line_2 = $post['line2'];
					$upd->medication_3 = $post['med7'];
					$upd->custom_line_3 = $post['line3'];
					$upd->medication1line1 = $post['medication1line1'];
					$upd->medication2line2 = $post['medication4line2'];
					$upd->medication3line3 = $post['medication7line3'];
					$upd->stampuser = $stampuser;
					$upd->stampid = $stampid;
					$upd->isdelete = "0";
					$ins->receipt_status = $post['receipt_status']; //wtf is this?
					if(!empty($post['print_save_receipt_btm_a'])){
// 					if( $post['receipt_type'] == 'kv_btm'){         //ISPC-2711 Lore 31.03.2021
					    $upd->btm_a_symbol = "1";
					} else{
					    $upd->btm_a_symbol = "0";
					}
					$upd->save();


					$receipt_id = $upd->id;

					if($receipt_id)
					{
						//prepare save data
						$ipid = $post['ipid'];
						$client = $post['clientid'];
						$data['user'] = $post['userid'];
						$data['receipt'] = $receipt_id;
						$data['date'] = date('Y-m-d H:i:s', time());
						$data['operation'] = "edited";


						//save log
						$receipt_log = new Application_Form_ReceiptLog();
						$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
						
						//save each medication as an receipt_item
						$this->update_receipt_items($receipt_id , $post);
					}

					return $receipt_id;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function duplicate_receipt($client = false, $receipt = false, $ipid = false, $rezeptgebuhrenbefreiung = false)
		{
			if($client && $receipt && $ipid)
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				//dont write anyting on these excluded keys
				$excluded_duplicate_keys = array('id', 'change_user', 'change_date');

				$upd = Doctrine::getTable('Receipts')->findOneByIdAndClientAndIpid($receipt, $client, $ipid);
				if($upd)
				{
					$res_data = $upd->toArray();
					
					$dup = new Receipts();
					$current_date = date('Y-m-d H:i:s', time());

					foreach($res_data as $k_elem => $v_elem_value)
					{
						if(!in_array($k_elem, $excluded_duplicate_keys))
						{
							if($k_elem == "create_user")
							{
								$v_elem_value = $logininfo->userid;
							}
							else if($k_elem == "create_date" || $k_elem == "date" || $k_elem == "datum")
							{
								$v_elem_value = $current_date;
							}
							else if($k_elem == "isduplicated")
							{
								$v_elem_value = "1";
							}
							else if($k_elem == "source")
							{
								$v_elem_value = $receipt;
							}
							else if($k_elem == "receipt_status")
							{
								//green, white, white
								$v_elem_value = "gww";
							}
							else if($k_elem == "foc")
							{
								$left_checkboxes = explode(',', $v_elem_value);
								if(!in_array($rezeptgebuhrenbefreiung, $left_checkboxes))
								{
									if($rezeptgebuhrenbefreiung == '1')
									{
										if(in_array('2', $left_checkboxes))
										{
											$left_checkboxes = array_diff( $left_checkboxes, array('2'));
										}
										array_push($left_checkboxes, $rezeptgebuhrenbefreiung);
									}
									if($rezeptgebuhrenbefreiung == '2')
									{
										if(in_array('1', $left_checkboxes))
										{
											$left_checkboxes = array_diff( $left_checkboxes, array('1'));
										}
										array_push($left_checkboxes, $rezeptgebuhrenbefreiung);
									}
									$v_elem_value = implode(',', $left_checkboxes);
								}
							}

							$dup->{$k_elem} = $v_elem_value;
						}
					}
					$dup->save();


					$duplicated_id = $dup->id;

					if($duplicated_id)
					{
						//prepare save data
						$ipid = $ipid;
						$client = $logininfo->clientid;
						$data['user'] = $logininfo->userid;
						$data['receipt'] = $duplicated_id;


						//add source field!
						$data['source'] = $receipt;
						$data['date'] = date('Y-m-d H:i:s', time());
						$data['operation'] = "duplicated";


						//save log
						$receipt_log = new Application_Form_ReceiptLog();
						$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
						
						//TODO-3766 Lore 20.01.2021
						//save each medication as an receipt_item
						$this->insert_receipt_items_duplicate($duplicated_id, $res_data);
					}
				}
			}
		}

		public function delete_receipt($client = false, $receipt = false, $data)
		{
			if($client && $receipt)
			{
				$q_del = Doctrine_Query::create()
					->update('Receipts')
					->set('isdelete', '1')
					->where('client="' . $client . '"')
					->andWhere('id="' . $receipt . '"');
				$q_del_res = $q_del->execute();

				$q_del = Doctrine_Query::create()
					->update('FaxUsersAssigned')
					->set('isdelete', '1')
					->where('client="' . $client . '"')
					->andWhere('receipt="' . $receipt . '"');
				$q_del_res = $q_del->execute();

				$q_del = Doctrine_Query::create()
					->update('PrintUsersAssigned')
					->set('isdelete', '1')
					->where('client="' . $client . '"')
					->andWhere('receipt="' . $receipt . '"');
				$q_del_res = $q_del->execute();

				//prepare save data
				$ipid = $data['ipid'];
				$client = $client;
				$data['user'] = $data['userid'];
				$data['receipt'] = $receipt;
				$data['date'] = date('Y-m-d H:i:s', time());
				$data['operation'] = "deleted";

				//save log
				$receipt_log = new Application_Form_ReceiptLog();
				$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $data);
			}
		}

		public function send_assigned_users_todos($client = false, $post = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$receipts = new Receipts();
			$pmaster = new PatientMaster();

			if($client && $post)
			{
				$encrypted_id = Pms_Uuid::encrypt(Pms_CommonData::getIdfromIpid($post['ipid']));

				//get patient details
				$patient_details = $pmaster->get_multiple_patients_details(array($post['ipid']));

				//get receipt details
				$receipt_details = $receipts->get_receipt($post['receipt']);

				$excluded_users[] = "99999999999999999";
				if(empty($post['assign_users']))
				{
					$post['assign_users'][] = '9999999999999999999';
				}

				//duplicate todos check
				$q = Doctrine_Query::create()
					->select('')
					->from('ToDos')
					->where('client_id = "' . $client . '"')
					->andWhereIn('user_id', $post['assign_users'])
					->andWhere('record_id = "' . $post['receipt'] . '"')
					->andWhere('triggered_by = "newreceipt_' . $post['assign_users_frm'] . '"')
					->andWhere('ipid LIKE "' . $post['ipid'] . '"');
				$q_res = $q->fetchArray();

				if($q_res)
				{
					foreach($q_res as $k_todo => $v_todo)
					{
						$excluded_users[] = $v_todo['user_id'];
					}
				}

				//eliminate excluded users and get remaining users details
//				$remaining_users = array_diff($post['assign_users'], $excluded_users);
//				$users_details = $users->getMultipleUserDetails($remaining_users);
				$user_additional_info = array();
				foreach($post['assign_users'] as $k_assigned => $v_assigned_user)
				{
					$user_additional_info[] =  'u'.$v_assigned_user;
				}
				//insert only what is not allready added
				foreach($post['assign_users'] as $k_assigned => $v_assigned_user)
				{
					$patient_name = array();
					$medications_str = array();
					if(!in_array($v_assigned_user, $excluded_users))
					{
						$receipt_date = date('d.m.Y', strtotime($receipt_details['date']));

						//receipt patient name
						if(strlen(trim(rtrim($patient_details[$post['ipid']]['first_name']))) > '0')
						{
							$patient_name[] = $patient_details[$post['ipid']]['first_name'];
						}

						if(strlen(trim(rtrim($patient_details[$post['ipid']]['last_name']))) > '0')
						{
							$patient_name[] = $patient_details[$post['ipid']]['last_name'];
						}

						//receipt medications
						if(strlen(trim(rtrim($receipt_details['medication_1']))) > '0')
						{
							$medications_str[] = $receipt_details['medication_1'];
						}

						if(strlen(trim(rtrim($receipt_details['medication_2']))) > '0')
						{
							$medications_str[] = $receipt_details['medication_2'];
						}

						if(strlen(trim(rtrim($receipt_details['medication_3']))) > '0')
						{
							$medications_str[] = $receipt_details['medication_3'];
						}

						$receipt_type_value = array("1" => "drucken", "2" => "faxen");

						$patient_name_str = implode(" ", $patient_name);
						$final_medications_str = implode(", ", $medications_str);

						$todo_link = APP_BASE . 'patientformnew/receiptpinew?id=' . $encrypted_id . '&rpid=' . $post['receipt'];

						$todo_message = '<a href="' . $todo_link . '">';
						$todo_message .= 'Rezept: ' . $patient_name_str . ' (' . $final_medications_str . ') ' . $receipt_type_value[$post['assign_users_frm']] . ' bis ' . $receipt_date;
						$todo_message .= '</a>';

						$curent_date = date('Y-m-d H:i:s', time());

						$todos[] = array(
							'client_id' => $client,
							'user_id' => $v_assigned_user,
							'group_id' => '0',
							'ipid' => $post['ipid'],
							'todo' => $todo_message,
							//1 = print user assigned || 2 => fax user assigned
							'triggered_by' => 'newreceipt_' . $post['assign_users_frm'],
							'isdelete' => '0',
							'iscompleted' => '0',
							'record_id' => $post['receipt'],
							'create_date' => $curent_date,
							'until_date' => $receipt_details['date'],
							'additional_info' => implode(";",$user_additional_info) 
						);
					}
				}

				if(!empty($todos))
				{
					$collection = new Doctrine_Collection('ToDos');
					$collection->fromArray($todos);
					$collection->save();
				}
			}
			else
			{
				return false;
			}
		}

		public function update_receipt_status($data)
		{
			$receipt_log = new Application_Form_ReceiptLog();
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if(!empty($data['receipt']) && !empty($data['status']))
			{
				$upd = Doctrine::getTable('Receipts')->findOneById($data['receipt']);
				if($upd)
				{
					$upd_arr = $upd->toArray();

					$new_status = $data['status'];
					$old_status = $upd_arr['receipt_status'];

					$upd->receipt_status = $new_status;
					$upd->save();


					//get old values
					$old_middle_value = substr($old_status, '1', '1');
					$old_last_value = substr($old_status, '2', '1');

					//get new values
					$new_middle_value = substr($new_status, '1', '1');
					$new_last_value = substr($new_status, '2', '1');

					$todo_data['receipt'] = $data['receipt'];

					//print todos
					if($new_middle_value != $old_middle_value)
					{
						$todo_data['type'] = "1"; //print todos type
						$todo_data['identifier'] = "newreceipt_" . $todo_data['type'];

						if($new_middle_value == "g")
						{
							//(done) print todos
							$todo_data['done'] = "1";
							
							//(done) remove assigned print users
							$remove_print_users = Receipts::remove_receipt_assigned_users($data['receipt'], $logininfo->clientid, "print");
							
						}
						else if($new_middle_value == "r")
						{
							//(undone) print todos
							$todo_data['done'] = "0";
						}
					}
					//fax todos
					else if($new_last_value != $old_last_value)
					{
						$todo_data['type'] = "2"; //fax todos type
						$todo_data['identifier'] = "newreceipt_" . $todo_data['type'];

						if($new_last_value == "g")
						{
							//(done) fax todos
							$todo_data['done'] = "1";
							
							//(done) remove assigned fax users
							$remove_fax_users = Receipts::remove_receipt_assigned_users($data['receipt'], $logininfo->clientid, "fax");
						}
						else if($new_last_value == "r")
						{
							//(undone) fax todos
							$todo_data['done'] = "0";
						}
					}

					//do update
					$this->update_todos($todo_data);

					if($data['silent'] != "1" || empty($data['silent']))
					{
						//prepare save data
						$ipid = $upd_arr['ipid'];
						$client = $logininfo->clientid;
						$log_data['user'] = $logininfo->userid;
						$log_data['receipt'] = $data['receipt'];
						$log_data['date'] = date('Y-m-d H:i:s', time());
						$log_data['operation'] = "sc";
						$log_data['old_status'] = $old_status;
						$log_data['new_status'] = $new_status;

						//save log
						$write_receipt_log = $receipt_log->insert_receipt_log($ipid, $client, $log_data);
					}

					return true;
				}
				else
				{
					return false;
				}
			}
		}

		private function update_todos($todo_data = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$client = $logininfo->clientid;
			$user = $logininfo->userid;	

			if($todo_data)
			{
				if($todo_data['receipt'] != '0')
				{
					if($todo_data['done'] == "1")
					{
						$complete_date = date('Y-m-d H:i:s', time());
					}
					else
					{
						$complete_date = "0000-00-00 00:00:00";
					}

					$q = Doctrine_Query::create()
						->update('ToDos')
						->set('iscompleted', '"' . $todo_data['done'] . '"')
						->set('complete_date', '"' . $complete_date . '"')
						->set('complete_user', '"' . $user . '"')
						->where('client_id = "' . $client . '"')
						->andWhere('isdelete = "0"')
						->andWhere('triggered_by LIKE "' . $todo_data['identifier'] . '"')
						->andWhere('record_id = "'.$todo_data['receipt'].'"');
					$q->execute();
				}
			}
		}

		
		
		
		private function insert_receipt_items($receipt_id = 0, $post = array())
		{
			//save each medication as an receipt_item
			$receipt_item_array =  array();

			//we now have just 3 rows with this names .... @todo : change to array[] so we don't have this mess of med 1,4,7
			$medication_items =  $post['medication_items'];
				
			$medication_items[0] = array(
					'medication' => $post['med1'],
					'custom_line' => $post['line1'],
					'pzn' => $medication_items[0]['pzn'],
					'source' => $medication_items[0]['source'],
					'dbf_id' => $medication_items[0]['dbf_id'],
			);
			$medication_items[1] = array(
					'medication' => $post['med4'],
					'custom_line' => $post['line2'],
					'pzn' => $medication_items[1]['pzn'],
					'source' => $medication_items[1]['source'],
					'dbf_id' => $medication_items[1]['dbf_id'],
			);
			$medication_items[2] = array(
					'medication' => $post['med7'],
					'custom_line' => $post['line3'],
					'pzn' => $medication_items[2]['pzn'],
					'source' => $medication_items[2]['source'],
					'dbf_id' => $medication_items[2]['dbf_id'],
			);
				
			foreach ($medication_items as $row) {
				//@todo: this IF must be changed
				if ( trim($row['medication' ]) != "" ) {
					
					$receipt_item_array[] = array(
						"receipt_id"	=> $receipt_id,
						"medication"	=> trim($row['medication' ]),
						"custom_line"	=> trim($row['custom_line' ]),
						"pzn"			=> $row['pzn'],
						"source"		=> $row['source'],
						"dbf_id"		=> $row['dbf_id'],
					);
				}
			}

			if ( ! empty($receipt_item_array) ) {
				$receipt_items = new Application_Form_ReceiptItems();
				$receipt_items->insert_collection_receipt_item( $receipt_item_array);
			}
		}
		
		private function update_receipt_items($receipt_id = 0, $post = array())
		{
			//set isdeleted the old items
			if ( (int)$receipt_id > 0 ) {
				$a_f_ri = new Application_Form_ReceiptItems();
				$a_f_ri->delete_receipt_items( $receipt_id);
			}
			
			//insert new items
			$this->insert_receipt_items($receipt_id, $post);
		}
		
		
		//TODO-3766 Lore 20.01.2021
		private function insert_receipt_items_duplicate($receipt_id = 0, $post = array())
		{
		    //save each medication as an receipt_item
		    $receipt_item_array =  array();
		    
		    //we now have just 3 rows with this names .... @todo : change to array[] so we don't have this mess of med 1,4,7
		    $medication_items =  $post['medication_items'];
		    
		    $medication_items[0] = array(
		        'medication' => $post['medication_1'],
		        'custom_line' => $post['custom_line_1'],
		        'pzn' => $medication_items[0]['medication1line1'],
		        'source' => $medication_items[0]['source'],
		        'dbf_id' => $medication_items[0]['dbf_id'],
		    );
		    $medication_items[1] = array(
		        'medication' => $post['medication_2'],
		        'custom_line' => $post['custom_line_2'],
		        'pzn' => $medication_items[1]['medication2line2'],
		        'source' => $medication_items[1]['source'],
		        'dbf_id' => $medication_items[1]['dbf_id'],
		    );
		    $medication_items[2] = array(
		        'medication' => $post['medication_3'],
		        'custom_line' => $post['custom_line_3'],
		        'pzn' => $medication_items[2]['medication3line3'],
		        'source' => $medication_items[2]['source'],
		        'dbf_id' => $medication_items[2]['dbf_id'],
		    );
		    
		    foreach ($medication_items as $row) {
		        //@todo: this IF must be changed
		        if ( trim($row['medication' ]) != "" ) {
		            
		            $receipt_item_array[] = array(
		                "receipt_id"	=> $receipt_id,
		                "medication"	=> trim($row['medication' ]),
		                "custom_line"	=> trim($row['custom_line' ]),
		                "pzn"			=> $row['pzn'],
		                "source"		=> $row['source'],
		                "dbf_id"		=> $row['dbf_id'],
		            );
		        }
		    }
		    
		    if ( ! empty($receipt_item_array) ) {
		        $receipt_items = new Application_Form_ReceiptItems();
		        $receipt_items->insert_collection_receipt_item( $receipt_item_array);
		    }
		}
		
	}

?>