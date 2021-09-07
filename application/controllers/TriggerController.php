<?

class TriggerController extends Zend_Controller_Action
{
	public function settriggerAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		if($this->getRequest()->isPost() && $_POST['submit1']=='Submit')
		{
			$settrr = new Application_Form_SetTriggers();
			$settrr->InsertData($_POST);

			$this->_redirect(APP_BASE.'trigger/listfields?frmid='.$_GET['frmid']);

		}

		$f = Doctrine_Core::getTable('TriggerForms')->findAll();
		$frmarr[0] = "Select Form";
		foreach($f->toArray() as $key=>$val)
		{
			$frmarr[$val['id']] = $val['formname'];
		}
		 
		$this->view->forms = $frmarr;

		$f = Doctrine_Query::create()
		->select('f.*,t.*')
		->from("TriggerFields f")
		->innerjoin("f.TriggerForms t")
		->where("f.id='".$_GET['fid']."'");
		$frr = $f->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
		$formname = $frr[0]['TriggerForms']['formname'];
			
		$this->getFieldNames($formname);
			
		$this->view->formname = $frr[0]['TriggerForms']['formname'];
		$this->view->fieldname  = $frr[0]['fieldname'];
		$this->view->form_label  = $frr[0]['form_label'];

		$f = Doctrine_Core::getTable('TriggerTriggers')->findAll();
		$f->toArray();
		$eventsarray = array(1=>'update',2=>'insert',3=>'fetch');
		 
		foreach($eventsarray as $id=>$event)
		{
			$q = Doctrine_Query::create()
			->select("*")
			->from("FieldTrigger f")
			->where("event='".$id."' and fieldid='".$_GET['fid']."' and clientid='".$logininfo->clientid."'");
			$qr = $q->execute();
			if($qr){
				$qarr = $qr->toArray();
			}
			$ftarr = array();
			foreach($qarr as $val)
			{
				$ftarr[$val['event']][$_GET['fid']]['trigger'][] = $val['triggerid'];
				$ftarr[$val['event']][$_GET['fid']][$val['triggerid']]['operator'] = $val['operator'];
				$ftarr[$val['event']][$_GET['fid']][$val['triggerid']]['operand'] = $val['operand'];
				$ftarr[$val['event']][$_GET['fid']][$val['triggerid']]['inputs'] = Pms_CommonData::array_stripslashes(unserialize($val['inputs']));
			}
			$grid = new Pms_Grid($f->toArray(),1,count($f->toArray()),"triggerlist.html");
			$grid->eventid = $id;
			$grid->fieldid = $_GET['fid'];
			$grid->ftarr = $ftarr;
			$grid->operators = array(0=>'Select',1=>'Equal To',2=>'Not Equal To',3=>'Any Value','Blank Value');
			$this->view->{"triggerlist".$id} = $grid->renderGrid();
		}
	}

	public function edittriggerAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		 
		if($this->getRequest()->isPost() && $_POST['submit1']=='Submit')
		{
			$settrr = new Application_Form_SetTriggers();
			$settrr->InsertData($_POST);
			$this->_redirect(APP_BASE.'trigger/listfields?frmid='.$_GET['frmid']);
		}

		$f = Doctrine_Query::create()
		->select('f.*,t.*')
		->from("TriggerFields f")
		->innerjoin("f.TriggerForms t")
		->where("f.id='".$_GET['id']."'");
			
		$frr = $f->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
			
		$eventarr = array(1=>'Update',2=>'Insert',3=>'Fetch');
		$formname = $frr[0]['TriggerForms']['formname'];
			
		$this->getFieldNames($formname);
		$this->view->formname = $frr[0]['TriggerForms']['formname'];
		$this->view->eventname  = $eventarr[$_GET['event']];
		$this->view->form_label  = $frr[0]['form_label'];
		$isform  = $frr[0]['isform'];
			

		$f = Doctrine_Core::getTable('TriggerTriggers')->findAll();
		$f->toArray();
		$q = Doctrine_Query::create()
		->select("*")
		->from("FieldTrigger f")
		->where("event='".$_GET['event']."' and fieldid='".$_GET['id']."' and clientid='".$logininfo->clientid."'");
		$qr = $q->execute();
		if($qr){
			$qarr = $qr->toArray();
		}
		$ftarr = array();

			

		foreach($qarr as $val)
		{

			$ftarr[$val['event']][$_GET['id']]['trigger'][] = $val['triggerid'];
			$ftarr[$val['event']][$_GET['id']][$val['triggerid']]['operator'] = $val['operator'];
			$ftarr[$val['event']][$_GET['id']][$val['triggerid']]['operand'] = $val['operand'];
			$ftarr[$val['event']][$_GET['id']][$val['triggerid']]['inputs'] = Pms_CommonData::array_stripslashes(unserialize($val['inputs']));
				
		}

		$grid = new Pms_Grid($f->toArray(),1,count($f->toArray()),"triggerlist.html");
		$grid->eventid = $_GET['event'];
		$grid->fieldid = $_GET['id'];
		$grid->ftarr = $ftarr;
		$grid->isform = $isform;
		$grid->operators = array(0=>'Select',1=>'Equal To',2=>'Not Equal To',3=>'Any Value','Blank Value');
		$this->view->triggerlist = $grid->renderGrid();

	}

	public function listfieldsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($_GET['did']>0)
		{
			$f = Doctrine_Query::create()
			->delete("FieldTrigger")
			->where("fieldid='".$_GET['did']."' and formid='".$_GET['frmid']."' and event='".$_GET['event']."' and clientid='".$logininfo->clientid."'");
			$f->execute();
			$this->_redirect(APP_BASE.'trigger/listfields?frmid='.$_GET['frmid']);
		}

		if($_GET['act']>0)
		{
			$f = Doctrine_Query::create()
			->select("*")
			->from("FieldTrigger")
			->where("fieldid='".$_GET['act']."' and formid='".$_GET['frmid']."' and event='".$_GET['event']."' and clientid='".$logininfo->clientid."'");
				
			$tr = $f->execute();

			if($tr)
			{
				$trigarr = $tr->toArray();
				if($trigarr[0]['isdelete']==1)
				{
					$setdelete = 0;
				}
				else
				{
					$setdelete = 1;
				}

				$up = Doctrine_Query::create()
				->update("FieldTrigger")
				->set("isdelete",$setdelete)
				->where("fieldid='".$_GET['act']."' and formid='".$_GET['frmid']."' and event='".$_GET['event']."' and clientid='".$logininfo->clientid."'");
					
				$tr = $up->execute();
				$this->_redirect(APP_BASE.'trigger/listfields');

			}
		}

		if($this->getRequest()->isPost() && strlen($_POST['submit'])>0)
		{

			$settrr = new Application_Form_AddFields();
			$settrr->InsertData($_POST);
			$this->view->message = "Field Added";
		}

		if($this->getRequest()->isPost() && strlen($_POST['submit1'])>0)
		{

			$settrr = new Application_Form_AddForm();
			if($settrr->validate($_POST))
			{
				$settrr->InsertData($_POST);
				$this->view->message = "Form Added";
			}else{
					
				$settrr->assignErrorMessages();
				$this->retainValues($_POST);

			}

		}
			
		$eventsarray = array(1=>'update',2=>'insert',3=>'fetch');
		 
		foreach($eventsarray as $id=>$event)
		{
			$q = Doctrine_Query::create()
			->select("f.*,t.*,tf.*")
			->from("FieldTrigger f")
			->innerjoin("f.TriggerTriggers t")
			->innerjoin("f.TriggerForms tf")
			->where("event='".$id."' and clientid='".$logininfo->clientid."'");

			$qr = $q->execute(array(),Doctrine_Core::HYDRATE_ARRAY);

			$ftarr[$id] = array();

			foreach($qr as $val)
			{
				$ftarr[$id][$val['formid']][$val['fieldid']]['triggers'][] = $val['TriggerTriggers']['triggername'];
				$ftarr[$id][$val['formid']][$val['fieldid']]['formname'] = $val['TriggerForms']['formname'];
					
			}


		}
			
			
		$q = Doctrine_Core::getTable('TriggerFields')->findAll();
		$qarr = $q->toArray();

			
			
		$finalqrr = array();
			
		foreach($qarr as $key=>$val)
		{
			if(is_array($ftarr[1][$val['formid']][$val['id']]['triggers']))
			{
				$val['triggers'] = join(" , ",$ftarr[1][$val['formid']][$val['id']]['triggers']);
				$val['eventname'] = 'Update';
				$val['formname'] = $ftarr[1][$val['formid']][$val['id']]['formname'];
				$val['event'] = 1;
				$finalqrr[] = $val;
			}
			if(is_array($ftarr[2][$val['formid']][$val['id']]['triggers']))
			{
				$val['triggers'] = join(" , ",$ftarr[2][$val['formid']][$val['id']]['triggers']);
				$val['eventname'] = 'Insert';
				$val['formname'] =$ftarr[2][$val['formid']][$val['id']]['formname'];
				$val['event'] = 2;
				$finalqrr[] = $val;
			}
			if(is_array($ftarr[3][$val['formid']][$val['id']]['triggers']))
			{
				$val['triggers'] = join(" , ",$ftarr[3][$val['formid']][$val['id']]['triggers']);
				$val['eventname'] = 'Fetch';
				$val['formname'] = $ftarr[3][$val['formid']][$val['id']]['formname'];
				$val['event'] = 3;
				$finalqrr[] = $val;
			}
				
		}
		$grid = new Pms_Grid($finalqrr,1,count($finalqrr),"fieldlist.html");
		$grid->ftarr = $ftarr;
		$this->view->fieldlist = $grid->renderGrid();
	}

	public function triggerinputsAction()
	{
		$trr = new Application_Triggers_SendMail();
		$this->view->triggerview =  $trr->createForm($_GET['tid']);
	}

	public function calltriggermethodAction()
	{
		$classname = "application_Triggers_".$_GET['trigger'];
		$method = $_GET['action'];

		$trr = new $classname;
		$trr->$method();
	}

	public function settriggerallAction()
	{
		if($this->getRequest()->isPost() && $_POST['submit1']=='Submit')
		{
			$settrr = new Application_Form_SetTriggersall();
			$settrr->InsertData($_POST);

			$this->_redirect(APP_BASE.'trigger/listfields?frmid='.$_GET['frmid']);
		}
		$f = Doctrine_Query::create()
		->select('*')
		->from("TriggerForms")
		->where("id='".$_GET['frmid']."'");
			
		$frr = $f->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
			
		$this->view->formname = $frr[0]['formname'];
		$f = Doctrine_Core::getTable('TriggerTriggers')->findAll();
		$f->toArray();
		 
		$eventsarray = array(1=>'update',2=>'insert',3=>'fetch');
		 
		foreach($eventsarray as $id=>$event)
		{
			$q = Doctrine_Query::create()
			->select("*")
			->from("FieldTrigger f")
			->where("event='".$id."' and formid='".$_GET['frmid']."' and fieldid=0");
			$qr = $q->execute();
			if($qr){
				$qarr = $qr->toArray();
			}
			$ftarr = array();

			foreach($qarr as $val)
			{
				$ftarr[$val['event']][$_GET['frmid']]['trigger'][] = $val['triggerid'];
				$ftarr[$val['event']][$_GET['frmid']][$val['triggerid']]['operator'] = $val['operator'];
				$ftarr[$val['event']][$_GET['frmid']][$val['triggerid']]['operand'] = $val['operand'];
				$ftarr[$val['event']][$_GET['frmid']][$val['triggerid']]['inputs'] = Pms_CommonData::array_stripslashes(unserialize($val['inputs']));
			}
			$grid = new Pms_Grid($f->toArray(),1,count($f->toArray()),"triggerlist.html");
			$grid->eventid = $id;
			$grid->fieldid = $_GET['frmid'];
			$grid->ftarr = $ftarr;
			$grid->isform = $isform;
			$grid->operators = array(0=>'Select',1=>'Equal To',2=>'Not Equal To',3=>'Any Value',4=>'Blank Value');
			$this->view->{"triggerlist".$id} = $grid->renderGrid();
		}
	}

	public function getfieldsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$q = Doctrine_Query::create()
		->select("*")
		->from('FieldTrigger')
		->where("formid='".$_GET['frmid']."' and event='".$_GET['event']."' and clientid=".$logininfo->clientid);
		$qe = $q->execute();

		$sep = ",";

		$fieldids = "0";

		foreach($qe->toArray() as $key=>$val)
		{
			$fieldids.=$sep.$val['fieldid'];
				
		}

		$q = Doctrine_Query::create()
		->select("*")
		->from("TriggerFields")
		->where("id not in (".$fieldids.") and formid='".$_GET['frmid']."'");
		$qe = $q->execute();

		$qearr[0] = "Select";
		foreach($qe->toArray() as $key=>$val)
		{
			$qearr[$val['id']]=$val['form_label'];
				
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "getFieldscallBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['fieldsdd'] = $this->view->formSelect('fieldid',NULL,array('id'=>'fieldid','onchange'=>'getTriggers()'),$qearr);
			
		echo json_encode($response);
		exit;
	}

	public function gettriggersAction()
	{
		$f = Doctrine_Query::create()
		->select('f.*,t.*')
		->from("TriggerFields f")
		->innerjoin("f.TriggerForms t")
		->where("f.id='".$_GET['fid']."'");
			
		$frr = $f->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
		$formname = $frr[0]['TriggerForms']['formname'];
		$fieldsarr = $this->getFieldNames($formname);
			
		$this->view->formname = $frr[0]['TriggerForms']['formname'];
		$this->view->fieldname  = $frr[0]['fieldname'];
		$this->view->form_label  = $frr[0]['form_label'];
		$isform  = $frr[0]['isform'];
			

		$f = Doctrine_Core::getTable('TriggerTriggers')->findAll();
		//$f->toArray();
		 
		$eventsarray = array(1=>'update',2=>'insert',3=>'fetch');
		 
		$q = Doctrine_Query::create()
		->select("*")
		->from("FieldTrigger f")
		->where("event='".$_GET['event']."' and fieldid='".$_GET['fid']."' and clientid='".$logininfo->clientid."'");
		$qr = $q->execute();
		if($qr){
			$qarr = $qr->toArray();
		}
		$ftarr = array();

			

		foreach($qarr as $val)
		{

			$ftarr[$val['event']][$_GET['fid']]['trigger'][] = $val['triggerid'];
			$ftarr[$val['event']][$_GET['fid']][$val['triggerid']]['operator'] = $val['operator'];
			$ftarr[$val['event']][$_GET['fid']][$val['triggerid']]['operand'] = $val['operand'];
			$ftarr[$val['event']][$_GET['fid']][$val['triggerid']]['inputs'] = Pms_CommonData::array_stripslashes(unserialize($val['inputs']));
				
		}



		$grid = new Pms_Grid($f->toArray(),1,count($f->toArray()),"triggerlist.html");
		$grid->eventid = $_GET['event'];
		$grid->fieldid = $_GET['fid'];
		$grid->isform = $isform;
		$grid->ftarr = $ftarr;
		$grid->operators = array(0=>'Select',1=>'Equal To',2=>'Not Equal To',3=>'Any Value','Blank Value');


		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "getTriggerscallBack";
		$response['callBackParameters'] = array();

		$response['callBackParameters']['triggers'] =  $grid->renderGrid();
		$response['callBackParameters']['fieldarray'] = $fieldsarr;
			
		echo json_encode($response);
		exit;

	}

	private function getFieldNames($frmname)
	{
		 
		if($frmname=="PatientContactPerson")
		{
			$placeholders = array("First Name"=>"#contactfirstname",
					"Last Name"=>"#contactlastname",
					"Address"=>"#contactaddress",
					"Phone"=>"#contactphone"
			);
			 
		}
			
		 
		if($frmname=="Patient")
		{
			$placeholders = array("First Name"=>"#patientfirstname",
					"Last Name"=>"#patientlastname",
					"Address"=>"#patientaddress",
					"Epid"=>"#epid",
					"Phone"=>"#patientphone",
					"Zip"=>"#patientzip",
					"City"=>"#patientcity",
					"Mobile"=>"#patientmobile",
					"Birth Date"=>"#patientbirthdate",
					"Gender"=>"#patientgender",
					"Admission Date"=>"#patientadmissiondate",
					"Doctor First Name"=>"#docfirstname",
					"Doctor Last Name"=>"#doclastname",
					"Olddocfirstname"=>"#olddocfirstname",
					"Olddoclasttname"=>"#olddoclastname",
			);
			 

		}
			
		if($frmname=="PatientDetails")
		{
			$placeholders = array("User First Name"=>"#userfirstname",
					"User Last Name"=>"#userlastname",
					"Createdate"=>"#createdate",
					"Course Title"=>"#title"
			);
		}
			
			
		if($frmname=="AssignusertoPatient")
		{
			$placeholders = array("User First Name"=>"#userfirstname",
					"User Last Name"=>"#userlastname",
					"First Name"=>"#patientfirstname",
					"Last Name"=>"#patientlastname",
					"Epid"=>"#epid"
						
						
			);
		}
			
		if($frmname=="PatientHealthInsurance")
		{
			$placeholders = array("Old Company Name"=>"#oldcompname",
					"New Company Name"=>"#newcompname",
					"Epid"=>"#epid"
			);
		}
		if($frmname=="PatientDischarge")
		{
			$placeholders = array("Discharge Date"=>"#dischargedate",
					"Discharge Method"=>"#dischargemethod",
					"Discharge Comment"=>"#dischargecomment",
					//ISPC-2645 Carmen 24.07.2020
					"Discharge Location" => "#dischargelocation",
					//--
					"Epid"=>"#epid"
			);
		}
		 
		if($frmname=="RezeptAnforderung")
		{
			$placeholders = array("First Name"=>"#patientfirstname",
					"Last Name"=>"#patientlastname",
					"Address"=>"#patientaddress",
					"Epid"=>"#epid",
					"Phone"=>"#patientphone",
					"Zip"=>"#patientzip",
					"City"=>"#patientcity",
					"Mobile"=>"#patientmobile",
					"Birth Date"=>"#patientbirthdate",
					"Gender"=>"#patientgender",
					"Admission Date"=>"#patientadmissiondate",
					
			);
		}
		
		
			
		$this->view->placeholders =  $placeholders;
			
		$arrs = "";
		$arrs.='<table width="100%" border="0" cellspacing="0" cellpadding="3">';
		 
		foreach($placeholders as $key=>$val)
		{
			$arrs.= '<tr>
			<td width="20%" align="right" class="textAlignRight"><strong>'.$key.'</strong></td>
			<td width="1%" align="left">:</td>
			<td width="79%" align="left">'.$val.'</td>
			</tr>
			<tr>
			<td align="right" class="textAlignRight">&nbsp;</td>
			<td align="left">&nbsp;</td>
			<td align="left">&nbsp;</td>
			</tr>';
		}
		$arrs.="</table'>";
		return $arrs;
	}



}


?>