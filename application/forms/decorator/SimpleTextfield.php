<?php

/**
 * Class Zend_Form_Decorator_SimpleTextfield
 *
 * gives a possibility to manipulate the name-attribute of
 * zend-form-elements without create a subform.
 *
 * @author Bärbel
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Zend_Form_Decorator_SimpleTextfield extends Zend_Form_Decorator_Abstract
{
   // <textarea name="item_1"  rows="24" cols="80" style="overflow: hidden; height: 36px;"></textarea>
    protected $_input_format = '<textarea id="%s" class="%s" name="%s"rows="%s" cols="%s" %s >%s</textarea>';

    public function render($content)
    {
        $element = $this->getElement();
        $name    = htmlentities($element->getFullyQualifiedName());
        $id      = htmlentities($element->getId());
        $value   = htmlentities($element->getValue());
        $readonly  = htmlentities($element->getAttrib('readonly')) == true ? 'readonly': '';
        $key  = htmlentities($element->getAttrib('array_index'));
        $belongsTo = htmlentities($element->getBelongsTo());
        $elementBelongsTo = htmlentities($element->getAttrib('elementBelongsTo'));
        $class = htmlentities($element->getAttrib('class'));
        $rows = htmlentities($element->getAttrib('rows')) == '' ? '24' : htmlentities($element->getAttrib('rows'));
        $cols = htmlentities($element->getAttrib('cols')) == '' ? '80' : htmlentities($element->getAttrib('cols'));


        $input_name = '';
        if(isset($elementBelongsTo))
            $input_name = $elementBelongsTo;
        if($key != 'noindex'){
            //Zend-Frameworks need an unique name, but this isn't the name i need
            //[name_0] => [name]
            $name = str_replace('_'.$key, '', $name);
            //build the needed path
            //[timelog][name] => [timelog][0][name]
            $name = str_replace($belongsTo, $belongsTo.'['.$key.']', $name);
        }

        //timedocumentation[timelog][0][name]
        $input_name = $input_name . $name;


        $markup  = sprintf($this->_input_format, $id, $class, $input_name, $rows, $cols, $readonly, $value);
        return $markup;
    }
}