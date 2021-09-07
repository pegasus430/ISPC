<?php

/**
 * Generate a DOCXStructure from a stream
 * 
 * @category   Phpdocx
 * @package    streams
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       http://www.phpdocx.com
 */
class DOCXStructureFromStream
{
    /**
     * Constructor
     * 
     * @access public
     */
    public function __construct() { }

    /**
     * Generate DOCXStructure from a stream
     * @param string $resourceInput Resource input
     * @return DOCXStructure
     */
    public function generateDOCXStructure($resourceInput) {
        $stream = fopen($resourceInput, 'r');
        if ($stream) {
            $fileContent = stream_get_contents($stream);
            $tempFile = tempnam(CreateDocx::getTempDir(), '_streamdocx');
            file_put_contents($tempFile, $fileContent);
        } else {
            PhpdocxLogger::logger('Unable to get the resource.', 'fatal');
        }

        $docxStructure = new DOCXStructure();
        $docxStructure->parseDocx($tempFile);

        return $docxStructure;
    }
}