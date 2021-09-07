<?

class PdfbackgroundController extends Zend_Controller_Action {

	public function listAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );


		if(!empty($_GET['delid']) && is_numeric($_GET['delid']))
		{
				
			$file = Doctrine_Query::create()
			->select('*')
			->from("PdfBackgrounds")
			->where("id= ?", intval($_GET['delid']));
			$filearr = $file->fetchArray();
			$filearr = $filearr[0];
				
			$file_path = PDFBG_PATH.'/'.$filearr['client'].'/'.$filearr['id'].'_'.$filearr['filename'];
			@unlink($file_path); //remove file from the server
				
			$ff = Doctrine_Query::create()
			->delete("PdfBackgrounds")
			->where("id= ?", intval($_GET['delid']));
			$ff->execute();
			$this->_redirect ( APP_BASE . 'pdfbackground/list');
		}

		$ff = Doctrine_Query::create()
		->select("*")
		->from("PdfBackgrounds")
		->orderBy('client ASC,date_added ASC');
		$ffarr = $ff->fetchArray();
		$clientarray = Pms_CommonData::getAllClientsDD ();
		$pdftypearray = Pms_CommonData::getPdfsAll ();
		foreach($ffarr as $key => $values){
			$ffarr[$key]['client_name'] = $clientarray[$values['client']];
			foreach($pdftypearray as $master => $type) {
				if(array_key_exists($values['pdf_type'], $type)) {
					$ffarr[$key]['pdf_type'] = $pdftypearray[$master][$values['pdf_type']];
					$ffarr[$key]['pdf_type_master'] = $master;
				}
			}
			$ffarr[$key]['date_added'] = date('d.m.Y H:i',$values['date_added']);
		}

		$grid = new Pms_Grid($ffarr,1,count($ffarr),"listpdfbgs.html");
		$this->view->listpdfbgs = $grid->renderGrid();


	}

	public function userbackgroundlistAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$userid = $logininfo->userid;

		if(!empty($_GET['delid']) && is_numeric($_GET['delid']))
		{

			$file = Doctrine_Query::create()
			->select('*')
			->from("UserPdfBackgrounds")
			->where("id= ?", intval($_GET['delid']));
			$filearr = $file->fetchArray();
			$filearr = $filearr[0];

			$file_path = PDFBG_PATH.'/'.$filearr['client'].'/'.$filearr['user'].'/'.$filearr['id'].'_'.$filearr['filename'];
			@unlink($file_path); //remove file from the server

			$ff = Doctrine_Query::create()
			->delete("UserPdfBackgrounds")
			->where("id= ?", intval($_GET['delid']));
			$ff->execute();
			$this->_redirect ( APP_BASE . 'pdfbackground/userbackgroundlist');
		}


		$ff = Doctrine_Query::create()
		->select("*")
		->from("UserPdfBackgrounds")
		->where('user = ?', $userid)
		->orderBy('client ASC,date_added ASC');
		$user_background_details = $ff->fetchArray();

		$pdftypearray = Pms_CommonData::getUserPdfs();


		foreach($user_background_details as $key => $values){
			foreach($pdftypearray as $master => $type) {
				if(array_key_exists($values['pdf_type'], $type)) {
					$user_background_details[$key]['pdf_type'] = $pdftypearray[$master][$values['pdf_type']];
					$user_background_details[$key]['pdf_type_master'] = $master;
				}
			}
			$user_background_details[$key]['date_added'] = date('d.m.Y H:i',$values['date_added']);
		}
		$user_details = Pms_CommonData::getUserData($userid);
		$user_name = $user_details[0]['last_name'].' '.$user_details[0]['first_name'];
		$this->view->user_name = $user_name;

		$this->view->user_background_details = $user_background_details;

	}

	public function addbackgroundAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );

		if ($this->getRequest ()->isPost ()) {
				
			$pdf_frm = new Application_Form_PdfAddBackground ();
				
			if ($pdf_frm->validate ( $_POST )) {
				$clean_path = strtolower( str_replace(' ','',$_FILES ['image'] ['name']) );
				$pdf_bg_id = $pdf_frm->InsertData ( $_POST, $clean_path );
				$this->view->error_message = $this->view->translate ( "recordinsertsucessfully" );
				$client_pdf_bg_path = PDFBG_PATH . '/' . $_POST ['client'];

				if (! is_dir ( $client_pdf_bg_path )) {
					mkdir ( $client_pdf_bg_path );
				}
				move_uploaded_file ( $_FILES ['image'] ['tmp_name'], $client_pdf_bg_path . '/' . $pdf_bg_id . '_' . $clean_path);
			} else {
				$pdf_frm->assignErrorMessages ();
			}

		}

		$this->view->clientarray = Pms_CommonData::getAllClientsDD ();
		$this->view->pdfarray = Pms_CommonData::getPdfsAll ();

	}

	public function adduserbackgroundAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		if ($this->getRequest ()->isPost ()) {

			$pdf_frm = new Application_Form_PdfAddBackground ();

			if ($pdf_frm->validate_user_form ( $_POST )) {
				$clean_path = strtolower( str_replace(' ','',$_FILES ['image'] ['name']) );
				$pdf_bg_id = $pdf_frm->InsertUserData ( $_POST, $clean_path );
				$this->view->error_message = $this->view->translate ( "recordinsertsucessfully" );
				$user_pdf_bg_path = PDFBG_PATH . '/' . $clientid. '/' . $userid;

				if (! is_dir ( $user_pdf_bg_path )) {
					mkdir ( $user_pdf_bg_path );
				}
				move_uploaded_file ( $_FILES ['image'] ['tmp_name'], $user_pdf_bg_path . '/' . $pdf_bg_id . '_' . $clean_path);
			} else {
				$pdf_frm->assignErrorMessages ();
			}
		}
		$this->view->pdfarray = Pms_CommonData::getUserPdfs ();

	}

	public function createformAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );

		if ($this->getRequest ()->isPost ()) {
				
			$frm = new Application_Form_CreatePdf ();
			$formid = $frm->InsertData ( $_POST );
				
			$this->view->formlink = APP_BASE . 'formbuilder/showpreview?frmid=' . $formid;
		}

		$frm = Doctrine_Core::getTable ( 'FbForms' )->findAll ();
		$frmarr = $frm->toArray ();

		$formsdd [0] = 'Select';

		foreach ( $frmarr as $key => $form ) {
			$formsdd [$form ['id']] = $form ['formname'];

		}

		$this->view->formsdd = $formsdd;

	}

	public function previewAction() {
		if ($this->getRequest ()->isPost ()) {

			$frm = new Application_Form_CreatePdf ();
			$fdarr = $frm->generateCollection ( $_POST );
				
			if (strlen ( $_POST ['altdimension'] ['width'] ) > 0 && strlen ( $_POST ['altdimension'] ['height'] ) > 0) {

				$document ['dimensions'] = array ('width' => $_POST ['altdimension'] ['width'], 'height' => $_POST ['altdimension'] ['height'] );
					
			} else {
				$dim = explode ( ",", $_POST ['dimensions'] );
				$document ['dimensions'] = array ('width' => $dim [1], 'height' => $dim [0] );
			}
				
			$onemm = 2.83;
				
			$document ['PDF_UNIT'] = 'pt';
			$document ['PDF_PAGE_FORMAT'] = 'A4';
				
			$document ['PDF_MARGIN_LEFT'] = '20';
			$document ['PDF_MARGIN_RIGHT'] = '20';
			$document ['PDF_MARGIN_HEADER'] = '20';
			$document ['PDF_MARGIN_FOOTER'] = '5';
			$document ['PDF_MARGIN_TOP'] = '5';
			$document ['PDF_MARGIN_BOTTOM'] = '5';
				
			$document ['PDF_FONT_SIZE_MAIN'] = '20';
			$document ['PDF_FONT_SIZE_DATA'] = '20';
			$document ['headerheight'] = $_POST ['headerheight'];
			$document ['footerheight'] = $_POST ['footerheight'];
			$document ['header'] = $_POST ['header'];
			$document ['footer'] = $_POST ['footer'];
				
			$this->view->document = $document;
				
			$fb = new Pms_PdfBuilder ();
			$fileds = $fb->buildvalues ( Pms_LinkedFields::getContents ( $fdarr, $_GET ), 1 );
				
			foreach ( $fileds as $key => $field ) {
				$this->view->tablewidth = strlen ( $field ['dimwidth'] ) > 0 ? $field ['dimwidth'] * $onemm : $document ['dimensions'] ['width'] * $onemm;
				$this->view->tableheight = strlen ( $field ['dimheight'] ) > 0 ? $field ['dimheight'] * $onemm : 0;

				$this->view->item = $field;
				$field ['html'] = $this->view->render ( 'pdfdesigner/pdfhtml.html' );
				$columns [$field ['pageno']] [] = $field;
					
			}
				
			ksort ( $columns );
				
			$pdf = new Pms_TCPDF ( PDF_PAGE_ORIENTATION, $document ['PDF_UNIT'], NULL, true, 'UTF-8', false );
			$fontsize = 3 * $onemm;
				
			$pdf->SetFont ( 'times', '', $fontsize );
				
			// set document information
			$pdf->SetCreator ( 'IPSC' );
			$pdf->SetAuthor ( 'ISPC' );
			$pdf->SetTitle ( 'ISPC' );
			$pdf->SetSubject ( 'ISPC' );
			$pdf->SetKeywords ( 'ISPC' );
				
			// set default header data
			$pdf->SetHeaderData ( PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING );
				
			// set header and footer fonts
			$pdf->setHeaderFont ( Array ($document ['PDF_FONT_SIZE_MAIN'], '', $document ['PDF_FONT_SIZE_MAIN'] ) );
			$pdf->setFooterFont ( Array ($document ['PDF_FONT_SIZE_DATA'], '', $document ['PDF_FONT_SIZE_DATA'] ) );
				
			// set default monospaced font
			$pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );
				
			//set margins
			$pdf->SetMargins ( $document ['PDF_MARGIN_LEFT'], $document ['PDF_MARGIN_TOP'], $document ['PDF_MARGIN_RIGHT'] );
			$pdf->setPrintHeader ( false );
			$pdf->setPrintFooter ( false );

			//set auto page breaks
			$pdf->SetAutoPageBreak ( TRUE, $document ['PDF_MARGIN_BOTTOM'] );
				
			//set image scale factor
			$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
				
			//set some language-dependent strings
			$pdf->setLanguageArray ( 'DE' );
				
			// ---------------------------------------------------------
				

			// set font

			foreach ( $columns as $page ) {
				$pdf->AddPage ( PDF_PAGE_ORIENTATION, array ($document ['dimensions'] ['width'] * $onemm, $document ['dimensions'] ['height'] * $onemm ) );

				$pdf->writeHTMLCell ( $document ['dimensions'] ['width'] * $onemm, $document ['headerheight'] * onemm, '', '', $document ['header'], 0, 1, 0, true, 'J' );

				foreach ( $page as $field ) {
					if ($field ['ishide'] == 1)
						continue;
						
					$cellwidth = strlen ( $field ['dimwidth'] ) > 0 ? ($field ['dimwidth'] * $onemm) - $document ['PDF_MARGIN_RIGHT'] : ($document ['dimensions'] ['width'] * $onemm) - $document ['PDF_MARGIN_RIGHT'];
					$cellheight = strlen ( $field ['dimheight'] ) > 0 ? $field ['dimheight'] * $onemm : 0;
						
					$cellx = strlen ( $field ['posx'] ) > 0 && $field ['posx'] > 0 ? ( int ) (($document ['PDF_MARGIN_LEFT'] + $field ['posx']) * $onemm) : '';
					$celly = strlen ( $field ['posy'] ) > 0 && $field ['posy'] > 0 ? ( int ) (($document ['PDF_MARGIN_TOP'] + $field ['posy'] + $document ['headerheight']) * $onemm) : '';
						
					$pdf->writeHTMLCell ( $cellwidth, $cellheight, $cellx, $celly, $field ['html'], 0, 1, 0, true, 'J' );
				}

				$pdf->writeHTMLCell ( $document ['dimensions'] ['width'] * $onemm, $document ['footerheight'] * onemm, '', '', $document ['footer'], 0, 1, 0, true, 'J' );
					
			}
				
			$pdf->Output ( 'pdfpreview/ispc.pdf', 'F' );
				
			$response = array ();
			$response ['previewpath'] = APP_BASE . 'pdfpreview/ispc.pdf';
			$response ['success'] = 'success';
				
			echo json_encode ( $response );
			exit ();
		}

	}

	public function getformelementsAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );

		if ($this->getRequest ()->isPost ()) {
				
			$frm = new Application_Form_CreateForm ();
			$formid = $frm->UpdateData ( $_POST );
				
			$this->view->formlink = APP_BASE . 'formbuilder/showpreview?frmid=' . $_GET ['frmid'];
		}

		$frm = Doctrine_Core::getTable ( 'FbForms' )->find ( $_GET ['frmid'] );
		$frmarr = $frm->toArray ();

		$this->view->formname = $frmarr ['formname'];

		$fd = Doctrine_Query::create ()
		->select ( "*" )
		->from ( 'FbFormFields' )
		->where ( "formid= ?", $_GET ['frmid'] )
		->orderBy ( "columnno ASC" );
		$frme = $fd->execute ();
		$fdarr = $frme->toArray ();


		$fb = new Pms_PdfBuilder ();
		$fileds = $fb->build ( Pms_LinkedFields::getContents ( $fdarr, $_GET ) );

		$noofgroups = count ( $columns );
		$grid = new Pms_Grid ( $fileds, 1, count ( $fileds ), "pdffieldlist.html" );
		$fieldlist = $grid->renderGrid ();

		$response = array ();
		$response ['fieldlist'] = $fieldlist;

		echo json_encode ( $response );
		exit ();

	}

	public function createelementsAction() {
		$fb = new Pms_PdfBuilder ();

		$action = (isset ( $_GET ['action'] )) ? $_GET ['action'] : null;

		switch ($action) {
			case 'properties' :
				$fb->properties ( $_GET );
				break;
			case 'element' :
				$fb->element ( $_GET );
				break;
			case 'linkedfields' :
				$fb->linkedfields ( $_GET );
				break;
			default :
				break;
		}

	}

}

?>