<?php

/**
 * Class Pms_PDFUtil
 *
 * little Helper-Class for generate display and stor pdf-files
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Pms_PDFUtil
{

    /**
     * Generate a pdf from given html an send it to the browser
     *
     * @param $html the input as html
     * @param $pdfname the pdf_name
     * @param $options array of options
     *
     * possible options are:
     * $options[orientation] = P|L default P
     * $options[margins]  default 6,5,10
     * $options[customheader]  default ""
     * $options[footer_type]  = "1 of n","1 of n date","1 of n date 12px","1" default ""
     * $options[footer_text]  default ""
     *
     * @throws Zend_Session_Exception
     */
    public static function generate_pdf_to_browser($html, $pdfname, $options = null)
    {

        $pdf = self::generate_pdf($html, $pdfname, $options);
        ob_end_clean();
        ob_start();
        $pdf->toBrowser($pdfname . '.pdf', "D");
        exit;

    }

    /**
     * Generate a pdf from given html an store it in file-system for further
     * downloads by patient.
     *
     * @param $html the input as html
     * @param $pdfname the pdf_name
     * @param $title the title of oatient file
     * @param $ipid ipid of patient
     * @param $options array of options
     *
     * possible options are:
     * $options[orientation] = P|L default P
     * $options[margins]  default 6,5,10
     * $options[customheader]  default ""
     * $options[footer_type]  = "1 of n","1 of n date","1 of n date 12px","1" default ""
     * $options[footer_text]  default ""
     * $options[recordid]  default ""
     * options[tabname]  default ""
     *
     * @return id of the new PatientFile
     *
     * @throws Zend_Session_Exception
     */
    public static function generate_pdf_to_patient_file($html, $pdfname, $title, $ipid, $options = null)
    {

        $pdf = self::generate_pdf($html, $pdfname, $options);

        $tmpstmp = $pdf->uniqfolder(PDF_PATH);

        $pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
        $filename =  $tmpstmp . '/' . $pdfname . '.pdf';

        //$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
        //$ftp_put_queue_result = Pms_CommonData::ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , "uploads" );
        Pms_CommonData::ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , "uploads" );

        $cust = new PatientFileUpload ();
        $cust->title = Pms_CommonData::aesEncrypt(addslashes($title));
        $cust->ipid = $ipid;
        $cust->file_name = Pms_CommonData::aesEncrypt($filename); //$post['fileinfo']['filename']['name'];
        $cust->file_type = Pms_CommonData::aesEncrypt('PDF');

        if (isset($options['recordid'])) {
            $cust->recordid = $options['recordid'];
        }
        if (isset($options['tabname'])) {
            $cust->tabname = $options['tabname'];
        }

        $cust->save();
        return $cust->id;

    }

    private static function generate_pdf($html, $pdfname,  $options=null)
    {
        $pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setDefaults(false); //defaults without header
        $pdf->setImageScale(1.6);
        $pdf->SetMargins(6, 5, 10); //reset margins
        $pdf->setPrintFooter(false); // remove black line at bottom
        $pdf->SetAutoPageBreak(TRUE, 10);

        if(isset($options['orientation'])){
            $pdf->setPageOrientation($options['orientation']);
        }

       if(isset($options['customheader'])) {
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetHeaderMargin(5);
            $pdf->setPrintHeader(true);
            $pdf->setHeaderFont(Array('helvetica','B',8));
            $pdf->customheadertext='<table border="0" width="100%"><tr><td>'.$options['customheader'] .'</td><td align="right">'.date('d.m.Y').'</td></tr></table>';
            $pdf->customheader = function ($obj) {
                $headtext= $obj->customheadertext;
                $obj->writeHTMLCell(0, 0, $x='', $y='',  $headtext, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=false);
            };
        }

        if (isset($options['margins'])) {
            $pdf->SetMargins($options['margins'][0], $options['margins'][1], $options['margins'][2]);
        }

        if (isset($options['footer_type']) && isset($options['footer_text'])) {
            $pdf->setPrintFooter(true);
            $pdf->setFooterType($options['footer_type']);
            $pdf->footer_border = "0";
            $pdf->footer_text = $options['footer_text'];
        }
        if(isset($options['no_div_vspace'])||1){
            //normally tcpdf adds vertical space around divs. use this switch to get rid of it
            $tagvs = array('div' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 1, 'n' => 0)));

            $pdf->setHtmlVSpace($tagvs);
        }
        if(isset($options['watermark'])){
            $pdf->setPrintHeader(true);
            $bg_image_path = $options['watermark'];
            $pdf->setBackgroundImage($bg_image_path);
        }
        if(isset($options['font'])){
            $pdf->SetFont($options['font']['family'], '', $options['font']['size']);
        }
        $pdf->setHTML($html);
        return $pdf;

    }

}

?>