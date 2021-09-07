<?php
/**
 * @license     phpdocx LICENSE
 */
 
defined('_JEXEC') or die;

/**
 * phpdocx plugin class
 */
class plgContentPhpdocx extends JPlugin
{
    /**
     * Register phpdocx library.
     * @return  void
     */
    public function onContentAfterTitle($context, &$article, &$params, $limitstart)
    {
        require_once JPATH_LIBRARIES . '/phpdocx/classes/CreateDocx.php';
    }
}