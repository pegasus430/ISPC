<?php


	/**
	 *  PDFMerger created by Jarrod Nettles December 2009
	*  jarrod@squarecrow.com
	*
	*  v1.0
	*
	* Class for easily merging PDFs (or specific pages of PDFs) together into one. Output to a file, browser, download, or return as a string.
	* Unfortunately, this class does not preserve many of the enhancements your original PDF might contain. It treats
	* your PDF page as an image and then concatenates them all together.
	*
	* Note that your PDFs are merged in the order that you provide them using the addPDF function, same as the pages.
	* If you put pages 12-14 before 1-5 then 12-15 will be placed first in the output.
	*
	*
	* Uses FPDI 1.3.1 from Setasign
	* Uses FPDF 1.6 by Olivier Plathey with FPDF_TPL extension 1.1.3 by Setasign
	*
	* Both of these packages are free and open source software, bundled with this class for ease of use.
	* They are not modified in any way. PDFMerger has all the limitations of the FPDI package - essentially, it cannot import dynamic content
	* such as form fields, links or page annotations (anything not a part of the page content stream).
	*
	*/
	class Pms_PDFMerger
	{
        private $_files;	//['form.pdf']  ["1,2,4, 5-19"]
        private $_fpdi;
	
	    /**
	     * Merge PDFs.
	     * @return void
	     */
	    public function __construct()
	    {
	      	require_once('Phpdocx/lib/fpdi/fpdi.php');
	    }
	
	    /**
	     * Add a PDF for inclusion in the merge with a valid file path. Pages should be formatted: 1,3,6, 12-16.
	     * @param $filepath
	     * @param $pages
	     * @return void
	     */
	    public function addPDF($filepath, $pages = 'all')
	    {
	        if(file_exists($filepath))
	        {
	            if(strtolower($pages) != 'all')
	            {
	                $pages = $this->_rewritepages($pages);
	            }
	            	
	            $this->_files[] = array($filepath, $pages);
	        }
	        else
	        {
	            throw new exception("Could not locate PDF on '$filepath'");
	        }
	
	        return $this;
	    }
	
	    /**
	     * Merges your provided PDFs and outputs to specified location.
	     * @param $outputmode
	     * @param $outputname
	     * @return PDF
	     */
	    public function merge($outputmode = 'browser', $outputpath = 'newfile.pdf',$file_data = false)
	    {
	        
	        if(!isset($this->_files) || !is_array($this->_files)): throw new exception("No PDFs to merge."); endif;
	
	        $fpdi = new FPDI;
	
	        
	        $fpdi->setPrintHeader(false);
	        $fpdi->setPrintFooter(false);
	        
	        //merger operations
	        foreach ($this->_files as $file) {
                $filename = $file[0];
                $filepages = $file[1];
                
                $count = $fpdi->setSourceFile($filename);
                
                // add the pages
                if ($filepages == 'all') {
                    for ($i = 1; $i <= $count; $i ++) {
                        $template = $fpdi->importPage($i);
                        $size = $fpdi->getTemplateSize($template);
                        
                        if ($size['w'] > $size['h']) {
                            $fpdi->AddPage('L', array($size['w'], $size['h']));
                        } else {
                            $fpdi->AddPage('P', array($size['w'], $size['h']));
                        }
                        
                        $fpdi->useTemplate($template);
                    }
                } else {
                    foreach ($filepages as $page) {
                        if (! $template = $fpdi->importPage($page)) :
                            throw new exception("Could not load page '$page' in PDF '$filename'. Check that the page exists.");
                         endif;
                        $size = $fpdi->getTemplateSize($template);

                        if ($size['w'] > $size['h']) {
                            $fpdi->AddPage('L', array($size['w'], $size['h']));
                        } else {
                            $fpdi->AddPage('P', array($size['w'], $size['h']));
                        }
 
                        $fpdi->useTemplate($template);
                    }
                }
            }
            
            if(strlen($file_data['pdfname']) > '0' && (isset($file_data['password']) && strlen($file_data['password']) > '0'))
            {
                $pdfname = $file_data['pdfname'];
                $zip_password = $file_data['password'];
            
                $tmpstmp = $this->uniqfolder(PDF_PATH);
                $file_name_real = basename($tmpstmp);
                
                $path = PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf';
                
                $fpdi->Output($path, "F");    
                $pdf_filename = $tmpstmp . '/' . $pdfname . '.pdf';
            
                $cmd = "zip -9 -r -P " . $zip_password . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
            
                exec($cmd);
                $zipname = $file_name_real . ".zip";
                $filename = "uploads/" . $file_name_real . ".zip";
            
                /*
                $con_id = Pms_FtpFileupload::ftpconnect();
                if($con_id)
                {
                    $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
                    Pms_FtpFileupload::ftpconclose($con_id);
                }
                */
                
                
                
                $cust = new PatientFileUpload();
                $cust->title = Pms_CommonData::aesEncrypt($file_data['file_title']);
                $cust->ipid = $file_data['ipid'];
                $cust->file_name = Pms_CommonData::aesEncrypt($pdf_filename);
                $cust->file_type = Pms_CommonData::aesEncrypt('PDF');
                $cust->tabname = "merged_".$file_data['file_title'];
                $cust->save();
                
                //this file is allready zipped
                $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . "/" . $zipname , "uploads" ,
                		array(
                				"is_zipped" => true,
                				"file_name" => $pdf_filename,
                				"insert_id" => $cust->id,
                				"db_table"	=> "PatientFileUpload",
                		));
                
                //remove each file
                foreach($this->_files as $file)
                {
                    // $file[0] - link to file 
                    exec("rm " . $file[0] . ";");
                }
            }

            
            //ISPC-2609 Ancuta 29.09.2020
            if( isset($file_data['return_file_name']) && $file_data['return_file_name'] == '1'){
                
                $fpdi->Output($outputpath, "F");
                
                return $outputpath;
            } else{
                $fpdi->Output($outputpath, "D");
            }
    	}
	
    	/**
    	* FPDI uses single characters for specifying the output location. Change our more descriptive string into proper format.
    	* @param $mode
    	* @return Character
    	*/
    	private function _switchmode($mode)
    	{
            switch (strtolower($mode)) {
                case 'download':
                    return 'D';
                    break;
                case 'browser':
                    return 'I';
                    break;
                case 'file':
                    return 'F';
                    break;
                case 'string':
                    return 'S';
                    break;
                default:
                    return 'I';
                    break;
            }
        }
	
	    /**
	    * Takes our provided pages in the form of 1,3,4,16-50 and creates an array of all pages
	    * @param $pages
	    * @return unknown_type
	    */
    
	    private function _rewritepages($pages)
	    {
            $pages = str_replace(' ', '', $pages);
            $part = explode(',', $pages);
        
            // parse hyphens
            foreach ($part as $i) {
                $ind = explode('-', $i);
                
                if (count($ind) == 2) {
                    $x = $ind[0]; // start page
                    $y = $ind[1]; // end page
                    
                    if ($x > $y) :
                        throw new exception("Starting page, '$x' is greater than ending page '$y'.");
                        return false;
                     endif;
                    
                    // add middle pages
                    while ($x <= $y) :
                        $newpages[] = (int) $x;
                        $x ++;
                    endwhile
                    ;
                } else {
                    $newpages[] = (int) $ind[0];
                }
            }
            
            return $newpages;
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
    
    public function toFile($path)
    {
        $fpdi = new FPDI;
        $fpdi->Output($path, 'F');
    }
    
    
	}	
	
?>