<?php

require_once dirname(__FILE__) . '/Sign.php';
require_once dirname(__FILE__) . '/TCPDF_lib.php';

/**
 * Sign a PDF file
 *
 * @category   Phpdocx
 * @package    sign
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
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
     * @param string $target PDF file output
     * @param array $optionsSignature Optional, signature options:
     *     'x' (float) abscissa of the upper-left corner. 180 as default
     *     'y' (float) ordinate of the upper-left corner. 60 as default
     *     'w' (float) width of the signature area. 15 as default
     *     'h' (float) height of the signature area. 15 as default
     *     'page' (int) page number (if < 0 the last page is used). -1 as default
     *     'name' (string) name of the signature. Empty as default
     *     'accessPermission' (int) access permissions granted for this document. 1 = No changes to the document shall be permitted; any change to the document shall invalidate the signature; 2 = Permitted changes shall be filling in forms, instantiating page templates, and signing;; 3 = Permitted changes shall be the same as for 2, as well as annotation creation, deletion, and modification. 2 as default
     * @param string $optionsImage Optional, image to add in PDF as sign:
     *     'src' (string) image file path
     *     'x' (float) abscissa of the upper-left corner (LTR) or upper-right corner (RTL). 180 as default
     *     'y' (float) ordinate of the upper-left corner (LTR) or upper-right corner (RTL). 60 as default
     *     'w' (float) width of the image in the page. If not set, it's automatically calculated. 15 as default
     *     'h' (float) height of the image in the page. If not set, it's automatically calculated. 15 as default
     *     'link' (string) URL. Empty as default
     */
    public function sign($target, $optionsSignature = null, $optionsImage = null)
    {
        $pdf = new TCPDI();
        $certificate = 'file://' . @realpath(dirname(FILE)) . '/' . $this->_x509Certificate;
        $private = 'file://' . @realpath(dirname(FILE)) . '/' . $this->_privatekey;
        $pageCount = $pdf->setSourceFile($this->_pdf);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplidx = $pdf->importPage($i);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->addPage();
            $pdf->useTemplate($tplidx, null, null, 0, 0, true);
        }
        
        // add an image
        if ($optionsImage && is_array($optionsImage)) {
            // default values
            if (!isset($optionsImage['x'])) {
                $optionsImage['x'] = 180;
            }
            if (!isset($optionsImage['y'])) {
                $optionsImage['y'] = 60;
            }
            if (!isset($optionsImage['w'])) {
                $optionsImage['w'] = 15;
            }
            if (!isset($optionsImage['h'])) {
                $optionsImage['h'] = 15;
            }
            if (!isset($optionsImage['link'])) {
                $optionsImage['link'] = '';
            }

            $pdf->Image($optionsImage['src'], $optionsImage['x'], $optionsImage['y'], $optionsImage['w'], $optionsImage['h'], '', $optionsImage['link']);
        }

        // signature appearance
        if ($optionsSignature && is_array($optionsSignature)) {
            // default values
            if (!isset($optionsSignature['x'])) {
                $optionsSignature['x'] = 180;
            }
            if (!isset($optionsSignature['y'])) {
                $optionsSignature['y'] = 60;
            }
            if (!isset($optionsSignature['w'])) {
                $optionsSignature['w'] = 15;
            }
            if (!isset($optionsSignature['h'])) {
                $optionsSignature['h'] = 15;
            }
            if (!isset($optionsSignature['page'])) {
                $optionsSignature['page'] = -1;
            }
            if (!isset($optionsSignature['name'])) {
                $optionsSignature['name'] = '';
            }

            $pdf->setSignatureAppearance($optionsSignature['x'], $optionsSignature['y'], $optionsSignature['w'], $optionsSignature['h'], $optionsSignature['page'], $optionsSignature['name']);
        } else {
            $pdf->setSignatureAppearance(180, 60, 15, 15);
        }

        if (!isset($optionsSignature['accessPermission'])) {
            $optionsSignature['accessPermission'] = 2;
        }
        
        $pdf->setSignature($certificate, $private, $this->_password, '', $optionsSignature['accessPermission']);
        
        $pdf->Output($target, 'F');
    }

}
