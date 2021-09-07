<?php

/**
 * Process a DOCX to get the best performance when using the document as a template.
 * 
 * @category   Phpdocx
 * @package    performance
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
class ProcessTemplate
{
    /**
     * Process the template to optimize the performance
     *
     * @access public
     * @param string $source DOCX Source path of the template to optimize
     * @param string $dest DOCX Destination path of the optimized template.
     * @param array $variables Array of variables to optimize.
     * @param string $templateSymbol Template symbol
     * @return void
     */
    public function optimizeTemplate($source, $dest, $variables = array(), $templateSymbol = '$')
    {
        if (!copy($source, $dest)) {
            throw new Exception('Error while creating the destination file ' . $dest);
        }

        $zipDocx = new ZipArchive();
        try {
            $openZip = $zipDocx->open($dest);
            if ($openZip !== true) {
                throw new Exception('Error while trying to open the (base) template as a zip file');
            }
        } catch (Exception $e) {
            PhpdocxLogger::logger($e->getMessage(), 'fatal');
        }

        $contentTypeT = $zipDocx->getFromName('[Content_Types].xml');

        // main document
        $loadContent = $zipDocx->getFromName('word/document.xml');
        $stringDoc = $this->repairVariables($variables, $loadContent, $templateSymbol);
        $stringDoc = $this->removeExtraTags($stringDoc);
        $zipDocx->addFromString('word/document.xml', $stringDoc);

        // headers
        $xpathHeaders = simplexml_load_string($contentTypeT);
        $xpathHeaders->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/package/2006/content-types');
        $xpathHeadersResults = $xpathHeaders->xpath('ns:Override[@ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"]');
        foreach ($xpathHeadersResults as $headersResults) {
            $header = substr($headersResults['PartName'], 1);
            $loadContent = $zipDocx->getFromName($header);
            $dom = $this->repairVariables($variables, $loadContent, $templateSymbol);
            $stringDoc = $this->removeExtraTags($dom);
            $zipDocx->addFromString($header, $stringDoc);
        }

        // footers
        $xpathFooters = simplexml_load_string($contentTypeT);
        $xpathFooters->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/package/2006/content-types');
        $xpathFootersResults = $xpathFooters->xpath('ns:Override[@ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"]');
        foreach ($xpathFootersResults as $footersResults) {
            $footer = substr($footersResults['PartName'], 1);
            $loadContent = $zipDocx->getFromName($footer);
            $dom = $this->repairVariables($variables, $loadContent, $templateSymbol);
            $stringDoc = $this->removeExtraTags($dom);
            $zipDocx->addFromString($footer, $stringDoc);
        }

        $zipDocx->close();
    }

    /**
     * Removes extra tags
     *
     * @access private
     * @param array $variables
     * @param string $content
     * @param string $templateSymbol
     * @return string
     */
    private function removeExtraTags($content)
    {
        $tagsToRemove = array('<w:proofErr w:type="spellStart"/>', '<w:proofErr w:type="spellEnd"/>');

        return str_replace($tagsToRemove, '', $content);
    }

    /**
     * Prepares a single PHPDocX variable for substitution
     *
     * @access private
     * @param array $variables
     * @param string $content
     * @param string $templateSymbol
     * @return string
     */
    private function repairVariables($variables, $content, $templateSymbol = '$')
    {
        $documentSymbol = explode($templateSymbol, $content);
        foreach ($variables as $var => $value) {
            foreach ($documentSymbol as $documentSymbolValue) {
                $tempSearch = trim(strip_tags($documentSymbolValue));
                if ($tempSearch == $value) {
                    $pos = strpos($content, $documentSymbolValue);
                    if ($pos !== false) {
                        $content = substr_replace($content, $value, $pos, strlen($documentSymbolValue));
                    }
                }
                if (strpos($documentSymbolValue, 'xml:space="preserve"')) {
                    $preserve = true;
                }
            }
            if (isset($preserve) && $preserve) {
                $query = '//w:t[text()[contains(., "' . $templateSymbol . $value . $templateSymbol . '")]]';
                $docDOM = new DOMDocument();
                $optionEntityLoader = libxml_disable_entity_loader(true);
                $docDOM->loadXML($content);
                libxml_disable_entity_loader($optionEntityLoader);
                $docXPath = new DOMXPath($docDOM);
                $docXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $affectedNodes = $docXPath->query($query);
                foreach ($affectedNodes as $node) {
                    $space = $node->getAttribute('xml:space');
                    if (isset($space) && $space == 'preserve') {
                        //Do nothing 
                    } else {
                        $str = $node->nodeValue;
                        $firstChar = $str[0];
                        if ($firstChar == ' ') {
                            $node->nodeValue = substr($str, 1);
                        }
                        $node->setAttribute('xml:space', 'preserve');
                    }
                }
                $content = $docDOM->saveXML($docDOM->documentElement);
                //$content = html_entity_decode($content, ENT_NOQUOTES, 'UTF-8');
            }
        }

        return $content;
    }
    
}