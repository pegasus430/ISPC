<?php

class KbvkeytabsController extends Zend_Controller_Action
{
	public function init()
	{
	}

	public function importkvbAction()
	{
		if($this->getRequest()->isPost())
		{
			ini_set("upload_max_filesize", "10M");
			if(strlen($_SESSION['filename'])<1){
				$this->view->error_message = $this->view->translate('uploadcsvfile');$error=1;
			}
			$filename= "uploadfile/".$_SESSION['filename'];
				
			if($error==0)
			{
				$xml = simplexml_load_file($filename,'SimpleXMLElement',LIBXML_NOCDATA);

				foreach($xml->keytab as $key=>$val)
				{
						
					foreach($xml->keytab->key as $ktkey=>$ktval)
					{

						$variable = $ktval->attributes();
							
						$import = new KbvKeytabs();
						$import->sn = htmlentities($val['SN']);
						$import->kbv_oid= $variable['S'];
						$import->version=$variable['SV'];
						$import->v=$variable['V'];
						$import->dn=htmlentities($variable['DN']);
						$import->valid='0';
						$import->save();
						$this->view->error_message =  $this->view->translate("importdonesucessfully");
					}
				}
					
				unset($_SESSION['filename']);
			}
		}
	}

	public function listkbvkeytabsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('KbvKeytabs',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");;
		}
		$oiddrop = Doctrine_Query::create()
		->select('DISTINCT (sn) as snd')
		->from('KbvKeytabs')
		->orderBy('sn ASC');
		$oiddropexec = $oiddrop->execute();
		$dropoid = array(""=>"Select ");
		foreach($oiddropexec->toArray() as $key=>$val)
		{

			$dropoid[$val['snd']] = $val['snd'];
				
		}
		$this->view->oid_array = $dropoid;

			
		$oiddrop = Doctrine_Query::create()
		->select('DISTINCT (version) as snd')
		->from('KbvKeytabs')
		->orderBy('version ASC');
		$oiddropexec = $oiddrop->execute();
		$dropversion = array(""=>"Select ");
		foreach($oiddropexec->toArray() as $key=>$val)
		{

			$dropversion[$val['snd']] = $val['snd'];
				
		}
		$this->view->version_array = $dropversion;


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


	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('KbvKeytabs',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
			
		$columnarray = array("pk"=>"id","oid"=>"kbv_oid","v"=>"v","ver"=>"version","dn"=>"dn","val"=>"valid");
		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");

		$this->view->{"style".$_GET['pgno']} = "active";
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$limit =50;
		$where = 'isdelete = 0 ';
		$sep = " and ";
		if(strlen($_GET['kbv_oid'])>0)
		{
			$where .=  $sep." sn ='".$_GET['kbv_oid']."'";
		}

		if(strlen($_GET['version'])>0)
		{
			$where .= $sep."version ='".$_GET['version']."'";
		}

		$oiddrop = Doctrine_Query::create()
		->select('DISTINCT (version) as snd')
		->from('KbvKeytabs')
		->where($where)
		->orderBy('version ASC');
		$oiddropexec = $oiddrop->execute();
		$dropversion = array(""=>"Select ");
		foreach($oiddropexec->toArray() as $key=>$val)
		{
			$dropversion[$val['snd']] = $val['snd'];
		}
			
		$version_array = $dropversion;

		$symptom = Doctrine_Query::create()
		->select('count(*)')
		->from('KbvKeytabs')
		->where($where)
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
		$symptomexec = $symptom->execute();
			
		$symptomarray = $symptomexec->toArray();
			
		$limit = 50;
			
		$symptom->select('*');
		$symptom->limit($limit);
		$symptom->offset($_GET['pgno']*$limit);
		$symptomexec = $symptom->execute();
		$symptolimitmarray = $symptomexec->toArray();
			
		$grid = new Pms_Grid($symptolimitmarray,1,$symptomarray[0]['count'],"listimportkbv.html");
		$this->view->kbvgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("kbvnavigation.html",5,$_GET['pgno'],$limit);
		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['kbvlist'] = $this->view->render('kbvkeytabs/fetchlist.html');
		$response['callBackParameters']['versionlist'] = $this->view->formSelect('version',$_GET['version'],array("onChange"=>"getKbvvalues(this);"),$version_array);
			
		echo json_encode($response);
		exit;
	}

	public function editkbvkeytabAction()
	{
		if($this->getRequest()->isPost())
		{
			if(strlen($_POST['kbv_oid'])<1){
				$this->view->error_kbv_oid=$this->view->translate('enteroid');$error=1;
			}
			if(strlen($_POST['version'])<1){
				$this->view->error_version =$this->view->translate('enterversion');$error=2;
			}
			if(strlen($_POST['v'])<1){
				$this->view->error_v=$this->view->translate('enterv');$error=3;
			}
			if(strlen($_POST['dn'])<1){
				$this->view->error_dn=$this->view->translate('enterdn');$error=4;
			}
				
			if($error==0)
			{
				$import = Doctrine::getTable('KbvKeytabs')->find($_GET['id']);
				$import->kbv_oid=$_POST['kbv_oid'];
				$import->version=$_POST['version'];
				$import->v=$_POST['v'];
				$import->dn=$_POST['dn'];
				$import->valid=$_POST['valid'];
				$import->save();
				$this->retainValues($_POST);
				$this->_redirect(APP_BASE.'kbvkeytabs/listkbvkeytabs?flg=suc');
			}
		}

		$kbv = Doctrine::getTable('KbvKeytabs')->find($_GET['id']);
			
		if($kbv)
		{
			$kbvarray = $kbv->toArray();
			if($kbvarray[0]['valid']==1)
			{
				$this->view->checked = 'checked="checked"';
			}
			$this->retainValues($kbvarray);
		}
	}

	public function deletekbvAction()
	{
		$this->_helper->viewRenderer('listkbvkeytabs');

		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($this->getRequest()->isPost())
		{

			if(count($_POST['kbv_id'])<1){
				$this->view->error_message =$this->view->translate('selectatleastone'); $error=1;
			}
				
			if($error==0)
			{

				foreach($_POST['kbv_id'] as $key=>$val)
				{

					$thrash = Doctrine::getTable('KbvKeytabs')->find($val);
					$thrash->isdelete = 1;
					$thrash->save();
						
				}
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");

			}
		}
			
		$oiddrop = Doctrine_Query::create()
		->select('DISTINCT (sn) as snd')
		->from('KbvKeytabs')
		->orderBy('sn ASC');
		$oiddropexec = $oiddrop->execute();
		$dropoid = array(""=>"Select ");
		foreach($oiddropexec->toArray() as $key=>$val)
		{

			$dropoid[$val['snd']] = $val['snd'];
				
		}
		$this->view->oid_array = $dropoid;
	}

	public function setvalidAction()
	{
		$this->_helper->viewRenderer('listkbvkeytabs');

		$logininfo= new Zend_Session_Namespace('Login_Info');


		if($this->getRequest()->isPost())
		{

			if(count($_POST['kbv_id'])<1){
				$this->view->error_message =$this->view->translate('selectatleastone'); $error=1;
			}
				
			if($error==0)
			{

				foreach($_POST['kbv_id'] as $key=>$val)
				{

					$thrash = Doctrine::getTable('KbvKeytabs')->find($val);
					$thrash->valid = 0;
					$thrash->save();
						
				}
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
		}
			
		$oiddrop = Doctrine_Query::create()
		->select('DISTINCT (sn) as snd')
		->from('KbvKeytabs')
		->orderBy('sn ASC');
		$oiddropexec = $oiddrop->execute();
		$dropoid = array(""=>"Select ");
		foreach($oiddropexec->toArray() as $key=>$val)
		{

			$dropoid[$val['snd']] = $val['snd'];
				
		}
		$this->view->oid_array = $dropoid;
	}

	public function setinvalidAction()
	{
		$this->_helper->viewRenderer('listkbvkeytabs');

		$logininfo= new Zend_Session_Namespace('Login_Info');


		if($this->getRequest()->isPost())
		{

			if(count($_POST['kbv_id'])<1){
				$this->view->error_message =$this->view->translate('selectatleastone'); $error=1;
			}
				
			if($error==0)
			{

				foreach($_POST['kbv_id'] as $key=>$val)
				{

					$thrash = Doctrine::getTable('KbvKeytabs')->find($val);
					$thrash->valid = 1;
					$thrash->save();
						
				}
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");

			}
		}
			
		$oiddrop = Doctrine_Query::create()
		->select('DISTINCT (sn) as snd')
		->from('KbvKeytabs')
		->orderBy('sn ASC');
		$oiddropexec = $oiddrop->execute();
		$dropoid = array(""=>"Select ");
		foreach($oiddropexec->toArray() as $key=>$val)
		{

			$dropoid[$val['snd']] = $val['snd'];
				
		}
		$this->view->oid_array = $dropoid;
	}

	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;

		}

	}
}

?>