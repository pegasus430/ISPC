<?php

/**
 * Transform documents using native PHP classes
 *
 * @category   Phpdocx
 * @package    trasform
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */

require_once dirname(__FILE__) . '/TransformDocAdv.php';

class TransformDocAdvNative extends TransformDocAdv
{
    /**
     * Transform documents:
     *     DOCX to PDF, HTML
     *
     * @access public
     * @param $source
     * @param $target
     * @param array $options :
     *   'htmlPlugin' (TransformDocAdvHTMLPlugin): plugin to use to do the transformation to HTML. TransformDocAdvHTMLDefaultPlugin as default
     *   'stream' (bool): enable the stream mode. False as default
     * @return void or stream
     */
    public function transformDocument($source, $target, $options = array())
    {
        $allowedExtensionsSource = array('docx', 'html');
        $allowedExtensionsTarget = array('html', 'docx', 'pdf');

        $filesExtensions = $this->checkSupportedExtension($source, $target, $allowedExtensionsSource, $allowedExtensionsTarget);

        if ($filesExtensions['sourceExtension'] == 'docx') {
            if ($filesExtensions['targetExtension'] == 'html') {
                if (!isset($options['htmlPlugin'])) {
                    $options['htmlPlugin'] = new TransformDocAdvHTMLDefaultPlugin();
                }

                $transform = new TransformDocAdvHTML($source);
                $html = $transform->transform($options['htmlPlugin']);

                if ((isset($options['stream']) && $options['stream']) || CreateDocx::$streamMode == true) {
                    // stream mode enabled
                    echo $html;
                } else {
                    // stream mode disabled, save the document
                    file_put_contents($target, $html);
                }
            } else if ($filesExtensions['targetExtension'] == 'pdf') {
                $transform = new TransformDocAdvPDF($source);
                $transform->transform($target);
            }
        } else if ($filesExtensions['sourceExtension'] == 'html') {
            if ($filesExtensions['targetExtension'] == 'docx') {
                $docx = new CreateDocx();
                $docx->embedHTML(file_get_contents($source));

                $docx->createDocx($target);
            } else if ($filesExtensions['targetExtension'] == 'pdf') {
                // first transform HTML to DOCX and then DOCX to PDF
                $docx = new CreateDocx();
                $docx->embedHTML(file_get_contents($source));
                $docx->createDocx($target . '.docx');

                $transform = new TransformDocAdvPDF($source);
                $transform->transform($target);
            }
        }
    }

}
