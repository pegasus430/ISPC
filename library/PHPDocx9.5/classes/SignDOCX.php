<?php

/**
 * Sign a DOCX file
 *
 * @category   Phpdocx
 * @package    sign
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
require_once dirname(__FILE__) . '/Sign.php';

class SignDOCX implements Sign
{
    /**
     * @access private
     * @var Zip
     */
    private $_docx;

    /**
     * @access private
     * @var array
     */
    private $_idsRels = array();

    /**
     * @access private
     * @var Sign
     * @static
     */
    private static $_instance = NULL;

    /**
     * @access private
     */
    private $_privatekey;

    /**
     * @access private
     */
    private $_publickey;

    /**
     * @access private
     */
    private $_x509Certificate;

    /**
     * @access private
     * @var SimpleXML
     */
    private $_xml;

    /**
     * Getter $privatekey
     */
    public function getPrivatekey($file)
    {
        return $this->_privatekey;
    }

    /**
     * Getter $x509Certificate
     */
    public function getX509Certificate()
    {
        return $this->_x509Certificate;
    }

    /**
     * Setter $docx
     */
    public function setDocx($file)
    {
        if (is_file($file)) {
            $this->_docx = new ZipArchive();
            $this->_docx->open($file);

            // exit if document is already signed
            /*if ($this->_docx->statName('_xmlsignatures/sig1.xml')) {
                exit('The document is already signed');
            }*/
        } else {
            exit('The file does not exist');
        }
    }

    /**
     * Setter $privatekey
     * 
     * @param string $file Path to private key file
     * @param string $password Password of private key
     */
    public function setPrivatekey($file, $password = null)
    {
        $this->_privatekey = new Crypt_RSA();
        if ($password) {
            $this->_privatekey->setPassword($password);
        }
        $this->_privatekey->loadKey(file_get_contents($file));
        if (!$this->_privatekey->getPrivatekey()) {
            exit('Unable to find a private key');
        }
    }

    /**
     * Setter $x509Certificate
     * 
     * @param string $file Path to certificate file
     */
    public function setX509Certificate($file)
    {
        if (is_file($file)) {
            $key = openssl_x509_read(file_get_contents($file));
            openssl_x509_export($key, $this->_x509Certificate);
            if (!$this->_x509Certificate) {
                CreateDocX::$log->fatal('Unable to find a certificate');
                exit();
            }
            // remove unwanted strings and blank spaces
            $this->_x509Certificate = str_replace('-----BEGIN CERTIFICATE-----', '', $this->_x509Certificate);
            $this->_x509Certificate = str_replace('-----END CERTIFICATE-----', '', $this->_x509Certificate);
            $this->_x509Certificate = trim($this->_x509Certificate);
        } else {
            exit('The file does not exist');
        }
    }

    /**
     * Construct
     *
     * @access public
     */
    public function __construct()
    {
        $this->_xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
                <Signature Id="idPackageSignature" xmlns="http://www.w3.org/2000/09/xmldsig#">
                    <SignedInfo>
                        <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
                        <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
                        <Reference Type="http://www.w3.org/2000/09/xmldsig#Object" URI="#idPackageObject">
                            <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                            <DigestValue></DigestValue>
                        </Reference>
                        <Reference Type="http://www.w3.org/2000/09/xmldsig#Object" URI="#idOfficeObject">
                            <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                            <DigestValue></DigestValue>
                        </Reference>
                    </SignedInfo>
                    <SignatureValue></SignatureValue>
                    <KeyInfo>
                        <X509Data>
                            <X509Certificate></X509Certificate>
                        </X509Data>
                    </KeyInfo>
                    <Object Id="idPackageObject" xmlns:mdssi="http://schemas.openxmlformats.org/package/2006/digital-signature">
                        <Manifest>
                        </Manifest>
                        <SignatureProperties>
                            <SignatureProperty Id="idSignatureTime" Target="#idPackageSignature">
                                <mdssi:SignatureTime>
                                    <mdssi:Format>YYYY-MM-DDThh:mm:ssTZD</mdssi:Format>
                                    <mdssi:Value>2012-10-05T09:24:10Z</mdssi:Value>
                                </mdssi:SignatureTime>
                            </SignatureProperty>
                        </SignatureProperties>
                    </Object>
                    <Object Id="idOfficeObject"><SignatureProperties><SignatureProperty Id="idOfficeV1Details" Target="#idPackageSignature"><SignatureInfoV1 xmlns="http://schemas.microsoft.com/office/2006/digsig"><SetupID/><SignatureText/><SignatureImage/><SignatureComments>Test phpdocx</SignatureComments><WindowsVersion>5.1</WindowsVersion><OfficeVersion>12.0</OfficeVersion><ApplicationVersion>12.0</ApplicationVersion><Monitors>1</Monitors><HorizontalResolution>1680</HorizontalResolution><VerticalResolution>1050</VerticalResolution><ColorDepth>32</ColorDepth><SignatureProviderId>{00000000-0000-0000-0000-000000000000}</SignatureProviderId><SignatureProviderUrl/><SignatureProviderDetails>9</SignatureProviderDetails><ManifestHashAlgorithm>http://www.w3.org/2000/09/xmldsig#sha1</ManifestHashAlgorithm><SignatureType>1</SignatureType></SignatureInfoV1></SignatureProperty></SignatureProperties></Object>
                </Signature>');
    }

    /**
     *
     * @access public
     * @return string
     */
    public function __toString()
    {
        return $this->_xml;
    }

    /**
     *
     * @access public
     */
    public function sign()
    {
        $this->_xml->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        // set SignatureTime
        $this->setSignatureTime();

        // get rels ids
        $this->returnIdsRels();

        // add _xmlsignatures/origin.sigs Relationship to _rels/.rels
        $relsRelsContent = $this->_docx->getFromName('_rels/.rels');
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $seRelsRelsContent = simplexml_load_string($relsRelsContent);
        libxml_disable_entity_loader($optionEntityLoader);
        $seRelsRelsContent->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $elementsRelationRelsRelsContent = $seRelsRelsContent->xpath('//r:Relationship');
        // get the highest rId to add a new Relationship for '_xmlsignatures/origin.sigs'
        $idTemp = 0;
        foreach ($elementsRelationRelsRelsContent as $elementRelationRelsRelsContent) {
            $domElementRelationRelsRelsContent = dom_import_simplexml($elementRelationRelsRelsContent);
            $idIntValue = $domElementRelationRelsRelsContent->getAttribute('Id');
            if ($idTemp < (int) substr($idIntValue, 3)) {
                $idTemp = (int) substr($idIntValue, 3);
            }
        }
        $idTemp = 'rId' . ($idTemp + 1);

        if (!$this->_docx->locateName('_xmlsignatures/origin.sigs')) {
            $newRelationship = $seRelsRelsContent->addChild('Relationship');
            $newRelationship->addAttribute('Id', $idTemp);
            $newRelationship->addAttribute('Type', 'http://schemas.openxmlformats.org/package/2006/relationships/digital-signature/origin');
            $newRelationship->addAttribute('Target', '_xmlsignatures/origin.sigs');
        }
        // add rels and ContentTypes
        $this->_docx->addFromstring('_rels/.rels', $seRelsRelsContent->asXML());

        // add X509certificate
        $domX509Certificate = $this->_xml->xpath('//ds:KeyInfo/ds:X509Data/ds:X509Certificate');
        $domElementX509Certificate = dom_import_simplexml($domX509Certificate[0]);
        $domElementX509Certificate->nodeValue = $this->_x509Certificate;

        // [Content_Types].xml is needed to get ContentType value for Reference tags
        $contentTypesContent = $this->_docx->getFromName('[Content_Types].xml');
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $seContentTypesContent = simplexml_load_string($contentTypesContent);
        libxml_disable_entity_loader($optionEntityLoader);
        $seContentTypesContent->registerXPathNamespace('t', 'http://schemas.openxmlformats.org/package/2006/content-types');

        // add new Default to [Content_Types].xml if not exists
        $elementsContentTypeDefaultSig = $seContentTypesContent->xpath('//t:Default[@Extension="sigs"]');
        if (!$elementsContentTypeDefaultSig) {
            $newDefault = $seContentTypesContent->addChild('Default');
            $newDefault->addAttribute('Extension', 'sigs');
            $newDefault->addAttribute('ContentType', 'application/vnd.openxmlformats-package.digital-signature-origin');
        }

        // get the highest sign rId to add a new Relationship for '_xmlsignatures/origin.sigs'
        $idTempNumericSig = 0;
        $elementsContentTypeOverrideSigFile = $seContentTypesContent->xpath('//t:Override[@ContentType="application/vnd.openxmlformats-package.digital-signature-xmlsignature+xml"]');
        foreach ($elementsContentTypeOverrideSigFile as $elementContentTypeOverrideSigFile) {
            $domElementContentTypeOverrideSigFileContent = dom_import_simplexml($elementContentTypeOverrideSigFile);
            $partNameIntValue = $domElementContentTypeOverrideSigFileContent->getAttribute('PartName');
            $partNameInt = str_replace(array('/_xmlsignatures/sig', '.xml'), '', $partNameIntValue);
            if ($idTempNumericSig < (int) $partNameInt) {
                $idTempNumericSig = (int) $partNameInt;
            }
        }
        $idTempNumericSig += 1;

        // add new Override to [Content_Types].xml if not exists
        $elementsContentTypeOverrideSig = $seContentTypesContent->xpath('//t:Override[@PartName="/_xmlsignatures/sig'.$idTempNumericSig.'.xml"]');
        if (!$elementsContentTypeOverrideSig) {
            $newOverride = $seContentTypesContent->addChild('Override');
            $newOverride->addAttribute('PartName', '/_xmlsignatures/sig'.$idTempNumericSig.'.xml');
            $newOverride->addAttribute('ContentType', 'application/vnd.openxmlformats-package.digital-signature-xmlsignature+xml');
        }
        $this->_docx->addFromstring('[Content_Types].xml', $seContentTypesContent->asXML());

        // _rels/.rels is needed to add its own Referer tag
        $elementsRelationRelsRelsContent = $seRelsRelsContent->xpath('//r:Relationship');

        // word/_rels/document.xml.rels is needed to set order of References and Transforms tag
        $wordRelsDocumentContent = $this->_docx->getFromName('word/_rels/document.xml.rels');
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $seWordRelsDocumentContent = simplexml_load_string($wordRelsDocumentContent);
        libxml_disable_entity_loader($optionEntityLoader);
        $seWordRelsDocumentContent->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $elementsRelationWordRelsDocumentContent = $seWordRelsDocumentContent->xpath('//r:Relationship');

        // add Reference for _rels/.rels
        $digestValue = $this->calculateDigestValueTransform($seRelsRelsContent, $this->_idsRels['_rels/.rels']);
        $this->addReference('/_rels/.rels?ContentType=application/vnd.openxmlformats-package.relationships+xml', $digestValue, 'http://www.w3.org/2000/09/xmldsig#sha1', true, $this->_idsRels['_rels/.rels']);

        // sort order of word/*.xml to add
        $filesXMLToBeAdded = array();
        // add document.xml from _rels/.rels
        foreach ($elementsRelationRelsRelsContent as $elementRelation) {
            $domElementRelation = dom_import_simplexml($elementRelation);
            // only document.xml is needed
            if ($domElementRelation->getAttribute('Target') != 'word/document.xml') {
                continue;
            }
            $filesXMLToBeAdded[] = $domElementRelation->getAttribute('Target');
        }
        // add content from word/_rels/document.xml.rels
        foreach ($elementsRelationWordRelsDocumentContent as $elementRelation) {
            $domElementRelation = dom_import_simplexml($elementRelation);
            // avoid externals relationships
            if ($domElementRelation->getAttribute('TargetMode') == 'External') {
                continue;
            }

            $filesXMLToBeAdded[] = 'word/' . $domElementRelation->getAttribute('Target');
        }
        // sort xml alphabetic
        sort($filesXMLToBeAdded);

        // add other rels, like charts and word/_rels/document.xml.rels
        for ($i = 0; $i < $this->_docx->numFiles; $i++) {
            if (preg_match('/^word.*\.xml.rels$/', $this->_docx->getNameIndex($i))) {
                $wordRels = $this->_docx->getFromName($this->_docx->getNameIndex($i));
                $optionEntityLoader = libxml_disable_entity_loader(true);
                $seWordRels = simplexml_load_string($wordRels);
                libxml_disable_entity_loader($optionEntityLoader);
                $seWordRels->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
                $digestValue = $this->calculateDigestValueTransform($seWordRels, $this->_idsRels[$this->_docx->getNameIndex($i)]);
                $this->addReference('/' . $this->_docx->getNameIndex($i) . '?ContentType=application/vnd.openxmlformats-package.relationships+xml', $digestValue, 'http://www.w3.org/2000/09/xmldsig#sha1', true, $this->_idsRels[$this->_docx->getNameIndex($i)]);
            }
        }

        // add xml files
        foreach ($filesXMLToBeAdded as $fileXMLToBeAdded) {
            // get ContentType value
            $elementOverride = $seContentTypesContent->xpath('//t:Override[@PartName="/' . $fileXMLToBeAdded . '"]');
            if ($elementOverride) {
                $domElementOverride = dom_import_simplexml($elementOverride[0]);
                $contentTypeValue = $domElementOverride->getAttribute('ContentType');
            } else {
                // get extension value
                $extensionFileXMLToBeAdded = pathinfo($fileXMLToBeAdded, PATHINFO_EXTENSION);
                $elementDefault = $seContentTypesContent->xpath('//t:Default[@Extension="' . $extensionFileXMLToBeAdded . '"]');
                if ($elementDefault) {
                    $domElementDefault = dom_import_simplexml($elementDefault[0]);
                    $contentTypeValue = $domElementDefault->getAttribute('ContentType');
                }
            }
            // get DigetsValue value
            $digestValue = $this->calculateDigestValue($this->_docx->getFromName($fileXMLToBeAdded));
            // add reference
            $this->addReference('/' . $fileXMLToBeAdded . '?ContentType=' . $contentTypeValue, $digestValue);
        }

        // set ValuesIdPackageSignature
        $this->setValuesIdPackageSignature();

        // set SignatureValue
        $this->setSignatureValue();

        // add _xmlsignatures content
        $this->addXmlsignaturesContent($idTempNumericSig);

        // close ZIP file
        $this->_docx->close();
    }

    /**
     *
     * @access public
     */
    public function setSignatureComments($comment)
    {
        $this->_xml->registerXPathNamespace('ds', 'http://schemas.microsoft.com/office/2006/digsig');

        // add X509certificate
        $domSignatureComments = $this->_xml->xpath('//ds:SignatureComments');
        $domElementSignatureComments = dom_import_simplexml($domSignatureComments[0]);
        $domElementSignatureComments->nodeValue = $comment;
    }

    /**
     *
     * @access public
     */
    public function setSignatureTime()
    {
        $this->_xml->registerXPathNamespace('mdssi', 'http://schemas.openxmlformats.org/package/2006/digital-signature');

        // add X509certificate
        $domSignatureTimeValue = $this->_xml->xpath('//mdssi:Value');
        $domSignatureTimeValue = dom_import_simplexml($domSignatureTimeValue[0]);
        $domSignatureTimeValue->nodeValue = gmdate("Y-m-d\TH:i:s\Z");
    }

    /**
     * Set SignatureValue
     * 
     * @access public
     * @param string $source Pfx filename
     * @param string $dest Pem filename
     * @param string $password Password access
     */
    public function transformPfxToPem($source, $dest, $password = null)
    {
        $results = array();
        $worked = openssl_pkcs12_read(file_get_contents($source), $results, $password);
        if ($worked) {
            $worked = openssl_pkey_export($results['pkey'], $result, $password);
            if ($worked) {
                $pemFile = $result . $results['cert'];
                file_put_contents($dest, $pemFile);
            } else {
                exit(openssl_error_string());
            }
        } else {
            exit(openssl_error_string());
        }
    }

    /**
     *  Add reference to DOM elment in idPackageObject
     * 
     * @access private
     */
    private function addReference($uri, $value, $algorithm = 'http://www.w3.org/2000/09/xmldsig#sha1', $transform = false, $ids = array())
    {
        $reference = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Reference></Reference>');
        $reference->addAttribute('URI', $uri);
        if ($transform) {
            $elementTransforms = $reference->addChild('Transforms');
            $elementTransform = $elementTransforms->addChild('Transform');
            $elementTransform->addAttribute('Algorithm', 'http://schemas.openxmlformats.org/package/2006/RelationshipTransform');
            foreach ($ids as $id) {
                $relationshipReference = $elementTransform->addChild('mdssi:RelationshipReference', null, 'http://schemas.openxmlformats.org/package/2006/digital-signature');
                $relationshipReference->addAttribute('SourceId', $id);
            }
            $elementTransform = $elementTransforms->addChild('Transform');
            $elementTransform->addAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        }
        $digestMethod = $reference->addChild('DigestMethod');
        $digestMethod->addAttribute('Algorithm', $algorithm);
        $digestValue = $reference->addChild('DigestValue', $value);
        $domElementReference = dom_import_simplexml($reference);

        // add new reference to main DOM
        $this->_xml->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $domObjectIdPackageObjectManifest = $this->_xml->xpath('//ds:Object/ds:Manifest');
        $domElementObjectIdPackageObjectManifest = dom_import_simplexml($domObjectIdPackageObjectManifest[0]);
        $this->insertNode($domElementReference, $domElementObjectIdPackageObjectManifest, 'inside');
    }

    /**
     * Add _xmlsignatures content
     * 
     * @access private
     * @return string DigestValue
     */
    private function addXmlsignaturesContent($idTempNumericSig)
    {
        if (!$this->_docx->locateName('_xmlsignatures')) {
            $this->_docx->addEmptyDir('_xmlsignatures');
        }
        if (!$this->_docx->locateName('_xmlsignatures/_rels')) {
            $this->_docx->addEmptyDir('_xmlsignatures/_rels');
        }

        if (!$this->_docx->locateName('_xmlsignatures/_rels/origin.sigs.rels')) {
            $this->_docx->addFromstring('_xmlsignatures/_rels/origin.sigs.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId'.$idTempNumericSig.'" Type="http://schemas.openxmlformats.org/package/2006/relationships/digital-signature/signature" Target="sig'.$idTempNumericSig.'.xml"/></Relationships>');
        } else {
            $originSigsRelsContent = $this->_docx->getFromName('_xmlsignatures/_rels/origin.sigs.rels');
            $originSigsRelsContent = str_replace('</Relationships>', '<Relationship Id="rId'.$idTempNumericSig.'" Type="http://schemas.openxmlformats.org/package/2006/relationships/digital-signature/signature" Target="sig'.$idTempNumericSig.'.xml"/></Relationships>', $originSigsRelsContent);
            $this->_docx->addFromstring('_xmlsignatures/_rels/origin.sigs.rels', $originSigsRelsContent);
        }

        if (!$this->_docx->locateName('_xmlsignatures/origin.sigs')) {
            $this->_docx->addFromstring('_xmlsignatures/origin.sigs', '');
        }

        $this->_docx->addFromstring('_xmlsignatures/sig'.$idTempNumericSig.'.xml', $this->_xml->asXML());
    }

    /**
     *
     * @access private
     * @return string DigestValue
     */
    private function calculateDigestValue($string)
    {
        $value = base64_encode((string) sha1($string, true));
        return $value;
    }

    /**
     *
     * @access private
     * @return string DigestValue
     */
    private function calculateDigestValueTransform($string, $IdsArray)
    {
        //we should first sort the IdsArray
        //The standard requires lexicographical ordering for case sensitive unicode strings
        sort($IdsArray);

        //We load into the DOM the required relationship file
        $doc = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $doc->loadXML($string->asXML());
        libxml_disable_entity_loader($optionEntityLoader);

        //We create a new XML that is canonicalized following the OOXML standard
        $newRels = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $newRels->loadXML('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
        libxml_disable_entity_loader($optionEntityLoader);

        foreach ($IdsArray as $key => $value) {

            //We extract the Relationship elements that are going to be signed
            $query = '//rels:Relationship[@Id="' . $value . '"]';
            $docxPath = new DOMXPath($doc);
            $docxPath->registerNamespace('rels', 'http://schemas.openxmlformats.org/package/2006/relationships');

            $node = $docxPath->query($query)->item(0);

            if (in_array($node->getAttribute('Id'), $IdsArray)) {
                //We check if there is a TargetMode attribute and we set it to Internal otherwise
                $target = $node->getAttribute('TargetMode');
                if (empty($target)) {
                    $node->setAttribute('TargetMode', "Internal");
                }
                //Now we clone the node to insert it in $newRels
                //We do not include the child nodes in case there are any
                $myNode = $node->cloneNode(false);
                $newNode = $newRels->importNode($myNode, true);
                $newRels->documentElement->appendChild($newNode);
            }
        }
        //Now we run a standard C14N canonicalization method and obtain its sha1 digest value
        $toBeSigned = $newRels->documentElement->C14N();
        return base64_encode(sha1($toBeSigned, true));
    }

    /**
     * Insert DOM element inside, before or after a DOM element
     * 
     * @access private
     * @param DOM $newNode New node
     * @param DOM $newNode Ref node
     * @param string $insertMode Ref node : 'inside', 'before', 'after'
     */
    private function insertNode($newNode, $refNode, $insertMode)
    {
        $newNode = $refNode->ownerDocument->importNode($newNode, true);
        if (!$insertMode || $insertMode == 'inside') {
            $refNode->appendChild($newNode);
        } else if ($insertMode == 'before') {
            $refNode->appendChild($newNode);
            $refNode->parentNode->insertBefore($newNode, $refNode);
        } else if ($insertMode == 'after') {
            if ($refNode->nextSibling) {
                $refNode->parentNode->insertBefore($newNode, $refNode->nextSibling);
            } else {
                $refNode->parentNode->appendChild($newNode);
            }
        }
    }

    /**
     * Return array of Ids of .rels files
     * 
     * @access private
     * @param DOM $newNode New node
     * @param DOM $newNode Ref node
     * @param string $insertMode Ref node : 'inside', 'before', 'after'
     */
    private function returnIdsRels()
    {

        // read each rels file and create an array of contents
        for ($i = 0; $i < $this->_docx->numFiles; $i++) {
            if (preg_match('/^.*\.(rels)$/', $this->_docx->getNameIndex($i))) {
                $optionEntityLoader = libxml_disable_entity_loader(true);
                $xmlContent = simplexml_load_string($this->_docx->getFromName($this->_docx->getNameIndex($i)));
                libxml_disable_entity_loader($optionEntityLoader);
                $xmlContent->registerXPathNamespace('rels', 'http://schemas.openxmlformats.org/package/2006/relationships');
                // _rels/.rels file only needs document.xml Id
                if ($this->_docx->getNameIndex($i) == '_rels/.rels') {
                    $elementsRelationship = $xmlContent->xpath('//rels:Relationship[@Target="word/document.xml"]');
                } else {
                    $elementsRelationship = $xmlContent->xpath('//rels:Relationship');
                }
                if ($elementsRelationship) {
                    $idsRelsTemp = array();
                    foreach ($elementsRelationship as $elementRelationship) {
                        $domElementRelationship = dom_import_simplexml($elementRelationship);
                        $idsRelsTemp[] = $domElementRelationship->getAttribute('Id');
                    }
                    $this->_idsRels[$this->_docx->getNameIndex($i)] = $idsRelsTemp;
                }
            }
        }
    }

    /**
     * Set SignatureValue
     * 
     * @access private
     */
    private function setSignatureValue()
    {
        $this->_xml->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        $seSignedInfo = $this->_xml->xpath('//ds:SignedInfo');
        $seSignatureValue = $this->_xml->xpath('//ds:SignatureValue');
        $domIdSignedInfo = dom_import_simplexml($seSignedInfo[0]);
        $domSignatureValue = dom_import_simplexml($seSignatureValue[0]);
        // use privatekey to sign SignatureValue
        $this->_privatekey->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        $signDigestValue = $this->_privatekey->sign($domIdSignedInfo->C14N());
        $domSignatureValue->nodeValue = base64_encode($signDigestValue);
    }

    /**
     *
     * @access private
     */
    private function setValuesIdPackageSignature()
    {
        $xmlIdPackageObject = $this->_xml->asXML();
        $xmlIdPackageObject = str_replace('xmlns:mdssi="http://schemas.openxmlformats.org/package/2006/digital-signature" S', 'S', $xmlIdPackageObject);
        $this->_xml = new SimpleXMLElement($xmlIdPackageObject);

        $this->_xml->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        //get idPackageObject XML
        $seIdPackageObject = $this->_xml->xpath('//ds:Object[@Id="idPackageObject"]');
        // set idPackageObject DigestValue
        $domDigestValueIdPackageObject = $this->_xml->xpath('//ds:Reference[@URI="#idPackageObject"]/ds:DigestValue');
        $domElementDigestValueIdPackageObject = dom_import_simplexml($domDigestValueIdPackageObject[0]);
        $domIdPackageObject = dom_import_simplexml($seIdPackageObject[0]);
        $domElementDigestValueIdPackageObject->nodeValue = $this->calculateDigestValue($domIdPackageObject->C14N());

        //get idOfficeObject XML
        $seIdOfficeObject = $this->_xml->xpath('//ds:Object[@Id="idOfficeObject"]');
        // set idOfficeObject DigestValue
        $domDigestValueIdOfficeObject = $this->_xml->xpath('//ds:Reference[@URI="#idOfficeObject"]/ds:DigestValue');
        $domElementDigestValueIdOfficeObject = dom_import_simplexml($domDigestValueIdOfficeObject[0]);
        $domIdOfficeObject = dom_import_simplexml($seIdOfficeObject[0]);
        $domElementDigestValueIdOfficeObject->nodeValue = $this->calculateDigestValue($domIdOfficeObject->C14N());
    }

}
