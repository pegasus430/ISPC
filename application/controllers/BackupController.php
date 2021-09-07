<?php

class BackupController extends Zend_Controller_Action
{
    
	public function init()
    {
    }
	
	public	function exportdbAction()
	{		
			set_time_limit(0);
			if($this->getRequest()->isPost())
			{
				if(strlen($_POST['dbname'])<1){$this->view->error_dbname=$this->view->translate('selectdatabasetotakebackup');$error=1;}
			
			if($error==0)
			{
				$manager = Doctrine_Manager::getInstance();			
				$manager->setCurrentConnection($_POST['dbname']);
				
				$conn =  $manager->getCurrentConnection();
				
				$cells = $conn->import->listTables();
					
				$f = fopen(APPLICATION_PATH."dbbackup.bcu", "w+");
				
				foreach($cells as $key=>$val) 
				{
					
					$table = $val;
					
					fwrite($f,"TRUNCATE TABLE ".$table.";\n"); 
					
					$query = $conn->prepare("SHOW CREATE TABLE ".$table );
					$res = $query->execute();
					
					
					
					if($res) 
					{
						
						$create = $query->fetchAll();

						$line ="";
						
						 $queryrow = $conn->prepare("SELECT * FROM ".$table);
						 $queryrow->execute();
						 
						  $num = $conn->import->listTableColumns($table);
						
						$row = $queryrow->fetchAll();
						foreach($row as $key=>$val)
						{
							
							$line = "INSERT INTO ".$table." VALUES(";
							
							for ($i=0;$i<count($num);$i++) {
									$line .= "'".addslashes($row[$key][$i])."', ";
							}
						
							$line = substr($line,0,-2).");\n";
							fwrite($f,$line);
						}
						
					}
				}
				
				fclose($f);
				
				$filename = "ISPC_".$_POST['dbname']."_BCKUP_".date("Ymd-His")."dbbackup.bcu";
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				$file = base64_encode(file_get_contents(APPLICATION_PATH."dbbackup.bcu"));
				echo $file;
				exit;
			}
		}
	}
	
	public function importdbAction()
	{
		set_time_limit(0);
		ini_set("upload_max_filesize", "10M");
		$this->_helper->viewRenderer('exportdb'); 
		if($this->getRequest()->isPost())
		{
			if(strlen($_SESSION['filename'])<1){$this->view->error_filename = $this->view->translate("uploadcsvfile");$error=1;}
			if(strlen($_POST['dbname'])<1){$this->view->error_dbname=$this->view->translate('Selectdatabasetotakebackup');$error=1;}
			if($error==0)
			{
				$manager = Doctrine_Manager::getInstance();	
				$manager->setCurrentConnection($_POST['dbname']);
				$conn =  $manager->getCurrentConnection();
				$ende = explode(".",trim(basename($_SESSION['filename'])));
				$filename= "uploadfile/".$_SESSION['filename'];
				if(trim($ende[count($ende)-1])=="bcu") 
				{
					$fopen = fopen($filename,'rb');
					while (!feof($fopen)) {
						
						$contents .= fread($fopen, 8192);
						}
					
					$backup = base64_decode($contents);
					
					$queryrow = $conn->prepare($backup);
					$queryrow->execute();
					unset($_SESSION['filename']);  
					$this->view->error_message1 = $this->view->translate("databaseimportsucessfully");
					$this->view->erorclass = "err";
					
				}else{
					unset($_SESSION['filename']);  
					$this->view->error_message1 = $this->view->translate("youuploadedaninvalidfile");
					$this->view->erorclass1 = "err";
					
				}
			}
		}
	}
	
	
	
	
	
	public function clientbackupAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		
		if($this->getRequest()->isPost())
		{
			
			$manager = Doctrine_Manager::getInstance();			
			$manager->setCurrentConnection('IDAT');
			$conispc1 =  $manager->getCurrentConnection();
			
			$manager = Doctrine_Manager::getInstance();			
			$manager->setCurrentConnection('MDAT');
			$conispc2 =  $manager->getCurrentConnection();
			
			$manager = Doctrine_Manager::getInstance();			
			$manager->setCurrentConnection('SYSDAT');
			$conispc3 =  $manager->getCurrentConnection();	   
			
			set_time_limit(0);
			
			define('BACKUP_PATH','clientbackup');
			mysql_select_db("ispc2",$conispc2);
			
						
			$tablearray = array(
				"epid_ipid"=>array(
				'ispc1'=>'contactperson_master',
				'ispc1'=>'epid_ipid',
				'ispc1'=>'patient_health_insurance',
				'ispc1'=>'patient_master',
				'ispc2'=>'patient_diagnosis',
				'ispc2'=>'patient_drugplan',
				'ispc2'=>'symptomatology'));
					
			$conarray = array("IDAT"=>"ispc1","MDAT"=>"ispc2","SYSDAT"=>"ispc3");
			
				$clientid = $logininfo->clientid;
				if($logininfo->usertype=='SA')
				{
					 $client = Doctrine_Query::create()
						 ->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
				AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
				,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
							->from("Client")
							->where('id ='.$logininfo->clientid);
						$clientexec = $client->execute();	
					  $clientarray = $clientexec->toArray(); 
					  $clientname = trim($clientarray[0]['client_name']);
					
					
				}else{
					$clientname = trim($logininfo->clientname);
				}
				
				$zipname = $clientname."_".date("Y-m-d-Hi");
				 $folderpath = BACKUP_PATH."/backupfiles/".$clientname."_".date("Y-m-d-Hi");
				 $folder = mkdir($folderpath);
				
				foreach($conarray as $key=>$val)
				{
					$con = 'con'.$val;
					mysql_select_db("'".$val."'",${$con});
					
					$f = fopen($folderpath."/".$val."_db.bcu", "w+");
					$tables = mysql_list_tables($val,${$con});
					
					while ($cells = mysql_fetch_array($tables)) 
					{
							$table = $cells[0];
							$data = mysql_query("SELECT * FROM  ".$table ,${$con});
							$isclientid=false;
							while($fields = mysql_fetch_field($data))
							{
								if($fields->name =='clientid')
								{
									$isclientid = true;
								}
							}
							
							if($isclientid) 
							{
											
								$clientdata = mysql_query("SELECT * FROM  ".$table ." where clientid='".$clientid."'",${$con});
								$num = mysql_num_fields($clientdata);
								
											if($num>0)
											{							
												while ($row = mysql_fetch_array($clientdata))
												{
													$line = "INSERT INTO ".$table ." VALUES(";
													for ($i=1;$i<=$num;$i++) 
													{
														$line .= "'".addslashes($row[$i-1])."', ";
													}
													$line = substr($line,0,-2);
													fwrite($f,base64_encode($line.");\n"));
												}
											}
								
							}else{
										mysql_select_db("ispc1",$conispc1);
										$dataipid = mysql_query("SELECT ipid FROM  epid_ipid  where clientid='".$clientid."'",$conispc1);
										
										
										$comma="";
										
										while($fetchdata = mysql_fetch_array($dataipid))
										{
											$ipidval .= $comma."'".$fetchdata['ipid']."'";
											$comma=",";
										}
										
							
								foreach($tablearray as $key1=>$val1)
								{
			
									foreach($val1 as $dtbname=>$tablename)
									{
											
											$con1 = 'con'.$dtbname;
										
											if($dtbname==$val)
											{
												$clientdata = mysql_query("SELECT * FROM  ".$table ." where ipid in (".$ipidval.")",${$con1});
												if($clientdata)
												{
													$num = mysql_num_fields($clientdata);
												
													if($num>0)
													{	
																		
														while ($row = mysql_fetch_array($clientdata))
														{
															$line = "INSERT INTO ".$table ." VALUES(";
															for ($i=1;$i<=$num;$i++) 
															{
																$line .= "'".addslashes($row[$i-1])."', ";
															}
															
															$line = substr($line,0,-2);
															fwrite($f, base64_encode($line.");\n"));
															
														}
													}
												}
											}
										
									}
								}
							}
							
							
						}
						
						fclose($f);
					
				
				
				
				
				$cmd = "zip -9 -r ". $folderpath.".zip ".$folderpath .";";
				$exec = exec($cmd);
				$this->view->error_message1 = $this->view->translate("zippasswordsendtoyourmailbox");
				header('Content-Type: application/zip');
				header('Content-Disposition: attachment; filename="'.$folderpath.'.zip"');
				$file = file_get_contents($folderpath.".zip");
				echo $file;
			}
				if($exec)
				{
					$dbuser = Doctrine::getTable('User')->find($logininfo->userid);
					$usarray = $dbuser->toArray();
				
					$mail = new Zend_Mail();
					$mail->setBodyHtml($zipname.".zip <br /> Password is : ".$logininfo->filepass)
				  	->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
				  	->addTo($usarray['emailid'], $usarray['last_name'].',&nbsp;'.$usarray['first_name'])
				  	->setSubject("Database Backup Zip Password")
				  	->send();
					
					
				  }
		}
	}
	
	public function importclientdbAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		set_time_limit(0);
		ini_set("upload_max_filesize", "10M");
		$this->_helper->viewRenderer('clientbackup'); 
			
		
		if($this->getRequest()->isPost())
		{			
			$manager = Doctrine_Manager::getInstance();			
			$manager->setCurrentConnection('IDAT');
			$conispc1 =  $manager->getCurrentConnection();
				
			$manager = Doctrine_Manager::getInstance();			
			$manager->setCurrentConnection('MDAT');
			$conispc2 =  $manager->getCurrentConnection();
				
			$manager = Doctrine_Manager::getInstance();			
			$manager->setCurrentConnection('SYSDAT');
			$conispc3 =  $manager->getCurrentConnection();	   
		
		
			mysql_select_db("ispc2",$conispc2);
			
			$tablearray = array(
				"epid_ipid"=>array(
				'ispc1'=>'contactperson_master',
				'ispc1'=>'epid_ipid',
				'ispc1'=>'patient_health_insurance',
				'ispc1'=>'patient_master',
				'ispc2'=>'patient_diagnosis',
				'ispc2'=>'patient_drugplan',
				'ispc2'=>'symptomatology'));
					
			$conarray = array("IDAT"=>"ispc1","MDAT"=>"ispc2","SYSDAT"=>"ispc3");
			
				$clientid = $logininfo->clientid;
			if(strlen($_SESSION['filename'])<1){$this->view->error_message =$this->view->translate('uploadcsvfile');$error=1;}	
			if(strlen($_POST['dbname'])<1){$this->view->error_dbname=$this->view->translate('Selectdatabasetotakebackup');$error=1;}
			$ende = explode(".",trim(basename($_SESSION['filename'])));
				$filename= "uploadfile/".$_SESSION['filename'];
				if(trim($ende[count($ende)-1])!="bcu") 
				{
					unset($_SESSION['filename']);  
					$this->view->error_message1 = $this->view->translate('Youuploadedaninvalidfile');	
					$error=1;
				}
			
			
			if($error==0)
			{			
				
				if(strlen($_POST['dbname'])>0)
				{
				
					$con = 'con'.$conarray[$_POST['dbname']];
					
					
					$tables = mysql_list_tables($conarray[$_POST['dbname']],${$con});
					
					while ($cells = mysql_fetch_array($tables)) 
					{
							$table = $cells[0];
							$data = mysql_query("Select * FROM  ".$table ,${$con});
							$isclientid=false;
							while($fields = mysql_fetch_field($data))
							{
								
								if($fields->name =='clientid')
								{
									$isclientid = true;
								}
							}
							
							if($isclientid) 
							{
								
								$clientdata = mysql_query("DELETE  FROM  ".$table ." where clientid='".$clientid."'",${$con});
																	
								
							}else{
										mysql_select_db("ispc1",$conispc1);
										
										$dataipid = mysql_query("SELECT ipid FROM  epid_ipid  where clientid='".$clientid."'",$conispc1);
																				
										$comma="";
										
										while($fetchdata = mysql_fetch_array($dataipid))
										{
											$ipidval .= $comma."'".$fetchdata['ipid']."'";
											$comma=",";
										}
										
										//echo $ipidval;
							
								foreach($tablearray as $key1=>$val1)
								{
								
									foreach($val1 as $dtbname=>$tablename)
									{
											
											$con1 = 'con'.$dtbname;
											$conarray[$_POST['dbname']]."<br>".$dtbname;
											if($dtbname==$conarray[$_POST['dbname']])
											{
												mysql_select_db("'".$dtbname."'",${$con1});
												
												$clientdata = mysql_query("DELETE FROM  ".$table ." where ipid in (".$ipidval.")",${$con1});
												if($clientdata)
												{
													$num = mysql_num_fields($clientdata);
												
												}
											}
										
									}
								}
							}
					
					
				}
			
			
			
								
				$manager = Doctrine_Manager::getInstance();	
				$manager->setCurrentConnection($_POST['dbname']);
				$conn =  $manager->getCurrentConnection();
				$ende = explode(".",trim(basename($_SESSION['filename'])));
				$filename= "uploadfile/".$_SESSION['filename'];
				if(trim($ende[count($ende)-1])=="sql") 
				{
					$backup = base64_encode(file_get_contents($filename));
					$array = explode("\n",$backup);
					
					for($x = 0; $x < count($array); $x++) 
					{ 
						if(strlen($array[$x])>0)
						{
						 
						 $queryrow = $conn->prepare($array[$x]);
						 $queryrow->execute();
						}
						
					}
					unset($_SESSION['filename']);
					$this->view->error_message = $this->view->translate("databaseimportedsucessfully");
				}else{
					unset($_SESSION['filename']);
					$this->view->error_message = $this->view->translate("databaseimporterror");
				}
			}
		}
	}
	
	}
	private function importquery($query,$dbname) 
	{
		$manager = Doctrine_Manager::getInstance();	
		$manager->setCurrentConnection($dbname);
		$conn =  $manager->getCurrentConnection();
		
		echo $query;
		
		
		if(is_array($query))
		{
			for($x = 0; $x < count($query); $x++)
			{
				
				$queryrow = $conn->prepare($query[$x]);
				$queryrow->execute();
			}
			
		}else{
			$queryrow = $conn->prepare($query);
			$queryrow->execute();
			
		}
	}
	
	 public function uploadifyAction()
     {
   		ini_set("upload_max_filesize", "10M");
		$filename= "uploadfile/".$_FILES['qqfile']['name'];
		$_SESSION['filename'] =$_FILES['qqfile']['name'];
		move_uploaded_file($_FILES['qqfile']['tmp_name'],"uploadfile/".$_FILES['qqfile']['name']);	
		echo json_encode(array(success=>true));
		exit;
    }
	
		public function ftpconectAction()
		{
		
			$conn_id = ftp_connect(Zend_Registry::get('ftpserver')) or die("Couldn't connect to $ftp_server");  
			// login with username and password
			$login_result = ftp_login($conn_id, Zend_Registry::get('ftpserveruser'), Zend_Registry::get('ftpserverpasswd')); 
			ftp_pasv($conn_id, true); 
			var_dump($conn_id);
			// check connection
			if ((!$conn_id) || (!$login_result)) 
			{ 
				$this->view->ftpmsg =  "FTP connection has failed!";
				$this->view->ftpmasg = "Attempted to connect to ".Zend_Registry::get('ftpserver')." user ".Zend_Registry::get('ftpserveruser');
				
			} else {
				$this->view->ftpmsg = "Connected to ".Zend_Registry::get('ftpserver').", for user ".Zend_Registry::get('ftpserveruser');
				$upload = Pms_FtpFileupload::fileupload($conn_id,'uploads/doctorletter1.pdf','uploads/doctorletter1.pdf');
				
			}
			
			$buff = ftp_rawlist($conn_id, './uploads/');
 			var_dump($buff); 
			Pms_FtpFileupload::ftpconclose($conn_id);
		}
			
}

?>