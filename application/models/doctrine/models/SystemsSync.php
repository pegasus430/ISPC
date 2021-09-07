<?php

/*
 * NEED UPDATES OF:
 * - extra column 'alien' in patient_course-table
 * - TimestampListener!!!!!
 * - CommonData Config
 * - BasePatientCourse&DB!!!alien
 * - templates/patientcourse.html
 * - patient/patientdetails, add this:
 * <div class="dragboxpatientdetails" id="block46">
 * <h2 class="">Systemsynchronisation</h2>
 * <script>
 * $('document').ready(function(){
 * $("#grow46 .dtbox").load('Systemssync/patientinfo?patid=<?php echo $this->patid2;?>');
 * });
 * </script>
 * <div style="display: none;" id="grow46" class="patientdragbox-content">
 * <div id="PtDetails_second" class="dtbox">
 * </div>
 * <div class="clearer"></div>
 * </div>
 * </div>
 *
 *
 * Add to Goettingen, ISPC ambulant
 *
 * triggers, controllers, config, bootstrap.php, models, modules, views
 *
 */
//require_once("CoreSystemsSync.php");

class SystemsSync extends CoreSystemsSync {
	function SystemsSync($clientid) {
		$this->clientid = $clientid;
		
		$this->unique_system_id = Zend_Registry::get ( 'localsync' );
		$this->connection_localid = $this->unique_system_id . "_" . $clientid;
		
		$links = $this->get_system_links ();
		
		$this->systemlinks = array ();
		$this->systemlink = null;
		if (isset ( $links )) {
			foreach ( $links as $link ) {
				if ($link->local == $clientid) {
					$this->systemlinks [] = $link;
				}
			}
		}
		
		$this->connection_name = ""; // set with setter method
		$this->remote_username_prefix = "r__";
		
		$this->cache_users = array ();
		
		// This is some default info for new tables
		$tables_default = array (
				
				// we always need some special colums to decide how to sync.
				// but what is their actual name?
				'key' => array (
						'id' => 'id',
						'change_date' => 'change_date',
						'create_date' => 'create_date' 
				),
				
				// colums with users must be handled specially
				'user' => array (
						'change_user',
						'create_user' 
				),
				
				// encrypted cols have to be unencrypted before sending
				'encrypt' => array (),
				
				// plaintext columns need no further treatment
				'plain' => array (),
				
				// very special columns may need very special treatment
				'function' => array (),
				
				// 'colname'=>'function'
				
				// when we need a very special selection
				'sql_select' => false,
				'update' => 'never' 
		); // 'always','received_only'
		
		$this->syncable_tables = array ();
		
		// All about PatientMaster
		$this->syncable_tables ['PatientMaster'] = $tables_default;
		$this->syncable_tables ['PatientMaster'] ['encrypt'] = array (
				'first_name',
				'middle_name',
				'last_name',
				'title',
				'salutation',
				'street1',
				'street2',
				'zip',
				'city',
				'phone',
				'mobile',
				'sex' 
		);
		$this->syncable_tables ['PatientMaster'] ['plain'] = array (
				'birthd',
				'recording_date',
				'admission_date', 
				'isstandby' 
		);
		$this->syncable_tables['PatientMaster']['sql_create']=function($ipid, $row, $table_config) {
		
			$pd=Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
		
			if(!$pd) {
				$pd = new PatientMaster();
				$pd->ipid=$ipid;
			}
		
			foreach ($table_config['plain'] as $col) {
				$pd->$col = $row[$col];
			}
			foreach ($table_config['encrypt'] as $col) {
				$pd->$col = $row[$col];
			}
			$pd->change_date=$row['change_date'];
			$pd->change_user=$row['change_user'];
			$pd->create_date=$row['create_date'];
			$pd->create_user=$row['create_user'];
			$pd->save();
		
			return $pd->id;
		};
		
		
		// All about EPID-IPID
		$this->syncable_tables ['EpidIpidMapping'] = $tables_default;
		$this->syncable_tables ['EpidIpidMapping'] ['plain'] = array (
				'epid',
				'epid_chars',
				'epid_num' 
		);
		$this->syncable_tables ['EpidIpidMapping'] ['add_for_input'] = array (
				'clientid' => $this->clientid 
		);
		
		$this->syncable_tables['EpidIpidMapping']['sql_create']=function($ipid, $row, $table_config) {
			$pd=Doctrine::getTable('EpidIpidMapping')->findOneByIpid($ipid);
		
			if($pd && $this->always_keep_epid){
				return $pd->id;
			}
			if(!$pd) {
				$pd = new EpidIpidMapping();
				$pd->clientid = $this->clientid;
				$pd->ipid=$ipid;
			}
		
			foreach ($table_config['plain'] as $col) {
				$pd->$col = $row[$col];
			}
		
			$pd->change_date=$row['change_date'];
			$pd->change_user=$row['change_user'];
			$pd->create_date=$row['create_date'];
			$pd->create_user=$row['create_user'];
			$pd->save();
		
			return $pd->id;
		};
		
		// All about PatientFile
		$this->syncable_tables ['PatientFileUpload'] = $tables_default;
		$this->syncable_tables ['PatientFileUpload'] ['encrypt'] = array (
				'title',
				'file_name',
				'file_type' 
		);
		/*$this->syncable_tables ['PatientFileUpload'] ['plain'] = array (
				'tag_name' 
		);*/
		$this->syncable_tables ['PatientFileUpload'] ['function'] = array (
				'file' => function ($row, $mode) {
					if ($mode == 'read') {
						$fname = Pms_CommonData::aesDecrypt ( $row ['file_name'] );
						
						// FTP stuff
						
						//$working_dir = $_SERVER ['DOCUMENT_ROOT'] . "/ispc/public/"; // change the path to fit your websites document structure
						$working_dir = PUBLIC_PATH.'/';
						
						$explo = explode ( "/",$fname );
						
						$fdname = $explo [0];
						$flname = utf8_decode ( $explo [1] );
						
						// medication plan pdf issue only
						if ($row ['title'] == "Medikamentenplan" && strpos ( $fname, '.zip' ) !== false) {
							// overwrite folder name so the file can be downloaded
							$fdname = str_replace ( ".zip", "", $flname );
							
							// overwrite the file name so the file can be served to user
							$flname = "medication.pdf";
						}
						
						
// 						$con_id = Pms_FtpFileupload::ftpconnect ();
						
						
// 						if ($con_id) {
// 							$old = false;
// 							if ($_REQUEST ['old']) {
// 								$old = true;
// 							}
// 							$upload = Pms_FtpFileupload::filedownload ( $con_id, $working_dir.'uploads/' . $fdname . '.zip', 'uploads/' . $fdname . '.zip', $old );
// 							Pms_FtpFileupload::ftpconclose ( $con_id );
// 						}
						
						$logininfo= new Zend_Session_Namespace('Login_Info');
						$file_password = $logininfo->filepass;
						
						$old = $_REQUEST['old'] ? true : false;
						if (($path = Pms_CommonData::ftp_download('uploads/' . $fdname . '.zip' , $file_password , $old , $logininfo->clientid , $fname, "PatientFileUpload", $row['id'])) === false){
							//failed to download file
							return array (
									'file',
									''
							);
						}
						
// 						$cmd = "unzip -P " . $file_password . " ".$working_dir."uploads/" . $fdname . ".zip;";
						
// 						exec ( $cmd );
						
// 						$path = $working_dir .'uploads/'. $fdname . "/"; 
// 						$fullPath = $path . $flname;
						
						$fullPath = $path . "/" . $flname;
						
						$filecont = file_get_contents ( $fullPath );
						$filestr = base64_encode ( $filecont );
						
						
						
						@unlink($fullPath);
						@unlink($path);
						
						return array (
								'file',
								$filestr 
						);
					}
					
					if ($mode == 'write') {
						$filename = $row ['file_name'];
						$filename = explode ( '/', $filename );
						$filename = end ( $filename );
						
						
						
						// FTP stuff
						
						//$working_dir = $_SERVER ['DOCUMENT_ROOT'] . "/ispc2014/public/"; // change the path to fit your websites document structure
						$working_dir = PUBLIC_PATH.'/';
						
						
						
						$dir_name = Pms_CommonData::uniqfolder ( $working_dir.'uploads/' );
						
						$dir_path =  $working_dir.'uploads/'.$dir_name;
						
						
						$filecontents = base64_decode ( $row ['file'] );
						
						
						$file_path = $dir_path.'/'.basename($row ['file_name']);
						
						//echo $file_path;
						
						file_put_contents ( $file_path, $filecontents );
						/*
						$logininfo= new Zend_Session_Namespace('Login_Info');
						
						$cmd = "cd ".$working_dir."; zip -9 -r -P ".$logininfo->filepass." uploads/".$dir_name.".zip  uploads/".$dir_name ."; rm -r ".$dir_path.";";
						
						//echo $cmd;
						
						exec($cmd);
						
						$zipname = $dir_name.".zip";
						
						
						//echo $working_dir.'/uploads/'.$zipname;
						
						$con_id = Pms_FtpFileupload::ftpconnect();
						if($con_id)
						{
							$upload = Pms_FtpFileupload::fileupload($con_id, $working_dir.'uploads/'.$zipname,'uploads/'.$zipname);
							Pms_FtpFileupload::ftpconclose($con_id);
						}
						*/
						
						
						
						$result = Pms_CommonData::ftp_put_queue($file_path , 'uploads', $is_zipped = NULL, $foster_file = false , $ftpclientid = $logininfo->clientid, $ftpfilepass = $logininfo->filepass);
						
						
						$path = Pms_CommonData::aesEncrypt ( $dir_name. '/'.$filename );
						
						return array (
								'file_name',
								$path 
						);
					}
				} 
		);
		
		// All about PatientCourse
		$this->syncable_tables ['PatientCourse'] = $tables_default;
		$this->syncable_tables ['PatientCourse'] ['encrypt'] = array (
				'course_type',
				'course_title',
				'done_name',
				'tabname' 
		);
		$this->syncable_tables ['PatientCourse'] ['plain'] = array (
				'course_date',
				'recordid',
				'recorddata',
				'wrong',
				'wrongcomment',
				'done_date' 
		);
		$this->syncable_tables ['PatientCourse'] ['function'] = array (
				'recorddata' => function ($row, $mode) {
					if ($mode == "read") {
						return $row ['recorddata'];
					}
					if ($mode == "write") {
						$dat = "";
						if ($row ['recorddata']) {
							$dat = $row ['recorddata'];
							$dat = parent::removeCourseLinks ( $dat, 'db_insert' );
						}
						return array (
								'recorddata',
								$dat 
						);
					}
				} 
		);
		$this->syncable_tables ['PatientCourse'] ['user'] = array (
				'change_user',
				'create_user',
				'user_id',
				'done_id' 
		);
		$this->syncable_tables ['PatientCourse'] ['add_for_input'] = array (
				'alien' => 1 
		);
		$this->syncable_tables ['PatientCourse'] ['filter'] = array (
				'col' => 'course_type',
				'filtermode' => 'include',
				'elems_receive' => function ($clientid, $connection_name) {
					$a = SystemsSyncConnections::getConnectionShortcuts ( $connection_name, $clientid );
					
					return $a->receive;
				},
				'elems_send' => function ($clientid, $connection_name) {
					$a = SystemsSyncConnections::getConnectionShortcuts ( $connection_name, $clientid );
					
					return $a->send;
				} 
		);
		
		$this->syncable_tables ['PatientCourse'] ['update'] = 'received_only';
		
		// All about contactperson_master
		$this->syncable_tables ['ContactPersonMaster'] = $tables_default;
		$this->syncable_tables ['ContactPersonMaster'] ['update'] = 'always';
		$this->syncable_tables ['ContactPersonMaster'] ['encrypt'] = array (
				'cnt_first_name',
				'cnt_last_name',
				'cnt_street1',
				'cnt_zip',
				'cnt_city',
				'cnt_phone',
				'cnt_mobile' 
		);
		$this->syncable_tables ['ContactPersonMaster'] ['plain'] = array (
				'cnt_hatversorgungsvollmacht',
				'cnt_legalguardian',
				'notify_funeral' 
		);
		
		
		// ISPC-2048 - if you sync patients it happens that contact persons are duplicated - please fix:: 25.10.2017
		$allow_cnt_update = 1 ; 
		
		if($allow_cnt_update == 1){
			
			$this->syncable_tables ['ContactPersonMaster'] ['sql_create'] = function ($ipid, $row, $table_config) {
					
				$p_cnt_new = new ContactPersonMaster ();
					
				$p_cnt_new->ipid = $ipid;
				foreach ( $table_config {'plain'} as $col ) {
					$p_cnt_new->$col = $row [$col];
				}
				foreach ( $table_config {'encrypt'} as $col ) {
					$p_cnt_new->$col = $row [$col];
				}
				$p_cnt_new->save();
					
				return $p_cnt_new->id;
			};
			
			$this->syncable_tables ['ContactPersonMaster'] ['sql_update'] = function ($ipid, $row, $table_config, $id) {
				if( ! empty($id) ) {
					$p_cnt_update = Doctrine::getTable ( 'ContactPersonMaster' )->findOneById ($id);
					if($p_cnt_update){
						foreach ( $table_config {'plain'} as $col ) {
							$p_cnt_update->$col = $row [$col];
						}
						foreach ( $table_config {'encrypt'} as $col ) {
							$p_cnt_update->$col = $row [$col];
						}
						$p_cnt_update->save ();
					}
					$p_cnt_update->free();
					
					return $id;
				}
			};
		}
		
		
		/*---------------*/
		/* 
		 $this->syncable_tables ['ContactPersonMaster'] ['sql_select'] = function ($ipid) {
		    $sql = Doctrine_Query::create ()->select ( '
            cp.id as id,
	        '. Pms_CommonData::aesDecrypt("cp.cnt_first_name") .' as cnt_first_name,
	        '. Pms_CommonData::aesDecrypt("cp.cnt_last_name") .' as cnt_last_name,
	        '. Pms_CommonData::aesDecrypt("cp.cnt_street1") .' as cnt_street1,
	        '. Pms_CommonData::aesDecrypt("cp.cnt_zip") .' as cnt_zip,
	        '. Pms_CommonData::aesDecrypt("cp.cnt_phone") .' as cnt_phone,
	        '. Pms_CommonData::aesDecrypt("cp.cnt_mobile") .' as cnt_mobile,
            cp.cnt_hatversorgungsvollmacht as cnt_hatversorgungsvollmacht,
            cp.cnt_legalguardian as cnt_legalguardian,
            cp.notify_funeral as notify_funeral,
		    cp.change_date as change_date,
            cp.create_date as create_date,
            cp.create_user as create_user,
            cp.change_user as change_user,
            cp.ipid as ipid
            ' )->from ( 'ContactPersonMaster cp INDEXBY cp.id' );
		    $sql->where ( "ipid=?", $ipid );
		    $sql = $sql->fetchArray ();
		    return $sql;
		};
		
		$this->syncable_tables ['ContactPersonMaster'] ['sql_update'] = function ($ipid, $row, $table_config, $id) {
		    $cp_model = new ContactPersonMaster();
		    $existing_cp_id = $cp_model->patient_check_cnt($ipid,$row);
		     
		    if(!$existing_cp_id) // If contact person not fond 
		    {// INSERT NEW
		        
	            $cp = new ContactPersonMaster ();
	            foreach ( $table_config {'plain'} as $col ) {
	                $cp->$col = $row [$col];
	            }
	            foreach ( $table_config {'encrypt'} as $col ) {
	                $cp->$col = Pms_CommonData::aesEncrypt ($row [$col]);
	            }
	            $cp->create_date = $row ['create_date'];
	            $cp->create_user = $row ['create_user'];
	            $cp->save ();
	             
	            return $cp->id;
		    }
		}; */
		/*---------------------------------------*/
		
		
		/**
		 * @since 09.10.2018
		 * ISPC-2254
		 * Nico's indication : You have to remove pharmacy and pflegedienste from models/SystemsSync.php
		 */
		/*
		// All about Pharmacy
		$this->syncable_tables ['Pharmacy'] = $tables_default;
		$this->syncable_tables ['Pharmacy'] ['encrypt'] = array ();
		$this->syncable_tables ['Pharmacy'] ['plain'] = array (
				'pharmacy',
				'first_name',
				'last_name',
				'street1',
				'zip',
				'city',
				'phone',
				'fax' 
		);
		$this->syncable_tables ['Pharmacy'] ['add_for_input'] = array (
				'indrop' => 1 
		);
		$this->syncable_tables ['Pharmacy'] ['sql_select'] = function ($ipid) {
			$sql = Doctrine_Query::create ()->select ( '
            pp.id as id,
            p.pharmacy as pharmacy,
            p.first_name as first_name,
            p.last_name as last_name,
            p.street1 as street1,
            p.zip as zip,
            p.city as city,
            p.phone as phone,
            p.fax as fax,
            pp.change_date as change_date,
            pp.create_date as create_date,
            pp.create_user as create_user,
            pp.change_user as change_user,
            pp.ipid as ipid
            ' )->from ( 'PatientPharmacy pp INDEXBY pp.id' )->leftJoin ( 'pp.Pharmacy p' );
			$sql->where ( "ipid=?", $ipid );
			$sql = $sql->fetchArray ();
			return $sql;
		};
		$this->syncable_tables ['Pharmacy'] ['sql_create'] = function ($ipid, $row, $table_config) {
			$pd = new Pharmacy ();
			foreach ( $table_config {'plain'} as $col ) {
				$pd->$col = $row [$col];
			}
			$pd->change_date = $row ['change_date'];
			$pd->change_user = $row ['change_user'];
			$pd->create_date = $row ['create_date'];
			$pd->create_user = $row ['create_user'];
			$pd->indrop = 1;
			$pd->save ();
			
			$ppd = new PatientPharmacy ();
			$ppd->ipid = $ipid;
			$ppd->pharmacy_id = $pd->id;
			$ppd->save ();
			
			return $ppd->id;
		};
		$this->syncable_tables ['Pharmacy'] ['sql_update'] = function ($ipid, $row, $table_config, $id) {
			$ppd = Doctrine::getTable ( 'PatientPharmacy' )->findOneById ( $id );
			$pharmacy_id = $ppd->id;
			$ppd->free ();
			$pd = Doctrine::getTable ( 'Pharmacy' )->findOneById ( $pharmacy_id );
			if ($pd->indrop) {
				foreach ( $table_config {'plain'} as $col ) {
					$pd->$col = $row [$col];
				}
				$pd->change_date = $row ['change_date'];
				$pd->change_user = $row ['change_user'];
				$pd->create_date = $row ['create_date'];
				$pd->create_user = $row ['create_user'];
				$pd->indrop = 1;
				$pd->save ();
			}
			$pd->free();
			
			return $pharmacy_id;
		};
		// All about Pflegedienste
		$this->syncable_tables ['Pflegedienstes'] = $tables_default;
		$this->syncable_tables ['Pflegedienstes'] ['encrypt'] = array ();
		$this->syncable_tables ['Pflegedienstes'] ['plain'] = array (
				'nursing',
				'first_name',
				'last_name',
				'street1',
				'zip',
				'city',
				'phone_practice',
				'phone_private',
				'fax' 
		);
		
		$this->syncable_tables ['Pflegedienstes'] ['sql_select'] = function ($ipid) {
			$sql = Doctrine_Query::create ()->select ( '
            pp.id as id,
            p.nursing as nursing,
            p.first_name as first_name,
            p.last_name as last_name,
            p.street1 as street1,
            p.zip as zip,
            p.city as city,
            p.phone_practice as phone_practice,
            p.phone_private as phone_private,
            p.fax as fax,
            pp.change_date as change_date,
            pp.create_date as create_date,
            pp.create_user as create_user,
            pp.change_user as change_user,
            pp.ipid as ipid
            ' )->from ( 'PatientPflegedienste pp INDEXBY pp.id' )->leftJoin ( 'pp.Pflegedienstes p' );
			$sql->where ( "ipid=?", $ipid );
			$sql = $sql->fetchArray ();
			return $sql;
		};
		
		$this->syncable_tables ['Pflegedienstes'] ['sql_create'] = function ($ipid, $row, $table_config) {
			$pd = new Pflegedienstes ();
			foreach ( $table_config {'plain'} as $col ) {
				$pd->$col = $row [$col];
			}
			$pd->change_date = $row ['change_date'];
			$pd->change_user = $row ['change_user'];
			$pd->create_date = $row ['create_date'];
			$pd->create_user = $row ['create_user'];
			$pd->indrop = 1;
			$pd->save ();
			
			$ppd = new PatientPflegedienste ();
			$ppd->ipid = $ipid;
			$ppd->pflid = $pd->id;
			$ppd->save ();
			
			return $ppd->id;
		};
		$this->syncable_tables ['Pflegedienstes'] ['sql_update'] = function ($ipid, $row, $table_config, $id) {
			$ppd = Doctrine::getTable ( 'PatientPflegedienste' )->findOneById ( $id );
			$pflid = $ppd->id;
			$ppd->free ();
			$pd = Doctrine::getTable ( 'Pflegedienstes' )->findOneById ( $pflid );
			if ($pd->indrop) {
				foreach ( $table_config {'plain'} as $col ) {
					$pd->$col = $row [$col];
				}
				$pd->change_date = $row ['change_date'];
				$pd->change_user = $row ['change_user'];
				$pd->create_date = $row ['create_date'];
				$pd->create_user = $row ['create_user'];
				$pd->indrop = 1;
				$pd->save ();
			}
			$pd->free();
			
			return $pflid;
		};
		*/
		
		// All about HealthInsurance
		$this->syncable_tables ['PatientHealthInsurance'] = $tables_default;
		$this->syncable_tables ['PatientHealthInsurance'] ['update'] = 'always';
		$this->syncable_tables ['PatientHealthInsurance'] ['encrypt'] = array (
				'company_name',
				'ins_first_name',
				'ins_last_name',
				'ins_contactperson',
				'ins_country',
				'ins_zip',
				'ins_city',
				'ins_phone',
				'ins_phone2',
				'ins_phonefax',
				'ins_street',
				'ins_email',
				'ins_zip_mailbox',
                'ins_post_office_box_location',
                'ins_post_office_box',
		        'comment',
		        'insurance_status',
		    	'ins_insurance_provider',
		);
		$this->syncable_tables ['PatientHealthInsurance'] ['plain'] = array (
				'kvk_no',
				'institutskennzeichen',
				'insurance_no',
				'vk_no',
				'rezeptgebuhrenbefreiung',
				'privatepatient',
				'direct_billing',
				'bg_patient',
				'status_added',
				'card_valid_till',
				'date_of_birth'
              
		);
		

		$this->syncable_tables ['PatientHealthInsurance'] ['sql_create'] = function ($ipid, $row, $table_config) {
			// Insert master Health insurance
			$client_hi = new HealthInsurance ();
			$client_hi->clientid = $this->clientid;
			$client_hi->name = Pms_CommonData::aesDecrypt ($row ['company_name']);
			$client_hi->insurance_provider = Pms_CommonData::aesDecrypt ($row ['ins_insurance_provider']);
			$client_hi->street1 = Pms_CommonData::aesDecrypt ($row ['ins_street']);
			$client_hi->zip = Pms_CommonData::aesDecrypt ($row ['ins_zip']);
			$client_hi->city = Pms_CommonData::aesDecrypt ($row ['ins_city']);
			$client_hi->phone = Pms_CommonData::aesDecrypt ($row ['ins_phone']);
			$client_hi->phonefax = Pms_CommonData::aesDecrypt ($row ['ins_phonefax']);
			$client_hi->post_office_box = Pms_CommonData::aesDecrypt ($row ['ins_post_office_box']);
			$client_hi->email = Pms_CommonData::aesDecrypt ($row ['ins_email']);
			$client_hi->zip_mailbox = Pms_CommonData::aesDecrypt ($row ['ins_zip_mailbox']);
			$client_hi->kvnumber = $row ['kvk_no'];
			$client_hi->iknumber = $row ['institutskennzeichen'];
			$client_hi->debtor_number = $row ['ins_debtor_number'];
			$client_hi->comments = $row ['comment'];
			$client_hi->extra = "1";
			$client_hi->onlyclients = '1';
			$client_hi->save ();
			$new_company_id = $client_hi->id;
				
			
			$phi = new PatientHealthInsurance ();
			
			$phi->ipid = $ipid;
			foreach ( $table_config {'plain'} as $col ) {
				$phi->$col = $row [$col];
			}
			foreach ( $table_config {'encrypt'} as $col ) {
				$phi->$col = $row [$col];
			}
			$phi->companyid = $new_company_id; // set new company id
			$phi->save();
			
			
			return $phi->id;
			
		};
		
		
		$this->syncable_tables ['PatientHealthInsurance'] ['sql_update'] = function ($ipid, $row, $table_config, $id) {
			
			$ph_pd = Doctrine::getTable ( 'PatientHealthInsurance' )->findOneById ( $id );
			$company_id = $ph_pd->companyid;
			$ph_pd->free ();

			$hi_m = Doctrine::getTable ( 'HealthInsurance' )->findOneById ( $company_id );
			if($hi_m->extra == '1'){ // if not master
				
				$hi_m->clientid = $this->clientid;
				$hi_m->name = Pms_CommonData::aesDecrypt ($row ['company_name']);
				$hi_m->insurance_provider = Pms_CommonData::aesDecrypt ($row ['ins_insurance_provider']);
				$hi_m->street1 = Pms_CommonData::aesDecrypt ($row ['ins_street']);
				$hi_m->zip = Pms_CommonData::aesDecrypt ($row ['ins_zip']);
				$hi_m->city = Pms_CommonData::aesDecrypt ($row ['ins_city']);
				$hi_m->phone = Pms_CommonData::aesDecrypt ($row ['ins_phone']);
				$hi_m->phonefax = Pms_CommonData::aesDecrypt ($row ['ins_phonefax']);
				$hi_m->post_office_box = Pms_CommonData::aesDecrypt ($row ['ins_post_office_box']);
				$hi_m->email = Pms_CommonData::aesDecrypt ($row ['ins_email']);
				$hi_m->zip_mailbox = Pms_CommonData::aesDecrypt ($row ['ins_zip_mailbox']);
				$hi_m->kvnumber = $row ['kvk_no'];
				$hi_m->iknumber = $row ['institutskennzeichen'];
				$hi_m->debtor_number = $row ['ins_debtor_number'];
				$hi_m->comments = $row ['comment'];
				$hi_m->extra = "1";
				$hi_m->onlyclients = '1';
				$hi_m->save ();
				
			$hi_m->free();
		
		
			$patient_hi = Doctrine::getTable ( 'PatientHealthInsurance' )->findOneById ($id);
			if($patient_hi){
			    foreach ( $table_config {'plain'} as $col ) {
				$patient_hi->$col = $row [$col];
			    }
			    foreach ( $table_config {'encrypt'} as $col ) {
				$patient_hi->$col = $row [$col];
		    	    }
		    	    $patient_hi->save ();
			}
			$patient_hi->free();
				
				
			} else {
				 
				$client_hi_new = new HealthInsurance ();
				$client_hi_new->clientid = $this->clientid;
				$client_hi_new->name = Pms_CommonData::aesDecrypt ($row ['company_name']);
				$client_hi_new->insurance_provider = Pms_CommonData::aesDecrypt ($row ['ins_insurance_provider']);
				$client_hi_new->street1 = Pms_CommonData::aesDecrypt ($row ['ins_street']);
				$client_hi_new->zip = Pms_CommonData::aesDecrypt ($row ['ins_zip']);
				$client_hi_new->city = Pms_CommonData::aesDecrypt ($row ['ins_city']);
				$client_hi_new->phone = Pms_CommonData::aesDecrypt ($row ['ins_phone']);
				$client_hi_new->phonefax = Pms_CommonData::aesDecrypt ($row ['ins_phonefax']);
				$client_hi_new->post_office_box = Pms_CommonData::aesDecrypt ($row ['ins_post_office_box']);
				$client_hi_new->email = Pms_CommonData::aesDecrypt ($row ['ins_email']);
				$client_hi_new->zip_mailbox = Pms_CommonData::aesDecrypt ($row ['ins_zip_mailbox']);
				$client_hi_new->kvnumber = $row ['kvk_no'];
				$client_hi_new->iknumber = $row ['institutskennzeichen'];
				$client_hi_new->debtor_number = $row ['ins_debtor_number'];
				$client_hi_new->comments = $row ['comment'];
				$client_hi_new->extra = "1";
				$client_hi_new->onlyclients = '1';
				$client_hi_new->save ();
				$new_company_u_id = $client_hi_new->id;
				
				$patient_hi = Doctrine::getTable ( 'PatientHealthInsurance' )->findOneById ($id);
				if($patient_hi){
					foreach ( $table_config {'plain'} as $col ) {
						$patient_hi->$col = $row [$col];
					}
					foreach ( $table_config {'encrypt'} as $col ) {
						$patient_hi->$col = $row [$col];
					}
					$patient_hi->companyid = $new_company_u_id; // set new company id
					$patient_hi->save ();
				}
			$patient_hi->free();				
			}
			
			return $company_id;
		};
		
		
		// All about FamilyDoctor
		$this->syncable_tables ['FamilyDoctor'] = $tables_default;
		$this->syncable_tables ['FamilyDoctor'] ['plain'] = array (
				'practice',
				'first_name',
				'last_name',
				'title',
				'salutation',
				'street1',
				'zip',
				'city',
				'doctornumber',
				'phone_practice',
				'fax',
		        // 18.10.2018
    		    'doctor_bsnr',
    		    'comments',
    		    'email',
    		    'phone_private'
		);
		
		$this->syncable_tables ['FamilyDoctor'] ['sql_select'] = function ($ipid) {
			$sqlpm = Doctrine_Query::create ()->select ( 'familydoc_id,id' )->from ( 'PatientMaster' )->where ( "ipid=?", $ipid );
			$sqlpm = $sqlpm->fetchOne ();
			
			$fdocid = $sqlpm ['familydoc_id'];
			
			if (! $fdocid > 0) {
				$fdocid = - 1;
			}
			$sql = Doctrine_Query::create ()->select ( '*' )->from ( 'FamilyDoctor' );
			$sql->where ( "id=?", $fdocid );
			$arr = $sql->fetchArray ();
			$return = array ();
			
			if (count ( $arr ) > 0) {
				$arr [0] ['id'] = $sqlpm ['id'];
				$arr [0] ['create_user'] = $sqlpm ['create_user'];
				$arr [0] ['change_user'] = $sqlpm ['change_user'];
				$return [$sqlpm ['id']] = $arr [0];
			}
			
			return $return;
		};
		
		$this->syncable_tables ['FamilyDoctor'] ['sql_create'] = function ($ipid, $row, $table_config) {
			$pd = new FamilyDoctor ();
			foreach ( $table_config {'plain'} as $col ) {
				$pd->$col = $row [$col];
			}
			$pd->change_date = $row ['change_date'];
			$pd->change_user = $row ['change_user'];
			$pd->create_date = $row ['create_date'];
			$pd->create_user = $row ['create_user'];
			$pd->indrop = 1;
			$pd->save ();
			
			$pm = Doctrine::getTable ( 'PatientMaster' )->findOneByIpid ( $ipid );
			
			$pm->familydoc_id = $pd->id;
			$pm->save ();
			
			return $ipid . "_" . $pd->id;
		};
		
		// New sync - for Bassisassesment
		$this->syncable_tables['SystemsSyncPackets']=$tables_default;
		$this->syncable_tables['SystemsSyncPackets']['plain']=array(
		    'clientid',
		    'actionname',
		    'payload'
		);
		$this->syncable_tables['SystemsSyncPackets']['add_for_input']=array(
		    'clientid'=>$this->clientid,
		);
		$this->syncable_tables['SystemsSyncPackets']['sql_select']=function($ipid) {
		    $sqlpm = Doctrine_Query::create()
		    ->select('*')
		    ->from('SystemsSyncPackets')
		    ->where("ipid=?", $ipid)
		    ->andwhere("outgoing=1");
		    $arr = $sqlpm->fetchArray();
		
		    $return=array();
		    if(count($arr)>0){
		        foreach($arr as $arr_e) {
		            $return[$arr_e['id']] = $arr_e;
		        }
		    }
		
		    return $return;
		};
		
		
		
		//All about PatientMaintainanceStage Pflegegrad - 22.11.2018
		$this->syncable_tables['PatientMaintainanceStage'] = $tables_default;
		$this->syncable_tables['PatientMaintainanceStage']['update']="always";
		$this->syncable_tables['PatientMaintainanceStage']['plain'] = array(
		    'fromdate',
		    'tilldate', //maybe we dont need it
		    'stage',
		    'erstantrag',
		    'horherstufung',
		    'e_fromdate',
		    'h_fromdate',
		    'isdelete'
		);
		
		
	}
}
?>