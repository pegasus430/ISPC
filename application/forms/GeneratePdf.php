<?php require_once("Pms/Form.php");
class Application_Form_GeneratePdf extends Pms_Form
{
		
	public function generatePdf($post,$filename)
	{
 		$htmlform = Pms_Template::createTemplate($post,'templates/'.$filename);
		
		$pdf = new Pms_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

		// set document information
		$pdf->SetCreator('IPSC');
		$pdf->SetAuthor('ISPC');
		$pdf->SetTitle('ISPC');
		$pdf->SetSubject('ISPC');
		$pdf->SetKeywords('ISPC');
		
		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
		
		//set some language-dependent strings
		$pdf->setLanguageArray($l); 
		
		// ---------------------------------------------------------
		
		// set font
		$pdf->SetFont('times', '', 10);
		
		// add a page
		$pdf->AddPage('P','A4');
				
		$pdf->writeHTML($htmlform, true, 0, true, 0);
				
		$pdf->Output('test.pdf', 'D');
		exit;
	}
}

?>