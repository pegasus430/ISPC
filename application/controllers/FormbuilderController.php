<?

class FormbuilderController extends Zend_Controller_Action
{
	public function createformAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($this->getRequest()->isPost())
		{
				
			$frm = new Application_Form_CreateForm();
			$formid = $frm->InsertData($_POST);
				
			$this->view->formlink = APP_BASE.'formbuilder/showpreview?frmid='.$formid;
		}

	}

	public function editformAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($this->getRequest()->isPost())
		{

			$frm = new Application_Form_CreateForm();
			$formid = $frm->UpdateData($_POST);
				
			$this->view->formlink = APP_BASE.'formbuilder/showpreview?frmid='.$_GET['frmid'];
		}

		$frm = Doctrine_Core::getTable('FbForms')->find($_GET['frmid']);
		$frmarr = $frm->toArray();

		$this->view->formname = $frmarr['formname'];

		$fd = Doctrine_Query::create()
		->select("*")
		->from('FbFormFields')
		->where("formid= ?", $_GET['frmid'])
		->orderBy("columnno ASC");
		$frme = $fd->execute();
		$fdarr = $frme->toArray();

		$fb = new Pms_FormBuilder();
		$fileds = $fb->build(Pms_LinkedFields::getContents($fdarr,$_GET));

		$columns = array();

		foreach($fileds  as $key=>$field)
		{
			$columns[$field['columnno']]['groupname'] = "Group ".$field['columnno'];
			$columns[$field['columnno']]['fields'][] = $field;

		}

		$this->view->noofgroups = count($columns);
		$grid = new Pms_Grid($columns,1,count($columns),"fbcolumnlist.html");
		$this->view->fieldlist = $grid->renderGrid();
	}

	public function createelementsAction()
	{
		$fb = new Pms_FormBuilder;

		$action = (isset($_GET['action'])) ? $_GET['action'] : null;

		switch ($action) {
			case 'properties':
				$fb->properties($_GET);
				break;
			case 'element':
				$fb->element($_GET);
				break;
			case 'linkedfields':
				$fb->linkedfields($_GET);
				break;
			default:
				break;
		}

	}

	public function showformAction()
	{
		if($this->getRequest()->isPost())
		{
			if(strlen($_POST['submit1'])>0)
			{
				$this->generatePdf();
			}
				
			$fs = new Application_Form_StoreForm();
				
			if($fs->validate($_POST))
			{
				$fs->InsertData($_POST);
			}else{
					
				$fs->assignErrorMessages();
			}
		}
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$patientmaster = new PatientMaster();
		$this->view->patientinfo =$patientmaster->getMasterData($decid,1);

		$frm = Doctrine_Core::getTable('FbForms')->find($_GET['frmid']);
		$frmarr = $frm->toArray();

		$this->view->formname = $frmarr['formname'];


		$fd = Doctrine_Query::create()
		->select("*")
		->from('FbFormFields')
		->where("formid= ?", $_GET['frmid'])
		->orderBy("columnno ASC");
		$frme = $fd->execute();
		$fdarr = $frme->toArray();


		$eipd = $_GET['id'];

		$fb = new Pms_FormBuilder();
		$fileds = $fb->build(Pms_LinkedFields::getContents($fdarr,$_GET));

		foreach($fileds  as $key=>$field)
		{
			$columns[$field['columnno']][] = $field;
		}



		$clsid = $_GET['frmid'];
		$this->view->items =  $columns;
		$this->view->{'extclass'.$clsid} = "active";

		$q = Doctrine_Query::create()
		->select("p.*,fp.*")
		->from('FormPdfs fp')
		->innerjoin("fp.PdfForms p")
		->where("fp.formid= ?", $_GET['frmid']);
		$qe = $q->execute();
		$qarray = $qe->toArray();


		$grid = new Pms_Grid($qarray,1,count($qarray),"formpdflist.html");
		$this->view->pdflist = $grid->renderGrid();

	}

	public function showpreviewAction()
	{
		if($this->getRequest()->isPost())
		{
			$fs = new Application_Form_StoreForm();
				
			if($fs->validate($_POST))
			{
				$fs->InsertData($_POST);
			}else{
					
				$fs->assignErrorMessages();
			}
		}

		$frm = Doctrine_Core::getTable('FbForms')->find($_GET['frmid']);
		$frmarr = $frm->toArray();

		$this->view->formname = $frmarr['formname'];


		$fd = Doctrine_Query::create()
		->select("*")
		->from('FbFormFields')
		->where("formid= ?", $_GET['frmid'])
		->orderBy("columnno ASC");
		$frme = $fd->execute();
		$fdarr = $frme->toArray();

		$fb = new Pms_FormBuilder();
		$fileds = $fb->build(Pms_LinkedFields::getContents($fdarr,$_GET));

		foreach($fileds  as $key=>$field)
		{
			$columns[$field['columnno']][] = $field;
		}
		$this->view->items =  $columns;
	}

	public function listformsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($_GET['did']>0)
		{
			$ff = Doctrine_Query::create()
			->update("FbForms")
			->set('isdelete',1)
			->where("id='".$_GET['did']."'");
			$ff->execute();
		}

		$ff = Doctrine_Query::create()
		->select("*")
		->from("FbForms")
		->where("isdelete=0");
		$ffe = $ff->execute();
		$ffarr = $ffe->toArray();

		$grid = new Pms_Grid($ffarr,1,count($ffarr),"listforms.html");
		$this->view->listforms = $grid->renderGrid();
	}

	public function formpdfsAction()
	{

		if($this->getRequest()->isPost())
		{
			$q = Doctrine_Query::create()
			->delete("FormPdfs")
			->where("formid= ?", $_GET['frmid']);
			$q->execute();
				
			if(is_array($_POST['formpdf']))
			{

				foreach($_POST['formpdf'] as $key=>$val)
				{
					$fc = new FormPdfs();
					$fc->formid= $_GET['frmid'];
					$fc->pdfid = $val;
					$fc->save();
				}
					
			}
				
			$this->_redirect(APP_BASE.'formbuilder/listforms');
		}

		$pdfs = Doctrine_Core::getTable('PdfForms')->findBy('formid',$_GET['frmid']);
		$qarray = $pdfs->toArray();


		$q = Doctrine_Core::getTable('FormPdfs')->findBy('formid',$_GET['frmid']);

		$clarr = array();

		foreach($q->toArray() as $key=>$val)
		{
			$clarr[] = $val['pdfid'];

		}

		$grid = new Pms_Grid($qarray,1,count($qarray),"formpdfcheckbox.html");
		$grid->clarr = $clarr;
		$this->view->listpdfs = $grid->renderGrid();

	}

	public function setclientsAction()
	{
		if($this->getRequest()->isPost())
		{
			$q = Doctrine_Query::create()
			->delete("FbFormClients")
			->where("formid= ?", $_GET['frmid']);
			$q->execute();
				
			if(is_array($_POST['clientid']))
			{

				foreach($_POST['clientid'] as $key=>$val)
				{
					$fc = new FbFormClients();
					$fc->formid= $_GET['frmid'];
					$fc->clientid = $val;
					$fc->save();
				}
					
			}
				
			$this->_redirect(APP_BASE.'formbuilder/listforms');
		}



		$q = Doctrine_Core::getTable('FbFormClients')->findBy('formid',$_GET['frmid']);


		$clarr = array();

		foreach($q->toArray() as $key=>$val)
		{
			$clarr[] = $val['clientid'];

		}


		$q = Doctrine_Query::create()
		->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
				AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
				,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
				->from('Client')
				->where('isdelete=0');
		$qexec = $q->execute();
		$qarray = $qexec->toArray();
		$grid = new Pms_Grid($qarray,1,count($qarray),"clientlistcheckbox.html");
		$grid->clarr = $clarr;
		$this->view->listclients = $grid->renderGrid();
	}


	public function generatepdfAction()
	{

		$onemm = 2.83;
			
		$document['PDF_UNIT'] = 'pt';
		$document['PDF_PAGE_FORMAT'] = 'A4';
			
		$document['PDF_MARGIN_LEFT'] = '20';
		$document['PDF_MARGIN_RIGHT'] = '20';
		$document['PDF_MARGIN_HEADER'] = '20';
		$document['PDF_MARGIN_FOOTER'] = '5';
		$document['PDF_MARGIN_TOP'] = '5';
		$document['PDF_MARGIN_BOTTOM'] = '5';
			
		$document['PDF_FONT_SIZE_MAIN'] = '20';
		$document['PDF_FONT_SIZE_DATA'] = '20';
			


		$pd = Doctrine::getTable('PdfForms')->find($_GET['pdfid']);
		$pdarr = $pd->toArray();



		if($pdarr['dimensionwidth']>0 && $pdarr['dimensionheight']>0){

			$document['dimensions'] = array('width'=>$pdarr['dimensionwidth'],'height'=>$pdarr['dimensionheight']);
				
		}else{
			$dim = explode(",",$pdarr['dimension']);
			$document['dimensions'] = array('width'=>$dim[1],'height'=>$dim[0]);

		}


		$document['header'] = $pdarr['header'];
		$document['footer'] = $pdarr['footer'];
		$document['headerheight'] = $pdarr['headerheight'];
		$document['footerheight'] = $pdarr['footerheight'];


			
		$fd = Doctrine_Query::create()
		->select("*")
		->from('PdfFields')
		->where("pdfid='".$_GET['pdfid']."'")
		->orderBy("pageno ASC");
		$frme = $fd->execute();
		$fdarr = $frme->toArray();


		$eipd = $_GET['id'];

		$fb = new Pms_PdfBuilder();
		$fileds = $fb->buildvalues(Pms_LinkedFields::getContents($fdarr,$_GET,true));

		foreach($fileds  as $key=>$field)
		{
			$this->view->tablewidth =  strlen($field['dimwidth'])>0 ? $field['dimwidth']*$onemm:$document['dimensions']['width']*$onemm;
			$this->view->tableheight =  strlen($field['dimheight'])>0 ? $field['dimheight']*$onemm:0;
				
			$this->view->item = $field;
			$field['html'] = $this->view->render('pdfdesigner/pdfhtml.html');
			$columns[$field['pageno']][] = $field;
				
		}

		ksort($columns);

			
		$pdf = new Pms_TCPDF(PDF_PAGE_ORIENTATION, $document['PDF_UNIT'], NULL, true, 'UTF-8', false);
		$fontsize =  3*$onemm;
			
		$pdf->SetFont('times', '', $fontsize);

		// set document information
		$pdf->SetCreator('IPSC');
		$pdf->SetAuthor('ISPC');
		$pdf->SetTitle('ISPC');
		$pdf->SetSubject('ISPC');
		$pdf->SetKeywords('ISPC');
			
		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
			
		// set header and footer fonts
		$pdf->setHeaderFont(Array($document['PDF_FONT_SIZE_MAIN'], '', $document['PDF_FONT_SIZE_MAIN']));
		$pdf->setFooterFont(Array($document['PDF_FONT_SIZE_DATA'], '', $document['PDF_FONT_SIZE_DATA']));
			
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			
		//set margins
		$pdf->SetMargins($document['PDF_MARGIN_LEFT'], $document['PDF_MARGIN_TOP'], $document['PDF_MARGIN_RIGHT']);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		//$pdf->SetHeaderMargin($document['PDF_MARGIN_HEADER']);
		//$pdf->SetFooterMargin($document['PDF_MARGIN_FOOTER']);
			
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, $document['PDF_MARGIN_BOTTOM']);
			
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			
		//set some language-dependent strings
		$pdf->setLanguageArray('DE');
			
		// ---------------------------------------------------------
			
		foreach($columns as $page)
		{
			$pdf->AddPage(PDF_PAGE_ORIENTATION,array($document['dimensions']['width']*$onemm,$document['dimensions']['height']*$onemm));

			$pdf->writeHTMLCell($document['dimensions']['width']*$onemm,$document['headerheight']*onemm, '', '', $document['header'], 0, 1, 0, true, 'J');
				
			foreach($page as $field)
			{
				if($field['ishide']==1) continue;
					
					
				$cellwidth =  strlen($field['dimwidth'])>0 ? ($field['dimwidth']*$onemm)-$document['PDF_MARGIN_RIGHT']:($document['dimensions']['width']*$onemm)-$document['PDF_MARGIN_RIGHT'];
				$cellheight =  strlen($field['dimheight'])>0 ? $field['dimheight']*$onemm:0;
					
				$cellx = strlen($field['posx'])>0 && $field['posx']>0 ? (int)(($document['PDF_MARGIN_LEFT'] + $field['posx'])*$onemm):'';
				$celly =  strlen($field['posy'])>0 && $field['posy']>0 ? (int)(($document['PDF_MARGIN_TOP'] + $field['posy']+$document['headerheight'])*$onemm): '';
					
				$pdf->writeHTMLCell($cellwidth,$cellheight, $cellx, $celly, $field['html'], 0, 1, 0, true, 'J');
			}

			$pdf->writeHTMLCell($document['dimensions']['width']*$onemm,$document['footerheight']*onemm, '', '', $document['footer'], 0, 1, 0, true, 'J');

		}


		$pdf->Output('ispc.pdf', 'D');
		exit;




	}
}

?>