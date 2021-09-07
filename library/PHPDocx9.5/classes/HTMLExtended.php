<?php

/**
 * Relate HTML tags to phpdocx methods
 *
 * @category   Phpdocx
 * @package    trasform
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */

class HTMLExtended
{
    /**
     * HTML inline tags => phpdocx method
     * @var array
     */
    public static $tagsInline = array(
        'phpdocx_bookmark' => 'addBookmark',
        'phpdocx_break' => 'addBreak',
        'phpdocx_comment_textdocument' => 'addCommentTextDocument',
        'phpdocx_crossreference' => 'addCrossReference',
        'phpdocx_dateandhour' => 'addDateAndHour',
        'phpdocx_endnote_textdocument' => 'addEndnoteTextDocument',
        'phpdocx_footnote_textdocument' => 'addFootnoteTextDocument',
        'phpdocx_formelement' => 'addFormElement',
        'phpdocx_heading' => 'addHeading',
        'phpdocx_image' => 'addImage',
        'phpdocx_link' => 'addLink',
        'phpdocx_mathequation' => 'addMathEquation',
        'phpdocx_mergefield' => 'addMergeField',
        'phpdocx_modifypagelayout' => 'modifyPageLayout',
        'phpdocx_pagenumber' => 'addPageNumber',
        'phpdocx_section' => 'addSection',
        'phpdocx_shape' => 'addShape',
        'phpdocx_simplefield' => 'addSimpleField',
        'phpdocx_structureddocumenttag' => 'addStructuredDocumentTag',
        'phpdocx_tablecontents' => 'addTableContents',
        'phpdocx_text' => 'addText',
        'phpdocx_textbox' => 'addTextBox',
        'phpdocx_wordfragment' => 'addWordFragment',
        'phpdocx_wordml' => 'addWordML',
    );

    /**
     * HTML block tags => phpdocx method
     * @var array
     */
    public static $tagsBlock = array(
        'phpdocx_comment' => 'addComment',
        'phpdocx_comment_textcomment' => 'addCommentTextComment',
        'phpdocx_endnote' => 'addEndnote',
        'phpdocx_endnote_textendnote' => 'addEndnoteTextEndnote',
        'phpdocx_footer' => 'addFooter',
        'phpdocx_footnote_textfootnote' => 'addFootnoteTextFootnote',
        'phpdocx_footnote' => 'addFootnote',
        'phpdocx_header' => 'addHeader',
    );

    /**
     * Getter $tagsInline
     * @return array
     */
    public static function getTagsInline()
    {
        return self::$tagsInline;
    }

    /**
     * Getter $tagsBlock
     * @return array
     */
    public static function getTagsBlock()
    {
        return self::$tagsBlock;
    }
}
