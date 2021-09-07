<?php
/**
 * demo
 * 
 * @author claudiu
 * 
 * add some default functions that you can use in the view templates
 * 
 * usage: echo $this->date();
 *
 */
class Default_Referrer extends Zend_View_Helper_Abstract
{

    public function referrer()
    {
        return Zend_Controller_Front::getInstance()->getRequest()->getServer('HTTP_REFERER');    
    }
    
    
}