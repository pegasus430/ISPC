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
class Default_Date extends Zend_View_Helper_Abstract
{

    private $_date_format_datepicked = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR;
    //private $_date_format_datetime = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR . " ". Zend_Date::HOUR.":".Zend_Date::MINUTE;
    //private $_date_format_db = Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY;
    
    public function date()
    {
        $date = new Zend_Date();
        return $date->toString($this->_date_format_datepicked);
    }
    
    
}