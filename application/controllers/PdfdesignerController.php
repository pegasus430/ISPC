<?

class PdfdesignerController extends Zend_Controller_Action
{
	public function createformAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($this->getRequest()->isPost())
		{

			$frm = new Application_Form_CreatePdf();
			$formid = $frm->InsertData($_POST);
				
			$this->view->formlink = APP_BASE.'formbuilder/showpreview?frmid='.$formid;
		}


		$frm = Doctrine_Core::getTable('FbForms')->findAll();
		$frmarr = $frm->toArray();

		$formsdd[0]='Select';

		foreach($frmarr as  $key=>$form)
		{
			$formsdd[$form['id']]=$form['formname'];

		}

		$this->view->formsdd = $formsdd;


	}

	public function previewAction()
	{
		if($this->getRequest()->isPost())
		{
			$frm = new Application_Form_CreatePdf();
			$fdarr = $frm->generateCollection($_POST);
				
			if(strlen($_POST['altdimension']['width'])>0 && strlen($_POST['altdimension']['height'])>0){
					
				$document['dimensions'] = array('width'=>$_POST['altdimension']['width'],'height'=>$_POST['altdimension']['height']);

			}else{
				$dim = explode(",",$_POST['dimensions']);
				$document['dimensions'] = array('width'=>$dim[1],'height'=>$dim[0]);
			}
				
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
			$document['headerheight'] = $_POST['headerheight'];
			$document['footerheight'] = $_POST['footerheight'];
			$document['header'] = $_POST['header'];
			$document['footer'] = $_POST['footer'];
				
			$this->view->document = $document;

			$fb = new Pms_PdfBuilder();
			$fileds = $fb->buildvalues(Pms_LinkedFields::getContents($fdarr,$_GET),1);
				
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
				
			//set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, $document['PDF_MARGIN_BOTTOM']);
				
			//set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
				
			//set some language-dependent strings
			$pdf->setLanguageArray('DE');
				
				
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
				
			$pdf->Output('pdfpreview/ispc.pdf', 'F');
				
			$response = array();
			$response['previewpath'] = APP_BASE.'pdfpreview/ispc.pdf';
			$response['success'] = 'success';
				
			echo json_encode($response);
			exit;

		}

	}

	public function getformelementsAction()
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

		$fb = new Pms_PdfBuilder();
		$fileds = $fb->build(Pms_LinkedFields::getContents($fdarr,$_GET));

		$noofgroups = count($columns);
		$grid = new Pms_Grid($fileds,1,count($fileds),"pdffieldlist.html");
		$fieldlist = $grid->renderGrid();

		$response = array();
		$response['fieldlist'] = $fieldlist;

		echo json_encode($response);
		exit;

	}

	public function createelementsAction()
	{
		$fb = new Pms_PdfBuilder;

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


}

?>