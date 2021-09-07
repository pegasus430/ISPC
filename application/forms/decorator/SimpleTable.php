<?php 
/**
 * 
 * @author claudiu
 * @version rc1 
 * @since 2017.12.07
 *
 */
class Zend_Form_Decorator_SimpleTable extends Zend_Form_Decorator_Abstract
{
    
    protected $_format = '<table%s%s>
                <thead>%s</thead>
                <tbody>%s</tbody>
            </table>';
    
    
    
    
    public function getOptions()
    {
        $options = parent::getOptions();
        if (null !== ($element = $this->getElement())) {
            $attribs = $element->getAttribs();
            foreach ($options as $k => &$v) {
                if (isset($attribs[$k]) && is_string($v) && is_string($attribs[$k])) {
                    $v = $attribs[$k] . ' ' . $v;
                }
            }
            $options = array_merge($attribs, $options);
            $this->setOptions($options);
        }
        return $options;
    }
    
    public function getOption($key)
    {
        $_options = $this->getOptions();
        
        $key = (string) $key;
        if (isset($_options[$key])) {
            return $_options[$key];
        }
    
        return null;
    }
    
    
    public function render($content)
    {
        if (null === $this->getElement()->getView()) {
            return $content;
        }
        
        $id = '';
        if ($id = $this->getOption('id')) {
            $id = ' id="'. htmlentities($id) . '"';
        }
        
        $classes = array('SimpleTable');
        if ($class = $this->getOption('class')) {
            $classes[] = htmlentities($class);
        }
        $class = ' class="'. implode(" ", $classes).'"';
        
        $columns_thead = '';
        if ($columns = $this->getOption('columns')) {
            foreach ($columns as $k => $current_column_name) {
                $columns_thead .= "<th class='selector_th_{$k}'>" . $current_column_name . "</th>" . PHP_EOL;
            }
            $columns_thead = '<tr>' . $columns_thead .'</tr>';
        }
        
        $result = sprintf($this->_format, $id, $class, $columns_thead, $content);
        

        return $result;
    }
}