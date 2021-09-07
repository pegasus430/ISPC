<?php
class CoreSystemsSync {
	
	/**
	 * set the actual connection
	 * 
	 * @param string $id
	 *        	the id to set
	 */
	public function set_connection_name($id) {
		$this->connection_name = $id;
		foreach ( $this->systemlinks as $link ) {
			if ($link->id == $id && $this->clientid == $link->local) {
				$this->systemlink = $link;
			}
		}
	}
	
	/**
	 *
	 * @return Array ['id','name'] with all connectionnames from clientconfig
	 */
	public function get_connection_names() {
		return $this->systemlinks;
	}
	public function get_system_links() {
		return SystemsSyncConnections::getConnections ( $this->clientid );
	}
	
	/**
	 * Send all updates to Remote System for this Patient
	 */
	public function sendPatient($ipid, $test = false) {
		if ($test) {
			$data = array (
					'test' => 'Test' 
			);
			$urlpath = "/Systemssync/droptest";
		} else {
			
			$pat = Doctrine::getTable ( 'SystemsSyncPatients' )->findOneByIpid_hereAndConnectionAndClientid ( $ipid, $this->connection_name, $this->clientid );
			
			//put actual medication to the syncpackets
			$pd=new PatientDrugPlan();
			$pd->get_medication_exportdata($ipid, array(),array(),true);
			unset($pd);
			

			//Add diag-Package 
			PatientDiagnosis::get_exportdata($ipid, true);
			
			
			//Add ClinicVersorger
			$pd=new ClinicVersorger();
			$pd->generate_patient_exportpackage($ipid);
			unset($pd);
			
			//TODO-1890 added  Ancuta from Nico 16.11.2018
			//Add Versorger-Package
			$vv=new PatientDetails($ipid);
			$vv->create_syncpackage(true);
			//--
			
			$data = $this->getPatientdata ( $ipid );
			
			$data ['_meta'] = array (
					'ipid_here' => $ipid,
					'ipid_there' => $pat->ipid_there,
					'connection' => $this->connection_name
			);
			
			$urlpath = "/Systemssync/drop";
		}
		
		$html = $this->sendData ( $data, $urlpath );
		
		if (trim ( $html ) == "OK" && ! $test) {
			$pat->last_sent = date ( 'Y-m-d H:i:s' );
			$pat->save ();
		}
		
		return $html;
	}
	
	/**
	 * Send data by dropping a post-blob to the other systems Systemssync/drop-method
	 */
	public function sendData($data, $urlpath) {
		$data = serialize ( $data );
		$data = urlencode ( $data );
		
		$url = $this->systemlink->url;
		$username = $this->systemlink->user;
		$password = $this->systemlink->pass;
		
		$url = $url . $urlpath;
		$postinfo = "username=" . $username . "&password=" . $password;
		$x = rand ( 1000000, 9000000 );
		$cookie_file_path = "/tmp/ispc_curl_cookie_" . $x . ".txt";
		
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_HEADER, false );
		curl_setopt ( $ch, CURLOPT_NOBODY, false );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file_path );
		curl_setopt ( $ch, CURLOPT_COOKIE, "cookiename=0" );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "ISPC" );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_REFERER, $_SERVER ['REQUEST_URI'] );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 0 );
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postinfo );
		
		// 1st request
		
		$result1 = curl_exec ( $ch );
		
		if ($result1 === false) {
			$error_log1 = htmlspecialchars ( curl_error ( $ch ) );
		}
		
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, "payload=" . urlencode ( $data ) );
		
		// 2nd request
		
		// enable some debugging
		
		$data_log = 'DATA: ' . serialize ( $data );
		
		curl_setopt ( $ch, CURLOPT_VERBOSE, true );
		
		$verbose = fopen ( 'php://temp', 'w+' );
		curl_setopt ( $ch, CURLOPT_STDERR, $verbose );
		
		$result2 = curl_exec ( $ch );
		if ($html === false) {
			$error_log2 = htmlspecialchars ( curl_error ( $ch ) );
		}
		
		rewind ( $verbose );
		$exec_log2 = stream_get_contents ( $verbose );
		
		/*
		 * $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/sync.log');
		 * $log = new Zend_Log($writer);
		 * $log->info(serialize('Error1: '.$error_log1));
		 * $log->info(serialize('Error2: '.$error_log2."\n\n\n\n".'URl:'.$url."\n\n\n\n".'Data: '.$data_log."\n\n\n\n".'Exec2: '.$exec_log2));
		 */
		
		$html = $result2;
		
		curl_close ( $ch );
		
		unlink ( $cookie_file_path );
		
		

		if (APPLICATION_ENV != 'production') {
		    try {
		        $logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
		        $logger->info(PHP_EOL . __METHOD__ . __LINE__ );
		        $logger->info('Error1: '.$error_log1);
		        $logger->info("payload=" . urlencode ( $data ));
		        $logger->info($result2);
		    } catch (Zend_Controller_Action_Exception $e) {
		
		    }
		}
		
		return $html;
	}
	

	public function sendDataTest($data, $urlpath,$url) {
		$data = serialize ( $data );
		$data = urlencode ( $data );
//		print_r($data);
//	print_r($urlpath);
	//	$url = $this->systemlink->url;
		$username = 'clinic_system';
		$password = 'wq0fwaw53j';
		
		$url = $url . $urlpath;
		$postinfo = "username=" . $username . "&password=" . $password;
		//var_dump($postinfo); exit;
		$x = rand ( 1000000, 9000000 );
		$cookie_file_path = "/tmp/ispc_curl_cookie_" . $x . ".txt";
	//	print_R($url);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_HEADER, false );
		curl_setopt ( $ch, CURLOPT_NOBODY, false );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie_file_path );
		curl_setopt ( $ch, CURLOPT_COOKIE, "cookiename=0" );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "ISPC" );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_REFERER, $_SERVER ['REQUEST_URI'] );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 0 );
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postinfo );
		
		// 1st request
		
		$result1 = curl_exec ( $ch );
	//	print_R($result1); exit;
		if ($result1 === false) {
			$error_log1 = htmlspecialchars ( curl_error ( $ch ) );
		}
		
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, "payload=" . urlencode ( $data ) );
		
		// 2nd request
		
		// enable some debugging
		
		$data_log = 'DATA: ' . serialize ( $data );
		
		curl_setopt ( $ch, CURLOPT_VERBOSE, true );
		
		$verbose = fopen ( 'php://temp', 'w+' );
		curl_setopt ( $ch, CURLOPT_STDERR, $verbose );
		
		$result2 = curl_exec ( $ch );
		if ($html === false) {
			$error_log2 = htmlspecialchars ( curl_error ( $ch ) );
		}
		
		rewind ( $verbose );
		$exec_log2 = stream_get_contents ( $verbose );
		
		
		 $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/sync.log');
		 $log = new Zend_Log($writer);
		$log->info(serialize('Error1: '.$error_log1)); 
		 $log->info(serialize('Error2: '.$error_log2."\n\n\n\n".'URl:'.$url."\n\n\n\n".'Data: '.$data_log."\n\n\n\n".'Exec2: '.$exec_log2));
		$log->info(serialize('Error3: '.$cookie_file_path));
		
		$html = $result2;
		
		curl_close ( $ch );
		
		//unlink ( $cookie_file_path );
		
		return $html;
	}



	
	
	
	
	
	private function getPatientdata($ipid) {
		$u_transmitid = uniqid ();
		
		$tables_data = array ();
		foreach ( $this->syncable_tables as $table_key => $table_config ) {
			
			// grab all rows for this patient
			if ($table_config ['sql_select']) {
				$sqlf = $table_config ['sql_select'];
				$all_data = $sqlf ( $ipid );
			} else {
				$sql = Doctrine_Query::create ()->select ( '*' )->from ( $table_key . ' t INDEXBY t.' . $table_config ['key'] ['id'] )->where ( "ipid=?", $ipid );
				
				$all_data = $sql->fetchArray ();
			}
			
			if (count ( $all_data ) < 1) {
				continue;
			}
			
			$table_ids = array_keys ( $all_data );
			if (count ( $table_ids ) < 1) {
				continue;
			}
			// search for existing sync-info for this rows
			$sql = Doctrine_Query::create ()->select ( '*' )->from ( 'SystemsSyncTables' )->where ( 'tabname=?', $table_key )->andWhere ( 'clientid=?', $this->clientid )->andWhere ( 'connection=?', $this->connection_name )->andWhereIn ( "id_here", $table_ids );
			$syncmapdatamap_array = $sql->fetchArray ();
			
			$syncmapdatamap = array ();
			$syncmapdatamap_keys = array ();
			foreach ( $syncmapdatamap_array as $syncmapdata ) {
				$syncmapdatamap [$syncmapdata ['id_here']] = $syncmapdata;
				$syncmapdatamap_keys [] = $syncmapdata ['id_here'];
			}
			
			unset ( $syncmapdatamap_array );
			$table_data = array ();
			
			$table_send_filter_elems = array ();
			foreach ( $all_data as $row ) {
				$row_id_there = "";
				$row_exportdata = array ();
				$row_mode = "insert";
				$row_syncmap_id = $row [$table_config ['key'] ['id']];
				
				if (is_array ( $table_config ['filter'] )) {
					if (count ( $table_send_filter_elems ) == 0) {
						$rcvfun = $table_config ['filter'] ['elems_send'];
						$table_send_filter_elems = $rcvfun ( $this->clientid, $this->connection_name );
					}
					$filtercol = $table_config ['filter'] ['col'];
					$filterval = $row [$table_config ['filter'] ['col']];
					if (in_array ( $filtercol, $table_config ['encrypt'] )) {
						$filterval = Pms_CommonData::aesDecrypt ( $filterval );
					}
					// this is mainly used for course-shortcuts-filter that can be configured on adminpage
					if ($table_config ['filter'] ['filtermode'] == "include") {
						if (! in_array ( $filterval, $table_send_filter_elems )) {
							continue;
						}
					}
					if ($table_config ['filter'] ['filtermode'] == "exclude") {
						if (in_array ( $filterval, $table_send_filter_elems )) {
							continue;
						}
					}
				}
				
				/*
				 * var_dump($syncmapdatamap_array);
				 * var_dump($syncmapdatamap_keys);
				 * var_dump($row_syncmap_id);
				 * var_dump($syncmapdatamap);
				 * var_dump($row);
				 */
				
				if (in_array ( $row_syncmap_id, $syncmapdatamap_keys )) {
					$row_mode = "update";
					
					// echo $syncmapdatamap[$row_syncmap_id]['last_change'].' -- '.$row[$table_config['key']['change_date']]; exit;
					
					if ($syncmapdatamap [$row_syncmap_id] ['last_change'] >= $row [$table_config ['key'] ['change_date']]) {
						// no change since last sync
						continue;
					}
					$row_id_there = $syncmapdatamap [$row_syncmap_id] ['id_there'];
				}
				;
				$row_change_date = $row [$table_config ["key"] ['change_date']];
				if (strtotime ( $row_change_date ) < strtotime ( "1.1.2010" )) {
					$row_change_date = $row [$table_config ["key"] ['create_date']];
				}
				$row_id_here = $row [$table_config ["key"] ['id']];
				
				foreach ( $table_config as $columnmode => $cols ) {
					foreach ( $cols as $col_k => $col_c ) {
						$columnname = "";
						switch ($columnmode) {
							case "key" :
								$data = $row [$col_c];
								$columnname = $col_c;
								break;
							case "user" :
								$data = $row [$col_c];
								$columnname = $col_c;
								break;
							case "encrypt" :
								$data = Pms_CommonData::aesDecrypt ( $row [$col_c] );
								$columnname = $col_c;
								break;
							case "plain" :
								$data = $row [$col_c];
								$columnname = $col_c;
								break;
							case "function" :
								$f_out = $col_c ( $row, "read" );
								$data = $f_out [1];
								$columnname = $f_out [0];
								break;
						}
						$row_exportdata [$columnname] = $data;
					}
				}
				if ($row_mode == "insert") {
					$o = new SystemsSyncTables ();
				} else {
					$o = Doctrine::getTable ( 'SystemsSyncTables' )->findOneByTabnameAndId_hereAndIpid_here ( $table_key, $row_id_here, $ipid );
				}
				if(!$o){
					$o = Doctrine::getTable ( 'SystemsSyncTables' )->findOneByTabnameAndId_here ( $table_key, $row_id_here );
				}
				
				$o->tabname = $table_key;
				$o->id_here = $row_id_here;
				$o->id_there = $row_id_there;
				$o->ipid_here = $ipid;
				$o->last_change = $row_change_date;
				$o->packet_id = $u_transmitid;
				$o->connection = $this->connection_name;
				$o->clientid = $this->clientid;
				$o->save ();
				
				$row_exportdata ['meta'] = array (
						'id_here' => $row_id_here,
						'id_there' => $row_id_there,
						'packet_id' => $u_transmitid 
				);
				$table_data [] = $row_exportdata;
			} // END: row
			$tables_data [$table_key] = $table_data;
		} // END: Table-for
		
		
		if (APPLICATION_ENV != 'production') {
		    try {
		        $logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
		        $logger->info(PHP_EOL . __METHOD__ . __LINE__ );
		        $logger->info(print_r($this->syncable_tables, true));
		    } catch (Zend_Controller_Action_Exception $e) {
		
		    }
		}
		
		return ($tables_data);
	}
	public function receivePatient($tables_data) {
		$conn = $tables_data ['_meta'] ['connection'];
		
		$this->set_connection_name ( $conn );
		$ipid_there = $tables_data ['_meta'] ['ipid_here'];
		$ipid_here = $tables_data ['_meta'] ['ipid_there'];
		if ($ipid_here) {
			$pat = Doctrine::getTable ( 'SystemsSyncPatients' )->findOneByIpid_hereAndConnectionAndClientid ( $ipid_here, $this->connection_name, $this->clientid );
		}
		if (! $pat && $ipid_there) {
			$pat = Doctrine::getTable ( 'SystemsSyncPatients' )->findOneByIpid_thereAndConnectionAndClientid ( $ipid_there, $this->connection_name, $this->clientid );
		}
		
		if (! $pat) {
			// check if Patient exists by name AND birthday
			$r = $this->findPatientOnLocalside ( $tables_data ['PatientMaster'] [0] ['first_name'], $tables_data ['PatientMaster'] [0] ['last_name'], $tables_data ['PatientMaster'] [0] ['birthd'] );
			if ($r [0]) {
				$ipid_here = $r [1];
				$tables_data ['PatientMaster'] [0] ['admission_date'] = $r [2];// get the existing admission date from findPatientOnLocalside
			} else {
				$ipid_here = Pms_Uuid::GenerateIpid ();
				$tables_data ['PatientMaster'] [0] ['isstandby'] = "1";
			}
			
			$pat = SystemsSyncPatients::addConnection ( $conn, $ipid_here, $this->clientid );
		}
		
		if ($pat) {
			if ($ipid_there) {
				$pat->ipid_there = $ipid_there;
			}
			$pat->last_received = date ( 'Y-m-d H:i:s' );
			$pat->save ();
			$this->updateLocalPatient ( $pat->ipid_here, $tables_data, $conn );
			return "OK";
		} else {
			return "FAIL-2";
		}
	}
	
	/**
	 * Get all updates from Remote System for this Patient
	 */
	private function updateLocalPatient($ipid, $tables_data, $debug = false) {
		$debug = true;
		
		if ($debug === true) {
			$writer = new Zend_Log_Writer_Stream ( APPLICATION_PATH . '/../public/log/sync.log' );
			$log = new Zend_Log ( $writer );
			$log->info ( 'start update' );
		}
		
		foreach ( $this->syncable_tables as $table_key => $table_config ) {
			
			if ($debug === true) {
				$log->info ( $table_key );
				$log->info ( serialize ( $tables_data [$table_key] ) );
			}
			
			$table_receive_filter_elems = array ();
			foreach ( $tables_data [$table_key] as $row ) {
				if (count ( $row ) == 0) {
					continue;
				}
				
				if ($debug === true) {
					$log->info ( 'after row' );
				}
				
				if (is_array ( $table_config ['filter'] )) {
					
					if (count ( $table_receive_filter_elems ) == 0) {
						$rcvfun = $table_config ['filter'] ['elems_receive'];
						$table_receive_filter_elems = $rcvfun ( $this->clientid, $this->connection_name );
					}
					$filterval = $row [$table_config ['filter'] ['col']];
					
					if ($debug === true) {
						$log->info ( serialize ( $table_receive_filter_elems ) );
					}
					
					// this is mainly used for course-shortcuts-filter that can be configured on adminpage
					if ($table_config ['filter'] ['filtermode'] == "include") {
						if (! in_array ( $filterval, $table_receive_filter_elems )) {
							continue;
						}
					}
					if ($table_config ['filter'] ['filtermode'] == "exclude") {
						if (in_array ( $filterval, $table_receive_filter_elems )) {
							continue;
						}
					}
				}
				
				if ($debug === true) {
					$log->info ( 'after filter' );
				}
				
				$st = Doctrine::getTable ( 'SystemsSyncTables' )->findOneByTabnameAndIpid_hereAndId_thereAndConnectionAndClientid ( $table_key, $ipid, $row ['id'], $this->connection_name, $this->clientid );
				if (! $st) {
					// Maybe we sent but never received this entry. Then we don't know their row_id. But the received packet contains that info
					if ($row ['meta'] ['id_there']) {
						$st = Doctrine::getTable ( 'SystemsSyncTables' )->findOneByTabnameAndIpid_hereAndId_hereAndConnectionAndClientid ( $table_key, $ipid, $row ['meta'] ['id_there'], $this->connection_name, $this->clientid );
					}
				}
				
				if ($debug === true) {
					$log->info ( 'ST: ' . serialize ( $st ) );
				}
				
				$update = false;
				if ($st && $table_config ['update'] != "never") {
					$id_here = $st->id_here;
					if ($table_config ['get_by_localid']) {
						$getfun = $table_config ['get_by_localid'];
						$ut = $getfun ( $id_here );
					} else {
						$idkeyfun = 'findOneBy' . ucfirst ( $table_config ['key'] ['id'] );
						$ut = Doctrine::getTable ( $table_key )->$idkeyfun ( $id_here );
					}
					$cdkeyfun = $table_config ['key'] ['change_date'];
					$last_local_change = $ut->$cdkeyfun;
					
					if ($last_local_change < $row [$table_config ['key'] ['change_date']]) {
						if (($st->received_data == 1 && $table_config ['update'] == 'received_only') || $table_config ['update'] == 'always') {
							$update = true;
						}
					}
				}
				
				if ($debug === true) {
					$log->info ( 'UPDATE: ' . serialize ( $update ) );
				}
				
				if (! $st || $update) {
					$row_insert = array ();
					foreach ( $table_config as $columnmode => $cols ) {
						
						foreach ( $cols as $col_k => $col_c ) {
							$columnname = "";
							switch ($columnmode) {
								case "key" :
									
									// keys are local only
									switch ($col_k) {
										case "create_date" :
										case "change_date" :
											$data = $row [$col_c];
											$columnname = $col_k;
											break;
									}
									break;
								case "user" :
									
									// map foreign users to local
									$data = $this->systemlink->localuserid;
									$columnname = $col_c;
									break;
								case "encrypt" :
									$data = Pms_CommonData::aesEncrypt ( $row [$col_c] );
									$columnname = $col_c;
									break;
								case "plain" :
									$data = $row [$col_c];
									$columnname = $col_c;
									break;
								case "function" :
									$f_out = $col_c ( $row, "write" );
									$data = $f_out [1];
									$columnname = $f_out [0];
									break;
								case "add_for_input" :
									$columnname = $col_k;
									$data = $col_c;
									break;
							}
							
							if ($columnname) {
								$row_insert [$columnname] = $data;
							}
						}
					}
					
					if (count ( $row_insert ) > 0 && ! $update) {
						if ($table_config ['sql_create']) {
							$insertfun = $table_config ['sql_create'];
							$ins_id = $insertfun ( $ipid, $row_insert, $table_config );
						} else {
							$ut = new $table_key ();
							foreach ( $row_insert as $col => $val ) {
								$ut->$col = $val;
							}
							$ut->ipid = $ipid;
							
							$ut->save ();
							$ins_id = $ut->id;
						}
						
						$st = new SystemsSyncTables ();
						$st->ipid_here = $ipid;
						$st->tabname = $table_key;
						$st->id_here = $ins_id;
						$st->id_there = $row ['id'];
						$st->received_data = 1;
						$st->connection = $this->connection_name;
						$st->clientid = $this->clientid;
						if ($row ['change_date'] > "2000-01-01 01:00") {
							$st->last_change = $row ['change_date'];
						} else {
							$st->last_change = $row ['create_date'];
						}
						
						$st->save ();
					}
					
					if (count ( $row_insert ) > 0 && $update) {
						if ($table_config ['sql_update']) {
							$insertfun = $table_config ['sql_update'];
							$ins_id = $insertfun ( $ipid, $row_insert, $table_config, $ut->id );
						} else {
							foreach ( $row_insert as $col => $val ) {
								$ut->$col = $val;
							}
							$ut->ipid = $ipid;
							$ut->save ();
							$ins_id = $ut->id;
						}
						
						$st->ipid_here = $ipid;
						$st->tabname = $table_key;
						$st->id_there = $row ['id'];
						$st->received_data = 1;
						if ($row ['change_date'] > "2000-01-01 01:00") {
							$st->last_change = $row ['change_date'];
						} else {
							$st->last_change = $row ['create_date'];
						}
						$st->save ();
					}
				}
			}
		}
		
		
		if (APPLICATION_ENV != 'production') {
		    try {
		        $logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
		        $logger->info(PHP_EOL . __METHOD__ . __LINE__ );
		        $logger->info($ipid);
		        $logger->info(print_r($this->syncable_tables , true));
		    } catch (Zend_Controller_Action_Exception $e) {
		
		    }
		}
		
		$cv = new ClinicVersorger();
		$cv->update_patient_from_exportpackage($ipid);
		
		
		//TODO-1890 added  Ancuta from Nico 16.11.2018
		//update Stammdaten if any Sync-Package is present
		$vv=new PatientDetails($ipid);
		$vv->update_from_syncpackage();
		//--
		
	}
	
	/**
	 * Get patients ipid on local system
	 */
	public function findPatientOnLocalside($firstname, $lastname, $birthday) {
		$pm = new PatientMaster ();
		$r = $pm->check_patients_exists ( array (
				array (
						'last_name' => $lastname,
						'first_name' => $firstname,
						'dob' => $birthday 
				) 
		) );
		
		if (is_array ( $r ) && count ( $r ) > 0) {
			return array (
					true,
					$r [0] ['ipid'], 
					$r [0] ['admission_date'] 
			);
		} else {
			return array (
					false,
					null 
			);
		}
	}
	
	/**
	 *
	 * @param $mode string:
	 *        	if set to "db_insert", patientfileupload-links wont be removed
	 * @return String with removed html-links
	 */
	public static function removeCourseLinks($in, $mode = null) {
		$dat = $in;
		$a = preg_match_all ( '/<a .+?>.+?<\/a>/im', $dat, $linkmatches );
		
		if ($a > 0) {
			foreach ( $linkmatches [0] as $mlink ) {
				$a = preg_match ( '/patientfileupload\?doc_id=(\d+)/', $mlink, $docid );
				if ($a > 0) {
					if ($mode === "db_insert") {
						$docid = $docid [1];
						$newdocid = 0;
						$sql = Doctrine_Query::create ()->select ( 'id_here' )->from ( 'SystemsSyncTables' )->where ( 'tabname=?', 'PatientFileUpload' )->andWhereIn ( "id_there", $docid );
						$syncmapdatamap_array = $sql->fetchArray ();
						if ($syncmapdatamap_array) {
							$newdocid = $syncmapdatamap_array [0] ['id_here'];
						}
						
						$link = str_replace ( $docid, $newdocid, $mlink );
					} else {
						$link = $mlink;
					}
				} else {
					preg_match ( '/<a .+?>(.+?)<\/a>/im', $mlink, $linkfree );
					$link = "";
					if (is_array ( $linkfree )) {
						$link = $linkfree [1];
					}
				}
				$dat = str_replace ( $mlink, $link, $dat );
			}
		}
		return $dat;
	}
}
?>