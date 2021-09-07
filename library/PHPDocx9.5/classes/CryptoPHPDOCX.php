<?php

/**
 * This class offers some utilities to protect and encrypt existing Word (.docx)
 * documents. It comes bundled exclusively with the Advanced version of PHPDocX 
 * 
 * @category   Phpdocx
 * @package    crypto
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
class CryptoPHPDOCX
{
    /**
     *
     * @access private
     * @var string
     */
    private $_hash = '';

    /**
     *
     * @access private
     * @var string
     */
    private $_hashSalt = '';

    /**
     *
     * @access private
     * @var string
     */
    private $_password = '';

    /**
     *
     * @var ZipArchive
     * @access private
     */
    private $_protectDocx;

    /**
     *
     * @var DOMDocument
     * @access private
     */
    private $_protectDocxSettingsDOM;

    /**
     *
     * @var string
     * @access private
     */
    private $_protectDocxSettingsXML;

    /**
     *
     * @var DOMXPath
     * @access private
     */
    private $_protectDocxSettingsXPath;

    /**
     *
     * @access private
     * @var string
     */
    private $_salt = '';

    /**
     *
     * @access private
     * @var string
     */
    private $_verifier = '';

    /**
     * Class constructor
     */
    public function __construct()
    {
        
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        
    }

    /**
     * Getter hash
     *
     * @access public
     */
    public function getHash()
    {
        return $this->_hash;
    }

    /**
     * Getter password
     *
     * @access public
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Getter salt
     *
     * @access public
     */
    public function getSalt()
    {
        return $this->_salt;
    }

    /**
     * Getter verifier
     *
     * @access public
     */
    public function getVerifier()
    {
        return $this->_verifier;
    }

    /**
     * Setter hash
     *
     * @access public
     */
    public function setHash($hash)
    {
        $this->_hash = $hash;
    }

    /**
     * Setter password
     *
     * @access public
     */
    public function setPassword($password)
    {
        // truncate to 15 chars if password length is bigger
        $this->_password = substr($password, 0, 15);
    }

    /**
     * Setter salt
     *
     * @access public
     */
    public function setSalt($salt)
    {
        $this->_salt = $salt;
    }

    /**
     * Setter verifier
     *
     * @access public
     */
    public function setVerifier($verifier)
    {
        $this->_verifier = $verifier;
    }

    /**
     * Encrypt a DOCX using a password
     * 
     * @access public
     * @param string $source path to the source document
     * @param string $target path to the protected document
     * @param array $options
     *        password: string
     */
    public function encryptDOCX($source, $target, $options)
    {
        if (!file_exists($source)) {
            throw new Exception('File does not exist');
        }

        if (isset($options['password'])) {
            $this->generateDocumentProtectionEncrypt($options['password']);
        } else {
            throw new Exception('You did not introduced any password');
        }
        
        // check that the file size is smaller than 6.5 MB
        $maxSize = 1024 * 1024 * 6.5;
        if (filesize($source) > $maxSize) {
            throw new Exception('File size bigger than maximum of 6.5 MB');
        } else {
            $numMSATSectors = 0;
        }

        $salt = pack('H*', $this->_salt);
        $sizeIV = openssl_cipher_iv_length('AES-256-CBC');
        $verifier = openssl_random_pseudo_bytes($sizeIV);

        $verifier_padded = $this->addPadding($verifier);
        $encrypted_verifier = openssl_encrypt($verifier, 'AES-128-ECB', $this->_hash, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);
        $sha_verifier = sha1($verifier, true);
        $sha_verifier_padded = $this->addPadding($sha_verifier);
        $encrypted_sha_verifier = openssl_encrypt($sha_verifier_padded, 'AES-128-ECB', $this->_hash, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);

        $crypto = $salt . $encrypted_verifier . pack('V*', 20) . $encrypted_sha_verifier;
        $originalLength = filesize($source);

        $source = fopen($source, "rb");
        $stream = '';
        while (!feof($source)) {
            $block = fread($source, 16);
            $block_padded = $this->addPadding($block);
            $stream .= openssl_encrypt($block_padded, 'AES-128-ECB', $this->_hash, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);
        }
        fclose($source);

        // prepend the length of the original archive a byte string 8 byte long
        $stream = pack('V*', $originalLength) . pack('V*', 0) . $stream;

        $encryptedLength = strlen($stream);

        // complete the stream so it is a multiple of 512 to fill whole sectors of the BCF
        $numStreamSectors = ceil($encryptedLength / 512);
        $trailingBytes = $encryptedLength % 512;
        $stream = $stream . $this->padStream(0, 512 - $trailingBytes);

        // compute the number of needed SAT sectors
        // estimate the number of sectors dedicated to the stream
        // seven extra sectors are used for the directory, SSAT and short streams.
        // If we allow bigger files we have to make sure we have enough extra space to accomodate the sectIDs of the SAT itself: NOT DONE
        $numSATSectorsStream = floor(($numStreamSectors + 7) / 128) + 1;

        // compute the first stream sector
        $initStream = 0;
        $initStream += 1; //the 0 sector is the first sector of the SAT
        $initStream += 6; //to take into account the Directory, SSAT and short streams
        $initStream += $numSATSectorsStream; //sectors used by the SAT
        // start to build the BCF
        $header = file_get_contents(dirname(__FILE__) . '/../templates/header');
        $header_ini = substr($header, 0, 44) . pack('V*', ($numSATSectorsStream + 1)) . substr($header, 48, 32);

        // paste the sectID of all the SAT sectors starting at 07 because our choive were to locate the directory, short streams and SSAT
        $header_ini .= $this->padSequential(7, $numSATSectorsStream);

        $usedBytes = strlen($header_ini);
        $header_ini .= $this->padStream(-1, 512 - $usedBytes);

        // add the SAT
        $SAT = pack('V*', -3) . pack('V*', 4) . pack('V*', -2) . pack('V*', 6) . pack('V*', 5) . pack('V*', -2) . pack('V*', -2);
        // pad now as many sectID identifiers of SATS
        $SAT .= $this->padStream(-3, 4 * $numSATSectorsStream);
        $SAT .= $this->padSequential($initStream + 1, $numStreamSectors);
        $SAT .= pack('V*', -2);
        // total length of the SAT = 512*$numSATSectorsStream
        $total = 512 * $numSATSectorsStream + 512;
        $usedBytes = strlen($SAT);
        $SAT .= $this->padStream(-1, $total - $usedBytes);

        $sect_0 = substr($SAT, 0, 512);
        $sect_1 = substr($SAT, 512);

        // add the directory part
        $directory = file_get_contents(dirname(__FILE__) . '/../templates/directory');
        $directory = substr($directory, 0, 244) . pack('V*', $initStream) . pack('V*', $encryptedLength) . pack('V*', 0) . substr($directory, 256);
        $directory = substr($directory, 0, 2804) . $crypto . substr($directory, 2872);

        // pack all data into a binary file to see what I get...Use the salt an encripted verifier of my example
        $BFC = $header_ini . $sect_0 . $directory . $sect_1 . $stream;

        $fh = fopen($target, 'w+');
        fwrite($fh, $BFC);
        fclose($fh);
    }

    /**
     * Encrypt a PDF using a password
     * 
     * @access public
     * @param string $source path to the source document
     * @param string $target path to the protected document
     * @param array $options, 
     *      password: string
     *      mode: int. 0, RSA 40 bit; 1, RSA 128 bit; 2, AES 128 bit; 3, AES 256 bit. Optional, default 0.
     */
    public function encryptPDF($source, $target, $options)
    {
        if (!file_exists($source)) {
            throw new Exception('File does not exist');
        }
        if (!isset($options['password'])) {
            throw new Exception('You did not introduced any password');
        }
        if (!isset($options['mode'])) {
            $options['mode'] = 0;
        }
        $typesPermissions = array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');

        require_once dirname(__FILE__) . '/TCPDF_lib.php';
        $pdf = new TCPDI();
        $pageCount = $pdf->setSourceFile($source);
        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->setPrintHeader(false);
            $pdf->addPage();
            $tplidx = $pdf->importPage($i);
            $pdf->useTemplate($tplidx, null, null, 0, 0, true);
        }
        $pdf->SetProtection($typesPermissions, $options['password'], null, $options['mode']);

        $pdf->Output($target, 'F');
    }

    /**
     * Encrypt a PPTX using a password
     * 
     * @access public
     * @param string $source path to the source document
     * @param string $target path to the protected document
     * @param array $options
     *        password: string
     */
    public function encryptPPTX($source, $target, $options)
    {
        $this->encryptDOCX($source, $target, $options);
    }

    /**
     * Encrypt a XLSX using a password
     * 
     * @access public
     * @param string $source path to the source document
     * @param string $target path to the protected document
     * @param array $options
     *        password: string
     */
    public function encryptXLSX($source, $target, $options)
    {
        $this->encryptDOCX($source, $target, $options);
    }

    /**
     * Add document protection to an existing Word document
     * 
     * @access public
     * @param string $source path to the source document
     * @param string $target path to the protected document
     * @param array $options, 
     * Values:type (readOnly, comments, trackedChanges, forms) that corresponds to the available protection types: 
     * Allow No Editing (default), Allow Editing of Comments, Allow Editing With Revision Tracking and Allow Editing of Form Fields.
     * password (string of 15 or less characters).
     * overwrite (boolean) if true (default value) overwrites the existing protection if it exists.
     * @return boolean
     */
    public function protectDOCX($source, $target, $options)
    {
        if (!file_exists($source)) {
            throw new Exception('File does not exist');
        }
        if (!isset($options['type'])) {
            $options['type'] = 'readOnly';
        }
        if (!isset($options['overwrite'])) {
            $options['overwrite'] = true;
        }
        $types = array('readOnly', 'comments', 'trackedChanges', 'forms');
        if (!in_array($options['type'], $types)) {
            throw new Exception('Incorrect protection type');
        }
        if (isset($options['password'])) {
            $this->generateDocumentProtection($options['password']);
        } else {
            throw new Exception('You did not introduced any password');
        }

        //child nodes of w://settings
        $settingChilds = array('w:writeProtection',
            'w:view',
            'w:zoom',
            'w:removePersonalInformation',
            'w:removeDateAndTime',
            'w:doNotDisplayPageBoundaries',
            'w:displayBackgroundShape',
            'w:printPostScriptOverText',
            'w:printFractionalCharacterWidth',
            'w:printFormsData',
            'w:embedTrueTypeFonts',
            'w:embedSystemFonts',
            'w:saveSubsetFonts',
            'w:saveFormsData',
            'w:mirrorMargins',
            'w:alignBordersAndEdges',
            'w:bordersDoNotSurroundHeader',
            'w:bordersDoNotSurroundFooter',
            'w:gutterAtTop',
            'w:hideSpellingErrors',
            'w:hideGrammaticalErrors',
            'w:activeWritingStyle',
            'w:proofState',
            'w:formsDesign',
            'w:attachedTemplate',
            'w:linkStyles',
            'w:stylePaneFormatFilter',
            'w:stylePaneSortMethod',
            'w:documentType',
            'w:mailMerge',
            'w:revisionView',
            'w:trackRevisions',
            'w:doNotTrackMoves',
            'w:doNotTrackFormatting',
            'w:documentProtection',
            'w:autoFormatOverride',
            'w:styleLockTheme',
            'w:styleLockQFSet',
            'w:defaultTabStop',
            'w:autoHyphenation',
            'w:consecutiveHyphenLimit',
            'w:hyphenationZone',
            'w:doNotHyphenateCaps',
            'w:showEnvelope',
            'w:summaryLength',
            'w:clickAndTypeStyle',
            'w:defaultTableStyle',
            'w:evenAndOddHeaders',
            'w:bookFoldRevPrinting',
            'w:bookFoldPrinting',
            'w:bookFoldPrintingSheets',
            'w:drawingGridHorizontalSpacing',
            'w:drawingGridVerticalSpacing',
            'w:displayHorizontalDrawingGridEvery',
            'w:displayVerticalDrawingGridEvery',
            'w:doNotUseMarginsForDrawingGridOrigin',
            'w:drawingGridHorizontalOrigin',
            'w:drawingGridVerticalOrigin',
            'w:doNotShadeFormData',
            'w:noPunctuationKerning',
            'w:characterSpacingControl',
            'w:printTwoOnOne',
            'w:strictFirstAndLastChars',
            'w:noLineBreaksAfter',
            'w:noLineBreaksBefore',
            'w:savePreviewPicture',
            'w:doNotValidateAgainstSchema',
            'w:saveInvalidXml',
            'w:ignoreMixedContent',
            'w:alwaysShowPlaceholderText',
            'w:doNotDemarcateInvalidXml',
            'w:saveXmlDataOnly',
            'w:useXSLTWhenSaving',
            'w:saveThroughXslt',
            'w:showXMLTags',
            'w:alwaysMergeEmptyNamespace',
            'w:updateFields',
            'w:hdrShapeDefaults',
            'w:footnotePr',
            'w:endnotePr',
            'w:compat',
            'w:docVars',
            'w:rsids',
            'm:mathPr',
            'w:uiCompat97To2003',
            'w:attachedSchema',
            'w:themeFontLang',
            'w:clrSchemeMapping',
            'w:doNotIncludeSubdocsInStats',
            'w:doNotAutoCompressPictures',
            'w:forceUpgrade',
            'w:captions',
            'w:readModeInkLockDown',
            'w:smartTagType',
            'sl:schemaLibrary',
            'w:shapeDefaults',
            'w:doNotEmbedSmartTags',
            'w:decimalSymbol',
            'w:listSeparator'
        );
        //we make a copy of the source document into its final destination so we do not overwrite it
        copy($source, $target);
        //we extract the relevant files for the protectio process
        $this->_protectDocx = new ZipArchive();
        $this->_protectDocx->open($target);

        $this->_protectDocxSettingsXML = $this->_protectDocx->getFromName('word/settings.xml');
        $this->_protectDocxSettingsDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $this->_protectDocxSettingsDOM->loadXML($this->_protectDocxSettingsXML);
        libxml_disable_entity_loader($optionEntityLoader);

        //Let us check if the document is already protected
        $this->_protectDocxSettingsXPath = new DOMXPath($this->_protectDocxSettingsDOM);
        $this->_protectDocxSettingsXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $query = '//w:documentProtection';
        $affectedNodes = $this->_protectDocxSettingsXPath->query($query);
        $numNodes = $affectedNodes->length;
        if (!isset($numNodes) || $numNodes < 1) {
            $sourceProtection = '<w:documentProtection xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" w:edit="' . $options['type'] . '" w:enforcement="1" w:cryptProviderType="rsaFull" w:cryptAlgorithmClass="hash" w:cryptAlgorithmType="typeAny" w:cryptAlgorithmSid="4" w:cryptSpinCount="100000" w:hash="' . $this->_hash . '" w:salt="' . base64_encode($this->_salt) . '"/>';
            $protectionFragment = $this->_protectDocxSettingsDOM->createDocumentFragment();
            $protectionFragment->appendXML($sourceProtection);

            $childNodes = $this->_protectDocxSettingsDOM->documentElement->childNodes;
            $index = false;
            foreach ($childNodes as $node) {
                $name = $node->nodeName;
                $index = array_search($node->nodeName, $settingChilds);
                if ($index > 34) {
                    $node->parentNode->insertBefore($protectionFragment, $node);
                    break;
                }
            }
            //in case no node was found (pretty unlikely)we should append the protectionFragment
            if (!$index) {
                $this->_protectDocxSettingsDOM->documentElement->appendChild($protectionFragment);
            }
        } else if (isset($numNodes) && $numNodes > 0 && $options['overwrite']) {
            $affectedNodes->item(0)->setAttribute('w:hash', $this->_hash);
            $affectedNodes->item(0)->setAttribute('w:salt', base64_encode($this->_salt));
        } else {
            throw new Exception('The document is already protected');
        }

        $this->_protectDocx->addFromString('word/settings.xml', $this->_protectDocxSettingsDOM->saveXML());

        //We close now the zip file
        return $this->_protectDocx->close();
    }

    /**
     * Add document protection to an existing PDF document
     * 
     * @access public
     * @param string $source path to the source document
     * @param string $target path to the protected document
     * @param array $options, 
     *      permissionsBlocked: array of permissions to block. These are the permissions available: 'print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'
     *      passwordOwner: string to get full access. Optional.
     */
    public function protectPDF($source, $target, $options)
    {
        if (!file_exists($source)) {
            throw new Exception('File does not exist');
        }
        $typesPermissions = array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high');
        $block = array();
        if (!isset($options['permissionsBlocked'])) {
            throw new Exception('You must set at least one permission');
        }
        foreach ($options['permissionsBlocked'] as $value) {
            if (in_array($value, $typesPermissions)) {
                $permissions[] = $value;
            }
        }

        if (!isset($options['passwordOwner'])) {
            $options['passwordOwner'] = null;
        }

        require_once dirname(__FILE__) . '/TCPDF_lib.php';
        $pdf = new TCPDI();
        $pageCount = $pdf->setSourceFile($source);
        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);
            $pdf->addPage();
            $tplidx = $pdf->importPage($i);
            $pdf->useTemplate($tplidx, null, null, 0, 0, true);
        }
        $pdf->SetProtection($permissions, null, $options['passwordOwner']);

        $pdf->Output($target, 'F');
    }

    /**
     * This method removes the document protection from an existing Word document
     * 
     * @access public
     * @param string $sourcet path to the source document
     * @param string $target path to the protected document
     * @return boolean
     */
    public function removeProtection($source, $target)
    {
        // copy of the source document into its final destination so we do not overwrite it
        copy($source, $target);
        // extract the relevant files for the protection process
        $this->_protectDocx = new ZipArchive();
        $this->_protectDocx->open($target);

        $this->_protectDocxSettingsXML = $this->_protectDocx->getFromName('word/settings.xml');
        $this->_protectDocxSettingsDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $this->_protectDocxSettingsDOM->loadXML($this->_protectDocxSettingsXML);
        libxml_disable_entity_loader($optionEntityLoader);

        // check if the document is already protected
        $this->_protectDocxSettingsXPath = new DOMXPath($this->_protectDocxSettingsDOM);
        $this->_protectDocxSettingsXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $query = '//w:documentProtection';
        $affectedNodes = $this->_protectDocxSettingsXPath->query($query);
        $numNodes = $affectedNodes->length;
        if (isset($numNodes) && $numNodes >= 1) {
            foreach ($affectedNodes as $node) {
                $node->parentNode->removeChild($node);
            }
        }
        $this->_protectDocx->addFromString('word/settings.xml', $this->_protectDocxSettingsDOM->saveXML());

        // close zip file
        return $this->_protectDocx->close();
    }

    /**
     * Add padding
     * 
     * @access private
     * @param string $content
     * @return string
     */
    private function addPadding($content)
    {
        if (strlen($content) % 16) {
            $content = str_pad($content,
                strlen($content) + 16 - strlen($content) % 16, "\0");
        }

        return $content;
    }

    /**
     * Convert chart to UTF-16BE
     * 
     * @access private
     * @param string $le LittleEndian
     */
    private function convertStringToUTF16($le = false)
    {
        if ($le) {
            $this->_password = mb_convert_encoding($this->_password, 'UTF-16LE');
        } else {
            $this->_password = mb_convert_encoding($this->_password, 'UTF-16');
        }
    }

    /**
     * Generate hash and salt for document protection
     *
     * @access private
     */
    private function generateDocumentProtection($password)
    {
        $this->setPassword($password);
        $this->convertStringToUTF16();
        $this->generateSalt();
        $this->generateHash();
    }

    /**
     * Generate hash and salt for document protection
     *
     * @access private
     */
    private function generateDocumentProtectionEncrypt($password)
    {
        $this->setPassword($password);
        $this->convertStringToUTF16(true);
        $this->generateSalt();
        $this->generateHashEncrypt();
    }

    /**
     * Generate password hash
     *
     * @access private
     * @param string $password
     */
    private function generateHash()
    {
        $lowHash = 0;
        $highHash = 0;
        $tempPos = 0;
        $pos = 0;
        $len = 0;
        $bit = 0;
        $highPosition = 14;

        $highHashlength = array(
            0xE1F0, 0x1D0F, 0xCC9C, 0x84C0, 0x110C, 0x0E10, 0xF1CE, 0x313E,
            0x1872, 0xE139, 0xD40F, 0x84F9, 0x280C, 0xA96A, 0x4EC39,
        );

        $lookupBits = array(
            array(0xAEFC, 0x4DD9, 0x9BB2, 0x2745, 0x4E8A, 0x9D14, 0x2A09),
            array(0x7B61, 0xF6C2, 0xFDA5, 0xEB6B, 0xC6F7, 0x9DCF, 0x2BBF),
            array(0x4563, 0x8AC6, 0x05AD, 0x0B5A, 0x16B4, 0x2D68, 0x5AD0),
            array(0x0375, 0x06EA, 0x0DD4, 0x1BA8, 0x3750, 0x6EA0, 0xDD40),
            array(0xD849, 0xA0B3, 0x5147, 0xA28E, 0x553D, 0xAA7A, 0x44D5),
            array(0x6F45, 0xDE8A, 0xAD35, 0x4A4B, 0x9496, 0x390D, 0x721A),
            array(0xEB23, 0xC667, 0x9CEF, 0x29FF, 0x53FE, 0xA7FC, 0x5FD9),
            array(0x47D3, 0x8FA6, 0x0F6D, 0x1EDA, 0x3DB4, 0x7B68, 0xF6D0),
            array(0xB861, 0x60E3, 0xC1C6, 0x93AD, 0x377B, 0x6EF6, 0xDDEC),
            array(0x45A0, 0x8B40, 0x06A1, 0x0D42, 0x1A84, 0x3508, 0x6A10),
            array(0xAA51, 0x4483, 0x8906, 0x022D, 0x045A, 0x08B4, 0x1168),
            array(0x76B4, 0xED68, 0xCAF1, 0x85C3, 0x1BA7, 0x374E, 0x6E9C),
            array(0x3730, 0x6E60, 0xDCC0, 0xA9A1, 0x4363, 0x86C6, 0x1DAD),
            array(0x3331, 0x6662, 0xCCC4, 0x89A9, 0x0373, 0x06E6, 0x0DCC),
            array(0x1021, 0x2042, 0x4084, 0x8108, 0x1231, 0x2462, 0x48C4),
        );

        // use multibytes functions to work with UTF-16 bytes
        $len = mb_strlen($this->_password, 'UTF-16');

        // empty password
        if ($len == 0) {
            return 0;
        }

        $pos = $len;
        $highHash = $highHashlength[$len - 1];

        $result = array();
        for ($idx = 0; $idx < $len; $idx++) {
            $result[] = hexdec(bin2hex(mb_substr($this->_password, $idx, 1, 'UTF-16')));
        }

        while ($pos-- > 0) {
            $tempPos = (($result[$pos] & 0xFF) == 0) ?
                    (($result[$pos] >> 8) & 0xFF) :
                    ($result[$pos] & 0xFF);

            $lowHash = (($lowHash >> 14) & 0x01) | (($lowHash << 1) & 0x7FFF);

            $lowHash ^= $tempPos;

            for ($bit = 0; $bit < 7; $bit++) {
                if (($tempPos & 1) != 0) {
                    $highHash ^= $lookupBits[$highPosition][$bit];
                }
                $tempPos >>= 1;
            }
            $highPosition--;
        }

        $lowHash = (($lowHash >> 14) & 0x01) | (($lowHash << 1) & 0x7FFF);
        $lowHash ^= $len;
        $lowHash ^= 0xCE4B;

        // get password hash
        $passwordHash = dechex($highHash << 16 | $lowHash);
        $len = mb_strlen($passwordHash, 'UTF-16');
        // reverse password and upper content
        $generatedKey = array();
        for ($idx = 0; $idx < $len; $idx++) {
            $generatedKey[] = mb_substr($passwordHash, $idx, 1, 'UTF-16');
        }
        $passwordHash = strtoupper(implode(array_reverse(($generatedKey))));

        // 
        $this->_salt = pack('H*', $this->_salt);
        $passwordHashConcat = '';
        for ($i = 0; $i < strlen($passwordHash); $i++) {
            $passwordHashConcat .= mb_convert_encoding($passwordHash[$i], 'UTF-16LE');
        }
        $concatenatedSaltPass = $this->_salt . $passwordHashConcat;

        $hashIni = sha1($concatenatedSaltPass, true);

        $it = '00000000';
        $iterator = pack('H*', $it);

        $string = $iterator . $hashIni;
        $hash_1 = sha1($string, true);

        for ($j = 0; $j <= 99999; $j++) {
            $iteratorBin = pack('I*', $j);
            $string = $hashIni . $iteratorBin;
            $hashIni = sha1($string, true);
        }

        $this->_hash = base64_encode($hashIni);
    }

    /**
     * Generate password hash
     *
     * @access private
     * @param string $password
     */
    private function generateHashEncrypt()
    {
        // transform password to dec values
        $arrPassword = str_split($this->_password);
        $passwordValues = array();
        foreach ($arrPassword as $value) {
            if (ord($value) != 0) {
                $passwordValues[] = ord($value);
            }
        }
        $password = '';
        foreach ($passwordValues as $value) {
            $value2Bits = '';
            if (strlen(dechex($value)) > 1) {
                $value2Bits = dechex($value);
            } else {
                $value2Bits = 0 . dechex($value);
            }
            $password .= $value2Bits . '00';
        }

        // concat salt
        $total = pack('H*', ($this->_salt . $password));
        $hashIni = sha1($total, true);
        // first block
        $it = '00000000';
        $iterator = pack('H*', $it);

        $string = $iterator . $hashIni;
        $hash_1 = sha1($string, true);

        // iterate 50000 times
        for ($j = 0; $j <= 49999; $j++) {
            $iteratorBin = pack('I*', $j);
            $string = $iteratorBin . $hashIni;
            $hashIni = sha1($string, true);
        }

        // last block
        $it = '00000000';
        $iterator = pack('H*', $it);

        $string = $hashIni . $iterator;
        $hashIni = sha1($string, true);

        $string = '';
        for ($k = 0; $k < 64; $k++) {
            if ($k < strlen($hashIni)) {
                $temp = 0x36 ^ hexdec(bin2hex($hashIni[$k]));
            } else {
                $temp = 0x36;
            }
            $value2Bits = '';
            if (strlen(dechex($temp)) > 1) {
                $temp = dechex($temp);
            } else {
                $temp = 0 . dechex($temp);
            }
            $temp = pack('H*', $temp);
            $string .= $temp;
        }
        $hash2 = sha1($string, true);

        $this->_hash = substr($hash2, 0, 16);
    }

    /**
     * Generate a random salt
     * 
     * @access private
     */
    private function generateSalt()
    {
        mt_srand(microtime(true) * 100000 + memory_get_usage(true));
        $this->_salt = md5(uniqid(mt_rand(), true));
    }

    /**
     * Pad sequential
     * 
     * @access private
     * @param string $start
     * @param string $steps
     * 
     */
    private function padSequential($start, $steps)
    {
        $padding = '';
        for ($j = 0; $j < $steps; $j++) {
            $data = $start + $j;
            $padding .= pack('V*', $data);
        }

        return $padding;
    }

    /**
     * Pad stream
     * 
     * @access private
     * @param string $seed
     * @param string $steps
     * 
     */
    private function padStream($seed, $steps)
    {
        $padding = '';
        for ($j = 1; $j <= $steps / 4; $j++) {
            $padding .= pack('V*', $seed);
        }

        return $padding;
    }

}
