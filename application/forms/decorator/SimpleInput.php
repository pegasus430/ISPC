<?php

/**
 * Class Zend_Form_Decorator_SimpleInput
 *
 * gives a possibility to manipulate the name-attribute of
 * zend-form-elements without create a subform.
 *
 * @author Bärbel
 * Maria:: Migration CISPC to ISPC 22.07.2020
 * Maria:: Migration CISPC to ISPC 20.08.2020
 */
class Zend_Form_Decorator_SimpleInput extends Zend_Form_Decorator_Abstract
{

    protected $_input_format = '<input id="%s" class="%s" name="%s" type="%s" value="%s" %s %s />';

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
        $type = ($element->getType() == 'Zend_Form_Element_Hidden') ? 'hidden' : 'text';
        $indexType = htmlentities($element->getAttrib('index_type'));

        $style=htmlentities($element->getAttrib('style'));
        if(strlen($style)){
            $style='style="' . $style.'"';
        }



        $input_name = '';
        if(isset($elementBelongsTo))
            $input_name = $elementBelongsTo;
        if($key == 'noindex'){
            $name = $belongsTo .'['.$name.']';
        }
        else{
            if($indexType == 'array'){
                //[name_0] => [name]
                $name = str_replace('_' . $key, '', $name);
                //build the needed path
                //[timelog][name] => [timelog][name][0]
                $name =  $name . '[' . $key . ']' ;
            }
            else {
                //Zend-Frameworks need an unique name, but this isn't the name i need
                //[name_0] => [name]
                $name = str_replace('_' . $key, '', $name);
                //build the needed path
                //[timelog][name] => [timelog][0][name]
                $name = str_replace($belongsTo, $belongsTo . '[' . $key . ']', $name);
            }
        }

        //timedocumentation[timelog][0][name]
        $input_name = $input_name . $name;


        $markup  = sprintf($this->_input_format, $id, $class, $input_name, $type, $value, $readonly, $style);
        return $markup;
    }
}