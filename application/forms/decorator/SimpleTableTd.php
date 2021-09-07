<?php 
/**
 * 
 * @author claudiu
 * @version rc1 
 * @since 2017.12.07
 *
 */
class Zend_Form_Decorator_SimpleTableTd extends Zend_Form_Decorator_Abstract
{
    
    protected $_format = '<td%s%s>%s</td>';
    
    public function render($content)
    {
        if (null === $this->getElement()->getView()) {
            return $content;
        }
        
        $id = '';
        if ($id = $this->getOption('id')) {
            $id = ' id="'. htmlentities($id) . '"';
        }
        
        $class = '';
        if ($class = $this->getOption('class')) {
            $class = ' class="'. htmlentities($class).'"';
        }
        
        $result = sprintf($this->_format, $id, $class, $content);
        
        return $result;
    }
}