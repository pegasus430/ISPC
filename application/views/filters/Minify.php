<?php
/**
 * 
 * @author claudiu 
 * Oct 15, 2018
 *
 */
class Version_Control_Minify implements Zend_Filter_Interface
{
    
    public function filter($value = '')
    {
        return $value;
        
        /**
         * will will not work... to much javascript written in wrong places
         */
        return  preg_replace(
            array('/>\s+/', '/\s+</', '/[\r\n]+/'),
            array('>', '<', ' '),
            $value
        );
    }
}