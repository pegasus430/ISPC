<?php

/**
 * Class Zend_Form_Decorator_SimpleCheckbox
 *
 * gives a possibility to manipulate the name-attribute of
 * zend-form-elements without create a subform.
 *
 * @author Bärbel
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Zend_Form_Decorator_SimpleCheckbox extends Zend_Form_Decorator_Abstract
{
    //<input type="hidden" name="checkbox_val" value="0">
    protected $_checkbox_hidden_format = '<input type="hidden" name="%s" value="%s">';
    //<input type="checkbox" name="checkbox_val" id="checkbox_val_6" value="1" checked>
    protected $_checkbox_format = '<input type="checkbox" name="%s" id="%s" value="%s" %s>';


    public function render($content)
    {
        $element = $this->getElement();
        $name    = htmlentities($element->getFullyQualifiedName());
        $id      = htmlentities($element->getId());
        $key  = htmlentities($element->getAttrib('array_index'));
        $belongsTo = htmlentities($element->getBelongsTo());
        $elementBelongsTo = htmlentities($element->getAttrib('elementBelongsTo'));
        $checkedValue = htmlentities($element->getCheckedValue());
        $uncheckedValue  = htmlentities($element->getUncheckedValue());
        $value   = htmlentities($element->getValue());
        $disableHidden = htmlentities($element->getAttrib('disableHidden'));

        $checked = ($value == $checkedValue) ? 'checked' : '';

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

        $markup = '';
        if(!$disableHidden)
            $markup = sprintf($this->_checkbox_hidden_format, $input_name, $uncheckedValue);

        $markup  .= sprintf($this->_checkbox_format, $input_name, $id, $checkedValue, $checked);

        return $markup;
    }
}