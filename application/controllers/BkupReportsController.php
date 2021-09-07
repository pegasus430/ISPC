<?php

class ReportsController extends Zend_Controller_Action
{
	public function init()
	{
	}

	public	function exportlistAction()
	{
		set_time_limit(0);
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->view->usertype = $logininfo->usertype;

		if($this->getRequest()->isPost())
		{
			if($logininfo->usertype=='SA')
			{
				if(strlen($_POST['clientid'])<1){
					$this->view->error_clientid="Select Client "; $error=1;
				}
				if(strlen($_POST['listname'])<1){
					$this->view->error_listname="Select list to create report";$error=1;
				}
				if($_POST['byyear_chk']=="on"){
					if(strlen($_POST['filteryear'])<1){
						$this->view->error_year="Enter year";$error=1;
					}
				}
				else
				{
					if(strlen($_POST['fromdate'])<1){
						$this->view->error_fromdate="Enter from date";$error=1;
					}
					if(strlen($_POST['todate'])<1){
						$this->view->error_todate="Enter To date";$error=1;
					}
				}
			}else{
				if(strlen($_POST['listname'])<1){
					$this->view->error_listname="Select list to create report";$error=1;
				}
				if($_POST['byyear_chk']=="on"){
					if(strlen($_POST['filteryear'])<1){
						$this->view->error_year="Enter year";$error=1;
					}
				}
				else
				{
					if(strlen($_POST['fromdate'])<1){
						$this->view->error_fromdate="Enter from date";$error=1;
					}
					if(strlen($_POST['todate'])<1){
						$this->view->error_todate="Enter To date";$error=1;
					}
				}
			}
				
			if($error==0)
			{
				$manager = Doctrine_Manager::getInstance();
				if($logininfo->usertype=='SA')
				{
					$client = Doctrine_Query::create()
					->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
							AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
							,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
							->from('Client')
							->where('id= ? ', $_POST['clientid'] );
					$clientexec = $client->execute();
					$clientarray = $clientexec->toArray();
					$clientname = trim($clientarray[0]['client_name']);
				}else{
					$clientname = trim($logininfo->clientname);
				}
				$this->_helper->layout->setLayout('layout_report');
				$this->_helper->viewRenderer->setNoRender();
				$this->_helper->layout->disableLayout();

				$fromdate = $_POST['fromdate'];
				$fromdatearr = explode(".",$fromdate);

				$todate = $_POST['todate'];
				$todatearr = explode(".",$todate);
				$todatearr[0] = $todatearr[0]+1;

				if($_POST['listname']=="PatientList")
				{

					$manager->setCurrentConnection("IDAT");
					$conn =  $manager->getCurrentConnection();
						
					$table="patient_master";
					$qrystr="";
						
					if($_POST['byyear_chk']=="on")
					{
						$startdate = $_POST['filteryear']."-01-01";
						$enddate = $_POST['filteryear']."-12-31";
						$qrystr="SELECT ipid,last_name,first_name,birthd,admission_date,last_update FROM ".$table." WHERE admission_date BETWEEN '".$startdate."' AND '".$enddate."'";
					}
					else
					{
						$startdate = $fromdatearr[2]."-".$fromdatearr[1]."-".$fromdatearr[0];
						$enddate = $todatearr[2]."-".$todatearr[1]."-".$todatearr[0];
						$qrystr="SELECT ipid,last_name,first_name,birthd,admission_date,last_update FROM ".$table." WHERE admission_date BETWEEN '".$startdate."' AND '".$enddate."'";;
					}
						
					$queryrow = $conn->prepare($qrystr);
					$queryrow->execute();
						
					$row = $queryrow->fetchAll();
					$treatedby=array();
						
					foreach($row as $key=>$val)
					{
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
							foreach($treatarray as $key=>$val)
							{
								$user = Doctrine::getTable('User')->find($val['userid']);
								$userarray = $user->toArray();
									
								$uname .= $br.$userarray['last_name']." ".$userarray['first_name'];
								$br = ";";
							}
						}
						$treatedby[]=$uname;
					}
						
						
					$html = array();
					$html[]= "Patient_last_name,Patient_first_name,Birthdate(age),Admission_date,Last_update,TreatedBy";
						
					function calculateage($birthday)
					{
						list($year,$month,$day) = explode("-",$birthday);
						$year_diff  = date("Y") - $year;
						$month_diff = date("m") - $month;
						$day_diff   = date("d") - $day;
						if ($day_diff < 0 || $month_diff < 0)
							$year_diff--;
						return $year_diff;
					}
					foreach($row as $key=>$val)
					{
						$calage = calculateage($val["birthd"]);
						$html[] = $val["last_name"].",".$val["first_name"].",".$val["birthd"]."(".$calage."),".$val["admission_date"].",".$val["last_update"].",".$treatedby[$key];
					}
						
					$html =  implode("\r\n", $html);
					$fileName = $clientname."_".$_POST['listname'].".csv";
					header("Content-type: application/vnd.ms-excel");
					header("Content-Disposition: attachment; filename=$fileName");
					echo $html;
					exit;
				}
			}
				
		}
	}
}
?>