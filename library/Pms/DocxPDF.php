<?php

	/* PDF generating class, agregating all features, uses TcPDF */

	set_time_limit(0);

	/**
	 * main configuration file
	 */
	 require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/config/lang/ger.php');
	 require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/config/tcpdf_config.php');

// includes some support files

	/**
	 * unicode data
	 */
//	 require_once(APPLICATION_PATH . '/../library/Pms/Tcpdf/unicode_data.php');

	/**
	 * html colors table
	 */
	 // Commented by Ancuta - 18.11.2019 <- Comentezi? // Maria:: Migration ISPC to CISPC 08.08.2020
	 //require_once(APPLICATION_PATH . '/../library/PHPDocx9.5/lib/pdf/tcpdf/htmlcolors.php');
	 require_once(APPLICATION_PATH . '/../library/PHPDocx9.5/classes/TCPDF_lib.php');

	class Pms_docxpdf extends TCPDF {

		public $pdf, $orientation, $bottom_margin;
		public $BackgroundImage = false;
		public $HeaderText = false;
		public $SubHeaderText = false;
		public $AssessmentHeaderText = false;
		
		//RWH - added this variable to reset the header margins for all pages except first page
		public $first_page_header = false;
		
		public $footer_text=FALSE;
 
		public $firstpagebackground = null;// 19.02.2014
		
		public $invoice_footer = false;// 17.12.2015
		
		public $no_first_page_invoice_footer = false;// 06.01.2016
		
		public function setDefaults($header = false, $orientation = 'P', $bottom_margin = 20)
		{ //set some default options
			// create new PDF document
			//$this = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			// set document information
			$this->SetCreator(PDF_CREATOR);
//		$pdf->SetAuthor('Nicola Asuni');
//		$pdf->SetTitle('TCPDF Example 001');
//		$pdf->SetSubject('TCPDF Tutorial');
//		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
			// set default header data
			//$this->SetHeaderData('', 0, '111', '001', '123123');
			// set header and footer fonts
//		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			// remove default header/footer
			//$this->setPrintHeader(true);
			//$this->setPrintFooter(false);
			// set default monospaced font
			$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

			//set margins
			//$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			if($header === true)
			{
				$this->setPrintHeader(true);
				$this->SetHeaderMargin(55);
				$this->SetMargins(20, 95, 50); //set top margin to prevent overlap with header
				$this->setHeaderFont(Array('helvetica', 'B', 8));
			}
			else
			{
				$this->setPrintHeader(false);
				$this->SetMargins(20, 55, 50);
			}
			//$this->SetFooterMargin(PDF_MARGIN_FOOTER);
			//set auto page breaks
			if(empty($bottom_margin) || !is_numeric($bottom_margin))
			{
				$bottom_margin = 20;
			}
			$this->SetAutoPageBreak(TRUE, $bottom_margin);
			$this->bottom_margin = $bottom_margin;

			//set image scale factor
			$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
			//$this->setImageScale(1.5);
			//$this->setImageScale(0.47); //set for HTML
			//set some language-dependent strings
			//$this->setLanguageArray($l);
			// ---------------------------------------------------------
			// set default font subsetting mode
			$this->setFontSubsetting(false);

			// Set font
			// dejavusans is a UTF-8 Unicode font, if you only need to
			// print standard ASCII chars, you can use core fonts like
			// helvetica or times to reduce file size.
			//$this->SetFont('dejavusans', '', 14, '', true);
			// set font
			$this->SetFont('helvetica', '', 8);
			$this->SetCellPadding(0);
			$this->orientation = $orientation;
			// Add a page
			// This method has several options, check the source code documentation for more information.
			//$this->AddPage();
// Print text using writeHTMLCell()
//		$this->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			//$this->BackgroundImage = $BackgroundImage;
			//echo $BackgroundImage;
		}

		public function setBackgroundImage($BackgroundImage = false)
		{
			$this->BackgroundImage = $BackgroundImage;
		}

		public function setHeaderText($HeaderText = false)
		{
			$this->HeaderText = $HeaderText;
		}
		
		public function setSubHeaderText($SubHeaderText = false)
		{
			$this->SubHeaderText = $SubHeaderText;
		}
		
		public function setAssessmentHeaderText($AssessmentHeaderText = false)
		{
			$this->AssessmentHeaderText = $AssessmentHeaderText;
		}
		public function setfooter_text($footerText = false)
		{
			$this->footer_text = $footer_text;
		}
		
		public function Header()
		{ // Maria:: Migration ISPC to CISPC 08.08.2020
		    $tcpdf_static = new TCPDF_STATIC();
		    $page_size = $tcpdf_static::getPageSizeFromFormat($this->format);
			$page_dimension=$this->getPageDimensions();
						
			//RWH - get page number
			$page_no = $this->PageNo();
			
			if($this->BackgroundImage !== false  && (!$this->firstpagebackground || $page_no == 1) )// 19.02.2014 - if background it is needed only for the first page 
			{
				if($_REQUEST['dbg_pdf'])
				{
					print_r("Orientation\n");
					print_r($this->orientation);
					
					print_r("Background Image\n");
					print_r($this->BackgroundImage);
					
					print_r("Page Dimensions\n");
					print_r($this->getPageDimensions());

					print_r("Page Size???\n");
					print_r($tcpdf_static->getPageSizeFromFormat($this->format));

					exit;
				}
//				echo $this->BackgroundImage;
//				exit;
				$this->SetAutoPageBreak(false, 0);
				$this->Image($this->BackgroundImage, 0, 0, $page_dimension['wk'], $page_dimension['hk'], '', '', '', false, 300, '', false, false, 0);
				
				$this->SetAutoPageBreak(TRUE, $this->bottom_margin);
			}
			
			//RWH - reset margins before first page is printed
			if($this->first_page_header === true)
			{				
				if($page_no > '1')
				{
					//make sure there is no header text to be writen
					$this->HeaderText = false;
					$this->SubHeaderText = false;
					$this->AssessmentHeaderText = false;
					
					//default margins to be set
					$this->SetMargins(20, 20, 30);
					
				}
			}

			if($this->AssessmentHeaderText !== false)
			{
				if($this->orientation == 'P')
				{
					$this->writeHTMLCell('', '', $x = 20, $y = 12, $this->AssessmentHeaderText, $border = 'B', $ln = 1, $fill = 0, $reseth = true, $align = 'C', $autopadding = false);
				}
			}
			
			if($this->HeaderText !== false)
			{

				if($this->orientation == 'P')
				{
					$this->writeHTMLCell(85, 40, $x = '', $y = '', $this->HeaderText, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = false);
				}
				else
				{
					$this->writeHTMLCell(255, 40, $x = '', $y = '', $this->HeaderText, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = false);
				}
			}
			
			if($this->SubHeaderText !== false)
			{
				if($this->orientation == 'P')
				{
					$this->writeHTMLCell(85, 40, $x = '' , $y = '102', $this->SubHeaderText, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = false);
				}
				//landscape -- not used yet
				else
				{
				}
			}
			
//			$this->Cell(10, 10, '098797908709870', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//			if($this->BackgroundImage !== false && $this->HeaderText !== false) {
//				$this->setPrintHeader(false);
//			}
		}

		public function toBrowser($title, $style = 'I')
		{
			$this->Output($title . '.pdf', $style);
		}

		public function toFile($path)
		{
			$this->Output($path, 'F');
		}

		public function toFTP($path)
		{
			$this->Output($path, 'F');
		}

		public function setHTML($html, $orientation = false)
		{
//			$this->Output($path, 'F');
//			$this->SetCellPadding(1);
//			$this->setCellHeightRatio(0.9);
			if($orientation)
			{
				$this->AddPage($orientation);
			}
			else
			{
				$this->AddPage();
			}

			$html = <<<EOD
			$html
EOD;
			$this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
			//$this->writeHTML($html, true, false, true, false, '');
			$this->lastPage();
		}

		public function uniqfolder($path)
		{
			$i = 0;
			$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
			while(!is_dir($path . '/' . $dir))
			{
				$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
				mkdir($path . '/' . $dir);
				if($i >= 50)
				{
					exit; //failsafe
				}
				$i++;
			}

			return $dir;
		}

//		public function setBackground($img_file) {
//			//$bMargin = $this->getBreakMargin();
//	        //$auto_page_break = $this->AutoPageBreak;
//	        $this->SetAutoPageBreak(false, 0);
//	        $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
//	        $this->SetAutoPageBreak(TRUE, 15);
//		    //$this->SetAutoPageBreak($auto_page_break, $bMargin);
//		}
//	
//		public function tcpdf_set($call){
//			$args = func_get_args();
//			$this->$call($args[1],$args[2],$args[3],$args[4]); //there shouldn't be more than 4 arguments
//		}
		// Page footer
		public function Footer() {
		    
		    if($this->invoice_footer )
		    {
		        $hide_footer = false;
		         
		        if($this->no_first_page_invoice_footer && count($this->pages) === 1){
		            $hide_footer = true;
		        }
		    
		        if(!$hide_footer){
		            // Position at 15 mm from bottom
		            $this->SetY(-33);
		            // Set font
		            //$this->SetFont('helvetica', 'I', 8);
		            //     			$this->setFooterFont(Array('helvetica', '', 7));
		            // Page number
		             
		            $this->writeHTMLCell(0, 0, '', '', $this->footer_text, $border = '0', $ln = 0, $fill = 0, $reseth = true, $align = 'l', $autopadding = false);
		        }
		    } 
		    else
		    {
    			// Position at 15 mm from bottom
    			$this->SetY(-15);
    			// Set font
    			//$this->SetFont('helvetica', 'I', 8);
    			// Page number
    			
    			$this->writeHTMLCell(0, 0, '', '', $this->footer_text, $border = 'T', $ln = 0, $fill = 0, $reseth = true, $align = 'R', $autopadding = false);
		    }    
		
		
		}
	}

?>