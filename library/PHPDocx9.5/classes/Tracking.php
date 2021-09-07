<?php

/**
 * Tracking methods
 * 
 * @category   Phpdocx
 * @package    tracking
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
class Tracking
{
    /**
     * Accepts tracking tags
     * 
     * @access public
     * @param DOMDocument $domDocument
     * @param DOMXPath $domXpath
     * @param string $query
     * @return DOMDocument
     */
    public function acceptTracking($domDocument, $domXpath, $query)
    {
        $contentNodes = $domXpath->query($query);
        
        $contentNodeXPath = new DOMXPath($domDocument);
        $contentNodeXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        if ($contentNodes->length > 0) {
            foreach ($contentNodes as $contentNode) {
                // w:pPrChange remove node and contents
                $queryPprPprChange = './/w:pPrChange';
                $pprPprChangeNodes = $contentNodeXPath->query($queryPprPprChange, $contentNode);
                if ($pprPprChangeNodes->length > 0) {
                    foreach ($pprPprChangeNodes as $pprPprChangeNode) {
                        $pprPprChangeNode->parentNode->removeChild($pprPprChangeNode);
                    }
                }

                // w:rPrChange remove node and keep the contents
                $queryRprRprChange = './/w:rPrChange';
                $rprRprChangeNodes = $contentNodeXPath->query($queryRprRprChange, $contentNode);
                if ($rprRprChangeNodes->length > 0) {
                    foreach ($rprRprChangeNodes as $rprRprChangeNode) {
                        $rprRprChangeNode->parentNode->removeChild($rprRprChangeNode);
                    }
                }

                // w:tblPrChange remove node and keep the contents
                $queryTblPrChange = './/w:tblPrChange';
                $tblPrChangeNodes = $contentNodeXPath->query($queryTblPrChange, $contentNode);
                if ($tblPrChangeNodes->length > 0) {
                    foreach ($tblPrChangeNodes as $tblPrChangeNode) {
                        $tblPrChangeNode->parentNode->removeChild($tblPrChangeNode);
                    }
                }

                // w:tblGridChange remove node and keep the contents
                $queryTblGridChange = './/w:tblGridChange';
                $tblGridNodes = $contentNodeXPath->query($queryTblGridChange, $contentNode);
                if ($tblGridNodes->length > 0) {
                    foreach ($tblGridNodes as $tblGridNode) {
                        $tblGridNode->parentNode->removeChild($tblGridNode);
                    }
                }

                // w:tcPrChange remove node and keep the contents
                $queryTcPrChange = './/w:tcPrChange';
                $tcPrChangeNodes = $contentNodeXPath->query($queryTcPrChange, $contentNode);
                if ($tcPrChangeNodes->length > 0) {
                    foreach ($tcPrChangeNodes as $tcPrChangeNode) {
                        $tcPrChangeNode->parentNode->removeChild($tcPrChangeNode);
                    }
                }
                
                // w:ins remove node and keep the contents
                $queryIns = './/w:ins';
                $insNodes = $contentNodeXPath->query($queryIns, $contentNode);
                if ($insNodes->length > 0) {
                    foreach ($insNodes as $insNode) {
                        // move nodes from w:ins
                        while ($insNode->hasChildNodes()) {
                            $insChild = $insNode->removeChild($insNode->firstChild);
                            $insNode->parentNode->insertBefore($insChild, $insNode);
                        }

                        // remove the w:ins tag
                        $insNode->parentNode->removeChild($insNode);
                    }
                }

                // w:del remove node and contents
                $queryDel = './/w:del';
                $delNodes = $contentNodeXPath->query($queryDel, $contentNode);
                if ($delNodes->length > 0) {
                    foreach ($delNodes as $delNode) {
                        $delNode->parentNode->removeChild($delNode);
                    }
                }
            }

            return $domDocument;
        } else {
            return null;
        }
    }

    /**
     * Adds a person to the people XML
     * 
     * @access public
     * @param DOMDocument $people can be * (all, default value), bookmark, break, chart, endnote (content reference), footnote (content reference), image, list, paragraph (also for links), run, section, shape, table, table-row, table-cell
     * @param array $person
     * @return DOMNode
     */
    public function addPerson($people, $person)
    {
        $peopleDOMXpath = new DOMXPath($people);
        $peopleDOMXpath->registerNamespace('w15', 'http://schemas.microsoft.com/office/word/2012/wordml');

        // check if the person already exists
        $queryPerson = '//w15:person[@w15:author="'.$person['author'].'"]';
        $personNodes = $peopleDOMXpath->query($queryPerson);

        if ($personNodes->length > 0) {
            // the person exists
            PhpdocxLogger::logger('This person is already in the people file', 'warn');
        } else {
            //it's a new person, add it
            $elementPerson = $people->createElementNS('http://schemas.microsoft.com/office/word/2012/wordml', 'w15:person');
            $elementPerson->setAttributeNS('http://schemas.microsoft.com/office/word/2012/wordml', 'w15:author', $person['author']);

            $elementPresenceInfo = $elementPerson->ownerDocument->createElementNS('http://schemas.microsoft.com/office/word/2012/wordml', 'w15:presenceInfo');
            $elementPresenceInfo->setAttributeNS('http://schemas.microsoft.com/office/word/2012/wordml', 'w15:providerId', $person['providerId']);
            $elementPresenceInfo->setAttributeNS('http://schemas.microsoft.com/office/word/2012/wordml', 'w15:userId', $person['userId']);

            $elementPerson->appendChild($elementPresenceInfo);
            $people->documentElement->appendChild($elementPerson);
        }
        
        return $people;
    }


    /**
     * Adds an ins tag to the first w:r XML
     * 
     * @access public
     * @param string $xml content to be updated
     * @return string
     */
    public function addTrackingInsFirstR($xml)
    {
        $posRpr = strpos($xml, '<w:r>');
        $tag = '<w:r>';

        // handle w:r with namespaces
        if (!$posRpr) {
            $posRpr = strpos($xml, '<w:r xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">');
            $tag = '<w:r xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">';
        }

        if ($posRpr !== false) {
            $xml = substr_replace($xml, '<w:ins w:author="'.CreateDocx::$trackingOptions['author'].'" w:date="'.CreateDocx::$trackingOptions['date'].'" w:id="'.CreateDocx::$trackingOptions['id'].'">' . $tag, $posRpr, strlen($tag));

            CreateDocx::$trackingOptions['id'] = CreateDocx::$trackingOptions['id'] + 1;

            $posCloseRpr = strrpos($xml, '</w:r>');

            if ($posCloseRpr) {
                $xml = substr_replace($xml, '</w:r></w:ins>', $posCloseRpr, strlen('</w:r>'));
            }
        }

        return $xml;
    }

    /**
     * Adds an ins tag to w:r XML
     * 
     * @access public
     * @param string $xml content to be updated
     * @return string
     */
    public function addTrackingInsR($xml)
    {
        $xmlDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        // check if the XML has an xml tag, otherwise set it
        $xmlDOM->loadXML('<?xml version="1.0" encoding="UTF-8" ?><w:root xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">' . $xml . '</w:root>');
        libxml_disable_entity_loader($optionEntityLoader);

        $xmlDOMXPath = new DOMXPath($xmlDOM);
        $xmlDOMXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $queryR = '//w:r';
        $rNodes = $xmlDOMXPath->query($queryR);

        // keep the nodes to be removed in an array to clean them in the correct order
        $nodesToBeRemoved = array();

        foreach ($rNodes as $rNode) {
            // avoid adding w:ins if the w:r tag already has one
            if ($rNode->parentNode->tagName == 'w:ins') {
                $xmlContent = $rNode->ownerDocument->saveXML($rNode);
                continue;
            }

            // create and append a w:ins tag
            $insNode = $xmlDOM->createElement('w:ins');
            $insNode->setAttribute('w:author', CreateDocx::$trackingOptions['author']);
            $insNode->setAttribute('w:date', CreateDocx::$trackingOptions['date']);
            $insNode->setAttribute('w:id', CreateDocx::$trackingOptions['id']);

            CreateDocx::$trackingOptions['id'] = CreateDocx::$trackingOptions['id'] + 1;

            $rNodeClone = $rNode->cloneNode(true);

            $insNode->appendChild($rNodeClone);
            $rNode->parentNode->insertBefore($insNode, $rNode);
            
            $nodesToBeRemoved[] = $rNode;
        }

        // clean the nodes to be removed
        foreach ($nodesToBeRemoved as $nodeToBeRemoved) {
            $nodeToBeRemoved->parentNode->removeChild($nodeToBeRemoved);
        }

        return $xmlDOM->saveXML($xmlDOM->firstChild->firstChild);
    }

    /**
     * Adds an ins tag to w:pPr/w:rPr XML tags
     * 
     * @access public
     * @param string $xml content to be updated
     * @return string
     */
    public function addTrackingInsList($xml)
    {
        $xmlDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $xmlDOM->loadXML('<?xml version="1.0" encoding="UTF-8" ?><w:root xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' . $xml . '</w:root>');
        libxml_disable_entity_loader($optionEntityLoader);

        $xmlDOMXPath = new DOMXPath($xmlDOM);
        $xmlDOMXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $queryPPR = '//w:pPr';
        $pPrNodes = $xmlDOMXPath->query($queryPPR);

        // there's a root element parent, so keep only the children tags
        $xmlContent = '';

        // iterate all elements of the list
        foreach ($pPrNodes as $pPrNode) {
            $rPrNodes = $pPrNode->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'rPr');

            if ($rPrNodes->length > 0) {
                $rPrNode = $rPrNodes->item(0);
            } else {
                // create an insert a w:rPr tag
                $rPrNode = $xmlDOM->createElement('w:rPr');
                $pPrNode->appendChild($rPrNode);
            }

            // create and append a w:ins tag
            $insNode = $xmlDOM->createElement('w:ins');
            $insNode->setAttribute('w:author', CreateDocx::$trackingOptions['author']);
            $insNode->setAttribute('w:date', CreateDocx::$trackingOptions['date']);
            $insNode->setAttribute('w:id', CreateDocx::$trackingOptions['id']);
            $rPrNode->appendChild($insNode);

            CreateDocx::$trackingOptions['id'] = CreateDocx::$trackingOptions['id'] + 1;

            //  keep the parent node to save the whole paragraph content
            $xmlContent .= $pPrNode->ownerDocument->saveXML($pPrNode->parentNode);
        }

        return $xmlContent;
    }

    /**
     * Adds an ins tag to w:pPr/w:rPr XML tags with namespaces
     * 
     * @access public
     * @param DOMDocument $xml content to be updated
     * @return string
     */
    public function addTrackingInsListNS($xml)
    {
        $xmlDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $xmlDOM->loadXML($xml);
        libxml_disable_entity_loader($optionEntityLoader);

        $xmlDOMXPath = new DOMXPath($xmlDOM);
        $xmlDOMXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $queryPPR = '//w:pPr';
        $pPrNodes = $xmlDOMXPath->query($queryPPR);

        // there's a root element parent, so keep only the children tags
        $xmlContent = '';

        // iterate all elements of the list
        foreach ($pPrNodes as $pPrNode) {
            $rPrNodes = $pPrNode->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'rPr');

            if ($rPrNodes->length > 0) {
                $rPrNode = $rPrNodes->item(0);
            } else {
                // create an insert a w:rPr tag
                $rPrNode = $xmlDOM->createElement('w:rPr');
                $pPrNode->appendChild($rPrNode);
            }

            // create and append a w:ins tag
            $insNode = $xmlDOM->createElement('w:ins');
            $insNode->setAttribute('w:author', CreateDocx::$trackingOptions['author']);
            $insNode->setAttribute('w:date', CreateDocx::$trackingOptions['date']);
            $insNode->setAttribute('w:id', CreateDocx::$trackingOptions['id']);
            $rPrNode->appendChild($insNode);

            CreateDocx::$trackingOptions['id'] = CreateDocx::$trackingOptions['id'] + 1;
        }

        return $xmlDOM->saveXML();
    }

    /**
     * Adds an ins tag to w:pPr/w:rPr XML tags
     * 
     * @access public
     * @param string $xml content to be updated
     * @return string
     */
    public function addTrackingInsSection($xml)
    {
        // add the same change than list (w:pPr/w:rPr/w:ins)
        return $this->addTrackingInsList($xml);
    }

    /**
     * Adds an ins tag to w:tr/w:trPr XML tags
     * 
     * @access public
     * @param string $xml content to be updated
     * @return string
     */
    public function addTrackingInsTable($xml)
    {
        $xmlDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $xmlDOM->loadXML('<?xml version="1.0" encoding="UTF-8" ?><w:root xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' . $xml . '</w:root>');
        libxml_disable_entity_loader($optionEntityLoader);

        $xmlDOMXPath = new DOMXPath($xmlDOM);
        $xmlDOMXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $queryPPR = '//w:tr';
        $trNodes = $xmlDOMXPath->query($queryPPR);

        // iterate all elements of the list
        foreach ($trNodes as $trNode) {
            $trPrNodes = $trNode->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'trPr');

            if ($trPrNodes->length > 0) {
                $trPrNode = $trPrNodes->item(0);
            } else {
                // create an insert a w:rPr tag
                $trPrNode = $xmlDOM->createElement('w:trPr');
                $trPrNode->appendChild($rPrNode);
            }

            // create and append a w:ins tag
            $insNode = $xmlDOM->createElement('w:ins');
            $insNode->setAttribute('w:author', CreateDocx::$trackingOptions['author']);
            $insNode->setAttribute('w:date', CreateDocx::$trackingOptions['date']);
            $insNode->setAttribute('w:id', CreateDocx::$trackingOptions['id']);
            $trPrNode->appendChild($insNode);

            CreateDocx::$trackingOptions['id'] = CreateDocx::$trackingOptions['id'] + 1;
        }

        $queryPPR = '//w:pPr';
        $pPrNodes = $xmlDOMXPath->query($queryPPR);

        // iterate all elements of the list
        foreach ($pPrNodes as $pPrNode) {
            $rPrNodes = $pPrNode->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'rPr');

            if ($rPrNodes->length > 0) {
                $rPrNode = $rPrNodes->item(0);
            } else {
                // create an insert a w:rPr tag
                $rPrNode = $xmlDOM->createElement('w:rPr');
                $pPrNode->appendChild($rPrNode);
            }

            // create and append a w:ins tag
            $insNode = $xmlDOM->createElement('w:ins');
            $insNode->setAttribute('w:author', CreateDocx::$trackingOptions['author']);
            $insNode->setAttribute('w:date', CreateDocx::$trackingOptions['date']);
            $insNode->setAttribute('w:id', CreateDocx::$trackingOptions['id']);
            $rPrNode->appendChild($insNode);

            CreateDocx::$trackingOptions['id'] = CreateDocx::$trackingOptions['id'] + 1;
        }

        // return the table, not the root parent
        return $xmlDOM->firstChild->ownerDocument->saveXML($xmlDOM->firstChild->firstChild);
    }

    /**
     * Rejects tracking tags
     * 
     * @access public
     * @param DOMDocument $domDocument
     * @param DOMXPath $domXpath
     * @param string $query
     * @return DOMDocument
     */
    public function rejectTracking($domDocument, $domXpath, $query)
    {
        $contentNodes = $domXpath->query($query);
        
        $contentNodeXPath = new DOMXPath($domDocument);
        $contentNodeXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        if ($contentNodes->length > 0) {
            foreach ($contentNodes as $contentNode) {
                // w:pPrChange move nodes and remove the previous w:pPr
                $queryPprPprChange = './/w:pPrChange';
                $pprPprChangeNodes = $contentNodeXPath->query($queryPprPprChange, $contentNode);
                if ($pprPprChangeNodes->length > 0) {
                    $nodesToBeRemoved = array();
                    foreach ($pprPprChangeNodes as $pprPprChangeNode) {
                        $pprChangeNodeClone = $pprPprChangeNode->firstChild->cloneNode(true);
                        $pprPprChangeNode->parentNode->parentNode->insertBefore($pprChangeNodeClone, $pprPprChangeNode->parentNode);
                        $nodesToBeRemoved[] = $pprPprChangeNode;
                    }

                    foreach ($nodesToBeRemoved as $nodeToBeRemoved) {
                        $nodeToBeRemoved->parentNode->parentNode->removeChild($nodeToBeRemoved->parentNode);
                    }
                }

                // w:rPrChange move nodes and remove the previous w:rPr
                $queryRprRprChange = './/w:rPrChange';
                $rprRprChangeNodes = $contentNodeXPath->query($queryRprRprChange, $contentNode);
                if ($rprRprChangeNodes->length > 0) {
                    $nodesToBeRemoved = array();
                    foreach ($rprRprChangeNodes as $rprRprChangeNode) {
                        $rprChangeNodeClone = $rprRprChangeNode->firstChild->cloneNode(true);
                        $rprRprChangeNode->parentNode->parentNode->insertBefore($rprChangeNodeClone, $rprRprChangeNode->parentNode);
                        $nodesToBeRemoved[] = $rprRprChangeNode;
                    }

                    foreach ($nodesToBeRemoved as $nodeToBeRemoved) {
                        $nodeToBeRemoved->parentNode->parentNode->removeChild($nodeToBeRemoved->parentNode);
                    }
                }

                // w:tblPrChange move nodes and remove the previous w:tblPr
                $queryTblPrChange = './/w:tblPrChange';
                $tblPrChangeNodes = $contentNodeXPath->query($queryTblPrChange, $contentNode);
                if ($tblPrChangeNodes->length > 0) {
                    $nodesToBeRemoved = array();
                    foreach ($tblPrChangeNodes as $tblPrChangeNode) {
                        $tblPrChangeNodeClone = $tblPrChangeNode->firstChild->cloneNode(true);
                        $tblPrChangeNode->parentNode->parentNode->insertBefore($tblPrChangeNodeClone, $tblPrChangeNode->parentNode);
                        $nodesToBeRemoved[] = $tblPrChangeNode;
                    }

                    foreach ($nodesToBeRemoved as $nodeToBeRemoved) {
                        $nodeToBeRemoved->parentNode->parentNode->removeChild($nodeToBeRemoved->parentNode);
                    }
                }

                // w:tblGridChange move nodes and remove the previous w:tblGrid
                $queryTblGridChange = './/w:tblGridChange';
                $tblGridNodes = $contentNodeXPath->query($queryTblGridChange, $contentNode);
                if ($tblGridNodes->length > 0) {
                    $nodesToBeRemoved = array();
                    foreach ($tblGridNodes as $tblGridNode) {
                        $tblGridNodeClone = $tblGridNode->firstChild->cloneNode(true);
                        $tblGridNode->parentNode->parentNode->insertBefore($tblGridNodeClone, $tblGridNode->parentNode);
                        $nodesToBeRemoved[] = $tblGridNode;
                    }

                    foreach ($nodesToBeRemoved as $nodeToBeRemoved) {
                        $nodeToBeRemoved->parentNode->parentNode->removeChild($nodeToBeRemoved->parentNode);
                    }
                }

                // w:tcPrChange move nodes and remove the previous w:tcPr
                $queryTcPrChange = './/w:tcPrChange';
                $tcPrChangeNodes = $contentNodeXPath->query($queryTcPrChange, $contentNode);
                if ($tcPrChangeNodes->length > 0) {
                    $nodesToBeRemoved = array();
                    foreach ($tcPrChangeNodes as $tcPrChangeNode) {
                        $tcPrChangeNodeClone = $tcPrChangeNode->firstChild->cloneNode(true);
                        $tcPrChangeNode->parentNode->parentNode->insertBefore($tcPrChangeNodeClone, $tcPrChangeNode->parentNode);
                        $nodesToBeRemoved[] = $tcPrChangeNode;
                    }

                    foreach ($nodesToBeRemoved as $nodeToBeRemoved) {
                        $nodeToBeRemoved->parentNode->parentNode->removeChild($nodeToBeRemoved->parentNode);
                    }
                }
                
                // w:ins remove node and contents
                $queryIns = './/w:ins';
                $insNodes = $contentNodeXPath->query($queryIns, $contentNode);
                if ($insNodes->length > 0) {
                    foreach ($insNodes as $insNode) {
                        $insNode->parentNode->removeChild($insNode);
                    }
                }

                // w:del remove node and keep the contents
                $queryDel = './/w:del';
                $delNodes = $contentNodeXPath->query($queryDel, $contentNode);
                if ($delNodes->length > 0) {
                    foreach ($delNodes as $delNode) {
                        // move nodes from w:del
                        while ($delNode->hasChildNodes()) {
                            $delChild = $delNode->removeChild($delNode->firstChild);
                            $delNode->parentNode->insertBefore($delChild, $delNode);
                        }

                        // remove the w:del tag
                        $delNode->parentNode->removeChild($delNode);
                    }
                }

                // rename w:delText to w:t
                $queryDelText = './/w:delText';
                $delTextNodes = $contentNodeXPath->query($queryDelText, $contentNode);

                if ($delTextNodes->length > 0) {
                    foreach ($delTextNodes as $delTextNode) {
                        $tNode = $domDocument->createElement('w:t', $delTextNode->nodeValue);
                        $delTextNode->parentNode->insertBefore($tNode, $delTextNode);

                        // remove the previous node
                        $delTextNode->parentNode->removeChild($delTextNode);
                    }
                }
            }

            return $domDocument;
        } else {
            return null;
        }
    }
}