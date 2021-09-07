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
	 require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/unicode_data.php');

	/**
	 * html colors table
	 */
	 require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/htmlcolors.php');
	 require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/tcpdf.php');
	 
	class Pms_pdf extends TCPDF {

		public $pdf, $orientation, $bottom_margin;
		public $BackgroundImage = false;
		public $HeaderText = false;
		
		public $SubHeaderText = false;
		public $SubHeaderText_pages = false; // this will include the $SubHeaderText
		
		protected $original_tMargin = 0;
		
		
		public $AssessmentHeaderText = false;
		
		//RWH - added this variable to reset the header margins for all pages except first page
		public $first_page_header = false;
		
		public $footer_text=FALSE;
 
		public $firstpagebackground = null;// 19.02.2014
		
		public $invoice_footer = false;// 17.12.2015
		
		public $no_first_page_invoice_footer = false;// 06.01.2016
		
		private $footer_text_type = false;//'1 of n';
		
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
		
		/**
		 * (non-PHPdoc)
		 * Aug 4, 2017 @claudiu 
		 * 
		 * @see TCPDF::SetMargins()
		 */
		public function SetMargins($left, $top, $right=-1, $keepmargins=false) {
			
			parent::SetMargins($left, $top, $right, $keepmargins);
			
			if ($keepmargins) {
				$this->original_tMargin = $this->tMargin;// overwrite original values
			}
		}
		
		public function Header()
		{
			//Maria:: Migration CISPC to ISPC 22.07.2020
            if(isset ($this->customheader) && is_callable($this->customheader)){
                $my_fun=$this->customheader;
                $my_fun($this);
                return;
            }

			//$page_size = TCPDF_STATIC::getPageSizeFromFormat($this->format);
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
					print_r($this->getPageSizeFromFormat($this->format));

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

				$html = $this->HeaderText;
				
				// append extra text to the header
				// this variable is used so you can filter the pages on withc this text to be shown
				if($this->SubHeaderText !== false && $this->SubHeaderText_pages !== false )
				{
					if($this->SubHeaderText_pages === true)
					{
						$html .= PHP_EOL . $this->SubHeaderText;
							
					} else {
						if( (is_array($this->SubHeaderText_pages) && in_array($page_no, $this->SubHeaderText_pages))
								|| ($this->SubHeaderText_pages == "first_page_only" && $page_no == 1)
								|| ($this->SubHeaderText_pages == "not_first_page" && $page_no > 1))
						{
							$html .= PHP_EOL . $this->SubHeaderText;
							$this->SetMargins($this->original_rMargin, $this->original_tMargin+5, $this->original_lMargin);//this needs to be changed
						}
					}
				}
					
				
				
				if($this->orientation == 'P')
				{
					$this->writeHTMLCell(85, 40, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = false);
				}
				else
				{
					$this->writeHTMLCell(255, 40, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = false);
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
			$title = $this->clean_filename($title);

			$this->Output($title . $ext, $style);
		}

		public function toFile($path)
		{
			//$title = $this->clean_filename($title);
			$this->Output($path, 'F');
		}

		public function toFTP( $pdfname , $legacy_path = "uploads", $is_zipped = NULL, $foster_file = false , $clientid = NULL, $filepass = NULL)
		{
			
			$pdfname = $this->clean_filename($pdfname);
			
			/**
			 * ! $pdfname used as filename if not sanitized here 
			 * Naming Files, Paths, and Namespaces
			 * https://msdn.microsoft.com/en-us/library/aa365247
			 */
			$tmpstmp = $this->uniqfolder(PDF_PATH);
			
			$file_path = PDF_PATH . "/" . $tmpstmp . '/' . $pdfname ;
			
			$this->Output($file_path, 'F');
							
			$result =  Pms_CommonData::ftp_put_queue( $file_path , $legacy_path , $is_zipped, $foster_file, $clientid, $filepass);

			if ($result !== false) {
				return $tmpstmp . '/' . $pdfname ;
			} else {
				return false;
			}
			
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
	       elseif($this->footer_text_type !== false) 
	       {
	       		//this else was introduced by ISPC-1976
		       	$this->SetY(-8);// Position at 15 mm from bottom
				//$this->SetFont($this->getFontFamily(), 'I', 8);// Set font
	 	       	$this->SetFontSize(7);
		       	if ($this->footer_text_type == "1 of n") {
	
		       		$text = sprintf($this->footer_text, $this->getAliasNumPage() , $this->getAliasNbPages());
	          		$this->Cell(0, 10, $text, 0, false, 'C', 0, '', 0, false, 'T', 'M');     
	
		       	} 
		       	elseif ($this->footer_text_type == "1 of n date") {
	
		       		$text = sprintf($this->footer_text, $this->getAliasNumPage() , $this->getAliasNbPages(), date("d.m.Y H:i", $this->doc_creation_timestamp));
	          		$this->Cell(0, 10, $text, 0, false, 'C', 0, '', 0, false, 'T', 'M');     
	
		       	}
		       	elseif ($this->footer_text_type == "1 of n date 12px") { //ISPC - 2321 - 04.02.2019
		       		$this->SetFontSize(12);
		       		$text = sprintf($this->footer_text, $this->getAliasNumPage() , $this->getAliasNbPages(), date("d.m.Y H:i", $this->doc_creation_timestamp));
		       		$this->Cell(0, 10, $text, 0, false, 'C', 0, '', 0, false, 'T', 'M');
		       	
		       	}
		       	elseif ($this->footer_text_type == "1") {
		       		$this->Cell(0, 10, $this->getAliasNumPage(), 0, false, 'R', 0, '', 0, false, 'T', 'M');	       		
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
		
		//clean filename and extension
		private function clean_filename( $filename =  "ispc_download.pdf")
		{
			//sanitize filename
			$filename = Pms_CommonData::filter_filename($filename, true);
			
			if ( ($ext = strtolower( substr($filename, strlen($filename) - 4, 4))) != '.pdf') {
				$filename .= '.pdf';
			}
			
			return $filename;
		}
	

		
		public function setFooterType( $type )
		{
		
			if (!preg_match("/^(1 of n)|(1)|(1 of n date)\z/", $type)) {
				$this->footer_text_type = false;
			} else {
				$this->footer_text_type = $type;
			}
			
			//throw new Exception("Only '1 of n' , '1', '1 of n date' are allowed.");
			
		}

		
		public function getFooterType( $type )
		{
			return $this->footer_text_type;
		}
	
	}

?>