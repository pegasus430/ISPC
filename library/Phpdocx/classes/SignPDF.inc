<?php

require_once dirname(__FILE__) . '/Sign.inc';

/**
 * Sign a PDF file
 *
 * @category   Phpdocx
 * @package    sign
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @version    2016.05.01
 * @link       http://www.phpdocx.com
 */
class SignPDF implements Sign
{

    /**
     * @access private
     * @var string
     */
    private $_password;

    /**
     * @access private
     * @var string
     */
    private $_pdf;

    /**
     * @access private
     */
    private $_privatekey;

    /**
     * @access private
     */
    private $_x509Certificate;

    /**
     * Setter $_pdf
     */
    public function setPDF($file)
    {
        if (is_file($file)) {
            $this->_pdf = $file;
        } else {
            exit('The file does not exist');
        }
    }

    /**
     * Setter $_privatekey
     */
    public function setPrivateKey($file, $password = null)
    {
        if (is_file($file)) {
            $this->_privatekey = $file;
            if ($password) {
                $this->_password = $password;
            }
        } else {
            exit('The file does not exist');
        }
    }

    /**
     * Setter $_x509Certificate
     */
    public function setX509Certificate($file)
    {
        if (is_file($file)) {
            $this->_x509Certificate = $file;
        } else {
            exit('The file does not exist');
        }
    }

    /**
     * Sign PDF
     * 
     * @access public
     * @param string $dest PDF to
     * @param string $info Optional, info of PDF:
     *      · 'name'
     *      · 'Location'
     *      · 'Reason'
     *      · 'ContactInfo'
     * @param string $image Optional, image to add in PDF as sign
     */
    public function sign($dest, $info = null, $image = null)
    {
        $pdf = new FPDI();
        $certificate = 'file://' . @realpath(dirname(FILE)) . '/' . $this->_x509Certificate;
        $private = 'file://' . @realpath(dirname(FILE)) . '/' . $this->_privatekey;
        $pageCount = $pdf->setSourceFile($this->_pdf);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplidx = $pdf->importPage($i);
            $pdf->addPage();
            $pdf->useTemplate($tplidx);
        }
        $pdf->setSignature($certificate, $private, $this->_password, '', 2, $info);
        if (is_file($image)) {
            $pdf->Image($image, 180, 60, 15, 15, 'PNG');
        }
        $pdf->setSignatureAppearance(180, 60, 15, 15);
        $pdf->Output($dest, 'F');
    }

}
