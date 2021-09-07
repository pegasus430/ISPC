<?php

/**
 * Customizer DOCX documents
 * 
 * @category   Phpdocx
 * @package    Customizer
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
class DOCXCustomizer extends DOCXPath
{
    /**
     * Customizes DOCX contents
     * 
     * @access public
     * @param DOMElement $wordElement Element to be customized
     */
    public function customize(DOMElement $wordElement, $options = array())
    {
        // custom attributes
        if (isset($options['customAttributes']) && is_array($options['customAttributes'])) {
            foreach ($options['customAttributes'] as $customAttributeKey => $customAttributeValue) {
                $wordElement->setAttribute($customAttributeKey, $customAttributeValue);
            }
        }

        // break tag
        if ($options['tagType'] == 'break') {
            if (isset($options['type'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'br');

                if (count($elements) > 0) {
                    foreach ($elements as $element) {
                        if ($options['type'] == 'line') {
                            $options['type'] = 'textWrapping';
                        }
                        $element->setAttribute('w:type', $options['type']);
                    }
                }
            }
        }

        // image tag
        if ($options['tagType'] == 'image') {
            if (isset($options['borderColor'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'srgbClr');

                if ($elements->length == 0) {
                    // there's no existing tag to change the property. Create it for each a:solidFill
                    $elementsSolidFill = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'solidFill');

                    if ($elementsSolidFill->length == 0) {
                        // there's no existing tag to add the new tag. Create it for each a:ln
                        $elementsLn = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'ln');

                        if ($elementsLn->length == 0) {
                            // there's no existing tag to add the new tag. Create it for each pic:spPr
                            $elementsSpPr = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/picture', 'spPr');

                            $elementsLn = array();
                            foreach ($elementsSpPr as $elementSpPr) {
                                $elementTag = $elementSpPr->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'ln');
                                $elementTag->setAttribute('w', 76200);
                                $elementSpPr->appendChild($elementTag);

                                $elementsLn[] = $elementTag;
                            }
                        }

                        $elementsSolidFill = array();
                        foreach ($elementsLn as $elementLn) {
                            $elementTag = $elementLn->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'solidFill');
                            $elementLn->appendChild($elementTag);

                            // default values
                            $elementPrtDash = $elementLn->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'prstDash');
                            $elementPrtDash->setAttribute('val', 'solid');
                            $elementLn->appendChild($elementPrtDash);

                            $elementsSolidFill[] = $elementTag;
                        }
                    }

                    $elements = array();
                    foreach ($elementsSolidFill as $elementSolidFill) {
                        $elementTag = $elementSolidFill->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'srgbClr');
                        $elementSolidFill->appendChild($elementTag);

                        $elements[] = $elementTag;
                    }
                }

                $this->insertAttributes($elements, array('val' => $options['borderColor']));
            }

            if (isset($options['borderStyle'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'prstDash');

                if ($elements->length == 0) {
                    // there's no existing tag to add the new tag. Create it for each a:ln
                    $elementsLn = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'ln');

                    if ($elementsLn->length == 0) {
                        // there's no existing tag to add the new tag. Create it for each pic:spPr
                        $elementsSpPr = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/picture', 'spPr');

                        $elementsLn = array();
                        foreach ($elementsSpPr as $elementSpPr) {
                            $elementTag = $elementSpPr->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'ln');
                            $elementTag->setAttribute('w', 76200);
                            $elementSpPr->appendChild($elementTag);

                            $elementsLn[] = $elementTag;
                        }
                    }

                    $elements = array();
                    foreach ($elementsLn as $elementLn) {
                        $elementTag = $elementLn->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'prstDash');
                        $elementLn->appendChild($elementTag);

                        // default values
                        $elementSolidFill = $elementLn->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'solidFill');
                        $elementSrgbClr = $elementLn->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'srgbClr');
                        $elementSolidFill->setAttribute('val', '000000');
                        $elementSolidFill->appendChild($elementSrgbClr);
                        $elementLn->appendChild($elementSolidFill);

                        $elements[] = $elementTag;
                    }
                }

                $this->insertAttributes($elements, array('val' => $options['borderStyle']));
            }

            if (isset($options['borderWidth'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'ln');

                if ($elements->length == 0) {
                    // there's no existing tag to add the new tag. Create it for each pic:spPr
                    $elementsSpPr = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/picture', 'spPr');

                    $elements = array();
                    foreach ($elementsSpPr as $elementSpPr) {
                        $elementTag = $elementSpPr->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'ln');
                        $elementTag->setAttribute('w', 76200);
                        $elementSpPr->appendChild($elementTag);

                        // default values
                        $elementPrtDash = $elementTag->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'prstDash');
                        $elementPrtDash->setAttribute('val', 'solid');
                        $elementTag->appendChild($elementPrtDash);
                        $elementSolidFill = $elementTag->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'solidFill');
                        $elementSrgbClr = $elementTag->ownerDocument->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'srgbClr');
                        $elementSolidFill->setAttribute('val', '000000');
                        $elementSolidFill->appendChild($elementSrgbClr);
                        $elementTag->appendChild($elementSolidFill);

                        $elements[] = $elementTag;
                    }
                }

                $this->insertAttributes($elements, array('w' => $options['borderWidth']*76200));
            }

            if (isset($options['height'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing', 'extent');

                $this->insertAttributes($elements, array('cy' => $options['height']));

                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'ext');

                $this->insertAttributes($elements, array('cy' => $options['height']));
            }

            if (isset($options['width'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing', 'extent');

                $this->insertAttributes($elements, array('cx' => $options['width']));

                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'ext');

                $this->insertAttributes($elements, array('cx' => $options['width']));
            }

            if (isset($options['imageAlign'])) {
                $this->generateWrapperContent($wordElement, 'jc', 'pPr', array('w:val' => $options['imageAlign']));
            }

            if (isset($options['spacingBottom']) || isset($options['spacingLeft']) || isset($options['spacingRight']) || isset($options['spacingTop'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing', 'effectExtent');

                if (count($elements) > 0) {
                    foreach ($elements as $element) {
                        if (isset($options['spacingBottom'])) {
                            $element->setAttribute('b', $options['spacingBottom']);
                        }
                        if (isset($options['spacingLeft'])) {
                            $element->setAttribute('l', $options['spacingLeft']);
                        }
                        if (isset($options['spacingRight'])) {
                            $element->setAttribute('r', $options['spacingRight']);
                        }
                        if (isset($options['spacingTop'])) {
                            $element->setAttribute('t', $options['spacingTop']);
                        }
                    }
                }
            }
        }

        // list tag
        if ($options['tagType'] == 'list' || $options['tagType'] == 'paragraph') {
            if (isset($options['depthLevel'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'ilvl');

                $this->insertAttributes($elements, array('w:val' => $options['depthLevel']));
            }

            if (isset($options['type'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'numId');

                $this->insertAttributes($elements, array('w:val' => $options['type']));
            }
        }

        // paragraph and list tags
        if ($options['tagType'] == 'paragraph' || $options['tagType'] == 'list' || $options['tagType'] == 'style') {
            // rPr values
            if (isset($options['bold'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'b', 'rPr', array('w:val' => $options['bold']));
                    $this->generateWrapperContent($wordElement, 'bCs', 'rPr', array('w:val' => $options['bold']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'b', 'rPr', array('w:val' => $options['bold']));
                    $this->generateWrapperRprContent($wordElement, 'bCs', 'rPr', array('w:val' => $options['bold']));
                }
            }

            if (isset($options['caps'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'caps', 'rPr', array('w:val' => $options['caps']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'caps', 'rPr', array('w:val' => $options['caps']));
                }
            }

            if (isset($options['color'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'color', 'rPr', array('w:val' => $options['color']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'color', 'rPr', array('w:val' => $options['color']));
                }
            }

            if (isset($options['contextualSpacing'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'contextualSpacing', 'rPr', array('w:val' => $options['contextualSpacing']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'contextualSpacing', 'rPr', array('w:val' => $options['contextualSpacing']));
                }
            }

            if (isset($options['em'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'em', 'rPr', array('w:val' => $options['em']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'em', 'rPr', array('w:val' => $options['em']));
                }
            }

            if (isset($options['font'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'rFonts', 'rPr', array('w:ascii' => $options['font'], 'w:hAnsi' => $options['font'], 'w:cs' => $options['font']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'rFonts', 'rPr', array('w:ascii' => $options['font'], 'w:hAnsi' => $options['font'], 'w:cs' => $options['font']));
                }
            }

            if (isset($options['fontSize'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'sz', 'rPr', array('w:val' => $options['fontSize']*2));
                    $this->generateWrapperContent($wordElement, 'szCs', 'rPr', array('w:ascii' => $options['fontSize']*2));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'sz', 'rPr', array('w:val' => $options['fontSize']*2));
                    $this->generateWrapperRprContent($wordElement, 'szCs', 'rPr', array('w:ascii' => $options['fontSize']*2));
                }
            }

            if (isset($options['italic'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'i', 'rPr', array('w:val' => $options['italic']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'i', 'rPr', array('w:val' => $options['italic']));
                }
            }

            if (isset($options['smallCaps'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'smallCaps', 'rPr', array('w:val' => $options['smallCaps']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'smallCaps', 'rPr', array('w:val' => $options['smallCaps']));
                }
            }

            if (isset($options['underline'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'u', 'rPr', array('w:val' => $options['underline']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'u', 'rPr', array('w:val' => $options['underline']));
                }
            }

            if (isset($options['underlineColor'])) {
                if ($options['tagType'] == 'style') {
                    $this->generateWrapperContent($wordElement, 'u', 'rPr', array('w:color' => $options['underlineColor']));
                } else {
                    $this->generateWrapperRprContent($wordElement, 'u', 'rPr', array('w:color' => $options['underlineColor']));
                }
                
            }

            // pPr values
            if (isset($options['backgroundColor'])) {
                $this->generateWrapperContent($wordElement, 'shd', 'pPr', array('w:fill' => $options['backgroundColor']));
            }

            if (isset($options['headingLevel'])) {
                $this->generateWrapperContent($wordElement, 'outlineLvl', 'pPr', array('w:val' => $options['headingLevel']));
            }

            if (isset($options['lineSpacing'])) {
                $this->generateWrapperContent($wordElement, 'spacing', 'pPr', array('w:line' => $options['lineSpacing'], 'w:lineRule' => 'auto'));
            }

            if (isset($options['pStyle'])) {
                $this->generateWrapperContent($wordElement, 'pStyle', 'pPr', array('w:val' => $options['pStyle']));
            }

            if (isset($options['spacingBottom'])) {
                $this->generateWrapperContent($wordElement, 'spacing', 'pPr', array('w:after' => $options['spacingBottom']));
            }

            if (isset($options['spacingTop'])) {
                $this->generateWrapperContent($wordElement, 'spacing', 'pPr', array('w:before' => $options['spacingTop']));
            }

            if (isset($options['pageBreakBefore'])) {
                $this->generateWrapperContent($wordElement, 'pageBreakBefore', 'pPr', array('val' => $options['pageBreakBefore']));
            }

            if (isset($options['textAlign'])) {
                $this->generateWrapperContent($wordElement, 'jc', 'pPr', array('w:val' => $options['textAlign']));
            }
        }

        // run tag
        if ($options['tagType'] == 'run') {
            if (isset($options['bold'])) {
                $this->generateWrapperContent($wordElement, 'b', 'rPr', array('w:val' => $options['bold']));
                $this->generateWrapperContent($wordElement, 'bCs', 'rPr', array('w:val' => $options['bold']));
            }

            if (isset($options['caps'])) {
                $this->generateWrapperContent($wordElement, 'caps', 'rPr', array('w:val' => $options['caps']));
            }

            if (isset($options['characterBorder'])) {
                $borderValues = array();
                if (!isset($options['characterBorder']['color'])) {
                    $borderValues['color'] = 'auto';
                } else {
                    $borderValues['color'] = $options['characterBorder']['color'];
                }
                if (!isset($options['characterBorder']['spacing'])) {
                    $borderValues['spacing'] = 0;
                } else {
                    $borderValues['spacing'] = $options['characterBorder']['spacing'];
                }
                if (!isset($options['characterBorder']['type'])) {
                    $borderValues['type'] = 'single';
                } else {
                    $borderValues['type'] = $options['characterBorder']['type'];
                }
                if (!isset($options['characterBorder']['width'])) {
                    $borderValues['width'] = 4;
                } else {
                    $borderValues['width'] = $options['characterBorder']['width'];
                }
                $this->generateWrapperContent($wordElement, 'bdr', 'rPr', array('w:color' => $borderValues['color'], 'w:space' => $borderValues['spacing'], 'w:sz' => $borderValues['width'], 'w:val' => $borderValues['type']));
            }

            if (isset($options['contextualSpacing'])) {
                $this->generateWrapperContent($wordElement, 'contextualSpacing', 'rPr', array('w:val' => $options['contextualSpacing']));
            }

            if (isset($options['em'])) {
                $this->generateWrapperContent($wordElement, 'em', 'rPr', array('w:val' => $options['em']));
            }

            if (isset($options['font'])) {
                $this->generateWrapperContent($wordElement, 'rFonts', 'rPr', array('w:ascii' => $options['font'], 'w:hAnsi' => $options['font'], 'w:cs' => $options['font']));
            }

            if (isset($options['fontSize'])) {
                $this->generateWrapperContent($wordElement, 'sz', 'rPr', array('w:val' => $options['fontSize']));
                $this->generateWrapperContent($wordElement, 'szCs', 'rPr', array('w:ascii' => $options['fontSize']));
            }

            if (isset($options['highlight'])) {
                $this->generateWrapperContent($wordElement, 'highlight', 'rPr', array('w:val' => $options['highlight']));
            }

            if (isset($options['italic'])) {
                $this->generateWrapperContent($wordElement, 'i', 'rPr', array('w:val' => $options['italic']));
            }

            if (isset($options['position'])) {
                $this->generateWrapperContent($wordElement, 'position', 'rPr', array('w:val' => $options['position']));
            }

            if (isset($options['scaling'])) {
                $this->generateWrapperContent($wordElement, 'w', 'rPr', array('w:val' => $options['scaling']));
            }

            if (isset($options['spacing'])) {
                $this->generateWrapperContent($wordElement, 'spacing', 'rPr', array('w:val' => $options['spacing']));
            }

            if (isset($options['smallCaps'])) {
                $this->generateWrapperContent($wordElement, 'smallCaps', 'rPr', array('w:val' => $options['smallCaps']));
            }

            if (isset($options['underline'])) {
                $this->generateWrapperContent($wordElement, 'u', 'rPr', array('w:val' => $options['underline']));
            }

            if (isset($options['underlineColor'])) {
                $this->generateWrapperContent($wordElement, 'u', 'rPr', array('w:color' => $options['underlineColor']));
            }
        }

        // section tag
        if ($options['tagType'] == 'section') {
            if (isset($options['gutter'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgMar');

                $this->insertAttributes($elements, array('w:gutter' => $options['gutter']));
            }

            if (isset($options['height'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgSz');

                $this->insertAttributes($elements, array('w:h' => $options['height']));
            }

            if (isset($options['marginBottom'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgMar');

                $this->insertAttributes($elements, array('w:bottom' => $options['marginBottom']));
            }

            if (isset($options['marginFooter'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgMar');

                $this->insertAttributes($elements, array('w:footer' => $options['marginFooter']));
            }

            if (isset($options['marginHeader'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgMar');

                $this->insertAttributes($elements, array('w:header' => $options['marginHeader']));
            }

            if (isset($options['marginLeft'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgMar');

                $this->insertAttributes($elements, array('w:left' => $options['marginLeft']));
            }

            if (isset($options['marginRight'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgMar');

                $this->insertAttributes($elements, array('w:right' => $options['marginRight']));
            }

            if (isset($options['marginTop'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgMar');

                $this->insertAttributes($elements, array('w:top' => $options['marginTop']));
            }

            if (isset($options['numberCols'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'cols');

                $this->insertAttributes($elements, array('w:num' => $options['numberCols']));
            }

            if (isset($options['orient'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgSz');

                $this->insertAttributes($elements, array('w:orient' => $options['orient']));
            }

            if (isset($options['width'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pgSz');

                $this->insertAttributes($elements, array('w:w' => $options['width']));
            }
        }

        // table tag
        if ($options['tagType'] == 'table') {
            if (
                isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                isset($options['borderRight']) || isset($options['borderRightColor']) || isset($options['borderRightSpacing']) || isset($options['borderRightWidth']) ||
                isset($options['borderBottom']) || isset($options['borderBottomColor']) || isset($options['borderBottomSpacing']) || isset($options['borderBottomWidth']) ||
                isset($options['borderTop']) || isset($options['borderTopColor']) || isset($options['borderTopSpacing']) || isset($options['borderTopWidth']) ||
                isset($options['borderLeft']) || isset($options['borderLeftColor']) || isset($options['borderLeftSpacing']) || isset($options['borderLeftWidth']) ||
                isset($options['borderInsideH']) || isset($options['borderInsideHColor']) || isset($options['borderInsideHSpacing']) || isset($options['borderInsideHWidth']) ||
                isset($options['borderInsideV']) || isset($options['borderInsideVColor']) || isset($options['borderInsideVSpacing']) || isset($options['borderInsideVWidth'])
            ) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblBorders');

                if ($elements->length == 0) {
                    // default values only if there's no previous tag
                    if (!isset($options['border'])) {
                        $options['border'] = 'single';
                    }
                    if (!isset($options['borderColor'])) {
                        $options['borderColor'] = 'auto';
                    }
                    if (!isset($options['borderSpacing'])) {
                        $options['borderSpacing'] = 0;
                    }
                    if (!isset($options['borderWidth'])) {
                        $options['borderWidth'] = 6;
                    }

                    $elements = array();

                    // there's no existing tag to add the borders. Create it for w:tblPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblPr');

                    $elementTblBorders = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblBorders');
                    $elementTag->item(0)->appendChild($elementTblBorders);

                    $elements[] = $elementTblBorders;
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderTop']) || isset($options['borderTopColor']) || isset($options['borderTopSpacing']) || isset($options['borderTopWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderTop'])) {
                        $border = $options['borderTop'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderTopColor'])) {
                        $borderColor = $options['borderTopColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderTopSpacing'])) {
                        $borderSpacing = $options['borderTopSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderTopWidth'])) {
                        $borderWidth = $options['borderTopWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'top', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderLeft']) || isset($options['borderLeftColor']) || isset($options['borderLeftSpacing']) || isset($options['borderLeftWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderLeft'])) {
                        $border = $options['borderLeft'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderLeftColor'])) {
                        $borderColor = $options['borderLeftColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderLeftSpacing'])) {
                        $borderSpacing = $options['borderLeftSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderLeftWidth'])) {
                        $borderWidth = $options['borderLeftWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'left', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderBottom']) || isset($options['borderBottomColor']) || isset($options['borderBottomSpacing']) || isset($options['borderBottomWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderBottom'])) {
                        $border = $options['borderBottom'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderBottomColor'])) {
                        $borderColor = $options['borderBottomColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderBottomSpacing'])) {
                        $borderSpacing = $options['borderBottomSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderBottomWidth'])) {
                        $borderWidth = $options['borderBottomWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'bottom', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderRight']) || isset($options['borderRightColor']) || isset($options['borderRightSpacing']) || isset($options['borderRightWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderRight'])) {
                        $border = $options['borderRight'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderRightColor'])) {
                        $borderColor = $options['borderRightColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderRightSpacing'])) {
                        $borderSpacing = $options['borderRightSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderRightWidth'])) {
                        $borderWidth = $options['borderRightWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'right', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));

                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderInsideH']) || isset($options['borderInsideHColor']) || isset($options['borderInsideHSpacing']) || isset($options['borderInsideHWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderInsideH'])) {
                        $border = $options['borderInsideH'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderInsideHColor'])) {
                        $borderColor = $options['borderInsideHColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderInsideHSpacing'])) {
                        $borderSpacing = $options['borderInsideHSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderInsideHWidth'])) {
                        $borderWidth = $options['borderInsideHWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'insideH', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderInsideV']) || isset($options['borderInsideVColor']) || isset($options['borderInsideVSpacing']) || isset($options['borderInsideVWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderInsideV'])) {
                        $border = $options['borderInsideV'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderInsideVColor'])) {
                        $borderColor = $options['borderInsideVColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderInsideVSpacing'])) {
                        $borderSpacing = $options['borderInsideVSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderInsideVWidth'])) {
                        $borderWidth = $options['borderInsideVWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'insideV', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }
            }

            if (isset($options['cellMargin'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblCellMar');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:tblPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblPr');

                    $elementCellMar = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblCellMar');
                    $elementTag->item(0)->appendChild($elementCellMar);

                    $elements[] = $elementCellMar;
                }

                if (count($elements) > 0) {
                    foreach ($elements as $element) {
                        if (isset($options['cellMargin']['top'])) {
                            $elementsTop = $this->generateMarginCellContent($element, 'top');

                            $this->insertAttributes($elementsTop, array('w:w' => $options['cellMargin']['top']));
                        }
                        if (isset($options['cellMargin']['right'])) {
                            $elementsRight = $this->generateMarginCellContent($element, 'right');

                            $this->insertAttributes($elementsRight, array('w:w' => $options['cellMargin']['right']));
                        }
                        if (isset($options['cellMargin']['bottom'])) {
                            $elementsBottom = $this->generateMarginCellContent($element, 'bottom');

                            $this->insertAttributes($elementsBottom, array('w:w' => $options['cellMargin']['bottom']));
                        }
                        if (isset($options['cellMargin']['left'])) {
                            $elementsLeft = $this->generateMarginCellContent($element, 'left');

                            $this->insertAttributes($elementsLeft, array('w:w' => $options['cellMargin']['left']));
                        }
                    }
                }
            }

            if (isset($options['cellSpacing'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblCellSpacing');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:tblPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblPr');

                    $elementCellSpacing = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblCellSpacing');
                    // default values
                    $elementCellSpacing->setAttribute('w:type', 'dxa');

                    $elementTag->item(0)->appendChild($elementCellSpacing);

                    $elements[] = $elementCellSpacing;
                }

                $this->insertAttributes($elements, array('w:w' => $options['cellSpacing']));
            }

            if (isset($options['columnWidths'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'gridCol');

                if (count($elements) > 0) {
                    $i = 0;
                    foreach ($elements as $element) {
                        $element->setAttribute('w:w', $options['columnWidths'][$i]);
                        $i++;
                    }
                }
            }

            if (isset($options['indent'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblInd');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:tblPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblPr');

                    $elementTblInd = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblInd');
                    // default values
                    $elementTblInd->setAttribute('w:type', 'dxa');

                    $elementTag->item(0)->appendChild($elementTblInd);

                    $elements[] = $elementTblInd;
                }

                $this->insertAttributes($elements, array('w:w' => $options['indent']));
            }

            if (isset($options['tableAlign'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'jc');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:tblPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblPr');

                    $elementJC = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'jc');

                    $elementTag->item(0)->appendChild($elementJC);

                    $elements[] = $elementJC;
                }

                $this->insertAttributes($elements, array('w:val' => $options['tableAlign']));
            }

            if (isset($options['tableStyle'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblStyle');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:tblPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblPr');

                    $elementTblStyle = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblStyle');

                    $elementTag->item(0)->appendChild($elementTblStyle);

                    $elements[] = $elementTblStyle;
                }

                $this->insertAttributes($elements, array('w:val' => $options['tableStyle']));
            }

            if (isset($options['tableWidth'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblW');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:tblPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblPr');

                    $elementTblWidth = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tblW');

                    $elementTag->item(0)->appendChild($elementTblWidth);

                    $elements[] = $elementTblWidth;
                }

                $this->insertAttributes($elements, array('w:type' => $options['tableWidth']['type'], 'w:w' => $options['tableWidth']['value']));
            }
        }

        // table-cell tag
        if ($options['tagType'] == 'table-cell') {
            if (isset($options['backgroundColor'])) {
                $this->generateWrapperContent($wordElement, 'shd', 'tcPr', array('w:fill' => $options['backgroundColor']));
            }

            if (
                isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                isset($options['borderRight']) || isset($options['borderRightColor']) || isset($options['borderRightSpacing']) || isset($options['borderRightWidth']) ||
                isset($options['borderBottom']) || isset($options['borderBottomColor']) || isset($options['borderBottomSpacing']) || isset($options['borderBottomWidth']) ||
                isset($options['borderTop']) || isset($options['borderTopColor']) || isset($options['borderTopSpacing']) || isset($options['borderTopWidth']) ||
                isset($options['borderLeft']) || isset($options['borderLeftColor']) || isset($options['borderLeftSpacing']) || isset($options['borderLeftWidth']) ||
                isset($options['borderInsideH']) || isset($options['borderInsideHColor']) || isset($options['borderInsideHSpacing']) || isset($options['borderInsideHWidth']) ||
                isset($options['borderInsideV']) || isset($options['borderInsideVColor']) || isset($options['borderInsideVSpacing']) || isset($options['borderInsideVWidth'])
            ) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tcBorders');

                if ($elements->length == 0) {
                    // default values only if there's no previous tag
                    if (!isset($options['border'])) {
                        $options['border'] = 'single';
                    }
                    if (!isset($options['borderColor'])) {
                        $options['borderColor'] = 'auto';
                    }
                    if (!isset($options['borderSpacing'])) {
                        $options['borderSpacing'] = 0;
                    }
                    if (!isset($options['borderWidth'])) {
                        $options['borderWidth'] = 6;
                    }

                    $elements = array();

                    // there's no existing tag to add the borders. Create it for w:tcPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tcPr');

                    $elementTblBorders = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tcBorders');
                    $elementTag->item(0)->appendChild($elementTblBorders);

                    $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tcBorders');
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderTop']) || isset($options['borderTopColor']) || isset($options['borderTopSpacing']) || isset($options['borderTopWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderTop'])) {
                        $border = $options['borderTop'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderTopColor'])) {
                        $borderColor = $options['borderTopColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderTopSpacing'])) {
                        $borderSpacing = $options['borderTopSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderTopWidth'])) {
                        $borderWidth = $options['borderTopWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'top', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderLeft']) || isset($options['borderLeftColor']) || isset($options['borderLeftSpacing']) || isset($options['borderLeftWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderLeft'])) {
                        $border = $options['borderLeft'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderLeftColor'])) {
                        $borderColor = $options['borderLeftColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderLeftSpacing'])) {
                        $borderSpacing = $options['borderLeftSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderLeftWidth'])) {
                        $borderWidth = $options['borderLeftWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'left', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderBottom']) || isset($options['borderBottomColor']) || isset($options['borderBottomSpacing']) || isset($options['borderBottomWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderBottom'])) {
                        $border = $options['borderBottom'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderBottomColor'])) {
                        $borderColor = $options['borderBottomColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderBottomSpacing'])) {
                        $borderSpacing = $options['borderBottomSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderBottomWidth'])) {
                        $borderWidth = $options['borderBottomWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'bottom', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderRight']) || isset($options['borderRightColor']) || isset($options['borderRightSpacing']) || isset($options['borderRightWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderRight'])) {
                        $border = $options['borderRight'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderRightColor'])) {
                        $borderColor = $options['borderRightColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderRightSpacing'])) {
                        $borderSpacing = $options['borderRightSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderRightWidth'])) {
                        $borderWidth = $options['borderRightWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'right', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderInsideH']) || isset($options['borderInsideHColor']) || isset($options['borderInsideHSpacing']) || isset($options['borderInsideHWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderInsideH'])) {
                        $border = $options['borderInsideH'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderInsideHColor'])) {
                        $borderColor = $options['borderInsideHColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderInsideHSpacing'])) {
                        $borderSpacing = $options['borderInsideHSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderInsideHWidth'])) {
                        $borderWidth = $options['borderInsideHWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'insideH', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }

                if (
                    isset($options['border']) || isset($options['borderColor']) || isset($options['borderSpacing']) || isset($options['borderWidth']) ||
                    isset($options['borderInsideV']) || isset($options['borderInsideVColor']) || isset($options['borderInsideVSpacing']) || isset($options['borderInsideVWidth'])
                ) {
                    // set values to be used. It doesn't overwrite existing tag values
                    $border = null;
                    $borderColor = null;
                    $borderSpacing = null;
                    $borderWidth = null;

                    if (isset($options['borderInsideV'])) {
                        $border = $options['borderInsideV'];
                    } elseif (isset($options['border'])) {
                        $border = $options['border'];
                    }

                    if (isset($options['borderInsideVColor'])) {
                        $borderColor = $options['borderInsideVColor'];
                    } elseif (isset($options['borderColor'])) {
                        $borderColor = $options['borderColor'];
                    }

                    if (isset($options['borderInsideVSpacing'])) {
                        $borderSpacing = $options['borderInsideVSpacing'];
                    } elseif (isset($options['borderSpacing'])) {
                        $borderSpacing = $options['borderSpacing'];
                    }

                    if (isset($options['borderInsideVWidth'])) {
                        $borderWidth = $options['borderInsideVWidth'];
                    } elseif (isset($options['borderWidth'])) {
                        $borderWidth = $options['borderWidth'];
                    }

                    $this->generateBorderTableContent($wordElement, $elements, 'insideV', array('border' => $border, 'color' => $borderColor, 'spacing' => $borderSpacing, 'width' => $borderWidth));
                }
            }

            if (isset($options['cellMargin'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tcMar');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:tblPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tcPr');

                    $elementCellMar = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'tcMar');
                    $elementTag->item(0)->appendChild($elementCellMar);

                    $elements[] = $elementCellMar;
                }

                if (count($elements) > 0) {
                    foreach ($elements as $element) {
                        if (isset($options['cellMargin']['top'])) {
                            $elementsTop = $this->generateMarginCellContent($element, 'top');

                            $this->insertAttributes($elementsTop, array('w:w' => $options['cellMargin']['top']));
                        }
                        if (isset($options['cellMargin']['right'])) {
                            $elementsRight = $this->generateMarginCellContent($element, 'right');

                            $this->insertAttributes($elementsRight, array('w:w' => $options['cellMargin']['right']));
                        }
                        if (isset($options['cellMargin']['bottom'])) {
                            $elementsBottom = $this->generateMarginCellContent($element, 'bottom');

                            $this->insertAttributes($elementsBottom, array('w:w' => $options['cellMargin']['bottom']));
                        }
                        if (isset($options['cellMargin']['left'])) {
                            $elementsLeft = $this->generateMarginCellContent($element, 'left');

                            $this->insertAttributes($elementsLeft, array('w:w' => $options['cellMargin']['left']));
                        }
                    }
                }
            }

            if (isset($options['colspan'])) {
                $this->generateWrapperContent($wordElement, 'gridSpan', 'tcPr', array('w:val' => $options['colspan']));
            }

            if (isset($options['fitText'])) {
                $this->generateWrapperContent($wordElement, 'fitText', 'tcPr', array('w:val' => $options['fitText']));
            }

            if (isset($options['vAlign'])) {
                $this->generateWrapperContent($wordElement, 'vAlign', 'tcPr', array('w:val' => $options['vAlign']));
            }

            if (isset($options['width'])) {
                $this->generateWrapperContent($wordElement, 'tcW', 'tcPr', array('w:w' => $options['width']));
            }
        }

        // table-row tag
        if ($options['tagType'] == 'table-row') {
            if (isset($options['height'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'trHeight');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:trPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'trPr');

                    $elementTrHeight = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'trHeight');

                    $elementTag->item(0)->appendChild($elementTrHeight);

                    $elements[] = $elementTrHeight;
                }

                $this->insertAttributes($elements, array('w:hRule' => 'exact', 'w:val' => $options['height']));
            }

            if (isset($options['minHeight'])) {
                $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'trHeight');

                if ($elements->length == 0) {
                    $elements = array();

                    // there's no existing tag to add the margin. Create it for w:trPr
                    $elementTag = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'trPr');

                    $elementTrHeight = $elementTag->item(0)->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'trHeight');

                    $elementTag->item(0)->appendChild($elementTrHeight);

                    $elements[] = $elementTrHeight;
                }

                $this->insertAttributes($elements, array('w:hRule' => 'atLeast', 'w:val' => $options['minHeight']));
            }
        }
    }

    /**
     * Generate border table content
     * 
     * @access private
     * @param DOMElement $wordElement Element to be customized
     * @param string $target Target of the attribute
     * @param array $attributes Attributes to be set
     */
    private function generateBorderTableContent(DOMElement $wordElement, $elementsTcPr, $target, $attributes)
    {
        $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $target);
        if ($elements->length == 0) {
            $elements = array();

            foreach ($elementsTcPr as $elementTcPr) {
                $elementTag = $elementTcPr->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $target);
                $elementTcPr->appendChild($elementTag);

                $elements[] = $elementTag;
            }
        }

        if (count($elements) > 0) {
            foreach ($elements as $element) {
                if (isset($attributes['border']) && $attributes['border'] !== null) {
                    $element->setAttribute('w:val', $attributes['border']);
                }
                if (isset($attributes['color']) && $attributes['color'] !== null) {
                    $element->setAttribute('w:color', $attributes['color']);
                }
                if (isset($attributes['spacing']) && $attributes['spacing'] !== null) {
                    $element->setAttribute('w:space', $attributes['spacing']);
                }
                if (isset($attributes['width']) && $attributes['width'] !== null) {
                    $element->setAttribute('w:sz', $attributes['width']);
                }
            }
        }
    }

    /**
     * Generate margin cell content
     * 
     * @access private
     * @param DOMElement $wordElement Element to be customized
     * @param string $target Target of the attribute
     * @return DOMNodeList Elements
     */
    private function generateMarginCellContent(DOMElement $wordElement, $target)
    {
        $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $target);

        if ($elements->length == 0) {
            $elements = array();

            $elementTarget = $wordElement->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $target);
            $wordElement->appendChild($elementTarget);

            $elements[] = $elementTarget;
        }

        return $elements;
    }

    /**
     * Generate content that is wrapped by other content such as pPr tblPr
     * 
     * @access private
     * @param DOMElement $wordElement Element to be customized
     * @param string $tag Tag to be added
     * @param string $wrapper Wrapper tag
     * @param array $attributes Attributes and values
     * @param string $value Value of the tag
     */
    private function generateWrapperContent(DOMElement $wordElement, $tag, $wrapper, $attributes)
    {
        $elements = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $tag);

        if ($elements->length == 0) {
            // there's no existing tag to change the property. Create it for each w:pPr
            $elementsWrapper = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $wrapper);

            // create the wrapper if not exists
            if ($elementsWrapper->length == 0) {
                $elementsWrapper = array();

                $elementTagWrapper = $wordElement->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $wrapper);
                if ($wordElement->firstChild) {
                    // add as first child
                    $wordElement->insertBefore($elementTagWrapper, $wordElement->firstChild);
                    $elementsWrapper[] = $elementTagWrapper;
                }
            }

            // keep each new tag to use it to add the new property
            $elements = array();
            foreach ($elementsWrapper as $elementWrapper) {
                $elementTag = $elementWrapper->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $tag);
                $elementWrapper->appendChild($elementTag);

                $elements[] = $elementTag;
            }
        }

        $this->insertAttributes($elements, $attributes);
    }

    /**
     * Generate content that is wrapped by one or more internal contents such as rPr
     * 
     * @access private
     * @param DOMElement $wordElement Element to be customized
     * @param string $tag Tag to be added
     * @param string $wrapper Wrapper tag
     * @param array $attributes Attributes and values
     * @param string $value Value of the tag
     */
    private function generateWrapperRprContent(DOMElement $wordElement, $tag, $wrapper, $attributes)
    {
        // get all w:r
        $elementsR = $wordElement->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'r');

        // iterate each w:r to add the new attribute
        if ($elementsR->length > 0) {
            foreach ($elementsR as $elementR) {
                $elements = $elementR->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $tag);

                if ($elements->length == 0) {
                    // there's no existing tag to change the property. Create it for each w:r
                    $elementsWrapper = $elementR->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $wrapper);

                    // create the wrapper if not exists
                    if ($elementsWrapper->length == 0) {
                        $elementsWrapper = array();

                        $elementTagWrapper = $wordElement->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $wrapper);
                        if ($elementR->firstChild) {
                            // add as first child
                            $elementR->insertBefore($elementTagWrapper, $elementR->firstChild);
                            $elementsWrapper[] = $elementTagWrapper;
                        }
                    }

                    // keep each new tag to use it to add the new property
                    $elements = array();
                    foreach ($elementsWrapper as $elementWrapper) {
                        $elementTag = $elementWrapper->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', $tag);
                        $elementWrapper->appendChild($elementTag);

                        $elements[] = $elementTag;
                    }
                }

                $this->insertAttributes($elements, $attributes);
            }
        }
    }

    /**
     * Insert attributes to elements
     * 
     * @access private
     * @param array $elements Elements to be changed
     * @param array $attributes New attributes
     */
    private function insertAttributes($elements, $attributes)
    {
        if (count($elements) > 0) {
            foreach ($elements as $element) {
                foreach ($attributes as $attribute => $value) {
                    if (is_bool($value)) {
                        ($value == true) ? $element->setAttribute($attribute, 'on') : $element->setAttribute($attribute, 'off');
                    } else {
                        $element->setAttribute($attribute, $value);
                    }
                }
            }
        }
    }
}