<?

class ClientpatientController extends Zend_Controller_Action
{
	 
	public function init()
	{
		/* Initialize action controller here */
	}

	public function patientlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patient',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
	}
	 

	public function fetchlistAction()
	{
		 
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$clist = Doctrine_Query::create()
		->select("*,lower(CONVERT(AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') using latin1)) as client_name,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as clientname")
		->from('Client c')
		->where('c.isdelete=0')
		->orderBy('h__0 ASC');
		$clistexecarray = $clist->fetchArray();
		$view->adminclientarray = $clistexecarray;
		 
		$clientarray = array("0"=>"Select Client");
		foreach($clistexecarray as $key=>$val)
		{
			$clientarray[$val['id']] = $val['clientname'];

		}
		$adminclientarray = $clientarray;
			

		$columnarray = array("pk"=>"id","fn"=>"p__0","ln"=>"p__2","ad"=>"p__admission_date","ledt"=>"p__change_date","bd"=>"p__birthd");
		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");

		$this->view->order = $orderarray[$_GET['ord']];

		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$patient = Doctrine_Query::create()
		->select('count(*)')
		->from('PatientMaster p')
		->where("isdischarged = 0 and isdelete = 0");
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient->andWhere('e.ipid = p.ipid');
		$patientarray = $patient->fetchArray();
			
			
		$limit = 50;
		$patient->select("e.*,ipid,birthd,admission_date,change_date,last_update,CONVERT(AES_DECRYPT(first_name,'".Zend_Registry::get('salt')."') using latin1) as first_name,
				CONVERT(AES_DECRYPT(middle_name,'".Zend_Registry::get('salt')."') using latin1)  as middle_name,
				CONVERT(AES_DECRYPT(last_name,'".Zend_Registry::get('salt')."') using latin1)  as last_name,
				CONVERT(AES_DECRYPT(title,'".Zend_Registry::get('salt')."') using latin1)  as title,
				CONVERT(AES_DECRYPT(salutation,'".Zend_Registry::get('salt')."') using latin1)  as salutation,
				CONVERT(AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') using latin1)  as street1,
				CONVERT(AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') using latin1)  as street2,
				CONVERT(AES_DECRYPT(zip,'".Zend_Registry::get('salt')."') using latin1)  as zip
				,CONVERT(AES_DECRYPT(city,'".Zend_Registry::get('salt')."') using latin1)  as city
				,CONVERT(AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') using latin1) as phone
				,CONVERT(AES_DECRYPT(mobile,'".Zend_Registry::get('salt')."') using latin1)  as mobile
				,CONVERT(AES_DECRYPT(sex,'".Zend_Registry::get('salt')."') using latin1)  as gensex");
		$patient->limit($limit);
		$patient->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
		$patient->offset($_GET['pgno']*$limit);
		 
		$patientlimit = $patient->fetchArray();
 
		$grid = new Pms_Grid($patientlimit,1,$patientarray[0]['count'],"listclientpatient.html");
		$grid->clients=$adminclientarray;
		$this->view->patientgrid = $grid->renderGrid();
			
		$this->view->navigation = $grid->dotnavigation("clientpatientnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['patientlist'] = $this->view->render('clientpatient/fetchlist.html');
			
		echo json_encode($response);
		exit;

	}

	public function updateclientAction()
	{

		$this->_helper->viewRenderer('patientlist');
			
		$q = Doctrine_Query::create()
		->update('EpidIpidMapping')
		->set('clientid',$_GET['cid'])
		->where("epid=?", $_GET['epid']);
		$q->execute();

	}


}




?>