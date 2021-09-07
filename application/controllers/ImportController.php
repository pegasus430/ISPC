<?php
// Maria:: Migration ISPC to CISPC 08.08.2020
	class ImportController extends Zend_Controller_Action {

		public function init()
		{
			
		}

		public function importfileAction()
		{
			$this->view->display = "none";
			$logininfo = new Zend_Session_Namespace('Login_Info');


			if($this->getRequest()->isPost())
			{
                // Lore 06.03.2020  // Maria:: Migration ISPC to CISPC 08.08.2020
			    if(strlen($_SESSION['filename']) > 0 && ($_POST['filetype'] != substr($_SESSION['filename'], strpos($_SESSION['filename'], '.' ) + 1 , 3 )) )
    			{
    			    $this->view->error_message = $this->view->translate('file type not ').$_POST['filetype'];
    			    $error = 4;
    			}
    			
				if(strlen($_SESSION['filename']) < 1)
				{
					$this->view->error_message = $this->view->translate('uploadcsvfile');
					$error = 1;
				}
				if(strlen($_POST['filetype']) < 1)
				{
					$this->view->error_filetype = $this->view->translate('selectfiletype');
					$error = 2;
				}
				if(strlen($_POST['tablename']) < 1)
				{
					$this->view->error_tablename = $this->view->translate('selecttabletoimport');
					$error = 3;
				}

				$dir = "uploadfile/";
				$filename = $dir . $_SESSION['filename'];

				if(!is_writable($filename))
				{
					$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
					$log = new Zend_Log($writer);
					$log->info('Load import file Procedure Error => ' . $filename . " does not exist!.");
					$error = 1;
				}

				$this->view->file_name = $_SESSION['filename'];

				
				if($error == 0)
				{

					$this->view->tablename = $_POST['tablename'];
					$this->view->hdnheading = $_POST['heading'];
					$this->view->hdnduplicate = $_POST['duplicate'];
					$this->view->hdnfiletype = $_POST['filetype'];
					
					$this->view->hdnremove_existing = $_POST['remove_existing'];
					
					
					//ISPC-2302 @Lore 17.10.2019
					$_POST['referal_tab'] = '';
					if($_POST['tablename'] == 'spender'){
					    $_POST['tablename'] = 'Member';
					    $_POST['referal_tab'] = 'donors';
					}
					
					$_POST['csv_category'] = '';
					if($_POST['tablename'] == 'ClientOrderMaterials_Arzneimittel'){
					    $_POST['tablename'] = 'ClientOrderMaterials';
					    $_POST['csv_category'] = 'drugs';
					} elseif($_POST['tablename'] == 'ClientOrderMaterials_Hilfsmittel'){
					    $_POST['tablename'] = 'ClientOrderMaterials';
					    $_POST['csv_category'] = 'auxiliaries';
					}elseif($_POST['tablename'] == 'ClientOrderMaterials_Pflegehilfsmittel'){
					    $_POST['tablename'] = 'ClientOrderMaterials';
					    $_POST['csv_category'] = 'nursingauxiliaries';
					}elseif($_POST['tablename'] == 'ClientOrderMaterials_Verbandsstoffe'){
					    $_POST['tablename'] = 'ClientOrderMaterials';
					    $_POST['csv_category'] = 'dressings';
					}
									
					
					$tablecolumns = array();
					
					$current_table = Doctrine_Core::getTable($_POST['tablename']);   
					$current_table_columns = $current_table->getColumns();
					$tablecolumns[$_POST['tablename']] = $current_table_columns;					
					
					if($_POST['tablename'] == 'Voluntaryworkers'){
					    unset($tablecolumns['Voluntaryworkers']['status']);
					    unset($tablecolumns['Voluntaryworkers']['status_color']);
					    unset($tablecolumns['Voluntaryworkers']['img_deleted']);
					    unset($tablecolumns['Voluntaryworkers']['parent_id']);
					    unset($tablecolumns['Voluntaryworkers']['img_path']);   
					    
					    $table_extra_voluntaryworkers_color_statuses = new VwColorStatuses();
					    $tablecolumns['VwColorStatuses'] = $table_extra_voluntaryworkers_color_statuses->getTable()->getColumns();				    
					}
					
					if($_POST['tablename'] == 'Member'){
					    unset($tablecolumns['Member']['family_id']);
					    unset($tablecolumns['Member']['payment_method_id']);
					    unset($tablecolumns['Member']['img_path']);
					    unset($tablecolumns['Member']['external_id']); 

					    $table_extra_member2memberships = new Member2Memberships();
					    $tablecolumns['Member2Memberships'] = $table_extra_member2memberships->getTable()->getColumns();
					    unset($tablecolumns['Member2Memberships']['member']);
					    
					    $table_extra_members_sepa_settings = new MembersSepaSettings();
					    $tablecolumns['MembersSepaSettings'] = $table_extra_members_sepa_settings->getTable()->getColumns();
					    
					    if($_POST['referal_tab'] == 'donors'){			        
					        $table_extra_member_donations = new MemberDonations();
					        $tablecolumns['MemberDonations'] = $table_extra_member_donations->getTable()->getColumns();
					        unset($tablecolumns['MemberDonations']['member']);
					    }    
					}
					
					if($_POST['tablename'] == 'ClientOrderMaterials'){
					    unset($tablecolumns['ClientOrderMaterials']['category']);
					}
					
					
					$tableclm = array();
					$tableclm['-'] = "TABLE-COL";
					
					//ISPC-2302 @Lore 17.10.2019
					foreach($tablecolumns as $key => $keyv )
					{
					    foreach($keyv as $key_id => $val){		
					        
					        if($key_id != 'id' && $key_id != 'clientid' && $key_id != 'create_user' && $key_id != 'create_date' && $key_id != 'change_user' && $key_id != 'change_date' && $key_id != 'isdelete' && $key_id != 'vw_id' && $key_id != 'memberid' && $key_id != 'member2membershipsid'  )
					        {
					            $tableclm[$key][$key.'|'.$key_id] = $key_id;
					        }
					    }
					}
				

					if($_POST['filetype'] == 'csv')
					{
						if(strlen($_POST['delimitercsv']) > 0)
						{
							$delimiter = $_POST['delimitercsv'];
							$this->view->hdndelimiter = $_POST['delimitercsv'];
						}
						else
						{
							$delimiter = ";";
							$this->view->hdndelimiter = ";";
						}


						$row = 0;
						$handle = fopen($filename, "r");

						while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
						{

							for($i = 0; $i <= count($data); $i++)
							{
								if($row == 0)
								{
									if(strlen($data[$i]) > 0)
									{
										$column[$i] = $data[$i];
									}
									else
									{
										$column[$i] = "-";
									}
								}
							}
							$row++;
						}

						foreach($column as $keycolm => $valcolm)
						{
							$this->view->drop_down .= "<td><b>" . $valcolm . "</b></td>";
							if( in_array($_POST['tablename'] ,array("FamilyDoctor",'HealthInsurance')) ) {
    							$this->view->tabledown .= "<td>" . $this->view->formSelect('columns[' . $keycolm.']', $_POST['column_' . $keycolm], NULL, $tableclm) . "</td>";
							} else{
    							$this->view->tabledown .= "<td>" . $this->view->formSelect('column_' . $keycolm, $_POST['column_' . $keycolm], NULL, $tableclm) . "</td>";
							}
						}
						fclose($handle);
						$this->view->display = "";
						unset($_SESSION['filename']);
					}

					if($_POST['filetype'] == 'xml')
					{
						$xml = simplexml_load_file($filename, 'SimpleXMLElement', LIBXML_NOCDATA);
						foreach($xml as $key => $val)
						{
							foreach($val as $kkey => $vval)
							{

								$xmlcolumn[str_replace(" ", "_", $kkey)] = $kkey;
							}
						}

						foreach($xmlcolumn as $keycolm => $valcolm)
						{
							$this->view->drop_down .= "<td><b>" . $valcolm . "</b></td>";
							$this->view->tabledown .= "<td>" . $this->view->formSelect('column_' . $keycolm, $_POST['column_' . $keycolm], NULL, $tableclm) . "</td>";
						}

						$this->view->display = "";
					}
				}
			}
			$this->view->icddisplay = "none";
			$this->view->otherdisplay = "block";
		}

		public function insertimportedAction()
		{
			$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
			$log = new Zend_Log($writer);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$filename = $_POST['file_name'];
			$handle = fopen("uploadfile/" . $filename, "r");

			
			$row = 0;
			$status = array();
			$count = 0;

			$_POST['referal_tab'] = '';
			if($_POST['tablename'] == 'spender'){
			    $_POST['tablename'] = 'Member';
			    $_POST['referal_tab'] = 'donors';
			}
			
			$_POST['csv_category'] = '';
			if($_POST['tablename'] == 'ClientOrderMaterials_Arzneimittel'){
			    $_POST['tablename'] = 'ClientOrderMaterials';
			    $_POST['csv_category'] = 'drugs';
			} elseif($_POST['tablename'] == 'ClientOrderMaterials_Hilfsmittel'){
			    $_POST['tablename'] = 'ClientOrderMaterials';
			    $_POST['csv_category'] = 'auxiliaries';
			}elseif($_POST['tablename'] == 'ClientOrderMaterials_Pflegehilfsmittel'){
			    $_POST['tablename'] = 'ClientOrderMaterials';
			    $_POST['csv_category'] = 'nursingauxiliaries';
			}elseif($_POST['tablename'] == 'ClientOrderMaterials_Verbandsstoffe'){
			    $_POST['tablename'] = 'ClientOrderMaterials';
			    $_POST['csv_category'] = 'dressings';
			}
			
			if($_POST['hdnfiletype'] != 'xml')
			{
				if(strlen($_POST['hdndelimiter']) > 0)
				{
					$delimiter = $_POST['hdndelimiter'];
				}
				else
				{
					$delimiter = ";";
				}

				if($_POST['tablename'] == "FamilyDoctor" && $_POST['hdnremove_existing'] == 1)
				{
					$res = Doctrine_Query::create()
						->update('FamilyDoctor')
						->set('valid_till', "'" . date("Y-m-d H:i:s") . "'")
						->where("clientid= ?", $logininfo->clientid);
					$resexc = $res->execute();
				}

				if($_POST['tablename'] == "Locations")
				{
					$locs = new Locations();
					$loctypearr = $locs->getLocationTypes();
					$loctypearr = array_flip($loctypearr);
				}
				

				$columns = 0;
				while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
				{

					if($row == 0)
					{
						$columns = count($data);
					}
					if($_POST['hdnheading'] == '1' && $row == 0)
					{
						$row++;
						continue;
					}
					else
					{
						$row++;
					}
					$tablename = $_POST['tablename'];

					$table = Doctrine_Core::getTable($tablename);
					
					// Ancuta 03.04.2019
					$table_columns_details = $table->getColumns();
					$table_columns = array_keys($table_columns_details);
					$post_columns_order = array_flip($_POST['columns']);
					// --
					//dd($table_columns,$data,$_POST,$post_columns_order);
					
					if($tablename == "FamilyDoctor")
					{

					    // Ancuta 03.04.2019
					    if(!empty($data[$post_columns_order['FamilyDoctor|first_name']])  && !empty($data[$post_columns_order['FamilyDoctor|first_name']]) ){
    						$finder = $table->findOneByFirstNameAndLastNameAndClientid($data[$post_columns_order['FamilyDoctor|first_name']], $data[$post_columns_order['FamilyDoctor|last_name']], $logininfo->clientid);
					    }
						// -- 
						
						//$finder = $table->findOneByFirstNameAndLastName($data[0], $data[1]);
						// Commented on 03.04.2019-> This is stupid::
						// $data[0] is not first name -> this has to be taken from post to find the correct position in data! 
						// Also added client id - !!!!!  to find only in client 

					}
					elseif($tablename == "HealthInsurance")
					{
                        
					    // Ancuta 03.04.2019
					    if(!empty($data[$post_columns_order['HealthInsurance|name']]) && $data[$post_columns_order['HealthInsurance|name2']]){
    					    $finder = $table->findOneByNameAndName2AndClientid($data[$post_columns_order['HealthInsurance|name']], $data[$post_columns_order['HealthInsurance|name2']], $logininfo->clientid);
					    }
					    // --
					    
					    // $finder = $table->findOneByNameAndName2($data[0], $data[
					    // Commented on 03.04.2019-> This is stupid::
					    // $data[0] is not name -> this has to be taken from post to find the correct position in data!
					    // Also added client id - !!!!!  to find only in client
					}
					elseif($tablename == "Specialists")        //ISPC-2302 @Lore 14.10.2019
					{   										    
					    $col_speciality = substr(array_search('Specialists|medical_speciality',$_POST),7);
					    $col_spec_name = $data[$col_speciality];
					    
					    $types = Doctrine_Query::create()
					    ->select('*')
					    ->from('SpecialistsTypes')
					    ->where('clientid= ? ', $logininfo->clientid)
					    ->andWhere('name= ? ',$col_spec_name );
					    $types_res = $types->fetchArray();
					    $last_id_st = '';
					    
					    if(empty($types_res)){
					        $insert = new SpecialistsTypes();
					        $insert->clientid = $logininfo->clientid;
					        $insert->name = $col_spec_name;
					        $insert->save();
					        
					        $last_id_st = $insert->id;    // get last id inserted
					    } else {
					        foreach($types_res as $key_val){
					            $last_id_st = $key_val['id'];
					        }					        
					    }
					}
					elseif($tablename == "Member")        //ISPC-2302 @Lore 15.10.2019
					{
					    $col_membership = substr(array_search('Member2Memberships|membership',$_POST),7);
					    $col_spec_name = $data[$col_membership];
					    
					    $types = Doctrine_Query::create()
					    ->select('*')
					    ->from('Memberships')
					    ->where('clientid= ? ', $logininfo->clientid)
					    ->andWhere('membership= ? ',$col_spec_name );
					    $types_res = $types->fetchArray();
					    $last_id_ms ='';
					    
					    if(empty($types_res)){
					        $inst = new Memberships();
					        $inst->clientid = $logininfo->clientid;
					        $inst->membership = $col_spec_name;
					        $inst->shortcut = substr($col_spec_name,0,2);
					        $inst->save();
					        
					        $last_id_ms = $inst->id;    // get last id inserted
					    } else {
					        foreach($types_res as $key_val){
					            $last_id_ms = $key_val['id'];
					        }
					    }
					}
					
					
					if(!empty($finder->id) && $_POST['hdnduplicate'] == 1)
					{
						$table = Doctrine::getTable($tablename)->find($finder->id);
						if($tablename == "FamilyDoctor" || $tablename == "HealthInsurance")
						{
    						for($i = 0; $i < $columns; $i++)
    						{
    							if($_POST['columns'][$i] != '-')
    							{
    								//$field = $_POST['columns'][$i];
    							    
    							    //ISPC-2302 @Lore 17.10.2019
    							    $field_total = $_POST['columns'][$i];
    							    $darr =  explode('|',$field_total);
    							    $table_where_field = $darr[0];
    							    $field = $darr[1];
    							    
    								$table->$field = $data[$i];
    								if($tablename == "FamilyDoctor")
    								{
    									$table->valid_till = "0000-00-00";
    								}
    							}
    						}
						    
						} else
						{
						    
    						for($i = 0; $i < $columns; $i++)
    						{
    							if($_POST['column_' . $i] != '-')
    							{
    								//$field = $_POST['column_' . $i];
    								
    							    //ISPC-2302 @Lore 17.10.2019
    							    $field_total = $_POST['column_' . $i];
    							    $darr =  explode('|',$field_total);
    							    $table_where_field = $darr[0];
    							    $field = $darr[1];
    							    
    								$table->$field = $data[$i];
    								if($tablename == "FamilyDoctor")
    								{
    									$table->valid_till = "0000-00-00";
    								}
    							}
    						}
						}
						$status['updated']++;
						$count++;
						
					}
					else
					{
						$table = new $tablename;
						
						if($tablename == "FamilyDoctor" || $tablename == "HealthInsurance")
						{
    						for($i = 0; $i < $columns; $i++)
    						{
    							if($_POST['columns'][$i] != '-')
    							{
    								//$field = $_POST['columns'][$i];
    							    
    							    //ISPC-2302 @Lore 17.10.2019
    							    $field_total = $_POST['columns'][$i];
    							    $darr =  explode('|',$field_total);
    							    $table_where_field = $darr[0];
    							    $field = $darr[1];
    							    
    								$value = $data[$i];
    
    								$table->$field = $value;
    							}
    						}
						    
						} else{
						    //ISPC-2302 @Lore 16.10.2019
						    if($tablename == "Voluntaryworkers" ){
						        $table_col_array = array();
						        
						        $table_extra_voluntaryworkers_color_statuses = new VwColorStatuses();
						        $table_col_array['VwColorStatuses'] = $table_extra_voluntaryworkers_color_statuses->getTable()->getColumns();
						        
						    }
						    if($tablename == "Member" ){
						        $table_col_array = array();
						        
						        $table_extra_member2memberships = new Member2Memberships();
						        $table_col_array['Member2Memberships'] = $table_extra_member2memberships->getTable()->getColumns();
						        
						        $table_extra_members_sepa_settings = new MembersSepaSettings();
						        $table_col_array['MembersSepaSettings'] = $table_extra_members_sepa_settings->getTable()->getColumns();
						    
						        if($_POST['referal_tab'] = 'donors'){
						            $table_extra_member_donations = new MemberDonations();
						            $table_col_array['MemberDonations'] = $table_extra_member_donations->getTable()->getColumns();
						        }
						        
						    }
						    
						    $values_arr = array();
						    
						    for($i = 0; $i < $columns; $i++)
						    {
						        if($_POST['column_' . $i] != '-')
						        {
						            
						            //ISPC-2302 @Lore 17.10.2019
						            $field_total = $_POST['column_' . $i];
						            $darr =  explode('|',$field_total);
						            $table_where_field = $darr[0];
						            $field = $darr[1];
						            					            
						            //$field = $_POST['column_' . $i];

						            if($tablename == "Locations" && $field == 'location')
						            {
						                $value = Pms_CommonData::aesEncrypt(trim($data[$i]));
						            }
						            elseif($tablename == "Locations" && $field == 'location_type')
						            {
						                $value = $loctypearr[trim($data[$i])];
						            }
						            else
						            {
						                $value = $data[$i];
						            }
						            
						            //ISPC-2302 @Lore 14.10.2019
						            if(in_array($field , $table_columns))
						            {
						                if (in_array($field ,array('birthd','birthdate','birth_date')))
						                {
						                    $table->$field = date('Y-m-d',strtotime($value));
						                } 
						                else 
						                {
						                    $table->$field = $value;
						                }
						            }
						            else
						            {
						                $list_colmns = array_keys($table_col_array[$table_where_field]);
						                
						                if (in_array($field , $list_colmns)) {
						                    
						                    $values_arr[$table_where_field][$field] =  $value;
						                    
						                }
						            }					            
						        }
						    }
						    
    						
						}
						$status['inserted']++;
						$count++;
					}

					if($_POST['tablename'] == "FamilyDoctor")
					{
						$table->valid_from = date("Y-m-d H:i:s");
					}
										
					if($tablename == "Locations")
					{
						$table->client_id = $logininfo->clientid;
					}
					else if($tablename != "MedicationIndex")
					{
						$table->clientid = $logininfo->clientid;
					}
					
					
					if($tablename == "HealthInsurance" && $logininfo->clientid > 0 )
					{
					    $table->onlyclients = "1"; // if client is selected -  also add - client condition
					}
					
					//ISPC-2302 @Lore 14.10.2019
					if($_POST['tablename'] == "Specialists")
					{
					    $table->medical_speciality = $last_id_st;
					    $table->indrop = "0";  // TODO-2917  ISPC : Facharzt-Import Ancuta 12.02.2020
					}
								
					//ISPC-2302 @Lore 14.10.2019
					if($_POST['tablename'] == "Voluntaryworkers")
					{
					    $table->status_color = '';
					    $vw_color_status = $table->status;
					    if (!empty($vw_color_status) && (strtolower($vw_color_status) == 'inaktiv' || strtolower($vw_color_status) == 'black' )){
					            $vw_color_status = "b";
					        
					    } else {
					        $vw_color_status = "g";
					    }
					    $table->status = "n";
					}
					
					//ISPC-2302 @Lore 29.10.2019
					if($_POST['tablename'] == "ClientOrderMaterials")
					{
					    $table->category = $_POST['csv_category'];
					}
					
					//============================//
					//============================//
					    $table->save();
					//============================//
					//============================//
					
					
					//ISPC-2302 @Lore 15.10.2019
					if($_POST['tablename'] == "Voluntaryworkers")
					{
					    $last_id_vw = $table->id;    // get last id inserted in Voluntaryworkers

					    $insrt_vwc = new VwColorStatuses();
					    $insrt_vwc->vw_id = $last_id_vw;
					    $insrt_vwc->clientid = $logininfo->clientid;
					    $insrt_vwc->status = $vw_color_status;
					    $insrt_vwc->start_date = ((isset($values_arr['VwColorStatuses']['start_date']) && !empty($values_arr['VwColorStatuses']['start_date'])) ? date('Y-m-d',strtotime($values_arr['VwColorStatuses']['start_date'])) : date("Y-m-d H:i:s"));
					    $insrt_vwc->end_date = ((isset($values_arr['VwColorStatuses']['end_date']) && !empty($values_arr['VwColorStatuses']['end_date'])) ? date('Y-m-d',strtotime($values_arr['VwColorStatuses']['end_date'])) : "0000-00-00");
					    $insrt_vwc->save();
					}
					
					//ISPC-2302 @Lore 15.10.2019
					if($_POST['tablename'] == "Member")
					{
					    $last_id_mb = $table->id;    // get last id inserted in Member
					    
					    $ins_mrt = new MemberReferalTab();
					    $ins_mrt->memberid = $last_id_mb;
					    $ins_mrt->clientid = $logininfo->clientid;
					    // TODO-2984 Loredana - 06.03.2020
					    if($_POST['referal_tab'] == 'donors'){
					        $ins_mrt->referal_tab = 'donors';
					    }else{
					        $ins_mrt->referal_tab = 'members';
					    }
					    $ins_mrt->save();
					    
					    $ins_mms = new Member2Memberships();
					    $ins_mms->clientid = $logininfo->clientid;
					    $ins_mms->member = $last_id_mb;
					    $ins_mms->membership = $last_id_ms;
					    $ins_mms->membership_price = $values_arr['Member2Memberships']['membership_price'];
					    $ins_mms->start_date = ((isset($values_arr['Member2Memberships']['start_date']) && !empty($values_arr['Member2Memberships']['start_date'])) ? date('Y-m-d',strtotime($values_arr['Member2Memberships']['start_date'])) : date("Y-m-d H:i:s"));
					    $ins_mms->end_date = ((isset($values_arr['Member2Memberships']['end_date']) && !empty($values_arr['Member2Memberships']['end_date'])) ? date('Y-m-d',strtotime($values_arr['Member2Memberships']['end_date'])) : "0000-00-00");
					    $ins_mms->save();
					    
					    if (isset($values_arr['MembersSepaSettings']['howoften']) || isset($values_arr['MembersSepaSettings']['when_day']) || isset($values_arr['MembersSepaSettings']['when_month']) || isset($values_arr['MembersSepaSettings']['amount']) )
					    {
					        $last_id_mms = $ins_mms->id;    // get last id inserted in Member2Memberships;
					        
					        $ins_mss = new MembersSepaSettings();
					        $ins_mss->clientid = $logininfo->clientid;
					        $ins_mss->memberid = $last_id_mb;
					        $ins_mss->member2membershipsid = $last_id_mms;
					        $ins_mss->howoften = $values_arr['MembersSepaSettings']['howoften'];
					        $ins_mss->when_day = $values_arr['MembersSepaSettings']['when_day'];
					        $ins_mss->when_month = $values_arr['MembersSepaSettings']['when_month'];
					        $ins_mss->amount = str_replace(',', '.', $values_arr['MembersSepaSettings']['amount']);
					        $ins_mss->save();
					    }
					    
					    if (isset($values_arr['MemberDonations']['donation_date']) || isset($values_arr['MemberDonations']['amount'])  )
					    {
					        $ins_md = new MemberDonations();
					        $ins_md->clientid = $logininfo->clientid;
					        $ins_md->member = $last_id_mb;
					        $ins_md->donation_date = $values_arr['MemberDonations']['donation_date'];
					        $ins_md->amount = str_replace(',', '.', $values_arr['MemberDonations']['amount']);
					        $ins_md->merged_parent = $values_arr['MemberDonations']['merged_parent'];
					        $ins_md->merged_slave = $values_arr['MemberDonations']['merged_slave'];
					        $ins_md->save();
					    }
					    
					}
					
				}
			}
			else
			{

				$xml = simplexml_load_file("uploadfile/" . $filename, 'SimpleXMLElement', LIBXML_NOCDATA);

				$columns = count($xml);
				$tablename = $_POST['tablename'];


				foreach($xml as $key => $val)
				{
					$table = new $tablename;
					foreach($val as $kkey => $vval)
					{
						$field = $_POST['column_' . $kkey];
						$table->$field = $vval;
					}
					$count++;
					if($_POST['tablename'] == "FamilyDoctor")
					{
						$table->valid_from = date("Y-m-d H:i:s");
					}
					$table->save();
				}
			}

			$this->view->error_message = $count . $this->view->translate('rowsimportdonesucessfully');
			$this->view->error_message .= "<br/>Updated entries: ".$status['updated'];
			$this->view->error_message .= "<br/>New entries: ".$status['inserted'];
		}

		public function importdiagnosisAction()
		{
			$this->_helper->viewRenderer('importfile');
			if($this->getRequest()->isPost())
			{

				//ini_set("upload_max_filesize", "10M");
				if(strlen($_POST['catalogue']) < 1)
				{
					$this->view->error_catalogue = $this->view->translate('providecataloguename');
					$error = 1;
				}
				if(strlen($_POST['icd_year']) < 1)
				{
					$this->view->error_year = $this->view->translate('provideyear');
					$error = 1;
				}
				if(strlen($_SESSION['filename']) < 1)
				{
					$this->view->error_filename = $this->view->translate('uploadcsvfile');
					$error = 1;
				}

				$filename = "uploadfile/" . $_SESSION['filename'];

				if(strlen($_POST['delimiter']) > 0)
				{
					$delimiter = $_POST['delimiter'];
				}
				else
				{
					$delimiter = ";";
				}


				if($error == 0)
				{
					$res = Doctrine_Query::create()
						->update('Diagnosis')
						->set('valid_till', "'" . date("Y-m-d H:i:s") . "'")
						->where("catalogue= ?", htmlentities($_POST["catalogue"]))
						->andWhere("icd_year= ?", $_POST["icd_year"]);
					$resexc = $res->execute();
					$handle = fopen($filename, "r");


					while(($data = fgetcsv($handle, '550', $delimiter, '"')) !== FALSE)
					{
						$data_count = count($data);
						if($data[2] == 1)
						{
							$import = new Diagnosis();
							$import->catalogue = htmlentities($_POST['catalogue']);
							$import->valid_from = date("Y-m-d H:i:s");
							$import->icd_year = $_POST['icd_year'];
							$import->detail_code = $data[1];
							$import->icd_primary = htmlentities($data[3]);
							$import->icd_star = htmlentities($data[4]);
							$import->icd_cross = htmlentities($data[5]);
							$import->description = htmlentities($data[6]);
							$import->save();
						}
						else if($data_count == '2')
						{
							$import = new Diagnosis();
							$import->catalogue = htmlentities($_POST['catalogue']);
							$import->valid_from = date("Y-m-d H:i:s");
							$import->icd_year = $_POST['icd_year'];
							$import->icd_primary = htmlentities($data[0]);
							$import->description = htmlentities($data[1]);
							$import->save();
						}
					}
					unset($_SESSION['filename']);
					fclose($handle);
					$this->view->error_message = $this->view->translate("importdonesucessfully");
				}
			}

			$this->view->icddisplay = "block";
			$this->view->otherdisplay = "none";
			$this->view->display = "none";
		}

		public function uploadifyAction()
		{
			$dir = APPLICATION_PATH . '/../public/uploadfile/';
			$ext = pathinfo($_FILES['qqfile']['name']);
			$ext = $ext['extension'];
			$filename = uniqid() . "." . $ext;

			//terminal import upload => do backup (filename = timestamp_client_user.original_ext)
			if($_REQUEST['terminal'] == '1')
			{
				$this->terminal_backup($_FILES);
			}

			if(!is_dir($dir))
			{
				if(mkdir($dir, "0777", false))
				{
					$err = 0;
				}
				else
				{
					$err = 1;
					$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
					$log = new Zend_Log($writer);
					$log->info('Upload Directory not exist and can`t be created! => ' . $dir . $filename . "[" . $_FILES['qqfile']['name'] . "].");
				}
			}

			if(is_writable($dir))
			{
				$err = 0;
			}
			else
			{
				$err = 1;

				$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
				$log = new Zend_Log($writer);
				$log->info('Upload Directory is not writeable => ' . $dir . $filename . "[" . $_FILES['qqfile']['name'] . "].");
			}

			$_SESSION['filename'] = $filename;

			if($err != 1)
			{
				move_uploaded_file($_FILES['qqfile']['tmp_name'], $dir . $filename);
				echo json_encode(array('success' => true));
			}
			else
			{
				echo json_encode(array(fail => "Upload error!"));
				$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
				$log = new Zend_Log($writer);
				$log->info('Upload Procedure Error => ' . $dir . $filename . "[" . $_FILES['qqfile']['name'] . "].");
			}
			exit;
		}

		public function terminalimportAction()
		{
			ini_set("auto_detect_line_endings", true);
			setlocale(LC_ALL, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$import_session = new Zend_Session_Namespace('importSession');
			$userid = $logininfo->userid;

			//csv validation settings
			$csv_cols = '18';
			$minimal_required = array('7', '10');
			//estabilish a delimiter if none was provided via post
			if(strlen(trim(rtrim($_POST['csvdelimiter']))) > 0)
			{
				$delimiter = trim(rtrim($_POST['csvdelimiter']));
				$this->view->delimiter = trim(rtrim($_POST['csvdelimiter']));
			}
			else
			{
				$delimiter = ";";
				$this->view->delimiter = ";";
			}


			if($import_session->userid == $userid)
			{
				$import_types = $import_session->import_type;
				$targeted_patients = $import_session->target_patient;
				$selected_data = $import_session->import_value;

				if(strlen($import_session->delimiter) > '0')
				{
					$delimiter = $import_session->delimiter;
					$this->view->delimiter = $delimiter;
				}



				$filename = $import_session->filename;
				$this->view->targeted_patients = $targeted_patients;
				$this->view->import_type = $import_types;


				$session_patients = $this->get_session_patients($targeted_patients);
			}

			//process post action
			if($this->getRequest()->isPost() || strlen($filename) != 0)
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$dir = "uploadfile/";
				if(strlen($filename) == 0)
				{
					$filename = $dir . $_SESSION['filename'];
				}
				else
				{
					$filename = $dir . $filename;
				}
				$error = '0';

				if(!is_writable($filename))
				{
					$this->view->error = 'file_open_error';
					$this->write_log('1', 'Load import file procedure error! File [' . $filename . '] does not exist!.');
					$error = 1;
				}

				if($_POST['cancel_import_value'] == '1')
				{
					//reset importSession namespace
					$import_session->userid = '';
					$import_session->target_patient = '';
					$import_session->import_value = '';
					$import_session->filename = '';
					$import_session->import_type = '';
					$import_session->delimiter = '';
					$this->_redirect(APP_BASE . 'import/terminalimport');
					exit;
				}


				if($error == '0')
				{
					$this->write_log('1', 'CSV Import: Start');
					$this->view->error = '';
					$handle = fopen($filename, "r");
					//parse csv into an array
					while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
					{
						foreach($data as $k_data => $v_data)
						{
							$data[$k_data] = htmlspecialchars($v_data);
						}


						$csv_data[] = $data;
					}
					fclose($handle);

					//validate obtained array
					$csv_data = $this->fix_csv($csv_data, $csv_cols);
					$valid_csv = $this->validate_csv($csv_data, $csv_cols, $minimal_required);

					//if valid, gather data
					if($valid_csv)
					{
						$this->view->csv_data = $csv_data;
						//set default import types in session
						if(empty($import_session->import_type))
						{
							foreach($csv_data as $k_row_csv => $v_row_csv)
							{
								$default_csv_row_import_types[$k_row_csv] = '2';
							}
							$import_session->import_types = $default_csv_row_import_types;
						}

						$found_patients = $this->gather_available_data($csv_data);

						//append session data to found patients array and create mapped_data
						ksort($session_patients);
						foreach($session_patients as $k_csv_ses_row => $v_csv_ses_data)
						{
							if(!empty($found_patients[$k_csv_row]))
							{
								$found_patients[$k_csv_row] = array();
							}


							$found_patients[$k_csv_ses_row][$v_csv_ses_data['id']] = $v_csv_ses_data;
							$found_patients[$k_csv_ses_row] = array_values($found_patients[$k_csv_ses_row]);
						}

						$this->view->mapped_data = $found_patients;
						$import_session->delimiter = $delimiter;
					}
					else
					{
						$this->view->error = 'file_validation_error';

						$import_session->target_patient = '';
						$import_session->import_value = '';
						$import_session->import_type = '';
						$import_session->delimiter = '';
					}
				}


				if($_POST['allow_import'] == '1')
				{
					$import_form = new Application_Form_Import();
					$import_form->import_handler($csv_data, $_POST);

					$import_session->userid = '';
					$import_session->target_patient = '';
					$import_session->import_value = '';
					$import_session->filename = '';
					$import_session->delimiter = '';
					$this->_redirect(APP_BASE . 'import/terminalimport?flg=suc');
					exit;
				}
			}
		}

		private function fix_csv($data = false, $col = '0')
		{
			if($data)
			{
				//clear incompleted (< 18 cols) or overhauled (> 18 cols) data rows 
				foreach($data as $k_data => $v_data)
				{
					if(count($data[$k_data]) != $col)
					{
						unset($data[$k_data]);

						$this->write_log('1', 'CSV Import: [Validation "Fix Attempt"] removed line ' . ($k_data + 1) . '. Wrong ammount of columns (' . count($data[$k_data]) . ') there should be (' . $col . ')!');
					}
				}

				return $data;
			}
		}
		
		private function validate_csv($data, $col = '0', $required_cols = false)
		{
			$validation_error = false;
			$empty_field_error = array();
			$column_mismatch_error = array();

			if($data)
			{
				foreach($data as $k_data => $v_data)
				{
					//verify if every line in csv has expected amount of data
					if($col > '0')
					{
						if(count($v_data) != $col)
						{
							$validation_error = true;
							$column_mismatch_error[$k_data] = true;
						}

						if($column_mismatch_error[$k_data])
						{
							//write log for further details
							$this->write_log('1', 'CSV Import: [Validation Error] expected (' . $col . ') columns and found (' . count($v_data) . ') columns in csv file!');
						}
					}

					//verify if required cols are not empty
					if($required_cols)
					{
						foreach($required_cols as $k_req_col => $v_req_col)
						{
							if(strlen(trim(rtrim($v_data[$v_req_col]))) == 0)
							{
								$validation_error = true;
								$empty_field_error[$k_data][$v_req_col] = true;
							}

							if($empty_field_error[$k_data][$v_req_col])
							{
								//write log for further details
								$this->write_log('1', 'CSV Import: [Validation Error] empty required field (' . $v_req_col . ') at line (' . $k_data . ') !');
							}
						}
					}
				}
			}
			else
			{
				$validation_error = true;
				//write log for further details
				$this->write_log('1', 'CSV Import: [Validation Error] empty source data!');
			}



			//return true if no validation errors
			return !$validation_error;
		}

		private function gather_available_data($csv_data)
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');

			//required vars
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
			$valid_date = false;
			
			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";


			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$sql_q = '';
			$swapped_year_day_date = '';
			foreach($csv_data as $k_row => $v_details)
			{
				//d.m.Y 2 Y-m-d *** birthdate cant use strtotime()
				//rewriten to user strtotime and string length checks
				$date_str_length = strlen(trim(rtrim($v_details['10'])));

				if($date_str_length == '8' || $date_str_length == "10")
				{
					$swapped_year_day_date = date('Y-m-d', strtotime($v_details['10']));
					$valid_date = true;
				}
				else if($date_str_length > '10' || $date_str_length < '8')
				{
					$this->write_log('1', 'CSV Import: [Data Gatherer] removed line ' . ($k_row + 1) . '. Wrong patient birthdate column (' . $v_details['10'] . ') there should be strtotime() friendly format!');
					$valid_date = false;
				}

				if($valid_date === true)
				{
					if($k_row == '0')
					{
						$sql_q .= " (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_details['7'] . "')) and trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_details['9'] . "')) or (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_details['7'] . "')) and birthd ='" . $swapped_year_day_date . "') or (trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_details['9'] . "')) and birthd ='" . $swapped_year_day_date . "')) ";
					}
					else
					{
						$sql_q .= " OR (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_details['7'] . "')) and trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_details['9'] . "')) or (trim(lower(convert(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_details['7'] . "')) and birthd ='" . $swapped_year_day_date . "') or (trim(lower(convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1))) = trim(lower('" . $v_details['9'] . "')) and birthd ='" . $swapped_year_day_date . "')) ";
					}
				}
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid)
				->andWhere($sql_q);
			$patient_details = $patient->fetchArray();

			foreach($csv_data as $k_row_data => $v_row_data)
			{
				foreach($patient_details as $k_patient => $v_patient)
				{
					$v_patient['encrypted_id'] = str_replace('=', '', Pms_Uuid::encrypt($v_patient['id']));

					$swapped_date = date('Y-m-d', strtotime($v_row_data['10']));
					$v_patient['dob'] = date('d.m.Y', strtotime($v_patient['birthd']));


					if(trim(rtrim(strtolower($v_row_data[7]))) == trim(rtrim(strtolower($v_patient['firstname']))) || $v_patient['birthd'] == $swapped_date)
					{
						$mapped_csv_results[$k_row_data][$v_patient['id']] = $v_patient;
					}
				}
			}

			return $mapped_csv_results;
		}

		private function get_session_patients($patientids)
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');

			//required vars
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
			//to avoid SQLSTATE[HY093]: Invalid parameter number: parameter was not defined
			$patientids_second = array_values($patientids);
			if(count($patientids_second) == '0')
			{
				$patientids_second[] = '99999999999';
			}
			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";


			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid)
				->andWhereIn("p.id", $patientids_second);
			$patient_details = $patient->fetchArray();


			foreach($patient_details as $k_patient => $v_patient)
			{
				$v_patient['encrypted_id'] = str_replace('=', '', Pms_Uuid::encrypt($v_patient['id']));

				//Y-m-d 2 d.m.Y *** birthdate cant use strtotime()
//				$v_patient['dob'] = implode('.', array_reverse(explode('-', $v_patient['birthd'])));
				$v_patient['dob'] = date('d.m.Y', strtotime($v_patient['birthd']));
				
				$patient_csv_row = array_search($v_patient['id'], $patientids);
				$result_mapped_array[$patient_csv_row] = $v_patient;
			}


			return $result_mapped_array;
		}

		private function terminal_backup($files)
		{
			//init
			$logininfo = new Zend_Session_Namespace('Login_Info');

			//setup vars
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$ext = pathinfo($files['qqfile']['name']);
			$ext = $ext['extension'];

			$bk_dir = APPLICATION_PATH . '/../public/terminal_backup/';
			$backup_filename = date('Ymd-His', time()) . "_" . $clientid . '_' . $userid . "." . $ext;


			//check the backup directory and create new one
			if(!is_dir($bk_dir))
			{
				if(mkdir($bk_dir, "0777", false))
				{
					$err_bk = 0;
				}
				else
				{
					$err_bk = 1;
					$this->write_log('1', 'Backup Upload Directory not exist and can`t be created! => ' . $bk_dir . $backup_filename . " [" . $files['qqfile']['name'] . "].");
				}
			}

			//check if we have permission in backup directory
			if(is_writable($bk_dir))
			{
				$err_bk = 0;
			}
			else
			{
				$err_bk = 1;

				$this->write_log('1', 'Upload Directory is not writeable => ' . $bk_dir . $backup_filename . " [" . $files['qqfile']['name'] . "].");
			}

			if($err_bk == '0')
			{
				if(copy($files['qqfile']['tmp_name'], $bk_dir . $backup_filename))
				{
					$this->write_log('1', 'Backup file succesfully => ' . $bk_dir . $backup_filename . " [" . $files['qqfile']['name'] . "].");
				}
				else
				{
					$this->write_log('1', 'Backup file failed => ' . $bk_dir . $backup_filename . " [" . $files['qqfile']['name'] . "].");
				}
			}
		}

		private function write_log($log_type = 1, $message = '')
		{
			switch($log_type)
			{
				case '1':
					$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
					break;

				case '2':
					$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/custom.log');
					break;

				default:
					$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
					break;
			}

			$log = new Zend_Log($writer);
			$log->info($message);
		}

		
		
		public function patientimportAction(){
		    exit;
		    ini_set("auto_detect_line_endings", true);
		    setlocale(LC_ALL, 'de_DE.UTF8');
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $import_session = new Zend_Session_Namespace('importSession');
		    $userid = $logininfo->userid;

		    set_time_limit(0);
		    
		    //csv validation settings
		    $csv_cols = '34';
		    $minimal_required = array('7', '10');
		    //estabilish a delimiter if none was provided via post
		    if(strlen(trim(rtrim($_POST['csvdelimiter']))) > 0)
		    {
		        $delimiter = trim(rtrim($_POST['csvdelimiter']));
		        $this->view->delimiter = trim(rtrim($_POST['csvdelimiter']));
		    }
		    else
		    {
		        $delimiter = ";";
		        $this->view->delimiter = ";";
		    }
		    
		    //process post action
		    if($this->getRequest()->isPost() || strlen($filename) != 0)
		    {
		    
		        $dir = "uploadfile/";
		        if(strlen($filename) == 0)
		        {
		            $filename = $dir . $_SESSION['filename'];
		        }
		        else
		        {
		            $filename = $dir . $filename;
		        }
		        $error = '0';
		    
		        if(!is_writable($filename))
		        {
		            $this->view->error = 'file_open_error';
		            $this->write_log('1', 'Load import file procedure error! File [' . $filename . '] does not exist!.');
		            $error = 1;
		        }
		    
		    
		        if($error == '0')
		        {
		            $this->write_log('1', 'CSV Import: Start');
		            $this->view->error = '';
		            $handle = fopen($filename, "r");
		            //parse csv into an array
		            while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
		            {
		                foreach($data as $k_data => $v_data)
		                {
		                    $data[$k_data] = htmlspecialchars($v_data);
		                }
		    
		    
		                $csv_data[] = $data;
		            }
		            fclose($handle);
		            
// 		            print_r($csv_data); exit;
        		    $import_form = new Application_Form_Import();
        		    $import_form->patient_import_handler($csv_data, $_POST);
        		    
        		    $import_session->userid = '';
        		    $import_session->target_patient = '';
        		    $import_session->import_value = '';
        		    $import_session->filename = '';
        		    $import_session->delimiter = '';
        		    $this->_redirect(APP_BASE . 'import/patientimport?flg=suc');
        		    exit;
		 
		        }
		    }	    
		    
		    
		}

		
		
		public function locationimportAction(){
            exit;
		    ini_set("auto_detect_line_endings", true);
		    setlocale(LC_ALL, 'de_DE.UTF8');
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $import_session = new Zend_Session_Namespace('importSession');
		    $userid = $logininfo->userid;

		    set_time_limit(0);
		    
		    //csv validation settings
		    $csv_cols = '34';
		    $minimal_required = array('7', '10');
		    //estabilish a delimiter if none was provided via post
		    if(strlen(trim(rtrim($_POST['csvdelimiter']))) > 0)
		    {
		        $delimiter = trim(rtrim($_POST['csvdelimiter']));
		        $this->view->delimiter = trim(rtrim($_POST['csvdelimiter']));
		    }
		    else
		    {
		        $delimiter = ";";
		        $this->view->delimiter = ";";
		    }
		    
		    //process post action
		    if($this->getRequest()->isPost() || strlen($filename) != 0)
		    {
		    
		        $dir = "uploadfile/";
		        if(strlen($filename) == 0)
		        {
		            $filename = $dir . $_SESSION['filename'];
		        }
		        else
		        {
		            $filename = $dir . $filename;
		        }
		        $error = '0';
		    
		        if(!is_writable($filename))
		        {
		            $this->view->error = 'file_open_error';
		            $this->write_log('1', 'Load import file procedure error! File [' . $filename . '] does not exist!.');
		            $error = 1;
		        }
		    
		    
		        if($error == '0')
		        {
		            $this->write_log('1', 'CSV Import: Start');
		            $this->view->error = '';
		            $handle = fopen($filename, "r");
		            //parse csv into an array
		            while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
		            {
		                foreach($data as $k_data => $v_data)
		                {
		                    $data[$k_data] = htmlspecialchars($v_data);
		                }
		    
		    
		                $csv_data[] = $data;
		            }
		            fclose($handle);
		            
        		    $import_form = new Application_Form_Import();
        		    $import_form->location_import_handler($csv_data, $_POST);
        		    
        		    $import_session->userid = '';
        		    $import_session->target_patient = '';
        		    $import_session->import_value = '';
        		    $import_session->filename = '';
        		    $import_session->delimiter = '';
        		    $this->_redirect(APP_BASE . 'import/locationimport?flg=suc');
        		    exit;
		 
		        }
		    }	    
		}
		
		public function lmuimportAction(){
		    ini_set("auto_detect_line_endings", true);
		    setlocale(LC_ALL, 'de_DE.UTF8');
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $import_session = new Zend_Session_Namespace('importSession');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;

		    set_time_limit(0);
		    
		    
		  /*   //Contacts
		    $filename_contactss = PUBLIC_PATH ."/import/nr_h_patients/Nr_patients.csv";
		    $handle_contacts = fopen($filename_contactss, "r");
		    $delimiter_contactss = ";";
		    //parse csv into an array
		    while(($data_contactss = fgetcsv($handle_contacts, NULL, $delimiter_contactss)) !== FALSE)
		    {
		        foreach($data_contactss as $k_data => $v_data)
		        {
		            $data_contactss[$k_data] = htmlspecialchars($v_data);
		        }
		         
		         
		        $csv_data[] = $data_contactss;
		    }
		    fclose($handle_contacts);
		    
		    $import_form = new Application_Form_Import();
		    
		    
		    $import_form->patient_import_handler_nr_2019_TODO2509($csv_data, $_POST);
		     */
		    
		    
		    //csv validation settings
		    $csv_cols = '34';
		    $minimal_required = array('7', '10');
		    //estabilish a delimiter if none was provided via post
		    if(strlen(trim(rtrim($_POST['csvdelimiter']))) > 0)
		    {
		        $delimiter = trim(rtrim($_POST['csvdelimiter']));
		        $this->view->delimiter = trim(rtrim($_POST['csvdelimiter']));
		    }
		    else
		    {
		        $delimiter = ";";
		        $this->view->delimiter = ";";
		    }
		    
		    //process post action
		    if($this->getRequest()->isPost() || strlen($filename) != 0)
		    {
		    
		        $dir = "uploadfile/";
		        if(strlen($filename) == 0)
		        {
		            $filename = $dir . $_SESSION['filename'];
		        }
		        else
		        {
		            $filename = $dir . $filename;
		        }
		        $error = '0';
		    
		        if(!is_writable($filename))
		        {
		            $this->view->error = 'file_open_error';
		            $this->write_log('1', 'Load import file procedure error! File [' . $filename . '] does not exist!.');
		            $error = 1;
		        }
		    
		    
		        if($error == '0')
		        {
		            $this->write_log('1', 'CSV Import: Start');
		            $this->view->error = '';
		            $handle = fopen($filename, "r");
		            //parse csv into an array
		            while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
		            {
		                foreach($data as $k_data => $v_data)
		                {
		                    $data[$k_data] = htmlspecialchars($v_data);
		                }
		    
		    
		                $csv_data[] = $data;
		            }
		            fclose($handle);
		            
		            
        		    $import_form = new Application_Form_Import();
        		    
                    if($_REQUEST['dbg'] == "1"){
                        print_R($csv_data); exit;
                    }
                    
        		    if(strlen($_POST['csv_type']) > 0 )
        		    {
        		        if($_POST['csv_type'] == "patients")
        		        {
//         		            $import_form->patient_import_handler_lmu($csv_data, $_POST);
//         		            $import_form->patient_import_handler_wlk($csv_data, $_POST);
        		            $import_form->patient_import_handler_wl_unna($csv_data, $_POST);


        		           /*  $dg = new DiagnosisType();
        		            
        		            $abb1 = "'HD'";
        		            $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
        		            if($ddarr1){
        		            
        		                $comma = ",";
        		                $typeid = "'0'";
        		                foreach($ddarr1 as $key => $valdia)
        		                {
        		                    $type_id_array[] = $valdia['id'];
        		                }
        		                $main_diagnosis_type = $type_id_array[0];
        		            }
        		            
        		            
        		            
        		            $abb2 = "'ND'";
        		            $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
        		            if($ddarr2){
        		            
        		                $comma = ",";
        		                $typeid = "'0'";
        		                foreach($ddarr2 as $key => $valdia)
        		                {
        		                    $type_id_arrayd[] = $valdia['id'];
        		                }
        		                $side_diagnosis_type = $type_id_arrayd[0];
        		            }
        		            
        		            $import_clients = array($clientid);
        		            $patient = Doctrine_Query::create()
        		            ->select("p.*,e.*")
        		            ->from('PatientMaster p')
        		            ->leftJoin("p.EpidIpidMapping e")
        		            ->andWhereIn('e.clientid', $import_clients)
        		            ->andWhere("p.import_pat != ''")
        		            ->andWhere("p.isdelete = 0 ");
        		            $patient_details = $patient->fetchArray();
        		            
        		            foreach($patient_details as $k => $pat_val)
        		            {
        		                $patients_ipids[] = $pat_val['ipid'];
        		                $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
        		                $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
        		                $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
        		                $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
        		            }
//         		            print_r($patients_ipids); exit;
        		            if(empty($patients_ipids)){
        		                $patients_ipids[] = "99999999999";
        		            }
        		            // delete all where ipids
        		            $Q = Doctrine_Query::create()
        		            ->delete('PatientDiagnosis')
        		            ->WhereIn("ipid",$patients_ipids);
        		            $Q->execute();

        		            $ipid="";
        		            foreach($csv_data as $csv_row=>$csv_details){

        		                $ipid = $patients_array[$csv_details['1']]['ipid'];
        		                if($ipid){
        		                // diagnosis
            		                if(strlen($csv_details['11']) > 0){
            		                    $free_diagno_id ="";
            		                    $diagno_free = new DiagnosisText();
            		                    $diagno_free->clientid = $clientid;
            		                    $diagno_free->icd_primary = $csv_details['11'];
            		                    $diagno_free->free_name = " ";
            		                    $diagno_free->save();
            		                    $free_diagno_id = $diagno_free->id;
            		                
            		                    $diagno = new PatientDiagnosis();
            		                    $diagno->ipid = $ipid;
            		                    $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
            		                    $diagno->diagnosis_type_id = $main_diagnosis_type;
            		                    $diagno->diagnosis_id = $free_diagno_id;
            		                    $diagno->icd_id = "0";
            		                    $diagno->save();
            		                }
            		                
            		                
            		                if(strlen($csv_details['12']) > 0){
            		                
            		                    $free_diagno_id ="";
            		                    $diagno_free = new DiagnosisText();
            		                    $diagno_free->clientid = $clientid;
            		                    $diagno_free->icd_primary = $csv_details['12'];
            		                    $diagno_free->free_name = " ";
            		                    $diagno_free->save();
            		                    $free_diagno_id = $diagno_free->id;
            		                
            		                    $diagno = new PatientDiagnosis();
            		                    $diagno->ipid = $ipid;
            		                    $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
            		                    $diagno->diagnosis_type_id = $side_diagnosis_type ;
            		                    $diagno->diagnosis_id = $free_diagno_id;
            		                    $diagno->icd_id = "0";
            		                    $diagno->save();
            		                }
            		                if(strlen($csv_details['13']) > 0){
            		                
            		                    $free_diagno_id ="";
            		                    $diagno_free = new DiagnosisText();
            		                    $diagno_free->clientid = $clientid;
            		                    $diagno_free->icd_primary = $csv_details['13'];
            		                    $diagno_free->free_name = " ";
            		                    $diagno_free->save();
            		                    $free_diagno_id = $diagno_free->id;
            		                
            		                    $diagno = new PatientDiagnosis();
            		                    $diagno->ipid = $ipid;
            		                    $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
            		                    $diagno->diagnosis_type_id = $side_diagnosis_type ; ;
            		                    $diagno->diagnosis_id = $free_diagno_id;
            		                    $diagno->icd_id = "0";
            		                    $diagno->save();
            		                }
            		                if(strlen($csv_details['14']) > 0){
            		                
            		                    $free_diagno_id ="";
            		                    $diagno_free = new DiagnosisText();
            		                    $diagno_free->clientid = $clientid;
            		                    $diagno_free->icd_primary = $csv_details['14'];
            		                    $diagno_free->free_name = " ";
            		                    $diagno_free->save();
            		                    $free_diagno_id = $diagno_free->id;
            		                
            		                    $diagno = new PatientDiagnosis();
            		                    $diagno->ipid = $ipid;
            		                    $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
            		                    $diagno->diagnosis_type_id = $side_diagnosis_type ; ;
            		                    $diagno->diagnosis_id = $free_diagno_id;
            		                    $diagno->icd_id = "0";
            		                    $diagno->save();
            		                }
        		              }
        		            } */
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        elseif($_POST['csv_type'] == "patients-nr")
        		        {
        		            
        		            dd($csv_data);
        		            $import_form->patient_import_handler_wl_unna($csv_data, $_POST);

        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        else if($_POST['csv_type'] == "falls")
        		        {
        		            $import_form->location_import_handler_lmu($csv_data, $_POST);
        		        } 
        		        else if($_POST['csv_type'] == "stamdaten")
        		        {
        		            $import_form->stamdaten_import_handler_lmu($csv_data, $_POST);
        		        } 
        		        else if($_POST['csv_type'] == "sapv")
        		        {
//         		            $import_form->sapv_import_handler_lmu($csv_data, $_POST);
        		            $import_form->sapv_import_handler_rp($csv_data, $_POST);
        		        } 
//         		        else if($_POST['csv_type'] == "patient_course")
//         		        {
//         		            $import_form->course_import_handler_lmu($csv_data, $_POST);
//         		        } 
        		        else if($_POST['csv_type'] == "medication_course")
        		        {
        		            $import_form->medication_course_import_handler_lmu($csv_data, $_POST);
        		        } 
        		        else if($_POST['csv_type'] == "ahps")
        		        {
        		            $import_form->stamdaten_import_handler_wlk($csv_data, $_POST);
        		            
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		            
        		        } 
        		        else if($_POST['csv_type'] == "contact_persons")
        		        {
        		            $import_form->contact_persons_import_handler_wlk($csv_data, $_POST);
        		            
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		            
       		        }
        		        else if($_POST['csv_type'] == "final_anamnese")
        		        {
        		            $import_form->verlauf_import_handler_wlk($csv_data, $_POST,"A");
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        } 
        		        else if($_POST['csv_type'] == "final_befunde")
        		        {
        		            $import_form->verlauf_import_handler_wlk($csv_data, $_POST,"B");
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        } 
        		        else if($_POST['csv_type'] == "final_gespraeche_special")
        		        {
        		            $import_form->verlauf_import_handler_wlk($csv_data, $_POST,"K",false,"1");
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        } 
        		        else if($_POST['csv_type'] == "final_gewicht")
        		        {
        		            $import_form->verlauf_import_handler_wlk($csv_data, $_POST,"K",1);
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        } 
        		        else if($_POST['csv_type'] == "final_sonstigeTherapien")
        		        {
        		            $import_form->verlauf_import_handler_wlk($csv_data, $_POST,"K");
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        } 
        		        else if($_POST['csv_type'] == "final_verlauf")
        		        {
        		            $import_form->verlauf_import_handler_wlk($csv_data, $_POST,"K");
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        }
        		         
        		        else if($_POST['csv_type'] == "diagnosis")
        		        {
        		            $import_form->diagnosis_import_handler_wlk($csv_data, $_POST);
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        } 
        		        else if($_POST['csv_type'] == "medication")
        		        {
        		            $import_form->normal_medication_import_handler_wlk($csv_data, $_POST);
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        } 
        		        else if($_POST['csv_type'] == "bedarf_medication")
        		        {
        		            $import_form->bedarf_medication_import_handler_wlk($csv_data, $_POST);
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        }
        		        
        		        
        		        
        		        else if($_POST['csv_type'] == "client_voluntary")
        		        {
        		        	$import_form->voluntary_workers($csv_data, $_POST);
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        else if($_POST['csv_type'] == "client_specialists")
        		        {
        		        	$import_form->specialists($csv_data, $_POST);
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        
        		        else if($_POST['csv_type'] == "stamdatem_rp")
        		        {
        		        	$import_form->stamdaten_import_handler_rp($csv_data, $_POST);
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        else if($_POST['csv_type'] == "medication_rp")
        		        {
        		        	$import_form->medication_import_handler_rp($csv_data, $_POST);
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_rp")
        		        {

        		        	$filename_falls = PUBLIC_PATH ."/import/rp_import/falls_bt.csv";
        		        	$handle_fall = fopen($filename_falls, "r");
        		        	$delimiter_falls = ";";
        		        	//parse csv into an array
        		        	while(($data_falls = fgetcsv($handle_fall, NULL, $delimiter_falls)) !== FALSE)
        		        	{
        		        		foreach($data_falls as $k_data => $v_data)
        		        		{
        		        			$data_falls[$k_data] = htmlspecialchars($v_data);
        		        		}
        		        	
        		        	
        		        		$csv_data_falls[] = $data_falls;
        		        	}
        		        	fclose($handle_fall);
        		        	
//         		        	$import_form->update_falls($csv_data,$csv_data_falls, $_POST);
        		        	$import_form->patient_import_handler_rp($csv_data,$csv_data_falls, $_POST);
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_wl_2019_2182")
        		        {
        		        	$import_form->patient_import_handler_wl_2019_2182($csv_data, $_POST);
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_wl_2019_2183")
        		        {
        		        	$import_form->patient_import_handler_wl_2019_2183($csv_data, $_POST);
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_wl_2019_2184")
        		        {
        		        	$import_form->patient_import_handler_wl_2019_2184($csv_data, $_POST);
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_new_rp")
        		        {

        		        	
        		        	$csv_data_extra = array();
        		        	//FALS
        		        	$filename_falls = PUBLIC_PATH ."/import/rp_new_import/falls.csv";
        		        	$handle_fall = fopen($filename_falls, "r");
        		        	$delimiter_falls = ",";
        		        	//parse csv into an array
        		        	while(($data_falls = fgetcsv($handle_fall, NULL, $delimiter_falls)) !== FALSE)
        		        	{
        		        		foreach($data_falls as $k_data => $v_data)
        		        		{
        		        			$data_falls[$k_data] = htmlspecialchars($v_data);
        		        		}
        		        	
        		        	
        		        		$csv_data_extra['falls'][] = $data_falls;
        		        	}
        		        	fclose($handle_fall);
        		        	
        		        	//Diagnosis
        		        	$filename_diagnosis = PUBLIC_PATH ."/import/rp_new_import/diagnosis.csv";
        		        	$handle_diagnosis = fopen($filename_diagnosis, "r");
        		        	$delimiter_diagnosis = ",";
        		        	//parse csv into an array
        		        	while(($data_diagnosis = fgetcsv($handle_diagnosis, NULL, $delimiter_diagnosis)) !== FALSE)
        		        	{
        		        		foreach($data_diagnosis as $k_data => $v_data)
        		        		{
        		        			$data_diagnosis[$k_data] = htmlspecialchars($v_data);
        		        		}
        		        	
        		        	
        		        		$csv_data_extra['diagnosis'][] = $data_diagnosis;
        		        	}
        		        	fclose($handle_diagnosis);
        		        	
        		        	//Contacts
        		        	$filename_contactss = PUBLIC_PATH ."/import/rp_new_import/contacts_second.csv";
        		        	$handle_contacts = fopen($filename_contactss, "r");
        		        	$delimiter_contactss = ",";
        		        	//parse csv into an array
        		        	while(($data_contactss = fgetcsv($handle_contacts, NULL, $delimiter_contactss)) !== FALSE)
        		        	{
        		        		foreach($data_contactss as $k_data => $v_data)
        		        		{
        		        			$data_contactss[$k_data] = htmlspecialchars($v_data);
        		        		}
        		        	
        		        	
        		        		$csv_data_extra['contacts'][] = $data_contactss;
        		        	}
        		        	fclose($handle_contacts);
        		        	
//         		        	$import_form->update_falls($csv_data,$csv_data_falls, $_POST);
        		        	$import_form->patient_import_handler_rp_new($csv_data,$csv_data_extra, $_POST);//TODO-1368
        		        
        		        	$this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        	exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_caritas_2019_TODO-2276")
        		        {
        		            // 25.04.2019 Ancuta
        		            $import_form->patient_import_handler_caritas_2019_TODO2276($csv_data, $_POST);
        		        
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_rp_2019_TODO-2271")
        		        {
        		            // 03.05.2019 Ancuta
        		            $import_form->patient_import_handler_rp_2019_TODO2271($csv_data, $_POST);
        		        
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_sl_2019_TODO2363")
        		        {
        		            
        		            // 26.06.2019 Ancuta
        		            $import_form->patient_import_handler_sl_2019_TODO2363($csv_data, $_POST);
        		        
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_he_2019_TODO2382")
        		        {
        		            
        		            // 26.06.2019 Ancuta
        		            $import_form->patient_import_handler_he_2019_TODO2382($csv_data, $_POST);
        		        
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_nr_2019_TODO2509")
        		        {
        		            
        		    
        		            
        		            // 22.08.2019 Ancuta
        		            $import_form->patient_import_handler_nr_2019_TODO2509($csv_data, $_POST);
        		        
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        else if($_POST['csv_type'] == "patients_rp_2020_2699")
        		        {
        		            
        		            // 07.01.2020 Ancuta
        		            $import_form->patient_import_handler_rp_2020_2699($csv_data, $_POST);
        		        
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
      		            //TODO-3629 07.01.2020 Ancuta
        		        else if($_POST['csv_type'] == "patients_bay_2020_3629")
        		        {
        		            $import_form->patient_import_handler_bay_2020_3629($csv_data, $_POST);
        		        
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        //--
        		        
      		            //TODO-3839 Ancuta 09.03.2021
        		        else if($_POST['csv_type'] == "patients_wl_2021_3839")
        		        {
        		            $import_form->patient_import_handler_wl_2021_3839($csv_data, $_POST);
        		        
        		            $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		            exit;
        		        }
        		        //--
        		        
        		        else
        		        {
            		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
            		        exit;
        		        }
        		    } 
        		    else
        		    {
        		        $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		        exit;
        		    }
        		    
        		    
        		    $import_session->userid = '';
        		    $import_session->target_patient = '';
        		    $import_session->import_value = '';
        		    $import_session->filename = '';
        		    $import_session->delimiter = '';
        		    $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
        		    exit;
		 
		        }
		    }	    
		}
		
		public function lmucourseimportAction(){

		    set_time_limit(0);
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $userid = $logininfo->userid;
		    $clientid = $logininfo->clientid;
		    
		    $this->_helper->layout->setLayout('layout');
		    $this->_helper->viewRenderer->setNoRender();
 
		    $import_clients = array($clientid);
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    //!!!!!!!!!!!!!!!!!
		    // get data from db 
		    $drop = Doctrine_Query::create()
		    ->select('*')
		    ->from('LmuImportActions')
		    ->where("imported = 0");
		    $droparray = $drop->fetchArray();
		    
		    foreach($droparray as $row => $data){
		        $lmu_data[$row]['row_id'] =  $data['id'];
		        
		        $lmu_data[$row]['ipid'] =  $patients_array[$data['patient_id']]['ipid'];
		        $lmu_data[$row]['patient_id'] =  $data['patient_id'];
		        
		        $lmu_data[$row]['start_date'] =  $data['start'];
		        $lmu_data[$row]['start_date_ymd'] =  date("Y-m-d H:i:s",strtotime($data['start']));
		        $lmu_data[$row]['start_date_dmy'] =  date("d.m.Y",strtotime($data['start']));
		        $lmu_data[$row]['start_datetime_dmyHi'] =  date("d.m.Y H:i",strtotime($data['start']));

		        $lmu_data[$row]['end_date'] =  $data['end'];
		        $lmu_data[$row]['end_date_ymd'] =  date("Y-m-d H:i:s",strtotime($data['end']));
		        $lmu_data[$row]['end_date_dmy'] =  date("d.m.Y",strtotime($data['end']));
		        $lmu_data[$row]['end_datetime_dmyHi'] =  date("d.m.Y H:i",strtotime($data['']));

		        $lmu_data[$row]['Akt_Inhalt'] =  utf8_encode($data['Akt_Inhalt']);;
		        $lmu_data[$row]['Akt_Kategorie'] =  utf8_encode($data['Akt_Kategorie']);
		        $lmu_data[$row]['Dauer'] =  $data['Dauer'];
		        $lmu_data[$row]['Benutzer'] =  utf8_encode($data['Benutzer']);
		        $lmu_data[$row]['Fahrtstrecke'] =  $data['Fahrtstrecke'];
		        $lmu_data[$row]['Rufbereitschaft'] =  $data['Rufbereitschaft'];
		    }
		    
		    $usr = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where("clientid ='" . $clientid. "'")
		    ->orderby("last_name ASC");
		    $dr = $usr->execute();
		     
		    foreach($dr as  $k=>$user_details){
		        $userln2id[$user_details['last_name']] = $user_details['id'];
		    }
		    
		    $type = "";
		    foreach($lmu_data as $row => $data){

		        $ipid =   $patients_array[$data['patient_id']]['ipid'];
    		    if($ipid)
    		    {
    		        $type = trim($data['Akt_Kategorie']);
    
    		        if( $type == "Kollegiale Absprache" || $type == "Teambesprechung") // Shortcut U  ("mit Leistungserbringer") || Teambesprechung
    		        {
    		            if(strlen($data['Dauer']) > 0 && $data['Dauer'] > 0  )
    		            {
    		                $u_duration = $data['Dauer'];
    		            }
    		            else
    		            {
    		                $u_duration = "10"; // default value for Beratrung
    		            }
    		        
    		            // insert in patient course
    		            if($type == "Kollegiale Absprache")
    		            {
    		                $verlauf_entry = "mit Leistungserbringer | ".$u_duration ." | ".$data['Akt_Inhalt']." | ".$data['start_datetime_dmyHi']." ";
    		            }
    		            elseif($type == "Teambesprechung")
    		            {
    		        
    		                $verlauf_entry = "mit Leistungserbringer | ".$u_duration ." | Teambesprechng: ".$data['Akt_Inhalt']." | ".$data['start_datetime_dmyHi']." ";
    		            }
    		        
    		            $cust = new PatientCourse();
    		            $cust->ipid = $ipid;
    		            $cust->course_date = date("Y-m-d H:i:s", strtotime($data['start_date']));
    		            $cust->course_type = Pms_CommonData::aesEncrypt("U");
    		            $cust->course_title = Pms_CommonData::aesEncrypt($verlauf_entry);
    		            $cust->done_date = date("Y-m-d H:i:s", strtotime($data['start_date']));
    		            
    		            if($userln2id[$data['Benutzer']]){
    		                $cust->user_id = $userln2id[$data['Benutzer']];
    		            } else{
    		                $cust->user_id = $userid;
    		            }
    		            $cust->save();
    		        }
    		        elseif($type == "Besuch Patient" || $type == "Besuch Arzt"  || $type == "Besuch Krankenhaus/stat. Einrichtung" )
    		        { // contact form
    		        
        		        // create contact from id - then add to verlauf as record id
        		        $stmb = new ContactForms();
        		        $stmb->ipid = $ipid;
        		        $stmb->start_date = date("Y-m-d H:i:s", strtotime($data['start_date']));
        		        $stmb->end_date = date("Y-m-d H:i:s", strtotime($data['end_date']));
        		        $stmb->begin_date_h =  date("H", strtotime($data['start_date']));
        		        $stmb->begin_date_m = date("i", strtotime($data['start_date']));
        		        $stmb->end_date_h = date("H", strtotime($data['end_date']));
        		        $stmb->end_date_m = date("i", strtotime($data['end_date']));
        		        $stmb->date = date("Y-m-d H:i:s", strtotime($data['start_date']));
        		        $stmb->form_type = "121";
        		        $stmb->fahrtstreke_km = $data['Fahrtstrecke'];
        		        $stmb->comment = htmlspecialchars($data['Akt_Inhalt']);
        		        
        		        if(strlen($data['Rufbereitschaft'])> 0 && $data['Rufbereitschaft'] == "1"){
        		            $stmb->quality = "4";
        		        }
        		        
        		        if($userln2id[$data['Benutzer']]){
        		            $stmb->create_user  = $userln2id[$data['Benutzer']];
        		        } else{
        		            $stmb->create_user = $userid;
        		        }
        		        
        		        
        		        $stmb->save();
        		        $conact_form_record_id = $stmb->id;
        		        
        		        $comment = 'Kontaktformular  hinzugefügt';
        		        $cust = new PatientCourse();
        		        $cust->ipid = $ipid;
        		        $cust->course_date = date("Y-m-d H:i:s", strtotime($data['start_date']));
        		        $cust->course_type = Pms_CommonData::aesEncrypt("F");
        		        $cust->course_title = Pms_CommonData::aesEncrypt($comment);
        		        $cust->tabname = Pms_CommonData::aesEncrypt("contact_form");
        		        $cust->recordid = $conact_form_record_id;
        		        
        		        if($userln2id[$data['Benutzer']])
        		        {
        		            $cust->user_id= $userln2id[$data['Benutzer']];
        		        } else {
        		            $cust->user_id = $userid; // DEFAUL USER
        		        }
        		        
        		        if($userln2id[$data['Benutzer']])
        		        {
        		            $cust->create_user= $userln2id[$data['Benutzer']];
        		        } else{
        		            $cust->create_user = $userid; // DEFAUL USER
        		        }
        		        $cust->done_date = date("Y-m-d H:i:s", strtotime($data['start_date']));
        		        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
        		        $cust->done_id = $conact_form_record_id;
        		        $cust->save();
        		        
        		        
        		        // KOMENT
        		        $cust = new PatientCourse();
        		        $cust->ipid = $ipid;
        		        $cust->course_date = date("Y-m-d H:i:s", strtotime($data['start_date'])); //??
        		        $cust->course_type = Pms_CommonData::aesEncrypt("K");
        		        $cust->course_title = Pms_CommonData::aesEncrypt(date("H:i",strtotime($data['start_date'])) . ' - ' . date("H:i",strtotime($data['end_date'])) . '  ' . date("d.m.Y",strtotime($data['start_date'])));
        		        if($userln2id[$data['Benutzer']])
        		        {
        		            $cust->user_id= $userln2id[$data['Benutzer']];
        		        } else {
        		            $cust->user_id = $userid; // DEFAUL USER
        		        }
        		        $cust->done_date = date("Y-m-d H:i:s", strtotime($data['start_date']));
        		        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
        		        $cust->done_id = $conact_form_record_id;
        		        $cust->save();
    		        
        		        // Km :: Fahrtstrecke
        		        if(strlen($data['Fahrtstrecke']) > 0 )
        		        {
        		            $cust = new PatientCourse();
        		            $cust->ipid = $ipid;
        		            $cust->course_date = date("Y-m-d H:i:s", strtotime($data['start_date'])); //??
        		            $cust->course_type = Pms_CommonData::aesEncrypt("K");
        		            $cust->course_title = Pms_CommonData::aesEncrypt("Fahrtstrecke: " . $data['Fahrtstrecke']);
        		        
        		            if($userln2id[$data['Benutzer']])
        		            {
        		                $cust->user_id= $userln2id[$data['Benutzer']];
        		            }
        		            else
        		            {
        		                $cust->user_id = $userid; // DEFAUL USER
        		            }
        		        
        		            $cust->done_date = date("Y-m-d H:i:s", strtotime($data['start_date'])); //??
        		            $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
        		            $cust->done_id = $conact_form_record_id;
        		            $cust->save();
        		        }
    		        }
    		        elseif($type == "Koordination")
    		        {// Shortcut V
    		        
        		        if(strlen($data['Dauer']) > 0 && $data['Dauer'] > 0  )
        		        {
        		            $v_duration = $data['Dauer'];
        		        }
        		        else
        		        {
        		            $v_duration = "8"; // default value for koordination
        		        }
        		        $koord_verlauf_entry = $v_duration." | ".$data['Akt_Inhalt']." | ".date("d.m.Y H:i",strtotime($data['start_date']))." ";
        		        
        		        $cust = new PatientCourse();
        		        $cust->ipid = $ipid;
        		        $cust->course_date = date("Y-m-d H:i:s",strtotime($data['start_date']));
        		        $cust->course_type = Pms_CommonData::aesEncrypt("V");
        		        $cust->course_title = Pms_CommonData::aesEncrypt($koord_verlauf_entry);
        		        $cust->done_date = date("Y-m-d H:i:s",strtotime($data['start_date']));
        		        if($userln2id[$data['Benutzer']]){
        		            $cust->user_id = $userln2id[$data['Benutzer']];
        		        } else{
        		            $cust->user_id = $userid;
        		        }
        		        $cust->save();
    		        
    		        }
    		        elseif($type == "Telefonat")
    		        {// Shortcut XT
    		        
        		        if(strlen($data['Dauer']) > 0 && $data['Dauer'] > 0  )
        		        {
        		            $xt_duration = $data['Dauer'];
        		        }
        		        else
        		        {
        		            $xt_duration = "12"; // default value for Telefonat
        		        }
        		        
        		        $ktelefon_verlauf_entry = "".$xt_duration ." | ".$data['Akt_Inhalt']." | ".date("d.m.Y H:i",strtotime($data['start_date']))." ";
        		        
        		        $cust = new PatientCourse();
        		        $cust->ipid = $ipid;
        		        $cust->course_date = date("Y-m-d H:i:s",strtotime($data['start_date']));
        		        $cust->course_type = Pms_CommonData::aesEncrypt("XT");
        		        $cust->course_title = Pms_CommonData::aesEncrypt($ktelefon_verlauf_entry);
        		        $cust->done_date = date("Y-m-d H:i:s",strtotime($data['start_date']));
        		        if($userln2id[$data['Benutzer']]){
        		            $cust->user_id = $userln2id[$data['Benutzer']];
        		        } else{
        		            $cust->user_id = $userid;
        		        }
        		        $cust->save();
    		        
    		        }
    		        elseif($type == "E-Mail")
    		        {// // Shortcut K That  starts with  "E-Mail: "
    		        
        		        $comment_verlauf_entry = "E-Mail:  ".$data['Akt_Inhalt']." ";
        		        
        		        $cust = new PatientCourse();
        		        $cust->ipid = $ipid;
        		        $cust->course_date = date("Y-m-d H:i:s",strtotime($data['start_date']));
        		        $cust->course_type = Pms_CommonData::aesEncrypt("K");
        		        $cust->course_title = Pms_CommonData::aesEncrypt($comment_verlauf_entry);
        		        $cust->done_date = date("Y-m-d H:i:s",strtotime($data['start_date']));
        		        if($userln2id[$data['Benutzer']]){
        		            $cust->user_id = $userln2id[$data['Benutzer']];
        		        } else{
        		            $cust->user_id = $userid;
        		        }
        		        $cust->save();
    		        
    		        }
    		        else
    		        {
    		            // do nothing
    		        }
    		          
    		      }
    		      // mark as imported
    		      $setimported = Doctrine_Query::create()
    		      ->update('LmuImportActions')
    		      ->set('imported', 1)
    		      ->where("id ='" . $data['row_id'] . "'");
    		      $setimported->execute();
		    }
		 
		}

		
		public function contactnumberimportAction(){
		    exit;
		    ini_set("auto_detect_line_endings", true);
		    setlocale(LC_ALL, 'de_DE.UTF8');
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $import_session = new Zend_Session_Namespace('importSession');
		    $userid = $logininfo->userid;

		    set_time_limit(0);
		    
		    //csv validation settings
		    $csv_cols = '34';
		    //estabilish a delimiter if none was provided via post
		    if(strlen(trim(rtrim($_POST['csvdelimiter']))) > 0)
		    {
		        $delimiter = trim(rtrim($_POST['csvdelimiter']));
		        $this->view->delimiter = trim(rtrim($_POST['csvdelimiter']));
		    }
		    else
		    {
		        $delimiter = ";";
		        $this->view->delimiter = ";";
		    }
		    
		    //process post action
		    if($this->getRequest()->isPost() || strlen($filename) != 0)
		    {
		    
		        $dir = "uploadfile/";
		        if(strlen($filename) == 0)
		        {
		            $filename = $dir . $_SESSION['filename'];
		        }
		        else
		        {
		            $filename = $dir . $filename;
		        }
		        $error = '0';
		    
		        if(!is_writable($filename))
		        {
		            $this->view->error = 'file_open_error';
		            $this->write_log('1', 'Load import file procedure error! File [' . $filename . '] does not exist!.');
		            $error = 1;
		        }
		    
		    
		        if($error == '0')
		        {
		            $this->write_log('1', 'CSV Import: Start');
		            $this->view->error = '';
		            $handle = fopen($filename, "r");
		            //parse csv into an array
		            while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
		            {
		                foreach($data as $k_data => $v_data)
		                {
		                    $data[$k_data] = htmlspecialchars($v_data);
		                }
		    
		    
		                $csv_data[] = $data;
		            }
		            fclose($handle);
		            
		            $import_clients = array("177","178","179");
		            $patient_array= array();
		            $patient = Doctrine_Query::create()
		            ->select("p.ipid,p.familydoc_id,p.import_pat,p.import_fd,e.epid,e.clientid")
		            ->from('PatientMaster p')
		            ->leftJoin("p.EpidIpidMapping e")
		            ->andWhereIn('e.clientid', $import_clients)
		            ->andWhere("import_pat != ''");
		            $patient_array = $patient->fetchArray();
		            
		            $existing_patients = array();
		            foreach($patient_array as $k => $pdata)
		            {
		                $existing_patients[] =  $pdata['import_pat'];
		                $imp2ipid[$pdata['import_pat']] = $pdata['ipid'];
		                $imp2id[$pdata['import_pat']] = $pdata['id'];
		                
		                $pat_det[$pdata['import_pat']]['ipid'] =  $pdata['ipid'];
		                $pat_det[$pdata['import_pat']]['id'] =  $pdata['id'];
		            }
		            
		            foreach($csv_data as $csv_row_id=>$row_data){
		                
		                if($csv_row_id != 0 )
		                {
    		                $new_csv_data[$csv_row_id]['pat_id'] = $row_data[0]; 
    		                $new_csv_data[$csv_row_id]['phone_id'] = $row_data[1]; 
    		                $new_csv_data[$csv_row_id]['type'] = $row_data[2]; 
    		                $new_csv_data[$csv_row_id]['phone_number'] = $row_data[3]; 
		                }
		            }
		            
		            foreach($new_csv_data as $row_id => $csv_row_data)
		            {
		                if(in_array($csv_row_data['pat_id'],$existing_patients))
		                {
    		                if($csv_row_data['type'] == "Patient")
    		                {
    		                    // update patient master
    		                    $cust = Doctrine::getTable('PatientMaster')->find($pat_det[$csv_row_data['pat_id']]['id']);
    		                    $cust->kontactnumber = Pms_CommonData::aesEncrypt($csv_row_data['phone_number']);
    		                    $cust->kontactnumbertype = "0";
    		                    $cust->save();
    		                } 
    		                else
    		                {
                                // create contact person
    		                    $cust = new ContactPersonMaster();
    		                    $cust->ipid = $pat_det[$csv_row_data['pat_id']]['ipid'];
    		                    $cust->cnt_last_name = Pms_CommonData::aesEncrypt($csv_row_data['type']);
    		                    $cust->cnt_phone = Pms_CommonData::aesEncrypt($csv_row_data['phone_number']);
    		                    $cust->save();
    		                    
                                // update patient master - with contact person number
    		                    $cust = Doctrine::getTable('PatientMaster')->find($pat_det[$csv_row_data['pat_id']]['id']);
    		                    $cust->kontactnumber = Pms_CommonData::aesEncrypt($csv_row_data['phone_number']);
    		                    $cust->kontactnumbertype = "2";
    		                    $cust->save();
    		                }
		                }
		            }
        		    $import_session->userid = '';
        		    $import_session->target_patient = '';
        		    $import_session->import_value = '';
        		    $import_session->filename = '';
        		    $import_session->delimiter = '';
        		    $this->_redirect(APP_BASE . 'import/contactnumberimport?flg=suc');
        		    exit;
		 
		        }
		    }	    
		}

		
		public function patientcourseimportAction(){
		    exit;
		    ini_set("auto_detect_line_endings", true);
		    setlocale(LC_ALL, 'de_DE.UTF8');
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $import_session = new Zend_Session_Namespace('importSession');
		    $userid = $logininfo->userid;

		    set_time_limit(0);
		    
		    //csv validation settings
		    $csv_cols = '34';
		    //estabilish a delimiter if none was provided via post
		    if(strlen(trim(rtrim($_POST['csvdelimiter']))) > 0)
		    {
		        $delimiter = trim(rtrim($_POST['csvdelimiter']));
		        $this->view->delimiter = trim(rtrim($_POST['csvdelimiter']));
		    }
		    else
		    {
		        $delimiter = ";";
		        $this->view->delimiter = ";";
		    }
		    
		    //process post action
		    if($this->getRequest()->isPost() || strlen($filename) != 0)
		    {
		    
		        $dir = "uploadfile/";
		        if(strlen($filename) == 0)
		        {
		            $filename = $dir . $_SESSION['filename'];
		        }
		        else
		        {
		            $filename = $dir . $filename;
		        }
		        $error = '0';
		    
		        if(!is_writable($filename))
		        {
		            $this->view->error = 'file_open_error';
		            $this->write_log('1', 'Load import file procedure error! File [' . $filename . '] does not exist!.');
		            $error = 1;
		        }
		    
		    
		        if($error == '0')
		        {
		            $this->write_log('1', 'CSV Import: Start');
		            $this->view->error = '';
		            $handle = fopen($filename, "r");
		            //parse csv into an array
		            while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
		            {
		                foreach($data as $k_data => $v_data)
		                {
		                    $data[$k_data] = htmlspecialchars($v_data);
		                }
		    
		    
		                $csv_data[] = $data;
		            }
		            fclose($handle);
		            
		            $import_clients = array("177","178","179");
		            $patient_array= array();
		            $patient = Doctrine_Query::create()
		            ->select("p.ipid,p.familydoc_id,p.import_pat,p.import_fd,e.epid,e.clientid")
		            ->from('PatientMaster p')
		            ->leftJoin("p.EpidIpidMapping e")
		            ->andWhereIn('e.clientid', $import_clients)
		            ->andWhere("import_pat != ''");
		            $patient_array = $patient->fetchArray();
		            
		            $existing_patients = array();
		            foreach($patient_array as $k => $pdata)
		            {
		                $existing_patients[] =  $pdata['import_pat'];
		                $imp2ipid[$pdata['import_pat']] = $pdata['ipid'];
		                $imp2id[$pdata['import_pat']] = $pdata['id'];
		                
		                $pat_det[$pdata['import_pat']]['ipid'] =  $pdata['ipid'];
		                $pat_det[$pdata['import_pat']]['id'] =  $pdata['id'];
		            }
		            
		            foreach($csv_data as $csv_row_id=>$row_data){
		                
		                if($csv_row_id != 0 )
		                {
    		                $new_csv_data[$csv_row_id]['pat_id'] = $row_data[0]; 
    		                $new_csv_data[$csv_row_id]['course_date'] = date("Y-m-d H:i:s",strtotime($row_data[1])); 
    		                
    		                $new_csv_data[$csv_row_id]['course_type'] = "K";
    		                
    		                if(strlen($row_data[5]) > 0 )
    		                {
    		                    $new_csv_data[$csv_row_id]['course_title'] = $row_data[5] .': '. $row_data[4];
    		                }
    		                else
    		                {
    		                    $new_csv_data[$csv_row_id]['course_title'] =  $row_data[4];
    		                }
		                }
		            }
		            
		            foreach($new_csv_data as $row_id => $csv_row_data)
		            {
		                if(in_array($csv_row_data['pat_id'],$existing_patients))
		                {
		                    
		                    if($pat_det[$csv_row_data['pat_id']]['ipid']){
		                        
    		                    $cust = new PatientCourse();
    		                    $cust->ipid = $pat_det[$csv_row_data['pat_id']]['ipid'];
    		                    $cust->course_date = $csv_row_data['course_date'];
    		                    $cust->course_type = Pms_CommonData::aesEncrypt($csv_row_data['course_type']);
    		                    $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($csv_row_data['course_title']));
    		                    $cust->tabname = Pms_CommonData::aesEncrypt("system_import");
    		                    $cust->done_date = $csv_row_data['course_date'];
    		                    $cust->user_id = $userid;
    		                    $cust->save();
		                    }
		                }
		            }
        		    $import_session->userid = '';
        		    $import_session->target_patient = '';
        		    $import_session->import_value = '';
        		    $import_session->filename = '';
        		    $import_session->delimiter = '';
        		    $this->_redirect(APP_BASE . 'import/patientcourseimport?flg=suc');
        		    exit;
		 
		        }
		    }	    
		}

		
		
		
		public function fdimportAction(){
		    exit;
		    ini_set("auto_detect_line_endings", true);
		    setlocale(LC_ALL, 'de_DE.UTF8');
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $import_session = new Zend_Session_Namespace('importSession');
		    $userid = $logininfo->userid;

		    set_time_limit(0);
		    
		    $import_clients = array("177","178","179");
		    /// get all family doctors grouped by cliemts and bsnr
		    $fdoc = Doctrine_Query::create()
		    ->select('*')
		    ->from('FamilyDoctor')
		    ->where("isdelete = 0 and valid_till='0000-00-00' and (first_name!='' or last_name!='')")
		    ->andWhere('indrop=0')
		    ->andWhereIn('clientid',$import_clients);
		    $fdoc_arr = $fdoc->fetchArray();
		    
		    $fd_data = array();
		    foreach($fdoc_arr as $k=>$f_data)
		    {
		      $fd_data[$f_data['clientid']][$f_data['doctor_bsnr']] = $f_data;    
		    }

		    // get all pateints grouped by client,
		    $patient_array= array();
		    $patient = Doctrine_Query::create()
		    ->select("p.ipid,p.familydoc_id,p.import_pat,p.import_fd,e.epid,e.clientid")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("familydoc_id = '0'")
		    ->andWhere("import_pat != ''");
		    $patient_array = $patient->fetchArray();

		    
		    if($this->getRequest()->isPost())
		    {
		        foreach($patient_array as $k => $pdata)
		        {
                    $patients2client[$pdata['EpidIpidMapping']['clientid']][$pdata['ipid']]['id'] = $pdata['id']; 
                    $patients2client[$pdata['EpidIpidMapping']['clientid']][$pdata['ipid']]['ipid'] = $pdata['ipid']; 
                    $patients2client[$pdata['EpidIpidMapping']['clientid']][$pdata['ipid']]['familydoc_id'] = $pdata['familydoc_id']; 
                    $patients2client[$pdata['EpidIpidMapping']['clientid']][$pdata['ipid']]['import_pat'] = $pdata['import_pat']; 
                    $patients2client[$pdata['EpidIpidMapping']['clientid']][$pdata['ipid']]['import_fd'] = $pdata['import_fd']; 
                    $patients2client[$pdata['EpidIpidMapping']['clientid']][$pdata['ipid']]['epid'] = $pdata['EpidIpidMapping']['epid']; 
                }
                
    		    foreach($patients2client as $client => $patients)
    		    {
    		        foreach($patients as $pat_ipid => $patient_data)
    		        {
    		            if(!empty($patient_data['import_fd']) && !empty($fd_data[$client][$patient_data['import_fd']]))
    		            {
    		                $doctor_data[$pat_ipid] = $fd_data[$client][$patient_data['import_fd']];
    
    		                // insert in family doc
    		                $fdoc = new FamilyDoctor();
    		                $fdoc->clientid = $doctor_data[$pat_ipid] ['clientid'];
    		                $fdoc->practice = $doctor_data[$pat_ipid] ['practice'];
    		                $fdoc->first_name = $doctor_data[$pat_ipid] ['first_name'];
    		                $fdoc->last_name = $doctor_data[$pat_ipid] ['last_name'];
    		                $fdoc->title = $doctor_data[$pat_ipid] ['title'];
    		                $fdoc->salutation = $doctor_data[$pat_ipid] ['salutation'];
    		                $fdoc->title_letter = $doctor_data[$pat_ipid] ['title_letter'];
    		                $fdoc->salutation_letter = $doctor_data[$pat_ipid] ['title_letter'];
    		                $fdoc->street1 = $doctor_data[$pat_ipid] ['street1'];
    		                $fdoc->street2 = $doctor_data[$pat_ipid] ['street2'];
    		                $fdoc->zip = $doctor_data[$pat_ipid] ['zip'];
    		                $fdoc->city = $doctor_data[$pat_ipid] ['city'];
    		                $fdoc->doctornumber = $doctor_data[$pat_ipid] ['doctornumber'];
    		                $fdoc->doctor_bsnr = $doctor_data[$pat_ipid] ['doctor_bsnr'];
    		                $fdoc->phone_practice = $doctor_data[$pat_ipid] ['phone_practice'];
    		                $fdoc->fax = $doctor_data[$pat_ipid] ['fax'];
    		                $fdoc->phone_private = $doctor_data[$pat_ipid] ['phone_private'];
    		                $fdoc->phone_cell = $doctor_data[$pat_ipid] ['phone_cell'];
    		                $fdoc->email = $doctor_data[$pat_ipid] ['email'];
    		                $fdoc->kv_no = $doctor_data[$pat_ipid] ['kv_no'];
    		                $fdoc->indrop = 1;
    		                $fdoc->medical_speciality = $doctor_data[$pat_ipid] ['medical_speciality'];
    		                $fdoc->comments = $doctor_data[$pat_ipid] ['comments'];
    		                $fdoc->create_user = "338";
    		                $fdoc->create_date = date("Y-m-d H:i:s", time());
    		                $fdoc->save();
    		                
    		                //  get inserted id
    		                $family_doctor_id[$pat_ipid] = $fdoc->id;
    		                
    		                if($family_doctor_id[$pat_ipid])
    		                {
    		                    // update patient master
    		                    $cust = Doctrine::getTable('PatientMaster')->find($patient_data['id']);
    		                    $cust->familydoc_id = $family_doctor_id[$pat_ipid];
    		                    $cust->save();
    		                    $family_doctor_id[$pat_ipid] = "";
    		                }
    		                
    		                $doctor_data[$pat_ipid] ="";
    		            }    
    		        }
    		    }
    		    $this->_redirect(APP_BASE . 'import/fdimport?flg=suc');
    		    exit;
		    }
		}
		
		
		
    /**
     * 
     *  IMPORT fo WL_HAMM - TODO-1682
     *  Ancuta
     *  11.07.2018
     *  first import healthinsurance master
     *  
     */
    public function xmlimporthealthAction()
    {
        exit;
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        $xmls = array(
            'healthinsurance' => array(
                'file_path' => PUBLIC_PATH . "/import/wl_hamm/KK.XML"
            ),
        );
        
        $data = array();
        
        libxml_use_internal_errors(true);
        
        foreach ($xmls as $xml_k => $xml) {
        
            $dom = new DomDocument('1.0', 'utf-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
        
            $result = $dom->load($xml['file_path']);
        
            if ($result === false) {
                echo ($xml_1 . PHP_EOL . " - Document is not well formed ! iDie !");
                continue;
            }
        
            // DOM to array
            $data[$xml_k] = Pms_XML2Array::createArray($dom);
        }
        
        $import_data = array();
        if (! empty($data['healthinsurance'])) {
            $import_data['healthinsurance'] = $data['healthinsurance']['export']['kk'];
        }
        
//         print_R($import_data); exit;
        foreach($import_data['healthinsurance'] as $k=>$hv){
            
            $hi[$hv['k-ik']][] = $hv;
            $health_array[] = array(
                'clientid'=> $clientid,
                'name'=> $hv['k-kname'],
                'street1'=> $hv['k-street'],
                'zip'=>  $hv['k-plz'],
                'city'=> $hv['k-ort'],
                'iknumber'=> $hv['k-ik'],
                'onlyclients'=> '1',
                'extra'=> '0',
            );
        }

        if( ! empty($health_array)){
        
            $collection = new Doctrine_Collection('HealthInsurance');
            $collection->fromArray($health_array);
            $collection->save();
        }
        
        // no html - so exit
        echo "success";
        exit; 
        
    }
    
    
    
    
    
    /**
     * Import patient s for WL_HAMM
     * TODO-1683
     * Ancuta
     * 11.07.2018
     * @return boolean
     */
    public function xmlimportAction()
    {
        echo "import";
        exit; // it was run on live server on 13.07.2018
         
        set_time_limit(0);
        error_reporting(E_ALL);
        // get imported patients
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        
        
        $patient_imported = Doctrine_Query::create()
        ->select("p.*,e.*")
        ->from('PatientMaster p')
        ->leftJoin("p.EpidIpidMapping e")
        ->andWhereIn('e.clientid', array($clientid))
        ->andWhere("p.import_pat != ''")
        ->andWhere("p.isdelete = 0 ")
        ->fetchArray();
        
        $imported_ids=  array();
        foreach($patient_imported as $k => $pat_val)
        {
            $imported_ids[] = $pat_val['import_pat'];
        }
        
        $xmls = array(
            'patient' => array(
                'file_path' => PUBLIC_PATH . "/import/wl_hamm/PATIENT.XML"
            ),
            'falls' => array(
                'file_path' => PUBLIC_PATH . "/import/wl_hamm/ZIFFERN.XML"
            ),
            'patient2diagno_code' => array(
                'file_path' => PUBLIC_PATH . "/import/wl_hamm/GESTDIAG.XML"
            ),
            'diagno_code2diagno_data' => array(
                'file_path' => PUBLIC_PATH . "/import/wl_hamm/DIAGNOSE.XML"
            ),
            'healthinsurance' => array(
                'file_path' => PUBLIC_PATH . "/import/wl_hamm/KK.XML"
            ),
            'patient_course' => array(
                'file_path' => PUBLIC_PATH . "/import/wl_hamm/BDOC.XML"
            )
        );
        
        // if needed change an load from file
        // 88
        $active_patient_ids = array(
            "15002","14204","15559","15635","15670","15680","15738","15756","15770","15810","13870","15849","15080","15910","15922","15939","15940","15946","15957","15963","15980","16015","16023","16027","16036","16040","16051","16055","16119","16124","16132","16131","16136","16140","16154","16170","16180","16181","16196","16208","16215","16218","16223","16229","16231","16235","16240","16243","16244","16248","16249","16254","16258","16264","16265","16266","16267","16268","16271","16272","16292","16274","16275","16277","16281","16279","16282","16283","16284","16288","16287","16290","16291","16294","16295","16297","16300","16301","16305","16307","16308","16309","16296","16311","16312","16313","16314","16317");
        // 23
        $paused_patient_ids = array(
            "16205","16187","16167","16157","16145","16142","16138","16073","16004","15988","15951","15871","15826","15811","15769","15766","15765","15743","15717","15713","15696","15676","15672");
        
        $data = array();
        
        libxml_use_internal_errors(true);
        
        foreach ($xmls as $xml_k => $xml) {
            
            $dom = new DomDocument('1.0', 'utf-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            
            $result = $dom->load($xml['file_path']);
            
            if ($result === false) {
                echo ($xml_1 . PHP_EOL . " - Document is not well formed ! iDie !");
                continue;
            }
            
            // DOM to array
            $data[$xml_k] = Pms_XML2Array::createArray($dom);
        }
        
        $import_data = array();
        if (! empty($data['patient'])) {
            $import_data['patient'] = $data['patient']['export']['patient'];
        }
        if (! empty($data['falls'])) {
            $import_data['falls'] = $data['falls']['export']['elebm'];
        }
        if (! empty($data['patient2diagno_code'])) {
            $import_data['patient2diagno_code'] = $data['patient2diagno_code']['export']['gest-diag'];
        }
        if (! empty($data['diagno_code2diagno_data'])) {
            $import_data['diagno_code2diagno_data'] = $data['diagno_code2diagno_data']['export']['diagnose'];
        }
        
        if (! empty($data['healthinsurance'])) {
            $import_data['healthinsurance'] = $data['healthinsurance']['export']['kk'];
        }
        
        if (! empty($data['patient_course'])) {
            $import_data['patient_course'] = $data['patient_course']['export']['bdoc'];
        }
//         dd($import_data['falls']);
//         dd($import_data['patient2diagno_code']);
        $gender_mapping = array(
            "W" => "2",
            "M" => "1"
        );
 

        
        // patients
        $import_final_data = array();
        $all_patients = array();
        $patient_details = array();
        foreach ($import_data['patient'] as $k => $patient) {
            if( !in_array($patient['p-nr'], $imported_ids)){
                $all_patients[] = $patient['p-nr'];
            }
        }
        $limit = 500;
        $all_patients = array_unique($all_patients);
        $all_patients = array_slice($all_patients, 0, $limit);   // returns first 30  elements
        
        $patient_details = array();
        foreach ($import_data['patient'] as $k => $patient) {
            if( in_array($patient['p-nr'], $all_patients)){
                $patient_details[$patient['p-nr']]['import_pat'] = $patient['p-nr'];
                $patient_details[$patient['p-nr']]['first_name'] = $patient['p-vname'];
                $patient_details[$patient['p-nr']]['last_name'] = $patient['p-name'];
                $patient_details[$patient['p-nr']]['sex'] = $gender_mapping[$patient['p-sex']];
                $patient_details[$patient['p-nr']]['birthd'] = $patient['p-birth'];
                if(isset($patient['p-hausnummer'])){
                    $patient_details[$patient['p-nr']]['street1'] = $patient['p-street'] . ' ' . $patient['p-hausnummer'];
                } else{
                    $patient_details[$patient['p-nr']]['street1'] = $patient['p-street'];
                }
                $patient_details[$patient['p-nr']]['zip'] = $patient['p-plz'];
                $patient_details[$patient['p-nr']]['city'] = $patient['p-ort'];
                
                $patient_details[$patient['p-nr']]['memo']['memo'] = $patient['p-frei'];
                
                $patient_details[$patient['p-nr']]['PatientHealthInsurance']['insurance_no'] = $patient['p-vnr'];
                $patient_details[$patient['p-nr']]['PatientHealthInsurance']['insurance_status'] = $patient['p-vs'];
                $patient_details[$patient['p-nr']]['PatientHealthInsurance']['institutskennzeichen'] = $patient['p-ik'];
            }
        }
//         print_r($patient_details); exit;
        // patients 2 falls
        $patients_falls = array();
//         $patients_falls = array_filter($import_data['falls'], function ($val, $key)
//         {
//             return $val['el-leist'] == '91511' || $val['el-leist'] == '92010';
//         });
        
        foreach($import_data['falls'] as $kpf=>$val){
            if($val['el-leist'] == '91511' || $val['el-leist'] == '92010'){
                $patients_falls[] = $val;
            }
        }
        $patients_falls = array_values($patients_falls);
        
        
        $patient2admission = array();
        foreach ($patients_falls as $k => $val) {
            $patient2admission[$val['el-pat']]['admission_date'] = date("Y-m-d H:i:s", strtotime($val['el-datum']));// last 
        }
        
        
        $patient2fall_array = array();
        
        foreach($all_patients as $pat_id){
            
            if( empty($patient2admission[$pat_id]) && !in_array($pat_id,$active_patient_ids)) {
                
                $patient2fall_array[$pat_id]['isstandby'] = "1";
                $patient2fall_array[$pat_id]['isdischarged'] = "0";
//                 $patient2fall_array[$pat_id]['admission_date'] = date("Y-m-d H:i:s");
                $patient2fall_array[$pat_id]['admission_date'] = "2018-01-01 00:00:00";
                $patient2fall_array[$pat_id]['STATUS'] = "STANDBY_NO_DATA";
                
            } else {
                
                if(in_array($pat_id,$active_patient_ids)){
                    
                    if(!empty($patient2admission[$pat_id]['admission_date'])){
                        
                        $patient2fall_array[$pat_id]['isstandby'] = "0";
                        $patient2fall_array[$pat_id]['isdischarged'] = "0";
                        $patient2fall_array[$pat_id]['admission_date'] = $patient2admission[$pat_id]['admission_date'];
                        $patient2fall_array[$pat_id]['STATUS'] = "ACTIVE";
                        
                    } else{
                        
                        $patient2fall_array[$pat_id]['isstandby'] = "1";
                        $patient2fall_array[$pat_id]['isdischarged'] = "0";
//                         $patient2fall_array[$pat_id]['admission_date'] = date("Y-m-d H:i:s");
                        $patient2fall_array[$pat_id]['admission_date'] = "2018-01-01 00:00:00";
                        $patient2fall_array[$pat_id]['STATUS'] = "STANDBY_ACTIVE";
                    }
                }
                elseif(in_array($pat_id,$paused_patient_ids))
                {
                    $patient2fall_array[$pat_id]['isstandby'] = "0";
                    $patient2fall_array[$pat_id]['isdischarged'] = "1";
                    $patient2fall_array[$pat_id]['admission_date'] = $patient2admission[$pat_id]['admission_date'];
                    $patient2fall_array[$pat_id]['discharge_date'] = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($patient2admission[$pat_id]['admission_date'])));
                    $patient2fall_array[$pat_id]['discharge_method'] = "2379";
                    $patient2fall_array[$pat_id]['STATUS'] = "DISCHARGE_PAUSE";
                    
                } else {
                    
                    $patient2fall_array[$pat_id]['isstandby'] = "0";
                    $patient2fall_array[$pat_id]['isdischarged'] = "1";
                    $patient2fall_array[$pat_id]['admission_date'] = $patient2admission[$pat_id]['admission_date'];
                    $patient2fall_array[$pat_id]['discharge_date'] = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($patient2admission[$pat_id]['admission_date'])));
                    $patient2fall_array[$pat_id]['discharge_method'] = "2459";
                    $patient2fall_array[$pat_id]['STATUS'] = "DISCHARGE_IMPORT";
                }
            }
        }
        
        foreach ($all_patients as $pall_id_val) {
            
            $patient_details[$pall_id_val]['falls'] = $patient2fall_array[$pall_id_val];
        }
        //dd(count($patient_details),$patient_details);
        
        // diagnosis
        foreach ($import_data['diagno_code2diagno_data'] as $ddk => $ddv) {
            
            $master_diagnosis[$ddv['d-id']] = $ddv;
        }
        $d = 0;
        $patient2diagnosis_array = array();
        $patient2diagnosis = array();
        $diagno_ident = array();
        foreach ($import_data['patient2diagno_code'] as $dpk => $dpv){
            $patient2diagnosis_array[$dpv['gd-pat']][] = $dpv;
            if ( (!isset($diagno_ident[$dpv['gd-pat']]) ||  ! in_array($dpv['gd-id'], $diagno_ident[$dpv['gd-pat']])) ){
                
                if(   (isset($master_diagnosis[$dpv['gd-id']]['d-icd']) && strlen($master_diagnosis[$dpv['gd-id']]['d-icd']) > 0 ) 
                   || (isset($master_diagnosis[$dpv['gd-id']]['d-text0']) && strlen($master_diagnosis[$dpv['gd-id']]['d-text0']) > 0 ) 
                    ){
                    
                
                $diagno_ident[$dpv['gd-pat']][] =  $dpv['gd-id'];
                $patient2diagnosis[$dpv['gd-pat']][$d]['patient'] = $dpv['gd-pat'];
                $patient2diagnosis[$dpv['gd-pat']][$d]['gd-datum'] = date('Y-m-d H:i:s', strtotime($dpv['gd-datum']));
                $patient2diagnosis[$dpv['gd-pat']][$d]['icd'] = $master_diagnosis[$dpv['gd-id']]['d-icd'];
                $patient2diagnosis[$dpv['gd-pat']][$d]['free_text'] = $master_diagnosis[$dpv['gd-id']]['d-text0'];
                $patient2diagnosis[$dpv['gd-pat']][$d]['tabname'] = "diagnosis_freetext"; // ENCRYPTED
                $d ++;
                }
            }
        }
        
        foreach ($all_patients as $pall_id_val) {
            
            $patient_details[$pall_id_val]['diagnosis'] = array_values($patient2diagnosis[$pall_id_val]);
            
        }
        
        $patients_course = array();
        foreach($import_data['patient_course'] as $k=>$pcvl){
            if($pcvl['bd-type'] == "bem"){
                $patients_course[] = $pcvl;
            }
        }
        $patients_course = array_values($patients_course);
        
        
        $pc = 0 ; 
        $patient2patientcourse = array();
        foreach($patients_course as $k=>$pcv){
            $patient2patientcourse[$pcv['bd-pat']][$pc]['course_type'] = "K";
            $patient2patientcourse[$pcv['bd-pat']][$pc]['course_title'] = $pcv['bd-text'];
            $patient2patientcourse[$pcv['bd-pat']][$pc]['course_date'] = date("Y-m-d H:i:s",strtotime($pcv['bd-datum']));
            $patient2patientcourse[$pcv['bd-pat']][$pc]['done_date'] = date("Y-m-d H:i:s",strtotime($pcv['bd-datum']));
            $pc++;
        }
        
        foreach ($all_patients as $pall_id_val){
            $patient_details[$pall_id_val]['patient_course'] = array_values($patient2patientcourse[$pall_id_val]);
        }
        
        
        $import_form = new Application_Form_Import();
        $done_ids = $import_form->patient_import_handler_wl_hamm($patient_details);
        
        if(! empty($done_ids)){
            echo "DONEEE imported ".$limit.' patients <br/>' ;
            $sql = "p.*,e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
            $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
            $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
            $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
            $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
            $sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
            $sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
            $sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
            $sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
            $sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
            $sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
            $sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
            
            $patient_imported = Doctrine_Query::create()
            ->select($sql)
            ->from('PatientMaster p')
            ->leftJoin("p.EpidIpidMapping e")
            ->andWhereIn('e.clientid', array($clientid))
            ->andWhere("p.import_pat != ''")
            ->andWhereIn("p.import_pat",$done_ids)
            ->andWhere("p.isdelete = 0 ")
            ->fetchArray();
            
            $imported_pdata ="";
            if(!empty($patient_imported)){
                foreach($patient_imported as $k=>$pd){
//                     $imported_pdata .= $pd['lastname'].", ".$pd['firstname'].'  [ '.$pd['ipid'].'   - '.$pd['import_pat'].'] <br/>' ;
                    $imported_pdata .= $pd['lastname'].", ".$pd['firstname'].'  [ '.$pd['import_pat'].'] <br/>' ;
                }
            }
            echo $imported_pdata ;
            
        } else{
            echo "none imported";
        }
        
        exit;
         
    }
    
    public function histatusAction(){
        exit;
        $logininfo = new Zend_Session_Namespace('Login_Info');
        
        //required vars
        $clientid = $logininfo->clientid;
//         $status_int_array = array("M" => "1", "F" => "3", "R" => "5");
        $status_int_array = array("1" => "M", "3" => "F", "5" => "R");
        
 
        $patient_imported = Doctrine_Query::create()
        ->select("p.ipid,p.import_pat,e.epid")
        ->from('PatientMaster p')
        ->leftJoin("p.EpidIpidMapping e")
        ->andWhereIn('e.clientid', array($clientid))
        ->andWhere("p.import_pat != ''")
        ->andWhere("p.isdelete = 0 ")
        ->fetchArray();
        
        $ps = array();
        $pdet = array();
        foreach($patient_imported as $k=>$ipid_d){
            $ps[] = $ipid_d['ipid'];
            $pdet[$ipid_d['ipid']] = $ipid_d['EpidIpidMapping']['epid'];
        }
        
        print_R($ps);
        
        //PatientHealthInsurance
        $sql = "ipid, AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status";
        $sql.=",insurance_no as insurance_no";
        $sql.=",institutskennzeichen as institutskennzeichen";
        $sql.=",kvk_no as kvk_no, companyid";
        $sql.=",rezeptgebuhrenbefreiung as rezeptgebuhrenbefreiung";
        $sql.=",exemption_till_date as exemption_till_date"; // ISPC - 2079
        $sql.=",privatepatient as privatepatient";
        $sql.=",direct_billing as direct_billing";
        $sql.=",bg_patient as bg_patient";
        $sql.=",private_valid_contribution as private_valid_contribution";
        $sql.=",private_contribution as private_contribution";
        $sql.=",AES_DECRYPT(status_added,'" . Zend_Registry::get('salt') . "') as status_added";
        $sql.=",AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name";
        $sql.=",AES_DECRYPT(ins_insurance_provider,'" . Zend_Registry::get('salt') . "') as ins_insurance_provider";
        $sql.=",AES_DECRYPT(ins_first_name,'" . Zend_Registry::get('salt') . "') as ins_first_name";
        $sql.=",AES_DECRYPT(ins_middle_name,'" . Zend_Registry::get('salt') . "') as ins_middle_name";
        $sql.=",AES_DECRYPT(ins_last_name,'" . Zend_Registry::get('salt') . "') as ins_last_name";
        $sql.=",AES_DECRYPT(ins_contactperson,'" . Zend_Registry::get('salt') . "') as ins_contactperson";
        $sql.=",AES_DECRYPT(ins_zip,'" . Zend_Registry::get('salt') . "') as ins_zip";
        $sql.=",AES_DECRYPT(ins_city,'" . Zend_Registry::get('salt') . "') as ins_city";
        $sql.=",AES_DECRYPT(ins_phone,'" . Zend_Registry::get('salt') . "') as ins_phone";
        $sql.=",AES_DECRYPT(ins_phone2,'" . Zend_Registry::get('salt') . "') as ins_phone2";
        $sql.=",AES_DECRYPT(ins_phonefax,'" . Zend_Registry::get('salt') . "') as ins_phonefax";
        $sql.=",AES_DECRYPT(ins_post_office_box,'" . Zend_Registry::get('salt') . "') as ins_post_office_box";
        $sql.=",AES_DECRYPT(ins_post_office_box_location,'" . Zend_Registry::get('salt') . "') as ins_post_office_box_location";
        $sql.=",AES_DECRYPT(ins_email,'" . Zend_Registry::get('salt') . "') as ins_email";
        $sql.=",AES_DECRYPT(ins_debtor_number,'" . Zend_Registry::get('salt') . "') as ins_debtor_number";
        $sql.=",AES_DECRYPT(ins_zip_mailbox,'" . Zend_Registry::get('salt') . "') as ins_zip_mailbox";
        $sql.=",AES_DECRYPT(ins_street,'" . Zend_Registry::get('salt') . "') as ins_street";
        $sql.=",AES_DECRYPT(help1,'" . Zend_Registry::get('salt') . "') as help1";
        $sql.=",AES_DECRYPT(help2,'" . Zend_Registry::get('salt') . "') as help2";
        $sql.=",AES_DECRYPT(help3,'" . Zend_Registry::get('salt') . "') as help3";
        $sql.=",AES_DECRYPT(help4,'" . Zend_Registry::get('salt') . "') as help4";
        $sql.=",AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment";
        
        $ph = Doctrine_Query::create()
        ->select($sql)
        ->from('PatientHealthInsurance')
        ->whereIn("ipid",$ps)
        ->fetchArray();

        $change_ids = array();
        foreach($ph as $k=>$p){
            if(in_array($p['insurance_status'], array('1','3','5') )){
//                 $sts[$p['insurance_status']][] = $p['ipid'];
                $change_ids[] = $p['id'];
            }
        }
//  dd($change_ids);
        $upd_arra = array();
        foreach($ph as $k=>$p){
            if(isset($status_int_array[$p['insurance_status']]) && in_array($p['id'],$change_ids)){
                
                $upd_arra[] = $pdet[$p['ipid']].' '.$p['insurance_status'].' - > '.$status_int_array[$p['insurance_status']];
                
                $cust = Doctrine::getTable('PatientHealthInsurance')->findOneBy('id',$p['id']);
                $cust->insurance_status = Pms_CommonData::aesEncrypt($status_int_array[$p['insurance_status']]);
                $cust->insurance_status = Pms_CommonData::aesEncrypt("");
                $cust->save();
                
            }
        }

        echo "<pre>";    
        print_R($upd_arra);
        
        exit;
    }
    
		
	public function updateflnameAction(){

	    echo "update - flname";
	    exit; // it was run on live server on 13.07.2018
	     
	    set_time_limit(0);
// 	    error_reporting(E_ALL);
	    // get imported patients
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }

	    $patient_imported = Doctrine_Query::create()
	    ->select("p.*,e.*")
	    ->from('PatientMaster p')
	    ->leftJoin("p.EpidIpidMapping e")
	    ->andWhereIn('e.clientid', array($clientid))
	    ->andWhere("p.import_pat != ''")
	    ->andWhere("p.isdelete = 0 ")
	    ->fetchArray();
	     
	    $imported_ids =  array();
	    $import_id2ipid =  array();
	    $import_id2id =  array();
	    
	    $i2p =  array();
	    foreach($patient_imported as $k => $pat_val)
	    {
	        $imported_ids[] = $pat_val['import_pat'];
	        $import_id2ipid[$pat_val['import_pat']] =  $pat_val['ipid'];
	        $import_id2id[$pat_val['import_pat']] =  $pat_val['id'];

	        $i2p[$pat_val['import_pat']] =  $pat_val;
	    }
	    

	    $xmls = array(
	        'patient' => array(
	            'file_path' => PUBLIC_PATH . "/import/wl_hamm/PATIENT.XML"
	        )
	    );
	    
	    $data = array();
	    
	    libxml_use_internal_errors(true);
	    
	    foreach ($xmls as $xml_k => $xml) {
	    
	        $dom = new DomDocument('1.0', 'utf-8');
	        $dom->preserveWhiteSpace = false;
	        $dom->formatOutput = true;
	    
	        $result = $dom->load($xml['file_path']);
	    
	        if ($result === false) {
	            echo ($xml_1 . PHP_EOL . " - Document is not well formed ! iDie !");
	            continue;
	        }
	    
	        // DOM to array
	        $data[$xml_k] = Pms_XML2Array::createArray($dom);
	    }
	    
	    $import_data = array();
	    if (! empty($data['patient'])) {
	        $import_data['patient'] = $data['patient']['export']['patient'];
	    }
	    
	    
	    $all_patients = array();
	    $patient_details = array();
	    foreach ($import_data['patient'] as $k => $patient) {
	        if(in_array($patient['p-nr'], $imported_ids)){
	            $all_patients[] = $patient['p-nr'];
	        }
	    }
	    
	    
	    foreach ($import_data['patient'] as $k => $patient) {
	        if( in_array($patient['p-nr'], $all_patients)){
                $patient_details[$import_id2id[$patient['p-nr']]]['ipid'] = $import_id2ipid[$patient['p-nr']];
                
                $patient_details[$import_id2id[$patient['p-nr']]]['import_pat'] = $patient['p-nr'];
                $patient_details[$import_id2id[$patient['p-nr']]]['first_name'] = $patient['p-vname'];
                $patient_details[$import_id2id[$patient['p-nr']]]['last_name'] = $patient['p-name'];
                
                $patient_details[$import_id2id[$patient['p-nr']]]['last_update'] = $i2p[$patient['p-nr']]['last_update'];
                $patient_details[$import_id2id[$patient['p-nr']]]['last_update_user'] = $i2p[$patient['p-nr']]['last_update_user'];
	        }
	    }
	    
// 	    echo "<pre/>";
// 	    print_r(count($patient_details));
	    
// 	    exit;
// 	    exit;
// 	    exit;
// 	    exit;
	    
	    if(!empty($patient_details)){
	        foreach($patient_details as $pm_id =>$pdt){

	            $cust = Doctrine::getTable('PatientMaster')->find($pm_id);
	            if($cust){
    	            $res = Doctrine_Query::create()
    	            ->update('PatientMaster')
    	            ->set('last_name', '?', Pms_CommonData::aesEncrypt($pdt['last_name']))
    	            ->set('first_name', '?', Pms_CommonData::aesEncrypt($pdt['first_name']))
    	            ->set('last_update', '?', $pdt['last_update'] ) 
    	            ->set('last_update_user', '?', $pdt['last_update_user'] ) 
    	            ->where("id = ?",$pm_id)
    	            ->andwhere("ipid = ?",$pdt['ipid']);
    	            $resexc = $res->execute();
    	            
	            }
	        }
	    }
	    
	    
	    ECHO "success";
	    exit;
	   
	    
	}	
    


    /**
     * 
     * TODO-1970
     * plz PREPARE the import of these patients 
     * Ancuta: 14.12.2018
     * 
     */
	public function nrimportAction(){
	    ini_set("auto_detect_line_endings", true);
	    setlocale(LC_ALL, 'de_DE.UTF8');
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $import_session = new Zend_Session_Namespace('importSession');
	    $userid = $logininfo->userid;
	    $clientid = $logininfo->clientid;
	
	    set_time_limit(0);
	

	    //estabilish a delimiter if none was provided via post
	    if(strlen(trim(rtrim($_POST['csvdelimiter']))) > 0)
	    {
	        $delimiter = trim(rtrim($_POST['csvdelimiter']));
	        $this->view->delimiter = trim(rtrim($_POST['csvdelimiter']));
	    }
	    else
	    {
	        $delimiter = ";";
	        $this->view->delimiter = ";";
	    }
	
	    //process post action
	    if($this->getRequest()->isPost() || strlen($filename) != 0)
	    {
	
	        $dir = "uploadfile/";
	        if(strlen($filename) == 0)
	        {
	            $filename = $dir . $_SESSION['filename'];
	        }
	        else
	        {
	            $filename = $dir . $filename;
	        }
	        $error = '0';
	
	        if(!is_writable($filename))
	        {
	            $this->view->error = 'file_open_error';
	            $this->write_log('1', 'Load import file procedure error! File [' . $filename . '] does not exist!.');
	            $error = 1;
	        }
	
	
	        if($error == '0')
	        {
	            $this->write_log('1', 'CSV Import: Start');
	            $this->view->error = '';
	            $handle = fopen($filename, "r");
	            //parse csv into an array
	            while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
	            {
	                foreach($data as $k_data => $v_data)
	                {
	                    $data[$k_data] = htmlspecialchars($v_data);
	                }
	
	
	                $csv_data[] = $data;
	            }
	            fclose($handle);
	
	
	            $import_form = new Application_Form_Import();
	
	            if($_REQUEST['dbg'] == "1"){
	                print_R($csv_data); exit;
	            }
	
	            if(strlen($_POST['csv_type']) > 0 )
	            {
	                if($_POST['csv_type'] == "patients")
	                {
	                    
	                    
	                    $import_form->patient_import_handler_nr_mambo($csv_data, $_POST);
	
	                    $this->_redirect(APP_BASE . 'import/nrimport?flg=suc');
	                    exit;
	                }
                    else
                    {
                        $this->_redirect(APP_BASE . 'import/nrimport?flg=suc');
                        exit;
                    }
	            }
	            else
	            {
	                $this->_redirect(APP_BASE . 'import/nrimport?flg=suc');
	                exit;
	            }
	
	
	            $import_session->userid = '';
	            $import_session->target_patient = '';
	            $import_session->import_value = '';
	            $import_session->filename = '';
	            $import_session->delimiter = '';
	            $this->_redirect(APP_BASE . 'import/nrimport?flg=suc');
	            exit;
	            	
	        }
	    }
	}
	
	
	public function slimportAction(){
exit;
// 	    ini_set('display_errors', 1);
// 	    ini_set('display_startup_errors', 1);
// 	    error_reporting(E_ALL);
	    set_time_limit(0);
	    $this->_helper->layout->setLayout('layout');
	    $this->_helper->viewRenderer->setNoRender();
	    
	    $filename_local = PUBLIC_PATH ."/import/sl/patients.csv";
	    
	    $handle_local_file = fopen($filename_local, "r");
	    $delimiter_local_file = ";";
	    //parse csv into an array
	    while(($data_local = fgetcsv($handle_local_file, NULL, $delimiter_local_file)) !== FALSE)
	    {
	     
	        foreach($data_local as $k_data => $v_data)
	        {
	            $data_local[$k_data] = htmlspecialchars($v_data);
	        }
	         
	         
	        $csv_data[] = $data_local;
	    }
	    fclose($handle_local_file);
 
	    // 26.06.2019 Ancuta
	    $import_form = new Application_Form_Import();
	    $import_form->patient_import_handler_sl_2019_TODO2363($csv_data, $_POST);
	    
	    $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
	    exit;
	}
	
	
	public function heimportAction(){
exit;
// 	    ini_set('display_errors', 1);
// 	    ini_set('display_startup_errors', 1);
// 	    error_reporting(E_ALL);
	    set_time_limit(0);
	    $this->_helper->layout->setLayout('layout');
	    $this->_helper->viewRenderer->setNoRender();
	    
// 	    $filename_local = PUBLIC_PATH ."/import/hepatients/patients_original.csv";
	    $filename_local = PUBLIC_PATH ."/import/hepatients/patients_clean.csv";
	    
	    $handle_local_file = fopen($filename_local, "r");
	    $delimiter_local_file = ";";
	    //parse csv into an array
	    while(($data_local = fgetcsv($handle_local_file, NULL, $delimiter_local_file)) !== FALSE)
	    {
	     
	        foreach($data_local as $k_data => $v_data)
	        {
	            $data_local[$k_data] = htmlspecialchars($v_data);
	        }
	         
	         
	        $csv_data[] = $data_local;
	    }
	    fclose($handle_local_file);
 
	    // 26.06.2019 Ancuta
	    $import_form = new Application_Form_Import();
	    $import_form->patient_import_handler_he_2019_TODO2382($csv_data, $_POST);
	    
	    $this->_redirect(APP_BASE . 'import/lmuimport?flg=suc');
	    exit;
	}


	public function slimportfixAction(){
	EXIT;
	    $this->_helper->layout->setLayout('layout');
	    $this->_helper->viewRenderer->setNoRender();

	    //init
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    
	    //required vars
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    
	    $patient = Doctrine_Query::create()
	    ->select("p.ipid")
	    ->from('PatientMaster p')
	    ->leftJoin("p.EpidIpidMapping e")
	    ->andWhereIn('e.clientid', array($clientid))
	    ->andWhere("p.isdelete= 0 ")
	    ->andWhere("p.isdischarged = 1 ")
	    ->andWhere("date(p.create_date) = '2019-07-01'")
	    ->andWhere("p.create_user = 3031");
	    $patient_details = $patient->fetchArray();
	    
	    
	    
	    foreach($patient_details as $k=>$pipids){
	        $ipids[] = $pipids['ipid'];
	    }
// 	    PatientDischarge
	    
	    $patientd = Doctrine_Query::create()
	    ->select('*')
	    ->from('PatientDischarge')
	    ->whereIn('ipid',$ipids);
	    $patient_dis_details = $patientd->fetchArray();

	   
	    

	    // get client discharge methods
	    $client_dm_q = Doctrine_Query::create()
	    ->select('*')
	    ->from('DischargeMethod')
	    ->where('clientid =?',$clientid)
	    ->andWhere('isdelete=0');
	    $client_dm = $client_dm_q->fetchArray();
	     
	    if( ! empty($client_dm)){
	        foreach($client_dm as $k=>$dm_data){
	            $client_data['discharge_methods'][$dm_data['id']] = $dm_data['description'];
	        }
	    }
// 	    dd($patient_dis_details);
	    foreach($patient_dis_details as $kd=>$pdis){
    	    $comment[$pdis['ipid']] = "Patient wurde am ".date('d.m.Y H:i', strtotime($pdis['discharge_date']))."  entlassen \n Entlassungsart : ".$client_data['discharge_methods'][$pdis['discharge_method']]."\n ";
	    }
// 	    print_r($client_data['discharge_methods']);
	    
// 	    $ipids = array("988ca2b14b5bfbf75a07a67a1dad41ae7e3918e6");
	    
	    $patient_pc = Doctrine_Query::create()
	    ->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
						AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,
						AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name")
	    ->from('PatientCourse')
	    ->whereIn('ipid',$ipids)
	    ->andWhere("date(create_date) = '2019-07-01'")
	    ->andWhere("create_user = 3031")
	    ->andWhere("aes_decrypt(course_title,'encrypt') LIKE '%Patient wurde am%'");
	    $patient_pc_details = $patient_pc->fetchArray();
	    
	    echo "<pre/>";
	    print_r($patient_pc_details); 
// exit;
	    
	    foreach($patient_pc_details as $k=>$pc){

// 	        if($pc['ipid'] == "988ca2b14b5bfbf75a07a67a1dad41ae7e3918e6"){
	            
    	        $cust = Doctrine::getTable('PatientCourse')->find($pc['id']);
    	        if($cust){
                    $cust->course_title=Pms_CommonData::aesEncrypt($comment[$pc['ipid']]);
                    $cust->save();
                    $updated[] = $comment[$pc['ipid']]; 
    	        }
// 	        }
	    }
	    echo "<pre/>DONE";
	    print_r($updated);
	    exit;
// 	    Patient wurde am  : entlassen
	}
	
	//ISPC-2623 Carmen 20.08.2020
	public function importcsvdatevAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		$this->_helper->layout->setLayout('layout_ajax');
		$this->_helper->viewRenderer->setNoRender();
		
		$fid = $_REQUEST['fid'];
		$importfiledata = InvoicePaymentsImportStatusTable::getInstance()->findOneById($fid, Doctrine_Core::HYDRATE_ARRAY);
		$invoice_type = $importfiledata['invoice_type'];
		
		if($invoice_type == "sh_invoice" ){
			$invoicetable = 'ShInvoices';
			$invoicepaymentstable = 'ShInvoicePayments';
		} else if($invoice_type == "bayern_sapv_invoice")  {
			
		} else if($invoice_type == "bw_medipumps_invoice")  {
			
		} else if($invoice_type == "bw_sapv_invoice_new")  {
			
		}else if($invoice_type == "hospiz_invoice")  {
			
		}else if($invoice_type == "rlp_invoice")  {
			
		}else if($invoice_type == "bre_kinder_invoice")  {
			
		}else if($invoice_type == "nr_invoice")  {
			
		}else if($invoice_type == "demstepcare_invoice")  {
			
		}
		
		$dir = 'uploadfile/';
		$oldurl = urldecode($_REQUEST['oldurl']);
		$uploadfile = $dir.$importfiledata['filename'];
		$delimiter = $importfiledata['filedelimiter'];

		if (!empty($importfiledata) && $uploadfile != '') {
			
			$filecontent = unserialize($importfiledata['filecontent']);

			
				foreach($filecontent as $krow => $vrow)
				{
					$vr = explode($delimiter, $vrow);
					
					$pd = '';
					$pinv = '';
					$ammount = '';
					if($krow == 0 || $krow == 1) continue;
					
					foreach($vr as $kc => $vc)
					{
						$vc = trim($vc);
						
						if($kc == 1 && $vc != '')
						{
							$pd = date('Y-m-d', strtotime($vc));
						}
						if($kc == '5')
						{
							$phi = $vc;
						}
						if($kc == '7')
						{
							$pinv = $vc;
						}
						if($kc == '8')
						{
							$ammount = $vc;
						}
					}
					if($pd != '' && $pinv != '' && $ammount != '' && $phi != '')
					{
					    //TODO-3800 Ancuta 02.02.2021
					    if(strpos($ammount,'.') !== false && strpos($ammount,',') !== false){
					        $ammount = str_replace('.','',$ammount);
					        $ammount = str_replace(',','.',$ammount);
					    } 
					    else if(strpos($ammount,'.') === false && strpos($ammount,',') !== false){
					        $ammount = str_replace(',','.',$ammount);
					    }
					    else if(strpos($ammount,'.') !== false && strpos($ammount,',') === false){
					        //do nothing 
					    }
					    // --
						$data_from_csv[] = array(
								'invoice' => $pinv,
								//TODO-3800 Ancuta 02.02.2021
								//'amount' => (float)str_replace(array('.', ','), array('', '.'), $ammount),
						        'amount' => (float)$ammount,
							    //--
								'paid_date' => $pd,
								'healthinsurance' => $phi
						);
					}
		    
				}
				
				if(!empty($data_from_csv))
				{
					$invoices = Doctrine_Query::create()
					->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
					->from($invoicetable)
					->andWhere('isdelete = "0"')
					->andWhere('client =?', $clientid)
					->andWhere('status = ? or status = ?', array('2', '5'));
					//->andWhere('isarchived = "0"');
					$invoices_res = $invoices->fetchArray();
		
					foreach($invoices_res as $k_inv => $v_inv)
					{
						if($v_inv['storno'] != "1")
						{
							 
							$invids[] = $v_inv['id'];
							$invoices_by_nr[$v_inv['prefix'].$v_inv['invoice_number']] = $v_inv;
						}
					}
	
					$invp = Doctrine_Query::create()
					->select("*, SUM(amount) as paid_sum")
					->from($invoicepaymentstable)
					->WhereIn("invoice", $invids)
					->andWhere('isdelete = 0')
					->groupBy('invoice');
		
					$paym_array = $invp->fetchArray();
					foreach($paym_array as $kp => $vp)
					{
						$invpids[] = $vp['invoice'];
						$paymentsinv[$vp['invoice']] = $vp;
					}
					
					$data_for_import = array();
					$data_about_import = array();
					$data_allpayed_import = array();
					$data_payed_import = array();
					$data_payed_amount_import = array();
					$ipm_index = 0;
					
 
					foreach($data_from_csv as $invi => $vpay)
					{
						if(array_key_exists($vpay['invoice'], $invoices_by_nr))
						{
							$invid = $invoices_by_nr[$vpay['invoice']]['id'];
		
		
							if(in_array($invid, $invpids) || in_array($invid, $data_payed_import))
							{
								$total_payment = $paymentsinv[$invid]['paid_sum'] + $vpay['amount'];
								foreach($data_payed_amount_import[$invid] as $vpaid)
								{
									$total_payment += $vpaid;
								}
							}
							else
							{
								$total_payment = $vpay['amount'];
							}
		
							
							if($total_payment > $invoices_by_nr[$vpay['invoice']]['invoice_total'])
							{
								$notpayed_invoices[] = array(
										'id' => $invid,
										'inv_number' => $vpay['invoice'],
										'inv_total' => $invoices_by_nr[$vpay['invoice']]['invoice_total'],
										'pay_total' => $total_payment,
								);
							}
							else
							{
								if($total_payment == $invoices_by_nr[$vpay['invoice']]['invoice_total'])
								{
									$data_allpayed_import[] = $invid;
								}
								else
								{
									$data_payed_import[] = $invid;
									$data_payed_import = array_unique($data_payed_import);
									$data_payed_amount_import[$invid][] = $vpay['amount'];
								}
								$data_for_import[$ipm_index]['invoice'] = $invid;
								$data_for_import[$ipm_index]['amount'] = $vpay['amount'];
								$data_for_import[$ipm_index]['paid_date'] = $vpay['paid_date'];
								
								$data_about_import[$ipm_index]['invoice_id'] = $invid;
								$data_about_import[$ipm_index]['invoice_number'] = $vpay['invoice'];
								$data_about_import[$ipm_index]['amount'] = $vpay['amount'];
								$data_about_import[$ipm_index]['paid_date'] = $vpay['paid_date'];
								$data_about_import[$ipm_index]['healthinsurance'] = $vpay['healthinsurance'];
								$ipm_index++;
							}
						}
					}
				
				if(!empty($notpayed_invoices_XXX)) //TODO-3800 Ancuta 16.02.2021 - changed the array - so we skip this condition
				{
					$import_info['status'] = 'error';
					foreach($notpayed_invoices as $kni => $vni)
					{
						//echo 'Rechnung '.$vni['inv_number']. 'has a total payed sum = '.$vni['pay_total']. ' and the invoice total= '.$vni['inv_total']."<br />";
						$import_info['message'][] = 'Rechnung '.$vni['inv_number']. ' has a total payed sum = '.$vni['pay_total']. ' and the invoice total= '.$vni['inv_total'];
					}
					$import_info['message'][] = 'The import was canceled';
					$import_info_string = serialize($import_info);
					
					$import_status_entity = InvoicePaymentsImportStatusTable::getInstance()->find($fid, Doctrine_Core::HYDRATE_RECORD);
					$import_status_entity->import_date =  date('Y-m-d H:i:s', time());
					$import_status_entity->status = $import_info_string;
					$import_status_entity->save();
				}
				else
				{
					
					if(!empty($data_for_import))
					{
						$import_info['status'] = 'success';
						$collectionpaym = new Doctrine_Collection($invoicepaymentstable);
						$collectionpaym->fromArray($data_for_import);
						$collectionpaym->save();						
						$paymentsresult = $collectionpaym->toArray();
						
						$invoicedpayed = array_unique(array_column($paymentsresult, 'invoice'));
						
						if(!empty($paymentsresult))
						{
							$invt = new $invoicetable();
							foreach($data_allpayed_import as $vps)
							{
									$invent = $invt->getTable()->find($vps, Doctrine_Core::HYDRATE_RECORD);
									$invent->status = "3";
									$invent->save();
							}

							$paymentmess = str_replace('%invoice_payed', count($invoicedpayed), $this->view->translate('payments were imported for X invoices'));
							$import_info['message'][] = count($paymentsresult). ' ' .$paymentmess;
							
							//TODO-3800 Ancuta 16.02.2021 - Include the not payd to message 
							if(!empty($notpayed_invoices))
							{
							    $import_info['status_extra'] = 'error';
							    foreach($notpayed_invoices as $kni => $vni)
							    {
							        $import_info['message'][] = 'Die Rechnung '.$vni['inv_number']. ' hat eine gesamt bezahlte Summe von = '.$vni['pay_total']. ' und der Gesamtbetrag der Rechnung beträgt = '.$vni['inv_total'];
							    }
							    $import_info['message'][] = $this->view->translate('The import was not done completely');
							}
							// -- 
							$import_info_string = serialize($import_info);
							
							$import_status_entity = InvoicePaymentsImportStatusTable::getInstance()->find($fid, Doctrine_Core::HYDRATE_RECORD);
							$import_status_entity->import_date =  date('Y-m-d H:i:s', time());
							$import_status_entity->status = $import_info_string;
							$import_status_entity->save();
							
							foreach($paymentsresult as $kp => $vp)
							{	
								$data_about_import[$kp]['clientid'] = $clientid;
								$data_about_import[$kp]['payment_id'] = $vp['id'];
								$data_about_import[$kp]['payment_table'] = $invoicepaymentstable;
								$data_about_import[$kp]['import_file_id'] = $import_status_entity->id;
							}
						//var_dump($data_about_import); exit;
							$collectionimp = new Doctrine_Collection('InvoicePaymentsImport');
							$collectionimp->fromArray($data_about_import);
							$collectionimp->save();
						}
					}
					else 
					{
						$import_info['status'] = 'error';
						$import_info['message'][] = 'Es wurden keine Zahlungen importiert, da keine Rechnungen gefunden wurden';
						$import_info_string = serialize($import_info);
						
						$import_status_entity = InvoicePaymentsImportStatusTable::getInstance()->find($fid, Doctrine_Core::HYDRATE_RECORD);
						$import_status_entity->import_date =  date('Y-m-d H:i:s', time());
						$import_status_entity->status = $import_info_string;
						$import_status_entity->save();
					}
				}
		}
		else
		{
			$import_info['status'] = 'error';
			$import_info['message'][] = 'File corrupted or no data to be imported';
			$import_info_string = serialize($import_info);
				
			$import_status_entity = InvoicePaymentsImportStatusTable::getInstance()->find($fid, Doctrine_Core::HYDRATE_RECORD);
			$import_status_entity->import_date =  date('Y-m-d H:i:s', time());
			$import_status_entity->status = $import_info_string;
			$import_status_entity->save();
		}
		
		
	}
	else 
	{

		$import_info['status'] = 'error';
		$import_info['message'][] = 'File corrupted or no data to be imported';
		$import_info_string = serialize($import_info);
		
		$import_status_entity = InvoicePaymentsImportStatusTable::getInstance()->find($fid, Doctrine_Core::HYDRATE_RECORD);
		$import_status_entity->import_date =  date('Y-m-d H:i:s', time());
		$import_status_entity->status = $import_info_string;
		$import_status_entity->save();
	}
	
	$this->redirect($oldurl.'?flg='.$import_info['status'].'&responseid='.$fid);
	}
	
	}
?>