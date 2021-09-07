<?php

/**
 * Document digest generation
 *
 * @category   Phpdocx
 * @package    blockchain
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       http://www.phpdocx.com
 */
class Blockchain
{
    /**
     * Generates a digest value from a DOCX
     * 
     * @param mixed $source DOCXStructure or string
     * @param array $scope : document, headers, footers, footnotes, endnotes, comments, styles, properties. All content by default
     * @return string digest value
     */
    public function generateDigestDOCX($source, $scope = array())
    {
        if ($source instanceof DOCXStructure) {
            $docxStructure = $source;
        } else {
            $docxStructure = new DOCXStructure();
            $docxStructure->parseDocx($source);
        }

        // get content type
        $contentTypesContent = $docxStructure->getContent('[Content_Types].xml');
        $contentTypesXml = simplexml_load_string($contentTypesContent);

        $content = null;
        if (count($scope) == 0) {
            // iterate over all files to get their contents and generate the digest
            foreach ($contentTypesXml->Override as $override) {
                $content .= $docxStructure->getContent(substr((string)$override->attributes()->PartName, 1));
            }
        } else {
            // iterate over all scope files to get their contents and generate the digest
            foreach ($contentTypesXml->Override as $override) {
                foreach ($override->attributes() as $attribute => $value) {
                    if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml' && in_array('document', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml' && in_array('headers', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml' && in_array('footers', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.comments+xml' && in_array('comments', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.footnotes+xml' && in_array('footnotes', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.endnotes+xml' && in_array('endnotes', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    } else if ($value == 'application/vnd.openxmlformats-package.core-properties+xml' && in_array('properties', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    } else if ($value == 'application/vnd.openxmlformats-officedocument.custom-properties+xml' && in_array('custom-properties', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml' && in_array('styles', $scope)) {
                        $content .= $docxStructure->getContent(substr($override->attributes()->PartName, 1));
                    }
                }
            }
        }

        if ($content) {
            $digest = base64_encode((string) hash('sha256', $content, true));
        }

        return $digest;
    }

    /**
     * Generates a digest value from a DOCX
     * 
     * @param mixed $source DOCXStructure or string
     * @return string digest value
     */
    public function generateDigestPDF($source)
    {
        $content = file_get_contents($source);

        $digest = base64_encode((string) hash('sha256', $content, true));
        
        return $digest;
    }

    /**
     * Returns the document_address custom property value of a DOCX document
     * 
     * @param mixed $source DOCXStructure or string
     * @return string address value
     */
    public function getAddress($source)
    {
        $docx = new CreateDocxFromTemplate($source);

        $indexer = new Indexer($source);
        $output = $indexer->getOutput();

        if (isset($output['properties']['custom']['document_address'])) {
            return $output['properties']['custom']['document_address'];
        } else {
            return null;
        }
    }

    /**
     * Inserts the address value in a DOCX document
     * 
     * @param mixed $source DOCXStructure or string
     * @param string $target
     */
    public function insertAddress($source, $target, $address)
    {
        $docx = new CreateDocxFromTemplate($source);
        $docx->addProperties(array('custom' => 
            array(
                'document_address' => array('text' => $address),
            )
        ));

        $docx->createDocx($target);
    }
}