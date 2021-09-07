<?php

class ReportsController extends Zend_Controller_Action
{
	public function init()
	{
		//setcookie("openmenu","admin_menu");
	}

	public	function exportlistAction()
	{
		set_time_limit(0);
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('reports',$logininfo->userid,'canview');

		if(!$return)
		{
			//$this->_redirect(APP_BASE."error/previlege");
		}

		$this->view->usertype = $logininfo->usertype;
		$error=0;

		$this->view->montharray = array('1'=>'1', '2'=>'2','3'=>'3', '4'=>'4','5'=>'5', '6'=>'6','7'=>'7', '8'=>'8','9'=>'9', '10'=>'10','11'=>'11', '12'=>'12');
		$this->view->listarray = array(""=>$this->view->translate('selectlist'),"1"=>$this->view->translate('patientlist'));
		$columnarray = array(
		"1"=>array(""=>"","powerdigit"=>$this->view->translate('powerdigit'),"count"=>$this->view->translate('count')),
		"2"=>array(""=>"","zip"=>$this->view->translate('zip'),"zip_nr"=>$this->view->translate('count')),
		"3"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"birthd"=>$this->view->translate("birthd"),"admission_date"=>$this->view->translate("admissiondate"),"dischargedate"=>$this->view->translate("dischargedate"),"treatedby"=>$this->view->translate("treatedby"),"familydoctor"=>$this->view->translate('familydoctor'),"familydoctorphone"=>$this->view->translate('familydoctorphone'),"dislocation"=>$this->view->translate('dielocation'),"daystreated"=>$this->view->translate("treateddays")),
		"4"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"birthd"=>$this->view->translate("birthd"),"admission_date"=>$this->view->translate("admissiondate"),"last_update"=>$this->view->translate("lastupdate"),"treatedby"=>$this->view->translate("treatedby"),"familydoctor"=>$this->view->translate('familydoctor'),"familydoctorphone"=>$this->view->translate('familydoctorphone'),"daystreated"=>$this->view->translate("treateddays")),
		"5"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"birthd"=>$this->view->translate("birthd"),"zip"=>$this->view->translate('zip'),"admission_date"=>$this->view->translate("admissiondate"),"last_update"=>$this->view->translate("lastupdate"),"treatedby"=>$this->view->translate("treatedby"),"familydoctor"=>$this->view->translate('familydoctor'),"familydoctorphone"=>$this->view->translate('familydoctorphone'),"daystreated"=>$this->view->translate("treateddays")),
		"6"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"birthd"=>$this->view->translate("birthd"),"admission_date"=>$this->view->translate("admissiondate"),"last_update"=>$this->view->translate("lastupdate"),"treatedby"=>$this->view->translate("treatedby"),"familydoctor"=>$this->view->translate('familydoctor'),"familydoctorphone"=>$this->view->translate('familydoctorphone')),
		"7"=>array(""=>"","icd_primary"=>$this->view->translate("icdprimary"),"description"=>$this->view->translate('description'),"count"=>$this->view->translate('count')),
		"20"=>array(""=>"","icd_primary"=>$this->view->translate("icdprimary"),"description"=>$this->view->translate('description'),"count"=>$this->view->translate('count')),
		"8"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"birthd"=>$this->view->translate("birthd"),"admission_date"=>$this->view->translate("admissiondate"),"last_update"=>$this->view->translate("lastupdate"),"treatedby"=>$this->view->translate("treatedby"),"familydoctor"=>$this->view->translate('familydoctor'),"familydoctorphone"=>$this->view->translate('familydoctorphone')),
		"9"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('lastname').",".$this->view->translate('firstname'),"phone"=>$this->view->translate('phone'),"familydoctor"=>$this->view->translate('familydoctor'),"diagnosis"=>$this->view->translate('diagnosis'),"treatedby"=>$this->view->translate('treatedby')),
		"10"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"birthd"=>$this->view->translate("birthd"),"admission_date"=>$this->view->translate("admissiondate"),"last_update"=>$this->view->translate("lastupdate"),"familydoctor"=>$this->view->translate('familydoctor'),"familydoctorphone"=>$this->view->translate('familydoctorphone')),
		"11"=>array(""=>"","dischargelocation"=>$this->view->translate('dischargelocation'),"count"=>$this->view->translate('count')),
		"13"=>array(""=>"","patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"epid_num"=>$this->view->translate('EPID'),"trateddays"=>$this->view->translate('treateddays'),"locationhospitalcount"=>"Anzahl KH Einweisungen","hopitaldaystreated"=>"KH Tage","dielocation"=>"Sterbeort","reason"=>"Grund der Aufnahme","hospdoc"=>"Einweisender Arzt ","transport"=>"Transportmittel","treatedby"=>$this->view->translate('treatedby')),
		"15"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"gender"=>$this->view->translate('gender'),"phone"=>$this->view->translate('phone'),"familydoctor"=>$this->view->translate('familydoctor'),"diagnosis"=>$this->view->translate('diagnosis'),"location"=>$this->view->translate('location'),"treatedby"=>$this->view->translate('treatedby')),
		"16"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"familydoctor"=>$this->view->translate('familydoctor'),"treatedby"=>$this->view->translate('treatedby')),
		"17"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"familydoctor"=>$this->view->translate('familydoctor'),"sapminuten"=>"Fahrtzeit in Minuten","sapkilometer"=>"gefahrene Kilometer","treatedby"=>$this->view->translate('treatedby'),"sahrten"=>"Fahrten" ),
		"18"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"familydoctor"=>$this->view->translate('familydoctor'),"sapvalue6"=>$this->view->translate('anzahltelefonateemails'),"treatedby"=>$this->view->translate('treatedby'),"zeit"=>"Zeit" ),
		"19"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname')),
		"21"=>array(""=>"","patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"epid_num"=>$this->view->translate('EPID'),"trateddays"=>$this->view->translate('treateddays'),"locationhospitalcount"=>"Anzahl KH Einweisungen","hopitaldaystreated"=>"KH Tage","dielocation"=>"Sterbeort","treatedby"=>$this->view->translate('treatedby'),"formonecount"=>"Notarzt"),
		"22"=>array(""=>"","referedby"=>$this->view->translate('referredby'),"count"=>$this->view->translate('count')),
		"23"=>array(""=>"","gender"=>$this->view->translate('gender'),"count"=>$this->view->translate('count')),
		"24"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"birthd"=>$this->view->translate("birthd"),"daystreated"=>$this->view->translate("treateddays"),"admission_date"=>$this->view->translate("admissiondate")),
		"25"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"diff_time"=>"Betreuungszeit"),
 		"26"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"admission_date"=>$this->view->translate("admissiondate"),"verordnun"=>"Verordnun"),
 		"27"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"lastname"=>$this->view->translate("lastname"),"firstname"=>$this->view->translate("firstname"),"daystreated"=>$this->view->translate("treateddays")),
		//		"28"=>array(""=>"","patientdata"=>$this->view->translate('lastname').", ".$this->view->translate('firstname'),"phone"=>$this->view->translate('phone'),"familydoctor"=>$this->view->translate('familydoctor'),"diagnosis"=>$this->view->translate('diagnosis'),"treatedby"=>$this->view->translate('treatedby')),
		"28"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('lastname').", ".$this->view->translate('firstname'),"phone"=>$this->view->translate('phone'),"familydoctor"=>$this->view->translate('familydoctor'),"diagnosis"=>$this->view->translate('diagnosis')),
		"29"=>array(""=>"","admisiondate"=>$this->view->translate("admissiondate"),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"allminutes"=>"Zeitaufwand "),
		"30"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"birthd"=>$this->view->translate("birthd"),"admission_date"=>$this->view->translate("admissiondate"),"address"=>$this->view->translate('address'),"treatedby"=>$this->view->translate('treatedby')),
		"31"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"admission_date"=>$this->view->translate("admissiondate"),"discharge_date"=>$this->view->translate('dischargedate'),"dischargelocation"=>$this->view->translate('dischargelocation'),"address"=>$this->view->translate('address'),"treatedby"=>$this->view->translate('treatedby')),
		"32"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"admission_date"=>$this->view->translate("admissiondate"),"familydoctor"=>$this->view->translate('familydoctor'),"familydoctorphone"=>$this->view->translate('familydoctorphone')),
		"33"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"pflegedienste"=>"Pflegedienst","pflegedienstephone"=>"Telefon Pflegedienst","familydoctor"=>$this->view->translate('familydoctor'),"familydoctorphone"=>$this->view->translate('familydoctorphone'),"admission_date"=>$this->view->translate("admissiondate")),
		"34"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"patientdata"=>$this->view->translate('firstname').",".$this->view->translate('lastname'),"admisiondate"=>$this->view->translate("admissiondate"))
		);

		$this->view->columnarray = json_encode($columnarray);


		for($i=2008;$i<=date('Y');$i++){
			$yeararray[$i] = $i;
		}

//		$this->view->yeararray = array(date("Y")-3=>date("Y")-3,date("Y")-2=>date("Y")-2,date("Y")-1=>date("Y")-1,date("Y")=>date("Y"));
		$this->view->yeararray = $yeararray;
		$this->view->year = array(date("Y")=>date("Y"));
		//$this->view->quarterarr = array(date("Y")-1=>date("Y")-1,date("Y")=>date("Y"));
		$this->view->radioarray = array('excel'=>$this->view->translate('excel'),'screen'=>$this->view->translate('screen'),'printing'=>$this->view->translate('printing'));
		$reporttypearray = array(
		""=>$this->view->translate('selectlist'),

		'1'=>$this->view->translate('powerdigit'),
		'2'=>$this->view->translate('zip'),
		'3'=>$this->view->translate('deceased'),
		'4'=>$this->view->translate('treatmentavgdays'),
		'5'=>$this->view->translate('treatedlongerthan3weeksandstillnotdischarged'),
		'6'=>$this->view->translate('dischargedpatientswherenotformanlage'),
		'7'=>$this->view->translate('mainstatisticofdiagnosis'),
		'20'=>$this->view->translate('sidestatisticofdiagnosis'),
		'8'=>$this->view->translate('agebetween'),
		'9'=>$this->view->translate('aktuellepatientenv1'),
		'15'=>$this->view->translate('aktuellepatientenv2'),
		'28'=>$this->view->translate('aktuelpatientandstanby'),
		'10'=>$this->view->translate('norentryincd'),
		'11'=>$this->view->translate('sterbeorte'),
		'12'=>$this->view->translate('statistics'),
		'13'=>$this->view->translate('anzahlkheinweisungen'),
		'14'=>$this->view->translate('cdspecialreport'),
		'16'=>$this->view->translate('anzahlbesuchepropatient'),
		'17'=>$this->view->translate('fahrtzeit&gefahrenekilometer'),
		'18'=>$this->view->translate('anzahltelefonateemails'),
		'19'=>$this->view->translate('verordnungenundartderbetreuung'),
		'21'=>$this->view->translate('krankenhausnotarzt'),
		'22'=>$this->view->translate('anfragendePerson'),
		'23'=>$this->view->translate('gender'),
		'24'=>$this->view->translate('patintgesamt'),
		'25'=>$this->view->translate('betreuungszeit'),
		'26'=>$this->view->translate('neupateint&vo'),
		'27'=>$this->view->translate('privatepatient'),
		//		'29'=>$this->view->translate('standbyzeitaufwand')
 		'30'=>$this->view->translate('Aufnahmen'),
 		'31'=>$this->view->translate('aufnahmeaktpatientverstorben'),
 		'32'=>$this->view->translate('bielefeldt1'),
 		'33'=>$this->view->translate('pflengediensthauzart'),
 		'34'=>$this->view->translate('versoroger'),


		);

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$cover = Doctrine::getTable('ReportPermission')->findBy('clientid',$clientid);
		$carray = $cover->toArray();

		$report_id = $carray[0]['report_id'];

		$permittedreportarr = array(""=>$this->view->translate('selectlist'));

		if($logininfo->usertype!='SA'){

			$permittedreport = explode(",",$report_id);

			foreach($reporttypearray as $key=>$val){

				if(in_array($key,$permittedreport)){

					$permittedreportarr[$key] = $val;

				}

			}

			$this->view->reporttypearray = $permittedreportarr;
		}else{

			$this->view->reporttypearray = $reporttypearray;
		}


		$this->view->radio = 'excel';
		$usrar = Doctrine_Query::create()
		->select('*')
		->from('User')
		->where('clientid ='.$logininfo->clientid.' and isdelete=0');
		$usrexec = $usrar->execute();
		$doctor_array = array("0"=>$this->view->translate('allebenutzer'));
		foreach($usrexec->toArray() as $key=>$val)
		{
			$doctor_array[$val['id']] = $val['last_name'].", ".$val['first_name'];
		}
		$this->view->doctorarray = $doctor_array;
		if($this->getRequest()->isPost())
		{

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('reports',$logininfo->userid,'canadd');

			if(!$return)
			{
				//$this->_redirect(APP_BASE."error/previlege");
			}

			if(strlen($logininfo->clientid)<1){$this->view->error_clientid=$this->view->translate("selectclient"); $error=1;}
			if(strlen($_POST['listname'])<1){$this->view->error_listname=$this->view->translate("selectlist");$error=2;}

			/*if(!is_numeric($_POST['fromtweeks'])){$this->view->error_tweeks=$this->view->translate("numericvalueonly"); $error=1;}
			 if(!is_numeric($_POST['tweeks'])){$this->view->error_tweeks=$this->view->translate("numericvalueonly"); $error=2;}*/



			if($_POST['only_now'] == '1') {
				$montharr = 'only_now'; //hack to remove time filter from reports, more to follow in getimeperiod() function
				$quarterarr = 'only_now';
				$yeararr = 'only_now';
			} else {
				$montharr=$_POST['month'];
				$quarterarr=$_POST['quarter'];
				$yeararr=$_POST['year'];
			}
			$radioarr=$_POST['radio'];
			//			$montharr=$_POST['month'];
			//				$quarterarr=$_POST['quarter'];
			//				$yeararr=$_POST['year'];

			$this->retainvalues($_POST);
			$this->view->died = $_POST['died'];
			$this->view->treatavg = $_POST['treatavg'];
			$this->view->statdiagnosis = $_POST['statdiagnosis'];
			$this->view->treatthreeweek = $_POST['treatthreeweek'];
			$this->view->zip = $_POST['reportzip'];

			if($_POST['reporttype']==9)
			{
				$this->akutellepatient($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==7)
			{
				$this->maindiagnosisstats($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==20)
			{
				$this->sidediagnosisstats($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==1)
			{
				$this->powerdigit($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==2)
			{
				$this->zipstats($radioarr,$quarterarr, $yeararr, $montharr);

			}elseif($_POST['reporttype']==11)
			{
				$this->dischargemethodDead($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==12)
			{
				$this->statstics($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==13)
			{
				$this->hospitalStats($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==14)
			{
				$this->cdspecialStats($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==15)
			{
				$this->akutellepatientv2($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==16)
			{
				$this->anzahlbesuchepropatient($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==17)
			{
				$this->fahrtzeitgefahrenekilometer($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==18)
			{
				$this->anzahltelefonateemails($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==19)
			{
				$this->verordnungenundartderbetreuung($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==21)
			{
			 $this->krankenhausnotarzt($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==22)
			{
			 $this->anfragendePerson($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==23)
			{
			 $this->geschlecht($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==24)
			{
			 $this->totalpatients($radioarr);

			}elseif($_POST['reporttype']==25)
			{
			 $this->betreuungszeit($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==26)
			{
			 $this->neuePatientenVO($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==27)
			{
			 $this->privatepatient($radioarr);
			}elseif($_POST['reporttype']==28)
			{
				$this->aktuelpatientandstanby($radioarr);

			}elseif($_POST['reporttype']==29)
			{
				//			 $this->allstanbypatients($radioarr,$montharr,$quarterarr,$yeararr);
			 $this->allstandbydeletedpatients($radioarr);

			}elseif($_POST['reporttype']==30)
			{
			 $this->admisionpatients($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==31)
			{
			 $this->admisiondischargepatients($radioarr,$montharr,$quarterarr,$yeararr);

			}elseif($_POST['reporttype']==32)
			{
			 $this->bielefeldt1($radioarr);

			}elseif($_POST['reporttype']==33)
			{
			 $this->reportaktualpatients($radioarr);

			}elseif($_POST['reporttype']==34)
			{
				$this->versorger($radioarr,$montharr,$quarterarr,$yeararr);

			}else{
				//				if($_POST['reporttype']!=3 || $_POST['reporttype']!=5)
				if($_POST['reporttype']!=5 && $_POST['reporttype']!=8 && $_POST['only_now'] != '1')
				{
					if(count($yeararr)<1){$this->view->error_year=$this->view->translate('selectyear'); $error=3;}
					if(count($montharr)<1 && count($quarterarr)<1){$montharr=array("0"=>"1","1"=>"2","2"=>"3","3"=>"4","4"=>"5","5"=>"6","6"=>"7","7"=>"8","8"=>"9","9"=>"10","10"=>"11","11"=>"12");}
					if(count($montharr)>0 && count($quarterarr)>0){$this->view->error_clientid=$this->view->translate("itmustbeeitherminselectquarteroramonth"); $error=4;}
					if(count($radioarr)<1){$this->view->error_radio=$this->view->translate("selectedition"); $error=5;}
				}
				$table="patient_master";
				$qrystr="";
				$startdate="";
				$enddate="";
				$where ="";
				$sep ="";


				//solo reports here

				if($error==0){

					$manager = Doctrine_Manager::getInstance();
					$this->_helper->viewRenderer->setNoRender();
					$this->_helper->layout->disableLayout();
					$where = "";

					//$where = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
					$where = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
					$where = substr(str_replace('%date%','admission_date',$where['date_sql']),4);

					$whereepid = $this->getDocCondition();

					if(strlen($where)>0)
					{
						$where = " and (".$where.")";
					}

					if($_POST['reporttype']==3){

						$ipidval = $this->dischargeMethod($whereepid,$montharr,$quarterarr,$yeararr);
						$where = " and isdelete=0 and isdischarged=1 and isstandbydelete = 0";

					}elseif($_POST['reporttype']==4){

					 	$ipidval = $this->allactivepatiens($quarterarr, $yeararr, $montharr);
//						$ipidval = $this->dischargePatient($whereepid,$montharr,$quarterarr,$yeararr);
						$where = " and isdelete=0 and isstandbydelete = 0";
					}elseif($_POST['reporttype']==5){

						$ipidval = $this->treatedThreeWeeks($whereepid);
						$where = " and isdelete=0 and isdischarged=0 and isstandbydelete = 0";

					}elseif($_POST['reporttype']==6){

						$ipidval = $this->patientNoForm();
						$where = " and isdelete=0  and isdischarged=1 and isstandbydelete = 0";
					}elseif($_POST['reporttype']==10){

					 $ipidval = $this->patientNoCDR();
					 $where = " and isdelete=0 and isstandbydelete = 0";

					}else{

						$ipidval = $this->allIpids($whereepid);
						$where .= " and isdelete=0 and isdischarged=0 and isstandbydelete = 0";
					}

					if(strlen($_POST['fromage'])>0 && strlen($_POST['toage'])>0)
					{
						$where .= " and year(CURRENT_DATE())-year(`birthd`) between ".$_POST['fromage']." and ".$_POST['toage'];
					}else if(strlen($_POST['fromage'])>0 && strlen($_POST['toage'])<1){
						$where .= " and year(CURRENT_DATE())-year(`birthd`)=".$_POST['fromage'];
					}else if(strlen($_POST['toage'])>0 && strlen($_POST['fromage'])<1)
					{
						$where .= " and year(CURRENT_DATE())-year(`birthd`)=".$_POST['toage'];
					}


					$queryrow = Doctrine_Query::create()
					->select("*, year(CURRENT_DATE())-year(`birthd`) AS age,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip")
					->from('PatientMaster')
					->where("ipid in (".$ipidval.") " .$where)
					->orderBy('admission_date DESC');
					//->andWhere($where);
					//$conn->prepare($qrystr);
//					echo $queryrow->getSqlQuery();


					$queryexec = $queryrow->execute();

					$row = $queryexec->toArray();
					$treatedby=array();
					$cnt=0;

					foreach($row as $key=>$val)
					{
						$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
						$epidipidarray = $epidipid->toArray();
						$gepid = $epidipidarray[0]['epid'];


						$familydoctor ="";
						$familydoctorphone = "";
						$fdocdetails = new Familydoctor();
						$fdocarray = $fdocdetails->getFamilyDoc($val['familydoc_id']);
						$familydoctor = $fdocarray[0]['last_name']." ".$fdocarray[0]['first_name'];

						$familydoctorphone = $fdocarray[0]['phone_practice'];


						if($_POST['reporttype']==3)
						{
							$dislocation = "";
							$dispat = Doctrine_Query::create()
							->select("*")
							->from("PatientDischarge")
							->where("ipid ='".$val['ipid']."'");
							$dispatexec = $dispat->execute();
							//echo $dispat->getSqlQuery();
							$disipidarray = $dispatexec->toArray();
							if(count($disipidarray)>0)
							{
								$dis = Doctrine_Query::create()
								->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
								->from('DischargeLocation')
								->where("clientid=".$logininfo->clientid." and id=".$disipidarray[0]['discharge_location']);

								$disexec = $dis->execute();
								$disarray = $disexec->toArray();

								$dislocation = $disarray[0]['location'];
							}
						}


						$expld =explode("-",$val["birthd"]);
						$bdate = date("Y")."-".$expld[1]."-".$expld[2];

						$patm = new PatientMaster();
						$calage = $patm->GetTreatedDays($val["birthd"],$bdate,1);


						if($val["birthd"]!='0000-00-00'){$birthd=date("d.m.Y",strtotime($val["birthd"]));}else{$birthd="-";}
						if($val["admission_date"]!='0000-00-00 00:00:00'){$admissiondate=date("d.m.Y H:i:s",strtotime($val["admission_date"]));}else{$admissiondate="-";}
						if($val["last_update"]!='0000-00-00 00:00:00'){$lastupdate=date("d.m.Y H:i:s",strtotime($val["last_update"]));}else{$lastupdate="-";}

						$allages += $calage['years'];
						if($_POST['reporttype']==3 || $_POST['reporttype']==4)
						{
							$dispat = Doctrine_Query::create()
							->select("*")
							->from("PatientDischarge")
							->where("ipid ='".$val['ipid']."'");
							$dispatexec = $dispat->execute();
							$disipidarray = $dispatexec->toArray();

							$split = explode(" ",$disipidarray[0]['discharge_date']);
							$bsplit = explode("-",$split[0]);
							$dischargedate = $bsplit[2].".".$bsplit[1].".".$bsplit[0];
							if($dischargedate=='00.00.0000'){$dischargedate="--";}
							$daystreated ="";
							$pms = new PatientMaster();

							if($admissiondate!="-" && $dischargedate!="--" && $dischargedate!="..")
							{
								$daystreated = $pms->getDaysDiff($admissiondate,$dischargedate);

								//								$admit = explode(" ",$admissiondate);
								//								$daystreated = ((int)((strtotime($dischargedate)-strtotime($admit[0]))/(24*60*60))+1);
							}elseif($admissiondate!="-"){
								//								$admit = explode(" ",$admissiondate);
								//								$daystreated = ((int)((strtotime(date("Y-m-d"))-strtotime($admit[0]))/(24*60*60))+1);

								$daystreated = $pms->getDaysDiff($admissiondate,date("Y-m-d H:i:s"));
								//$treatdays +=$daystreated;
							}
							$treatdays +=$daystreated;

						}

						if($_POST['reporttype']==5)
						{
							//							$pms = new PatientMaster();

							$daystreated ="";
							$admit = explode(" ",$admissiondate);
							$daystreated = ((int)((strtotime(date("Y-m-d"))-strtotime($admit[0]))/(24*60*60))+1);
							//							if($admissiondate!="-"){
							//							$daystreated = $pms->getDaysDiff($admissiondate,date("Y-m-d H:i:s"));
							//							} else {
							//							$daystreated ="";
							//							}
						}


						if(strlen($gepid)>0)
						{
							$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
							$treatarray = $treat->toArray();
							$user_id = $treatarray[0]['userid'];
							$uname = "";
							$br="";

							foreach($treatarray as $key=>$val)
							{
								$usr = Doctrine::getTable('User')->find($val['userid']);
								if($usr)
								{
									$userarray = $usr->toArray();
									$uname .= $br.$userarray['last_name']." ".$userarray['first_name'];
									$br = ";";
								}
							}

						}

						if($treatid!=$val['id'])
						{
							$row[$cnt]['treatedby'] = $uname;
							$row[$cnt]['familydoctor'] = $familydoctor;
							$row[$cnt]['familydoctorphone'] = $familydoctorphone;
							$row[$cnt]['dislocation'] = $dislocation;
							$row[$cnt]['dischargedate'] = $dischargedate;
							$row[$cnt]['daystreated'] = $daystreated;
							$row[$cnt]['treatdays'] = $treatdays;
							$row[$cnt]['epid_num'] = $gepid;
							$row[$cnt]['allages'] = $allages;
							$treatid=$val['id'];
						}
						$cnt++;


					}

					if($radioarr[0]=="excel"){

						$this->patientRelatedExcel($row);
					}else{

						$data = $this->patientRelatedScreen($row);

						if($radioarr[0]=="screen")
						{
							$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
							echo $data;
							exit;
							echo "<SCRIPT LANGUAGE='javascript'>";
							echo "newwindow=window.open(location.href,'reportlist');";
							echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
							echo "</SCRIPT>";

						}
						elseif($radioarr[0]=="printing")
						{
							$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
							echo $data;
							echo "<SCRIPT LANGUAGE='javascript'>";
							//echo "newwindow=window.open(location.href,'reportlist');";
							echo "window.print();";
							echo "</SCRIPT>";
							exit;
						}

					}


				}

			}

		}
	}

	private function akutellepatient($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		//$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
		//$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);

		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone")
		->from('PatientMaster p')
		->where('isdelete = 0 and isdischarged = 0 and isstandbydelete = 0')
		//->andWhere('isdischarged = 0')
		//->andWhere('isdischarged = 0 and '.$admtwhere)
		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();



		$dg = new DiagnosisType();
		$abb2 = "'HD','ND'";
		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);
		$comma=",";
		$typeid ="'0'";
		foreach($ddarr2 as $key=>$valdia)
		{
			$typeid .=$comma."'".$valdia['id']."'";
			$comma=", ";
		}




		foreach($ipidarray as $key=>$val)
		{
			unset($diagnosis,$metadiagnosis,$Familydoctor);

			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$familidoc = $famdoc->getFamilyDoc($val['familydoc_id']);
			if(count($familidoc)>0)
			{
				if(strlen($familidoc[0]['title'])>0)
				{
					$Familydoctor = $familidoc[0]['title'].", ";
				}
				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
				{
					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
				}

				if(strlen($familidoc[0]['phone_practice'])>0)
				{
					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
				}

				if(strlen($familidoc[0]['street1'])>0)
				{
					$Familydoctor .= $familidoc[0]['street1'].", ";
				}
				if(strlen($familidoc[0]['street2'])>0)
				{
					$Familydoctor .= $familidoc[0]['street2'].", ";
				}
				if(strlen($familidoc[0]['postcode'])>0)
				{
					$Familydoctor .= $familidoc[0]['postcode'].", ";
				}
				if(strlen($familidoc[0]['city'])>0)
				{
					$Familydoctor .= $familidoc[0]['city'];
				}
			}


			$dropSapv = Doctrine_Query::create()
			->select('*, GROUP_CONCAT(verordnet) as vero')
			->from('SapvVerordnung')
			->where('ipid ="'.$val['ipid'].'"   and isdelete=0 and status != 0  and status != 1 ')
			->groupBy('ipid');
			//			echo $dropSapv->getSqlQuery().'<br /><br />';
			//			exit;
			$droparray = $dropSapv->fetchArray();
			$valarr[] = $val['ipid'];

			//echo $droparray[0]['vero'].'<br /><br />';

			$sapv = explode(',', $droparray[0]['vero']);
			$s = max($sapv);
			if ($s == "1"){
				$sapv_value = "BE";
			}elseif ($s == "2") {
				$sapv_value = "KO";
			}elseif ($s == "3") {
				$sapv_value = "TV";
			}elseif ($s == "4"){
				$sapv_value = "VO";
			} else {
				$sapv_value = "-";
			}




			$patdia = new PatientDiagnosis();
			$dianoarray = $patdia->getFinalData($val['ipid'],$typeid);

			$patientmeta = new PatientDiagnosisMeta();
			$metaids =$patientmeta->getPatientDiagnosismeta($val['ipid']);

			if(count($metaids)>0)
			{
				$diagnosismeta = new DiagnosisMeta();
				$comma="";
				$metadiagnosis ="";
				foreach($metaids as $keymeta=>$valmeta)
				{
					$metadiagnosis ="";
					$metaarray = $diagnosismeta->getDiagnosisMetaDataById($valmeta['metaid']);
					if(count($metaarray)>0)
					{
						foreach($metaarray as $keytit=>$metatitle)
						{
							$metadiagnosis .= $comma.$metatitle['meta_title'];
							$comma = ", ";
						}
					}
				}
			}
			if(count($dianoarray)>0)
			{
				$comma = "";
				$diagnosis="";
				foreach($dianoarray as $key=>$valdia)
				{
					if(strlen($valdia['diagnosis'])>0)
					{
						$diagnosis .= $comma.$valdia['diagnosis'];
						$comma=", ";
					}
				}

			}
			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtrate)
				{

					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}
			$birthdatep="-";

			if($val['birthd']!="0000-00-00"){
				$date = new Zend_Date($val['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
			}

			$patinfo = "";
			$patientphone = "";

			if(strlen($val["last_name"])>0)
			{
				$patinfo .= $val["last_name"]." ";
			}
			if(strlen($val['first_name'])>0)
			{
				$patinfo .= $val['first_name'].", ";
			}
			if(strlen($birthdatep)>0)
			{
				$patinfo .=$birthdatep.", ";
			}
			if(strlen($val['street1'])>0)
			{
				$patinfo .= $val['street1'].", ";
			}
			if(strlen($val['zip'])>0)
			{
				$patinfo .=$val['zip'].", ";
			}
			if(strlen($val['city'])>0){
				$patinfo .=$val['city'];
			}

			$patientphone = trim($val['phone']);
			$statdia_array = array();
			//			 $statdia_array['vo_value'] = $s;
			$statdia_array['vo_value'] = $sapv_value;
			$statdia_array['patientdata'] = ltrim($patinfo);
			$statdia_array['phone'] = ltrim($patientphone);
			$statdia_array['familydoctor'] = ltrim($Familydoctor);
			$statdia_array['epid_num'] =  $gepid ;
			if(strlen($metadiagnosis)>0)
			{
				$statdia_array['diagnosis'] = ltrim($diagnosis.",".$metadiagnosis);
			}else{
				$statdia_array['diagnosis'] = ltrim($diagnosis);
			}
			$statdia_array['treatedby'] = ltrim($treatedby);
			$sortarray1[] = $statdia_array;


		}

		//echo implode('","',$valarr);

		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('epid'));
			$this->xlsWriteLabel(0,2,$this->view->translate('lastname').','.$this->view->translate('firstname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('phone'));
			$this->xlsWriteLabel(0,4,$this->view->translate('familydoctor'));
			$this->xlsWriteLabel(0,5,"VO");
			$this->xlsWriteLabel(0,6,$this->view->translate('diagnosis'));
			$this->xlsWriteLabel(0,7,$this->view->translate('symptom'));
			$this->xlsWriteLabel(0,8,$this->view->translate('pflege'));
			$this->xlsWriteLabel(0,9,$this->view->translate('treatedby'));

			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				/*if(strlen($val['patientdata'])<1)
				 {
				 continue;
				 }*/
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['phone']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['familydoctor']));
				$this->xlsWriteLabel($xlsRow,5,utf8_decode($valfile['vo_value']));
				//				$this->xlsWriteLabel($xlsRow,4,"");
				$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['diagnosis']));
				$this->xlsWriteLabel($xlsRow,7,"");
				$this->xlsWriteLabel($xlsRow,8,"");
				$this->xlsWriteLabel($xlsRow,9,utf8_decode($valfile['treatedby']));
				$xlsRow++;
			}

			$this->xlsEOF();

			$fileName = "Aktuelle_patienten.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th>'.$this->view->translate('epid').'</td><th width="10%">'.$this->view->translate('lastname').','.$this->view->translate('firstname').'&nbsp;</th><th width="15%">'.$this->view->translate('phone').'</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="15%">VO</th><th width="15%">'.$this->view->translate('Diagnose').'</th><th width="15%">'.$this->view->translate('Symptom').'</th><th width="15%">'.$this->view->translate('Pflege').'</th><th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td>
				<td  valign="top">'.$valfile['epid_num'].'&nbsp;</td>
				<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
				<td valign="top">'.$valfile['phone'].'&nbsp;</td>
				<td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td>
				<td  valign="top">'.$valfile['vo_value'].'&nbsp;</td>
				<td valign="top">'.$valfile['diagnosis'].'&nbsp;</td><td valign="top">&nbsp; </td><td valign="top">&nbsp; </td><td valign="top">'.$valfile['treatedby'].'&nbsp; </td></tr>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}

	private function akutellepatientv2($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		//$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
		//$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);

		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdelete = 0 and isdischarged = 0 and isstandbydelete = 0')
		//->andWhere('isdischarged = 0')
		//->andWhere('isdischarged = 0 and '.$admtwhere)
		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");


		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();


		$dg = new DiagnosisType();
		$abb2 = "'HD','ND'";
		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);
		$comma=",";
		$typeid ="'0'";
		foreach($ddarr2 as $key=>$valdia)
		{
			$typeid .=$comma."'".$valdia['id']."'";
			$comma=", ";
		}

		foreach($ipidarray as $key=>$val)
		{
			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);

			$patlocation = new PatientLocation();
			$ploc = $patlocation->getpatientLocation($val['ipid']);

			$location = new Locations();
			$locaray = $location->getLocationbyId($ploc[0]['location_id']);

			$actual_location = $locaray[0]['location'];

			$genderarray = Pms_CommonData::getGender();
			if($val['sex']>0)
			{
				$gender = $genderarray[$val['sex']];
			}

			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$familidoc = $famdoc->getFamilyDoc($val['familydoc_id']);
			if(count($familidoc)>0)
			{
				if(strlen($familidoc[0]['title'])>0)
				{
					$Familydoctor = $familidoc[0]['title'].", ";
				}
				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
				{
					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
				}

				if(strlen($familidoc[0]['phone_practice'])>0)
				{
					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
				}

				if(strlen($familidoc[0]['street1'])>0)
				{
					$Familydoctor .= $familidoc[0]['street1'].", ";
				}
				if(strlen($familidoc[0]['street2'])>0)
				{
					$Familydoctor .= $familidoc[0]['street2'].", ";
				}
				if(strlen($familidoc[0]['postcode'])>0)
				{
					$Familydoctor .= $familidoc[0]['postcode'].", ";
				}
				if(strlen($familidoc[0]['city'])>0)
				{
					$Familydoctor .= $familidoc[0]['city'];
				}
			}

			$patdia = new PatientDiagnosis();
			$dianoarray = $patdia->getFinalData($val['ipid'],$typeid);

			$patientmeta = new PatientDiagnosisMeta();
			$metaids =$patientmeta->getPatientDiagnosismeta($val['ipid']);

			if(count($metaids)>0)
			{
				$diagnosismeta = new DiagnosisMeta();
				$comma="";
				$metadiagnosis ="";
				foreach($metaids as $keymeta=>$valmeta)
				{
					$metadiagnosis ="";
					$metaarray = $diagnosismeta->getDiagnosisMetaDataById($valmeta['metaid']);
					if(count($metaarray)>0)
					{
						foreach($metaarray as $keytit=>$metatitle)
						{
							$metadiagnosis .= $comma.$metatitle['meta_title'];
							$comma = ", ";
						}
					}
				}
			}
			if(count($dianoarray)>0)
			{
				$comma = "";
				$diagnosis="";
				foreach($dianoarray as $key=>$valdia)
				{
					if(strlen($valdia['diagnosis'])>0)
					{
						$diagnosis .= $comma.$valdia['diagnosis'];
						$comma=", ";
					}
				}

			}
			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtrate)
				{

					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}
			$birthdatep="-";
			if($val['birthd']!="0000-00-00"){
				$date = new Zend_Date($val['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
			}

			$patinfo = "";
			$patientphone = "";

			if(strlen($val["last_name"])>0)
			{
				$patinfo .= $val["last_name"]." ";
			}
			if(strlen($val['first_name'])>0)
			{
				$patinfo .= $val['first_name'].", ";
			}
			if(strlen($birthdatep)>0)
			{
				$patinfo .=$birthdatep.", ";
			}
			if(strlen($val['street1'])>0)
			{
				$patinfo .= $val['street1'].", ";
			}
			if(strlen($val['zip'])>0)
			{
				$patinfo .=$val['zip'].", ";
			}
			if(strlen($val['city'])>0){
				$patinfo .=$val['city'];
			}

			$patientphone = trim($val['phone']);
			$statdia_array = array();

			$statdia_array['epid_num'] = $gepid;
			$statdia_array['patientdata'] = ltrim($patinfo);
			$statdia_array['phone'] = ltrim($patientphone);
			$statdia_array['familydoctor'] = ltrim($Familydoctor);
			if(strlen($metadiagnosis)>0)
			{
				$statdia_array['diagnosis'] = ltrim($diagnosis.",".$metadiagnosis);
			}else{
				$statdia_array['diagnosis'] = ltrim($diagnosis);
			}
			$statdia_array['location'] = ltrim($actual_location);
			$statdia_array['gender'] = ltrim($gender);

			$statdia_array['treatedby'] = ltrim($treatedby);
			$sortarray1[] = $statdia_array;


		}
		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('epid_num'));
			$this->xlsWriteLabel(0,2,$this->view->translate('lastname').", ".$this->view->translate('firstname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('phone'));
			//$this->xlsWriteLabel(0,2,$this->view->translate('gender'));
			$this->xlsWriteLabel(0,4,$this->view->translate('familydoctor'));
			$this->xlsWriteLabel(0,5,$this->view->translate('diagnosis'));
			$this->xlsWriteLabel(0,6,$this->view->translate('location'));
			$this->xlsWriteLabel(0,7,$this->view->translate('treatedby'));

			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				/*if(strlen($val['patientdata'])<1)
				 {
				 continue;
				 }*/
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['phone']));
				//$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['gender']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['familydoctor']));
				$this->xlsWriteLabel($xlsRow,5,utf8_decode($valfile['diagnosis']));
				$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['location']));
				$this->xlsWriteLabel($xlsRow,7,utf8_decode($valfile['treatedby']));
				$xlsRow++;
			}

			$this->xlsEOF();

			$fileName = "Aktuelle_patienten.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('EPID').'</th><th width="10%">'.$this->view->translate('lastname').','.$this->view->translate('firstname').'&nbsp;</th><th width="15%">'.$this->view->translate('phone').'</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="15%">'.$this->view->translate('Diagnose').'</th><th width="15%">Aufenthaltsort</th><th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['epid_num'].'&nbsp;</td><td  valign="top">'.$valfile['patientdata'].'&nbsp;</td><td valign="top">'.$valfile['phone'].'&nbsp;</td><td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td><td valign="top">'.$valfile['diagnosis'].'&nbsp;</td><td valign="top">&nbsp;'.$valfile['location'].'</td><td valign="top">'.$valfile['treatedby'].'&nbsp; </td></tr>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}


	private function aktuelpatientandstanby($radioarr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();

		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone")
		->from('PatientMaster p')
		->where('isdelete = 0 and isdischarged = 0 and isstandby = 0 and isstandbydelete = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();



		$dg = new DiagnosisType();
		$abb2 = "'HD','ND'";
		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);
		$comma=",";
		$typeid ="'0'";
		foreach($ddarr2 as $key=>$valdia)
		{
			$typeid .=$comma."'".$valdia['id']."'";
			$comma=", ";
		}
		foreach($ipidarray as $key=>$val)
		{
			unset($diagnosis,$metadiagnosis,$Familydoctor);

			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$familidoc = $famdoc->getFamilyDoc($val['familydoc_id']);
			if(count($familidoc)>0)
			{
				if(strlen($familidoc[0]['title'])>0)
				{
					$Familydoctor = $familidoc[0]['title'].", ";
				}
				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
				{
					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
				}

				if(strlen($familidoc[0]['phone_practice'])>0)
				{
					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
				}

				if(strlen($familidoc[0]['street1'])>0)
				{
					$Familydoctor .= $familidoc[0]['street1'].", ";
				}
				if(strlen($familidoc[0]['street2'])>0)
				{
					$Familydoctor .= $familidoc[0]['street2'].", ";
				}
				if(strlen($familidoc[0]['postcode'])>0)
				{
					$Familydoctor .= $familidoc[0]['postcode'].", ";
				}
				if(strlen($familidoc[0]['city'])>0)
				{
					$Familydoctor .= $familidoc[0]['city'];
				}
			}


			$dropSapv = Doctrine_Query::create()
			->select('*, GROUP_CONCAT(verordnet) as vero')
			->from('SapvVerordnung')
			->where('ipid ="'.$val['ipid'].'"   and isdelete=0 and status != 0  and status != 1 ')
			->groupBy('ipid');
			//			echo $dropSapv->getSqlQuery().'<br /><br />';
			//			exit;
			$droparray = $dropSapv->fetchArray();
			$valarr[] = $val['ipid'];

			//echo $droparray[0]['vero'].'<br /><br />';

			$sapv = explode(',', $droparray[0]['vero']);
			$s = max($sapv);
			if ($s == "1"){
				$sapv_value = "BE";
			}elseif ($s == "2") {
				$sapv_value = "KO";
			}elseif ($s == "3") {
				$sapv_value = "TV";
			}elseif ($s == "4"){
				$sapv_value = "VO";
			} else {
				$sapv_value = "-";
			}


			$patdia = new PatientDiagnosis();
			$dianoarray = $patdia->getFinalData($val['ipid'],$typeid);

			$patientmeta = new PatientDiagnosisMeta();
			$metaids =$patientmeta->getPatientDiagnosismeta($val['ipid']);

			if(count($metaids)>0)
			{
				$diagnosismeta = new DiagnosisMeta();
				$comma="";
				$metadiagnosis ="";
				foreach($metaids as $keymeta=>$valmeta)
				{
					$metadiagnosis ="";
					$metaarray = $diagnosismeta->getDiagnosisMetaDataById($valmeta['metaid']);
					if(count($metaarray)>0)
					{
						foreach($metaarray as $keytit=>$metatitle)
						{
							$metadiagnosis .= $comma.$metatitle['meta_title'];
							$comma = ", ";
						}
					}
				}
			}
			if(count($dianoarray)>0)
			{
				$comma = "";
				$diagnosis="";
				foreach($dianoarray as $key=>$valdia)
				{
					if(strlen($valdia['diagnosis'])>0)
					{
						$diagnosis .= $comma.$valdia['diagnosis'];
						$comma=", ";
					}
				}

			}
			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtrate)
				{

					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}
			$birthdatep="-";

			if($val['birthd']!="0000-00-00"){
				$date = new Zend_Date($val['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
			}

			$patinfo = "";
			$patientphone = "";

			if(strlen($val["last_name"])>0)
			{
				$patinfo .= $val["last_name"].", ";
			}
			if(strlen($val['first_name'])>0)
			{
				$patinfo .= $val['first_name']." ";
			}
			if(strlen($birthdatep)>0)
			{
				$patinfo .=$birthdatep.", ";
			}
			if(strlen($val['street1'])>0)
			{
				$patinfo .= $val['street1'].", ";
			}
			if(strlen($val['zip'])>0)
			{
				$patinfo .=$val['zip'].", ";
			}
			if(strlen($val['city'])>0){
				$patinfo .=$val['city'];
			}

			$patientphone = trim($val['phone']);
			$statdia_array = array();
			//			 $statdia_array['vo_value'] = $s;
			$statdia_array['vo_value'] = $sapv_value;
			$statdia_array['epid_num'] = $gepid;
			$statdia_array['patientdata'] = ltrim($patinfo);
			$statdia_array['phone'] = ltrim($patientphone);
			$statdia_array['familydoctor'] = ltrim($Familydoctor);
			if(strlen($metadiagnosis)>0)
			{
				$statdia_array['diagnosis'] = ltrim($diagnosis.",".$metadiagnosis);
			}else{
				$statdia_array['diagnosis'] = ltrim($diagnosis);
			}
			//$statdia_array['treatedby'] = ltrim($treatedby);
			$sortarray1[] = $statdia_array;


		}
		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('lastname').", ".$this->view->translate('firstname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('phone'));
			$this->xlsWriteLabel(0,4,$this->view->translate('familydoctor'));
			$this->xlsWriteLabel(0,5,"VO");
			$this->xlsWriteLabel(0,6,$this->view->translate('diagnosis'));
			$this->xlsWriteLabel(0,7,$this->view->translate('symptom').'/'.$this->view->translate('pflege'));
			//$this->xlsWriteLabel(0,7,$this->view->translate('pflege'));
			//$this->xlsWriteLabel(0,8,$this->view->translate('treatedby'));

			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				/*if(strlen($val['patientdata'])<1)
				 {
				 continue;
				 }*/
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['phone']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['familydoctor']));
				$this->xlsWriteLabel($xlsRow,5,utf8_decode($valfile['vo_value']));
				//				$this->xlsWriteLabel($xlsRow,4,"");
				$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['diagnosis']));
				$this->xlsWriteLabel($xlsRow,7,"");
				//$this->xlsWriteLabel($xlsRow,7,"");
				//$this->xlsWriteLabel($xlsRow,8,utf8_decode($valfile['treatedby']));
				$xlsRow++;
			}

			$this->xlsEOF();

			$fileName = "Aktuelle_patienten.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
			<tr>
			<th style="width: 1200px;">'.$this->view->translate('no').'</th>
			<th>'.$this->view->translate('EPID').'</th>
			<th style="width: 220px;">'.$this->view->translate('lastname').','.$this->view->translate('firstname').'&nbsp;</th>
			<th>'.$this->view->translate('phone').'</th>
			<th>'.$this->view->translate('familydoctor').'</th>
			<th>VO</th>
			<th width="150">'.$this->view->translate('Diagnose').'</th>
			<th width="300">'.$this->view->translate('Symptom').'/'.$this->view->translate('Pflege').'</th>
			</tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td>
				<td valign="top">'.$valfile['epid_num'].'&nbsp;</td>
				<td style="width: 150px;"valign="top">'.$valfile['patientdata'].'&nbsp;</td>
				<td valign="top">'.$valfile['phone'].'&nbsp;</td>
				<td valign="top">'.$valfile['familydoctor'].'&nbsp;</td>
				<td valign="top">'.$valfile['vo_value'].'&nbsp;</td>
				<td width="150" valign="top">'.$valfile['diagnosis'].'&nbsp;</td>
				<td width="300" valign="top">&nbsp; </td>
				</tr>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<html>
				<head>
				<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />
				<title></title>
				</head>
				<body>'.$data.'</body></html>';
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}


	private function allstandbydeletedpatients($radioarr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();

		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone")
		->from('PatientMaster p')
		->where('isstandby = 1 ')
		//		->where('isstandby = 1 and isdischarged = 0')
		->andWhere('isdelete = 1 OR isdelete= 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//		echo $patient->getSqlQuery();

		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();




		foreach($ipidarray as $key=>$val){


			$patinfo = "";
			if(strlen($val["first_name"])>0)
			{
				$patinfo .= $val["first_name"]." ";
			}
			if(strlen($val['last_name'])>0)
			{
				$patinfo .= $val['last_name'].", ";
			}

			$admisiondate =  date("Y.m.d H:i:s",strtotime($val['admission_date']));
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val['ipid']);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];


			//			$admisiondate = $val['admission_date'];
			$statdia_array = array();
			$statdia_array['patientdata'] = ltrim($patinfo);
			$statdia_array['epid_num'] = $gepid;
			$statdia_array['admission_date'] =  $admisiondate ;
			$sortarray1[] = $statdia_array;


		}
		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('admisiondate'));
			$this->xlsWriteLabel(0,3,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,4,$this->view->translate('zeit'));

			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				/*if(strlen($val['patientdata'])<1)
				 {
				 continue;
				 }*/
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['admission_date']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['zeit']));
				$xlsRow++;
			}

			$this->xlsEOF();

			$fileName = "Aktuelle_patienten.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
			<tr>
			<th width="10%">'.$this->view->translate('no').'</th>
			<th width="15%">'.$this->view->translate('EPID').'</th>
			<th width="15%">'.$this->view->translate('admisiondate').'</th>
			<th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th>
			<th width="15%">'.$this->view->translate('zeit').'</th>
			<tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td>
				<td  valign="top">'.$valfile['epid_num'].'&nbsp;</td>
				<td  valign="top">'.$valfile['admission_date'].'&nbsp;</td>
				<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
				<td valign="top">'.$valfile['zeit'].'</td>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}


	private function geschlecht($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();




		$finalipidval =$this->allactivepatiens($quarterarr, $yeararr, $montharr);

		unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);

		$genderarray = Pms_CommonData::getGender();

		foreach($genderarray as $keygen=>$valgen)
		{

			if(strlen($keygen)>0)
			{

				$patient = Doctrine_Query::create()
				->select("count(*)")
				->from('PatientMaster p')
				->where("ipid in (".$finalipidval.") and isdelete = 0  and isstandby=0 and isstandbydelete = 0 and convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1)=".$keygen);
				$patient->leftJoin("p.EpidIpidMapping e");
				$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
				//echo $patient->getSqlQuery();
				$patientexec = $patient->execute();
				$ipidarray = $patientexec->toArray();


				$statdia_array = array();
				$statdia_array['gender'] = $valgen;
				$statdia_array['count'] = $ipidarray[0]['count'];
				$totalcount+=$ipidarray[0]['count'];
				$sortarray1[] = $statdia_array;
			}
		}



		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('gender'));
			$this->xlsWriteLabel(0,2,$this->view->translate('count'));
			$this->xlsWriteLabel(0,3,$this->view->translate('percentage'));

			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['gender']));
				$this->xlsWriteNumber($xlsRow,2,utf8_decode($valfile['count']));
				$this->xlsWriteLabel($xlsRow,3,number_format(($valfile['count']/$totalcount)*100,2,".","")."%");

				$xlsRow++;
			}
			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum'));
			$this->xlsWriteNumber($xlsRow,2,$totalcount);

			$this->xlsEOF();

			$fileName = "Geschlecht.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('gender').'&nbsp;</th><th width="15%">'.$this->view->translate('count').'</th><th width="15%">'.$this->view->translate('percentage').'</th><tr>';
			$rowcount=1;

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['gender'].'&nbsp;</td><td  valign="top">'.$valfile['count'].'&nbsp;</td><td  valign="top">'.number_format(($valfile['count']/$totalcount)*100,2,".","").' %&nbsp;</td></tr>';
				$rowcount++;
				//number_format(($valfile['count']/$totalcount)*100,2,".","")


			}

			$data.="<tr><td>Summe</td><td>&nbsp;</td><td>".$totalcount."&nbsp;</td><td>&nbsp;</td></tr>";
			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}
	//
	//	private function geschlecht($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$finalipidval = array();
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval[]=$val['ipid'];
	//		}
	//		$activeipid="'0'";
	//		$comma=",";
	//		foreach($finalipidval as $keyip=>$valip)
	//		{
	//			$activeipid.=$comma."'".$valip."'";
	//			$comma=",";
	//		}
	//
	//		unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);
	//
	//		$genderarray = Pms_CommonData::getGender();
	//
	//		foreach($genderarray as $keygen=>$valgen)
	//		{
	//
	//			if(strlen($keygen)>0)
	//			{
	//
	//				$patient = Doctrine_Query::create()
	//				->select("count(*)")
	//				->from('PatientMaster p')
	//				->where("ipid in (".$activeipid.") and isdelete = 0  and isstandby=0 and convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1)=".$keygen);
	//				//					  ->andWhere("isdischarged = 0 and convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1)=".$keygen);
	//				//->andWhere("isdischarged = 0 and convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1)=".$keygenand." and ".$admtwhere)
	//				// ->andWhere('isstandby = 0');
	//				$patient->leftJoin("p.EpidIpidMapping e");
	//				$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//				//echo $patient->getSqlQuery();
	//				$patientexec = $patient->execute();
	//				$ipidarray = $patientexec->toArray();
	//
	//
	//				$statdia_array = array();
	//				$statdia_array['gender'] = $valgen;
	//				$statdia_array['count'] = $ipidarray[0]['count'];
	//				$totalcount+=$ipidarray[0]['count'];
	//				$sortarray1[] = $statdia_array;
	//			}
	//		}
	//
	//
	//
	//		$xlsRow = 1;
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('gender'));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('count'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('percentage'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$i++;
	//				$this->xlsWriteNumber($xlsRow,0,"$i");
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['gender']));
	//				$this->xlsWriteNumber($xlsRow,2,utf8_decode($valfile['count']));
	//				$this->xlsWriteLabel($xlsRow,3,number_format(($valfile['count']/$totalcount)*100,2,".","")."%");
	//
	//				$xlsRow++;
	//			}
	//			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum'));
	//			$this->xlsWriteNumber($xlsRow,2,$totalcount);
	//
	//			$this->xlsEOF();
	//
	//			$fileName = "Geschlecht.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			//ob_clean();
	//			//flush();
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//		}else{
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('gender').'&nbsp;</th><th width="15%">'.$this->view->translate('count').'</th><th width="15%">'.$this->view->translate('percentage').'</th><tr>';
	//			$rowcount=1;
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['gender'].'&nbsp;</td><td  valign="top">'.$valfile['count'].'&nbsp;</td><td  valign="top">'.number_format(($valfile['count']/$totalcount)*100,2,".","").' %&nbsp;</td></tr>';
	//				$rowcount++;
	//				//number_format(($valfile['count']/$totalcount)*100,2,".","")
	//
	//
	//			}
	//
	//			$data.="<tr><td>Summe</td><td>&nbsp;</td><td>".$totalcount."&nbsp;</td><td>&nbsp;</td></tr>";
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//				echo "<SCRIPT type='text/javascript'>";
	//				//	echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//
	//			}
	//		}
	//	}


	private function totalpatients($radioarr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();

		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdelete= "0" and isstandbydelete = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$activearray = $actpatient->fetchArray();

		foreach($activearray as $key=>$val)
		{
			$dispat = Doctrine_Query::create()
			->select("*")
			->from("PatientDischarge")
			->where("ipid ='".$val['ipid']."'");
			$dispatexec = $dispat->execute();
			$disipidarray = $dispatexec->toArray();

			$split = explode(" ",$disipidarray[0]['discharge_date']);
			$bsplit = explode("-",$split[0]);
			$dischargedate = $bsplit[2].".".$bsplit[1].".".$bsplit[0];

			if($val["admission_date"]!='0000-00-00 00:00:00'){$admissiondate=date("d.m.Y H:i:s",strtotime($val["admission_date"]));}else{$admissiondate="-";}

			if($dischargedate=='00.00.0000'){$dischargedate="--";}
			$daystreated ="";
			$pms = new PatientMaster();
			$daystreated = $pms->getDaysDiff($admissiondate,$dischargedate);


			$birthdatep="-";
			if($val['birthd']!="0000-00-00"){
				$date = new Zend_Date($val['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
			}
			$firstname = $val['first_name'];
			$lastname = $val['last_name'];

			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val['ipid']);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];


			$statdia_array = array();

			$statdia_array['patientdata'] =  $firstname.' '.$lastname;
			$statdia_array['epid_num'] =  $gepid;
			$statdia_array['birthd'] = ltrim($birthdatep);
			$statdia_array['daystreated'] = ltrim($daystreated);
			$statdia_array['admission_date'] = ltrim($admissiondate);
			$sortarray1[] = $statdia_array;


		}
		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('birthd'));
			$this->xlsWriteLabel(0,4,$this->view->translate('treateddays'));
			$this->xlsWriteLabel(0,5,$this->view->translate('admissiondate'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['birthd']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['daystreated']));
				$this->xlsWriteLabel($xlsRow,5,utf8_decode($valfile['admission_date']));
				$xlsRow++;
			}

			$fileName = "Patienten_gesamt.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;

		}else{

			$data='';
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
						<tr>
						<th width="10%">'.$this->view->translate('no').'</th>
						<th width="10%">'.$this->view->translate('EPID').'</th>
						<th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th>
						<th width="15%">'.$this->view->translate('birthd').'</th>
						<th width="15%">'.$this->view->translate('treateddays').'</th>
						<th width="15%">'.$this->view->translate('admissiondate').'</th>
						<tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row">
						<td valign="top">'.$rowcount.'</td>
						<td  valign="top">'.$valfile['epid_num'].'&nbsp;</td>
						<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
						<td valign="top">'.$valfile['birthd'].'&nbsp;</td>
						<td  valign="top">'.$valfile['daystreated'].'&nbsp;</td>
						<td valign="top">'.$valfile['admission_date'].'&nbsp;</td>
						</tr>';
				$rowcount++;
			}

			$data.='</table>';

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}

	}

	private function privatepatient($radioarr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();

		$ipids = $this->getClientPatients($clientid, $whereepid);

		foreach($ipids as $ipid){
			$ipid_str .= '"'.$ipid['ipid'].'",';
		}
		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->leftJoin("PatientHealthInsurance h");
		$actpatient->andWhere('h.ipid = p.ipid and h.privatepatient = 1');
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$actpatientexec = $actpatient->execute();
		$activearray = $actpatientexec->toArray();

		foreach($activearray as $key=>$val)
		{
			$dispat = Doctrine_Query::create()
			->select("*")
			->from("PatientDischarge")
			->where("ipid ='".$val['ipid']."'");

			$dispatexec = $dispat->execute();
			$disipidarray = $dispatexec->toArray();

			$split = explode(" ",$disipidarray[0]['discharge_date']);
			$bsplit = explode("-",$split[0]);
			$dischargedate = $bsplit[2].".".$bsplit[1].".".$bsplit[0];

			if($val["admission_date"]!='0000-00-00 00:00:00'){$admissiondate=date("d.m.Y H:i:s",strtotime($val["admission_date"]));}else{$admissiondate="-";}

			if($dischargedate=='00.00.0000'){$dischargedate="--";}

			$daystreated ="";

			$pms = new PatientMaster();
			$daystreated = $pms->getDaysDiff($admissiondate,$dischargedate);

			$patepid = Doctrine_Query::create()
			->select('epid')
			->from("EpidIpidMapping")
			->where('ipid ="'.$val['ipid'].'" and clientid = "'.$logininfo->clientid.'" ');
			$epidexec = $patepid->execute();
			$epidarray = $epidexec->toArray();

			$gepid = $epidarray[0]['epid'];

			$firstname = $val['first_name'];
			$lastname = $val['last_name'];


			$statdia_array = array();

			$statdia_array['firstname'] = $val['first_name'];
			$statdia_array['lastname'] = $val['last_name'];
			$statdia_array['epid_num'] = $gepid;
			$statdia_array['daystreated'] = ltrim($daystreated);
			$sortarray1[] = $statdia_array;
		}
		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('Epid'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,4,$this->view->translate('treateddays'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteNumber($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['firstname']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['lastname']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['daystreated']));
				$xlsRow++;
			}

			$fileName = "Privatpatient.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;

		}else{

			$data='';
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
						<tr>
						<th width="10%">'.$this->view->translate('no').'</th>
						<th width="10%">'.$this->view->translate('Epid').'</th>
						<th width="10%">'.$this->view->translate('firstname').'</th>
						<th width="10%">'.$this->view->translate('lastname').'&nbsp;</th>
						<th width="15%">'.$this->view->translate('treateddays').'</th>
						<tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row">
						<td valign="top">'.$rowcount.'</td>
						<td valign="top">'.$valfile['epid_num'].'</td>
						<td  valign="top">'.$valfile['firstname'].'&nbsp;</td>
						<td  valign="top">'.$valfile['lastname'].'&nbsp;</td>
						<td  valign="top">'.$valfile['daystreated'].'&nbsp;</td>
						</tr>';
				$rowcount++;
			}

			$data.='</table>';

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}

	}
	//	private function anzahlbesuchepropatient($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$sapdate = $this->getDiagnosisQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
	//
	//		$finalipidval = array();
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval[]=$val['ipid'];
	//		}
	//
	//		//print_r($finalipidval);
	//
	//		foreach($finalipidval as $key=>$valipid)
	//		{
	//			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);
	//
	//			$detpatient = Doctrine_Query::create()
	//			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//			->from('PatientMaster p')
	//			->where("ipid = '".$valipid."'")
	//			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//			$detailexec = $detpatient->execute();
	//			$detailarray = $detailexec->toArray();
	//
	//			if($detailarray[0]['birthd']!="0000-00-00"){
	//				$date = new Zend_Date($detailarray[0]['birthd']);
	//				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
	//				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
	//			}
	//
	//
	//			$sp = Doctrine_Query::create()
	//			->select('*')
	//			->from('Sapsymptom')
	//			->where("ipid='".$valipid."' and ".$sapdate)
	//			->orderBy('create_date ASC');
	//			$spexec = $sp->execute();
	//			$sparr = $spexec->toArray();
	//			$sapvalues = array();
	//			foreach($sparr as $keysap=>$valsap)
	//			{
	//				$explodearray = explode(",",$valsap['sapvalues']);
	//				foreach($explodearray as $keyexp=>$valexp)
	//				{
	//					$sapvalues[$valexp][] = $valexp;
	//				}
	//			}
	//
	//			$Familydoctor ="";
	//			$famdoc = new FamilyDoctor();
	//			$familidoc = $famdoc->getFamilyDoc($detailarray[0]['familydoc_id']);
	//			if(count($familidoc)>0)
	//			{
	//				if(strlen($familidoc[0]['title'])>0)
	//				{
	//					$Familydoctor = $familidoc[0]['title'].", ";
	//				}
	//				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
	//				}
	//
	//				if(strlen($familidoc[0]['phone_practice'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
	//				}
	//
	//				if(strlen($familidoc[0]['street1'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['street1'].", ";
	//				}
	//				if(strlen($familidoc[0]['street2'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['street2'].", ";
	//				}
	//				if(strlen($familidoc[0]['postcode'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['postcode'].", ";
	//				}
	//				if(strlen($familidoc[0]['city'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['city'];
	//				}
	//			}
	//
	//			$treatedby ="";
	//			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
	//			$epidipidarray = $epidipid->toArray();
	//			$gepid = $epidipidarray[0]['epid'];
	//			if(strlen($gepid)>0)
	//			{
	//				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
	//				$treatarray = $treat->toArray();
	//				$user_id = $treatarray[0]['userid'];
	//				$uname = "";
	//				$br="";
	//				foreach($treatarray as $key=>$valtrate)
	//				{
	//					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
	//					if($usr)
	//					{
	//						$userarray = $usr->toArray();
	//
	//						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
	//						$br = ";";
	//					}
	//				}
	//			}
	//
	//			$patinfo = "";
	//			$patientphone = "";
	//
	//			if(strlen($detailarray[0]["first_name"])>0)
	//			{
	//				$patinfo .= $detailarray[0]["first_name"]." ";
	//			}
	//			if(strlen($detailarray[0]['last_name'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['last_name'].", ";
	//			}
	//			if(strlen($birthdatep)>0)
	//			{
	//				$patinfo .=$birthdatep.", ";
	//			}
	//			if(strlen($detailarray[0]['street1'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['street1'].", ";
	//			}
	//			if(strlen($detailarray[0]['zip'])>0)
	//			{
	//				$patinfo .=$detailarray[0]['zip'].", ";
	//			}
	//			if(strlen($detailarray[0]['city'])>0){
	//				$patinfo .=$detailarray[0]['city'];
	//			}
	//
	//			$patientphone = trim($val['phone']);
	//			$statdia_array = array();
	//
	//			$statdia_array['patientdata'] = ltrim($patinfo);
	//			$statdia_array['phone'] = ltrim($patientphone);
	//			$statdia_array['familydoctor'] = ltrim($Familydoctor);
	//			$statdia_array['treatedby'] = ltrim($treatedby);
	//			$statdia_array['sapvalue1'] = count($sapvalues[1]);
	//			$statdia_array['sapvalue2'] = count($sapvalues[2]);
	//			$statdia_array['sapvalue3'] = count($sapvalues[3]);
	//			$statdia_array['sapvalue4'] = count($sapvalues[4]);
	//			$statdia_array['sapvaluetotal4'] = (int)(count($sapvalues[1]) + count($sapvalues[2]) + count($sapvalues[3]) + count($sapvalues[4]));
	//			$sortarray1[] = $statdia_array;
	//
	//		}
	//		$xlsRow = 1;
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('familydoctor'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('anzahlbesuchepropatient'));
	//			$this->xlsWriteLabel(0,8,$this->view->translate('treatedby'));
	//			$this->xlsWriteLabel(1,3,$this->view->translate('anzahlbesuchepropatient'));
	//			$this->xlsWriteLabel(1,4,$this->view->translate('anzahlbesuchepropatient'));
	//			$this->xlsWriteLabel(1,5,$this->view->translate('anzahlbesuchepropatient'));
	//			$this->xlsWriteLabel(1,6,$this->view->translate('anzahlbesuchepropatient'));
	//			$this->xlsWriteLabel(1,7,$this->view->translate('sum'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//			$sapgrandtotal="";
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//				//$sapdata = "";
	//				//$sapdata = 'Hausbesuch in Privatwohnung -'. $valfile['sapvalue1'].'  Besuch im Krankenhaus / Palliativstation -'. $valfile['sapvalue2'].'   Besuch in station?rer Pflegeeinrichtung / Hospiz  -'. $valfile['sapvalue3'].'   Besuch in Arztpraxis  -'. $valfile['sapvalue4'];
	//
	//				$i++;
	//				$this->xlsWriteNumber($xlsRow,0,"$i");
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
	//				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['familydoctor']));
	//				$this->xlsWriteLabel($xlsRow,8,utf8_decode($valfile['treatedby']));
	//				$this->xlsWriteNumber($xlsRow,3,$valfile['sapvalue1']);
	//				$this->xlsWriteNumber($xlsRow,4,$valfile['sapvalue2']);
	//				$this->xlsWriteNumber($xlsRow,5,$valfile['sapvalue3']);
	//				$this->xlsWriteNumber($xlsRow,6,$valfile['sapvalue4']);
	//				$this->xlsWriteNumber($xlsRow,7,$valfile['sapvaluetotal4']);
	//				$xlsRow++;
	//
	//				$sapgrandtotal+=$valfile['sapvaluetotal4'];
	//			}
	//			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum')." / Durchschnitt");
	//			$this->xlsWriteLabel($xlsRow,7,$sapgrandtotal.' ('.$percentage = number_format((($sapgrandtotal/$xlsRow)),2,".","").')');
	//			$this->xlsEOF();
	//
	//			$fileName = "Anzahl_Besuche_pro_Patient.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			//ob_clean();
	//			//flush();
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//		}else{
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="35%">Anzahl Besuche pro Patient</th><th width="15%">'.$this->view->translate('sum').'</th><th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
	//			$rowcount=1;
	//			//array_multisort($count,SORT_DESC,$sortarray1);
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//			$sapgrandtotal="";
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['patientdata'].'&nbsp;</td><td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td><td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
	//										  <tr><td class="botomborder">Hausbesuch in Privatwohnung</td><td class="botomborder">&nbsp;'.$valfile['sapvalue1'].'</td></tr>
	//										  <tr><td class="botomborder">Besuch im Krankenhaus / Palliativstation</td><td class="botomborder">&nbsp;'.$valfile['sapvalue2'].'</td></tr>
	//										  <tr><td class="botomborder">Besuch in station&auml;rer Pflegeeinrichtung / Hospiz</td><td class="botomborder">&nbsp;'.$valfile['sapvalue3'].'</td></tr>
	//										  <tr><td >Besuch in Arztpraxis</td><td >&nbsp;'.$valfile['sapvalue4'].'</td></tr>
	//										</table></td><td valign="top">'.$valfile['sapvaluetotal4'].'&nbsp; </td><td valign="top">'.$valfile['treatedby'].'&nbsp; </td></tr>';
	//				$rowcount++;
	//				$sapgrandtotal+=$valfile['sapvaluetotal4'];
	//
	//			}
	//
	//			$data.='<tr><th width="10%">&nbsp;</th><th width="10%">&nbsp;</th><th width="15%">&nbsp;</th><th width="35%">'.$this->view->translate('sum').' / Durchschnitt </th><th width="15%">'.$sapgrandtotal.'&nbsp;/&nbsp;('.$percentage = number_format((($sapgrandtotal/$rowcount)),2,".","").')</th><th width="15%">&nbsp;</th><tr></table>';
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//				echo "<SCRIPT type='text/javascript'>";
	//				//	echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//
	//			}
	//		}
	//	}
	//

	private function anzahlbesuchepropatient($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$finalipidval = $this->allactivepatiensArry($quarterarr, $yeararr, $montharr);
		foreach($finalipidval as $key=>$valipid)
		{
			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);

			$detpatient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
			->from('PatientMaster p')
			->where("ipid = '".$valipid."'")
			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
			$detailexec = $detpatient->execute();
			$detailarray = $detailexec->toArray();

			if($detailarray[0]['birthd']!="0000-00-00"){
				$date = new Zend_Date($detailarray[0]['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
			}


			$sp = Doctrine_Query::create()
			->select('*')
			->from('Sapsymptom')
			->where('ipid="'.$valipid.'" '.str_replace('%date%','create_date',$active_cond['date_sql']).' ')
			->orderBy('create_date ASC');
			$spexec = $sp->execute();
			$sparr = $spexec->toArray();
			$sapvalues = array();
			foreach($sparr as $keysap=>$valsap)
			{
				$explodearray = explode(",",$valsap['sapvalues']);
				foreach($explodearray as $keyexp=>$valexp)
				{
					$sapvalues[$valexp][] = $valexp;
				}
			}

			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$familidoc = $famdoc->getFamilyDoc($detailarray[0]['familydoc_id']);
			if(count($familidoc)>0)
			{
				if(strlen($familidoc[0]['title'])>0)
				{
					$Familydoctor = $familidoc[0]['title'].", ";
				}
				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
				{
					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
				}

				if(strlen($familidoc[0]['phone_practice'])>0)
				{
					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
				}

				if(strlen($familidoc[0]['street1'])>0)
				{
					$Familydoctor .= $familidoc[0]['street1'].", ";
				}
				if(strlen($familidoc[0]['street2'])>0)
				{
					$Familydoctor .= $familidoc[0]['street2'].", ";
				}
				if(strlen($familidoc[0]['postcode'])>0)
				{
					$Familydoctor .= $familidoc[0]['postcode'].", ";
				}
				if(strlen($familidoc[0]['city'])>0)
				{
					$Familydoctor .= $familidoc[0]['city'];
				}
			}

			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtrate)
				{
					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}

			$patinfo = "";
			$patientphone = "";

			if(strlen($detailarray[0]["first_name"])>0)
			{
				$patinfo .= $detailarray[0]["first_name"]." ";
			}
			if(strlen($detailarray[0]['last_name'])>0)
			{
				$patinfo .= $detailarray[0]['last_name'].", ";
			}
			if(strlen($birthdatep)>0)
			{
				$patinfo .=$birthdatep.", ";
			}
			if(strlen($detailarray[0]['street1'])>0)
			{
				$patinfo .= $detailarray[0]['street1'].", ";
			}
			if(strlen($detailarray[0]['zip'])>0)
			{
				$patinfo .=$detailarray[0]['zip'].", ";
			}
			if(strlen($detailarray[0]['city'])>0){
				$patinfo .=$detailarray[0]['city'];
			}

			$patientphone = trim($val['phone']);
			$statdia_array = array();

			$statdia_array['patientdata'] = ltrim($patinfo);
			$statdia_array['epid_num'] = $gepid;
			$statdia_array['phone'] = ltrim($patientphone);
			$statdia_array['familydoctor'] = ltrim($Familydoctor);
			$statdia_array['treatedby'] = ltrim($treatedby);
			$statdia_array['sapvalue1'] = count($sapvalues[1]);
			$statdia_array['sapvalue2'] = count($sapvalues[2]);
			$statdia_array['sapvalue3'] = count($sapvalues[3]);
			$statdia_array['sapvalue4'] = count($sapvalues[4]);
			$statdia_array['sapvaluetotal4'] = (int)(count($sapvalues[1]) + count($sapvalues[2]) + count($sapvalues[3]) + count($sapvalues[4]));
			$sortarray1[] = $statdia_array;

		}
		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,0,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,2,$this->view->translate('familydoctor'));
			$this->xlsWriteLabel(0,3,$this->view->translate('anzahlbesuchepropatient'));
			$this->xlsWriteLabel(0,8,$this->view->translate('treatedby'));
			$this->xlsWriteLabel(1,3,$this->view->translate('anzahlbesuchepropatient'));
			$this->xlsWriteLabel(1,4,$this->view->translate('anzahlbesuchepropatient'));
			$this->xlsWriteLabel(1,5,$this->view->translate('anzahlbesuchepropatient'));
			$this->xlsWriteLabel(1,6,$this->view->translate('anzahlbesuchepropatient'));
			$this->xlsWriteLabel(1,7,$this->view->translate('sum'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			$sapgrandtotal="";
			foreach($sortarray1 as $key=>$valfile)
			{
				//$sapdata = "";
				//$sapdata = 'Hausbesuch in Privatwohnung -'. $valfile['sapvalue1'].'  Besuch im Krankenhaus / Palliativstation -'. $valfile['sapvalue2'].'   Besuch in station?rer Pflegeeinrichtung / Hospiz  -'. $valfile['sapvalue3'].'   Besuch in Arztpraxis  -'. $valfile['sapvalue4'];
				if ($valfile['sapvaluetotal4'] != 0){
					$i++;
					$this->xlsWriteNumber($xlsRow,0,"$i");
					$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
					$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
					$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['familydoctor']));
					$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['treatedby']));
					$this->xlsWriteNumber($xlsRow,3,$valfile['sapvalue1']);
					$this->xlsWriteNumber($xlsRow,4,$valfile['sapvalue2']);
					$this->xlsWriteNumber($xlsRow,5,$valfile['sapvalue3']);
					$this->xlsWriteNumber($xlsRow,6,$valfile['sapvalue4']);
					$this->xlsWriteNumber($xlsRow,7,$valfile['sapvaluetotal4']);
					$xlsRow++;

					$sapgrandtotal+=$valfile['sapvaluetotal4'];
				}
			}
			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum')." / Durchschnitt");
			$this->xlsWriteLabel($xlsRow,7,$sapgrandtotal.' ('.$percentage = number_format((($sapgrandtotal/$xlsRow)),2,".","").')');
			$this->xlsEOF();

			$fileName = "Anzahl_Besuche_pro_Patient.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('EPID').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="35%">Anzahl Besuche pro Patient</th><th width="15%">'.$this->view->translate('sum').'</th><th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			$sapgrandtotal="";
			foreach($sortarray1 as $key=>$valfile)
			{
				if ($valfile['sapvaluetotal4'] != 0){
					$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['epid_num'].'&nbsp;</td><td  valign="top">'.$valfile['patientdata'].'&nbsp;</td><td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td><td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
										  <tr><td class="botomborder">Hausbesuch in Privatwohnung</td><td class="botomborder">&nbsp;'.$valfile['sapvalue1'].'</td></tr>
										  <tr><td class="botomborder">Besuch im Krankenhaus / Palliativstation</td><td class="botomborder">&nbsp;'.$valfile['sapvalue2'].'</td></tr>
										  <tr><td class="botomborder">Besuch in station&auml;rer Pflegeeinrichtung / Hospiz</td><td class="botomborder">&nbsp;'.$valfile['sapvalue3'].'</td></tr>
										  <tr><td >Besuch in Arztpraxis</td><td >&nbsp;'.$valfile['sapvalue4'].'</td></tr>
										</table></td><td valign="top">'.$valfile['sapvaluetotal4'].'&nbsp; </td><td valign="top">'.$valfile['treatedby'].'&nbsp; </td></tr>';
					$rowcount++;
					$sapgrandtotal+=$valfile['sapvaluetotal4'];
				}
			}

			$data.='<tr><th width="10%">&nbsp;</th><th width="10%">&nbsp;</th><th width="10%">&nbsp;</th><th width="15%">&nbsp;</th><th width="35%">'.$this->view->translate('sum').' / Durchschnitt </th><th width="15%">'.$sapgrandtotal.'&nbsp;/&nbsp;('.$percentage = number_format((($sapgrandtotal/$rowcount)),2,".","").')</th><th width="15%">&nbsp;</th><tr></table>';

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}

	//	private function anzahltelefonateemails($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$sapdate = $this->getDiagnosisQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$finalipidval = array();
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval[]=$val['ipid'];
	//		}
	//
	//		//print_r($finalipidval);
	//		foreach($finalipidval as $key=>$valipid)
	//		{
	//			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);
	//
	//			$detpatient = Doctrine_Query::create()
	//			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//			->from('PatientMaster p')
	//			->where("ipid = '".$valipid."'")
	//			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//			$detailexec = $detpatient->execute();
	//			$detailarray = $detailexec->toArray();
	//
	//			if($detailarray[0]['birthd']!="0000-00-00"){
	//				$date = new Zend_Date($detailarray[0]['birthd']);
	//				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
	//				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
	//			}
	//
	//			//print_r($detailarray);
	//			$sp = Doctrine_Query::create()
	//			->select('*')
	//			->from('Sapsymptom')
	//			//->where("CONCAT(',', sapvalues, ',') like '%,6,%' and ipid='".$val['ipid']."' and ".$sapdate)
	//			->where("ipid='".$valipid."' and ".$sapdate)
	//			->orderBy('create_date ASC');
	//			//echo $sp->getSqlQuery();
	//			$spexec = $sp->execute();
	//			$sparr = $spexec->toArray();
	//
	//			$sapvalues = array();
	//			foreach($sparr as $keysap=>$valsap)
	//			{
	//				$explodearray = explode(",",$valsap['sapvalues']);
	//				foreach($explodearray as $keyexp=>$valexp)
	//				{
	//					$sapvalues[$valexp][] = $valexp;
	//				}
	//			}
	//
	//			$Familydoctor ="";
	//			$famdoc = new FamilyDoctor();
	//			$familidoc = $famdoc->getFamilyDoc($detailarray[0]['familydoc_id']);
	//			if(count($familidoc)>0)
	//			{
	//				if(strlen($familidoc[0]['title'])>0)
	//				{
	//					$Familydoctor = $familidoc[0]['title'].", ";
	//				}
	//				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
	//				}
	//
	//				if(strlen($familidoc[0]['phone_practice'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
	//				}
	//
	//				if(strlen($familidoc[0]['street1'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['street1'].", ";
	//				}
	//				if(strlen($familidoc[0]['street2'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['street2'].", ";
	//				}
	//				if(strlen($familidoc[0]['postcode'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['postcode'].", ";
	//				}
	//				if(strlen($familidoc[0]['city'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['city'];
	//				}
	//			}
	//
	//			$treatedby ="";
	//			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
	//			$epidipidarray = $epidipid->toArray();
	//			$gepid = $epidipidarray[0]['epid'];
	//			if(strlen($gepid)>0)
	//			{
	//				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
	//				$treatarray = $treat->toArray();
	//				$user_id = $treatarray[0]['userid'];
	//				$uname = "";
	//				$br="";
	//				foreach($treatarray as $key=>$valtrate)
	//				{
	//					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
	//					if($usr)
	//					{
	//						$userarray = $usr->toArray();
	//
	//						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
	//						$br = ";";
	//					}
	//				}
	//			}
	//
	//			$patinfo = "";
	//			$patientphone = "";
	//
	//			if(strlen($detailarray[0]["first_name"])>0)
	//			{
	//				$patinfo .= $detailarray[0]["first_name"]." ";
	//			}
	//			if(strlen($detailarray[0]['last_name'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['last_name'].", ";
	//			}
	//			if(strlen($birthdatep)>0)
	//			{
	//				$patinfo .= $birthdatep.", ";
	//			}
	//			if(strlen($detailarray[0]['street1'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['street1'].", ";
	//			}
	//			if(strlen($val['zip'])>0)
	//			{
	//				$patinfo .=$detailarray[0]['zip'].", ";
	//			}
	//			if(strlen($val['city'])>0){
	//				$patinfo .=$detailarray[0]['city'];
	//			}
	//
	//			$patientphone = trim($detailarray[0]['phone']);
	//			$statdia_array = array();
	//
	//			$statdia_array['patientdata'] = ltrim($patinfo);
	//			$statdia_array['phone'] = ltrim($patientphone);
	//			$statdia_array['familydoctor'] = ltrim($Familydoctor);
	//			$statdia_array['treatedby'] = ltrim($treatedby);
	//			$statdia_array['sapvalue6'] = count($sapvalues[6]);
	//
	//			$sortarray1[] = $statdia_array;
	//
	//
	//		}
	//		$xlsRow = 1;
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('familydoctor'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('anzahltelefonateemails'));
	//			$this->xlsWriteLabel(0,4,$this->view->translate('treatedby'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$i++;
	//				$this->xlsWriteNumber($xlsRow,0,"$i");
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
	//				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['familydoctor']));
	//				$this->xlsWriteNumber($xlsRow,3,$valfile['sapvalue6']);
	//				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['treatedby']));
	//				$totalsapvalue += $valfile['sapvalue6'];
	//				$xlsRow++;
	//			}
	//			$this->xlsWriteLabel($xlsRow,2,$this->view->translate('sum')." / Durchschnitt");
	//			$this->xlsWriteLabel($xlsRow,3,$totalsapvalue." / ".number_format(($totalsapvalue/($xlsRow-1)),2,".",""));
	//			$this->xlsEOF();
	//
	//			$fileName = "Anzahl_Telefonate_Emails.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			//ob_clean();
	//			//flush();
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//		}else{
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="35%">'.$this->view->translate('anzahltelefonateemails').'</th><th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
	//			$rowcount=1;
	//			//array_multisort($count,SORT_DESC,$sortarray1);
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['patientdata'].'&nbsp;</td><td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td><td valign="top">'.$valfile['sapvalue6'].'</td><td valign="top">'.$valfile['treatedby'].'&nbsp; </td></tr>';
	//				$rowcount++;
	//				$totalsapvalue += $valfile['sapvalue6'];
	//			}
	//			$data.= '<tr class="row"><td valign="top" colspan="3">&nbsp;'.$this->view->translate('count').' / Durchschnitt</td><td valign="top">'.$totalsapvalue." / ".number_format(($totalsapvalue/($rowcount-1)),2,".","").'</td><td valign="top">&nbsp; </td></tr>';
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//				echo "<SCRIPT type='text/javascript'>";
	//				//	echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//
	//			}
	//		}
	//	}

	/*-------------------------------------------------------------------------------*/
	/*---------------------------Start of anzahltelefonateemails --------------------------------*/


	//	private function anzahltelefonateemails($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
	//
	//		$finalipidval = $this->allactivepatiens($quarterarr, $yeararr, $montharr);
	//
	//		$sapdate = $this->getDiagnosisQuarterCondition($quarterarr,$yeararr,$montharr);
	//
	//		foreach($finalipidval as $key=>$valipid)
	//		{
	//			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);
	//
	//			$detpatient = Doctrine_Query::create()
	//			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//			->from('PatientMaster p')
	//			->where("ipid = '".$valipid."'")
	//			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//			// echo $detpatient->getSqlQuery();
	//
	//			$detailexec = $detpatient->execute();
	//			$detailarray = $detailexec->toArray();
	//
	//			if($detailarray[0]['birthd']!="0000-00-00"){
	//				$date = new Zend_Date($detailarray[0]['birthd']);
	//				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
	//				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
	//			}
	//
	//			//print_r($detailarray);
	//			$sp = Doctrine_Query::create()
	//			->select('*')
	//			->from('Sapsymptom')
	//			//->where("CONCAT(',', sapvalues, ',') like '%,6,%' and ipid='".$val['ipid']."' and ".$sapdate)
	//			->where('ipid="'.$valipid.'" '.$sapdate.' ')
	////			->where('ipid="'.$valipid.'" '.str_replace('%date%','create_date',$active_cond['date_sql']).' ')
	////			->where("ipid='".$valipid."' and ".$sapdate)
	//			->orderBy('create_date ASC');
	//			//echo $sp->getSqlQuery();
	//			$spexec = $sp->execute();
	//			$sparr = $spexec->toArray();
	//
	//			$sapvalues = array();
	//			foreach($sparr as $keysap=>$valsap)
	//			{
	//				$explodearray = explode(",",$valsap['sapvalues']);
	//				foreach($explodearray as $keyexp=>$valexp)
	//				{
	//					$sapvalues[$valexp][] = $valexp;
	//				}
	//			}
	//
	//			$Familydoctor ="";
	//			$famdoc = new FamilyDoctor();
	//			$familidoc = $famdoc->getFamilyDoc($detailarray[0]['familydoc_id']);
	//			if(count($familidoc)>0)
	//			{
	//				if(strlen($familidoc[0]['title'])>0)
	//				{
	//					$Familydoctor = $familidoc[0]['title'].", ";
	//				}
	//				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
	//				}
	//
	//				if(strlen($familidoc[0]['phone_practice'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
	//				}
	//
	//				if(strlen($familidoc[0]['street1'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['street1'].", ";
	//				}
	//				if(strlen($familidoc[0]['street2'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['street2'].", ";
	//				}
	//				if(strlen($familidoc[0]['postcode'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['postcode'].", ";
	//				}
	//				if(strlen($familidoc[0]['city'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['city'];
	//				}
	//			}
	//
	//			$treatedby ="";
	//			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
	//			$epidipidarray = $epidipid->toArray();
	//			$gepid = $epidipidarray[0]['epid'];
	//			if(strlen($gepid)>0)
	//			{
	//				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
	//				$treatarray = $treat->toArray();
	//				$user_id = $treatarray[0]['userid'];
	//				$uname = "";
	//				$br="";
	//				foreach($treatarray as $key=>$valtrate)
	//				{
	//					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
	//					if($usr)
	//					{
	//						$userarray = $usr->toArray();
	//
	//						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
	//						$br = ";";
	//					}
	//				}
	//			}
	//
	//			$patinfo = "";
	//			$patientphone = "";
	//
	//			if(strlen($detailarray[0]["first_name"])>0)
	//			{
	//				$patinfo .= $detailarray[0]["first_name"]." ";
	//			}
	//			if(strlen($detailarray[0]['last_name'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['last_name'].", ";
	//			}
	//			if(strlen($birthdatep)>0)
	//			{
	//				$patinfo .= $birthdatep.", ";
	//			}
	//			if(strlen($detailarray[0]['street1'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['street1'].", ";
	//			}
	//			if(strlen($val['zip'])>0)
	//			{
	//				$patinfo .=$detailarray[0]['zip'].", ";
	//			}
	//			if(strlen($val['city'])>0){
	//				$patinfo .=$detailarray[0]['city'];
	//			}
	//
	//			$patientphone = trim($detailarray[0]['phone']);
	//			$statdia_array = array();
	//
	//			$statdia_array['patientdata'] = ltrim($patinfo);
	//			$statdia_array['phone'] = ltrim($patientphone);
	//			$statdia_array['familydoctor'] = ltrim($Familydoctor);
	//			$statdia_array['treatedby'] = ltrim($treatedby);
	//			$statdia_array['sapvalue6'] = count($sapvalues[6]);
	//
	//			$sortarray1[] = $statdia_array;
	//
	//
	//		}
	//		$xlsRow = 1;
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('familydoctor'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('anzahltelefonateemails'));
	//			$this->xlsWriteLabel(0,4,$this->view->translate('treatedby'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$i++;
	//				$this->xlsWriteNumber($xlsRow,0,"$i");
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
	//				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['familydoctor']));
	//				$this->xlsWriteNumber($xlsRow,3,$valfile['sapvalue6']);
	//				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['treatedby']));
	//				$totalsapvalue += $valfile['sapvalue6'];
	//				$xlsRow++;
	//			}
	//			$this->xlsWriteLabel($xlsRow,2,$this->view->translate('sum')." / Durchschnitt");
	//			$this->xlsWriteLabel($xlsRow,3,$totalsapvalue." / ".number_format(($totalsapvalue/($xlsRow-1)),2,".",""));
	//			$this->xlsEOF();
	//
	//			$fileName = "Anzahl_Telefonate_Emails.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			//ob_clean();
	//			//flush();
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//		}else{
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="35%">'.$this->view->translate('anzahltelefonateemails').'</th><th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
	//			$rowcount=1;
	//			//array_multisort($count,SORT_DESC,$sortarray1);
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['patientdata'].'&nbsp;</td><td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td><td valign="top">'.$valfile['sapvalue6'].'</td><td valign="top">'.$valfile['treatedby'].'&nbsp; </td></tr>';
	//				$rowcount++;
	//				$totalsapvalue += $valfile['sapvalue6'];
	//			}
	//			$data.= '<tr class="row"><td valign="top" colspan="3">&nbsp;'.$this->view->translate('count').' / Durchschnitt</td><td valign="top">'.$totalsapvalue." / ".number_format(($totalsapvalue/($rowcount-1)),2,".","").'</td><td valign="top">&nbsp; </td></tr>';
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//				echo "<SCRIPT type='text/javascript'>";
	//				//	echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//
	//			}
	//		}
	//	}


	private function anzahltelefonateemails($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$finalipidval =$this->allactivepatiensArry($quarterarr, $yeararr, $montharr);

		foreach($finalipidval as $key=>$valipid)
		{
			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);

			$detpatient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
			->from('PatientMaster p')
			->where("ipid = '".$valipid."'")
			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

			$detailarray = $detpatient->fetchArray();

			if($detailarray[0]['birthd']!="0000-00-00"){
				$date = new Zend_Date($detailarray[0]['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
			}

			$sp = Doctrine_Query::create()
			->select('*,(gesamt_zeit_in_minuten - davon_fahrtzeit) as zeit')
			->from('Sapsymptom')
			->where('ipid="'.$valipid.'" '.str_replace('%date%', 'create_date', $active_cond['date_sql']).'')
			->orderBy('create_date ASC');
			//			echo $sp->getSqlQuery().'<br /><br />';

			$sparr = $sp->fetchArray();

			$sapvalues = array();
			$zeit = 0;
			foreach($sparr as $keysap=>$valsap)
			{
				$explodearray = explode(",",$valsap['sapvalues']);
				if(in_array('6',$explodearray)){ //e-mail / tel
					$zeit += $valsap['zeit'];
				}
				foreach($explodearray as $keyexp=>$valexp)
				{
					$sapvalues[$valexp][] = $valexp;
				}
			}



			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$familidoc = $famdoc->getFamilyDoc($detailarray[0]['familydoc_id']);
			if(count($familidoc)>0)
			{
				if(strlen($familidoc[0]['title'])>0)
				{
					$Familydoctor = $familidoc[0]['title'].", ";
				}
				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
				{
					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
				}

				if(strlen($familidoc[0]['phone_practice'])>0)
				{
					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
				}

				if(strlen($familidoc[0]['street1'])>0)
				{
					$Familydoctor .= $familidoc[0]['street1'].", ";
				}
				if(strlen($familidoc[0]['street2'])>0)
				{
					$Familydoctor .= $familidoc[0]['street2'].", ";
				}
				if(strlen($familidoc[0]['postcode'])>0)
				{
					$Familydoctor .= $familidoc[0]['postcode'].", ";
				}
				if(strlen($familidoc[0]['city'])>0)
				{
					$Familydoctor .= $familidoc[0]['city'];
				}
			}

			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtrate)
				{
					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}

			$patinfo = "";
			$patientphone = "";

			if(strlen($detailarray[0]["first_name"])>0)
			{
				$patinfo .= $detailarray[0]["first_name"]." ";
			}
			if(strlen($detailarray[0]['last_name'])>0)
			{
				$patinfo .= $detailarray[0]['last_name'].", ";
			}
			if(strlen($birthdatep)>0)
			{
				$patinfo .= $birthdatep.", ";
			}
			if(strlen($detailarray[0]['street1'])>0)
			{
				$patinfo .= $detailarray[0]['street1'].", ";
			}
			if(strlen($val['zip'])>0)
			{
				$patinfo .=$detailarray[0]['zip'].", ";
			}
			if(strlen($val['city'])>0){
				$patinfo .=$detailarray[0]['city'];
			}

			$patientphone = trim($detailarray[0]['phone']);
			$statdia_array = array();

			$statdia_array['epid_num'] = $gepid;
			$statdia_array['patientdata'] = ltrim($patinfo);
			$statdia_array['phone'] = ltrim($patientphone);
			$statdia_array['familydoctor'] = ltrim($Familydoctor);
			$statdia_array['treatedby'] = ltrim($treatedby);
			$statdia_array['sapvalue6'] = count($sapvalues[6]);
			$statdia_array['zeit'] = rtrim($zeit);

			$sortarray1[] = $statdia_array;


		}
		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('familydoctor'));
			$this->xlsWriteLabel(0,4,$this->view->translate('anzahltelefonateemails'));
			$this->xlsWriteLabel(0,5,$this->view->translate('treatedby'));
			$this->xlsWriteLabel(0,6,$this->view->translate('(Zeit / Min.)'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['familydoctor']));
				$this->xlsWriteNumber($xlsRow,4,$valfile['sapvalue6']);
				$this->xlsWriteLabel($xlsRow,5,utf8_decode($valfile['treatedby']));
				$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['zeit']));
				$totalsapvalue += $valfile['sapvalue6'];
				$totalzeit += $valfile['zeit'];
				$xlsRow++;
			}
			$this->xlsWriteLabel($xlsRow,3,$this->view->translate('sum')." / Durchschnitt");
			$this->xlsWriteLabel($xlsRow,4,$totalsapvalue." / ".number_format(($totalsapvalue/($xlsRow-1)),2,".",""));
			$this->xlsWriteLabel($xlsRow,5,"");
			$this->xlsWriteLabel($xlsRow,6,$totalzeit." / ".number_format(($totalzeit/($xlsRow-1)),2,".",""));
			$this->xlsEOF();

			$fileName = "Anzahl_Telefonate_Emails.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('EPID').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="35%">'.$this->view->translate('anzahltelefonateemails').'</th><th width="15%">'.$this->view->translate('treatedby').'</th><th width="15%">'.$this->view->translate('(Zeit / Min.)').'</th><tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row">
				<td valign="top">'.$rowcount.'</td>
				<td  valign="top">'.$valfile['epid_num'].'&nbsp;</td>
				<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
				<td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td>
				<td valign="top">'.$valfile['sapvalue6'].'</td>
				<td valign="top">'.$valfile['treatedby'].'&nbsp; </td>
				<td valign="top">'.$valfile['zeit'].'</td>
				</tr>';
				$rowcount++;
				$totalsapvalue += $valfile['sapvalue6'];
				$totalzeit += $valfile['zeit'];
			}
			$data.= '<tr class="row">
			<td valign="top" colspan="4">&nbsp;'.$this->view->translate('count').' / Durchschnitt</td>
			<td valign="top">'.$totalsapvalue." / ".number_format(($totalsapvalue/($rowcount-1)),2,".","").'</td>
			<td valign="top">&nbsp; </td>
			<td valign="top">'.$totalzeit." / ".number_format(($totalzeit/($rowcount-1)),2,".","").'</td>
			</tr>';
			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}

	/*--------------------------- End of anzahltelefonateemails --------------------------------*/
	/*-------------------------------------------------------------		------------------*/




	//	private function fahrtzeitgefahrenekilometer($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$sapdate = $this->getDiagnosisQuarterCondition($quarterarr,$yeararr,$montharr);
	//
	//		$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$finalipidval = array();
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval[]=$val['ipid'];
	//		}
	//
	//		//print_r($finalipidval);
	//
	//		foreach($finalipidval as $key=>$valipid)
	//		{
	//			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);
	//
	//			$detpatient = Doctrine_Query::create()
	//			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//			->from('PatientMaster p')
	//			->where("ipid = '".$valipid."'")
	//			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//			$detailexec = $detpatient->execute();
	//			$detailarray = $detailexec->toArray();
	//
	//			if($detailarray[0]['birthd']!="0000-00-00"){
	//				$date = new Zend_Date($detailarray[0]['birthd']);
	//				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
	//				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
	//			}
	//
	//			$sp = Doctrine_Query::create()
	//			->select('*')
	//			->from('Sapsymptom')
	//			->where("ipid='".$detailarray[0]['ipid']."' and ".$sapdate)
	//			//->where("(gesamt_zeit_in_minuten!='' or gesamt_fahrstrecke_in_km!='') and ipid='".$val['ipid']."' and ".$sapdate)
	//			->orderBy('create_date ASC');
	//
	////			echo $sp->getSqlQuery();
	//			$spexec = $sp->execute();
	//			$sparr = $spexec->toArray();
	//			$sapmintuen = "0";
	//			$sapkilometer = "0";
	//			/*if(count($sparr)<1)
	//			 {
	//			 continue;
	//			 }*/
	//			foreach($sparr as $keysap=>$valsap)
	//			{
	//				if($valsap['gesamt_zeit_in_minuten']>0)
	//				{
	//					$sapmintuen += $valsap['gesamt_zeit_in_minuten'];
	//				}
	//
	//				if($valsap['gesamt_fahrstrecke_in_km']>0){
	//					$sapkilometer += $valsap['gesamt_fahrstrecke_in_km'];
	//				}
	//			}
	//
	//			$sumcollnr="";
	//			$collnr1 = "";
	//			$spcoll = Doctrine_Query::create()
	//			->select('count(*)')
	//			->from('Sapsymptom')
	//			->where("ipid='".$detailarray[0]['ipid']."' and (gesamt_zeit_in_minuten <> 0 OR gesamt_fahrstrecke_in_km <> 0 OR davon_fahrtzeit <>0) and ".$sapdate)
	//			->orderBy('create_date ASC');
	//			//  						 echo $spcoll->getSqlQuery();
	//			//  						 echo '<br/>';
	//			$spcollexec = $spcoll->execute();
	//			$spcollarr = $spcollexec->toArray();
	//			$collnr1 = $spcollarr[0]['count'];
	//			//						echo $collnr1.'<br/>';
	//			//						 $coll_array = array();
	//			//						 $coll_array['count'] = $spcollarr[0]['count'];
	//			//
	//			//
	//
	//			$Familydoctor ="";
	//			$famdoc = new FamilyDoctor();
	//			$familidoc = $famdoc->getFamilyDoc($detailarray[0]['familydoc_id']);
	//			if(count($familidoc)>0)
	//			{
	//				if(strlen($familidoc[0]['title'])>0)
	//				{
	//					$Familydoctor = $familidoc[0]['title'].", ";
	//				}
	//				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
	//				}
	//
	//				if(strlen($familidoc[0]['phone_practice'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
	//				}
	//
	//				if(strlen($familidoc[0]['street1'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['street1'].", ";
	//				}
	//				if(strlen($familidoc[0]['street2'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['street2'].", ";
	//				}
	//				if(strlen($familidoc[0]['postcode'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['postcode'].", ";
	//				}
	//				if(strlen($familidoc[0]['city'])>0)
	//				{
	//					$Familydoctor .= $familidoc[0]['city'];
	//				}
	//			}
	//
	//			$treatedby ="";
	//			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
	//			$epidipidarray = $epidipid->toArray();
	//			$gepid = $epidipidarray[0]['epid'];
	//			if(strlen($gepid)>0)
	//			{
	//				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
	//				$treatarray = $treat->toArray();
	//				$user_id = $treatarray[0]['userid'];
	//				$uname = "";
	//				$br="";
	//				foreach($treatarray as $key=>$valtrate)
	//				{
	//
	//					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
	//					if($usr)
	//					{
	//						$userarray = $usr->toArray();
	//
	//						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
	//						$br = ";";
	//					}
	//				}
	//			}
	//
	//
	//			$patinfo = "";
	//			$patientphone = "";
	//
	//			if(strlen($detailarray[0]["first_name"])>0)
	//			{
	//				$patinfo .= $detailarray[0]["first_name"]." ";
	//			}
	//			if(strlen($detailarray[0]['last_name'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['last_name'].", ";
	//			}
	//			if(strlen($birthdatep)>0)
	//			{
	//				$patinfo .=$birthdatep.", ";
	//			}
	//			if(strlen($detailarray[0]['street1'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['street1'].", ";
	//			}
	//			if(strlen($detailarray[0]['zip'])>0)
	//			{
	//				$patinfo .=$detailarray[0]['zip'].", ";
	//			}
	//			if(strlen($detailarray[0]['city'])>0){
	//				$patinfo .=$detailarray[0]['city'];
	//			}
	//
	//			$patientphone = trim($val['phone']);
	//			$statdia_array = array();
	//
	//			$statdia_array['patientdata'] = ltrim($patinfo);
	//			$statdia_array['phone'] = ltrim($patientphone);
	//			$statdia_array['familydoctor'] = ltrim($Familydoctor);
	//			$statdia_array['treatedby'] = ltrim($treatedby);
	//			$statdia_array['sapminuten'] = $sapmintuen;
	//			$statdia_array['sapkilometer'] = $sapkilometer;
	//			$statdia_array['collnr'] = $collnr1;
	//
	//
	//			$sortarray1[] = $statdia_array;
	//
	//
	//		}
	//		$xlsRow = 1;
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('familydoctor'));
	//			$this->xlsWriteLabel(0,3,"Fahrtzeit in Minuten");
	//			$this->xlsWriteLabel(0,4,"gefahrene Kilometer");
	//			$this->xlsWriteLabel(0,5,$this->view->translate('treatedby'));
	//			$this->xlsWriteLabel(0,6,"Fahrten");
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$i++;
	//				$this->xlsWriteNumber($xlsRow,0,"$i");
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
	//				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['familydoctor']));
	//				$this->xlsWriteNumber($xlsRow,3,$valfile['sapminuten']);
	//				$this->xlsWriteNumber($xlsRow,4,$valfile['sapkilometer']);
	//				$this->xlsWriteLabel($xlsRow,5,utf8_decode($valfile['treatedby']));
	//				$sapminutes+=$valfile['sapminuten'];
	//				$sapkilometer+=$valfile['sapkilometer'];
	//				$this->xlsWriteNumber($xlsRow,2,$valfile["collnr"]);
	//				$sumcollnr+=$valfile['collnr'];
	//
	//				$xlsRow++;
	//			}
	//			$this->xlsWriteLabel($xlsRow,2,$this->view->translate('sum'));
	//			$this->xlsWriteLabel($xlsRow,3,$sapminutes." (".(int)($sapminutes/60) ." Stunden ". ($sapminutes%60) ." Minuten)");
	//			$this->xlsWriteNumber($xlsRow,4,$sapkilometer);
	//			$this->xlsWriteNumber($xlsRow,5,$sumcollnr);
	//
	//			$this->xlsEOF();
	//
	//			$fileName = "Fahrtzeit_gefahrene_Kilometer.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			//ob_clean();
	//			//flush();
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//		}else{
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
	//						<tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="35%">Fahrtzeit in Minuten</th><th width="35%">gefahrene Kilometer</th><th width="15%">'.$this->view->translate('treatedby').'</th><th width="15%">Fahrten</th><tr>';
	//			$rowcount=1;
	//			//array_multisort($count,SORT_DESC,$sortarray1);
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['patientdata'].'&nbsp;</td><td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td><td valign="top">'.$valfile['sapminuten'].'&nbsp;</td><td valign="top">'.$valfile['sapkilometer'].'&nbsp;</td><td valign="top">'.$valfile['treatedby'].'&nbsp; </td><td valign="top">'.$valfile['collnr'].'&nbsp; </td></tr>';
	//				$sapminutes+=$valfile['sapminuten'];
	//				$sapkilometer+=$valfile['sapkilometer'];
	//				$sumcollnr+=$valfile['collnr'];
	//				$rowcount++;
	//			}
	//			$data.= '<tr class="row"><td valign="top">&nbsp;</td><td  valign="top">&nbsp;</td><td  valign="top">'.$this->view->translate('sum').'&nbsp;</td><td valign="top">'.$sapminutes.' ('.(int)($sapminutes/60) .' Stunden '. ($sapminutes%60) .' Minuten)&nbsp;</td><td valign="top">'.$sapkilometer.'&nbsp;</td><td valign="top">&nbsp; </td><td valign="top">'.$sumcollnr.'</td></tr>';
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//				echo "<SCRIPT type='text/javascript'>";
	//				//	echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//
	//			}
	//		}
	//	}
	//

	private function fahrtzeitgefahrenekilometer($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$finalipidval = array();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
		$finalipidval = $this->allactivepatiensArry($quarterarr, $yeararr, $montharr);


		foreach($finalipidval as $key=>$valipid)
		{
			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender);

			$detpatient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
			->from('PatientMaster p')
			->where("ipid = '".$valipid."'")
			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

			$detailarray = $detpatient->fetchArray();


			if($detailarray[0]['birthd']!="0000-00-00"){
				$date = new Zend_Date($detailarray[0]['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
			}

			$sp = Doctrine_Query::create()
			->select('*')
			->from('Sapsymptom')
			->where('ipid="'.$detailarray[0]['ipid'].'" '.str_replace('%date%', 'create_date', $active_cond['date_sql']).' ')
			->orderBy('create_date ASC');
			//			echo '<br/>'.$sp->getSqlQuery(). '<br/>';

			$sparr = $sp->fetchArray();

			$sapmintuen = "0";
			$sapkilometer = "0";

			foreach($sparr as $keysap=>$valsap)
			{
				if($valsap['gesamt_zeit_in_minuten']>0)
				{
					$sapmintuen += $valsap['gesamt_zeit_in_minuten'];
				}

				if($valsap['gesamt_fahrstrecke_in_km']>0){
					$sapkilometer += trim($valsap['gesamt_fahrstrecke_in_km']);
				}
			}

			$sumcollnr="";
			$collnr1 = "";
			$spcoll = Doctrine_Query::create()
			->select('count(*)')
			->from('Sapsymptom')
			->where('ipid="'.$detailarray[0]["ipid"].'" and (gesamt_zeit_in_minuten <> 0 OR gesamt_fahrstrecke_in_km <> 0 OR davon_fahrtzeit <>0) '.str_replace('%date%','create_date',$active_cond['date_sql']).' ')
			->orderBy('create_date ASC');

			$spcollarr = $spcoll->fetchArray();

			$collnr1 = $spcollarr[0]['count'];


			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$familidoc = $famdoc->getFamilyDoc($detailarray[0]['familydoc_id']);
			if(count($familidoc)>0)
			{
				if(strlen($familidoc[0]['title'])>0)
				{
					$Familydoctor = $familidoc[0]['title'].", ";
				}
				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
				{
					$Familydoctor .= $familidoc[0]['first_name']." ".$familidoc[0]['last_name'].", ";
				}

				if(strlen($familidoc[0]['phone_practice'])>0)
				{
					$Familydoctor .= $familidoc[0]['phone_practice'].", ";
				}

				if(strlen($familidoc[0]['street1'])>0)
				{
					$Familydoctor .= $familidoc[0]['street1'].", ";
				}
				if(strlen($familidoc[0]['street2'])>0)
				{
					$Familydoctor .= $familidoc[0]['street2'].", ";
				}
				if(strlen($familidoc[0]['postcode'])>0)
				{
					$Familydoctor .= $familidoc[0]['postcode'].", ";
				}
				if(strlen($familidoc[0]['city'])>0)
				{
					$Familydoctor .= $familidoc[0]['city'];
				}
			}

			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtrate)
				{

					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}


			$patinfo = "";
			$patientphone = "";

			if(strlen($detailarray[0]["first_name"])>0)
			{
				$patinfo .= $detailarray[0]["first_name"]." ";
			}
			if(strlen($detailarray[0]['last_name'])>0)
			{
				$patinfo .= $detailarray[0]['last_name'].", ";
			}
			if(strlen($birthdatep)>0)
			{
				$patinfo .=$birthdatep.", ";
			}
			if(strlen($detailarray[0]['street1'])>0)
			{
				$patinfo .= $detailarray[0]['street1'].", ";
			}
			if(strlen($detailarray[0]['zip'])>0)
			{
				$patinfo .=$detailarray[0]['zip'].", ";
			}
			if(strlen($detailarray[0]['city'])>0){
				$patinfo .=$detailarray[0]['city'];
			}

			$patientphone = trim($val['phone']);
			$statdia_array = array();

			$statdia_array['epid_num'] = $gepid;
			$statdia_array['patientdata'] = ltrim($patinfo);
			$statdia_array['phone'] = ltrim($patientphone);
			$statdia_array['familydoctor'] = ltrim($Familydoctor);
			$statdia_array['treatedby'] = ltrim($treatedby);
			$statdia_array['sapminuten'] = trim($sapmintuen);
			$statdia_array['sapkilometer'] = trim($sapkilometer);
			$statdia_array['collnr'] = $collnr1;


			$sortarray1[] = $statdia_array;


		}
		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('familydoctor'));
			$this->xlsWriteLabel(0,4,"Fahrtzeit in Minuten");
			$this->xlsWriteLabel(0,5,"gefahrene Kilometer");
			$this->xlsWriteLabel(0,6,$this->view->translate('treatedby'));
			$this->xlsWriteLabel(0,7,"Fahrten");

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				if ($valfile['collnr'] !== "0"){
					$i++;
					$this->xlsWriteNumber($xlsRow,0,"$i");
					$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
					$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
					$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['familydoctor']));
					$this->xlsWriteNumber($xlsRow,4,$valfile['sapminuten']);
					$this->xlsWriteNumber($xlsRow,5,$valfile['sapkilometer']);
					$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['treatedby']));
					$sapminutes+=$valfile['sapminuten'];
					$sapkilometer+=$valfile['sapkilometer'];
					$this->xlsWriteNumber($xlsRow,3,$valfile["collnr"]);
					$sumcollnr+=$valfile['collnr'];

					$xlsRow++;
				}
			}
			$this->xlsWriteLabel($xlsRow,2,$this->view->translate('sum'));
			$this->xlsWriteLabel($xlsRow,3,$sapminutes." (".(int)($sapminutes/60) ." Stunden ". ($sapminutes%60) ." Minuten)");
			$this->xlsWriteNumber($xlsRow,4,$sapkilometer);
			$this->xlsWriteNumber($xlsRow,5,$sumcollnr);

			$this->xlsEOF();

			$fileName = "Fahrtzeit_gefahrene_Kilometer.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
						<tr>
						<th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('EPID').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%">'.$this->view->translate('familydoctor').'</th><th width="35%">Fahrtzeit in Minuten</th><th width="35%">gefahrene Kilometer</th><th width="15%">'.$this->view->translate('treatedby').'</th><th width="15%">Fahrten</th><tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				if ($valfile['collnr'] !== "0"){
					$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$valfile['epid_num'].'&nbsp;</td><td  valign="top">'.$valfile['patientdata'].'&nbsp;</td><td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td><td valign="top">'.$valfile['sapminuten'].'&nbsp;</td><td valign="top">'.$valfile['sapkilometer'].'&nbsp;</td><td valign="top">'.$valfile['treatedby'].'&nbsp; </td><td valign="top">'.$valfile['collnr'].'&nbsp; </td></tr>';
					$sapminutes+=$valfile['sapminuten'];
					$sapkilometer+=$valfile['sapkilometer'];
					$sumcollnr+=$valfile['collnr'];
					$rowcount++;
				}
			}
			$data.= '<tr class="row"><td valign="top">&nbsp;</td><td valign="top">&nbsp;</td><td  valign="top">&nbsp;</td><td  valign="top">'.$this->view->translate('sum').'&nbsp;</td><td valign="top">'.$sapminutes.' ('.(int)($sapminutes/60) .' Stunden '. ($sapminutes%60) .' Minuten)&nbsp;</td><td valign="top">'.$sapkilometer.'&nbsp;</td><td valign="top">&nbsp; </td><td valign="top">'.$sumcollnr.'</td></tr>';
			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}


	private function neuePatientenVO($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
		$newpatient = Doctrine_Query::create()
		->select("*,date_format(p.admission_date, '%d.%m.%Y') as admission_date, p.ipid as ipid,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		//		->where('isdischarged = 0')
		//		->andWhere('isdelete = 0')
		//		->andWhere('isstandby = 0')
		->where(substr(str_replace('%date%','admission_date',$active_cond['date_sql']),4));
		$newpatient->leftJoin("p.EpidIpidMapping e");
		$newpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);


		$newpatientar = $newpatient->fetchArray();

		//echo $newpatient->getSqlQuery();
		//exit;


		foreach($newpatientar as $newpatient_ipid){
			$newpatient_ipid_str .= '"'.$newpatient_ipid['ipid'].'",';
			$newpatientarray[$newpatient_ipid['ipid']] = $newpatient_ipid;
		}



		if(!empty($newpatientarray)) {
			$drop = Doctrine_Query::create()
			->select('*')
			->from('SapvVerordnung')
			->where('ipid in ('.substr($newpatient_ipid_str, 0,-1).')  and isdelete=0 and verordnet != ""')
			->orderBy('ipid asc');
			$droparray = $drop->fetchArray();

			//echo $drop->getSqlQuery();

			$status1 = 0;
			$status2 = 0;
			$status3 = 0;
			$status4 = 0;

			$vo_statuses = array(1 => 'a', 2 => 'g', 3 => 'kA', 0 => 'kA');;
			$vo_net = array(1 => 'Beratung', 2 => 'Koordination', 3 => 'Teilversorgung', 4 => 'Vollversorgung' );

			foreach($droparray as $drop){
				$verarray = explode(',',$drop['verordnet']);
				$max = max($verarray);
				$veropatdet[$drop['ipid']]['verordnet'] = $drop['verordnet'];
				for($i=1;$i<=4;$i++){
					if(!isset($veropatdet[$drop['ipid']]['max']) || $veropatdet[$drop['ipid']]['max'] < $i || ($max == $i && $veropatdet[$drop['ipid']]['max_status'] != '2' && $drop['stauts'] == '2')) {
						$veropatdet[$drop['ipid']]['max'] = $vo_net[$max];
						$veropatdet[$drop['ipid']]['max_status'] = $vo_statuses[$drop['status']];
					}
					//					if(in_array($i, $verarray)){
					//						${'status'.$i}++;
					//					}
				}
				if($drop['status'] == '2'){
					$max_approved = max($verarray);
					for($i=1;$i<=4;$i++){
						if(!isset($veropatdet[$drop['ipid']]['max_approved']) || $max_approved < $i) {
							$veropatdet[$drop['ipid']]['max_approved'] = $vo_net[$max_approved];
							$veropatdet[$drop['ipid']]['max_approved_status'] = $vo_statuses[$drop['status']];
						}
					}
				}
			}

			foreach($veropatdet as $key=>$patient){
				$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$newpatientarray[$key]['ipid']);
				$epidipidarray = $epidipid->toArray();
				$gepid = $epidipidarray[0]['epid'];

				$actpatientarray[$key] = $patient;
				$actpatientarray[$key]['patientdata'] = $newpatientarray[$key]['first_name'].' '.$newpatientarray[$key]['last_name'];
				$actpatientarray[$key]['admission_date'] = $newpatientarray[$key]['admission_date'];
				$actpatientarray[$key]['epid_num'] = $gepid;
			}

		}


		$sortarray = $actpatientarray;

		$xlsRow = 2;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,3,"Aufnahmedatum ");
			$this->xlsWriteLabel(1,4,"Verordnung ");

			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray as $key=>$valfile)
			{
				$vertable ="";
				$i++;

				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['admission_date']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['maxvo']), $valfile['verordnet']);

				$xlsRow++;

				//$this->xlsWriteLabel($xlsRow,2,$vertable);


			}

			$this->xlsEOF();

			$fileName = "neuePatientenVO.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
			<tr>
			<th >'.$this->view->translate('no').'</th>
			<th >'.$this->view->translate('EPID').'</th>
			<th>'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th>
			<th>Aufnahmedatum</th>
			<th >Verordnung</th>

			<tr>';

			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			$rowcount = 1;
			foreach($sortarray as $key=>$val)
			{

				//					if ($val["max_status"] == 1){
				//						$status = "a";
				//					}elseif($val["max_status"] == 2){
				//						$status = "g";
				//					}elseif($val["max_status"] == 3 || empty($val['max_status'])){
				//						$status = "ka";
				//					}
				//					if ($val["max"] == 1){
				//						$maxvo ="Beratung";
				//					}elseif ($val["max"] == 2){
				//						$maxvo ="Koordination";
				//					}elseif ($val["max"] == 3){
				//						$maxvo ="Teilversorgung";
				//					}elseif ($val["max"] == 4){
				//						$maxvo ="Vollversorgung";
				//					}
				foreach($vo_net as $vo_key =>$vo_val){
					if($val['max'] == $vo_val){
						${'status'.$vo_key}++;
					}
				}

				if($val['max'] != $val['max_approved'] && !empty($val['max_approved'])){
					$approved_str = '   '.$val['max_approved'].'('.$val['max_approved_status'].')';
				} else {
					$approved_str = '';
				}

				$data.= "
					<tr class='row'>
					<td valign='top'>".$rowcount."</td>
					<td valign='top'>".$val["epid_num"]."</td>
					<td valign='top'>".$val["patientdata"]."</td>
					<td valign='top'>".$val["admission_date"]."</td>
					<td valign='top'><span style='float:right'> ".$val['max']." (".$val['max_status'].")".$approved_str."</span></td>
					</tr>";
				$rowcount++;

			}

			$data.="
			<tr><td colspan='4' rowspan='5'   valign='top' align='right'>Summe</td> </tr>
			<tr><td>Koordination -> ".$status1."</td></tr>
			<tr><td>Beratung -> ".$status2."</td></tr>
			<tr><td>Teilversorgung -> ".$status3."</td></tr>
			<tr><td>Vollversorgung -> ".$status4."</td></tr>
			";

			$data.="</table>";


			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}





	//	private function verordnungenundartderbetreuung($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$sapdate = $this->getDiagnosisQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
	//
	//		$finalipidval = array();
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval[]=$val['ipid'];
	//		}
	//
	//		//print_r($finalipidval);
	//
	//		foreach($finalipidval as $key=>$valipid)
	//		{
	//			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender,$status,$verordnet_von,$sapcheckvalue,$fromdate,$tilldate);
	//
	//			$detpatient = Doctrine_Query::create()
	//			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//			->from('PatientMaster p')
	//			->where("ipid = '".$valipid."'")
	//			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//			$detailexec = $detpatient->execute();
	//			$detailarray = $detailexec->toArray();
	//
	//			if($detailarray[0]['birthd']!="0000-00-00"){
	//				$date = new Zend_Date($detailarray[0]['birthd']);
	//				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
	//				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
	//			}
	//
	//			$Familydoctor ="";
	//			$famdoc = new FamilyDoctor();
	//			$sapver = new SapvVerordnung();
	//			$verdoung = $sapver->getSapvVerordnungData($valipid);
	//			$statusarray = $sapver->getSapvRadios();
	//			$sapvarray = Pms_CommonData::getSapvCheckBox();
	//			$verdreport = array();
	//			$conter=0;
	//			$rowcount=1;
	//			foreach($verdoung as $keyver=>$valver)
	//			{
	//				if($valver['status']>0)
	//				{
	//					$verdreport[$conter]['status'] = $statusarray[$valver['status']];
	//				}
	//				$explod = "";
	//				if(strlen($valver['verordnet'])>0)
	//				{	$comma="";
	//				$sapcheckvalue="";
	//				$explod = explode(",",$valver['verordnet']);
	//				foreach($explod as $keyverdnet=>$valverdnet)
	//				{
	//					$sapcheckvalue .=$comma.$sapvarray[$valverdnet];
	//					$comma=",";
	//				}
	//				$verdreport[$conter]['sapcheckvalue']=$sapcheckvalue;
	//				}
	//				if($valver['verordnet_von']>0)
	//				{
	//					$veror_array = $famdoc->getFamilyDoc($valver['verordnet_von']);
	//					if(strlen($veror_array[0]['first_name'])>0 || strlen($veror_array[0]['last_name'])>0)
	//					{
	//						$verdreport[$conter]['verordnet_von'] = $veror_array[0]['first_name']." ".$veror_array[0]['last_name'];
	//					}
	//				}
	//
	//				/*if($valver['fromdate']!="0000-00-00 00:00:00")
	//				 {*/
	//				$verdreport[$conter]['fromdate'] = date("d.m.Y",strtotime($valver['verordnungam']));
	//				//}
	//
	//				/*if($valver['tilldate']!="0000-00-00 00:00:00")
	//				 {*/
	//				$verdreport[$conter]['tilldate'] = date("d.m.Y",strtotime($valver['verordnungbis']));
	//				//}
	//				$conter++;
	//			}
	//
	//			$treatedby ="";
	//			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
	//			$epidipidarray = $epidipid->toArray();
	//			$gepid = $epidipidarray[0]['epid'];
	//			if(strlen($gepid)>0)
	//			{
	//				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
	//				$treatarray = $treat->toArray();
	//				$user_id = $treatarray[0]['userid'];
	//				$uname = "";
	//				$br="";
	//				foreach($treatarray as $key=>$valtrate)
	//				{
	//					$usr = Doctrine::getTable('User')->find($valtrate['userid']);
	//					if($usr)
	//					{
	//						$userarray = $usr->toArray();
	//
	//						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
	//						$br = ";";
	//					}
	//				}
	//			}
	//
	//
	//			$patinfo = "";
	//			$patientphone = "";
	//
	//			if(strlen($detailarray[0]["first_name"])>0)
	//			{
	//				$patinfo .= $detailarray[0]["first_name"]." ";
	//			}
	//			if(strlen($detailarray[0]['last_name'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['last_name'].", ";
	//			}
	//			if(strlen($birthdatep)>0)
	//			{
	//				$patinfo .= $birthdatep.", ";
	//			}
	//			if(strlen($detailarray[0]['street1'])>0)
	//			{
	//				$patinfo .= $detailarray[0]['street1'].", ";
	//			}
	//			if(strlen($detailarray[0]['zip'])>0)
	//			{
	//				$patinfo .=$detailarray[0]['zip'].", ";
	//			}
	//			if(strlen($detailarray[0]['city'])>0){
	//				$patinfo .=$detailarray[0]['city'];
	//			}
	//
	//			$patientphone = trim($val['phone']);
	//			$statdia_array = array();
	//
	//			$statdia_array['patientdata'] = ltrim($patinfo);
	//			//$statdia_array['familydoctor'] = ltrim($Familydoctor);
	//			$statdia_array['sapverordoung'] = $verdreport;
	//			/*$statdia_array['sapcheck'] = $sapcheckvalue;
	//			 $statdia_array['verordnet_von'] = $verordnet_von;
	//			 $statdia_array['fromdate'] = $fromdate;
	//			 $statdia_array['tilldate'] = $tilldate;*/
	//
	//			$sortarray1[] = $statdia_array;
	//
	//
	//		}
	//
	//		$xlsRow = 2;
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
	//			$this->xlsWriteLabel(0,2,"Verordnungen");
	//			$this->xlsWriteLabel(1,2,"Verordnet von");
	//			$this->xlsWriteLabel(1,3,"von - bis");
	//			$this->xlsWriteLabel(1,4,"Verordnet");
	//			$this->xlsWriteLabel(1,5,"Status");
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//				$vertable ="";
	//				$i++;
	//
	//				$this->xlsWriteNumber($xlsRow,0,"$i");
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
	//
	//				//$vertable = 'Verordnet von  -  bis  - Verordnet  -  Status';
	//				foreach($valfile['sapverordoung'] as $verkey=>$verval)
	//				{
	//					//$vertable.=$verval['verordnet_von'].' - '.$verval['fromdate'].' - '.$verval['tilldate'].' - '.$verval['sapcheckvalue'].' - '.$verval['status']; 				$this->xlsWriteLabel($xlsRow,2,"Verordnet von");
	//
	//					if($verval['fromdate']!='0000-00-00 00:00:00')
	//					{
	//						$verval['fromdate'] = date('d.m.Y',strtotime($verval['fromdate']));
	//					}
	//					if($verval['tilldate']!='0000-00-00 00:00:00')
	//					{
	//						$verval['tilldate'] = date('d.m.Y',strtotime($verval['tilldate']));
	//					}
	//
	//					$this->xlsWriteLabel($xlsRow,2,$verval['verordnet_von']);
	//					$this->xlsWriteLabel($xlsRow,3,$verval['fromdate'].' - '.$verval['tilldate']);
	//					$this->xlsWriteLabel($xlsRow,4,$verval['sapcheckvalue']);
	//					$this->xlsWriteLabel($xlsRow,5,$verval['status']);
	//
	//					$xlsRow++;
	//				}
	//
	//				$xlsRow++;
	//
	//				//$this->xlsWriteLabel($xlsRow,2,$vertable);
	//
	//
	//			}
	//
	//			$this->xlsEOF();
	//
	//			$fileName = "Verordnungen_und_Art_der_Betreuung.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			//ob_clean();
	//			//flush();
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//		}else{
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%" colspan="5">Verordnungen</th><tr>';
	//
	//			//array_multisort($count,SORT_DESC,$sortarray1);
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//			$data.= '<tr class="row"><td valign="top">&nbsp;</td><td  valign="top">&nbsp;</td>
	//								<td align="left" width="25%"><b>Verordnet von</b></td><td align="left" width="25%"><b>von - bis</b></td><td align="left" width="25%"><b>Verordnet</b></td><td align="left" width="25%"><b>Status</b></td></tr>';
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{ 	$vertable ="";
	//			$patientdata1="";
	//
	//			$cntname=0;
	//
	//			if(count($valfile['sapverordoung'])>0)
	//			{	$rowspan=0;
	//			foreach($valfile['sapverordoung'] as $verkey=>$verval)
	//			{	$patientdata1="";
	//			$countno="";
	//			$tds="";
	//			if($cntname<1)
	//			{
	//				$patientdata1=$valfile['patientdata'];
	//				$cntname++;
	//				$countno=$rowcount;
	//				$tds='<td valign="top" rowspan="'.(count($valfile['sapverordoung'])).'" >'.$countno.'&nbsp;</td><td  valign="top" rowspan="'.(count($valfile['sapverordoung'])).'">'.$patientdata1.'&nbsp;</td>';
	//			}
	//
	//			if($verval['fromdate']!='0000-00-00 00:00:00')
	//			{
	//				$verval['fromdate'] = date('d.m.Y',strtotime($verval['fromdate']));
	//			}
	//			if($verval['tilldate']!='0000-00-00 00:00:00')
	//			{
	//				$verval['tilldate'] = date('d.m.Y',strtotime($verval['tilldate']));
	//			}
	//
	//
	//			$data.= '<tr class="row">'.$tds.'<td align="left" width="25%">'.$verval['verordnet_von'].'&nbsp;</td><td align="left" width="25%">'.$verval['fromdate'].'-'.$verval['tilldate'].'&nbsp;</td><td align="left" width="25%">'.$verval['sapcheckvalue'].'&nbsp;</td><td align="left" width="25%">'.$verval['status'].'&nbsp;</td></tr>';
	//			$rowspan++;
	//			}
	//
	//			}else{
	//				$patientdata1=$valfile['patientdata'];
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$patientdata1.'&nbsp;</td><td align="left" width="25%">&nbsp;</td><td align="left" width="25%">&nbsp;</td><td align="left" width="25%">&nbsp;</td><td align="left" width="25%">&nbsp;</td></tr>';
	//
	//			}
	//			$rowcount++;
	//			}
	//
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//				echo "<SCRIPT type='text/javascript'>";
	//				//	echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//
	//			}
	//		}
	//	}

	private function verordnungenundartderbetreuung($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
		$sapdate = $this->getDiagnosisQuarterCondition($quarterarr,$yeararr,$montharr);

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$finalipidval = $this->allactivepatiensArry($quarterarr, $yeararr, $montharr);

		/*------------------------- Start Block to replace ---------------------------------
		 $finalipidval = array();
		 list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
		 $startdate = date("Y-m-d",strtotime($startdate));
		 $enddate = date("Y-m-d",strtotime($enddate));

		 $actpatient = Doctrine_Query::create()
		 ->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		 ->from('PatientMaster p')
		 ->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
		 //->andWhere('isdischarged = 0')
		 // ->andWhere('isdischarged = 0 and '.$admtwhere)
		 ->andWhere('isstandby = 0')
		 ->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		 //->andWhere('ipid in ('.$ipidval.')');
		 $actpatient->leftJoin("p.EpidIpidMapping e");
		 $actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		 $actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		 $actpatientexec = $actpatient->execute();
		 $actipidarray = $actpatientexec->toArray();
		 foreach($actipidarray as $key=>$val)
		 {
			$finalipidval[]= $val['ipid'];
			}


			$patient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
			->from('PatientMaster p')
			->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
			//->andWhere('isdischarged = 0')
			// ->andWhere('isdischarged = 0 and '.$admtwhere)
			->andWhere('isstandby = 0')
			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

			//->andWhere('ipid in ('.$ipidval.')');
			$patient->leftJoin("p.EpidIpidMapping e");
			$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
			$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

			$patientexec = $patient->execute();
			$ipidarray = $patientexec->toArray();
			$disipidval="'0'";
			$comma=",";
			foreach($ipidarray as $key=>$val)
			{
			$disipidval.=$comma."'".$val['ipid']."'";
			$comma=",";
			}
			$disquery = Doctrine_Query::create()
			->select("*")
			->from('PatientDischarge')
			->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
			//echo  $disquery->getSqlQuery();
			$disexec = $disquery->execute();
			$disarray = $disexec->toArray();
			foreach($disarray as $key=>$val)
			{
			$finalipidval[]=$val['ipid'];
			}


			/*------------------------- End Block to replace ---------------------------------*/

		//print_r($finalipidval);

		foreach($finalipidval as $key=>$valipid)
		{
			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender,$status,$verordnet_von,$sapcheckvalue,$fromdate,$tilldate);

			$detpatient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
			->from('PatientMaster p')
			->where("ipid = '".$valipid."'")
			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
			$detailexec = $detpatient->execute();
			$detailarray = $detailexec->toArray();

			if($detailarray[0]['birthd']!="0000-00-00"){
				$date = new Zend_Date($detailarray[0]['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
			}

			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$sapver = new SapvVerordnung();
			//			$verdoung = $sapver->getSapvVerordnungData($valipid);
			$statusarray = $sapver->getSapvRadios();

			$s = array('%date_start%','%date_end%');
			$r = array('verordnungam','verordnungbis');

			$sapver = Doctrine_Query::create()
			->select('*')
			->from('SapvVerordnung')
			->where("ipid='".$valipid."' and isdelete=0 and ((".str_replace($s,$r,$active_cond['interval_sql'])."))")
			->orderBy("id");


			$verdoung = $sapver->fetchArray();


			if(is_array($verdoung) && sizeof($verdoung) > 0) {

				$sapvarray = Pms_CommonData::getSapvCheckBox();
				$verdreport = array();
				$conter=0;
				$rowcount=1;
				foreach($verdoung as $keyver=>$valver)
				{
					if($valver['status']>0)
					{
						$verdreport[$conter]['status'] = $statusarray[$valver['status']];
					}
					$explod = "";
					if(strlen($valver['verordnet'])>0)
					{	$comma="";
					$sapcheckvalue="";
					$explod = explode(",",$valver['verordnet']);
					foreach($explod as $keyverdnet=>$valverdnet)
					{
						$sapcheckvalue .=$comma.$sapvarray[$valverdnet];
						$comma=",";
					}
					$verdreport[$conter]['sapcheckvalue']=$sapcheckvalue;
					}
					if($valver['verordnet_von']>0)
					{
						$veror_array = $famdoc->getFamilyDoc($valver['verordnet_von']);
						if(strlen($veror_array[0]['first_name'])>0 || strlen($veror_array[0]['last_name'])>0)
						{
							$verdreport[$conter]['verordnet_von'] = $veror_array[0]['first_name']." ".$veror_array[0]['last_name'];
						}
					}

					/*if($valver['fromdate']!="0000-00-00 00:00:00")
					 {*/
					$verdreport[$conter]['fromdate'] = date("d.m.Y",strtotime($valver['verordnungam']));
					//}

					/*if($valver['tilldate']!="0000-00-00 00:00:00")
					 {*/
					$verdreport[$conter]['tilldate'] = date("d.m.Y",strtotime($valver['verordnungbis']));
					//}
					$conter++;
				}

				$treatedby ="";
				$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
				$epidipidarray = $epidipid->toArray();
				$gepid = $epidipidarray[0]['epid'];
				if(strlen($gepid)>0)
				{
					$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
					$treatarray = $treat->toArray();
					$user_id = $treatarray[0]['userid'];
					$uname = "";
					$br="";
					foreach($treatarray as $key=>$valtrate)
					{
						$usr = Doctrine::getTable('User')->find($valtrate['userid']);
						if($usr)
						{
							$userarray = $usr->toArray();

							$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
							$br = ";";
						}
					}
				}


				$patinfo = "";
				$patientphone = "";

				if(strlen($detailarray[0]["first_name"])>0)
				{
					$patinfo .= $detailarray[0]["first_name"]." ";
				}
				if(strlen($detailarray[0]['last_name'])>0)
				{
					$patinfo .= $detailarray[0]['last_name'].", ";
				}
				if(strlen($birthdatep)>0)
				{
					$patinfo .= $birthdatep.", ";
				}
				if(strlen($detailarray[0]['street1'])>0)
				{
					$patinfo .= $detailarray[0]['street1'].", ";
				}
				if(strlen($detailarray[0]['zip'])>0)
				{
					$patinfo .=$detailarray[0]['zip'].", ";
				}
				if(strlen($detailarray[0]['city'])>0){
					$patinfo .=$detailarray[0]['city'];
				}

				$patientphone = trim($val['phone']);
				$statdia_array = array();

				$statdia_array['epid_num'] = $gepid;
				$statdia_array['patientdata'] = ltrim($patinfo);
				//$statdia_array['familydoctor'] = ltrim($Familydoctor);
				$statdia_array['sapverordoung'] = $verdreport;
				/*$statdia_array['sapcheck'] = $sapcheckvalue;
				 $statdia_array['verordnet_von'] = $verordnet_von;
				 $statdia_array['fromdate'] = $fromdate;
				 $statdia_array['tilldate'] = $tilldate;*/

				$sortarray1[] = $statdia_array;


			}

		}




		$xlsRow = 2;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,3,"Verordnungen");
			$this->xlsWriteLabel(1,4,"Verordnet von");
			$this->xlsWriteLabel(1,5,"von - bis");
			$this->xlsWriteLabel(1,6,"Verordnet");
			$this->xlsWriteLabel(1,7,"Status");

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				$vertable ="";
				$i++;

				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));

				//$vertable = 'Verordnet von  -  bis  - Verordnet  -  Status';
				foreach($valfile['sapverordoung'] as $verkey=>$verval)
				{
					//$vertable.=$verval['verordnet_von'].' - '.$verval['fromdate'].' - '.$verval['tilldate'].' - '.$verval['sapcheckvalue'].' - '.$verval['status']; 				$this->xlsWriteLabel($xlsRow,2,"Verordnet von");

					if($verval['fromdate']!='0000-00-00 00:00:00')
					{
						$verval['fromdate'] = date('d.m.Y',strtotime($verval['fromdate']));
					}
					if($verval['tilldate']!='0000-00-00 00:00:00')
					{
						$verval['tilldate'] = date('d.m.Y',strtotime($verval['tilldate']));
					}

					$this->xlsWriteLabel($xlsRow,3,$verval['verordnet_von']);
					$this->xlsWriteLabel($xlsRow,4,$verval['fromdate'].' - '.$verval['tilldate']);
					$this->xlsWriteLabel($xlsRow,5,$verval['sapcheckvalue']);
					$this->xlsWriteLabel($xlsRow,6,$verval['status']);

					$xlsRow++;
				}

				$xlsRow++;

				//$this->xlsWriteLabel($xlsRow,2,$vertable);


			}

			$this->xlsEOF();

			$fileName = "Verordnungen_und_Art_der_Betreuung.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%"><tr><th width="10%">'.$this->view->translate('no').'</th><th width="10%">'.$this->view->translate('EPID').'</th><th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th><th width="15%" colspan="5">Verordnungen</th><tr>';

			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			$data.= '<tr class="row"><td valign="top">&nbsp;</td><td valign="top">&nbsp;</td><td  valign="top">&nbsp;</td>
								<td align="left" width="25%"><b>Verordnet von</b></td><td align="left" width="25%"><b>von - bis</b></td><td align="left" width="25%"><b>Verordnet</b></td><td align="left" width="25%"><b>Status</b></td></tr>';

			foreach($sortarray1 as $key=>$valfile)
			{ 	$vertable ="";
			$patientdata1="";
			$epid_num1 ="";

			$cntname=0;

			if(count($valfile['sapverordoung'])>0)
			{	$rowspan=0;
			foreach($valfile['sapverordoung'] as $verkey=>$verval)
			{	$patientdata1="";
			$epid_num1 ="";
			$countno="";
			$tds="";
			if($cntname<1)
			{
				$epid_num1=$valfile['epid_num'];
				$patientdata1=$valfile['patientdata'];
				$cntname++;
				$countno=$rowcount;
				$tds='<td valign="top" rowspan="'.(count($valfile['sapverordoung'])).'" >'.$countno.'&nbsp;</td><td valign="top" rowspan="'.(count($valfile['sapverordoung'])).'" >'.$epid_num1.'&nbsp;</td><td  valign="top" rowspan="'.(count($valfile['sapverordoung'])).'">'.$patientdata1.'&nbsp;</td>';
			}

			if($verval['fromdate']!='0000-00-00 00:00:00')
			{
				$verval['fromdate'] = date('d.m.Y',strtotime($verval['fromdate']));
			}
			if($verval['tilldate']!='0000-00-00 00:00:00')
			{
				$verval['tilldate'] = date('d.m.Y',strtotime($verval['tilldate']));
			}


			$data.= '<tr class="row">'.$tds.'<td align="left" width="25%">'.$verval['verordnet_von'].'&nbsp;</td><td align="left" width="25%">'.$verval['fromdate'].'-'.$verval['tilldate'].'&nbsp;</td><td align="left" width="25%">'.$verval['sapcheckvalue'].'&nbsp;</td><td align="left" width="25%">'.$verval['status'].'&nbsp;</td></tr>';
			$rowspan++;
			}

			}else{
				$patientdata1=$valfile['patientdata'];
				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td  valign="top">'.$epid_num1.'&nbsp;</td><td  valign="top">'.$patientdata1.'&nbsp;</td><td align="left" width="25%">&nbsp;</td><td align="left" width="25%">&nbsp;</td><td align="left" width="25%">&nbsp;</td><td align="left" width="25%">&nbsp;</td></tr>';

			}
			$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}


	private function dischargemethodDead($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}

		$pwoerarrary = $this->dischargeMethodDeadlocation($montharr,$quarterarr,$yeararr);
		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
		//		$diswhere = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);

		$xlsRow = 1;
		$location = array();
		$statdia_array = array();
		$dischargelocation = array();
		$sumtotal="";
		foreach($pwoerarrary as $key=>$val)
		{
			if(!in_array($val['discharge_location'],$location))
			{
				++$i;
				$qpa1 = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
				->from('DischargeLocation')
				->where("isdelete=0 and clientid=".$clientid." and id='".$val['discharge_location']."'");
				$qp1 = $qpa1->execute();
				$newarr1=$qp1->toArray();
				if(count($newarr1)>0)
				{
					//echo  $newarr1[0]["count"];
					array_push($location,$val['discharge_location']);

					//array_push($dischargelocation,array($val['discharge_location']=>$newarr1[0]['location']);
					$statdia_array['dischargelocation'] = $newarr1[0]['location'];
					$qpacount1 = Doctrine_Query::create()
					->select("count(*)")
					->from('PatientDischarge')
					->where('discharge_location="'.$val['discharge_location'].'"   '.str_replace('%date%','discharge_date',$active_cond['date_sql']).' '  );
					//echo $qpacount1->getSqlQuery();
					$qpacount1exec = $qpacount1->execute();
					$counter =$qpacount1exec->toArray();
					$statdia_array['count'] = $counter[0]['count'];
					$sumtotal += $counter[0]['count'];
					$statdia_array['sumtotal'] = $sumtotal;
					//$statdia_array['count'] = $avgdiagnosisarray[0]['count'];

					//$count[$key]  = $avgdiagnosisarray[0]['count'];
					$sortarray[] = $statdia_array;
				}
			}
		}

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('dischargelocation'));
			$this->xlsWriteLabel(0,2,$this->view->translate('count'));
			$this->xlsWriteLabel(0,3,$this->view->translate('precentage'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			$totalcountd="";
			foreach($sortarray as $key=>$val)
			{
				$this->xlsWriteNumber($xlsRow,0,$key+1);
				$this->xlsWriteLabel($xlsRow,1,$val["dischargelocation"]);
				$this->xlsWriteNumber($xlsRow,2,$val["count"]);
				$this->xlsWriteNumber($xlsRow,3,number_format(($val["count"]/$sumtotal)*100, 2, '.', ' '));
				$totalcountd += $val["count"];
				$xlsRow++;
			}
			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum'));
			$this->xlsWriteNumber($xlsRow,2,$totalcountd);
			$this->xlsEOF();

			$fileName = "Dischargelocation_statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");

			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$fileName);

			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">'.$this->view->translate('no').'</th>
									<th width="10%">'.$this->view->translate('dischargelocation').'</th>
									<th width="15%">'.$this->view->translate('count').'</th><th width="15%">'.$this->view->translate('percentage').'</th></tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray as $key=>$val)
			{

				$data.= "<tr class='row'><td valign='top'>".$rowcount."</td><td valign='top'>".$val["dischargelocation"]."</td><td valign='top'>".$val["count"]."</td><td valign='top'>".number_format(($val["count"]/$sumtotal)*100, 2, '.', ' ')."</td></tr>";
				$rowcount++;
				$totalcountd += $val["count"];
			}
			$data.= "<tr class='row'><td valign='top'>&nbsp;</td><td valign='top'>Summe</td><td valign='top'>".$totalcountd."</td><td valign='top'>&nbsp;</td></tr>";
			$data.="</table>";
			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;

				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;

				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}



	//	private function dischargemethodDead($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//
	//		if($logininfo->clientid>0)
	//		{
	//			$clientid=$logininfo->clientid;
	//		}else{
	//			$clientid=0;
	//		}
	//
	//		$pwoerarrary = $this->dischargeMethodDeadlocation($montharr,$quarterarr,$yeararr);
	//		$diswhere = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
	//
	//		$xlsRow = 1;
	//		$location = array();
	//		$statdia_array = array();
	//		$dischargelocation = array();
	//		$sumtotal="";
	//		foreach($pwoerarrary as $key=>$val)
	//		{
	//			if(!in_array($val['discharge_location'],$location))
	//			{
	//				++$i;
	//				$qpa1 = Doctrine_Query::create()
	//				->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
	//				->from('DischargeLocation')
	//				->where("isdelete=0 and clientid=".$clientid." and id='".$val['discharge_location']."'");
	//				$qp1 = $qpa1->execute();
	//				$newarr1=$qp1->toArray();
	//				if(count($newarr1)>0)
	//				{
	//					//echo  $newarr1[0]["count"];
	//					array_push($location,$val['discharge_location']);
	//
	//					//array_push($dischargelocation,array($val['discharge_location']=>$newarr1[0]['location']);
	//					$statdia_array['dischargelocation'] = $newarr1[0]['location'];
	//					$qpacount1 = Doctrine_Query::create()
	//					->select("count(*)")
	//					->from('PatientDischarge')
	//					->where("discharge_location='".$val['discharge_location']."' and ".$diswhere);
	//					//echo $qpacount1->getSqlQuery();
	//					$qpacount1exec = $qpacount1->execute();
	//					$counter =$qpacount1exec->toArray();
	//					$statdia_array['count'] = $counter[0]['count'];
	//					$sumtotal += $counter[0]['count'];
	//					$statdia_array['sumtotal'] = $sumtotal;
	//					//$statdia_array['count'] = $avgdiagnosisarray[0]['count'];
	//
	//					//$count[$key]  = $avgdiagnosisarray[0]['count'];
	//					$sortarray[] = $statdia_array;
	//				}
	//			}
	//		}
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('dischargelocation'));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('count'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('precentage'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
	//			}
	//			$totalcountd="";
	//			foreach($sortarray as $key=>$val)
	//			{
	//				$this->xlsWriteNumber($xlsRow,0,$key+1);
	//				$this->xlsWriteLabel($xlsRow,1,$val["dischargelocation"]);
	//				$this->xlsWriteNumber($xlsRow,2,$val["count"]);
	//				$this->xlsWriteNumber($xlsRow,3,number_format(($val["count"]/$sumtotal)*100, 2, '.', ' '));
	//				$totalcountd += $val["count"];
	//				$xlsRow++;
	//			}
	//			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum'));
	//			$this->xlsWriteNumber($xlsRow,2,$totalcountd);
	//			$this->xlsEOF();
	//
	//			$fileName = "Dischargelocation_statistics.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//
	//			exit;
	//
	//		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
	//								 <tr>
	//									<th width="10%">'.$this->view->translate('no').'</th>
	//									<th width="10%">'.$this->view->translate('dischargelocation').'</th>
	//									<th width="15%">'.$this->view->translate('count').'</th><th width="15%">'.$this->view->translate('percentage').'</th></tr>';
	//			$rowcount=1;
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray as $key=>$val)
	//			{
	//
	//				$data.= "<tr class='row'><td valign='top'>".$rowcount."</td><td valign='top'>".$val["dischargelocation"]."</td><td valign='top'>".$val["count"]."</td><td valign='top'>".number_format(($val["count"]/$sumtotal)*100, 2, '.', ' ')."</td></tr>";
	//				$rowcount++;
	//				$totalcountd += $val["count"];
	//			}
	//			$data.= "<tr class='row'><td valign='top'>&nbsp;</td><td valign='top'>Summe</td><td valign='top'>".$totalcountd."</td><td valign='top'>&nbsp;</td></tr>";
	//			$data.="</table>";
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//
	//				exit;
	//				echo "<SCRIPT LANGUAGE='javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}
	//			elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//
	//				echo "<SCRIPT LANGUAGE='javascript'>";
	//				//echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//			}
	//		}
	//	}
	//

	private function powerdigit($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}
		//$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
		//$pwoerarrary = $this->getcourseLdigit($quarterarr,$yeararr,$montharr);

		//$activearray = $this->allactivepatiens($quarterarr,$yeararr,$montharr);

		$timeperiod = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
		$ipids = $this->getClientPatients($clientid, $whereepid);

		foreach($ipids as $ipid){
			$ipid_str .= '"'.$ipid['ipid'].'",';
		}

		$xlsRow = 1;
		//		foreach($pwoerarrary as $key=>$val)
		//		{
		//
		//			if($val['course_title']>0)
		//			{
		//				++$i;
		//				$qpa1 = Doctrine_Query::create()
		//				->select("count(*)")
		//				->from('PatientCourse')
		//				->where("ipid in (".$activearray.") and course_type='".addslashes(Pms_CommonData::aesEncrypt('L'))."' and course_title='".addslashes(Pms_CommonData::aesEncrypt($val['course_title']))."'");
		//				$qp1 = $qpa1->execute();
		//				$newarr1=$qp1->toArray();
		//				//echo  $newarr1[0]["count"];
		//				$statdia_array = array();
		//				$statdia_array['powerdigit'] = $val['course_title'];
		//				$statdia_array['count'] = $newarr1[0]["count"];
		//				//$statdia_array['count'] = $avgdiagnosisarray[0]['count'];
		//
		//				//$count[$key]  = $avgdiagnosisarray[0]['count'];
		//				$sortarray[] = $statdia_array;
		//			}
		//
		//		}


		$qpa1 = Doctrine_Query::create()
		->select("count(*) as count, AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') AS powerdigit")
		->from('PatientCourse')
		->where('ipid IN ('.substr($ipid_str,0,-1).') '.str_replace('%date%','course_date',$timeperiod['date_sql']).' AND AES_DECRYPT(course_title,"'.Zend_Registry::get('salt').'") != "" AND course_type="'.addslashes(Pms_CommonData::aesEncrypt('L')).'"')
		->groupby('course_title');

		$sortarray = $qpa1->fetchArray();

		foreach ($sortarray as $value){
			$totalafcount += $value['count'];
		}

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('powerdigit'));
			$this->xlsWriteLabel(0,2,$this->view->translate('count'));


			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray as $key=>$val)
			{

				$this->xlsWriteNumber($xlsRow,0,$key+1);
				$this->xlsWriteLabel($xlsRow,1,"L ".$val["powerdigit"]);
				$this->xlsWriteNumber($xlsRow,2,$val["count"]);

				$xlsRow++;
			}
			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum'));
			$this->xlsWriteNumber($xlsRow,2,$totalafcount);
			$this->xlsEOF();

			$fileName = "PowerDigit_statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");

			header("Content-Disposition: attachment; filename=".$fileName);

			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">'.$this->view->translate('no').'</th>
									<th width="10%">'.$this->view->translate('powerdigit').'</th>
									<th width="15%">'.$this->view->translate('count').'</th></tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray as $key=>$val)
			{

				$data.= "<tr class='row'><td valign='top'>".$rowcount."</td><td valign='top'>L ".$val["powerdigit"]."</td><td valign='top'>".$val["count"]."</td></tr>";
				$rowcount++;
			}
			$data.="<tr><td>&nbsp;</td><td>Summe</td><td>".$totalafcount."</td> </tr>";
			$data.="</table>";
			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;

				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;

				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}

		private function reportaktualpatients($radioarr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();


   		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where(' isdelete = 0 and isdischarged = 0 and isstandby = 0  and isstandbydelete = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();



		foreach($ipidarray as $key=>$val)
		{
			 $pflegedienste = "";
			 $pflegedienstephone = "";
			$pfle = new PatientPflegedienste();
			$pflearray = $pfle->getPatientLastPflegedienste($val['ipid']);
			if ($pflearray[0]['pflid'] > 0 ){
				$pfledet = new Pflegedienstes();
				$pfledetarray = $pfledet->getPflegedienste($pflearray[0]['pflid']);

			$pflegedienste  =  $pfledetarray[0]['nursing'];
		    $pflegedienstephone = $pfledetarray[0]['phone_practice'];
			}


			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$familidoc = $famdoc->getFamilyDoc($val['familydoc_id']);
			if(count($familidoc)>0)
			{
				if(strlen($familidoc[0]['title'])>0)
				{
					$Familydoctor = $familidoc[0]['title']."  ";
				}
				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
				{
					$Familydoctor_name = $familidoc[0]['first_name']." ".$familidoc[0]['last_name']." ";
				}

				if(strlen($familidoc[0]['phone_practice'])>0)
				{
					$Familydoctor_phone = $familidoc[0]['phone_practice']." ";
				}

			}

			$admisiondate =  date("d.m.Y",strtotime($val['admission_date']));


			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];




			$patientphone = trim($val['phone']);
			$statdia_array = array();

			$statdia_array['epid_num'] = $gepid;
			$statdia_array['patientdata'] =  $val["last_name"].' '.$val["first_name"] ;
			$statdia_array['admission_date'] = $admisiondate;
			$statdia_array['pflegedienste'] = $pflegedienste;
			$statdia_array['pflegedienstephone'] = $pflegedienstephone;
			$statdia_array['familydoctorphone'] = ltrim($Familydoctor_phone);
			$statdia_array['familydoctor'] = ltrim($Familydoctor_name);

			$sortarray1[] = $statdia_array;
			}

		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('epid_num'));
			$this->xlsWriteLabel(0,1,$this->view->translate('lastname').''.$this->view->translate('firstname'));
			$this->xlsWriteLabel(0,2,$this->view->translate('pflegedienste'));
			$this->xlsWriteLabel(0,3,$this->view->translate('pflegedienstephone'));
			$this->xlsWriteLabel(0,4,$this->view->translate('familydoctor'));
			$this->xlsWriteLabel(0,5,$this->view->translate('familydoctorphone'));
			$this->xlsWriteLabel(0,6,$this->view->translate('admissiondate'));



			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				/*if(strlen($val['patientdata'])<1)
				 {
				 continue;
				 }*/
				$i++;
				$this->xlsWriteLabel($xlsRow,0,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['pflegedienste']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['pflegedienstephone']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['familydoctor']));
				$this->xlsWriteLabel($xlsRow,5,utf8_decode($valfile['familydoctorphone']));
				$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['admission_date']));
				$xlsRow++;
			}

			$this->xlsEOF();

			$fileName = "Pflegedienst-Hausarzt.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
			<tr>

			<th width="10%">'.$this->view->translate('EPID').'</th>
			<th width="10%">'.$this->view->translate('lastname').' '.$this->view->translate('firstname').'</th>
			<th width="15%">'.$this->view->translate('pflegedienste').'</th>
			<th width="15%">'.$this->view->translate('pflegedienstephone').'</th>
			<th width="15%">'.$this->view->translate('familydoctor').'</th>
			<th width="15%">'.$this->view->translate('familydoctorphone').'</th>
			<th width="15%">'.$this->view->translate('admissiondate').'</th>
			<tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row">

				<td  valign="top">'.$valfile['epid_num'].'&nbsp;</td>
				<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
				<td  valign="top">'.$valfile['pflegedienste'].'&nbsp;</td>
				<td valign="top">'.$valfile['pflegedienstephone'].'&nbsp; </td>
				<td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td>
				<td valign="top">'.$valfile['familydoctorphone'].'&nbsp; </td>
				<td  valign="top">'.$valfile['admission_date'].'&nbsp;</td>
				</tr>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}



	private function versorger($radioarr,$montharr,$quarterarr,$yeararr)
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$finalipidval = $this->allactivepatiensArry($quarterarr, $yeararr, $montharr);


		foreach($finalipidval as $key=>$valipid)
		{
			unset($diagnosis,$metadiagnosis,$Familydoctor,$actual_location,$gender,$status,$verordnet_von,$sapcheckvalue,$fromdate,$tilldate,$patpfle,$patpharm);

			$detpatient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
			->from('PatientMaster p')
			->where("ipid = '".$valipid."'")
			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
			$detailexec = $detpatient->execute();
			$detailarray = $detailexec->toArray();

			if($detailarray[0]['birthd']!="0000-00-00"){
				$date = new Zend_Date($detailarray[0]['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
			}




			$pflr = new PatientPflegedienste();
			$pflengedianst = $pflr->getPatientPflegedienste($valipid);



			if (sizeof($pflengedianst)>0 ){

			$patpfle = "";

			$patpfle .= '<table border="0" cellpadding="0" cellspacine="0" width="100%"><tr><th align="left" class="botomborder">Pflegedienste</th></tr>';
			foreach ($pflengedianst as $pflevalue){
				$patpfle .='<tr><td class="botomborder">'.$pflevalue['nursing'].'</td></tr>';
			}
			$patpfle .= "</table>";

//			echo $patpfle;
			}

			$patpharm = "";

			$pharm = new PatientPharmacy();
			$pharmacy = $pharm->getPatientPharmacy($valipid);

						if (sizeof($pharmacy)>0 ){

			$patpharm .= '<table border="0" cellpadding="0" cellspacine="0" width="100%"><tr><th align="left" class="botomborder">Apotheke</th></tr>';
			foreach ($pharmacy as $pharmacy){
				$patpharm .='<tr><td class="botomborder">'.$pharmacy['apotheke'].'</td></tr>';
			}
			$patpharm .= "</table>";
						}



				$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
				$epidipidarray = $epidipid->toArray();
				$gepid = $epidipidarray[0]['epid'];



				$patinfo = "";
				$patientphone = "";

				if(strlen($detailarray[0]["first_name"])>0)
				{
					$patinfo .= $detailarray[0]["first_name"]." ";
				}
				if(strlen($detailarray[0]['last_name'])>0)
				{
					$patinfo .= $detailarray[0]['last_name'].", ";
				}


	 			$admisiondate =  date("d.m.Y",strtotime($detailarray[0]['admission_date']));


				$patientphone = trim($val['phone']);
				$statdia_array = array();

				$statdia_array['epid_num'] = $gepid;
				$statdia_array['patientdata'] = ltrim($patinfo);

				$statdia_array['admisiondate'] = $admisiondate;
				$statdia_array['pflegedienste'] = $patpfle;
				$statdia_array['pharmacy'] = $patpharm;

				$sortarray1[] = $statdia_array;



		}




		$xlsRow = 2;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('admision_date'));
			$this->xlsWriteLabel(0,4,"Pflegdienst / Apotheke");

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				/*if(strlen($val['patientdata'])<1)
				 {
				 continue;
				 }*/
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['admisiondate']));
				$this->xlsWriteLabel($xlsRow,5, "");
				$xlsRow++;
			}

			$this->xlsEOF();

			$fileName = "versorger.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="3" cellspacing="0" border="1" width="80%">
			<tr>
			<th width="10%">'.$this->view->translate('no').'</th>
			<th width="10%">'.$this->view->translate('EPID').'</th>
			<th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'&nbsp;</th>
			<th width="10%">'.$this->view->translate('admisiondate').'</th>
			<th width="15%" colspan="5">Pflegdienst / Apotheke </th>
			<tr>';

			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
$rowcount = 1;
			foreach($sortarray1 as $key=>$valfile)
			{


				$data.= '<tr class="row">
				<td  valign="top">'.$rowcount.'&nbsp;</td>
				<td  valign="top">'.$valfile['epid_num'].'&nbsp;</td>
				<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
				<td  valign="top">'.$valfile['admisiondate'].'&nbsp;</td>
				<td  valign="top">'.$valfile['pflegedienste'].'&nbsp;<br/> '.$valfile['pharmacy'].'&nbsp;</td>


				</tr>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}






		private function bielefeldt1($radioarr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
//ipid in ('.substr($ipid_str,0,-1).')


//		$patient = Doctrine_Query::create()
//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
//		->from('PatientMaster p')
//		->where(' isdelete = 0 and isdischarged = 0')
//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
//		$patient->leftJoin("p.EpidIpidMapping e");
//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
//
//		$patientexec = $patient->execute();
//		$ipidarray = $patientexec->toArray();
//
//
//
//
//			foreach($ipidarray as $ipivalue){
//			$ipid_str .= '"'.$ipivalue['ipid'].'",';
//		}




//  		$qpa1 = Doctrine_Query::create()
//		->select("*,AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type, AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
//		->from('PatientCourse')
//		->where('wrongcomment!=1 and ipid in ('.substr($ipid_str,0,-1).') and 	course_type="'.addslashes(Pms_CommonData::aesEncrypt("L")).'" and 	AES_DECRYPT(course_title,"'.Zend_Registry::get('salt').'")   LIKE "%t1%" ')
//        ->orderBy("convert(AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') using latin1) ASC");
//   		$qparray = $qpa1->fetchArray();
  		$qpa1 = Doctrine_Query::create()
		->select("*,AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type, AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
		->from('PatientCourse')
		->where('wrongcomment!=1  and 	course_type="'.addslashes(Pms_CommonData::aesEncrypt("L")).'" and 	AES_DECRYPT(course_title,"'.Zend_Registry::get('salt').'")   LIKE "%t1%" ')
        ->orderBy("convert(AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') using latin1) ASC");
   		$qparray = $qpa1->fetchArray();


   		foreach($qparray as $qvalue){
			$ipid_strt1 .= '"'.$qvalue['ipid'].'",';
   		}

   		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where(' isdelete = 0 and isdischarged = 0 and ipid not in ('.substr($ipid_strt1,0,-1).') ')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();

//
//
//   		$patientdetails = Doctrine_Query::create()
//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
//		->from('PatientMaster p')
//		->where('ipid in ('.substr($ipid_str,0,-1).') and ipid not in ('.substr($ipid_strt1,0,-1).') ')
//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
//
//		$patientarray = $patientdetails->fetchArray();
//


		foreach($ipidarray as $key=>$val)
		{
//			if (substr($val['course_title'],0,2) != "t1"){

			$Familydoctor ="";
			$famdoc = new FamilyDoctor();
			$familidoc = $famdoc->getFamilyDoc($val['familydoc_id']);
			if(count($familidoc)>0)
			{
				if(strlen($familidoc[0]['title'])>0)
				{
					$Familydoctor = $familidoc[0]['title']."  ";
				}
				if(strlen($familidoc[0]['first_name'])>0 || strlen($familidoc[0]['last_name'])>0)
				{
					$Familydoctor_name = $familidoc[0]['first_name']." ".$familidoc[0]['last_name']." ";
				}

				if(strlen($familidoc[0]['phone_practice'])>0)
				{
					$Familydoctor_phone = $familidoc[0]['phone_practice']." ";
				}

			}

			$admisiondate =  date("d.m.Y",strtotime($val['admission_date']));


			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];




			$patientphone = trim($val['phone']);
			$statdia_array = array();

			$statdia_array['epid_num'] = $gepid;
			$statdia_array['lastname'] = ltrim($val["last_name"]);
			$statdia_array['firstname'] = ltrim($val["first_name"]);
			$statdia_array['admission_date'] = $admisiondate;
			$statdia_array['familydoctorphone'] = ltrim($Familydoctor_phone);
			$statdia_array['familydoctor'] = ltrim($Familydoctor_name);

			$sortarray1[] = $statdia_array;
			}

//		}
		$xlsRow = 1;

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('epid_num'));
			$this->xlsWriteLabel(0,2,$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('firstname'));
			$this->xlsWriteLabel(0,4,$this->view->translate('admissiondate'));
			$this->xlsWriteLabel(0,5,$this->view->translate('familydoctor'));
			$this->xlsWriteLabel(0,6,$this->view->translate('familydoctorphone'));

			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				/*if(strlen($val['patientdata'])<1)
				 {
				 continue;
				 }*/
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['epid_num']));
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['lastname']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['firstname']));
				$this->xlsWriteLabel($xlsRow,4,utf8_decode($valfile['admission_date']));
				$this->xlsWriteLabel($xlsRow,5,utf8_decode($valfile['familydoctor']));
				$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['familydoctorphone']));
				$xlsRow++;
			}

			$this->xlsEOF();

			$fileName = "Bielefeld_T1.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			//ob_clean();
			//flush();
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
			<tr>

			<th width="10%">'.$this->view->translate('EPID').'</th>
			<th width="10%">'.$this->view->translate('lastname').'</th>
			<th width="15%">'.$this->view->translate('firstname').'</th>
			<th width="15%">'.$this->view->translate('admissiondate').'</th>
			<th width="15%">'.$this->view->translate('familydoctor').'</th>
			<th width="15%">'.$this->view->translate('familydoctorphone').'</th>
			<tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			if(strlen($_POST["columname"])>0)
			{
				//$sortarray1 = Pms_DataTable::specialSort($sortarray1,$_POST["columname"]);
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row">

				<td  valign="top">'.$valfile['epid_num'].'&nbsp;</td><td  valign="top">'.$valfile['lastname'].'&nbsp;</td><td  valign="top">'.$valfile['firstname'].'&nbsp;</td><td  valign="top">'.$valfile['admission_date'].'&nbsp;</td> <td  valign="top">'.$valfile['familydoctor'].'&nbsp;</td><td valign="top">'.$valfile['familydoctorphone'].'&nbsp; </td></tr>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				//	echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}
	}





	private function betreuungszeit($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}
		$timeperiod = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
		$ipids = $this->getClientPatients($clientid, $whereepid);

		foreach($ipids as $ipid){
			$ipid_str .= '"'.$ipid['ipid'].'",';
		}

		$xlsRow = 1;

		$insertedtime = str_replace('%date%','create_date',$timeperiod['date_sql']).' ' ;
		$dateneeded = substr($insertedtime,4 );


		$ben = Doctrine_Query::create()
		->select("s.ipid, sum( s.gesamt_zeit_in_minuten ) AS total_time, sum(s.davon_fahrtzeit ) AS travel_time, sum( s.gesamt_zeit_in_minuten - s.davon_fahrtzeit) AS diff_time")
		->from('Sapsymptom s')
		->where(''.$dateneeded.'')
		->andWhere('ipid in ('.substr($ipid_str,0,-1).')')
		->groupBy('s.ipid')
		->orderBy('s.ipid');
		$sumtime = $ben->fetchArray();
		foreach($sumtime as $ipid){
			$ipidsum_str .= '"'.$ipid['ipid'].'",';
		}
		if(!empty($sumtime)){
			$actpatient = Doctrine_Query::create()
			->select("ipid,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name")
			->from('PatientMaster p')
			->where('ipid in ('.substr($ipidsum_str,0,-1).')')
			->orderBy('ipid');
			$actpatientarray = $actpatient->fetchArray();
		}


		foreach($sumtime as $sum){
			$sumtimeipid[$sum['ipid']] = $sum;
		}

		foreach($actpatientarray as $key=>$patient){
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$patient['ipid']);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];

			$actpatientarray[$key]['total_time'] = trim($sumtimeipid[$patient['ipid']]['total_time']);
			$actpatientarray[$key]['travel_time'] = trim($sumtimeipid[$patient['ipid']]['travel_time']);
			$actpatientarray[$key]['diff_time'] = trim($sumtimeipid[$patient['ipid']]['diff_time']);
			$actpatientarray[$key]['patientdata'] = $patient['first_name'].' '.$patient['last_name'];
			$actpatientarray[$key]['epid_num'] = $gepid;
		}


		$sortarray = $actpatientarray;

		foreach ($sortarray as $value){
			$totalsum +=$value['diff_time'];
		}

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('name'));
			$this->xlsWriteLabel(0,3,'Betreuungszeit (in Min.)');


			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray as $key=>$val)
			{

				$this->xlsWriteNumber($xlsRow,0,$key+1);
				$this->xlsWriteLabel($xlsRow,1,$val["epid_num"]);
				$this->xlsWriteLabel($xlsRow,2,$val["patientdata"]);
				$this->xlsWriteNumber($xlsRow,3,$val["diff_time"]);

				$xlsRow++;
			}
			$this->xlsWriteLabel($xlsRow,2,$this->view->translate('sum'));
			$this->xlsWriteNumber($xlsRow,3,$totalsum);

			$this->xlsEOF();

			$fileName = "Betreuungszeit_statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");

			header("Content-Disposition: attachment; filename=".$fileName);

			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">'.$this->view->translate('no').'</th>
									<th width="10%">'.$this->view->translate('EPID').'</th>
									<th width="10%">'.$this->view->translate('name').'</th>
									<th width="15%">Betreuungszeit (in Min.)</th></tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray as $key=>$val)
			{

				$data.= "<tr class='row'><td valign='top'>".$rowcount."</td><td valign='top'>".$val["epid_num"]."</td><td valign='top'>".$val["patientdata"]."</td><td valign='top'>".$val["diff_time"]."</td></tr>";
				$rowcount++;
			}
			$data.="<tr><td>&nbsp;</td><td>&nbsp;</td><td>Summe</td><td>".$totalsum."</td> </tr>";
			$data.="</table>";
			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;

				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;

				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}



	private function statstics($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}

		$active_cond = $this->getTimePeriod($quarterarr, $yeararr, $montharr);

		$count++;
		$noofpatients ="";
		$noofactpatients = "";
		$noofdispatients = "";
		$noofstandbypatients = "";
		$noofusers ="";


		if (!empty($montharr)){
		 foreach($montharr as $month){
				switch ($month) {
					case '1':
						$monthper[] = 'Januar '.$yeararr[0].' ';
						break;
					case '2':
						$monthper[] = 'Februar '.$yeararr[0].' ';
						break;
					case '3':
						$monthper[] = 'März '.$yeararr[0].' ';
						break;
					case '4':
						$monthper[] = 'April '.$yeararr[0].' ';
						break;
					case '5':
						$monthper[] = 'Mai '.$yeararr[0].' ';
						break;
					case '6':
						$monthper[] = 'Juni '.$yeararr[0].' ';
						break;
					case '7':
						$monthper[] = 'Juli '.$yeararr[0].' ';
						break;
					case '8':
						$monthper[] = 'August '.$yeararr[0].' ';
						break;
					case '9':
						$monthper[] = 'September '.$yeararr[0].' ';
						break;
					case '10':
						$monthper[] = 'Oktober '.$yeararr[0].' ';
						break;
					case '11':
						$monthper[] = 'November '.$yeararr[0].' ';
						break;
					case '12':
						$monthper[] = 'Dezember '.$yeararr[0].' ';
						break;


					default:
						$monthper[] = $yeararr[0];
						break;
				}
			}
			foreach($monthper as $key=>$val)
			{
				$periods_new .= ' '.$val.' <br/>';
			}
		}
		if (!empty($quarterarr)){
		 foreach($quarterarr as $month){
				switch ($month) {
					case '1':
						$monthper[] = 'Januar '.$yeararr[0].' &raquo; März '.$yeararr[0].' ';

						break;
					case '2':
						$monthper[] = 'April '.$yeararr[0].' &raquo; Juni '.$yeararr[0].' ';

						break;
					case '3':
						$monthper[] = 'Juli '.$yeararr[0].' &raquo;  September '.$yeararr[0].' ';
						break;
					case '4':
						$monthper[] = 'Oktober '.$yeararr[0].' &raquo; Dezember '.$yeararr[0].' ';

						break;
					default:
						$monthper[] = $yeararr[0];
						break;
				}
			}
			foreach($monthper as $key=>$val)
			{
				$periods_new .= ' '.$val.' <br/>';
			}
		}
	 if (empty($quarterarr) && empty($montharr) ){
	 	$monthper[] = $yeararr[0];
	 	foreach($monthper as $key=>$val)
	 	{
				$periods_new .= ' '.$val.' <br/>';
	 	}
	 }

		$actpatient = Doctrine_Query::create()
		->select("count(*)")
		->from('PatientMaster p')
		->where("isdelete = 0  ")
		//		->where("isdelete = 0 and isdischarged=0")
//		->andWhere('isstandby = 0 '.str_replace('%date%','admission_date',$active_cond['date_sql']).'')
		->andWhere('1 '.str_replace('%date%','admission_date',$active_cond['date_sql']).'')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
// 	echo $actpatient -> getSqlQuery();

		$actipidarray = $actpatient->fetchArray();

		$noofpatients = $actipidarray[0]['count'];


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where("isdelete = 0  and isdischarged=1")
//		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$ipidarray = $patient->fetchArray();

		foreach($ipidarray as $key=>$val)
		{
			$disipidval .= '"'.$val['ipid'].'",';
		}

		$disquery = Doctrine_Query::create()
		->select("count(*)")
		->from('PatientDischarge')
		->where('ipid in ('.substr($disipidval,0,-1).') '.str_replace('%date%','discharge_date',$active_cond['date_sql']).'');
		//    	echo  $disquery->getSqlQuery();
		$disarray = $disquery->fetchArray();

		$noofdispatients = $disarray[0]['count'];

		$activepatient	= $noofpatients;
		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,"Zeitraum");
			$this->xlsWriteLabel(0,1,"Aufnahmen");
			$this->xlsWriteLabel(0,2,$this->view->translate('overalldispatients'));
			//$this->xlsWriteLabel(0,3,$this->view->translate("treateddays"));
			$xlsRow=1;
			$this->xlsWriteLabel($xlsRow,0,$periods_new);
			$this->xlsWriteLabel($xlsRow,1,$activepatient);
			$this->xlsWriteNumber($xlsRow,2,$noofdispatients);
			//$this->xlsWriteNumber($xlsRow,3,$actnoofdays);
			$xlsRow++;
			$this->xlsEOF();

			$fileName = "statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");

			header("Content-Disposition: attachment; filename=".$fileName);

			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">Zeitraum</th>
									<th width="10%">Aufnahmen</th>
									<th width="15%">'.$this->view->translate('overalldispatients').'</th></tr>';

			//<th width="15%">'.$this->view->translate('treateddays').'</th><td valign='top'>".$actnoofdays."</td>
			$rowcount=1;

			$data.= "<tr class='row'><td valign='top'>".$periods_new."</td><td valign='top'>".$activepatient."</td><td valign='top'>".$noofdispatients."</td></tr>";
			$rowcount++;


			$data.="</table>";
			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;

				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;

				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}


	//	private function hospitalStats($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		if($logininfo->clientid>0)
	//		{
	//			$clientid=$logininfo->clientid;
	//		}else{
	//			$clientid=0;
	//		}
	//
	//		$where = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
	//		if(strlen($where)>0)
	//		{
	//			$where = " and (".$where.")";
	//		}else{
	//			$where="";
	//		}
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone")
	//		->from('PatientMaster p')
	//		->where('isdelete = 0 and isstandby=0 '.$where);
	//		//->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		//$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$patinfo = "";
	//			$patientphone = "";
	//			$epid = "";
	//			$daystreated ="";
	//			$dielocation = "";
	//			$hospitalcount=0;
	//			$hopitaldaystreated=0;
	//			$epid  = Pms_CommonData::getEpid($val["ipid"]);
	//			$epid_array = Pms_CommonData::getEpidcharsandNum($val["ipid"]);
	//
	//			if($val['birthd']!="0000-00-00"){
	//				$date = new Zend_Date($val['birthd']);
	//				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
	//				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
	//			}
	//
	//			$patinfo = trim($val["first_name"])." ".trim($val['last_name']).", ".trim($birthdatep).", ".trim($val['street1']).", ".trim($val['zip']).", ".trim($val['city']);
	//			$patientphone = trim($val['phone']);
	//
	//			if($val['isdischarged']==0)
	//			{
	//				if($val['admission_date']!="0000-00-00 00:00:00")
	//				{
	//					$admit = explode(" ",$val['admission_date']);
	//					$daystreated = ((int)((strtotime(date("Y-m-d"))-strtotime($admit[0]))/(24*60*60))+1);
	//				}
	//			}else{
	//
	//				$dispat = Doctrine_Query::create()
	//				->select("*")
	//				->from("PatientDischarge")
	//				->where("ipid ='".$val['ipid']."'");
	//				$dispatexec = $dispat->execute();
	//				$disipidarray = $dispatexec->toArray();
	//
	//				$split = explode(" ",$disipidarray[0]['discharge_date']);
	//				$bsplit = explode("-",$split[0]);
	//				$dischargedate = $bsplit[2].".".$bsplit[1].".".$bsplit[0];
	//				if($dischargedate=='00.00.0000'){$dischargedate="--";}
	//				$pms = new PatientMaster();
	//
	//				if($val['admission_date']!="0000-00-00 00:00:00")
	//				{
	//					$admit = explode(" ",$val['admission_date']);
	//					$daystreated = ((int)((strtotime($dischargedate)-strtotime($admit[0]))/(24*60*60))+1);
	//				}
	//
	//				if($disipidarray[0]['discharge_location']>0)
	//				{
	//					$dis = Doctrine_Query::create()
	//					->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
	//					->from('DischargeLocation')
	//					->where("clientid=".$logininfo->clientid." and id=".$disipidarray[0]['discharge_location']);
	//
	//					$disexec = $dis->execute();
	//					$disarray = $disexec->toArray();
	//					$dielocation = $disarray[0]['location'];
	//				}
	//
	//			}
	//
	//			$patlocation = new PatientLocation();
	//			$ploc = $patlocation->getpatientLocation($val['ipid']);
	//
	//			$location = new Locations();
	//			$locationtype = $location->getLocationTypes();
	//			if(is_array($ploc))
	//			{
	//				foreach($ploc as $key=>$val)
	//				{
	//
	//					$locaray = $location->getLocationbyId($val['location_id']);
	//					if($locaray[0]['location_type']==1)
	//					{
	//						$fromdate = explode(" ",$val['valid_from']);
	//						if($val['valid_till']!="0000-00-00 00:00:00")
	//						{
	//							$tilldate = explode(" ",$val['valid_till']);
	//						}else{
	//							$tilldate = explode(" ",date("Y-m-d H:i:s"));
	//						}
	//						$hopitaldaystreated += ((int)((strtotime($tilldate[0])-strtotime($fromdate[0]))/(24*60*60))+1);
	//
	//						$hospitalcount++;
	//					}
	//				}
	//			}
	//
	//			$treatedby ="";
	//			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
	//			$epidipidarray = $epidipid->toArray();
	//			$gepid = $epidipidarray[0]['epid'];
	//			if(strlen($gepid)>0)
	//			{
	//				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
	//				$treatarray = $treat->toArray();
	//				$user_id = $treatarray[0]['userid'];
	//				$uname = "";
	//				$br="";
	//				foreach($treatarray as $key=>$valtreat)
	//				{
	//
	//					$usr = Doctrine::getTable('User')->find($valtreat['userid']);
	//					if($usr)
	//					{
	//						$userarray = $usr->toArray();
	//
	//						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
	//						$br = ";";
	//					}
	//				}
	//			}
	//
	//			//echo $hospitalcount."<br>";
	//			$statdia_array = array();
	//
	//			$statdia_array['patientdata'] = $patinfo;
	//			$statdia_array['phone'] = $patientphone;
	//			$statdia_array['epid'] = ltrim($epid);
	//			$statdia_array['epid_num'] = ltrim($epid_array['num']);
	//			$statdia_array['trateddays'] = $daystreated;
	//			$statdia_array['dielocation'] = $dielocation;
	//			$statdia_array['locationhospitalcount'] = $hospitalcount;
	//			$statdia_array['hopitaldaystreated'] = $hopitaldaystreated;
	//			$statdia_array['treatedby'] = $treatedby;
	//
	//			$sortarray1[] = $statdia_array;
	//
	//		}
	//
	//		$xlsRow = 1;
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('epid'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('treateddays'));
	//			$this->xlsWriteLabel(0,4,"Anzahl KH Einweisungen");
	//			$this->xlsWriteLabel(0,5,"KH Tage");
	//			$this->xlsWriteLabel(0,6,"Sterbeort");
	//			$this->xlsWriteLabel(0,7,$this->view->translate('treatedby'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$i++;
	//				$this->xlsWriteNumber($xlsRow,0,"$i");
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
	//				$this->xlsWriteLabel($xlsRow,2,$valfile['epid']);
	//				$this->xlsWriteNumber($xlsRow,3,$valfile['trateddays']);
	//				$this->xlsWriteNumber($xlsRow,4,$valfile['locationhospitalcount']);
	//				$this->xlsWriteNumber($xlsRow,5,$valfile['hopitaldaystreated']);
	//				$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['dielocation']));
	//				$this->xlsWriteLabel($xlsRow,7,utf8_decode($valfile['treatedby']));
	//
	//				$xlsRow++;
	//				$trateddayscount+=$valfile['trateddays'];
	//				$locationhospitalcount+=$valfile['locationhospitalcount'];
	//				$hopitaldaystreatedcount+=$valfile['hopitaldaystreated'];
	//
	//			}
	//
	//			$this->xlsWriteLabel($xlsRow,2,$this->view->translate('sum')." / Durchschnitt");
	//			$this->xlsWriteLabel($xlsRow,3,$trateddayscount.' / '.number_format(($trateddayscount/($xlsRow-1)),2,".",""));
	//			$this->xlsWriteLabel($xlsRow,4,$locationhospitalcount.' / '.number_format(($locationhospitalcount/($xlsRow-1)),2,".",""));
	//			$this->xlsWriteLabel($xlsRow,5,$hopitaldaystreatedcount.' / '.number_format(($hopitaldaystreatedcount/($xlsRow-1)),2,".",""));
	//
	//
	//			$this->xlsEOF();
	//
	//			$fileName = "hospitalstats.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//
	//			exit;
	//		}else{
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
	//						<tr><th width="10%">'.$this->view->translate('no').'</th>
	//						<th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'</th>
	//						<th width="15%">'.$this->view->translate('epid').'</th>
	//						<th width="15%">'.$this->view->translate('treateddays').'</th>
	//						<th width="15%">Anzahl KH Einweisungen</th>
	//						<th width="15%">KH Tage</th>
	//						<th width="15%">Sterbeort</th>
	//						<th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
	//			$rowcount=1;
	//			//array_multisort($count,SORT_DESC,$sortarray1);
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$data.= '<tr class="row">
	//							<td valign="top">'.$rowcount.'</td>
	//							<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
	//							<td  valign="top">'.$valfile['epid'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['trateddays'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['locationhospitalcount'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['hopitaldaystreated'].'&nbsp; </td>
	//							<td valign="top">'.$valfile['dielocation'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['treatedby'].'&nbsp;</td></tr>';
	//				$rowcount++;
	//
	//				$trateddayscount+=$valfile['trateddays'];
	//				$locationhospitalcount+=$valfile['locationhospitalcount'];
	//				$hopitaldaystreatedcount+=$valfile['hopitaldaystreated'];
	//			}
	//			$data.= '<tr class="row">
	//							<td valign="top">&nbsp;</td>
	//							<td  valign="top">&nbsp;</td>
	//							<td  valign="top">Summe / Durchschnitt&nbsp;</td>
	//							<td valign="top">'.$trateddayscount.'&nbsp;/&nbsp;'.number_format(($trateddayscount/($rowcount-1)),2,".","").'</td>
	//							<td valign="top">'.$locationhospitalcount.'&nbsp;/&nbsp;'.number_format(($locationhospitalcount/($rowcount-1)),2,".","").'</td>
	//							<td valign="top">'.$hopitaldaystreatedcount.'&nbsp;/&nbsp;'.number_format(($hopitaldaystreatedcount/($rowcount-1)),2,".","").'</td>
	//							<td valign="top">&nbsp;</td>
	//							<td valign="top">&nbsp;</td></tr>';
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//
	//			}
	//		}
	//
	//	}

	private function hospitalStats($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}


		$active_cond = $this->getTimePeriod($quarterarr, $yeararr, $montharr);

		//		$where = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
		//		$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
		//		if(strlen($where)>0)
		//		{
		//			$where = " and (".$where.")";
		//		}else{
		//			$where="";
		//		}

		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone")
		->from('PatientMaster p')
		->where('isdelete = 0 and isstandby=0  '.str_replace('%date%','admission_date',$active_cond['date_sql']).'');
		//		->andWhere('(1 '.str_replace('%date%','admission_date',$active_cond['date_sql']).')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		//$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//echo $patient->getSqlQuery();

		//		$patientexec = $patient->execute();
		//		$ipidarray = $patientexec->toArray();

		$ipidarray = $patient->fetchArray();

		foreach($ipidarray as $key=>$val)
		{
			$patinfo = "";
			$patientphone = "";
			$epid = "";
			$daystreated ="";
			$dielocation = "";
			$hospitalcount=0;
			$hopitaldaystreated=0;
			$epid  = Pms_CommonData::getEpid($val["ipid"]);
			$epid_array = Pms_CommonData::getEpidcharsandNum($val["ipid"]);

			if($val['birthd']!="0000-00-00"){
				$date = new Zend_Date($val['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
			}

			$patinfo = trim($val["first_name"])." ".trim($val['last_name']).", ".trim($birthdatep).", ".trim($val['street1']).", ".trim($val['zip']).", ".trim($val['city']);
			$patientphone = trim($val['phone']);

			if($val['isdischarged']==0)
			{
				if($val['admission_date']!="0000-00-00 00:00:00")
				{
					$pms = new PatientMaster();
					$daystreated = $pms->getDaysDiff($val['admission_date'],date("Y-m-d"));
				}
			}else{

				$dispat = Doctrine_Query::create()
				->select("*")
				->from("PatientDischarge")
				->where("ipid ='".$val['ipid']."'");
				$dispatexec = $dispat->execute();
				$disipidarray = $dispatexec->toArray();

				$split = explode(" ",$disipidarray[0]['discharge_date']);
				$bsplit = explode("-",$split[0]);
				$dischargedate = $bsplit[2].".".$bsplit[1].".".$bsplit[0];
				if($dischargedate=='00.00.0000'){$dischargedate="--";}
				$pms = new PatientMaster();

				if($val['admission_date']!="0000-00-00 00:00:00")
				{
					$daystreated = $pms->getDaysDiff($val['admission_date'],$dischargedate);
				}

				if($disipidarray[0]['discharge_location']>0)
				{
					$dis = Doctrine_Query::create()
					->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
					->from('DischargeLocation')
					->where("clientid=".$logininfo->clientid." and id=".$disipidarray[0]['discharge_location']);

					$disexec = $dis->execute();
					$disarray = $disexec->toArray();
					$dielocation = $disarray[0]['location'];
				}

			}


			$patlocation = new PatientLocation();
			$ploc = $patlocation->getpatientLocation($val['ipid']);

			$location = new Locations();
			$locationtype = $location->getLocationTypes();
			if(is_array($ploc))
			{
				foreach($ploc as $key=>$val)
				{

					$locaray = $location->getLocationbyId($val['location_id']);
					if($locaray[0]['location_type']==1)
					{
						$fromdate = $val['valid_from'];
						if($val['valid_till']!="0000-00-00 00:00:00")
						{
							$tilldate = $val['valid_till'];
						}else{
							$tilldate = date("Y-m-d H:i:s");
						}
						$pms = new PatientMaster();
						$hopitaldaystreated += $pms->getDaysDiff($fromdate,$tilldate);

						$hospitalcount++;
						$reasonarr = array('0'=>'','1'=>'Notfall','2'=>'palliative Chemo/Radiatio','3'=>'unbekannt');
						$hospdocarr  = array('0'=>'','1'=>'teil.HA oder Facharzt','2'=>'QPA','3'=>'Notarzt','4'=>'unbekannt');
						$transportarr = array('0'=>'','1'=>'KTW','2'=>'RTW','3'=>'TAXI','4'=>'Privatwagen');

						$reason =  $reasonarr[$val['reason']];
						$hospdoc =  $hospdocarr[$val['hospdoc']];
						$transport =  $transportarr[$val['transport']];

					}
				}
			}

			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtreat)
				{

					$usr = Doctrine::getTable('User')->find($valtreat['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}

			$statdia_array = array();

			$statdia_array['patientdata'] = $patinfo;
			$statdia_array['phone'] = $patientphone;
			$statdia_array['epid'] = ltrim($epid);
			$statdia_array['epid_num'] = ltrim($epid_array['num']);
			$statdia_array['trateddays'] = $daystreated;
			$statdia_array['dielocation'] = $dielocation;
			$statdia_array['reason'] = $reason;
			$statdia_array['hospdoc'] = $hospdoc;
			$statdia_array['transport'] = $transport;

			$statdia_array['locationhospitalcount'] = $hospitalcount;
			$statdia_array['hopitaldaystreated'] = $hopitaldaystreated;
			$statdia_array['treatedby'] = $treatedby;

			$sortarray1[] = $statdia_array;

		}

		$xlsRow = 1;
		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,2,$this->view->translate('epid'));
			$this->xlsWriteLabel(0,3,$this->view->translate('treateddays'));
			$this->xlsWriteLabel(0,4,"Anzahl KH Einweisungen");
			$this->xlsWriteLabel(0,5,"KH Tage");
			$this->xlsWriteLabel(0,6,"Sterbeort");
			$this->xlsWriteLabel(0,7,"Grund der Aufnahme");
			$this->xlsWriteLabel(0,8,"Einweisender Arzt");
			$this->xlsWriteLabel(0,9,"Transportmittel");
			$this->xlsWriteLabel(0,10,$this->view->translate('treatedby'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,2,$valfile['epid']);
				$this->xlsWriteNumber($xlsRow,3,$valfile['trateddays']);
				$this->xlsWriteNumber($xlsRow,4,$valfile['locationhospitalcount']);
				$this->xlsWriteNumber($xlsRow,5,$valfile['hopitaldaystreated']);
				$this->xlsWriteLabel($xlsRow,6,utf8_decode($valfile['dielocation']));
				$this->xlsWriteLabel($xlsRow,7,utf8_decode($valfile['reason']));
				$this->xlsWriteLabel($xlsRow,8,utf8_decode($valfile['hospdoc']));
				$this->xlsWriteLabel($xlsRow,9,utf8_decode($valfile['transport']));
				$this->xlsWriteLabel($xlsRow,10,utf8_decode($valfile['treatedby']));

				$xlsRow++;


			}



			$this->xlsEOF();

			$fileName = "hospitalstats.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");

			header("Content-Disposition: attachment; filename=".$fileName);

			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
						<tr><th width="10%">'.$this->view->translate('no').'</th>
						<th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'</th>
						<th width="15%">'.$this->view->translate('epid').'</th>
						<th width="15%">'.$this->view->translate('treateddays').'</th>
						<th width="15%">Anzahl KH Einweisungen</th>
						<th width="15%">KH Tage</th>
						<th width="15%">Sterbeort</th>
						<th width="15%">Grund der Aufnahme</th>
						<th width="15%">Einweisender Arzt </th>
						<th width="15%">Transportmittel</th>
						<th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
			 if($valfile['locationhospitalcount'] !=0){
			 	$data.= '<tr class="row">
							<td valign="top">'.$rowcount.'</td>
							<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
							<td  valign="top">'.$valfile['epid'].'&nbsp;</td>
							<td valign="top">'.$valfile['trateddays'].'&nbsp;</td>
							<td valign="top">'.$valfile['locationhospitalcount'].'&nbsp;</td>
							<td valign="top">'.$valfile['hopitaldaystreated'].'&nbsp; </td>
							<td valign="top">'.$valfile['dielocation'].'&nbsp;</td>
							<td valign="top">'.$valfile['reason'].'&nbsp;</td>
							<td valign="top">'.$valfile['hospdoc'].'&nbsp;</td>
							<td valign="top">'.$valfile['transport'].'&nbsp;</td>
							<td valign="top">'.$valfile['treatedby'].'&nbsp;</td></tr>';
			 	$rowcount++;

			 }
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}

	}

	private function krankenhausnotarzt($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$finalipidval = $this->allactivepatiensArry($quarterarr, $yeararr, $montharr);


		foreach($finalipidval as $key=>$valipid)
		{

			$patinfo = "";
			$patientphone = "";
			$epid = "";
			$daystreated ="";
			$dielocation = "";
			$hospitalcount=0;
			$hopitaldaystreated=0;
			$epid  = Pms_CommonData::getEpid($valipid);
			$epid_array = Pms_CommonData::getEpidcharsandNum($valipid);

			$detpatient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
			->from('PatientMaster p')
			->where("ipid = '".$valipid."'")
			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
			$detailexec = $detpatient->execute();
			$detailarray = $detailexec->toArray();

			$birthdatep ="-";

			if($detailarray[0]['birthd']!="0000-00-00"){
				$date = new Zend_Date($detailarray[0]['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
			}

			$patloc = Doctrine_Query::create()
			->select('*')
			->from('PatientLocation')
			->where('ipid ="'.$valipid.'"  and isdelete = "0"')
			->orderBy('id DESC');

			$pattlocationarray = $patloc->fetchArray();
			//			if (is_array($pattlocationarray) && sizeof($pattlocationarray) > 0){
			$emergencydoc=0;
			foreach($pattlocationarray as $key=>$patvalue){
				if ($patvalue['hospdoc'] == "3" ){
					$emergencydoc++;
				}
			}
			//			}

			$patinfo = trim($detailarray[0]["first_name"])." ".trim($detailarray[0]['last_name']).", ".$birthdatep.", ".trim($detailarray[0]['street1']).", ".trim($detailarray[0]['zip']).", ".trim($detailarray[0]['city']);
			$patientphone = trim($val['phone']);
			$formonecount ="";
			$stamq = Doctrine_Query::create()
			->select('*')
			->from('Formone')
			->where("ipid='".$valipid."' and valid_till='0000-00-00 00:00:00'");
			$stexec = $stamq->execute();
			$stamqarr = $stexec->toArray();
			//			if(strlen($stamqarr[0]['notarzteinsatze'])>0)
			//			{
			//					$eplodeformone = explode(",",$stamqarr[0]['notarzteinsatze']);
			//					$formonecountdr = max($eplodeformone);
			//					switch ($formonecountdr) {
			//						case '1': $formonecount = "keine"; break;
			//						case '2': $formonecount = "1"; break;
			//						case '3': $formonecount = "2"; break;
			//						case '4': $formonecount = "3"; break;
			//						case '5': $formonecount = "4"; break;
			//						case '6': $formonecount = "5"; break;
			//						case '7': $formonecount = "> 5"; break;
			//						default: $formonecount = ""; break;
			//					}
			// 				}
			$formonecount = $emergencydoc;
			if ($formonecount == 0 ){
				$formonecount = "";
			}


			if($detailarray[0]['isdischarged']==0)
			{
				if($detailarray[0]['admission_date']!="0000-00-00 00:00:00")
				{
					$admit = explode(" ",$detailarray[0]['admission_date']);
					$daystreated = ((int)((strtotime(date("Y-m-d"))-strtotime($admit[0]))/(24*60*60))+1);
				}
			}else{
				$dispat = Doctrine_Query::create()
				->select("*")
				->from("PatientDischarge")
				->where("ipid ='".$valipid."'");
				$dispatexec = $dispat->execute();
				$disipidarray = $dispatexec->toArray();

				$split = explode(" ",$disipidarray[0]['discharge_date']);
				$bsplit = explode("-",$split[0]);
				$dischargedate = $bsplit[2].".".$bsplit[1].".".$bsplit[0];
				if($dischargedate=='00.00.0000'){$dischargedate="--";}
				$pms = new PatientMaster();

				if($detailarray[0]['admission_date']!="0000-00-00 00:00:00")
				{
					$admit = explode(" ",$detailarray[0]['admission_date']);
					$daystreated = ((int)((strtotime($dischargedate)-strtotime($admit[0]))/(24*60*60))+1);
				}

				if($disipidarray[0]['discharge_location']>0)
				{
					$dis = Doctrine_Query::create()
					->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
					->from('DischargeLocation')
					->where("clientid=".$logininfo->clientid." and id=".$disipidarray[0]['discharge_location']);

					$disexec = $dis->execute();
					$disarray = $disexec->toArray();
					$dielocation = $disarray[0]['location'];
				}
			}

			$patlocation = new PatientLocation();
			$ploc = $patlocation->getpatientLocation($valipid);

			$location = new Locations();
			$locationtype = $location->getLocationTypes();

			if(is_array($ploc))
			{
				foreach($ploc as $key=>$val)
				{
					$locaray = $location->getLocationbyId($val['location_id']);
					if($locaray[0]['location_type']==1)
					{
						$fromdate = explode(" ",$val['valid_from']);
						if($val['valid_till']!="0000-00-00 00:00:00")
						{
							$tilldate = explode(" ",$val['valid_till']);
						}else{
							$tilldate = explode(" ",date("Y-m-d H:i:s"));
						}
						$hopitaldaystreated += ((int)((strtotime($tilldate[0])-strtotime($fromdate[0]))/(24*60*60))+1);
						$hospitalcount++;
					}
				}

				//			$emergencydoc =$ploc[0]['ipid'].'   =   '.$ploc[0]['location_id'].'--->'.$ploc[0]['hospdoc'];
				//			echo  $emergencydoc.'<br/>';
				//
			}




			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtreat)
				{

					$usr = Doctrine::getTable('User')->find($valtreat['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}

			//echo $hospitalcount."<br>";
			$statdia_array = array();

			$statdia_array['patientdata'] = $patinfo;
			$statdia_array['phone'] = $patientphone;
			$statdia_array['epid'] = ltrim($epid);
			$statdia_array['epid_num'] = ltrim($epid_array['num']);
			$statdia_array['trateddays'] = $daystreated;
			$statdia_array['dielocation'] = $dielocation;
			$statdia_array['locationhospitalcount'] = $hospitalcount;
			$statdia_array['hopitaldaystreated'] = $hopitaldaystreated;
			$statdia_array['treatedby'] = $treatedby;
			$statdia_array['formonecount'] = $formonecount;

			$sortarray1[] = $statdia_array;

		}

		$xlsRow = 1;
		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,2,$this->view->translate('epid'));
			$this->xlsWriteLabel(0,3,$this->view->translate('treateddays'));
			$this->xlsWriteLabel(0,4,"Anzahl KH Einweisungen");
			$this->xlsWriteLabel(0,5,"KH Tage");
			$this->xlsWriteLabel(0,6,"Sterbeort");
			$this->xlsWriteLabel(0,7,"Notarzt");
			$this->xlsWriteLabel(0,8,$this->view->translate('treatedby'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
				$this->xlsWriteLabel($xlsRow,2,$valfile['epid']);
				$this->xlsWriteNumber($xlsRow,3,$valfile['trateddays']);
				$this->xlsWriteNumber($xlsRow,4,$valfile['locationhospitalcount']);
				$this->xlsWriteNumber($xlsRow,5,$valfile['hopitaldaystreated']);
				$this->xlsWriteLabel($xlsRow,6,$valfile['dielocation']);
				$this->xlsWriteNumber($xlsRow,7,$valfile['formonecount']);
				$this->xlsWriteLabel($xlsRow,8,utf8_decode($valfile['treatedby']));

				$trateddaystotxls +=$valfile['trateddays'];
				$locationhospitaltotxls+=$valfile['locationhospitalcount'];
				$hopitaldaystreatedtotxls+= $valfile['hopitaldaystreated'];
				$xlsRow++;
			}
			$this->xlsWriteLabel($xlsRow,2,$this->view->translate('sum').' / Durchschnitt');
			$this->xlsWriteLabel($xlsRow,3,$trateddaystotxls." / ".number_format(($trateddaystotxls/($xlsRow-1)),2,".",""));
			$this->xlsWriteLabel($xlsRow,4,$locationhospitaltotxls." / ".number_format(($locationhospitaltotxls/($xlsRow-1)),2,".",""));
			$this->xlsWriteLabel($xlsRow,5,$hopitaldaystreatedtotxls." / ".number_format(($hopitaldaystreatedtotxls/($xlsRow-1)),2,".",""));
			$this->xlsEOF();


			$fileName = "Krankenhaus_Notarzt.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");

			header("Content-Disposition: attachment; filename=".$fileName);

			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="100%">
						<tr><th width="10%">'.$this->view->translate('no').'</th>
						<th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'</th>
						<th width="15%">'.$this->view->translate('epid').'</th>
						<th width="15%">'.$this->view->translate('treateddays').'</th>
						<th width="15%">Anzahl KH Einweisungen</th>
						<th width="20%">KH Tage</th>
						<th width="15%">Sterbeort</th>
						<th width="15%">Notarzt</th>
						<th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				$data.= '<tr class="row">
							<td valign="top">'.$rowcount.'</td>
							<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
							<td  valign="top">'.$valfile['epid'].'&nbsp;</td>
							<td valign="top">'.$valfile['trateddays'].'&nbsp;</td>
							<td valign="top">'.$valfile['locationhospitalcount'].'&nbsp;</td>
							<td valign="top">'.$valfile['hopitaldaystreated'].'&nbsp; </td>
							<td valign="top">'.$valfile['dielocation'].'&nbsp;</td>
							<td valign="top">'.$valfile['formonecount'].'&nbsp;</td>
							<td valign="top">'.$valfile['treatedby'].'&nbsp;</td></tr>';

				$trateddaystot +=$valfile['trateddays'];
				$locationhospitaltot+=$valfile['locationhospitalcount'];
				$hopitaldaystreatedtot+= $valfile['hopitaldaystreated'];
				$rowcount++;
			}

			$data.= '<tr class="row">
							<td valign="top">&nbsp;</td>
							<td  valign="top">&nbsp;</td>
							<td  valign="top">'.$this->view->translate('sum').' / Durchschnitt &nbsp;</td>
							<td valign="top">'.$trateddaystot.'&nbsp;/&nbsp;'.number_format(($trateddaystot/($rowcount-1)),2,".","").' &nbsp;</td>
							<td valign="top">'.$locationhospitaltot.'&nbsp;/&nbsp;'.number_format(($locationhospitaltot/($rowcount-1)),2,".","").' &nbsp;</td>
							<td valign="top">'.$hopitaldaystreatedtot.'&nbsp;/&nbsp;'.number_format(($hopitaldaystreatedtot/($rowcount-1)),2,".","").' &nbsp; </td>
							<td valign="top">&nbsp;</td>
							<td valign="top">&nbsp;</td>
							<td valign="top">&nbsp;</td></tr>';
			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}

	}


	//	private function krankenhausnotarzt($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		if($logininfo->clientid>0)
	//		{
	//			$clientid=$logininfo->clientid;
	//		}else{
	//			$clientid=0;
	//		}
	//
	//		$where = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$wheredischargedate = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$finalipidval = array();
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval[]=$val['ipid'];
	//		}
	//
	//		//print_r($finalipidval);
	//
	//		foreach($finalipidval as $key=>$valipid)
	//		{
	//
	//			$patinfo = "";
	//			$patientphone = "";
	//			$epid = "";
	//			$daystreated ="";
	//			$dielocation = "";
	//			$hospitalcount=0;
	//			$hopitaldaystreated=0;
	//			$epid  = Pms_CommonData::getEpid($valipid);
	//			$epid_array = Pms_CommonData::getEpidcharsandNum($valipid);
	//
	//			$detpatient = Doctrine_Query::create()
	//			->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//			->from('PatientMaster p')
	//			->where("ipid = '".$valipid."'")
	//			->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//			$detailexec = $detpatient->execute();
	//			$detailarray = $detailexec->toArray();
	//
	//			$birthdatep ="-";
	//
	//			if($detailarray[0]['birthd']!="0000-00-00"){
	//				$date = new Zend_Date($detailarray[0]['birthd']);
	//				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
	//				//$birthdatep = date("d.m.Y",strtotime($detailarray[0]['birthd']));
	//			}
	//
	//			$patinfo = trim($detailarray[0]["first_name"])." ".trim($detailarray[0]['last_name']).", ".$birthdatep.", ".trim($detailarray[0]['street1']).", ".trim($detailarray[0]['zip']).", ".trim($detailarray[0]['city']);
	//			$patientphone = trim($val['phone']);
	//			$formonecount ="";
	//			$stamq = Doctrine_Query::create()
	//			->select('*')
	//			->from('Formone')
	//			->where("ipid='".$valipid."' and valid_till='0000-00-00 00:00:00'");
	//			$stexec = $stamq->execute();
	//			$stamqarr = $stexec->toArray();
	//			if(strlen($stamqarr[0]['notarzteinsatze'])>0)
	//			{
	//				$eplodeformone = explode(",",$stamqarr[0]['notarzteinsatze']);
	//				$formonecount = count($eplodeformone);
	//			}
	//
	//			if($detailarray[0]['isdischarged']==0)
	//			{
	//				if($detailarray[0]['admission_date']!="0000-00-00 00:00:00")
	//				{
	//					$admit = explode(" ",$detailarray[0]['admission_date']);
	//					$daystreated = ((int)((strtotime(date("Y-m-d"))-strtotime($admit[0]))/(24*60*60))+1);
	//				}
	//			}else{
	//				$dispat = Doctrine_Query::create()
	//				->select("*")
	//				->from("PatientDischarge")
	//				->where("ipid ='".$valipid."'");
	//				$dispatexec = $dispat->execute();
	//				$disipidarray = $dispatexec->toArray();
	//
	//				$split = explode(" ",$disipidarray[0]['discharge_date']);
	//				$bsplit = explode("-",$split[0]);
	//				$dischargedate = $bsplit[2].".".$bsplit[1].".".$bsplit[0];
	//				if($dischargedate=='00.00.0000'){$dischargedate="--";}
	//				$pms = new PatientMaster();
	//
	//				if($detailarray[0]['admission_date']!="0000-00-00 00:00:00")
	//				{
	//					$admit = explode(" ",$detailarray[0]['admission_date']);
	//					$daystreated = ((int)((strtotime($dischargedate)-strtotime($admit[0]))/(24*60*60))+1);
	//				}
	//
	//				if($disipidarray[0]['discharge_location']>0)
	//				{
	//					$dis = Doctrine_Query::create()
	//					->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
	//					->from('DischargeLocation')
	//					->where("clientid=".$logininfo->clientid." and id=".$disipidarray[0]['discharge_location']);
	//
	//					$disexec = $dis->execute();
	//					$disarray = $disexec->toArray();
	//					$dielocation = $disarray[0]['location'];
	//				}
	//			}
	//
	//			$patlocation = new PatientLocation();
	//			$ploc = $patlocation->getpatientLocation($valipid);
	//
	//			$location = new Locations();
	//			$locationtype = $location->getLocationTypes();
	//			if(is_array($ploc))
	//			{
	//				foreach($ploc as $key=>$val)
	//				{
	//					$locaray = $location->getLocationbyId($val['location_id']);
	//					if($locaray[0]['location_type']==1)
	//					{
	//						$fromdate = explode(" ",$val['valid_from']);
	//						if($val['valid_till']!="0000-00-00 00:00:00")
	//						{
	//							$tilldate = explode(" ",$val['valid_till']);
	//						}else{
	//							$tilldate = explode(" ",date("Y-m-d H:i:s"));
	//						}
	//						$hopitaldaystreated += ((int)((strtotime($tilldate[0])-strtotime($fromdate[0]))/(24*60*60))+1);
	//						$hospitalcount++;
	//					}
	//				}
	//			}
	//
	//			$treatedby ="";
	//			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$valipid);
	//			$epidipidarray = $epidipid->toArray();
	//			$gepid = $epidipidarray[0]['epid'];
	//			if(strlen($gepid)>0)
	//			{
	//				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
	//				$treatarray = $treat->toArray();
	//				$user_id = $treatarray[0]['userid'];
	//				$uname = "";
	//				$br="";
	//				foreach($treatarray as $key=>$valtreat)
	//				{
	//
	//					$usr = Doctrine::getTable('User')->find($valtreat['userid']);
	//					if($usr)
	//					{
	//						$userarray = $usr->toArray();
	//
	//						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
	//						$br = ";";
	//					}
	//				}
	//			}
	//
	//			//echo $hospitalcount."<br>";
	//			$statdia_array = array();
	//
	//			$statdia_array['patientdata'] = $patinfo;
	//			$statdia_array['phone'] = $patientphone;
	//			$statdia_array['epid'] = ltrim($epid);
	//			$statdia_array['epid_num'] = ltrim($epid_array['num']);
	//			$statdia_array['trateddays'] = $daystreated;
	//			$statdia_array['dielocation'] = $dielocation;
	//			$statdia_array['locationhospitalcount'] = $hospitalcount;
	//			$statdia_array['hopitaldaystreated'] = $hopitaldaystreated;
	//			$statdia_array['treatedby'] = $treatedby;
	//			$statdia_array['formonecount'] = $formonecount;
	//
	//			$sortarray1[] = $statdia_array;
	//
	//		}
	//
	//		$xlsRow = 1;
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,$this->view->translate('firstname').", ".$this->view->translate('lastname'));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('epid'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('treateddays'));
	//			$this->xlsWriteLabel(0,4,"Anzahl KH Einweisungen");
	//			$this->xlsWriteLabel(0,5,"KH Tage");
	//			$this->xlsWriteLabel(0,6,"Sterbeort");
	//			$this->xlsWriteLabel(0,7,"Notarzt");
	//			$this->xlsWriteLabel(0,8,$this->view->translate('treatedby'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$i++;
	//				$this->xlsWriteNumber($xlsRow,0,"$i");
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($valfile['patientdata']));
	//				$this->xlsWriteLabel($xlsRow,2,$valfile['epid']);
	//				$this->xlsWriteNumber($xlsRow,3,$valfile['trateddays']);
	//				$this->xlsWriteNumber($xlsRow,4,$valfile['locationhospitalcount']);
	//				$this->xlsWriteNumber($xlsRow,5,$valfile['hopitaldaystreated']);
	//				$this->xlsWriteLabel($xlsRow,6,$valfile['dielocation']);
	//				$this->xlsWriteNumber($xlsRow,7,$valfile['formonecount']);
	//				$this->xlsWriteLabel($xlsRow,8,utf8_decode($valfile['treatedby']));
	//
	//				$trateddaystotxls +=$valfile['trateddays'];
	//				$locationhospitaltotxls+=$valfile['locationhospitalcount'];
	//				$hopitaldaystreatedtotxls+= $valfile['hopitaldaystreated'];
	//				$xlsRow++;
	//			}
	//			$this->xlsWriteLabel($xlsRow,2,$this->view->translate('sum').' / Durchschnitt');
	//			$this->xlsWriteLabel($xlsRow,3,$trateddaystotxls." / ".number_format(($trateddaystotxls/($xlsRow-1)),2,".",""));
	//			$this->xlsWriteLabel($xlsRow,4,$locationhospitaltotxls." / ".number_format(($locationhospitaltotxls/($xlsRow-1)),2,".",""));
	//			$this->xlsWriteLabel($xlsRow,5,$hopitaldaystreatedtotxls." / ".number_format(($hopitaldaystreatedtotxls/($xlsRow-1)),2,".",""));
	//			$this->xlsEOF();
	//
	//
	//			$fileName = "Krankenhaus_Notarzt.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//
	//			exit;
	//		}else{
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="100%">
	//						<tr><th width="10%">'.$this->view->translate('no').'</th>
	//						<th width="10%">'.$this->view->translate('firstname')." ".$this->view->translate('lastname').'</th>
	//						<th width="15%">'.$this->view->translate('epid').'</th>
	//						<th width="15%">'.$this->view->translate('treateddays').'</th>
	//						<th width="15%">Anzahl KH Einweisungen</th>
	//						<th width="20%">KH Tage</th>
	//						<th width="15%">Sterbeort</th>
	//						<th width="15%">Notarzt</th>
	//						<th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
	//			$rowcount=1;
	//			//array_multisort($count,SORT_DESC,$sortarray1);
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$valfile)
	//			{
	//
	//				$data.= '<tr class="row">
	//							<td valign="top">'.$rowcount.'</td>
	//							<td  valign="top">'.$valfile['patientdata'].'&nbsp;</td>
	//							<td  valign="top">'.$valfile['epid'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['trateddays'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['locationhospitalcount'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['hopitaldaystreated'].'&nbsp; </td>
	//							<td valign="top">'.$valfile['dielocation'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['formonecount'].'&nbsp;</td>
	//							<td valign="top">'.$valfile['treatedby'].'&nbsp;</td></tr>';
	//
	//				$trateddaystot +=$valfile['trateddays'];
	//				$locationhospitaltot+=$valfile['locationhospitalcount'];
	//				$hopitaldaystreatedtot+= $valfile['hopitaldaystreated'];
	//				$rowcount++;
	//			}
	//
	//			$data.= '<tr class="row">
	//							<td valign="top">&nbsp;</td>
	//							<td  valign="top">&nbsp;</td>
	//							<td  valign="top">'.$this->view->translate('sum').' / Durchschnitt &nbsp;</td>
	//							<td valign="top">'.$trateddaystot.'&nbsp;/&nbsp;'.number_format(($trateddaystot/($rowcount-1)),2,".","").' &nbsp;</td>
	//							<td valign="top">'.$locationhospitaltot.'&nbsp;/&nbsp;'.number_format(($locationhospitaltot/($rowcount-1)),2,".","").' &nbsp;</td>
	//							<td valign="top">'.$hopitaldaystreatedtot.'&nbsp;/&nbsp;'.number_format(($hopitaldaystreatedtot/($rowcount-1)),2,".","").' &nbsp; </td>
	//							<td valign="top">&nbsp;</td>
	//							<td valign="top">&nbsp;</td>
	//							<td valign="top">&nbsp;</td></tr>';
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//
	//				echo $data;
	//				echo "<SCRIPT type='text/javascript'>";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//
	//			}
	//		}
	//
	//	}
	//
	//

	private function cdspecialStats($radioarr,$montharr,$quarterarr,$yeararr)
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}


		$time_period = $this->getTimePeriod($quarterarr, $yeararr, $montharr) ;

		$actwhere = "";
		$where = "";
		$actwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
		$where = $this->getCourseQuarterCondition($quarterarr,$yeararr,$montharr);

		if(strlen($actwhere)>0)
		{
			$actwhere = " and (".$actwhere.")";
		}else{
			$actwhere="";
		}

		if(strlen($where)>0)
		{
			$where = " and (".$where.")";
		}else{
			$where="";
		}



		$ugroup = new Usergroup();
		$cordgroupid = $ugroup->getCordinatorGroupid($clientid);
		$doctorgroupid = $ugroup->getDoctorGroupid($clientid);

		$statdia_array = array();


		$usermod = new User();
		$docuserarray = $usermod->getuserbyGroupId($doctorgroupid,$clientid);
		$coruserarray = $usermod->getuserbyGroupId($cordgroupid,$clientid);

		$fromtilldates = $this->getQuarterperiods($quarterarr,$yeararr,$montharr);
		list($fromdate,$tilldate) = explode("-",$fromtilldates);

		if(is_array($docuserarray))
		{
			$pc = new PatientCourse();

			foreach($docuserarray as $key=>$val)
			{
				$coursearray = $pc->getCourseDataForSpecialreport($val['id'],$where);
				$statdia_array = array();
				$statdia_array['userdata'] = $val['last_name']." ".$val['first_name'];
				$hbcount = 0;
				$t1count = 0;
				$tpcount = 0;
				$tfcount = 0;
				foreach($coursearray as $keyc=>$valc)
				{
					$specialtext ="";
					$specialtext = trim(substr($valc['course_title'],0,3));
					switch($specialtext)
					{
						case hb : $hbcount++; $sumhb+=$hb; break;
						case t1 : $t1count++; $sumt1+=$t1; break;
						case tp : $tpcount++; $sumtp+=$tp; break;
						case tf : $tfcount++; $sumtf+=$tf; break;
					}


				}
				$statdia_array['type']='doctor';
				$statdia_array['hb']=$hbcount;
				$statdia_array['t1']=$t1count;
				$statdia_array['tp']=$tpcount;
				$statdia_array['tf']=$tfcount;

				$sortarray1[] = $statdia_array;
			}
		}
		$patientqury = Doctrine_Query::create()
		->select('count(*)')
		->from('PatientMaster p')
//		->where('isdelete = 0 '.$actwhere);
		->where('isdelete = 0 and isstandbydelete = 0 '.str_replace('%date%','admission_date',$time_period['date_sql']).'  ');
		$patientqury->leftJoin("p.EpidIpidMapping e");
		$patientqury->andWhere($whereepid.' e.clientid = '.$clientid);
		$patexec = $patientqury->execute();
		$patientcount = $patexec->toArray();
		$noofpatients = $patientcount[0]['count'];
		$patientqury->getSqlQuery();

		if(is_array($coruserarray))
		{

			$pc = new PatientCourse();
			foreach($coruserarray as $key=>$val)
			{
				$statdia_array = array();
				$statdia_array['userdata'] = $val['last_name']." ".$val['first_name'];
				$coursearray = $pc->getCourseDataForSpecialreport($val['id'],$where);
				$hb = 0;
				$t1 = 0;
				$tp = 0;
				$tf = 0;
				foreach($coursearray as $keyc=>$valc)
				{
					$specialtext ="";
					$specialtext = substr($valc['course_title'],0,2);
					switch($specialtext)
					{
						case hb : $hb++; $sumhb+=$hb; break;
						case t1 : $t1++; $sumt1+=$t1; break;
						case tp : $tp++; $sumtp+=$tp; break;
						case tf : $tf++; $sumtf+=$tf; break;
					}
				}
				$statdia_array['hb'] = $hb;
				$statdia_array['t1'] = $t1;
				$statdia_array['tp'] = $tp;
				$statdia_array['tf'] = $tf;
				$statdia_array['sumhb'] = $hb;
				$statdia_array['sumt1'] = $t1;
				$statdia_array['sumtp'] = $tp;
				$statdia_array['sumtf'] = $tf;
				$statdia_array['type']='cordinator';
				$sortarray1[] = $statdia_array;
			}

		}


		$xlsRow = 4;
		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();


			$this->xlsWriteLabel(0,0,"PKD-Statistik");
			$this->xlsWriteLabel(0,1,"Abrechnungszeitraum");
			$this->xlsWriteLabel(0,2,$fromdate);
			$this->xlsWriteLabel(0,3,$tilldate);
			$this->xlsWriteLabel(0,4,date("d.m.Y"));

			$this->xlsWriteLabel(2,0,"Anzahl der eingeschriebenen Pat.");
			$this->xlsWriteNumber(2,1,$noofpatients);

			$this->xlsWriteLabel(3,0,"QPA-Name");
			$this->xlsWriteLabel(3,1,"Initialtelefonat (t1)");
			$this->xlsWriteLabel(3,2,"Telefonat mit Prof. (tp)");
			$this->xlsWriteLabel(3,3,"Telefonat mit Fam. (tf)");
			$this->xlsWriteLabel(3,4,"Hausbesuch (hb)");





			foreach($sortarray1 as $key=>$valfile)
			{
				$i++;
				//$this->xlsWriteNumber($xlsRow,0,"$i")
				if($valfile['type']=='doctor')
				{
					$this->xlsWriteLabel($xlsRow,0,utf8_decode($valfile['userdata']));
					$this->xlsWriteNumber($xlsRow,1,$valfile['t1']);
					$this->xlsWriteNumber($xlsRow,2,$valfile['tp']);
					$this->xlsWriteNumber($xlsRow,3,$valfile['tf']);
					$this->xlsWriteNumber($xlsRow,4,$valfile['hb']);


					$rowcount++;
					$allhb += $valfile['hb'];
					$allt1 += $valfile['t1'];
					$alltp +=$valfile['tp'];
					$alltf +=$valfile['tf'];
				}
				$xlsRow++;
			}
			$this->xlsWriteLabel($xlsRow-3,0,"Summe");
			$this->xlsWriteNumber($xlsRow-3,1,$allt1);
			$this->xlsWriteNumber($xlsRow-3,2,$alltp);
			$this->xlsWriteNumber($xlsRow-3,3,$alltf);
			$this->xlsWriteNumber($xlsRow-3,4,$allhb);

			$this->xlsWriteLabel($xlsRow-2,0,"Aktion pro Patient");
			$this->xlsWriteNumber($xlsRow-2,1,number_format(($allt1/$noofpatients),2,".",""));
			$this->xlsWriteNumber($xlsRow-2,2,number_format(($alltp/$noofpatients),2,".",""));
			$this->xlsWriteNumber($xlsRow-2,3,number_format(($alltf/$noofpatients),2,".",""));
			$this->xlsWriteNumber($xlsRow-2,4,number_format(($allhb/$noofpatients),2,".",""));

			$this->xlsWriteLabel($xlsRow,0,"KO-Statistik");
			$this->xlsWriteLabel($xlsRow,1,"Abrechnungszeitraum");
			$this->xlsWriteLabel($xlsRow,2,$fromdate);
			$this->xlsWriteLabel($xlsRow,3,$tilldate);
			$this->xlsWriteLabel($xlsRow,4,date("d.m.Y"));

			$this->xlsWriteLabel($xlsRow+1,0,"Koordinator-Name");
			$this->xlsWriteLabel($xlsRow+1,1,"Initialtelefonat (t1)");
			$this->xlsWriteLabel($xlsRow+1,2,"Telefonat mit Prof. (tp)");
			$this->xlsWriteLabel($xlsRow+1,3,"Telefonat mit Fam. (tf)");
			$this->xlsWriteLabel($xlsRow+1,4,"Hausbesuch (hb)");

			//$rowcount=1;
			$sexlsRow=$xlsRow-5;
			$allhb = 0;
			$allt1 = 0;
			$alltp =0;
			$alltf =0;
			foreach($sortarray1 as $key=>$valfile)
			{
				if($valfile['type']=='cordinator')
				{
					$this->xlsWriteLabel($sexlsRow,0,utf8_decode($valfile['userdata']));
					$this->xlsWriteNumber($sexlsRow,1,$valfile['t1']);
					$this->xlsWriteNumber($sexlsRow,2,$valfile['tp']);
					$this->xlsWriteNumber($sexlsRow,3,$valfile['tf']);
					$this->xlsWriteNumber($sexlsRow,4,$valfile['hb']);

					$rowcount++;
					$allhb += $valfile['hb'];
					$allt1 += $valfile['t1'];
					$alltp +=$valfile['tp'];
					$alltf +=$valfile['tf'];
				}
				$sexlsRow++;
			}

			$this->xlsWriteLabel($sexlsRow,0,"Summe");
			$this->xlsWriteNumber($sexlsRow,1,$allt1);
			$this->xlsWriteNumber($sexlsRow,2,$alltp);
			$this->xlsWriteNumber($sexlsRow,3,$alltf);
			$this->xlsWriteNumber($sexlsRow,4,$allhb);

			$this->xlsWriteLabel($sexlsRow+1,0,"Aktion pro Patient");
			$this->xlsWriteNumber($sexlsRow+1,1,number_format(($allt1/$noofpatients),2,".",""));
			$this->xlsWriteNumber($sexlsRow+1,2,number_format(($alltp/$noofpatients),2,".",""));
			$this->xlsWriteNumber($sexlsRow+1,3,number_format(($alltf/$noofpatients),2,".",""));
			$this->xlsWriteNumber($sexlsRow+1,4,number_format(($allhb/$noofpatients),2,".",""));

			$this->xlsEOF();

			$fileName = "CD_special_stats.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");

			header("Content-Disposition: attachment; filename=".$fileName);

			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
						 <tr><th>PKD-Statistik</th>
						 <td colspan="3">Abrechnungszeitraum : '.$fromdate.' - '.$tilldate.'</td>
						 <th>'.date("d.m.Y").'</th></tr>
						  <tr><td colspan="10">Anzahl der eingeschriebenen Pat. : <b>'.$noofpatients.'</b></td></tr>
						  <tr> <th>QPA-Name</th>
						  <th>Initialtelefonat (t1)</th>
						  <th>Telefonat mit Prof. (tp)</th>
						  <th>Telefonat mit Fam. (tf)</th>
						  <th>Hausbesuch (hb)</th></tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);

			$allhb = 0;
			$allt1 = 0;
			$alltp =0;
			$alltf =0;
			foreach($sortarray1 as $key=>$valfile)
			{
				if($valfile['type']=='doctor')
				{
					$data.= '<tr><td>'.$valfile['userdata'].'&nbsp;</td>
									 <td align="center">'.$valfile['t1'].'&nbsp;</td>
									 <td align="center">'.$valfile['tp'].'&nbsp;</td>
									 <td align="center">'.$valfile['tf'].'&nbsp;</td>
									 <td align="center">'.$valfile['hb'].'&nbsp;</td></tr>';

					$rowcount++;
					$allhb += $valfile['hb'];
					$allt1 += $valfile['t1'];
					$alltp +=$valfile['tp'];
					$alltf +=$valfile['tf'];

				}
			}
			$data.= '<tr><td><b>Summe</b>&nbsp;</td>
										 <td align="center"><b>'.$allt1.'</b>&nbsp;</td>
										 <td align="center"><b>'.$alltp.'</b>&nbsp;</td>
										 <td align="center"><b>'.$alltf.'</b>&nbsp;</td>
										 <td align="center"><b>'.$allhb.'</b>&nbsp;</td></tr>
										 <tr><td><b>Aktion pro Patient</b>&nbsp;</td>
										 <td align="center"><b>'.number_format(($allt1/$noofpatients),2,".","").'</b>&nbsp;</td>
										 <td align="center"><b>'.number_format(($alltp/$noofpatients),2,".","").'</b>&nbsp;</td>
										 <td align="center"><b>'.number_format(($alltf/$noofpatients),2,".","").'</b>&nbsp;</td>
										 <td align="center"><b>'.number_format(($allhb/$noofpatients),2,".","").'</b>&nbsp;</td></tr>';
			$data.="</table><br /><br /><br />";

			$data .='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
						 <tr><th>KO-Statistik</th>
						 <td colspan="3">Abrechnungszeitraum : '.$fromdate.' - '.$tilldate.'</td>
						 <th>'.date("d.m.Y").'</th></tr><tr>
						  <th>Koordinator-Name</th>
						  <th>Initialtelefonat (t1)</th>
						  <th>Telefonat mit Prof. (tp)</th>
						  <th>Telefonat mit Fam. (tf)</th>
						  <th>Hausbesuch (hb)</th></tr>';
			$rowcount=1;
			//array_multisort($count,SORT_DESC,$sortarray1);
			$allhb = 0;
			$allt1 = 0;
			$alltp =0;
			$alltf =0;
			foreach($sortarray1 as $key=>$valfile)
			{
				if($valfile['type']=='cordinator')
				{
					$data.= '<tr><td>'.$valfile['userdata'].'&nbsp;</td>
									 <td align="center">'.$valfile['t1'].'&nbsp;</td>
									 <td align="center">'.$valfile['tp'].'&nbsp;</td>
									 <td align="center">'.$valfile['tf'].'&nbsp;</td>
									 <td align="center">'.$valfile['hb'].'&nbsp;</td></tr>';

					$rowcount++;
					$allhb += $valfile['hb'];
					$allt1 += $valfile['t1'];
					$alltp +=$valfile['tp'];
					$alltf +=$valfile['tf'];
				}
			}


			$data.= '<tr><td><b>Summe</b>&nbsp;</td>
									 <td align="center"><b>'.$allt1.'</b>&nbsp;</td>
									 <td align="center"><b>'.$alltp.'</b>&nbsp;</td>
									 <td align="center"><b>'.$alltf.'</b>&nbsp;</td>
									 <td align="center"><b>'.$allhb.'</b>&nbsp;</td></tr>
									 <tr><td><b>Aktion pro Patient</b>&nbsp;</td>
									 <td align="center"><b>'.number_format(($allt1/$noofpatients),2,".","").'</b>&nbsp;</td>
									 <td align="center"><b>'.number_format(($alltp/$noofpatients),2,".","").'</b>&nbsp;</td>
									 <td align="center"><b>'.number_format(($alltf/$noofpatients),2,".","").'</b>&nbsp;</td>
									 <td align="center"><b>'.number_format(($allhb/$noofpatients),2,".","").'</b>&nbsp;</td></tr>';
			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}

	}
//	private function cdspecialStats($radioarr,$montharr,$quarterarr,$yeararr)
//	{
//
//		$logininfo= new Zend_Session_Namespace('Login_Info');
//		$whereepid = $this->getDocCondition();
//		if($logininfo->clientid>0)
//		{
//			$clientid=$logininfo->clientid;
//		}else{
//			$clientid=0;
//		}
//		$actwhere = "";
//		$where = "";
//		$actwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
//		$where = $this->getCourseQuarterCondition($quarterarr,$yeararr,$montharr);
//
//		if(strlen($actwhere)>0)
//		{
//			$actwhere = " and (".$actwhere.")";
//		}else{
//			$actwhere="";
//		}
//
//		if(strlen($where)>0)
//		{
//			$where = " and (".$where.")";
//		}else{
//			$where="";
//		}
//		$ugroup = new Usergroup();
//		$cordgroupid = $ugroup->getCordinatorGroupid($clientid);
//		$doctorgroupid = $ugroup->getDoctorGroupid($clientid);
//
//		$statdia_array = array();
//
//
//		$usermod = new User();
//		$docuserarray = $usermod->getuserbyGroupId($doctorgroupid,$clientid);
//		$coruserarray = $usermod->getuserbyGroupId($cordgroupid,$clientid);
//
//		$fromtilldates = $this->getQuarterperiods($quarterarr,$yeararr,$montharr);
//		list($fromdate,$tilldate) = explode("-",$fromtilldates);
//		if(is_array($docuserarray))
//		{
//			$pc = new PatientCourse();
//
//			foreach($docuserarray as $key=>$val)
//			{
//				$coursearray = $pc->getCourseDataForSpecialreport($val['id'],$where);
//				$statdia_array = array();
//				$statdia_array['userdata'] = $val['last_name']." ".$val['first_name'];
//				$hbcount = 0;
//				$t1count = 0;
//				$tpcount = 0;
//				$tfcount = 0;
//				foreach($coursearray as $keyc=>$valc)
//				{
//					$specialtext ="";
//					$specialtext = trim(substr($valc['course_title'],0,3));
//					switch($specialtext)
//					{
//						case hb : $hbcount++; $sumhb+=$hb; break;
//						case t1 : $t1count++; $sumt1+=$t1; break;
//						case tp : $tpcount++; $sumtp+=$tp; break;
//						case tf : $tfcount++; $sumtf+=$tf; break;
//					}
//
//
//				}
//				$statdia_array['type']='doctor';
//				$statdia_array['hb']=$hbcount;
//				$statdia_array['t1']=$t1count;
//				$statdia_array['tp']=$tpcount;
//				$statdia_array['tf']=$tfcount;
//
//				$sortarray1[] = $statdia_array;
//			}
//		}
//		$patientqury = Doctrine_Query::create()
//		->select('count(*)')
//		->from('PatientMaster p')
//		->where('isdelete = 0 '.$actwhere);
//		$patientqury->leftJoin("p.EpidIpidMapping e");
//		$patientqury->andWhere($whereepid.' e.clientid = '.$clientid);
//		$patexec = $patientqury->execute();
//		$patientcount = $patexec->toArray();
//		$noofpatients = $patientcount[0]['count'];
//		$patientqury->getSqlQuery();
//
//		if(is_array($coruserarray))
//		{
//
//			$pc = new PatientCourse();
//			foreach($coruserarray as $key=>$val)
//			{
//				$statdia_array = array();
//				$statdia_array['userdata'] = $val['last_name']." ".$val['first_name'];
//				$coursearray = $pc->getCourseDataForSpecialreport($val['id'],$where);
//				$hb = 0;
//				$t1 = 0;
//				$tp = 0;
//				$tf = 0;
//				foreach($coursearray as $keyc=>$valc)
//				{
//					$specialtext ="";
//					$specialtext = substr($valc['course_title'],0,2);
//					switch($specialtext)
//					{
//						case hb : $hb++; $sumhb+=$hb; break;
//						case t1 : $t1++; $sumt1+=$t1; break;
//						case tp : $tp++; $sumtp+=$tp; break;
//						case tf : $tf++; $sumtf+=$tf; break;
//					}
//				}
//				$statdia_array['hb'] = $hb;
//				$statdia_array['t1'] = $t1;
//				$statdia_array['tp'] = $tp;
//				$statdia_array['tf'] = $tf;
//				$statdia_array['sumhb'] = $hb;
//				$statdia_array['sumt1'] = $t1;
//				$statdia_array['sumtp'] = $tp;
//				$statdia_array['sumtf'] = $tf;
//				$statdia_array['type']='cordinator';
//				$sortarray1[] = $statdia_array;
//			}
//
//		}
//
//
//		$xlsRow = 4;
//		if($radioarr[0]=="excel")
//		{
//			$this->xlsBOF();
//
//
//			$this->xlsWriteLabel(0,0,"PKD-Statistik");
//			$this->xlsWriteLabel(0,1,"Abrechnungszeitraum");
//			$this->xlsWriteLabel(0,2,$fromdate);
//			$this->xlsWriteLabel(0,3,$tilldate);
//			$this->xlsWriteLabel(0,4,date("d.m.Y"));
//
//			$this->xlsWriteLabel(2,0,"Anzahl der eingeschriebenen Pat.");
//			$this->xlsWriteNumber(2,1,$noofpatients);
//
//			$this->xlsWriteLabel(3,0,"QPA-Name");
//			$this->xlsWriteLabel(3,1,"Initialtelefonat (t1)");
//			$this->xlsWriteLabel(3,2,"Telefonat mit Prof. (tp)");
//			$this->xlsWriteLabel(3,3,"Telefonat mit Fam. (tf)");
//			$this->xlsWriteLabel(3,4,"Hausbesuch (hb)");
//
//
//
//
//
//			foreach($sortarray1 as $key=>$valfile)
//			{
//				$i++;
//				//$this->xlsWriteNumber($xlsRow,0,"$i")
//				if($valfile['type']=='doctor')
//				{
//					$this->xlsWriteLabel($xlsRow,0,utf8_decode($valfile['userdata']));
//					$this->xlsWriteNumber($xlsRow,1,$valfile['t1']);
//					$this->xlsWriteNumber($xlsRow,2,$valfile['tp']);
//					$this->xlsWriteNumber($xlsRow,3,$valfile['tf']);
//					$this->xlsWriteNumber($xlsRow,4,$valfile['hb']);
//
//
//					$rowcount++;
//					$allhb += $valfile['hb'];
//					$allt1 += $valfile['t1'];
//					$alltp +=$valfile['tp'];
//					$alltf +=$valfile['tf'];
//				}
//				$xlsRow++;
//			}
//			$this->xlsWriteLabel($xlsRow-3,0,"Summe");
//			$this->xlsWriteNumber($xlsRow-3,1,$allt1);
//			$this->xlsWriteNumber($xlsRow-3,2,$alltp);
//			$this->xlsWriteNumber($xlsRow-3,3,$alltf);
//			$this->xlsWriteNumber($xlsRow-3,4,$allhb);
//
//			$this->xlsWriteLabel($xlsRow-2,0,"Aktion pro Patient");
//			$this->xlsWriteNumber($xlsRow-2,1,number_format(($allt1/$noofpatients),2,".",""));
//			$this->xlsWriteNumber($xlsRow-2,2,number_format(($alltp/$noofpatients),2,".",""));
//			$this->xlsWriteNumber($xlsRow-2,3,number_format(($alltf/$noofpatients),2,".",""));
//			$this->xlsWriteNumber($xlsRow-2,4,number_format(($allhb/$noofpatients),2,".",""));
//
//			$this->xlsWriteLabel($xlsRow,0,"KO-Statistik");
//			$this->xlsWriteLabel($xlsRow,1,"Abrechnungszeitraum");
//			$this->xlsWriteLabel($xlsRow,2,$fromdate);
//			$this->xlsWriteLabel($xlsRow,3,$tilldate);
//			$this->xlsWriteLabel($xlsRow,4,date("d.m.Y"));
//
//			$this->xlsWriteLabel($xlsRow+1,0,"Koordinator-Name");
//			$this->xlsWriteLabel($xlsRow+1,1,"Initialtelefonat (t1)");
//			$this->xlsWriteLabel($xlsRow+1,2,"Telefonat mit Prof. (tp)");
//			$this->xlsWriteLabel($xlsRow+1,3,"Telefonat mit Fam. (tf)");
//			$this->xlsWriteLabel($xlsRow+1,4,"Hausbesuch (hb)");
//
//			//$rowcount=1;
//			$sexlsRow=$xlsRow-5;
//			$allhb = 0;
//			$allt1 = 0;
//			$alltp =0;
//			$alltf =0;
//			foreach($sortarray1 as $key=>$valfile)
//			{
//				if($valfile['type']=='cordinator')
//				{
//					$this->xlsWriteLabel($sexlsRow,0,utf8_decode($valfile['userdata']));
//					$this->xlsWriteNumber($sexlsRow,1,$valfile['t1']);
//					$this->xlsWriteNumber($sexlsRow,2,$valfile['tp']);
//					$this->xlsWriteNumber($sexlsRow,3,$valfile['tf']);
//					$this->xlsWriteNumber($sexlsRow,4,$valfile['hb']);
//
//					$rowcount++;
//					$allhb += $valfile['hb'];
//					$allt1 += $valfile['t1'];
//					$alltp +=$valfile['tp'];
//					$alltf +=$valfile['tf'];
//				}
//				$sexlsRow++;
//			}
//
//			$this->xlsWriteLabel($sexlsRow,0,"Summe");
//			$this->xlsWriteNumber($sexlsRow,1,$allt1);
//			$this->xlsWriteNumber($sexlsRow,2,$alltp);
//			$this->xlsWriteNumber($sexlsRow,3,$alltf);
//			$this->xlsWriteNumber($sexlsRow,4,$allhb);
//
//			$this->xlsWriteLabel($sexlsRow+1,0,"Aktion pro Patient");
//			$this->xlsWriteNumber($sexlsRow+1,1,number_format(($allt1/$noofpatients),2,".",""));
//			$this->xlsWriteNumber($sexlsRow+1,2,number_format(($alltp/$noofpatients),2,".",""));
//			$this->xlsWriteNumber($sexlsRow+1,3,number_format(($alltf/$noofpatients),2,".",""));
//			$this->xlsWriteNumber($sexlsRow+1,4,number_format(($allhb/$noofpatients),2,".",""));
//
//			$this->xlsEOF();
//
//			$fileName = "CD_special_stats.xls";
//			header("Pragma: public");
//			header("Expires: 0");
//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//			header("Content-Type: application/force-download");
//			header("Content-Type: application/octet-stream");
//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
//
//			header("Content-Disposition: attachment; filename=".$fileName);
//
//			exit;
//		}else{
//
//			$data="";
//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
//						 <tr><th>PKD-Statistik</th>
//						 <td colspan="3">Abrechnungszeitraum : '.$fromdate.' - '.$tilldate.'</td>
//						 <th>'.date("d.m.Y").'</th></tr>
//						  <tr><td colspan="10">Anzahl der eingeschriebenen Pat. : <b>'.$noofpatients.'</b></td></tr>
//						  <tr> <th>QPA-Name</th>
//						  <th>Initialtelefonat (t1)</th>
//						  <th>Telefonat mit Prof. (tp)</th>
//						  <th>Telefonat mit Fam. (tf)</th>
//						  <th>Hausbesuch (hb)</th></tr>';
//			$rowcount=1;
//			//array_multisort($count,SORT_DESC,$sortarray1);
//
//			$allhb = 0;
//			$allt1 = 0;
//			$alltp =0;
//			$alltf =0;
//			foreach($sortarray1 as $key=>$valfile)
//			{
//				if($valfile['type']=='doctor')
//				{
//					$data.= '<tr><td>'.$valfile['userdata'].'&nbsp;</td>
//									 <td align="center">'.$valfile['t1'].'&nbsp;</td>
//									 <td align="center">'.$valfile['tp'].'&nbsp;</td>
//									 <td align="center">'.$valfile['tf'].'&nbsp;</td>
//									 <td align="center">'.$valfile['hb'].'&nbsp;</td></tr>';
//
//					$rowcount++;
//					$allhb += $valfile['hb'];
//					$allt1 += $valfile['t1'];
//					$alltp +=$valfile['tp'];
//					$alltf +=$valfile['tf'];
//
//				}
//			}
//			$data.= '<tr><td><b>Summe</b>&nbsp;</td>
//										 <td align="center"><b>'.$allt1.'</b>&nbsp;</td>
//										 <td align="center"><b>'.$alltp.'</b>&nbsp;</td>
//										 <td align="center"><b>'.$alltf.'</b>&nbsp;</td>
//										 <td align="center"><b>'.$allhb.'</b>&nbsp;</td></tr>
//										 <tr><td><b>Aktion pro Patient</b>&nbsp;</td>
//										 <td align="center"><b>'.number_format(($allt1/$noofpatients),2,".","").'</b>&nbsp;</td>
//										 <td align="center"><b>'.number_format(($alltp/$noofpatients),2,".","").'</b>&nbsp;</td>
//										 <td align="center"><b>'.number_format(($alltf/$noofpatients),2,".","").'</b>&nbsp;</td>
//										 <td align="center"><b>'.number_format(($allhb/$noofpatients),2,".","").'</b>&nbsp;</td></tr>';
//			$data.="</table><br /><br /><br />";
//
//			$data .='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
//						 <tr><th>KO-Statistik</th>
//						 <td colspan="3">Abrechnungszeitraum : '.$fromdate.' - '.$tilldate.'</td>
//						 <th>'.date("d.m.Y").'</th></tr><tr>
//						  <th>Koordinator-Name</th>
//						  <th>Initialtelefonat (t1)</th>
//						  <th>Telefonat mit Prof. (tp)</th>
//						  <th>Telefonat mit Fam. (tf)</th>
//						  <th>Hausbesuch (hb)</th></tr>';
//			$rowcount=1;
//			//array_multisort($count,SORT_DESC,$sortarray1);
//			$allhb = 0;
//			$allt1 = 0;
//			$alltp =0;
//			$alltf =0;
//			foreach($sortarray1 as $key=>$valfile)
//			{
//				if($valfile['type']=='cordinator')
//				{
//					$data.= '<tr><td>'.$valfile['userdata'].'&nbsp;</td>
//									 <td align="center">'.$valfile['t1'].'&nbsp;</td>
//									 <td align="center">'.$valfile['tp'].'&nbsp;</td>
//									 <td align="center">'.$valfile['tf'].'&nbsp;</td>
//									 <td align="center">'.$valfile['hb'].'&nbsp;</td></tr>';
//
//					$rowcount++;
//					$allhb += $valfile['hb'];
//					$allt1 += $valfile['t1'];
//					$alltp +=$valfile['tp'];
//					$alltf +=$valfile['tf'];
//				}
//			}
//
//
//			$data.= '<tr><td><b>Summe</b>&nbsp;</td>
//									 <td align="center"><b>'.$allt1.'</b>&nbsp;</td>
//									 <td align="center"><b>'.$alltp.'</b>&nbsp;</td>
//									 <td align="center"><b>'.$alltf.'</b>&nbsp;</td>
//									 <td align="center"><b>'.$allhb.'</b>&nbsp;</td></tr>
//									 <tr><td><b>Aktion pro Patient</b>&nbsp;</td>
//									 <td align="center"><b>'.number_format(($allt1/$noofpatients),2,".","").'</b>&nbsp;</td>
//									 <td align="center"><b>'.number_format(($alltp/$noofpatients),2,".","").'</b>&nbsp;</td>
//									 <td align="center"><b>'.number_format(($alltf/$noofpatients),2,".","").'</b>&nbsp;</td>
//									 <td align="center"><b>'.number_format(($allhb/$noofpatients),2,".","").'</b>&nbsp;</td></tr>';
//			$data.="</table>";
//
//			if($radioarr[0]=="screen")
//			{
//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
//				echo $data;
//				exit;
//				echo "<SCRIPT type='text/javascript'>";
//				echo "newwindow=window.open(location.href,'reportlist');";
//				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
//				echo "</SCRIPT>";
//
//			}elseif($radioarr[0]=="printing")
//			{
//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
//
//				echo $data;
//				echo "<SCRIPT type='text/javascript'>";
//				echo "window.print();";
//				echo "</SCRIPT>";
//				exit;
//
//			}
//		}
//
//	}
//
//
//
	//	private function maindiagnosisstats($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//
	//		$activeipid =$this->allactivepatiens($quarterarr, $yeararr, $montharr);
	//
	//  		$dg = new DiagnosisType();
	//		$abb2 = "'HD'";
	//		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);
	//
	//		foreach($ddarr2 as $key=>$valdia)
	//		{
	//			$typeid .= '"'.$valdia['id'].'",';
	//		}
	//		$dispat = Doctrine_Query::create()
	//		->select(" diagnosis_id as diagnosis_id, ipid as ipid ")
	//		->from("PatientDiagnosis")
	//		->where('ipid in ('.$activeipid.') and diagnosis_type_id in ('.substr($typeid,0,-1).') 	 ')
	////		->andWhere("tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."'")
	//		->orderBy('diagnosis_id DESC')
	// 		->groupBy('diagnosis_id')
	//		->limit('100');
	//		$dispatexec = $dispat->execute();
	// //	echo $dispat->getSqlQuery();
	//		$disipidarray = $dispatexec->toArray();
	//
	//		$xlsRow = 1;
	//
	//		foreach($disipidarray as $key=>$val)
	//		{
	//			if($val['diagnosis_id']>0)
	//			{
	//				++$i;
	//				$avgdiagnosis = Doctrine_Query::create()
	//				->select("count(*)")
	//				->from("PatientDiagnosis")
	////				->where('diagnosis_id = '.$val["diagnosis_id"].'')
	////				->where('diagnosis_id = '.$val["diagnosis_id"].' and ipid = "'.$val["ipid"].'"  ')
	//				->where('diagnosis_id = '.$val["diagnosis_id"].' and ipid in ('.$activeipid.') ')
	//				->andWhere("tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."'")
	//// 				->groupBy('diagnosis_id')
	// 				;
	//				$avgdiagnosisexec = $avgdiagnosis->execute();
	// 				//echo $avgdiagnosis->getSqlQuery();
	//				$avgdiagnosisarray = $avgdiagnosisexec->toArray();
	//
	//				$diagno = Doctrine_Query::create()
	//				->select("*")
	//				->from("Diagnosis")
	//				->where("id=".$val['diagnosis_id']);
	//				$diagnoexec = $diagno->execute();
	//				$dispat->getSqlQuery();
	//				$diagnoarray = $diagnoexec->toArray();
	//
	//				$statdia_array = array();
	//				$statdia_array['icd_primary'] = $diagnoarray[0]["icd_primary"];
	////				$statdia_array['icd_primary'] = $diagnoarray[0]["icd_primary"].'-- '.$diagnoarray[0]["id"];
	//				$statdia_array['description'] = $diagnoarray[0]["description"];
	//				$statdia_array['count'] = $avgdiagnosisarray[0]['count'];
	//
	//				$count[$key]  = $avgdiagnosisarray[0]['count'];
	//				$sortarray1[] = $statdia_array;
	//			}
	//
	//		}
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,utf8_decode($this->view->translate('icdprimary')));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('description'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('count'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$val)
	//			{
	//
	//				$this->xlsWriteNumber($xlsRow,0,$key+1);
	//				$this->xlsWriteLabel($xlsRow,1,$val["icd_primary"]);
	//				$this->xlsWriteLabel($xlsRow,2,html_entity_decode($val["description"]));
	//				$this->xlsWriteNumber($xlsRow,3,$val['count']);
	//				$xlsRow++;
	//			}
	//			$this->xlsEOF();
	//
	//			$fileName = "Diagnosis_statistics.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//
	//		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
	//								 <tr>
	//									<th width="10%">'.$this->view->translate('no').'</th>
	//									<th width="10%">'.$this->view->translate('icdprimary').'</th>
	//									<th width="15%">'.$this->view->translate('description').'</th>
	//									<th width="15%">'.$this->view->translate('count').'</th></tr>';
	//			$rowcount=1;
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//			foreach($sortarray1 as $key=>$val)
	//			{
	//				if(strlen($val['icd_primary'])>0){$icdcode=$val['icd_primary'];}else{$icdcode="-";}
	//				if(strlen($val["description"])>0){$dscript=utf8_encode(html_entity_decode($val["description"]));}else{$dscript="-";}
	//
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td valign="top">'.$icdcode.'</td><td valign="top">'.$dscript.'</td><td valign="top">'.$val["count"].'</td></tr>';
	//				$rowcount++;
	//			}
	//
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT LANGUAGE='javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}
	//			elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				echo "<SCRIPT LANGUAGE='javascript'>";
	//				//echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//			}
	//		}
	//	}
	//
	//
	//

	private function maindiagnosisstats($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();

		$activeipid =$this->allactivepatiens($quarterarr, $yeararr, $montharr);

		$dg = new DiagnosisType();
		$abb2 = "'HD'";
		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);

		foreach($ddarr2 as $key=>$valdia)
		{
			$typeid .= '"'.$valdia['id'].'",';
		}
		$dispat = Doctrine_Query::create()
		->select('*, count(diagnosis_id) as sum_diagnos, AES_DECRYPT(tabname, "'.Zend_Registry::get('salt').'") as a_tabname')
		->from("PatientDiagnosis")
		->where('ipid in ('.$activeipid.') and diagnosis_type_id in ('.substr($typeid,0,-1).') ')
		//->andWhere(" tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."' ")
		->groupBy('diagnosis_id')
		->orderBy('diagnosis_id DESC');
		//echo $dispat->getSqlQuery();
		//exit;
		$disipidarray = $dispat->fetchArray();

		$xlsRow = 1;
		$i = 0;
		foreach($disipidarray as $key=>$val)
		{
			if($val['a_tabname']=='diagnosis')
			{
				if($val['diagnosis_id']==""){$val['diagnosis_id']='0';}

				$diagno = Doctrine_Query::create()
				->select("*")
				->from("Diagnosis")
				->where("id=".$val['diagnosis_id']);
				$diagnoexec = $diagno->execute();
				$dispat->getSqlQuery();
				$diagnoarray = $diagnoexec->toArray();

				$statdia_array = array();
				$statdia_array['icd_primary'] = $diagnoarray[0]["icd_primary"];
				$statdia_array['description'] = $diagnoarray[0]["description"];
				$statdia_array['count'] = $disipidarray[$i]['sum_diagnos'];

				//radu here
				$statdia_array['tabname']   =   $disipidarray[$i]['a_tabname'];
				//radu gone

				$count[$key]  = $disipidarray[$i]['sum_diagnos'];
				$sortarray1[] = $statdia_array;
			} elseif ($val['a_tabname'] == "diagnosis_freetext"){

				if($val['diagnosis_id']==""){$val['diagnosis_id']='0';}

				$dg = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisText')
				->where('id in ('.$val['diagnosis_id'].')');

				$res1 = $dg->execute();
				$try1 = $res1->toArray();

				$statdia_array['icd_primary'] = "-";
				$statdia_array['description'] = $try1[0]['free_name'];
				$statdia_array['diagno_comment']=$try1[0]['free_desc'];
				$statdia_array['tabname']   =   $disipidarray[$i]['a_tabname'];
				$statdia_array['count']  = $disipidarray[$i]['sum_diagnos'];
				$sortarray1[] = $statdia_array;

			} elseif($val['a_tabname'] == "diagnosis_icd") {

				if($val['diagnosis_id']==""){$val['diagnosis_id']='0';}

				$dgg = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisIcd')
				->where("id = '".$val['diagnosis_id']."' ")
				->orderBy('id ASC');

				$res2 = $dgg->execute();
				$try2 = $res2->toArray();

				$icd_primary = !empty($try2[0]['icd_primary'])? $try2[0]['icd_primary'] : "-";

				$statdia_array['icd_primary'] = $icd_primary;
				$statdia_array['description'] = $try2[0]['description'];
				$statdia_array['diagno_comment']=$try2[0]['free_desc'];
				$statdia_array['tabname']   =   $disipidarray[$i]['a_tabname'];
				$statdia_array['count']  = $disipidarray[$i]['sum_diagnos'];
				$sortarray1[] = $statdia_array;

			}
			++$i;
		}

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,utf8_decode($this->view->translate('icdprimary')));
			$this->xlsWriteLabel(0,2,$this->view->translate('description'));
			$this->xlsWriteLabel(0,3,$this->view->translate('count'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$val)
			{

				$this->xlsWriteNumber($xlsRow,0,$key+1);
				$this->xlsWriteLabel($xlsRow,1,$val["icd_primary"]);
				$this->xlsWriteLabel($xlsRow,2,html_entity_decode($val["description"]));
				$this->xlsWriteNumber($xlsRow,3,$val['count']);
				$xlsRow++;
			}
			$this->xlsEOF();

			$fileName = "Diagnosis_statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">'.$this->view->translate('no').'</th>
									<th width="10%">'.$this->view->translate('icdprimary').'</th>
									<th width="15%">'.$this->view->translate('description').'</th>
									<th width="15%">'.$this->view->translate('count').'</th>
                                                                        <!--<th width="15%">Test</th>--></tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$val)
			{
				if(strlen($val['icd_primary'])>0){$icdcode=$val['icd_primary'];}else{$icdcode="-";}
				if(strlen($val["description"])>0){$dscript=utf8_encode(html_entity_decode($val["description"]));}else{$dscript="-";}

				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td valign="top">'.$icdcode.'</td><td valign="top">'.$dscript.'</td><td valign="top">'.$val["count"].'</td><!--<td>'.$val["tabname"].'</td>--></tr>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}





	//	private function maindiagnosisstats($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		//$where = "";
	//		$actwhere = $this->getDiagnosisQuarterCondition($quarterarr,$yeararr,$montharr);
	//
	//		$finalipidval = array();
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval[]=$val['ipid'];
	//		}
	//
	//		$comma=",";
	//		$activeipid ="'0'";
	//		foreach($finalipidval as $keyip=>$valip)
	//		{
	//			$activeipid .=$comma."'".$valip."'";
	//			$comma=",";
	//		}
	//
	//		$dg = new DiagnosisType();
	//		$abb2 = "'HD'";
	//		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);
	//		$comma=",";
	//		$typeid ="'0'";
	//		foreach($ddarr2 as $key=>$valdia)
	//		{
	//			$typeid .=$comma."'".$valdia['id']."'";
	//			$comma=",";
	//		}
	//
	//		$dispat = Doctrine_Query::create()
	//		->select("distinct(diagnosis_id) as diagnosis_id")
	//		->from("PatientDiagnosis")
	//		->where("ipid in (".$activeipid.") and diagnosis_type_id in (".$typeid.") and ".$actwhere)
	//		->andWhere("tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."'")
	//		->orderBy('diagnosis_id DESC')
	//		->limit('100');
	//		$dispatexec = $dispat->execute();
	//		//echo $dispat->getSqlQuery();
	//		$disipidarray = $dispatexec->toArray();
	//		//print_r($disipidarray);
	//		//exit;
	//
	//		$xlsRow = 1;
	//
	//		foreach($disipidarray as $key=>$val)
	//		{
	//			if($val['diagnosis_id']>0)
	//			{
	//				++$i;
	//				$avgdiagnosis = Doctrine_Query::create()
	//				->select("count(*)")
	//				->from("PatientDiagnosis")
	//				->where("diagnosis_id = ".$val['diagnosis_id']." and ".$actwhere)
	//				->andWhere("tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."'");
	//				$avgdiagnosisexec = $avgdiagnosis->execute();
	//				$avgdiagnosis->getSqlQuery();
	//				$avgdiagnosisarray = $avgdiagnosisexec->toArray();
	//
	//				$diagno = Doctrine_Query::create()
	//				->select("*")
	//				->from("Diagnosis")
	//				->where("id=".$val['diagnosis_id']);
	//				$diagnoexec = $diagno->execute();
	//				$dispat->getSqlQuery();
	//				$diagnoarray = $diagnoexec->toArray();
	//
	//				$statdia_array = array();
	//				$statdia_array['icd_primary'] = $diagnoarray[0]["icd_primary"];
	//				$statdia_array['description'] = $diagnoarray[0]["description"];
	//				$statdia_array['count'] = $avgdiagnosisarray[0]['count'];
	//
	//				$count[$key]  = $avgdiagnosisarray[0]['count'];
	//				$sortarray1[] = $statdia_array;
	//			}
	//
	//		}
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,utf8_decode($this->view->translate('icdprimary')));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('description'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('count'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$val)
	//			{
	//
	//				$this->xlsWriteNumber($xlsRow,0,$key+1);
	//				$this->xlsWriteLabel($xlsRow,1,$val["icd_primary"]);
	//				$this->xlsWriteLabel($xlsRow,2,html_entity_decode($val["description"]));
	//				$this->xlsWriteNumber($xlsRow,3,$val['count']);
	//				$xlsRow++;
	//			}
	//			$this->xlsEOF();
	//
	//			$fileName = "Diagnosis_statistics.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//
	//		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
	//								 <tr>
	//									<th width="10%">'.$this->view->translate('no').'</th>
	//									<th width="10%">'.$this->view->translate('icdprimary').'</th>
	//									<th width="15%">'.$this->view->translate('description').'</th>
	//									<th width="15%">'.$this->view->translate('count').'</th></tr>';
	//			$rowcount=1;
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//			foreach($sortarray1 as $key=>$val)
	//			{
	//				if(strlen($val['icd_primary'])>0){$icdcode=$val['icd_primary'];}else{$icdcode="-";}
	//				if(strlen($val["description"])>0){$dscript=utf8_encode(html_entity_decode($val["description"]));}else{$dscript="-";}
	//
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td valign="top">'.$icdcode.'</td><td valign="top">'.$dscript.'</td><td valign="top">'.$val["count"].'</td></tr>';
	//				$rowcount++;
	//			}
	//
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT LANGUAGE='javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}
	//			elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				echo "<SCRIPT LANGUAGE='javascript'>";
	//				//echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//			}
	//		}
	//	}
	//

	//
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
	//
	//		$activeipid =$this->allactivepatiens($quarterarr, $yeararr, $montharr);
	//
	//		$dg = new DiagnosisType();
	//		$abb2 = "'HD'";
	//		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);
	//
	//		foreach($ddarr2 as $key=>$valdia)
	//		{
	//			$typeid .= '"'.$valdia['id'].'",';
	//		}
	//		$dispat = Doctrine_Query::create()
	//		->select("distinct(diagnosis_id) as diagnosis_id, ipid as ipid ")
	//		->from("PatientDiagnosis")
	//		->where('ipid in ('.$activeipid.') and diagnosis_type_id in ('.substr($typeid,0,-1).') 	 ')
	//		->andWhere("tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."'")
	//		->orderBy('diagnosis_id DESC')
	// 		->groupBy('diagnosis_id')
	//		->limit('100');
	//		$dispatexec = $dispat->execute();
	//   //echo $dispat->getSqlQuery();
	//		$disipidarray = $dispatexec->toArray();
	//
	//		$xlsRow = 1;
	//
	//		foreach($disipidarray as $key=>$val)
	//		{
	//			if($val['diagnosis_id']>0)
	//			{
	//				++$i;
	//				$avgdiagnosis = Doctrine_Query::create()
	//				->select("count(*)")
	//				->from("PatientDiagnosis")
	//				->where('diagnosis_id = '.$val["diagnosis_id"].' and ipid in ('.$activeipid.') ')
	//				->andWhere("tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."'")
	// 				->groupBy('diagnosis_id');
	//				$avgdiagnosisexec = $avgdiagnosis->execute();
	// 				//echo $avgdiagnosis->getSqlQuery();
	//				$avgdiagnosisarray = $avgdiagnosisexec->toArray();
	//
	//				$diagno = Doctrine_Query::create()
	//				->select("*")
	//				->from("Diagnosis")
	//				->where("id=".$val['diagnosis_id']);
	//				$diagnoexec = $diagno->execute();
	//				$dispat->getSqlQuery();
	//				$diagnoarray = $diagnoexec->toArray();
	//
	//				$statdia_array = array();
	//				$statdia_array['icd_primary'] = $diagnoarray[0]["icd_primary"];
	//				$statdia_array['description'] = $diagnoarray[0]["description"];
	//				$statdia_array['count'] = $avgdiagnosisarray[0]['count'];
	//
	//				$count[$key]  = $avgdiagnosisarray[0]['count'];
	//				$sortarray1[] = $statdia_array;
	//			}
	//
	//		}
	private function sidediagnosisstats($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$statdia_array = array();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$finalipidval =$this->allactivepatiens($quarterarr, $yeararr, $montharr);

		$diameta = new DiagnosisMeta();
		$diagnosismeta = $diameta->getDiagnosisMetaData(0);
		foreach($diagnosismeta as $keymeta=>$valmeta)
		{
			$meta = Doctrine_Query::create()
			->select("count(*)")
			->from('PatientDiagnosisMeta')
			->where('ipid in ('.$finalipidval.') and metaid='.$valmeta["id"].' ')
			;
			$metaexec = $meta->execute();
			$metaarray = $metaexec->toArray();
			//echo $meta->getSqlQuery();
			//exit;
			if($metaarray[0]['count'] != 0){
				$statdia_array['description'] = utf8_decode($valmeta['meta_title']);
				$statdia_array['count'] = $metaarray[0]['count'];
				$sortarray1[] = $statdia_array;
			}
		}

		$dg = new DiagnosisType();
		$abb2 = "'ND'";
		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);

		foreach($ddarr2 as $key=>$valdia)
		{
			$typeid .= '"'.$valdia['id'].'",';
		}

		$dispat = Doctrine_Query::create()
		->select("distinct(diagnosis_id) as diagnosis_id, AES_DECRYPT(tabname, '".Zend_Registry::get('salt')."') as a_tabname")
		->from("PatientDiagnosis")
		->where('ipid in ('.$finalipidval.') and diagnosis_type_id in ('.substr($typeid,0,-1).')   ')
		//->andWhere("tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."'")
		->orderBy('diagnosis_id DESC')
		//		->groupBy('diagnosis_id')
		->limit('100');
		$dispatexec = $dispat->execute();
		//echo $dispat->getSqlQuery();
		$disipidarray = $dispatexec->toArray();
		//print_r($disipidarray);
		//exit;

		$xlsRow = 1;

		foreach($disipidarray as $key=>$val)
		{
			if($val['a_tabname'] == "diagnosis")
			{
				++$i;
				$avgdiagnosis = Doctrine_Query::create()
				->select("count(*)")
				->from("PatientDiagnosis")
				->where('diagnosis_id = '.$val["diagnosis_id"].' and ipid in ('.$finalipidval.') ')
				//->andWhere("tabname ='".addslashes(Pms_CommonData::aesEncrypt("diagnosis"))."'")
				;
				$avgdiagnosisexec = $avgdiagnosis->execute();
				//echo $avgdiagnosis->getSqlQuery().'-->'.$i.'<br/>';
				$avgdiagnosisarray = $avgdiagnosisexec->toArray();

				$diagno = Doctrine_Query::create()
				->select("*")
				->from("Diagnosis")
				->where("id=".$val['diagnosis_id']);
				$diagnoexec = $diagno->execute();
				$dispat->getSqlQuery();
				$diagnoarray = $diagnoexec->toArray();


				$statdia_array['icd_primary'] = $diagnoarray[0]["icd_primary"];
				$statdia_array['description'] = $diagnoarray[0]["description"];
				$statdia_array['count'] = $avgdiagnosisarray[0]['count'];
				$statdia_array['tabname'] = $val['a_tabname'];


				$count[$key]  = $avgdiagnosisarray[0]['count'];
				$sortarray1[] = $statdia_array;
			} elseif ($val['a_tabname'] == "diagnosis_freetext"){

				if($val['diagnosis_id']==""){$val['diagnosis_id']='0';}

				$dg = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisText')
				->where('id in ('.$val['diagnosis_id'].')');

				$res1 = $dg->execute();
				$try1 = $res1->toArray();

				$statdia_array['icd_primary'] = "-";
				$statdia_array['description'] = $try1[0]['free_name'];
				$statdia_array['diagno_comment']=$try1[0]['free_desc'];
				$statdia_array['tabname']   =   $val['a_tabname'];
				$statdia_array['count']  =  $avgdiagnosisarray[0]['count'];
				$sortarray1[] = $statdia_array;

			} elseif($val['a_tabname'] == "diagnosis_icd") {

				if($val['diagnosis_id']==""){$val['diagnosis_id']='0';}

				$dgg = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisIcd')
				->where("id = '".$val['diagnosis_id']."' ")
				->orderBy('id ASC');

				$res2 = $dgg->execute();
				$try2 = $res2->toArray();

				$icd_primary = !empty($try2[0]['icd_primary'])? $try2[0]['icd_primary'] : "-";

				$statdia_array['icd_primary'] = $icd_primary;
				$statdia_array['description'] = $try2[0]['description'];
				$statdia_array['diagno_comment']=$try2[0]['free_desc'];
				$statdia_array['tabname']   =   $val['a_tabname'];
				$statdia_array['count']  =  $avgdiagnosisarray[0]['count'];
				$sortarray1[] = $statdia_array;

			}

		}

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,utf8_decode($this->view->translate('icdprimary')));
			$this->xlsWriteLabel(0,2,$this->view->translate('description'));
			$this->xlsWriteLabel(0,3,$this->view->translate('count'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$val)
			{

				$this->xlsWriteNumber($xlsRow,0,$key+1);
				$this->xlsWriteLabel($xlsRow,1,$val["icd_primary"]);
				$this->xlsWriteLabel($xlsRow,2,html_entity_decode($val["description"]));
				$this->xlsWriteNumber($xlsRow,3,$val['count']);
				$xlsRow++;
			}
			$this->xlsEOF();

			$fileName = "Diagnosis_statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">'.$this->view->translate('no').'</th>
									<th width="10%">'.$this->view->translate('icdprimary').'</th>
									<th width="15%">'.$this->view->translate('description').'</th>
									<th width="15%">'.$this->view->translate('count').'</th>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray1 as $key=>$val)
			{
				if(strlen($val['icd_primary'])>0){$icdcode=$val['icd_primary'];}else{$icdcode="-";}
				if(strlen($val["description"])>0){$dscript=utf8_encode(html_entity_decode($val["description"]));}else{$dscript="-";}

				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td valign="top">'.$icdcode.'</td><td valign="top">'.$dscript.'</td><td valign="top">'.$val["count"].'</td></tr>';
				$rowcount++;
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}


	private function anfragendePerson($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$statdia_array = array();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$finalipidval =$this->allactivepatiens($quarterarr, $yeararr, $montharr);

		$refby = new PatientReferredBy();
		$refaray = $refby->getPatientReferredByreport($logininfo->clientid,0);
		$refaray = array_merge($refaray,array("0"=>array("id"=>"0","referred_name"=>"keine Angabe")));

		$xlsRow = 1;

		foreach($refaray as $key=>$val)
		{
			++$i;

			$patient = Doctrine_Query::create()
			->select("count(*)")
			->from('PatientMaster p')
			->where("ipid in (".$finalipidval.") and isdelete = 0  and isstandby=0 and isstandbydelete = 0 and referred_by = '".$val['id']."'");
			$patient->leftJoin("p.EpidIpidMapping e");
			$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
			$patientexec = $patient->execute();
			$ipidarray = $patientexec->toArray();

			/*if($val['isdelete']==1){
			 $statdia_array['referedby'] = $val["referred_name"];
			 $statdia_array['count'] = $ipidarray[0]['count'];
			 }	*/

			$statdia_array['referedby'] = $val["referred_name"];
			$statdia_array['isdelete'] = $val['isdelete'];
			$statdia_array['count'] = $ipidarray[0]['count'];
			$totalafcount += $ipidarray[0]['count'];
			$count[$key] = $ipidarray[0]['count'];
			$sortarray1[] = $statdia_array;
		}

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,utf8_decode($this->view->translate('referredby')));
			$this->xlsWriteLabel(0,2,$this->view->translate('count'));
			$this->xlsWriteLabel(0,3,$this->view->translate('percentage'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}



			foreach($sortarray1 as $key=>$val)
			{
				if($val['isdelete']==1)
				{
					$kainecount += $val['count'];
					continue;
				}

				if($val['referedby']=="keine Angabe"){ $kanetotal=$val['count']+$kainecount;}else{ $kanetotal=$val['count']; }

				$this->xlsWriteNumber($xlsRow,0,$key+1);
				$this->xlsWriteLabel($xlsRow,1,utf8_decode($val["referedby"]));
				$this->xlsWriteNumber($xlsRow,2,$kanetotal);
				$this->xlsWriteLabel($xlsRow,3,number_format(($kanetotal/$totalafcount)*100,2,".","")." %");

				$xlsRow++;
			}

			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum'));
			$this->xlsWriteNumber($xlsRow,2,$totalafcount);
			$this->xlsEOF();

			$fileName = "referedby_statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">'.$this->view->translate('no').'</th>
									<th width="10%">'.$this->view->translate('referredby').'</th>
									<th width="15%">'.$this->view->translate('count').'</th>
									<th width="15%">'.$this->view->translate('percentage').'</th></tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$val)
			{

				if($val['isdelete']==1)	{ $kainecount += $val['count'];	continue; }

				if($val['referedby']=="keine Angabe"){ $kanetotal=$val['count']+$kainecount;}else{ $kanetotal=$val['count']; }
				if(strlen($val['referedby'])>0){$referedby=$val['referedby'];}else{$referedby="-";}

				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td valign="top">'.$referedby.'</td><td valign="top">'.$kanetotal.'</td>
							<td valign="top">'.number_format(($kanetotal/$totalafcount)*100,2,".","").'&nbsp;%</td></tr>';
				$rowcount++;

			}

			$data.="<tr><td>Summe</td><td>&nbsp;</td><td>".$totalafcount."</td><td>&nbsp;</td></tr>";
			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}
	//	private function anfragendePerson($radioarr,$montharr,$quarterarr,$yeararr)
	//	{
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		//$where = "";
	//		$actwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
	//		$statdia_array = array();
	//		$finalipidval = array();
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval[]=$val['ipid'];
	//		}
	//		$activeipid="'0'";
	//		$comma=",";
	//		foreach($finalipidval as $keyip=>$valip)
	//		{
	//			$activeipid.=$comma."'".$valip."'";
	//			$comma=",";
	//		}
	//		$refby = new PatientReferredBy();
	//		$refaray = $refby->getPatientReferredByreport($logininfo->clientid,0);
	//		$refaray = array_merge($refaray,array("0"=>array("id"=>"0","referred_name"=>"keine Angabe")));
	//
	//		$xlsRow = 1;
	//
	//		foreach($refaray as $key=>$val)
	//		{
	//			++$i;
	//
	//			$patient = Doctrine_Query::create()
	//			->select("count(*)")
	//			->from('PatientMaster p')
	//			->where("ipid in (".$activeipid.") and isdelete = 0  and isstandby=0 and referred_by = '".$val['id']."'");
	//			$patient->leftJoin("p.EpidIpidMapping e");
	//			$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//			$patientexec = $patient->execute();
	//			$ipidarray = $patientexec->toArray();
	//
	//			/*if($val['isdelete']==1){
	//			 $statdia_array['referedby'] = $val["referred_name"];
	//			 $statdia_array['count'] = $ipidarray[0]['count'];
	//			 }	*/
	//
	//			$statdia_array['referedby'] = $val["referred_name"];
	//			$statdia_array['isdelete'] = $val['isdelete'];
	//			$statdia_array['count'] = $ipidarray[0]['count'];
	//			$totalafcount += $ipidarray[0]['count'];
	//			$count[$key] = $ipidarray[0]['count'];
	//			$sortarray1[] = $statdia_array;
	//		}
	//
	//		if($radioarr[0]=="excel")
	//		{
	//			$this->xlsBOF();
	//			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
	//			$this->xlsWriteLabel(0,1,utf8_decode($this->view->translate('referredby')));
	//			$this->xlsWriteLabel(0,2,$this->view->translate('count'));
	//			$this->xlsWriteLabel(0,3,$this->view->translate('percentage'));
	//
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//
	//
	//			foreach($sortarray1 as $key=>$val)
	//			{
	//				if($val['isdelete']==1)
	//				{
	//					$kainecount += $val['count'];
	//					continue;
	//				}
	//
	//				if($val['referedby']=="keine Angabe"){ $kanetotal=$val['count']+$kainecount;}else{ $kanetotal=$val['count']; }
	//
	//				$this->xlsWriteNumber($xlsRow,0,$key+1);
	//				$this->xlsWriteLabel($xlsRow,1,utf8_decode($val["referedby"]));
	//				$this->xlsWriteNumber($xlsRow,2,$kanetotal);
	//				$this->xlsWriteLabel($xlsRow,3,number_format(($kanetotal/$totalafcount)*100,2,".","")." %");
	//
	//				$xlsRow++;
	//			}
	//
	//			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum'));
	//			$this->xlsWriteNumber($xlsRow,2,$totalafcount);
	//			$this->xlsEOF();
	//
	//			$fileName = "referedby_statistics.xls";
	//			header("Pragma: public");
	//			header("Expires: 0");
	//			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//			header("Content-Type: application/force-download");
	//			header("Content-Type: application/octet-stream");
	//			header("Content-type: application/vnd.ms-excel; charset=utf-8");
	//			header("Content-Disposition: attachment; filename=".$fileName);
	//			//echo trim($html);
	//			exit;
	//
	//		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){
	//
	//			$data="";
	//			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
	//								 <tr>
	//									<th width="10%">'.$this->view->translate('no').'</th>
	//									<th width="10%">'.$this->view->translate('referredby').'</th>
	//									<th width="15%">'.$this->view->translate('count').'</th>
	//									<th width="15%">'.$this->view->translate('percentage').'</th></tr>';
	//			$rowcount=1;
	//			if(strlen($_POST["columname"])>0)
	//			{
	//				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
	//			}
	//
	//			foreach($sortarray1 as $key=>$val)
	//			{
	//
	//				if($val['isdelete']==1)	{ $kainecount += $val['count'];	continue; }
	//
	//				if($val['referedby']=="keine Angabe"){ $kanetotal=$val['count']+$kainecount;}else{ $kanetotal=$val['count']; }
	//				if(strlen($val['referedby'])>0){$referedby=$val['referedby'];}else{$referedby="-";}
	//
	//				$data.= '<tr class="row"><td valign="top">'.$rowcount.'</td><td valign="top">'.$referedby.'</td><td valign="top">'.$kanetotal.'</td>
	//							<td valign="top">'.number_format(($kanetotal/$totalafcount)*100,2,".","").'&nbsp;%</td></tr>';
	//				$rowcount++;
	//
	//			}
	//
	//			$data.="<tr><td>Summe</td><td>&nbsp;</td><td>".$totalafcount."</td><td>&nbsp;</td></tr>";
	//			$data.="</table>";
	//
	//			if($radioarr[0]=="screen")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				exit;
	//				echo "<SCRIPT LANGUAGE='javascript'>";
	//				echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
	//				echo "</SCRIPT>";
	//
	//			}
	//			elseif($radioarr[0]=="printing")
	//			{
	//				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
	//				echo $data;
	//				echo "<SCRIPT LANGUAGE='javascript'>";
	//				//echo "newwindow=window.open(location.href,'reportlist');";
	//				echo "window.print();";
	//				echo "</SCRIPT>";
	//				exit;
	//			}
	//		}
	//	}


	private function zipstats($radioarr,$quarterarr, $yeararr, $montharr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();


		$activeipid = $this->allactivepatiens($quarterarr, $yeararr, $montharr);
		$patient = Doctrine_Query::create()
		->select("ipid,AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') as zip, count(zip) as  zip_nr ")
		->from("PatientMaster p")
		->where('ipid in ('.$activeipid.')')
		//		->andWhere('AES_DECRYPT(zip,"'.Zend_Registry::get('salt').'") != "" ')
		->groupBy("convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//echo $patient->getSqlQuery();

		$patientexec = $patient->execute();
		$sortarray = $patientexec->toArray();

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('zip'));
			$this->xlsWriteLabel(0,2,$this->view->translate('count'));

			$xlsRow = 1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray as $key=>$val)
			{
				if($val["zip"] != ""){
					$zip = $val["zip"];
				} else {
					$zip = "keine Angabe";
				}
				$this->xlsWriteNumber($xlsRow,0,$key+1);
				$this->xlsWriteLabel($xlsRow,1,$zip);
				$this->xlsWriteLabel($xlsRow,2,$val["zip_nr"]);
				$xlsRow++;
			}
			$this->xlsEOF();

			$fileName = "PLZ_statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$fileName);
			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="50%">
								 <tr>
									<th width="20%">'.$this->view->translate('no').'</th>
									<th >'.$this->view->translate('zip').'</th>
									<th >'.$this->view->translate('count').'</th>
								 </tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray as $key=>$val)
			{
				if($val["zip"] != ""){
					$zip = $val["zip"];
				} else {
					$zip = "keine Angabe";
				}

				$data.= "<tr class='row'>
				<td valign='top'>".$rowcount."</td>
				<td valign='top'>".$zip."</td>
				<td valign='top'>".$val["zip_nr"]."</td>
				</tr>";
				$rowcount++;
			}

			$data.="</table>";
			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}




	private function zipstats_old($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		//		$admtwhere = $this->getQuarterCondition($quarterarr,$yeararr,$montharr);
		$finalipidval = array();
		//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
		//		$startdate = date("Y-m-d",strtotime($startdate));
		$enddate = date("Y-m-d",strtotime($enddate));

		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0 and isstandbydelete = 0")
		//->andWhere('isdischarged = 0')
		// ->andWhere('isdischarged = 0 and '.$admtwhere)
		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$actpatientexec = $actpatient->execute();
		$actipidarray = $actpatientexec->toArray();
		foreach($actipidarray as $key=>$val)
		{
			$finalipidval[]= $val['ipid'];
		}


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1 and isstandbydelete = 0")
		//->andWhere('isdischarged = 0')
		// ->andWhere('isdischarged = 0 and '.$admtwhere)
		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();
		$disipidval="'0'";
		$comma=",";
		foreach($ipidarray as $key=>$val)
		{
			$disipidval.=$comma."'".$val['ipid']."'";
			$comma=",";
		}
		$disquery = Doctrine_Query::create()
		->select("*")
		->from('PatientDischarge')
		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
		//echo  $disquery->getSqlQuery();
		$disexec = $disquery->execute();
		$disarray = $disexec->toArray();
		foreach($disarray as $key=>$val)
		{
			$finalipidval[]=$val['ipid'];
		}
		$activeipid ="'0'";
		$comma=",";
		foreach($finalipidval as $keyip=>$valipid){
			$activeipid.=$comma."'".$valipid."'";
			$comma=",";
		}

		$dispat = Doctrine_Query::create()
		->select("distinct(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."')) as zip")
		->from("PatientMaster")
		->where("ipid in (".$activeipid.") and isdelete=0")
		->orderBy('admission_date DESC');
		$dispatexec = $dispat->execute();
		//echo $dispat->getSqlQuery();
		$disipidarray = $dispatexec->toArray();
		//print_r($disipidarray);
		//exit;

		foreach($disipidarray as $key=>$val)
		{

			if($val['zip']>0)
			{
				++$i;
				$admt = Doctrine_Query::create()
				->select("count(*)")
				->from("PatientMaster")
				->where("ipid in (".$activeipid.") and AES_DECRYPT(zip,'".Zend_Registry::get('salt')."')='".$val['zip']."' and isdelete='0' and isstandbydelete = '0'");
				$admtexec = $admt->execute();

				$admtarray = $admtexec->toArray();

				$isdist = Doctrine_Query::create()
				->select("count(*)")
				->from("PatientMaster")
				->where("ipid in (".$disipidval.") and AES_DECRYPT(zip,'".Zend_Registry::get('salt')."')='".$val['zip']."' and isdischarged='1' and isdelete='0' and isstandbydelete = '0'");
				//->where("zip = '".$val['zip']."' and isdischarge=1");
				$isdistexec = $isdist->execute();

				$isdistarray = $isdistexec->toArray();


				$statdia_array = array();
				$statdia_array['zip'] = $val['zip'];
				$statdia_array['admit'] = $admtarray[0]["count"];
				$statdia_array['discharge'] = $isdistarray[0]['count'];

				$count[$key]  = $avgdiagnosisarray[0]['count'];
				$sortarray[] = $statdia_array;
			}




		}

		if($radioarr[0]=="excel")
		{
			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('zip'));
			$this->xlsWriteLabel(0,2,$this->view->translate('numcurrent'));
			$this->xlsWriteLabel(0,3,$this->view->translate('numdischarge'));

			$xlsRow = 1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray as $key=>$val)
			{

				$this->xlsWriteNumber($xlsRow,0,$key+1);
				$this->xlsWriteLabel($xlsRow,1,$val["zip"]);
				$this->xlsWriteNumber($xlsRow,2,$val["admit"]);
				$this->xlsWriteNumber($xlsRow,3,$val['discharge']);
				$admitcount +=$val["admit"];
				$dischargecount+=$val['discharge'];
				$xlsRow++;
			}
			$this->xlsWriteLabel($xlsRow,1,$this->view->translate('sum'));
			$this->xlsWriteNumber($xlsRow,2,$admitcount);
			$this->xlsWriteNumber($xlsRow,3,$dischargecount);
			$this->xlsEOF();

			$fileName = "PLZ_statistics.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=".$fileName);
			//echo trim($html);
			exit;

		}elseif($radioarr[0]=="screen" || $radioarr[0]=="printing"){

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">'.$this->view->translate('no').'</th>
									<th width="10%">'.$this->view->translate('zip').'</th>
									<th width="15%">'.$this->view->translate('numcurrent').'</th>
									<th width="15%">'.$this->view->translate('numdischarge').'</th></tr>';
			$rowcount=1;
			if(strlen($_POST["columname"])>0)
			{
				$sortarray = $this->array_sort($sortarray,$_POST["columname"],SORT_ASC);
			}
			foreach($sortarray as $key=>$val)
			{

				$data.= "<tr class='row'><td valign='top'>".$rowcount."</td><td valign='top'>".$val["zip"]."</td><td valign='top'>".$val["admit"]."</td><td valign='top'>".$val['discharge']."</td></tr>";
				$admitcount +=$val["admit"];
				$dischargecount+=$val['discharge'];
				$rowcount++;
			}
			$data.= "<tr class='row'><td valign='top'>&nbsp;</td><td valign='top'>Summe</td><td valign='top'>".$admitcount."</td><td valign='top'>".$dischargecount."</td></tr>";
			$data.="</table>";
			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT LANGUAGE='javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}
			elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				echo "<SCRIPT LANGUAGE='javascript'>";
				//echo "newwindow=window.open(location.href,'reportlist');";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;
			}
		}
	}

	private function xlsBOF() {
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
		return;
	}

	private function xlsEOF() {
		echo pack("ss", 0x0A, 0x00);
		return;
	}

	private function xlsWriteNumber($Row, $Col, $Value) {
		echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		echo pack("d", $Value);
		return;
	}

	private function xlsWriteLabel($Row, $Col, $Value ) {
		$L = strlen($Value);
		echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		echo $Value;
		return;
	}

	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}

	}

	private function getReportFileData($clnt)
	{
		if($clnt>0)
		{
			$whereclnt = " and ep.clientid=".$clnt;
		}

		$ipid = Doctrine_Query::create()
		->select('ipid')
		->from('PatientMaster pm')
		->where('isdischarged = 1 and isdelete=0 and isstandbydelete = 0')
		->leftJoin('pm.EpidIpidMapping ep')
		->andWhere('ep.ipid=pm.ipid '.$whereclnt)
		->orderBy('pm.admission_date DESC');
		$ipid->getSqlQuery();
		$ipidexec =	$ipid->execute();
		$ipidarray = $ipidexec->toArray();

		$comma=",";
		$disipidval="'0'";
		foreach($ipidarray as $key=>$val)
		{
			$disipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(title,'".Zend_Registry::get('salt')."') as title,
					AES_DECRYPT(file_name,'".Zend_Registry::get('salt')."') as file_name,
					AES_DECRYPT(file_type,'".Zend_Registry::get('salt')."') as file_type")
		->from('PatientFileUpload')
		->where("ipid in(".$disipidval.") and (convert(AES_DECRYPT(title,'".Zend_Registry::get('salt')."') using latin1)='".Pms_CommonData::aesEncrypt('Teilnahmeerkl?rung (Anlage 3)')."' or convert(AES_DECRYPT(title,'".Zend_Registry::get('salt')."') using latin1)='".Pms_CommonData::aesEncrypt('Stammdatenblatt f?r den PKD (Anlage 3a)')."' or convert(AES_DECRYPT(title,'".Zend_Registry::get('salt')."') using latin1) ='".Pms_CommonData::aesEncrypt('Basisdokumentation (Anlage 4)')."' or convert(AES_DECRYPT(title,'".Zend_Registry::get('salt')."') using latin1) ='".Pms_CommonData::aesEncrypt('Palliativ Versorgung a7')."' or convert(AES_DECRYPT(title,'".Zend_Registry::get('salt')."') using latin1) ='".Pms_CommonData::aesEncrypt('Anlage 7')."')");

		//		echo $patient->getSqlQuery();
		$fl = $patient->execute();
		$filearray = $fl->toArray();

		return $filearray;
	}


	function getClientPatients($clientid, $whereepid) {
		$actpatient = Doctrine_Query::create()
		->select("p.ipid")
		->from('PatientMaster p');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->where($whereepid.'e.clientid = '.$clientid);


		$actipidarray = $actpatient->fetchArray();

		return $actipidarray;
	}

	function allactivepatiens($quarterarr,$yeararr,$montharr){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$finalipidval = array();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdischarged = 0')
		->andWhere('isdelete = 0')
		->andWhere('isstandby = 0')
		->andWhere('isstandbydelete = 0')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//		echo $actpatient->getSqlQuery().'<br /><br /><br /><br />';
		//		$actpatientexec = $actpatient->execute();
		//		$actipidarray = $actpatientexec->toArray();

		$actipidarray = $actpatient->fetchArray();

		foreach($actipidarray as $key=>$val)
		{
			$finalipidval[]= $val['ipid'];
		}


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdischarged = 1')
		->andWhere('isdelete = 0')
		->andWhere('isstandbydelete = 0')
		->andWhere('isstandby = 0')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//		$patientexec = $patient->execute();
		//		$ipidarray = $patientexec->toArray();

		$ipidarray = $patient->fetchArray();
		//		echo $patient->getSqlQuery().'<br /><br /><br /><br /><br />';

		if(is_array($ipidarray) && sizeof($ipidarray) > 0){

			foreach($ipidarray as $key=>$val)
			{
				$disipidval .= '"'.$val['ipid'].'",';
			}
			$disquery = Doctrine_Query::create()
			->select("*")
			->from('PatientDischarge')
			->where('ipid in ('.substr($disipidval,0,-1).') AND ('.str_replace('%date%', 'discharge_date', $active_cond['active_sql']).')');

			//$disexec = $disquery->execute();
			//$disarray = $disexec->toArray();

			//			echo $disquery->getSqlQuery().'<br /><br /><br /><br />';

			$disarray = $disquery->fetchArray();

			foreach($disarray as $key=>$val)
			{
				$finalipidval[]=$val['ipid'];
			}
		}


		$activeipid ="'0'";
		$comma=",";
		foreach($finalipidval as $keyip=>$valipid)
		{
			$activeipid.=$comma."'".$valipid."'";
			$comma=",";
		}

		return $activeipid;
	}


	function allactivepatiensArry($quarterarr,$yeararr,$montharr){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$finalipidval = array();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdischarged = 0')
		->andWhere('isdelete = 0')
		->andWhere('isstandbydelete = 0')
		->andWhere('isstandby = 0')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$actipidarray = $actpatient->fetchArray();


		foreach($actipidarray as $key=>$val)
		{
			$finalipidval[]= $val['ipid'];
		}


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdischarged = 1')
		->andWhere('isdelete = 0')
		->andWhere('isstandbydelete = 0')
		->andWhere('isstandby = 0')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$ipidarray = $patient->fetchArray();

		if(is_array($ipidarray) && sizeof($ipidarray) > 0){

			foreach($ipidarray as $key=>$val)
			{
				$disipidval .= '"'.$val['ipid'].'",';
			}
			$disquery = Doctrine_Query::create()
			->select("*")
			->from('PatientDischarge')
			->where('ipid in ('.substr($disipidval,0,-1).') AND ('.str_replace('%date%', 'discharge_date', $active_cond['active_sql']).')');
			//			echo $disquery->getSqlQuery();
			$disarray = $disquery->fetchArray();

			foreach($disarray as $key=>$val)
			{
				$finalipidval[]=$val['ipid'];
			}
		}

		//exit;
		return $finalipidval;
	}


	//	function allactivepatiensArry($quarterarr,$yeararr,$montharr){
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//		$finalipidval = array();
	//
	//		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where('isdischarged = 0')
	//		->andWhere('isdelete = 0')
	//		->andWhere('isstandby = 0')
	//		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//		$actipidarray = $actpatient->fetchArray();
	//
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval[]= $val['ipid'];
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where('isdischarged = 1')
	//		->andWhere('isdelete = 0')
	//		->andWhere('isstandby = 0')
	//		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$ipidarray = $patient->fetchArray();
	//
	//		echo $patient->getSqlQuery().'<br />';
	//
	//		if(is_array($ipidarray) && sizeof($ipidarray) > 0){
	//
	//			foreach($ipidarray as $key=>$val)
	//			{
	//				$disipidval .= '"'.$val['ipid'].'",';
	//			}
	//			$disquery = Doctrine_Query::create()
	//			->select("*")
	//			->from('PatientDischarge')
	//			->where('ipid in ('.substr($disipidval,0,-1).') AND ('.str_replace('%date%', 'discharge_date', $active_cond['active_sql']).')');
	//			$disarray = $disquery->fetchArray();
	//
	//			foreach($disarray as $key=>$val)
	//			{
	//				$finalipidval[]=$val['ipid'];
	//			}
	//		}
	//
	//exit;
	//		return $finalipidval;
	//	}
	//
	function allpatients(){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$finalipidval = array();
		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		//			echo $actpatient->getSqlQuery();
		$actpatientexec = $actpatient->execute();
		$actipidarray = $actpatientexec->toArray();

		foreach($actipidarray as $key=>$val)
		{
			$finalipidval[]= $val;
		}
		return $finalipidval;
	}
	function dischargedpatients(){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$finalipidval = array();

		$actpatient = Doctrine_Query::create()
		->select("ipid")
		->from('PatientMaster p')
		->where('isdischarged = 1');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		//			echo $actpatient->getSqlQuery();
		$actpatientexec = $actpatient->execute();
		$actipidarray = $actpatientexec->toArray();
		$disipidval="'0'";
		$comma=",";
		foreach($actipidarray as $key=>$val)
		{
			$disipidval.=$comma."'".$val['ipid']."'";
			$comma=",";
		}
		$disquery = Doctrine_Query::create()
		->select("*")
		->from('PatientDischarge')
		->where("ipid in (".$disipidval.")");
		//echo  $disquery->getSqlQuery();
		$disexec = $disquery->execute();
		$disarray = $disexec->toArray();
		foreach($disarray as $key=>$val)
		{
			$finalipidval[]=$val['ipid'];
		}


		return $finalipidval;
	}
	private function getcourseLdigit($quarterarr,$yeararr,$montharr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$finalipidval = array();
		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
		$startdate = date("Y-m-d",strtotime($startdate));
		$enddate = date("Y-m-d",strtotime($enddate));

		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0 and isstandbydelete = 0")
		//->andWhere('isdischarged = 0')
		// ->andWhere('isdischarged = 0 and '.$admtwhere)
		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$actpatientexec = $actpatient->execute();
		$actipidarray = $actpatientexec->toArray();
		foreach($actipidarray as $key=>$val)
		{
			$finalipidval[]= $val['ipid'];
		}


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1 and isstandbydelete = 0")
		//->andWhere('isdischarged = 0')
		// ->andWhere('isdischarged = 0 and '.$admtwhere)
		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();
		$disipidval="'0'";
		$comma=",";
		foreach($ipidarray as $key=>$val)
		{
			$disipidval.=$comma."'".$val['ipid']."'";
			$comma=",";
		}
		$disquery = Doctrine_Query::create()
		->select("*")
		->from('PatientDischarge')
		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
		//echo  $disquery->getSqlQuery();
		$disexec = $disquery->execute();
		$disarray = $disexec->toArray();
		foreach($disarray as $key=>$val)
		{
			$finalipidval[]=$val['ipid'];
		}

		$activeipid ="'0'";
		$comma=",";
		foreach($finalipidval as $keyip=>$valipid)
		{
			$activeipid.=$comma."'".$valipid."'";
			$comma=",";
		}


		$qpa1 = Doctrine_Query::create()
		->select("distinct(AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."')) as course_title")
		->from('PatientCourse')
		->where("ipid in (".$activeipid.") and course_type='".addslashes(Pms_CommonData::aesEncrypt('L'))."'");

		$qp1 = $qpa1->execute();

		if($qp1)
		{
			$newarr1=$qp1->toArray();
			for($i=0;$i<count($newarr1);$i++)
			{
				$rem = explode("|",$newarr1[$i]['course_title']);
				$newarr1[$i]['course_title'] = $rem[0];
			}
			return $newarr1;

		}

	}

	public function listbpatientAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');


		$ipid = Doctrine_Query::create()
		->select('ipid')
		->from('EpidIpidMapping')
		->where("clientid=21");
		//echo $ipid->getSqlQuery();
		$ipidexec =	$ipid->execute();
		$ipidarray = $ipidexec->toArray();

		$comma=",";
		$ipidval="'0'";
		foreach($ipidarray as $key=>$val)
		{
			$ipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}


		$queryrow = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,")
		->from('PatientMaster')
		->where("ipid in (".$ipidval.") and isdischarged = 0 and isdelete=0 and isstandbydelete = 0")
		->orderBy('admission_date ASC');
		$qexec = $queryrow->execute();
		$patientarray = $qexec->toArray();
		foreach($patientarray as $key=>$val)
		{
			$statdia_array = array();
			$statdia_array['name'] = $val['last_name'].", ".$val['first_name'];
			$statdia_array['admission_date'] = $val['admission_date'];

			++$i;
			$ph = new  PatientHealthInsurance();
			$phi = $ph->getPatientHealthInsurance($val['ipid']);


			if(count($phi)>0)
			{

				//	$this->retainValues($phi[0]);

				if($phi[0]['insurance_no']!=0)
				{
					$this->view->insurance_no = $phi[0]['insurance_no'];
				}
				if($phi[0]['kvk_no']!=0)
				{
					$this->view->kvk_no = $phi[0]['kvk_no'];
				}
				$company_name = $phi[0]['company_name'];
				$comment = $phi[0]['comment'];
				$statdia_array['healthinsurance'] =$phi[0]['company_name'];

				$st = new KbvKeytabs();
				$status_array = $st->getKbvKeytabs(1);

				if($phi[0]['insurance_status']!="")
				{
					$insurance_status  = $status_array[$phi[0]['insurance_status']];
				}
			}



			//$count[$key]  = $avgdiagnosisarray[0]['count'];
			$sortarray[] = $statdia_array;

		}


		$this->xlsBOF();
		$this->xlsWriteLabel(0,0,$this->view->translate('no'));
		$this->xlsWriteLabel(0,1,$this->view->translate('lastname').",".$this->view->translate('firstname'));
		$this->xlsWriteLabel(0,2,$this->view->translate('admission_date'));
		$this->xlsWriteLabel(0,3,$this->view->translate('patient_health_insurance'));

		$xlsRow = 1;

		array_multisort($count,SORT_DESC,$sortarray);

		foreach($sortarray as $key=>$val)
		{

			$this->xlsWriteNumber($xlsRow,0,$key+1);
			$this->xlsWriteLabel($xlsRow,1,utf8_decode($val["name"]));
			$this->xlsWriteLabel($xlsRow,2,$val["admission_date"]);
			$this->xlsWriteLabel($xlsRow,3,utf8_decode($val['healthinsurance']));
			$xlsRow++;
		}
		$this->xlsEOF();

		$fileName = "Bochumpatientlist.xls";
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-type: application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition: attachment; filename=".$fileName);
		//echo trim($html);
		exit;


	}

	private function getQuarterCondition($quarterarr,$yeararr,$montharr){

		if(count($quarterarr)>0)
		{
			foreach($quarterarr as $key=>$val)
			{
				if($val==1)
				{
					$startdate = $yeararr[0]."-01-01";
					$enddate = $yeararr[0]."-03-31";

					$where .= " (admission_date BETWEEN '".$startdate."' AND '".$enddate."')";
					$sep = " or ";
				}

				if($val==2)
				{
					$startdate = $yeararr[0]."-04-01";
					$enddate = $yeararr[0]."-06-30";

					$where .= $sep."(admission_date BETWEEN '".$startdate."' AND '".$enddate."')";
					$sep = " or ";
				}

				if($val==3)
				{
					$startdate = $yeararr[0]."-07-01";
					$enddate = $yeararr[0]."-09-30";

					$where .= $sep."(admission_date BETWEEN '".$startdate."' AND '".$enddate."')";
					$sep = " or ";
				}

				if($val==4)
				{
					$startdate = $yeararr[0]."-10-01";
					$enddate = $yeararr[0]."-12-31";
					$where .= $sep."(admission_date BETWEEN '".$startdate."' AND '".$enddate."')";
					$sep = " or ";
				}

			}


		}
		elseif(count($montharr)>0)
		{
			$mcnt = count($montharr)-1;
			$startdate = $yeararr[0]."-".$montharr[0]."-01";
			$enddate = $yeararr[0]."-".$montharr[$mcnt]."-31";

			$where = "(admission_date BETWEEN '".$startdate."' AND '".$enddate."')";
			//					foreach ($montharr as $key => $value){
			//						$startdate = $yeararr[0]."-".$value."-01";
			//						$enddate = $yeararr[0]."-".$value."-31";
			//
			//						$where .= $sep." admission_date BETWEEN '".$startdate."' AND '".$enddate."' ";
			//						$sep = " or ";
			//
			//					}
		}else{

			$startdate = $yeararr[0]."-01-01";
			$enddate = $yeararr[0]."-12-31";

			$where = "(admission_date BETWEEN '".$startdate."' AND '".$enddate."')";
		}

		return $where;

	}

	private function getDischargeQuarterCondition($quarterarr,$yeararr,$montharr){

		if(count($quarterarr)>0)
		{
			foreach($quarterarr as $key=>$val)
			{
				if($val==1)
				{
					$startdate = $yeararr[0]."-01-01";
					$enddate = $yeararr[0]."-03-31";

					$where .= " (discharge_date >= '".$startdate."' AND discharge_date <='".$enddate."')";
					$sep = " or ";
				}

				if($val==2)
				{
					$startdate = $yeararr[0]."-04-01";
					$enddate = $yeararr[0]."-06-30";

					$where .= $sep."(discharge_date >= '".$startdate."' AND discharge_date <='".$enddate."')";
					$sep = " or ";
				}

				if($val==3)
				{
					$startdate = $yeararr[0]."-07-01";
					$enddate = $yeararr[0]."-09-30";

					$where .= $sep."(discharge_date >= '".$startdate."' AND discharge_date <= '".$enddate."')";
					$sep = " or ";
				}

				if($val==4)
				{
					$startdate = $yeararr[0]."-10-01";
					$enddate = $yeararr[0]."-12-31";
					$where .= $sep."(discharge_date >= '".$startdate."' AND discharge_date <= '".$enddate."')";
					$sep = " or ";
				}

			}


		}
		elseif(count($montharr)>0)
		{
			$mcnt = count($montharr)-1;
			$startdate = $yeararr[0]."-".$montharr[0]."-01";
			$enddate = $yeararr[0]."-".$montharr[$mcnt]."-31";

			$where = "(discharge_date >= '".$startdate."' AND discharge_date <='".$enddate."')";

		}else{

			$startdate = $yeararr[0]."-01-01";
			$enddate = $yeararr[0]."-12-31";

			$where = "(discharge_date >= '".$startdate."' AND discharge_date <= '".$enddate."')";
		}

		return $where;

	}

	private function getDiagnosisQuarterCondition($quarterarr,$yeararr,$montharr){

		if(count($quarterarr)>0)
		{
			foreach($quarterarr as $key=>$val)
			{
				if($val==1)
				{
					$startdate = $yeararr[0]."-01-01";
					$enddate = $yeararr[0]."-03-31";

					$where .= " (create_date BETWEEN '".$startdate."' AND '".$enddate."')";
					$sep = " or ";
				}

				if($val==2)
				{
					$startdate = $yeararr[0]."-04-01";
					$enddate = $yeararr[0]."-06-30";

					$where .= $sep."(create_date BETWEEN '".$startdate."' AND '".$enddate."')";
					$sep = " or ";
				}

				if($val==3)
				{
					$startdate = $yeararr[0]."-07-01";
					$enddate = $yeararr[0]."-09-30";

					$where .= $sep."(create_date BETWEEN '".$startdate."' AND '".$enddate."')";
					$sep = " or ";
				}

				if($val==4)
				{
					$startdate = $yeararr[0]."-10-01";
					$enddate = $yeararr[0]."-12-31";
					$where .= $sep."(create_date BETWEEN '".$startdate."' AND '".$enddate."')";
					$sep = " or ";
				}

			}


		}
		elseif(count($montharr)>0)
		{
			$mcnt = count($montharr)-1;
			$startdate = $yeararr[0]."-".$montharr[0]."-01";
			$enddate = $yeararr[0]."-".$montharr[$mcnt]."-31";

			$where = "(create_date BETWEEN '".$startdate."' AND '".$enddate."')";

		}else{

			$startdate = $yeararr[0]."-01-01";
			$enddate = $yeararr[0]."-12-31";

			$where = "(create_date BETWEEN '".$startdate."' AND '".$enddate."')";
		}

		return $where;

	}

	private function getCourseQuarterCondition($quarterarr,$yeararr,$montharr){

		if(count($quarterarr)>0)
		{
			foreach($quarterarr as $key=>$val)
			{
				if($val==1)
				{
					$startdate = $yeararr[0]."-01-01 23.59.59";
					$enddate = $yeararr[0]."-03-31 23.59.59";

					$where .= " (course_date >= '".$startdate."' AND course_date <='".$enddate."')";
					$sep = " or ";
				}

				if($val==2)
				{
					$startdate = $yeararr[0]."-04-01 23.59.59";
					$enddate = $yeararr[0]."-06-30 23.59.59";

					$where .= $sep."(course_date >= '".$startdate."' AND course_date <='".$enddate."')";
					$sep = " or ";
				}

				if($val==3)
				{
					$startdate = $yeararr[0]."-07-01 23.59.59";
					$enddate = $yeararr[0]."-09-30 23.59.59";

					$where .= $sep."(course_date >= '".$startdate."' AND course_date <='".$enddate."')";
					$sep = " or ";
				}

				if($val==4)
				{
					$startdate = $yeararr[0]."-10-01 23.59.59";
					$enddate = $yeararr[0]."-12-31 23.59.59";
					$where .= $sep."(course_date >= '".$startdate."' AND course_date <='".$enddate."')";
					$sep = " or ";
				}

			}


		}
		elseif(count($montharr)>0)
		{
			$mcnt = count($montharr)-1;
			$startdate = $yeararr[0]."-".$montharr[0]."-01 23.59.59";
			$enddate = $yeararr[0]."-".$montharr[$mcnt]."-31 23.59.59";

			$where = "(course_date >= '".$startdate."' AND course_date <='".$enddate."')";

		}else{

			$startdate = $yeararr[0]."-01-01 23.59.59";
			$enddate = $yeararr[0]."-12-31 23.59.59";

			$where = "(course_date >= '".$startdate."' AND course_date <='".$enddate."')";
		}

		return $where;

	}


	public function getTimePeriod ($quarterarr,$yeararr,$montharr =  array()) {

		if($quarterarr == 'only_now' && $yeararr == 'only_now' && $montharr == 'only_now') {
			$active_sql = '(date(%date%) >= "'.date('Y').'-'.date('m').'-'.date('d').'") OR ';
			$admission_sql = '(date(%date%) < "'.date('Y').'-'.(date('m')+1).'-01") OR ';
			$date_sql = ' AND year(%date%) > "1900"';
			$interval_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
		} else {

			if(!empty($quarterarr)){
				$montharr = array();
				foreach($quarterarr as $quart){
					switch ($quart) {
						case '2':
							$montharr[] = 4;
							$montharr[] = 5;
							$montharr[] = 6;
							break;

						case '3':
							$montharr[] = 7;
							$montharr[] = 8;
							$montharr[] = 9;
							break;

						case '4':
							$montharr[] = 10;
							$montharr[] = 11;
							$montharr[] = 12;
							break;

						default:
							$montharr[] = 1;
							$montharr[] = 2;
							$montharr[] = 3;
							break;
					}
				}
			}

			foreach($yeararr as $year){
				if(is_numeric($year)){
					$year_sql .= '"'.$year.'",';

					if(is_array($montharr) && sizeof($montharr)){
						foreach($montharr as $month){
							if(is_numeric($month)){
								//$active_sql .= '(month(%date%) >= "'.$month.'" AND year(%date%) >= "'.$year.'") OR ';
								//$admission_sql .= '(year(%date%) <= "'.$year.'" AND month(%date%) <= "'.$month.'") OR ';
								$active_sql .= '(date(%date%) >= "'.$year.'-'.$month.'-01") OR ';
								$admission_sql .= '(date(%date%) < "'.$year.'-'.($month+1).'-01") OR ';
								$interval_sql .= '(((date(%date_start%) <= "'.$year.'-'.$month.'-01") AND (date(%date_end%) >= "'.$year.'-'.$month.'-01")) OR ((date(%date_start%) >= "'.$year.'-'.$month.'-01") AND (date(%date_end%) < "'.$year.'-'.($month+1).'-01"))) OR ';
							} else {
								$active_sql .= '(year(%date%) >= "'.$year.'") OR ';
								$admission_sql .= '(year(%date%) <= "'.$year.'") OR ';
								$interval_sql .= '(((year(%date_start%) <= "'.$year.'") AND (year(%date_end%) >= "'.$year.'")) OR ((year(%date_start%) >= "'.$year.'") AND (year(%date_end%) < "'.($year+1).'"))) OR ';
							}
						}
					} else {
						$active_sql .= '(year(%date%) >= "'.$year.'") OR ';
						$admission_sql .= '(year(%date%) <= "'.$year.'") OR ';
						//$interval_sql .= '((year(%date_start%) >= "'.$year.'") AND (year(%date_end%) <= "'.$year.'")) OR ';
						$interval_sql .= '(((year(%date_start%) <= "'.$year.'") AND (year(%date_end%) >= "'.$year.'")) OR ((year(%date_start%) >= "'.$year.'") AND (year(%date_end%) < "'.($year+1).'"))) OR ';
					}

				}
			}

			foreach($montharr as $month){
				if(is_numeric($month)){
					$month_sql .= '"'.$month.'",';
				}
			}

			if(!empty($month_sql)) {
				$date_sql .= ' AND month(%date%) IN ('.substr($month_sql,0,-1).')';
			}

			if(!empty($year_sql)) {
				$date_sql .= ' AND year(%date%) IN ('.substr($year_sql,0,-1).')';
			}

		}

		if(!empty($date_sql)) {
			$return['date_sql'] = $date_sql;
			$return['active_sql'] = substr($active_sql, 0, -4);
			$return['admission_sql'] = substr($admission_sql, 0, -4);
			$return['interval_sql'] = substr($interval_sql, 0, -4);

			return $return;
		} else {
			return false;
		}

	}

	private function getQuarterperiods($quarterarr,$yeararr,$montharr){

		if(count($quarterarr)>0)
		{
			$startdate = "";
			foreach($quarterarr as $key=>$val)
			{


				if($val==1)
				{
					$startdate = $yeararr[0]."-01-01";
					$enddate = $yeararr[0]."-03-31";

					$where .= "'".$startdate."' - '".$enddate."'";
					$sep = " or ";

				}

				if($val==2)
				{
					if(strlen($startdate)==0){
						$startdate = $yeararr[0]."-04-01";
					}
					$enddate = $yeararr[0]."-06-30";

					$where .= $sep."'".$startdate."' - '".$enddate."'";
					$sep = " or ";

				}

				if($val==3)
				{
					if(strlen($startdate)==0){
						$startdate = $yeararr[0]."-07-01";
					}
					$enddate = $yeararr[0]."-09-30";

					$where .= $sep."'".$startdate."' - '".$enddate."'";
					$sep = " or ";
				}

				if($val==4)
				{
					if(strlen($startdate)==0){
						$startdate = $yeararr[0]."-10-01";
					}
					$enddate = $yeararr[0]."-12-31";
					$where .= $sep."'".$startdate."' - '".$enddate."'";
					$sep = " or ";
				}

			}


			return date("d.m.Y",strtotime($startdate))." - ".date("d.m.Y",strtotime($enddate));

		}
		elseif(count($montharr)>0)
		{
			$mcnt = count($montharr)-1;
			$startdate = $yeararr[0]."-".$montharr[0]."-01";
			$enddate = $yeararr[0]."-".$montharr[$mcnt]."-31";

			$where = date("d.m.Y",strtotime($startdate))." - ".date("d.m.Y",strtotime($enddate));

		}else{

			$startdate = $yeararr[0]."-01-01";
			$enddate = $yeararr[0]."-12-31";

			$where = date("d.m.Y",strtotime($startdate))." - ".date("d.m.Y",strtotime($enddate));
		}

		return $where;

	}

	private function getDocCondition(){


		if($_POST['doctorname']!=0)
		{

			$eipd = Doctrine_Query::create()
			->select('*')
			->from('PatientQpaMapping')
			->where('userid = ?', $_POST['doctorname']);
			$epidexec = $eipd->execute();
			$epidarray = $epidexec->toArray();

			$comma=",";
			$epidval="'0'";
			foreach($epidarray as $key=>$val)
			{
				$epidval .= $comma."'".$val['epid']."'";
				$comma=",";
			}

			$whereepid = "epid in (".$epidval.") and ";
		}

		return $whereepid;


	}

	private function dischargeMethod($whereepid,$montharr,$quarterarr,$yeararr){


		$logininfo= new Zend_Session_Namespace('Login_Info');

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$ipid = Doctrine_Query::create()
		->select('*')
		->from('EpidIpidMapping')
		->where($whereepid." clientid=".$logininfo->clientid);
		//echo $ipid->getSqlQuery();
		$ipidexec =	$ipid->execute();
		$ipidarray = $ipidexec->toArray();

		$comma=",";
		$disipidval="'0'";
		foreach($ipidarray as $key=>$val)
		{
			$disipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}

		$dis = Doctrine_Query::create()
		->select("*")
		->from('DischargeMethod')
		->where("isdelete = 0  and clientid=".$logininfo->clientid." and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN')");
		$dis->getSqlQuery();

		$disexec = $dis->execute();
		$disarray = $disexec->toArray();

		if(count($disarray)>0)
		{
			$todid = $disarray[0]['id'];
		}

		$dispat = Doctrine_Query::create()
		->select("*")
		->from("PatientDischarge")
		->where('ipid in ('.$disipidval.') and discharge_method='.$todid.' '.str_replace('%date%','discharge_date',$active_cond['date_sql']).'');
		//		->andWhere('('.str_replace('%date%','discharge_date',$active_cond['date_sql']).')');
		$dispatexec = $dispat->execute();
		//	echo 	$dispat->getSqlQuery();
		$disipidarray = $dispatexec->toArray();

		$comma=",";
		$ipidval="'0'";
		foreach($disipidarray as $key=>$val)
		{
			$ipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}

		return $ipidval;

	}
	//	private function dischargeMethod($whereepid){
	//
	//
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//
	//		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);
	//
	//		$ipid = Doctrine_Query::create()
	//		->select('*')
	//		->from('EpidIpidMapping')
	//		->where($whereepid." clientid=".$logininfo->clientid);
	//		//echo $ipid->getSqlQuery();
	//		$ipidexec =	$ipid->execute();
	//		$ipidarray = $ipidexec->toArray();
	//
	//		$comma=",";
	//		$disipidval="'0'";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval .= $comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//
	//		$dis = Doctrine_Query::create()
	//		->select("*")
	//		->from('DischargeMethod')
	//		->where("isdelete = 0  and clientid=".$logininfo->clientid." and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN')");
	//		$dis->getSqlQuery();
	//
	//		$disexec = $dis->execute();
	//		$disarray = $disexec->toArray();
	//
	//		if(count($disarray)>0)
	//		{
	//			$todid = $disarray[0]['id'];
	//		}
	//
	//		$dispat = Doctrine_Query::create()
	//		->select("*")
	//		->from("PatientDischarge")
	//		->where('ipid in ('.$disipidval.') and discharge_method='.$todid.' ');
	//		$dispatexec = $dispat->execute();
	//		$dispat->getSqlQuery();
	//		$disipidarray = $dispatexec->toArray();
	//
	//		$comma=",";
	//		$ipidval="'0'";
	//		foreach($disipidarray as $key=>$val)
	//		{
	//			$ipidval .= $comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//
	//		return $ipidval;
	//
	//	}


	private function dischargeMethodDeadlocation($montharr,$quarterarr,$yeararr){

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);

		$diswhere = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);

		$ipid = Doctrine_Query::create()
		->select('*')
		->from('EpidIpidMapping')
		->where($whereepid." clientid=".$logininfo->clientid);
		//echo $ipid->getSqlQuery();
		$ipidexec =	$ipid->execute();
		$ipidarray = $ipidexec->toArray();

		$comma=",";
		$disipidval="'0'";
		foreach($ipidarray as $key=>$val)
		{
			$disipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}

		$dis = Doctrine_Query::create()
		->select("*")
		->from('DischargeMethod')
		->where("isdelete = 0 and clientid=".$logininfo->clientid." and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod')");
		//echo $dis->getSqlQuery();
		$disexec = $dis->execute();
		$disarray = $disexec->toArray();

		if(count($disarray)>0)
		{
			$todid = $disarray[0]['id'];
		}

		$dispat = Doctrine_Query::create()
		->select("*")
		->from("PatientDischarge")
		->where('ipid in ('.$disipidval.') and discharge_method='.$todid.'   '.str_replace('%date%','discharge_date',$active_cond['date_sql']).' '  );
		$dispatexec = $dispat->execute();
		$dispat->getSqlQuery();

		$disipidarray = $dispatexec->toArray();

		$comma=",";
		$ipidval="'0'";
		foreach($disipidarray as $key=>$val)
		{
			$ipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}

		return $disipidarray;
	}
	//	private function dischargeMethodDeadlocation($montharr,$quarterarr,$yeararr){
	//
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//		$whereepid = $this->getDocCondition();
	//
	//		$diswhere = $this->getDischargeQuarterCondition($quarterarr,$yeararr,$montharr);
	//
	//		$ipid = Doctrine_Query::create()
	//		->select('*')
	//		->from('EpidIpidMapping')
	//		->where($whereepid." clientid=".$logininfo->clientid);
	//		//echo $ipid->getSqlQuery();
	//		$ipidexec =	$ipid->execute();
	//		$ipidarray = $ipidexec->toArray();
	//
	//		$comma=",";
	//		$disipidval="'0'";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval .= $comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//
	//		$dis = Doctrine_Query::create()
	//		->select("*")
	//		->from('DischargeMethod')
	//		->where("isdelete = 0 and clientid=".$logininfo->clientid." and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod')");
	//		//echo $dis->getSqlQuery();
	//		$disexec = $dis->execute();
	//		$disarray = $disexec->toArray();
	//
	//		if(count($disarray)>0)
	//		{
	//			$todid = $disarray[0]['id'];
	//		}
	//
	//		$dispat = Doctrine_Query::create()
	//		->select("*")
	//		->from("PatientDischarge")
	//		->where("ipid in (".$disipidval.") and discharge_method=".$todid." and ".$diswhere);
	//		$dispatexec = $dispat->execute();
	//		$dispat->getSqlQuery();
	//
	//		$disipidarray = $dispatexec->toArray();
	//
	//		$comma=",";
	//		$ipidval="'0'";
	//		foreach($disipidarray as $key=>$val)
	//		{
	//			$ipidval .= $comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//
	//		return $disipidarray;
	//	}

	private function admisionpatients($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}


		$active_cond = $this->getTimePeriod($quarterarr, $yeararr, $montharr);


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone")
		->from('PatientMaster p')
		->where('isdelete = 0 and isstandbydelete = 0 and isstandby=0  '.str_replace('%date%','admission_date',$active_cond['date_sql']).'');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		//echo $patient->getSqlQuery();

		$ipidarray = $patient->fetchArray();



		foreach($ipidarray as $key=>$val)
		{
			$firstname = "";
			$lastname = "";
			$dateofbirth = "";
			$admissiondate = "";

			$epid ="";
			$epid  = Pms_CommonData::getEpid($val["ipid"]);
			$epid_array = Pms_CommonData::getEpidcharsandNum($val["ipid"]);


			if($val['birthd']!="0000-00-00"){
				$date = new Zend_Date($val['birthd']);
				$birthdatep = $date->toString(Zend_Date::DAY.".".Zend_Date::MONTH.".".Zend_Date::YEAR);
			}
			$firstname = trim($val["first_name"]);
			$lastname = trim($val["last_name"]);
			$dateofbirth = trim($birthdatep);


			if($val["admission_date"]!='0000-00-00 00:00:00'){$admissiondate=date("d.m.Y H:i:s",strtotime($val["admission_date"]));}else{$admissiondate="-";}

			$address =  $val['street1']."<br/>".trim($val['zip'])." - ".trim($val['city']);

			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtreat)
				{

					$usr = Doctrine::getTable('User')->find($valtreat['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}

			$statdia_array = array();
			$statdia_array['epid'] = ltrim($epid);
			$statdia_array['epid_num'] = ltrim($epid_array['num']);
			$statdia_array['first_name'] = $firstname;
			$statdia_array['last_name'] = $lastname;
			$statdia_array['dateofbirth'] = $dateofbirth;
			$statdia_array['admission_date'] = $admissiondate;
			$statdia_array['address'] = $address;
			$statdia_array['treatedby'] = $treatedby;
			$sortarray1[] = $statdia_array;



		}
		$xlsRow = 1;
		if($radioarr[0]=="excel")
		{

			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('EPID'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,4,$this->view->translate('birthd'));
			$this->xlsWriteLabel(0,5,$this->view->translate('admisiondate'));
			$this->xlsWriteLabel(0,6,$this->view->translate('address'));
			$this->xlsWriteLabel(0,7,$this->view->translate('treatedby'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,$valfile['epid']);
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['first_name']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['last_name']));
				$this->xlsWriteNumber($xlsRow,4,$valfile['dateofbirth']);
				$this->xlsWriteNumber($xlsRow,5,$valfile['admission_date']);
				$this->xlsWriteNumber($xlsRow,6,utf8_decode($valfile['address']));
				$this->xlsWriteLabel($xlsRow,7,utf8_decode($valfile['treatedby']));

				$xlsRow++;


			}

			$this->xlsEOF();

			$fileName = "Aufnahmen.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");

			header("Content-Disposition: attachment; filename=".$fileName);

			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
						<tr><th width="10%">'.$this->view->translate('no').'</th>
						 <th width="10%">'.$this->view->translate('EPID').'</th>
						<th width="10%">'.$this->view->translate('firstname').'</th>
						<th width="10%">'.$this->view->translate('lastname').'</th>
						<th width="15%">'.$this->view->translate('birthd').'</th>
						<th width="15%">'.$this->view->translate('admissiondate').'</th>
						<th width="15%">'.$this->view->translate('address').'</th>
						<th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
			$rowcount=0;
			//array_multisort($count,SORT_DESC,$sortarray1);

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				$rowcount++;
				$data.= '<tr class="row">
							<td  valign="top">'.$rowcount.'&nbsp;</td>
							<td  valign="top">'.$valfile['epid'].'&nbsp;</td>
							<td  valign="top">'.$valfile['first_name'].'&nbsp;</td>
							<td  valign="top">'.$valfile['last_name'].'&nbsp;</td>
							<td  valign="top">'.$valfile['dateofbirth'].'&nbsp;</td>
							<td  valign="top">'.$valfile['admission_date'].'&nbsp;</td>
							<td  valign="top">'.$valfile['address'].'&nbsp;</td>
							<td valign="top">'.$valfile['treatedby'].'&nbsp;</td></tr>';
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}

	}



	private function admisiondischargepatients($radioarr,$montharr,$quarterarr,$yeararr)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}

		$activeipids = $this->allactivepatiens($quarterarr, $yeararr, $montharr);

		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone")
		->from('PatientMaster p')
		->where('ipid in ('.$activeipids.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);

		$ipidarray = $patient->fetchArray();


		foreach($ipidarray as $key=>$val)
		{
			$firstname = "";
			$lastname = "";
			$dateofbirth = "";
			$admissiondate = "";
			$epid ="";
			$dielocation="";
			$epid  = Pms_CommonData::getEpid($val["ipid"]);
			$epid_array = Pms_CommonData::getEpidcharsandNum($val["ipid"]);


			$firstname = trim($val["first_name"]);
			$lastname = trim($val["last_name"]);



			if($val['isdischarged']==0)
			{
				$dischargeddate= " - ";
			}else{
				$dispat = Doctrine_Query::create()
				->select("*")
				->from("PatientDischarge")
				->where("ipid ='".$val['ipid']."'");
				//echo $dispat->getSqlQuery();
				$dispatexec = $dispat->execute();
				$disipidarray = $dispatexec->toArray();

				//				$dischargeddate = $disipidarray[0]['discharge_date'];
				if($disipidarray[0]['discharge_date']!='0000-00-00 00:00:00'){$dischargeddate=date("d.m.Y H:i:s",strtotime($disipidarray[0]['discharge_date']));}else{$dischargeddate="-";}
				if($disipidarray[0]['discharge_location']>0)
				{
					$dis = Doctrine_Query::create()
					->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."') as location")
					->from('DischargeLocation')
					->where("clientid=".$logininfo->clientid."  and id=".$disipidarray[0]['discharge_location']);
					//echo $dis->getSqlQuery();
					$disexec = $dis->execute();
					$disarray = $disexec->toArray();
					$dielocation = $disarray[0]['location'];
				}

			}

			if ($val['admission_date'] != "00-00-000 00:00:00"){
				$admissiondate = date("d.m.Y H:i:s",strtotime($val["admission_date"]));
			} else {
				$admissiondate = "-";
			}
			$address =  $val['street1']."<br/>".trim($val['zip'])." - ".trim($val['city']);

			$treatedby ="";
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];
			if(strlen($gepid)>0)
			{
				$treat = Doctrine::getTable('PatientQpaMapping')->findBy('epid',$gepid);
				$treatarray = $treat->toArray();
				$user_id = $treatarray[0]['userid'];
				$uname = "";
				$br="";
				foreach($treatarray as $key=>$valtreat)
				{

					$usr = Doctrine::getTable('User')->find($valtreat['userid']);
					if($usr)
					{
						$userarray = $usr->toArray();

						$treatedby .= $br.$userarray['last_name']." ".$userarray['first_name'];
						$br = ";";
					}
				}
			}
			// 		"31"=>array(""=>"","epid_num"=>$this->view->translate('EPID'),"last_name"=>$this->view->translate("lastname"),"first_name"=>$this->view->translate("firstname"),"admission_date"=>$this->view->translate("admissiondate"),"dischargedate"=>$this->view->translate('dischargedate'),"dischargelocation"=>$this->view->translate('dischargelocation'),"address"=>$this->view->translate('address'),"treatedby"=>$this->view->translate('treatedby'))

			$statdia_array = array();
			$statdia_array['epid'] = ltrim($epid);
			$statdia_array['epid_num'] = ltrim($epid_array['num']);
			$statdia_array['first_name'] = $firstname;
			$statdia_array['last_name'] = $lastname;
			$statdia_array['admission_date'] = $admissiondate;
			$statdia_array['discharge_date'] = $dischargeddate;
			$statdia_array['dischargelocation'] = $dielocation;
			$statdia_array['address'] = $address;
			$statdia_array['treatedby'] = $treatedby;
			$sortarray1[] = $statdia_array;




		}
		$xlsRow = 1;
		if($radioarr[0]=="excel")
		{

			$this->xlsBOF();
			$this->xlsWriteLabel(0,0,$this->view->translate('no'));
			$this->xlsWriteLabel(0,1,$this->view->translate('epid'));
			$this->xlsWriteLabel(0,2,$this->view->translate('firstname'));
			$this->xlsWriteLabel(0,3,$this->view->translate('lastname'));
			$this->xlsWriteLabel(0,4,$this->view->translate('admisiondate'));
			$this->xlsWriteLabel(0,5,$this->view->translate('dischargedate'));
			$this->xlsWriteLabel(0,6,$this->view->translate('dischargelocation'));
			$this->xlsWriteLabel(0,7,$this->view->translate('address'));
			$this->xlsWriteLabel(0,8,$this->view->translate('treatedby'));

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{

				$i++;
				$this->xlsWriteNumber($xlsRow,0,"$i");
				$this->xlsWriteLabel($xlsRow,1,$valfile['epid']);
				$this->xlsWriteLabel($xlsRow,2,utf8_decode($valfile['first_name']));
				$this->xlsWriteLabel($xlsRow,3,utf8_decode($valfile['last_name']));
				$this->xlsWriteNumber($xlsRow,4,$valfile['admission_date']);
				$this->xlsWriteNumber($xlsRow,5,$valfile['discharge_date']);
				$this->xlsWriteNumber($xlsRow,6,$valfile['dischargelocation']);
				$this->xlsWriteNumber($xlsRow,7,utf8_decode($valfile['address']));
				$this->xlsWriteLabel($xlsRow,8,utf8_decode($valfile['treatedby']));

				$xlsRow++;
			}

			$this->xlsEOF();

			$fileName = "Aufnahmen.xls";
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");

			header("Content-Disposition: attachment; filename=".$fileName);

			exit;
		}else{

			$data="";
			$data ='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
						<tr><th width="10%">'.$this->view->translate('no').'</th>
						 <th width="10%">'.$this->view->translate('EPID').'</th>
						<th width="10%">'.$this->view->translate('firstname').'</th>
						<th width="10%">'.$this->view->translate('lastname').'</th>
						<th width="15%">'.$this->view->translate('admissiondate').'</th>
						<th width="15%">'.$this->view->translate('dischargedate').'</th>
						<th width="15%">'.$this->view->translate('dischargelocation').'</th>
						<th width="15%">'.$this->view->translate('address').'</th>
						<th width="15%">'.$this->view->translate('treatedby').'</th><tr>';
			$rowcount=0;
			//array_multisort($count,SORT_DESC,$sortarray1);

			if(strlen($_POST["columname"])>0)
			{
				$sortarray1 = $this->array_sort($sortarray1,$_POST["columname"],SORT_ASC);
			}

			foreach($sortarray1 as $key=>$valfile)
			{
				$rowcount++;

				$data.= '<tr class="row">
							<td  valign="top">'.$rowcount.'&nbsp;</td>
							<td  valign="top">'.$valfile['epid'].'&nbsp;</td>
							<td  valign="top">'.$valfile['first_name'].'&nbsp;</td>
							<td  valign="top">'.$valfile['last_name'].'&nbsp;</td>
							<td  valign="top">'.$valfile['admission_date'].'&nbsp;</td>
							<td  valign="top">'.$valfile['discharge_date'].'&nbsp;</td>
							<td  valign="top">'.$valfile['dischargelocation'].'&nbsp;</td>
							<td  valign="top">'.$valfile['address'].'&nbsp;</td>
							<td valign="top">'.$valfile['treatedby'].'&nbsp;</td></tr>';
			}

			$data.="</table>";

			if($radioarr[0]=="screen")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;
				echo $data;
				exit;
				echo "<SCRIPT type='text/javascript'>";
				echo "newwindow=window.open(location.href,'reportlist');";
				echo "newwindow.document.write(".$data.");//newwindow.document.close();window.location=location.href;";
				echo "</SCRIPT>";

			}elseif($radioarr[0]=="printing")
			{
				$data='<link href="'.APP_BASE.'css/reports.css" rel="stylesheet" type="text/css" />'.$data;

				echo $data;
				echo "<SCRIPT type='text/javascript'>";
				echo "window.print();";
				echo "</SCRIPT>";
				exit;

			}
		}

	}





	private function dischargePatient($whereepid,$montharr,$quarterarr,$yeararr){

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$finalipidval = array();

		$active_cond = $this->getTimePeriod($quarterarr,$yeararr,$montharr);


		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdischarged = 0')
		->andWhere('isdelete = 0')
		->andWhere('isstandbydelete = 0')
		->andWhere('isstandby = 0')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->Where($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$actipidarray = $actpatient->fetchArray();


		foreach($actipidarray as $key=>$val)
		{
			$finalipidval[]= $val['ipid'];
		}

		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where('isdischarged = 1')
		->andWhere('isdelete = 0')
		->andWhere('isstandbydelete = 0')
		->andWhere('isstandby = 0')
		->andWhere('('.str_replace('%date%','admission_date',$active_cond['admission_sql']).')')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->Where($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
		$ipidarray = $patient->fetchArray();


		if(is_array($ipidarray) && sizeof($ipidarray) > 0){

			foreach($ipidarray as $key=>$val)
			{
				$disipidval .= '"'.$val['ipid'].'",';
			}

			$disquery = Doctrine_Query::create()
			->select("*")
			->from('PatientDischarge')
			->where('ipid in ('.substr($disipidval,0,-1).') AND ('.str_replace('%date%', 'discharge_date', $active_cond['active_sql']).')');
			$disarray = $disquery->fetchArray();

			foreach($disarray as $key=>$val)
			{
				$finalipidval[]=$val['ipid'];
			}
		}

		$activeipid ="'0'";
		$comma=",";
		foreach($finalipidval as $keyip=>$valipid)
		{
			$activeipid.=$comma."'".$valipid."'";
			$comma=",";
		}

		return $activeipid;



	}

	private function dischargePatient_new($whereepid,$montharr,$quarterarr,$yeararr){

		$logininfo= new Zend_Session_Namespace('Login_Info');


		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
		$startdate = date("Y-m-d",strtotime($startdate));
		$enddate = date("Y-m-d",strtotime($enddate));

		$actpatient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0 and isstandbydelete = 0")
		//->andWhere('isdischarged = 0')
		// ->andWhere('isdischarged = 0 and '.$admtwhere)
		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$actpatientexec = $actpatient->execute();
		$actipidarray = $actpatientexec->toArray();
		$finalipidval = "'0'";
		$comma=",";
		foreach($actipidarray as $key=>$val)
		{
			$finalipidval.=$comma."'".$val['ipid']."'";
			$comma=",";
		}


		$patient = Doctrine_Query::create()
		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
		->from('PatientMaster p')
		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1 and isstandbydelete = 0")
		//->andWhere('isdischarged = 0')
		// ->andWhere('isdischarged = 0 and '.$admtwhere)
		->andWhere('isstandby = 0')
		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		//->andWhere('ipid in ('.$ipidval.')');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");

		$patientexec = $patient->execute();
		$ipidarray = $patientexec->toArray();
		$disipidval="'0'";
		$comma=",";
		foreach($ipidarray as $key=>$val)
		{
			$disipidval.=$comma."'".$val['ipid']."'";
			$comma=",";
		}
		$disquery = Doctrine_Query::create()
		->select("*")
		->from('PatientDischarge')
		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
		//echo  $disquery->getSqlQuery();
		$disexec = $disquery->execute();
		$disarray = $disexec->toArray();
		$finalipidval1 = "'0'";
		$comma1=",";
		foreach($disarray as $key=>$val)
		{
			$finalipidval.=$comma."'".$val['ipid']."'";
			$comma=",";
		}

		//print_r($finalipidval);
		/*			echo $finalipidval."<br />".$finalipidval;
			exit;*/
		return $finalipidval;

	}
	//private function dischargePatient($whereepid,$montharr,$quarterarr,$yeararr){
	//
	//		$logininfo= new Zend_Session_Namespace('Login_Info');
	//
	//
	//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
	//		$startdate = date("Y-m-d",strtotime($startdate));
	//		$enddate = date("Y-m-d",strtotime($enddate));
	//
	//		$actpatient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=0")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$actpatient->leftJoin("p.EpidIpidMapping e");
	//		$actpatient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$actpatient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$actpatientexec = $actpatient->execute();
	//		$actipidarray = $actpatientexec->toArray();
	//		$finalipidval = "'0'";
	//		$comma=",";
	//		foreach($actipidarray as $key=>$val)
	//		{
	//			$finalipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//
	//
	//		$patient = Doctrine_Query::create()
	//		->select("*,AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone,convert(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1) as sex")
	//		->from('PatientMaster p')
	//		->where("isdelete = 0 and admission_date<='".$enddate."' and isdischarged=1")
	//		//->andWhere('isdischarged = 0')
	//		// ->andWhere('isdischarged = 0 and '.$admtwhere)
	//		->andWhere('isstandby = 0')
	//		->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		//->andWhere('ipid in ('.$ipidval.')');
	//		$patient->leftJoin("p.EpidIpidMapping e");
	//		$patient->andWhere($whereepid.' e.clientid = '.$logininfo->clientid);
	//		$patient->orderBy("convert(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1) ASC");
	//
	//		$patientexec = $patient->execute();
	//		$ipidarray = $patientexec->toArray();
	//		$disipidval="'0'";
	//		$comma=",";
	//		foreach($ipidarray as $key=>$val)
	//		{
	//			$disipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//		$disquery = Doctrine_Query::create()
	//		->select("*")
	//		->from('PatientDischarge')
	//		->where("ipid in (".$disipidval.") and discharge_date>='".$startdate."'");
	//		//echo  $disquery->getSqlQuery();
	//		$disexec = $disquery->execute();
	//		$disarray = $disexec->toArray();
	//		$finalipidval1 = "'0'";
	//		$comma1=",";
	//		foreach($disarray as $key=>$val)
	//		{
	//			$finalipidval.=$comma."'".$val['ipid']."'";
	//			$comma=",";
	//		}
	//
	//		//print_r($finalipidval);
	//		/*			echo $finalipidval."<br />".$finalipidval;
	//			exit;*/
	//		return $finalipidval;
	//
	//	}

	private function treatedThreeWeeks(){

		$logininfo= new Zend_Session_Namespace('Login_Info');

		if(strlen($_POST['tweeks'])>0)
		{
			$days = $_POST['tweeks'];
		}else{
			$days = 1;
		}
		$whereepid = $this->getDocCondition();
		$pipid = Doctrine_Query::create()
		->select('ipid')
		->from('PatientMaster pm')
		->where('isdischarged = 0 and isdelete=0 and isstandby = 0 and isstandbydelete = 0')
		->andWhere("admission_date <= DATE_SUB('".date("Y-m-d H:i:s")."',INTERVAL ".($days-1)." DAY)")
		->leftJoin('pm.EpidIpidMapping ep')
		->andWhere($whereepid.' ep.clientid='.$logininfo->clientid)
		->andWhere('ep.ipid=pm.ipid')
		->orderBy('pm.admission_date DESC')
		->groupBy('pm.ipid');
		//	echo	$pipid->getSqlQuery();

		$ipidexec =	$pipid->execute();

		$ipidarray = $ipidexec->toArray();

		$comma=",";
		$ipidval="'0'";
		foreach($ipidarray as $key=>$val)
		{
			$ipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}

		return $ipidval;


	}

	private function patientNoForm(){

		$logininfo= new Zend_Session_Namespace('Login_Info');
		//		list($startdate,$enddate) = explode("-",$this->getQuarterperiods($quarterarr,$yeararr,$montharr));
		//		$startdate = date("Y-m-d",strtotime($startdate));
		//		$enddate = date("Y-m-d",strtotime($enddate));

		$whereepid = $this->getDocCondition();
		if($logininfo->clientid>0)
		{
			$clientid=$logininfo->clientid;
		}else{
			$clientid=0;
		}

		$pwoerarrary = $this->getReportFileData($clientid);
		//print_r($pwoerarrary);

		$comma=",";
		$fileipidval="'0'";
		foreach($pwoerarrary as $key=>$val)
		{
			$fileipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}


		$pipid = Doctrine_Query::create()
		->select('ipid')
		->from('PatientMaster pm')
		->where('isdelete=0')
		->andWhere("pm.ipid NOT IN (".$fileipidval.")")
		->leftJoin('pm.EpidIpidMapping ep')
		->andWhere($whereepid.' ep.clientid='.$logininfo->clientid)
		->andWhere('ep.ipid=pm.ipid')
		->orderBy('pm.admission_date DESC');

		/*$pipid = Doctrine_Query::create()
		 ->select('ipid')
		 ->from('PatientMaster pm')
		 ->where("isdelete=0 and isdischarged = 1 and pm.ipid NOT IN (".$fileipidval.")")
		 ->leftJoin('EpidIpidMapping ep')
		 ->andWhere($whereepid.' ep.clientid='.$logininfo->clientid)
		 ->andWhere('ep.ipid=pm.ipid')
		 ->orderBy('pm.admission_date DESC');*/

		//echo $pipid->getSqlQuery();

		$ipidexec =	$pipid->execute();

		$ipidarray = $ipidexec->toArray();


		$comma=",";
		$fipidval="'0'";
		foreach($ipidarray as $key=>$val)
		{
			$fipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}

		$disquery = Doctrine_Query::create()
		->select("ipid")
		->from('PatientDischarge')
		->where("ipid in (".$fipidval.")");
		//		->where("ipid in (".$fipidval.") and discharge_date>='".$startdate."' and discharge_date<='".$enddate."'");

		$disquery->getSqlQuery();
		$disexec = $disquery->execute();
		$disarray = $disexec->toArray();

		$comma=",";
		$disipidval="'0'";
		foreach($disarray as $key=>$val)
		{
			$disipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}
		//echo $disipidval;

		return $disipidval;

	}


	private function patientNoCDR(){

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$whereepid = $this->getDocCondition();
		$fileipidval = $this->getCourseDataForReport();

		$pipid = Doctrine_Query::create()
		->select('ipid')
		->from('PatientMaster pm')
		->where('isdelete=0 and isstandbydelete = 0')
		->andWhere("pm.ipid NOT IN (".$fileipidval.")")
		->leftJoin('pm.EpidIpidMapping ep')
		->andWhere($whereepid.' ep.clientid='.$logininfo->clientid)
		->andWhere('ep.ipid=pm.ipid')
		->orderBy('pm.admission_date DESC');

		$pipid->getSqlQuery();

		$ipidexec =	$pipid->execute();

		$ipidarray = $ipidexec->toArray();


		$comma=",";
		$ipidval="'0'";
		foreach($ipidarray as $key=>$val)
		{
			$ipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}

		return $ipidval;

	}


	private function allIpids($whereepid){

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$ipid = Doctrine_Query::create()
		->select('ipid')
		->from('EpidIpidMapping')
		->where($whereepid.'clientid = '.$logininfo->clientid);

		$ipidexec =	$ipid->execute();
		$ipidarray = $ipidexec->toArray();

		$comma=",";
		$ipidval="'0'";
		foreach($ipidarray as $key=>$val)
		{
			$ipidval .= $comma."'".$val['ipid']."'";
			$comma=",";
		}

		return $ipidval;


	}

	private function patientRelatedExcel($row){

		//echo "in excel";
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$html = array();
		//$html[]= "Patient_last_name,Patient_first_name,Birthdate(age),Admission_date,Last_update,TreatedBy";


		$this->xlsBOF();
		$this->xlsWriteLabel(0,0,$this->view->translate('no'));
		$this->xlsWriteLabel(0,1,$this->view->translate('epid'));
		$this->xlsWriteLabel(0,2,$this->view->translate("lastname"));
		$this->xlsWriteLabel(0,3,$this->view->translate("firstname"));
		$this->xlsWriteLabel(0,4,$this->view->translate("birthd")."( Jahre )");
		$this->xlsWriteLabel(0,5,$this->view->translate("zip"));
		$this->xlsWriteLabel(0,6,$this->view->translate("admissiondate"));
		if($_POST['reporttype']==3)
		{
			$this->xlsWriteLabel(0,7,utf8_decode($this->view->translate("dischargedate")));
		}else {
			$this->xlsWriteLabel(0,7,utf8_decode($this->view->translate("lastupdate")));
		}
		$this->xlsWriteLabel(0,8,$this->view->translate("treatedby"));
		$this->xlsWriteLabel(0,9,$this->view->translate("familydoctor"));
		$this->xlsWriteLabel(0,10,$this->view->translate("familydoctorphone"));
		if($_POST['reporttype']==3)
		{
			$this->xlsWriteLabel(0,11,$this->view->translate("dielocation"));
			$this->xlsWriteLabel(0,12,$this->view->translate("treateddays"));
		}
		if($_POST['reporttype']==3 && $_POST['reporttype']==4)
		{
			$this->xlsWriteLabel(0,10,$this->view->translate("treateddays"));
		}elseif($_POST['reporttype']==4){
			$this->xlsWriteLabel(0,10,$this->view->translate("treateddays"));
		}elseif($_POST['reporttype']==5){
			$this->xlsWriteLabel(0,10,$this->view->translate("treateddays"));
		}


		$xlsRow = 1;

		if(strlen($_POST["columname"])>0)
		{
			$row = $this->array_sort($row,$_POST["columname"],SORT_ASC);
		}
		foreach($row as $key=>$val)
		{
			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];

			$expld =explode("-",$val["birthd"]);
			$bdate = date("Y")."-".$expld[1]."-".$expld[2];

			$patm = new PatientMaster();
			$calage = $patm->GetTreatedDays($val["birthd"],$bdate,1);


			if($_POST['reporttype']==3)
			{
				$this->xlsWriteLabel($xlsRow,10,$val['dislocation']);
			}

			if($val["birthd"]!='0000-00-00'){$birthd=date("d.m.Y",strtotime($val["birthd"]));}else{$birthd="-";}
			if($val["admission_date"]!='0000-00-00 00:00:00'){$admissiondate=date("d.m.Y H:i:s",strtotime($val["admission_date"]));}else{$admissiondate="-";}
			if($val["last_update"]!='0000-00-00 00:00:00'){$lastupdate=date("d.m.Y H:i:s",strtotime($val["last_update"]));}else{$lastupdate="-";}


			if($_POST['reporttype']==3 || $_POST['reporttype']==4)
			{
				if($_POST['reporttype']==3)
				{

				}else{
					$this->xlsWriteNumber($xlsRow,10,$val['daystreated']);
					$treatdays +=$val['daystreated'];
				}


			}elseif($_POST['reporttype']==5)
			{
				$this->xlsWriteNumber($xlsRow,10,$val['daystreated']);
				$treatdays +=$val['daystreated'];

			}


			++$i;

			$this->xlsWriteNumber($xlsRow,0,"$i");
			$this->xlsWriteLabel($xlsRow,1,$gepid);
			$this->xlsWriteLabel($xlsRow,2,utf8_decode($val["last_name"]));
			$this->xlsWriteLabel($xlsRow,3,utf8_decode($val["first_name"]));
			$this->xlsWriteLabel($xlsRow,4,$birthd." (".$calage['years']." Jahre)");
			$this->xlsWriteLabel($xlsRow,5,$val["zip"]);
			$this->xlsWriteLabel($xlsRow,6,$admissiondate);
			if ($_POST['reporttype'] == 3){
				$this->xlsWriteLabel($xlsRow,7,utf8_decode($val['dischargedate']));
			}else{
				$this->xlsWriteLabel($xlsRow,7,$lastupdate);
			}
			$this->xlsWriteLabel($xlsRow,8,utf8_decode($val['treatedby']));
			$this->xlsWriteLabel($xlsRow,9,utf8_decode($val['familydoctor']));
			$this->xlsWriteLabel($xlsRow,10,$val['familydoctorphone']);
			if ($_POST['reporttype'] == 3){
			$this->xlsWriteLabel($xlsRow,11,$val['dislocation']);
			$this->xlsWriteLabel($xlsRow,12,$val['treatdays']);
			} else {

			}
			$xlsRow++;
		}

		if($_POST['reporttype']==3)
		{
						$this->xlsWriteLabel($xlsRow,3,$this->view->translate('Durchschnittliche Alter'));
					$this->xlsWriteNumber($xlsRow,4,number_format(($val['allages']/($xlsRow-1)),2,'.',''));
					$this->xlsWriteLabel($xlsRow,11,$this->view->translate('treatmentavgdays'));
					$this->xlsWriteNumber($xlsRow,12,number_format(($val['treatdays']/($xlsRow-1)),2,'.',''));

		}elseif($_POST['reporttype']==4)
		{
			$this->xlsWriteLabel($xlsRow,10,$this->view->translate('treatmentavgdays'));
			$this->xlsWriteNumber($xlsRow,11,number_format(($val['treatdays']/($xlsRow-1)),2,'.',''));
		}

		$this->xlsEOF();

		//exit;
		$fileName = "ispc_list.xls";
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-type: application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition: attachment; filename=".$fileName);
		//echo trim($html);
		exit;


	}

	private function patientRelatedScreen($row){

		$data="";
		$data.=' <table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">
								 <tr>
									<th width="10%">'.$this->view->translate('no').'</th>
									<th width="10%">'.$this->view->translate('epid').'</th>
									<th width="10%">'.$this->view->translate('lastname').'</th>
									<th width="10%">'.$this->view->translate('firstname').'</th>
									<th width="15%">'.$this->view->translate('birthd').' ('.$this->view->translate('age').')</th>
									<th width="15%">'.$this->view->translate("zip").'</th>
									<th width="15%">'.$this->view->translate('admissiondate').'</th>';
						if($_POST['reporttype']==3)
									{
						    $data.='<th width="15%">'.$this->view->translate('dischargedate').'</th>';
									}else{
						  $data.=	'<th width="15%">'.$this->view->translate('lastupdate').'</th>';}
							$data.=	'<th width="15%">'.$this->view->translate('treatedby').'</th>
									<th width="15%">'.$this->view->translate('familydoctor').'</th>
									<th width="15%">'.$this->view->translate('familydoctorphone').'</th>';

		if($_POST['reporttype']==3)
		{
			$data.= '<th width="15%">'.$this->view->translate('dielocation').'</th><th width="15%">'.$this->view->translate('treateddays').'</th>';
		}
		if($_POST['reporttype']==3 && $_POST['reporttype']==4)
		{
			$data.= '<th width="15%">'.$this->view->translate('dielocation').'</th><th width="15%">'.$this->view->translate('treateddays').'</th>';
		}elseif($_POST['reporttype']==4){
			$data.= '<th width="15%">'.$this->view->translate('treateddays').'</th>';
		}elseif($_POST['reporttype']==5){
			$data.= '<th width="15%">'.$this->view->translate('treateddays').'</th>';
		}elseif($_POST['reporttype']==16){
			$data.= '<th width="15%">'.$this->view->translate('treateddays').'</th>';
		}

		$data.='</tr>';

		if(strlen($_POST["columname"])>0)
		{
			$row = $this->array_sort($row,$_POST["columname"],SORT_ASC);
		}

		foreach($row as $key=>$val)
		{
			$rowcount++;
			if(is_array($val)){foreach($val as $k=>$v){if($v==""){$val[$k]="-";}}}
			//if($treatedby[$key]==""){$treatedby[$key]="-";}

			$expld =explode("-",$val["birthd"]);
			$bdate = date("Y")."-".$expld[1]."-".$expld[2];

			$patm = new PatientMaster();
			$calage = $patm->GetTreatedDays($val["birthd"],$bdate,1);


			if($val["birthd"]!='0000-00-00'){$birthd=date("d.m.Y",strtotime($val["birthd"]));}else{$birthd="-";}
			if($val["admission_date"]!='0000-00-00 00:00:00'){$admissiondate=date("d.m.Y H:i:s",strtotime($val["admission_date"]));}else{$admissiondate="-";}
			if($val["last_update"]!='0000-00-00 00:00:00'){$lastupdate=date("d.m.Y H:i:s",strtotime($val["last_update"]));}else{$lastupdate="-";}

			$epidipid = Doctrine::getTable('EpidIpidMapping')->findBy('ipid',$val["ipid"]);
			$epidipidarray = $epidipid->toArray();
			$gepid = $epidipidarray[0]['epid'];


			/*	if($_POST['reporttype']==3 || $_POST['reporttype']==4)
			 {
			 $dispat = Doctrine_Query::create()
			 ->select("*")
			 ->from("PatientDischarge")
			 ->where("ipid ='".$val['ipid']."'");
			 $dispatexec = $dispat->execute();
			 $disipidarray = $dispatexec->toArray();

			 $split = explode(" ",$disipidarray[0]['discharge_date']);
			 $bsplit = explode("-",$split[0]);
			 $dischargedate = $bsplit[2].".".$bsplit[1].".".$bsplit[0];
			 if($dischargedate=='00.00.0000'){$dischargedate="--";}
			 $daystreated ="";
			 $pms = new PatientMaster();

			 if($admissiondate!="-" && $dischargedate!="--" && $dischargedate!="..")
			 {
			 $admit = explode(" ",$admissiondate);
			 $daystreated = ((int)((strtotime($dischargedate)-strtotime($admit[0]))/(24*60*60))+1);

			 }
			 $treatdays +=$daystreated;

			 }*/



			$data.= "<tr class='row'>";
			$data.= "<td valignh='top'>".$rowcount."</td>
			<td valignh='top'>".$val['epid_num']."</td>
			<td  valign='top'>".$val["last_name"]."</td>
			<td  valign='top'>".$val["first_name"]."</td>
			<td  valign='top'>".$birthd." (".$calage["years"].")</td>
			<td  valign='top'>".$val['zip']."</td>
			<td  valign='top'>".$admissiondate."</td>";
		if($_POST['reporttype']==3){
			$data.= "<td  valign='top'>".$val['dischargedate']."</td>";
		} else{

			$data.= "<td  valign='top'>".$lastupdate."</td>";
		}

			$data.= "<td  valign='top'>".$val['treatedby']."</td>
			<td valign='top'>".$val['familydoctor']."&nbsp;</td>
			<td valign='top'>".$val['familydoctorphone']."&nbsp;</td>";

			if($_POST['reporttype']==3)
			{
				$data .="<td>".$val['dislocation']."</td><td>".$val['daystreated']."</td>";
			}
			if($_POST['reporttype']==4)
			{
				$data .="<td>".$val['daystreated']."</td>";
			}elseif($_POST['reporttype']==5)
			{
				$treatdays +=$val['daystreated'];
				$data .="<td valign='top'>".$val['daystreated']."&nbsp;</td>";
			}

			$data.= "</tr>";

		}
		if($_POST['reporttype']==3)
		{
			$data.= "<tr><td colspan='4' align='right'>".$this->view->translate('Durchschnitt Alter')."</td><td>".number_format(($val['allages']/($rowcount)),2,'.','')."</td><td colspan='7' align='right'>".$this->view->translate('treatmentavgdays')."</td><td>".number_format(($val['treatdays']/($rowcount)),2,'.','')."</td></tr>";

		}
		if($_POST['reporttype']==4)
		{
			$data.= "<tr><td colspan='11' align='right'>".$this->view->translate('treatmentavgdays')."</td><td>".number_format(($val['treatdays']/($rowcount)),2,'.','')."</td></tr>";

		}

		$data.="</table>";

		return $data;

	}

	private function getCourseDataForReport()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$ipidl = $this->allIpids("");

		$qpa1 = Doctrine_Query::create()
		->select("*,AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,
				              AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
		->from('PatientCourse')
		->where("ipid in(".$ipidl.") and course_type='".addslashes(Pms_CommonData::aesEncrypt("R"))."'");

		$qp1 = $qpa1->execute();

		if($qp1)
		{
			$newarr1=$qp1->toArray();
			$comma=",";
			$ipidval ="'0'";
			foreach($newarr1 as $key=>$valar)
			{
				$ipidval .= $comma."'".$valar['ipid']."'";
				$comma=",";
			}

			return $ipidval;

		}

	}


	public function permissionsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		if($this->getRequest()->isPost())
		{

			$doc = Doctrine_Query::create()
			->delete('ReportPermission')
			->where("clientid='".$clientid."'");
			$doc->execute();

			$over = new ReportPermission();
			$over->clientid = $clientid;
			$over->report_id = join(",",$_POST['boxid']);
			$over->save();
		}
		$cover = Doctrine::getTable('ReportPermission')->findBy('clientid',$clientid);
		$carray = $cover->toArray();
		$this->view->boxjs = $carray[0]['report_id'];
		//$this->view->closebox = $carray[0]['boxconditions'];
	}

	private function array_sort($array, $on=NULL, $order=SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0)
		{
			foreach ($array as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $k2 => $v2)
					{
						if ($k2 == $on)
						{
							if($on == 'birthd' || $on == 'admission_date' || $on == 'discharge_date' ) {
								$sortable_array[$k] = strtotime($v2);
							} elseif($on == 'epid_num') {
								$sortable_array[$k] = preg_replace ('/[^\d\s]/', '', $v2) ;
							} else {
								$sortable_array[$k] = ucfirst($v2);
							}
						}
					}
				} else
				{
					if($on == 'birthd' || $on == 'admission_date' || $on == 'discharge_date' ) {
						$sortable_array[$k] = strtotime($v);
					} elseif($on == 'epid_num') {
						$sortable_array[$k] = preg_replace ('/[^\d\s]/', '', $v) ;
					} else {
						$sortable_array[$k] = ucfirst($v);
					}
				}
			}

			switch ($order)
			{
				case SORT_ASC:
//					asort($sortable_array);
					$sortable_array = Pms_CommonData::a_sort($sortable_array);
					break;
				case SORT_DESC:
//					arsort($sortable_array);
					$sortable_array = Pms_CommonData::ar_sort($sortable_array);
					break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

		return $new_array;
	}
  //xx

}
?>