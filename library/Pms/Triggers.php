<?

class Pms_Triggers{

	protected $view;
	
	protected $logininfo = null;
	
	public function __construct()
	{
		$this->view = Zend_Layout::getMvcInstance()->getView();
		
		$this->logininfo= new Zend_Session_Namespace('Login_Info');
	}
	
	public function sendmail(Doctrine_Event $event)
	{
		$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
		$mail = new Zend_Mail();

		$mail->setBodyText("Patient first name updated to " . $event->getInvoker()->first_name)
			->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
			->addTo(ISPC_ERRORMAILTO, ISPC_ERRORSENDERNAME)
			->setSubject("Patient Name Updated")
			->send($mail_transport);
	}

	public function internalmessage(Doctrine_Event $event)
	{
		$assignid = Doctrine_Query::create()
			  ->select('*')
			  ->from('PatientQpaMapping')
			  ->where("epid = '".Pms_CommonData::getEpidFromId($event->getInvoker()->id)."'");
				
		$assignidexec = $assignid->execute();
		$assignidarray = $assignidexec->toArray();
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		foreach($assignidarray as $key=>$val)
		{
			$mail = new Messages();
			$mail->sender = $logininfo->userid;
			$mail->clientid = $logininfo->clientid;
			$mail->recipient = $val['userid']; 
			$mail->msg_date = date("Y-m-d H:i:s",time()); 
			$mail->title = "Patient Name Updated";
			$mail->content = "Patient Name Updated to ".$event->getInvoker()->first_name;		
			$mail->create_date = date("Y-m-d",time());
			$mail->create_user = $logininfo->userid;
			$mail->save(); 
		}
	
	}
	
	public static function addfamilydoctocourse($a_post)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$query = Doctrine_Query::create()
			  ->select('*')
			  ->from('FieldTrigger')
			  ->where('fieldid=14 and formid=1 and event=2 and isdelete=0 and triggerid=4 and clientid='.$logininfo->clientid);
			 $result = $query->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
		foreach($result as $key=>$val)
		{
			$ipid = $a_post['ipid'];
			//$epid  = $a_post['epid'];
			
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
		
		    if($a_post['hidd_docid']>0)
			{
				$doctorid = $a_post['hidd_docid'];
				$cs = new FamilyDoctor();
				$csarr = $cs->getFamilyDoc($doctorid);
				$comment = "Hausarzt ".$csarr[0]["first_name"]." ".$csarr[0]["last_name"]." eingetragen.";
				
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->isstandby = $a_post['isstandby'];
				$cust->course_type=Pms_CommonData::aesEncrypt("K");
				$cust->course_title=Pms_CommonData::aesEncrypt($comment);
				$cust->user_id = $userid;
				$cust->save(); 
			}
			
			if($a_post['fdoc_caresalone']==1)
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt("K");
				$cust->isstandby = $a_post['isstandby'];
				$cust->course_title=Pms_CommonData::aesEncrypt("!! Hausarzt versorgt alleine !!");
				$cust->user_id = $userid;
				$cust->save();
			}
		}
	}
	
	public static function addMetaDiagnosistocourse($a_post)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$ipid = $a_post['ipid'];
		
		$query = Doctrine_Query::create()
			  ->select('*')
			  ->from('FieldTrigger')
			  ->where('fieldid=30 and formid=9 and event=2 and isdelete=0 and triggerid=4 and clientid='.$logininfo->clientid);
			 $result = $query->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
			
		if(count($result)>0)
		{
			
			
			if(count($a_post['meta_title'])>0)
			  {
				 
					 foreach($a_post['meta_title'] as $key=>$val)
					{
						  
						  foreach($val as $k=>$v)
						  {
								
								if($v>0) 
								{
									  $dm = new DiagnosisMeta();
									  $dmarr = $dm->getDiagnosisMetaDataById($v);
									  $dtitle = $dmarr[0]['meta_title'];
									  
									  $cust = new PatientCourse();
									  $cust->ipid = $ipid;
									  $cust->course_date = date("Y-m-d H:i:s",time());
									  $cust->course_type=Pms_CommonData::aesEncrypt("D");
									  $cust->isstandby = $a_post['isstandby'];
									  $cust->course_title=Pms_CommonData::aesEncrypt($dtitle);
									  $cust->user_id = $userid;
									  $cust->save(); 
								 } 
						   } 
						  
					 }
				}
	
		}
	}
	
	public static function updateMetaDiagnosistocourse($meta_id,$ipid)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		
		
		if($meta_id>0)
		{
			$dm = new DiagnosisMeta();
			$dmarr = $dm->getDiagnosisMetaDataById($meta_id);
			$dtitle = $dmarr[0]['meta_title'];
						
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("D");
			$cust->course_title=Pms_CommonData::aesEncrypt(addslashes($dtitle));
			$cust->user_id = $userid;
			$cust->save(); 
		}
				 
	}
	
	public static function DiagnosisTypeChange($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		
		$dp = new DiagnosisType();
		$dparr = $dp->getDiagnosisTypesById($post['diagnosis_type']);
		
		if($dparr[0]['abbrevation']=="HD")
		{
		  $type = "H";
		}
		else if($dparr[0]['abbrevation']=="HS")
		{
			$type = "HS";
		}
		else
		{
		  $type = "D";
		}
		
		$cust = new PatientCourse();
		$cust->ipid = $post['ipid'];
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt($type);
		$cust->course_title=Pms_CommonData::aesEncrypt($post['diagnosis']);
		$cust->user_id = $userid;
		$cust->save(); 
				 
	}
    
	public static function InsertFamilyDoctor($ipid,$clientid,$epid)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$query = Doctrine_Query::create()
			  ->select('f.*,t.*,s.*,k.*')
			  ->from('FieldTrigger f')
			  ->innerjoin("f.TriggerTriggers t")
			  ->innerjoin("f.TriggerForms s")
			  ->innerjoin("f.TriggerFields k")
			  ->where("f.formid=1 and f.event=2 and f.isdelete=0 and f.fieldid=14 and f.clientid=".$clientid);
		
	   $result = $query->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
		
		if(count($result)>0)
		{
		      $patientmaster = new PatientMaster();
		      $patientdetails = $patientmaster->getMasterData(0,0,0,$ipid); 
			  if($patientdetails['familydoc_id']>0)
			  {
			  		$fdoc = new FamilyDoctor();
					$docarray = $fdoc->getFamilyDoc($patientdetails['familydoc_id']);
				
					$docfirstname = $docarray[0]['first_name'];
					$doclastname = $docarray[0]['last_name'];	
					
					$inputs = unserialize($result[0]['inputs']);
					
					if(strlen($inputs['dataset'])>0)
					{
						$comment = str_replace("#docfirstname",$docarray[0]["first_name"],$inputs['dataset']);
						$comment = str_replace("#doclastname",$docarray[0]["last_name"],$comment);
						$comment = str_replace("#epid",$epid,$comment);	
					}
					else
					{
						$comment = $docfirstname." ".$doclastname." "."wurde diesem Patienten zugewiesen";
					}
					
					    $cust = new PatientCourse();
						$cust->ipid = $ipid;
						$cust->course_date = date("Y-m-d H:i:s",time());
						$cust->course_type=Pms_CommonData::aesEncrypt("K");
						$cust->course_title=Pms_CommonData::aesEncrypt($comment);
						$cust->user_id = $logininfo->userid;
						$cust->isstandby = $patientdetails['isstandby'];
						$cust->save();
				
			  }
		}
		
		
		/*if($meta_id>0)
		{
			$dm = new DiagnosisMeta();
			$dmarr = $dm->getDiagnosisMetaDataById($meta_id);
			$dtitle = $dmarr[0]['meta_title'];
						
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("D");
			$cust->course_title=Pms_CommonData::aesEncrypt(addslashes($dtitle));
			$cust->user_id = $userid;
			$cust->save(); 
		}*/
				 
	}
	
	
	public static function callTrigger($event,$frmid,$eventid,$fieldidarr,$gmod,$gpost)
	{
	  
	  /*print_r($gpost); */
	  
	  
		$fieldidarr=array_unique($fieldidarr);
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		//echo "fieldcount : ".count($fieldidarr);
	    foreach($fieldidarr as $fkey => $fval)
	    {
			$query = Doctrine_Query::create()
			  ->select('f.*,t.*,s.*,k.*')
			  ->from('FieldTrigger f')
			  ->innerjoin("f.TriggerTriggers t")
			  ->innerjoin("f.TriggerForms s")
			  ->innerjoin("f.TriggerFields k")
			  ->where('f.formid='.$frmid.' and f.event="'.$eventid.'" and f.isdelete=0 and f.fieldid='.$fval ." and f.clientid=".$logininfo->clientid);
			  
			// echo $query->getSqlQuery();
			
			 $result = $query->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
				//echo "triggercount : ".count($result);
			
			
			 foreach($result as $key=>$val)
			 {
				//print_r($val);
				$triggername= $val['TriggerTriggers']['triggername'];
				
				$formname= $val['TriggerForms']['formname'];
				$fieldid=$val['fieldid'];
				$fieldname=$val['TriggerFields']['fieldname'];
				
				$inputs=Pms_CommonData::array_stripslashes(unserialize($val['inputs']));
				$classname='application_Triggers_'.$triggername;
				$callfn = new $classname;
				$functionname='trigger'.$triggername;
				
			
				
				
				if($frmid==9)
				{				  
					 
					  $dquery=Doctrine_Query::create()
					  ->select('free_name')
					  ->from('DiagnosisText')
					  ->where('id='.$gmod['diagnosis_id']);
					  $dresult = $dquery->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
					  $diagnosistext = $dresult[0]['free_name'];
					  $inputs=$diagnosistext;
						
					  $callfn->$functionname($event,$inputs,$fieldname,$fieldid,$eventid,$gpost);
				}
				elseif($frmid==10)
				{
				  
				    if(isset($_POST['diagnosis']))
					{
						$diagnosis_arr = implode('', $_POST['diagnosis']); 
						
						if($functionname=="hl7Message")
						{
						  if(empty($diagnosis_arr))
							{
								$callfn->$functionname($event,"Patient discharge without any diagnosis",$fieldname,$fieldid,$eventid,$gpost);
							}
							else
							{
								$callfn->$functionname($event,"Patient discharge with diagnosis",$fieldname,$fieldid,$eventid,$gpost);
							}
						}
						else
						{
						   $callfn->$functionname($event,$inputs,$fieldname,$fieldid,$eventid,$gpost);
						}
						
					}
					else
					{
					    $callfn->$functionname($event,$inputs,$fieldname,$fieldid,$eventid,$gpost,$gmod);
					}
				}
				else
				{
				     if($frmid==4)
					 {
					 	 
						 //$val[operand] = Pms_CommonData::aesEncrypt($val[operand]);	
						 $aparr = array();
						 $aparr = explode(",",$val[operand]);
					 }
					
					if($val['operator']==1)
					{
					  for($i=0;$i<count($aparr);$i++)
					  {
					  	$val[operand] = Pms_CommonData::aesEncrypt($aparr[$i]);
						
						if($gmod[$fieldname]==$val[operand])
						  {
							$callfn->$functionname($event,$inputs,$fieldname,$fieldid,$eventid,$gpost);
						  }
					  }
				
					}
					
					elseif($val['operator']==2)
					{
					   //not equalto
					  for($i=0;$i<count($aparr);$i++)
					  {
					  	$val[operand] = Pms_CommonData::aesEncrypt($aparr[$i]);
						
						if($gmod[$fieldname]<>$val[operand])
						  {
							$callfn->$functionname($event,$inputs,$fieldname,$fieldid,$eventid,$gpost);
						  }
					  }
					
					}
					elseif($val['operator']==3)
					{
					   //any value
					   $callfn->$functionname($event,$inputs,$fieldname,$fieldid,$eventid,$gpost);
					}
					elseif($val['operator']==4)
					{
					   //blank value
					   if($gmod[$fieldname]=="")
					   {
						 $callfn->$functionname($event,$inputs,$fieldname,$fieldid,$eventid,$gpost);
					   }
					}
					elseif($val['operator']==0)
					{
						$callfn->$functionname($event,$inputs,$fieldname,$fieldid,$eventid,$gpost);
					}
				}
				
			 }
		}
		
	}

}

?>