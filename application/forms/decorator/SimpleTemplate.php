<?php

/**
 * Class Zend_Form_Decorator_SimpleTemplate
 *
 * simply render a template
 *
 * @author Nico
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Zend_Form_Decorator_SimpleTemplate extends Zend_Form_Decorator_Abstract
{

    public function render($content)
    {
        $element = $this->getElement();
        $html   = $element->getValue('');


        return $content.$html;
    }
}