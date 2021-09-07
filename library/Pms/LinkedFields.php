<?

class Pms_LinkedFields{
	
	public static function getContents($farr,$get,$forpdf = false)
	{
		$c = 0;
		
		foreach($farr as $key=>$val)
		{
				$fetchvalue = true;
				
				switch ($val['type'])
				{
					case 'text': 
						
					break;
					case 'textarea':
						if(strlen($val['linkedtable'])>0)
						{
							$farr[$key] = Pms_LinkedFields::getFieldContent($val,$get);
							$fetchvalue = false;
						}
						break;
					case 'textbox': 
						if(strlen($val['linkedtable'])>0)
						{
							$farr[$key] = Pms_LinkedFields::getFieldContent($val,$get);
							$fetchvalue = false;
						}	
						break;
					case 'dropdown': 
						if(strlen($val['linkedtable'])>0)
						{
							$farr[$key] = Pms_LinkedFields::getoptions($val);
							
							
						}
						$fetchvalue = true;
						break;
					case 'checkbox':
						$fetchvalue = true;
						break;
					case 'checkboxmatrix':
						$fetchvalue = true;
						break;	
					case 'radio':
						$fetchvalue = true;
						break;
					case 'datetime':
						$fetchvalue = true;
						break;
					case 'fileupload':
						
						break;
					case 'fbbutton':
						break;
				}
				
				if($fetchvalue)
				{
					$frmid = $get['frmid'];
					
					$fieldid = $val['id'];
					
					if($forpdf){
						
						$q = Doctrine_Query::create()
							->select('*')
							->from('PdfForms')
							->where("id='".$val['pdfid']."'");
						$qe = $q->execute();
						$qearr = $qe->toArray();
						
						
						
						$fieldid = $val['fieldid'];
						$frmid =  $qearr[0]['formid'];
						
					}
					
					$q = Doctrine_Query::create()
							->select('*')
							->from('FbFieldValues')
							->where("fieldid='".$fieldid."' and formid='".$frmid."' and patientid='".$get['id']."'");
						
						$qe = $q->execute();
						$qearr = $qe->toArray();
						
						$farr[$key]['content'] = $qearr[0]['fieldvalue'];
				}	
						
						
				
			/*if($c>9)
			{
				break;
			}	
			$c++;	*/
				
		
			
		}
		
		return $farr;
	
	}
	
	public static function getoptions($field)
	{
		$tablename = $field['linkedtable'];
		
		if(method_exists('Pms_LinkedFields',$tablename))
		{
			$field['options'] = Pms_LinkedFields::$tablename($field);
			
		}
			return $field;
	}
	
	public static function getFieldContent($field,$get)
	{
		$tablename = $field['linkedtable']."_content";
		
		if(method_exists('Pms_LinkedFields',$tablename))
		{
			
			$field = Pms_LinkedFields::$tablename($field,$get);
			
		}
			return $field;
	}
	
	
	
	public static function symptomatology_master($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("SymptomatologyMaster")
			->where("clientid=0 or clientid='".$logininfo->clientid."'");
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function course_shortcuts($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("Courseshortcuts")
			->where("clientid=0 or clientid='".$logininfo->clientid."'");
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['shortcut_id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function diagnosis($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("Diagnosis")
			->where("clientid=0 or clientid='".$logininfo->clientid."'")
			->limit(100);
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function diagnosis_freetext($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("DiagnosisText")
			->where("clientid=0 or clientid='".$logininfo->clientid."'");
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function diagnosis_type($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("DiagnosisType")
			->where("clientid=0 or clientid='".$logininfo->clientid."'");
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function discharge_method($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("DischargeMethod")
			->where("clientid=0 or clientid='".$logininfo->clientid."'");
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function family_doctor($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("FamilyDoctor")
			->where("clientid=0 or clientid='".$logininfo->clientid."'");
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function health_insurance($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("HealthInsurance")
			->where("clientid=0 or clientid='".$logininfo->clientid."'");
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function kbv_keytabs($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("KbvKeytabs");
			
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	public static function medication_master($field)
	{
		
		$logininfo= new Zend_Session_Namespace('Login_Info');
		
		$q = Doctrine_Query::create()
			->select("*")
			->from("Medication")
			->where("clientid=0 or clientid='".$logininfo->clientid."'");
			
		$qe = $q->execute();
		$qarr = $qe->toArray();
		

		
		foreach($qarr as $key=>$val)
		{
			$options[$val['id']] = utf8_encode(stripslashes($val[$field['linkedfield']]));
		
		}
		
		return $options;
	}
	
	
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	public static function patient_master_content($field,$get)
	{
		if(!isset($get['id'])) return $field;
		
		
		
		$p = Doctrine_Query::create()
		->select("*,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,
		   	AES_DECRYPT(middle_name,'".Zend_Registry::get('salt')."') as middle_name,
			AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,
			AES_DECRYPT(title,'".Zend_Registry::get('salt')."') as title,
			AES_DECRYPT(salutation,'".Zend_Registry::get('salt')."') as salutation,
			AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,
			AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
			AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') as zip
			,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city
			,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone
			,AES_DECRYPT(mobile,'".Zend_Registry::get('salt')."') as mobile
			,AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') as sex")
		->from('PatientMaster')
		->where('id='.$get['id']);
		$pexec = $p->execute();
		$parr = $pexec->toArray();
		
		
		
		$field['content'] = stripslashes($parr[0][$field['linkedfield']]);
		
		return $field;
	
	}
	
	public static function patient_health_insurance_content($field,$get)
	{
		if(!isset($get['id'])) return $field;
		
		
		
		$ipid = Pms_CommonData::getIpid($get['id']);
		
		$p = Doctrine_Query::create()
			  ->select("*,AES_DECRYPT(insurance_status,'".Zend_Registry::get('salt')."') as insurance_status
			  ,AES_DECRYPT(status_added,'".Zend_Registry::get('salt')."') as status_added
			  ,AES_DECRYPT(ins_first_name,'".Zend_Registry::get('salt')."') as ins_first_name
			  ,AES_DECRYPT(ins_middle_name,'".Zend_Registry::get('salt')."') as ins_middle_name
			  ,AES_DECRYPT(ins_last_name,'".Zend_Registry::get('salt')."') as ins_last_name
			  ,AES_DECRYPT(ins_zip,'".Zend_Registry::get('salt')."') as ins_zip
			  ,AES_DECRYPT(ins_city,'".Zend_Registry::get('salt')."') as ins_city
			  ,AES_DECRYPT(help1,'".Zend_Registry::get('salt')."') as help1
			  ,AES_DECRYPT(help2,'".Zend_Registry::get('salt')."') as help2
			  ,AES_DECRYPT(help3,'".Zend_Registry::get('salt')."') as help3
			  ,AES_DECRYPT(help4,'".Zend_Registry::get('salt')."') as help4")
			  ->from('PatientHealthInsurance')
			  ->where("ipid='".$ipid."'");
		$pexec = $p->execute();
		$parr = $pexec->toArray();
		
		$field['content'] = stripslashes($parr[0][$field['linkedfield']]);
		
		return $field;
	
	}
}

?>