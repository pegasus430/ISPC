<?php 
/**
 * 
 * @author claudiu
 * @version rc1 
 * @since 2017.12.07
 *
 */
class Zend_Form_Decorator_SimpleTableRow extends Zend_Form_Decorator_Abstract
{
    
    protected $_format = '<tr%s%s>%s</tr>';
    
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