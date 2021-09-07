<?php
/**
 * demo
 * 
 * @author claudiu
 * 
 * add some default functions that you can use in the view templates
 * 
 * usage: echo $this->time();
 *
 */
class Default_Time extends Zend_View_Helper_Abstract
{
	
    public function time()
    {
        $date = new Zend_Date();

        return $date->get(Zend_Date::TIME_MEDIUM);
    }
}