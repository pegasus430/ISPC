<?php

class Pms_Oldpdf {
    
    public $pdf, $config;
    
    function __construct() {

        require 'Oldpdf/fpdf_js.php';
//         require 'Oldpdf/fpdf.php';

        $this->config = new Zend_Config_Ini("Oldpdf/sizes.ini", "kvrezept");
        
    }
    
    public function generate($data,$download = true, $upload = false){

        $angle = 90;

        $receipt_type = $data['receipt_type'];
        $patient['last_name'] = $data['patientlastname'];
        $patient['first_name'] = $data['patientfirstname'];
        $patient['street'] = $data['street'];
        $patient['zipcity'] = $data['zipcode_city'];
        $patient['versnummer'] = $data['insuranceno'];
        $patient['birthd'] = $data['birthdate'];
        $patient['versstatus'] = $data['status'];
        $patient['vkgueltigbis'] = "09/12";
        $patient['kassennummer'] = $data['kassenno'];
        $krankenkasse = $data['insurancecomname'];
        $krankenkasse = $data['insurancecomname'];
        // $lanr = $data['lanr'];
        $ik_nr = $data['insurance_ik_number'];
        $ik = "260590106";
        //$datum = date("d.m.y");
        $datum = $data['datum'];
        // $bsnr = $data['betriebsstatten_nr'];
        
        $bsnr = $data['betriebsstatten_nr'];
        $lanr = $data['lanr'];
        // new
        $valid_till = $data['valid_till'];
        	        
        $this->pdf = new FPDF_JavaScript('P', 'mm', array($this->position(148000), $this->position(105000)));
        $this->pdf->AddPage();
        
        $this->setText($datum, "datum");
        $this->setText($patient['last_name'], "patientnachname");
        $this->setText($patient['first_name'], "patientvorname");
        $this->setText($patient['street'], "patientstrasse");
        $this->setText($patient['zipcity'], "patientzipcity");
        $this->setText($patient['birthd'], "patientbirthd");
        $this->setText($patient['versnummer'], "patientversichertennummer");
        $this->setText($patient['versstatus'], "patientstatus");
        $this->setText($krankenkasse, "krankenkasse");
        $this->setText($ik_nr, "iknummer");
        $this->setText($patient['kassennummer'], "patientkassennummer");
        $this->setText($bsnr, "betriebsstaette");
        $this->setText($lanr, "vertragsarztnummer");
        //boxes on the left
        if(in_array(1,$data['getiuhrfrei'])){$this->setText("X", "boxGF");}
        if(in_array(2,$data['getiuhrfrei'])){$this->setText("X", "boxGP");}
        if(in_array(3,$data['getiuhrfrei'])){$this->setText("X", "boxNC");}
        if(in_array(4,$data['getiuhrfrei'])){$this->setText("X", "boxSO");}
        if(in_array(5,$data['getiuhrfrei'])){$this->setText("X", "boxUN");}
        if(in_array(6,$data['getiuhrfrei'])){$this->setText("X", "boxAR");}
        // boxes top right
        if($data['bvg'] != 0){ $this->setText("X", "boxBVG");}
        if($data['mttel'] != 0){ $this->setText("7", "boxHIL");}// ISPC-2947 Ancuta 16.06.2021 - changed from X to relevant number 
        if($data['soff'] != 0){ $this->setText("8", "boxIMP");}// ISPC-2947 Ancuta 16.06.2021 - changed from X to relevant number 
        if($data['bedaf'] != 0){ $this->setText("9", "boxSPR");}// ISPC-2947 Ancuta 16.06.2021 - changed from X to relevant number 
        if($data['pricht'] != 0){ $this->setText("X", "boxBEG");}
        
        if(in_array(7,$data['getiuhrfrei'])){
        
            $this->setText("X", "boxREZ");//the box on the left (Rezeptur)
            $this->setText($data['othertext'], "othertext","J");
            $this->setText($data['userstamp1']."\n".$data['userstamp2']."\n".$data['userstamp3']."\n".$data['userstamp4']."\n".$data['userstamp5']."\n".$data['userstamp6']."\n".$data['userstamp7'], "stempel", "C");
        } else{
            //boxes on the left (autidem)
            if(in_array(8,$data['getiuhrfrei'])){$this->setText("X", "boxAU1");}
            if(in_array(9,$data['getiuhrfrei'])){$this->setText("X", "boxAU2");}
            if(in_array(10,$data['getiuhrfrei'])){$this->setText("X", "boxAU3");}
            // medication lines
            $this->setText($data['med1'], "medizeile1");
            $this->setText($data['med2'], "medizeile2");
            $this->setText($data['med4'], "medizeile3");
            $this->setText($data['med5'], "medizeile4");
            $this->setText($data['med7'], "medizeile5");
            if(is_array($data['med8'])){
                $this->setText($data['med8'][0], "medizeile6");
                $this->setText($data['med8'][1], "medizeile6b");
            } else{
                $this->setText($data['med8'], "medizeile6");
            }
            
            $this->setText($data['userstamp1']."\n".$data['userstamp2']."\n".$data['userstamp3']."\n".$data['userstamp4']."\n".$data['userstamp5']."\n".$data['userstamp6']."\n".$data['userstamp7'], "stempel", "C");
        }
        
        //ISPC-2711 + TODO-4034 Ancuta 12.04.2021
        if($data['btm_a_symbol'] == 1){ $this->setText("A", "boxBTMA");}
        //--
        
 
        if($upload){
            if(strlen($data['pdfname']) > '0' && strlen($data['password']) > '0')
            {
                $pdfname = $data['pdfname'];
                $zip_password = $data['password'];
            
                $tmpstmp = $this->uniqfolder(PDF_PATH);
                $file_name_real = basename($tmpstmp);
            
                $this->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
                $pdf_filename = $tmpstmp . '/' . $pdfname . '.pdf';
            
                $cmd = "zip -9 -r -P " . $zip_password . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;
            
                exec($cmd);
                $zipname = $file_name_real . ".zip";
                $filename = "uploads/" . $file_name_real . ".zip";
            
                /* $con_id = Pms_FtpFileupload::ftpconnect();
                if($con_id)
                {
                    $upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
                    Pms_FtpFileupload::ftpconclose($con_id);
                } */
                
                $ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . "/" . $zipname , "uploads" ,
                		array(
                				"is_zipped" => true,
                				"file_name" => $pdf_filename,
                				"insert_id" => NULL,
                				"db_table"	=> "PatientFileUpload",
                		));
            
                $return_data['pdf_filename'] = $pdf_filename;
                $return_data['zip_filename'] = $filename;
                return $return_data;
            } else{
                return false;
            }
        }
        
        
        
        ob_end_clean();
        ob_start();
        if($download){

            // set javascript
            if($data['auto_print'])
            {
                   $this->pdf->IncludeJS("print('true');");
            }
            
            
            $this->pdf->Output('Rezept.pdf', 'D');
        }
        
    }

    function position($value, $faktor = 1000)
    {
        return round($value / $faktor);
    }

    function mm2dpi($mm)
    {
        $faktor = 25.4 / 72;
        return round($mm / $faktor);
    }

    function top($mm)
    {
        global $config;
        return $config->page->height - $mm;
    }
    
    
    public function setText($value, $key, $align="L") {
        $config = $this->config;

        $this->pdf->SetFont(($config->$key->font->family!="" ? $config->$key->font->family : $config->font->family), '', ($config->$key->font->size!="" ? $config->$key->font->size : $config->font->size));
        
        if($align=="L"){
            $this->pdf->Text($this->position($config->position->left + $config->$key->left), $this->position($config->position->top + $config->$key->top), utf8_decode($value));
        }else if( $align=="J") {
            $this->pdf->SetXY($this->position($config->position->left + $config->$key->left), $this->position($config->position->top + $config->$key->top));
            $this->pdf->MultiCell(78,3,utf8_decode($value),0,'J');
        }else if($align=="N"){
            $this->pdf->SetXY($this->position($config->position->left + $config->$key->left), $this->position($config->position->top + $config->$key->top));
            $this->pdf->MultiCell(5,3, utf8_decode($value), 0, 'N');
            $this->pdf->SetXY(0,0);
        }else if($align=="C"){
            $this->pdf->SetXY($this->position($config->position->left + $config->$key->left), $this->position($config->position->top + $config->$key->top));
            $this->pdf->MultiCell(50,3, utf8_decode($value), 0, 'C');
            $this->pdf->SetXY(0,0);
        }else{
            $this->pdf->SetXY($this->position($config->position->left + $config->$key->left), $this->position($config->position->top + $config->$key->top));
            $this->pdf->MultiCell(0,3, utf8_decode($value), 0, $align);
            $this->pdf->SetXY(0,0);
        }
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
        $this->pdf->Output($path, 'F');
    }
    
    public function toBrowser($title, $style = 'I')
    {
        $this->pdf->Output($title . '.pdf', $style);
    }
    
    
    
    
    
}
?>