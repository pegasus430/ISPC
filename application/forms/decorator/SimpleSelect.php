<?php

/**
 * Class Zend_Form_Decorator_SimpleSelect
 *
 * gives a possibility to manipulate the name-attribute of
 * zend-form-elements without create a subform.
 *
 * @author Bärbel
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Zend_Form_Decorator_SimpleSelect extends Zend_Form_Decorator_Abstract
{

    protected $_select_format = '<select name="%s" id="%s" class="%s">%s</select>';
    protected $_option_format = '<option value="%s" %s>%s</option>';

    public function render($content)
    {
        $element = $this->getElement();
        $name    = htmlentities($element->getFullyQualifiedName());
        $id      = htmlentities($element->getId());
        $key  = htmlentities($element->getAttrib('array_index'));
        $belongsTo = htmlentities($element->getBelongsTo());
        $elementBelongsTo = htmlentities($element->getAttrib('elementBelongsTo'));
        $class = htmlentities($element->getAttrib('class'));
        $options = $element->getAttrib('options');
        $value   = htmlentities($element->getValue());
        $opt = '';

        foreach ( $options as $optkey => $optvalue) {
            $sel = ($value == $optkey) ? 'selected' : '';
            $opt  .= sprintf($this->_option_format, $optkey, $sel, $optvalue);
        }


        $input_name = '';
        if(isset($elementBelongsTo))
            $input_name = $elementBelongsTo;
        if($key == 'noindex'){
            $name = $belongsTo .'['.$name.']';
        }
        else{
            //Zend-Frameworks need an unique name, but this isn't the name i need
            //[name_0] => [name]
            $name = str_replace('_'.$key, '', $name);
            //build the needed path
            //[timelog][name] => [timelog][0][name]
            $name = str_replace($belongsTo, $belongsTo.'['.$key.']', $name);
        }

        //timedocumentation[timelog][0][name]
        $input_name = $input_name . $name;


        $markup  = sprintf($this->_select_format, $input_name, $id, $class, $opt);
        return $markup;
    }
}