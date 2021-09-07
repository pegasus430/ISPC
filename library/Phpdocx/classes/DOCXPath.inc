<?php

/**
 * Generate xpath queries to select content in a DOCX
 * 
 * @category   Phpdocx
 * @package    DOCXPath
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @version    2016.08.25
 * @link       http://www.phpdocx.com
 */
class DOCXPath extends CreateDocx
{        
    /**
     * Creates the required Xpath query expression
     * 
     * @access public
     * @param string $type can be * (all, default value), bookmark, break, chart, endnote (content reference), footnote (content reference), image, paragraph (also for links and lists), section, shape, table
     * @param array $filters, 
     * Keys and values:
     *     'contains' (string) 
     *     'position' (int)
     *     'ocurrence' (int)
     *     'attributes' (array)
     *     'parent' (string) w:body immediate children (default), '/' (any children) or any other parent (w:tbl/, w:r/...)
     * @param array $options, 
     * Values:
     *     'parse' (bool) if true parses the resulting nodes for images, charts, etcetera
     *     'location' (string) after (default), before, inlineBefore or inlineAfter (don't create a new w:p and add the WordFragment before or after the location)
     * @return DOMNode
     */
    public static function xpathContentQuery($type, $filters, $options = array())
    {
        $contentTypes = array(
            '*' => '*',
            'all' => '*',
            'bookmarkStart' => 'w:bookmarkStart',
            'bookmarkEnd' => 'w:bookmarkEnd',
            'break' => 'w:p/w:r/w:br',
            'chart' => 'w:p/w:r/w:drawing//a:graphic/a:graphicData/c:chart',
            'endnote' => 'w:p/w:r/w:endnoteReference',
            'footnote' => 'w:p/w:r/w:footnoteReference',
            'image' => 'w:p/w:r/w:drawing//a:graphic/a:graphicData/pic:pic',
            'link' => 'w:p/w:hyperlink',
            'paragraph' => 'w:p',
            'section' => 'w:p/w:pPr/w:sectPr',
            'shape' => 'w:p/w:r/w:pict',
            'table' => 'w:tbl',
        );

        if (isset($options['location']) && ($options['location'] == 'inlineBefore' || $options['location'] == 'inlineAfter')) {
            // inline location
            
            if ($type == 'bookmarkStart') {
                $nodeType = $contentTypes[$type] . '/following-sibling::w:p';
            } elseif ($type == 'bookmarkEnd') {
                $nodeType = $contentTypes[$type] . '/preceding-sibling::w:p';
            } else {
                $nodeType = $contentTypes[$type];
            }
        } else {
            $nodeType = $contentTypes[$type];
        }

        // set parent filter, direct children as default
        if (isset($filters['parent'])) {
            $nodeType = $filters['parent'] . $nodeType;
        }

        $condition = '1=1';
        
        $lastCondition = '';

        if (isset($filters['contains'])) {
            if ($type == 'bookmarkStart') {
                $contentFilter = ' following-sibling::w:p[contains(., \'' . $filters['contains']  . '\')]';
            } elseif ($type == 'bookmarkEnd') {
                $contentFilter = ' preceding-sibling::w:p[contains(., \'' . $filters['contains']  . '\')]';
            } else {
                $contentFilter =  ' contains(., \'' . $filters['contains']  . '\')';
            }

            $condition .= ' and ' . $contentFilter;
        }

        // match elements that are childs of "position" level.
        if (isset($filters['position']) && is_int($filters['position'])) {
            if ($filters['position'] < 0) {
                $lastCondition = '[last()]';
            } else {
                $positionFilter =  'position()=' . $filters['position'];
                $condition .= ' and ' . $positionFilter;     
            }
           
        }

        if (isset($filters['attributes']) && is_array($filters['attributes'])) {
            // the attribute value may be a string if getting the current element
            // or an array if getting a descendant of the current element
            foreach ($filters['attributes'] as $keyAttribute => $valueAttribute) {
                if (is_array($valueAttribute)) {
                    // get the descendant of the current element based on the key attribute to get the descendant
                    foreach ($valueAttribute as $keyValue => $valueValue) {
                        $condition .= ' and descendant::'.$keyAttribute.'[contains(@'.$keyValue.', "'.$valueValue.'")]';
                    }
                } else {
                    // get the current element
                    $condition .= ' and contains(@'.$keyAttribute.', "'.$valueAttribute.'")';
                }
            }
        }
        
        $mainQuery = '//w:body/' . $nodeType . '[' . $condition . ']' . $lastCondition;

        if (isset($options['location']) && ($options['location'] == 'inlineBefore' || $options['location'] == 'inlineAfter')) {
            // inline location
            
            // get the body parent, needed to get the right child 
            if ($type == 'shape') {
                $mainQuery .= '/../..';
            } elseif ($type == 'chart' || $type == 'image') {
                $mainQuery .= '/../../../../../..';
            }

            $mainQuery .= '/w:r';
            
        } else {
            // get the body parent, needed to get the right child 
            if ($type == 'break' || $type == 'endnote' || $type == 'footnote' || $type == 'shape') {
                $mainQuery .= '/../..';
            } elseif ($type == 'chart' || $type == 'image') {
                $mainQuery .= '/../../../../../..';
            } elseif ($type == 'link') {
                $mainQuery .= '/..';
            }

            if ($type == 'section') {
                $mainQuery .= '/../..';
            }
        }
        
        // ocurrence
        // if first() set value as 1
        if (isset($filters['ocurrence']) && $filters['ocurrence'] === 'first()') {
            $filters['ocurrence'] = 1;
        }
        // if last() set value as 1
        if (isset($filters['ocurrence']) && $filters['ocurrence'] === 'last()') {
            $filters['ocurrence'] = -1;
        }
        if(isset($filters['ocurrence']) && is_int($filters['ocurrence'])) {
            $ocurrence = ($filters['ocurrence'] < 0) ? 'last()' : $filters['ocurrence'];
            $mainQuery = '(' . $mainQuery . ')[' . $ocurrence . ']';
        }
        
        $query = $mainQuery;

        return $query;
    }
    
}